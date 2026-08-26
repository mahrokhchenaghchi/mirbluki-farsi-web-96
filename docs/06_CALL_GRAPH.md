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
