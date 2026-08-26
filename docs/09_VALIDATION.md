# 09 — Validation کامل (از کد)

علامت‌ها: C = Client JS (`joma.js`) ؛ S = Server PHP

## ثبت‌نام (`validate_registration` + صفحه + JS)

| فیلد | Required | C | S |
|---|---|---|---|
| first_name / last_name | بله | HTML required | خالی نباشد |
| username | بله | طول ۳–۲۰، شروع حرف انگلیسی، `[A-Za-z0-9._]`، fetch یکتا | regex `^[a-zA-Z][a-zA-Z0-9._]{2,19}$` + `username_taken` |
| email | بله | الگوی ساده `@` و نقطه | `filter_var FILTER_VALIDATE_EMAIL` + `email_taken` |
| phone | بله | `^09[0-9]{9}$` | همان |
| job | بله | select | باید عضو `jobs_list()` باشد |
| password | بله | حداقل ۶ | `strlen >= 6` |
| confirm | بله | برابر رمز | برابر رمز |
| accept | بله | checkbox required | `empty(accept)` رد |

یکتایی username/email فقط سمت سرور قطعی است (JS ممکن است fail شود).

## ورود (`login.php`)

- identifier و password خالی: «نام کاربری و رمز عبور را وارد کنید.»
- کاربر نیست: «حسابی با این نام کاربری یا ایمیل پیدا نشد.»
- رمز غلط: «نام کاربری/ایمیل یا رمز عبور نادرست است.»
- identifier برای جستجو `strtolower`
- HTML required روی فیلدها

## فراموشی رمز

- رمز ≥ ۶ و برابر تکرار
- identifier باید کاربر موجود باشد

## فعالیت (`validate_activity_input`)

- name خالی نباشد
- weight ≥ 0
- هدف تناوب فعلی `> 0` (daily یا weekly یا monthly بر اساس frequency)
- Client: HTML required روی name

**نکته:** بعضی Seedها daily_target=0 با frequency WEEKLY/MONTHLY دارند. Validation هنگام **ذخیره فرم کتابخانه** هدف تناوب انتخاب‌شده را چک می‌کند، نه هر سه هدف.

## عملکرد (`validate_performance_value` + `can_register_performance`)

| نوع | قانون |
|---|---|
| همه | عدد باشد، منفی نباشد |
| RATING | ۱ تا ۵ |
| BOOLEAN | دقیقاً 0 یا 1 |
| تاریخ | `jalali_is_valid` و داخل دوره |
| پلن | status باید RUNNING |
| DAILY | تکراری همان plan_activity + همان تاریخ رد |

Client: `required` روی عدد؛ BOOLEAN/RATING با radio.

## Mood

- هر پنج مقدار ۱..۵ وگرنه «هر پنج شاخص را انتخاب کنید.»
- یادداشت اختیاری
- یکتایی روز در DB/JSON

## تنظیمات

- موبایل اگر پر باشد باید الگوی ایران باشد
- شغل از فهرست

## وزن در برنامه

- در `add_plan_activity` / `update_plan_activity`: weight ≥ 0 ، target > 0 ، frequency یکی از DAILY/WEEKLY/MONTHLY
