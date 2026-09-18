<?php
if (current_user()) joma_redirect('index.php?p=dashboard');
$err = '';
$identifier = '';
$session_closed = false;
// (B1 — مرحلهٔ ۲): اگر رمز عوض شده و این نشست باطل شده است، یک پیام روشن نشان بده.
if (!empty($_SESSION['auth_epoch_boot'])) {
    $session_closed = true;
    unset($_SESSION['auth_epoch_boot']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $identifier = strtolower(trim(isset($_POST['identifier']) ? $_POST['identifier'] : ''));
    $pass = isset($_POST['password']) ? $_POST['password'] : '';
    if ($identifier === '' || $pass === '') {
        $err = 'نام کاربری و رمز عبور را وارد کنید.';
    } else {
        $u = user_by_username($identifier);
        if (!$u) {
            $err = 'حسابی با این نام کاربری یا ایمیل پیدا نشد.';
        } elseif (!password_verify($pass, $u['password_hash'])) {
            $err = 'نام کاربری/ایمیل یا رمز عبور نادرست است.';
        } else {
            $_SESSION['user'] = session_user_array($u);
            copy_seed_to_user($u['id']);
            // (حکم PO — باگ ۹ گزینه A) مقصد پس از ورود داشبورد است؛ کاربرِ بدون خلقِ امروز
            // همچنان توسط maybe_mood_gate از همان داشبورد به p=mood هدایت می‌شود (رفتار gate دست‌نخورده).
            joma_redirect('index.php?p=dashboard');
        }
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
  <p class="lede"></p>
  <div class="mode-tabs">
    <a class="on" href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام</a>
    <a href="<?php echo e(joma_url('index.php?p=forgot')); ?>">فراموشی</a>
  </div>
  <?php if ($session_closed) { ?>
    <p class="lede"><b>برای امنیت، نشست‌های قبلی بسته شدند.</b> چون رمز این حساب تازه عوض شده است، با رمز تازه دوباره وارد شو.</p>
  <?php } ?>
  <form method="post" action="<?php echo e(joma_url('index.php?p=login')); ?>">
    <?php echo csrf_field(); ?>
    <label>نام کاربری یا ایمیل</label>
    <input name="identifier" dir="ltr" value="<?php echo e($identifier); ?>" required>
    <label>رمز عبور</label>
    <input type="password" name="password" required>
    <?php if ($err) echo '<p class="bad">'.e($err).'</p>'; ?>
    <p><button class="btn btn-block" type="submit">ورود</button></p>
  </form>
  <p style="text-align:center"><a href="<?php echo e(joma_url('index.php?p=about')); ?>">درباره جوما</a></p>
</div>
<?php joma_footer(); ?>
