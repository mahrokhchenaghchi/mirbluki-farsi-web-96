# 10 — Authentication و امنیت

## Login / Logout / Session

- ورود: `user_by_username` (username یا email) + `password_verify`
- رمز: `password_hash(..., PASSWORD_DEFAULT)` معمولاً bcrypt `$2y$`
- Session کلید `user` بدون password_hash
- خروج: پاک کردن Session و `session_destroy`

Remember-me جدا IMPLEMENTED نیست.

## CSRF

- تولید: `csrf_token()` در Session
- فرم: hidden `csrf` + `joma_sid`
- چک: POST بدون توکن مطابق Session → HTTP 400 و متن «درخواست نامعتبر است.»

### چرا قبلاً در Preview شکست می‌خورد؟

1. فرم GET توکن را در Session A می‌گذاشت.
2. در iframe Preview کوکی PHPSESSID به POST نمی‌رسید (شخص‌ثالث / SameSite).
3. POST Session خالی B می‌ساخت.
4. توکن فرم ≠ Session → CSRF.

### راه‌حل فعلی در کد (بدون حذف CSRF)

- `joma_sid` در فرم و در URL وقتی کوکی Session نیست (`joma_needs_sid`)
- اگر `$_POST` خالی بود، `php://input` پارس می‌شود
- `session_write_close` قبل از Redirect
- کوکی SameSite=None در Preview/HTTPS

CSRF هنوز سرورساید است.

## SQL Injection

مسیر MySQL: `mysqli_prepare` + bind.  
Query رشته‌ای با دادهٔ کاربر در منطق اصلی دیده نمی‌شود.

## XSS

خروجی متن کاربر با `e()` = htmlspecialchars ENT_QUOTES UTF-8.  
رنگ فعالیت در بعضی `style="..."` از DB می‌آید (باید HEX باشد؛ فیلد آزاد است — ریسک اگر مقدار مخرب ذخیره شود).

## Permission و جداسازی داده

- `require_perm` قبل از بعضی صفحات
- SELECT/UPDATE با `user_id` در توابع
- ID فعالیت/برنامه دیگران در تئوری اگر حدس زده شود، شرط user_id باید مانع شود (در توابع لیست‌شده رعایت شده)

## OTP

جدول هست. ارسال/تأیید SMS: IMPLEMENTED نیست.  
`mobile_verified` همیشه 0 هنگام ساخت.

## نصب

`install.php` پسورد دیتابیس را در `config.php` می‌نویسد — بعد از نصب باید حذف شود.
