<?php
require_login();
require_perm('VIEW_HISTORY');
$u = current_user();
$key = isset($_GET['period']) ? $_GET['period'] : current_period_key();
if (!preg_match('/^\d{4}-\d{2}$/', $key)) $key = jalali_period_key(jalali_today());
$wp = ensure_period($u['id'], $key);
$plan = $wp['plan'];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['archive'])) {
        $msg = transition_plan($u['id'], $plan, 'ARCHIVED');
        $wp = ensure_period($u['id'], $key);
        $plan = $wp['plan'];
    } elseif (isset($_POST['work'])) {
        set_current_period_key($key);
        joma_redirect('index.php?p=dashboard');
    }
}
$acts = list_plan_activities($plan['id'], $u['id']);
$rep = build_report($u['id'], $plan);
joma_header('جزئیات دوره', array(
    array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')),
    array('label' => 'دوره‌ها', 'href' => joma_url('index.php?p=periods')),
    array('label' => jalali_period_label($key)),
));
?>
<div class="page-head">
<?php joma_v2_pagehead('دوره‌های من', jalali_period_label($key), 'این صفحه همه‌چیز همین دوره را یک‌جا نشان می‌دهد: برنامه، عددها و وضعیت.', status_badge($plan['status'])); ?>
<?php if ($msg) echo '<p class="toast bad">' . e($msg) . '</p>'; ?>

<div class="card" style="background:linear-gradient(150deg,#FFFFFF,var(--brand-softer))">
  <div class="k">مسیر این دوره</div>
  <div class="row" style="gap:6px;margin-top:6px">
    <?php
    $steps = array('DRAFT' => 'پیش‌نویس', 'PLANNING' => 'آمادهٔ اجرا', 'RUNNING' => 'در حال اجرا', 'ARCHIVED' => 'بایگانی');
    $order = array_keys($steps); $cur = array_search($plan['status'], $order, true); if ($cur === false) $cur = 0;
    foreach ($order as $i => $st) {
        echo '<span class="chip ' . ($i < $cur ? 'g' : ($i === $cur ? 's' : 'n')) . '">' . e($steps[$st]) . '</span>';
        if ($i < 3) echo '<span class="tiny">←</span>';
    }
    ?>
  </div>
  <div class="btn-row" style="margin-top:10px">
    <form method="post" style="margin:0"><?php echo csrf_field(); ?><button class="btn sec sm" name="work" value="1">کار روی این دوره</button></form>
    <?php if ($plan['status'] === 'RUNNING') { ?>
      <form method="post" style="margin:0" onsubmit="return confirm('این دوره بایگانی شود؟ بعد از بایگانی فقط خواندنی می‌شود و عددهایش برای همیشه می‌مانند.');">
        <?php echo csrf_field(); ?><button class="btn sm" name="archive" value="1">بایگانی دوره</button>
      </form>
    <?php } ?>
    <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=reports&period=' . $key)); ?>">گزارش کامل</a>
    <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=periods')); ?>">همهٔ دوره‌ها</a>
  </div>
  <p class="tiny" style="margin-top:6px">پیش‌نویس → آمادهٔ اجرا → در حال اجرا → بایگانی. در «در حال اجرا» برنامه قفل است تا تاریخچه سالم بماند.</p>
</div>

<div class="rkpis">
  <div class="rkpi"><div class="k">رخدادهای ثبت‌شده</div><div class="v"><?php echo fa_num((int) $rep['source_event_count']); ?></div><div class="t">هر ثبت، یک رخداد</div></div>
  <div class="rkpi"><div class="k">فعالیت‌های برنامه</div><div class="v"><?php echo fa_num(count($rep['activities'])); ?></div><div class="t"><?php echo e(jalali_period_label($key)); ?></div></div>
  <div class="rkpi"><div class="k">روزهای ثبت حال</div><div class="v"><?php echo fa_num(count($rep['moods'])); ?></div><div class="t">خودگزارشی</div></div>
  <div class="rkpi"><div class="k">جمع وزن‌ها</div><div class="v"><?php echo fa_num((int) $rep['weight_sum']); ?></div><div class="t">سهم در موفقیت کلی</div></div>
</div>

<section class="card">
  <div class="k">برنامهٔ این دوره</div>
  <?php if (!$acts) { ?>
    <p class="lede" style="margin-top:6px">این دوره هنوز فعالیتی ندارد. از کتابخانه چند فعالیت اضافه کن.</p>
    <div class="btn-row" style="margin-top:8px"><a class="btn sm" href="<?php echo e(joma_url('index.php?p=library')); ?>">رفتن به کتابخانه</a></div>
  <?php } else { ?>
    <div class="grid grid-2" style="margin-top:8px">
      <?php foreach ($acts as $a) { ?>
        <div class="card" style="border:1px solid var(--card-brd);box-shadow:none">
          <div class="row" style="justify-content:space-between">
            <span class="row" style="gap:8px">
              <span class="sticker" style="background:<?php echo e($a['color']); ?>33"><?php echo e($a['sticker']); ?></span>
              <b style="font-size:12.5px"><?php echo e($a['name']); ?></b>
            </span>
            <span class="chip n"><?php echo e(frequencies_list()[$a['frequency']]); ?></span>
          </div>
          <p class="tiny" style="margin-top:6px">هدف: <?php echo e(format_value($a['data_type'], $a['target_value'], $a['unit'])); ?> · اهمیت <?php echo e(weight_label($a['weight'])); ?></p>
        </div>
      <?php } ?>
    </div>
  <?php } ?>
</section>

<?php
// اگر دوره در حال اجرا/بایگانی است، امکان «دورهٔ نو با همین فعالیت‌ها» را همین‌جا هم نشان بده (B8)
$__cs = function_exists('joma_copy_plan_status') ? joma_copy_plan_status($u['id'], $plan) : null;
if ($__cs && !empty($__cs['can_copy'])) { ?>
<section class="card" style="background:linear-gradient(150deg,#FFFFFF,var(--brand-softer))">
  <div class="row" style="justify-content:space-between">
    <div class="k">دورهٔ نو با همین فعالیت‌ها</div><span class="chip g"><?php echo e($__cs['target_label']); ?></span>
  </div>
  <p class="lede" style="margin-top:6px"><?php echo fa_num($__cs['source_count']); ?> فعالیت و هدف‌هایشان از <?php echo e(jalali_period_label($key)); ?> به <?php echo e($__cs['target_label']); ?> منتقل می‌شود. ثبت‌ها و گزارش‌های این دوره دست‌نخورده می‌ماند.</p>
  <form method="post" action="<?php echo e(joma_url('index.php?p=plan')); ?>" style="margin-top:8px">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="copy_next">
    <button class="btn sm" type="submit">بساز</button>
  </form>
</section>
<?php } ?>

<section class="card">
  <div class="k">خروجی این دوره</div>
  <p class="lede" style="margin-top:6px">گزارش خوانا، صفحه‌گسترده و فایل پشتیبان از صفحهٔ «حقوق داده» گرفته می‌شود — شامل همهٔ دوره‌ها.</p>
  <div class="btn-row" style="margin-top:8px">
    <a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=rights')); ?>">حقوق داده و خروجی</a>
  </div>
</section>
<?php joma_footer(); ?>
