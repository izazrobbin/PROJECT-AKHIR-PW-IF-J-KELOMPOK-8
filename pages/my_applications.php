
<?php

require_once __DIR__ . '/../includes/auth_check.php';

require_candidate();

require_once __DIR__ . '/../includes/db.php';

$user = current_user();

// Ambil riwayat lamaran Kandidat.
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.application_code,
        a.status,
        a.applied_at,
        j.title,
        j.location_name,
        j.employment_type,
        d.name AS department_name
    FROM applications a
    INNER JOIN jobs j
        ON j.id = a.job_id
    INNER JOIN departments d
        ON d.id = j.department_id
    WHERE a.candidate_id = ?
    ORDER BY a.applied_at DESC
");

$stmt->execute([$user['id']]);

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container py-5">

    <h2 class="fw-bold">
        Lamaran Saya
    </h2>

    <p class="text-muted">
        Pantau perkembangan proses rekrutmen dari setiap lamaran.
    </p>

    <div class="card shadow-sm border-0 mt-4">

        <div class="card-body">

            <?php if (count($applications) > 0): ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>
                                <th>Kode Lamaran</th>
                                <th>Posisi</th>
                                <th>Departemen</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($applications as $application): ?>

                                <?php

                                $statusClass = match ($application['status']) {
                                    'Reviewing' => 'warning',
                                    'Interview' => 'info',
                                    'Offered' => 'success',
                                    'Rejected' => 'danger',
                                    default => 'secondary'
                                };

                                ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars($application['application_code']) ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?= htmlspecialchars($application['title']) ?>
                                        </strong>

                                        <br>

                                        <small class="text-muted">

                                            <?= htmlspecialchars($application['location_name']) ?>

                                        </small>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($application['department_name']) ?>

                                    </td>

                                    <td>

                                        <?= date(
                                            'd-m-Y',
                                            strtotime($application['applied_at'])
                                        ) ?>

                                    </td>

                                    <td>

                                        <span class="badge bg-<?= $statusClass ?>">

                                            <?= htmlspecialchars($application['status']) ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <h5>
                        Belum Ada Lamaran
                    </h5>

                    <p class="text-muted">
                        Kamu belum mengirimkan lamaran pekerjaan.
                    </p>

                    <a
                        href="<?= BASE_URL ?>/index.php?page=jobs"
                        class="btn btn-primary"
                    >

                        Cari Lowongan

                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>
