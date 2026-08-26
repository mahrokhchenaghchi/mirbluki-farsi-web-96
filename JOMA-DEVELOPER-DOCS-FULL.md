


# ===== JOMA_DEVELOPER_HANDBOOK.md =====

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



# ===== 01_SYSTEM_OVERVIEW.md =====

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



# ===== 02_ARCHITECTURE.md =====

# 02 — معماری واقعی

## دیاگرام درخواست (کد فعلی)

```
کاربر (مرورگر RTL)
  ↓  GET/POST  /joma/index.php?p=...
index.php
  ↓
includes/bootstrap.php
  ├ session_start (کوکی PHPSESSID یا joma_sid)
  ├ خواندن config.php
  ├ jalali.php + helpers.php + store.php + functions/joma.php + layout.php
  ↓
maybe_mood_gate($p)     اگر لاگین است و حال امروز نیست → mood
  ↓
pages/{p}.php
  ├ require_login / require_perm   (بعضی صفحات)
  ├ csrf_check()                   (اگر POST)
  ├ Validation (helpers یا خود صفحه)
  ├ توابع functions/joma.php
  │     ├ store_mode()==file  → store.json
  │     └ store_mode()==mysql → mysqli_prepare / joma_query
  ↓
joma_header() … HTML … joma_footer()
  ↓
CSS assets/css/joma.css
JS  assets/js/joma.js   (فقط hint؛ Submit فرم معمولی است)
  ↓
پاسخ HTML به کاربر
```

**Frontend جدا با API JSON برای صفحات اصلی IMPLEMENTED نیست.**  
صفحات PHP خودشان HTML می‌سازند. تنها API: `api/username.php` (JSON برای hint نام کاربری).

## ارتباط PHP و MySQL

فقط وقتی `storage === 'mysql'`:

1. `db()` در `store.php` با `mysqli_connect`.
2. `joma_query` / `joma_exec` با `mysqli_prepare` و `joma_stmt_bind`.
3. PDO و ORM در کد **IMPLEMENTED نیست**.

اگر اتصال قطع شود: `die('اتصال پایگاه داده برقرار نشد.')`.

## Session

در `bootstrap.php` قبل از هر صفحه:

- `session.save_path` = `joma/data/sessions`
- کوکی HttpOnly
- اگر Preview/HTTPS: SameSite=None و در HTTPS: Secure + Partitioned
- اگر کوکی نرسد: `joma_sid` از POST یا GET
- `session.use_only_cookies = 0` تا sid در URL/فرم کار کند

`$_SESSION['user']` آرایهٔ `session_user_array` است (بدون رمز).

## قالب

- کاربر لاگین: سایدبار + breadcrumb + منوی پایین موبایل (`layout.php`)
- مهمان: `public-shell` (خانه، ورود، ثبت‌نام، فراموشی، درباره)

بازگشت: `javascript:history.back()` در crumbs.

## فایل‌های Config

| فایل | نقش |
|---|---|
| `config/config.php` | تنظیم واقعی سرور |
| `config/config.example.php` | نمونه بدون رمز واقعی |
| `install.php` | ساخت جداول از SQL و نوشتن config — بعد از نصب باید حذف شود |

## امنیت لایه درخواست

- صفحات خارج از `$allowed` به `home` می‌روند.
- XSS: خروجی با `e()` = `htmlspecialchars`.
- CSRF: فیلد `csrf` در فرم‌های POST.
- دادهٔ کاربر با `user_id` در کوئری‌ها جدا می‌شود (در مسیر MySQL و فایل).



# ===== 03_DATABASE.md =====

# 03 — پایگاه داده

دو پیاده‌سازی موازی وجود دارد. Schema رسمی MySQL در `joma/database/joma.sql` است.

## حالت file

فایل: `joma/data/store.json`  
کلیدها (از `store_empty` در `store.php`):  
`users, preferences, activities, periods, plans, plan_activities, events, moods, projections, seq`

`projections` در JSON رزرو شده؛ `build_report` آن را پر نمی‌کند.

شناسه جدید: `store_next_id` روی `seq`.

## حالت mysql — جداول

پیشوند همه: `joma_`  
موتور: InnoDB، charset utf8mb4  
**Foreign Key در SQL تعریف نشده** (`FOREIGN_KEY_CHECKS=0`). رابطه منطقی است نه قید دیتابیس.

### joma_roles
- PK: `id`
- `role_key` UNIQUE، `access_level` TINYINT، `title`
- Seed: member/1، plus/2، coach/3، admin/4
- کد PHP برای چک دسترسی این جدول را **نمی‌خواند** (`store_role_permissions` ثابت است).

### joma_permissions
- PK: `id`، `perm_key` UNIQUE، `title`
- ۹ کلید: VIEW_DASHBOARD, CREATE_PLAN, EDIT_PLAN, RECORD_PERFORMANCE, VIEW_REPORT, VIEW_HISTORY, MANAGE_ACTIVITY_LIBRARY, MANAGE_USERS, ADMIN_ACCESS

### joma_role_permissions
- PK مرکب: `(role_key, perm_key)`
- member/plus/coach: ۷ مجوز اول؛ admin: هر ۹
- کد اجرا این جدول را **نمی‌خواند**.

### joma_users
- PK: `id`
- UNIQUE: `username`, `email`
- فیلدها: first_name, last_name, username, email, phone, job, password_hash, role_key (پیش‌فرض member), access_level (پیش‌فرض 1), mobile_verified (پیش‌فرض 0), created_at
- ایجاد: `create_user`
- تغییر نام/تلفن/شغل: `update_user` (username/email/role از UI عوض نمی‌شود)
- رمز: `reset_user_password`

### joma_user_preferences
- PK: `user_id`
- compact_cards, notifications_enabled
- ایجاد همزمان ثبت‌نام؛ ذخیره در تنظیمات: `save_prefs`

### joma_otp_codes — PREPARED FOR FUTURE
- PK: `id`، موبایل، code، purpose، expires_at، used_at
- هیچ تابع PHP برای INSERT/VERIFY/ارسال SMS وجود ندارد.

### joma_activities
- PK: `id`، ایندکس user_id و code
- `user_id` NULL در Seed SQL سراسری؛ پس از ثبت‌نام کپی با user_id واقعی
- فیلدها مطابق کتابخانه + is_seed, created_at, updated_at
- ایجاد/ویرایش/حذف: `save_activity` / `delete_activity` (حذف تصویر دوره‌های قبلی را پاک نمی‌کند)

### joma_periods
- PK: `id`، UNIQUE (user_id, period_key)
- period_key مثل `1405-06`، year, month, start_date, end_date (رشته شمسی ۱۰ کاراکتری)
- ایجاد: `ensure_period`

### joma_plans
- PK: `id`، UNIQUE (user_id, period_id)
- status: DRAFT / PLANNING / RUNNING / ARCHIVED
- finalized_at, started_at, archived_at
- ایجاد همراه دوره؛ تغییر وضعیت: `transition_plan`

### joma_plan_activities
- PK: `id`، UNIQUE (plan_id, activity_id)
- Snapshot: name, category, frequency, data_type, unit, daily/weekly/monthly_target, target_value, weight, sticker, color, sort_order, snapshot_at, activity_code
- افزودن فقط اگر برنامه DRAFT یا PLANNING باشد

### joma_performance_events
- PK: `id`
- ایندکس plan_id و (plan_activity_id, performance_date)
- **قید UNIQUE روزانه در SQL نیست**؛ یکتایی DAILY در PHP است
- event_type پیش‌فرض PERFORMANCE_REGISTERED
- performance_date شمسی؛ actual_value DECIMAL

### joma_mood_records
- PK: `id`، UNIQUE (user_id, jalali_date) → حداکثر یک رکورد در روز
- energy, general_mood, focus, sleep_quality, stress TINYINT
- note TEXT
- ذخیره: `save_mood` (INSERT یا UPDATE)

### joma_report_projections — PREPARED FOR FUTURE
- payload LONGTEXT، source_event_count
- `build_report` در حافظه می‌سازد و در این جدول **نمی‌نویسد**.

### joma_settings
- PK: setting_key
- Seed: support_contact='' ، product_name='جوما'
- PHP این جدول را در صفحات **نمی‌خواند** (پشتیبانی متن ثابت دارد).

### joma_notifications — PREPARED FOR FUTURE
- title, body, is_read
- UI صندوق پیام IMPLEMENTED نیست.

## تناقض مهم

جداول نقش/مجوز در SQL هستند؛ اجرای زنده از آرایه ثابت PHP است.  
جزئیات: `15_PERMISSION_SYSTEM.md`.



# ===== 04_ENTITY_RELATIONSHIPS.md =====

# 04 — موجودیت‌ها و روابط

رابطه در کد با فیلدهای `user_id` / `plan_id` / … است، نه FOREIGN KEY در MySQL.

## ERD متنی (منطق واقعی)

```
joma_roles ──┐
joma_permissions
joma_role_permissions     ⚠ در Runtime خوانده نمی‌شوند

joma_users
  ├── 1:1  joma_user_preferences
  ├── 1:N  joma_activities          (کتابخانهٔ همان کاربر)
  ├── 1:N  joma_periods
  │         └── 1:1  joma_plans
  │                    └── 1:N  joma_plan_activities   (Snapshot)
  │                               └── 1:N  joma_performance_events
  ├── 1:N  joma_mood_records        (یکتا در هر jalali_date)
  ├── 1:N  joma_report_projections  (جدول هست، نوشته نمی‌شود)
  ├── 1:N  joma_notifications       (جدول هست، UI نیست)
  └── 1:N  joma_otp_codes           (جدول هست، ارسال نیست)

joma_settings                   مستقل؛ UI نمی‌خواند
```

## موجودیت به موجودیت

### User
- هدف: حساب و هویت
- PK: id
- وابسته به او: تقریباً همهٔ داده‌های عملیاتی
- حذف کاربر در UI: IMPLEMENTED نیست

### Activity (Library)
- هدف: قالب فعالیت برای یک کاربر
- PK: id ؛ منطقاً متعلق به user_id
- ایجاد: کپی Seed یا فرم کتابخانه
- تغییر Library روی PlanActivity قدیمی اثر **ندارد** (Snapshot)

### Period
- هدف: یک ماه شمسی یک کاربر
- PK: id ؛ یکتا با (user_id, period_key)
- همیشه با یک Plan ساخته می‌شود

### Plan
- هدف: وضعیت برنامه همان دوره
- FK منطقی: user_id, period_id
- Lifecycle: DRAFT → PLANNING → RUNNING → ARCHIVED (و PLANNING→DRAFT مجاز است)

### PlanActivity
- هدف: کپی قفل‌شدنی از فعالیت برای همان ماه
- FK منطقی: user_id, plan_id, activity_id
- Override: frequency, target_value, weight روی همین ردیف

### PerformanceEvent
- هدف: یک ثبت عملکرد
- وابسته به plan و plan_activity
- حذف رویداد در UI: IMPLEMENTED نیست

### Mood
- هدف: پنج شاخص + یادداشت در یک روز شمسی
- یکتا per user+date ؛ ویرایش همان روز با UPDATE

### Role / Permission
- در SQL Seed شده
- در PHP: `store_role_permissions($role)` آرایه ثابت

### OTP / Notification / ReportProjection / Settings
- PREPARED FOR FUTURE از نظر منطق کامل برنامه



# ===== 05_FUNCTION_REFERENCE.md =====

# 05 — مرجع توابع (استخراج از کد)

قالب فشرده: **نام** — فایل:خط — ورودی → خروجی — اثر جانبی / جداول

صفحات PHP تابع جدا نیستند؛ منطق در خود فایل صفحه است.

---

## includes/helpers.php

| نام | خط | کار | ورودی | خروجی | جداول |
|---|---|---|---|---|---|
| e | 2 | XSS escape | string | string | — |
| joma_now | 6 | زمان SQL | — | `Y-m-d H:i:s` | — |
| joma_compute_base | 10 | base URL از SCRIPT_NAME | — | string مثل `/joma` یا `''` | — |
| joma_needs_sid | 23 | آیا کوکی Session نیست؟ | — | bool | — |
| joma_append_sid | 28 | افزودن joma_sid به URL | url | url | — |
| joma_redirect | 37 | Location + session_write_close | path | exit | — |
| joma_url | 45 | ساخت URL داخلی | path | string | — |
| joma_asset | 52 | URL استیت | path | string | — |
| csrf_token | 56 | توکن Session | — | hex | Session |
| csrf_field | 63 | hidden csrf + joma_sid | — | HTML | — |
| csrf_check | 68 | رد POST نامعتبر | — | die یا ادامه | — |
| current_user | 78 | Session user | — | array/null | — |
| require_login | 82 | اجبار ورود | — | redirect | — |
| has_perm | 88 | چک مجوز | perm string | bool | — |
| require_perm | 95 | اجبار مجوز | perm | die/ادامه | — |
| current_period_key | 102 | دوره انتخابی Session/GET | — | `YYYY-MM` | Session |
| set_current_period_key | 113 | ذخیره دوره | key | — | Session |
| maybe_mood_gate | 119 | اجبار Mood امروز | page | redirect؟ | moods |
| jobs_list | 129 | شغل‌ها | — | array | — |
| categories_list | 133 | دسته‌ها | — | array | — |
| frequencies_list | 137 | DAILY/WEEKLY/MONTHLY | — | map | — |
| datatypes_list | 141 | چهار نوع | — | map | — |
| units_list | 145 | واحدها + alias کوتاه | — | map | — |
| stickers_list | 162 | استیکرها | — | array | — |
| target_of | 166 | هدف بر اساس frequency | row | float | — |
| format_value | 172 | نمایش مقدار | type,value,unit | string | — |
| plan_editable | 180 | DRAFT/PLANNING | status | bool | — |
| status_label / status_badge | 184–189 | برچسب وضعیت | status | string/HTML | — |
| role_label / access_label | 194–199 | برچسب نقش/سطح | key/n | string | — |
| greeting_fa | 204 | سلام بر اساس ساعت | — | string | — |
| joma_contains | 211 | جستجوی متن | hay,needle | bool | — |
| unspecified_notice | 219 | جعبه کهربایی | html | html | — |
| empty_state | 223 | کارت خالی | title,desc,href,cta | html | — |
| joma_logo | 232 | لوگو یا «ج» | size,compact | html | فایل لوگو |
| flash_set / flash_get | 261–265 | پیام یک‌بارمصرف | — | html | Session |
| nav_items | 273 | منو | — | map | — |
| sparkline_svg | 289 | روند SVG | points | svg | — |
| is_valid_username/email/mobile | 315–323 | فرمت | string | bool | — |
| validate_registration | 327 | همه قوانین ثبت‌نام | array in | پیام یا `''` | users (taken) |
| validate_activity_input | 341 | نام/وزن/هدف | array | پیام یا `''` | — |
| validate_performance_value | 350 | نوع+مقدار | type,value | پیام یا `''` | — |

خطا: CSRF die ؛ require_perm die «دسترسی مجاز نیست.»

---

## includes/store.php

| نام | خط | کار |
|---|---|---|
| store_mode | 2 | `file` یا `mysql` |
| store_path | 6 | مسیر JSON (قابل override با JOMA_STORE_PATH) |
| store_empty | 11 | ساختار خالی |
| store_load / store_save | 26/42 | خواندن/نوشتن JSON |
| store_next_id | 48 | seq++ |
| db | 54 | mysqli یا null |
| store_role_permissions | 67 | آرایه مجوز نقش — **SQL نمی‌خواند** |
| joma_stmt_bind / fetch_all / query / query_one / exec | 76–136 | MySQLi آماده |

---

## includes/jalali.php

تبدیل میلادی↔شمسی، امروز، period_key، طول ماه، کبیسه، حدود ماه، in_period، شروع هفته شنبه، same_week، نام ماه، format نمایش، weekday، اعتبار تاریخ، ارقام فارسی `fa_num`.

---

## includes/layout.php

`joma_header($title, $crumbs, $opts)` خط 2 — HTML شروع، منو، compact از prefs.  
`joma_footer()` خط 64 — منوی موبایل + script.

---

## functions/joma.php — کسب‌وکار

| نام | خط | ورودی مهم | خروجی | جداول | امنیت |
|---|---|---|---|---|---|
| official_library | 2 | — | array 45 | JSON فایل | — |
| copy_seed_to_user | 6 | user_id | void | activities | اگر Seed دارد برمی‌گردد |
| user_by_username | 43 | username/email | user/null | users | lower |
| username_taken / email_taken | 55/68 | + except id | bool | users | — |
| create_user | 81 | array ثبت‌نام | user | users, prefs, activities | hash رمز؛ role ثابت member |
| get_user | 118 | id | user/null | users | — |
| update_user | 127 | id, patch | user | users | فقط name/phone/job در SQL |
| reset_user_password | 143 | id, pass, confirm | پیام یا `''` | users | hash |
| get_prefs / save_prefs | 161/174 | user_id, flags | array/void | preferences | — |
| session_user_array | 196 | user | array Session | — | بدون hash |
| list_user_activities | 212 | user_id | list | activities | فیلتر user |
| get_activity | 225 | id, user_id | row/null | — | از لیست کاربر |
| save_activity | 230 | user, in, id | id | activities | WHERE user_id در SQL |
| delete_activity | 283 | id, user_id | void | activities | همان |
| ensure_period | 297 | user, period_key | period+plan | periods, plans | — |
| get_plan_by_period | 343 | user, period_id | plan | plans | — |
| list_periods | 352 | user_id | list+status | periods+plans | ماه جاری را می‌سازد |
| get_plan | 374 | plan_id, user_id | plan | plans | user_id |
| list_plan_activities | 383 | plan_id, user_id | list | plan_activities | — |
| add_plan_activity | 394 | user, plan, activity, over | پیام یا `''` | plan_activities | قفل + تکراری + target |
| update_plan_activity | 449 | user, plan, pa_id, patch | پیام | plan_activities | قفل |
| remove_plan_activity | 474 | user, plan, pa_id | پیام | plan_activities | قفل |
| can_transition | 490 | from, to | bool | — | — |
| transition_plan | 500 | user, plan, next | پیام | plans | نقشه وضعیت + حداقل ۱ فعالیت |
| list_events | 527 | plan_id, user_id | list | events | — |
| can_register_performance | 537 | plan, pa, date, value, existing | پیام یا `''` | — | قوانین DAILY |
| register_performance | 555 | user, plan, pa, date, value | پیام | events | صدا می‌زند can_* |
| events_for / sum_actual / weekly_actual / displayed_actual / has_daily_registration | 587–613 | — | list/number/bool | — | — |
| get_mood / list_moods / save_mood | 618–639 | — | row/list/void | mood | یکتا روز |
| weight_sum | 687 | acts | int | — | — |
| build_report | 693 | user, plan | array گزارش | plan_act, events, moods | achievement ثابت |

---

## JavaScript `assets/js/joma.js`

IIFE بدون export.

- منوی `data-more-toggle`
- hint username + `fetch api/username.php`
- hint phone / email / password
- کلیک `.moodbtn` (صفحه Mood بیشتر با radio CSS کار می‌کند)

Submit فرم‌ها JS نیست.

## api/username.php

GET `u` → JSON `{ok: bool}` از `!username_taken`.



# ===== 06_CALL_GRAPH.md =====

# 06 — Call Graph واقعی

صفحه PHP معمولاً «Handler» جدا ندارد؛ خود `pages/*.php` Handler است.

## Registration

```
pages/register.php (POST)
  → csrf_check()
  → validate_registration($in)
       → is_valid_username / username_taken
       → is_valid_email_addr / email_taken
       → is_valid_iran_mobile
       → jobs_list
  → create_user($in)
       → password_hash
       → INSERT user + preferences
       → copy_seed_to_user
            → official_library()  [JSON]
            → INSERT 45 activities
       → get_user
  → session_user_array
  → joma_redirect(mood)
```

JS همزمان (اجباری نیست): `assets/js/joma.js` → `api/username.php` → `username_taken`

## Login

```
pages/login.php (POST)
  → csrf_check
  → user_by_username(identifier)   [username یا email]
  → password_verify
  → session_user_array
  → copy_seed_to_user   (اگر Seed نبود)
  → redirect mood
```

اگر Session خالی باشد: فرم خالی GET دوباره.  
اگر CSRF ببرد: متن «درخواست نامعتبر است».

## Logout

```
pages/logout.php
  → $_SESSION = array()
  → session_destroy
  → redirect login
```

## Mood

```
index.php → maybe_mood_gate  [اگر لاگین و بدون حال امروز]
pages/mood.php
  GET: get_mood
  POST: csrf_check → محدوده 1..5 برای ۵ شاخص → save_mood → redirect dashboard
```

## Activity Library

```
pages/library.php  require_perm(MANAGE_ACTIVITY_LIBRARY)
  POST save → validate_activity_input → save_activity
  POST toggle → get_activity → save_activity (status)
  POST del → delete_activity
  GET list → list_user_activities + فیلتر PHP
```

## Period

```
pages/periods.php
  POST year/month → ensure_period → redirect period یا dashboard
  GET list_periods → ensure_period(ماه جاری) + فهرست

pages/period.php  require_perm(VIEW_HISTORY)
  POST archive → transition_plan(ARCHIVED)
  GET build_report
```

## Plan / Finalize

```
pages/plan.php  require_perm(EDIT_PLAN)
  ensure_period(current_period_key)
  POST add → get_activity → add_plan_activity (Override)
  POST savepa → update_plan_activity
  POST up → جابجایی sort_order
  POST del → remove_plan_activity
  POST next → transition_plan(PLANNING یا RUNNING یا ARCHIVED)
```

`CREATE_PLAN` در هیچ صفحه `require_perm` نشده است.

## Performance

```
pages/today.php  require_perm(RECORD_PERFORMANCE)
  POST → register_performance
           → list_events
           → can_register_performance
                → validate_performance_value
                → jalali_is_valid / jalali_in_period
                → duplicate DAILY
           → INSERT event
```

نمایش مقدار: `displayed_actual` (WEEKLY جمع همان هفته شمسی؛ وگرنه جمع کل رویدادهای برنامه).

## Reporting

```
pages/reports.php  require_perm(VIEW_REPORT)
  → build_report
       → list_plan_activities
       → list_events
       → list_moods
       → sum_actual / weight_sum
       → achievement = 'UNSPECIFIED'
  تب‌ها فقط همان آرایه را نشان می‌دهند (بدون جدول projection)
```

## Permissions

```
require_perm($key)
  → require_login
  → has_perm
       → store_role_permissions(role_key)   آرایه ثابت، نه SQL
```

## Profile / Settings

```
profile.php  فقط خواندن $_SESSION['user']
settings.php POST
  → csrf_check
  → is_valid_iran_mobile / jobs_list
  → update_user
  → save_prefs
```



# ===== 07_DATA_FLOW.md =====

# 07 — جریان داده از ثبت‌نام تا گزارش

```
Registration
  ↓ User (role=member, level=1) + 45 Activity کپی‌شده
Period (ماه شمسی) + Plan DRAFT
  ↓ انتخاب از Library
PlanActivity Snapshot (+ Override اختیاری)
  ↓ Finalize DRAFT→PLANNING سپس PLANNING→RUNNING
PerformanceEvent(ها) + MoodRecord(ها)
  ↓ build_report در حافظه
  actual جمع رویداد | weight از Snapshot | achievement=UNSPECIFIED
  ↓ HTML تب‌های گزارش
```

## مرحله به مرحله

### 1. Registration
- ورودی: نام، نام‌خانوادگی، username، email، phone، job، password، accept
- تولید: ردیف User، Preferences، ۴۵ Activity با `user_id`
- ذخیره: `joma_users` / `store.json`
- بعدی: Session و صفحه Mood

### 2. User
- در Session می‌ماند (بدون hash)
- هر کوئری عملیاتی با `user_id` محدود می‌شود

### 3. Period
- کلید: `YYYY-MM` شمسی از `jalali_period_key`
- تولید: start/end/days از `jalali_period_bounds`
- ذخیره: periods + plan DRAFT

### 4. Plan
- ورودی: وضعیت
- قفل ویرایش بعد از RUNNING (جز بایگانی)

### 5. Activity Library → PlanActivity
- ورودی: activity_id + frequency/target/weight اختیاری
- تولید: کپی نام، رنگ، استیکر، data_type، واحد، سه هدف، و `target_value` متناسب تناوب
- `snapshot_at` زمان کپی
- Library بعدی این ردیف را عوض نمی‌کند

### 6. Finalize / Start
- PLANNING: `finalized_at`
- RUNNING: `started_at`
- بدون حداقل یک PlanActivity رد می‌شود

### 7. Performance
- ورودی: تاریخ شمسی داخل دوره + مقدار
- تولید: Event با actual_value
- DAILY: یک Event در همان تاریخ برای همان PlanActivity
- WEEKLY/MONTHLY: چند Event مجاز؛ جمع در نمایش/گزارش

### 8. Aggregation
- `sum_actual`: جمع actual_value رویدادهای همان فعالیت
- گزارش ماه: همه Eventهای همان plan
- نمایش امروز برای WEEKLY: `weekly_actual` (همان هفته از شنبه)

### 9. Achievement / Weight
- Weight: عدد ذخیره‌شده Snapshot ؛ نوار وزن = سهم از جمع وزن‌ها (نه درصد موفقیت)
- Achievement: رشته `UNSPECIFIED` — **فرمول IMPLEMENTED نیست**
- Overall Success: `UNSPECIFIED`

### 10. Report UI
- از خروجی `build_report`؛ جدول `joma_report_projections` پر نمی‌شود



# ===== 08_WORKFLOW.md =====

# 08 — Workflow و Lifecycle

## User

```
ثبت‌نام → Active در عمل (وضعیت جدا در جدول نیست)
```

- `mobile_verified` همیشه 0 در ایجاد
- حذف حساب: IMPLEMENTED نیست
- فراموشی رمز: `pages/forgot.php` بدون ایمیل واقعی؛ مستقیم hash جدید

## Plan

مجاز در `can_transition`:

| از | به |
|---|---|
| DRAFT | PLANNING, ARCHIVED |
| PLANNING | RUNNING, DRAFT, ARCHIVED |
| RUNNING | ARCHIVED |
| ARCHIVED | هیچ |

نهایی‌سازی/شروع اجرا اگر فعالیت نباشد خطا می‌دهد.

UI برنامه (`plan.php`): دکمه DRAFT→PLANNING و PLANNING→RUNNING و در RUNNING بایگانی.  
بایگانی از `period.php` هم هست.

## PlanActivity

1. ساخته می‌شود وقتی کاربر از Library اضافه می‌کند و plan_editable (DRAFT|PLANNING).
2. Snapshot فیلدهای نمایش و هدف/وزن.
3. Override همان لحظه یا بعد با `update_plan_activity`.
4. پس از RUNNING ویرایش/حذف از برنامه قفل است.
5. تغییر بعدی Library روی این ردیف اثر ندارد.

## PerformanceEvent

- فقط Plan RUNNING
- تاریخ باید داخل period_key باشد
- حذف از UI نیست

## Mood

- یک ردیف در روز؛ ذخیره دوباره UPDATE است
- الزام «هر پنج شاخص» در `mood.php` قبل از save

## Period

- با `ensure_period` همیشه Plan هم دارد
- `list_periods` ماه جاری را اگر نباشد می‌سازد



# ===== 09_VALIDATION.md =====

# 09 — Validation کامل (از کد)

علامت‌ها: C = Client JS (`joma.js`) ؛ S = Server PHP

## ثبت‌نام (`validate_registration` + صفحه + JS)

| فیلد | Required | C | S |
|---|---|---|---|
| first_name / last_name | بله | HTML required | خالی نباشد |
| username | بله | طول ۳–۲۰، شروع حرف انگلیسی، `[A-Za-z0-9._]`، fetch یکتا | regex `^[a-zA-Z][a-zA-Z0-9._]{2,19}$` + `username_taken` |
| email | بله | الگوی ساده `@` و نقطه | `filter_var FILTER_VALIDATE_EMAIL` + `email_taken` |
| phone | بله | `^09[0-9]{9}$` | همان |
| job | بله | select | باید عضو `jobs_list()` باشد |
| password | بله | حداقل ۶ | `strlen >= 6` |
| confirm | بله | برابر رمز | برابر رمز |
| accept | بله | checkbox required | `empty(accept)` رد |

یکتایی username/email فقط سمت سرور قطعی است (JS ممکن است fail شود).

## ورود (`login.php`)

- identifier و password خالی: «نام کاربری و رمز عبور را وارد کنید.»
- کاربر نیست: «حسابی با این نام کاربری یا ایمیل پیدا نشد.»
- رمز غلط: «نام کاربری/ایمیل یا رمز عبور نادرست است.»
- identifier برای جستجو `strtolower`
- HTML required روی فیلدها

## فراموشی رمز

- رمز ≥ ۶ و برابر تکرار
- identifier باید کاربر موجود باشد

## فعالیت (`validate_activity_input`)

- name خالی نباشد
- weight ≥ 0
- هدف تناوب فعلی `> 0` (daily یا weekly یا monthly بر اساس frequency)
- Client: HTML required روی name

**نکته:** بعضی Seedها daily_target=0 با frequency WEEKLY/MONTHLY دارند. Validation هنگام **ذخیره فرم کتابخانه** هدف تناوب انتخاب‌شده را چک می‌کند، نه هر سه هدف.

## عملکرد (`validate_performance_value` + `can_register_performance`)

| نوع | قانون |
|---|---|
| همه | عدد باشد، منفی نباشد |
| RATING | ۱ تا ۵ |
| BOOLEAN | دقیقاً 0 یا 1 |
| تاریخ | `jalali_is_valid` و داخل دوره |
| پلن | status باید RUNNING |
| DAILY | تکراری همان plan_activity + همان تاریخ رد |

Client: `required` روی عدد؛ BOOLEAN/RATING با radio.

## Mood

- هر پنج مقدار ۱..۵ وگرنه «هر پنج شاخص را انتخاب کنید.»
- یادداشت اختیاری
- یکتایی روز در DB/JSON

## تنظیمات

- موبایل اگر پر باشد باید الگوی ایران باشد
- شغل از فهرست

## وزن در برنامه

- در `add_plan_activity` / `update_plan_activity`: weight ≥ 0 ، target > 0 ، frequency یکی از DAILY/WEEKLY/MONTHLY



# ===== 10_AUTH_SECURITY.md =====

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



# ===== 11_ACTIVITY_LIBRARY.md =====

# 11 — منطق Activity Library

## نقش

**Activity Library** = قالب‌های فعالیت **مالک همان کاربر**.  
بعد از ثبت‌نام ۴۵ ردیف رسمی با `user_id` کاربر و `is_seed=1` کپی می‌شود.

**PlanActivity** = Snapshot همان قالب برای **یک دوره/برنامه مشخص**.

```
Library  = Template قابل ویرایش برای ماه‌های بعد
PlanActivity = کپی یخ‌زده (+ Override) برای همان ماه
```

## اگر امروز وزن ورزش را از ۵ به ۳ در Library عوض کنم؟

- ردیف `joma_activities` همان کاربر عوض می‌شود.
- `joma_plan_activities` ماه قبل **عوض نمی‌شود** چون weight آنجا جدا ذخیره شده.
- برنامه‌های DRAFT/PLANNING که از قبل Snapshot گرفته‌اند هم عوض نمی‌شوند مگر کاربر حذف کند و دوباره اضافه کند.
- فقط اضافه کردن **جدید** به برنامه از این به بعد وزن ۳ را به‌عنوان پیش‌فرض می‌گیرد.

## فیلدهای Library در کد

از JSON/SQL: code, group_code, name, category, data_type, track_mode, unit, daily_target, weekly_target, monthly_target, weight, frequency, sticker, color, status

`save_activity` مقدار `track_mode` را برابر `data_type` می‌گذارد.

فعالیت کاربرساز: `code = ACTU{id}` ، `group_code = ACT_USR` ، `is_seed=0`.

## UI کتابخانه

فایل: `pages/library.php`  
مجوز: `MANAGE_ACTIVITY_LIBRARY` (در Runtime به member هم داده می‌شود)

کارها: جستجو، فیلتر دسته/تناوب، ایجاد، ویرایش، فعال/غیرفعال، حذف با confirm.

حذف Library رویدادها و Snapshot دوره قبل را پاک نمی‌کند.

لیست کامل ۴۵ مورد: `16_SEED_DATA.md`.



# ===== 12_PLAN_AND_PERFORMANCE.md =====

# 12 — برنامه و عملکرد (از کد، بدون حدس)

## Override هنگام افزودن

`add_plan_activity($user_id, $plan, $activity, $over)`

- frequency خالی → frequency کتابخانه
- target خالی → `target_of` بر اساس frequency (daily/weekly/monthly_target)
- weight خالی → weight کتابخانه
- target باید > 0 وگرنه خطا (پس فعالیت با هدف ۰ بدون Override اضافه نمی‌شود)

## قفل

`plan_editable` = DRAFT یا PLANNING  
RUNNING: متن قفل + لینک ثبت عملکرد

## قوانین Performance — دقیقاً `can_register_performance`

### مشترک
- مقدار عددی و نامنفی
- RATING ∈ [1,5]
- BOOLEAN ∈ {0,1}
- تاریخ شمسی معتبر
- `plan.status === 'RUNNING'` وگرنه: «فقط در دوره در حال اجرا می‌توان عملکرد ثبت کرد.»
- تاریخ داخل `period_key`

### DAILY
- اگر Event دیگری با همان `plan_activity_id` و همان `performance_date` باشد → رد  
  «برای این فعالیت روزانه، در این تاریخ قبلاً عملکرد ثبت شده است.»
- یعنی **حداکثر یک ثبت در هر روز شمسی** برای آن فعالیت
- روز بعد مجاز است
- قید UNIQUE در SQL برای این قانون **نیست**؛ فقط PHP

### WEEKLY
- در `can_register_performance` محدودیت تعداد در هفته **نیست**
- چند ثبت در هفته (حتی چند ثبت در یک روز) از این تابع رد نمی‌شود
- UI امروز برای WEEKLY فرم را هر روز نشان می‌دهد مگر plan قفل باشد
- جمع نمایش: `weekly_actual` فقط Eventهای **همان هفته شمسی** (شروع شنبه `jalali_week_start`)
- گزارش ماه: `sum_actual` همه Eventهای آن فعالیت در کل دوره

### MONTHLY
- محدودیت تعداد در ماه در قوانین ثبت **نیست**
- چند Event در ماه مجاز
- Aggregation گزارش: جمع همه actual_value

### مقدار نمایش «امروز»
`displayed_actual`: اگر frequency===WEEKLY همان هفته، وگرنه جمع کل Eventهای plan (برای DAILY و MONTHLY).

## تاریخچه Event
در `today.php` داخل `<details>` لیست تاریخ‌ها. حذف Event IMPLEMENTED نیست.



# ===== 13_MOOD.md =====

# 13 — Mood

## پنج شاخص (کد `pages/mood.php`)

| کلید فرم POST | ستون ذخیره | عنوان UI | استیکر ۱→۵ |
|---|---|---|---|
| energy | energy | انرژی | 😴 😐 🙂 😄 ⚡ |
| general | general_mood | حال عمومی | 😞 😐 🙂 😊 🤩 |
| focus | focus | تمرکز | 🌫️ 😐 🙂 🎯 🧠 |
| sleep | sleep_quality | کیفیت خواب | 😫 😐 🙂 😴 ✨ |
| stress | stress | سطح استرس | 😌 🙂 😐 😟 😣 |

مقدار مجاز: عدد صحیح **۱ تا ۵** (هر پنج‌تا اجباری).

یادداشت: `note` اختیاری.

## ذخیره

- جدول `joma_mood_records` یا آرایه `moods`
- یکتا: user + jalali_date (`jalali_today()`)
- ذخیره مجدد همان روز: UPDATE
- نوع ستون‌ها TINYINT ؛ note TEXT

## محدودیت

- Gate: اگر لاگین باشد و حال امروز نباشد، به‌جز صفحات skip به mood می‌رود  
  skip: mood, logout, about, login, register, forgot, home
- چند رکورد در یک روز از مسیر عادی ساخته نمی‌شود

## گزارش

- تب خلق: اگر Mood باشد، میانگین هر شاخص (جمع/تعداد، دامنه ۱–۵) به‌صورت نوار عرض `avg/5*100` — این **نوار مقیاس نمره است نه Achievement**
- لیست روزها با پنج عدد و note
- تقویم: گل 🌸 اگر آن روز Mood دارد
- همبستگی Mood با Performance: IMPLEMENTED نیست

## نمودار خطی جدا per metric مثل React Recharts

در PHP: نوار میانگین + لیست. نمودار Recharts IMPLEMENTED نیست.



# ===== 14_REPORTING.md =====

# 14 — Reporting Engine

## کد واقعی، نه دیاگرام ایده‌آل

```
list_plan_activities + list_events + list_moods
        ↓
build_report()     functions/joma.php حدود خط 693
        ↓
برای هر PlanActivity:
   events فیلترشده
   actual = sum(actual_value)
   event_count
   achievement = 'UNSPECIFIED'   ← محاسبه نمی‌شود
        ↓
calendar[] برای هر روز ماه: event_count, actual_total, has_mood
weight_sum = جمع weight اسنپ‌شات‌ها
overall_success = 'UNSPECIFIED'
        ↓
pages/reports.php  هشت تب HTML
```

`joma_report_projections` نوشته نمی‌شود.

## تب‌های UI (همه از همان آرایه)

| تب GET `tab` | برچسب | داده واقعی |
|---|---|---|
| overview | خلاصه | تعداد رویداد، تعداد فعالیت، تعداد روز خلق + اخطار UNSPECIFIED |
| acts | فعالیت‌ها | نام، تناوب، actual / target |
| weight | وزن | وزن Snapshot و نوار سهم از جمع وزن — **نه درصد موفقیت** |
| cal | تقویم | شنبه شروع؛ ● رویداد ؛ 🌸 خلق |
| trend | روند | sparkline جمع actual روزهایی که Event دارند |
| mood | خلق | میانگین شاخص‌ها + لیست |
| cmp | مقایسه | تعداد رویداد/فعالیت دو دوره — بدون درصد |
| det | جزئیات | متن «تحقق (تعریف‌نشده)» + لیست Event خام |

هیچ تبی Achievement عددی جعلی نشان نمی‌دهد.

## آیا Achievement محاسبه می‌شود؟

**خیر.** مقدار ثابت رشته `'UNSPECIFIED'`.

## آیا Overall Success محاسبه می‌شود؟

**خیر.** همان.

## نمودارها

- روند: SVG `sparkline_svg` در helpers
- وزن/خلق: CSS bar
- Chart کتابخانه JS جدا IMPLEMENTED نیست



# ===== 15_PERMISSION_SYSTEM.md =====

# 15 — Role / Access Level / Permission

## پاسخ مستقیم به سؤال «آیا درجه‌بندی واقعی است؟»

| سؤال | پاسخ از کد |
|---|---|
| آیا Role داریم؟ | فیلد `role_key` روی User هست. پیش‌فرض `member`. SQL چهار نقش Seed دارد. |
| آیا Access Level داریم؟ | فیلد `access_level` TINYINT هست. پیش‌فرض `1`. SQL: 1 عضو، 2 پلاس، 3 مربی، 4 مدیر. |
| آیا Level در Permission Check استفاده می‌شود؟ | **خیر.** `has_perm` فقط `store_role_permissions($u['role_key'])` را می‌بیند. `access_level` فقط نمایش پروفایل است. |
| آیا می‌توان User A را Level 1 و B را Level 2 کرد با امکانات متفاوت از UI؟ | **UI IMPLEMENTED نیست.** با UPDATE دستی DB روی `role_key` می‌توان (اگر Runtime را به SQL وصل کنید هنوز نه — Runtime آرایه ثابت است). الان member/plus/coach در PHP **یکسان**اند. فقط `admin` دو مجوز اضافه دارد. |
| آیا Admin می‌تواند Level را عوض کند؟ | پنل ادمین IMPLEMENTED نیست. |
| آیا Permissionها Database-driven در Runtime هستند؟ | جدول هست. Runtime: **خیر** (آرایه PHP). |
| آیا برای هر Role در UI مجموعه Permission تعریف می‌شود؟ | IMPLEMENTED نیست. |

## Runtime واقعی (`store_role_permissions`)

```
member, plus, coach:
  VIEW_DASHBOARD, CREATE_PLAN, EDIT_PLAN, RECORD_PERFORMANCE,
  VIEW_REPORT, VIEW_HISTORY, MANAGE_ACTIVITY_LIBRARY

admin: همان + MANAGE_USERS, ADMIN_ACCESS
```

صفحات:

| مجوز | کجا require می‌شود |
|---|---|
| VIEW_DASHBOARD | dashboard.php |
| EDIT_PLAN | plan.php |
| RECORD_PERFORMANCE | today.php |
| MANAGE_ACTIVITY_LIBRARY | library.php |
| VIEW_REPORT | reports.php |
| VIEW_HISTORY | period.php |
| CREATE_PLAN | **هیچ صفحه** |
| MANAGE_USERS / ADMIN_ACCESS | **هیچ صفحه** |

نتیجه عملی V1: هر عضو ثبت‌نام‌شده به کتابخانه، برنامه، عملکرد و گزارش دسترسی دارد.

## تفکیک آمادگی

| لایه | Role | Level | Permission matrix | Admin UI |
|---|---|---|---|---|
| Database | Ready (Seed) | Ready (ستون) | Ready (جداول) | — |
| Backend PHP Runtime | نیمه‌کاره (ثابت) | ذخیره می‌شود، چک نمی‌شود | آرایه ثابت | نیست |
| UI تغییر نقش | نیست | نمایش در پروفایل | نیست | نیست |

**Backend Ready (جزئی) / Database Ready / UI Ready نیست / Future-ready برای ماتریس SQL**



# ===== 16_SEED_DATA.md =====

# 16 — Seed Data واقعی (حدس زده نشده)

منبع: `joma/database/library_official.json` (با `joma.sql` هم‌خوان).  
ID عددی Auto Increment است؛ شناسه پایدار **Code** است.

TrackMode در Seed برابر DataType است.

| Code | Name | Category | DataType | Unit | Daily | Weekly | Monthly | Weight | Frequency | Sticker | Color | Group |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| ACT001 | ورزش | سلامت جسم | DURATION | UNIT_MIN | 30 | 150 | 600 | 5 | DAILY | 🏃 | #E85D75 | ACT_HEA |
| ACT002 | نوشیدن آب | سلامت جسم | NUMERIC | UNIT_GLASS | 6 | 42 | 180 | 4 | DAILY | 💧 | #4F9FC4 | ACT_HEA |
| ACT003 | پیاده‌روی | سلامت جسم | DURATION | UNIT_MIN | 20 | 100 | 400 | 4 | DAILY | 🚶 | #5AA469 | ACT_HEA |
| ACT004 | مصرف میوه | سلامت جسم | NUMERIC | UNIT_TIMES | 2 | 14 | 60 | 3 | DAILY | 🍎 | #D95D5D | ACT_HEA |
| ACT005 | خواب کافی | خواب و استراحت | DURATION | UNIT_HOUR | 7 | 49 | 210 | 5 | DAILY | 🌙 | #5964B4 | ACT_SLE |
| ACT006 | ساعت خواب منظم | خواب و استراحت | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | ⏰ | #6D75B8 | ACT_SLE |
| ACT007 | مدیتیشن | سلامت روان | DURATION | UNIT_MIN | 10 | 50 | 200 | 4 | DAILY | 🧘 | #7C6CE7 | ACT_MEN |
| ACT008 | تمرین تنفس | سلامت روان | DURATION | UNIT_MIN | 5 | 35 | 150 | 4 | DAILY | 🌬️ | #769FCD | ACT_MEN |
| ACT009 | ثبت حال روزانه | سلامت روان | RATING | UNIT_SCORE | 1 | 7 | 30 | 5 | DAILY | 📓 | #E9A23B | ACT_MEN |
| ACT010 | مطالعه بدون حواس‌پرتی | تمرکز و ذهن | DURATION | UNIT_MIN | 30 | 150 | 600 | 5 | DAILY | 🎯 | #4F7CAC | ACT_FOC |
| ACT011 | زمان بدون موبایل | تمرکز و ذهن | DURATION | UNIT_MIN | 30 | 150 | 600 | 4 | DAILY | 📵 | #5B8E7D | ACT_FOC |
| ACT012 | نوشتن برنامه روزانه | تمرکز و ذهن | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 📝 | #7C8C6C | ACT_FOC |
| ACT013 | مطالعه | یادگیری | DURATION | UNIT_MIN | 30 | 150 | 600 | 4 | DAILY | 📚 | #D99A2B | ACT_GRO |
| ACT014 | یادگیری مهارت جدید | رشد فردی | DURATION | UNIT_MIN | 20 | 100 | 400 | 4 | DAILY | 🛠️ | #5AA469 | ACT_GRO |
| ACT015 | نوشتن اهداف | رشد فردی | BOOLEAN | UNIT_NONE | 1 | 3 | 4 | 4 | WEEKLY | ⭐ | #8C6BB1 | ACT_GRO |
| ACT016 | گفت‌وگوی باکیفیت با همسر | روابط | DURATION | UNIT_MIN | 15 | 105 | 450 | 5 | DAILY | 💑 | #D66BA0 | ACT_REL |
| ACT017 | قدردانی از همسر | روابط | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🙏 | #C7D4A0 | ACT_REL |
| ACT018 | قرار دونفره | روابط | DURATION | UNIT_HOUR | 0 | 2 | 8 | 5 | WEEKLY | ☕ | #C06C84 | ACT_REL |
| ACT019 | تماس با خانواده | خانواده | DURATION | UNIT_MIN | 0 | 30 | 120 | 3 | WEEKLY | 📞 | #C7D4A0 | ACT_FAM |
| ACT020 | دیدار با خانواده | خانواده | DURATION | UNIT_HOUR | 0 | 2 | 8 | 4 | WEEKLY | 🏠 | #B97855 | ACT_FAM |
| ACT021 | نوشتن سه نکته مثبت | ذهن‌آگاهی | NUMERIC | UNIT_COUNT | 3 | 21 | 90 | 4 | DAILY | ✨ | #6D8B74 | ACT_MIN |
| ACT022 | تمرین شکرگزاری | ذهن‌آگاهی | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🌸 | #9A8C98 | ACT_MIN |
| ACT023 | زمان شخصی | مراقبت از خود | DURATION | UNIT_MIN | 20 | 140 | 600 | 4 | DAILY | 🛁 | #C08497 | ACT_SEL |
| ACT024 | انجام یک فعالیت لذت‌بخش | مراقبت از خود | BOOLEAN | UNIT_NONE | 1 | 3 | 12 | 3 | WEEKLY | 🎨 | #B565A7 | ACT_SEL |
| ACT025 | اولویت‌بندی کارهای روز | بهره‌وری | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | ✅ | #E09F3E | ACT_PRO |
| ACT026 | انجام مهم‌ترین کار روز | بهره‌وری | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 5 | DAILY | 🔥 | #D9A441 | ACT_PRO |
| ACT027 | مرور هفتگی | بهره‌وری | DURATION | UNIT_MIN | 0 | 30 | 120 | 4 | WEEKLY | 📅 | #4F7CAC | ACT_PRO |
| ACT028 | خواندن کتاب | یادگیری | DURATION | UNIT_MIN | 20 | 140 | 600 | 4 | DAILY | 📖 | #B07D3C | ACT_LEA |
| ACT029 | تکمیل یک فصل کتاب | یادگیری | NUMERIC | UNIT_COUNT | 0 | 1 | 4 | 3 | WEEKLY | 📗 | #A86F52 | ACT_LEA |
| ACT030 | خواندن یک کتاب کامل | یادگیری | NUMERIC | UNIT_COUNT | 0 | 0 | 1 | 5 | MONTHLY | 📘 | #D99A2B | ACT_MON |
| ACT031 | یک روز کامل بدون شبکه اجتماعی | سبک زندگی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 4 | MONTHLY | 📵 | #5B8E7D | ACT_MON |
| ACT032 | مرور ماه و ارزیابی شخصی | رشد فردی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 5 | MONTHLY | 🪞 | #7C6CE7 | ACT_MON |
| ACT033 | تعیین اهداف ماه آینده | رشد فردی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 5 | MONTHLY | 🧭 | #4F7CAC | ACT_MON |
| ACT034 | غذای سالم | سلامت جسم | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🥗 | #6A994E | ACT_HEA |
| ACT035 | کاهش مصرف قند | سلامت جسم | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🍬 | #BC6C25 | ACT_HEA |
| ACT036 | کشش بدن | سلامت جسم | DURATION | UNIT_MIN | 10 | 50 | 200 | 3 | DAILY | 🤸 | #76A5AF | ACT_HEA |
| ACT037 | نوشتن افکار و احساسات | سلامت روان | DURATION | UNIT_MIN | 10 | 50 | 200 | 4 | DAILY | 💭 | #8064A2 | ACT_MEN |
| ACT038 | فاصله گرفتن آگاهانه هنگام تنش | سلامت روان | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 5 | DAILY | 🌿 | #739E9B | ACT_MEN |
| ACT039 | گفت‌وگوی بدون موبایل | روابط | DURATION | UNIT_MIN | 15 | 105 | 450 | 4 | DAILY | 💬 | #C45B8A | ACT_REL |
| ACT040 | یک کار در هر لحظه | تمرکز و ذهن | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🎧 | #547AA5 | ACT_FOC |
| ACT041 | خاموش‌کردن صفحه‌نمایش قبل از خواب | خواب و استراحت | DURATION | UNIT_MIN | 30 | 210 | 900 | 4 | DAILY | 🖥️ | #5964B4 | ACT_SLE |
| ACT042 | مراقبت شخصی | مراقبت از خود | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 3 | DAILY | 🧴 | #C08497 | ACT_SEL |
| ACT043 | جمع‌بندی پایان روز | بهره‌وری | DURATION | UNIT_MIN | 5 | 35 | 150 | 3 | DAILY | 🌇 | #7A9E7E | ACT_PRO |
| ACT044 | بررسی یک رفتار و یادگیری از آن | رشد فردی | DURATION | UNIT_MIN | 10 | 70 | 300 | 4 | DAILY | 🔍 | #6B7AA1 | ACT_GRO |
| ACT045 | انجام یک تجربه جدید | رشد فردی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 3 | MONTHLY | 🌈 | #D17A22 | ACT_MON |

Status همه: **ACTIVE**. CreatedBy در JSON نیست؛ در SQL `is_seed=1` و `user_id=NULL` سپس کپی per-user.

## Seed دیگر SQL

- نقش‌ها و مجوزها (پرونده ۱۵)
- settings: product_name=جوما ، support_contact خالی

## مشاغل (`jobs_list`)

دانش‌آموز، دانشجو، کارمند، مدیر، کارآفرین، پزشک، روانشناس، مهندس، معلم، وکیل، حسابدار، فروشنده، فریلنسر، خانه‌دار، بازنشسته، پژوهشگر، مشاغل آزاد، سایر



# ===== 17_DEVELOPER_MAP.md =====

# 17 — Developer Map (اگر بخواهم … کجا بروم)

| اگر بخواهم… | فایل اصلی | تابع / محل | جدول / ذخیره |
|---|---|---|---|
| فرم ثبت‌نام را تغییر دهم | `pages/register.php` | HTML فرم + `validate_registration` | `joma_users` |
| شغل جدید اضافه کنم | `includes/helpers.php` | `jobs_list()` | مقدار `job` رشته آزاد در users |
| قانون رمز را عوض کنم | `helpers.php` + `joma.js` + `login.php`/`forgot.php` | `validate_registration` ، `pass()` | password_hash |
| فعالیت رسمی جدید (Seed) | `database/library_official.json` و `joma.sql` | `copy_seed_to_user` / `official_library` | `joma_activities` |
| فعالیت فقط برای یک کاربر | `pages/library.php` | `save_activity` | `joma_activities` |
| رنگ فعالیت Library | `library.php` فیلد color | `save_activity` | activities.color |
| هدف پیش‌فرض Seed | JSON + SQL | — | daily/weekly/monthly_target |
| هدف همین ماه | `pages/plan.php` | `add_plan_activity` / `update_plan_activity` | plan_activities.target_value |
| Frequency جدید (مثلاً YEARLY) | `helpers.php` `frequencies_list` + `can_register_performance` + `target_of` + UI today/plan | چند نقطه | VARCHAR frequency |
| شاخص Mood جدید | `pages/mood.php` + `save_mood` + SQL ستون جدید | — | `joma_mood_records` |
| تب گزارش جدید | `pages/reports.php` | آرایه `$tabs` + شاخه if | خروجی `build_report` |
| منوی جدید | `helpers.php` `nav_items` + `index.php` `$allowed` + `pages/x.php` | `layout.php` | — |
| Permission جدید | `store.php` `store_role_permissions` **و** در صورت DB-driven بعداً SQL | `has_perm` / `require_perm` | جداول roles در SQL فعلاً خوانده نمی‌شوند |
| سطح دسترسی جدید | ستون access_level + UI ادمین **نیست** | — | `joma_users.access_level` |
| SMS OTP | جدول آماده؛ تابع ارسال **نیست** | — | `joma_otp_codes` |
| متن پشتیبانی | `pages/support.php` | — | `joma_settings.support_contact` خوانده نمی‌شود |
| لوگو | فایل `assets/images/logo.jpg` | `joma_logo` | — |
| ظاهر | `assets/css/joma.css` | — | — |
| hint زنده | `assets/js/joma.js` | — | `api/username.php` |
| تقویم هفته | `includes/jalali.php` | `jalali_week_start` | — |
| قوانین DAILY | `functions/joma.php` | `can_register_performance` | events |
| Finalize | `pages/plan.php` | `transition_plan` | `joma_plans` |
| ذخیره فایل به‌جای MySQL | `config/config.php` | `storage` | `data/store.json` |



# ===== 18_VERSION_2_ROADMAP.md =====

# 18 — قابلیت‌های JOMA V2 (بر اساس معماری فعلی)

برچسب **RECOMMENDATION**. هیچ‌کدام در V1 کامل نیستند مگر جایی که نوشته شده IMPLEMENTED.

زیرساخت قابل استفادهٔ مشترک: User, Period, Plan, PlanActivity Snapshot, Events, Mood 1–5, build_report, نقش SQL، OTP table، notifications table، settings، Jalali، CSRF.

---

## حداقل ۲۰ پیشنهاد

### User / Admin
1. **مدیریت کاربران ادمین** — دیدن لیست، تغییر `role_key`. جدول users آماده. UI و خواندن SQL permissions نیست. سختی متوسط. اولویت بالا اگر چند سطح می‌خواهید.
2. **Permission از دیتابیس** — `store_role_permissions` را به `joma_role_permissions` وصل کنید. جداول Ready. سختی متوسط.
3. **تأیید موبایل** — ستون `mobile_verified` و جدول OTP Ready؛ ارسال SMS نیست. سختی بالا (سرویس خارجی).

### Planning / Activity
4. **کپی برنامه ماه قبل** — Snapshotها مستقل‌اند؛ خواندن plan_activities دوره قبل و insert در DRAFT جدید. بدون جدول جدید. سختی کم. اولویت بالا.
5. **قالب برنامه آماده** — چند PlanActivity پیش‌فرض. می‌توان جدول کوچک `joma_plan_templates` یا JSON. سختی متوسط.
6. **مرتب‌سازی کامل Library** — الان sort در plan است؛ فیلتر دسته IMPLEMENTED. سختی کم.

### Performance / Mood
7. **ویرایش/حذف Event** — جدول events هست؛ UI حذف نیست. سختی کم-متوسط (باید قوانین DAILY حفظ شود).
8. **یادآوری ثبت امروز** — `notifications` + cron/هاست. جدول هست. SMS نیست. سختی متوسط.
9. **همبستگی Mood و عملکرد** — هر دو داده روزانه موجود است؛ تابع جدید روی calendar report. بدون جدول اجباری. سختی متوسط.

### Reporting
10. **ذخیره Projection** — جدول `joma_report_projections` خالی است. `build_report` را JSON ذخیره کنید. سختی کم.
11. **خروجی CSV/Excel از Event خام** — داده در events. سختی کم. PDF نیاز کتابخانه/سرویس دارد (متوسط-بالا روی هاست بدون Composer).
12. **فرمول Achievement وقتی مالک تعریف کرد** — الان عمداً UNSPECIFIED. فقط بعد از تعریف رسمی. سختی متوسط + ریسک محصول.

### Gamification (با احتیاط)
13. **Streak ثبت روزانه** — از events DAILY قابل محاسبه بدون جدول؛ برای پایداری جدول `joma_streaks`. سختی متوسط. Achievement جعلی نشود.
14. **نشان (Badge) ساده** — مثلاً «۷ روز Mood». نیاز جدول badges. سختی متوسط.

### Notification
15. **صندوق درون‌برنامه** — جدول notifications Ready. صفحه inbox جدید. سختی کم-متوسط.
16. **ایمیل یادآوری** — نیاز SMTP خارجی. سختی متوسط.

### Mobile / API
17. **JSON API برای اپ** — الان صفحه HTML است. می‌توان endpointهای نازک روی همان توابع `register_performance` ساخت. سختی بالا. Session/توکن جدید.

### AI (فقط روی داده موجود)
18. **مرور هفتگی متنی از Event+Mood** — بدون مدل هم می‌توان خلاصه آماری از `build_report` ساخت. مدل زبانی = سرویس خارجی. سختی متوسط تا بالا.
19. **پیشنهاد فعالیت از Library بر اساس ثبت کم** — مقایسه target/actual بدون درصد دروغین. سختی متوسط.

### Social / Coaching
20. **نقش coach دیدن گزارش با اجازه** — role در SQL هست؛ اشتراک plan بین کاربران IMPLEMENTED نیست. نیاز جدول اشتراک. سختی بالا.
21. **هدف شخصی جدا از Library** — فیلد اضافه روی plan یا جدول goals. سختی متوسط.

### تقویم
22. **خروجی ICS** — از events تاریخ شمسی→میلادی (`jalali_to_gregorian` موجود). سختی متوسط.

---

## Roadmap پیشنهادی

```
V1.1  سریع: کپی برنامه ماه قبل، حذف/ویرایش Event، Inbox اعلان، CSV
V1.2  اتصال permissions به SQL + صفحه ادمین نقش
V1.5  OTP موبایل (سرویس SMS) + صندوق و ایمیل
V2.0  API موبایل + فرمول Achievement رسمی (فقط اگر مالک نوشت)
V2.5  Coach share + همبستگی Mood/عملکرد + ICS
```

| نوع | مثال |
|---|---|
| سریع | کپی ماه قبل، CSV، inbox |
| متوسط | permissions SQL، streak، همبستگی |
| پیچیده | API موبایل، SMS، AI خارجی |
| نیاز DB | badges, shares, گاهی streak |
| نیاز API/سرویس | SMS, email, LLM |
| نیاز Admin | تغییر role |

**RECOMMENDATION:** درصد موفقیت نسازید تا فرمول رسمی نیاید.



# ===== 19_CHANGE_GUIDE.md =====

# 19 — اگر فردا بخواهم قابلیت X را اضافه کنم

کد برنامه را در این مرحله عوض نکرده‌ایم؛ این آموزش «چطور دست بزنید» است.

## مثال 1 — فعالیت رسمی جدید ACT046

1. `database/library_official.json` یک آبجکت مثل بقیه.  
2. همان ردیف را به INSERT انتهای `database/joma.sql` اضافه کنید.  
3. Database: بله برای نصب تازه. کاربران قبلی: `copy_seed_to_user` فقط اگر هنوز Seed ندارند کپی می‌کند — کاربران قدیمی این فعالیت را **خودکار نمی‌گیرند** مگر منطق جدا بنویسید.  
4. Validation: همان فیلدهای Seed.  
5. UI: کتابخانه از لیست کاربر می‌خواند.  
6. تست: کاربر جدید باید ACT046 را ببیند؛ Snapshot ماه قبل نباید عوض شود.

## مثال 2 — فیلد جدید روی User (مثلاً city)

1. ستون در `joma.sql` + کلید در آرایه `create_user` / `store.json`.  
2. `pages/register.php` و `validate_registration` و `session_user_array` و پروفایل/تنظیمات.  
3. Database: بله ALTER برای دیتابیس موجود.  
4. Validation سرور اجباری.  
5. فرم register + settings.  
6. تست ثبت‌نام و ذخیره تنظیمات؛ Session بعد ورود فیلد را دارد.

## مثال 3 — Permission جدید مثلاً VIEW_COACH

1. INSERT در `joma_permissions` و `joma_role_permissions`.  
2. **حتماً** `store_role_permissions` در `store.php` — وگرنه Runtime نمی‌فهمد.  
3. Database: بله.  
4. `require_perm('VIEW_COACH')` در صفحه جدید.  
5. منو: `nav_items` + `index.php` allowed.  
6. تست با user member (باید رد شود اگر به member ندادید) و admin.

الان member و admin در PHP فرق کمی دارند؛ بدون تغییر `store_role_permissions` نقش SQL بی‌اثر است.

## مثال 4 — گزارش جدید «تعداد روزهای دارای Event»

1. یا داخل `build_report` فیلد جدید بشمارید (از `calendar`) یا در `reports.php` تب جدید از همان calendar.  
2. تابع: ترجیحاً `build_report` تا یک منبع داده بماند.  
3. DB لازم نیست اگر از events موجود است. Projection جدول را هنوز اجباری نکنید.  
4. درصد نسازید مگر فرمول رسمی.  
5. `$tabs` در `reports.php`.  
6. تست با صفر Event و با چند Event.

## مثال 5 — SMS OTP

1. جدول `joma_otp_codes` آماده است.  
2. توابع جدید بسازید (ارسال/verify) — الان نیستند.  
3. DB: جدول هست؛ سرویس SMS خارجی لازم است.  
4. Validation کد و انقضا.  
5. UI ثبت‌نام/ورود.  
6. تست: بدون ارسال واقعی در dev؛ `mobile_verified`.  
سختی بالا؛ هاست اشتراکی cron/queue ندارد مگر سرویس بیرونی.

## الگوی کلی هر تغییر

1. صفحه یا Seed؟  
2. تابع در `functions/joma.php` یا helpers؟  
3. آیا هر دو حالت file و mysql را باید لمس کنید؟ **بله اگر داده است.**  
4. CSRF برای POST.  
5. `e()` برای خروجی.  
6. Snapshot دوره قبل را نشکنید.



# ===== 20_KNOWN_LIMITATIONS.md =====

# 20 — محدودیت‌ها، تناقض‌ها، چه چیز را عوض کنیم

## CONFLICTS & RISKS

1. **نقش SQL در برابر PHP ثابت**  
   جداول `joma_roles` / `joma_role_permissions` Seed می‌شوند اما `has_perm` از `store_role_permissions` می‌خواند. عوض کردن SQL بدون PHP بی‌اثر است.

2. **member ≈ plus ≈ coach در Runtime**  
   فقط admin دو مجوز اضافه دارد و آن‌ها UI ندارند.

3. **CREATE_PLAN هرگز require نمی‌شود**  
   در ماتریس هست؛ صفحه plan از EDIT_PLAN استفاده می‌کند.

4. **access_level ذخیره می‌شود، چک نمی‌شود**

5. **گزارش Projection جدول دارد، نوشته نمی‌شود**

6. **OTP و Notification جدول دارند، محصول کامل نیستند**

7. **support_contact در settings خالی است و صفحه support از جدول نمی‌خواند**

8. **یکتایی DAILY فقط در PHP است نه UNIQUE ایندکس SQL**  
   دو درخواست همزمان theoretically می‌توانند دو Event روزانه بسازند.

9. **هدف ۰ در Seed هفتگی/ماهانه**  
   `add_plan_activity` target<=0 را رد می‌کند. ACT018 و مشابه اگر Override نشود ممکن است با target پیش‌فرض weekly>0 OK باشند؛ اگر کسی frequency را DAILY کند و daily=0، افزودن رد می‌شود.

10. **رنگ در style خام**  
    اگر کاربر در Library مقدار غیر HEX بگذارد، ریسک HTML/CSS.

11. **دو فرانت**  
    React در `src/` و PHP در `joma/`. منبع حقیقت هاست: PHP. مستندات قدیمی `docs/ARCHITECTURE.md` مربوط به React است و ممکن است با PHP فرق کند.

12. **copy_seed_to_user فقط یک‌بار**  
    Seed جدید به کاربران قدیمی نمی‌رسد.

13. **حذف Activity Library**  
    Snapshot می‌ماند؛ activity_id ممکن است یتیم شود (FK فیزیکی نیست).

14. **install.php خطرناک روی Production اگر حذف نشود**

15. **Preview CSRF/session**  
    `joma_sid` روی URL؛ روی هاست عادی اگر کوکی کار کند sid اضافه نمی‌شود.

## IMPLEMENTED نیست (صریح)

- فرمول Achievement / Overall Success / درصد موفقیت کلی
- پنل ادمین کاربران
- ارسال SMS
- Inbox کامل اعلان
- API موبایل
- یادآوری push
- همبستگی Mood-Performance به‌عنوان گزارش جدا
- Remember-me
- تأیید ایمیل
- چندزبانه
- FOREIGN KEY دیتابیس

## WHAT I CAN SAFELY CHANGE

- متن و CSS و برچسب منو
- `jobs_list` / `categories_list` (اگر دادهٔ قدیمی شغل نامعتبر نشود)
- استیکر/رنگ یک فعالیت در Library برای ماه‌های **بعد**
- `pages/about.php` و متن support (بدون حدس تماس واقعی)
- اضافه کردن تب گزارش که فقط شمارش داده خام باشد
- فایل لوگو در مسیر استاندارد

## WHAT I SHOULD NOT CHANGE WITHOUT REVIEW

- `can_register_performance` و تفاوت DAILY/WEEKLY/MONTHLY
- `transition_plan` و قفل Snapshot
- `build_report` برای ساخت درصد بدون فرمول رسمی
- `store_role_permissions` بدون همگام‌سازی SQL و UI
- CSRF / session / `joma_sid`
- الگوریتم jalali و شروع هفته شنبه
- پاک کردن `user_id` از کوئری‌ها
- عوض کردن Seed کاربران Production بدون مهاجرت

## فایل‌های مستند قدیمی در همین پوشه docs/

`ARCHITECTURE.md`, `AUTHENTICATION.md`, `BUSINESS_RULES.md`, `DATABASE.md`, `DEPLOYMENT.md`, … مربوط به نسخه React/محلی قبلی‌اند. برای هاست PHP این مجموعهٔ شماره‌دار را مبنا بگیرید.
