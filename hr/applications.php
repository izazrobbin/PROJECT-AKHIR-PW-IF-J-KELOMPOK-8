```php
<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_hr();

$applicationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$applicationId || $applicationId < 1) {
    set_flash('danger', 'ID lamaran tidak valid.');
    redirect(BASE_URL . '/hr/applications.php');
}

$stmt = $pdo->prepare("
    SELECT
        a.*,
        j.title AS job_title,
        j.location_name,
        j.employment_type,
        d.name AS department_name
    FROM applications a
    INNER JOIN jobs j ON j.id = a.job_id
    LEFT JOIN departments d ON d.id = j.department_id
    WHERE a.id = ?
    LIMIT 1
");
$stmt->execute([$applicationId]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    set_flash('danger', 'Data lamaran tidak ditemukan.');
    redirect(BASE_URL . '/hr/applications.php');
}

$historyStmt = $pdo->prepare("
    SELECT
        h.old_status,
        h.new_status,
        h.note,
        h.changed_at,
        u.full_name AS changed_by_name
    FROM application_status_history h
    LEFT JOIN users u ON u.id = h.changed_by
    WHERE h.application_id = ?
    ORDER BY h.changed_at DESC
");
$historyStmt->execute([$applicationId]);
$history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

$statuses = ['Reviewing', 'Interview', 'Offered', 'Rejected'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Detail Lamaran</h3>
            <p class="text-muted mb-0">
                Kode Lamaran:
                <?= htmlspecialchars($application['application_code']) ?>
            </p>
        </div>

        <a href="<?= BASE_URL ?>/hr/applications.php"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    <div class="row g-4">

        <div class="col-lg-7">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Kandidat</h5>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <strong>Nama:</strong><br>
                        <?= htmlspecialchars($application['candidate_name_snapshot'] ?? '-') ?>
                    </div>

                    <div class="mb-3">
                        <strong>Email:</strong><br>
                        <?= htmlspecialchars($application['email_snapshot'] ?? '-') ?>
                    </div>

                    <div class="mb-3">
                        <strong>Nomor Telepon:</strong><br>
                        <?= htmlspecialchars($application['phone_snapshot'] ?? '-') ?>
                    </div>

                    <div class="mb-3">
                        <strong>Portfolio:</strong><br>

                        <?php if (!empty($application['portfolio_url_snapshot'])): ?>
                            <a
                                href="<?= htmlspecialchars($application['portfolio_url_snapshot']) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Lihat Portfolio
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Belum tersedia</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($application['resume_path'])): ?>
                        <div class="mb-3">
                            <strong>CV Kandidat:</strong><br>

                            <a
                                href="<?= BASE_URL ?>/download_cv.php?id=<?= (int) $application['id'] ?>"
                                class="btn btn-primary btn-sm mt-2"
                            >
                                Download CV (PDF)
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Kandidat belum memiliki CV pada lamaran ini.
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <strong>Pengalaman / Catatan Kandidat:</strong><br>
                        <?= nl2br(htmlspecialchars($application['experience_notes'] ?? '-')) ?>
                    </div>

                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Lowongan</h5>
                </div>

                <div class="card-body">

                    <div class="mb-2">
                        <strong>Posisi:</strong>
                        <?= htmlspecialchars($application['job_title']) ?>
                    </div>

                    <div class="mb-2">
                        <strong>Departemen:</strong>
                        <?= htmlspecialchars($application['department_name'] ?? '-') ?>
                    </div>

                    <div class="mb-2">
                        <strong>Lokasi:</strong>
                        <?= htmlspecialchars($application['location_name'] ?? '-') ?>
                    </div>

                    <div class="mb-2">
                        <strong>Tipe Pekerjaan:</strong>
                        <?= htmlspecialchars($application['employment_type'] ?? '-') ?>
                    </div>

                    <div class="mb-2">
                        <strong>Tanggal Melamar:</strong>
                        <?= htmlspecialchars($application['applied_at']) ?>
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-5">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Perbarui Status Lamaran</h5>
                </div>

                <div class="card-body">

                    <p>
                        Status saat ini:
                        <strong>
                            <?= htmlspecialchars($application['status']) ?>
                        </strong>
                    </p>

                    <form
                        action="<?= BASE_URL ?>/hr/application_update.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(csrf_token()) ?>"
                        >

                        <input
                            type="hidden"
                            name="application_id"
                            value="<?= (int) $application['id'] ?>"
                        >

                        <div class="mb-3">
                            <label class="form-label">Status Baru</label>

                            <select name="status" class="form-select" required>
                                <?php foreach ($statuses as $status): ?>
                                    <option
                                        value="<?= htmlspecialchars($status) ?>"
                                        <?= $application['status'] === $status ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan HR</label>

                            <textarea
                                name="hr_notes"
                                class="form-control"
                                rows="4"
                                placeholder="Tulis catatan untuk proses seleksi..."
                            ><?= htmlspecialchars($application['hr_notes'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Simpan Perubahan
                        </button>

                    </form>

                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Riwayat Status</h5>
                </div>

                <div class="card-body">

                    <?php if (empty($history)): ?>
                        <p class="text-muted mb-0">
                            Belum ada riwayat perubahan status.
                        </p>
                    <?php else: ?>

                        <?php foreach ($history as $item): ?>
                            <div class="border-bottom pb-3 mb-3">

                                <strong>
                                    <?= htmlspecialchars($item['old_status'] ?? 'Baru') ?>
                                    →
                                    <?= htmlspecialchars($item['new_status']) ?>
                                </strong>

                                <div class="small text-muted mt-1">
                                    <?= htmlspecialchars($item['changed_at']) ?>
                                </div>

                                <div class="small">
                                    Oleh:
                                    <?= htmlspecialchars($item['changed_by_name'] ?? 'HR') ?>
                                </div>

                                <?php if (!empty($item['note'])): ?>
                                    <p class="mb-0 mt-2">
                                        <?= nl2br(htmlspecialchars($item['note'])) ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```