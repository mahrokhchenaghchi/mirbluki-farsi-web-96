<?php
/**
 * JOMA — کارهای امروز — طرح نسخهٔ ۲۰
 * قواعد دست‌نخورده: فقط در دورهٔ RUNNING · داخل دوره · آینده بسته (B3) ·
 * روزانه یک ثبت در هر روز · همان فیلدهای POST (pa_id, date, value).
 */
require_login();
require_perm('RECORD_PERFORMANCE');
$u = current_user();
$key = current_period_key();
$wp = ensure_period($u['id'], $key);
$plan = $wp['plan'];
$b = jalali_period_bounds($key);
$date = jalali_in_period(jalali_today(), $key) ? jalali_today() : $wp['period']['start_date'];
if (isset($_GET['date']) && jalali_is_valid($_GET['date'])) $date = $_GET['date'];
if (isset($_POST['date']) && jalali_is_valid($_POST['date'])) $date = $_POST['date'];
$msg = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pa_id'])) {
    csrf_check();
    $pas = list_plan_activities($plan['id'], $u['id']);
    $pa = null;
    foreach ($pas as $x) if ((int) $x['id'] === (int) $_POST['pa_id']) $pa = $x;
    if ($pa) {
        // (B6) آبِ امروز: اگر رکورد همان روز هست و پیش‌نویس است → ویرایش؛
        // اگر رکورد هست و قطعی است → خطا؛ اگر رکورد نیست → ثبت تازه (پیش‌نویس).
        $isWater = (isset($pa['activity_code']) && $pa['activity_code'] === 'ACT002');
        $wdate = isset($_POST['date']) ? $_POST['date'] : '';
        $hasRec = false;
        if ($isWater) {
            foreach (list_events($plan['id'], $u['id']) as $e) {
                if ((int) $e['plan_activity_id'] === (int) $pa['id'] && $e['performance_date'] === $wdate) { $hasRec = true; break; }
            }
        }
        $wantDraftEdit = (isset($_POST['water_action']) && $_POST['water_action'] === 'draft_save');
        if ($isWater && $hasRec && $wantDraftEdit && function_exists('joma_water_update_draft')) {
            $msg = joma_water_update_draft($u['id'], $plan, $pa, $wdate, isset($_POST['value']) ? $_POST['value'] : '');
            if ($msg === '') $ok = 'پیش‌نویس به‌روز شد.';
        } else {
            $msg = register_performance($u['id'], $plan, $pa, $wdate, isset($_POST['value']) ? $_POST['value'] : '');
            if ($msg === '' && $isWater) {
                if ($wdate === jalali_today() && function_exists('joma_water_mark_draft')) {
                    joma_water_mark_draft($u['id'], $wdate, isset($_POST['value']) ? $_POST['value'] : '');
                    $ok = 'به‌عنوان پیش‌نویس ذخیره شد. هر وقت خواستی «ثبت نهایی» کن.';
                } else {
                    $ok = 'ثبت شد.'; // ثبت روزهای گذشته = قطعی (بدون وضعیت ذخیره‌شده)
                }
            } elseif ($msg === '') {
                $ok = 'ثبت شد.';
            }
        }
        // (C1) تب تناوبِ همان فعالیت پس از ثبت فعال می‌ماند تا نتیجهٔ ثبت دیده شود.
        if ($msg === '' && isset($pa['frequency'])
            && in_array($pa['frequency'], array('DAILY', 'WEEKLY', 'MONTHLY'), true)) {
            $_SESSION['joma_today_freq'] = $pa['frequency'];
        }
    }
}

// (B6) پاسخ JSON برای اقدام‌های آب (تا لیوان‌ها بدون بارگذاری صفحه کار کنند)
$__ajax = (isset($_POST['ajax']) && (string) $_POST['ajax'] === '1');
$__json_out = function ($payload) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
};

// (B6) قطعی‌کردن ثبت آب امروز
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['water_action']) && $_POST['water_action'] === 'finalize') {
    csrf_check();
    $wdate = (isset($_POST['date']) && jalali_is_valid($_POST['date'])) ? $_POST['date'] : jalali_today();
    if (function_exists('joma_water_finalize')) {
        $has = false;
        $wpas = list_plan_activities($plan['id'], $u['id']);
        foreach ($wpas as $x) {
            if (!isset($x['activity_code']) || $x['activity_code'] !== 'ACT002') continue;
            foreach (list_events($plan['id'], $u['id']) as $e) {
                if ((int) $e['plan_activity_id'] === (int) $x['id'] && $e['performance_date'] === $wdate) { $has = true; break 2; }
            }
        }
        $res = joma_water_finalize($u['id'], $wdate, $has);
        if (!empty($res['ok'])) { $ok = 'ثبت آب قطعی شد.'; } else { $msg = $res['error']; }
    }
}

// (B6) اگر درخواست از نوع ajax بود، همان‌جا وضعیت تازه را برگردان (بدون رندر کل صفحه)
if ($__ajax) {
    $__pas = list_plan_activities($plan['id'], $u['id']);
    $__evs = list_events($plan['id'], $u['id']);
    $__wpa = null;
    foreach ($__pas as $x) if (isset($x['activity_code']) && $x['activity_code'] === 'ACT002') $__wpa = $x;
    $__wdate = (isset($_POST['date']) && jalali_is_valid($_POST['date'])) ? $_POST['date'] : jalali_today();
    $__sum = ($__wpa && function_exists('joma_water_day_summary')) ? joma_water_day_summary($u['id'], $__wdate, $__evs, $__wpa) : null;
    $__done = 0;
    foreach ($__pas as $a2) {
        $rel = events_for($__evs, $a2['id']);
        if ($a2['frequency'] === 'DAILY' && has_daily_registration($rel, $__wdate)) $__done++;
    }
    $__json_out(array(
        'ok' => ($msg === ''),
        'message' => ($msg !== '') ? $msg : (($ok !== '') ? $ok : 'ثبت شد.'),
        'state' => array(
            'value' => $__sum ? (float) $__sum['value'] : 0,
            'target' => $__sum ? (float) $__sum['target'] : 0,
            'has' => $__sum ? (bool) $__sum['has'] : false,
            'is_draft' => $__sum ? (bool) $__sum['is_draft'] : false,
            'is_final' => $__sum ? (bool) $__sum['is_final'] : false,
            'label' => $__sum ? (($__sum['has'] ? fa_num($__sum['value']) . ' از هدف ' . fa_num($__sum['target']) . ' لیوان' : ('هنوز چیزی ثبت نشده — هدف امروز ' . fa_num($__sum['target']) . ' لیوان'))) : '',
            'done_count' => $__done,
            'total_count' => count($__pas),
        ),
    ));
}
$pas = list_plan_activities($plan['id'], $u['id']);
$evs = list_events($plan['id'], $u['id']);
$groups = array('DAILY' => array(), 'WEEKLY' => array(), 'MONTHLY' => array());
foreach ($pas as $a) $groups[$a['frequency']][] = $a;
$labels = array('DAILY' => 'روزانه', 'WEEKLY' => 'هفتگی', 'MONTHLY' => 'ماهانه');
$todayCap = jalali_today();
$view = (isset($_GET['view']) && $_GET['view'] === 'month') ? 'month' : 'today';

// ------------------------------------------------------------------
// (C1) تب‌های تناوب — روزانه · هفتگی · ماهانه (SPEC/12 §۳)
//   • شمارش sup از بک‌اند (تعداد فعالیت‌های همان تناوب در همین برنامه)
//   • تب خالی: disabled + دلیل، بدون نشانگر شمارش
//   • تب پیش‌فرض: روزانه؛ اگر خالی بود، اولین تب غیرخالی
//   • تب انتخابی در نشست می‌ماند
//   • کارت لیوان فقط در تب روزانه دیده می‌شود
// ------------------------------------------------------------------
$freqTabs = array('DAILY' => 'روزانه', 'WEEKLY' => 'هفتگی', 'MONTHLY' => 'ماهانه');
$freqWhy = array(
    'DAILY' => 'در برنامه‌ات فعالیت روزانه نداری.',
    'WEEKLY' => 'در برنامه‌ات فعالیت هفتگی نداری.',
    'MONTHLY' => 'در برنامه‌ات فعالیت ماهانه نداری.',
);
$freqCounts = array();
foreach ($freqTabs as $fk => $flbl) $freqCounts[$fk] = isset($groups[$fk]) ? count($groups[$fk]) : 0;
$activeFreq = '';
if (isset($_GET['f']) && isset($freqTabs[$_GET['f']]) && $freqCounts[$_GET['f']] > 0) {
    $activeFreq = $_GET['f'];
    $_SESSION['joma_today_freq'] = $activeFreq;
} elseif (isset($_SESSION['joma_today_freq']) && isset($freqTabs[$_SESSION['joma_today_freq']]) && $freqCounts[$_SESSION['joma_today_freq']] > 0) {
    $activeFreq = $_SESSION['joma_today_freq'];
} else {
    foreach ($freqTabs as $fk => $flbl) {
        if ($freqCounts[$fk] > 0) { $activeFreq = $fk; break; }
    }
    if ($activeFreq !== '') $_SESSION['joma_today_freq'] = $activeFreq;
}
$freqEmptyReasons = array();
foreach ($freqTabs as $fk => $flbl) {
    if ($freqCounts[$fk] === 0) $freqEmptyReasons[] = $freqWhy[$fk];
}

// (B6) فعالیت آب این برنامه و وضعیت همان روز
$waterPa = null;
foreach ($pas as $x) if (isset($x['activity_code']) && $x['activity_code'] === 'ACT002') $waterPa = $x;
$waterSum = null;
if ($waterPa && function_exists('joma_water_day_summary')) {
    $waterSum = joma_water_day_summary($u['id'], $date, $evs, $waterPa);
}

$doneCount = 0;
foreach ($pas as $a) {
    $rel = events_for($evs, $a['id']);
    if ($a['frequency'] === 'DAILY' && has_daily_registration($rel, $date)) $doneCount++;
}
$moodToday = function_exists('get_mood') ? get_mood($u['id'], $todayCap) : null;
$stressToday = $moodToday ? (int) $moodToday['stress'] : 0;

joma_header('کارهای امروز', array());
?>
<?php joma_v2_pagehead('کارهای امروز', 'ثبت کارها', 'دورهٔ ' . jalali_period_label($plan['period_key']) . ' — هر ثبت، یک قدم واقعی است.', '<span>' . e(jalali_weekday_name($date) . ' ' . jalali_format($date)) . '</span>'); ?>

<div class="viewtabs">
  <a class="<?php echo ($view === 'today') ? 'on' : ''; ?>" href="<?php echo e(joma_url('index.php?p=today')); ?>">امروز</a>
  <a class="<?php echo ($view === 'month') ? 'on' : ''; ?>" href="<?php echo e(joma_url('index.php?p=today&view=month')); ?>">مسیر این ماه</a>
</div>

<?php if ($view === 'month') { ?>
  <?php
  if (function_exists('joma_v2_month_calendar')) {
      joma_v2_month_calendar((int) $u['id'], $plan, $pas, $evs, list_moods($u['id'], $b['start'], $b['end']), array('title' => true, 'counters' => true, 'stats' => true));
  }
  ?>
  <?php joma_footer(); return; ?>
<?php } ?>

<?php /* (C1) تب‌های تناوب — همان سه تب «روزانه · هفتگی · ماهانه» */ ?>
<div class="ftabs" role="tablist" aria-label="تناوب فعالیت‌ها">
  <?php foreach ($freqTabs as $fk => $flbl) {
      $cnt = (int) $freqCounts[$fk];
      if ($cnt === 0) {
          echo '<span class="ftab dis" role="tab" aria-disabled="true" aria-label="' . e($flbl . ' — ' . $freqWhy[$fk]) . '" title="' . e($freqWhy[$fk]) . '">' . e($flbl) . '</span>';
      } else {
          echo '<a class="ftab' . (($activeFreq === $fk) ? ' on' : '') . '" role="tab" aria-selected="' . (($activeFreq === $fk) ? 'true' : 'false') . '" '
             . 'href="' . e(joma_url('index.php?p=today&f=' . $fk . (($date !== $todayCap) ? '&date=' . $date : ''))) . '">'
             . e($flbl) . ' <sup class="num">' . fa_num($cnt) . '</sup></a>';
      }
  } ?>
</div>
<?php if ($freqEmptyReasons) { ?>
  <p class="tiny ftab-why"><?php echo e(implode(' ', $freqEmptyReasons)); ?></p>
<?php } ?>

<?php if (!$pas) { ?>
  <section class="card popin" style="display:flex;gap:12px;align-items:center">
    <span class="av" style="flex:none;width:44px;height:44px;border-radius:50%;background:var(--brand-softer);display:grid;place-items:center">
      <?php echo joma_v2_icon('owl-think', 'ic lg'); ?>
    </span>
    <div><b style="font-size:13px">هنوز فعالیتی در برنامه نیست</b>
      <p class="lede">از کتابخانه چند فعالیت انتخاب کن تا امروز هم قابل ثبت شود.</p></div>
    <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=library')); ?>">کتابخانه</a>
  </section>
<?php } ?>
<?php if ($plan['status'] !== 'RUNNING') { echo empty_state('دوره در حال اجرا نیست', 'اول برنامه را نهایی و اجرا را شروع کن.', joma_url('index.php?p=plan'), 'رفتن به برنامه'); } ?>

<?php if (strcmp($b['start'], $todayCap) > 0) { ?>
  <p class="card lede">ثبت عملکرد برای این دوره هنوز شروع نشده است.</p>
<?php } else { ?>
  <div class="card">
    <div class="row" style="justify-content:space-between">
      <div class="k">تاریخ ثبت</div>
      <span class="chip" data-done-count><?php echo fa_num($doneCount); ?> از <?php echo fa_num(count($pas)); ?> ثبت شده</span>
    </div>
    <div class="row" style="gap:6px;margin-top:8px;overflow-x:auto;flex-wrap:nowrap;padding-bottom:4px">
      <?php
      // (B3) فقط روزهای مجاز: از ابتدای دوره تا امروز — آینده هرگز در فهرست نمی‌آید.
      $chipDays = array();
      for ($d = 1; $d <= $b['days']; $d++) {
          $ds = $b['year'] . '-' . jalali_pad($b['month']) . '-' . jalali_pad($d);
          if (strcmp($ds, $todayCap) > 0) break;
          $chipDays[] = $ds;
      }
      $chipDays = array_reverse($chipDays); // امروز اول، بعد روزهای قبل
      foreach ($chipDays as $ds) {
          $isOn = ($ds === $date);
          $isToday = ($ds === $todayCap);
          echo '<a class="chip ' . ($isOn ? 'g' : 'n') . '" style="flex:none" href="' . e(joma_url('index.php?p=today&date=' . $ds)) . '">'
             . ($isToday ? 'امروز' : e(jalali_format($ds)))
             . '</a>';
      } ?>
    </div>
    <p class="tiny" style="margin-top:6px">ثبت برای روزهای آینده بسته است. روزهای گذشتهٔ همین ماه باز است.</p>
  </div>
<?php } ?>

<?php if ($ok) echo '<p class="toast ok">' . e($ok) . '</p>'; ?>
<?php if ($msg) echo '<p class="toast bad">' . e($msg) . '</p>'; ?>

<?php /* (C1) کارت لیوان فقط در تب روزانه */ ?>
<?php if ($waterPa && $plan['status'] === 'RUNNING' && $activeFreq === 'DAILY') {
    $glasses = (int) $waterPa['target_value'];
    if ($glasses < 1) $glasses = 1;
    if ($glasses > 12) $glasses = 12; // کارت‌ها خوانا بمانند؛ مقدار واقعی همان بالاست
    $filled = (int) round((float) $waterSum['value']);
    $isDraft = !empty($waterSum['is_draft']);
    $isFinal = !empty($waterSum['is_final']);
?>
<section class="card water-card" id="water-card"
  data-pa="<?php echo e($waterPa['id']); ?>" data-date="<?php echo e($date); ?>"
  data-target="<?php echo (int) $glasses; ?>" data-value="<?php echo (int) $filled; ?>"
  data-draft="<?php echo $isDraft ? '1' : '0'; ?>" data-final="<?php echo $isFinal ? '1' : '0'; ?>">
  <?php echo csrf_field(); ?>
  <div class="row" style="justify-content:space-between">
    <div class="k">آب امروز<?php echo ($date !== $todayCap) ? ' — ' . e(jalali_format($date)) : ''; ?></div>
    <div class="wrow">
      <button type="button" class="snd-btn" data-water-sound="1" aria-pressed="true">
        <?php echo joma_v2_icon('i-vol', 'ic'); ?><span>صدای آب</span>
      </button>
      <?php if ($isDraft) { ?><span class="chip go" data-water-badge>پیش‌نویس</span>
      <?php } elseif ($isFinal) { ?><span class="chip g" data-water-badge>قطعی</span>
      <?php } else { ?><span class="chip n" data-water-badge>ثبت نشده</span><?php } ?>
    </div>
  </div>
  <p class="lede" style="margin-top:6px" data-water-label>
    <?php echo $waterSum['has']
        ? (fa_num($waterSum['value']) . ' از هدف ' . fa_num($waterSum['target']) . ' لیوان')
        : ('هنوز چیزی ثبت نشده — هدف امروز ' . fa_num($waterSum['target']) . ' لیوان'); ?>
  </p>
  <?php if ($isFinal) { ?>
    <div class="glasses" dir="ltr">
      <?php for ($i = 1; $i <= $glasses; $i++) {
          $on = ($i <= $filled);
          echo '<span class="glass' . ($on ? ' on' : '') . '" data-fill="' . $i . '" title="' . $i . ' لیوان">';
          echo function_exists('joma_v2_glass_svg') ? joma_v2_glass_svg($on, 30, $waterPa['id'] . '_' . $i) : '';
          echo '</span>';
      } ?>
    </div>
    <p class="tiny">این ثبت قطعی است و تغییر نمی‌کند.</p>
  <?php } else { ?>
    <div class="glasses" dir="ltr" data-water-glasses>
      <?php for ($i = 1; $i <= $glasses; $i++) {
          $on = ($i <= $filled);
          echo '<button type="button" class="glass' . ($on ? ' on' : '') . '" data-fill="' . $i . '" '
             . 'title="' . $i . ' لیوان" aria-label="' . $i . ' لیوان">';
          echo function_exists('joma_v2_glass_svg') ? joma_v2_glass_svg($on, 30, $waterPa['id'] . '_' . $i) : '';
          echo '</button>';
      } ?>
    </div>
    <p class="tiny">روی لیوان بزن تا همان تعداد ثبت شود؛ لمس دوبارهٔ همان لیوان، یکی کم می‌کند (بی‌صدا). موج آب داخل لیوان تکان می‌خورد. تا «ثبت نهایی» نشود، پیش‌نویس است و در سنجه‌ها شمرده نمی‌شود.</p>
  <?php } ?>
  <?php if ($isDraft) { ?>
    <form method="post" style="margin-top:8px" data-water-finalize="1">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="water_action" value="finalize">
      <input type="hidden" name="date" value="<?php echo e($date); ?>">
      <button class="btn soft" type="submit">ثبت نهایی آب امروز</button>
    </form>
  <?php } ?>
</section>
<?php } ?>

<?php /* تمرین تنفس — همیشه در دسترس؛ کارت پیشنهاد وقتی استرس بالاست پررنگ‌تر می‌شود */ ?>
<div class="card" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
  <span style="flex:none;width:44px;height:44px;border-radius:14px;background:var(--sky-soft);display:grid;place-items:center">
    <?php echo joma_v2_icon('i-lotus', 'ic lg'); ?>
  </span>
  <div style="flex:1;min-width:180px">
    <b style="font-size:13px">تمرین تنفس ۴-۷-۸</b>
    <p class="tiny">سه دور نفس آرام با راهنمای صوتی و انیمیشن. بدون امتیاز و بدون ثبت خودکار.</p>
  </div>
  <button class="btn sec sm" type="button" data-breath-open="1">شروع تمرین</button>
</div>
<div class="dlg" id="breath-sheet"><div class="dlg-panel">
  <div class="mob-head"><b>تنفس ۴-۷-۸</b>
    <span class="wrow">
      <button type="button" class="snd-btn" data-breath-sound="1" aria-pressed="false"><?php echo joma_v2_icon('i-vol', 'ic'); ?><span>صدای راهنما</span></button>
      <button class="btn ghost sm" type="button" data-breath-close="1">بستن</button>
    </span>
  </div>
  <div style="display:grid;place-items:center;padding:18px 0">
    <div id="breath-circle" style="width:220px;height:220px;border-radius:50%;background:var(--brand-softer);
      border:3px solid var(--brand2);display:grid;place-items:center;transition:transform 4s ease-in-out">
      <b id="breath-phase" style="font-size:15px">آماده‌ای؟</b>
    </div>
  </div>
  <p class="breath-sentence" id="breath-sentence">دکمه را بزن؛ سه دور نفس آرام.</p>
  <p class="lede" id="breath-hint">راهنمای صوتی — با هر مرحله، جملهٔ همان مرحله هم این‌جا نوشته می‌شود.</p>
  <div class="wrow" style="justify-content:center;margin-top:8px">
    <button class="btn" type="button" id="breath-start">شروع تمرین</button>
    <span class="chip n" id="breath-round">دور ۰ از ۳</span>
  </div>
  <p class="tiny" style="margin-top:10px">این تمرین هیچ سنجه‌ای را پر نمی‌کند و هیچ پاداشی ندارد.</p>
</div></div>
<?php if ($stressToday >= 4) { ?>
<section class="card" style="display:flex;gap:12px;align-items:center;border:1.5px solid var(--sky)">
  <span style="flex:none;width:44px;height:44px;border-radius:14px;background:var(--sky-soft);display:grid;place-items:center">
    <?php echo joma_v2_icon('i-lotus', 'ic lg'); ?>
  </span>
  <div style="flex:1;min-width:0">
    <b style="font-size:13px">امروز استرست بالاست (<?php echo fa_num($stressToday); ?> از ۵)</b>
    <p class="tiny">یک تمرین کوتاه تنفس ۴-۷-۸ می‌تواند کمکت کند. بدون امتیاز، بدون ثبت خودکار.</p>
  </div>
  <button class="btn sec sm" type="button" data-breath-open="1">تمرین تنفس</button>
</section>
<?php } ?>

<?php if ($activeFreq !== '') { ?>
  <h2><?php echo e($freqTabs[$activeFreq]); ?></h2>
  <div class="grid grid-2">
  <?php foreach ($groups[$activeFreq] as $a) {
      $related = events_for($evs, $a['id']);
      $sum = displayed_actual($a, $evs, $date);
      $dailyLocked = $a['frequency'] === 'DAILY' && has_daily_registration($related, $date);
      $last = null;
      foreach ($related as $e) if ($e['performance_date'] === $date) $last = $e;
      ?>
    <article class="card perf act-card">
      <div class="act-head">
        <span class="sticker" style="background:<?php echo e($a['color']); ?>33"><?php echo e($a['sticker']); ?></span>
        <div style="flex:1;min-width:0">
          <b style="font-size:13.5px"><?php echo e($a['name']); ?></b>
          <p class="meta"><?php echo e($a['category']); ?> · <?php echo e($labels[$a['frequency']]); ?> · <?php echo e(datatypes_list()[$a['data_type']]); ?></p>
        </div>
        <span class="chip">اهمیت <?php echo e(weight_label($a['weight'])); ?></span>
      </div>

      <div class="act-body">
        <p class="lede">ثبت‌شده: <b><?php echo e(format_value($a['data_type'], $sum, $a['unit'])); ?></b> از هدف <?php echo e(format_value($a['data_type'], $a['target_value'], $a['unit'])); ?></p>

        <?php if ($plan['status'] === 'RUNNING') {
            if ($dailyLocked) { ?>
              <div class="done-box" style="margin-top:8px">ثبت شد ✓ <?php echo $last ? e(format_value($a['data_type'], $last['actual_value'], $a['unit'])) : ''; ?>
                <span class="tiny"> — برای همین روز ثبت شده است (قانون: روزانه یک ثبت در هر روز).</span></div>
            <?php } else { ?>
              <form method="post" style="margin-top:8px">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="pa_id" value="<?php echo e($a['id']); ?>">
                <input type="hidden" name="date" value="<?php echo e($date); ?>">
                <?php if ($a['data_type'] === 'BOOLEAN') { ?>
                  <div class="bool-grid">
                    <label class="mood-opt" style="flex:1"><input type="radio" name="value" value="1" checked><span class="moodbtn on">انجام شد</span></label>
                    <label class="mood-opt" style="flex:1"><input type="radio" name="value" value="0"><span class="moodbtn">انجام نشد</span></label>
                  </div>
                <?php } elseif ($a['data_type'] === 'RATING') {
                    $faces = array(1 => '😞', 2 => '😐', 3 => '🙂', 4 => '😊', 5 => '🤩'); ?>
                  <div class="rate-grid">
                    <?php foreach ($faces as $n => $f) { ?>
                      <label class="mood-opt"><input type="radio" name="value" value="<?php echo $n; ?>" <?php echo ($n === 3 ? 'checked' : ''); ?>><span><?php echo $f; ?></span></label>
                    <?php } ?>
                  </div>
                <?php } else { ?>
                  <input name="value" type="number" min="0" step="any" dir="ltr" required placeholder="<?php echo ($a['data_type'] === 'DURATION' ? 'مدت' : 'مقدار'); ?>">
                <?php } ?>
                <p style="margin-top:8px"><button class="btn btn-block" type="submit">ثبت عملکرد</button></p>
              </form>
            <?php }
        } ?>

        <?php if ($related) { ?>
          <details style="margin-top:8px">
            <summary class="lede">تاریخچهٔ این فعالیت (<?php echo fa_num(count($related)); ?>)</summary>
            <ul style="margin:6px 0 0;padding-inline-start:18px">
              <?php
              $shown = 0;
              foreach (array_reverse($related) as $e) {
                  if ($shown++ >= 8) break;
                  echo '<li class="tiny">' . e(jalali_format($e['performance_date'])) . ' · ' . e(format_value($a['data_type'], $e['actual_value'], $a['unit'])) . '</li>';
              } ?>
            </ul>
          </details>
        <?php } ?>
      </div>
    </article>
  <?php } ?>
  </div>
<?php } ?>

<?php
// تکمیل همهٔ کارهای امروز: جغد cheer + انیمیشن popin (طبق سند ۱۱ §۸)
$__todayRemaining = 0;
foreach ($pas as $a2) {
    $rel2 = events_for($evs, $a2['id']);
    if ($a2['frequency'] !== 'DAILY' || !has_daily_registration($rel2, $date)) $__todayRemaining++;
}
if ($pas && $plan['status'] === 'RUNNING' && $__todayRemaining === 0) { ?>
  <section class="card popin" style="display:flex;gap:12px;align-items:center;border:1.5px solid var(--brand2);background:linear-gradient(150deg,#FFFFFF,var(--brand-softer))">
    <span class="av owl-float" style="flex:none;width:52px;height:52px;border-radius:50%;background:var(--card);display:grid;place-items:center;box-shadow:var(--shadow-sm)">
      <?php echo joma_v2_icon('owl-cheer', 'ic lg'); ?>
    </span>
    <div style="flex:1;min-width:0">
      <b style="font-size:13.5px">کارهای این روز کامل ثبت شد</b>
      <p class="lede">همهٔ فعالیت‌های روزانهٔ این روز ثبت شده‌اند. فردا هم همین‌جا منتظرت هستم.</p>
    </div>
    <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=reports')); ?>">دیدن روند</a>
  </section>
<?php } ?>
<?php if (!$pas) { echo empty_state('برنامهٔ این ماه خالی است', 'اول چند فعالیت به برنامه اضافه کن.', joma_url('index.php?p=plan'), 'برنامهٔ من'); } ?>
<?php joma_footer(); ?>
