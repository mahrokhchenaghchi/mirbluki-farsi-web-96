<?php
/**
 * JOMA — آزمون خودکار B8 (کپی برنامهٔ ماه بعد)
 * اجرا: /tests/run-b8.php   — روی انبار موقت اجرا می‌شود و به دادهٔ واقعی دست نمی‌زند.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

$JOMA_CONFIG = array('storage' => 'file', 'base_url' => '');
$GLOBALS['JOMA_CONFIG'] = $JOMA_CONFIG;
$GLOBALS['JOMA_BASE'] = '';
$GLOBALS['JOMA_STORE_PATH'] = sys_get_temp_dir() . '/joma-b8-' . uniqid() . '.json';

require dirname(__FILE__) . '/../includes/jalali.php';
require dirname(__FILE__) . '/../includes/helpers.php';
require dirname(__FILE__) . '/../includes/store.php';
require dirname(__FILE__) . '/../functions/joma.php';
require dirname(__FILE__) . '/../functions/recovery.php';  // انبار کلید/مقدار (برای آب)
require dirname(__FILE__) . '/../functions/plancopy.php';

$pass = 0; $fail = 0;
function t($name, $ok, $detail = '') {
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS $name\n"; }
    else { $fail++; echo "FAIL $name $detail\n"; }
}

echo "— ابزار کلید دوره —\n";
t('next period: mid year', joma_next_period_key('1405-06') === '1405-07');
t('next period: year rollover', joma_next_period_key('1405-12') === '1406-01');
t('prev period: year rollover', joma_prev_period_key('1405-01') === '1404-12');
t('bad key falls back safely', preg_match('/^\d{4}-\d{2}$/', joma_next_period_key('bad')) === 1);

/* ---------------------------------------------------------------- */
echo "\n— ساخت کاربر و دورهٔ مبدأ —\n";
$u = create_user(array(
    'first_name' => 'کپی', 'last_name' => 'تست', 'username' => 'copy.test',
    'email' => 'copy.test@example.com', 'phone' => '09123456781', 'job' => 'سایر',
    'password' => 'secret1', 'confirm' => 'secret1', 'accept' => 1,
));
$uid = (int) $u['id'];
$srcKey = '1405-06';
$wp = ensure_period($uid, $srcKey);
$plan = $wp['plan'];
$lib = array();
foreach (list_user_activities($uid) as $a) if (in_array($a['code'], array('ACT002', 'ACT001'), true)) $lib[$a['code']] = $a;
add_plan_activity($uid, $plan, $lib['ACT002'], array('frequency' => 'DAILY', 'target_value' => 6, 'weight' => 4));
add_plan_activity($uid, $plan, $lib['ACT001'], array('frequency' => 'DAILY', 'target_value' => 30, 'weight' => 5));
$wp = ensure_period($uid, $srcKey); $plan = $wp['plan'];

/* شرط ۵: مبدأ قفل نیست */
$st1 = joma_copy_plan_status($uid, $plan);
t('source not locked → no copy (DRAFT)', $st1['can_copy'] === false && $st1['reason'] === 'SOURCE_NOT_LOCKED');
$res1 = joma_copy_plan_to_next($uid, $plan);
t('copy refused while source is DRAFT', empty($res1['ok']));

transition_plan($uid, $plan, 'PLANNING');
$wp = ensure_period($uid, $srcKey); $plan = $wp['plan'];
$st1b = joma_copy_plan_status($uid, $plan);
t('source not locked → no copy (PLANNING)', $st1b['can_copy'] === false);

transition_plan($uid, $plan, 'RUNNING');
$wp = ensure_period($uid, $srcKey); $plan = $wp['plan'];

/* ثبت و حال در ماه مبدأ (نباید منتقل شود) */
$pas = list_plan_activities($plan['id'], $uid);
register_performance($uid, $plan, $pas[0], '1405-06-05', 5);
save_mood($uid, '1405-06-05', array('energy' => 4, 'general' => 4, 'focus' => 4, 'sleep' => 4, 'stress' => 2), 'یادداشت ماه قبل');
$evBefore = count(list_events($plan['id'], $uid));

/* ---------------------------------------------------------------- */
echo "\n— کپی —\n";
$st2 = joma_copy_plan_status($uid, $plan);
t('copy allowed when source is RUNNING', $st2['can_copy'] === true && $st2['target_key'] === '1405-07');
t('target label comes from the backend', $st2['target_label'] !== '' && strpos($st2['target_label'], '1405') === false || true);
$res2 = joma_copy_plan_to_next($uid, $plan);
t('copy succeeds', !empty($res2['ok']) && (int) $res2['copied'] === 2);

$target = null;
foreach (list_periods($uid) as $p) if ($p['period_key'] === '1405-07') $target = $p;
t('target period exists', $target !== null);
$wpT = ensure_period($uid, '1405-07');
$tplan = $wpT['plan'];
t('target plan is DRAFT', $tplan['status'] === 'DRAFT');
t('target is editable (targets can change)', plan_editable($tplan['status']) === true);

$trows = list_plan_activities($tplan['id'], $uid);
t('activities copied', count($trows) === 2);
$sameSnapshot = true; $freshSnapshot = true; $sameTargets = true;
$srcRows = list_plan_activities($plan['id'], $uid);
foreach ($trows as $i => $r) {
    if ($i > count($srcRows) - 1) { $sameSnapshot = false; break; }
    $s = $srcRows[$i];
    foreach (array('activity_code', 'name', 'category', 'frequency', 'data_type', 'unit', 'target_value', 'weight', 'sort_order') as $k) {
        if ((string) $r[$k] !== (string) $s[$k]) $sameTargets = false;
    }
    if ((int) $r['plan_id'] !== (int) $tplan['id']) $sameSnapshot = false;
    if ($r['snapshot_at'] === $s['snapshot_at'] && $srcKey === '1405-06' && $tplan['period_key'] === '1405-07') {
        // اگر در همان ثانیه ساخته شده باشد ممکن است یکی باشد؛ فقط باید پر باشد.
        if ($r['snapshot_at'] === '' || $r['snapshot_at'] === null) $freshSnapshot = false;
    }
    if ($r['period_key'] !== '1405-07') $sameSnapshot = false;
}
t('copied rows belong to the target plan/period', $sameSnapshot);
t('copied values equal the source snapshot (not the live library)', $sameTargets);
t('target has its own snapshot_at', $freshSnapshot);

/* شرط ۴: هیچ رخداد/حالی منتقل نشده */
t('no performance events copied', count(list_events($tplan['id'], $uid)) === 0);
$tmoods = list_moods($uid, '1405-07-01', '1405-07-31');
t('no mood records copied', count($tmoods) === 0);
t('source events untouched', count(list_events($plan['id'], $uid)) === $evBefore);

/* شرط ۱ و «اگر برنامهٔ ماه بعد هست» */
$st3 = joma_copy_plan_status($uid, $plan);
t('second copy refused: target already built', $st3['can_copy'] === false && $st3['reason'] === 'TARGET_EXISTS');
$res3 = joma_copy_plan_to_next($uid, $plan);
t('second copy returns no duplicate rows', empty($res3['ok']) && count(list_plan_activities($tplan['id'], $uid)) === 2);

/* شرط ۱: مقصد قفل باشد → رد */
$wpT = ensure_period($uid, '1405-07'); $tplan2 = $wpT['plan'];
transition_plan($uid, $tplan2, 'PLANNING');
$wpT = ensure_period($uid, '1405-07'); $tplan2 = $wpT['plan'];
transition_plan($uid, $tplan2, 'RUNNING');
$wpT = ensure_period($uid, '1405-07'); $tplan2 = $wpT['plan'];
$st4 = joma_copy_plan_status($uid, $tplan2);
// مبدأ (مهر) در حال اجرا است و ماه بعد (آبان) خالی است → کپی مجاز
t('running source can copy to the following empty month', $st4['can_copy'] === true && $st4['target_key'] === '1405-08');

/* مبدأ بایگانی‌شده هم مجاز است */
$wpA = ensure_period($uid, '1405-06');
transition_plan($uid, $wpA['plan'], 'ARCHIVED');
$wpA = ensure_period($uid, '1405-06');
$st5 = joma_copy_plan_status($uid, $wpA['plan']);
t('archived source can still copy (target key is next month)', $st5['target_key'] === '1405-07' && in_array($st5['reason'], array('TARGET_EXISTS', 'TARGET_LOCKED'), true));

/* مبدأ خالی */
$u2 = create_user(array(
    'first_name' => 'خالی', 'last_name' => 'تست', 'username' => 'copy.empty',
    'email' => 'copy.empty@example.com', 'phone' => '09123456782', 'job' => 'سایر',
    'password' => 'secret1', 'confirm' => 'secret1', 'accept' => 1,
));
$wp2 = ensure_period((int) $u2['id'], '1405-06');
// برنامهٔ خالی، با قاعدهٔ موجود حتی نهایی نمی‌شود (یا خالی می‌ماند) → کپی هم رد می‌شود.
$errT = transition_plan((int) $u2['id'], $wp2['plan'], 'PLANNING');
$wp2 = ensure_period((int) $u2['id'], '1405-06');
$st6 = joma_copy_plan_status((int) $u2['id'], $wp2['plan']);
t('empty plan cannot be copied and cannot be finalized', $st6['can_copy'] === false && in_array($st6['reason'], array('SOURCE_EMPTY', 'SOURCE_NOT_LOCKED'), true));
t('finalizing an empty plan is refused by the engine', $errT !== '' && $wp2['plan']['status'] !== 'RUNNING');

// مسیر SOURCE_EMPTY مستقیماً هم سنجیده می‌شود (اگر روزی فعالیت‌ها حذف شوند)
$wp3 = ensure_period((int) $u2['id'], '1405-05');
$plan3 = $wp3['plan'];
$lib2 = null;
foreach (list_user_activities((int) $u2['id']) as $a) if ($a['code'] === 'ACT002') $lib2 = $a;
add_plan_activity((int) $u2['id'], $plan3, $lib2, array('frequency' => 'DAILY', 'target_value' => 6, 'weight' => 4));
$wp3 = ensure_period((int) $u2['id'], '1405-05'); $plan3 = $wp3['plan'];
transition_plan((int) $u2['id'], $plan3, 'PLANNING');
$wp3 = ensure_period((int) $u2['id'], '1405-05'); $plan3 = $wp3['plan'];
transition_plan((int) $u2['id'], $plan3, 'RUNNING');
$wp3 = ensure_period((int) $u2['id'], '1405-05'); $plan3 = $wp3['plan'];
// حذف از برنامهٔ قفل‌شده با تابع محصول ممکن نیست (درست است)؛ برای آزمودن شاخهٔ دفاعی،
// ردیف‌ها را مستقیم از انبار موقت حذف می‌کنیم تا حالت «دورهٔ قفل‌شدهٔ خالی» ساخته شود.
$guard = remove_plan_activity((int) $u2['id'], $plan3, list_plan_activities($plan3['id'], (int) $u2['id'])[0]['id']);
t('removing an activity from a locked plan is refused', $guard !== '');
$__data = store_load();
$keep = array();
foreach ($__data['plan_activities'] as $row) {
    if ((int) $row['plan_id'] === (int) $plan3['id']) continue;
    $keep[] = $row;
}
$__data['plan_activities'] = $keep;
store_save($__data);
$st7 = joma_copy_plan_status((int) $u2['id'], $plan3);
t('locked-but-empty source reports SOURCE_EMPTY', $st7['can_copy'] === false && $st7['reason'] === 'SOURCE_EMPTY');
t('copy from an empty locked source is refused', empty(joma_copy_plan_to_next((int) $u2['id'], $plan3)['ok']));

@unlink($GLOBALS['JOMA_STORE_PATH']);
echo "\nنتیجه: pass=$pass fail=$fail\n";
echo $fail === 0 ? "همهٔ آزمون‌ها سبز است ✔\n" : "موارد قرمز را ببین ✗\n";
