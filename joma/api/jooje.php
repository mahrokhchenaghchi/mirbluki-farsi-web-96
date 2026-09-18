<?php
/**
 * JOMA — API «جوجهٔ من» (B5 — مرحلهٔ ۱)
 * خروجی JSON: همان قراردادی که صفحهٔ جوجه و بازنویسی فرانت مصرف می‌کند.
 * فقط برای کاربر واردشده؛ بدون احراز هویت → 401.
 */
require dirname(__FILE__) . '/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$u = current_user();
if (!$u) {
    http_response_code(401);
    echo json_encode(array('ok' => false, 'error' => 'AUTH_REQUIRED'), JSON_UNESCAPED_UNICODE);
    exit;
}

$__jooje_fn = dirname(__FILE__) . '/../functions/jooje.php';
if (is_file($__jooje_fn)) require_once $__jooje_fn;

if (!function_exists('jooje_state')) {
    http_response_code(503);
    echo json_encode(array('ok' => false, 'error' => 'NOT_AVAILABLE'), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('ok' => true, 'data' => jooje_state((int) $u['id'])), JSON_UNESCAPED_UNICODE);
