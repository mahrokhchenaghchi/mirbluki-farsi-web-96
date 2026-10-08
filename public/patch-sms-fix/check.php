<?php
/**
 * صفحهٔ «بررسی سلامت نصب» — test.mirbolouki.com/check.php
 * این صفحه فقط می‌خواند و چیزی را تغییر نمی‌دهد. مطمئن می‌شوی همه‌چیز سرجایش است.
 */
declare(strict_types=1);

$ROOT = __DIR__;
$rows = [];
function row(string $title, string $state, string $detail): void
{
    global $rows;
    $rows[] = [$title, $state, $detail];   // state: ok | warn | bad
}

$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
row('نسخهٔ PHP', $phpOk ? 'ok' : 'bad', PHP_VERSION . ($phpOk ? '' : ' — باید ۸.۰ یا بالاتر باشد'));

$hasSqlite = extension_loaded('pdo_sqlite');
$hasMysql  = extension_loaded('pdo_mysql');
row('درایور پایگاه داده', ($hasSqlite || $hasMysql) ? 'ok' : 'bad',
    'SQLite: ' . ($hasSqlite ? 'فعال ✅' : 'غیرفعال ✖') . ' · MySQL: ' . ($hasMysql ? 'فعال ✅' : 'غیرفعال ✖') .
    (($hasSqlite || $hasMysql) ? '' : ' — از سی‌پنل → Select PHP Version → Extensions، pdo_sqlite یا pdo_mysql را فعال کن'));

$curlOk = function_exists('curl_init');
$fopenOk = (bool)ini_get('allow_url_fopen');
row('امکان ارسال پیامک/ارتباط با API', ($curlOk || $fopenOk) ? 'ok' : 'warn',
    ($curlOk ? 'curl فعال ✅' : 'curl غیرفعال') . ' · ' . ($fopenOk ? 'allow_url_fopen فعال ✅' : 'allow_url_fopen غیرفعال') .
    (($curlOk || $fopenOk) ? '' : ' — در سی‌پنل → Select PHP Version → Extensions گزینهٔ curl را فعال کن'));

$dataDir = $ROOT . '/redeem/data';
$dataOk = is_dir($dataDir) && is_writable($dataDir);
row('پوشهٔ داده (redeem/data)', $dataOk ? 'ok' : 'bad',
    $dataOk ? 'قابل نوشتن ✅' : 'قابل نوشتن نیست — در File Manager روی پوشهٔ data کلیک راست → Change Permissions → ۷۷۵ (یا ۷۵۵)');

$cfgFile = $ROOT . '/redeem/config.php';
if (!is_file($cfgFile)) {
    row('فایل تنظیمات دروازه', 'bad', 'redeem/config.php پیدا نشد — یعنی فایل‌های دروازه آپلود نشده‌اند');
    $cfg = null;
} else {
    $cfg = require $cfgFile;
    row('فایل تنظیمات دروازه', 'ok', 'redeem/config.php سرجایش است');
}

// بررسی توکن API صفحهٔ مسیر
$landing = $ROOT . '/tract/tract-landing.php';
if (is_file($landing)) {
    $src = (string)file_get_contents($landing);
    preg_match("/\\\$SECRET\s*=\s*\(string\)\(getenv\('GATE_SECRET'\)\s*\?:\s*'([^']*)'\)/", $src, $m);
    $secInLanding = $m[1] ?? '';
    $secInConfig  = (string)($cfg['api_secret'] ?? '');
    $match = $secInLanding !== '' && $secInLanding === $secInConfig;
    row('صفحهٔ مسیر (tract)', 'ok', 'فایل‌ها سرجایشان هستند');
    row('هم‌خوانی کلید API', $match ? 'ok' : 'warn',
        $match ? 'کلید دروازه و صفحهٔ مسیر یکی است ✅'
               : 'کلید این دو فایل یکی نیست — یعنی صفحهٔ «خوش آمدی» نمی‌تواند حساب را بخواند. برای رفع، بگو تا فایل درست را بفرستم.');
} else {
    row('صفحهٔ مسیر (tract)', 'bad', 'tract/tract-landing.php پیدا نشد — پوشهٔ tract آپلود نشده است');
}

// بررسی پایگاه داده و سریال‌ها
if ($cfg) {
    try {
        $dsn = (string)$cfg['db']['dsn'];
        $pdo = new PDO($dsn, (string)($cfg['db']['user'] ?? ''), (string)($cfg['db']['pass'] ?? ''),
                       [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        row('اتصال به پایگاه داده', 'ok', str_starts_with($dsn, 'sqlite:') ? 'SQLite (فایل داخلی)' : 'MySQL');
        $isSqlite = str_starts_with($dsn, 'sqlite:');
        $tables = $isSqlite
            ? $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN)
            : $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('serials', $tables, true)) {
            $n = (int)$pdo->query('SELECT COUNT(*) c FROM serials')->fetch()['c'];
            $used = (int)$pdo->query("SELECT COUNT(*) c FROM serials WHERE status='used'")->fetch()['c'];
            row('سریال‌ها', $n > 0 ? 'ok' : 'warn', "تعداد: $n · فعال‌شده: $used" . ($n === 0 ? ' — به /redeem/ برو تا خودش از فایل serials.csv پر کند' : ''));
        } else {
            row('سریال‌ها', 'warn', 'جدول‌ها هنوز ساخته نشده‌اند — روی دکمهٔ «آماده‌سازی خودکار» پایین همین صفحه بزن');
        }
        $mode = 'dev';
        if (in_array('settings', $tables, true)) {
            $st = $pdo->prepare('SELECT v FROM settings WHERE k=?');
            $st->execute(['otp_mode']);
            $mode = (string)(($st->fetch()['v'] ?? '') ?: 'dev');
        }
        $smsUrl = '';
        if (in_array('settings', $tables, true)) {
            $st = $pdo->prepare('SELECT v FROM settings WHERE k=?');
            $st->execute(['sms_url']);
            $smsUrl = (string)($st->fetch()['v'] ?? '');
        }
        $ssl = [];
        if (in_array('settings', $tables, true)) {
            foreach (['sms_provider', 'sms_sender', 'sms_last_attempt', 'sms_template_id', 'sms_param_code', 'sms_key', 'sms_key_check'] as $k) {
                $st = $pdo->prepare('SELECT v FROM settings WHERE k=?');
                $st->execute([$k]);
                $ssl[$k] = (string)($st->fetch()['v'] ?? '');
            }
        }
        $provNames = [
            'kavenegar' => 'کاوهنگار', 'melipayamak' => 'ملی‌پیامک', 'smsir' => 'sms.ir (متن آزاد)',
            'smsir_pattern' => 'sms.ir (الگوی تأییدشده)',
            'ippanel' => 'فراز اسامه/آی‌پی‌پنل', 'ghasedak' => 'قاصدک',
            'generic_get' => 'دلخواه (GET)', 'generic_post_json' => 'دلخواه (JSON)', '' => '— انتخاب نشده',
        ];
        // وضعیت کلید وب‌سرویس (خودِ check.php وابستگی به lib.php ندارد)
        $rawKey = (string)($ssl['sms_key'] ?? '');
        if ($rawKey === 'PUT-API-KEY-HERE') $rawKey = '';
        $rawKey = trim($rawKey);
        $keyState = $rawKey === '' ? 'empty' : (strlen($rawKey) < 24 ? 'short' : 'set');
        $keyMasked = $rawKey === '' ? '—' : 'اثر ' . substr(hash('sha256', $rawKey), 0, 8);
        $prov = (string)($ssl['sms_provider'] ?? '');
        $provName = $provNames[$prov] ?? $prov;
        // از نسخهٔ جدید: provider و شمارهٔ فرستنده در جدول settings هستند؛ در نسخهٔ قدیمی فقط sms_url بود
        $newSms = $prov !== '';
        $extra = [];
        if ($ssl['sms_sender'] !== '')      $extra[] = 'شمارهٔ فرستنده: ' . $ssl['sms_sender'];
        if ($prov === 'smsir_pattern')      $extra[] = 'شناسهٔ الگو: ' . ($ssl['sms_template_id'] !== '' ? $ssl['sms_template_id'] : 'خالی!')
                                                      . ' · نام متغیر: ' . ($ssl['sms_param_code'] !== '' ? $ssl['sms_param_code'] : 'CODE');
        if (in_array($prov, ['smsir', 'smsir_pattern'], true) && $ssl['sms_sender'] === '' && $prov === 'smsir') $extra[] = 'شمارهٔ خط خالی است!';
        row('پنل پیامک انتخابی', $newSms ? 'ok' : 'warn',
            $newSms
                ? $provName . ($extra ? ' · ' . implode(' · ', $extra) : '')
                : 'در نسخهٔ فعلی نصب‌شده، انتخاب پنل نیست (نصب قدیمی است). بستهٔ به‌روز `patch-sms.zip` را آپلود کن.');
        row('حالت کد تأیید (پیامک)', $mode === 'sms' ? 'ok' : 'warn',
            $mode === 'sms'
                ? 'واقعی — کد برای مخاطب پیامک می‌شود. آزمون ارسال را در پنل مدیر → «خلاصه» → «ارسال آزمایشی» بزن.'
                : 'در حالت «تست» است؛ مخاطب پیامک نمی‌گیرد. در پنل مدیر → «خلاصه» وضعیت پیامک را ببین');
        $last = (string)($ssl['sms_last_attempt'] ?? '');
        row('کلید وب‌سرویس پیامک',
            $keyState === 'set' ? 'ok' : ($keyState === 'short' ? 'warn' : 'bad'),
            $keyState === 'set'
                ? 'ثبت شده · ' . $keyMasked . ' · طول ' . strlen($rawKey) . ' کاراکتر (کلید کامل sms.ir = ۶۴ کاراکتر، نوع «واقعی» نه Sandbox)'
                : ($keyState === 'short'
                    ? 'کلید کوتاه/نیمه‌کپی‌شده است (' . strlen($rawKey) . ' کاراکتر) — کلید را کامل کپی کن'
                    : 'کلید ست نشده است ✖ — تا این مقدار پر نشود پیامکی برای مخاطب نمی‌رود. پنل مدیر → «تنظیمات پیامک» یا خط sms_key_config در redeem/config.php'));
        if (($ssl['sms_key_check'] ?? '') !== '') {
            $kc = (string)$ssl['sms_key_check'];
            row('آخرین آزمون کلید (اعتبار پنل)', str_contains($kc, 'موفق ✓') ? 'ok' : 'bad', $kc);
        }
        // باگ نسخهٔ قبل: «ناموفق» شامل زیررشتهٔ «موفق» است؛ پس هر ارسال شکست‌خورده «سالم» نشان داده می‌شد.
        $lastOk  = $last !== '' && str_contains($last, 'موفق ✓');
        $lastBad = $last !== '' && str_contains($last, 'ناموفق');
        row('آخرین تلاش ارسال پیامک', $last === '' ? 'warn' : ($lastOk ? 'ok' : 'bad'),
            $last !== ''
                ? $last . ($lastBad ? ' ← این ارسال شکست خورده است؛ متن کامل پاسخ پنل پیامک داخل همین خط است' : '')
                : 'هنوز پیامکی ارسال نشده — از پنل مدیر → «خلاصه» → «ارسال آزمایشی» را بزن');
    } catch (Throwable $e) {
        row('اتصال به پایگاه داده', 'bad', 'خطا: ' . $e->getMessage());
    }
}

$bad  = count(array_filter($rows, fn($r) => $r[1] === 'bad'));
$warn = count(array_filter($rows, fn($r) => $r[1] === 'warn'));
$head = $bad ? 'نصب کامل نشده — ' . $bad . ' مورد باید درست شود'
             : ($warn ? 'نصب سالم است، ' . $warn . ' مورد توصیه‌ای باقی مانده' : 'همه‌چیز آماده است ✅');
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title>بررسی نصب — مسیر تراکت</title>
<link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
<style>
:root{--ink:#241f19;--muted:#6d6357;--line:#e3dbcd;--teal:#08a078;--gold:#f0cc3c;--navy:#061020;--cream:#f7f3ea}
*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:var(--ink);direction:rtl;line-height:2;
  font-family:Vazirmatn,Tahoma,system-ui,sans-serif}
.wrap{max-width:760px;margin:0 auto;padding:22px 16px 60px}
h1{font-size:21px;margin:0 0 6px}
.sub{color:var(--muted);font-size:14px;margin:0 0 16px}
.banner{border-radius:14px;padding:14px 16px;font-weight:700;margin-bottom:16px;border:1px solid}
.banner.ok{background:#eaf7f1;border-color:#bfe6d4;color:#1d5c46}
.banner.warn{background:#fdf6df;border-color:#efd98a;color:#7a5f0d}
.banner.bad{background:#fdf3f0;border-color:#eccfc4;color:#8d3b22}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;font-size:14px}
th,td{border-bottom:1px solid #f0eade;padding:10px 12px;text-align:right;vertical-align:top}
th{background:#f2ece0}
td.s{white-space:nowrap;font-weight:700}
td.s.ok{color:#1d5c46}td.s.warn{color:#7a5f0d}td.s.bad{color:#8d3b22}
.btns{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
a.btn{display:inline-block;text-decoration:none;border-radius:12px;padding:12px 18px;font-weight:700;background:var(--teal);color:#fff}
a.btn.ghost{background:#fff;color:#0b6a52;border:1.5px solid #bfe6da}
a.btn.navy{background:var(--navy)}
.note{margin-top:18px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:14px 16px;font-size:13.5px;color:#4a4438}
code{direction:ltr;font-family:ui-monospace,monospace;background:#f4efe4;border-radius:5px;padding:1px 5px;font-size:12.5px}
</style>
</head>
<body><div class="wrap">
  <h1>بررسی نصب مسیر تراکت</h1>
  <p class="sub">این صفحه وضعیت فایل‌ها، پایگاه داده و پیامک را نشان می‌دهد. اگر همه «سالم» بود، مسیر آماده است.</p>

  <div class="banner <?= $bad ? 'bad' : ($warn ? 'warn' : 'ok') ?>"><?= htmlspecialchars($head, ENT_QUOTES) ?></div>

  <table>
    <tr><th style="width:32%">مورد</th><th style="width:12%">وضعیت</th><th>توضیح</th></tr>
    <?php foreach ($rows as [$t, $s, $d]): ?>
      <tr><td><?= htmlspecialchars($t, ENT_QUOTES) ?></td>
          <td class="s <?= $s ?>"><?= $s === 'ok' ? 'سالم' : ($s === 'warn' ? 'توصیه' : 'اشکال') ?></td>
          <td><?= htmlspecialchars($d, ENT_QUOTES) ?></td></tr>
    <?php endforeach; ?>
  </table>

  <div class="btns">
    <a class="btn navy" href="redeem/?setup=1">آماده‌سازی خودکار (ساخت جدول‌ها و درج ۲۰ سریال)</a>
    <a class="btn" href="redeem/?s=ML-1404-0001&src=check">امتحان دروازه با سریال ۰۰۰۱</a>
    <a class="btn ghost" href="redeem/admin.php">پنل مدیریت</a>
    <a class="btn ghost" href="tract/">صفحهٔ مسیر (مسیر کاربر)</a>
  </div>

  <div class="note">
    <b>یادآوری:</b> QRهای چاپ‌شده همه به <code>test.mirbolouki.com/redeem/?s=ML-1404-00xx&src=tract</code> می‌روند؛
    پس همین ساب‌دامین باید بالا باشد. صفحهٔ ایونت هم روی <code>mirbolouki.com/event/midnight-library</code> است
    (همان آدرسی که روی پشت تراکت چاپ شده).
  </div>
</div></body></html>
