<?php
/**
 * JOMA — گزارش‌ها — طرح نسخهٔ ۲۰ (گام ۳)
 * ده تب کامل (مطابق SPEC/16) · یک جملهٔ توضیح زیر تب فعال · فیلتر دوره · پنل «واژه‌ها».
 * همهٔ اعداد و نمودارها از همان موتور موفقیت (JOMA_SUCCESS_V1) و لایه‌های تحلیلی می‌آید؛
 * این فایل هیچ محاسبه‌ای از خودش اضافه نمی‌کند و هیچ عددی را جای‌گزین نمی‌کند.
 */
require_login();
require_perm('VIEW_REPORT');
$u = current_user();
require_once dirname(__FILE__) . '/../includes/success_view.php';
// JOMA-ANALYTICS-R1-BEGIN (گزارش تحلیلی — فاز R1؛ fail-closed)
$__analytics_r1_ok = false;
try {
    $__ar1_file = dirname(__FILE__) . '/../functions/analytics_reports.php';
    if (is_file($__ar1_file)) {
        include_once $__ar1_file;
        $__analytics_r1_ok = function_exists('analytics_reports_render');
    }
} catch (Throwable $e) {
    $__analytics_r1_ok = false;
    error_log('analytics r1 kept disabled: safe load failed.');
}
unset($__ar1_file);
// JOMA-ANALYTICS-R1-END
$list = list_periods($u['id']);
$key = isset($_GET['period']) ? $_GET['period'] : current_period_key();
if (!preg_match('/^\d{4}-\d{2}$/', $key)) $key = jalali_period_key(jalali_today());
$wp = ensure_period($u['id'], $key);
$rep = build_report($u['id'], $wp['plan']);
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
$tabs = array(
    'overview' => 'خلاصه',
    'success' => 'تحلیل موفقیت',
    'acts' => 'فعالیت‌ها',
    'weight' => 'وزن',
    'cal' => 'تقویم',
    'trend' => 'روند',
    'mood' => 'خلق',
    'cmp' => 'مقایسه',
    'det' => 'جزئیات',
);
// (tabfix) ثبت تب باید «قبل از» نرمال‌سازی $tab باشد.
if ($__analytics_r1_ok) {
    $tabs['analytics'] = 'گزارش تحلیلی';
}
if (!isset($tabs[$tab])) $tab = 'overview';
if ($tab === 'analytics') {
    header('Cache-Control: private, no-store');
}
// JOMA-ANALYTICS-R1-END

$tab_desc = array(
    'overview' => 'یک نگاه کلی: چند روز ثبت کرده‌ای، چند فعالیت داری و چرخه‌ها در چه وضعی‌اند.',
    'success' => 'مقدار واقعی در برابر هدف، درصد تحقق و پوشش داده — با در نظر گرفتن وزن هر فعالیت.',
    'acts' => 'هر فعالیت چه مقدار ثبت شده و هدف همان ماه چه بوده است.',
    'weight' => 'سهم هر فعالیت در موفقیت کلی؛ «وزن» یعنی اهمیت آن فعالیت.',
    'cal' => 'نقشهٔ روزهای ماه: شدت ثبت هر روز و اینکه آن روز حال ثبت شده یا نه.',
    'trend' => 'روند روزانهٔ موفقیت در طول دوره؛ روز بی‌ثبت «بی‌داده» است، نه صفر.',
    'mood' => 'پنج شاخص حال: میانگین و تغییرها. این‌ها خودگزارشی‌اند، نه نمرهٔ تو.',
    'cmp' => 'دو دوره را کنار هم ببین؛ درصدها نسبی‌اند، نه برتری مطلق.',
    'det' => 'رخدادهای خام هر فعالیت — برای وقتی می‌خواهی دقیق ببینی چه ثبت شده.',
    'analytics' => 'ترکیب دلخواه شاخص‌ها، میانگین روزهای هفته و همبستگی — با این جمله که «همبستگی، علت نیست».',
);

$metrics = array(
    'energy' => 'انرژی',
    'general_mood' => 'حال عمومی',
    'focus' => 'تمرکز',
    'sleep_quality' => 'خواب',
    'stress' => 'استرس',
);

/* ---- موتور موفقیت: یک‌بار برای تب‌هایی که به آن نیاز دارند ---- */
$sr = null;
if (function_exists('success_report')) {
    try {
        $sr = success_report($rep['plan'], $rep['bounds'], $rep['activities'], $rep['events'], jalali_today());
    } catch (Throwable $e) {
        $sr = null;
    }
}
$hasEvents = ((int) $rep['source_event_count'] > 0);
$daysWithData = 0;
foreach ($rep['calendar'] as $c) { if (!empty($c['event_count'])) $daysWithData++; }

/* ---- جملهٔ جمع‌بندی صادقانه (از دادهٔ واقعی همین دوره) ---- */
$summary = '';
if (!$rep['activities']) {
    $summary = 'این دوره هنوز برنامه‌ای ندارد؛ برای دیدن گزارش، اول چند فعالیت به برنامهٔ این ماه اضافه کن.';
} elseif (!$hasEvents && !$rep['moods']) {
    $summary = 'در این دوره هنوز چیزی ثبت نشده است. گزارش‌ها وقتی پر می‌شوند که ثبت‌ها شروع شود.';
} else {
    $summary = 'در این دوره <b>' . fa_num($daysWithData) . ' روز</b> ثبت عملکرد داری و حال '
        . fa_num(count($rep['moods'])) . ' روز ثبت شده است. مجموع رخدادهای خام: <b>'
        . fa_num((int) $rep['source_event_count']) . '</b>.';
    if ($sr !== null) {
        $ostatus = isset($sr['overall_status']) ? $sr['overall_status'] : '';
        if ($ostatus === 'OK') {
            $summary .= ' موفقیت کلی با وزن و چرخه‌های واجد شرایط محاسبه شده است.';
        } elseif ($ostatus !== '') {
            $map = array(
                'NO_DATA' => 'برای محاسبهٔ موفقیت کلی، هنوز ثبت کافی نیست.',
                'NO_ELIGIBLE_CYCLES' => 'هنوز چرخهٔ کاملی بسته نشده؛ به همین دلیل درصد موفقیت کلی اعلام نمی‌شود.',
                'NOT_STARTED' => 'برنامه هنوز شروع نشده است.',
                'INSUFFICIENT_DATA' => 'هیچ فعالیت وزن‌داری برای محاسبهٔ موفقیت کلی وجود ندارد.',
                'NO_ELIGIBLE_ACTIVITIES' => 'هیچ فعالیت قابل‌تحلیلی در این برنامه نیست.',
            );
            if (isset($map[$ostatus])) $summary .= ' ' . $map[$ostatus];
        }
    }
}

joma_header('گزارش‌ها', array());
?>
<?php joma_v2_pagehead('گزارش‌ها', 'گزارش‌های من', 'فقط دادهٔ دورهٔ ' . jalali_period_label($key) . ' — بدون عدد ساختگی.', '<span>' . e(jalali_period_label($key)) . '</span>'); ?>

<?php /* ---------- فیلتر دوره + پنل واژه‌ها ---------- */ ?>
<div class="rfilters">
  <?php if (count($list) > 1) {
      foreach ($list as $p) {
          $on = ($p['period_key'] === $key) ? ' on' : '';
          echo '<a class="fsel' . $on . '" href="' . e(joma_url('index.php?p=reports&period=' . $p['period_key'] . '&tab=' . $tab)) . '">'
             . joma_v2_icon('i-cal') . e(jalali_period_label($p['period_key'])) . '</a>';
      }
  } ?>
  <span class="fsel"><?php echo joma_v2_icon('i-check'); ?><?php echo fa_num($daysWithData); ?> روز با داده</span>
</div>

<details class="gl">
  <summary>این عددها یعنی چه؟</summary>
  <div class="glossary" style="margin-top:8px">
    <div class="tiny">واژه‌ها را جدی می‌گیریم: هر عدد در این صفحه معنای دقیق خودش را دارد.</div>
    <table>
      <tr><th>واژه</th><th>معنی</th><th>نمایش</th></tr>
      <tr><td>مقداری که ثبت کرده‌ای</td><td>عدد خودت با واحد فعالیت</td><td>«۷٫۵ ساعت»</td></tr>
      <tr><td>هدف</td><td>هدفِ قفل‌شدهٔ همان برنامه</td><td>«هدف: ۸ ساعت»</td></tr>
      <tr><td>هدف را چقدر برآورده کردی</td><td>نسبت مقدار به هدف</td><td>درصد</td></tr>
      <tr><td>چقدر داده داری</td><td>میزان وجود داده (پوشش)</td><td>درصد</td></tr>
      <tr><td>موفقیت کلی</td><td>تجمیع با وزن و چرخه‌های واجد شرایط</td><td>درصد</td></tr>
      <tr><td>ثبت‌نشده</td><td>داده‌ای وجود ندارد</td><td>«ثبت نشده» — هرگز صفر</td></tr>
    </table>
    <p class="tiny" style="margin-top:8px">حال (خلق) و امتیازها در «موفقیت کلی» حساب نمی‌شوند؛ جدا دیده می‌شوند.</p>
  </div>
</details>

<?php /* ---------- نوار تب‌ها ---------- */ ?>
<nav class="rtabs" aria-label="تب‌های گزارش">
  <?php foreach ($tabs as $k => $lab) {
      $on = ($tab === $k) ? ' on' : '';
      echo '<a class="rtab' . $on . '" href="' . e(joma_url('index.php?p=reports&period=' . $key . '&tab=' . $k)) . '">' . e($lab) . '</a>';
  } ?>
</nav>
<p class="tabdesc"><?php echo e(isset($tab_desc[$tab]) ? $tab_desc[$tab] : ''); ?></p>

<?php /* ---------- هشدار رخداد مبهم (هرگز پنهان نمی‌شود) ---------- */
if ($sr !== null && function_exists('render_success_warnings') && $tab !== 'success') {
    // هشدارهای موتور همیشه نمایش داده می‌شوند (طبق SPEC: برای زیبایی حذف نمی‌شوند).
    render_success_warnings($sr);
}
?>

<?php
/* ---------- حالت «بی‌داده»: تب خالی، نمودار خالی رسم نمی‌شود ---------- */
$needsData = array('success', 'acts', 'weight', 'cal', 'trend', 'mood', 'det', 'cmp');
if (in_array($tab, $needsData, true) && !$hasEvents && !$rep['moods'] && !$rep['activities']) {
    echo '<div class="nodata"><span class="ico">🌱</span><b>برای این دوره داده‌ای ثبت نشده است</b>'
       . '<p class="lede">وقتی چند روز ثبت کنی، این تب پر می‌شود. عدد جعلی نمی‌سازیم.</p>'
       . '<div class="btn-row" style="justify-content:center;margin-top:10px">'
       . '<a class="btn sm" href="' . e(joma_url('index.php?p=today')) . '">ثبت کارهای امروز</a>'
       . '<a class="btn sec sm" href="' . e(joma_url('index.php?p=plan')) . '">برنامهٔ من</a></div></div>';
    joma_footer();
    return;
}
?>

<?php
if ($tab === 'overview') {
    /* ---------- خلاصه ---------- */
    echo '<section class="summaryline">' . $summary . '</section>';

    echo '<div class="rkpis">';
    echo '<div class="rkpi"><div class="k">روزهای با ثبت</div><div class="v">' . fa_num($daysWithData) . '</div><div class="t">در این دوره</div></div>';
    echo '<div class="rkpi"><div class="k">فعالیت‌های برنامه</div><div class="v">' . fa_num(count($rep['activities'])) . '</div><div class="t">همین ماه</div></div>';
    echo '<div class="rkpi"><div class="k">رخدادهای خام</div><div class="v">' . fa_num((int) $rep['source_event_count']) . '</div><div class="t">هر ثبت، یک رخداد</div></div>';
    echo '<div class="rkpi"><div class="k">روزهای ثبت حال</div><div class="v">' . fa_num(count($rep['moods'])) . '</div><div class="t">خودگزارشی</div></div>';
    echo '</div>';

    /* وضعیت چرخه‌ها — از موتور موفقیت */
    if ($sr !== null) {
        $cs = array();
        foreach ($sr['activities'] as $row) {
            foreach ($row['cycles'] as $cy) {
                $st = isset($cy['status']) ? $cy['status'] : '';
                if ($st === '') continue;
                if (!isset($cs[$st])) $cs[$st] = 0;
                $cs[$st]++;
            }
        }
        $order = array('COMPLETED', 'IN_PROGRESS', 'NOT_ELIGIBLE_YET', 'NOT_ELIGIBLE_PARTIAL_START', 'NOT_ELIGIBLE', 'NOT_ELIGIBLE_PARTIAL_ARCHIVE', 'NOT_STARTED');
        echo '<section class="card"><div class="k">وضعیت چرخه‌ها</div><div class="btn-row" style="margin-top:6px">';
        if (!$cs) {
            echo '<span class="chip n">چرخه‌ای برای نمایش نیست</span>';
        } else {
            $ordered = array();
            foreach ($order as $st) { if (isset($cs[$st])) { $ordered[$st] = $cs[$st]; unset($cs[$st]); } }
            foreach ($cs as $st => $n) { $ordered[$st] = $n; } // هر وضعیت تازه‌ای هم نمایش داده می‌شود
            foreach ($ordered as $st => $n) {
                $cls = in_array($st, array('COMPLETED'), true) ? 'g' : (($st === 'IN_PROGRESS') ? 's' : 'n');
                echo '<span class="chip ' . $cls . '">' . e(function_exists('success_ui_cycle_text') ? success_ui_cycle_text($st) : $st) . ': ' . fa_num($n) . '</span>';
            }
        }
        echo '</div><p class="tiny" style="margin-top:6px">چرخه = یک بازهٔ کامل (روز، هفته یا ماه) که فعالیت باید در آن انجام شود. '
           . 'چرخه‌های «خارج از محاسبه» آن‌هایی‌اند که پیش از شروع برنامه بوده‌اند؛ در فرمول نمی‌آیند و حذف هم نمی‌شوند.</p></section>';
    }

    echo success_guide_card(joma_url('index.php?p=reports&period=' . $key . '&tab=success'));
    echo '<div class="card"><div class="k">قدم بعدی</div>'
       . '<p class="lede" style="margin-top:6px">برای دیدن نمودارها: تب «روند»، «تقویم» و «خلق». برای دقت محاسبات: تب «تحلیل موفقیت».</p></div>';
} elseif ($tab === 'success') {
    render_success_tab($rep, joma_url('index.php?p=reports&period=' . $key . '&tab=success'));
} elseif ($tab === 'acts') {
    if (!$rep['activities']) {
        echo '<div class="nodata"><span class="ico">📋</span><b>فعالیتی در این دوره نیست</b><p class="lede">اول از کتابخانه، فعالیت به برنامهٔ این ماه اضافه کن.</p></div>';
    } else {
        echo '<div class="grid grid-2">';
        foreach ($rep['activities'] as $a) {
            $pct = ((float) $a['target_value'] > 0) ? min(1, ((float) $a['actual'] / (float) $a['target_value'])) : 0;
            echo '<article class="card">';
            echo '<div class="row" style="justify-content:space-between">';
            echo '<span class="row" style="gap:8px"><span class="sticker" style="background:' . e($a['color']) . '33">' . e($a['sticker']) . '</span><b style="font-size:13px">' . e($a['name']) . '</b></span>';
            echo '<span class="chip">' . e(frequencies_list()[$a['frequency']]) . '</span>';
            echo '</div>';
            echo '<p class="lede" style="margin-top:6px">ثبت‌شده: <b>' . e(format_value($a['data_type'], $a['actual'], $a['unit'])) . '</b> از هدف ' . e(format_value($a['data_type'], $a['target_value'], $a['unit'])) . '</p>';
            echo '<div class="bar" style="margin:6px 0"><i style="width:' . (int) round($pct * 100) . '%"></i></div>';
            echo '<p class="tiny">' . fa_num((int) $a['event_count']) . ' رخداد ثبت‌شده · اهمیت ' . e(weight_label($a['weight'])) . '</p>';
            echo '</article>';
        }
        echo '</div>';
    }
} elseif ($tab === 'weight') {
    echo '<p class="lede">وزن ذخیره‌شدهٔ همین دوره. «وزن» یعنی این فعالیت در موفقیت کلی چقدر سهم دارد.</p>';
    if (!$rep['activities']) {
        echo '<div class="nodata"><span class="ico">⚖️</span><b>وزنی برای نمایش نیست</b><p class="lede">ابتدا فعالیت به برنامه اضافه کن.</p></div>';
    } else {
        $max = max(1, $rep['weight_sum']);
        foreach ($rep['activities'] as $a) {
            $pct = min(100, ((int) $a['weight'] / $max) * 100);
            echo '<div class="card weight-row"><div style="flex:1;min-width:0"><div class="row" style="justify-content:space-between"><span>' . e($a['sticker'] . ' ' . $a['name']) . '</span><b style="font-size:12.5px">اهمیت ' . e(weight_label($a['weight'])) . '</b></div><div class="bar" style="margin-top:6px"><i style="width:' . $pct . '%"></i></div></div></div>';
        }
        echo '<p class="tiny">جمع وزن‌های این دوره: ' . fa_num((int) $rep['weight_sum']) . ' · وزن‌ها در فرمول موفقیت کلی استفاده می‌شوند.</p>';
    }
} elseif ($tab === 'cal') {
    $calS = success_report($rep['plan'], $rep['bounds'], $rep['activities'], $rep['events'], jalali_today());
    render_analytics_heatmap($calS, $rep['moods']);
} elseif ($tab === 'trend') {
    $trendS = success_report($rep['plan'], $rep['bounds'], $rep['activities'], $rep['events'], jalali_today());
    render_analytics_daily_chart($trendS, 'ch-daily');
} elseif ($tab === 'mood') {
    if (!$rep['moods']) {
        echo '<div class="nodata"><span class="ico">💗</span><b>خلق این دوره ثبت نشده است</b><p class="lede">هر روز می‌توانی پنج شاخص را ثبت کنی؛ چند ثانیه وقت می‌گیرد.</p><div class="btn-row" style="justify-content:center;margin-top:10px"><a class="btn sm" href="' . e(joma_url('index.php?p=mood')) . '">ثبت حال امروز</a></div></div>';
    } else {
        echo '<div class="grid grid-2">';
        foreach ($metrics as $mk => $lab) {
            $s = 0; $n = 0;
            foreach ($rep['moods'] as $m) { $s += (int) $m[$mk]; $n++; }
            $avg = $n ? $s / $n : 0;
            echo '<div class="card"><div class="row" style="justify-content:space-between"><b style="font-size:12.5px">' . e($lab) . '</b><span class="chip g">میانگین ' . fa_num(round($avg, 1)) . ' از ۵</span></div>';
            echo '<div class="bar" style="margin-top:6px"><i style="width:' . (int) round($avg / 5 * 100) . '%"></i></div></div>';
        }
        echo '</div>';
        $moodS = success_report($rep['plan'], $rep['bounds'], $rep['activities'], $rep['events'], jalali_today());
        render_analytics_mood($moodS, $rep['moods'], array('canvas' => 'ch-mood-tab', 'toggle' => 'mood-toggle-tab'));

        echo '<details class="gl"><summary>فهرست روزهای ثبت‌شده (' . fa_num(count($rep['moods'])) . ')</summary>';
        echo '<div class="card" style="margin-top:8px">';
        foreach ($rep['moods'] as $m) {
            echo '<div class="hd-item"><b style="font-size:12.5px">' . e(jalali_format($m['jalali_date'])) . '</b>'
               . '<p class="tiny">انرژی ' . fa_num($m['energy']) . ' · حال ' . fa_num($m['general_mood']) . ' · تمرکز ' . fa_num($m['focus'])
               . ' · خواب ' . fa_num($m['sleep_quality']) . ' · استرس ' . fa_num($m['stress']) . '</p>';
            if ($m['note']) echo '<p class="tiny" style="color:var(--ink-2)">یادداشت خصوصی تو (برای هم‌مسیر فرستاده نمی‌شود): ' . e($m['note']) . '</p>';
            echo '</div>';
        }
        echo '</div></details>';
    }
} elseif ($tab === 'cmp') {
    $other = isset($_GET['other']) ? $_GET['other'] : '';
    echo '<form method="get" class="card" style="max-width:460px">';
    echo '<input type="hidden" name="p" value="reports"><input type="hidden" name="tab" value="cmp"><input type="hidden" name="period" value="' . e($key) . '">';
    echo function_exists('joma_sid_field') ? joma_sid_field() : '';
    echo '<label>دورهٔ دوم برای مقایسه</label><select name="other" onchange="this.form.submit()">';
    foreach ($list as $p) if ($p['period_key'] !== $key) {
        echo '<option value="' . e($p['period_key']) . '" ' . ($other === $p['period_key'] ? 'selected' : '') . '>' . e(jalali_period_label($p['period_key'])) . '</option>';
    }
    echo '</select><p style="margin-top:8px"><button class="btn sec">مقایسه کن</button></p></form>';

    if (count($list) < 2) {
        echo '<div class="nodata"><span class="ico">📊</span><b>برای مقایسه حداقل دو دوره لازم است</b><p class="lede">وقتی دورهٔ بعد شروع شود، این‌جا می‌توانی دو دوره را کنار هم ببینی.</p></div>';
    } elseif ($other) {
        $w2 = ensure_period($u['id'], $other);
        $r2 = build_report($u['id'], $w2['plan']);
        $todayJ = jalali_today();
        $sumA = analytics_period_summary($rep['plan'], $rep['bounds'], $rep['activities'], $rep['events'], $rep['moods'], $todayJ);
        $sumB = analytics_period_summary($r2['plan'], $r2['bounds'], $r2['activities'], $r2['events'], $r2['moods'], $todayJ);
        render_analytics_compare($sumA, $sumB, jalali_period_label($key), jalali_period_label($other));
        echo '<p class="hint">برای اطلاع: رخدادهای خام ' . fa_num($rep['source_event_count']) . ' (' . e(jalali_period_label($key)) . ') در برابر '
           . fa_num($r2['source_event_count']) . ' (' . e(jalali_period_label($other)) . ') — شمارش خام معیار برتری نیست.</p>';
        echo success_guide_card(joma_url('index.php?p=reports&period=' . $key . '&tab=success'));
    }
} elseif ($tab === 'analytics') {
    // JOMA-ANALYTICS-R1-BEGIN — تب گزارش تحلیلی (فاز R1)
    if ($__analytics_r1_ok) {
        try {
            analytics_reports_render($u);
        } catch (Throwable $e) {
            error_log('analytics r1 render failed: fixed message.');
            echo '<div class="nodata"><span class="ico">📈</span><b>امکان تهیه گزارش در حال حاضر وجود ندارد</b><p class="lede">بعداً دوباره سر بزن.</p></div>';
        }
    } else {
        echo '<div class="nodata"><span class="ico">📈</span><b>امکان تهیه گزارش در حال حاضر وجود ندارد</b><p class="lede">بعداً دوباره سر بزن.</p></div>';
    }
    // JOMA-ANALYTICS-R1-END
} else {
    /* ---------- جزئیات ---------- */
    if (!$rep['activities']) {
        echo '<div class="nodata"><span class="ico">🧾</span><b>جزئیاتی برای نمایش نیست</b><p class="lede">فعالیتی در این دوره نیست.</p></div>';
    } else {
        foreach ($rep['activities'] as $a) {
            echo '<details class="gl"><summary>' . e($a['sticker'] . ' ' . $a['name']) . ' — ' . fa_num((int) $a['event_count']) . ' رخداد</summary>';
            echo '<div class="card" style="margin-top:8px">';
            echo '<p class="tiny">رخداد خام ← مقدار واقعی ← تحقق (تعریف‌نشده برای این برنامه) ← اهمیت ' . e(weight_label($a['weight'])) . '</p>';
            echo '<ul style="margin:6px 0 0;padding-inline-start:18px">';
            foreach (array_reverse($a['events']) as $ev) {
                $ulist = units_list();
                $ulabel = isset($ulist[$a['unit']]) ? $ulist[$a['unit']] : '';
                echo '<li class="tiny">' . e(jalali_format($ev['performance_date'])) . ' · <b>' . e($ev['actual_value']) . '</b> ' . e($ulabel) . '</li>';
            }
            if (!$a['events']) echo '<li class="tiny">رخدادی نیست.</li>';
            echo '</ul></div></details>';
        }
    }
}
joma_footer();
