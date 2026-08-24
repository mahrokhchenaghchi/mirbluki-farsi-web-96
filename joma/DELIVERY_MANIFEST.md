# فهرست تحویل نسخه نهایی جوما (PHP / MySQL)

این فهرست از **درخت واقعی همین بسته** استخراج شده است، نه از حدس.

بسته شامل سورس قابل ویرایش است. فایل Build-only، Minified-only، یا وابسته به Arena / React / Vite / Node / Composer نیست.

## درخت واقعی

```text
joma/
├── .htaccess
├── index.php
├── install.php
├── README.md
├── INSTALLATION.md
├── INSTALLATION_FOR_DESIGNER.md
├── DELIVERY_MANIFEST.md
├── DEPLOYMENT.md
├── api/
│   └── username.php
├── assets/
│   ├── css/
│   │   └── joma.css
│   ├── js/
│   │   └── joma.js
│   └── images/
│       └── PUT_LOGO_HERE.txt
├── config/
│   ├── .htaccess
│   ├── config.example.php
│   └── config.php
├── data/
│   ├── .htaccess
│   ├── .gitkeep
│   └── sessions/
│       └── .gitkeep
├── database/
│   ├── joma.sql
│   └── library_official.json
├── functions/
│   └── joma.php
├── includes/
│   ├── bootstrap.php
│   ├── helpers.php
│   ├── jalali.php
│   ├── layout.php
│   └── store.php
└── pages/
    ├── about.php
    ├── dashboard.php
    ├── forgot.php
    ├── home.php
    ├── library.php
    ├── login.php
    ├── logout.php
    ├── mood.php
    ├── period.php
    ├── periods.php
    ├── plan.php
    ├── profile.php
    ├── register.php
    ├── reports.php
    ├── settings.php
    ├── support.php
    └── today.php
```

## توضیح فایل‌ها و پوشه‌ها

| مسیر | نقش |
|---|---|
| `index.php` | ورودی برنامه و مسیریابی صفحات |
| `install.php` | نصب‌کننده مرورگری برای ساخت جداول و `config.php` — بعد از نصب حذف شود |
| `.htaccess` | `DirectoryIndex` و `RewriteBase /joma/` |
| `INSTALLATION_FOR_DESIGNER.md` | راهنمای مرحله‌به‌مرحله برای طراح سایت |
| `DELIVERY_MANIFEST.md` | همین فهرست تحویل |
| `INSTALLATION.md` / `DEPLOYMENT.md` / `README.md` | یادداشت‌های نصب و استقرار |
| `api/username.php` | بررسی زنده بودن نام کاربری در ثبت‌نام |
| `assets/css/joma.css` | ظاهر RTL، پاستلی، ریسپانسیو |
| `assets/js/joma.js` | Validation زنده نام کاربری / رمز / موبایل / ایمیل |
| `assets/images/` | محل لوگوی رسمی: **`assets/images/logo.jpg`** — فایل لوگو هنوز تحویل نشده |
| `config/config.example.php` | نمونه تنظیمات بدون رمز واقعی |
| `config/config.php` | تنظیم پیش‌فرض تست (`storage=file`، بدون رمز واقعی). برای Production از روی example کپی و MySQL را پر کنید |
| `config/.htaccess` | جلوگیری از اجرای مستقیم PHPهای تنظیمات |
| `data/` | ذخیره فایل‌تست و Session؛ از وب بسته است |
| `database/joma.sql` | ساخت جداول + Seed رسمی ۴۵ فعالیت + نقش و مجوز |
| `database/library_official.json` | منبع کتابخانه رسمی ACT001–ACT045 |
| `functions/joma.php` | Backend: کاربر، کتابخانه، دوره، برنامه، عملکرد، خلق، گزارش |
| `includes/bootstrap.php` | شروع Session، بارگذاری تنظیمات |
| `includes/helpers.php` | CSRF، XSS escape، URL، Validation، لوگو، مجوز ظاهری |
| `includes/jalali.php` | تقویم شمسی (هفته از شنبه) |
| `includes/layout.php` | قالب، منو، بازگشت، Breadcrumb |
| `includes/store.php` | ذخیره فایل یا MySQLi (بدون PDO) |
| `pages/home.php` | صفحه معرفی |
| `pages/register.php` | ثبت‌نام + پذیرش قوانین |
| `pages/login.php` | ورود |
| `pages/forgot.php` | بازیابی رمز در حالت محلی |
| `pages/logout.php` | خروج |
| `pages/mood.php` | خلق پنج‌شاخصه با استیکر |
| `pages/dashboard.php` | داشبورد |
| `pages/plan.php` | برنامه دوره، Override، Finalize |
| `pages/today.php` | ثبت Performance روزانه / هفتگی / ماهانه |
| `pages/library.php` | کتابخانه فعالیت‌ها |
| `pages/periods.php` | فهرست دوره‌ها |
| `pages/period.php` | جزئیات و تاریخچه یک دوره |
| `pages/reports.php` | گزارش‌ها: خلاصه، فعالیت، وزن، تقویم، روند، خلق، مقایسه، جزئیات |
| `pages/profile.php` | پروفایل |
| `pages/settings.php` | تنظیمات حساب / نمایش / اعلان |
| `pages/about.php` | درباره جوما |
| `pages/support.php` | پشتیبانی (تماس هنوز تعریف نشده) |

## لوگو

- مسیر استاندارد در کد: `assets/images/logo.jpg`
- مسیر جایگزین که کد هم چک می‌کند: `public/logo.jpg` در ریشه سایت (برای این بسته لازم نیست)
- **فایل لوگوی رسمی داخل این ZIP نیست.** مالک باید `logo.jpg` را در `joma/assets/images/logo.jpg` بگذارد.

## چیزهایی که عمداً داخل ZIP نیستند

| مورد | دلیل |
|---|---|
| `preview/` و `node_modules` | فقط برای پیش‌نمایش Arena با Node بود؛ روی هاست لازم نیست |
| `tests/` | تست توسعه؛ برای نصب cPanel لازم نیست |
| `data/store.json` | داده تست کاربران آزمایشی |
| `data/sessions/sess_*` | نشست‌های محیط تست |
| رمز / API Key / اطلاعات Production | هیچ‌کدام در بسته نیست |

## تأیید استقلال اجرا

- اجرای هاست: PHP + MySQLi + فایل‌های همین پوشه
- بدون Composer، npm، Vite، React، Arena
- کتابخانه ۴۵ فعالیت از جدول رسمی مالک است
- فرمول Achievement / موفقیت کلی ساخته نشده و درصد جعلی ندارد
