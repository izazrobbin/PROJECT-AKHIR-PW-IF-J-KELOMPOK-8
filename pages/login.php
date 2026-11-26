<?php

/*
|--------------------------------------------------------------------------
| LOGIN PAGE
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| REMEMBER EMAIL
|--------------------------------------------------------------------------
*/

$rememberedEmail = (
    isset($_COOKIE['remember_email']) &&
    is_string($_COOKIE['remember_email'])
)
    ? $_COOKIE['remember_email']
    : '';

/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

?>

<section class="auth-section">

    <div class="container">

        <div class="auth-wrapper">

            <!-- LEFT SIDE -->

            <div class="auth-information">

                <span class="section-label"style="color: #f9f9f9;">
                    WELCOME TO ARTANA CORPORATION
                    
                </span>

                <h1>
                    Welcome
                    <span>Back.</span>
                </h1>

                <p>

                    Masuk ke akun ARTANA ATS untuk
                    melanjutkan aktivitas rekrutmen
                    dan pengelolaan kariermu.

                </p>

                <div class="auth-benefit">

                    <div>
                        <i class="bi bi-shield-check"></i>
                        Secure Authentication
                    </div>

                    <div>
                        <i class="bi bi-person-check"></i>
                        Personal Account Access
                    </div>

                    <div>
                        <i class="bi bi-briefcase"></i>
                        Recruitment Management
                    </div>

                </div>

            </div>


            <!-- LOGIN FORM -->

            <div class="auth-form-container">

                <div class="auth-form-header">

                    <h2>Login Account</h2>

                    <p>
                        Masukkan email dan password akunmu.
                    </p>

                </div>


                <?php if ($flash): ?>

                    <div class="alert alert-<?= htmlspecialchars(
                        $flash['type'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?> alert-dismissible fade show"
                         role="alert">

                        <?= htmlspecialchars(
                            $flash['message'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                        ></button>

                    </div>

                <?php endif; ?>


                <form action="<?= BASE_URL ?>/auth.php"
                      method="POST">

                    <input
                        type="hidden"
                        name="action"
                        value="login"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >


                    <!-- EMAIL -->

                    <div class="mb-3">

                        <label class="form-label">
                            Email Address
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control auth-input"
                            placeholder="example@email.com"
                            value="<?= htmlspecialchars(
                                $rememberedEmail,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            maxlength="190"
                            autocomplete="email"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="mb-3">

                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control auth-input"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                    </div>


                    <!-- REMEMBER EMAIL -->

                    <div class="form-check mb-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember_email"
                            value="1"
                            id="remember_email"
                            <?= $rememberedEmail !== ''
                                ? 'checked'
                                : '' ?>
                        >

                        <label
                            class="form-check-label"
                            for="remember_email"
                        >

                            Remember my email

                        </label>

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="btn btn-primary-custom w-100"
                    >

                        Login

                        <i class="bi bi-arrow-right"></i>

                    </button>

                </form>


                <div class="auth-bottom">

                    Don't have an account?

                    <a href="index.php?page=register">
                        Register here
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>