
<?php

require_once __DIR__ . '/../includes/db.php';

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

?>

<div class="container py-5">

    <?php if (!$job): ?>

        <div class="alert alert-warning">

            <h4>Lowongan Tidak Ditemukan</h4>

            Lowongan mungkin sudah ditutup atau tidak tersedia.

            <a href="<?= BASE_URL ?>/index.php?page=jobs">
                Kembali ke daftar lowongan
            </a>

        </div>

    <?php else: ?>

        <a href="<?= BASE_URL ?>/index.php?page=jobs"
           class="text-decoration-none">

            &larr; Kembali ke Lowongan

        </a>

        <div class="card shadow-sm border-0 mt-4">

            <div class="card-body p-4 p-md-5">

                <span class="badge bg-primary mb-3">

                    <?= htmlspecialchars($job['department_name']) ?>

                </span>

                <h1 class="fw-bold">

                    <?= htmlspecialchars($job['title']) ?>

                </h1>

                <p class="text-muted">

                    <?= htmlspecialchars($job['location_name']) ?>

                    ·

                    <?= htmlspecialchars($job['location_type']) ?>

                    ·

                    <?= htmlspecialchars($job['employment_type']) ?>

                </p>

                <hr>

                <h5 class="fw-bold">
                    Deskripsi Pekerjaan
                </h5>

                <p style="white-space: pre-line">

                    <?= htmlspecialchars($job['description']) ?>

                </p>

                <hr>

                <h5 class="fw-bold">
                    Kualifikasi
                </h5>

                <p style="white-space: pre-line">

                    <?= htmlspecialchars(
                        $job['qualification'] ?? 'Tidak ada informasi kualifikasi.'
                    ) ?>

                </p>

                <hr>

                <p>
                    <strong>Deadline:</strong>

                    <?= $job['deadline']
                        ? date('d-m-Y', strtotime($job['deadline']))
                        : 'Tidak ditentukan'
                    ?>
                </p>

                <p>
                    <strong>Departemen:</strong>

                    <?= htmlspecialchars($job['department_name']) ?>
                </p>

                <a
                    href="<?= BASE_URL ?>/index.php?page=apply&id=<?= (int) $job['id'] ?>"
                    class="btn btn-primary btn-lg mt-3"
                >

                    Lamar Sekarang

                </a>

            </div>

        </div>

    <?php endif; ?>

</div>
