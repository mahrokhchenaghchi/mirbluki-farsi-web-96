<?php
if (current_user()) joma_redirect('index.php?p=dashboard');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = strtolower(trim($_POST['identifier']));
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
joma_header('ورود');
?>
<div class="card">
  <a href="<?php echo e(joma_url('index.php?p=home')); ?>">بازگشت</a>
  <h1>ورود به جوما</h1>
  <form method="post">
    <?php echo csrf_field(); ?>
    <label>نام کاربری یا ایمیل</label>
    <input name="identifier" required>
    <label>رمز عبور</label>
    <input type="password" name="password" required>
    <?php if ($err) echo '<p class="bad">'.e($err).'</p>'; ?>
    <p><button class="btn" type="submit">ورود</button></p>
    <p><a href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام</a></p>
  </form>
</div>
<?php joma_footer(); ?>
