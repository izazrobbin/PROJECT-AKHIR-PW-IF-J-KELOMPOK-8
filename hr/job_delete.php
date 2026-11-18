<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_hr();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/hr/jobs.php');
    exit;
}

if (!verify_csrf()) {
    set_flash('danger', 'Permintaan tidak valid. Silakan coba kembali.');
    header('Location: ' . BASE_URL . '/hr/jobs.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID lowongan tidak valid.');
    header('Location: ' . BASE_URL . '/hr/jobs.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, status
    FROM jobs
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);

$job = $stmt->fetch();

if (!$job) {
    set_flash('danger', 'Lowongan tidak ditemukan.');
    header('Location: ' . BASE_URL . '/hr/jobs.php');
    exit;
}

$update = $pdo->prepare("
    UPDATE jobs
    SET
        status = 'archived',
        updated_by = ?
    WHERE id = ?
");

$user = current_user();

$update->execute([
    $user['id'],
    $id
]);

set_flash('success', 'Lowongan berhasil diarsipkan.');

header('Location: ' . BASE_URL . '/hr/jobs.php');
exit;