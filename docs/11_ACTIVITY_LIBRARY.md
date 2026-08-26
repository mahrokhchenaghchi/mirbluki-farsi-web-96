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
