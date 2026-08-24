<?php
if (current_user()) joma_redirect('index.php?p=dashboard');
joma_header('جوما');
?>
<div class="card">
  <h1>جوما</h1>
  <p>برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود</p>
  <p>محصول مستقل خودمدیریتی. نسخه PHP برای هاست اشتراکی.</p>
  <p>
    <a class="btn" href="<?php echo e(joma_url('index.php?p=register')); ?>">شروع / ثبت‌نام</a>
    <a class="btn sec" href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=about')); ?>">درباره جوما</a>
  </p>
</div>
<?php joma_footer(); ?>
