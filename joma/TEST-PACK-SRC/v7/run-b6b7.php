<?php
/**
 * JOMA — آزمون خودکار B6 (آب پیش‌نویس/قطعی) و B7 (دفترچه و موتور بینش)
 * اجرا: آدرس /tests/run-b6b7.php  (فقط روی کپی تست؛ داده روی همان سرور می‌ماند)
 * این فایل هیچ دادهٔ واقعی را دست نمی‌زند: یک انبار موقت جدا می‌سازد و آخر کار پاک می‌کند.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

$JOMA_CONFIG = array('storage' => 'file', 'base_url' => '');
$GLOBALS['JOMA_CONFIG'] = $JOMA_CONFIG;
$GLOBALS['JOMA_BASE'] = '';
$GLOBALS['JOMA_STORE_PATH'] = sys_get_temp_dir() . '/joma-b6b7-' . uniqid() . '.json';

require dirname(__FILE__) . '/../includes/jalali.php';
require dirname(__FILE__) . '/../includes/helpers.php';
require dirname(__FILE__) . '/../includes/store.php';
require dirname(__FILE__) . '/../functions/joma.php';
require dirname(__FILE__) . '/../functions/recovery.php'; // انبار کلید/مقدار
require dirname(__FILE__) . '/../functions/water.php';
require dirname(__FILE__) . '/../functions/insight.php';

$pass = 0; $fail = 0;
function t($name, $ok, $detail = '') {
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS $name\n"; }
    else { $fail++; echo "FAIL $name $detail\n"; }
}

/* ---------------------------------------------------------------- */
echo "— B6: آب (پیش‌نویس/قطعی) —\n";
/* ---------------------------------------------------------------- */
$u = create_user(array(
    'first_name' => 'آب', 'last_name' => 'تست', 'username' => 'water.test',
    'email' => 'water.test@example.com', 'phone' => '09123456780', 'job' => 'سایر',
    'password' => 'secret1', 'confirm' => 'secret1', 'accept' => 1,
));
$uid = (int) $u['id'];
$today = jalali_today();
$yesterday = joma_insight_next_day($today); // فردا (برای بررسی «آینده» استفاده نمی‌کنیم)
$past = joma_insight_next_day(joma_insight_next_day($today)); // دو روز بعد — فقط برای تست تاریخ

/* قاعدهٔ مالک (v7): رکورد قدیمیِ «امروز» پیش‌نویس است تا قابل ویرایش و ثبت نهایی باشد؛
   رکورد قدیمیِ «گذشته» قطعی می‌ماند (گذشته بازنویسی نمی‌شود). */
$st0 = joma_water_state($uid, $today);
t('water: legacy record of TODAY is editable (owner rule v7)', $st0['status'] === 'draft' && $st0['effective_final'] === false);
t('water: legacy record of today is not counted until finalised', joma_water_is_final($uid, $today) === false);
$pastLegacy = '1405-06-02';
$stPast = joma_water_state($uid, $pastLegacy);
t('water: legacy record of a PAST day stays final', $stPast['status'] === 'none' && $stPast['effective_final'] === true);

joma_water_mark_draft($uid, $today, 3);
t('water: draft is not final', joma_water_is_final($uid, $today) === false);
$st1 = joma_water_state($uid, $today);
t('water: draft keeps the value', $st1['status'] === 'draft' && (float) $st1['value'] === 3.0);

$pastDay = '1405-06-01';
joma_water_mark_draft($uid, $pastDay, 6);
t('water: draft of a past day counts as final', joma_water_is_final($uid, $pastDay) === true);

$fin = joma_water_finalize($uid, $today, false);
t('water: cannot finalize a day without a record', empty($fin['ok']));
joma_water_finalize($uid, $today, true);
t('water: finalize works with a record', joma_water_is_final($uid, $today) === true);

/* برنامه و ثبت واقعی، برای ویرایش پیش‌نویس و سنجهٔ جوجه */
$wp = ensure_period($uid, '1405-06');
$lib = null;
foreach (list_user_activities($uid) as $a) if ($a['code'] === 'ACT002') $lib = $a;
t('water: library has ACT002', $lib !== null);
add_plan_activity($uid, $wp['plan'], $lib, array('frequency' => 'DAILY', 'target_value' => 6, 'weight' => 4));
$wp = ensure_period($uid, '1405-06'); $plan = $wp['plan'];
transition_plan($uid, $plan, 'PLANNING');
$wp = ensure_period($uid, '1405-06'); $plan = $wp['plan'];
transition_plan($uid, $plan, 'RUNNING');
$wp = ensure_period($uid, '1405-06'); $plan = $wp['plan'];
$pa = null;
foreach (list_plan_activities($plan['id'], $uid) as $x) if ($x['activity_code'] === 'ACT002') $pa = $x;
t('water: plan activity exists', $pa !== null);
$d = $today; // ویرایش پیش‌نویس فقط برای امروز مجاز است
$err = register_performance($uid, $plan, $pa, $d, 2);
t('water: first registration of the day works', $err === '');
joma_water_mark_draft($uid, $d, 2);
$err2 = joma_water_update_draft($uid, $plan, $pa, $d, 5);
t('water: today draft can be edited', $err2 === '');
$row = null; $cnt = 0;
foreach (list_events($plan['id'], $uid) as $e) {
    if ((int) $e['plan_activity_id'] === (int) $pa['id'] && $e['performance_date'] === $d) { $row = $e; $cnt++; }
}
t('water: edited value stored once (no duplicate)', $row && (float) $row['actual_value'] === 5.0 && $cnt === 1);
$pastEdit = '1405-06-09';
register_performance($uid, $plan, $pa, $pastEdit, 4);
joma_water_mark_draft($uid, $pastEdit, 4);
$errPast = joma_water_update_draft($uid, $plan, $pa, $pastEdit, 7);
t('water: a past day cannot be re-edited (no rewriting history)', $errPast !== '');
joma_water_save_state($uid, $d, 'final', 5);
$err3 = joma_water_update_draft($uid, $plan, $pa, $d, 9);
t('water: final record cannot be edited', $err3 !== '');
$cnt2 = 0;
foreach (list_events($plan['id'], $uid) as $e) if ((int) $e['plan_activity_id'] === (int) $pa['id'] && $e['performance_date'] === $d) $cnt2++;
t('water: still exactly one record for the day', $cnt2 === 1);

/* سنجهٔ جوجه: پیش‌نویس شمرده نمی‌شود */
require dirname(__FILE__) . '/../functions/jooje.php';
/* ثبت تازهٔ امروز روی فعالیت دیگری از همان برنامه لازم نیست؛ همین فعالیت را با روز تازه‌تر بسنجیم.
   مقدار قطعی امروز را ۵ گذاشتیم؛ حالا همان روز را پیش‌نویس می‌کنیم و می‌سنجیم. */
$finalBefore = joma_water_final_glasses($uid, jooje_event_rows($uid), $d);
joma_water_mark_draft($uid, $d, 5);
$draftGlasses = joma_water_final_glasses($uid, jooje_event_rows($uid), $d);
t('water: draft day is excluded from the chick metric', $finalBefore > 0 && $draftGlasses === 0.0);
joma_water_save_state($uid, $d, 'final', 5);
$finalAfter = joma_water_final_glasses($uid, jooje_event_rows($uid), $d);
t('water: after finalizing it counts again', $finalAfter > 0);

/* ---------------------------------------------------------------- */
echo "\n— B7: یادداشت‌های دفترچه —\n";
/* ---------------------------------------------------------------- */
$bad = joma_journal_note_save($uid, '1405-06-10', 'بدون رکورد حال');
t('journal: note needs a mood record first', empty($bad['ok']));
save_mood($uid, '1405-06-10', array('energy' => 4, 'general' => 4, 'focus' => 3, 'sleep' => 4, 'stress' => 2), '');
$ok1 = joma_journal_note_save($uid, '1405-06-10', 'امروز آرام‌تر بودم.');
t('journal: note saved after the mood record', !empty($ok1['ok']));
save_mood($uid, '1405-06-11', array('energy' => 3, 'general' => 3, 'focus' => 3, 'sleep' => 3, 'stress' => 3), 'یک روز معمولی.');
t('journal: list returns newest first', (function () use ($uid) {
    $n = joma_journal_notes($uid, '', 10, 0);
    return count($n) === 2 && $n[0]['jalali_date'] === '1405-06-11';
})());
t('journal: search works', count(joma_journal_notes($uid, 'آرام', 10, 0)) === 1);
t('journal: count works', joma_journal_notes_count($uid) === 2);
joma_journal_note_delete($uid, '1405-06-10');
t('journal: delete clears the text', count(joma_journal_notes($uid, '', 10, 0)) === 1);
$still = get_mood($uid, '1405-06-10');
t('journal: delete keeps the mood record itself', $still !== null && $still['energy'] === 4);

/* ---------------------------------------------------------------- */
echo "\n— B7: موتور بینش —\n";
/* ---------------------------------------------------------------- */
$acts = list_plan_activities($plan['id'], $uid);
$evs = list_events($plan['id'], $uid);
$moods = list_moods($uid, '1405-06-01', '1405-06-31');
$ib = joma_insights_build($plan, $acts, $evs, $moods, $today);
t('insight: too little data → no insight (honest state)', $ib['status'] === 'INSUFFICIENT_DATA' && count($ib['insights']) === 0);

/* دادهٔ ساختگی کافی: ۱۰ روز ثبت + حال */
for ($i = 1; $i <= 10; $i++) {
    $day = '1405-06-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    register_performance($uid, $plan, $pa, $day, 6);
    if (!get_mood($uid, $day)) save_mood($uid, $day, array('energy' => 5, 'general' => 4, 'focus' => 4, 'sleep' => 5, 'stress' => 2), '');
}
$evs2 = list_events($plan['id'], $uid);
$moods2 = list_moods($uid, '1405-06-01', '1405-06-31');
$ib2 = joma_insights_build($plan, $acts, $evs2, $moods2, $today);
t('insight: enough data → insights built', $ib2['status'] === 'OK' && count($ib2['insights']) > 0);
$haveEvidence = true; $haveCaveat = false; $haveSample = true;
foreach ($ib2['insights'] as $in) {
    foreach (array('indicators', 'from', 'to', 'sampleSize', 'sampleLabel', 'droppedNote', 'direction', 'lag', 'ruleId', 'ruleVersion', 'computedAt') as $k) {
        if (!isset($in['evidence'][$k])) $haveEvidence = false;
    }
    if ((int) $in['evidence']['sampleSize'] < 1) $haveSample = false;
    if (strpos($in['text'], 'دلیل نیست') !== false) $haveCaveat = true;
}
t('insight: every insight carries full evidence', $haveEvidence);
t('insight: sample size is always present and ≥ 1', $haveSample);
t('insight: uncertainty sentence is inside the text', $haveCaveat);
t('insight: no accuracy/confidence number in texts', (function () use ($ib2) {
    foreach ($ib2['insights'] as $in) {
        if (strpos($in['text'], '٪ دقت') !== false || strpos($in['text'], 'اطمینان') !== false) return false;
    }
    return true;
})());
t('insight: correlation guard returns null on zero variance', joma_insight_corr(array(3, 3, 3), array(1, 2, 3)) === null);
t('insight: streak rule counts consecutive days', (function () use ($ib2) {
    foreach ($ib2['insights'] as $in) if ($in['id'] === 'streak') return (int) $in['evidence']['sampleSize'] >= 3;
    return false;
})());
t('insight: seen-marker starts as unseen', joma_journal_unseen_count($uid, $ib2['insights']) === count($ib2['insights']));
joma_journal_mark_seen($uid, $ib2['computedAt']);
t('insight: after visiting the journal all are seen', joma_journal_unseen_count($uid, $ib2['insights']) === 0);

@unlink($GLOBALS['JOMA_STORE_PATH']);
echo "\nنتیجه: pass=$pass fail=$fail\n";
echo $fail === 0 ? "همهٔ آزمون‌ها سبز است ✔\n" : "موارد قرمز را ببین ✗\n";
