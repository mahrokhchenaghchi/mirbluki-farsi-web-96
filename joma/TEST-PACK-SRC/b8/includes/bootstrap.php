<?php
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
    || (strpos($host, 'e2b.app') !== false);
$preview = (getenv('JOMA_PREVIEW') === '1') || (strpos($host, 'e2b.app') !== false);
$sessionDir = dirname(__FILE__) . '/../data/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $parsed = array();
        parse_str($raw, $parsed);
        if ($parsed) $_POST = $parsed;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_path', '/');
    ini_set('session.save_path', $sessionDir);
    if ($preview || $https) {
        ini_set('session.cookie_secure', $https ? '1' : '0');
        ini_set('session.cookie_samesite', 'None');
    }
    $sid = '';
    $name = session_name();
    if (!empty($_COOKIE[$name])) {
        $sid = $_COOKIE[$name];
    } elseif (!empty($_POST['joma_sid'])) {
        $sid = $_POST['joma_sid'];
    } elseif (!empty($_GET['joma_sid'])) {
        $sid = $_GET['joma_sid'];
    }
    if ($sid !== '' && preg_match('/^[A-Za-z0-9,-]{16,128}$/', $sid)) {
        session_id($sid);
    }
    session_start();
    if ($preview || $https) {
        $cookie = $name . '=' . session_id() . '; Path=/; HttpOnly; SameSite=None';
        if ($https) $cookie .= '; Secure; Partitioned';
        header('Set-Cookie: ' . $cookie, false);
    }
}
$configFile = dirname(__FILE__) . '/../config/config.php';
if (file_exists($configFile)) {
    require $configFile;
} else {
    $JOMA_CONFIG = array('storage' => 'file', 'base_url' => '/joma');
}
$GLOBALS['JOMA_CONFIG'] = $JOMA_CONFIG;
require dirname(__FILE__) . '/jalali.php';
require dirname(__FILE__) . '/helpers.php';
$_joma_reg_gate = dirname(__FILE__) . '/registration_gate.php';
if (is_file($_joma_reg_gate)) {
    require $_joma_reg_gate;
}
unset($_joma_reg_gate);
$GLOBALS['JOMA_BASE'] = joma_compute_base();
require dirname(__FILE__) . '/store.php';
require dirname(__FILE__) . '/../functions/joma.php';
// (B1 — مرحلهٔ ۲) ماژول بازیابی رمز با کد یک‌بارمصرف.
// اگر فایل نبود، هیچ رفتاری عوض نمی‌شود (fail-open) — مسیر ورود سالم می‌ماند.
$_joma_recovery = dirname(__FILE__) . '/../functions/recovery.php';
if (is_file($_joma_recovery)) {
    require_once $_joma_recovery;
}
unset($_joma_recovery);
// (B5) موتور «جوجهٔ من» — اگر فایل نبود، هیچ رفتاری عوض نمی‌شود (fail-open).
$_joma_jooje = dirname(__FILE__) . '/../functions/jooje.php';
if (is_file($_joma_jooje)) {
    require_once $_joma_jooje;
}
unset($_joma_jooje);
// (B6/B7) آب (پیش‌نویس/قطعی) و موتور بینش — اگر فایل‌ها نبودند، هیچ رفتاری عوض نمی‌شود.
$_joma_water = dirname(__FILE__) . '/../functions/water.php';
if (is_file($_joma_water)) require_once $_joma_water;
unset($_joma_water);
$_joma_insight = dirname(__FILE__) . '/../functions/insight.php';
if (is_file($_joma_insight)) require_once $_joma_insight;
unset($_joma_insight);
// (B8) کپی برنامه به ماه بعد — تابع محض + نوشتن در دورهٔ مقصد.
$_joma_plancopy = dirname(__FILE__) . '/../functions/plancopy.php';
if (is_file($_joma_plancopy)) require_once $_joma_plancopy;
unset($_joma_plancopy);
// (B6) شکل لیوان آب — فقط تابع نمایشی؛ اگر نبود، صفحه بدون لیوان رندر می‌شود.
$_joma_glass = dirname(__FILE__) . '/v2_glass.php';
if (is_file($_joma_glass)) require_once $_joma_glass;
unset($_joma_glass);
require dirname(__FILE__) . '/layout.php';
