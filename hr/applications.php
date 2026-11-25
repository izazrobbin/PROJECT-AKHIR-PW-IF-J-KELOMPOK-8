<?php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Pastikan hanya HR yang dapat membuka halaman ini.
require_hr();

// Ambil filter pencarian.
$keyword = trim($_GET['keyword'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$allowedStatuses = [
    'Reviewing',
    'Interview',
    'Offered',
    'Rejected'
];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

try {

    // Query dasar daftar lamaran.
    $sql = "
        SELECT
            a.id,
            a.application_code,
            a.candidate_name_snapshot,
            a.email_snapshot,
            a.status,
            a.applied_at,
            a.resume_path,
            j.title AS job_title,
            d.name AS department_name

        FROM applications a

        INNER JOIN jobs j
            ON j.id = a.job_id

        LEFT JOIN departments d
            ON d.id = j.department_id

        WHERE 1 = 1
    ";

    $params = [];

    // Filter berdasarkan status.
    if ($statusFilter !== '') {
        $sql .= " AND a.status = ?";
        $params[] = $statusFilter;
    }

    // Filter berdasarkan nama, email, kode lamaran, atau lowongan.
    if ($keyword !== '') {
        $sql .= "
            AND (
                a.candidate_name_snapshot LIKE ?
                OR a.email_snapshot LIKE ?
                OR a.application_code LIKE ?
                OR j.title LIKE ?
            )
        ";

        $search = '%' . $keyword . '%';

        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    $sql .= " ORDER BY a.applied_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistik lamaran.
    $statsStmt = $pdo->query("
        SELECT
            COUNT(*) AS total,
            SUM(status = 'Reviewing') AS reviewing,
            SUM(status = 'Interview') AS interview,
            SUM(status = 'Offered') AS offered,
            SUM(status = 'Rejected') AS rejected
        FROM applications
    ");

    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log('HR applications error: ' . $e->getMessage());

    require_once __DIR__ . '/../includes/header.php';
    ?>

    <div class="alert alert-danger">
        <h5>Gagal Memuat Data Lamaran</h5>
        <p class="mb-0">
            Terjadi kesalahan saat mengambil data dari database.
            Periksa struktur tabel applications dan jobs.
        </p>
    </div>

    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container-fluid py-4">

    <!-- Judul halaman -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h2 class="fw-bold mb-1">Kelola Lamaran</h2>

            <p class="text-muted mb-0">
                Kelola dan pantau proses seleksi kandidat ARTANA ATS.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>/hr/dashboard.php"
            class="btn btn-outline-secondary"
        >
            Kembali ke Dashboard
        </a>

    </div>

    <!-- Statistik -->
    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Lamaran</p>

                    <h3 class="fw-bold mb-0">
                        <?= (int) ($stats['total'] ?? 0) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Reviewing</p>

                    <h3 class="fw-bold text-primary mb-0">
                        <?= (int) ($stats['reviewing'] ?? 0) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Interview</p>

                    <h3 class="fw-bold text-info mb-0">
                        <?= (int) ($stats['interview'] ?? 0) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Offered</p>

                    <h3 class="fw-bold text-success mb-0">
                        <?= (int) ($stats['offered'] ?? 0) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Rejected</p>

                    <h3 class="fw-bold text-danger mb-0">
                        <?= (int) ($stats['rejected'] ?? 0) ?>
                    </h3>
                </div>
            </div>
        </div>

    </div>

    <!-- Tabel lamaran -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">
                Daftar Kandidat
            </h5>

        </div>

        <div class="card-body">

            <!-- Form pencarian -->
            <form method="GET" action="" class="row g-2 mb-4">

                <div class="col-md-6">

                    <input
                        type="text"
                        name="keyword"
                        class="form-control"
                        placeholder="Cari nama, email, kode lamaran, atau lowongan..."
                        value="<?= htmlspecialchars($keyword) ?>"
                    >

                </div>

                <div class="col-md-3">

                    <select name="status" class="form-select">

                        <option value="">Semua Status</option>

                        <?php foreach ($allowedStatuses as $status): ?>

                            <option
                                value="<?= htmlspecialchars($status) ?>"
                                <?= $statusFilter === $status ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($status) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-3 d-flex gap-2">

                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>

                    <a
                        href="<?= BASE_URL ?>/hr/applications.php"
                        class="btn btn-outline-secondary"
                    >
                        Reset
                    </a>

                </div>

            </form>

            <!-- Tabel -->
            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>No.</th>
                            <th>Kode Lamaran</th>
                            <th>Nama Kandidat</th>
                            <th>Email</th>
                            <th>Posisi</th>
                            <th>Departemen</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>CV</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($applications)): ?>

                            <tr>
                                <td colspan="10" class="text-center py-5">

                                    <h6 class="text-muted">
                                        Belum ada data lamaran.
                                    </h6>

                                    <p class="text-muted small mb-0">
                                        Lamaran kandidat yang masuk akan ditampilkan di sini.
                                    </p>

                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($applications as $index => $application): ?>

                                <?php

                                $statusClass = 'secondary';

                                if ($application['status'] === 'Reviewing') {
                                    $statusClass = 'primary';
                                } elseif ($application['status'] === 'Interview') {
                                    $statusClass = 'info';
                                } elseif ($application['status'] === 'Offered') {
                                    $statusClass = 'success';
                                } elseif ($application['status'] === 'Rejected') {
                                    $statusClass = 'danger';
                                }

                                ?>

                                <tr>

                                    <td>
                                        <?= $index + 1 ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($application['application_code']) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($application['candidate_name_snapshot'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($application['email_snapshot'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($application['job_title'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($application['department_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($application['applied_at'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-<?= $statusClass ?>">
                                            <?= htmlspecialchars($application['status']) ?>
                                        </span>
                                    </td>

                                    <td>

                                        <?php if (!empty($application['resume_path'])): ?>

                                            <span class="badge text-bg-success">
                                                Tersedia
                                            </span>

                                        <?php else: ?>

                                            <span class="badge text-bg-warning">
                                                Belum ada
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <a
                                            href="<?= BASE_URL ?>/hr/application_detail.php?id=<?= (int) $application['id'] ?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            Detail
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="text-muted small mt-3">
                Total data yang ditampilkan:
                <strong><?= count($applications) ?></strong> lamaran.
            </div>

        </div>

    </div>

</div>

<?php

require_once __DIR__ . '/../includes/footer.php';

?>
```