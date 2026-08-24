<?php
require_login();
require_perm('VIEW_REPORT');
$u = current_user();
$list = list_periods($u['id']);
$key = isset($_GET['period']) ? $_GET['period'] : current_period_key();
if (!preg_match('/^\d{4}-\d{2}$/', $key)) $key = jalali_period_key(jalali_today());
$wp = ensure_period($u['id'], $key);
$rep = build_report($u['id'], $wp['plan']);
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
$tabs = array(
    'overview' => 'خلاصه',
    'acts' => 'فعالیت‌ها',
    'weight' => 'وزن',
    'cal' => 'تقویم',
    'trend' => 'روند',
    'mood' => 'خلق',
    'cmp' => 'مقایسه',
    'det' => 'جزئیات',
);
if (!isset($tabs[$tab])) $tab = 'overview';
$metrics = array(
    'energy' => 'انرژی',
    'general_mood' => 'حال عمومی',
    'focus' => 'تمرکز',
    'sleep_quality' => 'خواب',
    'stress' => 'استرس',
);
joma_header('گزارش‌ها', array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'گزارش')));
?>
<div class="page-head">
  <div>
    <h1>گزارش‌ها</h1>
    <p class="lede">فقط دادهٔ همان دوره انتخاب‌شده.</p>
  </div>
  <form method="get">
    <input type="hidden" name="p" value="reports">
    <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
    <select name="period" onchange="this.form.submit()">
      <?php foreach ($list as $p) echo '<option value="'.e($p['period_key']).'" '.($p['period_key'] === $key ? 'selected' : '').'>'.e(jalali_period_label($p['period_key'])).'</option>'; ?>
    </select>
  </form>
</div>
<div class="tabs">
<?php foreach ($tabs as $k => $lab) {
    $cls = $tab === $k ? 'tab on' : 'tab';
    echo '<a class="'.$cls.'" href="'.e(joma_url('index.php?p=reports&period='.$key.'&tab='.$k)).'">'.$lab.'</a>';
} ?>
</div>
<?php
if ($tab === 'overview') {
    echo unspecified_notice('فرمول Achievement و موفقیت کلی تعریف نشده است. درصد ساختگی نشان داده نمی‌شود.');
    echo '<div class="grid grid-3">';
    echo '<div class="card stat"><div class="k">رویدادها</div><div class="v">'.fa_num($rep['source_event_count']).'</div></div>';
    echo '<div class="card stat"><div class="k">فعالیت‌ها</div><div class="v">'.fa_num(count($rep['activities'])).'</div></div>';
    echo '<div class="card stat"><div class="k">روزهای ثبت خلق</div><div class="v">'.fa_num(count($rep['moods'])).'</div></div>';
    echo '</div>';
} elseif ($tab === 'acts') {
    if (!$rep['activities']) echo empty_state('فعالیتی نیست', 'برای این دوره برنامه‌ای ثبت نشده.');
    foreach ($rep['activities'] as $a) {
        echo '<article class="card" style="display:flex;gap:12px;align-items:center">';
        echo '<div class="sticker" style="background:'.$a['color'].'">'.e($a['sticker']).'</div>';
        echo '<div><strong>'.e($a['name']).'</strong><p class="meta">'.e(frequencies_list()[$a['frequency']]).' · '.e(format_value($a['data_type'], $a['actual'], $a['unit'])).' / '.e(format_value($a['data_type'], $a['target_value'], $a['unit'])).'</p></div>';
        echo '</article>';
    }
} elseif ($tab === 'weight') {
    echo '<p class="lede">وزن ذخیره‌شدهٔ همین دوره — فرمول موفقیت کلی محاسبه نمی‌شود.</p>';
    if (!$rep['activities']) echo empty_state('وزنی برای نمایش نیست', 'ابتدا فعالیت به برنامه اضافه کنید.');
    $max = max(1, $rep['weight_sum']);
    foreach ($rep['activities'] as $a) {
        $pct = min(100, ((int) $a['weight'] / $max) * 100);
        echo '<div class="card weight-row"><div style="display:flex;justify-content:space-between"><span>'.e($a['sticker'].' '.$a['name']).'</span><span>وزن '.fa_num($a['weight']).'</span></div><div class="bar"><i style="width:'.$pct.'%"></i></div></div>';
    }
} elseif ($tab === 'cal') {
    $startPad = jalali_weekday_index($rep['bounds']['start']);
    echo '<div class="card cal-wrap"><div class="cal-head">';
    foreach (array('ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج') as $d) echo '<div>'.$d.'</div>';
    echo '</div><div class="cal">';
    for ($i = 0; $i < $startPad; $i++) echo '<div></div>';
    foreach ($rep['calendar'] as $day) {
        $hot = ($day['event_count'] || $day['has_mood']) ? ' hot' : '';
        echo '<div class="day'.$hot.'"><strong>'.fa_num((int) substr($day['date'], 8)).'</strong><div class="marks">';
        if ($day['event_count']) echo '<span style="color:var(--primary)">●</span>';
        if ($day['has_mood']) echo '<span>🌸</span>';
        echo '</div></div>';
    }
    echo '</div></div>';
} elseif ($tab === 'trend') {
    $pts = array();
    foreach ($rep['calendar'] as $day) if ($day['event_count'] > 0) $pts[] = $day['actual_total'];
    if (!$pts) echo empty_state('هنوز عملکردی ثبت نشده است', 'بعد از ثبت عملکرد، روند اینجا دیده می‌شود.');
    else {
        echo '<div class="card">'.sparkline_svg($pts).'<p class="lede">جمع مقدار ثبت‌شده در روزهایی که رویداد داشته‌اند — نه درصد موفقیت.</p></div>';
    }
} elseif ($tab === 'mood') {
    if (!$rep['moods']) echo empty_state('خلق این دوره ثبت نشده است', 'هر روز می‌توانید پنج شاخص را ثبت کنید.');
    else {
        echo '<div class="card"><h3>میانگین شاخص‌ها (فقط داده واقعی)</h3><div class="mood-bars">';
        foreach ($metrics as $mk => $lab) {
            $s = 0; $n = 0;
            foreach ($rep['moods'] as $m) { $s += (int) $m[$mk]; $n++; }
            $avg = $n ? $s / $n : 0;
            echo '<div class="row"><span>'.e($lab).'</span><div class="bar"><i style="width:'.($avg / 5 * 100).'%"></i></div><strong>'.fa_num(round($avg, 1)).'</strong></div>';
        }
        echo '</div></div>';
        foreach ($rep['moods'] as $m) {
            echo '<div class="card">'.e(jalali_format($m['jalali_date'])).' · انرژی '.fa_num($m['energy']).' · حال '.fa_num($m['general_mood']).' · تمرکز '.fa_num($m['focus']).' · خواب '.fa_num($m['sleep_quality']).' · استرس '.fa_num($m['stress']);
            if ($m['note']) echo '<p class="lede">'.e($m['note']).'</p>';
            echo '</div>';
        }
    }
} elseif ($tab === 'cmp') {
    $other = isset($_GET['other']) ? $_GET['other'] : '';
    echo '<form method="get" class="card"><input type="hidden" name="p" value="reports"><input type="hidden" name="tab" value="cmp"><input type="hidden" name="period" value="'.e($key).'">';
    echo '<label>دوره دوم</label><select name="other">';
    foreach ($list as $p) if ($p['period_key'] !== $key) echo '<option value="'.e($p['period_key']).'" '.($other === $p['period_key'] ? 'selected' : '').'>'.e(jalali_period_label($p['period_key'])).'</option>';
    echo '</select><p><button class="btn sec">مقایسه</button></p></form>';
    if (count($list) < 2) echo empty_state('برای مقایسه حداقل دو دوره لازم است', 'یک دوره دیگر بسازید.');
    elseif ($other) {
        $w2 = ensure_period($u['id'], $other);
        $r2 = build_report($u['id'], $w2['plan']);
        echo '<div class="grid grid-2">';
        echo '<div class="card"><h3>'.e(jalali_period_label($key)).'</h3><p>رویداد '.fa_num($rep['source_event_count']).' · فعالیت '.fa_num(count($rep['activities'])).'</p></div>';
        echo '<div class="card"><h3>'.e(jalali_period_label($other)).'</h3><p>رویداد '.fa_num($r2['source_event_count']).' · فعالیت '.fa_num(count($r2['activities'])).'</p></div>';
        echo '</div>';
        echo unspecified_notice('مقایسه فقط شمارش رویداد و فعالیت است. درصد موفقیت محاسبه نمی‌شود.');
    }
} else {
    foreach ($rep['activities'] as $a) {
        echo '<div class="card"><h3>'.e($a['sticker'].' '.$a['name']).'</h3>';
        echo '<p class="lede">رویداد خام ← مقدار واقعی ← تحقق (تعریف‌نشده) ← وزن '.fa_num($a['weight']).'</p><ul>';
        foreach ($a['events'] as $ev) echo '<li>'.e(jalali_format($ev['performance_date'])).' · '.e($ev['actual_value']).'</li>';
        if (!$a['events']) echo '<li class="lede">رویدادی نیست.</li>';
        echo '</ul></div>';
    }
    if (!$rep['activities']) echo empty_state('جزئیاتی نیست', 'فعالیتی در این دوره نیست.');
}
joma_footer();
