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
    }
}

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
$pas = list_plan_activities($plan['id'], $u['id']);
$evs = list_events($plan['id'], $u['id']);
$groups = array('DAILY' => array(), 'WEEKLY' => array(), 'MONTHLY' => array());
foreach ($pas as $a) $groups[$a['frequency']][] = $a;
$labels = array('DAILY' => 'روزانه', 'WEEKLY' => 'هفتگی', 'MONTHLY' => 'ماهانه');
$todayCap = jalali_today();

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

<?php if ($plan['status'] !== 'RUNNING') { echo empty_state('دوره در حال اجرا نیست', 'اول برنامه را نهایی و اجرا را شروع کن.', joma_url('index.php?p=plan'), 'رفتن به برنامه'); } ?>

<?php if (strcmp($b['start'], $todayCap) > 0) { ?>
  <p class="card lede">ثبت عملکرد برای این دوره هنوز شروع نشده است.</p>
<?php } else { ?>
  <div class="card">
    <div class="row" style="justify-content:space-between">
      <div class="k">تاریخ ثبت</div>
      <span class="chip"><?php echo fa_num($doneCount); ?> از <?php echo fa_num(count($pas)); ?> ثبت شده</span>
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

<?php if ($waterPa && $plan['status'] === 'RUNNING') {
    $glasses = (int) $waterPa['target_value'];
    if ($glasses < 1) $glasses = 1;
    if ($glasses > 12) $glasses = 12; // کارت‌ها خوانا بمانند؛ مقدار واقعی همان بالاست
    $filled = (int) round((float) $waterSum['value']);
    $isDraft = !empty($waterSum['is_draft']);
    $isFinal = !empty($waterSum['is_final']);
?>
<section class="card water-card">
  <div class="row" style="justify-content:space-between">
    <div class="k">آب امروز<?php echo ($date !== $todayCap) ? ' — ' . e(jalali_format($date)) : ''; ?></div>
    <?php if ($isDraft) { ?><span class="chip go">پیش‌نویس</span>
    <?php } elseif ($isFinal) { ?><span class="chip g">قطعی</span>
    <?php } else { ?><span class="chip n">ثبت نشده</span><?php } ?>
  </div>
  <p class="lede" style="margin-top:6px">
    <?php echo $waterSum['has']
        ? (fa_num($waterSum['value']) . ' از هدف ' . fa_num($waterSum['target']) . ' لیوان')
        : ('هنوز چیزی ثبت نشده — هدف امروز ' . fa_num($waterSum['target']) . ' لیوان'); ?>
  </p>
  <div class="glasses" dir="ltr">
    <?php for ($i = 1; $i <= $glasses; $i++) {
        $on = ($i <= $filled);
        $isLast = ($i === $filled);
        $action = ($isDraft && $isLast) ? 'draft_save' : 'draft_save';
        echo '<form method="post" class="glass-form">' . csrf_field();
        echo '<input type="hidden" name="pa_id" value="' . e($waterPa['id']) . '">';
        echo '<input type="hidden" name="date" value="' . e($date) . '">';
        echo '<input type="hidden" name="value" value="' . $i . '">';
        if (!($isDraft && $isLast)) echo '<input type="hidden" name="water_action" value="draft_save">';
        else echo '<input type="hidden" name="water_action" value="draft_save">';
        echo '<button class="glass' . ($on ? ' on' : '') . '" type="submit" title="' . $i . ' لیوان" aria-label="' . $i . ' لیوان">';
        echo function_exists('joma_v2_glass_svg') ? joma_v2_glass_svg($on, 30) : '';
        echo '</button></form>';
    } ?>
  </div>
  <p class="tiny">هر لیوان را بزن تا همان تعداد ذخیره شود. ثبت امروز «پیش‌نویس» است و در سنجه‌ها شمرده نمی‌شود تا قطعی کنی.</p>
  <?php if ($isDraft) { ?>
    <form method="post" style="margin-top:8px">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="water_action" value="finalize">
      <input type="hidden" name="date" value="<?php echo e($date); ?>">
      <button class="btn soft" type="submit">ثبت نهایی آب امروز</button>
    </form>
  <?php } elseif ($isFinal) { ?>
    <p class="tiny">این ثبت قطعی است و تغییر نمی‌کند.</p>
  <?php } ?>
</section>
<?php } ?>

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
<div class="mob-sheet" id="breath-sheet"><div class="inner" style="text-align:center">
  <div class="mob-head"><b>تنفس ۴-۷-۸</b><button class="btn ghost sm" type="button" data-breath-close="1">بستن</button></div>
  <div style="display:grid;place-items:center;padding:18px 0">
    <div id="breath-circle" style="width:170px;height:170px;border-radius:50%;background:var(--brand-softer);
      border:3px solid var(--brand2);display:grid;place-items:center;transition:transform 4s ease-in-out">
      <b id="breath-phase" style="font-size:15px">آماده‌ای؟</b>
    </div>
  </div>
  <p class="lede" id="breath-hint">دکمه را بزن؛ سه دور نفس آرام.</p>
  <div class="btn-row" style="justify-content:center;margin-top:8px">
    <button class="btn" type="button" id="breath-start">شروع تمرین</button>
  </div>
  <p class="tiny" style="margin-top:10px">این تمرین هیچ سنجه‌ای را پر نمی‌کند و هیچ پاداشی ندارد.</p>
</div></div>
<?php } ?>

<?php foreach ($groups as $fk => $list) { ?>
  <h2><?php echo e($labels[$fk]); ?></h2>
  <?php if (!$list) { echo '<p class="lede">فعالیتی با این تناوب نیست.</p>'; continue; } ?>
  <div class="grid grid-2">
  <?php foreach ($list as $a) {
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

<?php if (!$pas) { echo empty_state('برنامهٔ این ماه خالی است', 'اول چند فعالیت به برنامه اضافه کن.', joma_url('index.php?p=plan'), 'برنامهٔ من'); } ?>
<?php joma_footer(); ?>
