<?php
joma_header('درباره جوما', current_user() ? array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'درباره')) : array(), current_user() ? array() : array('public' => 1));
?>
<article class="card" style="max-width:760px;margin:<?php echo current_user() ? '0' : '40px auto'; ?>">
  <?php echo joma_logo(72); ?>
  <p>
    <a href="javascript:history.back()">بازگشت</a>
    ·
    <a href="<?php echo e(joma_url('index.php?p=home')); ?>">صفحه معرفی</a>
  </p>
  <h1>درباره جوما</h1>
  <p class="lede">جوما یک محصول مستقل برای خودمدیریتی است: برنامه‌ریزی فعالیت‌ها در دوره‌های شمسی، ثبت عملکرد واقعی، ثبت حال روزانه و فهمیدن روند از روی داده.</p>
  <h2>چرا ساخته شده؟</h2>
  <p class="lede">چون فهرست کارها کافی نیست. جوما دوره، برنامه، اجرا و تاریخچه را جدا نگه می‌دارد تا ماه بعد، ماه قبل را عوض نکند.</p>
  <h2>فلسفه</h2>
  <p>برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود</p>
  <h2>طراح</h2>
  <p class="lede">جواد میربلوکی</p>
</article>
<?php joma_footer(); ?>
