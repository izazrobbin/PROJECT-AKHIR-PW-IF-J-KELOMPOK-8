
<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// 1. Mengambil halaman
$page = $_GET['page'] ?? 'home';

// 2. Daftar halaman yang diperbolehkan
$allowedPages = [
    'home',
    'jobs',
    'job_detail',
    'login',
    'register',
    'profile',
    'apply',
    'my-applications'
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

// 3. Halaman khusus kandidat
$candidatePages = [
    'profile',
    'apply',
    'my-applications'
];

if (in_array($page, $candidatePages, true)) {
    require_candidate();
}

// 4. Pengalihan pengguna HR
if (
    is_logged_in()
    && ($_SESSION['role'] ?? '') === 'hr'
    && in_array($page, ['login', 'register'], true)
) {
    redirect(BASE_URL . '/hr/dashboard.php');
}

// 5. Pengalihan kandidat yang sudah login
if (
    is_logged_in()
    && ($_SESSION['role'] ?? '') === 'candidate'
    && in_array($page, ['login', 'register'], true)
) {
    $page = 'home';
}

// 6. Pemetaan URL ke file
$pageFiles = [
    'home' => 'home.php',
    'jobs' => 'jobs.php',
    'job_detail' => 'job_detail.php',
    'login' => 'login.php',
    'register' => 'register.php',
    'profile' => 'profile.php',
    'apply' => 'apply.php',
    'my-applications' => 'my_applications.php'
];

// 7. Menentukan file halaman
$pageFile = __DIR__ . '/pages/' . $pageFiles[$page];

// 8. Memeriksa file
if (!file_exists($pageFile)) {
    http_response_code(404);
    $pageFile = __DIR__ . '/pages/home.php';
}

// 9. Menampilkan halaman
require_once __DIR__ . '/includes/header.php';

require $pageFile;

require_once __DIR__ . '/includes/footer.php';

