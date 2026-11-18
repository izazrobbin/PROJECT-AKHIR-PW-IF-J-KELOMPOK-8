```php
<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

require_hr();

$applicationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$applicationId || $applicationId < 1) {
    http_response_code(400);
    exit('ID lamaran tidak valid.');
}

$stmt = $pdo->prepare("
    SELECT resume_path
    FROM applications
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$applicationId]);

$resumePath = $stmt->fetchColumn();

if (!$resumePath) {
    http_response_code(404);
    exit('CV tidak ditemukan pada data lamaran ini.');
}

// Pastikan file hanya berasal dari folder uploads/resumes.
$relativePath = str_replace('\\', '/', $resumePath);

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
    exit('File CV tidak tersedia di server.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($filePath);

if ($mimeType !== 'application/pdf') {
    http_response_code(403);
    exit('File yang diminta bukan PDF.');
}

$fileName = 'CV_Lamaran_' . $applicationId . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;
```