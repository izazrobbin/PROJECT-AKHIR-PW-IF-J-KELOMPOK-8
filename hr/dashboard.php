<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

// Hanya akun HR yang boleh membuka dashboard.
require_hr();

$user = current_user();

// ==========================================
// STATISTIK DASHBOARD
// ==========================================

// Total seluruh lowongan
$totalJobs = (int) $pdo->query("
    SELECT COUNT(*)
    FROM jobs
")->fetchColumn();

// Total lowongan aktif
$activeJobs = (int) $pdo->query("
    SELECT COUNT(*)
    FROM jobs
    WHERE status = 'published'
")->fetchColumn();

// Total seluruh lamaran
$totalApplications = (int) $pdo->query("
    SELECT COUNT(*)
    FROM applications
")->fetchColumn();

// Lamaran yang masih perlu ditinjau
$reviewingApplications = (int) $pdo->query("
    SELECT COUNT(*)
    FROM applications
    WHERE status = 'Reviewing'
")->fetchColumn();

// Total lamaran tahap interview
$interviewApplications = (int) $pdo->query("
    SELECT COUNT(*)
    FROM applications
    WHERE status = 'Interview'
")->fetchColumn();

// ==========================================
// 10 LOWONGAN TERBARU
// ==========================================

$stmt = $pdo->query("
    SELECT
        j.id,
        j.title,
        j.status,
        j.location_name,
        j.created_at,
        d.name AS department_name
    FROM jobs j
    JOIN departments d ON d.id = j.department_id
    ORDER BY j.created_at DESC
    LIMIT 10
");

$jobs = $stmt->fetchAll();

// ==========================================
// 10 LAMARAN TERBARU
// ==========================================

$stmt = $pdo->query("
    SELECT
        a.id,
        a.application_code,
        a.candidate_name_snapshot,
        a.status,
        a.applied_at,
        j.title AS job_title
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    ORDER BY a.applied_at DESC
    LIMIT 10
");

$applications = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <!-- HEADER DASHBOARD -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h2 class="fw-bold mb-1">Dashboard HR</h2>

            <p class="text-muted mb-0">
                Selamat datang,
                <?= htmlspecialchars($user['name'] ?? 'Admin ARTANA') ?>.
            </p>
        </div>

        <span class="badge bg-dark p-2">
            ADMIN ARTANA
        </span>

    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <p class="text-muted mb-1">
                        Total Lowongan
                    </p>

                    <h2 class="fw-bold">
                        <?= $totalJobs ?>
                    </h2>

                    <small class="text-muted">
                        Seluruh lowongan pekerjaan
                    </small>

                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <p class="text-muted mb-1">
                        Lowongan Aktif
                    </p>

                    <h2 class="fw-bold text-success">
                        <?= $activeJobs ?>
                    </h2>

                    <small class="text-muted">
                        Lowongan berstatus published
                    </small>

                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <p class="text-muted mb-1">
                        Total Lamaran
                    </p>

                    <h2 class="fw-bold text-primary">
                        <?= $totalApplications ?>
                    </h2>

                    <small class="text-muted">
                        Seluruh lamaran kandidat
                    </small>

                </div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <p class="text-muted mb-1">
                        Perlu Ditinjau
                    </p>

                    <h2 class="fw-bold text-warning">
                        <?= $reviewingApplications ?>
                    </h2>

                    <small class="text-muted">
                        Lamaran berstatus Reviewing
                    </small>

                </div>
            </div>
        </div>

    </div>

    <!-- MENU PENGELOLAAN -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-2">
                Menu Pengelolaan
            </h5>

            <p class="text-muted">
                Kelola lowongan pekerjaan dan proses seleksi kandidat ARTANA Corporation.
            </p>

            <div class="d-flex flex-wrap gap-2">

                <a href="<?= BASE_URL ?>/hr/jobs.php"
                   class="btn btn-primary">

                    <i class="bi bi-briefcase"></i>
                    Kelola Lowongan

                </a>

                <a href="<?= BASE_URL ?>/hr/applications.php"
                   class="btn btn-success">

                    <i class="bi bi-people"></i>
                    Kelola Lamaran

                </a>

            </div>

        </div>

    </div>

    <!-- LOWONGAN TERBARU -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

            <h5 class="fw-bold mb-0">
                10 Lowongan Terbaru
            </h5>

            <a href="<?= BASE_URL ?>/hr/jobs.php"
               class="btn btn-sm btn-outline-primary">

                Lihat Semua

            </a>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>No.</th>
                            <th>Nama Lowongan</th>
                            <th>Departemen</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Tanggal Dibuat</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($jobs): ?>

                        <?php foreach ($jobs as $index => $job): ?>

                            <?php
                            $badgeClass = 'secondary';

                            if ($job['status'] === 'published') {
                                $badgeClass = 'success';
                            } elseif ($job['status'] === 'draft') {
                                $badgeClass = 'warning';
                            } elseif ($job['status'] === 'closed') {
                                $badgeClass = 'danger';
                            } elseif ($job['status'] === 'archived') {
                                $badgeClass = 'dark';
                            }
                            ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($job['title']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($job['department_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($job['location_name']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <?= htmlspecialchars($job['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($job['created_at']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada lowongan pekerjaan.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- LAMARAN TERBARU -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

            <h5 class="fw-bold mb-0">
                10 Lamaran Terbaru
            </h5>

            <a href="<?= BASE_URL ?>/hr/applications.php"
               class="btn btn-sm btn-outline-success">

                Lihat Semua

            </a>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>No.</th>
                            <th>Kode Lamaran</th>
                            <th>Nama Kandidat</th>
                            <th>Posisi Dilamar</th>
                            <th>Status</th>
                            <th>Tanggal Melamar</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($applications): ?>

                        <?php foreach ($applications as $index => $application): ?>

                            <?php
                            $badgeClass = 'warning';

                            if ($application['status'] === 'Interview') {
                                $badgeClass = 'info';
                            } elseif ($application['status'] === 'Offered') {
                                $badgeClass = 'success';
                            } elseif ($application['status'] === 'Rejected') {
                                $badgeClass = 'danger';
                            }
                            ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($application['application_code']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($application['candidate_name_snapshot']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($application['job_title']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <?= htmlspecialchars($application['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($application['applied_at']) ?>
                                </td>

                                <td>

                                    <a
                                        href="<?= BASE_URL ?>/hr/application_detail.php?id=<?= (int) $application['id'] ?>"
                                        class="btn btn-sm btn-primary">

                                        Detail

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada lamaran kandidat.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- RINGKASAN PROSES SELEKSI -->
    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                Ringkasan Proses Seleksi
            </h5>

            <div class="row g-3">

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <p class="text-muted mb-1">
                            Lamaran Menunggu Peninjauan
                        </p>

                        <h3 class="fw-bold text-warning mb-0">
                            <?= $reviewingApplications ?>
                        </h3>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <p class="text-muted mb-1">
                            Lamaran Tahap Interview
                        </p>

                        <h3 class="fw-bold text-info mb-0">
                            <?= $interviewApplications ?>
                        </h3>

                    </div>

                </div>

            </div>

            <div class="mt-3">

                <a href="<?= BASE_URL ?>/hr/applications.php"
                   class="btn btn-outline-primary">

                    Kelola Proses Seleksi

                </a>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>