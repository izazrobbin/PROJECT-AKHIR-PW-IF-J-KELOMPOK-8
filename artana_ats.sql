-- DATABASE ATS REKRUTMEN ARTANA CORPORATION
-- MySQL 8.0+ / XAMPP (InnoDB, utf8mb4)
-- Jalankan melalui phpMyAdmin > Import.
-- Catatan keamanan: akun HR dibuat oleh administrator melalui proses seed/manual.
-- Jangan pernah menyediakan pilihan role HR pada form registrasi publik.

CREATE DATABASE IF NOT EXISTS artana_ats
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE artana_ats;

-- 1. Akun login. Role hanya candidate atau hr.
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('candidate','hr') NOT NULL DEFAULT 'candidate',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  email_verified_at DATETIME NULL,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role_active (role, is_active)
) ENGINE=InnoDB;

-- 2. Profil kandidat. Data kontak dan tautan CV pada halaman profil.
CREATE TABLE candidate_profiles (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  phone VARCHAR(30) NULL,
  portfolio_url VARCHAR(2048) NULL,
  resume_path VARCHAR(500) NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_profile_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Departemen untuk filter lowongan.
CREATE TABLE departments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO departments(name) VALUES
 ('Engineering'), ('Product'), ('Marketing'), ('Operations');

-- 4. Lowongan kerja yang dikelola HR.
CREATE TABLE jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  department_id BIGINT UNSIGNED NOT NULL,
  location_type ENUM('Hybrid','Remote','On-site') NOT NULL DEFAULT 'Hybrid',
  location_name VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  qualification TEXT NULL,
  employment_type ENUM('Full-time','Part-time','Contract','Internship') NOT NULL DEFAULT 'Full-time',
  status ENUM('draft','published','closed','archived') NOT NULL DEFAULT 'published',
  published_at DATETIME NULL,
  deadline DATE NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_jobs_department FOREIGN KEY (department_id)
    REFERENCES departments(id),
  CONSTRAINT fk_jobs_creator FOREIGN KEY (created_by)
    REFERENCES users(id),
  CONSTRAINT fk_jobs_updater FOREIGN KEY (updated_by)
    REFERENCES users(id),
  INDEX idx_jobs_public (status, published_at),
  INDEX idx_jobs_department (department_id),
  INDEX idx_jobs_title (title)
) ENGINE=InnoDB;

-- 5. Lamaran. Satu kandidat hanya dapat melamar satu lowongan satu kali.
CREATE TABLE applications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_code VARCHAR(30) NOT NULL UNIQUE,
  job_id BIGINT UNSIGNED NOT NULL,
  candidate_id BIGINT UNSIGNED NOT NULL,
  candidate_name_snapshot VARCHAR(150) NOT NULL,
  email_snapshot VARCHAR(190) NOT NULL,
  phone_snapshot VARCHAR(30) NULL,
  portfolio_url_snapshot VARCHAR(2048) NULL,
  resume_path VARCHAR(500) NULL,
  experience_notes TEXT NULL,
  status ENUM('Reviewing','Interview','Offered','Rejected') NOT NULL DEFAULT 'Reviewing',
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  hr_notes TEXT NULL,
  CONSTRAINT fk_app_job FOREIGN KEY (job_id)
    REFERENCES jobs(id) ON DELETE RESTRICT,
  CONSTRAINT fk_app_candidate FOREIGN KEY (candidate_id)
    REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_app_reviewer FOREIGN KEY (reviewed_by)
    REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_candidate_job (candidate_id, job_id),
  INDEX idx_app_job_status (job_id, status),
  INDEX idx_app_candidate_date (candidate_id, applied_at),
  INDEX idx_app_status (status)
) ENGINE=InnoDB;

-- 6. Riwayat perubahan status lamaran untuk jejak seleksi.
CREATE TABLE application_status_history (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  old_status ENUM('Reviewing','Interview','Offered','Rejected') NULL,
  new_status ENUM('Reviewing','Interview','Offered','Rejected') NOT NULL,
  changed_by BIGINT UNSIGNED NOT NULL,
  note TEXT NULL,
  changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_history_application FOREIGN KEY (application_id)
    REFERENCES applications(id) ON DELETE CASCADE,
  CONSTRAINT fk_history_user FOREIGN KEY (changed_by)
    REFERENCES users(id),
  INDEX idx_history_application_date (application_id, changed_at)
) ENGINE=InnoDB;

-- 7. Log aktivitas admin HR (opsional tetapi disarankan).
CREATE TABLE hr_activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  hr_user_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) NULL,
  entity_id BIGINT UNSIGNED NULL,
  details JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_hr FOREIGN KEY (hr_user_id)
    REFERENCES users(id),
  INDEX idx_log_hr_date (hr_user_id, created_at),
  INDEX idx_log_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- 8. Contoh data departemen dan lowongan awal.
-- Ganti created_by dengan ID akun HR yang sudah dibuat.
-- INSERT INTO jobs
-- (title, department_id, location_type, location_name, description, status, published_at, created_by)
-- VALUES
-- ('Senior Fullstack Developer (PHP/JS)', 1, 'Hybrid', 'Yogyakarta',
--  'Membangun arsitektur backend dan antarmuka web menggunakan PHP dan MySQL.',
--  'published', NOW(), 1),
-- ('UI/UX Design Lead', 2, 'Remote', 'Indonesia',
--  'Merancang pengalaman pengguna untuk platform korporat.',
--  'published', NOW(), 1),
-- ('Digital Marketing Strategist', 3, 'On-site', 'Jakarta',
--  'Mengelola kampanye pemasaran digital dan analitik.',
--  'published', NOW(), 1),
-- ('DevOps & System Administrator', 1, 'Hybrid', 'Yogyakarta',
--  'Mengelola server, deployment, keamanan jaringan, dan performa.',
--  'published', NOW(), 1);

-- 9. Contoh query untuk statistik dashboard HR.
-- Total lowongan aktif:
-- SELECT COUNT(*) FROM jobs WHERE status='published';
-- Total lamaran:
-- SELECT COUNT(*) FROM applications;
-- Tahap interview:
-- SELECT COUNT(*) FROM applications WHERE status='Interview';
-- Diterima:
-- SELECT COUNT(*) FROM applications WHERE status='Offered';

-- PENTING:
-- Jangan simpan password plaintext. Gunakan password_hash() dan password_verify() PHP.
-- Jangan percaya role dari form/browser/session localStorage.
-- Setiap endpoint admin wajib memeriksa session login dan role='hr' di SERVER.
-- Jangan menghapus lowongan yang sudah memiliki lamaran; ubah status menjadi closed/archived.
