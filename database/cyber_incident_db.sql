-- Cyber Incident Ticketing System (CITS) Database Initialization Script
-- Target Database: MySQL
-- Database Name: cyber_incident_db
-- Database creation skipped to support pre-provisioned hosting databases (e.g. MonsterASP)

-- Disabling foreign key checks during schema setup
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- TABLE: users
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) DEFAULT NULL,
  `department` VARCHAR(255) DEFAULT NULL,
  `job_title` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'active',
  `role` VARCHAR(255) NOT NULL DEFAULT 'Analyst',
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `last_login_ip` VARCHAR(45) DEFAULT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: password_reset_tokens
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL PRIMARY KEY,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: sessions
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: cache
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: cache_locks
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL PRIMARY KEY,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: jobs
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: job_batches
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT DEFAULT NULL,
  `cancelled_at` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: failed_jobs
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: personal_access_tokens
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT DEFAULT NULL,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: roles
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `roles_name_unique` (`name`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: permissions
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `permissions_name_unique` (`name`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: permission_role
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: role_user
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `assigned_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `role_user_role_id_user_id_unique` (`role_id`,`user_id`),
  CONSTRAINT `role_user_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_categories
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_categories`;
CREATE TABLE `incident_categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `incident_categories_name_unique` (`name`),
  UNIQUE KEY `incident_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_statuses
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_statuses`;
CREATE TABLE `incident_statuses` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT '1',
  `is_closed` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `incident_statuses_name_unique` (`name`),
  UNIQUE KEY `incident_statuses_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incidents
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incidents`;
CREATE TABLE `incidents` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_number` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `severity` VARCHAR(20) NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `status_id` BIGINT UNSIGNED NOT NULL,
  `reporter_id` BIGINT UNSIGNED NOT NULL,
  `current_assignee_id` BIGINT UNSIGNED DEFAULT NULL,
  `affected_asset` VARCHAR(255) DEFAULT NULL,
  `confidentiality_impact` VARCHAR(20) DEFAULT NULL,
  `integrity_impact` VARCHAR(20) DEFAULT NULL,
  `availability_impact` VARCHAR(20) DEFAULT NULL,
  `affected_systems_count` INT NOT NULL DEFAULT 0,
  `data_sensitivity` VARCHAR(50) DEFAULT NULL,
  `severity_override` TINYINT(1) NOT NULL DEFAULT 0,
  `severity_override_justification` TEXT DEFAULT NULL,
  `source_ip` VARCHAR(45) DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `impact_summary` TEXT DEFAULT NULL,
  `resolution_notes` TEXT DEFAULT NULL,
  `root_cause_category` VARCHAR(50) DEFAULT NULL,
  `root_cause_explanation` TEXT DEFAULT NULL,
  `lessons_learned` TEXT DEFAULT NULL,
  `rejection_reason` TEXT DEFAULT NULL,
  `occurred_at` TIMESTAMP NOT NULL,
  `reported_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` TIMESTAMP NULL DEFAULT NULL,
  `closed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `incidents_ticket_number_unique` (`ticket_number`),
  KEY `incidents_severity_index` (`severity`),
  CONSTRAINT `incidents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `incident_categories` (`id`),
  CONSTRAINT `incidents_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `incident_statuses` (`id`),
  CONSTRAINT `incidents_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incidents_current_assignee_id_foreign` FOREIGN KEY (`current_assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `incidents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `incidents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_assignments
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_assignments`;
CREATE TABLE `incident_assignments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `assigned_to` BIGINT UNSIGNED NOT NULL,
  `assigned_by` BIGINT UNSIGNED DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `released_at` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_assignments_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_comments
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_comments`;
CREATE TABLE `incident_comments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `body` TEXT NOT NULL,
  `is_internal` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_comments_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_attachments
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_attachments`;
CREATE TABLE `incident_attachments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_name` VARCHAR(255) NOT NULL,
  `disk` VARCHAR(255) NOT NULL DEFAULT 'public',
  `file_path` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(255) NOT NULL,
  `file_hash` VARCHAR(64) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `size_bytes` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_attachments_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_history
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_history`;
CREATE TABLE `incident_history` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `event_type` VARCHAR(255) NOT NULL,
  `field_name` VARCHAR(255) DEFAULT NULL,
  `old_value` JSON DEFAULT NULL,
  `new_value` JSON DEFAULT NULL,
  `description` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_history_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_timelines
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_timelines`;
CREATE TABLE `incident_timelines` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `occurred_at` TIMESTAMP NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_timelines_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_iocs
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_iocs`;
CREATE TABLE `incident_iocs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_iocs_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_affected_systems
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_affected_systems`;
CREATE TABLE `incident_affected_systems` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `asset_name` VARCHAR(255) NOT NULL,
  `asset_type` VARCHAR(255) NOT NULL,
  `impact_level` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_affected_systems_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_actions_taken
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_actions_taken`;
CREATE TABLE `incident_actions_taken` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `occurred_at` TIMESTAMP NOT NULL,
  `action` TEXT NOT NULL,
  `performed_by` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_actions_taken_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: incident_remediation_actions
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `incident_remediation_actions`;
CREATE TABLE `incident_remediation_actions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `incident_id` BIGINT UNSIGNED NOT NULL,
  `description` TEXT NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `due_date` DATE NOT NULL,
  `status` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `incident_remediation_actions_incident_id_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `incident_remediation_actions_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: audit_logs
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `entity_type` VARCHAR(255) DEFAULT NULL,
  `entity_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `metadata` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `audit_logs_action_index` (`action`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: notifications
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(255) NOT NULL DEFAULT 'info',
  `data` JSON DEFAULT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- TABLE: reports
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `generated_by` BIGINT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `format` VARCHAR(10) NOT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'generated',
  `filters` JSON DEFAULT NULL,
  `summary` JSON DEFAULT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `generated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `reports_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enabling foreign key checks
SET FOREIGN_KEY_CHECKS = 1;


-- =============================================================
-- DATA SEEDING (INITIAL DATABASE VALUES)
-- =============================================================

-- -------------------------------------------------------------
-- SEED: roles
-- -------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'administrator', 'Full platform administration and oversight.', 1, NOW(), NOW()),
(2, 'Security Analyst', 'security-analyst', 'Investigates and manages cybersecurity incidents.', 1, NOW(), NOW()),
(3, 'User', 'user', 'Reports incidents and tracks their own submissions.', 1, NOW(), NOW());

-- -------------------------------------------------------------
-- SEED: permissions
-- -------------------------------------------------------------
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'View Dashboard', 'dashboard.view', 'View dashboard metrics and widgets.', NOW(), NOW()),
(2, 'View Users', 'users.view', 'View user accounts.', NOW(), NOW()),
(3, 'Manage Users', 'users.manage', 'Create, edit, and deactivate users.', NOW(), NOW()),
(4, 'View Roles', 'roles.view', 'View roles and permissions.', NOW(), NOW()),
(5, 'View Incidents', 'incidents.view', 'View incidents that are allowed by policy.', NOW(), NOW()),
(6, 'Create Incidents', 'incidents.create', 'Create new incidents.', NOW(), NOW()),
(7, 'Update Incidents', 'incidents.update', 'Update incident details.', NOW(), NOW()),
(8, 'Delete Incidents', 'incidents.delete', 'Delete incident records.', NOW(), NOW()),
(9, 'Assign Incidents', 'incidents.assign', 'Assign incidents to analysts.', NOW(), NOW()),
(10, 'Change Incident Status', 'incidents.change-status', 'Transition incident workflow states.', NOW(), NOW()),
(11, 'Comment On Incidents', 'incidents.comment', 'Add comments to incidents.', NOW(), NOW()),
(12, 'Upload Incident Evidence', 'incidents.upload', 'Upload evidence files for incidents.', NOW(), NOW()),
(13, 'View Audit Logs', 'audit-logs.view', 'Review security audit logs.', NOW(), NOW()),
(14, 'View Reports', 'reports.view', 'View generated reports.', NOW(), NOW()),
(15, 'Export Reports', 'reports.export', 'Export PDF and CSV reports.', NOW(), NOW()),
(16, 'View Analytics', 'analytics.view', 'View analytical dashboards.', NOW(), NOW()),
(17, 'View Notifications', 'notifications.view', 'View personal notifications.', NOW(), NOW());

-- -------------------------------------------------------------
-- SEED: permission_role (Mapping matrix)
-- -------------------------------------------------------------
-- Administrator (Role 1): Gets all permissions (1-17)
INSERT INTO `permission_role` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17);

-- Security Analyst (Role 2): Gets all except manage users (3) and delete incidents (8)
INSERT INTO `permission_role` (`role_id`, `permission_id`) VALUES
(2, 1), (2, 2), (2, 4), (2, 5), (2, 6), (2, 7), (2, 9), (2, 10), (2, 11), (2, 12), (2, 13), (2, 14), (2, 15), (2, 16), (2, 17);

-- Regular User (Role 3): Gets basic view, create, comment, upload, and notifications permissions
INSERT INTO `permission_role` (`role_id`, `permission_id`) VALUES
(3, 1), (3, 5), (3, 6), (3, 11), (3, 12), (3, 17);

-- -------------------------------------------------------------
-- SEED: users (admin, analyst, user)
-- Passwords are set to 'password' (hashed using bcrypt)
-- -------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `department`, `job_title`, `status`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@cyberincidentsystem.com', NOW(), '$2y$10$QqT4VCIt5HD33TkqOazFhu1d1m4Fus4HUpMvwUpXepUZY4NO5Wk3G', '+1234567890', 'Security Operations Center', 'Chief Information Security Officer', 'active', 'Admin', NOW(), NOW()),
(2, 'Analyst User', 'analyst@cyberincidentsystem.com', NOW(), '$2y$10$QqT4VCIt5HD33TkqOazFhu1d1m4Fus4HUpMvwUpXepUZY4NO5Wk3G', '+1234567891', 'Incident Response Team', 'Senior Incident Responder', 'active', 'Analyst', NOW(), NOW()),
(3, 'Regular User', 'user@cyberincidentsystem.com', NOW(), '$2y$10$QqT4VCIt5HD33TkqOazFhu1d1m4Fus4HUpMvwUpXepUZY4NO5Wk3G', '+1234567892', 'Human Resources', 'HR Specialist', 'active', 'Analyst', NOW(), NOW());

-- -------------------------------------------------------------
-- SEED: role_user (Assign role to users)
-- -------------------------------------------------------------
INSERT INTO `role_user` (`role_id`, `user_id`, `assigned_by`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NOW(), NOW()), -- Admin User has Administrator role
(2, 2, NULL, NOW(), NOW()), -- Analyst User has Security Analyst role
(3, 3, NULL, NOW(), NOW()); -- Regular User has User role

-- -------------------------------------------------------------
-- SEED: incident_categories
-- -------------------------------------------------------------
INSERT INTO `incident_categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Malware', 'malware', 'Malicious software activity or infection.', 1, NOW(), NOW()),
(2, 'Phishing', 'phishing', 'Deceptive messages or credential harvesting attempts.', 1, NOW(), NOW()),
(3, 'Unauthorized Access', 'unauthorized-access', 'Suspicious or confirmed unauthorized access events.', 1, NOW(), NOW()),
(4, 'Data Breach', 'data-breach', 'Exposure, exfiltration, or destruction of sensitive data.', 1, NOW(), NOW()),
(5, 'Denial Of Service', 'denial-of-service', 'Availability-impacting traffic or system overload.', 1, NOW(), NOW()),
(6, 'Insider Threat', 'insider-threat', 'Potential malicious or negligent insider activity.', 1, NOW(), NOW()),
(7, 'Vulnerability', 'vulnerability', 'Security weaknesses requiring remediation.', 1, NOW(), NOW()),
(8, 'Suspicious Activity', 'suspicious-activity', 'Observed behavior requiring further triage.', 1, NOW(), NOW());

-- -------------------------------------------------------------
-- SEED: incident_statuses
-- -------------------------------------------------------------
INSERT INTO `incident_statuses` (`id`, `name`, `slug`, `description`, `sort_order`, `is_closed`, `created_at`, `updated_at`) VALUES
(1, 'New', 'new', 'Newly reported incident awaiting triage.', 1, 0, NOW(), NOW()),
(2, 'Investigating', 'investigating', 'Investigation or response is underway.', 2, 0, NOW(), NOW()),
(3, 'Contained', 'contained', 'Incident has been contained.', 3, 0, NOW(), NOW()),
(4, 'Eradicated', 'eradicated', 'Threat has been eradicated.', 4, 0, NOW(), NOW()),
(5, 'Recovering', 'recovering', 'Systems are recovering.', 5, 0, NOW(), NOW()),
(6, 'Closed', 'closed', 'Incident fully closed.', 6, 1, NOW(), NOW());
