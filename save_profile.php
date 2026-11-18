```php
<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/index.php?page=profile');
}

if (!verify_csrf()) {
    set_flash('danger', 'Permintaan tidak valid. Silakan coba kembali.');
    redirect(BASE_URL . '/index.php?page=profile');
}

$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$portfolioUrl = trim($_POST['portfolio_url'] ?? '');

if ($fullName === '') {
    set_flash('danger', 'Nama lengkap wajib diisi.');
    redirect(BASE_URL . '/index.php?page=profile');
}

if ($portfolioUrl !== '' && !filter_var($portfolioUrl, FILTER_VALIDATE_URL)) {
    set_flash('danger', 'Link portfolio tidak valid.');
    redirect(BASE_URL . '/index.php?page=profile');
}

$uploadDir = __DIR__ . '/uploads/resumes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$oldStatement = $pdo->prepare(
    "SELECT resume_path FROM candidate_profiles WHERE user_id = ?"
);
$oldStatement->execute([$userId]);
$oldResume = $oldStatement->fetchColumn();

$newFilePath = null;
$newAbsolutePath = null;

try {
    if (
        isset($_FILES['resume']) &&
        $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['resume'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload CV gagal. Silakan coba kembali.');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('Ukuran CV maksimal 2 MB.');
        }

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if ($extension !== 'pdf') {
            throw new Exception('CV harus menggunakan format PDF.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if ($mimeType !== 'application/pdf') {
            throw new Exception('File yang diunggah bukan PDF yang valid.');
        }

        $fileSignature = file_get_contents($file['tmp_name'], false, null, 0, 5);

        if ($fileSignature !== '%PDF-') {
            throw new Exception('Isi file PDF tidak valid.');
        }

        $newFileName = bin2hex(random_bytes(16)) . '.pdf';
        $newAbsolutePath = $uploadDir . $newFileName;
        $newFilePath = 'uploads/resumes/' . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $newAbsolutePath)) {
            throw new Exception('Gagal menyimpan file CV.');
        }
    }

    $pdo->beginTransaction();

    $updateUser = $pdo->prepare(
        "UPDATE users SET full_name = ? WHERE id = ?"
    );
    $updateUser->execute([$fullName, $userId]);

    $resumePath = $newFilePath !== null
        ? $newFilePath
        : $oldResume;

    $saveProfile = $pdo->prepare("
        INSERT INTO candidate_profiles
            (user_id, phone, portfolio_url, resume_path, updated_at)
        VALUES
            (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            phone = VALUES(phone),
            portfolio_url = VALUES(portfolio_url),
            resume_path = VALUES(resume_path),
            updated_at = NOW()
    ");

    $saveProfile->execute([
        $userId,
        $phone,
        $portfolioUrl,
        $resumePath
    ]);

    $pdo->commit();

    // Perbarui nama pada session.
    $_SESSION['full_name'] = $fullName;

    // Periksa apakah CV lama masih digunakan oleh lamaran.
    $oldResumeUsed = false;

    if ($newFilePath !== null && !empty($oldResume)) {
        $checkOldResume = $pdo->prepare("
            SELECT COUNT(*)
            FROM applications
            WHERE resume_path = ?
        ");
        $checkOldResume->execute([$oldResume]);

        $oldResumeUsed = ((int) $checkOldResume->fetchColumn()) > 0;
    }

    // Hapus CV lama hanya jika tidak dipakai oleh lamaran mana pun.
    if (
        $newFilePath !== null &&
        !empty($oldResume) &&
        !$oldResumeUsed
    ) {
        $oldAbsolutePath = __DIR__ . '/' . ltrim($oldResume, '/');

        if (is_file($oldAbsolutePath)) {
            @unlink($oldAbsolutePath);
        }
    }

    set_flash('success', 'Profil berhasil diperbarui.');
    redirect(BASE_URL . '/index.php?page=profile');

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Hapus file baru jika penyimpanan database gagal.
    if ($newAbsolutePath !== null && is_file($newAbsolutePath)) {
        @unlink($newAbsolutePath);
    }

    error_log('Save profile error: ' . $e->getMessage());

    set_flash('danger', $e->getMessage());
    redirect(BASE_URL . '/index.php?page=profile');
}