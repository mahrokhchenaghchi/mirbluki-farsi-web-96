<?php
if (current_user()) joma_redirect('index.php?p=dashboard');
joma_header('جوما', array(), array('public' => 1));
?>
<div class="public-top">
  <?php echo joma_logo(56); ?>
  <div class="btn-row">
    <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a class="btn" href="<?php echo e(joma_url('index.php?p=register')); ?>">شروع</a>
  </div>
</div>
<section class="landing">
  <div>
    <p class="kicker">محصول مستقل خودمدیریتی</p>
    <h1>جوما</h1>
    <p class="lede" style="font-size:20px">برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود</p>
    <p class="lede">جوما برای ثبت فعالیت‌ها، عملکرد واقعی و حال روزانه شماست. هر ماه شمسی تاریخچهٔ مستقل خودش را نگه می‌دارد.</p>
    <div class="btn-row" style="margin-top:22px">
      <a class="btn" href="<?php echo e(joma_url('index.php?p=register')); ?>">شروع استفاده</a>
      <a class="btn sec" href="<?php echo e(joma_url('index.php?p=about')); ?>">درباره جوما</a>
    </div>
  </div>
  <div class="grid">
    <?php
    $bits = array(
      array('🌸', 'حال امروز را با استیکر ثبت کن'),
      array('📚', 'کتابخانه ۸۰ فعالیت آماده'),
      array('📅', 'دوره‌های شمسی مستقل'),
      array('📊', 'گزارش فقط از داده واقعی'),
    );
    foreach ($bits as $b) {
        echo '<div class="card" style="display:flex;align-items:center;gap:14px;margin:0"><span style="font-size:30px">'.$b[0].'</span><strong>'.e($b[1]).'</strong></div>';
    }
    ?>
  </div>
</section>
<?php joma_footer(); ?>
