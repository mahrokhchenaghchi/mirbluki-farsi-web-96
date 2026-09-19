<?php
if (current_user()) joma_redirect('index.php?p=dashboard');
$err = '';
$old = array('first_name'=>'','last_name'=>'','username'=>'','email'=>'','phone'=>'','job'=>'سایر');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $in = array(
        'first_name' => trim(isset($_POST['first_name']) ? $_POST['first_name'] : ''),
        'last_name' => trim(isset($_POST['last_name']) ? $_POST['last_name'] : ''),
        'username' => strtolower(trim(isset($_POST['username']) ? $_POST['username'] : '')),
        'email' => strtolower(trim(isset($_POST['email']) ? $_POST['email'] : '')),
        'phone' => trim(isset($_POST['phone']) ? $_POST['phone'] : ''),
        'job' => trim(isset($_POST['job']) ? $_POST['job'] : ''),
        'password' => isset($_POST['password']) ? $_POST['password'] : '',
        'confirm' => isset($_POST['confirm']) ? $_POST['confirm'] : '',
        'accept' => !empty($_POST['accept']),
    );
    $old = $in;
    $err = '';
    if (function_exists('joma_registration_gate_check')) {
        $posted_code = function_exists('joma_registration_gate_posted_code')
            ? joma_registration_gate_posted_code()
            : (isset($_POST['security_code']) ? $_POST['security_code'] : null);
        $err = joma_registration_gate_check($posted_code);
    }
    if ($err === '') {
        $err = validate_registration($in);
    }
    if ($err === '') {
        $u = create_user($in);
        $_SESSION['user'] = session_user_array($u);
        joma_redirect('index.php?p=mood');
    }
}
// کدام گام خطا داشته؟ (۱ هویت · ۲ شغل · ۳ حساب · ۴ تماس و تأیید)
$errStep = ($_SERVER['REQUEST_METHOD'] === 'POST') ? 4 : 1;
if ($err !== '') {
    if (mb_strpos($err, 'نام کاربری') !== false || mb_strpos($err, 'ایمیل قبلاً') !== false || mb_strpos($err, 'رمز') !== false) $errStep = 3;
    elseif (mb_strpos($err, 'شغل') !== false) $errStep = 2;
    elseif (mb_strpos($err, 'نام و نام خانوادگی') !== false) $errStep = 1;
    elseif (mb_strpos($err, 'موبایل') !== false || mb_strpos($err, 'قوانین') !== false || mb_strpos($err, 'کد امنیتی') !== false) $errStep = 4;
}
joma_header('ثبت‌نام', array(), array('public' => 1));
?>
<div class="card auth-card" style="max-width:640px">
  <div style="text-align:center">
    <span class="logo-tile" style="margin:0 auto;width:48px;height:48px;border-radius:15px"><?php echo joma_v2_icon('owl-hi', 'ic lg'); ?></span>
    <h1 style="margin-top:10px">ساخت حساب جوما</h1>
    <p class="lede">چهار قدم کوتاه؛ هر قدم یک دستهٔ مربوط به هم. بعدش اولین برنامه‌ات را می‌سازی.</p>
  </div>

  <div class="mode-tabs">
    <a href="<?php echo e(joma_url('index.php?p=login')); ?>">ورود</a>
    <a class="on" href="<?php echo e(joma_url('index.php?p=register')); ?>">ثبت‌نام</a>
    <a href="<?php echo e(joma_url('index.php?p=forgot')); ?>">بازیابی رمز</a>
  </div>

  <form method="post" action="<?php echo e(joma_url('index.php?p=register')); ?>" id="reg-wizard" data-start="<?php echo (int) $errStep; ?>">
    <?php echo csrf_field(); ?>

    <div class="wdots" aria-label="مراحل ثبت‌نام">
      <button type="button" class="wdot" data-step="1"><i class="num">۱</i><span>معرفی</span></button>
      <button type="button" class="wdot" data-step="2"><i class="num">۲</i><span>شغل</span></button>
      <button type="button" class="wdot" data-step="3"><i class="num">۳</i><span>نام کاربری</span></button>
      <button type="button" class="wdot" data-step="4"><i class="num">۴</i><span>رمز</span></button>
      <button type="button" class="wdot" data-step="5"><i class="num">۵</i><span>تماس و تأیید</span></button>
    </div>
    <p class="wcount tiny" data-wcount>قدم ۱ از ۵</p>

    <!-- گام ۱ — معرفی -->
    <section class="wstep scene-sky" data-step="1">
      <div class="whead">
        <span class="wvisual"><?php echo joma_v2_icon('owl-hi', 'ic lg'); ?></span>
        <div><b class="wtitle">خودت را معرفی کن</b><p class="tiny">با همان نامی که دوست داری در خانه صدایت کنیم. فامیلی کامل لازم نیست.</p></div>
      </div>
      <div class="field-row" style="margin-top:10px">
        <div><label>نام</label><input name="first_name" aria-label="نام" autocomplete="given-name" value="<?php echo e($old['first_name']); ?>" required></div>
        <div><label>نام خانوادگی</label><input name="last_name" aria-label="نام خانوادگی" autocomplete="family-name" value="<?php echo e($old['last_name']); ?>" required></div>
      </div>
      <div class="wrow" style="justify-content:flex-end;margin-top:12px"><button type="button" class="btn sm" data-wnext>بعدی</button></div>
    </section>

    <!-- گام ۲ — شغل -->
    <section class="wstep scene-gold" data-step="2">
      <div class="whead">
        <span class="wvisual"><?php echo joma_v2_icon('i-target', 'ic lg'); ?></span>
        <div><b class="wtitle">شغلت چیست؟</b><p class="tiny">از فهرست انتخاب کن (۱۸ مورد). فقط برای هم‌سوکردن تجربهٔ توست، نه تبلیغ.</p></div>
      </div>
      <label style="margin-top:10px">شغل</label>
      <select name="job"><?php foreach (jobs_list() as $j) echo '<option'.($old['job']===$j?' selected':'').'>'.e($j).'</option>'; ?></select>
      <p class="tiny">نقش حساب همیشه «کاربری» ساخته می‌شود و در این فرم هیچ انتخاب نقشی وجود ندارد؛ ارتقای نقش فقط از مسیر پشتیبانی است.</p>
      <div class="wrow" style="justify-content:space-between;margin-top:12px">
        <button type="button" class="btn ghost sm" data-wback>قبلی</button>
        <button type="button" class="btn sm" data-wnext>بعدی</button>
      </div>
    </section>

    <!-- گام ۳ — نام کاربری -->
    <section class="wstep scene-rose" data-step="3">
      <div class="whead">
        <span class="wvisual"><?php echo joma_v2_icon('i-eye', 'ic lg'); ?></span>
        <div><b class="wtitle">نام کاربری‌ات</b><p class="tiny">با این نام وارد می‌شوی. حروف انگلیسی و عدد؛ ۳ تا ۲۰ نویسه.</p></div>
      </div>
      <label style="margin-top:10px">نام کاربری</label>
      <input name="username" aria-label="نام کاربری" data-live-username dir="ltr" autocomplete="username" value="<?php echo e($old['username']); ?>" required>
      <div id="user-hints" class="hint"></div>
      <div class="wrow" style="justify-content:space-between;margin-top:12px">
        <button type="button" class="btn ghost sm" data-wback>قبلی</button>
        <button type="button" class="btn sm" data-wnext>بعدی</button>
      </div>
    </section>

    <!-- گام ۴ — رمز -->
    <section class="wstep scene-indigo" data-step="4">
      <div class="whead">
        <span class="wvisual"><?php echo joma_v2_icon('i-lock', 'ic lg'); ?></span>
        <div><b class="wtitle">رمز عبورت</b><p class="tiny">حداقل ۶ نویسه. رمز و تکرارش باید یکی باشند.</p></div>
      </div>
      <div class="field-row" style="margin-top:10px">
        <div>
          <label>رمز عبور</label>
          <div style="position:relative">
            <input type="password" name="password" id="reg-pass" aria-label="رمز عبور" autocomplete="new-password" required minlength="6">
            <button type="button" class="icon-btn" data-pass-toggle="reg-pass" aria-label="نمایش رمز"
              style="position:absolute;inset-inline-start:6px;top:50%;transform:translateY(-50%);box-shadow:none;background:transparent;border:0">
              <?php echo joma_v2_icon('i-eye', 'ic'); ?>
            </button>
          </div>
        </div>
        <div><label>تکرار رمز عبور</label><input type="password" name="confirm" aria-label="تکرار رمز عبور" autocomplete="new-password" required minlength="6"></div>
      </div>
      <div id="pass-hints" class="hint"></div>
      <div class="wrow" style="justify-content:space-between;margin-top:12px">
        <button type="button" class="btn ghost sm" data-wback>قبلی</button>
        <button type="button" class="btn sm" data-wnext>بعدی</button>
      </div>
    </section>

    <!-- گام ۵ — تماس و تأیید -->
    <section class="wstep scene-coral" data-step="5">
      <div class="whead">
        <span class="wvisual"><?php echo joma_v2_icon('i-phoneoff', 'ic lg'); ?></span>
        <div><b class="wtitle">راه‌های تماس و تأیید نهایی</b><p class="tiny">موبایل و ایمیل فقط برای بازیابی حساب و پشتیبانی است — هرگز برای تبلیغ.</p></div>
      </div>
      <div class="field-row" style="margin-top:10px">
        <div>
          <label>شماره موبایل</label>
          <input name="phone" data-live-phone dir="ltr" inputmode="numeric" autocomplete="tel" placeholder="09123456789" value="<?php echo e($old['phone']); ?>" required>
          <div id="phone-hints" class="hint"></div>
        </div>
        <div>
          <label>ایمیل</label>
          <input type="email" name="email" aria-label="ایمیل" data-live-email dir="ltr" autocomplete="email" value="<?php echo e($old['email']); ?>" required>
          <div id="email-hints" class="hint"></div>
        </div>
      </div>

      <div class="card" style="background:var(--brand-softer);border:0;margin-top:10px">
        <div class="k">خلاصهٔ اطلاعات</div>
        <div class="wsum">
          <span class="chip n" data-sum="name">نام: —</span>
          <span class="chip n" data-sum="job">شغل: —</span>
          <span class="chip n" data-sum="username">نام کاربری: —</span>
          <span class="chip n" data-sum="contact">تماس: —</span>
        </div>
        <p class="tiny" style="margin-top:6px">یک‌بار همه را ببین؛ اگر چیزی را عوض می‌خواهی، با «قبلی» برگرد.</p>
      </div>

      <?php
      if (function_exists('joma_registration_gate_ui')) {
          echo joma_registration_gate_ui(isset($_POST['security_code']) && is_string($_POST['security_code']) ? $_POST['security_code'] : '');
      }
      ?>
      <label style="display:flex;gap:8px;align-items:flex-start;font-weight:700;color:var(--ink-2);margin-top:10px">
        <input type="checkbox" name="accept" value="1" required style="margin-top:6px">
        <span>قوانین و فلسفه جوما را می‌پذیرم. جوما محصول خودمدیریتی است: برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود. <a href="<?php echo e(joma_url('index.php?p=about')); ?>">درباره جوما</a></span>
      </label>

      <?php if ($err) echo '<p class="bad">' . e($err) . '</p>'; ?>
      <div class="wrow" style="justify-content:space-between;margin-top:12px">
        <button type="button" class="btn ghost sm" data-wback>قبلی</button>
        <button class="btn" type="submit">ساخت حساب جدید</button>
      </div>
      <p class="tiny" style="margin-top:8px">کد امنیتی را از پشتیبانی می‌گیری: <b dir="ltr">09967979471</b> — این کد پیامکی نیست.</p>
    </section>
  </form>

  <p class="tiny" style="text-align:center;margin-top:10px">قبلاً حساب ساختی؟ <a href="<?php echo e(joma_url('index.php?p=login')); ?>">وارد شو</a></p>
</div>
<?php joma_footer(); ?>
