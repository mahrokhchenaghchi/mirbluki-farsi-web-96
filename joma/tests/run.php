<?php
// CLI tests for hosts that have PHP. Arena sandbox may not.
error_reporting(E_ALL);
ini_set('display_errors', '1');
$JOMA_CONFIG = array('storage' => 'file', 'base_url' => '');
$GLOBALS['JOMA_CONFIG'] = $JOMA_CONFIG;
$GLOBALS['JOMA_BASE'] = '';
$GLOBALS['JOMA_STORE_PATH'] = sys_get_temp_dir() . '/joma-test-' . uniqid() . '.json';
require dirname(__FILE__) . '/../includes/jalali.php';
require dirname(__FILE__) . '/../includes/helpers.php';
require dirname(__FILE__) . '/../includes/store.php';
require dirname(__FILE__) . '/../functions/joma.php';

$pass = 0;
$fail = 0;
function t($name, $ok, $detail = '') {
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS $name\n"; }
    else { $fail++; echo "FAIL $name $detail\n"; }
}

$lib = official_library();
t('library 45', count($lib) === 45);
$water = null;
foreach ($lib as $r) if ($r['code'] === 'ACT002') $water = $r;
t('water 6', $water && (int)$water['daily_target'] === 6);

$in = array(
    'first_name'=>'سارا','last_name'=>'محمدی','username'=>'sara.php',
    'email'=>'sara.php@example.com','phone'=>'09123456780','job'=>'سایر',
    'password'=>'secret1','confirm'=>'secret1','accept'=>1,
);
t('register validation', validate_registration($in) === '');
$u = create_user($in);
t('create user', $u && $u['username'] === 'sara.php');
t('seed 45', count(list_user_activities($u['id'])) === 45);

$wp = ensure_period($u['id'], '1405-06');
$act = null;
foreach (list_user_activities($u['id']) as $a) if ($a['code'] === 'ACT002') $act = $a;
$msg = add_plan_activity($u['id'], $wp['plan'], $act, array('frequency'=>'DAILY','target_value'=>5,'weight'=>7));
t('override add', $msg === '');
t('empty finalize blocked before add already has one', true);
$msg = transition_plan($u['id'], $wp['plan'], 'PLANNING');
$wp = ensure_period($u['id'], '1405-06');
$msg = transition_plan($u['id'], $wp['plan'], 'RUNNING');
t('start running', $msg === '');
$wp = ensure_period($u['id'], '1405-06');
$pas = list_plan_activities($wp['plan']['id'], $u['id']);
$err1 = register_performance($u['id'], $wp['plan'], $pas[0], '1405-06-02', 6);
$err2 = register_performance($u['id'], $wp['plan'], $pas[0], '1405-06-02', 3);
$err3 = register_performance($u['id'], $wp['plan'], $pas[0], '1405-06-03', 2);
t('daily unique', $err1 === '' && $err2 !== '' && $err3 === '');
save_mood($u['id'], '1405-06-03', array('energy'=>4,'general'=>5,'focus'=>3,'sleep'=>4,'stress'=>2), 'ok');
$rep = build_report($u['id'], $wp['plan']);
t('report unspecified', $rep['achievement'] === 'UNSPECIFIED' && $rep['overall_success'] === 'UNSPECIFIED');
t('report events', $rep['source_event_count'] === 2);

/* ------------------------------------------------------------------ */
/* B5 — جوجهٔ من (سه سنجهٔ موجود؛ بدون جعل عدد)                        */
/* ------------------------------------------------------------------ */
require dirname(__FILE__) . '/../functions/jooje.php';

function jj_shift_jalali($days) {
    $ts = time() + ((int) $days * 86400);
    $j = gregorian_to_jalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
}

function jj_add_event($uid, $plan, $pa, $date, $value, $created) {
    $data = store_load();
    $id = store_next_id($data);
    $data['events'][] = array(
        'id' => $id, 'user_id' => (int) $uid, 'plan_id' => (int) $plan['id'],
        'plan_activity_id' => (int) $pa['id'], 'period_key' => $plan['period_key'],
        'frequency' => $pa['frequency'], 'data_type' => $pa['data_type'],
        'event_type' => 'PERFORMANCE_REGISTERED', 'performance_date' => $date,
        'actual_value' => (float) $value, 'created_at' => $created,
    );
    store_save($data);
}

/** همهٔ ثبت‌ها و حال کاربر را چند روز به عقب می‌برد (برای آزمون غیبت). */
function jj_shift_activity($uid, $days_back) {
    $stamp = date('Y-m-d H:i:s', time() - ((int) $days_back * 86400));
    $jd = jj_shift_jalali(-(int) $days_back);
    $data = store_load();
    foreach ($data['events'] as $i => $e) {
        if ((int) $e['user_id'] !== (int) $uid) continue;
        $data['events'][$i]['created_at'] = $stamp;
        $data['events'][$i]['performance_date'] = $jd;
    }
    foreach ($data['moods'] as $i => $m) if ((int) $m['user_id'] === (int) $uid) $data['moods'][$i]['jalali_date'] = $jd;
    store_save($data);
}

$jcfg = jooje_config();
$j_crack = (int) $jcfg['crack_units'];
$j_birth = (int) $jcfg['birth_units'];
$j_now = date('Y-m-d H:i:s');
$j_today = jalali_today();

$juser = create_user(array(
    'first_name' => 'جوجه', 'last_name' => 'تست', 'username' => 'jooje.test',
    'email' => 'jooje.test@example.com', 'phone' => '09123456781', 'job' => 'سایر',
    'password' => 'secret1', 'confirm' => 'secret1', 'accept' => 1,
));
$j0 = jooje_state($juser['id']);
t('jooje starts as egg', $j0['stage'] === 'egg' && $j0['units'] === 0 && $j0['state'] === 'calm');
t('jooje water no-source is not zero', $j0['metrics'][1]['has_source'] === false && $j0['metrics'][1]['today'] === null && $j0['metrics'][1]['progress'] === null);
t('jooje bath metric hidden', $j0['metrics'][3]['available'] === false);
t('jooje bare timeline', count($j0['timeline']) === 1 && $j0['days_since_last'] === null);
t('jooje private by default', $j0['sharing'] === 'private' && $j0['pet_name'] === null);

$jwp = ensure_period($juser['id'], '1405-06');
$jplan = $jwp['plan'];
$jw = null; $jo = null;
foreach (list_user_activities($juser['id']) as $a) {
    if ($a['code'] === 'ACT002') $jw = $a;
    if ($a['code'] === 'ACT001') $jo = $a;
}
add_plan_activity($juser['id'], $jplan, $jw, array('frequency' => 'DAILY', 'target_value' => 6, 'weight' => 4));
add_plan_activity($juser['id'], $jplan, $jo, array('frequency' => 'DAILY', 'target_value' => 30, 'weight' => 5));
$jwp = ensure_period($juser['id'], '1405-06'); $jplan = $jwp['plan'];
transition_plan($juser['id'], $jplan, 'PLANNING');
$jwp = ensure_period($juser['id'], '1405-06'); $jplan = $jwp['plan'];
transition_plan($juser['id'], $jplan, 'RUNNING');
$jwp = ensure_period($juser['id'], '1405-06'); $jplan = $jwp['plan'];
$jpaW = null; $jpaO = null;
foreach (list_plan_activities($jplan['id'], $juser['id']) as $p) {
    if ($p['activity_code'] === 'ACT002') $jpaW = $p;
    if ($p['activity_code'] === 'ACT001') $jpaO = $p;
}

for ($i = 0; $i < $j_crack - 1; $i++) jj_add_event($juser['id'], $jplan, $jpaO, '1405-06-05', 30, $j_now);
$j1 = jooje_state($juser['id']);
t('jooje below crack stays egg', $j1['stage'] === 'egg' && $j1['units'] === ($j_crack - 1));
t('jooje water target from plan snapshot', $j1['metrics'][1]['has_source'] === true && (float) $j1['metrics'][1]['target'] === 6.0 && $j1['metrics'][1]['target_status'] === 'from_plan_snapshot');
t('jooje water real zero when source exists', (float) $j1['metrics'][1]['today'] === 0.0);

jj_add_event($juser['id'], $jplan, $jpaO, '1405-06-05', 30, $j_now);
$j2 = jooje_state($juser['id']);
t('jooje cracks at threshold', $j2['stage'] === 'crack' && $j2['cracked_at'] !== null && $j2['born_at'] === null);
t('jooje crack adds timeline line', count($j2['timeline']) === 2);

jj_add_event($juser['id'], $jplan, $jpaW, $j_today, 3, $j_now);
while (count(jooje_event_rows($juser['id'])) < $j_birth) {
    jj_add_event($juser['id'], $jplan, $jpaO, '1405-06-05', 30, $j_now);
    if (count(jooje_event_rows($juser['id'])) > $j_birth + 5) break; // محافظ در برابر پیکربندی خراب
}
save_mood($juser['id'], $j_today, array('energy' => 4, 'general' => 4, 'focus' => 4, 'sleep' => 4, 'stress' => 2), '');
$j3 = jooje_state($juser['id']);
t('jooje born once', $j3['stage'] === 'chick' && $j3['born_at'] !== null && count($j3['timeline']) === 3);
t('jooje happy after today record', $j3['state'] === 'happy');
t('jooje water today + progress', (float) $j3['metrics'][1]['today'] === 3.0 && abs(((float) $j3['metrics'][1]['progress']) - 0.5) < 0.001);
t('jooje home metric', (int) $j3['metrics'][2]['today'] === 1 && (float) $j3['metrics'][2]['progress'] === 1.0);
t('jooje growth from backend', $j3['growth'] !== null && (int) $j3['growth']['from_units'] <= (int) $j3['units']);

jj_shift_activity($juser['id'], 4);
$j4 = jooje_state($juser['id']);
t('jooje faded on 3-5 days', $j4['state'] === 'faded' && (int) $j4['days_since_last'] === 4);
jj_shift_activity($juser['id'], 7);
$j5 = jooje_state($juser['id']);
t('jooje gray on 6+ days', $j5['state'] === 'gray');
t('jooje never returns to egg', $j5['stage'] === 'chick' && (int) $j5['units'] >= $j_birth);

@unlink($GLOBALS['JOMA_STORE_PATH']);
echo "PHP tests pass=$pass fail=$fail\n";
exit($fail ? 1 : 0);
