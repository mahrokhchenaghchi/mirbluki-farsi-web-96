<?php
/**
 * JOMA — صفحهٔ معرفی (لندینگ) — طرح تازه، مطابق SPEC/10
 * هفت ناحیه: نوار بالا · قهرمان · سه قدم · کارها+حال · جوجهٔ من · دعوت پایانی · فوتر
 * هیچ آمار بی‌منبعی در این صفحه نیست؛ تنها عدد، «۱۰۷ فعالیت» است که از خود کتابخانه می‌آید.
 */
$__lib = function_exists('official_library') ? official_library() : array();
$__libCount = count($__lib);
if ($__libCount < 1) $__libCount = 107;
$u = current_user();
joma_header('جوما — برنامه‌ریزی، اجرا، فهم', array(), array('public' => 1));
?>
<div class="land">

  <div class="lnav">
    <span class="logo">
      <span class="logo-tile"><?php echo joma_v2_icon('owl-hi', 'lg'); ?></span>
      <span><b>جوما</b><small>برنامه. اجرا. فهم.</small></span>
    </span>
    <span class="sp"></span>
    <?php if ($u) { ?>
      <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=dashboard')); ?>">داشبورد من</a>
    <?php } else { ?>
      <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
      <a class="btn sm" href="<?php echo e(joma_url('index.php?p=register')); ?>">شروع رایگان</a>
    <?php } ?>
  </div>

  <!-- ۲) قهرمان -->
  <section class="hero">
    <div>
      <span class="kicker">محصول مستقل خودمدیریتی</span>
      <h1>برنامه‌ات را بگذار، هر روز ثبت کن، و ببین مسیرت چطور پیش می‌رود.</h1>
      <p class="lead">جوما کارها و حالت را کنار هم می‌بیند: چند ثانیه ثبت می‌کنی، بعد نمودار و معنی‌اش را می‌بینی — بدون قضاوت، بدون ادعای درمان.</p>
      <div class="cta">
        <a class="btn" href="<?php echo e(joma_url('index.php?p=register')); ?>">ساخت حساب رایگان</a>
        <a class="btn sec" href="<?php echo e(joma_url('index.php?p=login')); ?>">وارد می‌شوم</a>
      </div>
      <div class="hero-badges">
        <span class="chip g"><?php echo fa_num($__libCount); ?> فعالیت آماده</span>
        <span class="chip s">برنامهٔ ماهانه ساده</span>
        <span class="chip go">گزارش با معنی</span>
      </div>
    </div>

    <!-- نمای نمونهٔ محصول (نمایشی — همه‌چیز برچسب «نمونه» دارد) -->
    <div class="phone">
      <div class="prow" style="border-bottom:1px solid var(--card-brd);padding-bottom:9px">
        <span class="logo-tile" style="width:30px;height:30px;border-radius:10px"><?php echo joma_v2_icon('owl-hi'); ?></span>
        <b style="font-size:12.5px">یک روز در جوما</b>
        <span class="sp" style="flex:1"></span>
        <span class="chip">نمایش نمونه</span>
      </div>
      <p class="tiny" style="margin:8px 0 4px">شنبه، ۳ شهریور</p>
      <div class="prow" style="border:0">
        <span style="width:26px;height:26px"><?php echo joma_v2_icon('owl-hi', 'ic lg'); ?></span>
        <span style="font-size:12.5px">سلام سارا، روزت چطور است؟</span>
      </div>
      <div class="prow"><span><?php echo joma_v2_icon('i-drop'); ?></span><span>آب</span><span class="sp" style="flex:1"></span><b>۶ از ۶ لیوان</b></div>
      <div class="prow"><span><?php echo joma_v2_icon('i-walk'); ?></span><span>پیاده‌روی</span><span class="sp" style="flex:1"></span><b>۲۰ دقیقه</b></div>
      <div class="prow"><span><?php echo joma_v2_icon('i-heart'); ?></span><span>حال امروز</span><span class="sp" style="flex:1"></span><b>ثبت شد</b></div>
      <div class="prow"><a href="<?php echo e(joma_url('index.php?p=register')); ?>">رفتن به کارهای امروز ←</a></div>
    </div>
  </section>

  <!-- ۳) سه قدم -->
  <h2 style="margin:10px 0 12px">جوما در سه قدم</h2>
  <section class="feat3">
    <div class="card">
      <span class="owl"><?php echo joma_v2_icon('owl-think', 'ic lg'); ?></span>
      <b>۰۱ — انتخاب کن</b>
      <p class="lede">از <?php echo fa_num($__libCount); ?> فعالیت آماده، آن‌چه با زندگی‌ات جور است را انتخاب کن و یک هدف بگذار.</p>
    </div>
    <div class="card">
      <span class="owl"><?php echo joma_v2_icon('owl-hi', 'ic lg'); ?></span>
      <b>۰۲ — هر روز ثبت کن</b>
      <p class="lede">چند ثانیه: کاری که کردی و حالی که داشتی. تمام.</p>
    </div>
    <div class="card">
      <span class="owl"><?php echo joma_v2_icon('owl-cheer', 'ic lg'); ?></span>
      <b>۰۳ — مسیرت را ببین</b>
      <p class="lede">جوما نشان می‌دهد چطور پیش رفته‌ای — و کنار هر عدد می‌نویسد یعنی چه.</p>
    </div>
  </section>

  <!-- ۴) کارها + حال -->
  <section class="calm" style="margin-top:18px">
    <div>
      <span class="kicker">تفاوت اصلی</span>
      <h2 style="margin-top:6px">کارها و حالت، کنار هم</h2>
      <p class="lede" style="margin-top:8px">پنج شاخص کوتاه حال را ثبت می‌کنی و کنارش ثبت‌های همان روز را می‌بینی. گزارش‌ها بعداً نشان می‌دهند کدام روزها با هم هماهنگ بوده‌اند — با این جملهٔ صادقانه که «همبستگی، علت نیست».</p>
      <div class="btn-row" style="margin-top:12px">
        <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=register')); ?>">شروع کن</a>
      </div>
    </div>
    <div class="card" style="background:var(--card)">
      <b style="font-size:13px">ثبت حال امروز</b>
      <p class="tiny">انرژی · حال عمومی · تمرکز · خواب · استرس</p>
      <div class="mood-bars" style="border:0;box-shadow:none;padding:8px 0">
        <div class="row" style="justify-content:space-between"><span class="meta">انرژی</span><span class="chip g">۴ از ۵</span></div>
        <div class="bar" style="margin:6px 0"><i style="width:80%"></i></div>
        <div class="row" style="justify-content:space-between"><span class="meta">خواب</span><span class="chip s">۴ از ۵</span></div>
        <div class="bar" style="margin:6px 0"><i style="width:80%"></i></div>
        <div class="row" style="justify-content:space-between"><span class="meta">استرس</span><span class="chip go">۲ از ۵</span></div>
        <div class="bar" style="margin:6px 0"><i style="width:40%"></i></div>
      </div>
      <p class="tiny">نمایش نمونه — در حساب واقعی، همهٔ این‌ها از ثبت خودت می‌آید.</p>
    </div>
  </section>

  <!-- ۵) جوجهٔ من -->
  <section class="petband" style="margin-top:18px">
    <span class="owl"><?php echo joma_v2_icon('owl-logo', 'ic lg'); ?></span>
    <div>
      <b style="font-size:14.5px">جوجهٔ من — همراه مسیر تو</b>
      <p class="lede" style="margin-top:6px">با هر ثبت واقعی، یک دونه به ظرف دونه اضافه می‌شود؛ ثبت آب و ثبت حال هم سنجه‌های خودش را دارند. جوجه نه می‌میرد، نه بیمار می‌شود و هیچ‌وقت با کلیک پر نمی‌شود.</p>
    </div>
  </section>

  <!-- ۶) دعوت پایانی -->
  <section class="cta-band" id="cta">
    <h2>همین امروز یک قدم کوچک بردار</h2>
    <p style="color:#EAFBF4;margin-top:6px">ساخت حساب چند ثانیه است. بعدش اولین برنامه‌ات را می‌سازی.</p>
    <a class="btn" href="<?php echo e(joma_url('index.php?p=register')); ?>">ساخت حساب رایگان</a>
  </section>

  <!-- ۷) فوتر در layout چاپ می‌شود -->
</div>
<?php joma_footer(); ?>
