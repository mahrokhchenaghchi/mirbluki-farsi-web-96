-- JOMA — اختیاری و ADD-ONLY
-- این فایل را فقط در صورتی اجرا کن که joma_settings در دیتابیس نباشد
-- (لازم است اگر بخواهی این کد با MySQL اجرا شود؛ در حالت فایل هیچ نیازی نیست).
-- کاملاً بی‌خطر است: فقط اگر جدول نباشد، می‌سازد. هیچ داده‌ای را عوض/پاک نمی‌کند.

CREATE TABLE IF NOT EXISTS `joma_settings` (
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
