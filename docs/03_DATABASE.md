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
