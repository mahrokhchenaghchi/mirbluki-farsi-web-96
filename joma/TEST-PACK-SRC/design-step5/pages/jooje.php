<?php
/**
 * JOMA — جوجهٔ من — طرح نسخهٔ ۲۰
 * همهٔ اعداد از ثبت واقعی خود کاربر می‌آید (JOMA_JOOJE_V1)؛ هیچ حالتی از روی کلیک ساخته نمی‌شود.
 */
require_login();
$_jooje_fn = dirname(__FILE__) . '/../functions/jooje.php';
if (is_file($_jooje_fn)) require_once $_jooje_fn;
$_chick_fn = dirname(__FILE__) . '/../includes/v2_chick.php';
if (is_file($_chick_fn)) require_once $_chick_fn;
$u = current_user();
$jooje_msg = '';
$jooje_err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jooje_action']) && $_POST['jooje_action'] === 'name') {
    csrf_check();
    $res = function_exists('jooje_name_save') ? jooje_name_save((int) $u['id'], isset($_POST['pet_name']) ? $_POST['pet_name'] : '') : array('ok' => false, 'error' => 'این بخش در دسترس نیست.');
    if (!empty($res['ok'])) {
        $jooje_msg = 'نام ثبت شد: ' . $res['name'];
    } else {
        $jooje_err = isset($res['error']) ? $res['error'] : 'ثبت نام ممکن نشد.';
    }
}
$j = function_exists('jooje_state') ? jooje_state((int) $u['id']) : array('enabled' => false);
$jooje_title = (!empty($j['pet_name'])) ? $j['pet_name'] : 'جوجهٔ من';
joma_header($jooje_title, array());
?>
<?php if (empty($j['enabled'])) { ?>
  <div class="card"><h1>جوجهٔ من</h1><p class="lede">این بخش همین حالا در دسترس نیست.</p></div>
<?php } else {
  $state_labels = array('calm' => 'آرام', 'happy' => 'شاد', 'sleep' => 'خواب', 'faded' => 'کم‌رنگ', 'gray' => 'خاکستری');
  $stage_labels = array('egg' => 'تخم', 'crack' => 'تخمِ ترک‌خورده', 'chick' => 'جوجه');
  $state = isset($state_labels[$j['state']]) ? $state_labels[$j['state']] : $j['state'];
  $stage = isset($stage_labels[$j['stage']]) ? $stage_labels[$j['stage']] : $j['stage'];
  $state_note = array(
      'calm' => 'حالِ عادی. کنار توست.',
      'happy' => 'امروز ثبت کردی؛ سرحال است.',
      'sleep' => 'استراحت می‌کند.',
      'faded' => 'کم‌رنگ شده و منتظر توست. جوجه نه می‌میرد و نه بیمار می‌شود.',
      'gray' => 'خاکستری شده و منتظر توست. جوجه نه می‌میرد و نه بیمار می‌شود.',
  );
?>
<?php joma_v2_pagehead('جوجهٔ من', $jooje_title, 'همهٔ اعداد این صفحه از ثبت‌های واقعی خودت می‌آید.', '<span>' . e($stage) . ' · ' . e($state) . '</span>'); ?>

<?php if ($jooje_msg) echo '<p class="toast ok">' . e($jooje_msg) . '</p>'; ?>
<?php if ($jooje_err) echo '<p class="toast bad">' . e($jooje_err) . '</p>'; ?>

<div class="grid grid-2">
  <section class="card" style="padding:14px">
    <div class="row" style="justify-content:space-between">
      <div class="k">صحنه</div>
      <button type="button" class="snd-btn" id="pet-sound" aria-pressed="false">
        <?php echo joma_v2_icon('i-vol', 'ic'); ?><span>صدای جوجه</span>
      </button>
    </div>

    <?php $__stateForJs = $j['state']; ?>
    <div class="pet-stage pet-state-<?php echo e($__stateForJs); ?>" id="pet-stage"
         data-stage="<?php echo e($j['stage']); ?>" data-state="<?php echo e($__stateForJs); ?>"
         data-state-label="<?php echo e($state); ?>" role="button" tabindex="0"
         aria-label="جوجهٔ من — لمس کن">
      <span class="pet-art"><?php echo joma_v2_pet_svg($j['stage'], $__stateForJs, 168); ?></span>
      <?php if ($__stateForJs === 'sleep') { ?>
        <span class="pet-z" style="top:22px;inset-inline-end:28px">z</span>
        <span class="pet-z z2" style="top:34px;inset-inline-end:44px">z</span>
        <span class="pet-z z3" style="top:44px;inset-inline-end:58px">z</span>
      <?php } ?>
      <span class="pet-bubble" id="pet-bubble" aria-live="polite"></span>
    </div>

    <div class="pet-statline">
      <span class="chip g"><?php echo e($stage); ?></span>
      <span class="chip" data-pet-state-label><?php echo e($state); ?></span>
      <?php if (!empty($j['pet_name'])) { ?><span class="chip s"><?php echo e($j['pet_name']); ?></span><?php } ?>
    </div>
    <p class="lede" style="text-align:center;margin-top:8px"><?php echo e(isset($state_note[$j['state']]) ? $state_note[$j['state']] : ''); ?></p>
    <div class="pet-btns">
      <button type="button" class="btn sec sm" data-pet-sleep>بخوابانش</button>
      <span class="tiny" style="align-self:center">لمس جوجه بی‌پاداش است؛ هیچ دونه‌ای اضافه نمی‌کند.</span>
    </div>
    <?php if ($j['stage'] === 'chick') { ?>
      <p class="tiny">دونه‌ها: <b><?php echo fa_num((int) $j['units']); ?></b> — هر ثبت معتبر، یک دونه.</p>
    <?php } else { ?>
      <div class="bar" style="max-width:320px;margin:10px auto 4px"><i style="width:<?php
        $cr = (int) $j['thresholds']['crack_units']; $bt = (int) $j['thresholds']['birth_units'];
        $tot = ($j['stage'] === 'egg') ? max(1, $cr) : max(1, $bt - $cr);
        $have = ($j['stage'] === 'egg') ? (int) $j['units'] : ((int) $j['units'] - $cr);
        echo (int) round(min(1, $have / $tot) * 100);
      ?>%"></i></div>
      <p class="tiny"><?php echo fa_num((int) $j['units']); ?> دونه — <?php echo fa_num((int) $j['units_to_birth']); ?> دونه تا تولد (آستانه: <?php echo fa_num($bt); ?>)</p>
    <?php } ?>
  </section>

  <section class="card">
    <div class="k">مراقبت امروز</div>
    <?php foreach ($j['metrics'] as $m) {
        if (empty($m['available'])) continue; ?>
      <div style="margin-top:10px">
        <div class="row" style="justify-content:space-between">
          <b style="font-size:12.5px"><?php echo e($m['icon'] . ' ' . $m['label']); ?></b>
          <?php if ($m['key'] === 'water' && empty($m['has_source'])) { ?>
            <span class="chip n">در برنامه نیست</span>
          <?php } elseif ($m['key'] === 'water' && !empty($m['draft_today'])) { ?>
            <span class="chip go">پیش‌نویس</span>
          <?php } else { ?>
            <span class="chip g"><?php echo fa_num($m['today']); ?><?php if (!empty($m['unit'])) echo ' ' . e($m['unit']); ?></span>
          <?php } ?>
        </div>
        <?php if ($m['key'] === 'water' && empty($m['has_source'])) { ?>
          <p class="tiny">فعالیت آب در برنامه‌ات نیست، پس امروز چیزی برای شمردن ندارد — این «صفر» نیست.</p>
        <?php } elseif ($m['key'] === 'water' && !empty($m['draft_today'])) { ?>
          <p class="tiny">ثبت آب امروز هنوز پیش‌نویس است؛ در «کارهای امروز» قطعی‌اش کن تا در سنجهٔ آب شمرده شود. پیش‌نویس پاداش نمی‌سازد.</p>
        <?php } else { ?>
          <?php if (!empty($m['target']) && $m['progress'] !== null) { ?>
            <div class="bar" style="margin:6px 0"><i style="width:<?php echo (int) round(((float) $m['progress']) * 100); ?>%"></i></div>
            <p class="tiny"><?php
              if ($m['target_status'] === 'from_plan_snapshot') {
                  echo 'هدف از برنامهٔ همین ماه: ' . fa_num($m['target']) . (!empty($m['unit']) ? ' ' . e($m['unit']) : '');
              } elseif ($m['target_status'] === 'daily_binary') {
                  echo 'هدف امروز: یک ثبت حال';
              } else {
                  echo 'هدف: ' . fa_num($m['target']) . (!empty($m['unit']) ? ' ' . e($m['unit']) : '');
              }
            ?></p>
          <?php } else { ?>
            <p class="tiny">برای این سنجه هدف روزانه تعیین نشده؛ فقط عدد واقعی نمایش داده می‌شود.</p>
          <?php } ?>
        <?php } ?>
      </div>
    <?php } ?>
    <?php foreach ($j['metrics'] as $m) { if (!empty($m['available'])) continue; ?>
      <p class="tiny" style="margin-top:10px">سنجهٔ «<?php echo e($m['label']); ?>» تا ساخته‌شدن منبعش نمایش داده نمی‌شود.</p>
    <?php } ?>
  </section>
</div>

<?php if (!empty($j['growth'])) { ?>
<section class="card">
  <div class="row" style="justify-content:space-between">
    <div class="k">رشد</div>
    <span class="chip s"><?php echo e($j['growth']['label']); ?></span>
  </div>
  <div class="bar" style="margin:8px 0"><i style="width:<?php echo (int) round(((float) $j['growth']['progress']) * 100); ?>%"></i></div>
  <?php if (!empty($j['growth']['next_label'])) { ?>
    <p class="tiny">مرحلهٔ بعد: <?php echo e($j['growth']['next_label']); ?> — از <?php echo fa_num((int) $j['growth']['next_at_units']); ?> دونه.</p>
  <?php } else { ?>
    <p class="tiny">به آخرین مرحلهٔ رشد رسیده است.</p>
  <?php } ?>
</section>
<?php } ?>

<?php if ($j['stage'] === 'chick' && empty($j['pet_name']) && !empty($j['pet_name_can_set'])) { ?>
<section class="card" style="border:1.5px solid var(--brand2)">
  <b style="font-size:13.5px">حالا که به دنیا آمده، اسمش را چه بگذاریم؟</b>
  <p class="lede" style="margin-top:4px">۲ تا ۱۶ نویسه. یک‌بار می‌گذاری و یک‌بار می‌توانی عوضش کنی. اگر نامی نگذاری، «جوجهٔ من» می‌ماند.</p>
  <form method="post" style="margin-top:8px;max-width:360px">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="jooje_action" value="name">
    <input name="pet_name" maxlength="16" placeholder="مثلاً: جوجهٔ من" required>
    <p style="margin-top:8px"><button class="btn" type="submit">ثبت نام</button></p>
  </form>
</section>
<?php } ?>

<?php if ($j['stage'] === 'chick' && !empty($j['pet_name'])) { ?>
<section class="card">
  <div class="row" style="justify-content:space-between">
    <div class="k">نام جوجه</div>
    <b><?php echo e($j['pet_name']); ?></b>
  </div>
  <?php if (!empty($j['pet_name_can_change'])) { ?>
    <details style="margin-top:8px">
      <summary class="lede">عوض‌کردن نام (فقط همین یک‌بار)</summary>
      <form method="post" style="margin-top:8px;max-width:360px">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="jooje_action" value="name">
        <input name="pet_name" maxlength="16" required>
        <p style="margin-top:8px"><button class="btn sec" type="submit">مطمئنی؟ نام را عوض کن</button></p>
      </form>
    </details>
  <?php } else { ?>
    <p class="tiny">نام یک‌بار گذاشته و یک‌بار عوض می‌شود؛ نوبت تغییرش گذشته است.</p>
  <?php } ?>
  <p class="tiny">این نام فقط در همین صفحه دیده می‌شود — نه در کارت خانه و نه برای هم‌مسیر.</p>
</section>
<?php } ?>

<div class="grid grid-2">
  <section class="card">
    <div class="k">خط زمانی</div>
    <?php foreach ($j['timeline'] as $t) { ?>
      <div class="hd-item" style="display:flex;gap:8px;align-items:center">
        <span style="font-size:18px"><?php echo e($t['icon']); ?></span>
        <div style="flex:1"><b style="font-size:12.5px"><?php echo e($t['label']); ?></b>
          <?php if (!empty($t['date'])) echo '<p class="tiny">' . e(jalali_format($t['date'])) . '</p>'; ?></div>
      </div>
    <?php } ?>
    <p class="tiny" style="margin-top:8px">این خط زمانی پاک نمی‌شود؛ حتی اگر ثبت‌ها کم شوند، مسیر آمده‌ات می‌ماند.</p>
  </section>

  <section class="card">
    <div class="k">قواعد این بخش</div>
    <p class="lede" style="margin-top:6px">جوجه، اتاق شخصی توست: وضعیتش با «هم‌مسیر» به اشتراک گذاشته نمی‌شود.</p>
    <p class="lede">هیچ سنجه‌ای با کلیک، نوازش یا بازکردن صفحه پر نمی‌شود — فقط با ثبت واقعی.</p>
    <p class="lede">اگر پشت‌سرهم نوازش کنی، واکنش‌ها آرام‌تر می‌شوند (۴ لمس: فقط خرخر · ۸ لمس: فقط یک پلک) تا لمس به حلقهٔ بی‌معنی تبدیل نشود.</p>
    <p class="lede">جوجه نه می‌میرد، نه بیمار می‌شود و هیچ‌وقت به تخم برنمی‌گردد.</p>
    <div class="btn-row" style="margin-top:10px">
      <a class="btn" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت کارهای امروز</a>
      <a class="btn sec" href="<?php echo e(joma_url('index.php?p=mood')); ?>">ثبت حال امروز</a>
    </div>
  </section>
</div>
<?php } ?>
<?php joma_footer(); ?>
