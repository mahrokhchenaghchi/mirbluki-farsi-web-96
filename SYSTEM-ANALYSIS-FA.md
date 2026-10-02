# تحلیل کامل سامانهٔ آزمون‌های روان‌شناختی میربلوکی
## `test.mirbolouki.com` — گردش کار، گردش داده و معماری از منظر توسعه‌دهنده

> **این سند برای کیست؟** برای توسعه‌دهنده‌ای که **هیچ‌وقت سامانه را ندیده** و باید فقط با خواندن همین فایل، منطق، صفحات، جداول و جریان‌های داده را آن‌قدر دقیق بفهمد که بتواند بدون شکستن چیز develop یا debug کند.
> **منبع:** این تحلیل مستقیماً از خود کدبیس (نسخهٔ 1.3.6a) استخراج شده — نه از حافظه و نه از حدس.
> **تاریخ تحلیل:** مهر ۱۴۰۵ (اکتبر ۲۰۲۶)

---

## فهرست

۱. [شناسنامهٔ سامانه](#1)
۲. [معماری فنی و ساختار کد](#2)
۳. [مدل داده (جداول، روابط، چرخهٔ وضعیت‌ها)](#3)
۴. [نقش‌ها، احراز هویت و دسترسی‌ها](#4)
۵. [سفر کاربر (مراجع) — گام‌به‌گام با گردش داده](#5)
۶. [موتور آزمون و نمره‌دهی](#6)
۷. [گردش مالی: فیش ← تأیید ← تفسیر ← انتشار](#7)
۸. [قیف فروش: پکیج طلایی، جوما، بستهٔ تمرینی](#8)
۹. [سامانهٔ پیامک](#9)
۱۰. [گزارش‌های PDF](#10)
۱۱. [ماژول تحلیل زوجین](#11)
۱۲. [ماژول تحلیل فردی (v1.3.6)](#12)
۱۳. [نقشهٔ کامل پنل مدیریت](#13)
۱۴. [امنیت لایه‌به‌لایه](#14)
۱۵. [قواعد و محدودیت‌های توسعه](#15)
۱۶. [واژه‌نامهٔ سریع](#16)
۱۷. [خط زمانی نسخه‌ها](#17)

---

<a name="1"></a>
## ۱) شناسنامهٔ سامانه

| مورد | مقدار |
|---|---|
| **نام** | سامانهٔ تخصصی آزمون‌های روان‌شناختی دکتر جواد میربلوکی |
| **دامنه** | `test.mirbolouki.com` (سایت اصلی: `mirbolouki.com`) |
| **ماهیت** | پلتفرم اجرای آزمون‌های روان‌سنجی فرم‌بلند بالینی + گزارش + فروش تفسیر متخصص + ماژول تحلیل زوجین/فردی برای درمانگر |
| **فناوری** | PHP 7.x سازگار (بدون فریم‌ورک، بدون Composer، بدون کلاس در کد اپلیکیشن) |
| **دیتابیس** | MySQL/MariaDB از طریق **MySQLi** (کاملاً Prepared Statement — بدون PDO) |
| **استقرار** | هاست اشتراکی cPanel؛ docroot مثال: `/home/mirbolouki/test` |
| **PDF** | کتابخانهٔ TCPDF **باندل‌شده** در `includes/tcpdf/` |
| **پیامک** | سرویس SMS.ir (REST API) — حالت توسعه بدون کلید هم دارد |
| **پرداخت** | **درگاه آنلاین ندارد** — کارت‌به‌کارت + آپلود فیش + تأیید دستی مدیر |
| **زبان/جهت** | فارسی، RTL، تاریخ شمسی (تبدیل g2j داخلی)، اعداد فارسی |
| **نسخهٔ فعلی کدبیس** | 1.3.6a (اکتبر ۲۰۲۶) |

**در یک جمله:** مراجع آزمون می‌دهد ← سامانه رایگان کارنامهٔ اولیه (وب) می‌سازد ← برای دیدن نتیجه و گزارش کامل، فیش واریز آپلود می‌کند ← مدیر تأیید و تفسیر می‌نویسد و «منتشر» می‌کند ← مراجع گزارش وب+PDF می‌گیرد. موازی روی آن، مدیر برای درمانگر پروندهٔ زوجین/تحلیل فردی می‌سازد.

---

<a name="2"></a>
## ۲) معماری فنی و ساختار کد

### ۲.۱ سبک معماری
- **سبک:** PHP رویه‌ای (procedural) — فقط تابع، بدون کلاس اپلیکیشنی. هر صفحه = یک اسکریپت مستقل که در بالای آن requireهای مشترک می‌آید و بعد HTML.
- **لایه‌بندی واقعی (ضمنی):**
  - **لایهٔ دسترسی داده:** `includes/db.php` — هشت تابع عمومی روی mysqli: `db_connect`, `db_query` (prepared با types رشته‌ای مثل `'is'`), `db_fetch_one`, `db_fetch_all`, `db_insert` (بازگشت insert_id), `db_update`, `db_delete`, `db_count`.
  - **لایهٔ دامنه (Domain):** `includes/engine.php` (آزمون و نمره), `couple_engine.php` (تحلیل زوج/فرد), `package_engine.php` (بستهٔ تمرینی), `offer.php` (قیف فروش/اشتراک جوما).
  - **لایهٔ زیرساخت:** `sms.php` (پیامک/OTP), `pdf_engine.php` (PDF), `joma_library.php` (پل کتابخانهٔ تمرین جوما), `auth.php` (نشست‌ها), `functions.php` (کمک‌کارها).
  - **لایهٔ ارائه:** صفحات ریشه/`user/`/`admin/` + `header.php`/`footer.php` + CSS خام در `assets/css` و JS سبک در `assets/js`.
- **هیچ روتینگ/کانتrolری وجود ندارد** — URL = مسیر فایل. لینک‌ها با `/` مطلق از ریشهٔ سایت هستند.
- **CSRF:** همهٔ فرم‌ها و اکشن‌های POST از `csrf_field()` / `csrf_verify()` رد می‌شوند (توکن در `$_SESSION`).
- **Flash message:** `set_flash(type,msg)` + `render_flash()` (سشن-محور).
- **حالت توسعهٔ پیامک:** اگر `SMS_PROVIDER='dev'` باشد، پیامک واقعی نمی‌رود؛ کد OTP روی صفحه چاپ و در `sms_outbox` با وضعیت `dev_sent` ثبت می‌شود.

### ۲.۲ نقشهٔ پوشه‌ها

```
/
├── index.php                 ← لندینگ: معرفی، فهرست آزمون‌های فعال، CTA
├── install.php               ← نصب‌کنندهٔ تحت‌وب (فقط بار اول؛ بعد باید حذف/محافظت شود)
├── reset_admin.php           ← ابزار ریست رمز admin (ابزار عملیاتی — باید پس از use حذف شود)
├── download_pdf.php          ← ریدایرکت‌کنندهٔ کوتاه به user/download_report.php
├── cron-reminders.php        ← cron یادآور پیامکی (هر ۱۰ دقیقه از cPanel صدا زده می‌شود)
├── config/config.php         ← پیکربندی (از الگوی config.example.php)
├── includes/                 ← همهٔ منطق مشترک (۲۱ فایل، جدول پایین)
├── tests/
│   ├── index.php             ← فهرست آزمون‌های فعال (عمومی)
│   ├── intro.php             ← معرفی علمی آزمون + دکمهٔ شروع
│   └── take.php              ← اجرای سوالات (رادیوباتن، اجباری)
├── ajax/
│   ├── submit-test.php       ← دریافت پاسخ‌ها، محاسبه، ذخیره
│   ├── save-info.php         ← تکمیل هویت مهمان و نهایی‌سازی نتیجه
│   └── submit-receipt.php    ← آپلود امن فیش واریزی
├── user/                     ← پنل مراجع (لاگین OTP، داشبورد، کارنامه، پرداخت، پکیج)
├── admin/                    ← پنل مدیریت (۱۵+ صفحه) + admin/couples/ (۹ صفحه)
├── database/                 ← install.sql + ۱۳ فایل migration/additive update + seedها
├── uploads/
│   ├── receipts/             ← فیش‌های واریزی (نام‌گذاری تصادفی‌شده)
│   └── reports/              ← کش PDFهای تولیدشده
└── assets/                   ← css/js خام (بدون build)
```

### ۲.۳ فایل‌های `includes/` (نقشهٔ بارگذاری)

| فایل | خطوط | مسئولیت |
|---|---|---|
| `db.php` | ۲۵۴ | هندلر MySQLi؛ همهٔ کوئری‌ها prepared |
| `functions.php` | ۳۱۵ | clean_input، escape_html/e، csrf، flash، redirect، json_response، to_persian_num، شمسی، بج وضعیت‌ها، log_event |
| `auth.php` | ۱۵۰ | admin_login (password_hash)، user_login_by_mobile، require_*_login، logoutها |
| `engine.php` | ۸۷۸ | موتور آزمون: ساختار، سشن، اعتبارسنجی، نمره‌دهی، ذخیره، تفسیر پایه |
| `couple_engine.php` | ۱۳۰۱ | موتور تحلیل زوجین + فردی + KB + wash واژگان |
| `couple_kb_fa.php` | ۳۴۴ | seed فارسی پایگاه‌دانش تحلیل (interpretation/pattern/cycle/… ) |
| `couple_narrative_fa.php` | ۶۹۴ | قالب‌های نثر فارسی گزارش (۲۴ تابع couple_nt_*) |
| `couple_ai_adapter.php` | ۱۲۷ | آداپتور اختیاری AI — پیش‌فرض خاموش، هستهٔ قاعده‌محور مستقل از آن |
| `sms.php` | ۶۸۶ | SMS.ir API، صف outbox، OTP (تولید/راستی‌آزمایی/ریت‌لیمیت)، نوتیف‌ها |
| `pdf_engine.php` | ۷۰۹ | PDF کارنامهٔ اولیه/پیشرفته + کش |
| `package_engine.php` | ۵۷۰ | موتور انتخاب تمرین و ساخت/انتشار بستهٔ تمرینی |
| `offer.php` | ۱۴۳ | قیف پکیج طلایی + اشتراک جوما (فعال/لغو/چک) |
| `joma_library.php` | ۱۰۶ | پل به `database/joma/library_official_full.json` (۱۰۷ فعالیت رسمی جوما) |
| `header.php` / `footer.php` | — | قالب عمومی سایت |
| `tcpdf/` | — | کتابخانهٔ TCPDF |

---

<a name="3"></a>
## ۳) مدل داده

### ۳.۱ نقشهٔ کل جداول (۲۶ جدول)

**هستهٔ آزمون (install.sql):**
`admins`, `users`, `tests`, `test_questions`, `test_options`, `test_dimensions`, `test_scoring_rules`, `test_sources`, `test_sessions`, `user_answers`, `test_results`, `interpretation_requests`, `full_interpretations`, `system_logs`

**پیامک و OTP (v1.1.0):**
`sms_otp_codes`, `sms_outbox`

**پکیج تمرینی و جوما (v1.1.0–v1.2.3):**
`exercise_bank`, `exercise_packages`, `package_items`, `package_progress`, `joma_subscriptions`

**زوجین/فردی (v1.3.0–v1.3.6):**
`couple_cases`, `couple_case_members`, `couple_assessments`, `couple_kb_entries`, `couple_reports`, `couple_case_files`

### ۳.۲ ستون‌های کلیدی (سطح فیلد مهم)

```
users(id, mobile UNIQUE, first_name, last_name, gender, age, ip_address, created_at, updated_at)
tests(id, slug, title, original_name, version, intro_text, scientific_profile,
      estimated_minutes, accent_color, is_active, is_original_instrument, sort_order)
test_dimensions(id, test_id, code, title, min_score, max_score, color_hex, …)
test_questions(id, test_id, dimension_id, question_number, question_text, is_reverse_scored)
test_options(id, test_id, option_number, option_text, score_value)
test_scoring_rules(id, test_id, dimension_id, min_range, max_range,
                   category_name, severity_level, short_interpretation)
test_sessions(id, session_token, test_id, test_version, user_id NULL, 
              status ENUM(started,completed,abandoned), ip_address, user_agent)
user_answers(id, session_id, question_id, option_id, …)
test_results(id, session_id, user_id, test_id, test_version, total_score,
             dimension_scores_json, overall_category, overall_severity,
             short_interpretation, scientific_audit_trail_json, 
             user_visible TINYINT(1) DEFAULT 0   ← v1.3.5 گیت پرداخت, created_at)
interpretation_requests(id, request_code "MB-XXXXXX-999", user_id, result_id, test_id,
             amount, product_type DEFAULT 'advanced_analysis'  ← v1.2.0,
             payment_status ENUM(...۱۰ وضعیت...), receipt_file, user_notes, admin_notes,
             requested_at, payment_verified_at, published_at)
full_interpretations(id, request_id, result_id, user_id, test_id,
             scientific_interpretation, admin_additional_notes, clinical_recommendations,
             status ENUM(draft,published,unpublished), created_by_admin_id)
sms_otp_codes(id, mobile, code_hash, purpose, ip_address, attempts, is_used, expires_at, used_at)
sms_outbox(id, recipient, message, related_type, related_id,
             status ENUM(queued,sent,failed,dev_sent,scheduled), error_msg, attempts,
             send_datetime ← ارسال زمان‌بندی‌شده, provider_message_id, sent_at)
exercise_bank(id, code, title, instructions, category, modality, schema_codes,
             target_severity, duration_minutes, difficulty, source_ref,
             joma_category, joma_unit, joma_frequency, joma_daily_target, joma_sticker, joma_color)
exercise_packages(id, result_id, user_id, test_id, title,
             status ENUM(draft,published,...), duration_days, rationale_json,
             generated_by_admin_id, published_by_admin_id)
package_items(id, package_id, exercise_id, day_number, sort_order, custom_instruction, joma_snapshot_json)
package_progress(id, package_id, item_id, user_id, is_done, note, done_at)
joma_subscriptions(id, user_id, plan, source, request_id, starts_at, expires_at, status, admin_note)
couple_cases(id, alias, status ENUM(awaiting_data,ready,completed,archived), notes, created_by)
couple_case_members(id, case_id, side ENUM('A','B'), user_id NULL, result_id NULL, test_id,
             data_source ENUM('system','manual'...), assessment_code, assessment_version,
             manual_scores_json, display_label,
             data_confirmed TINYINT, scores_confirmed TINYINT)
couple_assessments(id, code, name, version, source, scoring_method, score_range_json,
             dimensions_json, bands_json, comparison_mode, status, effective_date)
couple_kb_entries(id, entry_type, assessment_code, assessment_version, dimension_code,
             dimension_b_code, title, body_json, confidence, source_note, status, effective_date)
couple_reports(id, case_id, version_no, analysis_json, clinician_edits_json, ai_meta_json,
             is_final TINYINT, finalized_at, created_by, created_at,
             subject_user_id INT DEFAULT 0  ← v1.3.6؛ اگر >0 یعنی «تحلیل فردی»)
```

### ۳.۳ نمودار روابط (ERD متنی)

```
users 1───∞ test_results ∞───1 tests ─┬─∞ test_questions ───1 test_dimensions
                                      ├─∞ test_options
                                      ├─∞ test_scoring_rules
                                      └─∞ test_sources
test_results 1───1 test_sessions 1───∞ user_answers
test_results 1───1 interpretation_requests 1───0..1 full_interpretations
test_results 1───0..1 exercise_packages 1───∞ package_items ∞───1 exercise_bank
users 1───∞ package_progress
users 1───∞ joma_subscriptions
users 1───∞ sms_outbox (منطقی، با related_type/related_id)
users 1───∞ couple_case_members ∞───1 couple_cases 1───∞ couple_reports
couple_kb_entries / couple_assessments: مستقل (پیکربندی موتور زوجین)
```

### ۳.۴ چرخهٔ وضعیت‌ها (State Machines)

**`interpretation_requests.payment_status` — ستون فقرات گردش مالی:**
```
not_requested → requested → awaiting_payment → receipt_submitted
   → payment_pending_verification → payment_confirmed
   → interpretation_draft → interpretation_ready → published
                                ↘ rejected (فیش رد شد → کاربر دوباره آپلود می‌کند)
```

**`test_sessions.status`:** `started → completed | abandoned`

**`full_interpretations.status`:** `draft → published → unpublished`

**`couple_cases.status`:** `awaiting_data → ready → completed → archived`
(تغییر خودکار با `couple_case_recompute_status()`: هر دو سمت «تأییدشده» داشته باشند = ready)

**`exercise_packages.status`:** `draft → published` (و برگشت با unpublish)

**`sms_outbox.status`:** `queued → sent | failed | dev_sent` (+ `scheduled` با send_datetime)

---

<a name="4"></a>
## ۴) نقش‌ها، احراز هویت و دسترسی‌ها

### ۴.۱ سه نقش

| نقش | کجا وارد می‌شود | احراز هویت | نشست |
|---|---|---|---|
| **مهمان** | بدون ورود | — | فقط توکن `session_token` برای پیوند نتیجهٔ آزمون به هویت بعدی |
| **مراجع (کاربر)** | `/user/login.php` | **OTP پیامکی دومرحله‌ای** (فقط شماره‌هایی که در `users` موجودند — ثبت‌نام جداگانه وجود ندارد؛ حساب هنگام اولین آزمون ساخته می‌شود) | `$_SESSION['user_id']` |
| **مدیر (درمانگر)** | `/admin/login.php` | username + password (password_hash/verify)، جدول `admins` (فیلد role هست ولی در کد تفکیک نقش اجرایی وجود ندارد — تک‌نقش عملی) | `$_SESSION['admin_id']` + `admin_logged_in` |

### ۴.۲ جریان OTP (فایل‌ها: `user/login.php` + `includes/sms.php`)

1. کاربر شماره می‌دهد ← اعتبارسنجی `validate_iran_mobile` (الگوی 09xxxxxxxxx) ← **باید از قبل در `users` باشد** وگرنه خطای «ابتدا در آزمون شرکت کنید».
2. `otp_rate_limit_check`: سقف تلاش بر اساس شماره+IP.
3. `otp_generate_and_send`: کد ۶رقمی ← ذخیرهٔ **hash** کد در `sms_otp_codes` با `expires_at = now+120s` و `attempts=0` ← ارسال با قالب تأییدشدهٔ SMS.ir (پارامتر CODE).
4.Cooldown ارسال مجدد: **۹۰ ثانیه**؛ سقف تلاش ورود هر کد: **۵ بار** (attempts++)؛ یک‌بارمصرف (`is_used`).
5. تأیید ← `user_login_by_mobile` ← redirect به داشبورد.

### ۴.۳ ماتریس دسترسی (چه کسی چه چیزی می‌بیند)

| امکان | مهمان | مراجع | مدیر |
|---|:---:|:---:|:---:|
| فهرست/معرفی/اجرای آزمون | ✅ | ✅ | ✅ |
| کارنامهٔ وب (result.php) | ❌ | فقط اگر `user_visible=1` | ✅ همیشه |
| دانلود PDF | ❌ | فقط پس از انتشار توسط مدیر | ✅ (+ پیش‌نمایش پیش‌نویس) |
| آپلود فیش/پیگیری مالی | ❌ | ✅ (فقط نتایج خودش) | — |
| بستهٔ تمرینی | ❌ | فقط با اشتراک جومای فعال | ✅ |
| پنل ادمین | ❌ | ❌ | ✅ |
| تحلیل زوجین/فردی + PDF آن | ❌ | ❌ | ✅ |
| خروجی CSV نتایج | ❌ | ❌ | ✅ |

**گیت اصلی داده (از v1.3.5):** `test_results.user_visible` — پیش‌فرض 0. کاربر تا وقتی 0 است کارنامه را **قفل** می‌بیند (با پیام «پس از انتشار توسط مدیریت فعال می‌شود»). فقط مدیر در `admin/result-detail.php` با دکمهٔ publish/unpublish آن را 1/0 می‌کند. `download_report.php` هم همین گیت را برای PDF چک می‌کند. (نکتهٔ سازگاری: اگر ستون در DB نباشد، رفتار قدیمی حفظ می‌شود تا نصبِ میانه سایت را نشکند.)

---

<a name="5"></a>
## ۵) سفر کاربر (مراجع) — گام‌به‌گام با گردش داده

```
[مهمان] صفحهٔ اصلی index.php ──► tests/index.php (فهرست ۱۱ آزمون فعال)
   │
   ├─ tests/intro.php?slug=…   (مشخصات علمی: تعداد سوال، زمان، منبع، مقیاس‌ها)
   │
   ▼
tests/take.php?slug=…  ── پاسخ‌دهی (رادیوباتن، اجباری، JS: test-runner.js)
   │
   ▼ POST ajax/submit-test.php
   │   ① csrf_verify  ② engine_validate_answers (همهٔ سوالات، گزینه‌های متعلق به آزمون)
   │   ③ engine_calculate_scores  (محاسبهٔ قطعی سمت سرور — اعتماد به کلاینت = صفر)
   │   ④ engine_create_session → test_sessions(token, version)
   │   ⑤ پاسخ‌ها+نمرات در $_SESSION[pending_*]
   │
   ├─ اگر لاگین: engine_save_test_result → test_results ← ریدایرکت به صفحهٔ پرداخت
   │
   └─ اگر مهمان: user/complete-info.php?token=…
          (نام، جنسیت، سن، موبایل)
          ▼ POST ajax/save-info.php
          ① یافتن/ساخت users  ② اتصال user_id به test_sessions
          ③ engine_save_test_result → test_results (+user_answers قطعی)
          ▼
   user/request-interpretation.php?result_id=…   (صفحهٔ پرداخت: شماره‌کارت + مبلغ + فرم فیش)
          ▼ POST ajax/submit-receipt.php
          ① CSRF + لاگین + مالکیت result  ② finfo MIME + پسوند (jpg/png/webp/pdf) + ≤۵MB
          ③ ذخیرهٔ uploads/receipts/receipt_{id}_{rand}.ext
          ④ ساخت interpretation_requests (payment_status=receipt_submitted)
          ⑤ پیامک به مدیر «فیش جدید» + پیامک به کاربر «نتیجه پس از بررسی»
          ▼
   [مدیر] admin/requests.php → request-detail.php:
          verify_payment → payment_confirmed (+ پیامک)
          regenerate_engine / نگارش دستی تفسیر → save_interpretation (draft/published)
          در صورت published: published_at ثبت + کاربر پیامک «منتشر شد» می‌گیرد
   [مدیر] admin/result-detail.php: دکمهٔ «انتشار برای مراجع» → user_visible=1
          ▼
   [کاربر] داشبورد: قفل باز می‌شود ← user/result.php (کارنامهٔ وب: نمودار/جدول ابعاد)
          + user/download_report.php?type=initial|advanced → PDF (با کش در uploads/reports)
```

**نکات ظریف این سفر (مهم برای دیباگ):**
- «پرداخت» در این سامانه **گیت نرم است**: ابتدای مسیر (نسخه‌های قدیم) کاربر بلافاصله کارنامه می‌دید؛ از v1.3.5 تا «انتشار مدیر» صبر می‌کند. یعنی جریان پول و جریان انتشار از هم جدا هستند.
- پاسخ‌های موقت مهمان تا لحظهٔ save-info در `$_SESSION` (pending_*) می‌مانند؛ اگر نشست بین راه بمیرد، save-info می‌تواند از `user_answers` موقتِ همان session_token بازتولید کند.
- `active_sid()`: اگر کوکی سشن موجود نباشد، PHPSESSID به URL چسبانده می‌شود (برای هاست‌هایی که کوکی در iframe مشکل دارد) — در لینک‌های ریدایرکت دیده می‌شود.
- `request_code` فرمت `MB-XXXXXX-999` دارد (uniqid + رندم) — شناسهٔ انسانیِ گفت‌وگو با کاربر.

---

<a name="6"></a>
## ۶) موتور آزمون و نمره‌دهی (`includes/engine.php`)

### ۶.۱ اصل طراحی: Assessment-Agnostic
موتور **هیچ اسم آزمونی را نمی‌شناسد**. هر آزمون فقط داده است: ردیف در `tests` + سوالات/گزینه‌ها/ابعاد/قواعدش. اضافه‌کردن آزمون جدید = INSERT داده، بدون یک خط کد. (MMPI-2 با ۵۶۷ سوال دوگزینه‌ای تا Rosenberg ۱۰ سوالی همه از یک مسیر رد می‌شوند.)

### ۶.۲ بانک آزمون‌ها (۱۱ آزمون فرم‌بلند نصب‌شده)

| # | slug | آزمون | سوال | گزینه |
|---|---|---|---|---|
| 1 | mmpi-2-full | MMPI-2 (فرم ۵۶۷) | ۵۶۷ | ۲گزینه‌ای |
| 2 | neo-pi-r-full | NEO-PI-R | ۲۴۰ | ۵ درجه‌ای |
| 3 | young-schema-full-l3 | YSQ-L3 (طرحواره‌های یانگ) | ۲۳۲ | ۶ درجه‌ای |
| 4 | cattell-16pf-full | 16PF (ویرایش ۵) | ۱۸۵ | ۳گزینه‌ای |
| 5 | csi-32-full | CSI-32 (رضایت زوجین) | ۳۲ | ۰–۵ |
| 6 | dass-21 | DASS-21 | ۲۱ | ۰–۳ |
| 7 | gad-7 | GAD-7 | ۷ | ۰–۳ |
| 8 | phq-9 | PHQ-9 | ۹ | ۰–۳ |
| 9 | rosenberg-self-esteem | Rosenberg عزت‌نفس | ۱۰ | ۴ درجه‌ای |
| 10 | satisfaction-with-life | SWLS رضایت از زندگی | ۵ | ۷ درجه‌ای |
| 11 | attachment-style | ECR-R دلبستگی | ۳۶ | ۷ درجه‌ای |

(هنجارهای ایرانی هر آزمون در `test_sources` ثبت است: پوراعتماد/براهنی، حق‌شناس، یوسفی، کرمی، بشارت و…)

### ۶.۳ الگوریتم `engine_calculate_scores`

```
برای هر سوال q از questions(آزمون):
   گزینهٔ انتخابی کاربر → option.score_value  (raw)
   اگر q.is_reverse_scored=1:
        final = (min(score_value همهٔ گزینه‌ها) + max(...)) - raw
   total_score += final
   اگر سوال به dimension_id تعلق دارد:
        dimension_scores[dim] += final   (raw_score و item_count هم)
پس از پیمایش، برای هر بُعد:
   percentage = (raw_score - min_score) / (max_score - min_score) × 100   ← نرمال‌سازی بازهٔ واقعی بُعد
   average_score = raw / item_count
   سطح شدت:
      آزمون یانگ (YSQ): طبق spec داخلی v2.1 → ۵ سطح:
        <۳۹٪ normal «غیرفعال/سازگار»، <۵۹ low «خفیف»، <۷۹ moderate «متوسط»،
        <۹۴ high «بالا/فعال»، ≥۹۴ severe «بسیار شدید/مسلط»
      سایر آزمون‌ها: اول تطبیق با test_scoring_rules (min_range≤raw≤max_range →
        category_name/severity_level/short_interpretation از DB)
        اگر قاعده‌ای نخورد: پیش‌فرض درصدی (≥۸۰ severe، ≥۶۰ high، ≥۴۰ moderate، وگرنه normal)
خروجی = آرایهٔ کامل + audit_trail (به‌ازای هر سوال: متن سوال، گزینهٔ انتخاب‌شده،
        نمرهٔ خام، معکوس‌بودن، نمرهٔ نهایی، بُعد) → در test_results.scientific_audit_trail_json
```

**چرا audit trail مهم است:** هر عدد در گزارش قابل ردیابی به گویه است — هم برای حسابرسی بالینی هم برای دفاع در برابر «چرا این نمره شد؟».

### ۶.۴ ذخیرهٔ نتیجه — `engine_save_test_result`
```
UPDATE test_sessions SET status=completed, user_id=… 
DELETE user_answers قبلی این session (idempotent)
DELETE test_results قبلی این session
INSERT test_results(total_score, dimension_scores_json, …, test_version=tests.version)
```
یعنی همان سشن اگر دوباره ذخیره شود **تکرار نمی‌سازد** — همیشه یک نتیجه به‌ازای هر اجرا.

### ۶.۵ لایه‌های معنایی بالای نمره (مخصوص YSQ)
- `engine_calculate_schema_domains`: تجمیع ۱۸ طرحواره در ۵ حوزهٔ تحولی یانگ.
- `engine_evaluate_schema_patterns`: شناسایی الگوهای شناخته‌شده (مثلاً ترکیب‌های کلاسیک طرحواره‌ها).
- `engine_generate_treatment_map`: نقشهٔ درمان (کدام طرحواره اولویت مداخله).
- `engine_generate_full_interpretation(result_id)`: **پیش‌نویس ماشینیِ تفسیر علمی استاندارد** (متن ۱۳سطحی فارسی: هدر، جدول ابعاد، برچسب شدت‌ها، تفسیر هر مقیاس، توصیه‌ها). مدیر در request-detail با دکمهٔ `regenerate_engine` آن را به‌عنوان نقطهٔ شروع متن خودش می‌گیرد و ویرایش/انتشار می‌کند — یعنی AI/موتور جایگزین متخصص نیست، پیش‌نویس‌ساز است.

---

<a name="7"></a>
## ۷) گردش مالی: فیش ← تأیید ← تفسیر ← انتشار

### ۷.۱ دو محصول
| محصول | قیمت (تومان) | چه می‌گیرد |
|---|---|---|
| تحلیل پیشرفته (`advanced_analysis`) | ۳۶۹٬۰۰۰ | تفسیر کامل متخصص (وب + PDF پیشرفته) |
| پکیج طلایی (`golden_bundle`) | ۷۹۹٬۰۰۰ | تحلیل پیشرفته + دسترسی پلنر جوما + بستهٔ تمرینی اختصاصی (تخفیف >۳۰٪، پیشنهاد ۷۲ساعته) |

قیمت‌ها ثابت‌های کانفیگ‌اند (`PRICE_*`) و یک نسخهٔ دیگر در `includes/offer.php` به‌عنوان «نقطهٔ یکتا» برای نمایش. کارت: `PAYMENT_CARD_NUMBER`، پشتیبانی: `09967979471`، کانال بله: `ble.ir/mirbolouki`.

### ۷.۲ گام‌های مدیر روی هر درخواست (`admin/request-detail.php`)
1. **`verify_payment`** — تأیید فیش: `payment_status=payment_confirmed` + `payment_verified_at` + پیامک به کاربر. اگر product_type=golden_bundle باشد، هم‌زمان `joma_activate_subscription` صدا زده می‌شود (دسترسی جوما ۱ماهه) و بستهٔ تمرینی به‌صورت پیش‌نویس در ورک‌بنچ ساخته می‌شود. خطا هم مدیریت‌شده است («پرداخت تأیید شد اما جوما فعال نشد؛ دستی فعال کنید»).
2. **`regenerate_engine`** — تولید/بازتولید پیش‌نویس تفسیر توسط موتور (بخش ۶.۵).
3. **`save_interpretation`** — ذخیرهٔ سه‌فیلد تفسیر (علمی / یادداشت مدیر / توصیه‌های بالینی) با وضعیت draft یا published؛ در انتشار: `published_at` ثبت و کاربر پیامک می‌گیرد.
4. درخواستِ رد‌شده: `rejected` — کاربر در result.php بج رد می‌بیند و می‌تواند فیش جدید آپلود کند.

### ۷.۳ دو صفحهٔ متفاوت برای دو چشم
- `admin/result-detail.php`: نمای فنی کارنامه (نمرات خام، JSON، audit، گیت user_visible، باکس تحلیل فردی).
- `admin/request-detail.php`: نمای مالی/تفسیری (فیش، تأیید، ویرایشگر تفسیر، باکس تحلیل فردی).
هر دو دکمهٔ PDF و پیوند به تحلیل فردی دارند.

---

<a name="8"></a>
## ۸) قیف فروش: پکیج طلایی، جوما، بستهٔ تمرینی

### ۸.۱ قیف (`includes/offer.php` + باکس در `user/result.php`)
```
رایگان: آزمون + کارنامهٔ اولیهٔ وب (پس از انتشار)
   ↓ (باکس پیشنهاد ویژه با شمارش معکوس ۷۲ ساعته از created_at نتیجه)
تحلیل پیشرفته ۳۶۹,۰۰۰
   ↓
پکیج طلایی ۷۹۹,۰۰۰ (تحلیل + جوما ۵۰۰,۰۰۰ + بستهٔ تمرینی ۲۹۰,۰۰۰ — بسته)
   ↳ cron-reminders.php: هر ۱۰ دقیقه چک می‌کند؛ اگر تا سررسید خرید نکرده باشد
     یادآور پیامکی زمان‌بندی‌شده (sms_outbox.send_datetime) می‌فرستد؛
     اگر خرید کرده باشد یادآور خودکار لغو می‌شود.
```
`offer_is_active(created_at)` / `offer_deadline_ts` پنجرهٔ ۷۲ ساعت را مدیریت می‌کنند؛ `offer_get_orders` تاریخچهٔ سفارش‌های آن نتیجه.

### ۸.۲ اشتراک جوما (`joma_subscriptions` + `joma_library.php`)
- مرز مسئولیت صریح: **پلنر جوما سرویس جداگانه روی ساب‌دامین خودش است**؛ این سامانه فقط «دسترسی» را نگه می‌دارد: `joma_has_active_subscription(user_id)` (چک expires_at+status)، `joma_activate_subscription` (۱ماهه)، `joma_revoke_subscription`.
- `joma_library.php` کتابخانهٔ رسمی ۱۰۷ فعالیت جوما را از JSON (`database/joma/library_official_full.json`) می‌خواند — منبع حقیقت برای هم‌راستایی تمرین‌های بسته با واحد/فرکانس/برچسب جوما.

### ۸.۳ موتور بستهٔ تمرینی (`package_engine.php`)
گردش: **نتیجهٔ آزمون (عملاً YSQ) → پیش‌نویس خودکار → بازبینی مدیر در ورک‌بنچ → انتشار → استفادهٔ کاربر**

```
package_engine_prioritize_dimensions: مرتب‌سازی ابعاد بر اساس شدت + گزینش حداکثر
   PKG_TOP_SCHEMAS=4 طرحوارهٔ هدف
package_engine_select_exercises: به‌ازای هر طرحواره تا PKG_EXERCISES_PER_SCHEMA=2 تمرین
   از exercise_bank با تطبیق schema_codes و target_severity (سطح شدت تمرین باید
   با شدت فعلی کاربر fit باشد — نه ساده‌تر/سخت‌تر از حد)
package_engine_joma_snapshot: snapshot جوما هر تمرین در package_items.joma_snapshot_json
   (تا اگر کتابخانه تغییر کرد، بستهٔ منتشرشدهٔ کاربر دست‌نخورده بماند)
package_engine_generate_draft(result_id, admin_id) → exercise_packages(status=draft)
admin/package-workbench.php: regenerate / update / publish / unpublish
package_engine_publish → status=published (کاربر از user/packages.php می‌بیند)
```

### ۸.۴ تجربهٔ کاربر از بسته
- `user/packages.php`: فهرست بسته‌های منتشرشدهٔ خودش — **گیت اشتراک جوما**: بدون اشتراک فعال، ورود مسدود.
- `user/package.php`: برنامهٔ روزشده (day_number)، تیک زدن انجام تمرین (`package_engine_toggle_progress` → package_progress)، نوار پیشرفت (done/total/percentage).
- `user/package-pdf.php` + `includes/package_pdf.php`: نسخهٔ چاپی برنامه (کش در uploads/reports، باطل‌سازی کش با هر publish/unpublish/toggle: `pdf_invalidate_package_cache`).

---

<a name="9"></a>
## ۹) سامانهٔ پیامک (`includes/sms.php` + `admin/sms.php`)

**پروایدر:** SMS.ir — دو API:
- `sms_send_verify_template` → POST `/v1/send/verify` با `{mobile, templateId, parameters}` (OTP با قالب تأییدشده، پارامتر CODE)
- ارسال متنی خطی برای نوتیف‌ها (bulk/line با `SMSIR_LINE`)
- `sms_http_post_json`: هندلر cURL با timeout و لاگ خطا

**صف ارسال (outbox pattern):**
```
sms_enqueue(recipient, message, related_type, related_id)  → sms_outbox(status=queued)
sms_dispatch_outbox_item(outbox_id)                         → ارسال + status/error_msg/attempts
sms_send_text()                                             → enqueue + dispatch فوری
scheduled: ردیف‌های با send_datetime آینده (یادآور ۲۴ساعتهٔ قیف) در cron dispatch می‌شوند
```

**نوتیف‌های خودکار:**

| رویداد | گیرنده | تابع |
|---|---|---|
| پایان آزمون + آپلود فیش (از v1.3.5 فقط بعد از فیش) | مدیر | `sms_notify_admin_new_result` / `sms_notify_admin_receipt` |
| آپلود فیش | کاربر | `sms_notify_user_result_ready(after_receipt=true)` |
| انتشار تفسیر | کاربر | `sms_notify_user_result_ready` |
| فعال‌شدن پکیج طلایی | کاربر | `sms_notify_user_golden_activated` |
| یادآور پیشنهاد ویژه | کاربر | `sms_dispatch_due_reminders` (cron) |

**حالت توسعه:** `SMS_PROVIDER='dev'` → ارسال واقعی نمی‌شود، کد OTP روی صفحهٔ ورود نمایش داده می‌شود و ردیف outbox با `dev_sent` ثبت می‌شود — برای تست بدون هزینه.

`admin/sms.php`: صندوق خروجی برای مدیر (فیلتر وضعیت، مشاهدهٔ خطاها).

---

<a name="10"></a>
## ۱۰) گزارش‌های PDF (`includes/pdf_engine.php`)

| گزارش | تابع | محتوا | چه کسی/کِی |
|---|---|---|---|
| کارنامهٔ اولیه | `pdf_generate_initial_report` | مشخصات مراجع، جدول/نوار ابعاد (`pdf_render_dimension_bars_html`)، برچسب شدت، تفسیر کوتاه | کاربر پس از user_visible=1 |
| گزارش پیشرفته | `pdf_generate_advanced_report(allow_draft)` | تفسیر کامل متخصص + توصیه‌ها + نمودارها | کاربر پس از انتشار تفسیر؛ مدیر می‌تواند پیش‌نویس را زنده ببیند |
| گزارش زوجین | `admin/couples/report-pdf.php` | گزارش ۱۳بخشی پروندهٔ زوج | فقط مدیر |
| گزارش تحلیل فردی | `admin/individual-pdf.php` | گزارش ۸بخشی فرد | فقط مدیر |
| برنامهٔ بستهٔ تمرینی | `includes/package_pdf.php` | برنامهٔ روزشدهٔ تمرین‌ها | کاربر دارای بستهٔ منتشرشده |

- **کش:** `pdf_get_cached_or_generate` — PDF ساخته‌شده در `uploads/reports` نگه داشته می‌شود؛ `pdf_invalidate_cache(result_id)` پس از هر تغییر تفسیر/انتشار. پیش‌نویس مدیریتی هر بار زنده تولید می‌شود (بدون کش) — v1.3.5b.
- نام فایل‌ها گویا و نسخه‌دار است (مثلاً individual-analysis-<uid>-v<ver>.pdf).
- خروجی‌ها: `'I'` inline، `'D'` download، `'F'` file (کش).

---

<a name="11"></a>
## ۱۱) ماژول تحلیل زوجین (v1.3.0+) — ابزار درمانگر

### ۱۱.۱ مفهوم
مدیر (درمانگر) برای هر زوج یک **پرونده** می‌سازد؛ هر فرد «سمت A یا B» است؛ برای هر سمت «سنجش» اضافه می‌شود — یا از آزمون‌های خود سامانه (نتیجهٔ واقعی کاربر) یا **ورود دستی نمرات آزمون خارجی** (با رجیستری `couple_assessments`). موتور دو نیم‌رخ را با **پایگاه‌دانش (KB)** مقایسه و گزارش ۱۳بخشی فارسی می‌سازد.

### ۱۱.۲ صفحات (`admin/couples/`)
| صفحه | نقش |
|---|---|
| `index.php` | فهرست پرونده‌ها + ساخت/بایگانی |
| `new.php` | ویزارد ساخت پرونده (انتخاب دو مراجع از کاربران دارای کارنامه + تیک نوبت‌ها؛ تیک آگاهانهٔ مدیر = تأیید داده و نمره) |
| `case-view.php` | قلب ماژول: اعضا، تأیید/رد سنجش‌ها (`confirm_member`/`unconfirm_member`/`remove_member`)، اجرای تحلیل (`run_analysis`)، تاریخچهٔ نسخه‌های گزارش |
| `member-pick.php` | افزودن سنجش از نتایج سامانه (جستجوی کاربر → انتخاب نتیجه) |
| `member-manual.php` | ورود دستی نمرات آزمون خارجی (manual_scores_json) |
| `assessments.php` | رجیستری آزمون‌ها: کد، نسخه، bands، comparison_mode، فعال/غیرفعال |
| `kb.php` | مدیریت پایگاه‌دانش: seed (couple_kb_fa)، toggle، ویرایش، حذف |
| `report-view.php` | نمایش گزارش + ویرایش Clinician + **گیت تأیید نهایی (is_final)** |
| `report-pdf.php` | PDF گزارش |

### ۱۱.۳ موتور (`couple_run_analysis`) — قواعد قطعی
```
ورودی: فقط سنجش‌های data_confirmed=1 && scores_confirmed=1 (تأیید دومرحله‌ای)
قاعده: هر (سمت، کد آزمون) حداکثر یک سنجش — ادغام خودکار ممنوع
گیت اجرا: حداقل یک سنجش تأییدشده برای هر دو سمت (وگرنه خطا، نه گزارش نصفه)
مقایسهٔ عددی فقط هم‌آزمون + هم‌نسخه (assessment_code + assessment_version)
دادهٔ غایب حدس زده نمی‌شود → بخش «اطلاعاتی که هنوز کم است»
```

**سنجش** (`couple_build_measurement`): از نتیجهٔ سامانه یا نمرات دستی؛ نرمال‌سازی به percentage بر اساس بازهٔ هر بُعد + نگاشت به bands رجیستری → سطح شدت (normal/low/moderate/high/severe).

**پایگاه‌دانش** (`couple_kb_entries`, seed: `couple_kb_fa.php`):
- `entry_type=interpretation` (۶۱ مدخل seed): برای هر (آزمون، بُعد): need / sensitivity / partner_sees / resource / question
- `pattern` (۹): الگوهای ترکیبی (مثلاً دلواپس–دلواپس، اجتنابی–اجتنابی، امن، سطوح CSI)
- `cycle` (۶): چرخه‌های تعاملی (شرط فعال‌شدن: `couple_condition_match` روی سطوح دو طرف)
- valence (۴۰): بار مثبت/منفی هر مدخل — مبنای تفکیک «حوزهٔ آسیب‌پذیر» از «نقطهٔ قوت»
- `generic_question`: سوالات عمومی جلسه
- هلپرها: `couple_kb_load/kb_find`, `couple_band_level`, `couple_severity_rank`, `couple_confidence_label_fa` (اظهار اطمینان در گزارش)، `couple_system_assessment_meta` (متادیتای ۱۱ آزمون فعال)

**خروجی — ۱۳ بخش:**
۱. روایت رابطه و جمع‌بندی (story) ۲. تصویر روان‌شناختی طرفین (portrait) ۳. نقاط همپوشان و نیازهای مشترک (shared) ۴. حوزه‌های آسیب‌پذیر فعال (vulnerable) ۵. نقاط قوت و منابع (strengths) ۶. نقاط احتمال سوءبرداشت/فعال‌شدن طرحواره (misread) ۷. چرخه‌های تعاملی احتمالی (cycles) ۸. تفاوت‌های مهم دو نفر (diff) ۹. اطلاعات هنوز ناکافی (missing) ۱۰. سناریوهای محتمل تعارض (scenarios) ۱۱. محورهای پیشنهادی جلسهٔ زوج‌درمانی (session) ۱۲. فرضیه‌های قابل بررسی توسط درمانگر (hypotheses) ۱۳. محدودیت تحلیل و هشدار قطعی‌نبودن (limits) + **پیوست کمّی** (appendix: اعداد خام برای حسابرسی، جدا از نثر)

نثر با قالب‌های `couple_narrative_fa.php` (۲۴ تابع couple_nt_*) تولید می‌شود؛ **نثر اصلی بدون درصد** — اعداد فقط در پیوست (اصل بالینی: گزارش برای انسان، پیوست برای ردگیری).

**نسخه‌بندی و گیت:**
- هر `run_analysis` یک ردیف **جدید** در `couple_reports` با `version_no = MAX+1` می‌سازد (تاریخچه هرگز overwrite نمی‌شود).
- `report-view.php`: پیش‌نویس → ویرایش `clinician_edits_json` → **تأیید نهایی** (`is_final=1`, `finalized_at`) — بعد از آن قفل و فقط نمایش. لحن گزارش: همیشه «فرضیه برای بررسی درمانگر»، نه تشخیص.

---

<a name="12"></a>
## ۱۲) ماژول تحلیل فردی (v1.3.6 / v1.3.6a) — همان موتور، برای تک‌مراجع

**ایده:** موتورِ مقایسه‌ای زوجین، برای فردی که فقط خودش آزمون داده — برای اینکه درمانگر پیش از ساخت پروندهٔ زوج، نیم‌رخ فرد را تحلیل‌شده داشته باشد.

### ۱۲.۱ منطق فنی
```
admin/individual-analysis.php
  گام ۰ (v1.3.6a خودآزمایی): SHOW COLUMNS couple_reports LIKE 'subject_user_id'
     → اگر نبود: بنر قرمز «مرحلهٔ نصب SQL انجام نشده» + هنگام کلیک پیام صریح راهنمای Import
  لیست مراجع: users JOIN test_results GROUP BY user ORDER BY آخرین آزمون (LIMIT ۲۰۰)
  کلیک «اجرای تحلیل» (?user_id=N&run=1):
     ① گارد SQL  ② couple_run_individual_analysis(user_id)
     ③ version_no = MAX(version_no)+1 WHERE subject_user_id=user_id
     ④ db_insert(couple_reports: case_id=0, subject_user_id=N, analysis_json, ai_meta_json='{}')
        ← مالکیت همان جدول؛ تاریخچهٔ نسخه‌دار مثل زوجین
     ⑤ flash موفقیت + انتقال به ?rid=… با اسکریپت location.replace
        (چون header.php خروجی را آغاز کرده، هدر HTTP ممکن نیست — v1.3.6a:
         <script>window.location.replace + meta refresh + لینک fallback + exit)
  گارد ذخیره: rid=0 → پیام صریح «ذخیره ناموفق؛ SQL را ایمپورت کنید» (هیچ سکوتی نیست)
```

- `couple_run_individual_analysis`: همهٔ نتایج تکمیل‌شدهٔ کاربر را به measurement تبدیل می‌کند (assessment-agnostic)؛ الگوهای KB که فقط برای دو نفر معنا دارند به‌عنوان «فرضیهٔ فردی» برچسب می‌خورند.
- **`couple_individual_wash()`**: پاک‌سازی واژگان دونفرهٔ KB در مسیر فردی — «شریک» → «طرف مقابل»، «این زوج» → «این فرد در روابط نزدیک»، «طرفین» → «فرد و اطرافیان نزدیک» و… روی فیلدهای need/sens/partner_sees/resource/question. (تضمین: گزارش فرد هرگز از «شریک/زوج» صحبت نکند.)
- **گزارش ۸بخشی:** خلاصهٔ اجرایی، تصویر روان‌شبختی، آسیب‌پذیرهای فعال، نقاط قوت (مستند-خودگزارشی)، سوءبرداشت‌های محتمل، فرضیه‌های فردی، محورهای جلسهٔ فردی، محدودیت‌ها + پیوست کمّی. اعداد/درصدها فقط در پیوست.
- **نمایش در سه‌جا:** خود صفحهٔ individual-analysis (`?rid=`)، و دو باکس «🧠 تحلیل فردی» در `admin/result-detail.php` و `admin/request-detail.php` (آخرین نسخه + دکمهٔ مشاهده/اجرا).
- **PDF:** `admin/individual-pdf.php` — همان تم report، بدون پیوست در بدنه، پیوست در صفحهٔ جدا؛ نام فایل نسخه‌دار.
- **SQL نصب (additive):** `ALTER TABLE couple_reports ADD COLUMN subject_user_id INT NOT NULL DEFAULT 0 AFTER case_id` — خطای Duplicate در ایمپورت دوباره = بی‌ضرر (قبلاً اجرا شده).

---

<a name="13"></a>
## ۱۳) نقشهٔ کامل پنل مدیریت

منو (`admin/header.php`): داشبورد | درخواست‌های تفسیر | نتایج آزمون | تحلیل زوجین | 🧠 تحلیل فردی | آزمون‌ها | بسته‌های تمرینی | اشتراک‌های جوما | پیامک | خروج

| صفحه | کارکردها (اکشن‌های واقعی کد) |
|---|---|
| `index.php` | KPI: کاربران/آزمون‌ها/نتایج/درخواست‌ها/فیش‌های در انتظار + ۵ درخواست اخیر + ۵ آزمون اخیر |
| `requests.php` | فهرست درخواست‌ها با فیلتر payment_status (فیش ارسال‌شده، تأییدشده، منتشرشده…) |
| `request-detail.php` | verify_payment / regenerate_engine / save_interpretation(draft-published) + باکس تحلیل فردی |
| `results.php` / `result-detail.php` | فهرست/جزئیات نتیجه + گیت publish (user_visible) + create_direct_interpretation (ساخت مستقیم درخواست بدون پرداخت) + باکس تحلیل فردی |
| `users.php` | فهرست/جستجوی مراجع |
| `tests.php` / `test-edit.php` | فهرست آزمون‌ها، toggle فعال/غیرفعال، ویرایش مشخصات |
| `questions.php` | CRUD سوال و گزینه (ویرایش متن، ترتیب، معکوس، نمرهٔ گزینه) |
| `scoring-rules.php` | CRUD ابعاد + قواعد بازه‌ای + منابع (`test_sources` هم همین‌جاست) |
| `sources.php` | مدیریت منابع علمی/هنجارها |
| `couples/*` | بخش ۱۱ |
| `individual-analysis.php` + `individual-pdf.php` | بخش ۱۲ |
| `packages.php` / `package-workbench.php` | فهرست بسته‌ها؛ ورک‌بنچ: regenerate/update/publish/unpublish |
| `subscriptions.php` | جوما: grant دستی / revoke |
| `sms.php` | صندوق خروجی پیامک + وضعیت‌ها/خطاها |
| `export.php` | خروجی CSV همهٔ نتایج (mirbolouki_results_YYYYmmdd_HHii.csv) |
| `login.php`/`logout.php` | ورود/خروج مدیر |

**ابزارهای ریشه (برای عملیات، نه کاربر نهایی):** `install.php` (نصب تحت‌وب)، `reset_admin.php` (ریست رمز admin به مقدار پیش‌فرض) — این دو باید بعد از استفاده از دسترس خارج شوند (حذف/محافظت؛ ریسک امنیتی مستقیم).

---

<a name="14"></a>
## ۱۴) امنیت لایه‌به‌لایه

| لایه | پیاده‌سازی |
|---|---|
| SQL Injection | ۱۰۰٪ Prepared Statement در `db_query` با types؛ هیچ الحاق رشتهٔ خام برای ورودی کاربر |
| XSS | `escape_html()/e()` روی همهٔ خروجی‌های داینامیک؛ charset صریح UTF-8 |
| CSRF | توکن سشن‌محور در همهٔ POSTها + رد با پیام فارسی؛ اکشن‌های GET تغییردهندهٔ داده ندارند (استثنا: اجرای تحلیل فردی/زوجین که read-only+INSERT گزارش است و فقط از پنل ادمینِ لاگین‌شده) |
| نشست | `session.cookie_httponly=1`، `use_only_cookies=1`؛ جداسازی کامل سشن کاربر/مدیر؛ `session_regenerate_id` در logout‌ها |
| OTP | هش کد (نه متن روشن)، TTL ۱۲۰s، سقف ۵ تلاش، cooldown ۹۰s، ریت‌لیمیت شماره+IP، یک‌بارمصرف |
| آپلود | whitelist پسوند + بررسی MIME واقعی با finfo + سقف ۵MB + نام‌گذاری تصادفی سمت سرور |
| IDOR | همهٔ صفحات کاربر: `require_user_login` + چک `result.user_id == session.user_id`؛ PDF هم مالکیت چک می‌کند |
| گیت محتوا | `user_visible` + وضعیت published تفسیر — خروجی حساس تا تأیید صریح مدیر بسته است |
| رمز مدیر | password_hash/verify (bcrypt) |
| Audit | `log_event(level, action, msg)` → `system_logs` (لاگین‌ها، تغییر گیت، تأیید گزارش زوج، …) |
| خطا | `display_errors=0` در کانفیگ؛ خطاها لاگ می‌شوند نه نمایش |

---

<a name="15"></a>
## ۱۵) قواعد و محدودیت‌های توسعه (قراردادهای این کدبیس)

۱. **PHP 7 سازگار** — از فیچرهای PHP8-only استفاده نکنید؛ `array()` نه `[]`، توابع رویه‌ای نه کلاس.
۲. **MySQLi فقط** — PDO ممنوع؛ هر کوئری با `db_*` و types.
۳. **SQL همیشه فقط-افزودنی (ADD-ONLY):** migrationها فقط CREATE TABLE IF NOT EXISTS / ADD COLUMN؛ هیچ DROP/ALTER تخریبی؛ «Duplicate column» در ایمپورت مجدد باید بی‌ضرر باشد. فایل‌ها در `database/update_v*_*.sql`.
۴. **هیچ Composer/CLI/سرویس سیستمی** — هاست اشتراکی cPanel؛ فقط cron از نوع `php file.php`.
۵. **تحلیل قاعده‌محور است؛ دادهٔ غایب حدس زده نمی‌شود** — موتور زوجین/فردی بدون دادهٔ کافی، بخش «کم است» می‌سازد نه حدس. AI فقط آداپتور اختیاری خاموش.
۶. **مقایسهٔ عددی فقط هم‌آزمون+هم‌نسخه**؛ `test_version` در نتایج و سشن‌ها ثبت می‌شود.
۷. **تاریخچه گزارش‌ها immutabl است** — اصلاح = نسخهٔ جدید؛ تأیید نهایی = قفل.
۸. **نثر گزارش بدون درصد** — اعداد فقط در پیوست کمّی.
۹. **جریان انتشار دو مرحله‌ای:** تفسیر published ≠ کاربر می‌بیند؛ کاربر با `user_visible=1` می‌بیند.
۱۰. هر تغییر نسخه: changelog (`CHANGELOG_FA.md`) + راهنمای نصب فارسی جداگانه + گزارش تست.

---

<a name="16"></a>
## ۱۶) واژه‌نامهٔ سریع

| اصطلاح | یعنی |
|---|---|
| **کارنامهٔ اولیه** | گزارش رایگان وب/PDF نمرات ابعاد (بعد از user_visible) |
| **تفسیر کامل / تحلیل پیشرفته** | محصول ۳۶۹هزار تومانی: تفسیر متخصص + PDF پیشرفته |
| **پکیج طلایی** | ۷۹۹هزار: تحلیل + جوما + بستهٔ تمرینی |
| **جوما** | پلنر تمرین روزانهٔ جدا (subdomain)؛ اینجا فقط اشتراک/دسترسی |
| **بستهٔ تمرینی** | برنامهٔ ۳۰روزهٔ تمرین طرحواره‌محور از exercise_bank (۴ طرحواره × ۲ تمرین) |
| **پیوست کمّی** | بخش پایانی گزارش‌ها با اعداد خام (جدا از نثر) |
| **تأیید دومرحله‌ای سنجش** | در زوجین: افزودن سنجش ≠ تأیید؛ confirm جدا لازم دارد |
| **گیت پرداخت (v1.3.5)** | قفل کارنامه تا انتشار صریح مدیر |
| **wash (تحلیل فردی)** | پاک‌سازی واژگان دونفرهٔ متن‌های KB در گزارش تک‌نفره |
| **audit trail** | scientific_audit_trail_json: ردّ هر نمره به گویه |

### مسیرهای کلیدی برای شروع دیباگ
- پاسخ‌ها ذخیره نمی‌شود؟ → `ajax/submit-test.php` → `engine_validate_answers`
- نمره عجیب؟ → `test_results.scientific_audit_trail_json` + `test_scoring_rules`
- کاربر کارنامه نمی‌بیند؟ → `test_results.user_visible` + تفسیر published؟
- پیامک نمی‌رود؟ → `sms_outbox.status/error_msg` + `SMS_PROVIDER`
- گزارش زوج خالی/ناقص؟ → `data_confirmed`/`scores_confirmed` اعضا + kb seed شده؟ + هم‌نسخه‌بودن
- تحلیل فردی «هیچ»؟ → ستون `subject_user_id` (بنر قرمز صفحه باید دیده شود — v1.3.6a)

---

<a name="17"></a>
## ۱۷) خط زمانی نسخه‌ها (از CHANGELOG)

| نسخه | تاریخ | محور |
|---|---|---|
| 1.0.0 | ۱۴۰۵/۰۶/۰۸ | نصب پایه: موتور آزمون، کارنامه، درخواست تفسیر |
| 1.0.3–1.0.4 | ۰۶ | تفسیر کامل + YSQ فرم ۲۳۲ |
| 1.1.0 | ۰۶/۳۰ | پیامک SMS.ir + OTP + بانک تمرین + بستهٔ تمرینی |
| 1.2.0–1.2.3 | ۰۶/۳۰–۰۷ | پکیج طلایی + اشتراک جوما + هم‌راستایی کتابخانهٔ جوما (۱۰۷ فعالیت) |
| 1.2.4–1.2.10 | ۰۷ | اصلاحات پیامک/موبایل/یادآور زمان‌بندی |
| 1.3.0–1.3.1 | ۰۹ | موتور تحلیل زوجین + KB + ویزارد |
| 1.3.2–1.3.4 | ۰۹–۱۰/۱ | روایت‌های فارسی KB + valence + بازطراحی ۱۳بخشی |
| 1.3.5–1.3.5c | ۱۰/۱–۲ | **گیت پرداخت** (user_visible) + نمایش مدیریتی تفسیر + باکس‌ها |
| 1.3.6–1.3.6a | ۱۰/۲ | **تحلیل فردی** (subject_user_id) + هات‌فیکس ریدایرکت/گاردهای SQL |

---

*پایان سند — تولیدشده از تحلیل خط‌به‌خط کدبیس در `/home/user/work/test3/test` (نسخهٔ 1.3.6a).*
