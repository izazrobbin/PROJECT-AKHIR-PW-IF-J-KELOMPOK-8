<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_hr();

$stmt = $pdo->query("
    SELECT
        j.id,
        j.title,
        j.location_type,
        j.location_name,
        j.employment_type,
        j.status,
        j.deadline,
        j.created_at,
        d.name AS department_name,
        (
            SELECT COUNT(*)
            FROM applications a
            WHERE a.job_id = j.id
        ) AS total_applications
    FROM jobs j
    JOIN departments d ON d.id = j.department_id
    ORDER BY j.created_at DESC
");

$jobs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manajemen Lowongan</h2>
            <p class="text-muted mb-0">
                Kelola seluruh informasi lowongan pekerjaan ARTANA.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/hr/dashboard.php" class="btn btn-outline-secondary">
                Kembali
            </a>

            <a href="<?= BASE_URL ?>/hr/job_create.php" class="btn btn-primary">
                + Tambah Lowongan
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Posisi</th>
                            <th>Departemen</th>
                            <th>Lokasi</th>
                            <th>Tipe</th>
                            <th>Lamaran</th>
                            <th>Status</th>
                            <th>Batas Lamaran</th>
                            <th>Aksi</th>
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
                                <td><?= $index + 1 ?></td>

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
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($job['location_type']) ?>
                                    </small>
                                </td>

                                <td>
                                    <?= htmlspecialchars($job['employment_type']) ?>
                                </td>

                                <td class="text-center">
                                    <?= (int) $job['total_applications'] ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <?= htmlspecialchars($job['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $job['deadline']
                                        ? htmlspecialchars($job['deadline'])
                                        : '-' ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-2 flex-wrap">

                                        <a
                                            href="<?= BASE_URL ?>/hr/job_edit.php?id=<?= (int) $job['id'] ?>"
                                            class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <?php if ($job['status'] !== 'archived'): ?>
                                            <form
                                                action="<?= BASE_URL ?>/hr/job_delete.php"
                                                method="POST"
                                                onsubmit="return confirm('Arsipkan lowongan ini?');">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(csrf_token()) ?>">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $job['id'] ?>">

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger">
                                                    Arsipkan
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Belum ada lowongan pekerjaan.
                            </td>
                        </tr>

                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>