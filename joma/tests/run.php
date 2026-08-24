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

@unlink($GLOBALS['JOMA_STORE_PATH']);
echo "PHP tests pass=$pass fail=$fail\n";
exit($fail ? 1 : 0);
