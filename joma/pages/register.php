<?php
if (current_user()) joma_redirect('index.php?p=dashboard');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $in = array(
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'username' => strtolower(trim($_POST['username'])),
        'email' => strtolower(trim($_POST['email'])),
        'phone' => trim($_POST['phone']),
        'job' => trim($_POST['job']),
        'password' => $_POST['password'],
        'confirm' => $_POST['confirm'],
    );
    if ($in['first_name'] === '' || $in['last_name'] === '') $err = 'نام و نام خانوادگی را وارد کنید.';
    elseif (!preg_match('/^[a-zA-Z][a-zA-Z0-9._]{2,19}$/', $in['username'])) $err = 'نام کاربری معتبر نیست.';
    elseif (username_taken($in['username'])) $err = 'این نام کاربری قبلاً استفاده شده است.';
    elseif (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) $err = 'ایمیل معتبر نیست.';
    elseif (email_taken($in['email'])) $err = 'این ایمیل قبلاً ثبت شده است.';
    elseif (!preg_match('/^09[0-9]{9}$/', $in['phone'])) $err = 'شماره موبایل معتبر نیست.';
    elseif (!in_array($in['job'], jobs_list(), true)) $err = 'شغل را از فهرست انتخاب کنید.';
    elseif (strlen($in['password']) < 6) $err = 'رمز عبور باید حداقل ۶ نویسه باشد.';
    elseif ($in['password'] !== $in['confirm']) $err = 'رمز عبور و تکرار آن یکسان نیستند.';
    else {
        $u = create_user($in);
        $_SESSION['user'] = session_user_array($u);
        joma_redirect('index.php?p=mood');
    }
}
joma_header('ثبت‌نام');
?>
<div class="card">
  <a href="<?php echo e(joma_url('index.php?p=home')); ?>">بازگشت</a>
  <h1>ساخت حساب جوما</h1>
  <form method="post">
    <?php echo csrf_field(); ?>
    <label>نام</label><input name="first_name" required>
    <label>نام خانوادگی</label><input name="last_name" required>
    <label>نام کاربری</label><input name="username" data-live-username required>
    <div id="user-hints" class="hint"></div>
    <label>ایمیل</label><input type="email" name="email" required>
    <label>شماره موبایل</label><input name="phone" placeholder="09123456789" required>
    <label>شغل</label>
    <select name="job"><?php foreach (jobs_list() as $j) echo '<option>'.e($j).'</option>'; ?></select>
    <label>رمز عبور</label><input type="password" name="password" required>
    <label>تکرار رمز عبور</label><input type="password" name="confirm" required>
    <div id="pass-hints" class="hint"></div>
    <?php if ($err) echo '<p class="bad">'.e($err).'</p>'; ?>
    <p><button class="btn" type="submit">ساخت حساب</button></p>
  </form>
</div>
<?php joma_footer(); ?>
