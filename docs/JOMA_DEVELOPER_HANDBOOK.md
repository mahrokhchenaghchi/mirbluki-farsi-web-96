# دفترچه توسعه‌دهنده جوما (نسخه فعلی PHP)

این پرونده **نقشهٔ راه خواندن** بقیه مستندات است.  
برنامه را مثل کسی که خودش نوشته مدیریت کنید: اول اینجا، بعد پروندهٔ مربوط به همان بخش.

**قانون این مجموعه:** هر ادعا از کد واقعی `joma/` آمده است. حدس به‌عنوان پیشنهاد جدا آمده.

| برچسب | معنی |
|---|---|
| IMPLEMENTED | در کد اجرا می‌شود |
| PREPARED FOR FUTURE | جدول یا فیلد هست، منطق/UI کامل نیست |
| IMPLEMENTED نیست | در این نسخه وجود ندارد |
| RECOMMENDATION | پیشنهاد؛ قابلیت فعلی نیست |

## جوما چیست؟

جوما محصول **مستقل خودمدیریتی** است، نه سایت روانشناسی و نه نوبت‌دهی.

فلسفه در UI: **برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود**

چرخه عملی در کد:

1. کاربر ثبت‌نام می‌کند (`pages/register.php` → `create_user`).
2. کتابخانه ۴۵ فعالیت برای **همان کاربر** کپی می‌شود (`copy_seed_to_user`).
3. دوره شمسی ماه جاری ساخته می‌شود (`ensure_period`) با برنامهٔ `DRAFT`.
4. فعالیت‌ها به برنامه **Snapshot** می‌شوند (`add_plan_activity`).
5. برنامه نهایی و اجرا می‌شود (`transition_plan`: DRAFT → PLANNING → RUNNING).
6. عملکرد و خلق ثبت می‌شود.
7. گزارش از **رویداد خام** ساخته می‌شود؛ Achievement محاسبه نمی‌شود.

## از کجا شروع کنید؟

| اگر می‌خواهید… | بخوانید |
|---|---|
| تصویر کلی | `01_SYSTEM_OVERVIEW.md` |
| مسیر یک درخواست | `02_ARCHITECTURE.md` |
| جداول MySQL / فایل JSON | `03_DATABASE.md` و `04_ENTITY_RELATIONSHIPS.md` |
| لیست توابع | `05_FUNCTION_REFERENCE.md` |
| ترتیب صدا زدن توابع | `06_CALL_GRAPH.md` |
| جریان داده تا گزارش | `07_DATA_FLOW.md` |
| وضعیت برنامه و قفل | `08_WORKFLOW.md` |
| قوانین فرم‌ها | `09_VALIDATION.md` |
| ورود، CSRF، Session | `10_AUTH_SECURITY.md` |
| ۴۵ فعالیت رسمی | `11_ACTIVITY_LIBRARY.md` و `16_SEED_DATA.md` |
| ثبت عملکرد | `12_PLAN_AND_PERFORMANCE.md` |
| پنج شاخص حال | `13_MOOD.md` |
| تب‌های گزارش | `14_REPORTING.md` |
| نقش و سطح | `15_PERMISSION_SYSTEM.md` |
| «اگر بخواهم X را عوض کنم کجا بروم» | `17_DEVELOPER_MAP.md` |
| نسخه ۲ | `18_VERSION_2_ROADMAP.md` |
| آموزش اضافه کردن قابلیت | `19_CHANGE_GUIDE.md` |
| چیزهایی که نیستند / خطرناک‌اند | `20_KNOWN_LIMITATIONS.md` |

## ورود به کد در ۳۰ ثانیه

فایل ورودی: `joma/index.php`

```
مرورگر → index.php?p=نام‌صفحه
       → includes/bootstrap.php  (Session + Config + توابع)
       → maybe_mood_gate
       → pages/{نام}.php
       → HTML + CSS + JS
```

صفحات مجاز در `index.php` خط ۳–۸:  
`home, login, register, forgot, logout, mood, dashboard, plan, today, library, periods, period, reports, profile, settings, about, support`

ذخیره داده دو حالت دارد (`config/config.php` کلید `storage`):

- `file` → `joma/data/store.json` (تست)
- `mysql` → MySQLi روی جداول `joma_*` (هاست)

منطق کسب‌وکار در هر دو حالت **همان توابع** `functions/joma.php` است.

## چه چیزی را می‌توانید با خیال نسبی عوض کنید؟

رنگ و متن UI، شغل‌های فهرست، متن درباره، CSS، برچسب منو، محتوای Seed **قبل از ثبت‌نام کاربران واقعی**.

جزئیات: بخش پایانی `20_KNOWN_LIMITATIONS.md`.

## چه چیزی را بدون بررسی عوض نکنید؟

`can_register_performance`، `transition_plan`، Snapshot در `add_plan_activity`، `build_report` (Achievement)، `store_role_permissions`، CSRF/`joma_sid`، تقویم شمسی.

---

**هیچ کدی در این مرحله تغییر داده نشده است.** فقط مستندسازی.
