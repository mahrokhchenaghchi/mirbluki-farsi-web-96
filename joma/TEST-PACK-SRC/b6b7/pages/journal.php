<?php
/**
 * JOMA — دفترچهٔ جوما (B7)
 * دو بخش: «از ثبت‌های تو» (بینش‌های واقعی با شواهد) و «نوشته‌های خودت» (یادداشت‌های حال).
 * قواعد سند ۱۵: بدون داده، بینش ساخته نمی‌شود · شواهد اجباری · جملهٔ عدم قطعیت بخشی از متن ·
 * هیچ اشتراک‌گذاری با هم‌مسیر · هیچ گیمیفیکیشن.
 */
require_login();
$u = current_user();
$wfn = dirname(__FILE__) . '/../functions/water.php';
if (is_file($wfn)) require_once $wfn;
$ifn = dirname(__FILE__) . '/../functions/insight.php';
if (is_file($ifn)) require_once $ifn;

$msg = '';
$err = '';
$today = jalali_today();

/* ---------- اقدام‌ها ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = isset($_POST['action']) ? $_POST['action'] : '';
    if ($act === 'save_note') {
        $date = isset($_POST['date']) && jalali_is_valid($_POST['date']) ? $_POST['date'] : $today;
        if (!function_exists('joma_journal_note_save')) {
            $err = 'این بخش در دسترس نیست.';
        } else {
            $res = joma_journal_note_save((int) $u['id'], $date, isset($_POST['note']) ? $_POST['note'] : '');
            if (!empty($res['ok'])) $msg = 'یادداشت ذخیره شد.';
            else $err = $res['error'];
        }
    } elseif ($act === 'delete_note') {
        $date = isset($_POST['date']) ? $_POST['date'] : '';
        $res = function_exists('joma_journal_note_delete') ? joma_journal_note_delete((int) $u['id'], $date) : array('ok' => false, 'error' => 'این بخش در دسترس نیست.');
        if (!empty($res['ok'])) $msg = 'یادداشت پاک شد. (رکورد حالِ همان روز دست‌نخورده است.)';
        else $err = $res['error'];
    }
}

/* ---------- دادهٔ دورهٔ جاری برای بینش ---------- */
$key = current_period_key();
$wp = ensure_period($u['id'], $key);
$plan = $wp['plan'];
$b = jalali_period_bounds($key);
$acts = list_plan_activities($plan['id'], $u['id']);
$evs = list_events($plan['id'], $u['id']);
$moods = list_moods($u['id'], $b['start'], $b['end']);

$ins = function_exists('joma_insights_build')
    ? joma_insights_build($plan, $acts, $evs, $moods, $today)
    : array('status' => 'INSUFFICIENT_DATA', 'insights' => array(), 'computedAt' => '');

/* ---------- دیده‌شدن ---------- */
$filter = isset($_GET['show']) ? $_GET['show'] : 'all';
if (!in_array($filter, array('all', 'personal', 'notes'), true)) $filter = 'all';
if ($filter === 'all' && function_exists('joma_journal_mark_seen')) {
    joma_journal_mark_seen((int) $u['id'], $ins['computedAt']);
}

/* ---------- نوشته‌های خودت ---------- */
$q = trim(isset($_GET['q']) ? $_GET['q'] : '');
$page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
$per = 20;
$notes = function_exists('joma_journal_notes') ? joma_journal_notes((int) $u['id'], $q, $per, ($page - 1) * $per) : array();
$notesTotal = function_exists('joma_journal_notes_count') ? joma_journal_notes_count((int) $u['id'], $q) : 0;
$pages = max(1, (int) ceil($notesTotal / $per));
$todayMood = get_mood($u['id'], $today);

joma_header('دفترچهٔ جوما', array());
?>
<?php joma_v2_pagehead('دفترچهٔ جوما', 'دفترچهٔ جوما', 'هرچه ثبت کنی، این‌جا معنی می‌گیرد — با شواهد و بدون ادعای قطعی.', ''); ?>

<?php if ($msg) echo '<p class="toast ok">' . e($msg) . '</p>'; ?>
<?php if ($err) echo '<p class="toast bad">' . e($err) . '</p>'; ?>

<div class="rfilters">
  <a class="fsel<?php echo $filter === 'all' ? ' on' : ''; ?>" href="<?php echo e(joma_url('index.php?p=journal')); ?>">همه</a>
  <a class="fsel<?php echo $filter === 'personal' ? ' on' : ''; ?>" href="<?php echo e(joma_url('index.php?p=journal&show=personal')); ?>">از ثبت‌های تو</a>
  <a class="fsel<?php echo $filter === 'notes' ? ' on' : ''; ?>" href="<?php echo e(joma_url('index.php?p=journal&show=notes')); ?>">نوشته‌های خودت</a>
</div>

<?php if ($filter !== 'notes') { ?>
  <h2>از ثبت‌های تو</h2>
  <?php if (empty($ins['insights'])) { ?>
    <div class="nodata">
      <span class="ico">🌱</span>
      <b>جوما هنوز چیز تازه‌ای برای گفتن ندارد</b>
      <p class="lede">برای این بازه هنوز دادهٔ کافی نیست. با چند ثبت دیگر، این‌جا چیزهای واقعی خواهی دید — بینش بدون داده ساخته نمی‌شود.</p>
      <div class="btn-row" style="justify-content:center;margin-top:10px">
        <a class="btn sm" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت کارهای امروز</a>
        <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=mood')); ?>">ثبت حال امروز</a>
      </div>
    </div>
  <?php } else { ?>
    <div class="grid grid-2">
      <?php foreach ($ins['insights'] as $in) {
          $evi = $in['evidence'];
      ?>
        <article class="card insight-card">
          <div class="row" style="justify-content:space-between">
            <span class="row" style="gap:8px">
              <span class="icon-btn" style="width:34px;height:34px;border-radius:11px;background:var(--brand-softer);border:0"><?php echo joma_v2_icon('owl-hi'); ?></span>
              <span class="chip g">از ثبت‌های تو</span>
            </span>
            <span class="tiny"><?php echo e($in['title']); ?></span>
          </div>
          <p class="in-text" style="margin-top:8px">
            <?php echo str_replace('**همراهی، دلیل نیست.**', '<b>همراهی، دلیل نیست.</b>', e($in['text'])); ?>
          </p>
          <div class="in-meta">
            <span><?php echo e($evi['sampleLabel']); ?></span> ·
            <span>بازهٔ <?php echo e(jalali_format($evi['from'])); ?> تا <?php echo e(jalali_format($evi['to'])); ?></span>
          </div>
          <details class="gl" style="margin-top:8px">
            <summary>این برداشت از کجا آمده؟</summary>
            <div class="glossary" style="margin-top:8px">
              <table>
                <tr><th>دربارهٔ کدام شاخص‌ها</th><td><?php echo e($evi['indicators']); ?></td></tr>
                <tr><th>بازهٔ استفاده‌شده</th><td><?php echo e(jalali_format($evi['from'])); ?> تا <?php echo e(jalali_format($evi['to'])); ?></td></tr>
                <tr><th>چند مشاهده</th><td><?php echo e($evi['sampleLabel']); ?></td></tr>
                <tr><th>دادهٔ ناقص</th><td><?php echo e($evi['droppedNote']); ?></td></tr>
                <tr><th>جهت رابطه</th><td><?php echo e($evi['direction']); ?></td></tr>
                <tr><th>همان روز یا روز بعد</th><td><?php echo e($evi['lag']); ?></td></tr>
                <tr><th>قاعده و نسخه</th><td dir="ltr"><?php echo e($evi['ruleId']); ?> · v<?php echo e($evi['ruleVersion']); ?></td></tr>
                <tr><th>زمان محاسبه</th><td dir="ltr"><?php echo e($evi['computedAt']); ?></td></tr>
                <tr><th>اعتبار</th><td><?php echo !empty($evi['isValid']) ? 'هنوز معتبر است' : 'با دادهٔ تازه جایگزین شد'; ?></td></tr>
              </table>
              <p class="tiny" style="margin-top:8px">این بینش فقط برای خودت است و با «هم‌مسیر» به اشتراک گذاشته نمی‌شود.</p>
            </div>
          </details>
        </article>
      <?php } ?>
    </div>
  <?php } ?>
<?php } ?>

<?php if ($filter !== 'personal') { ?>
  <h2 style="margin-top:18px">نوشته‌های خودت</h2>

  <?php if ($todayMood) { ?>
    <section class="card">
      <div class="row" style="justify-content:space-between">
        <div class="k">یادداشت امروز — <?php echo e(jalali_format($today)); ?></div>
        <span class="chip g">فقط خودت</span>
      </div>
      <form method="post" style="margin-top:8px">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="save_note">
        <input type="hidden" name="date" value="<?php echo e($today); ?>">
        <textarea name="note" placeholder="اگر چیزی روی دلت است، همین‌جا بنویس…" style="max-width:720px"><?php echo e($todayMood['note']); ?></textarea>
        <div class="btn-row" style="margin-top:8px">
          <button class="btn sm" type="submit">ذخیرهٔ یادداشت</button>
          <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=mood')); ?>">اصلاح پنج شاخص حال</a>
        </div>
      </form>
    </section>
  <?php } else { ?>
    <section class="card">
      <div class="k">امروز هنوز حالی ثبت نشده</div>
      <p class="lede" style="margin-top:6px">یادداشت به همان ثبت روز می‌چسبد؛ اول حال امروزت را ثبت کن، بعد این‌جا بنویس.</p>
      <div class="btn-row" style="margin-top:8px"><a class="btn sm" href="<?php echo e(joma_url('index.php?p=mood')); ?>">ثبت حال امروز</a></div>
    </section>
  <?php } ?>

  <form class="card" method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="p" value="journal">
    <input type="hidden" name="show" value="notes">
    <div style="flex:1;min-width:200px"><label>جست‌وجو در یادداشت‌ها</label><input name="q" value="<?php echo e($q); ?>" placeholder="یک واژه…"></div>
    <button class="btn sec">جست‌وجو</button>
    <?php if ($q !== '') { ?><a class="btn ghost" href="<?php echo e(joma_url('index.php?p=journal&show=notes')); ?>">پاک‌کردن</a><?php } ?>
  </form>

  <p class="tiny"><?php echo fa_num($notesTotal); ?> یادداشت ذخیره شده است.</p>

  <?php if (!$notes) { ?>
    <div class="nodata">
      <span class="ico">📖</span>
      <b>هنوز یادداشتی ننوشته‌ای</b>
      <p class="lede">قدم ششم ثبت حال، یک جملهٔ اختیاری است: «امروز را در یک جمله بنویس». همان جمله‌ها این‌جا جمع می‌شوند.</p>
    </div>
  <?php } else { ?>
    <div class="grid grid-2">
      <?php foreach ($notes as $n) { ?>
        <article class="card">
          <div class="row" style="justify-content:space-between">
            <b style="font-size:12.5px"><?php echo e(jalali_format($n['jalali_date'])); ?></b>
            <span class="tiny">انرژی <?php echo fa_num($n['energy']); ?> · حال <?php echo fa_num($n['general_mood']); ?> · استرس <?php echo fa_num($n['stress']); ?></span>
          </div>
          <p class="lede" style="margin-top:6px"><?php echo nl2br(e($n['note'])); ?></p>
          <div class="btn-row" style="margin-top:8px">
            <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=journal&show=notes&edit=' . urlencode($n['jalali_date']))); ?>">ویرایش</a>
            <form method="post" onsubmit="return confirm('یادداشت این روز پاک شود؟ (رکورد حال دست‌نخورده می‌ماند)');">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="delete_note">
              <input type="hidden" name="date" value="<?php echo e($n['jalali_date']); ?>">
              <button class="btn ghost sm" type="submit">پاک‌کردن یادداشت</button>
            </form>
          </div>
          <p class="tiny">این نوشته فقط برای خودت است و در هیچ پاسخ مشاور نمی‌آید.</p>
        </article>
      <?php } ?>
    </div>

    <?php if ($pages > 1) { ?>
      <div class="btn-row" style="margin-top:12px">
        <?php if ($page > 1) { ?>
          <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=journal&show=notes&page=' . ($page - 1) . ($q !== '' ? '&q=' . urlencode($q) : ''))); ?>">قبل</a>
        <?php } ?>
        <span class="chip n">صفحهٔ <?php echo fa_num($page); ?> از <?php echo fa_num($pages); ?></span>
        <?php if ($page < $pages) { ?>
          <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=journal&show=notes&page=' . ($page + 1) . ($q !== '' ? '&q=' . urlencode($q) : ''))); ?>">بعد</a>
        <?php } ?>
      </div>
    <?php } ?>
  <?php } ?>

  <?php
  $editDate = isset($_GET['edit']) ? $_GET['edit'] : '';
  if ($editDate !== '' && jalali_is_valid($editDate)) {
      $em = get_mood($u['id'], $editDate);
      if ($em) { ?>
        <section class="card" style="border:1.5px solid var(--brand2)">
          <div class="k">ویرایش یادداشت <?php echo e(jalali_format($editDate)); ?></div>
          <form method="post" style="margin-top:8px">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save_note">
            <input type="hidden" name="date" value="<?php echo e($editDate); ?>">
            <textarea name="note" style="max-width:720px"><?php echo e($em['note']); ?></textarea>
            <div class="btn-row" style="margin-top:8px">
              <button class="btn sm" type="submit">ذخیره</button>
              <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=journal&show=notes')); ?>">انصراف</a>
            </div>
          </form>
        </section>
      <?php }
  } ?>
<?php } ?>

<section class="card" style="margin-top:16px">
  <p class="lede">بینش‌ها از ثبت‌های خودت ساخته می‌شوند و هیچ‌کدام ادعای علت ندارند. «همراهی، دلیل نیست.»</p>
  <p class="tiny">دفترچه و بینش‌ها با «هم‌مسیر» به اشتراک گذاشته نمی‌شوند و در هیچ پاسخ مشاور نمی‌آیند.</p>
</section>
<?php joma_footer(); ?>
