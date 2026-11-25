-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2026 at 06:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `artana_ats`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_code` varchar(30) NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `candidate_name_snapshot` varchar(150) NOT NULL,
  `email_snapshot` varchar(190) NOT NULL,
  `phone_snapshot` varchar(30) DEFAULT NULL,
  `portfolio_url_snapshot` varchar(2048) DEFAULT NULL,
  `resume_path` varchar(500) DEFAULT NULL,
  `experience_notes` text DEFAULT NULL,
  `status` enum('Reviewing','Interview','Offered','Rejected') NOT NULL DEFAULT 'Reviewing',
  `applied_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `hr_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `application_code`, `job_id`, `candidate_id`, `candidate_name_snapshot`, `email_snapshot`, `phone_snapshot`, `portfolio_url_snapshot`, `resume_path`, `experience_notes`, `status`, `applied_at`, `updated_at`, `reviewed_by`, `reviewed_at`, `hr_notes`) VALUES
(1, 'APP-20261118-1BC461', 1, 2, 'robbin', 'izaz.robbin2106@gmail.com', '081930460169', '', 'uploads/resumes/28edd8f85435b3072c329819d147bafa.pdf', 'kerja', 'Reviewing', '2026-11-18 18:26:34', '2026-11-18 15:42:18', 1, '2026-11-18 22:42:18', 'goblok lu');

-- --------------------------------------------------------

--
-- Table structure for table `application_status_history`
--

CREATE TABLE `application_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` enum('Reviewing','Interview','Offered','Rejected') DEFAULT NULL,
  `new_status` enum('Reviewing','Interview','Offered','Rejected') NOT NULL,
  `changed_by` bigint(20) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_status_history`
--

INSERT INTO `application_status_history` (`id`, `application_id`, `old_status`, `new_status`, `changed_by`, `note`, `changed_at`) VALUES
(1, 1, 'Reviewing', 'Interview', 1, 'goblok lu', '2026-11-18 11:55:33'),
(2, 1, 'Interview', 'Reviewing', 1, 'goblok lu', '2026-11-18 15:42:18');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_profiles`
--

CREATE TABLE `candidate_profiles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `portfolio_url` varchar(2048) DEFAULT NULL,
  `resume_path` varchar(500) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidate_profiles`
--

INSERT INTO `candidate_profiles` (`user_id`, `phone`, `portfolio_url`, `resume_path`, `updated_at`) VALUES
(2, '081930460169', '', 'uploads/resumes/28edd8f85435b3072c329819d147bafa.pdf', '2026-11-18 11:25:56');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_at`) VALUES
(1, 'Engineering', '2026-11-18 09:45:36'),
(2, 'Product', '2026-11-18 09:45:36'),
(3, 'Marketing', '2026-11-18 09:45:36'),
(4, 'Operations', '2026-11-18 09:45:36');

-- --------------------------------------------------------

--
-- Table structure for table `hr_activity_logs`
--

CREATE TABLE `hr_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hr_user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `location_type` enum('Hybrid','Remote','On-site') NOT NULL DEFAULT 'Hybrid',
  `location_name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `qualification` text DEFAULT NULL,
  `employment_type` enum('Full-time','Part-time','Contract','Internship') NOT NULL DEFAULT 'Full-time',
  `status` enum('draft','published','closed','archived') NOT NULL DEFAULT 'published',
  `published_at` datetime DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `department_id`, `location_type`, `location_name`, `description`, `qualification`, `employment_type`, `status`, `published_at`, `deadline`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Junior Web Developer', 1, 'Hybrid', 'Yogyakarta', 'Mengembangkan dan memelihara aplikasi web menggunakan PHP, MySQL, HTML, CSS, JavaScript, dan Bootstrap.', 'Memahami dasar HTML, CSS, JavaScript, PHP, dan MySQL. Mampu bekerja dalam tim dan memiliki kemauan belajar.', 'Internship', 'published', '2026-11-18 17:39:13', '2026-12-18', 1, NULL, '2026-11-18 10:39:13', '2026-11-18 10:39:13'),
(2, 'Data Analyst', 1, 'Remote', 'Banten', 'Basis Data & System Analyst', 'Pengalaman Kerja Minimal 6 Bulan di Perusahaan mana pun.', 'Part-time', 'published', '2026-11-18 22:50:10', '2028-12-23', 1, NULL, '2026-11-18 15:50:10', '2026-11-18 15:50:10'),
(3, 'Cyber Security', 1, 'Hybrid', 'Jakarta', 'Keamanan Jaringan (Network Security): Melindungi jaringan dari penyusup, malware, dan serangan seperti Distributed Denial-of-Service (DDoS).', 'Pengalaman Kerja 8 Bulan', 'Contract', 'published', '2026-11-18 22:51:57', '2029-02-23', 1, NULL, '2026-11-18 15:51:57', '2026-11-18 15:51:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('candidate','hr') NOT NULL DEFAULT 'candidate',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `is_active`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin ARTANA', 'admin@artana.com', '$2y$10$I0QNvpI2EMTaGlofBQ86JeDh67EllwsgObvs39e7TE2c1c.JgDEQy', 'hr', 1, NULL, '2026-11-25 20:53:40', '2026-11-18 10:35:07', '2026-11-25 13:53:40'),
(2, 'robbin', 'izaz.robbin2106@gmail.com', '$2y$10$7wbs2ycWlbYPPnP8M8YOiOqZG4OeWiCQbCHArtW8W2mpBeNP0LvKi', 'candidate', 1, NULL, '2026-11-18 18:15:15', '2026-11-18 11:15:02', '2026-11-18 11:15:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_code` (`application_code`),
  ADD UNIQUE KEY `uq_candidate_job` (`candidate_id`,`job_id`),
  ADD KEY `fk_app_reviewer` (`reviewed_by`),
  ADD KEY `idx_app_job_status` (`job_id`,`status`),
  ADD KEY `idx_app_candidate_date` (`candidate_id`,`applied_at`),
  ADD KEY `idx_app_status` (`status`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_user` (`changed_by`),
  ADD KEY `idx_history_application_date` (`application_id`,`changed_at`);

--
-- Indexes for table `candidate_profiles`
--
ALTER TABLE `candidate_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hr_activity_logs`
--
ALTER TABLE `hr_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_hr_date` (`hr_user_id`,`created_at`),
  ADD KEY `idx_log_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jobs_creator` (`created_by`),
  ADD KEY `fk_jobs_updater` (`updated_by`),
  ADD KEY `idx_jobs_public` (`status`,`published_at`),
  ADD KEY `idx_jobs_department` (`department_id`),
  ADD KEY `idx_jobs_title` (`title`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_active` (`role`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hr_activity_logs`
--
ALTER TABLE `hr_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_app_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  ADD CONSTRAINT `fk_app_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD CONSTRAINT `fk_history_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `candidate_profiles`
--
ALTER TABLE `candidate_profiles`
  ADD CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hr_activity_logs`
--
ALTER TABLE `hr_activity_logs`
  ADD CONSTRAINT `fk_log_hr` FOREIGN KEY (`hr_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_jobs_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_jobs_updater` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
