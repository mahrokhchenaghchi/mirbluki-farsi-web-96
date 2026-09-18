<?php
/**
 * JOMA — پشتیبانی (طرح نسخهٔ ۲۰)
 * همان راه‌های ارتباطی قبلی، با قالب تازه و جمله‌های روشن دربارهٔ کاری که پشتیبانی می‌کند.
 */
$u = current_user();
$phone = '09967979471';
joma_header('پشتیبانی', array(), $u ? array() : array('public' => 1));
?>
<?php joma_v2_pagehead('پشتیبانی', 'مرکز ارتباط جوما', 'ارتباط همیشه باز است — با هر پیام‌رسانی که دوست داری.', ''); ?>

<div class="grid grid-2">
  <section class="card" style="border:1.5px solid var(--brand2)">
    <div class="row" style="justify-content:space-between">
      <b style="font-size:14px">پیامک و تماس مستقیم</b>
      <?php echo joma_v2_icon('i-phoneoff', 'ic'); ?>
    </div>
    <p style="font-size:26px;font-weight:900;margin:10px 0" dir="ltr"><?php echo e($phone); ?></p>
    <div class="btn-row">
      <a class="btn" href="sms:+98<?php echo e(substr($phone, 1)); ?>">ارسال پیامک</a>
      <a class="btn sec" href="tel:+98<?php echo e(substr($phone, 1)); ?>">تماس</a>
      <button class="btn ghost" type="button" data-copy="<?php echo e($phone); ?>">کپی شماره</button>
    </div>
  </section>

  <section class="card">
    <b style="font-size:14px">پیام‌رسان‌ها</b>
    <div class="profile-list" style="margin-top:8px">
      <div class="hd-item"><b style="font-size:12.5px">بله (Bale)</b>
        <p class="tiny"><a href="https://bale.me/javad_mirbolouki" target="_blank" rel="noopener">پروفایل شخصی: @javad_mirbolouki</a></p>
        <p class="tiny"><a href="https://bale.me/jomaplanner" target="_blank" rel="noopener">کانال برنامهٔ جوما: @jomaplanner</a></p>
      </div>
      <div class="hd-item"><b style="font-size:12.5px">روبیکا</b>
        <p class="tiny"><a href="https://rubika.ir/javad_mirbolouki" target="_blank" rel="noopener">@javad_mirbolouki</a> — اگر لینک باز نشد، نام کاربری را در اپ جست‌وجو کن.</p>
      </div>
      <div class="hd-item"><b style="font-size:12.5px">اینستاگرام</b>
        <p class="tiny"><a href="https://instagram.com/Joma.mirbolouki" target="_blank" rel="noopener">صفحهٔ برنامهٔ جوما</a> · <a href="https://instagram.com/Javad_mirbolouki" target="_blank" rel="noopener">صفحهٔ جواد میربلوکی</a></p>
      </div>
    </div>
  </section>
</div>

<div class="grid grid-2">
  <section class="card">
    <div class="k">پشتیبانی چه کارهایی می‌کند؟</div>
    <div class="hd-item" style="margin-top:8px"><b style="font-size:12.5px">کد ساخت حساب</b>
      <p class="tiny">ساخت حساب با کد امنیتی قفل است تا هر کسی نتواند حساب بسازد. کد را پشتیبانی می‌دهد؛ <b>پیامکی نیست</b>.</p></div>
    <div class="hd-item"><b style="font-size:12.5px">کد بازیابی رمز</b>
      <p class="tiny">اگر رمزت را فراموش کردی، پس از احراز هویت، پشتیبانی یک کد یک‌بارمصرف (۶ رقمی، ۱۵ دقیقه، یک‌بارمصرف) صادر می‌کند. بعد از تغییر رمز، نشست‌های دیگر همان حساب بسته می‌شوند.</p></div>
    <div class="hd-item"><b style="font-size:12.5px">اشکال فنی</b>
      <p class="tiny">اگر جایی از برنامه درست کار نکرد، صفحه و کاری که انجام دادی را بنویس تا سریع بررسی شود.</p></div>
  </section>

  <section class="card" style="background:linear-gradient(150deg,#FFFFFF,var(--gold-soft))">
    <div class="k">قاعده‌های امنیتی</div>
    <p class="lede" style="margin-top:6px"><b>جوما هرگز رمز تو را نمی‌پرسد.</b> اگر کسی به نام پشتیبانی رمز خواست، مطمئن باش کلاهبرداری است.</p>
    <p class="lede">کد یک‌بارمصرف را در هیچ‌جای عمومی (گروه، شبکه‌های اجتماعی) نگذار.</p>
    <p class="lede">هیچ‌وقت رمزت را برای کسی نفرست؛ حتی برای ما.</p>
  </section>
</div>

<section class="card">
  <div class="k">سؤال‌های رایج</div>
  <details class="gl" style="margin-top:8px"><summary>حسابم را چطور بسازم؟</summary>
    <p class="lede" style="margin-top:6px">صفحهٔ ثبت‌نام را پر کن؛ در انتها کد امنیتی می‌خواهد. اگر کد را نداری، به پشتیبانی پیام بده.</p></details>
  <details class="gl"><summary>چرا ثبت برای روزهای آینده بسته است؟</summary>
    <p class="lede" style="margin-top:6px">جوما ثبت «واقعی» را نگه می‌دارد؛ ثبت آینده معنا ندارد. از ابتدای همین ماه تا امروز می‌توانی ثبت کنی.</p></details>
  <details class="gl"><summary>یادداشت‌هایم را چه کسی می‌بیند؟</summary>
    <p class="lede" style="margin-top:6px">یادداشت حال فقط برای خودت است و برای «هم‌مسیر» فرستاده نمی‌شود. وضعیت «جوجهٔ من» هم خصوصی است.</p></details>
  <details class="gl"><summary>عدد «موفقیت کلی» چطور حساب می‌شود؟</summary>
    <p class="lede" style="margin-top:6px">با وزن فعالیت‌ها و فقط روی چرخه‌های کامل‌شده؛ تا وقتی داده کافی نباشد، درصدی اعلام نمی‌شود. توضیح کامل در تب «تحلیل موفقیت» و پنل «این عددها یعنی چه؟» در گزارش‌ها هست.</p></details>
  <details class="gl"><summary>داده‌ام را می‌توانم بگیرم؟</summary>
    <p class="lede" style="margin-top:6px">بله — از پشتیبانی درخواست کن؛ گزارش خوانا، صفحه‌گسترده یا فایل پشتیبان، هر سه آماده می‌شود.</p></details>
</section>
<?php joma_footer(); ?>
