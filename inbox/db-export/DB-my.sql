-- =====================================================================
-- DB-my.sql — دادهٔ ساب‌دامین my (demo)
-- =====================================================================
-- این فایل، «دادهٔ فایلی» همین ساب‌دامین است که به SQL رسمی جوما تبدیل شده.
-- آن ساب‌دامین با storage = file کار می‌کند و دیتابیس ندارد؛ پس این خروجی
-- phpMyAdmin نیست، معادل SQL همان داده است (ساختار از اسکیمای رسمی جوما).
-- برای بازگردانی: اول ساختار (CREATE TABLE) و بعد داده (INSERT).
-- هیچ UPDATE/DELETE/ALTER در این فایل نیست.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = "+00:00";

-- ---------- ساختار (اسکیمای رسمی) ----------
CREATE TABLE IF NOT EXISTS joma_roles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_key VARCHAR(32) NOT NULL,
  access_level TINYINT UNSIGNED NOT NULL,
  title VARCHAR(64) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_role_key (role_key)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_permissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  perm_key VARCHAR(64) NOT NULL,
  title VARCHAR(128) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_perm_key (perm_key)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_role_permissions (
  role_key VARCHAR(32) NOT NULL,
  perm_key VARCHAR(64) NOT NULL,
  PRIMARY KEY (role_key, perm_key)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  username VARCHAR(32) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  job VARCHAR(80) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role_key VARCHAR(32) NOT NULL DEFAULT 'member',
  access_level TINYINT UNSIGNED NOT NULL DEFAULT 1,
  mobile_verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username),
  UNIQUE KEY uq_email (email)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_user_preferences (
  user_id INT UNSIGNED NOT NULL,
  compact_cards TINYINT(1) NOT NULL DEFAULT 0,
  notifications_enabled TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_otp_codes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  mobile VARCHAR(20) NOT NULL,
  code VARCHAR(10) NOT NULL,
  purpose VARCHAR(32) NOT NULL DEFAULT 'verify_mobile',
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_otp_mobile (mobile)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_activities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  code VARCHAR(16) NOT NULL,
  group_code VARCHAR(32) NOT NULL,
  name VARCHAR(190) NOT NULL,
  category VARCHAR(64) NOT NULL,
  data_type VARCHAR(16) NOT NULL,
  track_mode VARCHAR(16) NOT NULL,
  unit VARCHAR(24) NOT NULL,
  daily_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  weekly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  monthly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  weight INT NOT NULL DEFAULT 1,
  frequency VARCHAR(16) NOT NULL,
  sticker VARCHAR(16) NOT NULL,
  color VARCHAR(16) NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  is_seed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_act_user (user_id),
  KEY idx_act_code (code)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_periods (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  year SMALLINT NOT NULL,
  month TINYINT NOT NULL,
  start_date CHAR(10) NOT NULL,
  end_date CHAR(10) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_period (user_id, period_key)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_plans (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  period_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'DRAFT',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  finalized_at DATETIME DEFAULT NULL,
  started_at DATETIME DEFAULT NULL,
  archived_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_period_plan (user_id, period_id)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_plan_activities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  activity_id INT UNSIGNED NOT NULL,
  activity_code VARCHAR(16) NOT NULL,
  name VARCHAR(190) NOT NULL,
  category VARCHAR(64) NOT NULL,
  frequency VARCHAR(16) NOT NULL,
  data_type VARCHAR(16) NOT NULL,
  unit VARCHAR(24) NOT NULL,
  daily_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  weekly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  monthly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  target_value DECIMAL(10,2) NOT NULL DEFAULT 0,
  weight INT NOT NULL DEFAULT 1,
  sticker VARCHAR(16) NOT NULL,
  color VARCHAR(16) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  snapshot_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_plan_activity (plan_id, activity_id)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_performance_events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  plan_activity_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  frequency VARCHAR(16) NOT NULL,
  data_type VARCHAR(16) NOT NULL,
  event_type VARCHAR(32) NOT NULL DEFAULT 'PERFORMANCE_REGISTERED',
  performance_date CHAR(10) NOT NULL,
  actual_value DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_evt_plan (plan_id),
  KEY idx_evt_pa_date (plan_activity_id, performance_date)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_mood_records (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  jalali_date CHAR(10) NOT NULL,
  energy TINYINT NOT NULL,
  general_mood TINYINT NOT NULL,
  focus TINYINT NOT NULL,
  sleep_quality TINYINT NOT NULL,
  stress TINYINT NOT NULL,
  note TEXT,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mood_user_day (user_id, jalali_date)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_report_projections (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  payload LONGTEXT NOT NULL,
  source_event_count INT NOT NULL DEFAULT 0,
  generated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_proj_plan (plan_id)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_settings (
  setting_key VARCHAR(64) NOT NULL,
  setting_value TEXT,
  PRIMARY KEY (setting_key)
) ENGINE;

CREATE TABLE IF NOT EXISTS joma_notifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  body TEXT,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_notif_user (user_id)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_providers` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'INACTIVE',
  `title` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hammasir_provider_user` (`user_id`),
  KEY `idx_hammasir_provider_status` (`status`)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_links` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `provider_user_id` int(10) UNSIGNED NOT NULL,
  `client_user_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'PENDING',
  `consent_text` text NOT NULL,
  `consent_version` varchar(32) NOT NULL,
  `requested_at` datetime NOT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `declined_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_hammasir_link_client_status` (`client_user_id`, `status`),
  KEY `idx_hammasir_link_provider_status` (`provider_user_id`, `status`)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_permissions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_id` int(10) UNSIGNED NOT NULL,
  `perm_key` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `changed_by_user_id` int(10) UNSIGNED NOT NULL,
  `changed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hammasir_perm_link_key` (`link_id`, `perm_key`)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_permission_history` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_id` int(10) UNSIGNED NOT NULL,
  `perm_key` varchar(64) NOT NULL,
  `previous_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `changed_by_user_id` int(10) UNSIGNED NOT NULL,
  `change_source` varchar(32) NOT NULL,
  `changed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_hammasir_perm_hist_link` (`link_id`, `perm_key`)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_messages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_id` int(10) UNSIGNED NOT NULL,
  `sender_user_id` int(10) UNSIGNED NOT NULL,
  `recipient_user_id` int(10) UNSIGNED NOT NULL,
  `jalali_date` char(10) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_hammasir_msg_daily` (`link_id`, `sender_user_id`, `jalali_date`),
  KEY `idx_hammasir_msg_unread` (`recipient_user_id`, `is_read`),
  KEY `idx_hammasir_msg_page` (`link_id`, `created_at`, `id`)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_system_events` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_id` int(10) UNSIGNED NOT NULL,
  `event_type` varchar(32) NOT NULL,
  `actor_user_id` int(10) UNSIGNED NOT NULL,
  `recipient_user_id` int(10) UNSIGNED NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_hammasir_event_unread` (`recipient_user_id`, `is_read`),
  KEY `idx_hammasir_event_order` (`recipient_user_id`, `created_at`, `id`)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_user_flags` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `flag_key` varchar(64) NOT NULL,
  `flag_value` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hammasir_flag_user_key` (`user_id`, `flag_key`)
) ENGINE;

CREATE TABLE IF NOT EXISTS `joma_hammasir_invite_codes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `provider_user_id` INT UNSIGNED NOT NULL,
  `code` VARCHAR(16) NOT NULL,
  `status` VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  `created_by_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `used_by_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `used_at` DATETIME NULL,
  `link_id` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invite_code` (`code`),
  KEY `ix_invite_provider` (`provider_user_id`, `status`)
) ENGINE;

-- ---------- داده ----------
INSERT INTO `joma_users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `job`, `password_hash`, `role_key`, `access_level`, `mobile_verified`, `created_at`) VALUES (1, 'دمو', 'مدیر', 'demo', 'demo@example.com', '', 'مدیر', '$2y$10$tp31lCjBIrRo7OnxTelNhOv6hAwkHkIv0da7PxHI9KTiVkcCKTtIS', 'admin', 2, 0, '2026-08-20 09:00:00');
INSERT INTO `joma_users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `job`, `password_hash`, `role_key`, `access_level`, `mobile_verified`, `created_at`) VALUES (2, 'محمدرضا', 'احمدی', 'drahmadi', 'dr@example.com', '', 'روانشناس', '$2y$10$tp31lCjBIrRo7OnxTelNhOv6hAwkHkIv0da7PxHI9KTiVkcCKTtIS', 'member', 1, 0, '2026-08-20 09:05:00');
INSERT INTO `joma_users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `job`, `password_hash`, `role_key`, `access_level`, `mobile_verified`, `created_at`) VALUES (3, 'سارا', 'کریمی', 'sara', 'sara@example.com', '', 'دانشجو', '$2y$10$tp31lCjBIrRo7OnxTelNhOv6hAwkHkIv0da7PxHI9KTiVkcCKTtIS', 'member', 1, 0, '2026-08-20 09:10:00');
INSERT INTO `joma_users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `job`, `password_hash`, `role_key`, `access_level`, `mobile_verified`, `created_at`) VALUES (4, 'علی', 'رضایی', 'ali', 'ali@example.com', '', 'کارمند', '$2y$10$tp31lCjBIrRo7OnxTelNhOv6hAwkHkIv0da7PxHI9KTiVkcCKTtIS', 'member', 1, 0, '2026-08-20 09:15:00');
INSERT INTO `joma_users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `job`, `password_hash`, `role_key`, `access_level`, `mobile_verified`, `created_at`) VALUES (5, 'مریم', 'کریمی', 'maryam', 'maryam@example.com', '', 'خانه‌دار', '$2y$10$tp31lCjBIrRo7OnxTelNhOv6hAwkHkIv0da7PxHI9KTiVkcCKTtIS', 'member', 1, 0, '2026-08-20 09:20:00');
INSERT INTO `joma_users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `job`, `password_hash`, `role_key`, `access_level`, `mobile_verified`, `created_at`) VALUES (6, 'حسین', 'احمدی', 'hossein', 'hossein@example.com', '', 'دانش‌آموز', '$2y$10$tp31lCjBIrRo7OnxTelNhOv6hAwkHkIv0da7PxHI9KTiVkcCKTtIS', 'member', 1, 0, '2026-08-20 09:25:00');
INSERT INTO `joma_user_preferences` (`user_id`, `compact_cards`, `notifications_enabled`) VALUES (1, 0, 0);
INSERT INTO `joma_user_preferences` (`user_id`, `compact_cards`, `notifications_enabled`) VALUES (2, 0, 0);
INSERT INTO `joma_user_preferences` (`user_id`, `compact_cards`, `notifications_enabled`) VALUES (3, 0, 0);
INSERT INTO `joma_user_preferences` (`user_id`, `compact_cards`, `notifications_enabled`) VALUES (4, 0, 0);
INSERT INTO `joma_user_preferences` (`user_id`, `compact_cards`, `notifications_enabled`) VALUES (5, 0, 0);
INSERT INTO `joma_user_preferences` (`user_id`, `compact_cards`, `notifications_enabled`) VALUES (6, 0, 0);
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1021, 2, 'ACT001', 'ACT_HEA', 'ورزش', 'سلامت جسم', 'DURATION', 'DURATION', 'UNIT_MIN', 30, 150, 600, 5, 'DAILY', '🏃', '#E85D75', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1022, 2, 'ACT002', 'ACT_HEA', 'نوشیدن آب', 'سلامت جسم', 'NUMERIC', 'NUMERIC', 'UNIT_GLASS', 6, 42, 180, 4, 'DAILY', '💧', '#4F9FC4', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1023, 2, 'ACT003', 'ACT_HEA', 'پیاده‌روی', 'سلامت جسم', 'DURATION', 'DURATION', 'UNIT_MIN', 20, 100, 400, 4, 'DAILY', '🚶', '#5AA469', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1024, 2, 'ACT004', 'ACT_HEA', 'مصرف میوه', 'سلامت جسم', 'NUMERIC', 'NUMERIC', 'UNIT_TIMES', 2, 14, 60, 3, 'DAILY', '🍎', '#D95D5D', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1025, 2, 'ACT005', 'ACT_SLE', 'خواب کافی', 'خواب و استراحت', 'DURATION', 'DURATION', 'UNIT_HOUR', 7, 49, 210, 5, 'DAILY', '🌙', '#5964B4', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1026, 2, 'ACT006', 'ACT_SLE', 'ساعت خواب منظم', 'خواب و استراحت', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '⏰', '#6D75B8', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1027, 2, 'ACT007', 'ACT_MEN', 'مدیتیشن', 'سلامت روان', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 50, 200, 4, 'DAILY', '🧘', '#7C6CE7', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1028, 2, 'ACT008', 'ACT_MEN', 'تمرین تنفس', 'سلامت روان', 'DURATION', 'DURATION', 'UNIT_MIN', 5, 35, 150, 4, 'DAILY', '🌬️', '#769FCD', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1029, 2, 'ACT009', 'ACT_MEN', 'ثبت حال روزانه', 'سلامت روان', 'RATING', 'RATING', 'UNIT_SCORE', 1, 7, 30, 5, 'DAILY', '📓', '#E9A23B', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1030, 2, 'ACT010', 'ACT_FOC', 'مطالعه بدون حواس‌پرتی', 'تمرکز و ذهن', 'DURATION', 'DURATION', 'UNIT_MIN', 30, 150, 600, 5, 'DAILY', '🎯', '#4F7CAC', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1031, 2, 'ACT011', 'ACT_FOC', 'زمان بدون موبایل', 'تمرکز و ذهن', 'DURATION', 'DURATION', 'UNIT_MIN', 30, 150, 600, 4, 'DAILY', '📵', '#5B8E7D', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1032, 2, 'ACT012', 'ACT_FOC', 'نوشتن برنامه روزانه', 'تمرکز و ذهن', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '📝', '#7C8C6C', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1033, 2, 'ACT013', 'ACT_GRO', 'مطالعه', 'یادگیری', 'DURATION', 'DURATION', 'UNIT_MIN', 30, 150, 600, 4, 'DAILY', '📚', '#D99A2B', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1034, 2, 'ACT014', 'ACT_GRO', 'یادگیری مهارت جدید', 'رشد فردی', 'DURATION', 'DURATION', 'UNIT_MIN', 20, 100, 400, 4, 'DAILY', '🛠️', '#5AA469', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1035, 2, 'ACT015', 'ACT_GRO', 'نوشتن اهداف', 'رشد فردی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 3, 4, 4, 'WEEKLY', '⭐', '#8C6BB1', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1036, 2, 'ACT016', 'ACT_REL', 'گفت‌وگوی باکیفیت با همسر', 'روابط', 'DURATION', 'DURATION', 'UNIT_MIN', 15, 105, 450, 5, 'DAILY', '💑', '#D66BA0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1037, 2, 'ACT017', 'ACT_REL', 'قدردانی از همسر', 'روابط', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🙏', '#C7D4A0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1038, 2, 'ACT018', 'ACT_REL', 'قرار دونفره', 'روابط', 'DURATION', 'DURATION', 'UNIT_HOUR', 0, 2, 8, 5, 'WEEKLY', '☕', '#C06C84', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1039, 2, 'ACT019', 'ACT_FAM', 'تماس با خانواده', 'خانواده', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 30, 120, 3, 'WEEKLY', '📞', '#C7D4A0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1040, 2, 'ACT020', 'ACT_FAM', 'دیدار با خانواده', 'خانواده', 'DURATION', 'DURATION', 'UNIT_HOUR', 0, 2, 8, 4, 'WEEKLY', '🏠', '#B97855', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1041, 2, 'ACT021', 'ACT_MIN', 'نوشتن سه نکته مثبت', 'ذهن‌آگاهی', 'NUMERIC', 'NUMERIC', 'UNIT_COUNT', 3, 21, 90, 4, 'DAILY', '✨', '#6D8B74', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1042, 2, 'ACT022', 'ACT_MIN', 'تمرین شکرگزاری', 'ذهن‌آگاهی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🌸', '#9A8C98', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1043, 2, 'ACT023', 'ACT_SEL', 'زمان شخصی', 'مراقبت از خود', 'DURATION', 'DURATION', 'UNIT_MIN', 20, 140, 600, 4, 'DAILY', '🛁', '#C08497', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1044, 2, 'ACT024', 'ACT_SEL', 'انجام یک فعالیت لذت‌بخش', 'مراقبت از خود', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 3, 12, 3, 'WEEKLY', '🎨', '#B565A7', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1045, 2, 'ACT025', 'ACT_PRO', 'اولویت‌بندی کارهای روز', 'بهره‌وری', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '✅', '#E09F3E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1046, 2, 'ACT026', 'ACT_PRO', 'انجام مهم‌ترین کار روز', 'بهره‌وری', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🔥', '#D9A441', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1047, 2, 'ACT027', 'ACT_PRO', 'مرور هفتگی', 'بهره‌وری', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 30, 120, 4, 'WEEKLY', '📅', '#4F7CAC', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1048, 2, 'ACT028', 'ACT_LEA', 'خواندن کتاب', 'یادگیری', 'DURATION', 'DURATION', 'UNIT_MIN', 20, 140, 600, 4, 'DAILY', '📖', '#B07D3C', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1049, 2, 'ACT029', 'ACT_LEA', 'تکمیل یک فصل کتاب', 'یادگیری', 'NUMERIC', 'NUMERIC', 'UNIT_COUNT', 0, 1, 4, 3, 'WEEKLY', '📗', '#A86F52', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1050, 2, 'ACT030', 'ACT_MON', 'خواندن یک کتاب کامل', 'یادگیری', 'NUMERIC', 'NUMERIC', 'UNIT_COUNT', 0, 0, 1, 5, 'MONTHLY', '📘', '#D99A2B', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1051, 2, 'ACT031', 'ACT_MON', 'یک روز کامل بدون شبکه اجتماعی', 'سبک زندگی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 0, 1, 4, 'MONTHLY', '📵', '#5B8E7D', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1052, 2, 'ACT032', 'ACT_MON', 'مرور ماه و ارزیابی شخصی', 'رشد فردی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 0, 1, 5, 'MONTHLY', '🪞', '#7C6CE7', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1053, 2, 'ACT033', 'ACT_MON', 'تعیین اهداف ماه آینده', 'رشد فردی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 0, 1, 5, 'MONTHLY', '🧭', '#4F7CAC', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1054, 2, 'ACT034', 'ACT_HEA', 'غذای سالم', 'سلامت جسم', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🥗', '#6A994E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1055, 2, 'ACT035', 'ACT_HEA', 'کاهش مصرف قند', 'سلامت جسم', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🍬', '#BC6C25', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1056, 2, 'ACT036', 'ACT_HEA', 'کشش بدن', 'سلامت جسم', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 50, 200, 3, 'DAILY', '🤸', '#76A5AF', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1057, 2, 'ACT037', 'ACT_MEN', 'نوشتن افکار و احساسات', 'سلامت روان', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 50, 200, 4, 'DAILY', '💭', '#8064A2', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1058, 2, 'ACT038', 'ACT_MEN', 'فاصله گرفتن آگاهانه هنگام تنش', 'سلامت روان', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🌿', '#739E9B', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1059, 2, 'ACT039', 'ACT_REL', 'گفت‌وگوی بدون موبایل', 'روابط', 'DURATION', 'DURATION', 'UNIT_MIN', 15, 105, 450, 4, 'DAILY', '💬', '#C45B8A', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1060, 2, 'ACT040', 'ACT_FOC', 'یک کار در هر لحظه', 'تمرکز و ذهن', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🎧', '#547AA5', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1061, 2, 'ACT041', 'ACT_SLE', 'خاموش‌کردن صفحه‌نمایش قبل از خواب', 'خواب و استراحت', 'DURATION', 'DURATION', 'UNIT_MIN', 30, 210, 900, 4, 'DAILY', '🖥️', '#5964B4', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1062, 2, 'ACT042', 'ACT_SEL', 'مراقبت شخصی', 'مراقبت از خود', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 3, 'DAILY', '🧴', '#C08497', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1063, 2, 'ACT043', 'ACT_PRO', 'جمع‌بندی پایان روز', 'بهره‌وری', 'DURATION', 'DURATION', 'UNIT_MIN', 5, 35, 150, 3, 'DAILY', '🌇', '#7A9E7E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1064, 2, 'ACT044', 'ACT_GRO', 'بررسی یک رفتار و یادگیری از آن', 'رشد فردی', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 70, 300, 4, 'DAILY', '🔍', '#6B7AA1', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1065, 2, 'ACT045', 'ACT_MON', 'انجام یک تجربه جدید', 'رشد فردی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 0, 1, 3, 'MONTHLY', '🌈', '#D17A22', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1066, 2, 'ACT046', 'ACT_CPL', 'بازسازی صمیمیت عاطفی', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 15, 105, 450, 5, 'DAILY', '💗', '#C45B8A', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1067, 2, 'ACT047', 'ACT_CPL', 'تمرین گفت‌وگوی بدون دعوا', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 15, 105, 450, 5, 'DAILY', '🗣️', '#D66BA0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1068, 2, 'ACT048', 'ACT_CPL', 'هنر شنیدن همسر', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 70, 300, 5, 'DAILY', '👂', '#9A6B8C', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1069, 2, 'ACT049', 'ACT_CPL', 'تمرین همدلی با شریک عاطفی', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🤲', '#C08497', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1070, 2, 'ACT050', 'ACT_CPL', 'بیان نیازها بدون سرزنش', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🗨️', '#B565A7', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1071, 2, 'ACT051', 'ACT_CPL', 'مدیریت خشم و جلوگیری از انفجار رابطه', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🧯', '#C0392B', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1072, 2, 'ACT052', 'ACT_CPL', 'حل تعارض و اختلاف‌نظر', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 30, 120, 5, 'WEEKLY', '⚖️', '#6B7AA1', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1073, 2, 'ACT053', 'ACT_CPL', 'ترمیم رابطه بعد از دعوا', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 1, 4, 5, 'WEEKLY', '🩹', '#E07A5F', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1074, 2, 'ACT054', 'ACT_CPL', 'بازسازی اعتماد آسیب‌دیده', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 5, 'WEEKLY', '🔐', '#5C4B6C', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1075, 2, 'ACT055', 'ACT_CPL', 'مرزبندی سالم در رابطه', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🧱', '#8D6E63', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1076, 2, 'ACT056', 'ACT_CPL', 'شناخت الگوهای تکراری رابطه', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 15, 60, 4, 'WEEKLY', '🔁', '#7C6CE7', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1077, 2, 'ACT057', 'ACT_CPL', 'شناخت طرحواره‌های مؤثر بر رابطه', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🧩', '#6D75B8', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1078, 2, 'ACT058', 'ACT_CPL', 'تقویت محبت و توجه روزانه', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '💝', '#D66BA0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1079, 2, 'ACT059', 'ACT_CPL', 'بازگرداندن عشق و هیجان به رابطه', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 1, 4, 4, 'WEEKLY', '💘', '#E85D75', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1080, 2, 'ACT060', 'ACT_CPL', 'تمرین دوستی دوباره با همسر', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 30, 120, 4, 'WEEKLY', '🤝', '#C06C84', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1081, 2, 'ACT061', 'ACT_CPL', 'هم‌راستا کردن اهداف زوجین', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 1, 4, 4, 'WEEKLY', '🏁', '#4F7CAC', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1082, 2, 'ACT062', 'ACT_CPL', 'مدیریت اختلافات مالی', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 1, 4, 4, 'WEEKLY', '💰', '#D9A441', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1083, 2, 'ACT063', 'ACT_CPL', 'مدیریت دخالت خانواده‌ها', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 1, 4, 4, 'WEEKLY', '🛡️', '#B97855', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1084, 2, 'ACT064', 'ACT_CPL', 'مدیریت موبایل و فضای مجازی در رابطه', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '📱', '#5B8E7D', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1085, 2, 'ACT065', 'ACT_CPL', 'افزایش صمیمیت و نزدیکی زوجین', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 15, 105, 450, 5, 'DAILY', '💞', '#C45B8A', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1086, 2, 'ACT066', 'ACT_CPL', 'مقابله با حسادت و ناامنی عاطفی', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '💚', '#6A994E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1087, 2, 'ACT067', 'ACT_CPL', 'مدیریت افکار منفی درباره همسر', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '☁️', '#6B7AA1', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1088, 2, 'ACT068', 'ACT_CPL', 'تقسیم مسئولیت‌های زندگی', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 1, 4, 4, 'WEEKLY', '🧺', '#A86F52', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1089, 2, 'ACT069', 'ACT_CPL', 'کنار آمدن با سردی عاطفی', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🌨️', '#769FCD', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1090, 2, 'ACT070', 'ACT_CPL', 'ترمیم رابطه پس از خیانت', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 30, 120, 5, 'WEEKLY', '🕊️', '#9A8C98', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1091, 2, 'ACT071', 'ACT_CPL', 'تمرین امنیت عاطفی', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🏡', '#7A9E7E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1092, 2, 'ACT072', 'ACT_CPL', 'شناخت نقش خودم در مشکلات رابطه', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 70, 300, 4, 'DAILY', '👤', '#8064A2', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1093, 2, 'ACT073', 'ACT_CPL', 'شکستن چرخه دعواهای تکراری', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🌀', '#BC6C25', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1094, 2, 'ACT074', 'ACT_CPL', 'تقویت نقاط قوت رابطه', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 1, 4, 3, 'WEEKLY', '💪', '#5AA469', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1095, 2, 'ACT075', 'ACT_CPL', 'بازسازی تعهد و آینده مشترک', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 0, 1, 5, 'MONTHLY', '💍', '#8C6BB1', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1096, 2, 'ACT076', 'ACT_CPL', 'تمرین قدردانی از همسر', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '💐', '#C7D4A0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1097, 2, 'ACT077', 'ACT_CPL', 'دفترچه گفت‌وگوی زوجین', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 70, 300, 4, 'DAILY', '📒', '#B07D3C', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1098, 2, 'ACT078', 'ACT_CPL', 'قرار هفتگی زوجین', 'زوج درمانی', 'DURATION', 'DURATION', 'UNIT_HOUR', 0, 2, 8, 5, 'WEEKLY', '🍷', '#C06C84', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1099, 2, 'ACT079', 'ACT_CPL', 'تمرین روزانه اتصال عاطفی', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🔗', '#D66BA0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1100, 2, 'ACT080', 'ACT_CPL', 'ارزیابی ماهانه سلامت رابطه', 'زوج درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 0, 0, 1, 5, 'MONTHLY', '📋', '#4F7CAC', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1101, 2, 'ACT081', 'ACT_SCH', 'دفتر طرحواره امروز', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 15, 105, 450, 5, 'DAILY', '📒', '#5B6ABF', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1102, 2, 'ACT082', 'ACT_SCH', 'فلش‌کارت بزرگسال سالم', 'طرحواره درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🃏', '#6A994E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1103, 2, 'ACT083', 'ACT_SCH', 'شواهد موافق و مخالف طرحواره', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 10, 70, 300, 4, 'DAILY', '⚖️', '#4F7CAC', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1104, 2, 'ACT084', 'ACT_SCH', 'تشخیص سبک مقابله', 'طرحواره درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '🧭', '#7C6CE7', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1105, 2, 'ACT085', 'ACT_SCH', 'مکث قبل از الگوی قدیمی', 'طرحواره درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 4, 'DAILY', '⏸️', '#5B8E7D', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1106, 2, 'ACT086', 'ACT_SCH', 'شکستن الگو؛ رفتار جدید', 'طرحواره درمانی', 'BOOLEAN', 'BOOLEAN', 'UNIT_NONE', 1, 7, 30, 5, 'DAILY', '🦋', '#D17A22', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1107, 2, 'ACT087', 'ACT_SCH', 'گفتگوی سه صندلی ذهنیت‌ها', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 25, 100, 5, 'WEEKLY', '🪑', '#8C6BB1', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1108, 2, 'ACT088', 'ACT_SCH', 'مرور هفتگی طرحواره و ذهنیت', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 40, 160, 4, 'WEEKLY', '🧾', '#6D75B8', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1109, 2, 'ACT089', 'ACT_SCH', 'مرور ماه نیازهای هیجانی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 0, 60, 4, 'MONTHLY', '📊', '#4F9FC4', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1110, 2, 'ACT090', 'ACT_SCH', 'تمرین طرحواره رهاشدگی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🪢', '#C45B8A', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1111, 2, 'ACT091', 'ACT_SCH', 'تمرین طرحواره بی‌اعتمادی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '👁️', '#5C4B6C', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1112, 2, 'ACT092', 'ACT_SCH', 'تمرین طرحواره محرومیت هیجانی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🤍', '#9A8C98', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1113, 2, 'ACT093', 'ACT_SCH', 'تمرین طرحواره نقص و شرم', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🎭', '#8064A2', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1114, 2, 'ACT094', 'ACT_SCH', 'تمرین طرحواره انزوای اجتماعی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🏝️', '#4F7CAC', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1115, 2, 'ACT095', 'ACT_SCH', 'تمرین طرحواره وابستگی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🛟', '#769FCD', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1116, 2, 'ACT096', 'ACT_SCH', 'تمرین طرحواره آسیب‌پذیری', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '⚠️', '#BC6C25', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1117, 2, 'ACT097', 'ACT_SCH', 'تمرین طرحواره گرفتارشدگی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🧵', '#B97855', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1118, 2, 'ACT098', 'ACT_SCH', 'تمرین طرحواره شکست', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '📉', '#6B7AA1', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1119, 2, 'ACT099', 'ACT_SCH', 'تمرین طرحواره استحقاق', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '👑', '#D9A441', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1120, 2, 'ACT100', 'ACT_SCH', 'تمرین طرحواره خویشتن‌داری ناکافی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '⏳', '#E09F3E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1121, 2, 'ACT101', 'ACT_SCH', 'تمرین طرحواره اطاعت', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🙇', '#739E9B', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1122, 2, 'ACT102', 'ACT_SCH', 'تمرین طرحواره ایثار', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🕯️', '#C08497', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1123, 2, 'ACT103', 'ACT_SCH', 'تمرین طرحواره تأییدطلبی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '👏', '#D66BA0', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1124, 2, 'ACT104', 'ACT_SCH', 'تمرین طرحواره منفی‌گرایی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '⛈️', '#5964B4', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1125, 2, 'ACT105', 'ACT_SCH', 'تمرین طرحواره بازداری هیجانی', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🤐', '#547AA5', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1126, 2, 'ACT106', 'ACT_SCH', 'تمرین طرحواره معیارهای سرسختانه', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '📏', '#7A9E7E', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_activities` (`id`, `user_id`, `code`, `group_code`, `name`, `category`, `data_type`, `track_mode`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `weight`, `frequency`, `sticker`, `color`, `status`, `is_seed`, `created_at`, `updated_at`) VALUES (1127, 2, 'ACT107', 'ACT_SCH', 'تمرین طرحواره تنبیه‌گری', 'طرحواره درمانی', 'DURATION', 'DURATION', 'UNIT_MIN', 0, 20, 80, 4, 'WEEKLY', '🔨', '#8D6E63', 'ACTIVE', 1, '2026-09-12 17:22:41', '2026-09-12 17:22:41');
INSERT INTO `joma_periods` (`id`, `user_id`, `period_key`, `year`, `month`, `start_date`, `end_date`, `created_at`) VALUES (100, 3, '1405-06', 1405, 6, '1405-06-01', '1405-06-31', '2026-08-25 08:00:00');
INSERT INTO `joma_periods` (`id`, `user_id`, `period_key`, `year`, `month`, `start_date`, `end_date`, `created_at`) VALUES (120, 4, '1405-06', 1405, 6, '1405-06-01', '1405-06-31', '2026-08-25 08:00:00');
INSERT INTO `joma_periods` (`id`, `user_id`, `period_key`, `year`, `month`, `start_date`, `end_date`, `created_at`) VALUES (140, 5, '1405-06', 1405, 6, '1405-06-01', '1405-06-31', '2026-08-25 08:00:00');
INSERT INTO `joma_periods` (`id`, `user_id`, `period_key`, `year`, `month`, `start_date`, `end_date`, `created_at`) VALUES (1129, 2, '1405-06', 1405, 6, '1405-06-01', '1405-06-31', '2026-09-12 17:22:58');
INSERT INTO `joma_plans` (`id`, `user_id`, `period_id`, `period_key`, `status`, `created_at`, `updated_at`, `finalized_at`, `started_at`, `archived_at`) VALUES (101, 3, 100, '1405-06', 'RUNNING', '2026-08-25 08:05:00', '2026-08-25 08:05:00', NULL, '2026-08-25 08:10:00', NULL);
INSERT INTO `joma_plans` (`id`, `user_id`, `period_id`, `period_key`, `status`, `created_at`, `updated_at`, `finalized_at`, `started_at`, `archived_at`) VALUES (121, 4, 120, '1405-06', 'RUNNING', '2026-08-25 08:05:00', '2026-08-25 08:05:00', NULL, '2026-08-25 08:10:00', NULL);
INSERT INTO `joma_plans` (`id`, `user_id`, `period_id`, `period_key`, `status`, `created_at`, `updated_at`, `finalized_at`, `started_at`, `archived_at`) VALUES (141, 5, 140, '1405-06', 'RUNNING', '2026-08-25 08:05:00', '2026-08-25 08:05:00', NULL, '2026-08-25 08:10:00', NULL);
INSERT INTO `joma_plans` (`id`, `user_id`, `period_id`, `period_key`, `status`, `created_at`, `updated_at`, `finalized_at`, `started_at`, `archived_at`) VALUES (1130, 2, 1129, '1405-06', 'DRAFT', '2026-09-12 17:22:58', '2026-09-12 17:22:58', NULL, NULL, NULL);
INSERT INTO `joma_plan_activities` (`id`, `user_id`, `plan_id`, `period_key`, `activity_id`, `activity_code`, `name`, `category`, `frequency`, `data_type`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `target_value`, `weight`, `sticker`, `color`, `sort_order`, `snapshot_at`) VALUES (102, 3, 101, '1405-06', 0, 'DEMO1', 'مدیتیشن روزانه', 'رشد فردی', 'DAILY', 'BOOLEAN', 'UNIT_NONE', 1, 0, 0, 1, 3, '🧘', '#7C6AA8', 0, '2026-08-25 08:06:00');
INSERT INTO `joma_plan_activities` (`id`, `user_id`, `plan_id`, `period_key`, `activity_id`, `activity_code`, `name`, `category`, `frequency`, `data_type`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `target_value`, `weight`, `sticker`, `color`, `sort_order`, `snapshot_at`) VALUES (103, 3, 101, '1405-06', 0, 'DEMO2', 'ورزش هفتگی', 'رشد فردی', 'WEEKLY', 'DURATION', 'UNIT_MIN', 0, 30, 0, 30, 2, '🏃', '#4E9B94', 1, '2026-08-25 08:06:00');
INSERT INTO `joma_plan_activities` (`id`, `user_id`, `plan_id`, `period_key`, `activity_id`, `activity_code`, `name`, `category`, `frequency`, `data_type`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `target_value`, `weight`, `sticker`, `color`, `sort_order`, `snapshot_at`) VALUES (122, 4, 121, '1405-06', 0, 'DEMO1', 'مطالعه روزانه', 'رشد فردی', 'DAILY', 'NUMERIC', 'UNIT_PAGES', 10, 0, 0, 10, 3, '📚', '#C4A34A', 0, '2026-08-25 08:06:00');
INSERT INTO `joma_plan_activities` (`id`, `user_id`, `plan_id`, `period_key`, `activity_id`, `activity_code`, `name`, `category`, `frequency`, `data_type`, `unit`, `daily_target`, `weekly_target`, `monthly_target`, `target_value`, `weight`, `sticker`, `color`, `sort_order`, `snapshot_at`) VALUES (142, 5, 141, '1405-06', 0, 'DEMO1', 'پیاده‌روی', 'رشد فردی', 'WEEKLY', 'DURATION', 'UNIT_MIN', 0, 20, 0, 20, 2, '🌿', '#6B8E5A', 0, '2026-08-25 08:06:00');
INSERT INTO `joma_performance_events` (`id`, `user_id`, `plan_id`, `plan_activity_id`, `period_key`, `frequency`, `data_type`, `event_type`, `performance_date`, `actual_value`, `created_at`) VALUES (1016, 3, 101, 102, '1405-06', 'DAILY', 'BOOLEAN', 'PERFORMANCE_REGISTERED', '1405-06-19', 1, '2026-09-10 18:00:00');
INSERT INTO `joma_performance_events` (`id`, `user_id`, `plan_id`, `plan_activity_id`, `period_key`, `frequency`, `data_type`, `event_type`, `performance_date`, `actual_value`, `created_at`) VALUES (1017, 3, 101, 102, '1405-06', 'DAILY', 'BOOLEAN', 'PERFORMANCE_REGISTERED', '1405-06-20', 1, '2026-09-11 18:00:00');
INSERT INTO `joma_performance_events` (`id`, `user_id`, `plan_id`, `plan_activity_id`, `period_key`, `frequency`, `data_type`, `event_type`, `performance_date`, `actual_value`, `created_at`) VALUES (1018, 3, 101, 102, '1405-06', 'DAILY', 'BOOLEAN', 'PERFORMANCE_REGISTERED', '1405-06-21', 1, '2026-09-12 18:00:00');
INSERT INTO `joma_performance_events` (`id`, `user_id`, `plan_id`, `plan_activity_id`, `period_key`, `frequency`, `data_type`, `event_type`, `performance_date`, `actual_value`, `created_at`) VALUES (1019, 4, 121, 122, '1405-06', 'DAILY', 'NUMERIC', 'PERFORMANCE_REGISTERED', '1405-06-20', 8, '2026-09-11 18:00:00');
INSERT INTO `joma_performance_events` (`id`, `user_id`, `plan_id`, `plan_activity_id`, `period_key`, `frequency`, `data_type`, `event_type`, `performance_date`, `actual_value`, `created_at`) VALUES (1020, 5, 141, 142, '1405-06', 'WEEKLY', 'DURATION', 'PERFORMANCE_REGISTERED', '1405-06-14', 15, '2026-09-05 18:00:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1000, 3, '1405-06-15', 5, 4, 5, 4, 3, '', '2026-09-06 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1001, 3, '1405-06-16', 5, 4, 5, 4, 3, '', '2026-09-07 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1002, 3, '1405-06-17', 5, 4, 5, 4, 3, '', '2026-09-08 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1003, 3, '1405-06-18', 5, 5, 5, 5, 1, '', '2026-09-09 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1004, 3, '1405-06-19', 5, 5, 5, 5, 1, '', '2026-09-10 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1005, 3, '1405-06-20', 5, 5, 5, 5, 1, '', '2026-09-11 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1006, 3, '1405-06-21', 5, 5, 5, 5, 1, '', '2026-09-12 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1007, 4, '1405-06-15', 4, 4, 4, 4, 2, '', '2026-09-06 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1008, 4, '1405-06-16', 4, 4, 4, 4, 2, '', '2026-09-07 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1009, 4, '1405-06-17', 4, 4, 4, 4, 2, '', '2026-09-08 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1010, 4, '1405-06-18', 4, 4, 4, 4, 2, '', '2026-09-09 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1011, 4, '1405-06-19', 4, 4, 4, 3, 4, '', '2026-09-10 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1012, 4, '1405-06-20', 2, 3, 3, 3, 3, '', '2026-09-11 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1013, 4, '1405-06-21', 2, 2, 2, 2, 4, '', '2026-09-12 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1014, 5, '1405-06-14', 4, 4, 4, 3, 4, '', '2026-09-05 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1015, 5, '1405-06-15', 4, 4, 4, 3, 4, '', '2026-09-06 09:30:00');
INSERT INTO `joma_mood_records` (`id`, `user_id`, `jalali_date`, `energy`, `general_mood`, `focus`, `sleep_quality`, `stress`, `note`, `created_at`) VALUES (1128, 2, '1405-06-21', 4, 4, 3, 5, 3, '', '2026-09-12 17:22:58');

-- ---------- دادهٔ ماژول هم‌مسیر ----------
INSERT INTO `joma_hammasir_providers` (`id`, `user_id`, `status`, `title`, `created_at`, `updated_at`) VALUES (1, 2, 'ACTIVE', 'روان‌شناس بالینی', '2026-08-27 09:00:00', '2026-08-27 09:00:00');
INSERT INTO `joma_hammasir_links` (`id`, `provider_user_id`, `client_user_id`, `status`, `consent_text`, `consent_version`, `requested_at`, `accepted_at`, `declined_at`, `revoked_at`, `revoked_by_user_id`, `created_at`, `updated_at`) VALUES (1, 2, 3, 'ACTIVE', 'با ارسال این درخواست، همراه انتخاب‌شده می‌تواند مواردی را که در زیر اجازه می‌دهید مشاهده کند. شما هر زمان بخواهید می‌توانید دسترسی را کاهش دهید یا ارتباط را قطع کنید.', 'v1', '2026-08-28 09:00:00', '2026-08-28 10:00:00', NULL, NULL, NULL, '2026-08-28 09:00:00', '2026-08-28 10:00:00');
INSERT INTO `joma_hammasir_links` (`id`, `provider_user_id`, `client_user_id`, `status`, `consent_text`, `consent_version`, `requested_at`, `accepted_at`, `declined_at`, `revoked_at`, `revoked_by_user_id`, `created_at`, `updated_at`) VALUES (2, 2, 4, 'ACTIVE', 'با ارسال این درخواست، همراه انتخاب‌شده می‌تواند مواردی را که در زیر اجازه می‌دهید مشاهده کند. شما هر زمان بخواهید می‌توانید دسترسی را کاهش دهید یا ارتباط را قطع کنید.', 'v1', '2026-08-29 09:00:00', '2026-08-29 09:30:00', NULL, NULL, NULL, '2026-08-29 09:00:00', '2026-08-29 09:30:00');
INSERT INTO `joma_hammasir_links` (`id`, `provider_user_id`, `client_user_id`, `status`, `consent_text`, `consent_version`, `requested_at`, `accepted_at`, `declined_at`, `revoked_at`, `revoked_by_user_id`, `created_at`, `updated_at`) VALUES (3, 2, 5, 'ACTIVE', 'با ارسال این درخواست، همراه انتخاب‌شده می‌تواند مواردی را که در زیر اجازه می‌دهید مشاهده کند. شما هر زمان بخواهید می‌توانید دسترسی را کاهش دهید یا ارتباط را قطع کنید.', 'v1', '2026-08-30 09:00:00', '2026-08-30 09:30:00', NULL, NULL, NULL, '2026-08-30 09:00:00', '2026-08-30 09:30:00');
INSERT INTO `joma_hammasir_links` (`id`, `provider_user_id`, `client_user_id`, `status`, `consent_text`, `consent_version`, `requested_at`, `accepted_at`, `declined_at`, `revoked_at`, `revoked_by_user_id`, `created_at`, `updated_at`) VALUES (4, 2, 6, 'PENDING', 'با ارسال این درخواست، همراه انتخاب‌شده می‌تواند مواردی را که در زیر اجازه می‌دهید مشاهده کند. شما هر زمان بخواهید می‌توانید دسترسی را کاهش دهید یا ارتباط را قطع کنید.', 'v1', '2026-09-12 10:00:00', NULL, NULL, NULL, NULL, '2026-09-12 10:00:00', '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (1, 1, 'VIEW_SUMMARY', 1, 3, '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (3, 1, 'VIEW_PROGRESS', 1, 3, '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (5, 1, 'VIEW_ACTIVITY_DETAILS', 0, 3, '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (7, 1, 'VIEW_MOOD', 1, 3, '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (9, 2, 'VIEW_SUMMARY', 1, 4, '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (11, 2, 'VIEW_PROGRESS', 1, 4, '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (13, 2, 'VIEW_ACTIVITY_DETAILS', 0, 4, '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (15, 2, 'VIEW_MOOD', 0, 4, '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (17, 3, 'VIEW_SUMMARY', 1, 5, '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (19, 3, 'VIEW_PROGRESS', 0, 5, '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (21, 3, 'VIEW_ACTIVITY_DETAILS', 0, 5, '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (23, 3, 'VIEW_MOOD', 0, 5, '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (25, 4, 'VIEW_SUMMARY', 1, 6, '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (27, 4, 'VIEW_PROGRESS', 1, 6, '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (29, 4, 'VIEW_ACTIVITY_DETAILS', 0, 6, '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permissions` (`id`, `link_id`, `perm_key`, `enabled`, `changed_by_user_id`, `changed_at`) VALUES (31, 4, 'VIEW_MOOD', 0, 6, '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (2, 1, 'VIEW_SUMMARY', 0, 1, 3, 'CLIENT_REQUEST', '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (4, 1, 'VIEW_PROGRESS', 0, 1, 3, 'CLIENT_REQUEST', '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (6, 1, 'VIEW_ACTIVITY_DETAILS', 0, 0, 3, 'CLIENT_REQUEST', '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (8, 1, 'VIEW_MOOD', 0, 1, 3, 'CLIENT_REQUEST', '2026-08-28 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (10, 2, 'VIEW_SUMMARY', 0, 1, 4, 'CLIENT_REQUEST', '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (12, 2, 'VIEW_PROGRESS', 0, 1, 4, 'CLIENT_REQUEST', '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (14, 2, 'VIEW_ACTIVITY_DETAILS', 0, 0, 4, 'CLIENT_REQUEST', '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (16, 2, 'VIEW_MOOD', 0, 0, 4, 'CLIENT_REQUEST', '2026-08-29 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (18, 3, 'VIEW_SUMMARY', 0, 1, 5, 'CLIENT_REQUEST', '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (20, 3, 'VIEW_PROGRESS', 0, 0, 5, 'CLIENT_REQUEST', '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (22, 3, 'VIEW_ACTIVITY_DETAILS', 0, 0, 5, 'CLIENT_REQUEST', '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (24, 3, 'VIEW_MOOD', 0, 0, 5, 'CLIENT_REQUEST', '2026-08-30 09:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (26, 4, 'VIEW_SUMMARY', 0, 1, 6, 'CLIENT_REQUEST', '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (28, 4, 'VIEW_PROGRESS', 0, 1, 6, 'CLIENT_REQUEST', '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (30, 4, 'VIEW_ACTIVITY_DETAILS', 0, 0, 6, 'CLIENT_REQUEST', '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_permission_history` (`id`, `link_id`, `perm_key`, `previous_enabled`, `enabled`, `changed_by_user_id`, `change_source`, `changed_at`) VALUES (32, 4, 'VIEW_MOOD', 0, 0, 6, 'CLIENT_REQUEST', '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_messages` (`id`, `link_id`, `sender_user_id`, `recipient_user_id`, `jalali_date`, `body`, `is_read`, `read_at`, `created_at`) VALUES (1, 1, 3, 2, '1405-06-21', 'سلام، امروز تمرین‌ها را کامل کردم و حس بهتری داشتم.', 0, NULL, '2026-09-12 10:05:00');
INSERT INTO `joma_hammasir_messages` (`id`, `link_id`, `sender_user_id`, `recipient_user_id`, `jalali_date`, `body`, `is_read`, `read_at`, `created_at`) VALUES (2, 1, 3, 2, '1405-06-21', 'ممنون از راهنمایی شما؛ هفته‌ی خوبی بود.', 0, NULL, '2026-09-12 10:20:00');
INSERT INTO `joma_hammasir_messages` (`id`, `link_id`, `sender_user_id`, `recipient_user_id`, `jalali_date`, `body`, `is_read`, `read_at`, `created_at`) VALUES (3, 2, 4, 2, '1405-06-20', 'سلام، این هفته کمی سخت گذشت.', 1, '2026-09-11 19:00:00', '2026-09-11 18:30:00');
INSERT INTO `joma_hammasir_system_events` (`id`, `link_id`, `event_type`, `actor_user_id`, `recipient_user_id`, `is_read`, `read_at`, `created_at`) VALUES (1, 4, 'LINK_REQUESTED', 6, 2, 1, '2026-09-12 10:01:00', '2026-09-12 10:00:00');
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (1, 2, 'mode', 1, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (2, 2, 'mode_chosen', 1, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (3, 3, 'onboarding_seen', 1, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (4, 3, 'onboarding_intent', 0, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (5, 4, 'onboarding_seen', 1, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (6, 4, 'onboarding_intent', 0, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (7, 5, 'onboarding_seen', 1, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (8, 5, 'onboarding_intent', 0, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (9, 6, 'onboarding_seen', 1, NULL);
INSERT INTO `joma_hammasir_user_flags` (`id`, `user_id`, `flag_key`, `flag_value`, `updated_at`) VALUES (10, 6, 'onboarding_intent', 0, NULL);

-- ---------- پایان ----------
