<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

require_hr();

if (!isset($_GET['id']) || !ctype_digit((string) $_GET['id'])) {
    http_response_code(400);
    exit('ID lamaran tidak valid.');
}

$applicationId = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT resume_path
        FROM applications
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application || empty($application['resume_path'])) {
        http_response_code(404);
        exit('CV tidak ditemukan pada data lamaran.');
    }

    $relativePath = $application['resume_path'];

    if (
        strpos($relativePath, 'uploads/resumes/') !== 0 ||
        strpos($relativePath, '..') !== false
    ) {
        http_response_code(403);
        exit('Lokasi file tidak diizinkan.');
    }

    $baseDirectory = realpath(__DIR__ . '/uploads/resumes');
    $filePath = realpath(__DIR__ . '/' . $relativePath);

    if (
        $baseDirectory === false ||
        $filePath === false ||
        strpos($filePath, $baseDirectory . DIRECTORY_SEPARATOR) !== 0 ||
        !is_file($filePath)
    ) {
        http_response_code(404);
        exit('File CV tidak ditemukan di folder penyimpanan.');
    }

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $filePath);
    finfo_close($fileInfo);

    if ($mimeType !== 'application/pdf') {
        http_response_code(415);
        exit('File yang diminta bukan PDF.');
    }

    $downloadName = 'CV_Lamaran_' . $applicationId . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');

    readfile($filePath);
    exit;

} catch (PDOException $e) {
    error_log('Download CV error: ' . $e->getMessage());
    http_response_code(500);
    exit('Terjadi kesalahan database saat mengambil CV.');
}
