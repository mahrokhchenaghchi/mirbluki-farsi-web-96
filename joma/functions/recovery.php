<?php
/**
 * JOMA — بازیابی رمز با کد یک‌بارمصرف (B1 — مرحلهٔ ۲)
 * =========================================================================
 * تصمیم مالک:
 *   · کد ۶ رقمی، یک‌بارمصرف، اعتبار ۱۵ دقیقه، حداکثر ۵ تلاش.
 *   · کد فقط یک‌بار و فقط به مدیر نشان داده می‌شود؛ هیچ‌جا خام ذخیره نمی‌شود.
 *   · صفحهٔ ساخت کد فقط برای مدیر (مجوز ADMIN_ACCESS).
 *   · پس از تغییر رمز، همهٔ نشست‌های دیگر کاربر باطل می‌شوند.
 *
 * هیچ ستون/جدول تازه‌ای لازم نیست:
 *   - MySQL: از جدول‌های موجود `joma_settings` (کلید/مقدار) استفاده می‌شود.
 *   - فایل‌مود: از بخش `settings` در همان store.json.
 * جدول `joma_otp_codes` دست‌نخورده می‌ماند (این ماژول از آن استفاده نمی‌کند).
 * =========================================================================
 */

/** اعتبار کد به دقیقه. */
function joma_recovery_ttl_minutes() {
    return 15;
}

/** حداکثر تلاش اشتباه. */
function joma_recovery_max_attempts() {
    return 5;
}

/** طول کد. */
function joma_recovery_code_length() {
    return 6;
}

/** فاصلهٔ حداقلی میان دو درخواست کاربر (دقیقه) — فقط ضدسوءاستفاده. */
function joma_recovery_request_cooldown_minutes() {
    return 3;
}

/* ------------------------------------------------------------------ */
/* انبار کلید/مقدار (MySQL: joma_settings · فایل: store.json)          */
/* ------------------------------------------------------------------ */

/** انبار موقت درون‌درخواستی (تا خواندن‌های تکراری هزینه نسازد). */
function &joma_kv_cache() {
    static $cache = array();
    return $cache;
}

function joma_kv_get($key) {
    $cache = &joma_kv_cache();
    if (array_key_exists($key, $cache)) return $cache[$key];
    $val = null;
    if (store_mode() === 'mysql') {
        $row = joma_query_one('SELECT setting_value FROM joma_settings WHERE setting_key = ?', 's', array((string) $key));
        if ($row && isset($row['setting_value'])) $val = $row['setting_value'];
    } else {
        $data = store_load();
        if (isset($data['settings']) && is_array($data['settings']) && array_key_exists($key, $data['settings'])) {
            $val = $data['settings'][$key];
        }
    }
    $cache[$key] = $val;
    return $val;
}

function joma_kv_set($key, $value) {
    $cache = &joma_kv_cache();
    if (store_mode() === 'mysql') {
        joma_exec(
            'INSERT INTO joma_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            'ss',
            array((string) $key, (string) $value)
        );
        $cache[$key] = (string) $value;
        return true;
    }
    $data = store_load();
    if (!isset($data['settings']) || !is_array($data['settings'])) $data['settings'] = array();
    $data['settings'][$key] = (string) $value;
    store_save($data);
    $cache[$key] = (string) $value;
    return true;
}

function joma_kv_forget($key) {
    $cache = &joma_kv_cache();
    unset($cache[$key]);
    if (store_mode() === 'mysql') {
        joma_exec('DELETE FROM joma_settings WHERE setting_key = ?', 's', array((string) $key));
        return true;
    }
    $data = store_load();
    if (isset($data['settings']) && is_array($data['settings'])) {
        unset($data['settings'][$key]);
        store_save($data);
    }
    return true;
}

function joma_kv_get_json($key) {
    $raw = joma_kv_get($key);
    if ($raw === null || $raw === '') return null;
    $val = json_decode($raw, true);
    return is_array($val) ? $val : null;
}

function joma_kv_set_json($key, $value) {
    return joma_kv_set($key, json_encode($value, JSON_UNESCAPED_UNICODE));
}

/* ------------------------------------------------------------------ */
/* کد بازیابی                                                          */
/* ------------------------------------------------------------------ */

function joma_recovery_code_key($user_id) {
    return 'pwreset_code_' . (int) $user_id;
}

/** کد تازهٔ ۶ رقمی — با random_int تا قابل حدس نباشد. */
function joma_recovery_new_code() {
    $max = (int) pow(10, joma_recovery_code_length()) - 1;
    $n = random_int(0, $max);
    return str_pad((string) $n, joma_recovery_code_length(), '0', STR_PAD_LEFT);
}

/**
 * صدور کد برای یک کاربر. خروجی: کد خام **فقط همین یک‌بار** در همین آرایه.
 * در انبار فقط هش کد (password_hash) نگه داشته می‌شود.
 */
function joma_recovery_issue($user_id, $admin = null) {
    $user_id = (int) $user_id;
    if ($user_id <= 0) return array('ok' => false, 'error' => 'کاربر پیدا نشد.');
    $code = joma_recovery_new_code();
    $now = time();
    $ttl = joma_recovery_ttl_minutes() * 60;
    $rec = array(
        'user_id' => $user_id,
        'hash' => password_hash($code, PASSWORD_DEFAULT), // هرگز خام ذخیره نمی‌شود
        'created_at' => date('Y-m-d H:i:s', $now),
        'expires_at' => date('Y-m-d H:i:s', $now + $ttl),
        'expires_ts' => $now + $ttl,
        'attempts' => 0,
        'max_attempts' => joma_recovery_max_attempts(),
        'issued_by' => ($admin && isset($admin['id'])) ? (int) $admin['id'] : null,
        'issued_by_username' => ($admin && isset($admin['username'])) ? (string) $admin['username'] : '',
        'used_at' => null,
    );
    joma_kv_set_json(joma_recovery_code_key($user_id), $rec);
    // بررسی ذخیره‌شدن: اگر جدول تنظیمات در دیتابیس نباشد، همان‌جا خطای روشن بده
    // (بهتر از این‌که کد نشان داده شود و کاربر نتواند استفاده کند).
    $check = joma_recovery_status($user_id);
    if (empty($check['has_code'])) {
        return array('ok' => false, 'error' => 'ذخیره‌سازی کد ممکن نشد. جدول joma_settings در دیتابیس را بررسی کن (فایل SQL اختیاری در همین بسته).');
    }
    joma_recovery_log_add(array(
        'type' => 'ISSUED',
        'user_id' => $user_id,
        'username' => ($admin && isset($admin['username'])) ? (string) $admin['username'] : '',
        'at' => $rec['created_at'],
        'expires_at' => $rec['expires_at'],
    ));
    // کد خام فقط در همین خروجی برمی‌گردد و هیچ‌جا ذخیره نمی‌شود.
    return array(
        'ok' => true,
        'code' => $code,
        'expires_at' => $rec['expires_at'],
        'attempts_left' => (int) $rec['max_attempts'],
        'issued_at' => $rec['created_at'],
    );
}

/** وضعیت کد فعلی کاربر (بدون افشای کد). */
function joma_recovery_status($user_id) {
    $rec = joma_kv_get_json(joma_recovery_code_key($user_id));
    if (!$rec) return array('has_code' => false);
    $now = time();
    $expired = isset($rec['expires_ts']) ? ($now > (int) $rec['expires_ts']) : true;
    return array(
        'has_code' => true,
        'changed_by' => isset($rec['issued_by_username']) ? $rec['issued_by_username'] : '',
        'issued_at' => isset($rec['created_at']) ? $rec['created_at'] : '',
        'expires_at' => isset($rec['expires_at']) ? $rec['expires_at'] : '',
        'expired' => $expired,
        'attempts' => isset($rec['attempts']) ? (int) $rec['attempts'] : 0,
        'attempts_left' => max(0, (int) $rec['max_attempts'] - (int) $rec['attempts']),
        'used' => !empty($rec['used_at']),
    );
}

/** باطل‌کردن کد فعلی کاربر (بدون مصرف). */
function joma_recovery_revoke($user_id) {
    joma_kv_forget(joma_recovery_code_key($user_id));
    joma_recovery_log_add(array('type' => 'REVOKED', 'user_id' => (int) $user_id, 'at' => date('Y-m-d H:i:s')));
    return true;
}

/**
 * بررسی کد. خروجی: آرایه با ok و status.
 * status: OK · WRONG · EXPIRED · TOO_MANY · NO_CODE · USED
 */
function joma_recovery_verify($user_id, $code) {
    $user_id = (int) $user_id;
    $code = preg_replace('/[^0-9]/', '', (string) $code);
    $rec = joma_kv_get_json(joma_recovery_code_key($user_id));
    if (!$rec) return array('ok' => false, 'status' => 'NO_CODE', 'error' => 'کد فعالی برای این حساب صادر نشده است.');
    if (!empty($rec['used_at'])) return array('ok' => false, 'status' => 'USED', 'error' => 'این کد قبلاً استفاده شده است.');
    if (time() > (int) $rec['expires_ts']) return array('ok' => false, 'status' => 'EXPIRED', 'error' => 'اعتبار کد تمام شده است. کد تازه بگیر.');
    if ((int) $rec['attempts'] >= (int) $rec['max_attempts']) {
        return array('ok' => false, 'status' => 'TOO_MANY', 'error' => 'تعداد تلاش‌ها تمام شد. کد تازه بگیر.');
    }
    if ($code === '' || !password_verify($code, $rec['hash'])) {
        $rec['attempts'] = (int) $rec['attempts'] + 1;
        joma_kv_set_json(joma_recovery_code_key($user_id), $rec);
        $left = max(0, (int) $rec['max_attempts'] - (int) $rec['attempts']);
        joma_recovery_log_add(array('type' => 'FAILED', 'user_id' => $user_id, 'at' => date('Y-m-d H:i:s'), 'attempts' => (int) $rec['attempts']));
        if ($left <= 0) {
            joma_recovery_revoke($user_id);
            return array('ok' => false, 'status' => 'TOO_MANY', 'error' => 'تعداد تلاش‌ها تمام شد. کد تازه بگیر.');
        }
        $left_fa = function_exists('fa_num') ? fa_num($left) : (string) $left;
        return array('ok' => false, 'status' => 'WRONG', 'error' => 'کد درست نیست. ' . $left_fa . ' تلاش باقی مانده است.');
    }
    return array('ok' => true, 'status' => 'OK');
}

/** مصرف یک‌بارهٔ کد. */
function joma_recovery_consume($user_id) {
    $rec = joma_kv_get_json(joma_recovery_code_key($user_id));
    if (!$rec) return false;
    $rec['used_at'] = date('Y-m-d H:i:s');
    $rec['hash'] = ''; // هش هم پاک می‌شود تا دیگر قابل استفاده نباشد
    joma_kv_set_json(joma_recovery_code_key($user_id), $rec);
    return true;
}

/* ------------------------------------------------------------------ */
/* تغییر رمز + باطل‌کردن نشست‌های دیگر                                  */
/* ------------------------------------------------------------------ */

function joma_auth_epoch_key($user_id) {
    return 'auth_epoch_' . (int) $user_id;
}

function joma_auth_epoch($user_id) {
    $v = joma_kv_get(joma_auth_epoch_key($user_id));
    return ($v === null) ? 0 : (int) $v;
}

function joma_auth_epoch_bump($user_id) {
    $next = joma_auth_epoch($user_id) + 1;
    joma_kv_set(joma_auth_epoch_key($user_id), (string) $next);
    return $next;
}

/**
 * آیا نشست کاربر از کار افتاده است؟ (پس از تغییر رمز)
 * اگر «شمارندهٔ نشست» صفر باشد، هیچ‌کس باطل نمی‌شود (رفتار قبلی حفظ می‌شود).
 */
function joma_auth_epoch_outdated($session_user) {
    if (!is_array($session_user) || empty($session_user['id'])) return false;
    $current = joma_auth_epoch((int) $session_user['id']);
    if ($current <= 0) return false;
    $seen = isset($session_user['auth_epoch']) ? (int) $session_user['auth_epoch'] : 0;
    return $seen < $current;
}

/** نوشتن رمز تازه (فقط hash) در همان جای همیشگی. */
function joma_recovery_store_password($user_id, $password) {
    $user_id = (int) $user_id;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (store_mode() === 'mysql') {
        joma_exec('UPDATE joma_users SET password_hash = ? WHERE id = ?', 'si', array($hash, $user_id));
        return true;
    }
    $data = store_load();
    foreach ($data['users'] as $i => $row) {
        if ((int) $row['id'] === $user_id) $data['users'][$i]['password_hash'] = $hash;
    }
    store_save($data);
    return true;
}

/**
 * جریان کامل: کد + رمز تازه.
 * خروجی: array('ok'=>bool, 'error'=>string, 'sessions_closed'=>int)
 */
function joma_recovery_complete($user_id, $code, $password, $confirm) {
    $user_id = (int) $user_id;
    if (strlen((string) $password) < 6) return array('ok' => false, 'error' => 'رمز عبور باید حداقل ۶ نویسه باشد.');
    if ($password !== $confirm) return array('ok' => false, 'error' => 'رمز عبور و تکرار آن یکسان نیستند.');
    $v = joma_recovery_verify($user_id, $code);
    if (empty($v['ok'])) return array('ok' => false, 'error' => $v['error'], 'status' => $v['status']);
    joma_recovery_store_password($user_id, $password);
    joma_recovery_consume($user_id);
    $epoch = joma_auth_epoch_bump($user_id); // همهٔ نشست‌های دیگر باطل می‌شوند
    joma_recovery_log_add(array('type' => 'DONE', 'user_id' => $user_id, 'at' => date('Y-m-d H:i:s'), 'epoch' => $epoch));
    return array('ok' => true, 'error' => '', 'sessions_closed' => $epoch);
}

/* ------------------------------------------------------------------ */
/* درخواست کاربر + گزارش مدیر                                          */
/* ------------------------------------------------------------------ */

function joma_recovery_request_key($user_id) {
    return 'pwreset_req_' . (int) $user_id;
}

/** ثبت درخواست کاربر (ضدسوءاستفاده: هر ۳ دقیقه یک‌بار). */
function joma_recovery_request_log($user_id) {
    $user_id = (int) $user_id;
    $last = joma_kv_get_json(joma_recovery_request_key($user_id));
    $now = time();
    if ($last && isset($last['ts']) && ($now - (int) $last['ts']) < (joma_recovery_request_cooldown_minutes() * 60)) {
        return array('ok' => true, 'cooldown' => true);
    }
    joma_kv_set_json(joma_recovery_request_key($user_id), array('ts' => $now, 'at' => date('Y-m-d H:i:s', $now)));
    joma_recovery_log_add(array('type' => 'REQUESTED', 'user_id' => $user_id, 'at' => date('Y-m-d H:i:s', $now)));
    return array('ok' => true, 'cooldown' => false);
}

function joma_recovery_log_key() {
    return 'pwreset_log';
}

/** گزارش کوتاه برای مدیر (بدون کد — کد هیچ‌جا ذخیره نمی‌شود). */
function joma_recovery_log($limit = 40) {
    $rows = joma_kv_get_json(joma_recovery_log_key());
    if (!is_array($rows)) return array();
    if (count($rows) > $limit) $rows = array_slice($rows, 0, $limit);
    return $rows;
}

function joma_recovery_log_add($entry) {
    $rows = joma_kv_get_json(joma_recovery_log_key());
    if (!is_array($rows)) $rows = array();
    array_unshift($rows, $entry);
    if (count($rows) > 60) $rows = array_slice($rows, 0, 60);
    joma_kv_set_json(joma_recovery_log_key(), $rows);
    return true;
}
