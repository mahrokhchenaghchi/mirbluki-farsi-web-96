# نصب جوما روی cPanel — مرحله‌به‌مرحله

فعلاً Deploy نکنید تا خودتان تأیید کنید. این راهنما برای زمان نصب است.

## پیش‌نیاز

- هاست لینوکس با PHP 7.2 یا بالاتر (با 7.x سازگار است)
- در صورت استفاده از MySQL: ساخت دیتابیس از بخش MySQL Databases در cPanel
- نیازی به SSH، Composer، Node و npm نیست

## روش ۱ — تست بدون MySQL

1. پوشه `joma` را با File Manager در `public_html/joma` آپلود کنید.
2. فایل `config/config.php` را باز کنید و `storage` را `file` بگذارید.
3. `base_url` را `/joma` بگذارید.
4. پوشه `joma/data` مجوز 755 یا 775 داشته باشد.
5. آدرس `https://دامنه/joma/` را باز کنید.

داده در فایل `data/store.json` ذخیره می‌شود.

## روش ۲ — MySQL واقعی (پیشنهادی برای Production بعدی)

1. در cPanel یک Database و User بسازید و User را به Database وصل کنید.
2. phpMyAdmin → دیتابیس → Import → فایل `database/joma.sql`
3. `config.example.php` را کپی و نام آن را `config.php` کنید.
4. مقادیر host، name، user، pass را پر کنید.
5. `storage` را `mysql` بگذارید.
6. `base_url` را `/joma` بگذارید.
7. فایل `install.php` را بعد از نصب موفق حذف کنید.

یا به‌جای Import دستی، `install.php` را در مرورگر باز کنید و فرم را پر کنید.

## نسخه PHP

cPanel → Select PHP Version → 7.4 یا 8.x (کد برای 7.x نوشته شده)

## بکاپ

- فایل‌ها: File Manager → Compress پوشه joma
- دیتابیس: phpMyAdmin → Export
