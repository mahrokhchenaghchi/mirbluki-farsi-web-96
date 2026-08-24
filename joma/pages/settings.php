<?php
require_login();
$u = current_user();
$ok = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $nu = update_user($u['id'], array(
        'first_name'=>trim($_POST['first_name']),
        'last_name'=>trim($_POST['last_name']),
        'phone'=>trim($_POST['phone']),
        'job'=>trim($_POST['job']),
    ));
    $_SESSION['user'] = session_user_array($nu);
    $u = current_user();
    $ok = 'ذخیره شد.';
}
joma_header('تنظیمات', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'تنظیمات')));
?>
<h1>تنظیمات</h1>
<form class="card" method="post">
<?php echo csrf_field(); ?>
<label>نام</label><input name="first_name" value="<?php echo e($u['first_name']); ?>">
<label>نام خانوادگی</label><input name="last_name" value="<?php echo e($u['last_name']); ?>">
<label>موبایل</label><input name="phone" value="<?php echo e($u['phone']); ?>">
<label>شغل</label><select name="job"><?php foreach (jobs_list() as $j) echo '<option '.($u['job']===$j?'selected':'').'>'.e($j).'</option>'; ?></select>
<p>اعلان‌ها فعلاً فقط به‌عنوان ترجیح ذخیره می‌شوند و SMS ارسال نمی‌شود.</p>
<button class="btn">ذخیره</button>
<?php if ($ok) echo '<p class="ok">'.e($ok).'</p>'; ?>
</form>
<?php joma_footer(); ?>
