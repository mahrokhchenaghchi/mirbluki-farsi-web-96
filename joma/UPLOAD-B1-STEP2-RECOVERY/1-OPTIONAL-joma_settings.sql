-- JOMA — B1 مرحلهٔ ۲ (بازیابی رمز)
-- این فایل فقط در صورتی لازم است که جدول joma_settings در دیتابیس نباشد.
-- ADD-ONLY و بی‌خطر است: اگر جدول هست، هیچ کاری نمی‌کند.
--
-- بررسی سریع در phpMyAdmin: جدول‌های دیتابیس جوما را ببین؛ اگر joma_settings بود،
-- این فایل را اجرا نکن. اگر نبود، اجرا کن (فقط همین یک جدول).

CREATE TABLE IF NOT EXISTS `joma_settings` (
  `setting_key` VARCHAR(64) NOT NULL,
  `setting_value` TEXT,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
