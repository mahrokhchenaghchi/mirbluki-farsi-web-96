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

function learn_card($href, $ico, $title, $desc) {
    echo '<a class="card" href="' . e($href) . '" style="display:block;text-decoration:none;color:inherit">';
    echo '<div style="font-size:32px;margin-bottom:8px">' . $ico . '</div>';
    echo '<h2 style="margin:0 0 8px">' . e($title) . '</h2>';
    echo '<p class="lede" style="margin:0">' . e($desc) . '</p></a>';
}
?>
<?php if ($guide) { ?>
  <article class="card" style="max-width:760px">
    <p class="meta" dir="ltr"><?php echo e($guide['code']); ?> · <?php echo e($guide['category']); ?> · <?php echo e($guide['frequency']); ?></p>
    <h1><?php echo e($guide['sticker'] . ' ' . $guide['name']); ?></h1>
    <p class="lede">در جوما چه ثبت کنید: <?php echo e($guide['register']); ?></p>
    <p class="meta">نوع ثبت: <?php echo e($guide['how']); ?></p>
    <?php if (!empty($guide['warn'])) echo unspecified_notice(e($guide['warn'])); ?>
    <h2>قدم‌به‌قدم</h2>
    <ol>
      <?php foreach ($guide['steps'] as $s) echo '<li>' . e($s) . '</li>'; ?>
    </ol>
    <div class="btn-row">
      <a class="btn" href="<?php echo e(joma_url('index.php?p=today')); ?>">برو به امروز</a>
      <a class="btn sec" href="<?php echo e(joma_url('index.php?p=learn&g=' . $guide['group'])); ?>">بازگشت به فهرست</a>
    </div>
  </article>
<?php } elseif ($g === 'joma' && !empty($data['joma'])) { $j = $data['joma']; ?>
  <article class="card" style="max-width:760px">
    <h1><?php echo e($j['title']); ?></h1>
    <p class="lede"><?php echo e($j['lede']); ?></p>
    <?php foreach ($j['sections'] as $sec) {
        echo '<h2>' . e($sec['h']) . '</h2><p>' . e($sec['p']) . '</p>';
    } ?>
    <p><a class="btn sec" href="<?php echo e(joma_url('index.php?p=learn')); ?>">بازگشت</a></p>
  </article>
<?php } elseif ($g === 'couple' || $g === 'schema') {
    $meta = isset($data['groups'][$g]) ? $data['groups'][$g] : array('title' => 'آموزش', 'intro' => '');
    echo '<div class="page-head"><div><h1>' . e($meta['title']) . '</h1><p class="lede">' . e($meta['intro']) . '</p></div></div>';
    if (!empty($meta['safety'])) echo unspecified_notice(e($meta['safety']));
    echo '<div class="grid grid-2">';
    foreach (learn_group_codes($g) as $item) {
        echo '<a class="card" style="display:block;text-decoration:none;color:inherit" href="' . e(joma_url('index.php?p=learn&code=' . $item['code'])) . '">';
        echo '<div style="display:flex;gap:12px;align-items:center"><div class="sticker">' . e($item['sticker']) . '</div>';
        echo '<div><strong>' . e($item['name']) . '</strong><p class="meta" dir="ltr">' . e($item['code']) . ' · ' . e($item['frequency']) . '</p></div></div></a>';
    }
    echo '</div>';
    echo '<p><a class="btn sec" href="' . e(joma_url('index.php?p=learn')) . '">بازگشت</a></p>';
} else { ?>
  <div class="page-head">
    <div>
      <h1>آموزش جوما</h1>
      <p class="lede">اول نحوه کار با خود جوما را بخوانید، بعد راهنمای هر فعالیت را ببینید. آموزش متن است؛ ثبت عملکرد جدا و کوتاه می‌ماند.</p>
    </div>
  </div>
  <div class="grid grid-3">
    <?php
    learn_card(joma_url('index.php?p=learn&g=joma'), '🦉', 'نحوه استفاده از جوما', 'ثبت‌نام، حال، دوره، برنامه، امروز، گزارش.');
    learn_card(joma_url('index.php?p=learn&g=couple'), '💗', 'زوج درمانی', '۳۵ فعالیت: گفت‌وگو، مرز، ترمیم، قرار هفتگی.');
    learn_card(joma_url('index.php?p=learn&g=schema'), '📒', 'طرحواره درمانی', '۹ تکنیک هسته + ۱۸ طرحوارهٔ انتخابی.');
    ?>
  </div>
<?php } ?>
<?php joma_footer(); ?>
