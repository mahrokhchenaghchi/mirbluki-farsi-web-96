<?php
/**
 * JOMA — دادهٔ آزمون بستهٔ C1/C2/C3 و کارت دعوت (فقط کپی تست — حالت فایلی)
 * -------------------------------------------------------------------------
 * این فایل هیچ‌کاری با سایت اصلی ندارد و فقط روی همین کپی تست اجرا می‌شود.
 * می‌سازد:
 *   tana   — کاربر تک‌نقشه با یک فعالیت روزانه و یک ثبت دیروز → کارت دعوت باید «یک‌بار» بیاید
 *   tala   — کاربری که امروز ثبت‌نام کرده → کارت دعوت نباید بیاید (روز اول)
 *   hamkar — کاربر + مشاور (چند‌نقشه) با پنج مراجع در وضعیت‌های مختلف + یک درخواست در انتظار
 * بعد از اجرا، این فایل را از هاست پاک کن.
 */
require dirname(__FILE__) . '/../includes/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');
if (store_mode() !== 'file') {
    echo '<meta charset="utf-8">فقط حالت فایلی.';
    return;
}
$PASS = 'JomaTest1405!';
// ماژول هم‌مسیر را همان اول لود می‌کنیم تا رابط‌هایش در دسترس باشند
if (function_exists('joma_companion_module_ready')) joma_companion_module_ready();
function s_out($s) { echo '<p style="margin:4px 0;font:14px sans-serif">' . $s . '</p>'; }

/** کاربر بساز یا پیدا کن */
function s_user($username, $first, $last, $pass, $days_ago = 0) {
    $u = user_by_username($username);
    if (!$u) {
        $r = create_user(array(
            'first_name' => $first, 'last_name' => $last, 'username' => $username,
            'email' => $username . '@example.test', 'phone' => '09120000000', 'job' => 'کارمند',
            'password' => $pass, 'confirm' => $pass, 'accept' => 1,
        ));
        $u = user_by_username($username);
    }
    if (!$u) return null;
    if ($days_ago > 0) s_backdate_user((int) $u['id'], $days_ago);
    return $u;
}

/** تاریخ ثبت‌نام را به عقب می‌برد (فقط برای آزمون «روز اول نبودن») */
function s_backdate_user($uid, $days) {
    $data = store_load();
    foreach ($data['users'] as $i => $row) {
        if ((int) $row['id'] !== (int) $uid) continue;
        $data['users'][$i]['created_at'] = date('Y-m-d H:i:s', time() - ($days * 86400));
    }
    store_save($data);
}

/** تاریخ پذیرش لینک را به عقب می‌برد (فقط برای آزمون حالت‌های ۳ تا ۵ روز) */
function s_backdate_link_accept($link_id, $days) {
    $data = hammasir_store_load();
    if ($data === null) return;
    foreach ($data['links'] as $i => $row) {
        if ((int) $row['id'] !== (int) $link_id) continue;
        $data['links'][$i]['accepted_at'] = date('Y-m-d H:i:s', time() - ($days * 86400));
    }
    hammasir_store_save_atomic($data);
}

/** برنامهٔ کاربر: چند فعالیت + شروع اجرا */
function s_plan($uid, $codes, $targets) {
    $key = current_period_key();
    $wp = ensure_period((int) $uid, $key);
    $plan = $wp['plan'];
    $lib = array();
    foreach (list_user_activities((int) $uid) as $a) $lib[$a['code']] = $a;
    foreach ($codes as $code) {
        if (!isset($lib[$code])) continue;
        add_plan_activity((int) $uid, $plan, $lib[$code], array(
            'frequency' => $lib[$code]['frequency'],
            'target_value' => isset($targets[$code]) ? $targets[$code] : $lib[$code]['target_value'],
            'weight' => $lib[$code]['weight'],
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

/** یک ثبت عملکرد در تاریخ شمسی مشخص (اگر آن روز قبلاً ثبت شده، رد می‌شود) */
function s_record($uid, $plan, $code, $jalali_date, $value) {
    foreach (list_plan_activities($plan['id'], (int) $uid) as $pa) {
        if ($pa['activity_code'] !== $code) continue;
        $err = register_performance((int) $uid, $plan, $pa, $jalali_date, $value);
        return ($err === '') ? true : $err;
    }
    return 'no_activity';
}

/** ثبت حال امروز (گیت B2 صفحهٔ امروز) */
function s_mood_today($uid) {
    if (get_mood((int) $uid, jalali_today())) return true;
    return save_mood((int) $uid, jalali_today(), array(
        'energy' => 3, 'general' => 3, 'focus' => 3, 'sleep' => 3, 'stress' => 2,
    ), '');
}

/** چند روز قبل، به تاریخ شمسی */
function s_days_ago_jalali($days) {
    $b = explode('-', jalali_today());
    $g = jalali_to_gregorian((int) $b[0], (int) $b[1], (int) $b[2]);
    $ts = strtotime($g[0] . '-' . jalali_pad($g[1]) . '-' . jalali_pad($g[2]) . ' 12:00:00') - ($days * 86400);
    $j = gregorian_to_jalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
}

s_out('<b>۱) کاربران</b>');
$tana = s_user('tana', 'تنها', 'آزمون', $PASS, 3);
$tala = s_user('tala', 'تازه', 'آزمون', $PASS, 0);
$hamkar = s_user('hamkar', 'همکار', 'آزمون', $PASS, 30);
s_out('tana=' . (int) $tana['id'] . ' · tala=' . (int) $tala['id'] . ' · hamkar=' . (int) $hamkar['id']);

s_out('<b>۲) برنامهٔ tana (تک‌نقشه، فقط روزانه)</b>');
$plan_tana = s_plan((int) $tana['id'], array('ACT001'), array('ACT001' => 30));
$r = s_record((int) $tana['id'], $plan_tana, 'ACT001', s_days_ago_jalali(1), 25);
s_out('وضعیت برنامه: ' . e($plan_tana['status']) . ' · ثبت دیروز: ' . e(is_bool($r) ? 'انجام شد' : (string) $r));

// ثبت حال امروز (گیت B2 روی صفحهٔ امروز) — لازم برای دیدن «کارهای امروز»
s_mood_today((int) $tana['id']);
s_out('حال امروز tana ثبت شد.');

s_out('<b>۳) برنامهٔ tala (امروز ثبت‌نام کرده)</b>');
$plan_tala = s_plan((int) $tala['id'], array('ACT001'), array('ACT001' => 30));
s_out('وضعیت برنامه: ' . e($plan_tala['status']));

s_out('<b>۳-۲) کاربر tab — آب روزانه + یک فعالیت هفتگی + یک فعالیت ماهانه</b>');
$tab = s_user('tab', 'تب', 'آزمون', $PASS, 5);
$plan_tab = s_plan((int) $tab['id'], array('ACT002', 'ACT015', 'ACT030'), array('ACT002' => 6, 'ACT015' => 3, 'ACT030' => 1));
s_out('وضعیت برنامه: ' . e($plan_tab['status']) . ' (آب: ' . e((string) s_record((int) $tab['id'], $plan_tab, 'ACT002', s_days_ago_jalali(1), 3)) . ')');
s_mood_today((int) $tab['id']);

s_out('<b>۴) همکار → مشاور</b>');
if (function_exists('hammasir_provider_register')) {
    $r1 = hammasir_provider_register((int) $hamkar['id'], 'همکار آزمون');
    $r2 = hammasir_provider_set_status((int) $hamkar['id'], 'ACTIVE');
    s_out('ثبت مشاور: ' . e((string) $r1) . ' · فعال‌سازی: ' . e((string) $r2));
}

s_out('<b>۵) مراجعان همکار در پنج وضعیت</b>');
$clients = array(
    //  نام کاربری   نام        روزهای قبل    آخرین ثبت چند روز قبل   اشتراک وضعیت   وضعیت لینک
    'cneedy' => array('نیازمند', 40, 8, 1, 'ACTIVE'),
    'ccalm' => array('آرام', 40, 4, 1, 'ACTIVE'),
    'cgood' => array('درمسیر', 40, 0, 1, 'ACTIVE'),
    'coff' => array('خاموش', 40, 1, 0, 'ACTIVE'),
    'cnew' => array('تازه‌وارد', 40, 0, 1, 'NEW'),
    'cpend' => array('درانتظار', 40, 1, 0, 'PENDING'),
);
foreach ($clients as $un => $cfg) {
    $cu = s_user($un, $cfg[0], 'آزمون', $PASS, $cfg[1]);
    if (!$cu) { s_out($un . ': ساخته نشد'); continue; }
    $plan = s_plan((int) $cu['id'], array('ACT001'), array('ACT001' => 30));
    if ($cfg[2] >= 0) s_record((int) $cu['id'], $plan, 'ACT001', s_days_ago_jalali($cfg[2]), 24);
    if (function_exists('joma_status_share_set')) joma_status_share_set((int) $cu['id'], $cfg[3] ? 1 : 0);
    $link = hammasir_link_open_by_client((int) $cu['id']);
    if (!$link) {
        $res = hammasir_link_request((int) $cu['id'], (int) $hamkar['id'], array(
            'VIEW_PROGRESS' => ($un === 'coff') ? 0 : 1,
            'VIEW_ACTIVITY_DETAILS' => 0,
            'VIEW_MOOD' => 0,
        ));
        s_out($un . ': درخواست → ' . e((string) $res));
        $link = hammasir_link_open_by_client((int) $cu['id']);
    }
    if ($link && $cfg[4] === 'ACTIVE' && $link['status'] === 'PENDING') {
        $res = hammasir_link_respond((int) $hamkar['id'], (int) $link['id'], 'ACTIVE');
        s_out($un . ': پذیرش → ' . e((string) $res));
        if ($un !== 'cnew') s_backdate_link_accept((int) $link['id'], 20);
    }
    if ($un === 'cnew' && $link) {
        $res = hammasir_link_respond((int) $hamkar['id'], (int) $link['id'], 'ACTIVE');
        s_out($un . ': پذیرش → ' . e((string) $res));
        s_backdate_link_accept((int) $link['id'], 2);
    }
    if ($un === 'ccalm' && $link) s_backdate_link_accept((int) $link['id'], 15);
    if ($un === 'cgood' && $link) s_backdate_link_accept((int) $link['id'], 15);
    if ($un === 'coff' && $link) s_backdate_link_accept((int) $link['id'], 15);
    if ($un === 'cneedy' && $link) s_backdate_link_accept((int) $link['id'], 15);
}

s_out('<b>۶) نتیجه</b>');
s_out('رمز همهٔ حساب‌ها: ' . e($PASS));
s_out('tana و tala: تک‌نقشه · hamkar: چندنقشه (کاربری + مشاور)');
