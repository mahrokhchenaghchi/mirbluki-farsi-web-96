# 05 — مرجع توابع (استخراج از کد)

قالب فشرده: **نام** — فایل:خط — ورودی → خروجی — اثر جانبی / جداول

صفحات PHP تابع جدا نیستند؛ منطق در خود فایل صفحه است.

---

## includes/helpers.php

| نام | خط | کار | ورودی | خروجی | جداول |
|---|---|---|---|---|---|
| e | 2 | XSS escape | string | string | — |
| joma_now | 6 | زمان SQL | — | `Y-m-d H:i:s` | — |
| joma_compute_base | 10 | base URL از SCRIPT_NAME | — | string مثل `/joma` یا `''` | — |
| joma_needs_sid | 23 | آیا کوکی Session نیست؟ | — | bool | — |
| joma_append_sid | 28 | افزودن joma_sid به URL | url | url | — |
| joma_redirect | 37 | Location + session_write_close | path | exit | — |
| joma_url | 45 | ساخت URL داخلی | path | string | — |
| joma_asset | 52 | URL استیت | path | string | — |
| csrf_token | 56 | توکن Session | — | hex | Session |
| csrf_field | 63 | hidden csrf + joma_sid | — | HTML | — |
| csrf_check | 68 | رد POST نامعتبر | — | die یا ادامه | — |
| current_user | 78 | Session user | — | array/null | — |
| require_login | 82 | اجبار ورود | — | redirect | — |
| has_perm | 88 | چک مجوز | perm string | bool | — |
| require_perm | 95 | اجبار مجوز | perm | die/ادامه | — |
| current_period_key | 102 | دوره انتخابی Session/GET | — | `YYYY-MM` | Session |
| set_current_period_key | 113 | ذخیره دوره | key | — | Session |
| maybe_mood_gate | 119 | اجبار Mood امروز | page | redirect؟ | moods |
| jobs_list | 129 | شغل‌ها | — | array | — |
| categories_list | 133 | دسته‌ها | — | array | — |
| frequencies_list | 137 | DAILY/WEEKLY/MONTHLY | — | map | — |
| datatypes_list | 141 | چهار نوع | — | map | — |
| units_list | 145 | واحدها + alias کوتاه | — | map | — |
| stickers_list | 162 | استیکرها | — | array | — |
| target_of | 166 | هدف بر اساس frequency | row | float | — |
| format_value | 172 | نمایش مقدار | type,value,unit | string | — |
| plan_editable | 180 | DRAFT/PLANNING | status | bool | — |
| status_label / status_badge | 184–189 | برچسب وضعیت | status | string/HTML | — |
| role_label / access_label | 194–199 | برچسب نقش/سطح | key/n | string | — |
| greeting_fa | 204 | سلام بر اساس ساعت | — | string | — |
| joma_contains | 211 | جستجوی متن | hay,needle | bool | — |
| unspecified_notice | 219 | جعبه کهربایی | html | html | — |
| empty_state | 223 | کارت خالی | title,desc,href,cta | html | — |
| joma_logo | 232 | لوگو یا «ج» | size,compact | html | فایل لوگو |
| flash_set / flash_get | 261–265 | پیام یک‌بارمصرف | — | html | Session |
| nav_items | 273 | منو | — | map | — |
| sparkline_svg | 289 | روند SVG | points | svg | — |
| is_valid_username/email/mobile | 315–323 | فرمت | string | bool | — |
| validate_registration | 327 | همه قوانین ثبت‌نام | array in | پیام یا `''` | users (taken) |
| validate_activity_input | 341 | نام/وزن/هدف | array | پیام یا `''` | — |
| validate_performance_value | 350 | نوع+مقدار | type,value | پیام یا `''` | — |

خطا: CSRF die ؛ require_perm die «دسترسی مجاز نیست.»

---

## includes/store.php

| نام | خط | کار |
|---|---|---|
| store_mode | 2 | `file` یا `mysql` |
| store_path | 6 | مسیر JSON (قابل override با JOMA_STORE_PATH) |
| store_empty | 11 | ساختار خالی |
| store_load / store_save | 26/42 | خواندن/نوشتن JSON |
| store_next_id | 48 | seq++ |
| db | 54 | mysqli یا null |
| store_role_permissions | 67 | آرایه مجوز نقش — **SQL نمی‌خواند** |
| joma_stmt_bind / fetch_all / query / query_one / exec | 76–136 | MySQLi آماده |

---

## includes/jalali.php

تبدیل میلادی↔شمسی، امروز، period_key، طول ماه، کبیسه، حدود ماه، in_period، شروع هفته شنبه، same_week، نام ماه، format نمایش، weekday، اعتبار تاریخ، ارقام فارسی `fa_num`.

---

## includes/layout.php

`joma_header($title, $crumbs, $opts)` خط 2 — HTML شروع، منو، compact از prefs.  
`joma_footer()` خط 64 — منوی موبایل + script.

---

## functions/joma.php — کسب‌وکار

| نام | خط | ورودی مهم | خروجی | جداول | امنیت |
|---|---|---|---|---|---|
| official_library | 2 | — | array 45 | JSON فایل | — |
| copy_seed_to_user | 6 | user_id | void | activities | اگر Seed دارد برمی‌گردد |
| user_by_username | 43 | username/email | user/null | users | lower |
| username_taken / email_taken | 55/68 | + except id | bool | users | — |
| create_user | 81 | array ثبت‌نام | user | users, prefs, activities | hash رمز؛ role ثابت member |
| get_user | 118 | id | user/null | users | — |
| update_user | 127 | id, patch | user | users | فقط name/phone/job در SQL |
| reset_user_password | 143 | id, pass, confirm | پیام یا `''` | users | hash |
| get_prefs / save_prefs | 161/174 | user_id, flags | array/void | preferences | — |
| session_user_array | 196 | user | array Session | — | بدون hash |
| list_user_activities | 212 | user_id | list | activities | فیلتر user |
| get_activity | 225 | id, user_id | row/null | — | از لیست کاربر |
| save_activity | 230 | user, in, id | id | activities | WHERE user_id در SQL |
| delete_activity | 283 | id, user_id | void | activities | همان |
| ensure_period | 297 | user, period_key | period+plan | periods, plans | — |
| get_plan_by_period | 343 | user, period_id | plan | plans | — |
| list_periods | 352 | user_id | list+status | periods+plans | ماه جاری را می‌سازد |
| get_plan | 374 | plan_id, user_id | plan | plans | user_id |
| list_plan_activities | 383 | plan_id, user_id | list | plan_activities | — |
| add_plan_activity | 394 | user, plan, activity, over | پیام یا `''` | plan_activities | قفل + تکراری + target |
| update_plan_activity | 449 | user, plan, pa_id, patch | پیام | plan_activities | قفل |
| remove_plan_activity | 474 | user, plan, pa_id | پیام | plan_activities | قفل |
| can_transition | 490 | from, to | bool | — | — |
| transition_plan | 500 | user, plan, next | پیام | plans | نقشه وضعیت + حداقل ۱ فعالیت |
| list_events | 527 | plan_id, user_id | list | events | — |
| can_register_performance | 537 | plan, pa, date, value, existing | پیام یا `''` | — | قوانین DAILY |
| register_performance | 555 | user, plan, pa, date, value | پیام | events | صدا می‌زند can_* |
| events_for / sum_actual / weekly_actual / displayed_actual / has_daily_registration | 587–613 | — | list/number/bool | — | — |
| get_mood / list_moods / save_mood | 618–639 | — | row/list/void | mood | یکتا روز |
| weight_sum | 687 | acts | int | — | — |
| build_report | 693 | user, plan | array گزارش | plan_act, events, moods | achievement ثابت |

---

## JavaScript `assets/js/joma.js`

IIFE بدون export.

- منوی `data-more-toggle`
- hint username + `fetch api/username.php`
- hint phone / email / password
- کلیک `.moodbtn` (صفحه Mood بیشتر با radio CSS کار می‌کند)

Submit فرم‌ها JS نیست.

## api/username.php

GET `u` → JSON `{ok: bool}` از `!username_taken`.
