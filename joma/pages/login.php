<?php
if (current_user()) joma_redirect('index.php?p=dashboard');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = strtolower(trim(isset($_POST['identifier']) ? $_POST['identifier'] : ''));
    $pass = isset($_POST['password']) ? $_POST['password'] : '';
    $u = user_by_username($id);
    if (!$u || !password_verify($pass, $u['password_hash'])) {
        $err = 'نام کاربری/ایمیل یا رمز عبور نادرست است.';
    } else {
        $_SESSION['user'] = session_user_array($u);
        copy_seed_to_user($u['id']);
        joma_redirect('index.php?p=mood');
    }
}
joma_header('ورود', array(), array('public' => 1));
?>
<div class="card auth-card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <?php echo joma_logo(56); ?>
    <a href="<?php echo e(joma_url('index.php?p=home')); ?>">بازگشت به معرفی</a>
  </div>
  <h1>ورود به جوما</h1>
  <p class="lede">حالت آزمایشی محلی — داده روی همین سرور می‌ماند.</p>
  <div class="mode-tabs">
    <a class="on" href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام</a>
    <a href="<?php echo e(joma_url('index.php?p=forgot')); ?>">فراموشی</a>
  </div>
  <form method="post" action="<?php echo e(joma_url('index.php?p=login')); ?>">
    <?php echo csrf_field(); ?>
    <label>نام کاربری یا ایمیل</label>
    <input name="identifier" dir="ltr" required>
    <label>رمز عبور</label>
    <input type="password" name="password" required>
    <?php if ($err) echo '<p class="bad">'.e($err).'</p>'; ?>
    <p><button class="btn btn-block" type="submit">ورود</button></p>
  </form>
  <p style="text-align:center"><a href="<?php echo e(joma_url('index.php?p=about')); ?>">درباره جوما</a></p>
</div>
<?php joma_footer(); ?>
