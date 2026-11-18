```php
// Ambil data profil kandidat, termasuk lokasi CV.
$profileStmt = $pdo->prepare("
    SELECT phone, portfolio_url, resume_path
    FROM candidate_profiles
    WHERE user_id = ?
    LIMIT 1
");
$profileStmt->execute([(int) $user['id']]);
$candidateProfile = $profileStmt->fetch(PDO::FETCH_ASSOC);

if (!$candidateProfile || empty($candidateProfile['resume_path'])) {
    set_flash('danger', 'Silakan unggah CV melalui halaman Profil sebelum melamar.');
    redirect(BASE_URL . '/index.php?page=profile');
}

$resumePath = $candidateProfile['resume_path'];
```
```php
<?php

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

// ==================================================
// 1. Pastikan pengguna sudah login sebagai kandidat
// ==================================================

require_candidate();

$user = current_user();
$userId = (int) $user['id'];

// ==================================================
// 2. Pastikan request menggunakan POST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/index.php?page=jobs');
}

// ==================================================
// 3. Validasi CSRF
// ==================================================

if (!verify_csrf()) {
    set_flash('danger', 'Permintaan tidak valid. Silakan coba kembali.');
    redirect(BASE_URL . '/index.php?page=jobs');
}

// ==================================================
// 4. Ambil ID lowongan
// ==================================================

$jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);

if (!$jobId || $jobId < 1) {
    set_flash('danger', 'Lowongan yang dipilih tidak valid.');
    redirect(BASE_URL . '/index.php?page=jobs');
}

// ==================================================
// 5. Ambil catatan pengalaman kandidat
// ==================================================

$experienceNotes = trim($_POST['experience_notes'] ?? '');

if (strlen($experienceNotes) > 5000) {
    set_flash('danger', 'Catatan pengalaman maksimal 5000 karakter.');
    redirect(BASE_URL . '/index.php?page=job_detail&id=' . $jobId);
}

// ==================================================
// 6. Periksa lowongan
// ==================================================

$jobStmt = $pdo->prepare("
    SELECT
        id,
        title,
        status,
        deadline
    FROM jobs
    WHERE id = ?
    LIMIT 1
");

$jobStmt->execute([$jobId]);
$job = $jobStmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    set_flash('danger', 'Lowongan tidak ditemukan.');
    redirect(BASE_URL . '/index.php?page=jobs');
}

if ($job['status'] !== 'published') {
    set_flash('danger', 'Lowongan ini sudah tidak tersedia.');
    redirect(BASE_URL . '/index.php?page=jobs');
}

if (
    !empty($job['deadline']) &&
    $job['deadline'] < date('Y-m-d')
) {
    set_flash('danger', 'Batas waktu pendaftaran lowongan sudah berakhir.');
    redirect(BASE_URL . '/index.php?page=jobs');
}

// ==================================================
// 7. Ambil data profil kandidat dan lokasi CV
// ==================================================

$profileStmt = $pdo->prepare("
    SELECT
        u.full_name,
        u.email,
        cp.phone,
        cp.portfolio_url,
        cp.resume_path
    FROM users u
    LEFT JOIN candidate_profiles cp
        ON cp.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");

$profileStmt->execute([$userId]);
$candidateProfile = $profileStmt->fetch(PDO::FETCH_ASSOC);

if (!$candidateProfile) {
    set_flash('danger', 'Data kandidat tidak ditemukan.');
    redirect(BASE_URL . '/index.php?page=profile');
}

// ==================================================
// 8. Pastikan kandidat sudah memiliki CV
// ==================================================

if (empty($candidateProfile['resume_path'])) {
    set_flash(
        'danger',
        'Silakan unggah CV melalui halaman Profil sebelum melamar.'
    );

    redirect(BASE_URL . '/index.php?page=profile');
}

$resumePath = $candidateProfile['resume_path'];

// ==================================================
// 9. Pastikan file CV benar-benar tersedia
// ==================================================

$relativePath = str_replace('\\', '/', $resumePath);

if (
    strpos($relativePath, 'uploads/resumes/') !== 0 ||
    strpos($relativePath, '..') !== false
) {
    set_flash('danger', 'Lokasi CV tidak valid. Silakan unggah ulang CV.');
    redirect(BASE_URL . '/index.php?page=profile');
}

$baseDirectory = realpath(__DIR__ . '/uploads/resumes');
$filePath = realpath(__DIR__ . '/' . $relativePath);

if (
    $baseDirectory === false ||
    $filePath === false ||
    strpos($filePath, $baseDirectory . DIRECTORY_SEPARATOR) !== 0 ||
    !is_file($filePath)
) {
    set_flash('danger', 'File CV tidak ditemukan. Silakan unggah ulang CV.');
    redirect(BASE_URL . '/index.php?page=profile');
}

// ==================================================
// 10. Periksa apakah kandidat sudah pernah melamar
// ==================================================

$duplicateStmt = $pdo->prepare("
    SELECT id
    FROM applications
    WHERE candidate_id = ?
      AND job_id = ?
    LIMIT 1
");

$duplicateStmt->execute([$userId, $jobId]);

if ($duplicateStmt->fetch()) {
    set_flash('warning', 'Kamu sudah pernah melamar lowongan ini.');
    redirect(BASE_URL . '/index.php?page=my-applications');
}

// ==================================================
// 11. Buat kode lamaran
// ==================================================

$applicationCode = 'APP-' . date('Ymd') . '-' .
    strtoupper(bin2hex(random_bytes(3)));

// ==================================================
// 12. Simpan lamaran ke database
// ==================================================

try {

    $pdo->beginTransaction();

    $insertStmt = $pdo->prepare("
        INSERT INTO applications (
            application_code,
            job_id,
            candidate_id,
            candidate_name_snapshot,
            email_snapshot,
            phone_snapshot,
            portfolio_url_snapshot,
            resume_path,
            experience_notes,
            status,
            applied_at
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Reviewing', NOW()
        )
    ");

    $insertStmt->execute([
        $applicationCode,
        $jobId,
        $userId,
        $candidateProfile['full_name'],
        $candidateProfile['email'],
        $candidateProfile['phone'] ?? '',
        $candidateProfile['portfolio_url'] ?? '',
        $resumePath,
        $experienceNotes
    ]);

    $pdo->commit();

    set_flash(
        'success',
        'Lamaran berhasil dikirim. Kode lamaran: ' . $applicationCode
    );

    redirect(BASE_URL . '/index.php?page=my-applications');

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Application database error: ' . $e->getMessage());

    // SQLSTATE 23000 biasanya menunjukkan pelanggaran constraint,
    // termasuk lamaran ganda.
    if ($e->getCode() === '23000') {
        set_flash(
            'warning',
            'Kamu sudah pernah melamar lowongan ini atau data tidak memenuhi aturan database.'
        );

        redirect(BASE_URL . '/index.php?page=my-applications');
    }

    set_flash(
        'danger',
        'Terjadi kesalahan saat menyimpan lamaran. Silakan coba kembali.'
    );

    redirect(BASE_URL . '/index.php?page=job_detail&id=' . $jobId);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Application error: ' . $e->getMessage());

    set_flash(
        'danger',
        'Terjadi kesalahan saat memproses lamaran.'
    );

    redirect(BASE_URL . '/index.php?page=job_detail&id=' . $jobId);
}
