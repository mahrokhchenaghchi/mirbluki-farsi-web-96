# جوما — نسخه PHP / MySQL

این پوشه انتقال نسخه فعلی JOMA به HTML + CSS + JS + PHP 7.x + MySQLi است.

- بدون Composer / Node / npm / SSH برای اجرا روی هاست
- کتابخانه ACT001–ACT045 از **جدول رسمی مالک محصول** است نه seed موقت
- قابلیت‌های نسخه فعلی حفظ شده‌اند
- Achievement جعلی محاسبه نمی‌شود

## اجرا

- تست بدون MySQL: `config/config.php` → `storage = file` سپس پوشه را روی PHP باز کنید.
- Production: `INSTALLATION.md`

## فایل‌ها

- `database/joma.sql` — ساخت جداول + Seed رسمی
- `database/library_official.json` — منبع کتابخانه
- `config/config.example.php` — نمونه تنظیمات
- `assets/css/joma.css` — ظاهر لوکس، پاستلی، RTL و Responsive
- `tests/run.mjs` — تست سناریوها (Node)
- `tests/run.php` — تست CLI اگر PHP روی سیستم باشد

ظاهر برنامه کارت‌محور و پاستلی است و منوی کامل + بازگشت + Breadcrumb دارد. Achievement جعلی نشان داده نمی‌شود.
