<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_hr();

$user = current_user();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID lowongan tidak valid.');
    header('Location: ' . BASE_URL . '/hr/jobs.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM jobs
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);

$job = $stmt->fetch();

if (!$job) {
    set_flash('danger', 'Lowongan tidak ditemukan.');
    header('Location: ' . BASE_URL . '/hr/jobs.php');
    exit;
}

$departments = $pdo->query("
    SELECT id, name
    FROM departments
    ORDER BY name ASC
")->fetchAll();

$errors = [];

$title = $job['title'];
$department_id = $job['department_id'];
$location_type = $job['location_type'];
$location_name = $job['location_name'];
$description = $job['description'];
$qualification = $job['qualification'];
$employment_type = $job['employment_type'];
$status = $job['status'];
$deadline = $job['deadline'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf()) {
        $errors[] = 'Token keamanan tidak valid. Silakan muat ulang halaman.';
    }

    $title = trim($_POST['title'] ?? '');
    $department_id = (int) ($_POST['department_id'] ?? 0);
    $location_type = $_POST['location_type'] ?? '';
    $location_name = trim($_POST['location_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $employment_type = $_POST['employment_type'] ?? '';
    $status = $_POST['status'] ?? '';
    $deadline = trim($_POST['deadline'] ?? '');

    $validLocationTypes = ['Hybrid', 'Remote', 'On-site'];
    $validEmploymentTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];
    $validStatuses = ['draft', 'published', 'closed', 'archived'];

    if ($title === '') {
        $errors[] = 'Nama lowongan wajib diisi.';
    }

    if ($department_id <= 0) {
        $errors[] = 'Departemen wajib dipilih.';
    }

    if ($location_name === '') {
        $errors[] = 'Lokasi pekerjaan wajib diisi.';
    }

    if ($description === '') {
        $errors[] = 'Deskripsi pekerjaan wajib diisi.';
    }

    if ($qualification === '') {
        $errors[] = 'Kualifikasi wajib diisi.';
    }

    if (!in_array($location_type, $validLocationTypes, true)) {
        $errors[] = 'Tipe lokasi tidak valid.';
    }

    if (!in_array($employment_type, $validEmploymentTypes, true)) {
        $errors[] = 'Tipe pekerjaan tidak valid.';
    }

    if (!in_array($status, $validStatuses, true)) {
        $errors[] = 'Status lowongan tidak valid.';
    }

    if ($deadline !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $deadline);

        if (!$date || $date->format('Y-m-d') !== $deadline) {
            $errors[] = 'Tanggal batas lamaran tidak valid.';
        }
    }

    $departmentCheck = $pdo->prepare("
        SELECT COUNT(*)
        FROM departments
        WHERE id = ?
    ");
    $departmentCheck->execute([$department_id]);

    if ((int) $departmentCheck->fetchColumn() === 0) {
        $errors[] = 'Departemen tidak ditemukan.';
    }

    if (empty($errors)) {

        $publishedAt = $job['published_at'];

        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $update = $pdo->prepare("
            UPDATE jobs
            SET
                title = :title,
                department_id = :department_id,
                location_type = :location_type,
                location_name = :location_name,
                description = :description,
                qualification = :qualification,
                employment_type = :employment_type,
                status = :status,
                published_at = :published_at,
                deadline = :deadline,
                updated_by = :updated_by
            WHERE id = :id
        ");

        $update->execute([
            ':title' => $title,
            ':department_id' => $department_id,
            ':location_type' => $location_type,
            ':location_name' => $location_name,
            ':description' => $description,
            ':qualification' => $qualification,
            ':employment_type' => $employment_type,
            ':status' => $status,
            ':published_at' => $publishedAt,
            ':deadline' => $deadline !== '' ? $deadline : null,
            ':updated_by' => $user['id'],
            ':id' => $id
        ]);

        set_flash('success', 'Informasi lowongan berhasil diperbarui.');

        header('Location: ' . BASE_URL . '/hr/jobs.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <div class="mb-4">
        <h2 class="fw-bold">Edit Lowongan</h2>
        <p class="text-muted">
            Perbarui informasi lowongan pekerjaan.
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form method="POST">

                <input type="hidden" name="id" value="<?= $id ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(csrf_token()) ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nama Posisi
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="<?= htmlspecialchars($title) ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Departemen
                    </label>

                    <select name="department_id" class="form-select" required>
                        <?php foreach ($departments as $department): ?>
                            <option
                                value="<?= (int) $department['id'] ?>"
                                <?= (string) $department_id === (string) $department['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($department['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Tipe Lokasi
                        </label>

                        <select name="location_type" class="form-select">
                            <?php foreach (['On-site', 'Hybrid', 'Remote'] as $type): ?>
                                <option
                                    value="<?= $type ?>"
                                    <?= $location_type === $type ? 'selected' : '' ?>>
                                    <?= $type ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Nama Lokasi
                        </label>

                        <input
                            type="text"
                            name="location_name"
                            class="form-control"
                            value="<?= htmlspecialchars($location_name) ?>"
                            required>
                    </div>

                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Deskripsi Pekerjaan
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="5"
                        required><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Kualifikasi
                    </label>

                    <textarea
                        name="qualification"
                        class="form-control"
                        rows="5"
                        required><?= htmlspecialchars($qualification) ?></textarea>
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Tipe Pekerjaan
                        </label>

                        <select name="employment_type" class="form-select">
                            <?php foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $type): ?>
                                <option
                                    value="<?= $type ?>"
                                    <?= $employment_type === $type ? 'selected' : '' ?>>
                                    <?= $type ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Status Lowongan
                        </label>

                        <select name="status" class="form-select">
                            <?php foreach (['draft', 'published', 'closed', 'archived'] as $value): ?>
                                <option
                                    value="<?= $value ?>"
                                    <?= $status === $value ? 'selected' : '' ?>>
                                    <?= ucfirst($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Batas Lamaran
                    </label>

                    <input
                        type="date"
                        name="deadline"
                        class="form-control"
                        value="<?= htmlspecialchars($deadline) ?>">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>

                    <a href="<?= BASE_URL ?>/hr/jobs.php"
                       class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>