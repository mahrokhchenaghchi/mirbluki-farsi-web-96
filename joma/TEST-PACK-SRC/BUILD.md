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
