<?php
// (تصمیم مالک — B1): مسیر بازیابی رمز بدون اثبات مالکیت موقتاً بسته شد.
// فرم قبلی (نام کاربری + رمز تازه) حذف شد؛ بازیابی فقط از طریق پشتیبانی.
if (current_user()) joma_redirect('index.php?p=dashboard');
joma_header('بازیابی رمز', array(), array('public' => 1));
?>
<div class="card auth-card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <?php echo joma_logo(56); ?>
    <a href="<?php echo e(joma_url('index.php?p=home')); ?>">بازگشت به معرفی</a>
  </div>
  <h1>بازیابی رمز عبور</h1>
  <p class="lede">برای بازیابی رمز به پشتیبانی پیام بده.</p>
  <div class="mode-tabs">
    <a href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام</a>
    <a class="on" href="<?php echo e(joma_url('index.php?p=forgot')); ?>">فراموشی</a>
  </div>
  <div class="joma-reg-gate-contacts">
    <div><span>پیامک:</span> <b dir="ltr">09967979471</b></div>
    <div><span>پیام‌رسان بله:</span> <b dir="ltr">09967979471</b></div>
  </div>
  <p class="lede">پس از احراز هویت، پشتیبانی کد بازیابی را برایت صادر می‌کند. کد را در هیچ‌جای عمومی ننویس.</p>
  <p><a class="btn btn-block" href="<?php echo e(joma_url('index.php?p=login')); ?>">بازگشت به ورود</a></p>
</div>
<?php joma_footer(); ?>
