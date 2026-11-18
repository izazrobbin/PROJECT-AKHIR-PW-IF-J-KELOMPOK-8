<?php

/*
|--------------------------------------------------------------------------
| REGISTER PAGE
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

                <span class="section-label">
                    JOIN ARTANA CORPORATION
                </span>

                <h1>
                    Start Your
                    <span>Career Journey.</span>
                </h1>

                <p>
                    Buat akun ARTANA ATS dan temukan
                    peluang pekerjaan yang sesuai
                    dengan kemampuan serta minatmu.
                </p>

                <div class="auth-benefit">

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        Easy Job Application
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        Manage Your Profile
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        Track Application Status
                    </div>

                </div>

            </div>


            <!-- REGISTER FORM -->

            <div class="auth-form-container">

                <div class="auth-form-header">

                    <h2>Create Account</h2>

                    <p>
                        Daftar sebagai kandidat ARTANA ATS.
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
                        value="register"
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


                    <!-- FULL NAME -->

                    <div class="mb-3">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control auth-input"
                            placeholder="Enter your full name"
                            maxlength="150"
                            autocomplete="name"
                            required
                        >

                    </div>


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
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="mb-3">

                        <label class="form-label">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            name="confirm_password"
                            class="form-control auth-input"
                            placeholder="Repeat your password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                    </div>


                    <!-- TERMS -->

                    <div class="form-check mb-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="terms"
                            value="1"
                            id="terms"
                            required
                        >

                        <label
                            class="form-check-label"
                            for="terms"
                        >

                            I agree to create an ARTANA ATS
                            candidate account.

                        </label>

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="btn btn-primary-custom w-100"
                    >

                        Create Account

                        <i class="bi bi-arrow-right"></i>

                    </button>

                </form>


                <div class="auth-bottom">

                    Already have an account?

                    <a href="index.php?page=login">
                        Login here
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>