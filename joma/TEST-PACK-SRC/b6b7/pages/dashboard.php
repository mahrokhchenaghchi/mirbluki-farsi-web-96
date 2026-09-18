<?php
/**
 * JOMA — خانهٔ من (داشبورد) — طرح نسخهٔ ۲۰
 * ترتیب سند ۱۱: سلام و وضعیت · کارت «قدم بعدی» · حال امروز · چهار عدد وضعیت ·
 * قدم‌های امروز / جوجه · برنامهٔ این ماه.
 * هیچ عددی ساخته نمی‌شود؛ هرچه نشان داده می‌شود از ثبت واقعی کاربر است.
 */
require_login();
require_perm('VIEW_DASHBOARD');
$u = current_user();
$today = jalali_today();
$key = current_period_key();
$wp = ensure_period($u['id'], $key);
$plan = $wp['plan'];
$b = jalali_period_bounds($key);
$acts = list_plan_activities($plan['id'], $u['id']);
$evs = list_events($plan['id'], $u['id']);
$mood = get_mood($u['id'], $today);
$moods = list_moods($u['id'], $b['start'], $b['end']);

$moodFaces = array(
    'energy' => array('😴', '😐', '🙂', '😄', '⚡'),
    'general_mood' => array('😞', '😐', '🙂', '😊', '🤩'),
    'focus' => array('🌫️', '😐', '🙂', '🎯', '🧠'),
    'sleep_quality' => array('😫', '😐', '🙂', '😴', '✨'),
    'stress' => array('😌', '🙂', '😐', '😟', '😣'),
);
$moodLabels = array(
    'energy' => 'انرژی', 'general_mood' => 'حال عمومی', 'focus' => 'تمرکز',
    'sleep_quality' => 'خواب', 'stress' => 'استرس',
);

$remaining = 0;
foreach ($acts as $a) {
    $rel = events_for($evs, $a['id']);
    if ($a['frequency'] !== 'DAILY' || !has_daily_registration($rel, $today)) $remaining++;
}
$doneDays = array();
foreach ($evs as $e) $doneDays[$e['performance_date']] = true;
$doneToday = 0;
foreach ($acts as $a) {
    $rel = events_for($evs, $a['id']);
    if ($a['frequency'] === 'DAILY' && has_daily_registration($rel, $today)) $doneToday++;
}

/* جوجه (fail-closed) */
$j = null;
$__jfn = dirname(__FILE__) . '/../functions/jooje.php';
if (is_file($__jfn)) {
    include_once $__jfn;
    if (function_exists('jooje_state')) {
        try { $st = jooje_state((int) $u['id']); if (!empty($st['enabled'])) $j = $st; } catch (Throwable $e) { $j = null; }
    }
}
$eval = array();
if (is_file(dirname(__FILE__) . '/../includes/v2_chick.php')) require_once dirname(__FILE__) . '/../includes/v2_chick.php';

/* پیام تک‌خطی جغد (از وضعیت واقعی روز) */
$owlMsg = '';
if (!$acts) {
    $owlMsg = 'بیا از ساختن اولین برنامهٔ این ماه شروع کنیم 🥚';
} elseif ($plan['status'] !== 'RUNNING') {
    $owlMsg = 'برنامه آماده است؛ برای شروع ثبت، نهایی‌سازی و شروع اجرا لازم است.';
} elseif (!$mood) {
    $owlMsg = 'حال امروزت را ثبت نکرده‌ای؛ چند ثانیه وقت می‌گیرد 💚';
} elseif ($remaining > 0) {
    $owlMsg = fa_num($remaining) . ' کار از امروز مانده؛ یکی‌یکی جلو برویم 🌱';
} else {
    $owlMsg = 'امروز کارهایت را ثبت کردی؛ خیالم راحت است 🌿';
}

joma_header('خانهٔ من', array());
?>
<?php joma_v2_pagehead('خانهٔ من', joma_v2_greeting($u), 'امروز هم می‌توانی با یک قدم کوچک شروع کنی.'); ?>

<section class="card hero-card" style="display:flex;align-items:center;gap:14px">
  <span style="flex:none;display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:18px;background:var(--brand-softer)">
    <?php echo joma_v2_icon(joma_v2_time_icon_id(), 'ic lg'); ?>
  </span>
  <div style="flex:1;min-width:0">
    <div class="row" style="justify-content:space-between">
      <b style="font-size:13.5px">امروز، <?php echo e(jalali_weekday_name($today)); ?></b>
      <span class="chip"><?php echo e(jalali_period_label($plan['period_key'])); ?></span>
    </div>
    <p class="lede" style="margin-top:4px"><?php echo e(jalali_format($today)); ?> — دورهٔ جاری تو</p>
    <div class="row" style="margin-top:6px"><?php echo status_badge($plan['status']); ?></div>
  </div>
</section>

<?php if ($owlMsg !== '') { ?>
<section class="card" style="display:flex;gap:10px;align-items:center">
  <span style="flex:none;width:40px;height:40px;border-radius:50%;background:var(--brand-softer);display:grid;place-items:center">
    <?php echo joma_v2_icon('owl-hi', 'ic lg'); ?>
  </span>
  <div><b style="font-size:12.5px">جوما</b><p class="lede"><?php echo e($owlMsg); ?></p></div>
</section>
<?php } ?>

<div class="grid grid-2">
  <?php
  /* ---------- کارت قدم بعدی (تنها اقدام اصلی صفحه) ---------- */
  $stepTitle = ''; $stepBody = ''; $stepHref = ''; $stepCta = '';
  if (!$acts) {
      $stepTitle = 'اول برنامه‌ات را بسازیم';
      $stepBody = 'از کتابخانه، فعالیت‌هایی که با زندگی‌ات جور است را به این ماه اضافه کن.';
      $stepHref = joma_url('index.php?p=library'); $stepCta = 'رفتن به کتابخانه';
  } elseif ($plan['status'] === 'DRAFT') {
      $stepTitle = 'برنامه را نهایی کن';
      $stepBody = 'با نهایی‌سازی، هدف و وزنِ همین ماه قفل می‌شود تا تاریخچه‌ات سالم بماند.';
      $stepHref = joma_url('index.php?p=plan'); $stepCta = 'رفتن به برنامه';
  } elseif ($plan['status'] === 'PLANNING') {
      $stepTitle = 'اجرای برنامه را شروع کن';
      $stepBody = 'وقتی شروع کنی، ثبت عملکرد برای همین دوره باز می‌شود.';
      $stepHref = joma_url('index.php?p=plan'); $stepCta = 'شروع اجرا';
  } elseif ($remaining > 0) {
      $stepTitle = 'کارهای امروز';
      $stepBody = fa_num($remaining) . ' کار برای امروز مانده است.';
      $stepHref = joma_url('index.php?p=today'); $stepCta = 'ثبت کارهای امروز';
  } elseif (!$mood) {
      $stepTitle = 'حال امروزت را ثبت کن';
      $stepBody = 'پنج شاخص کوتاه؛ بعدش الگوها در گزارش‌ها دیده می‌شوند.';
      $stepHref = joma_url('index.php?p=mood'); $stepCta = 'ثبت حال';
  } else {
      $stepTitle = 'امروز تمام است';
      $stepBody = 'همهٔ کارهای امروز ثبت شده. می‌توانی مسیر این ماه را ببینی.';
      $stepHref = joma_url('index.php?p=reports'); $stepCta = 'دیدن گزارش‌ها';
  }
  ?>
  <section class="card" style="border:1.5px solid var(--brand2);background:linear-gradient(150deg,#FFFFFF,var(--brand-softer))">
    <div class="k">قدم بعدی</div>
    <h2 style="margin-top:4px"><?php echo e($stepTitle); ?></h2>
    <p class="lede"><?php echo e($stepBody); ?></p>
    <div class="btn-row" style="margin-top:10px">
      <a class="btn" href="<?php echo e($stepHref); ?>"><?php echo e($stepCta); ?></a>
      <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=reports')); ?>">گزارش‌ها</a>
    </div>
  </section>

  <?php /* ---------- حال امروز ---------- */ ?>
  <section class="card">
    <div class="row" style="justify-content:space-between">
      <div class="k">حال امروز</div>
      <?php if ($mood) { ?><span class="chip g">ثبت شد</span><?php } else { ?><span class="chip go">ثبت نشده</span><?php } ?>
    </div>
    <?php if ($mood) { ?>
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(74px,1fr));gap:8px;margin-top:8px">
        <?php foreach ($moodLabels as $f => $label) {
            $sc = (int) $mood[$f]; ?>
          <div style="text-align:center;background:var(--brand-softer);border-radius:var(--r-sm);padding:8px 4px">
            <div style="font-size:20px"><?php echo $sc ? $moodFaces[$f][$sc - 1] : '—'; ?></div>
            <div class="tiny"><?php echo e($label); ?></div>
          </div>
        <?php } ?>
      </div>
      <div class="btn-row" style="margin-top:10px">
        <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=mood')); ?>">ویرایش حال امروز</a>
      </div>
    <?php } else { ?>
      <p class="lede" style="margin-top:6px">هنوز ثبت نشده. پنج شاخص کوتاه است و بعداً در گزارش‌ها کنار کارهایت دیده می‌شود.</p>
      <div class="btn-row" style="margin-top:10px">
        <a class="btn sm" href="<?php echo e(joma_url('index.php?p=mood')); ?>">ثبت حال امروز</a>
      </div>
    <?php } ?>
    <p class="tiny">یادداشتت خصوصی است و برای هم‌مسیر فرستاده نمی‌شود.</p>
  </section>
</div>

<?php /* ---------- چهار عدد وضعیت ---------- */ ?>
<div class="grid grid-3">
  <div class="card stat"><div class="k">فعالیت‌های برنامه</div><div class="v"><?php echo fa_num(count($acts)); ?></div><p class="tiny">در دورهٔ <?php echo e(jalali_period_label($key)); ?></p></div>
  <div class="card stat"><div class="k">ثبت‌شدهٔ امروز</div><div class="v"><?php echo fa_num($doneToday); ?></div><p class="tiny">از <?php echo fa_num(count($acts)); ?> فعالیت برنامه</p></div>
  <div class="card stat"><div class="k">روزهای با ثبت</div><div class="v"><?php echo fa_num(count($doneDays)); ?></div><p class="tiny">در همین ماه — روز بی‌ثبت صفر حساب نمی‌شود</p></div>
  <div class="card stat"><div class="k">روزهای ثبت حال</div><div class="v"><?php echo fa_num(count($moods)); ?></div><p class="tiny">در همین ماه</p></div>
</div>

<?php /* ---------- قدم‌های امروز + جوجه ---------- */ ?>
<div class="grid grid-2">
  <section class="card">
    <div class="row" style="justify-content:space-between">
      <div class="k">قدم‌های امروز</div>
      <a class="tiny" href="<?php echo e(joma_url('index.php?p=today')); ?>">همه ←</a>
    </div>
    <?php if (!$acts) { ?>
      <p class="lede" style="margin-top:6px">هنوز فعالیتی در برنامه نیست.</p>
    <?php } else {
        $n = 0;
        foreach ($acts as $a) {
            if ($n++ >= 5) break;
            $rel = events_for($evs, $a['id']);
            $sum = displayed_actual($a, $evs, $today);
            $isDone = ($a['frequency'] === 'DAILY') ? has_daily_registration($rel, $today) : ($sum > 0);
            echo '<div class="hd-item" style="display:flex;align-items:center;gap:10px">';
            echo '<span class="sticker" style="background:' . e($a['color']) . '33">' . e($a['sticker']) . '</span>';
            echo '<div style="flex:1;min-width:0"><b style="font-size:12.5px">' . e($a['name']) . '</b>';
            echo '<p class="tiny">' . e(format_value($a['data_type'], $sum, $a['unit'])) . ' از ' . e(format_value($a['data_type'], $a['target_value'], $a['unit'])) . '</p></div>';
            echo $isDone ? '<span class="chip g">ثبت شد</span>' : '<span class="chip n">مانده</span>';
            echo '</div>';
        }
    } ?>
  </section>

  <section class="card">
    <?php if ($j) {
        $stageText = array('egg' => 'تخم', 'crack' => 'تخمِ ترک‌خورده', 'chick' => 'جوجه');
        $stateText = array('calm' => 'آرام', 'happy' => 'شاد', 'sleep' => 'خواب', 'faded' => 'کم‌رنگ', 'gray' => 'خاکستری');
        $sv = ($j['stage'] === 'chick' && !empty($j['pet_name'])) ? $j['pet_name'] : ($j['stage'] === 'chick' ? 'جوجهٔ من' : 'تخم جوما');
    ?>
      <div class="row" style="justify-content:space-between">
        <div class="k"><?php echo e($sv); ?></div>
        <span class="chip"><?php echo e(isset($stateText[$j['state']]) ? $stateText[$j['state']] : $j['state']); ?></span>
      </div>
      <div style="display:flex;gap:12px;align-items:center;margin-top:6px">
        <div style="flex:none"><?php echo joma_v2_pet_svg($j['stage'], $j['state'], 84); ?></div>
        <div style="flex:1;min-width:0">
          <p class="lede"><?php echo e(isset($stageText[$j['stage']]) ? $stageText[$j['stage']] : $j['stage']); ?> — <?php echo fa_num((int) $j['units']); ?> دونه</p>
          <?php if ($j['stage'] !== 'chick') { ?>
            <div class="bar" style="margin:6px 0"><i style="width:<?php
              $cr = (int) $j['thresholds']['crack_units']; $bt = (int) $j['thresholds']['birth_units'];
              $tot = ($j['stage'] === 'egg') ? max(1, $cr) : max(1, $bt - $cr);
              $have = ($j['stage'] === 'egg') ? (int) $j['units'] : ((int) $j['units'] - $cr);
              echo (int) round(min(1, $have / $tot) * 100);
            ?>%"></i></div>
            <p class="tiny"><?php echo fa_num((int) $j['units_to_birth']); ?> دونه تا تولد</p>
          <?php } elseif (!empty($j['growth'])) { ?>
            <div class="bar" style="margin:6px 0"><i style="width:<?php echo (int) round(((float) $j['growth']['progress']) * 100); ?>%"></i></div>
            <p class="tiny"><?php echo e($j['growth']['label']); ?><?php if (!empty($j['growth']['next_label'])) echo ' → ' . e($j['growth']['next_label']); ?></p>
          <?php } ?>
        </div>
      </div>
      <div class="btn-row" style="margin-top:10px">
        <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=jooje')); ?>">سر زدن به جوجه ←</a>
      </div>
    <?php } else { ?>
      <div class="k">جوجهٔ من</div>
      <p class="lede" style="margin-top:6px">این بخش همین حالا در دسترس نیست.</p>
    <?php } ?>
  </section>
</div>

<?php
// JOMA-HAMMASIR-BEGIN (کارت‌های هم‌مسیر در داشبورد — باگ ۸؛ فقط mode=admin/provider و flag روشن؛
// با Flag خاموش: فقط خواندن config ماژول، هیچ خروجی — D39/AC6.8)
try {
    $__hcfg = dirname(__FILE__) . '/../config/hammasir_config.php';
    if (is_file($__hcfg)) {
        $HAMMASIR_CONFIG = array();
        include $__hcfg;
        if (!empty($HAMMASIR_CONFIG['hammasir_enabled'])) {
            $__hfn = dirname(__FILE__) . '/../functions/hammasir.php';
            if (is_file($__hfn)) {
                include_once $__hfn;
                $__hmode = hammasir_active_mode();
                if ($__hmode === 'admin') {
                    echo '<section class="card"><h2>پنل مدیریت هم‌مسیر</h2>';
                    echo '<div class="btn-row">';
                    echo '<a class="btn" href="' . e(joma_url('index.php?p=hammasir')) . '">مدیریت همراهان</a>';
                    echo '<a class="btn sec" href="' . e(joma_url('index.php?p=hammasir')) . '">مدیران سیستم</a>';
                    echo '</div></section>';
                } elseif ($__hmode === 'provider') {
                    $__hpend = hammasir_links_by_provider((int) $u['id'], 'PENDING');
                    $__hact = hammasir_links_by_provider((int) $u['id'], 'ACTIVE');
                    $__hnp = is_array($__hpend) ? count($__hpend) : 0;
                    $__hna = is_array($__hact) ? count($__hact) : 0;
                    echo '<section class="card"><h2>میز کار مشاور</h2>';
                    echo '<div class="btn-row">';
                    echo '<span class="chip">درخواست‌های در انتظار: ' . (int) $__hnp . '</span>';
                    echo '<span class="chip">مراجعان فعال: ' . (int) $__hna . '</span>';
                    echo '<a class="btn" href="' . e(joma_url('index.php?p=hammasir')) . '">ورود به میز کار</a>';
                    echo '</div></section>';
                } elseif ($__hmode === 'client') {
                    // کارت دعوت هم‌مسیر در داشبورد (حکم PO — اصلاح نهایی Onboarding بخش ۱):
                    // فقط seen=0 و بدون «هیچ» لینک (باز یا بسته — بخش ۴)؛ فرم‌ها POST به p=hammasir.
                    // fail-closed: خطای خواندن → دعوت رندر نمی‌شود.
                    $__hstate = array('seen' => 1, 'intent' => 0);
                    $__hany = true;
                    try {
                        $__hstate = hammasir_onboarding_state((int) $u['id']);
                        $__hany = (hammasir_links_any_by_client((int) $u['id']) === true);
                    } catch (Throwable $e) {
                        $__hstate = array('seen' => 1, 'intent' => 0);
                        $__hany = true;
                    }
                    if ($__hstate['seen'] === 0 && !$__hany) {
                        echo '<section class="card">';
                        echo '<h2>آیا مایلید در این مسیر یک همراه داشته باشید؟</h2>';
                        echo '<p>' . e(hammasir_onboarding_text()) . '</p>';
                        echo '<div class="btn-row">';
                        echo '<form method="post" action="' . e(joma_url('index.php?p=hammasir')) . '">';
                        echo csrf_field();
                        echo '<input type="hidden" name="hammasir_action" value="onboarding_accept">';
                        echo '<button class="btn" type="submit">بله، انتخاب می‌کنم</button>';
                        echo '</form>';
                        echo '<form method="post" action="' . e(joma_url('index.php?p=hammasir')) . '">';
                        echo csrf_field();
                        echo '<input type="hidden" name="hammasir_action" value="onboarding_dismiss">';
                        echo '<button class="btn sec" type="submit">فعلاً نه</button>';
                        echo '</form>';
                        echo '</div></section>';
                    }
                }
            }
        }
        unset($HAMMASIR_CONFIG);
    }
    unset($__hcfg);
} catch (Throwable $e) {
    // fail-closed: هیچ کارتی؛ فقط لاگ امن با پیام ثابت (PC-4)
    error_log('hammasir dashboard kept silent: safe load failed.');
}
// JOMA-HAMMASIR-END
?>

<?php
/* (B7) پیش‌نمایش یک بینش از دفترچه — فقط اگر بینش واقعی وجود داشته باشد. */
$__ins = null;
try {
    $__ifn = dirname(__FILE__) . '/../functions/insight.php';
    if (is_file($__ifn)) {
        include_once $__ifn;
        if (function_exists('joma_insights_build')) {
            $__ib = joma_insights_build($plan, $acts, $evs, list_moods($u['id'], $b['start'], $b['end']), $today);
            if (!empty($__ib['insights'])) $__ins = $__ib['insights'][0];
        }
    }
} catch (Throwable $e) { $__ins = null; }
?>
<section class="card">
  <div class="row" style="justify-content:space-between">
    <div class="k">از دفترچهٔ جوما</div>
    <a class="tiny" href="<?php echo e(joma_url('index.php?p=journal')); ?>">دفترچه ←</a>
  </div>
  <?php if ($__ins) { ?>
    <p class="lede" style="margin-top:6px"><?php echo str_replace('**همراهی، دلیل نیست.**', '<b>همراهی، دلیل نیست.</b>', e($__ins['text'])); ?></p>
    <p class="tiny"><?php echo e($__ins['evidence']['sampleLabel']); ?> · برای دیدن شواهد، دفترچه را باز کن.</p>
  <?php } else { ?>
    <p class="lede" style="margin-top:6px">جوما هنوز چیز تازه‌ای برای گفتن ندارد؛ هرچه بیشتر ثبت کنی، تصویر روشن‌تر می‌شود.</p>
  <?php } ?>
</section>

<?php
/* (B6) خلاصهٔ خواندنی آب — نوشتن فقط در «کارهای امروز» */
$__wpa = null;
foreach ($acts as $a) if (isset($a['activity_code']) && $a['activity_code'] === 'ACT002') $__wpa = $a;
if ($__wpa && function_exists('joma_water_day_summary')) {
    $__ws = joma_water_day_summary($u['id'], $today, $evs, $__wpa);
?>
<section class="card">
  <div class="k">آب امروز</div>
  <p class="lede" style="margin-top:6px">
    <?php echo $__ws['has'] ? (fa_num($__ws['value']) . ' از هدف ' . fa_num($__ws['target']) . ' لیوان') : ('هنوز ثبت نشده — هدف ' . fa_num($__ws['target']) . ' لیوان'); ?>
    <?php if (!empty($__ws['is_draft'])) echo '<span class="chip go" style="margin-inline-start:6px">پیش‌نویس</span>'; ?>
    <?php if (!empty($__ws['is_final'])) echo '<span class="chip g" style="margin-inline-start:6px">قطعی</span>'; ?>
  </p>
  <div class="btn-row" style="margin-top:8px">
    <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت در کارهای امروز</a>
  </div>
</section>
<?php } ?>

<h2>برنامهٔ این ماه</h2>
<?php if (!$acts) {
    echo empty_state('هنوز فعالیتی به این دوره اضافه نشده', 'از کتابخانه به برنامهٔ این ماه اضافه کن.', joma_url('index.php?p=plan'), 'ساخت برنامه');
} else {
    echo '<div class="grid grid-2">';
    foreach ($acts as $a) {
        $sum = displayed_actual($a, $evs, $today);
        $pct = ((float) $a['target_value'] > 0) ? min(1, ((float) $sum / (float) $a['target_value'])) : 0;
        echo '<article class="card" style="display:flex;gap:12px;align-items:center">';
        echo '<div class="sticker" style="background:' . e($a['color']) . '33">' . e($a['sticker']) . '</div>';
        echo '<div style="flex:1;min-width:0">';
        echo '<strong style="font-size:13px">' . e($a['name']) . '</strong>';
        echo '<p class="meta">' . e(frequencies_list()[$a['frequency']]) . ' · ' . e(format_value($a['data_type'], $sum, $a['unit'])) . ' / ' . e(format_value($a['data_type'], $a['target_value'], $a['unit'])) . '</p>';
        echo '<div class="bar" style="margin-top:6px"><i style="width:' . (int) round($pct * 100) . '%"></i></div>';
        echo '</div></article>';
    }
    echo '</div>';
    echo '<div class="btn-row"><a class="btn sec" href="' . e(joma_url('index.php?p=plan')) . '">مدیریت برنامه</a>'
       . '<a class="btn ghost" href="' . e(joma_url('index.php?p=periods')) . '">دوره‌های من</a></div>';
} ?>
<?php joma_footer(); ?>
