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
