<?php
/**
 * JOMA — بررسی سلامت نصب (Health check)
 * یک نگاه: آیا این کپی روی این هاست درست نصب شده است؟
 * هیچ چیزی را تغییر نمی‌دهد (فقط می‌خواند و می‌نویسد/پاک می‌کند یک فایل آزمون موقت).
 * دسترسی: بدون ورود هم باز است تا قبل از ساخت حساب هم بتوانی استفاده کنی.
 * پیشنهاد: بعد از بررسی، این فایل را نگه دار (بی‌خطر است) یا در صورت تمایل پاک کن.
 */
$root = dirname(__FILE__);
$rows = array();
// این صفحه به bootstrap وابسته نیست؛ اگر تابع اعداد فارسی نبود، خودمان می‌سازیم.
if (!function_exists('fa_num')) {
    function fa_num($v) {
        return str_replace(array('0','1','2','3','4','5','6','7','8','9'),
                           array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'), (string) $v);
    }
}
function hc_add(&$rows, $title, $ok, $detail = '', $fix = '') {
    $rows[] = array('title' => $title, 'ok' => $ok, 'detail' => $detail, 'fix' => $fix);
}
$isPreview = (getenv('JOMA_PREVIEW') === '1');

/* ---------- ۱) نسخهٔ PHP ---------- */
$phpOk = version_compare(PHP_VERSION, '7.4', '>=');
$phpNew = version_compare(PHP_VERSION, '8.4', '>=');
hc_add($rows, 'نسخهٔ PHP', $phpOk && !$phpNew, PHP_VERSION,
    $phpOk ? 'PHP 8.4+ تست نشده است؛ اگر روی ۸.۴ خطا دیدی، دامنه را روی 7.4 یا 8.0 تا 8.3 بگذار.'
           : 'از cPanel → MultiPHP Manager نسخهٔ دامنه را روی 7.4 یا 8.x بگذار.',
);

/* ---------- ۲) افزونه‌های لازم ---------- */
foreach (array(
    'mbstring' => array('افزونهٔ mbstring', 'برای ماژول هم‌مسیر لازم است (شمارش نویسه‌های فارسی).'),
    'json' => array('افزونهٔ json', 'برای ذخیره‌سازی و خروجی JSON لازم است.'),
    'session' => array('افزونهٔ session', 'برای ورود و نگه‌داشتن نشست لازم است.'),
) as $ext => $info) {
    hc_add($rows, $info[0], extension_loaded($ext), extension_loaded($ext) ? 'فعال' : 'غیرفعال',
        $ext === 'mbstring' ? 'اگر cPanel داری: PHP Selector / MultiPHP INI → mbstring را روشن کن.' : 'با هاستینگ تماس بگیر و همین افزونه را بخواه.');
}

/* ---------- ۳) نوشتنی بودن پوشه‌ها ---------- */
$dirs = array(
    'data' => 'داده‌های جومای خودت (حال، ثبت‌ها، تنظیمات)',
    'data/sessions' => 'نشست‌های ورود',
    'data/hammasir' => 'دادهٔ ماژول هم‌مسیر',
);
foreach ($dirs as $d => $desc) {
    $path = $root . '/' . $d;
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    $test = false;
    if ($writable) {
        $f = $path . '/.hc-test-' . uniqid();
        $test = (@file_put_contents($f, 'ok') !== false);
        if ($test) @unlink($f);
    }
    hc_add($rows, 'پوشهٔ ' . $d, $writable && $test, $exists ? ($writable ? 'نوشتنی' : 'فقط‌خواندنی') : 'وجود ندارد',
        'در File Manager روی پوشه راست‌کلیک → Permissions → 755 (و فایل‌ها 644).');
}

/* ---------- ۴) فایل‌های کلیدی ---------- */
$files = array(
    'index.php' => 'نقطهٔ ورود اپ',
    'includes/bootstrap.php' => 'راه‌اندازی',
    'assets/css/joma-v2.css' => 'طرح تازه (اگر نبود، صفحه بی‌رنگ می‌شود)',
    'assets/js/joma-v2.js' => 'تعامل‌ها (لیوان آب، تنفس، جوجه)',
    'assets/audio/br-in.mp3' => 'صدای راهنمای تنفس',
    'functions/joma.php' => 'هستهٔ برنامه',
    'functions/success.php' => 'موتور موفقیت',
    'functions/analytics_reports.php' => 'گزارش تحلیلی',
    'functions/water.php' => 'آب (پیش‌نویس/قطعی)',
    'functions/insight.php' => 'دفترچه و موتور بینش',
    'functions/plancopy.php' => 'کپی برنامهٔ ماه بعد',
    'includes/v2_chick.php' => 'تصویر جوجه',
    'database/library_official.json' => 'کتابخانهٔ فعالیت‌ها',
);
$missing = array();
foreach ($files as $f => $desc) {
    if (!is_file($root . '/' . $f)) $missing[] = $f;
}
hc_add($rows, 'فایل‌های اصلی بسته', count($missing) === 0,
    count($missing) ? ('کم است: ' . implode(' · ', $missing)) : 'همه سرجایشان‌اند',
    'فایل‌های عقب‌مانده را از بستهٔ ZIP دوباره اکسترکت کن.');

/* ---------- ۵) حفاظت پوشه‌ها (دسترسی مستقیم) ---------- */
foreach (array('data/.htaccess', 'config/.htaccess', 'content/.htaccess') as $ht) {
    hc_add($rows, 'حفاظت ' . dirname($ht) . '/', is_file($root . '/' . $ht),
        is_file($root . '/' . $ht) ? 'فایل .htaccess هست' : 'نیست',
        'فایل .htaccess این پوشه را از بسته برگردان (جلوی خواندن مستقیم داده‌ها را می‌گیرد).');
}

/* ---------- ۶) حالت ذخیره‌سازی ---------- */
$cfgFile = $root . '/config/config.php';
$cfgOk = is_file($cfgFile);
$storage = '?';
if ($cfgOk) {
    $JOMA_CONFIG = array();
    include $cfgFile;
    if (isset($JOMA_CONFIG['storage'])) $storage = $JOMA_CONFIG['storage'];
}
hc_add($rows, 'حالت ذخیره‌سازی', true, ($storage === 'mysql' ? 'MySQL (دیتابیس)' : 'فایل (بدون دیتابیس)'),
    'همین بسته به‌صورت پیش‌فرض با «فایل» کار می‌کند و به دیتابیس وصل نمی‌شود؛ اگر MySQL می‌خواهی، بعداً با هم انجامش می‌دهیم.');

/* ---------- ۷) اتصال دیتابیس (اگر MySQL باشد) ---------- */
if ($storage === 'mysql') {
    $c = isset($JOMA_CONFIG) ? $JOMA_CONFIG : array();
    $link = @mysqli_connect(isset($c['db_host']) ? $c['db_host'] : '', isset($c['db_user']) ? $c['db_user'] : '', isset($c['db_pass']) ? $c['db_pass'] : '', isset($c['db_name']) ? $c['db_name'] : '');
    if ($link) {
        $hasSettings = false;
        $r = @mysqli_query($link, "SHOW TABLES LIKE 'joma_settings'");
        if ($r && mysqli_num_rows($r) > 0) $hasSettings = true;
        hc_add($rows, 'اتصال به دیتابیس', true, 'وصل شد' . ($hasSettings ? ' · جدول joma_settings هست' : ' · جدول joma_settings نیست'),
            $hasSettings ? '' : 'فایل اختیاری «اختیاری-1-joma_settings.sql» را در phpMyAdmin یک‌بار اجرا کن (ADD-ONLY).');
        mysqli_close($link);
    } else {
        hc_add($rows, 'اتصال به دیتابیس', false, 'وصل نشد', 'مشخصات دیتابیس را در config/config.php بررسی کن.');
    }
}

/* ---------- ۸) کتابخانهٔ فعالیت‌ها ---------- */
$libCount = 0;
$libFile = $root . '/database/library_official.json';
if (is_file($libFile)) {
    $lib = json_decode(file_get_contents($libFile), true);
    if (is_array($lib)) $libCount = count($lib);
}
hc_add($rows, 'کتابخانهٔ فعالیت‌ها', $libCount >= 107, fa_num($libCount) . ' فعالیت',
    $libCount >= 107 ? '' : 'فایل database/library_official.json را از بستهٔ کامل برگردان (باید ۱۰۷ فعالیت باشد).');

/* ---------- ۹) کارهای بعد از نصب ---------- */
hc_add($rows, 'فایل ساخت دادهٔ نمونه', !is_file($root . '/_SEED-DEMO.php'),
    is_file($root . '/_SEED-DEMO.php') ? 'هنوز روی سرور است' : 'پاک شده است',
    'اگر دیگر لازمش نداری، _SEED-DEMO.php را از File Manager پاک کن.');

hc_add($rows, 'فایل نصب خطرناک (install.php)', !is_file($root . '/install.php'),
    is_file($root . '/install.php') ? 'روی سرور هست' : 'نصبش نیست',
    'فایل install.php را از سرور بردار؛ روی نصب فعال، اجرای آن خطرناک است.');

/* ---------- ۱۰) زمان سرور ---------- */
$tz = date_default_timezone_get();
$now = date('Y-m-d H:i');
hc_add($rows, 'ساعت و منطقهٔ زمانی سرور', true, $now . ' · ' . $tz,
    'اگر ساعت سرور با تهران اختلاف دارد، در cPanel → MultiPHP INI Editor مقدار date.timezone را Asia/Tehran بگذار.');

/* ---------- ۱۱) نسخهٔ بسته و وضعیت قابلیت‌ها ---------- */
$features = array();
foreach (array(
    'جوجهٔ من' => 'functions/jooje.php',
    'آب پیش‌نویس/قطعی (B6)' => 'functions/water.php',
    'دفترچه و بینش (B7)' => 'functions/insight.php',
    'کپی برنامهٔ ماه بعد (B8)' => 'functions/plancopy.php',
    'حقوق داده' => 'pages/rights.php',
    'کنسول مدیر' => 'pages/admin_console.php',
    'هم‌مسیر' => 'functions/hammasir.php',
) as $label => $file) {
    $features[] = ($label . ': ' . (is_file($root . '/' . $file) ? '✔' : '✘'));
}
$okCount = 0; $badCount = 0;
foreach ($rows as $r) { if ($r['ok']) $okCount++; else $badCount++; }
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>بررسی سلامت نصب جوما</title>
<style>
  body{font-family:Tahoma,system-ui,sans-serif;background:#fbf7f2;color:#2f2a33;margin:0;padding:26px 16px;line-height:1.9}
  .wrap{max-width:820px;margin:0 auto}
  h1{font-size:22px;margin:0 0 4px}
  .sum{border-radius:14px;padding:14px 16px;margin:14px 0;font-weight:700}
  .sum.ok{background:#e7f7ef;border:1px solid #bfe7d0;color:#0a7e5b}
  .sum.bad{background:#fff4e5;border:1px solid #f3d9ae;color:#8a5a12}
  table{width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden;font-size:13.5px}
  th,td{padding:9px 12px;border-bottom:1px solid #f1ece4;text-align:right;vertical-align:top}
  th{background:#f7f2ec;font-size:12.5px;color:#6f6876}
  .y{color:#12905F;font-weight:900}
  .n{color:#b42318;font-weight:900}
  .fx{color:#8a5a12;font-size:12.5px}
  .feat{background:#fff;border:1px solid #eee2d6;border-radius:14px;padding:12px 14px;margin-top:14px;font-size:13px}
  a.btn{display:inline-block;background:#0FA678;color:#fff;text-decoration:none;padding:8px 15px;border-radius:10px;margin:6px 6px 0 0}
  code{background:#f4f0ea;padding:1px 6px;border-radius:6px;direction:ltr;display:inline-block}
</style>
</head>
<body>
<div class="wrap">
  <h1>بررسی سلامت نصب جوما</h1>
  <p style="color:#6f6876;margin:0">این صفحه هیچ‌چیز را تغییر نمی‌دهد؛ فقط می‌گوید نصب روی این هاست سالم است یا نه.</p>

  <div class="sum <?php echo $badCount === 0 ? 'ok' : 'bad'; ?>">
    <?php if ($badCount === 0) { ?>
      همه‌چیز مرتب است ✔ — می‌توانی با خیال راحت تست را شروع کنی.
    <?php } else { ?>
      <?php echo fa_num($badCount); ?> مورد نیاز به رسیدگی دارد (ردیف‌های قرمز پایین؛ راه‌حل هرکدام نوشته شده).
    <?php } ?>
    <div style="font-weight:400;font-size:12.5px;margin-top:4px"><?php echo fa_num($okCount); ?> مورد سالم · <?php echo fa_num($badCount); ?> مورد نیازمند کار</div>
  </div>

  <table>
    <thead><tr><th>مورد</th><th>وضعیت</th><th>جزئیات</th><th>اگر درست نیست</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r) { ?>
      <tr>
        <td><?php echo htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="<?php echo $r['ok'] ? 'y' : 'n'; ?>"><?php echo $r['ok'] ? 'سالم' : 'نیازمند کار'; ?></td>
        <td><?php echo htmlspecialchars($r['detail'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="fx"><?php echo htmlspecialchars($r['fix'], ENT_QUOTES, 'UTF-8'); ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>

  <div class="feat">
    <b>قابلیت‌های موجود در این نصب</b>
    <div style="margin-top:6px"><?php echo implode(' · ', array_map('htmlspecialchars', $features)); ?></div>
  </div>

  <div style="margin-top:16px">
    <a class="btn" href="index.php?p=home">رفتن به اپ</a>
    <a class="btn" href="index.php?p=login" style="background:#EDFAF3;color:#2f2a33">ورود</a>
    <a class="btn" href="tests/run-b6b7.php" style="background:#EDFAF3;color:#2f2a33">آزمون آب و دفترچه</a>
    <a class="btn" href="tests/run-b8.php" style="background:#EDFAF3;color:#2f2a33">آزمون کپی ماه بعد</a>
  </div>
  <p style="color:#8a8296;font-size:12px;margin-top:14px">حالت پیش‌نمایش: <?php echo $isPreview ? 'بله' : 'خیر'; ?> · PHP: <?php echo htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
</body>
</html>
