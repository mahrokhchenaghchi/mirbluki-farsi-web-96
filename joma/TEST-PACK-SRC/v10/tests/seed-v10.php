<?php
/**
 * JOMA — دادهٔ آزمون بستهٔ v10 (فقط کپی تست · حالت فایلی)
 * -------------------------------------------------------------------------
 * می‌سازد (طبق درخواست مالک):
 *   ostad            — حساب چندنقشه (کاربری + مشاور)
 *   s0 · s4 · s9     — سه مراجع با ۰ / ۴ / ۹ روز بی‌ثبت  (سبز / طلایی / کورال)
 *   snew             — مراجع تازه‌وارد (تخم) — کمتر از ۷ روز از شروع همراهی
 *   soff             — مراجع با نشانگر خاموش
 *   s1               — کاربر تک‌نقشه
 *   کد دعوت            — یک کد فعال ساخته می‌شود تا مسیر «کد دعوت دارم» آزمون شود
 * بعد از اجرا، این فایل را از هاست پاک کن.
 */
require dirname(__FILE__) . '/../includes/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');
if (store_mode() !== 'file') {
    echo '<meta charset="utf-8">فقط حالت فایلی.';
    return;
}
if (function_exists('joma_companion_module_ready')) joma_companion_module_ready();
$invcode = dirname(__FILE__) . '/../functions/companion_invitecode.php';
if (is_file($invcode)) require_once $invcode;
$PASS = 'JomaTest1405!';

function v10_out($s) { echo '<p style="margin:4px 0;font:14px sans-serif">' . $s . '</p>'; }

function v10_user($username, $first, $last, $pass, $days_ago = 0) {
    $u = user_by_username($username);
    if (!$u) {
        create_user(array(
            'first_name' => $first, 'last_name' => $last, 'username' => $username,
            'email' => $username . '@example.test', 'phone' => '09120000000', 'job' => 'کارمند',
            'password' => $pass, 'confirm' => $pass, 'accept' => 1,
        ));
        $u = user_by_username($username);
    }
    if ($u && $days_ago > 0) v10_backdate_user((int) $u['id'], $days_ago);
    return $u;
}

function v10_backdate_user($uid, $days) {
    $data = store_load();
    foreach ($data['users'] as $i => $row) {
        if ((int) $row['id'] !== (int) $uid) continue;
        $data['users'][$i]['created_at'] = date('Y-m-d H:i:s', time() - ($days * 86400));
    }
    store_save($data);
}

function v10_backdate_link($link_id, $days) {
    $data = hammasir_store_load();
    if ($data === null) return;
    foreach ($data['links'] as $i => $row) {
        if ((int) $row['id'] !== (int) $link_id) continue;
        $data['links'][$i]['accepted_at'] = date('Y-m-d H:i:s', time() - ($days * 86400));
    }
    hammasir_store_save_atomic($data);
}

function v10_days_ago_jalali($days) {
    $b = explode('-', jalali_today());
    $g = jalali_to_gregorian((int) $b[0], (int) $b[1], (int) $b[2]);
    $ts = strtotime($g[0] . '-' . jalali_pad($g[1]) . '-' . jalali_pad($g[2]) . ' 12:00:00') - ($days * 86400);
    $j = gregorian_to_jalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
}

/** برنامهٔ یک کاربر با یک فعالیت روزانه + شروع اجرا */
function v10_plan($uid) {
    $key = current_period_key();
    $wp = ensure_period((int) $uid, $key);
    $plan = $wp['plan'];
    $lib = array();
    foreach (list_user_activities((int) $uid) as $a) $lib[$a['code']] = $a;
    foreach (array('ACT001' => 30, 'ACT002' => 6) as $code => $target) {
        if (!isset($lib[$code])) continue;
        add_plan_activity((int) $uid, $plan, $lib[$code], array(
            'frequency' => $lib[$code]['frequency'], 'target_value' => $target, 'weight' => $lib[$code]['weight'],
        ));
    }
    $wp = ensure_period((int) $uid, $key);
    $plan = $wp['plan'];
    if (isset($plan['status']) && $plan['status'] === 'DRAFT') transition_plan((int) $uid, $plan, 'PLANNING');
    $wp = ensure_period((int) $uid, $key);
    $plan = $wp['plan'];
    if (isset($plan['status']) && $plan['status'] === 'PLANNING') transition_plan((int) $uid, $plan, 'RUNNING');
    $wp = ensure_period((int) $uid, $key);
    return $wp['plan'];
}

/** یک ثبت عملکرد در «چند روز قبل» */
function v10_record($uid, $plan, $days_ago) {
    foreach (list_plan_activities($plan['id'], (int) $uid) as $pa) {
        $err = register_performance((int) $uid, $plan, $pa, v10_days_ago_jalali($days_ago), 24);
        return ($err === '') ? true : $err;
    }
    return 'no_activity';
}

function v10_mood_today($uid) {
    if (get_mood((int) $uid, jalali_today())) return true;
    return save_mood((int) $uid, jalali_today(), array(
        'energy' => 3, 'general' => 3, 'focus' => 3, 'sleep' => 3, 'stress' => 2,
    ), '');
}

/** ساخت ارتباط ACTIVE بین مراجع و مشاور با تعداد روزِ مشخص */
function v10_link($client_uid, $provider_uid, $share, $active_days_ago, $last_record_days_ago) {
    if (function_exists('joma_status_share_set')) joma_status_share_set((int) $client_uid, $share ? 1 : 0);
    $plan = v10_plan((int) $client_uid);
    if ($last_record_days_ago !== null) v10_record((int) $client_uid, $plan, (int) $last_record_days_ago);
    $link = hammasir_link_open_by_client((int) $client_uid);
    if (!$link) {
        hammasir_link_request((int) $client_uid, (int) $provider_uid, array('VIEW_PROGRESS' => 1, 'VIEW_ACTIVITY_DETAILS' => 0, 'VIEW_MOOD' => 0));
        $link = hammasir_link_open_by_client((int) $client_uid);
    }
    if ($link && $link['status'] === 'PENDING') {
        hammasir_link_respond((int) $provider_uid, (int) $link['id'], 'ACTIVE');
        v10_backdate_link((int) $link['id'], (int) $active_days_ago);
    }
    return $link;
}

v10_out('<b>۱) مشاور و کاربران</b>');
$ostad = v10_user('ostad', 'استاد', 'آزمون', $PASS, 60);
$s1 = v10_user('s1', 'تک‌نقشه', 'آزمون', $PASS, 10);
v10_plan((int) $s1['id']);
v10_mood_today((int) $s1['id']);
v10_out('ostad=' . (int) $ostad['id'] . ' · s1=' . (int) $s1['id']);

/* مدیر (برای جاروب سه‌نقشه) */
$modir = v10_user('modir', 'مدیر', 'آزمون', $PASS, 30);
if ($modir) {
    $data = store_load();
    foreach ($data['users'] as $i => $row) {
        if ((int) $row['id'] === (int) $modir['id']) $data['users'][$i]['role_key'] = 'admin';
    }
    store_save($data);
}

v10_out('<b>۲) مشاور شدن ostad</b>');
$r1 = hammasir_provider_register((int) $ostad['id'], 'استاد آزمون');
$r2 = hammasir_provider_set_status((int) $ostad['id'], 'ACTIVE');
v10_out('ثبت: ' . e((string) $r1) . ' · فعال‌سازی: ' . e((string) $r2));

v10_out('<b>۳) مراجعان ۰ / ۴ / ۹ روز</b>');
$s0 = v10_user('s0', 'سبز', 'آزمون', $PASS, 40);
$s4 = v10_user('s4', 'طلایی', 'آزمون', $PASS, 40);
$s9 = v10_user('s9', 'کورال', 'آزمون', $PASS, 40);
$snew = v10_user('snew', 'تازه‌وارد', 'آزمون', $PASS, 40);
$soff = v10_user('soff', 'خاموش', 'آزمون', $PASS, 40);

v10_link((int) $s0['id'], (int) $ostad['id'], true, 20, 0);
v10_link((int) $s4['id'], (int) $ostad['id'], true, 20, 4);
v10_link((int) $s9['id'], (int) $ostad['id'], true, 20, 9);
v10_link((int) $snew['id'], (int) $ostad['id'], true, 2, 0);
v10_link((int) $soff['id'], (int) $ostad['id'], false, 20, 1);
v10_out('ساخته شد: s0 (۰ روز) · s4 (۴ روز) · s9 (۹ روز) · snew (تازه‌وارد) · soff (نشانگر خاموش)');

v10_out('<b>۴) یک کد دعوت فعال</b>');
$code = function_exists('joma_invitecode_create') ? joma_invitecode_create((int) $ostad['id'], (int) $ostad['id']) : 'error';
v10_out('کد: <b dir="ltr">' . e((string) $code) . '</b>');

v10_out('<b>۵) نتیجه</b>');
v10_out('رمز همهٔ حساب‌ها: ' . e($PASS));
v10_out('ورود چندنقشه: ostad · تک‌نقشه: s1 · مراجعان: s0 · s4 · s9 · snew · soff');
