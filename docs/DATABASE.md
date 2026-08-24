# پایگاه داده جوما

PostgreSQL روی Supabase. مهاجرت کامل:

`supabase/migrations/20260824120000_joma_core.sql`

## جدول‌ها

| جدول | نقش |
|---|---|
| `joma_profiles` | پروفایل کاربر |
| `joma_activities` | کتابخانه فعالیت |
| `joma_periods` | دوره ماهانه کاربر |
| `joma_plans` | برنامه دوره |
| `joma_plan_activities` | تصویر ثابت فعالیت در برنامه |
| `joma_performance_events` | رویدادهای عملکرد |
| `joma_mood_records` | ثبت خلق روزانه |
| `joma_mood_metric_definitions` | تعریف آینده شاخص‌های خلق |
| `joma_report_projections` | تصویر گزارش |
| `joma_settings` | تنظیمات قابل گسترش |

## قیود مهم

- یک برنامه برای هر کاربر و هر دوره
- یک PlanActivity برای هر فعالیت در یک برنامه
- ایندکس یکتا برای فعالیت روزانه: `(plan_activity_id, performance_date) WHERE frequency = 'DAILY'`
- یک ثبت خلق در هر روز برای هر کاربر

## توابع

- `joma_ensure_period(...)` ساخت دوره جاری و برنامه پیش‌نویس
- `joma_register_performance(...)` ثبت امن عملکرد با کنترل تکرار روزانه

## بازسازی

پروژه Supabase جدید بسازید، مهاجرت را اجرا کنید، URL و anon key را در محیط بگذارید.
