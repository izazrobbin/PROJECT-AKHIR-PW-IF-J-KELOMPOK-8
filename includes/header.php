
<?php
require_once __DIR__ . '/functions.php';

$user = current_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARTANA CORPORATION</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<a class="navbar-brand d-flex align-items-center gap-3"
   href="<?= BASE_URL ?>/index.php">

    <img
        src="<?= BASE_URL ?>/assets/images/logo.jpeg"
        alt="Logo ARTANA"
        style="width: 55px; height: 55px; object-fit: contain;"
    >

    <span class="fw-bold text-white"
          style="font-size: 22px; letter-spacing: 1px;">
        ARTANA ATS
    </span>

</a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php?page=home">
                        Beranda
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php?page=jobs">
                        Lowongan
                    </a>
                </li>

                <?php if ($user && $user['role'] === 'candidate'): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php?page=my-applications">
                            Lamaran Saya
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php?page=profile">
                            Profil
                        </a>
                    </li>

                <?php elseif ($user && $user['role'] === 'hr'): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/hr/dashboard.php">
                            Dashboard HR
                        </a>
                    </li>

                <?php endif; ?>

                <?php if ($user): ?>

                    <li class="nav-item">
                        <span class="nav-link text-warning">
                            <?= htmlspecialchars($user['name']) ?>
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/logout.php">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php?page=login">
                            Login
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php?page=register">
                            Daftar
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
