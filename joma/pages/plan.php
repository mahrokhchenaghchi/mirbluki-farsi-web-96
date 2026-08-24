<?php
require_login(); require_perm('EDIT_PLAN');
$u = current_user();
$key = isset($_GET['period']) ? $_GET['period'] : jalali_period_key(jalali_today());
$wp = ensure_period($u['id'], $key);
$plan = $wp['plan'];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = isset($_POST['action']) ? $_POST['action'] : '';
    if ($act === 'add') {
        $a = get_activity($_POST['activity_id'], $u['id']);
        if ($a) $msg = add_plan_activity($u['id'], $plan, $a, array(
            'frequency' => $_POST['frequency'],
            'target_value' => $_POST['target_value'],
            'weight' => $_POST['weight'],
        ));
        $wp = ensure_period($u['id'], $key); $plan = $wp['plan'];
    } elseif ($act === 'savepa') {
        $msg = update_plan_activity($u['id'], $plan, $_POST['pa_id'], array(
            'frequency' => $_POST['frequency'],
            'target_value' => (float) $_POST['target_value'],
            'weight' => (int) $_POST['weight'],
            'sort_order' => (int) $_POST['sort_order'],
        ));
    } elseif ($act === 'del' && !empty($_POST['confirm'])) {
        $msg = remove_plan_activity($u['id'], $plan, $_POST['pa_id']);
    } elseif ($act === 'next') {
        $msg = transition_plan($u['id'], $plan, $_POST['next']);
        $wp = ensure_period($u['id'], $key); $plan = $wp['plan'];
    }
}
$acts = list_user_activities($u['id']);
$pas = list_plan_activities($plan['id'], $u['id']);
$freq = frequencies_list();
joma_header('برنامه من', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'برنامه')));
?>
<h1>برنامه <?php echo e(jalali_period_label($plan['period_key'])); ?> <span class="chip"><?php echo e(status_label($plan['status'])); ?></span></h1>
<?php if ($msg) echo '<p class="bad">'.e($msg).'</p>'; ?>
<div class="card">
<?php if ($plan['status']==='DRAFT') { ?>
<form method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="next"><input type="hidden" name="next" value="PLANNING"><button class="btn">نهایی‌سازی</button></form>
<?php } elseif ($plan['status']==='PLANNING') { ?>
<form method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="next"><input type="hidden" name="next" value="RUNNING"><button class="btn">شروع اجرا</button></form>
<?php } else echo '<p>برنامه این دوره برای حفظ تاریخچه قفل است.</p>'; ?>
</div>
<?php if (plan_editable($plan['status'])) { ?>
<form class="card" method="post">
  <?php echo csrf_field(); ?><input type="hidden" name="action" value="add">
  <label>فعالیت کتابخانه</label>
  <select name="activity_id"><?php foreach ($acts as $a) if ($a['status']!=='INACTIVE') echo '<option value="'.e($a['id']).'">'.e($a['sticker'].' '.$a['name']).'</option>'; ?></select>
  <label>تناوب این دوره (خالی = پیش‌فرض کتابخانه)</label>
  <select name="frequency"><option value="">پیش‌فرض کتابخانه</option><?php foreach ($freq as $k=>$v) echo '<option value="'.$k.'">'.$v.'</option>'; ?></select>
  <label>هدف این دوره</label><input name="target_value" placeholder="خالی = پیش‌فرض">
  <label>وزن این دوره</label><input name="weight" placeholder="خالی = پیش‌فرض">
  <p><button class="btn" type="submit">افزودن به برنامه</button></p>
</form>
<?php } ?>
<div class="grid grid-3">
<?php foreach ($pas as $a) { ?>
  <div class="card act" style="border:2px solid <?php echo e($a['color']); ?>">
    <div class="sticker"><?php echo e($a['sticker']); ?></div>
    <h3><?php echo e($a['name']); ?></h3>
    <p><?php echo e($a['category']); ?> · <?php echo e($freq[$a['frequency']]); ?></p>
    <?php if (plan_editable($plan['status'])) { ?>
    <form method="post">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="savepa"><input type="hidden" name="pa_id" value="<?php echo e($a['id']); ?>">
      <input type="hidden" name="sort_order" value="<?php echo e($a['sort_order']); ?>">
      <select name="frequency"><?php foreach ($freq as $k=>$v) echo '<option value="'.$k.'" '.($a['frequency']===$k?'selected':'').'>'.$v.'</option>'; ?></select>
      <input name="target_value" value="<?php echo e($a['target_value']); ?>">
      <input name="weight" value="<?php echo e($a['weight']); ?>">
      <button class="btn sec" type="submit">ذخیره</button>
    </form>
    <form method="post" onsubmit="return confirm('حذف از برنامه این دوره؟');">
      <?php echo csrf_field(); ?><input type="hidden" name="action" value="del"><input type="hidden" name="confirm" value="1"><input type="hidden" name="pa_id" value="<?php echo e($a['id']); ?>">
      <button class="btn ghost" type="submit">حذف</button>
    </form>
    <?php } else { ?>
      <p>هدف <?php echo e($a['target_value']); ?> · وزن <?php echo e($a['weight']); ?></p>
      <a class="btn" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت عملکرد</a>
    <?php } ?>
  </div>
<?php } if (!$pas) echo '<div class="card">هنوز دوره‌ای/فعالیتی ندارید. از کتابخانه اضافه کنید.</div>'; ?>
</div>
<?php joma_footer(); ?>
