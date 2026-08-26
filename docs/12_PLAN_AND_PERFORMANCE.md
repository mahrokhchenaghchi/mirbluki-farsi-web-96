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
