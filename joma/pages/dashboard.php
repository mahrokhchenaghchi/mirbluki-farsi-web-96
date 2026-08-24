<?php
require_login(); require_perm('VIEW_DASHBOARD');
$u = current_user();
$today = jalali_today();
$wp = ensure_period($u['id'], jalali_period_key($today));
$plan = $wp['plan'];
$acts = list_plan_activities($plan['id'], $u['id']);
$evs = list_events($plan['id'], $u['id']);
$mood = get_mood($u['id'], $today);
joma_header('داشبورد', array(array('label'=>'داشبورد')));
?>
<div class="card">
  <div class="chip"><?php echo e(jalali_format($today)); ?></div>
  <h1>سلام <?php echo e($u['full_name']); ?></h1>
  <p>دوره <?php echo e(jalali_period_label($plan['period_key'])); ?> · <?php echo e(status_label($plan['status'])); ?></p>
</div>
<div class="grid grid-3">
  <div class="card"><h3>حال امروز</h3><?php echo $mood ? 'ثبت شده' : '<a href="'.e(joma_url('index.php?p=mood')).'">ثبت حال</a>'; ?></div>
  <div class="card"><h3>فعالیت‌های برنامه</h3><p style="font-size:32px;font-weight:900"><?php echo fa_num(count($acts)); ?></p></div>
  <div class="card"><h3>رویدادها</h3><p style="font-size:32px;font-weight:900"><?php echo fa_num(count($evs)); ?></p></div>
</div>
<p>
  <a class="btn" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت عملکرد</a>
  <a class="btn sec" href="<?php echo e(joma_url('index.php?p=plan')); ?>">برنامه من</a>
  <a class="btn sec" href="<?php echo e(joma_url('index.php?p=reports')); ?>">گزارش‌ها</a>
</p>
<h2>فعالیت‌های دوره</h2>
<div class="grid grid-3">
<?php foreach ($acts as $a) {
    $sum=0; foreach ($evs as $e) if ((int)$e['plan_activity_id']===(int)$a['id']) $sum += (float)$e['actual_value'];
    echo '<div class="card act" style="border:2px solid '.$a['color'].'"><div class="sticker">'.e($a['sticker']).'</div><h3>'.e($a['name']).'</h3><p>'.e($a['category']).'</p><p>'.e(format_value($a['data_type'],$sum,$a['unit'])).' / '.e(format_value($a['data_type'],$a['target_value'],$a['unit'])).'</p></div>';
}
if (!$acts) echo '<div class="card">هنوز فعالیتی به این دوره اضافه نشده. <a href="'.e(joma_url('index.php?p=plan')).'">ساخت برنامه</a></div>';
?>
</div>
<?php joma_footer(); ?>
