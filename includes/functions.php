
<?php

require_once __DIR__ . '/../config/config.php';

/**
 * Mengarahkan pengguna.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Memeriksa status login.
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

/**
 * Mengambil data pengguna.
 */
function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['full_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role'  => $_SESSION['role'] ?? ''
    ];
}

/**
 * Menyimpan notifikasi.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Mengambil notifikasi.
 */
function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Memastikan pengguna sudah login.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan login terlebih dahulu.');

        redirect(BASE_URL . '/index.php?page=login');
    }
}

/**
 * Membatasi akses Kandidat.
 */
function require_candidate(): void
{
    require_login();

    if ($_SESSION['role'] !== 'candidate') {

        set_flash(
            'danger',
            'Anda tidak memiliki akses ke halaman tersebut.'
        );

        if ($_SESSION['role'] === 'hr') {
            redirect(BASE_URL . '/hr/dashboard.php');
        }

        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Membatasi akses Admin/HR.
 */
function require_hr(): void
{
    require_login();

    if ($_SESSION['role'] !== 'hr') {

        set_flash(
            'danger',
            'Halaman ini hanya dapat diakses oleh Admin/HR.'
        );

        redirect(BASE_URL . '/index.php');
    }
}

/**
 * Membuat token CSRF.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Memeriksa token CSRF.
 */
function verify_csrf(): bool
{
    $submittedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    return is_string($submittedToken)
        && is_string($sessionToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $submittedToken);
}

/**
 * Mengakhiri sesi pengguna.
 */
function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax'
        ]);
    }

    session_destroy();
}
