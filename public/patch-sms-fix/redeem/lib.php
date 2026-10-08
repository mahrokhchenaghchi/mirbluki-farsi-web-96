<?php
/**
 * توابع مشترک اپ دروازه: پایگاه داده، امنیت، پیامک، ابزارها
 */
declare(strict_types=1);

// نشست باید پیش از هر خروجی شروع شود، وگرنه مرورگر کوکی را نمی‌گیرد
// (اپ‌های API با ثابت GATE_NO_SESSION نشست نمی‌سازند)
if (!defined('GATE_NO_SESSION') && PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function cfg(?string $key = null)
{
    static $c = null;
    if ($c === null) $c = require __DIR__ . '/config.php';
    return $key === null ? $c : ($c[$key] ?? null);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $d = cfg('db');
    $isSqlite = str_starts_with($d['dsn'], 'sqlite:');
    if ($isSqlite) {
        $dir = dirname(substr($d['dsn'], 7));
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
    }
    $pdo = new PDO($d['dsn'], $d['user'] ?? '', $d['pass'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    init_schema($pdo, $isSqlite);
    return $pdo;
}

function init_schema(PDO $pdo, bool $sqlite): void
{
    $auto = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT PRIMARY KEY AUTO_INCREMENT';
    $t = fn(string $sql) => $pdo->exec($sql);
    $t("CREATE TABLE IF NOT EXISTS serials (
        code VARCHAR(40) PRIMARY KEY, batch VARCHAR(40) NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'new', phone VARCHAR(20) NULL, activated_at VARCHAR(32) NULL)");
    $t("CREATE TABLE IF NOT EXISTS users (
        id $auto, phone VARCHAR(20) NULL UNIQUE, name VARCHAR(120) NULL, serial VARCHAR(40) NULL,
        src VARCHAR(24) NULL, test_token VARCHAR(64) NULL, test_completed TINYINT NOT NULL DEFAULT 0,
        created_at VARCHAR(32) NULL)");
    $t("CREATE TABLE IF NOT EXISTS otp (
        id $auto, phone VARCHAR(20) NULL, code_hash VARCHAR(255) NULL, created_at INT NULL,
        expires_at INT NULL, tries INT NOT NULL DEFAULT 0, consumed TINYINT NOT NULL DEFAULT 0)");
    $t("CREATE TABLE IF NOT EXISTS results (
        id $auto, phone VARCHAR(20) NULL, top1 VARCHAR(40) NULL, top2 VARCHAR(40) NULL, top3 VARCHAR(40) NULL,
        scores_json TEXT NULL, created_at VARCHAR(32) NULL)");
    $t("CREATE TABLE IF NOT EXISTS requests (
        id $auto, phone VARCHAR(20) NULL, name VARCHAR(120) NULL, package_key VARCHAR(40) NULL, note TEXT NULL,
        status VARCHAR(16) NOT NULL DEFAULT 'new', joma_user VARCHAR(80) NULL, joma_pass VARCHAR(80) NULL,
        created_at VARCHAR(32) NULL, updated_at VARCHAR(32) NULL)");
    $t("CREATE TABLE IF NOT EXISTS events (
        id $auto, ts INT NULL, name VARCHAR(40) NULL, phone VARCHAR(20) NULL, meta TEXT NULL)");
    $t("CREATE TABLE IF NOT EXISTS settings (
        k VARCHAR(60) PRIMARY KEY, v TEXT NULL)");

    // بارگذاری خودکار سریال‌ها از serials.csv در اولین اجرا
    $n = (int)$pdo->query('SELECT COUNT(*) c FROM serials')->fetch()['c'];
    $csv = __DIR__ . '/serials.csv';
    if ($n === 0 && is_readable($csv)) {
        $st = $pdo->prepare('INSERT INTO serials (code,batch,status) VALUES (?,?,?)');
        $fh = fopen($csv, 'r');
        $first = true;
        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = str_getcsv($line);
            if ($first && stripos((string)$parts[0], 'code') !== false) { $first = false; continue; }
            $first = false;
            $code = strtoupper(trim((string)$parts[0]));
            if ($code === '') continue;
            try { $st->execute([$code, (string)($parts[1] ?? '') ?: null, 'new']); } catch (Throwable $e) {}
        }
        fclose($fh);
    }
}

// ------------------------------------------------------------ امنیت و ابزار
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function now_ts(): int { return time(); }
function now_str(): string { return date('Y-m-d H:i:s'); }

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_ok(?string $t): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t);
}

function norm_phone(?string $p): string
{
    $map = ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9'];
    $p = strtr((string)$p, $map);
    $p = preg_replace('/\D+/', '', $p) ?? '';
    if (str_starts_with($p, '0098')) $p = substr($p, 4);
    elseif (str_starts_with($p, '98') && strlen($p) === 12) $p = substr($p, 2);
    if (str_starts_with($p, '9') && strlen($p) === 10) $p = '0' . $p;
    return $p;
}

function valid_phone(string $p): bool { return (bool)preg_match('/^09\d{9}$/', $p); }

function norm_serial(?string $s): string
{
    $map = ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9'];
    $s = strtoupper(trim(strtr((string)$s, $map)));
    $s = preg_replace('/[^A-Z0-9]+/', '-', $s) ?? '';
    return trim($s, '-');
}

function gen_code(int $len = 6): string
{
    $out = '';
    for ($i = 0; $i < $len; $i++) $out .= random_int(0, 9);
    return $out;
}

function gen_token(): string { return bin2hex(random_bytes(24)); }

function log_event(string $name, ?string $phone = null, array $meta = []): void
{
    try {
        db()->prepare('INSERT INTO events (ts,name,phone,meta) VALUES (?,?,?,?)')
            ->execute([now_ts(), $name, $phone, json_encode($meta, JSON_UNESCAPED_UNICODE)]);
    } catch (Throwable $e) { /* ثبت رویداد نباید جریان کاربر را قطع کند */ }
}

function rate_limit(string $name, ?string $phone, int $limit, int $windowSec): bool
{
    $st = db()->prepare('SELECT COUNT(*) c FROM events WHERE name=? AND phone=? AND ts>?');
    $st->execute([$name, $phone, now_ts() - $windowSec]);
    return ((int)($st->fetch()['c'] ?? 0)) < $limit;
}

/** مقدارِ نمونهٔ کلید که در بسته‌های قبلی بود — دیگر هرگز نباید وارد تنظیمات شود */
if (!defined('SMS_KEY_PLACEHOLDER')) define('SMS_KEY_PLACEHOLDER', 'PUT-API-KEY-HERE');

/**
 * اعمال خودکار تنظیمات config.php روی جدول settings.
 * دو قاعدهٔ ایمنی (رفع باگ نسخهٔ قبل):
 *  ۱) مقدار خالی یا نمونه («PUT-API-KEY-HERE») هرگز روی تنظیمات موجود نوشته نمی‌شود؛
 *     برای همین، کلیدی که از پنل مدیر ذخیره کرده‌ای با یک آپلودِ دوباره پاک نمی‌شود.
 *  ۲) هر مقدار «یک‌بار برای هر مقدارِ تازه» اعمال می‌شود؛ بعد از آن اختیار با پنل مدیر است.
 */
function apply_config_defaults(): void
{
    try {
        $cfgKey = trim((string)cfg('sms_key_config'));
        if ($cfgKey !== '' && $cfgKey !== SMS_KEY_PLACEHOLDER && (string)setting('applied_sms_key', '') !== $cfgKey) {
            set_setting('sms_key', $cfgKey);
            set_setting('applied_sms_key', $cfgKey);
        }
        $vals = cfg('sms_defaults_apply');
        if (!is_array($vals) || !$vals) return;
        foreach ($vals as $k => $v) {
            $k = (string)$k;
            $v = trim((string)$v);
            if ($v === '' || $v === SMS_KEY_PLACEHOLDER) continue;
            if ($k === 'sms_key') continue;                       // کلید فقط از sms_key_config یا پنل مدیر
            if ((string)setting('applied:' . $k, '') === $v) continue;
            set_setting($k, $v);
            set_setting('applied:' . $k, $v);
        }
    } catch (Throwable $e) {
        // اگر جدول تازه ساخته شده یا هنوز آماده نیست، بی‌صدا رد شو
    }
}
apply_config_defaults();

// ------------------------------------------------------------ تنظیمات (قابل تغییر از پنل مدیر)
function setting(string $key, $default = null)
{
    try {
        $st = db()->prepare('SELECT v FROM settings WHERE k=?');
        $st->execute([$key]);
        $row = $st->fetch();
        if ($row && isset($row['v']) && $row['v'] !== '') return (string)$row['v'];
    } catch (Throwable $e) { /* اولین اجرا: جدول تازه ساخته شده */ }
    return $default;
}

function set_setting(string $key, string $value): void
{
    $isSqlite = str_starts_with((string)cfg('db')['dsn'], 'sqlite:');
    $sql = $isSqlite
        ? 'INSERT INTO settings (k,v) VALUES (?,?) ON CONFLICT(k) DO UPDATE SET v=excluded.v'
        : 'INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)';
    db()->prepare($sql)->execute([$key, $value]);
}

function otp_mode(): string
{
    return (string)setting('otp_mode', cfg('otp')['mode'] ?? 'dev');
}

/** اگر ارسال پیامک در حالت واقعی شکست بخورد، کد پشتیبان روی صفحه نشان داده شود؟ (پیش‌فرض: روشن) */
function otp_fallback(): bool
{
    return (string)setting('otp_fallback_onscreen', '1') === '1';
}

/** لاگ ماندگار هر تلاش ارسال — برای این‌که «پیامک نرفتن» بدون حدس قابل ردیابی باشد */
function sms_attempt_log(string $phone, array $r): void
{
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    @file_put_contents(
        $dir . '/sms-attempts.log',
        sprintf("%s | %s | %s | %s\n", date('Y-m-d H:i:s'), $phone, $r['ok'] ? 'OK' : 'FAIL',
                preg_replace('/\s+/', ' ', (string)($r['detail'] ?? ''))),
        FILE_APPEND
    );
}

/** ثبت کد پشتیبان (فقط وقتی پیامک نرفته) — در data/fallback-otp.log */
function log_fallback_code(string $phone, string $code): void
{
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    @file_put_contents($dir . '/fallback-otp.log', date('Y-m-d H:i:s') . ' | ' . $phone . ' | کد پشتیبان: ' . $code . "\n", FILE_APPEND);
}

// ------------------------------------------------------------ پیامک
function sms_providers(): array
{
    return [
        'kavenegar'         => 'کاوهنگار (Kavenegar)',
        'melipayamak'       => 'ملی‌پیامک (MeliPayamak)',
        'smsir_pattern'     => 'sms.ir — با الگوی تأییدشده (کد تأیید)',
        'smsir'             => 'sms.ir — متن آزاد با خط',
        'ippanel'           => 'فراز اساماس / آی‌پی‌پنل (IPPanel)',
        'ghasedak'          => 'قاصدک (Ghasedak)',
        'generic_get'       => 'دلخواه — آدرس GET (با {phone} و {text})',
        'generic_post_json' => 'دلخواه — POST با متن JSON',
    ];
}

/** برش امن رشتهٔ UTF-8 — بدون وابستگی به mbstring (روی همهٔ هاست‌ها کار می‌کند) */
function cut_str(string $s, int $len): string
{
    if (function_exists('mb_substr')) return (string)mb_substr($s, 0, $len, 'UTF-8');
    if (strlen($s) <= $len) return $s;
    $out = substr($s, 0, $len);
    while ($out !== '' && (ord($out[strlen($out) - 1]) & 0xC0) === 0x80) $out = substr($out, 0, -1);
    if ($out !== '' && (ord($out[strlen($out) - 1]) & 0xC0) === 0xC0) $out = substr($out, 0, -1);
    return $out;
}

/** نورمال‌سازی یک مقدار تنظیمات: حذف فاصله/خط جدید و بی‌اثرکردن مقدار نمونهٔ قدیمی */
function sms_clean($v): string
{
    $v = str_replace(["\r", "\n", "\t", "\0"], '', (string)$v);
    if (strncmp($v, "\xEF\xBB\xBF", 3) === 0) $v = substr($v, 3);   // BOM که گاهی همراه پیست‌کردن می‌آید
    $v = trim($v);
    return $v === SMS_KEY_PLACEHOLDER ? '' : $v;
}

function sms_settings(): array
{
    $c = cfg('sms');
    $key = sms_clean(setting('sms_key', ''));
    $cfgKey = sms_clean(cfg('sms_key_config'));
    if ($cfgKey !== '') $key = $cfgKey;          // اگر کلید در config.php هم نوشته شده باشد، همان اعمال شده
    return [
        'provider' => sms_clean(setting('sms_provider', (string)($c['provider'] ?? 'generic_get'))),
        'key'      => $key,
        'user'     => sms_clean(setting('sms_user', '')),
        'pass'     => sms_clean(setting('sms_pass', '')),
        'sender'   => sms_clean(setting('sms_sender', '')),
        'url'      => sms_clean(setting('sms_url', (string)($c['url'] ?? ''))),
        // مخصوص ارسال با «الگو/پترن» (sms.ir و مشابه): شناسهٔ الگو و نام متغیر کد
        'template_id'      => sms_clean(setting('sms_template_id', (string)($c['template_id'] ?? ''))),
        'param_code'       => sms_clean(setting('sms_param_code', (string)($c['param_code'] ?? 'CODE'))),
        // پیامک خوش‌آمد (اختیاری)
        'send_welcome'     => sms_clean(setting('sms_send_welcome', '0')),
        'template_welcome' => sms_clean(setting('sms_template_welcome', '')),
        'param_welcome'    => sms_clean(setting('sms_param_welcome', 'LINK')),
    ];
}

/** وضعیت کلید وب‌سرویس — برای تشخیص سریع «کلید ست نشده» در برابر «کلید غلط» */
function sms_key_state(): array
{
    $key = sms_settings()['key'];
    if ($key === '') return ['state' => 'empty', 'label' => 'ست نشده', 'len' => 0, 'masked' => '—'];
    return [
        'state'  => strlen($key) < 24 ? 'suspicious' : 'set',
        'label'  => strlen($key) < 24 ? 'کوتاه/نیمه‌کپی‌شده' : 'ثبت شده',
        'len'    => strlen($key),
        // فقط اثرانگشت نمایش داده می‌شود؛ خودِ کلید هیچ‌جا چاپ نمی‌شود
        'masked' => 'اثر ' . substr(hash('sha256', $key), 0, 8),
    ];
}

/**
 * آزمون مستقیم کلید: GET /v1/credit — اگر این جواب داد، کلید و دسترسی وب‌سرویس سالم است
 * و مشکل فقط از طرف خط/الگو/اعتبار است.
 */
function sms_check_credit(): array
{
    $s = sms_settings();
    if ($s['key'] === '') return ['ok' => false, 'detail' => 'کلید وب‌سرویس خالی است'];
    $r = http_request('GET', 'https://api.sms.ir/v1/credit', null, ['x-api-key: ' . $s['key'], 'Accept: application/json']);
    $raw = trim((string)($r['body'] ?? ''));
    $j = json_decode($raw, true);
    $snip = cut_str(preg_replace('/\s+/', ' ', $raw) ?? '', 140);
    $tag = 'sms.ir/credit · HTTP ' . $r['code'] . ($snip !== '' ? ' · ' . $snip : '');
    if (($r['err'] ?? '') !== '') return ['ok' => false, 'detail' => $tag . ' · ' . $r['err']];
    $st = is_array($j) ? (int)($j['status'] ?? 0) : 0;
    if ($r['code'] === 200 && $st === 1) {
        return ['ok' => true, 'detail' => 'کلید معتبر است ✓ · اعتبار پنل: ' . number_format((float)($j['data'] ?? 0), 0, '.', '٬') . ' تومان'];
    }
    return ['ok' => false, 'detail' => $tag . ' · ' . sms_ir_status_hint($st, (string)($j['message'] ?? ''))];
}

/** ترجمهٔ کدهای وضعیت sms.ir (مطابق جدول رسمی api.sms.ir) */
function sms_ir_status_hint(int $st, string $msg = ''): string
{
    $map = [
        1   => 'نام کاربری یا رمز اشتباه است',
        10  => 'کلید وب‌سرویس نامعتبر است (کپی ناقص، کلید قدیمی، یا کلید از نوع Sandbox)',
        11  => 'کلید وب‌سرویس غیرفعال/منقضی است — در پنل sms.ir کلید تازه بساز',
        12  => 'کلید وب‌سرویس به IPهای تعریف‌شده محدود است — آی‌پی سرور را به فهرست مجاز اضافه کن',
        13  => 'محدودیت ارسال روزانه',
        14  => 'متن پیامک خالی است',
        16  => 'شمارهٔ خط نامعتبر است',
        20  => 'تعداد یا نام پارامترهای الگو درست نیست',
        113 => 'قالبی با این شناسه پیدا نشد — شناسهٔ الگو را چک کن',
    ];
    $hint = $map[$st] ?? ($st === 0 ? '' : 'کد وضعیت ' . $st);
    $msg = trim($msg);
    return trim(($msg !== '' ? 'پاسخ پنل: ' . $msg : '') . ($hint !== '' ? ' — ' . $hint : ''));
}

/** درخواست سادهٔ HTTP بدون وابستگی به curl (اگر curl باشد از آن استفاده می‌کند) */
function http_request(string $method, string $url, ?string $body = null, array $headers = []): array
{
    $method = strtoupper($method);
    if (function_exists('curl_init')) {
        try {
            $ch = curl_init($url);
            $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 8];
            if ($headers) $opts[CURLOPT_HTTPHEADER] = $headers;
            if ($method === 'POST') { $opts[CURLOPT_POST] = true; $opts[CURLOPT_POSTFIELDS] = (string)$body; }
            curl_setopt_array($ch, $opts);
            $out = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($out === false) return ['body' => null, 'code' => $code, 'err' => $err ?: 'اتصال برقرار نشد'];
            return ['body' => (string)$out, 'code' => $code, 'err' => ''];
        } catch (Throwable $e) {
            // می‌رویم سراغ روش دوم
        }
    }
    if (!ini_get('allow_url_fopen')) {
        return ['body' => null, 'code' => 0, 'err' => 'نه curl فعال است و نه allow_url_fopen'];
    }
    $hdr = $headers ? implode("\r\n", $headers) . "\r\n" : '';
    $ctx = stream_context_create(['http' => [
        'method' => $method, 'timeout' => 15, 'ignore_errors' => true, 'header' => $hdr, 'content' => (string)$body,
    ]]);
    $out = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', (string)$http_response_header[0], $m)) $code = (int)$m[1];
    if ($out === false && $code === 0) return ['body' => null, 'code' => 0, 'err' => 'اتصال به وب‌سرویس ممکن نشد'];
    return ['body' => (string)$out, 'code' => $code ?: 200, 'err' => ''];
}

/**
 * ارسال پیامک از پنل خودت — پشتیبانی از پنل‌های رایج ایران.
 * خروجی: ['ok' => bool, 'detail' => 'توضیح خوانا برای عیب‌یابی']
 */
function sms_dispatch(string $phone, string $text, array $vars = []): array
{
    $s = sms_settings();
    $url = ''; $method = 'GET'; $body = null; $headers = [];
    $need = [];

    switch ($s['provider']) {
        case 'kavenegar':
            $need = ['key'];
            $url = 'https://api.kavenegar.com/v1/' . rawurlencode($s['key']) . '/sms/send.json'
                 . '?receptor=' . rawurlencode($phone) . '&message=' . rawurlencode($text);
            if ($s['sender'] !== '') $url .= '&sender=' . rawurlencode($s['sender']);
            break;

        case 'melipayamak':
            $need = ['user', 'pass', 'sender'];
            $url = 'https://rest.payamak-panel.com/api/SendSMS/SendSMS';
            $method = 'POST';
            $headers = ['Content-Type: application/x-www-form-urlencoded'];
            $body = http_build_query([
                'username' => $s['user'], 'password' => $s['pass'], 'from' => $s['sender'],
                'to' => $phone, 'text' => $text, 'isflash' => 'false',
            ]);
            break;

        case 'smsir_pattern':
            $need = ['key', 'template_id'];
            $url = 'https://api.sms.ir/v1/send/verify';
            $method = 'POST';
            $headers = ['Content-Type: application/json', 'Accept: text/plain', 'x-api-key: ' . $s['key']];
            $pname = trim($s['param_code']) !== '' ? trim($s['param_code']) : 'CODE';
            $pval  = (string)($vars['code'] ?? $vars['value'] ?? $text);
            $body = json_encode([
                'mobile' => $phone,
                'templateId' => (int)$s['template_id'],
                'parameters' => [['name' => $pname, 'value' => $pval]],
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'smsir':
            $need = ['key', 'sender'];
            $url = 'https://api.sms.ir/v1/send/bulk';
            $method = 'POST';
            $headers = ['Content-Type: application/json', 'Accept: application/json', 'x-api-key: ' . $s['key']];
            $body = json_encode(['lineNumber' => $s['sender'], 'messageText' => $text, 'mobiles' => [$phone]], JSON_UNESCAPED_UNICODE);
            break;

        case 'ippanel':
            $need = ['user', 'pass', 'sender'];
            $url = 'https://ippanel.com/services.jspd';
            $method = 'POST';
            $headers = ['Content-Type: application/x-www-form-urlencoded'];
            $body = http_build_query([
                'uname' => $s['user'], 'pass' => $s['pass'], 'from' => $s['sender'],
                'message' => $text, 'to' => $phone, 'op' => 'send',
            ]);
            break;

        case 'ghasedak':
            $need = ['key'];
            $url = 'https://api.ghasedak.me/v2/sms/send/simple'
                 . '?receptor=' . rawurlencode($phone) . '&message=' . rawurlencode($text);
            $headers = ['apikey: ' . $s['key']];
            if ($s['sender'] !== '') $url .= '&linenumber=' . rawurlencode($s['sender']);
            break;

        case 'generic_post_json':
            $need = ['url'];
            $url = $s['url'];
            $method = 'POST';
            $headers = ['Content-Type: application/json'];
            $body = json_encode(['mobile' => $phone, 'message' => $text, 'text' => $text], JSON_UNESCAPED_UNICODE);
            break;

        default: // generic_get
            $need = ['url'];
            $url = str_replace(['{phone}', '{text}'], [rawurlencode($phone), rawurlencode($text)], $s['url']);
    }

    foreach ($need as $k) {
        if (trim((string)$s[$k]) === '') {
            $fix = $k === 'key' ? 'کلید وب‌سرویس تنظیم نشده — پنل مدیر → «تنظیمات پیامک» → کلید وب‌سرویس (یا خط sms_key_config در config.php)'
                                : 'فیلد ناقص: ' . $k . ' (پنل مدیر → تنظیمات پیامک پر کن)';
            return ['ok' => false, 'detail' => $fix];
        }
    }
    if (in_array($s['provider'], ['smsir', 'smsir_pattern'], true) && strlen($s['key']) < 24) {
        return ['ok' => false, 'detail' => 'کلید وب‌سرویس کوتاه است (' . strlen($s['key']) . ' کاراکتر) — کلید sms.ir باید کامل کپی شود (۶۴ کاراکتر)'];
    }
    if (trim($url) === '') return ['ok' => false, 'detail' => 'آدرس وب‌سرویس خالی است'];

    $r = http_request($method, $url, $body, $headers);
    $raw = (string)($r['body'] ?? '');
    $j = json_decode($raw, true);
    $snip = $raw === '' ? ''
        : preg_replace('/\s+/', ' ', cut_str(is_array($j) ? (string)json_encode($j, JSON_UNESCAPED_UNICODE) : $raw, 150));
    $tag = $s['provider'] . ' · HTTP ' . $r['code'] . ($snip !== '' ? ' · ' . $snip : '');

    if ($r['err'] !== '') return ['ok' => false, 'detail' => $tag . ' · ' . $r['err']];
    if ($r['code'] >= 400) return ['ok' => false, 'detail' => $tag];

    // ── ارزیابی پاسخ، طبق قرارداد هر پنل ──
    $bad = '';
    switch ($s['provider']) {
        case 'kavenegar':
            $st = (int)($j['return']['status'] ?? 0);
            if ($st !== 200) $bad = 'پاسخ پنل: ' . (string)($j['return']['message'] ?? ('کد وضعیت ' . $st));
            break;
        case 'melipayamak':
            $st = (int)($j['RetStatus'] ?? 0);
            if ($st !== 1) $bad = 'پاسخ پنل: ' . (string)($j['StrRetStatus'] ?? ('کد وضعیت ' . $st));
            break;
        case 'smsir':
        case 'smsir_pattern':
            $st = (int)($j['status'] ?? 0);
            if ($st !== 1) $bad = sms_ir_status_hint($st, (string)($j['message'] ?? ''));
            break;
        case 'ippanel':
            if (!str_starts_with(trim($raw), '0')) $bad = 'پاسخ پنل: ' . (trim($raw) !== '' ? trim(cut_str(trim($raw), 80)) : 'پاسخ خالی');
            break;
        case 'ghasedak':
            $st = (int)($j['result']['code'] ?? 0);
            if ($st !== 200) $bad = 'پاسخ پنل: ' . (string)($j['result']['message'] ?? ('کد وضعیت ' . $st));
            break;
        default:
            if (is_array($j) && isset($j['status']) && !in_array((int)$j['status'], [0, 1, 200], true)) {
                $bad = 'پاسخ پنل: ' . (string)($j['message'] ?? ('کد وضعیت ' . (int)$j['status']));
            }
    }
    if ($bad !== '') return ['ok' => false, 'detail' => $tag . ' · ' . $bad];

    return ['ok' => true, 'detail' => $tag];
}

/** ارسال پیامک (dev = فقط ثبت در فایل لاگ) */
function send_sms(string $phone, string $text, array $vars = []): bool
{
    if (otp_mode() === 'dev') {
        $dir = __DIR__ . '/data';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents($dir . '/dev-otp.log', date('Y-m-d H:i:s') . " | $phone | $text\n", FILE_APPEND);
        return true;
    }
    $r = sms_dispatch($phone, $text, $vars);
    try { set_setting('sms_last_attempt', date('Y-m-d H:i') . ' · ' . ($r['ok'] ? 'موفق ✓ · ' : 'ناموفق ✗ · ') . $r['detail']); } catch (Throwable $e) {}
    sms_attempt_log($phone, $r);
    if (!$r['ok']) log_event('sms_failed', $phone, ['detail' => $r['detail'], 'mode' => otp_mode(), 'provider' => sms_settings()['provider']]);
    return $r['ok'];
}

function send_otp_sms(string $phone, string $code): bool
{
    $tpl = (string)setting('sms_text_otp', cfg('sms')['text_otp'] ?? 'کد تأیید شما: {code}');
    return send_sms($phone, str_replace('{code}', $code, $tpl), ['code' => $code, 'value' => $code]);
}

function send_welcome_sms(string $phone, string $name, string $link): bool
{
    $s = sms_settings();
    if ($s['send_welcome'] !== '1') return true;      // خاموش است؛ کاری نمی‌کنیم

    // حالت الگویی: به الگوی جداگانهٔ خوش‌آمد نیاز دارد
    if ($s['provider'] === 'smsir_pattern' && trim($s['template_welcome']) !== '') {
        $tpl = $s;                                    // فقط برای خوانایی
        $pname = trim($s['param_welcome']) !== '' ? trim($s['param_welcome']) : 'LINK';
        $r = sms_dispatch_with_template($phone, (int)$s['template_welcome'], $pname, $link);
        try { set_setting('sms_last_attempt', date('Y-m-d H:i') . ' · ' . ($r['ok'] ? 'موفق ✓ · ' : 'ناموفق ✗ · ') . $r['detail']); } catch (Throwable $e) {}
        return (bool)$r['ok'];
    }

    $tpl = (string)setting('sms_text_welcome', cfg('sms')['text_welcome'] ?? '{name} عزیز، لینک شما: {link}');
    return send_sms($phone, str_replace(['{name}', '{link}'], [$name ?: 'دوست', $link], $tpl));
}

/** ارسال با یک الگوی مشخص (برای پیامک خوش‌آمد در حالت الگویی) */
function sms_dispatch_with_template(string $phone, int $templateId, string $paramName, string $value): array
{
    $s = sms_settings();
    if (trim($s['key']) === '') return ['ok' => false, 'detail' => 'کلید API خالی است'];
    $body = json_encode([
        'mobile' => $phone, 'templateId' => $templateId,
        'parameters' => [['name' => $paramName, 'value' => $value]],
    ], JSON_UNESCAPED_UNICODE);
    $r = http_request('POST', 'https://api.sms.ir/v1/send/verify', $body,
        ['Content-Type: application/json', 'Accept: text/plain', 'x-api-key: ' . $s['key']]);
    $j = json_decode((string)($r['body'] ?? ''), true);
    $snip = preg_replace('/\s+/', ' ', cut_str(is_array($j) ? (string)json_encode($j, JSON_UNESCAPED_UNICODE) : (string)($r['body'] ?? ''), 150));
    $detail = 'sms.ir (الگوی خوش‌آمد) · HTTP ' . $r['code'] . ($snip !== '' ? ' · ' . $snip : '');
    if ($r['err'] !== '') return ['ok' => false, 'detail' => $detail . ' · ' . $r['err']];
    return ['ok' => ((int)($j['status'] ?? 0) === 1), 'detail' => $detail];
}

// ------------------------------------------------------------ سریال و کاربر
function serial_info(string $code): ?array
{
    $st = db()->prepare('SELECT * FROM serials WHERE code=?');
    $st->execute([$code]);
    return $st->fetch() ?: null;
}

function user_by_phone(string $phone): ?array
{
    $st = db()->prepare('SELECT * FROM users WHERE phone=?');
    $st->execute([$phone]);
    return $st->fetch() ?: null;
}

function user_by_token(string $tk): ?array
{
    $st = db()->prepare('SELECT * FROM users WHERE test_token=?');
    $st->execute([$tk]);
    return $st->fetch() ?: null;
}

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function api_secret_ok(): bool
{
    $given = $_SERVER['HTTP_X_API_SECRET'] ?? ($_POST['secret'] ?? '');
    $real = (string)cfg('api_secret');
    return $real !== '' && is_string($given) && hash_equals($real, $given);
}

// قالب نمایش (page_head / page_foot) همیشه همراه lib بارگذاری می‌شود
require_once __DIR__ . '/ui.php';
