<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_hr();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID lamaran tidak valid.');
    header('Location: ' . BASE_URL . '/hr/applications.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        a.*,
        j.title AS job_title,
        j.location_name,
        j.employment_type,
        d.name AS department_name
    FROM applications a
    JOIN jobs j ON j.id = a.job_id
    JOIN departments d ON d.id = j.department_id
    WHERE a.id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$application = $stmt->fetch();

if (!$application) {
    set_flash('danger', 'Data lamaran tidak ditemukan.');
    header('Location: ' . BASE_URL . '/hr/applications.php');
    exit;
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

$historyStmt->execute([$id]);
$histories = $historyStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Detail Lamaran</h2>
            <p class="text-muted mb-0">
                Kode: <?= htmlspecialchars($application['application_code']) ?>
            </p>
        </div>

        <a href="<?= BASE_URL ?>/hr/applications.php"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    <div class="row g-4">

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Informasi Kandidat</h5>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <small class="text-muted">Nama Kandidat</small>
                        <h5 class="fw-bold">
                            <?= htmlspecialchars($application['candidate_name_snapshot']) ?>
                        </h5>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Email</small>
                        <p>
                            <?= htmlspecialchars($application['email_snapshot']) ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Nomor Telepon</small>
                        <p>
                            <?= htmlspecialchars($application['phone_snapshot'] ?? '-') ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Portfolio</small>
                        <div class="mb-4">
    <label class="form-label">CV / Resume Kandidat</label>

    <?php if (!empty($application['resume_path'])): ?>
        <div class="d-flex gap-2 flex-wrap">
            <a
                href="<?= BASE_URL ?>/download_cv.php?id=<?= (int) $application['id'] ?>"
                class="btn btn-primary"
            >
                Lihat / Download CV
            </a>
        </div>
    <?php else: ?>
        <p class="text-danger mb-0">
            Kandidat belum mengunggah CV.
        </p>
    <?php endif; ?>
</div>
                    </div>

                    <div>
                        <small class="text-muted">Pengalaman / Catatan Kandidat</small>
                        <div class="border rounded p-3 mt-2">
                            <?= nl2br(htmlspecialchars($application['experience_notes'] ?? '-')) ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Riwayat Status Lamaran</h5>
                </div>

                <div class="card-body">

                    <?php if ($histories): ?>

                        <?php foreach ($histories as $history): ?>
                            <div class="border-start border-3 ps-3 mb-4">

                                <strong>
                                    <?= htmlspecialchars($history['old_status'] ?? 'Baru') ?>
                                    →
                                    <?= htmlspecialchars($history['new_status']) ?>
                                </strong>

                                <p class="mb-1">
                                    <?= htmlspecialchars($history['note'] ?? '-') ?>
                                </p>

                                <small class="text-muted">
                                    Oleh:
                                    <?= htmlspecialchars($history['changed_by_name'] ?? 'HR') ?>
                                    |
                                    <?= htmlspecialchars($history['changed_at']) ?>
                                </small>

                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <p class="text-muted mb-0">
                            Belum ada riwayat perubahan status.
                        </p>

                    <?php endif; ?>

                </div>
            </div>

        </div>

        <div class="col-lg-5">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Informasi Lowongan</h5>
                </div>

                <div class="card-body">

                    <h5 class="fw-bold">
                        <?= htmlspecialchars($application['job_title']) ?>
                    </h5>

                    <p class="text-muted mb-2">
                        <?= htmlspecialchars($application['department_name']) ?>
                    </p>

                    <p class="mb-2">
                        <strong>Lokasi:</strong>
                        <?= htmlspecialchars($application['location_name']) ?>
                    </p>

                    <p class="mb-2">
                        <strong>Tipe:</strong>
                        <?= htmlspecialchars($application['employment_type']) ?>
                    </p>

                    <p class="mb-2">
                        <strong>Tanggal Melamar:</strong>
                        <?= htmlspecialchars($application['applied_at']) ?>
                    </p>

                    <p class="mb-0">
                        <strong>Status:</strong>
                        <?= htmlspecialchars($application['status']) ?>
                    </p>

                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Perbarui Status Lamaran</h5>
                </div>

                <div class="card-body">

                    <form
                        action="<?= BASE_URL ?>/hr/application_update.php"
                        method="POST">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars(csrf_token()) ?>">

                        <input type="hidden" name="id"
                               value="<?= (int) $application['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Status Lamaran
                            </label>

                            <select name="status" class="form-select" required>
                                <?php foreach (['Reviewing', 'Interview', 'Offered', 'Rejected'] as $status): ?>
                                    <option
                                        value="<?= $status ?>"
                                        <?= $application['status'] === $status ? 'selected' : '' ?>>
                                        <?= $status ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Catatan HR
                            </label>

                            <textarea
                                name="hr_notes"
                                class="form-control"
                                rows="5"
                                placeholder="Masukkan catatan hasil peninjauan..."><?= htmlspecialchars($application['hr_notes'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Simpan Perubahan
                        </button>

                    </form>

                </div>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>