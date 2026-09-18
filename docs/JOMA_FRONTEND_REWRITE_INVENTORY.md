# نقشهٔ سطح جوما — بازنویسی فرانت (بدون تغییر کد)

تاریخ برداشت: ۱۴۰۵-۰۶-۲۶ (۲۰۲۶-۰۹-۱۷)  
بازنگری اتحاد شاخه‌ها: همان روز (مالک: همهٔ قابلیت‌های زیرشاخه به دامنهٔ اصلی می‌آید؛ کاربر واقعی روی یک نسخه همه را می‌بیند.)  
**هیچ کد/جدول/ستونی در این کار عوض نشده است.**

## دامنهٔ محصول هدف = اتحاد شاخه‌ها (نه «تست اختیاری»)

بازنویسی فرانت باید **اتحاد** را پوشش دهد، نه فقط هستهٔ یک شاخه.

| شاخه | داخل جومای دامنه؟ | محتوا |
|---|---|---|
| `arena/01a032da-…` | **بله — هسته + بسته‌ها** | PHP جوما، گیت ثبت‌نام، فیلتر دسته در برنامه، آموزش، زوج ACT046–080، طرحواره ACT081–107 |
| `arena/01a08b36-…` | **بله — ماژول‌ها** | هم‌مسیر، موتور موفقیت، تحلیل V1، گزارش تحلیلی R1/R2، منوی موبایل/سوییچ نقش |
| `arena/01a06d08-…` | **خیر — فقط مارکتینگ** | ریل/کاتالوگ اینستاگرام؛ صفحهٔ داخل برنامه نیست |
| `arena/01a0820a-…` | **خیر** | دموی سفارش رستوران؛ جوما نیست |
| `arena/01a07243-…` | **خیر — سایت جدا** | کارت ویزیت دیجیتال `/card`؛ داخل جوما نیست |

ستون «لایهٔ الف / ب» در جدول‌های پایین یعنی **الان روی کدام درخت کد است**، نه اینکه فرانت بتواند حذفش کند. هدف نهایی: همهٔ ردیف‌های ✅ ب روی دامنهٔ اصلی هم بیایند.

سند فرانت `docs/spec/` در این checkout **نیست**. قرارداد ستون‌ها:

- **صفحهٔ جدید** = صفحهٔ منطقی بازنویسی
- **وضعیت:** `✅` باید در بازنویسی دامنه بیاید · `➕` بک‌اند/طراحی ندارد و باید اضافه شود · `⏳` عمداً معوق · `🗑` خواب/بن‌بست/خارج از جوما

## قفل ثبت‌نام (نه SMS)

سیستم پیامک **متصل نیست**. فیلد کد امنیتی **قفل ساخت حساب** است تا هر کسی نتواند اکانت بسازد.

- کد به کاربر **داده نمی‌شود** و در UI نشان داده نمی‌شود (`type=password`؛ پیام خطا کد صحیح را فاش نمی‌کند).
- دکمهٔ «درخواست کد امنیتی» مودال می‌گوید با پشتیبانی تماس بگیرید (پیامک / بله: `09967979471`) — این لینک `sms:` تماس دستی است، نه OTP.
- ترجیح `notifications_enabled` ذخیره می‌شود؛ ارسال نمی‌شود.
- جدول `joma_otp_codes` خالی و بدون تابع است.

فرانت نباید وعدهٔ «کد برایتان پیامک می‌شود» بدهد.

---

# ۱) درهای ورود

ورودی واحد: `index.php?p=…`  
صفحهٔ نامعتبر → `home`. پارامتر صفحه: **فقط** `p` (نه `page`).

## ۱.۱ مسیرهای `p` (allowlist)

| قلم | نقش در محصول | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `p=home` GET | معرفی عمومی | فرود | ✅ |
| `p=login` GET/POST | ورود | ورود | ✅ |
| `p=register` GET/POST | ساخت حساب | ثبت‌نام | ✅ |
| `p=forgot` GET/POST | عوض کردن رمز بدون ایمیل | فراموشی رمز | ✅ |
| `p=logout` GET | خروج نشست | — | ✅ |
| `p=mood` GET/POST | پنج شاخص حال امروز | خلق | ✅ |
| `p=dashboard` GET | خانهٔ داخل برنامه | داشبورد | ✅ |
| `p=plan` GET/POST | برنامهٔ ماه | برنامه | ✅ |
| `p=today` GET/POST | ثبت عملکرد | امروز | ✅ |
| `p=library` GET/POST | کتابخانهٔ فعالیت | کتابخانه | ✅ |
| `p=periods` GET/POST | فهرست/ساخت ماه | دوره‌ها | ✅ |
| `p=period` GET/POST | جزئیات یک ماه + بایگانی | دوره | ✅ |
| `p=reports` GET (+POST در R2) | گزارش دوره | گزارش | ✅ |
| `p=profile` GET | نمایش پروفایل | پروفایل | ✅ |
| `p=settings` GET/POST | نام/موبایل/شغل/ترجیح | تنظیمات | ✅ |
| `p=about` GET | درباره | درباره | ✅ |
| `p=support` GET | پشتیبانی (لینک sms ثابت) | پشتیبانی | ✅ |
| `p=learn` GET | آموزش جوما/زوج/طرحواره | آموزش | ✅ اتحاد (بستهٔ AMOOZESH) |
| `p=hammasir` GET/POST | مرکز هم‌مسیر (سوییچ نقش + include سه نما) | هم‌مسیر | ✅ اتحاد (فعلاً روی شاخهٔ هم‌مسیر) |
| `p=hammasir_providers` GET/POST | رجیستری همراهان (مدیر) | هم‌مسیر · مدیر | ✅ ب |
| `p=hammasir_admins` GET/POST | ارتقا/سلب مدیر | هم‌مسیر · مدیر | ✅ ب |

صفحات فایل‌دار که **در allowlist نیستند** (با `?p=` مستقیم نمی‌آیند؛ اگر بیایند → خانه):

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `pages/hammasir_client.php` | نمای مراجع — include از `hammasir.php` | هم‌مسیر · کاربر | ✅ (نه مسیر جدا) |
| `pages/hammasir_provider.php` | نمای مشاور + داشبورد KPI | هم‌مسیر · مشاور | ✅ (نه مسیر جدا) |
| `pages/hammasir_admin.php` | نمای مدیر داخل مرکز | هم‌مسیر · مدیر | ✅ (نه مسیر جدا) |
| `pages/hammasir_chat.php` | گفتگو — include از client/provider | هم‌مسیر · گفتگو | ✅ (نه مسیر جدا) |

## ۱.۲ API جدا از `p`

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `api/username.php?u=` GET JSON `{ok:bool}` | hint یکتایی نام کاربری (لینک منو ندارد؛ از `joma.js` در ثبت‌نام) | ثبت‌نام | ✅ در مخفی |
| `install.php` | نصب جداول+config — **در دموی تست نیست**؛ در هستهٔ الف هست | — | 🗑 روی پروداکشن باید حذف شود؛ در بازنویسی UI نیاید |

## ۱.۳ پارامترها و مقدارهای مجاز

### مشترک همهٔ POST
- `csrf` — الزامی؛ ناهماهنگ → HTTP 400 «درخواست نامعتبر است.»
- `joma_sid` — وقتی کوکی نشست نرسد (Preview)

### نشست / دوره
- GET `period` = `YYYY-MM` شمسی → `$_SESSION['period_key']` (`current_period_key`)

### ورود `login`
- POST `identifier`, `password`

### ثبت‌نام `register`
- POST `first_name`, `last_name`, `username` (regex `^[a-zA-Z][a-zA-Z0-9._]{2,19}$`), `email`, `phone` (`^09[0-9]{9}$`), `job` (فقط `jobs_list`), `password` (≥۶), `confirm`, `accept`
- POST `security_code` اگر گیت روشن باشد — **قفل دستی**؛ کاربر کد را از پشتیبانی می‌گیرد نه از SMS سیستم. مودال: «برای دریافت کد امنیتی، لطفاً با پشتیبانی تماس بگیرید.»

### فراموشی
- POST `identifier`, `password`, `confirm`

### خلق
- POST `energy`,`general`,`focus`,`sleep`,`stress` هرکدام ۱..۵؛ `note` اختیاری

### برنامه `action`
- `add`: `activity_id`, `frequency` ∈ {'',DAILY,WEEKLY,MONTHLY}, `target_value`, `weight`
- `savepa`: `pa_id`, `frequency`, `target_value`, `weight`, `sort_order`
- `up`: `pa_id`
- `del`: `pa_id`, `confirm=1`
- `next`: `next` ∈ {PLANNING,RUNNING,ARCHIVED} طبق `can_transition`

### امروز
- GET/POST `date` = روز شمسی داخل همان `period_key` (**آیندهٔ همان ماه هم قبول است**)
- POST `pa_id`, `value` (عدد ≥۰؛ BOOLEAN 0\|1؛ RATING 1..5)

### کتابخانه `action`
- `save`: `id` (۰=جدید), `name`,`category`,`frequency`,`data_type`,`unit`,`target`,`weight`,`sticker`,`color`,`status`
- `toggle`: `id`
- `del`: `id`,`confirm=1`
- GET `q`,`cat`,`fr`,`edit`,`new`

### دوره‌ها
- POST `year`,`month` → `ensure_period`؛ اختیاری `work=1`

### جزئیات دوره
- GET `period`
- POST `archive=1` یا `work=1`

### گزارش
- GET `tab` ∈ overview, success, acts, weight, cal, trend, mood, cmp, det، و در R2: `analytics`
- GET `other` = `YYYY-MM` (تب مقایسه)
- R2 POST داخل تب analytics: `report_action=analytics_r1` یا نوع R2؛ `period_preset` ∈ month\|month_week\|last_4_weeks؛ `year_month`؛ `week_index`؛ `activity_id`؛ شاخص‌های خلق/عملکرد؛ `preview=1` (فقط فرم)

### تنظیمات
- POST `first_name`,`last_name`,`phone`,`job`,`compact`,`notify`

### آموزش
- GET `g` ∈ joma\|couple\|schema
- GET `code` = ACT… (uppercase)

### هم‌مسیر POST `hammasir_action`
`mode_set`, `onboarding_accept`, `onboarding_dismiss`, `onboarding_reopen`, `client_request_new`, `client_request_cancel`, `provider_open`, `provider_close`, `provider_register`, `provider_set_title`, `provider_set_status`, `admin_promote`, `admin_demote`, `link_request`, `link_cancel`, `link_revoke`, `link_accept`, `link_decline`, `perms_update`, `messaging_set`  
فیلدهای همراه: `mode` ∈ admin\|provider\|client ؛ `link_id` ؛ `user_id` ؛ `title` ؛ `status` ∈ ACTIVE\|INACTIVE ؛ `provider_user_id` ؛ `perm_VIEW_PROGRESS` / `perm_VIEW_ACTIVITY_DETAILS` / `perm_VIEW_MOOD` ؛ `enabled` ؛ `body`

## ۱.۴ درهای بدون لینک منو که کار می‌کنند

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `api/username.php` | یکتایی نام | ثبت‌نام | ✅ مخفی |
| `today&date=` روز دیگر/آیندهٔ ماه | ثبت با تأخیر یا جلو | امروز | ✅ |
| `reports&tab=` مقدار نامعتبر → overview | سقوط امن | گزارش | ✅ |
| `p=` خارج allowlist → home | سقوط امن | — | ✅ |
| `learn&code=` مستقیم | راهنمای یک فعالیت بدون عبور از هاب | آموزش · کارت تمرین | ✅ ب |
| گیت خلق: هر `p` لاگین‌شده جز skiplist اگر حال امروز نباشد → redirect mood | اجبار ثبت حال | خلق | ✅ skip: mood,logout,about,login,register,forgot,home |

---

# ۲) توابع دامنه

صدا از **صفحه/API**. توابع داخلی موتور اگر فقط همان فایل صدا بزند، «مصرف UI» همان تب گزارش است.

## ۲.۱ هستهٔ جوما — `functions/joma.php`

| قلم | نقش (یک خط) | خروجی | صفحهٔ جدید | وضعیت |
|---|---|---|---|---|
| `official_library` | خواندن seed رسمی JSON | آرایه فعالیت | کتابخانه (غیرمستقیم) | ✅ |
| `copy_seed_to_user` | کپی seed به کاربر جدید/ورود | void | ثبت‌نام/ورود | ✅ |
| `user_by_username` | پیدا کردن با username یا email | user/null | ورود/فراموشی | ✅ |
| `username_taken` / `email_taken` | یکتایی | bool | ثبت‌نام + API | ✅ |
| `create_user` | ساخت member سطح ۱ + prefs + seed | user | ثبت‌نام | ✅ |
| `get_user` | خواندن کاربر | user/null | نشست/هم‌مسیر | ✅ |
| `update_user` | فقط نام/تلفن/شغل | user | تنظیمات | ✅ |
| `reset_user_password` | hash جدید | پیام یا '' | فراموشی | ✅ |
| `get_prefs` / `save_prefs` | compact + notifications_enabled | array/void | تنظیمات/قالب | ✅ |
| `session_user_array` | کپی نشست بدون رمز | array | ورود/ثبت‌نام | ✅ |
| `list_user_activities` / `get_activity` / `save_activity` / `delete_activity` | CRUD کتابخانهٔ همان کاربر | list/row/id | کتابخانه | ✅ |
| `ensure_period` | ساخت ماه+برنامه DRAFT اگر نبود | period+plan | تقریباً همهٔ صفحات داخل | ✅ |
| `get_plan_by_period` / `get_plan` / `list_periods` | خواندن برنامه/ماه‌ها | row/list | دوره/گزارش | ✅ |
| `list_plan_activities` | اسنپ‌شات‌های برنامه | list | برنامه/امروز/گزارش/هم‌مسیر | ✅ |
| `add_plan_activity` | اسنپ‌شات+override؛ قفل اگر RUNNING | پیام یا '' | برنامه | ✅ |
| `update_plan_activity` | تناوب/هدف/وزن/ترتیب | پیام | برنامه | ✅ |
| `remove_plan_activity` | حذف از برنامه اگر باز باشد | پیام | برنامه | ✅ |
| `can_transition` / `transition_plan` | DRAFT→PLANNING→RUNNING→ARCHIVED | bool / پیام | برنامه/دوره | ✅ |
| `list_events` | رویدادهای عملکرد برنامه | list | امروز/گزارش | ✅ |
| `can_register_performance` / `register_performance` | ثبت قطعی INSERT | پیام یا '' | امروز | ✅ |
| `events_for` / `sum_actual` / `weekly_actual` / `displayed_actual` / `has_daily_registration` | جمع نمایش | عدد/bool | امروز | ✅ |
| `get_mood` / `list_moods` / `save_mood` | خلق روزانه یکتا | row/list/void | خلق/گزارش | ✅ |
| `weight_sum` | جمع وزن اسنپ‌شات | int | برنامه/گزارش | ✅ |
| `build_report` | گزارش خام؛ achievement=UNSPECIFIED | array | گزارش/دوره | ✅ |

## ۲.۲ موتور موفقیت — `functions/success.php` (فقط لایهٔ ب)

ورود UI: `success_report($plan,$bounds,$acts,$evs,$today)`  
خالص؛ بدون نوشتن. مقیاس: نسبت ۰..۱ یا null. نسخه: `JOMA_SUCCESS_V1`.

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `success_report` | تحقق/پوشش یک برنامه یک ماه | گزارش · موفقیت | ✅ ب / ➕ الف |
| `success_activity` + `data_status` | OK / NO_DATA / NO_ELIGIBLE_CYCLES / NOT_STARTED / INVALID_CONFIG / UNSUPPORTED / EXCLUDED / AMBIGUOUS_DUPLICATE | همان | ✅ |
| `success_overall` | موفقیت کلی وزن‌دار؛ RATING بیرون | همان | ✅ |
| `success_rating_summary` | میانگین/کمینه/بیشینه/توزیع RATING | گزارش · امتیاز | ✅ |
| `success_cycles` | DAILY روز؛ WEEKLY شنبه–جمعه ∩ ماه؛ MONTHLY یک چرخه | — | ✅ |
| `success_jalali_add_days` | ±روز شمسی | گزارش تحلیلی/هم‌مسیر | ✅ |

**PARTIAL به‌عنوان enum وجود ندارد.** نزدیک‌ترین: `IN_PROGRESS` در چرخه (نمرهٔ نهایی نمی‌گیرد) و `overall_status=INSUFFICIENT_DATA`.

## ۲.۳ تحلیل V1 — `includes/analytics.php` (لایهٔ ب)

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `analytics_daily_rows` | نرخ موفقیت روزانه فقط فعالیت DAILY وزن‌دار؛ روز بدون ثبت = null | گزارش · روند/تقویم | ✅ ب |
| `analytics_weeks` | هفته‌های ۷روزه از روز ۱ ماه (نه لزوماً شنبه) | گزارش | ✅ ب — با هفتهٔ شمسی R2 فرق دارد |
| `analytics_mood_series` | پنج شاخص + شاخص مثبت (بدون استرس) | گزارش · خلق | ✅ ب |
| `analytics_correlation` | پیرسون شاخص‌مثبت × موفقیت روزانه؛ n<3 → null | گزارش · موفقیت | ✅ ب |
| `analytics_period_summary` | خلاصه برای مقایسه دو ماه | گزارش · مقایسه | ✅ ب |

شاخص مثبت تحلیل V1: میانگین energy+general+focus+sleep **بدون** معکوس استرس.  
حال‌وهوای ترکیبی هم‌مسیر/R2 جداست (پایین).

## ۲.۴ گزارش تحلیلی R1/R2 — `functions/analytics_reports.php` (بستهٔ R2؛ در دموی standalone هنوز به `reports.php` وصل نیست)

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `analytics_reports_render` | تب گزارش تحلیلی شخصی | گزارش · تحلیلی | ✅ اگر R2 نصب شود / ⏳ روی دموی standalone فعلی |
| `hammasir_report_series` | سری مجاز viewer/subject | همان + بعداً مشاور | ✅ R2 شخصی؛ ⏳ مشاور R3 |
| `hammasir_report_correlation` | پیرسون دو سری؛ استرس خام | تحلیلی · همبستگی | ✅ R2 |
| `hammasir_report_weekday_averages` | count/avg/min/max شنبه..جمعه | تحلیلی · روز هفته | ✅ R2 |
| `hammasir_report_normalize_series` | نرمال ۰..۱۰۰ فقط ترکیب آزاد | تحلیلی · ترکیب | ✅ R2 |
| `analytics_reports_mood_composite_series` | حال‌وهوای ترکیبی = wellbeing | تحلیلی | ✅ R2 |
| `analytics_reports_perf_count_series` | تعداد ثبت در روز؛ بدون ثبت=۰ | تحلیلی | ✅ R2 |

ورودی R2: بازهٔ سرورساخته ≤۹۰ روز؛ `month` / `month_week` / `last_4_weeks`.  
خروجی سری: `{id,label,unit,perm_ok,points[{jalali_date,value|null}]}`.

## ۲.۵ هم‌مسیر — عملیات UI (نه primitiveهای db_/file_)

مصرف از `pages/hammasir*.php`:

`hammasir_enabled`, `hammasir_capabilities`, `hammasir_active_mode`, `hammasir_mode_set`, `hammasir_onboarding_state`, `hammasir_provider_list`, `hammasir_link_request/respond/revoke`, `hammasir_perms_update_view`, `hammasir_messaging_set`, `hammasir_message_send`, `hammasir_dto_*`, `hammasir_dashboard_kpis`, `hammasir_attention_items`, `hammasir_clinic_mood_strip`, `hammasir_consent_version`, `hammasir_message_daily_limit`, `hammasir_companion_daily_limit`, `hammasir_user_set_role`, `hammasir_bootstrap_owner`, …

صفحهٔ جدید: هم‌مسیر. وضعیت: ✅ ب.

## ۲.۶ توابعی که از UI صدا نمی‌شوند (خواب)

| قلم | پیشنهاد |
|---|---|
| `sparkline_svg` | در دمو با Chart جایگزین شده — 🗑 یا نگه برای بدون-JS |
| `hammasir_db_provider_upsert` | مسیر زنده `insert` است — 🗑 ادغام |
| `units_primary_list` | نیازمند بررسی helpers دمو — اگر فقط تعریف است 🗑 |
| `hammasir_permission_history_for_link` | تاریخچه **نوشته** می‌شود؛ خواندن UI پیدا نشد — نگه برای ممیزی رضایت ➕ نمایش در طراحی یا ⏳ |
| `joma_report_projections` / توابع ندارد | جدول خالی — ⏳ |
| `CREATE_PLAN` در `require_perm` | هیچ صفحه — 🗑 یا وصل به برنامه |
| OTP verify/send | جدول هست، تابع نیست — ⏳ |

---

# ۳) داده

## ۳.۱ MySQL — جداول جوما (`joma.sql`)

هر دو حالت file/mysql همان فیلدهای منطقی را دارند مگر جدول‌های فقط-SQL.

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `joma_users` | حساب؛ `role_key` پیش‌فرض member؛ `access_level` ذخیره می‌شود **چک نمی‌شود**؛ `mobile_verified` همیشه ۰ | ورود/پروفایل | ✅ |
| `joma_user_preferences` | compact_cards, notifications_enabled | تنظیمات | ✅ |
| `joma_roles` / `joma_permissions` / `joma_role_permissions` | seed نقش؛ Runtime از آرایه PHP است نه این جداول | — | ⏳ تا اتصال SQL |
| `joma_otp_codes` | آمادهٔ SMS | — | ⏳ هیچ تابعی نمی‌نویسد |
| `joma_activities` | کتابخانه per-user | کتابخانه | ✅ |
| `joma_periods` | ماه شمسی؛ UNIQUE (user, period_key)؛ start/end از طول ماه | دوره‌ها | ✅ پایان ماه **قابل ویرایش نیست** |
| `joma_plans` | یک برنامه per period؛ status؛ finalized_at/started_at/archived_at | برنامه | ✅ ستون‌ها در UI کم‌دیده |
| `joma_plan_activities` | اسنپ‌شات؛ UNIQUE (plan_id, activity_id)؛ target_value منبع هدف | برنامه/امروز | ✅ |
| `joma_performance_events` | ثبت قطعی؛ event_type فقط PERFORMANCE_REGISTERED؛ **UNIQUE روزانه در SQL نیست** | امروز | ✅ |
| `joma_mood_records` | UNIQUE (user, jalali_date) | خلق | ✅ `note` هست |
| `joma_report_projections` | رزرو گزارش ذخیره‌شده | — | ⏳ نوشته نمی‌شود |
| `joma_settings` | product_name, support_contact | پشتیبانی نمی‌خواند | 🗑/⏳ |
| `joma_notifications` | صندوق | — | ⏳ UI نیست |

**کتابخانه پس از اتحاد بسته‌ها (ADD-ONLY؛ برنامهٔ در حال اجرا دست نمی‌خورد):**

| بسته | کدها | دسته |
|---|---|---|
| هسته | ACT001–ACT045 | سلامت، خواب، روان، تمرکز، … |
| زوج درمانی | ACT046–ACT080 | زوج |
| طرحواره | ACT081–ACT107 | طرحواره (از ۱۸ طرحواره در برنامهٔ یک نفر حداکثر ۱ تا ۳) |

فعالیت‌های جدید خودکار وارد برنامهٔ ماه جاری نمی‌شوند.

**قید محصولی (کد، نه FK):**  
ثبت فقط RUNNING + تاریخ داخل period_key. DAILY حداکثر یک رویداد همان روز (فقط PHP). هدف اسنپ‌شات >۰. وزن ≥۰.

## ۳.۲ File — `data/store.json`

کلیدها: `users, preferences, activities, periods, plans, plan_activities, events, moods, projections, seq`  
**قفل نوشتن فایل جوما: نیست** (فقط `file_put_contents`).

## ۳.۳ هم‌مسیر MySQL (migration ۷ جدول — فایل؛ اجرای پروداکشن ممنوع بدون GO)

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| `joma_hammasir_providers` | همراه؛ status ACTIVE/INACTIVE؛ UNIQUE user_id | هم‌مسیر | ✅ ب |
| `joma_hammasir_links` | اتصال؛ PENDING/ACTIVE/DECLINED/REVOKED؛ consent_text+version | هم‌مسیر | ✅ ب |
| `joma_hammasir_permissions` | VIEW_* و پیام | هم‌مسیر | ✅ ب |
| `joma_hammasir_permission_history` | append-only رضایت | ممیزی | ✅ نوشتن / ➕ خواندن UI |
| `joma_hammasir_messages` | پیام؛ jalali_date سرور | گفتگو | ✅ ب |
| `joma_hammasir_system_events` | رویداد لینک بدون body آزاد | اعلان درون‌برنامه | ✅ ب |
| `joma_hammasir_user_flags` | mode, mode_chosen, onboarding_seen, onboarding_intent | هم‌مسیر | ✅ ب |

File هم‌مسیر: `data/hammasir/store.json` + `store.lock` (flock). کلیدها: providers, links, permissions, permission_history, messages, system_events, user_flags, seq.  
جدا از `store.json` جوما.

## ۳.۴ ستون/کلید پنهان از رابط ولی خوانده/نوشته

| قلم | نقش | وضعیت |
|---|---|---|
| `plans.started_at` / `archived_at` / `finalized_at` | مرز چرخهٔ موفقیت | ✅ موتور می‌خواند |
| `plan_activities.snapshot_at` + سه هدف روزانه/هفتگی/ماهانه | freeze کتابخانه | ✅ |
| `users.access_level` | نمایش پروفایل؛ چک مجوز نیست | ⏳ |
| `users.mobile_verified` | همیشه ۰ | ⏳ |
| `events.event_type` | فقط PERFORMANCE_REGISTERED | ✅ |
| `moods.note` | در گزارش خود کاربر هست؛ در DTO مشاور هم فعلاً می‌آید | ⚠️ حریم — پایین |
| `preferences.notifications_enabled` | ذخیره می‌شود؛ SMS نیست | ✅ |

---

# ۴) موتورها

## ۴.۱ موفقیت JOMA_SUCCESS_V1

| | |
|---|---|
| ورودی | plan + bounds ماه + plan_activities + events + today شمسی |
| خروجی | overall_success (۰..۱ یا null) + overall_coverage + per-activity achievement/coverage/live + data_status |
| مقیاس | نسبت سقف ۱؛ UI درصد فقط برای Actual ثبت‌شدهٔ صفر = ۰٪؛ null = «—» |
| خاص | No Data ≠ Zero. Achievement × Coverage ممنوع. Weight فقط در کلی. RATING بیرون کلی. DAILY تکراری → AMBIGUOUS_DUPLICATE (از صورت میانگین بیرون، در مخرج پوشش می‌ماند). شروع وسط هفته/ماه → NOT_ELIGIBLE بدون سرشکن. بایگانی روز ناقص در نهایی نیست. BOOLEAN روزانه فقط اگر target==1. |
| file/mysql | یکسان (آرایه از قبل لود شده) |

صفحهٔ جدید: گزارش · موفقیت. ✅ ب / ➕ الف.

## ۴.۲ تحلیل V1 (`analytics.php`)

| | |
|---|---|
| ورودی | خروجی success_report + moods |
| Daily Success | فقط DAILY وزن‌دار؛ روز بی‌ثبت = null |
| شاخص خلق مثبت | میانگین ۴ مثبت؛ استرس جدا و خام |
| هفته | قطعه‌های ۱–۷، ۸–۱۴، … از اول ماه — **نه هفتهٔ شنبه** |
| همبستگی | پیرسون index خلق × موفقیت روزانه |

صفحه: گزارش. ✅ ب.

## ۴.۳ R1/R2 گزارش تحلیلی

| | |
|---|---|
| استرس نمودار مستقل / همبستگی / روز هفته / ترکیب | **خام ۱..۵** (بالاتر=تنش بیشتر) |
| `mood_composite` | فقط اینجا معکوس: میانگین ۴ مثبت + (6−stress) در ۱..۵ سپس round×8/5 → ۱..۸ (عین `hammasir_mood_wellbeing`) |
| null در برابر صفر | خلق بی‌ثبت = null؛ `perf_count` بی‌ثبت = ۰؛ `perf_activity` بی‌ثبت = null |
| R1 spanGaps | false |
| R2 خط ترکیب | spanGaps true فقط خوانایی؛ DTO عوض نمی‌شود |
| نرمال ۰..۱۰۰ | فقط ترکیب آزاد |
| مشاور | ⏳ R3 |

صفحه: گزارش · تحلیلی. ✅ R2 شخصی / ⏳ مشاور.

## ۴.۴ تقویم جلالی `includes/jalali.php`

امروز سرور (TZ پیش‌فرض PHP، نه لزوماً تهران).  
هفته از **شنبه**. `period_key=YYYY-MM`. طول ماه ۲۹/۳۰/۳۱.  
هم‌مسیر و R1 «امروز» را با `Asia/Tehran` جدا می‌سازند (`hammasir_jalali_today`).  
**دو «امروز» ممکن است در مرز نیمه‌شب فرق کنند.** نیازمند بررسی محصول اگر سرور UTC باشد.

صفحه: همه. ✅

---

# ۵) هم‌مسیر

## ۵.۱ وضعیت لینک و انتقال

```
(هیچ) --request--> PENDING --accept--> ACTIVE
                    |                    |
                    |decline             |revoke (هر طرف)
                    v                    v
                 DECLINED             REVOKED
PENDING --cancel توسط مراجع--> REVOKED (رویداد LINK_CANCELLED)
DECLINED / REVOKED انتهایی‌اند.
```

قیدها: یک لینک باز (PENDING|ACTIVE) per مراجع. خود-لینک ممنوع. همراه باید ACTIVE. کول‌داون درخواست مجدد به همان همراه: ۲۴ ساعت. INACTIVE کردن همراه اگر لینک باز دارد ممنوع.

حالت کاربر (جدا از لینک): `client` | `provider` | `admin` در نشست + flags.

| قلم | صفحهٔ جدید | وضعیت |
|---|---|---|
| چرخهٔ لینک | هم‌مسیر | ✅ |
| سوییچ حالت | هم‌مسیر / منو | ✅ |

## ۵.۲ DTO مشاور — فیلدبه‌فیلد (هر فیلد اضافه = نقض حریم)

اجازه فقط اگر لینک ACTIVE و perm همان لینک.

### VIEW_SUMMARY — `hammasir_dto_summary`
`period_key`, `plan_status`, `activity_count`, `event_count`, `weight_sum`, `days_with_events`

### VIEW_PROGRESS — `hammasir_dto_progress`
`overall_coverage`, `overall_success`  
`activities[]`: `title`, `coverage`, `achievement`, `in_valid`

### VIEW_ACTIVITY_DETAILS — `hammasir_dto_activity_details`
`activities[]`: `title`, `target`, `unit`, `frequency`  
`events[]` (حداکثر ۲۰): `date`, `title`, `value`, `unit`

### VIEW_MOOD — `hammasir_dto_mood` (۳۰ روز)
هر ردیف: `date`, `general_mood`, `energy`, `stress`, `note`

**هشدار حریم:** `note` در DTO مشاور هست. سند R2 می‌گوید note نباید به مشاور برود. این **نقض بالقوه** است — رجیستر شکاف.

**نمی‌آید به مشاور (عمدی در همین توابع):** raw `build_report`, کل `success_report`, ایمیل/موبایل، `focus` و `sleep_quality` در dto_mood (فقط general/energy/stress+note).

### داشبورد مشاور (نه پروندهٔ یک مراجع)
- KPI: `active_count`, `pending_count`, `unread_count`, `week_performance_count`, `has_progress_view`
- توجه: `type` pending\|unread\|mood_decline ؛ `link_id` ؛ `client_name` (نام نمایشی) ؛ `time_ago` ؛ `detail` ؛ `perms` کلیدهای VIEW_*
- نوار خلق کلینیک: `client_name` کوتاه + `dots[]` مقدار ۱..۸ یا null (۷ روز) — فقط VIEW_MOOD
- نقاط خلق: `hammasir_mood_dots` → عدد wellbeing یا null

**file mode نیازمند بررسی:** `hammasir_mood_dots` / `hammasir_client_last_activity` در شاخهٔ file از `hammasir_store` کلید `moods`/`events` می‌خوانند که در store هم‌مسیر **نیستند** (مال جوماست). mysql جدا می‌خواند.

## ۵.۳ سهمیه و فاصله (استقرار واقعی — `hammasir_policy_defaults`)

| کلید | پیش‌فرض کد | override |
|---|---|---|
| سقف پیام مراجع / روز شمسی تهران | **۳** | config int>0 |
| سقف پیام همراه / روز | **۲۰** | config |
| کول‌داون همان فرستنده | **۵ ثانیه** | config |
| درخواست مجدد به همان همراه | **۲۴ ساعت** | config |
| طول پیام | ۲۰۰۰ (mb_strlen) | ثابت کد |
| صفحه پیام | ۱۰۰ | ثابت |

دموی standalone: `hammasir_enabled=true`؛ quota در config دمو override نشده → همین پیش‌فرض‌ها.

## ۵.۴ نگهداشت رضایت و پیام

- رضایت: `consent_text` + `consent_version` روی لینک در لحظهٔ درخواست (الان متن ثابت + `v1`)
- تغییر مجوز: ردیف جاری + **history append-only** (previous_enabled, enabled, change_source ∈ INITIAL_CONSENT \| CLIENT_UPDATE \| PROVIDER_MESSAGE_SETTING \| SYSTEM)
- پیام: حذف UI ندارد؛ is_read فقط برای گیرنده
- رویداد سیستمی: بدون متن آزاد

صفحه: هم‌مسیر. ✅ نوشتن / ➕ UI تاریخچه.

---

# ۶) زمان‌بند و جانبی

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| cron / wget زمان‌دار | — | — | **نیست** |
| پاکسازی نشست/OTP/پیام | — | — | **نیست** |
| بایگانی خودکار ماه تمام | — | — | **نیست** (دکمهٔ دستی) |
| قطعی‌سازی خودکار آب | — | — | **نیست** (ACT002 فعالیت عادی است) |
| پشتیبان | فقط `hammasir store.json.bak` یک‌بار قبل از اولین بازنویسی ماژول | — | محدود به هم‌مسیر file |
| ایمیل | — | — | **نیست** |
| SMS / OTP محصول | ارسال کد ثبت‌نام یا اعلان | ثبت‌نام | **نیست و وصل نخواهد شد در این نسخه** — قفل کد امنیتی دستی است |
| لینک `sms:` و بله | شمارهٔ پشتیبانی برای انسان | ثبت‌نام مودال / پشتیبانی | ✅ تماس دستی `09967979471` |
| اعلان درون‌برنامه جوما | جدول notifications | — | **نیست** (ترجیح ذخیره می‌شود) |
| اعلان هم‌مسیر | badge پیام+رویداد در ماژول | هم‌مسیر | ✅ اتحاد |

---

# ۷) محدودیت‌ها

| قلم | نقش | صفحهٔ جدید | وضعیت |
|---|---|---|---|
| شناسهٔ `SEC-01` به‌صورت برچسب در فایل‌های استخراج‌شده | — | — | **پیدا نشد.** نزدیک: AC-SEC گزارش‌ها (حدس `link_id`، CSRF، بدون شناسه در QS) + گیت کد ثبت‌نام |
| CSRF سرور | همهٔ POST | همه | ✅ |
| گیت کد ثبت‌نام | `security_code` type=password | ثبت‌نام | ✅ اگر فلگ روشن |
| PRG | ورود/ثبت‌نام/خلق/onboarding redirect دارند | — | ناقص |
| نبود PRG در `today` پس از ثبت | Refresh مرورگر = ثبت دوباره | امروز | ➕ برای WEEKLY/MONTHLY (DAILY دومی را PHP رد می‌کند) |
| نبود PRG در plan add/save | تکرار POST | برنامه | ➕ |
| تراکنش چندمرحله‌ای جوما (mysql) | add/register/transition جدا | — | **نیست** |
| تراکنش+GET_LOCK هم‌مسیر mysql | لینک/پیام | هم‌مسیر | ✅ ب |
| قفل نوشتن file جوما | store.json | — | **نیست** |
| قفل file هم‌مسیر | flock ۲ثانیه + rename اتمیک | هم‌مسیر | ✅ ب |
| یکتایی DAILY فقط PHP | دو درخواست موازی | امروز | محدودیت شناخته |
| `db()` در خطای اتصال `die` | کل درخواست | — | fail سخت |

---

# الف) بدون مصرف‌کننده / بن‌بست

| قلم | پیشنهاد برای مالک |
|---|---|
| `sparkline_svg` | 🗑 اگر Chart مانده؛ یا نگه برای بدون JS |
| `joma_report_projections` | ⏳ یا 🗑 |
| `joma_otp_codes` + `mobile_verified` | ⏳ SMS |
| `joma_notifications` | ⏳ اعلان |
| `joma_settings.support_contact` | صفحه support از جدول نمی‌خواند — وصل کن یا 🗑 |
| `CREATE_PLAN` perm | به صفحهٔ برنامه وصل یا 🗑 |
| `plus` / `coach` runtime = member | 🗑 برچسب یا ⏳ ماتریس واقعی |
| `?p=hammasir_client` مستقیم | عمداً بسته؛ نگه به‌صورت include |
| R2 روی دموی standalone نصب‌نشده | بسته zip جدا — نصب یا ⏳ |
| `hammasir_dto_mood.note` برای مشاور | 🗑 از DTO یا رضایت جدا |
| file-mode mood_dots روی store هم‌مسیر | باگ/بن‌بست — باید از store جوما بخواند |
| `install.php` هستهٔ الف | 🗑 از پروداکشن |
| مقایسهٔ `analytics_weeks` (از روز۱) با هفتهٔ شنبه R2 | دو تعریف هفته — در طراحی یکی شود |

---

# ب) طراحی جدید می‌خواهد / بک‌اند ندارد

| # | نیاز طراحی | بدون جدول/ستون تازه؟ | کوچک‌ترین راه |
|---|---|---|---|
| ۱ | پرچم «راهنمای تمرین دارد» | **بله** | وجود کلید در `learn_guides.json` / `learn_guide($code)` — فیلد محاسبه‌ای روی `activity_code` اسنپ‌شات |
| ۲ | میانگین/کمینه/بیشینهٔ سری گزارش | **بله** | R2 برای روز هفته همین را دارد. برای سری دلخواه: تابع خالص روی `points[]` موجود؛ فرانت حساب نکند |
| ۳ | جملهٔ «این نمودار چه می‌گوید» | **بله** | چند قالب ثابت PHP per-tab (الان زیرنویس استرس/همبستگی/روز هفته هست؛ جملهٔ کامل per-tab نیست) |
| ۴ | وضعیت موتور OK/PARTIAL/NO_DATA/AMBIGUOUS_DUPLICATE در پاسخ هر تب | **بله** | نگاشت `data_status` / `overall_status` / خالی بودن سری. `PARTIAL` در بک‌اند نیست → یا از `IN_PROGRESS` بسازید یا نیاورید |
| ۵ | هدف آب از اسنپ‌شات | **بله** | `plan_activities.target_value` همان فعالیت؛ عدد ثابت ۶ نسازید. ACT002 ویژه نیست |
| ۶ | فهرست زندهٔ مشاوران فعال | **بله** | `hammasir_provider_list(true)` — title+user_id همراه ACTIVE |
| ۷ | نسخهٔ رضایت و سهمیه پیام از سرور | **بله** | `hammasir_consent_version()` + `hammasir_policy_*` ؛ اگر فرانت API می‌خواهد یک endpoint خواندنی بدون جدول |
| ۸ | پرچم صریح نبود اعلان | **بله** | ثابت config یا پاسخ `{notifications_implemented:false}` — جدول notifications را «هست» نشان ندهید |
| ۹ | کارت دعوت READY/DONE/HIDDEN | **بله با نگاشت** | امروز: `onboarding_seen` + `onboarding_intent` (۰/۱). پیشنهادی بدون ستون: READY=seen0 ؛ HIDDEN=seen1+intent0 ؛ DONE=seen1 و (لینک دارد یا intent مصرف شده). سه رشته در DB نیست |

---

# رجیستر شکاف (برای تصمیم مالک)

1. `note` خلق در DTO مشاور  
2. دو ساعت «امروز» (PHP default vs Asia/Tehran)  
3. دو تعریف هفته (قطعه‌های ۱–۷ ماه vs شنبه–جمعه)  
4. R2 روی دمو نصب نشده  
5. file-mode داشبورد مشاور و خلق  
6. تاریخچهٔ رضایت بدون UI  
7. enum PARTIAL  
8. کارت دعوت سه حالته vs دو فلگ  
9. قفل برنامه از شروع اجرا (نه اولین ثبت) — بازبینی قبلی  
10. ~~کدام درخت کد؟~~ **بسته شد با حکم مالک:** اتحاد همهٔ قابلیت‌های جوما روی دامنه؛ مارکتینگ/رستوران/کارت ویزیت داخل اپ جوما نیستند.

---

# پیوست — B5 «جوجهٔ من» (مرحلهٔ ۱ ساخته شد)

تاریخ: ۱۴۰۵-۰۶-۲۷ · فقط روی تست (بستهٔ `joma/UPLOAD-B5-JOOJE.zip`) · بدون SQL و بدون ستون تازه.

**قرارداد داده (بک‌اند → فرانت):** `jooje_state($user_id)` در `functions/jooje.php` و خروجی JSON آن در
`api/jooje.php` (فقط کاربر واردشده؛ بدون ورود `401`).

| کلید | معنا |
|---|---|
| `stage` | `egg` · `crack` · `chick` — آستانه‌ها از `config/jooje_config.php` (پیش‌فرض ۶ و ۱۲ دونه) |
| `state` | `calm` · `happy` · `sleep` · `faded` · `gray` — «نوازش» سمت رابط است و از بک‌اند نمی‌آید |
| `units` | تعداد ثبت معتبر (هر ثبت = یک دونه). از رخدادها می‌آید و چون رخداد حذف نمی‌شود، هرگز کم نمی‌شود |
| `laid_at` / `cracked_at` / `born_at` | تاریخ جلالی مهرها؛ تولد یک‌بار و برگشت‌ناپذیر |
| `days_since_last` | روز از آخرین ثبت؛ `null` = هیچ ثبتی نیست (صفر نیست). **در رابط نمایش داده نشود** |
| `metrics[]` | سه سنجهٔ موجود + سنجهٔ چهارم با `available:false` |
| `metrics[water]` | `has_source` · `today` · `total` · `target` (از اسنپ‌شات برنامه) · `progress` — بدون منبع، `today:null` |
| `growth` | بعد از تولد: مرحلهٔ فعلی + `next_at_units` + `progress` |
| `pet_name` | نام جوجه (۲..۱۶ نویسه). یک‌بار گذاشتن + یک‌بار عوض‌کردن؛ فقط در صفحهٔ جوجه دیده می‌شود |
| `pet_name_status` | `available` (هنوز نامی ندارد) · `set` (یک‌بار دیگر قابل تغییر) · `locked_after_change` |
| `sharing` | همیشه `private` — با هم‌مسیر به اشتراک گذاشته نمی‌شود |

**B1 مرحلهٔ ۲ (ساخته شد):** کد ۶ رقمی بازیابی رمز، فقط هش ذخیره می‌شود، ۱۵ دقیقه، یک‌بارمصرف،
۵ تلاش، صفحهٔ صدور فقط برای مدیر (`?p=admin_recovery`)، و باطل‌شدن خودکار نشست‌های دیگر پس از
تغییر رمز. بستهٔ `joma/UPLOAD-B1-STEP2-RECOVERY.zip`.

**تصمیم‌های ثبت‌شده:** سنجهٔ چهارم («وقت حمام/تنفس») تا ساخته‌شدن منبع نمایش داده نمی‌شود ·
آب: هر ثبت موجود ACT002 «قطعی» حساب می‌شود · واحد «دونه» = ثبت معتبر · جوجه هرگز نمی‌میرد
و به تخم برنمی‌گردد · هیچ سنجه‌ای با کلیک/نوازش پر نمی‌شود.

**باز (در مرحله‌های بعد):** پیش‌نویس/قطعی آب · ذخیرهٔ نام جوجه · تصویر و انیمیشن شش حالت
(کار فرانت) · صدا (پیش‌فرض خاموش) · «فقط امروز و دیروز» (B3 گزینهٔ الف اجرا شده: فقط آینده بسته است).

**اعداد نمونه که مالک باید تأیید/تغییر دهد:** ترک ۶ دونه · تولد ۱۲ دونه · کمرنگ ۳ روز ·
خاکستری ۶ روز · رشد: جوجهٔ کوچک ۱۲ / نوپا ۴۰ / بالغ ۹۰ · هدف روزانهٔ «دونه» تعیین نشده
(پس فرانت نباید نوار هدف برای دونه بسازد).

---

**بررسی وقتی کامل است که مالک برای هر ردیف رجیستر یکی از نگه/حذف/بساز را بگذارد و سند فرانت با ستون وضعیت همین فایل تطبیق داده شود.**

---

# پیوست ۲ — B6 و B7 (مرحله‌های بعدی: ساخته شد)

**B6 — آب، پیش‌نویس/قطعی** (`functions/water.php` · بستهٔ `joma/JOMA-B6B7-JOURNAL.zip`)

- ثبت آبِ **امروز** = پیش‌نویس: قابل ویرایش مکرر، اما **یک رکورد** برای هر روز (رکورد دوم ساخته نمی‌شود).
- پیش‌نویس **در سنجه‌ها شمرده نمی‌شود** (سنجهٔ آبِ جوجه، شمارش امروز) — قاعدهٔ WTR-10: پیش‌نویس پاداش نمی‌سازد.
- «ثبت نهایی» → قطعی و غیرقابل‌تغییر. ثبت روزهای گذشته خودکار قطعی است (گذشته بازنویسی نمی‌شود).
- دادهٔ قدیمی بدون وضعیت ذخیره‌شده = قطعی (هیچ عدد قبلی عوض نمی‌شود) · انبار: `joma_settings`/`store.json`
  با کلید `water_<user>_<date>` — **بدون هیچ تغییر دیتابیس**.
- خانه فقط «خلاصهٔ خواندنی» دارد؛ نوشتن فقط از «کارهای امروز» (خواندن در چند جا، نوشتن در یک جا).

**B7 — دفترچهٔ جوما + موتور بینش** (`functions/insight.php` · `pages/journal.php`)

- صفحهٔ مستقل «دفترچهٔ جوما» با دو بخش: «از ثبت‌های تو» (بینش) و «نوشته‌های خودت» (یادداشت حال: ویرایش/پاک‌کردن/جست‌وجو/صفحه‌بندی).
- هر بینش با **شواهد اجباری**: شاخص‌ها · بازه · شمار نمونه · دادهٔ ناقص و چه چیزی کنار گذاشته شد ·
  جهت · همان روز/روز بعد · قاعده و نسخه · زمان محاسبه · اعتبار. جملهٔ **«همراهی، دلیل نیست.»** داخل خود متن است.
- بینش‌های این نسخه: بهترین روز هفته · رابطهٔ حال×موفقیت روزانه · پیوستگی ثبت · پایدارترین فعالیت · نسبت ثبت قطعی آب (پیوند با B6).
- بدون دادهٔ کافی: هیچ بینشی ساخته نمی‌شود (حالت صادق)، و هیچ عدد دقت/اطمینان یا نموداری در کارت نیست.
- هیچ‌کدام با «هم‌مسیر» به اشتراک گذاشته نمی‌شود.
- آزمون خودکار همراه بسته: `tests/run-b6b7.php` → **۳۴ آزمون، همه سبز** (در دو درخت تست شد).

**باقی‌مانده‌ها:** B8 (کپی برنامهٔ ماه بعد) · B9 (ستون جنسیت — منتفی شد) · هم‌مسیر ظاهری · صدا/انیمیشن تصویری جوجه (کار فرانت).

---

# پیوست ۳ — B8 (کپی برنامهٔ ماه بعد) — ساخته شد

`functions/plancopy.php` · `pages/plan.php` · بستهٔ `joma/JOMA-B8-NEXT-PLAN.zip` · آزمون `tests/run-b8.php` (۲۹ سبز)

پنج شرط سند `SPEC/18 §۸٫۱` همه رعایت شده:
۱) مقصد فقط ماه بعد و در وضعیت `DRAFT`/`PLANNING` · ۲) مقصد اسنپ‌شات تازهٔ خودش را می‌گیرد ·
۳) منبع، اسنپ‌شات دورهٔ مبدأ است نه کتابخانهٔ زنده · ۴) هیچ رخداد/حالی کپی نمی‌شود ·
۵) فقط وقتی مبدأ قفل است (`RUNNING`/`ARCHIVED`).

رفتار رابط: اگر ماه بعد ساخته شده باشد → «از قبل ساخته شده» + لینک · اگر قفل باشد → دکمه نمی‌آید و دلیل ·
اگر مبدأ خالی باشد → راهنما · بعد از ساخت، کاربر در ماه نو (`DRAFT`) است با پیام «هدف‌ها تا شروع اجرا قابل تغییرند».

**با این، فهرست شکاف‌های بک‌اند B1–B9 بسته شد.** باقی‌مانده‌ها ظاهری/رابط‌اند: هم‌مسیر، انیمیشن و صدای جوجه.

---

# پیوست ۴ — بستهٔ واحد v2 و اصلاح‌های بازخورد مالک

بستهٔ تحویلی: `joma/JOMA-TEST-PACK-v2.zip` — همه‌چیز در یک اکسترکت (۱.۳ مگابایت، ۱۲۴ فایل).

**اصلاح‌های بازخورد (روی همان بسته):**

| ایراد مالک | اصلاح |
|---|---|
| هدر صفحهٔ معرفی دو بار دیده می‌شد | نوار عمومی پوسته در صفحه‌ای که نوار خودش را دارد نمایش داده نمی‌شود (`'no_top' => 1` در `joma_header`) — فقط یک نوار، دو دکمه |
| قاب «یک روز در جوما» کج بود | چرخش قاب حذف شد؛ عرض ۳۲۰px، گوشهٔ ۲۸px، وسط‌چین |
| لیوان آب: خالی‌کردن کار نمی‌کرد · کُند بود · صدای موج/آب نداشت | کلیک روی آخرین لیوان پر = یکی کم؛ کل کارت با `fetch` و پاسخ JSON بدون بارگذاری صفحه؛ صدای آب (WebAudio: «پلینک» + صدای ریختن) با دکمهٔ روشن/خاموش و یادآوری؛ موج سینوسی دو لایه داخل خودِ لیوان (`clip-path` روی بدنه) |
| تمرین تنفس نبود (قرار بود با صدا و انیمیشن) | کارت همیشه‌در‌دسترس در «کارهای امروز» + شیت تمرین ۴-۷-۸ با انیمیشن دایره، شمارش دور و **راهنمای صوتی** سه‌مرحله‌ای با فایل‌های `assets/audio/br-in|hold|out.mp3` (پیش‌فرض خاموش، انتخاب کاربر ذخیره می‌شود) |
| «حال من» مرحله‌به‌مرحله نبود | ویزارد پنج‌قدمی طبق `SPEC/13`: نوار مراحل (حال · انرژی · تمرکز · خواب دیشب · استرس)، هر قدم صحنهٔ رنگ خودش و تصویر SVG با اندازهٔ صریح (چهره · باتری · حلقهٔ هدف · ماه و ستاره · موج)، پرش فقط تا دورترین قدم دیده‌شده، + قدم ششم اختیاری (جملهٔ شخصی، «فقط خودت») |
| ثبت‌نام مرحله‌به‌مرحله نبود | ویزارد چهارگامی طبق `SPEC/22 §۳٫۰٫۱` **بدون فیلد جنسیت** (طبق تصمیم B9): ۱) معرفی ۲) شغل ۳) حساب (نام کاربری + بررسی زنده + رمز) ۴) تماس و تأیید (موبایل · ایمیل · کد امنیتی · قوانین + **خلاصهٔ اطلاعات**). نام همهٔ فیلدها همان قبلی است؛ سرور دست‌نخورده |
| صفحهٔ گزارش‌ها درست کار نمی‌کرد | علت: نصب ناقص بسته‌های قبلی (طرح/گزارش‌ها در گام ۳). در بستهٔ v2 همه با هم نصب می‌شود. علاوه بر آن، هر ده تب روی سه حالت کاربر (بدون برنامه · برنامهٔ خالی · با داده) تست شد: همه ۲۰۰ با صفر خطا؛ نمودارها در «تحلیل موفقیت» (۵)، «روند» (۱)، «خلق» (۱) و تب‌های R2 (همبستگی/روزهای هفته/ترکیب) بارگذاری می‌شوند. |

**تست پذیرش بستهٔ v2 (اکسترکت تازه):** seed بدون هشدار · آزمون خودکار B6/B7 = ۳۴ سبز · B8 = ۲۹ سبز ·
هدر معرفی یک‌بار · ویزاردها از گام درست شروع می‌شوند · کارت آب و تنفس سرجایشان ·
هر ۱۶ صفحهٔ اپ با صفر Warning/Notice.

---

# پیوست ۵ — گام ۵: جان‌بخشی به جوجه + هماهنگی هم‌مسیر

بستهٔ `joma/JOMA-DESIGN-STEP5.zip` (۵۲ کیلوبایت) — روی بستهٔ v2 اکسترکت می‌شود.

**جوجهٔ من (طبق `SPEC/14`):** صحنهٔ زنده با انیمیشن هر حالت از دادهٔ واقعی (نفس آرام · پرش و جرقه و لپ
برای شاد · zzz و خروپف برای خواب · کم‌رنگ آرام · خاکستری بی‌حرکت) · لمس بی‌پاداش با سه جملهٔ سند ·
`petGate` (۴ لمس: فقط خرخر · ۸ لمس: فقط پلک) · کلید مستقل «صدای جوجه» **پیش‌فرض خاموش** با چهار صدا
که با WebAudio ساخته می‌شوند (جیک دو نُتی ۴۷۰→۷۸۰ و ۶۲۰→۹۰۰ با لرزش ۲۲Hz · خرخر مثلثی ۱۱۲→۱۲۶ با
ترمولوی ۲۶Hz + لایهٔ نفس · خروپف دم ۰٫۶۲s و بازدم ۰٫۸۵s · خمیازهٔ نزولی ۳۴۰→۱۹۰) ·
دکمهٔ «بخوابانش/بیدارش کن» (کارکرد رابط، بدون اثر روی سنجه‌ها) · پلک خودکار ·
احترام به `prefers-reduced-motion`.

**هم‌مسیر:** سرصفحهٔ تازه (کیکر · تیتر · توضیح · تراشهٔ حالت) + نشانگرهای واقعی پیام/رویداد/درخواست،
هماهنگی رنگ‌های میز کار مشاور و گفتگو با توکن‌های طرح. هیچ منطق گارد/مجوز/پیام عوض نشد؛
دسترسی‌ها تست شد (مشاور → صفحهٔ مدیران: ۳۰۲).

**تست پذیرش (زنجیرهٔ v2 + گام ۵، اکسترکت تازه):** seed بدون هشدار · B6/B7 = ۳۴ سبز · B8 = ۲۹ سبز ·
هر سه نقش همهٔ صفحه‌ها ۲۰۰ · صحنهٔ جوجه با کلاس حالت درست · مجموع خطای PHP: صفر.
