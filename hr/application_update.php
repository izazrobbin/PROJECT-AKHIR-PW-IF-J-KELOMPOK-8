<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_hr();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/hr/applications.php');
    exit;
}

if (!verify_csrf()) {
    set_flash('danger', 'Token keamanan tidak valid.');
    header('Location: ' . BASE_URL . '/hr/applications.php');
    exit;
}

$user = current_user();

$id = (int) ($_POST['id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$hrNotes = trim($_POST['hr_notes'] ?? '');

$validStatuses = ['Reviewing', 'Interview', 'Offered', 'Rejected'];

if ($id <= 0 || !in_array($newStatus, $validStatuses, true)) {
    set_flash('danger', 'Data perubahan lamaran tidak valid.');
    header('Location: ' . BASE_URL . '/hr/applications.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT id, status
        FROM applications
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);
    $application = $stmt->fetch();

    if (!$application) {
        $pdo->rollBack();

        set_flash('danger', 'Lamaran tidak ditemukan.');
        header('Location: ' . BASE_URL . '/hr/applications.php');
        exit;
    }

    $oldStatus = $application['status'];

    $update = $pdo->prepare("
        UPDATE applications
        SET
            status = :status,
            reviewed_by = :reviewed_by,
            reviewed_at = NOW(),
            hr_notes = :hr_notes
        WHERE id = :id
    ");

    $update->execute([
        ':status' => $newStatus,
        ':reviewed_by' => $user['id'],
        ':hr_notes' => $hrNotes,
        ':id' => $id
    ]);

    if ($oldStatus !== $newStatus) {
        $history = $pdo->prepare("
            INSERT INTO application_status_history (
                application_id,
                old_status,
                new_status,
                changed_by,
                note,
                changed_at
            ) VALUES (
                :application_id,
                :old_status,
                :new_status,
                :changed_by,
                :note,
                NOW()
            )
        ");

        $history->execute([
            ':application_id' => $id,
            ':old_status' => $oldStatus,
            ':new_status' => $newStatus,
            ':changed_by' => $user['id'],
            ':note' => $hrNotes
        ]);
    }

    $pdo->commit();

    set_flash('success', 'Status lamaran berhasil diperbarui.');

    header(
        'Location: ' . BASE_URL . '/hr/application_detail.php?id=' . $id
    );
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Gagal memperbarui lamaran: ' . $e->getMessage());

    set_flash('danger', 'Terjadi kesalahan saat menyimpan perubahan lamaran.');

    header(
        'Location: ' . BASE_URL . '/hr/application_detail.php?id=' . $id
    );
    exit;
}