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
  <div style="text-align:center">
    <span class="logo-tile" style="margin:0 auto;width:48px;height:48px;border-radius:15px"><?php echo joma_v2_icon('owl-hi', 'ic lg'); ?></span>
    <h1 style="margin-top:10px">خوش برگشتی.</h1>
    <p class="lede">از همان‌جایی که بودی ادامه بده.</p>
  </div>

  <div class="mode-tabs">
    <a class="on" href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام</a>
    <a href="<?php echo e(joma_url('index.php?p=forgot')); ?>">بازیابی رمز</a>
  </div>

  <?php if ($session_closed) { ?>
    <p class="ok"><b>برای امنیت، نشست‌های قبلی بسته شدند.</b> چون رمز این حساب تازه عوض شده است، با رمز تازه دوباره وارد شو.</p>
  <?php } ?>

  <form method="post" action="<?php echo e(joma_url('index.php?p=login')); ?>">
    <?php echo csrf_field(); ?>
    <label for="identifier">نام کاربری یا ایمیل</label>
    <input id="identifier" name="identifier" dir="ltr" autocomplete="username" autofocus value="<?php echo e($identifier); ?>" required>
    <label for="password">رمز عبور</label>
    <div style="position:relative">
      <input id="password" type="password" name="password" dir="ltr" autocomplete="current-password" required>
      <button type="button" class="icon-btn" data-pass-toggle="password" aria-label="نمایش رمز"
        style="position:absolute;inset-inline-start:6px;top:50%;transform:translateY(-50%);box-shadow:none;background:transparent;border:0">
        <?php echo joma_v2_icon('i-eye', 'ic'); ?>
      </button>
    </div>
    <?php if ($err) echo '<p class="bad">' . e($err) . '</p>'; ?>
    <p style="margin-top:12px"><button class="btn btn-block" type="submit">ورود</button></p>
  </form>

  <p class="tiny" style="text-align:center;margin-top:10px">
    رمزت را فراموش کردی؟ <a href="<?php echo e(joma_url('index.php?p=forgot')); ?>">بازیابی رمز</a>
    — حساب نداری؟ <a href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام کن</a>
  </p>
  <p class="tiny" style="text-align:center">پشتیبانی: <b dir="ltr">09967979471</b> (پیامک و بله) · ما هرگز رمز را از تو نمی‌پرسیم.</p>
</div>
<?php joma_footer(); ?>
