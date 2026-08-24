<?php
require_login();
require_perm('VIEW_DASHBOARD');
$u = current_user();
$today = jalali_today();
$key = current_period_key();
$wp = ensure_period($u['id'], $key);
$plan = $wp['plan'];
$acts = list_plan_activities($plan['id'], $u['id']);
$evs = list_events($plan['id'], $u['id']);
$mood = get_mood($u['id'], $today);
$metrics = array(
    'energy' => array('😴', '😐', '🙂', '😄', '⚡'),
    'general_mood' => array('😞', '😐', '🙂', '😊', '🤩'),
    'focus' => array('🌫️', '😐', '🙂', '🎯', '🧠'),
    'sleep_quality' => array('😫', '😐', '🙂', '😴', '✨'),
    'stress' => array('😌', '🙂', '😐', '😟', '😣'),
);
$remaining = 0;
foreach ($acts as $a) {
    $rel = events_for($evs, $a['id']);
    if ($a['frequency'] !== 'DAILY' || !has_daily_registration($rel, $today)) $remaining++;
}
joma_header('داشبورد', array(array('label' => 'داشبورد')));
?>
<section class="card hero-card">
  <p class="lede"><?php echo e(jalali_format($today)); ?></p>
  <h1>سلام <?php echo e($u['full_name'] ? $u['full_name'] : $u['username']); ?></h1>
  <div class="btn-row">
    <span class="chip"><?php echo e(jalali_period_label($plan['period_key'])); ?></span>
    <?php echo status_badge($plan['status']); ?>
  </div>
</section>
<div class="grid grid-3">
  <a class="card stat" href="<?php echo e(joma_url('index.php?p=mood')); ?>">
    <div class="k">حال امروز</div>
    <?php if ($mood) {
        echo '<div class="v" style="font-size:28px">';
        foreach ($metrics as $k => $stickers) {
            $sc = (int) $mood[$k];
            echo $sc ? $stickers[$sc - 1] . ' ' : '';
        }
        echo '</div>';
    } else {
        echo '<p class="lede">هنوز ثبت نشده — ثبت حال</p>';
    } ?>
  </a>
  <div class="card stat"><div class="k">فعالیت‌های برنامه</div><div class="v"><?php echo fa_num(count($acts)); ?></div></div>
  <div class="card stat"><div class="k">قابل ثبت امروز</div><div class="v"><?php echo fa_num($remaining); ?></div></div>
</div>
<div class="grid grid-2" style="margin:8px 0 18px">
  <a class="btn" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت عملکرد</a>
  <a class="btn sec" href="<?php echo e(joma_url('index.php?p=plan')); ?>">برنامه دوره</a>
  <a class="btn sec" href="<?php echo e(joma_url('index.php?p=reports')); ?>">گزارش‌ها</a>
  <a class="btn sec" href="<?php echo e(joma_url('index.php?p=periods')); ?>">دوره‌های من</a>
</div>
<h2>فعالیت‌های دوره</h2>
<?php if (!$acts) {
    echo empty_state('هنوز فعالیتی به این دوره اضافه نشده', 'از کتابخانه به برنامه این ماه اضافه کنید.', joma_url('index.php?p=plan'), 'ساخت برنامه');
} else {
    echo '<div class="grid grid-2">';
    $n = 0;
    foreach ($acts as $a) {
        if ($n++ >= 6) break;
        $sum = displayed_actual($a, $evs, $today);
        echo '<article class="card" style="display:flex;gap:12px;align-items:center">';
        echo '<div class="sticker" style="background:'.$a['color'].'">'.e($a['sticker']).'</div>';
        echo '<div><strong>'.e($a['name']).'</strong><p class="meta">'.e(frequencies_list()[$a['frequency']]).' · '.e(format_value($a['data_type'], $sum, $a['unit'])).' / '.e(format_value($a['data_type'], $a['target_value'], $a['unit'])).'</p></div>';
        echo '</article>';
    }
    echo '</div>';
} ?>
<?php joma_footer(); ?>
