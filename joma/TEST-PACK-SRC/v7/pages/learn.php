<?php
require_login();
$learnLib = dirname(__FILE__) . '/../content/learn_lib.php';
if (is_file($learnLib)) require $learnLib;
$data = function_exists('learn_data') ? learn_data() : array();
$code = isset($_GET['code']) ? strtoupper(trim($_GET['code'])) : '';
$g = isset($_GET['g']) ? $_GET['g'] : '';
if ($g !== 'joma' && $g !== 'couple' && $g !== 'schema') $g = '';

$guide = ($code && function_exists('learn_guide')) ? learn_guide($code) : null;

$crumbs = array(
    array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')),
    array('label' => 'آموزش', 'href' => joma_url('index.php?p=learn')),
);
if ($guide) $crumbs[] = array('label' => $guide['name']);
elseif ($g === 'joma') $crumbs[] = array('label' => 'استفاده از جوما');
elseif ($g === 'couple') $crumbs[] = array('label' => 'زوج درمانی');
elseif ($g === 'schema') $crumbs[] = array('label' => 'طرحواره درمانی');

joma_header('آموزش', $crumbs);
?>

<?php
function learn_hub_card($href, $icon_id, $title, $desc, $count = '') {
    echo '<a class="card learn-tile" href="' . e($href) . '">';
    echo '<span class="icon-btn" style="width:46px;height:46px;border-radius:15px;background:var(--brand-softer);border:0">'
       . joma_v2_icon($icon_id, 'ic lg') . '</span>';
    echo '<h2 style="margin:10px 0 8px;font-size:18px">' . e($title) . '</h2>';
    echo '<p class="lede" style="margin:0">' . e($desc) . '</p>';
    if ($count !== '') echo '<span class="chip g" style="margin-top:10px">' . e($count) . '</span>';
    echo '</a>';
}
?>
<?php if ($guide) { ?>
  <article class="learn-wrap">
    <section class="card learn-hero">
      <p class="learn-kicker"><?php echo e($guide['category']); ?>  ·  <?php echo e($guide['frequency']); ?></p>
      <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
        <div class="sticker" style="width:72px;height:72px;font-size:36px;border-radius:24px"><?php echo e($guide['sticker']); ?></div>
        <div>
          <h1 style="margin:0"><?php echo e($guide['name']); ?></h1>
          <p class="meta" dir="ltr"><?php echo e($guide['code']); ?></p>
        </div>
      </div>
      <?php if (!empty($guide['voice'])) echo '<p class="learn-quote">' . e($guide['voice']) . '</p>'; ?>
    </section>
    <?php if (!empty($guide['warn'])) echo unspecified_notice(e($guide['warn'])); ?>
    <?php if (!empty($guide['why'])) { ?>
      <div class="card learn-block"><h2>چرا این تمرین؟</h2><p style="margin:0"><?php echo e($guide['why']); ?></p></div>
    <?php } ?>
    <?php if (!empty($guide['before'])) { ?>
      <div class="card learn-block"><h2>قبل از شروع</h2><p style="margin:0"><?php echo e($guide['before']); ?></p></div>
    <?php } ?>
    <div class="card learn-block">
      <h2>قدم‌به‌قدم، همان‌طور که در جلسه می‌گویم</h2>
      <?php $n = 1; foreach ($guide['steps'] as $s) {
          echo '<div class="learn-step"><div class="learn-n">' . fa_num($n++) . '</div><div>' . e($s) . '</div></div>';
      } ?>
    </div>
    <?php if (!empty($guide['after'])) { ?>
      <div class="card learn-block"><h2>وقتی تمام شد</h2><p style="margin:0"><?php echo e($guide['after']); ?></p></div>
    <?php } ?>
    <?php if (!empty($guide['mistakes'])) { ?>
      <div class="card learn-block"><h2>این‌ها تمرین نیست</h2>
        <ul class="learn-mist"><?php foreach ($guide['mistakes'] as $m) echo '<li>' . e($m) . '</li>'; ?></ul>
      </div>
    <?php } ?>
    <div class="learn-reg">
      <strong>در جوما چه ثبت کنید</strong>
      <?php echo e($guide['register']); ?>
      <p class="meta" style="margin-top:8px">نوع ثبت: <?php echo e($guide['how']); ?></p>
    </div>
    <div class="btn-row">
      <a class="btn" href="<?php echo e(joma_url('index.php?p=today')); ?>">ثبت در امروز</a>
      <a class="btn sec" href="<?php echo e(joma_url('index.php?p=plan')); ?>">افزودن به برنامه</a>
      <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=learn&g=' . $guide['group'])); ?>">بقیهٔ تمرین‌ها</a>
    </div>
    <p class="learn-sign">جواد میربلوکی  ·  جوما؛ جغد دانا</p>
  </article>
<?php } elseif ($g === 'joma' && !empty($data['joma'])) { $j = $data['joma']; ?>
  <article class="learn-wrap">
    <section class="card learn-hero">
      <p class="learn-kicker">شروع با جوما</p>
      <h1><?php echo e($j['title']); ?></h1>
      <p class="lede" style="margin-top:10px;font-size:17px"><?php echo e($j['lede']); ?></p>
      <?php if (!empty($j['quote'])) echo '<p class="learn-quote">' . e($j['quote']) . '</p>'; ?>
    </section>
    <?php foreach ($j['sections'] as $sec) {
        echo '<div class="card learn-block"><h2>' . e($sec['h']) . '</h2><p style="margin:0">' . e($sec['p']) . '</p></div>';
    } ?>
    <p class="learn-sign">جواد میربلوکی  ·  جوما؛ جغد دانا</p>
    <p><a class="btn sec" href="<?php echo e(joma_url('index.php?p=learn')); ?>">بازگشت به آموزش</a></p>
  </article>
<?php } elseif ($g === 'couple' || $g === 'schema') {
    $meta = isset($data['groups'][$g]) ? $data['groups'][$g] : array('title' => 'آموزش', 'intro' => '');
    echo '<section class="card learn-hero"><p class="learn-kicker">تمرین‌ها</p>';
    echo '<h1>' . e($meta['title']) . '</h1>';
    echo '<p class="lede" style="margin-top:10px;font-size:17px">' . e($meta['intro']) . '</p>';
    if (!empty($meta['quote'])) echo '<p class="learn-quote">' . e($meta['quote']) . '</p>';
    echo '</section>';
    if (!empty($meta['safety'])) echo unspecified_notice(e($meta['safety']));
    echo '<div class="grid grid-2">';
    foreach (learn_group_codes($g) as $item) {
        echo '<a class="card learn-ex" href="' . e(joma_url('index.php?p=learn&code=' . $item['code'])) . '">';
        echo '<div class="learn-ex-h"><div class="sticker">' . e($item['sticker']) . '</div>';
        echo '<div><strong>' . e($item['name']) . '</strong><p class="meta">' . e($item['frequency']);
        if (!empty($item['voice'])) echo ' · لمس کنید و کامل بخوانید';
        echo '</p></div></div>';
        if ($g === 'schema' && !empty($item['how_short'])) {
            echo '<div class="learn-ex-b"><p class="lede" style="margin:12px 0 0;font-size:14px;line-height:2">' . e($item['how_short']) . '</p></div>';
        }
        echo '</a>';
    }
    echo '</div>';
    echo '<p style="margin-top:18px"><a class="btn sec" href="' . e(joma_url('index.php?p=learn')) . '">بازگشت</a></p>';
} else { ?>
  <section class="card learn-hero">
    <span class="kicker">آموزش</span>
    <h1>آموزش جوما</h1>
    <p class="lede" style="margin-top:10px;font-size:17px">اینجا من کنار شما می‌نشینم و قدم‌به‌قدم می‌گویم هر تمرین یعنی چه، چطور انجامش بدهید، و در جوما چه چیزی را ثبت کنید. کارت را باز کنید؛ عجله نکنید.</p>
    <p class="learn-quote">قرار نیست کامل باشید. قرار است ببینید، انتخاب کنید و ادامه بدهید.</p>
  </section>
  <div class="grid grid-3">
    <?php
    learn_hub_card(joma_url('index.php?p=learn&g=joma'), 'owl-think', 'نحوهٔ استفاده از جوما', 'از ورود تا گزارش؛ فلسفه و قواعد ثبت.');
    learn_hub_card(joma_url('index.php?p=learn&g=couple'), 'owl-hi', 'زوج‌درمانی', 'تمرین‌های رابطه؛ هر کدام یک جلسهٔ کوتاه نوشتاری.');
    learn_hub_card(joma_url('index.php?p=learn&g=schema'), 'owl-cheer', 'طرح‌واره‌درمانی', 'تکنیک‌های هسته و طرح‌واره‌های انتخابی.');
    ?>
  </div>
<?php } ?>
<?php joma_footer(); ?>
