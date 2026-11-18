
<?php

require_once __DIR__ . '/../includes/auth_check.php';

require_candidate();

require_once __DIR__ . '/../includes/db.php';

$user = current_user();

$jobId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$job = null;

if ($jobId) {

    $stmt = $pdo->prepare("
        SELECT
            j.*,
            d.name AS department_name
        FROM jobs j
        INNER JOIN departments d
            ON d.id = j.department_id
        WHERE j.id = ?
        AND j.status = 'published'
        AND (
            j.deadline IS NULL
            OR j.deadline >= CURDATE()
        )
        LIMIT 1
    ");

    $stmt->execute([$jobId]);

    $job = $stmt->fetch(PDO::FETCH_ASSOC);

}

// Ambil profil Kandidat.
$stmt = $pdo->prepare("
    SELECT
        u.full_name,
        u.email,
        cp.phone,
        cp.portfolio_url
    FROM users u
    LEFT JOIN candidate_profiles cp
        ON cp.user_id = u.id
    WHERE u.id = ?
");

$stmt->execute([$user['id']]);

$profile = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<div class="container py-5">

    <?php if (!$job): ?>

        <div class="alert alert-warning">

            Lowongan tidak ditemukan atau sudah ditutup.

            <a href="<?= BASE_URL ?>/index.php?page=jobs">
                Kembali ke lowongan
            </a>

        </div>

    <?php else: ?>

        <div class="card shadow-sm border-0">

            <div class="card-body p-4 p-md-5">

                <h2 class="fw-bold">
                    Formulir Lamaran
                </h2>

                <p class="text-muted">
                    Silakan periksa data sebelum mengirimkan lamaran.
                </p>

                <hr>

                <h5 class="fw-bold">
                    <?= htmlspecialchars($job['title']) ?>
                </h5>

                <p class="text-muted">
                    <?= htmlspecialchars($job['department_name']) ?>
                    ·
                    <?= htmlspecialchars($job['location_name']) ?>
                </p>

                <hr>

                <form
                    action="<?= BASE_URL ?>/process_application.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(csrf_token()) ?>"
                    >

                    <input
                        type="hidden"
                        name="job_id"
                        value="<?= (int) $job['id'] ?>"
                    >

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($profile['full_name']) ?>"
                            disabled
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            value="<?= htmlspecialchars($profile['email']) ?>"
                            disabled
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Nomor Telepon
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($profile['phone'] ?? '-') ?>"
                            disabled
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Portfolio
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($profile['portfolio_url'] ?? '-') ?>"
                            disabled
                        >

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Pengalaman atau Motivasi Melamar
                        </label>

                        <textarea
                            name="experience_notes"
                            class="form-control"
                            rows="5"
                            maxlength="5000"
                            placeholder="Ceritakan pengalaman atau alasan kamu tertarik dengan posisi ini..."
                        ></textarea>

                    </div>

                    <div class="alert alert-info">

                        Pastikan profil Kandidat sudah dilengkapi sebelum mengirimkan lamaran.

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg"
                    >

                        Kirim Lamaran

                    </button>

                    <a
                        href="<?= BASE_URL ?>/index.php?page=job_detail&id=<?= (int) $job['id'] ?>"
                        class="btn btn-secondary btn-lg"
                    >

                        Kembali

                    </a>

                </form>

            </div>

        </div>

    <?php endif; ?>

</div>
