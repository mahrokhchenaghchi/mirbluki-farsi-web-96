<?php
/**
 * JOMA — حال من (ویزارد پنج‌قدمی) — طبق SPEC/13
 * پنج قدم + قدم ششمِ اختیاری (جملهٔ شخصی، فقط برای خودت).
 * نام فیلدهای فرم همان قبلی است (energy/general/focus/sleep/stress/note) و اعتبارسنجی سرور دست‌نخورده.
 */
require_login();
$u = current_user();
$today = jalali_today();
$err = '';
$errStep = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $scores = array(
        'energy' => (int) (isset($_POST['energy']) ? $_POST['energy'] : 0),
        'general' => (int) (isset($_POST['general']) ? $_POST['general'] : 0),
        'focus' => (int) (isset($_POST['focus']) ? $_POST['focus'] : 0),
        'sleep' => (int) (isset($_POST['sleep']) ? $_POST['sleep'] : 0),
        'stress' => (int) (isset($_POST['stress']) ? $_POST['stress'] : 0),
    );
    // ترتیب مراحل در ویزارد: حال · انرژی · تمرکز · خواب دیشب · استرس
    $order = array('general' => 1, 'energy' => 2, 'focus' => 3, 'sleep' => 4, 'stress' => 5);
    foreach ($order as $k => $n) {
        if ($scores[$k] < 1 || $scores[$k] > 5) { $err = 'هر پنج شاخص را انتخاب کنید.'; $errStep = $n; break; }
    }
    if ($err === '') {
        // دکمهٔ «بدون جمله» هم همان مسیر را می‌رود و متن خالی ذخیره می‌شود (هرگز خاکستری نمی‌شود).
        save_mood($u['id'], $today, $scores, isset($_POST['note']) ? $_POST['note'] : '');
        joma_redirect('index.php?p=dashboard');
    }
}
$ex = get_mood($u['id'], $today);
$v = array(
    'general' => $ex ? (int) $ex['general_mood'] : 0,
    'energy' => $ex ? (int) $ex['energy'] : 0,
    'focus' => $ex ? (int) $ex['focus'] : 0,
    'sleep' => $ex ? (int) $ex['sleep_quality'] : 0,
    'stress' => $ex ? (int) $ex['stress'] : 0,
);
$ui = array(
    'general' => array('label' => 'حال', 'title' => 'امروز چه حسی داری؟', 'hint' => 'اول از همه، حال کلی‌ات. نزدیک‌ترین چهره را انتخاب کن.', 'scene' => 'rose',
        'opts' => array(1 => array('😖', 'خیلی بد'), 2 => array('🙁', 'خوب نیستم'), 3 => array('😐', 'معمولی'), 4 => array('🙂', 'خوب'), 5 => array('😄', 'عالی'))),
    'energy' => array('label' => 'انرژی', 'title' => 'باتری امروزت چقدر شارژ دارد؟', 'hint' => 'انرژی بدنی و ذهنی‌ات را در نظر بگیر.', 'scene' => 'gold', 'svg' => 'battery',
        'opts' => array(1 => array('', 'خیلی کم'), 2 => array('', 'کم'), 3 => array('', 'متوسط'), 4 => array('', 'زیاد'), 5 => array('', 'پُر'))),
    'focus' => array('label' => 'تمرکز', 'title' => 'تمرکزت چطور بود؟', 'hint' => 'نگاهت به کارها و کارهای روزانه‌ات چطور بود؟', 'scene' => 'sky', 'svg' => 'rings',
        'opts' => array(1 => array('', 'خیلی سخت'), 2 => array('', 'سخت'), 3 => array('', 'متوسط'), 4 => array('', 'آسان'), 5 => array('', 'خیلی آسان'))),
    'sleep' => array('label' => 'خواب دیشب', 'title' => 'خواب دیشبت چطور بود؟', 'hint' => 'کیفیت خواب شب قبل را در نظر بگیر؛ نه تعداد ساعت‌هایی که خوابیدی.', 'scene' => 'indigo', 'svg' => 'moon',
        'opts' => array(1 => array('', 'خیلی ضعیف'), 2 => array('', 'ضعیف'), 3 => array('', 'متوسط'), 4 => array('', 'خوب'), 5 => array('', 'عالی'))),
    'stress' => array('label' => 'استرس', 'title' => 'بادکنک استرست چقدر پف کرده؟', 'hint' => '۱ = خیلی کم · ۵ = خیلی زیاد. استرس جدا از بقیه گزارش می‌شود.', 'scene' => 'coral', 'svg' => 'balloon',
        'opts' => array(1 => array('', 'خیلی کم'), 2 => array('', 'کم'), 3 => array('', 'متوسط'), 4 => array('', 'زیاد'), 5 => array('', 'خیلی زیاد'))),
);
$map = array('general' => 'general', 'sleep' => 'sleep');
// نگاشت هر شاخص به نوع تصویر اختصاصی‌اش (SPEC/13 §۳)
$imap = array(
    'general' => 'general',   // پنج چهره
    'energy' => 'energy',     // باتری با پرشدگی
    'focus' => 'focus',       // حلقه‌های هدف
    'sleep' => 'sleep',       // ماه و ستاره
    'stress' => 'stress',     // بادکنک
);
if ($errStep > 0) {
    $startStep = $errStep;
} else {
    // اولین پرسش بی‌پاسخ؛ اگر همه پاسخ داده شده، از قدم ششم (جملهٔ اختیاری) شروع کن.
    $startStep = 6;
    foreach (array('general' => 1, 'energy' => 2, 'focus' => 3, 'sleep' => 4, 'stress' => 5) as $k => $n) {
        if ($v[$k] === 0) { $startStep = $n; break; }
    }
}
joma_header('حال من', array());
?>
<?php joma_v2_pagehead('حال من', 'امروز چطوری؟', 'پنج پرسش کوتاه، یکی‌یکی — و در پایان یک جملهٔ اختیاری برای خودت.',
  function_exists('joma_v2_time_sticker')
    ? '<span style="display:inline-flex;align-items:center;gap:6px">' . joma_v2_time_sticker(null, 32) . '<span>' . e(joma_v2_greeting($u)) . '</span></span>'
    : ''
); ?>

<?php if ($ex) { ?>
  <div class="card" style="border:1.5px solid var(--brand2)">حال امروز ثبت شده است؛ اگر خواستی می‌توانی اصلاحش کنی.</div>
<?php } ?>

<form method="post" id="mood-wizard" data-start="<?php echo (int) $startStep; ?>">
  <?php echo csrf_field(); ?>

  <div class="wdots" role="tablist" aria-label="مراحل ثبت حال">
    <?php $i = 0; foreach ($ui as $key => $cfg) { $i++;
        echo '<button type="button" class="wdot" data-step="' . $i . '" role="tab">'
           . '<i class="num">' . fa_num($i) . '</i><span>' . e($cfg['label']) . '</span></button>';
    } ?>
    <button type="button" class="wdot" data-step="6" role="tab"><i class="num">✎</i><span>یک جمله</span></button>
  </div>
  <p class="wcount tiny" data-wcount>قدم ۱ از ۵</p>

  <?php $i = 0; foreach ($ui as $key => $cfg) { $i++; ?>
    <section class="card wstep scene-<?php echo e($cfg['scene']); ?>" data-step="<?php echo $i; ?>">
      <div class="whead">
        <div>
          <b class="wtitle"><?php echo e($cfg['title']); ?></b>
          <p class="tiny"><?php echo e($cfg['hint']); ?></p>
        </div>
      </div>
      <div class="wopts">
        <?php foreach ($cfg['opts'] as $score => $opt) { ?>
          <label class="wopt<?php echo ($v[$key] === $score) ? ' on' : ''; ?>"
                 title="<?php echo e($cfg['label'] . ' — ' . $opt[1]); ?>">
            <input type="radio" name="<?php echo e($key); ?>" value="<?php echo $score; ?>" <?php echo ($v[$key] === $score) ? 'checked' : ''; ?>>
            <span class="wpic"><?php echo joma_mood_option_svg($imap[$key], $score, 34); ?></span>
            <span class="wlabel"><?php echo e($opt[1]); ?></span>
          </label>
        <?php } ?>
      </div>
      <div class="wrow" style="justify-content:space-between;margin-top:12px">
        <button type="button" class="btn ghost sm" data-wback>قبلی</button>
        <button type="button" class="btn sm" data-wnext>بعدی</button>
      </div>
    </section>
  <?php } ?>

  <section class="card wstep" data-step="6">
    <div class="wrow" style="gap:12px;align-items:center;margin-bottom:6px">
      <span class="owl-float" style="flex:none;width:64px;height:64px;display:grid;place-items:center"><?php echo joma_v2_icon('owl-cheer', 'ic lg'); ?></span>
      <div><b style="font-size:13.5px">پنج پرسش تمام شد</b>
        <p class="lede">اگر خواستی، یک جمله هم برای خودت بنویس — یا بدون جمله تمام کن.</p></div>
    </div>
    <div class="k">قدم ششم — یک جمله برای خودت (اختیاری)</div>
    <p class="tiny">این جمله در «دفترچهٔ جوما» کنار همین روز می‌نشیند و برای هیچ‌کس فرستاده نمی‌شود.</p>
    <textarea name="note" placeholder="امروز را در یک جمله بنویس…" style="margin-top:8px"><?php echo $ex ? e($ex['note']) : ''; ?></textarea>
    <p class="tiny" id="mood-skip-note" style="margin-top:8px">بدون جمله — هر وقت خواستی می‌توانی بنویسی.</p>
    <div class="wrow" style="justify-content:space-between;margin-top:12px">
      <button type="button" class="btn ghost sm" data-wback>قبلی</button>
      <span class="wrow" style="gap:8px">
        <button class="btn sec" type="submit" name="skip_note" value="1">بدون جمله</button>
        <button class="btn" type="submit">ذخیرهٔ حال امروز</button>
      </span>
    </div>
    <p class="tiny" style="margin-top:6px">این جمله فقط برای خودت است؛ برای کسی فرستاده نمی‌شود.</p>
  </section>

  <?php if ($err) echo '<p class="toast bad">' . e($err) . '</p>'; ?>
</form>

<section class="card">
  <div class="k">چرا این پنج شاخص؟</div>
  <p class="lede" style="margin-top:6px">چهار شاخص (حال عمومی، انرژی، تمرکز، خواب) و استرس جدا ثبت می‌شوند. هیچ‌کدام در «موفقیت کلی» حساب نمی‌شوند؛ حال، تصویر روزت است، نه نمرهٔ تو.</p>
</section>
<?php joma_footer(); ?>
