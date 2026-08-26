# 01 — تصویر کلی سیستم JOMA

## این نسخه چیست؟

نسخهٔ تحویلی هاست: پوشه `joma/`  
فناوری اجرا: **HTML + CSS + JS + PHP 7.x + MySQLi** (یا ذخیره فایل برای تست)

Frontend جدا (React/Vite) در ریشهٔ ریپو وجود دارد؛ **اجرای هاست به آن وابسته نیست.**  
`joma/preview/` فقط برای پیش‌نمایش Arena است و روی cPanel لازم نیست.

## مسئله‌ای که حل می‌کند

کاربر برای **یک ماه شمسی** برنامه می‌چیند، در طول ماه عملکرد واقعی ثبت می‌کند، حال روزانه می‌نویسد، و در پایان از **همان داده** گزارش می‌بیند. ماه بعد برنامهٔ ماه قبل را عوض نمی‌کند چون فعالیت‌های برنامه Snapshot هستند.

## اجزای اصلی (IMPLEMENTED)

| جزء | نقش | فایل اصلی |
|---|---|---|
| ورود درخواست | `?p=` | `joma/index.php` |
| Session / CSRF | نشست PHP + توکن فرم | `includes/bootstrap.php`, `helpers.php` |
| صفحات | فرم و HTML | `joma/pages/*.php` |
| ظاهر | RTL پاستلی | `assets/css/joma.css` |
| JS کمکی | hint زنده، منوی موبایل | `assets/js/joma.js` |
| منطق | کاربر، کتابخانه، برنامه، عملکرد، خلق، گزارش | `functions/joma.php` |
| ذخیره | فایل یا MySQL | `includes/store.php` |
| تقویم شمسی | امروز، ماه، هفته از شنبه | `includes/jalali.php` |
| Seed | ACT001–ACT045 | `database/library_official.json` و `database/joma.sql` |

## چیزهایی که در این نسخه کامل نیستند

| مورد | وضعیت |
|---|---|
| فرمول Achievement | IMPLEMENTED نیست — مقدار ثابت `UNSPECIFIED` |
| Overall Success / درصد موفقیت | IMPLEMENTED نیست |
| ارسال SMS/OTP | جدول `joma_otp_codes` هست — PREPARED FOR FUTURE |
| صندوق اعلان | جدول `joma_notifications` هست — UI کامل IMPLEMENTED نیست |
| پنل ادمین / تغییر سطح کاربر | IMPLEMENTED نیست |
| تماس پشتیبانی واقعی | `support_contact` در SQL خالی است |
| لوگو رسمی | مسیر آماده است؛ فایل `logo.jpg` غالباً نیست |

## دو مسیر ذخیره

```
storage=file   →  یک فایل JSON برای همه چیز (تست محلی)
storage=mysql  →  جداول joma_* با MySQLi prepared statements
```

توابع کسب‌وکار `if (store_mode() === 'mysql')` دارند؛ رفتار باید یکسان باشد.

## کاربر پیش‌فرض

`create_user` همیشه می‌سازد:

- `role_key = member`
- `access_level = 1`
- `mobile_verified = 0`

تغییر سطح از UI IMPLEMENTED نیست.
