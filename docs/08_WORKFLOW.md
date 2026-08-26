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
