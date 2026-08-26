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
