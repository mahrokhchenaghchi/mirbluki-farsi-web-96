<?php
/**
 * JOMA — درباره جوما (طرح نسخهٔ ۲۰)
 * محتوای صفحهٔ قبلی حفظ شده است (چرا ساخته شد، فلسفه، شیوهٔ کار، پشتوانهٔ فکری، بنیان‌گذار، جغد دانا).
 * این نسخه داخل پوستهٔ خود جوما می‌نشیند و از همان طراحی استفاده می‌کند.
 */
$u = current_user();
joma_header('درباره جوما', array(), $u ? array() : array('public' => 1));
?>
<div class="land">
  <section class="hero" style="padding-top:8px">
    <div>
      <span class="kicker">محصول مستقل خودمدیریتی</span>
      <h1>دربارهٔ جوما؛ جغد دانا</h1>
      <p class="lead">جوما تلاش می‌کند فاصلهٔ میان «دانستن» و «عمل کردن» را کمتر کند؛ با تبدیل هدف‌ها و تصمیم‌های ذهنی به فعالیت‌هایی که بتوان آن‌ها را مشاهده، ثبت و دنبال کرد.</p>
      <div class="hero-badges">
        <span class="chip g">رشد فردی</span>
        <span class="chip s">عادت‌سازی</span>
        <span class="chip go">برنامه‌ریزی</span>
        <span class="chip">خودآگاهی</span>
      </div>
    </div>
    <div class="card" style="text-align:center;background:linear-gradient(150deg,#FFFFFF,var(--brand-softer))">
      <div style="display:grid;place-items:center"><?php echo joma_v2_icon('owl-logo', 'ic lg'); ?></div>
      <b style="display:block;margin-top:8px">جوما | جغد دانا</b>
      <p class="tiny">برنامه‌ریزی، اجرا، فهم — بدون ادعای درمان.</p>
    </div>
  </section>

  <div class="grid grid-2" style="margin-top:6px">
    <section class="card">
      <div class="k">جوما چیست؟</div>
      <p class="lede" style="margin-top:6px">جوما یک محصول مستقل خودمدیریتی برای انتخاب فعالیت‌های مهم زندگی، ثبت عملکرد، دیدن میزان پایبندی و مشاهدهٔ مسیر رشد فردی است.</p>
      <p class="lede" style="margin-top:6px">در جوما قرار نیست فقط فهرستی از کارها داشته باشی؛ هدف این است که رفتارهای مؤثر زندگی به فعالیت‌هایی قابل مشاهده، قابل ثبت و قابل پیگیری تبدیل شوند. خواب، ورزش، مطالعه، یادگیری، تمرکز، سلامت روان و روابط می‌توانند بخشی از این مسیر باشند.</p>
    </section>
    <section class="card">
      <div class="k">چرا ساخته شد؟</div>
      <p class="lede" style="margin-top:6px">بسیاری از ما می‌دانیم چه چیزی برایمان خوب است، اما دانستن همیشه به معنای انجام دادن نیست. فهرستی طولانی از هدف‌ها می‌سازیم، برنامه می‌نویسیم و بعد از مدتی رهایش می‌کنیم.</p>
      <p class="lede" style="margin-top:6px">جوما برای پر کردن همین فاصله شکل گرفت: این‌که برنامه‌ریزی از یک فهرست آرزو، به فرآیندی قابل مشاهده تبدیل شود. این‌جا به‌جای فشار برای کامل بودن، نگاه به روند واقعی تغییر معطوف می‌شود.</p>
    </section>
  </div>

  <h2 style="margin:22px 0 10px">فلسفهٔ جوما</h2>
  <div class="feat3">
    <div class="card">
      <span class="icon-btn" style="width:44px;height:44px;border-radius:14px;background:var(--brand-softer);border:0"><?php echo joma_v2_icon('i-trend', 'ic lg'); ?></span>
      <b>کمتر کمال‌گرا، بیشتر پیوسته</b>
      <p class="lede">یک روز ضعیف به معنای شکست کامل نیست و یک روز عالی هم به معنای رسیدن به مقصد. آن‌چه اهمیت دارد، دیدن روند و ادامه دادن است.</p>
    </div>
    <div class="card">
      <span class="icon-btn" style="width:44px;height:44px;border-radius:14px;background:var(--sky-soft);border:0"><?php echo joma_v2_icon('i-target', 'ic lg'); ?></span>
      <b>از هدف به رفتار</b>
      <p class="lede">هدف‌ها مهم‌اند، اما هدف به‌تنهایی رفتار نمی‌سازد. جوما هدف‌های بزرگ را به فعالیت‌های روزمرهٔ قابل ثبت تبدیل می‌کند.</p>
    </div>
    <div class="card">
      <span class="icon-btn" style="width:44px;height:44px;border-radius:14px;background:var(--gold-soft);border:0"><?php echo joma_v2_icon('i-check', 'ic lg'); ?></span>
      <b>از روی کاغذ تا زندگی واقعی</b>
      <p class="lede">برنامه‌ای ارزشمند است که وارد زندگی واقعی شود؛ جوما رابطهٔ برنامه، رفتار و استمرار را دیدنی می‌کند.</p>
    </div>
  </div>

  <h2 style="margin:22px 0 10px">جوما چگونه کمک می‌کند؟</h2>
  <div class="grid grid-2">
    <?php
    $steps = array(
        array('۱ — انتخاب فعالیت‌ها', 'از کتابخانهٔ فعالیت‌ها، آن‌چه با هدف و اولویت تو جور است را انتخاب می‌کنی.'),
        array('۲ — برنامه‌ریزی', 'فعالیت‌ها را در برنامهٔ ماه می‌گذاری؛ هدف و اهمیت هر کدام را خودت تعیین می‌کنی.'),
        array('۳ — ثبت عملکرد', 'میزان انجام هر فعالیت را در طول زمان ثبت می‌کنی؛ چند ثانیه در روز.'),
        array('۴ — مشاهدهٔ روند', 'به‌جای حدس زدن، روند واقعی فعالیت‌ها و پایبندی‌ات را در گزارش‌ها می‌بینی.'),
    );
    foreach ($steps as $st) {
        echo '<section class="card"><b style="font-size:13px">' . e($st[0]) . '</b><p class="lede" style="margin-top:6px">' . e($st[1]) . '</p></section>';
    }
    ?>
  </div>

  <section class="card" style="margin-top:16px;background:linear-gradient(150deg,#FFFFFF,var(--brand-softer))">
    <div class="k">پشتوانهٔ فکری</div>
    <b style="display:block;margin-top:6px;font-size:13.5px">جوما و روان‌شناسی رفتار</b>
    <p class="lede" style="margin-top:6px">جوما با الهام از مفاهیم روان‌شناسی نوین، علوم رفتار و اصول شکل‌گیری عادت طراحی شده است. در طراحی آن تلاش شده برنامه‌ریزی به ابزاری برای فشار و کمال‌گرایی تبدیل نشود؛ بلکه کاربر رفتار خودش را ببیند، عملکردش را بهتر بشناسد و آگاهانه‌تر تصمیم بگیرد.</p>
    <p class="lede" style="margin-top:8px">«قرار نیست کامل باشیم؛ قرار است آگاهانه‌تر ببینیم، انتخاب کنیم و ادامه بدهیم.»</p>
    <p class="tiny" style="margin-top:8px">جوما ابزار خودمدیریتی است، نه درمان. اگر حال تو بد است یا فکر آسیب به خودت داری، همین حالا با اورژانس اجتماعی ۱۲۳ یا یک متخصص تماس بگیر.</p>
  </section>

  <h2 style="margin:22px 0 10px">طراح و بنیان‌گذار</h2>
  <div class="petband">
    <span class="icon-btn" style="width:56px;height:56px;border-radius:18px;background:var(--brand-softer);border:0"><?php echo joma_v2_icon('owl-logo', 'ic lg'); ?></span>
    <div>
      <b style="font-size:14px">جواد میربلوکی</b>
      <p class="lede" style="margin-top:4px">روان‌شناس، روان‌درمانگر و متخصص زوج‌درمانی و سکس‌تراپی.</p>
      <p class="lede" style="margin-top:4px">جوما حاصل پیوند دانش روان‌شناسی، تجربهٔ عملی و اصول برنامه‌ریزی رفتاری است؛ ایده‌ای که می‌خواهد مفاهیم رشد فردی و تغییر رفتار در قالب یک ابزار کاربردی وارد زندگی روزمره شود.</p>
      <p class="tiny" style="margin-top:6px">توسعه و اجرای جوما: ماهرخ چناقچی · برندینگ «جغد دانا» در ایران.</p>
    </div>
  </div>

  <section class="card" style="margin-top:16px">
    <div class="k">چرا «جغد دانا»؟</div>
    <p class="lede" style="margin-top:6px">جغد در فرهنگ‌های مختلف با دانایی، مشاهده‌گری و نگاه عمیق پیوند خورده است. «جغد دانا» در هویت جوما نماد این نگاه است: پیش از عجله برای تغییر، ببینیم و آگاهانه انتخاب کنیم.</p>
  </section>

  <section class="cta-band">
    <h2>آماده‌ای مسیرت را ببینی؟</h2>
    <p style="color:#EAFBF4;margin-top:6px">اولین برنامه‌ات چند دقیقه وقت می‌گیرد.</p>
    <?php if ($u) { ?>
      <a class="btn" href="<?php echo e(joma_url('index.php?p=dashboard')); ?>">داشبورد من</a>
    <?php } else { ?>
      <a class="btn" href="<?php echo e(joma_url('index.php?p=register')); ?>">ساخت حساب رایگان</a>
    <?php } ?>
  </section>
</div>
<?php joma_footer(); ?>
