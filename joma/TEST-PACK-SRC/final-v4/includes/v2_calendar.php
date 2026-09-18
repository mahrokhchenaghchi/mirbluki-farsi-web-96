<?php
/**
 * JOMA — کامپوننت «مسیر این ماه» (تقویم سه‌بعدی پاستلی) — طبق SPEC/11 §۷
 * یک کامپوننت، دو جا: خانهٔ من و تب «مسیر این ماه» در کارهای امروز.
 *
 * همهٔ رنگ‌ها از وضعیت واقعی همان روز می‌آید و هیچ روزی از خودمان رنگی نمی‌شود.
 * رنگ، تنها حامل معنا نیست: زیر تقویم راهنمای برچسب‌دار و شمارنده‌ها هست.
 */

/**
 * وضعیت روزها را از دادهٔ واقعی می‌سازد.
 * خروجی: array(day => array(state, tasks_done, tasks_total, water, mood, has))
 */
function joma_calendar_days($user_id, $period_key, $activities, $events, $moods) {
    $b = jalali_period_bounds($period_key);
    $today = jalali_today();
    $moodByDate = array();
    foreach ($moods as $m) $moodByDate[$m['jalali_date']] = $m;

    $out = array();
    for ($d = 1; $d <= $b['days']; $d++) {
        $date = $b['year'] . '-' . jalali_pad($b['month']) . '-' . jalali_pad($d);
        $isFuture = (strcmp($date, $today) > 0);

        // کارهای روزانهٔ همین روز
        $dailyTotal = 0; $dailyDone = 0;
        foreach ($activities as $a) {
            if ($a['frequency'] !== 'DAILY') continue;
            $dailyTotal++;
            foreach ($events as $e) {
                if ((int) $e['plan_activity_id'] === (int) $a['id'] && $e['performance_date'] === $date) { $dailyDone++; break; }
            }
        }
        // هر رخدادی در آن روز (شامل هفتگی/ماهانه)
        $eventsToday = 0;
        foreach ($events as $e) if ($e['performance_date'] === $date) $eventsToday++;

        $water = null;      // مقدار آب همان روز
        $waterDraft = false;
        foreach ($activities as $a) {
            if (!isset($a['activity_code']) || $a['activity_code'] !== 'ACT002') continue;
            foreach ($events as $e) {
                if ((int) $e['plan_activity_id'] === (int) $a['id'] && $e['performance_date'] === $date) {
                    $water = (float) $e['actual_value'];
                    if (function_exists('joma_water_state')) {
                        $st = joma_water_state($user_id, $date);
                        $waterDraft = ($st['status'] === 'draft' && $date === $today);
                    }
                    break;
                }
            }
        }

        $hasMood = isset($moodByDate[$date]);
        $has = ($eventsToday > 0) || $hasMood;

        if ($isFuture) $state = 'future';
        elseif ($dailyTotal > 0 && $dailyDone >= $dailyTotal && $eventsToday > 0) $state = 'complete';
        elseif ($has) $state = 'partial';
        elseif ($date === $today) $state = 'today';
        else $state = 'none';

        $out[$date] = array(
            'day' => $d,
            'date' => $date,
            'state' => $state,
            'is_today' => ($date === $today),
            'is_future' => $isFuture,
            'tasks_done' => $dailyDone,
            'tasks_total' => $dailyTotal,
            'events' => $eventsToday,
            'water' => $water,
            'water_draft' => $waterDraft,
            'mood' => $hasMood,
        );
    }
    return array('days' => $out, 'bounds' => $b, 'today' => $today);
}

/** نام خانه‌های هفته، شنبه اول (راست‌به‌چپ). */
function joma_calendar_weekdays() {
    return array('ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج');
}

/** شاخص ستون (۰ = شنبه) برای یک تاریخ جلالی. */
function joma_calendar_col($date) {
    $i = jalali_weekday_index($date); // 0=شنبه … 6=جمعه
    return (int) $i;
}

/**
 * رندر تقویم ماه.
 * $opts: 'title' => bool (سرصفحه) · 'counters' => bool (چهار شمارنده) · 'stats' => bool
 */
function joma_v2_month_calendar($user_id, $plan, $activities, $events, $moods, $opts = array()) {
    $data = joma_calendar_days($user_id, $plan['period_key'], $activities, $events, $moods);
    $days = $data['days'];
    $b = $data['bounds'];
    $today = $data['today'];
    $showTitle = !isset($opts['title']) || $opts['title'];
    $showCounters = !isset($opts['counters']) || $opts['counters'];
    $showStats = !isset($opts['stats']) || $opts['stats'];

    $cnt = array('complete' => 0, 'partial' => 0, 'none' => 0);
    $daysWithData = 0;
    $bestDay = null;
    foreach ($days as $date => $d) {
        if ($d['is_future']) continue;
        if ($d['state'] === 'complete') $cnt['complete']++;
        elseif ($d['state'] === 'partial') $cnt['partial']++;
        else $cnt['none']++;
        if ($d['events'] > 0 || $d['mood']) $daysWithData++;
        if (!$d['is_future'] && ($bestDay === null || $d['events'] > $bestDay['events'])) $bestDay = $d;
    }

    $labels = array(
        'complete' => 'کامل ثبت شده',
        'partial' => 'ناقص — بخشی ثبت شده',
        'none' => 'چیزی ثبت نشده',
        'today' => 'امروز',
        'future' => 'روزهای آینده',
    );
    ?>
<section class="card" id="month-path">
  <?php if ($showTitle) { ?>
  <div class="row" style="justify-content:space-between;flex-wrap:wrap">
    <div>
      <div class="k">مسیر این ماه</div>
      <b style="font-size:13.5px"><?php echo e(jalali_period_label($plan['period_key'])); ?></b>
    </div>
    <span class="chip n"><?php echo fa_num($cnt['complete']); ?> روز کامل · <?php echo fa_num($cnt['partial']); ?> ناقص · <?php echo fa_num($cnt['none']); ?> بی‌ثبت</span>
  </div>
  <?php } ?>

  <div class="cal-wh" dir="rtl">
    <?php foreach (joma_calendar_weekdays() as $w) echo '<span>' . e($w) . '</span>'; ?>
  </div>

  <div class="cal-grid" dir="rtl">
    <?php
    // خانه‌های خالی پیش از روز اول (تا ستون درست بیفتد)
    $first = $b['year'] . '-' . jalali_pad($b['month']) . '-01';
    $lead = joma_calendar_col($first);
    for ($i = 0; $i < $lead; $i++) echo '<span class="cal-tile cal-empty" aria-hidden="true"></span>';

    foreach ($days as $date => $d) {
        $cls = 'cal-tile cal-' . $d['state'];
        if ($d['is_today']) $cls .= ' cal-istoday';
        $summary = array();
        if ($d['tasks_total'] > 0) $summary[] = 'کارها ' . fa_num($d['tasks_done']) . ' از ' . fa_num($d['tasks_total']);
        if ($d['events'] > 0 && $d['tasks_total'] === 0) $summary[] = fa_num($d['events']) . ' ثبت';
        if ($d['water'] !== null) $summary[] = 'آب ' . fa_num($d['water']) . ' لیوان' . ($d['water_draft'] ? ' (پیش‌نویس)' : '');
        if ($d['mood']) $summary[] = 'حال ثبت شد';
        if (!$summary) $summary[] = ($d['is_future'] ? 'هنوز نیامده' : 'چیزی ثبت نشده — بدون سرزنش');
        $label = jalali_format($date) . ' — ' . $labels[$d['state']] . ' · ' . implode(' · ', $summary);
        $detail = implode(' · ', $summary);

        if ($d['is_future']) {
            echo '<span class="' . $cls . '" role="button" aria-disabled="true" tabindex="-1" title="ثبت برای روزهای آینده ممکن نیست" data-day="' . e($date) . '" data-detail="' . e($detail) . '" data-state="future">';
            echo '<b class="cal-num">' . fa_num($d['day']) . '</b>';
            echo '</span>';
        } else {
            echo '<a class="' . $cls . '" href="' . e(joma_url('index.php?p=today&date=' . $date)) . '" '
               . 'aria-label="' . e($label) . '" data-day="' . e($date) . '" data-detail="' . e($detail) . '" data-state="' . e($d['state']) . '">';
            echo '<b class="cal-num">' . fa_num($d['day']) . '</b>';
            if ($d['state'] === 'complete') echo '<i class="cal-dot cal-dot-ok" title="کامل"></i>';
            elseif ($d['state'] === 'partial') echo '<i class="cal-dot cal-dot-part" title="ناقص"></i>';
            if ($d['is_today']) echo '<i class="cal-mark">امروز</i>';
            echo '</a>';
        }
    }
    ?>
  </div>

  <div class="cal-legend">
    <span><i class="cal-sw cal-complete"></i><?php echo e($labels['complete']); ?></span>
    <span><i class="cal-sw cal-partial"></i><?php echo e($labels['partial']); ?></span>
    <span><i class="cal-sw cal-none"></i><?php echo e($labels['none']); ?></span>
    <span><i class="cal-sw cal-istoday"></i><?php echo e($labels['today']); ?></span>
    <span><i class="cal-sw cal-future"></i><?php echo e($labels['future']); ?></span>
  </div>

  <p class="cal-bar" id="cal-bar" aria-live="polite">
    <?php if ($daysWithData === 0) { ?>
      اولین ثبت، اولین خانهٔ رنگی — تقویم سرجای خودش هست و با هر ثبت رنگ می‌گیرد.
    <?php } else { ?>
      روی هر روز بزن تا خلاصه‌اش را ببینی؛ با زدن دوباره به «کارهای امروز» همان روز می‌روی.
    <?php } ?>
  </p>

  <?php if ($showCounters) { ?>
  <div class="rkpis" style="margin-top:10px">
    <div class="rkpi"><div class="k">روز کامل</div><div class="v"><?php echo fa_num($cnt['complete']); ?></div><div class="t">همهٔ کارهای روزانه ثبت شده</div></div>
    <div class="rkpi"><div class="k">روز ناقص</div><div class="v"><?php echo fa_num($cnt['partial']); ?></div><div class="t">بخشی ثبت شده</div></div>
    <div class="rkpi"><div class="k">روز بی‌ثبت</div><div class="v"><?php echo fa_num($cnt['none']); ?></div><div class="t">بدون عدد و بدون سرزنش</div></div>
    <div class="rkpi"><div class="k">روزهای ماه</div><div class="v"><?php echo fa_num($b['days']); ?></div><div class="t">تا امروز: <?php echo fa_num($cnt['complete'] + $cnt['partial'] + $cnt['none']); ?></div></div>
  </div>
  <?php } ?>

  <?php if ($showStats && $daysWithData > 0) { ?>
  <div class="row" style="gap:8px;flex-wrap:wrap;margin-top:10px">
    <span class="chip g">روزهایی که چیزی ثبت کرده‌ای: <?php echo fa_num($daysWithData); ?></span>
    <?php if ($bestDay && $bestDay['events'] > 0) { ?>
      <span class="chip s">پرثبت‌ترین روز: <?php echo e(jalali_format($bestDay['date'])); ?> (<?php echo fa_num($bestDay['events']); ?> ثبت)</span>
    <?php } ?>
  </div>
  <?php } ?>

  <p class="tiny" style="margin-top:8px">تقویم فقط نشان می‌دهد چه روزی چه‌قدر ثبت شده — نه اینکه آن روز «خوب» یا «بد» بوده است.</p>
</section>
<?php
}
