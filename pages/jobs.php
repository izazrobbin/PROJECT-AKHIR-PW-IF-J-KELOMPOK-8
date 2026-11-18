
<?php

require_once __DIR__ . '/../includes/db.php';

// Ambil keyword pencarian.
$keyword = trim($_GET['q'] ?? '');

// Ambil filter departemen.
$departmentId = filter_input(
    INPUT_GET,
    'department',
    FILTER_VALIDATE_INT
);

$departmentId = $departmentId ?: 0;

// Ambil daftar departemen.
$departmentQuery = $pdo->query("
    SELECT id, name
    FROM departments
    ORDER BY name ASC
");

$departments = $departmentQuery->fetchAll(PDO::FETCH_ASSOC);

// Query dasar lowongan.
$sql = "
    SELECT
        j.*,
        d.name AS department_name
    FROM jobs j
    INNER JOIN departments d
        ON d.id = j.department_id
    WHERE j.status = 'published'
    AND (j.deadline IS NULL OR j.deadline >= CURDATE())
";

$params = [];

// Pencarian berdasarkan judul, deskripsi, dan departemen.
if ($keyword !== '') {

    $sql .= "
        AND (
            j.title LIKE ?
            OR j.description LIKE ?
            OR d.name LIKE ?
            OR j.location_name LIKE ?
        )
    ";

    $search = '%' . $keyword . '%';

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// Filter departemen.
if ($departmentId > 0) {

    $sql .= " AND j.department_id = ? ";

    $params[] = $departmentId;
}

// Urutan lowongan terbaru.
$sql .= "
    ORDER BY
        j.published_at DESC,
        j.created_at DESC
";

// Jalankan query.
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container py-5">

    <div class="mb-4">

        <h1 class="fw-bold">
            Cari Lowongan Pekerjaan
        </h1>

        <p class="text-muted">
            Temukan kesempatan karier yang sesuai dengan kemampuanmu.
        </p>

    </div>

    <!-- FORM PENCARIAN -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET"
                  action="<?= BASE_URL ?>/index.php">

                <input type="hidden"
                       name="page"
                       value="jobs">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Kata Kunci
                        </label>

                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            placeholder="Cari posisi pekerjaan..."
                            value="<?= htmlspecialchars($keyword) ?>"
                        >

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">
                            Departemen
                        </label>

                        <select name="department"
                                class="form-select">

                            <option value="0">
                                Semua Departemen
                            </option>

                            <?php foreach ($departments as $department): ?>

                                <option
                                    value="<?= (int) $department['id'] ?>"
                                    <?= $departmentId === (int) $department['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($department['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-2 d-flex align-items-end">

                        <button type="submit"
                                class="btn btn-primary w-100">

                            Cari

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- DAFTAR LOWONGAN -->

    <div class="d-flex justify-content-between mb-3">

        <h4 class="fw-bold">
            Daftar Lowongan
        </h4>

        <span class="text-muted">
            <?= count($jobs) ?> lowongan ditemukan
        </span>

    </div>

    <div class="row g-4">

        <?php if (count($jobs) > 0): ?>

            <?php foreach ($jobs as $job): ?>

                <div class="col-md-6 col-lg-4">

                    <div class="card h-100 shadow-sm border-0">

                        <div class="card-body">

                            <span class="badge bg-primary mb-3">
                                <?= htmlspecialchars($job['department_name']) ?>
                            </span>

                            <h5 class="fw-bold">

                                <?= htmlspecialchars($job['title']) ?>

                            </h5>

                            <p class="text-muted mb-2">

                                <?= htmlspecialchars($job['location_name']) ?>

                            </p>

                            <p class="small text-muted">

                                <?= htmlspecialchars($job['location_type']) ?>
                                ·
                                <?= htmlspecialchars($job['employment_type']) ?>

                            </p>

                            <p>
                                <?= htmlspecialchars(
                                    substr(strip_tags($job['description']), 0, 120)
                                ) ?>...
                            </p>

                            <p class="small text-muted">

                                Deadline:

                                <?= $job['deadline']
                                    ? date('d-m-Y', strtotime($job['deadline']))
                                    : 'Tidak ditentukan'
                                ?>

                            </p>

                        </div>

                        <div class="card-footer bg-white border-0 pb-3">

                            <a
                                href="<?= BASE_URL ?>/index.php?page=job_detail&id=<?= (int) $job['id'] ?>"
                                class="btn btn-outline-primary w-100"
                            >

                                Lihat Detail

                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="alert alert-info">

                    Tidak ditemukan lowongan yang sesuai dengan pencarian.

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>
