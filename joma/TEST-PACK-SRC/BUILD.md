# ساخت «بستهٔ تست یک‌جا» (JOMA-TEST-PACK)

این پوشه فقط برای **بازتولید** بستهٔ تست است. خود بستهٔ تحویلی اینجاست:
`joma/JOMA-TEST-PACK-v1.zip` (یک پوشهٔ `test/` در ریشه + همهٔ فایل‌های اپ).

## بستهٔ تست چیست

یک کپی **کامل و مستقل** از جوما که در زیرپوشهٔ `test` سایت ساب‌دامین
(`joma.mirbolouki.com/test`) می‌نشیند، با فایل کار می‌کند (بدون MySQL) و کاری با
سایت اصلی ندارد. شامل:

| بخش | منبع |
|---|---|
| هستهٔ اپ + آموزش + هم‌مسیر + موتور موفقیت + تحلیل | `hammasir-demo-standalone.zip` (شاخهٔ `arena/01a08b36-…`) |
| B1/B2/B3 | `UPLOAD-B1B2B3-GATES/` |
| B4 | `UPLOAD-B4-NOTE-PRIVACY/` |
| B5 (جوجهٔ من) | `UPLOAD-B5-JOOJE/` |
| B1 مرحلهٔ ۲ (کد یک‌بارمصرف) | `UPLOAD-B1-STEP2-RECOVERY/` |
| گزارش تحلیلی R1/R2 | `TEST-PACK-SRC/r2/` |
| کتابخانهٔ ۱۰۷ فعالیت (۴۵ + زوج ۴۶–۸۰ + طرحواره ۸۱–۱۰۷) | `UPLOAD-TARHWARE/3-library_official.json` |
| صفحهٔ شروع، راهنما، اسکریپت دادهٔ نمونه، کانفیگ تست | `TEST-PACK-SRC/extras/` |

## روش بازتولید (خط فرمان)

```bash
W=$(mktemp -d)
BASE=https://github.com/mahrokhchenaghchi/mirbluki-farsi-web-96/raw/arena/01a08b36-mirbluki-farsi-web-96/hammasir-demo-standalone.zip
curl -sL "$BASE" -o "$W/base.zip"
unzip -q "$W/base.zip" -d "$W/union"

P=/path/to/repo/joma
cp $P/UPLOAD-B1B2B3-GATES/includes/helpers.php           "$W/union/includes/"
cp $P/UPLOAD-B1B2B3-GATES/functions/joma.php             "$W/union/functions/"
cp $P/UPLOAD-B1B2B3-GATES/pages/forgot.php               "$W/union/pages/"
cp $P/UPLOAD-B1B2B3-GATES/pages/today.php                "$W/union/pages/"
cp $P/UPLOAD-B4-NOTE-PRIVACY/functions/hammasir.php      "$W/union/functions/"
cp $P/UPLOAD-B4-NOTE-PRIVACY/pages/hammasir_provider.php "$W/union/pages/"
cp $P/UPLOAD-B5-JOOJE/config/jooje_config.php            "$W/union/config/"
cp $P/UPLOAD-B5-JOOJE/functions/jooje.php                "$W/union/functions/"
cp $P/UPLOAD-B5-JOOJE/pages/jooje.php                    "$W/union/pages/"
cp $P/UPLOAD-B5-JOOJE/api/jooje.php                      "$W/union/api/"
cp $P/UPLOAD-B5-JOOJE/pages/dashboard.php                "$W/union/pages/"
cp $P/UPLOAD-B5-JOOJE/includes/helpers.php               "$W/union/includes/"
cp $P/UPLOAD-B5-JOOJE/index.php                          "$W/union/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/functions/recovery.php    "$W/union/functions/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/functions/joma.php        "$W/union/functions/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/pages/forgot.php          "$W/union/pages/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/pages/admin_recovery.php  "$W/union/pages/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/pages/login.php           "$W/union/pages/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/includes/bootstrap.php    "$W/union/includes/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/includes/helpers.php      "$W/union/includes/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/includes/layout.php       "$W/union/includes/"
cp $P/UPLOAD-B1-STEP2-RECOVERY/index.php                 "$W/union/"
cp $P/TEST-PACK-SRC/r2/functions/analytics_reports.php   "$W/union/functions/"
cp $P/TEST-PACK-SRC/r2/pages/reports.php                 "$W/union/pages/"
mkdir -p "$W/union/docs" && cp $P/TEST-PACK-SRC/r2/docs/HAMMASIR-REPORTS.md "$W/union/docs/"
cp $P/UPLOAD-TARHWARE/3-library_official.json            "$W/union/database/library_official.json"

# بستهٔ تست
mkdir -p "$W/site/test" && cp -a "$W/union/." "$W/site/test/"
cp $P/TEST-PACK-SRC/extras/config.php   "$W/site/test/config/config.php"
cp $P/TEST-PACK-SRC/extras/htaccess     "$W/site/test/.htaccess"
cp $P/TEST-PACK-SRC/extras/_SEED-DEMO.php "$W/site/test/"
cp $P/TEST-PACK-SRC/extras/START-HERE.html "$W/site/test/"
cp $P/TEST-PACK-SRC/extras/_RAHNAMA-CPANEL.txt "$W/site/test/"

# پیش‌نمایش فرانت نسخهٔ ۲۰ (فقط پوشهٔ FRONTEND از joma-frontend-v20.zip)
unzip -q $P/../joma-frontend-v20.zip -d "$W/fe"
mkdir -p "$W/site/test/FRONTEND-PREVIEW"
cp -a "$W/fe/joma-frontend-v20/FRONTEND/." "$W/site/test/FRONTEND-PREVIEW/"

# ⚠️ هیچ داده‌ای داخل بسته نمی‌رود (نه store.json، نه سشن)
rm -f "$W/site/test/data/store.json" "$W/site/test/data/hammasir/"* "$W/site/test/data/sessions/"*

(cd "$W/site" && zip -qr JOMA-TEST-PACK-v1.zip test)
```

## قواعد ثابت این بسته (چیزی که نباید عوض شود)

1. **هیچ داده‌ای داخل ZIP نرود** — نه `data/store.json`، نه `data/hammasir/*`، نه سشن‌ها.
   دلیل: اگر اشتباهی جای دیگر اکسترکت شود، هیچ دادهٔ زنده‌ای بازنویسی نشود.
2. **هیچ `install.php`** در بسته نباشد.
3. کانفیگ بسته فقط حالت فایلی است (`storage = file`) و `db_*` خالی — به دیتابیس دست نمی‌زند.
4. ZIP فقط یک پوشهٔ `test/` در ریشه داشته باشد (اکسترکت در ریشهٔ ساب‌دامین → `/test`).
5. `data/.htaccess` و `config/.htaccess` و `content/.htaccess` (منع دسترسی مستقیم) بمانند.
6. قفل ثبت‌نام در بستهٔ تست روشن است: کد `J0m@14O5`.
7. فایل‌های `_SEED-DEMO.php` و راهنما و `START-HERE.html` با `_` یا نام بزرگ شروع می‌شوند تا
   با فایل‌های اپ قاطی نشوند؛ راهنما صریح می‌گوید `_SEED-DEMO.php` بعد از اجرا پاک شود.

## تفاوت‌های عمدی با بسته‌های آپلودی قبلی

- `includes/bootstrap.php` در بستهٔ تست یک `require_once` محافظت‌شده برای
  `functions/jooje.php` هم دارد (بقیهٔ کد بدون تغییر). این تغییر در بسته‌های B5/B1-2 نیست،
  چون آن‌ها برای «افزودن به سایت موجود» ساخته شده‌اند و صفحهٔ جوجه خودش فایل را لود می‌کند.
- `.htaccess` ریشه در بستهٔ تست `RewriteBase /test/` دارد (در بستهٔ آپلودی `/demo/` بود).

## تست پذیرش بسته (همان چیزی که قبل از تحویل اجرا شد)

1. اکسترکت در مسیر تازه → `test/` ساخته می‌شود.
2. `GET /test/`، `/test/START-HERE.html`، `/test/index.php?p=login` و
   `/test/FRONTEND-PREVIEW/index.html` → همه `200`.
3. `GET /test/_SEED-DEMO.php` → بدون هیچ Warning/Notice: ۳ حساب، برنامهٔ RUNNING،
   ۵۹ ثبت عملکرد، ۱۰ ثبت حال، هم‌مسیر ACTIVE + پیام نمونه، جوجه متولد.
4. ورود سه حساب → همهٔ صفحه‌ها `200`؛ کتابخانه ۱۰۷ فعالیت؛ `api/jooje.php` = `chick/59/happy`.
5. مدیر → `admin_recovery` = 200؛ کد ۶ رقمی صادر می‌شود و کد خام در `store.json` نیست.
6. مشاور → `hammasir` = 200 و متن یادداشت خصوصی در پاسخ نیست (۰ مورد).
7. B3 → ثبت برای تاریخ آینده: «ثبت عملکرد برای روزهای آینده ممکن نیست.» و تاریخ آینده در
   فهرست تاریخ‌ها نیست؛ تاریخ گذشتهٔ همان ماه ثبت می‌شود.
8. B2 → کاربر بدون حالِ امروز: «برنامه من» و «گزارش‌ها» باز، «امروز» به صفحهٔ حال می‌رود.

---

## نسخهٔ ۹ (این دور) — C1 · C2 · C3 · بندِ ۴

بستهٔ تحویلی: `joma/JOMA-TEST-PACK-v9.zip` (همان ساختار v8: یک پوشهٔ `test/`).
منابعِ این دور (فقط فایل‌های عوض‌شده/تازه): `TEST-PACK-SRC/v9/…`

| بخش | خروجی |
|---|---|
| C1 | تب‌های تناوب در `pages/today.php` |
| C2 | `functions/companion_roles.php` + `includes/v2_roles.php` + یک خط گارد در `index.php` + فیلتر ناوبری در `includes/v2_shell.php` |
| C3 | `functions/companion_provider.php` + بازسازی داشبورد در `pages/hammasir_provider.php` |
| بندِ ۴ | `functions/companion_invite.php` + کارت در `pages/dashboard.php` + چهار گام در `pages/hammasir_client.php` |
| ظاهر/رفتار | `assets/css/joma-companion.css` · `assets/js/joma-companion.js` (فایل‌های طرح و JS قبلی دست‌نخورده‌اند) |

قفل: `joma/LOCK-v8.sha256` (هش همهٔ فایل‌های v8) + `test/DIFF.txt` + `test/LOCK-LIST-v8.txt` (L01…L31).
قاعدهٔ این دور: فقط افزودن — هیچ بازنویسی/بهسازی/جابه‌جایی بیرون از بخش اعلام‌شده.

---

## نسخهٔ ۱۰ (این دور) — تجمعی از v8 + کارهای این دور

بستهٔ تحویلی: `joma/JOMA-TEST-PACK-v10.zip` (همان ساختار `test/`).
**تجمعی:** همهٔ کارهای v9 + کارهای این دور، در یک زیپ. نصب v9 لازم نیست.
منابع این دور: `TEST-PACK-SRC/v10/…` — شامل **۱۲ فایل عوض‌شده** و فایل‌های تازه:

| بخش | خروجی |
|---|---|
| C5 | `functions/companion_provider.php` (آستانه‌ها · رنگ‌ها · ترتیب · عدد روز · آخرین ثبت) + `pages/hammasir_provider.php` |
| کد دعوت | `functions/companion_invitecode.php` (تازه) + `اختیاری-2-hammasir_invite_codes.sql` (ADD-ONLY) + `pages/hammasir.php` · `pages/hammasir_client.php` · `pages/hammasir_provider.php` |
| F1 | `includes/v2_rings.php` (تازه) + `pages/dashboard.php` |
| F2 | `pages/register.php` · `pages/forgot.php` (فقط افزودن aria-label) |
| F3 | `pages/settings.php` (بلوک «نمایش») + `assets/js/joma-companion.js` |
| ظاهر | `assets/css/joma-companion.css` (بندهای ۶ · ۷ · ۸ · ۹) |
| آزمون‌ها | `tests/c5-glance-test.mjs` · `tests/v10-aria-density-test.mjs` · `tests/sweep-roles.mjs` · `tests/seed-v10.php` (تازه) + اصلاح دو مورد در `tests/c-round-dom-test.mjs` (رزولوشن مسیر ریدایرکت · متن حالت خاموش طبق C5) |

قفل: `joma/LOCK-v8.sha256` (بی‌تغییر) + `test/DIFF.txt` (با جملهٔ تجمعی) + `test/LOCK-LIST-v8.txt` (L01…L33).
بررسی قفل روی بستهٔ تحویلی: **۱۱۴ خط OK و ۱۲ خط FAILED** — دقیقاً همان ۱۲ فایل DIFF.
