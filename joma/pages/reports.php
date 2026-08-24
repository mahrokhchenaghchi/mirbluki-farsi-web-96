<?php
require_login(); require_perm('VIEW_REPORT');
$u = current_user();
$list = list_periods($u['id']);
$key = isset($_GET['period']) ? $_GET['period'] : jalali_period_key(jalali_today());
$wp = ensure_period($u['id'], $key);
$rep = build_report($u['id'], $wp['plan']);
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
joma_header('گزارش‌ها', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'گزارش')));
echo '<h1>گزارش '.e(jalali_period_label($key)).'</h1>';
echo '<form method="get" class="card"><input type="hidden" name="p" value="reports"><select name="period">';
foreach ($list as $p) echo '<option value="'.e($p['period_key']).'" '.($p['period_key']===$key?'selected':'').'>'.e(jalali_period_label($p['period_key'])).'</option>';
echo '</select> <button class="btn sec">نمایش</button></form>';
$tabs = array('overview'=>'خلاصه','acts'=>'فعالیت‌ها','cal'=>'تقویم','mood'=>'خلق','cmp'=>'مقایسه','det'=>'جزئیات');
echo '<p>';
foreach ($tabs as $k=>$lab) echo '<a class="chip" href="'.e(joma_url('index.php?p=reports&period='.$key.'&tab='.$k)).'">'.$lab.'</a> ';
echo '</p>';
echo '<div class="card">فرمول Achievement رسمی تعریف نشده؛ درصد جعلی نشان داده نمی‌شود.</div>';
if ($tab==='overview') {
    echo '<div class="grid grid-3"><div class="card"><h3>رویدادها</h3><p>'.fa_num($rep['source_event_count']).'</p></div>';
    echo '<div class="card"><h3>فعالیت‌ها</h3><p>'.fa_num(count($rep['activities'])).'</p></div>';
    echo '<div class="card"><h3>روزهای خلق</h3><p>'.fa_num(count($rep['moods'])).'</p></div></div>';
} elseif ($tab==='acts') {
    foreach ($rep['activities'] as $a) {
        echo '<div class="card"><strong>'.e($a['sticker'].' '.$a['name']).'</strong> وزن '.e($a['weight']).' · ثبت '.e($a['actual']).' / هدف '.e($a['target_value']).'</div>';
    }
    if (!$rep['activities']) echo '<div class="card">فعالیتی نیست.</div>';
} elseif ($tab==='cal') {
    echo '<div class="cal">';
    $startPad = 0;
    $g = jalali_to_gregorian($rep['bounds']['year'], $rep['bounds']['month'], 1);
    $w = (int) date('w', mktime(0,0,0,$g[1],$g[2],$g[0]));
    $startPad = ($w + 1) % 7;
    for ($i=0;$i<$startPad;$i++) echo '<div></div>';
    $hasE = array(); $hasM = array();
    foreach ($rep['events'] as $e) $hasE[$e['performance_date']] = 1;
    foreach ($rep['moods'] as $m) $hasM[$m['jalali_date']] = 1;
    for ($d=1;$d<=$rep['bounds']['days'];$d++) {
        $ds = $rep['bounds']['year'].'-'.jalali_pad($rep['bounds']['month']).'-'.jalali_pad($d);
        echo '<div>'.fa_num($d).(isset($hasE[$ds])?' ●':'').(isset($hasM[$ds])?' 🌸':'').'</div>';
    }
    echo '</div>';
} elseif ($tab==='mood') {
    if (!$rep['moods']) echo '<div class="card">خلق این دوره ثبت نشده است.</div>';
    foreach ($rep['moods'] as $m) {
        echo '<div class="card">'.e(jalali_format($m['jalali_date'])).' · انرژی '.$m['energy'].' · حال '.$m['general_mood'].' · تمرکز '.$m['focus'].' · خواب '.$m['sleep_quality'].' · استرس '.$m['stress'].'</div>';
    }
} elseif ($tab==='cmp') {
    $other = isset($_GET['other']) ? $_GET['other'] : '';
    echo '<form method="get" class="card"><input type="hidden" name="p" value="reports"><input type="hidden" name="tab" value="cmp"><input type="hidden" name="period" value="'.e($key).'"><select name="other">';
    foreach ($list as $p) if ($p['period_key']!==$key) echo '<option value="'.e($p['period_key']).'">'.e(jalali_period_label($p['period_key'])).'</option>';
    echo '</select><button class="btn sec">مقایسه</button></form>';
    if ($other) {
        $w2 = ensure_period($u['id'], $other);
        $r2 = build_report($u['id'], $w2['plan']);
        echo '<div class="grid grid-2"><div class="card"><h3>'.e(jalali_period_label($key)).'</h3>رویداد '.$rep['source_event_count'].'</div><div class="card"><h3>'.e(jalali_period_label($other)).'</h3>رویداد '.$r2['source_event_count'].'</div></div>';
    } elseif (count($list)<2) echo '<div class="card">برای مقایسه حداقل دو دوره لازم است.</div>';
} else {
    foreach ($rep['activities'] as $a) {
        echo '<div class="card"><h3>'.e($a['name']).'</h3><p>رویداد خام ← مقدار واقعی ← تحقق (تعریف‌نشده) ← وزن '.e($a['weight']).'</p><ul>';
        foreach ($a['events'] as $e) echo '<li>'.e(jalali_format($e['performance_date'])).' · '.e($e['actual_value']).'</li>';
        if (!$a['events']) echo '<li>رویدادی نیست.</li>';
        echo '</ul></div>';
    }
}
joma_footer();
