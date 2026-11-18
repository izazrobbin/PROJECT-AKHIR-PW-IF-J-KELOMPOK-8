<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| ARTANA ATS - AUTHENTICATION PROCESS
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/db.php';

/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| REDIRECT FUNCTION
|--------------------------------------------------------------------------
*/

function auth_redirect(string $page): never
{
    header('Location: ' . BASE_URL . '/index.php?page=' . $page);
    exit;
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

function auth_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/*
|--------------------------------------------------------------------------
| POST REQUEST CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_redirect('home');
}

/*
|--------------------------------------------------------------------------
| CSRF VALIDATION
|--------------------------------------------------------------------------
*/

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrfToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {

    auth_flash(
        'danger',
        'Permintaan tidak valid. Silakan coba kembali.'
    );

    auth_redirect('login');

}

/*
|--------------------------------------------------------------------------
| ACTION
|--------------------------------------------------------------------------
*/

$action = $_POST['action'] ?? '';


/*
|--------------------------------------------------------------------------
| 1. REGISTRASI KANDIDAT
|--------------------------------------------------------------------------
*/

if ($action === 'register') {

    $fullName = trim($_POST['full_name'] ?? '');

    $email = strtolower(trim($_POST['email'] ?? ''));

    $password = $_POST['password'] ?? '';

    $confirmPassword = $_POST['confirm_password'] ?? '';

    $terms = $_POST['terms'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */

    if (
        $fullName === '' ||
        $email === '' ||
        $password === '' ||
        $confirmPassword === ''
    ) {

        auth_flash(
            'danger',
            'Semua kolom wajib diisi.'
        );

        auth_redirect('register');

    }


    if (strlen($fullName) > 150) {

        auth_flash(
            'danger',
            'Nama maksimal 150 karakter.'
        );

        auth_redirect('register');

    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        auth_flash(
            'danger',
            'Format email tidak valid.'
        );

        auth_redirect('register');

    }


    if (strlen($email) > 190) {

        auth_flash(
            'danger',
            'Email terlalu panjang.'
        );

        auth_redirect('register');

    }


    if (strlen($password) < 8) {

        auth_flash(
            'danger',
            'Password minimal 8 karakter.'
        );

        auth_redirect('register');

    }


    if ($password !== $confirmPassword) {

        auth_flash(
            'danger',
            'Konfirmasi password tidak sesuai.'
        );

        auth_redirect('register');

    }


    if ($terms !== '1') {

        auth_flash(
            'danger',
            'Silakan setujui persyaratan pendaftaran.'
        );

        auth_redirect('register');

    }


    /*
    |--------------------------------------------------------------------------
    | HASH PASSWORD
    |--------------------------------------------------------------------------
    */

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    /*
    |--------------------------------------------------------------------------
    | SIMPAN DATA KANDIDAT
    |--------------------------------------------------------------------------
    */

    try {

        $pdo->beginTransaction();

        /*
        | Insert akun kandidat
        */

        $stmt = $pdo->prepare("
            INSERT INTO users
            (
                full_name,
                email,
                password_hash,
                role,
                is_active
            )
            VALUES
            (
                :full_name,
                :email,
                :password_hash,
                'candidate',
                1
            )
        ");

        $stmt->execute([

            'full_name' => $fullName,

            'email' => $email,

            'password_hash' => $passwordHash

        ]);


        /*
        | Ambil ID kandidat
        */

        $userId = (int) $pdo->lastInsertId();


        /*
        | Buat profil kandidat otomatis
        */

        $profileStmt = $pdo->prepare("
            INSERT INTO candidate_profiles
            (
                user_id
            )
            VALUES
            (
                :user_id
            )
        ");

        $profileStmt->execute([
            'user_id' => $userId
        ]);


        /*
        | Simpan transaksi
        */

        $pdo->commit();


        auth_flash(
            'success',
            'Registrasi berhasil! Silakan login menggunakan akunmu.'
        );

        auth_redirect('login');


    } catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());


        /*
        | Email sudah terdaftar
        */

        if ($e->getCode() === '23000') {

            auth_flash(
                'danger',
                'Email sudah terdaftar. Gunakan email lain.'
            );

            auth_redirect('register');

        }


        auth_flash(
            'danger',
            'Registrasi gagal. Silakan coba kembali.'
        );

        auth_redirect('register');

    }

}


/*
|--------------------------------------------------------------------------
| 2. LOGIN KANDIDAT DAN HR
|--------------------------------------------------------------------------
*/

elseif ($action === 'login') {

    $email = strtolower(trim($_POST['email'] ?? ''));

    $password = $_POST['password'] ?? '';

    $rememberEmail = isset($_POST['remember_email']);


    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */

    if (
        $email === '' ||
        $password === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        auth_flash(
            'danger',
            'Email atau password tidak valid.'
        );

        auth_redirect('login');

    }


    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA USER
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            full_name,
            email,
            password_hash,
            role,
            is_active

        FROM users

        WHERE email = :email

        LIMIT 1
    ");

    $stmt->execute([
        'email' => $email
    ]);

    $user = $stmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | VERIFIKASI PASSWORD DAN STATUS AKUN
    |--------------------------------------------------------------------------
    */

    if (
        !$user ||
        !password_verify($password, $user['password_hash']) ||
        (int) $user['is_active'] !== 1
    ) {

        auth_flash(
            'danger',
            'Email atau password salah, atau akun tidak aktif.'
        );

        auth_redirect('login');

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE LAST LOGIN
    |--------------------------------------------------------------------------
    */

    $updateLogin = $pdo->prepare("
        UPDATE users

        SET last_login_at = NOW()

        WHERE id = :id
    ");

    $updateLogin->execute([
        'id' => $user['id']
    ]);


    /*
    |--------------------------------------------------------------------------
    | BUAT SESSION
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];

    $_SESSION['full_name'] = $user['full_name'];

    $_SESSION['email'] = $user['email'];

    $_SESSION['role'] = $user['role'];

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));


    /*
    |--------------------------------------------------------------------------
    | REMEMBER EMAIL COOKIE
    |--------------------------------------------------------------------------
    */

    $cookieOptions = [

        'expires' => $rememberEmail
            ? time() + (30 * 24 * 60 * 60)
            : time() - 3600,

        'path' => BASE_URL . '/',

        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',

        'httponly' => true,

        'samesite' => 'Lax'

    ];


    if ($rememberEmail) {

        setcookie(
            'remember_email',
            $email,
            $cookieOptions
        );

    } else {

        setcookie(
            'remember_email',
            '',
            $cookieOptions
        );

    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT BERDASARKAN ROLE
    |--------------------------------------------------------------------------
    */

    if ($user['role'] === 'hr') {

        header(
            'Location: ' . BASE_URL . '/hr/dashboard.php'
        );

        exit;

    }


    if ($user['role'] === 'candidate') {

        auth_redirect('home');

    }


    /*
    | Role tidak dikenal
    */

    $_SESSION = [];

    session_destroy();

    auth_redirect('login');

}


/*
|--------------------------------------------------------------------------
| ACTION TIDAK DIKENAL
|--------------------------------------------------------------------------
*/

else {

    auth_flash(
        'danger',
        'Aksi autentikasi tidak dikenal.'
    );

    auth_redirect('login');

}