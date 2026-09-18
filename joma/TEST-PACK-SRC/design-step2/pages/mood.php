<?php
/**
 * JOMA — حال من — طرح نسخهٔ ۲۰
 * پنج پرسش + قدم ششم (یادداشت اختیاری، خصوصی).
 * قواعد دست‌نخورده: همان نام فیلدها (energy/general/focus/sleep/stress/note) و همان اعتبارسنجی.
 */
require_login();
$u = current_user();
$today = jalali_today();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $scores = array(
        'energy' => (int) (isset($_POST['energy']) ? $_POST['energy'] : 0),
        'general' => (int) (isset($_POST['general']) ? $_POST['general'] : 0),
        'focus' => (int) (isset($_POST['focus']) ? $_POST['focus'] : 0),
        'sleep' => (int) (isset($_POST['sleep']) ? $_POST['sleep'] : 0),
        'stress' => (int) (isset($_POST['stress']) ? $_POST['stress'] : 0),
    );
    foreach ($scores as $v) {
        if ($v < 1 || $v > 5) { $err = 'هر پنج شاخص را انتخاب کنید.'; break; }
    }
    if ($err === '') {
        save_mood($u['id'], $today, $scores, isset($_POST['note']) ? $_POST['note'] : '');
        joma_redirect('index.php?p=dashboard');
    }
}
$ex = get_mood($u['id'], $today);
$metrics = array(
    'energy' => array('انرژی', array('😴', '😐', '🙂', '😄', '⚡')),
    'general' => array('حال عمومی', array('😞', '😐', '🙂', '😊', '🤩')),
    'focus' => array('تمرکز', array('🌫️', '😐', '🙂', '🎯', '🧠')),
    'sleep' => array('کیفیت خواب', array('😫', '😐', '🙂', '😴', '✨')),
    'stress' => array('سطح استرس', array('😌', '🙂', '😐', '😟', '😣')),
);
$hints = array(
    'energy' => '۱ = خیلی کم · ۵ = پرانرژی',
    'general' => '۱ = خیلی بد · ۵ = خیلی خوب',
    'focus' => '۱ = پراکنده · ۵ = متمرکز',
    'sleep' => '۱ = بد · ۵ = خوب',
    'stress' => '۱ = آرام · ۵ = پرتنش',
);
$map = array('general' => 'general_mood', 'sleep' => 'sleep_quality');
$first = $u['first_name'] ? $u['first_name'] : '';
joma_header('حال من', array());
?>
<?php joma_v2_pagehead('حال من', function_exists('joma_v2_greeting') ? joma_v2_greeting($u) : 'سلام', 'امروز چطوری؟ پنج شاخص کوتاه — بعدش الگوها را در گزارش‌ها می‌بینی.'); ?>

<?php if ($ex) { ?>
  <p class="card lede" style="border:1.5px solid var(--brand2)">حال امروز ثبت شده است؛ اگر خواستی می‌توانی اصلاحش کنی.</p>
<?php } ?>

<form method="post">
<?php
echo csrf_field();
foreach ($metrics as $key => $m) {
    $field = isset($map[$key]) ? $map[$key] : $key;
    $cur = $ex ? (int) $ex[$field] : 0;
    echo '<section class="card mood-card" style="max-width:720px">';
    echo '<div class="row" style="justify-content:space-between"><b style="font-size:13.5px">' . e($m[0]) . '</b><span class="tiny">' . e($hints[$key]) . '</span></div>';
    echo '<div class="rate-grid" style="margin-top:8px">';
    for ($i = 1; $i <= 5; $i++) {
        echo '<label class="mood-opt' . ($cur === $i ? ' on' : '') . '">';
        echo '<input type="radio" name="' . e($key) . '" value="' . $i . '" ' . ($cur === $i ? 'checked' : '') . '>';
        echo '<span style="font-size:24px">' . $m[1][$i - 1] . '</span>';
        echo '</label>';
    }
    echo '</div></section>';
}
?>
<section class="card" style="max-width:720px">
  <div class="row" style="justify-content:space-between">
    <b style="font-size:13.5px">قدم ششم — یک جمله برای خودت (اختیاری)</b>
    <span class="chip g">فقط خودت</span>
  </div>
  <textarea name="note" placeholder="اگر چیزی روی دلت است، همین‌جا بنویس…" style="margin-top:8px"><?php echo $ex ? e($ex['note']) : ''; ?></textarea>
  <p class="tiny">این متن به «هم‌مسیر» فرستاده نمی‌شود؛ مجوز جداگانه‌ای برای دیدن یادداشت وجود ندارد و پیش‌فرض، خصوصی است.</p>
</section>
<?php if ($err) echo '<p class="bad">' . e($err) . '</p>'; ?>
<div class="btn-row">
  <button class="btn" type="submit">ثبت حال امروز</button>
  <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=dashboard')); ?>">فعلاً نه، برگرد</a>
</div>
</form>

<section class="card" style="max-width:720px">
  <div class="k">چرا این پنج شاخص؟</div>
  <p class="lede" style="margin-top:6px">چهار شاخص اول (انرژی، حال عمومی، تمرکز، خواب) در امتیاز کلی «موفقیت» حساب نمی‌شوند؛ استرس هم جدا دیده می‌شود. حال، تصویر روزت است، نه نمرهٔ تو.</p>
</section>
<?php joma_footer(); ?>
