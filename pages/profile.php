<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$user = current_user();

$stmt = $pdo->prepare("
    SELECT
        u.full_name,
        u.email,
        cp.phone,
        cp.portfolio_url,
        cp.resume_path
    FROM users u
    LEFT JOIN candidate_profiles cp ON cp.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->execute([(int) $user['id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    $profile = [
        'full_name' => $user['name'] ?? '',
        'email' => $user['email'] ?? '',
        'phone' => '',
        'portfolio_url' => '',
        'resume_path' => null
    ];
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0">Profil Saya</h4>
                </div>

                <div class="card-body p-4">

                    <form
                        action="<?= BASE_URL ?>/save_profile.php"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(csrf_token()) ?>"
                        >

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                class="form-control"
                                value="<?= htmlspecialchars($profile['email'] ?? '') ?>"
                                disabled
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?= htmlspecialchars($profile['phone'] ?? '') ?>"
                                placeholder="Contoh: 081234567890"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link Portfolio</label>
                            <input
                                type="url"
                                name="portfolio_url"
                                class="form-control"
                                value="<?= htmlspecialchars($profile['portfolio_url'] ?? '') ?>"
                                placeholder="https://contoh.com"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload CV (PDF)</label>
                            <input
                                type="file"
                                name="resume"
                                class="form-control"
                                accept=".pdf,application/pdf"
                            >
                            <small class="text-muted">
                                Format PDF, maksimal 2 MB.
                            </small>
                        </div>

                        <?php if (!empty($profile['resume_path'])): ?>
                            <div class="alert alert-success">
                                <strong>CV sudah tersimpan.</strong>
                                <br>
                                Silakan unggah file baru jika ingin menggantinya.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                Kamu belum mengunggah CV.
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">
                            Simpan Profil
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
