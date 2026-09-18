<?php
/**
 * JOMA — صفحهٔ «جوجهٔ من» (B5 — مرحلهٔ ۱)
 * این صفحه فقط دادهٔ واقعی را نشان می‌دهد: سه سنجهٔ موجود + مرحله + خط زمانی.
 * تصویر و انیمیشن جوجه در بازنویسی رابط می‌آید؛ اینجا هیچ شکلی جعل نمی‌شود.
 */
require_login();
$_jooje_fn = dirname(__FILE__) . '/../functions/jooje.php';
if (is_file($_jooje_fn)) require_once $_jooje_fn;
$u = current_user();
$j = function_exists('jooje_state') ? jooje_state((int) $u['id']) : array('enabled' => false);
joma_header('جوجهٔ من', array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'جوجهٔ من')));
?>
<?php if (empty($j['enabled'])) { ?>
  <div class="card">
    <h1>جوجهٔ من</h1>
    <p class="lede">این بخش همین حالا در دسترس نیست.</p>
  </div>
<?php } else {
  $state_labels = array(
      'calm' => 'آرام',
      'happy' => 'شاد',
      'sleep' => 'خواب',
      'faded' => 'کم‌رنگ',
      'gray' => 'خاکستری',
  );
  $stage_labels = array('egg' => 'تخم', 'crack' => 'تخمِ ترک‌خورده', 'chick' => 'جوجه');
  $state = isset($state_labels[$j['state']]) ? $state_labels[$j['state']] : $j['state'];
  $stage = isset($stage_labels[$j['stage']]) ? $stage_labels[$j['stage']] : $j['stage'];
  $state_note = array(
      'calm' => 'حالِ عادی.',
      'happy' => 'امروز ثبت کردی؛ جوجه سرحال است.',
      'sleep' => 'استراحت می‌کند.',
      'faded' => 'کم‌رنگ شده؛ منتظر توست. جوجه نه می‌میرد و نه بیمار می‌شود.',
      'gray' => 'خاکستری شده؛ منتظر توست. جوجه نه می‌میرد و نه بیمار می‌شود.',
  );
?>
<div class="page-head">
  <div>
    <h1>جوجهٔ من</h1>
    <p class="lede">همهٔ اعداد این صفحه از ثبت‌های واقعی خودت می‌آید. هیچ عدد نمایشی یا نمونه‌ای وجود ندارد.</p>
  </div>
</div>

<div class="grid grid-3">
  <div class="card stat">
    <div class="k">مرحله</div>
    <div class="v" style="font-size:24px"><?php echo e($stage); ?></div>
    <p class="lede"><?php echo fa_num((int) $j['units']); ?> دونه ثبت شده است.</p>
  </div>
  <div class="card stat">
    <div class="k">حال جوجه</div>
    <div class="v" style="font-size:24px"><?php echo e($state); ?></div>
    <p class="lede"><?php echo e(isset($state_note[$j['state']]) ? $state_note[$j['state']] : ''); ?></p>
  </div>
  <div class="card stat">
    <div class="k">تا تولد</div>
    <div class="v"><?php echo fa_num((int) $j['units_to_birth']); ?></div>
    <p class="lede">دونهٔ دیگر (آستانهٔ تولد: <?php echo fa_num((int) $j['thresholds']['birth_units']); ?> دونه)</p>
  </div>
</div>

<h2>مراقبت امروز</h2>
<div class="grid grid-3">
<?php foreach ($j['metrics'] as $m) {
    if (empty($m['available'])) continue; ?>
  <div class="card">
    <div class="k"><?php echo e($m['icon'] . ' ' . $m['label']); ?></div>
    <?php if ($m['key'] === 'water' && empty($m['has_source'])) { ?>
      <p class="lede">فعالیت آب در برنامه‌ات نیست، پس سنجهٔ آب امروز چیزی برای شمردن ندارد (این «صفر» نیست).</p>
    <?php } else { ?>
      <div class="v" style="font-size:26px">
        <?php echo fa_num($m['today']); ?>
        <?php if (!empty($m['unit'])) echo '<span style="font-size:14px;font-weight:400"> ' . e($m['unit']) . '</span>'; ?>
      </div>
      <?php if (!empty($m['target']) && $m['progress'] !== null) { ?>
        <div style="background:#eceff4;border-radius:999px;height:10px;margin:8px 0">
          <div style="background:#157347;height:10px;border-radius:999px;width:<?php echo (int) round($m['progress'] * 100); ?>%"></div>
        </div>
        <p class="lede">هدف امروز: <?php echo fa_num($m['target']); ?><?php if (!empty($m['unit'])) echo ' ' . e($m['unit']); ?></p>
      <?php } else { ?>
        <p class="lede">هدف روزانه برای این سنجه تعیین نشده؛ فقط عدد واقعی نمایش داده می‌شود.</p>
      <?php } ?>
    <?php } ?>
    <p class="lede" style="font-size:12px"><?php echo e($m['source']); ?></p>
  </div>
<?php } ?>
</div>

<?php foreach ($j['metrics'] as $m) { if (!empty($m['available'])) continue; ?>
  <p class="lede">سنجهٔ «<?php echo e($m['label']); ?>» تا ساخته‌شدن منبعش نمایش داده نمی‌شود. <?php echo e($m['reason']); ?></p>
<?php } ?>

<?php if (!empty($j['growth'])) { ?>
<h2>رشد</h2>
<div class="card">
  <div class="k"><?php echo e($j['growth']['label']); ?></div>
  <div style="background:#eceff4;border-radius:999px;height:10px;margin:8px 0">
    <div style="background:#4F9FC4;height:10px;border-radius:999px;width:<?php echo (int) round($j['growth']['progress'] * 100); ?>%"></div>
  </div>
  <?php if (!empty($j['growth']['next_label'])) { ?>
    <p class="lede">مرحلهٔ بعد: <?php echo e($j['growth']['next_label']); ?> — از <?php echo fa_num((int) $j['growth']['next_at_units']); ?> دونه.</p>
  <?php } else { ?>
    <p class="lede">به آخرین مرحلهٔ رشد رسیده است.</p>
  <?php } ?>
</div>
<?php } ?>

<h2>خط زمانی</h2>
<div class="card">
  <?php foreach ($j['timeline'] as $t) { ?>
    <p><?php echo e($t['icon']); ?> <b><?php echo e($t['label']); ?></b>
      <?php if (!empty($t['date'])) echo '<span class="lede"> — ' . e(jalali_format($t['date'])) . '</span>'; ?>
    </p>
  <?php } ?>
  <p class="lede">این خط زمانی پاک نمی‌شود؛ حتی اگر ثبت‌ها کم شوند، مسیر آمده‌ات می‌ماند.</p>
</div>

<div class="card">
  <p class="lede">جوجه، اتاق شخصی توست: وضعیتش با «هم‌مسیر» به اشتراک گذاشته نمی‌شود.</p>
  <p class="lede">هیچ‌کدام از سنجه‌ها با کلیک، نوازش یا بازکردن صفحه پر نمی‌شود؛ فقط با ثبت واقعی.</p>
  <div class="btn-row">
    <a class="btn" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت کارهای امروز</a>
    <a class="btn sec" href="<?php echo e(joma_url('index.php?p=mood')); ?>">ثبت حال امروز</a>
  </div>
</div>
<?php } ?>
<?php joma_footer(); ?>
