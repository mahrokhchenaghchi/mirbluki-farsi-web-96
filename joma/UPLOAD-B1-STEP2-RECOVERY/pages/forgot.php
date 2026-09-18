<?php
/**
 * JOMA — بازیابی رمز (B1 — مرحلهٔ ۲)
 * جریان: درخواست کاربر → صدور کد توسط پشتیبانی/مدیر → ورود کد + رمز تازه.
 * کد هرگز خام ذخیره نمی‌شود، ۱۵ دقیقه اعتبار دارد، یک‌بارمصرف است و ۵ تلاش.
 */
if (current_user()) joma_redirect('index.php?p=dashboard');
if (!function_exists('joma_recovery_issue')) {
    $__rf = dirname(__FILE__) . '/../functions/recovery.php';
    if (is_file($__rf)) require_once $__rf;
}

$step = 'ask';
$identifier = '';
$err = '';
$notice = '';
$contact = '09967979471';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $identifier = strtolower(trim(isset($_POST['identifier']) ? $_POST['identifier'] : ''));
    if ($action === 'ask') {
        if ($identifier === '') {
            $err = 'نام کاربری یا ایمیل را وارد کن.';
        } else {
            $u = function_exists('user_by_username') ? user_by_username($identifier) : null;
            if ($u && function_exists('joma_recovery_request_log')) joma_recovery_request_log((int) $u['id']);
            // پاسخ یکسان، حتی اگر حساب وجود نداشته باشد (بدون افشای حساب‌ها)
            $step = 'code';
            $notice = 'درخواست ثبت شد. حالا به پشتیبانی پیام بده تا پس از احراز هویت، کد یک‌بارمصرف برایت صادر شود.';
        }
    } elseif ($action === 'reset') {
        $code = isset($_POST['code']) ? $_POST['code'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';
        $u = function_exists('user_by_username') ? user_by_username($identifier) : null;
        if (!$u) {
            $err = 'کد یا نام کاربری درست نیست.';
            $step = 'code';
        } else {
            $res = joma_recovery_complete((int) $u['id'], $code, $password, $confirm);
            if (empty($res['ok'])) {
                $err = $res['error'];
                $step = 'code';
            } else {
                flash_set('ok', 'رمز عوض شد. همهٔ نشست‌های دیگر بسته شدند؛ با رمز تازه وارد شو.');
                joma_redirect('index.php?p=login');
            }
        }
    }
}

joma_header('بازیابی رمز', array(), array('public' => 1));
?>
<div class="card auth-card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <?php echo joma_logo(56); ?>
    <a href="<?php echo e(joma_url('index.php?p=home')); ?>">بازگشت به معرفی</a>
  </div>
  <h1>بازیابی رمز عبور</h1>
  <p class="lede">برای امنیت، رمز را خودت عوض نمی‌کنی: پشتیبانی هویت را می‌سنجد و یک کد یک‌بارمصرف می‌دهد.</p>
  <div class="mode-tabs">
    <a href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام</a>
    <a class="on" href="<?php echo e(joma_url('index.php?p=forgot')); ?>">فراموشی</a>
  </div>

  <?php if ($step === 'ask') { ?>
    <form method="post" action="<?php echo e(joma_url('index.php?p=forgot')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="ask">
      <label>نام کاربری یا ایمیل</label>
      <input name="identifier" dir="ltr" value="<?php echo e($identifier); ?>" required>
      <?php if ($err) echo '<p class="bad">' . e($err) . '</p>'; ?>
      <p><button class="btn btn-block" type="submit">درخواست کد بازیابی</button></p>
    </form>
    <div class="joma-reg-gate-contacts">
      <div><span>پیامک:</span> <b dir="ltr"><?php echo e($contact); ?></b></div>
      <div><span>پیام‌رسان بله:</span> <b dir="ltr"><?php echo e($contact); ?></b></div>
    </div>
    <p class="lede">کد بازیابی فقط با احراز هویت پشتیبانی صادر می‌شود. هیچ پیامک خودکاری فرستاده نمی‌شود.</p>
  <?php } else { ?>
    <?php if ($notice) echo '<p class="ok">' . e($notice) . '</p>'; ?>
    <div class="joma-reg-gate-contacts">
      <div><span>پیامک:</span> <b dir="ltr"><?php echo e($contact); ?></b></div>
      <div><span>پیام‌رسان بله:</span> <b dir="ltr"><?php echo e($contact); ?></b></div>
    </div>
    <p class="lede">کد ۱۵ دقیقه اعتبار دارد، فقط یک‌بار قابل استفاده است و حداکثر ۵ تلاش دارد.</p>
    <form method="post" action="<?php echo e(joma_url('index.php?p=forgot')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="reset">
      <input type="hidden" name="identifier" value="<?php echo e($identifier); ?>">
      <label>کد ۶ رقمی</label>
      <input name="code" dir="ltr" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
      <label>رمز تازه</label>
      <input type="password" name="password" required>
      <label>تکرار رمز تازه</label>
      <input type="password" name="confirm" required>
      <?php if ($err) echo '<p class="bad">' . e($err) . '</p>'; ?>
      <p><button class="btn btn-block" type="submit">تغییر رمز</button></p>
    </form>
    <p class="lede">پشتیبانی هرگز کد را از تو نمی‌پرسد؛ کد را در هیچ‌جای عمومی (پیام گروهی، شبکه‌های اجتماعی) نگذار.</p>
  <?php } ?>

  <p><a class="btn sec btn-block" href="<?php echo e(joma_url('index.php?p=login')); ?>">بازگشت به ورود</a></p>
</div>
<?php joma_footer(); ?>
