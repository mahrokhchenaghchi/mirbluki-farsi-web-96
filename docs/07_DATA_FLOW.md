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
