<?php
/**
 * JOMA — پنل مدیر: صدور کد بازیابی رمز (B1 — مرحلهٔ ۲)
 * فقط برای مدیر (مجوز ADMIN_ACCESS). کد فقط یک‌بار و فقط در همین صفحه نشان داده می‌شود.
 */
require_perm('ADMIN_ACCESS');
if (!function_exists('joma_recovery_issue')) {
    $__rf = dirname(__FILE__) . '/../functions/recovery.php';
    if (is_file($__rf)) require_once $__rf;
}
$u = current_user();

$err = '';
$issued = null;      // کد خام — فقط در همین پاسخ
$revoked = false;
$q = trim(isset($_GET['u']) ? $_GET['u'] : '');
$target = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $q = strtolower(trim(isset($_POST['identifier']) ? $_POST['identifier'] : ''));
    $target = ($q !== '' && function_exists('user_by_username')) ? user_by_username($q) : null;
    if (!$target) {
        $err = 'کاربری با این نام کاربری یا ایمیل پیدا نشد.';
    } elseif ($action === 'issue') {
        $res = joma_recovery_issue((int) $target['id'], $u);
        if (!empty($res['ok'])) {
            $issued = array(
                'code' => $res['code'],
                'expires_at' => $res['expires_at'],
                'username' => $target['username'],
                'name' => trim($target['first_name'] . ' ' . $target['last_name']),
                'user_id' => (int) $target['id'],
            );
        } else {
            $err = isset($res['error']) ? $res['error'] : 'صدور کد ممکن نشد.';
        }
    } elseif ($action === 'revoke') {
        joma_recovery_revoke((int) $target['id']);
        $revoked = true;
    }
} elseif ($q !== '') {
    $target = function_exists('user_by_username') ? user_by_username(strtolower($q)) : null;
    if (!$target) $err = 'کاربری با این نام کاربری یا ایمیل پیدا نشد.';
}

$status = ($target && function_exists('joma_recovery_status')) ? joma_recovery_status((int) $target['id']) : null;
$log = function_exists('joma_recovery_log') ? joma_recovery_log(20) : array();
$log_types = array(
    'REQUESTED' => 'درخواست کاربر',
    'ISSUED' => 'صدور کد',
    'FAILED' => 'تلاش ناموفق',
    'DONE' => 'رمز عوض شد',
    'REVOKED' => 'کد باطل شد',
);

joma_header('مدیریت بازیابی رمز', array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'مدیریت بازیابی رمز')));
?>
<div class="page-head">
  <div>
    <h1>مدیریت بازیابی رمز</h1>
    <p class="lede">کد یک‌بارمصرف صادر کن، همان‌جا به کاربر بده. کد در سرور فقط به‌صورت هش ذخیره می‌شود و هیچ‌وقت دوباره نمایش داده نمی‌شود.</p>
  </div>
</div>

<?php if ($err) echo '<p class="toast bad">' . e($err) . '</p>'; ?>
<?php if ($revoked) echo '<p class="toast ok">کد فعال این کاربر باطل شد.</p>'; ?>

<?php if ($issued) { ?>
<section class="card" style="border:2px solid #157347">
  <h2>کد بازیابی — فقط همین یک‌بار</h2>
  <p class="lede">کاربر: <b><?php echo e($issued['name']); ?></b> (@<?php echo e($issued['username']); ?> · شناسه <?php echo fa_num($issued['user_id']); ?>)</p>
  <p style="text-align:center;margin:14px 0">
    <b dir="ltr" style="font-size:34px;letter-spacing:8px;font-family:monospace"><?php echo e($issued['code']); ?></b>
  </p>
  <p class="lede">اعتبار تا: <b dir="ltr"><?php echo e($issued['expires_at']); ?></b> · یک‌بارمصرف · حداکثر ۵ تلاش</p>
  <p class="lede">این کد را همین حالا به کاربر بده. اگر این صفحه را ببندی، کد دیگر نمایش داده نمی‌شود (در سرور فقط هش آن مانده است) و باید کد تازه صادر کنی.</p>
</section>
<?php } ?>

<section class="card">
  <h2>پیدا کردن کاربر</h2>
  <form method="get" action="<?php echo e(joma_url('index.php')); ?>">
    <input type="hidden" name="p" value="admin_recovery">
    <label>نام کاربری یا ایمیل</label>
    <input name="u" dir="ltr" value="<?php echo e($q); ?>" required>
    <p><button class="btn" type="submit">جست‌وجو</button></p>
  </form>
</section>

<?php if ($target) { ?>
<section class="card">
  <h2><?php echo e(trim($target['first_name'] . ' ' . $target['last_name'])); ?></h2>
  <p class="lede">@<?php echo e($target['username']); ?> · <?php echo e($target['email']); ?> · شناسه <?php echo fa_num($target['id']); ?></p>
  <?php if ($status && !empty($status['has_code'])) { ?>
    <div class="btn-row">
      <?php if (!empty($status['used'])) { ?><span class="chip">کد مصرف شده</span><?php } elseif (!empty($status['expired'])) { ?><span class="chip">کد منقضی شده</span><?php } else { ?><span class="chip">کد فعال دارد</span><?php } ?>
      <span class="chip">تلاش باقی‌مانده: <?php echo fa_num($status['attempts_left']); ?></span>
      <span class="chip">اعتبار تا: <b dir="ltr"><?php echo e($status['expires_at']); ?></b></span>
    </div>
    <form method="post" action="<?php echo e(joma_url('index.php?p=admin_recovery')); ?>" style="margin-top:8px">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="revoke">
      <input type="hidden" name="identifier" value="<?php echo e($target['username']); ?>">
      <button class="btn sec" type="submit">باطل‌کردن کد فعال</button>
    </form>
  <?php } else { ?>
    <p class="lede">کد فعالی برای این کاربر نیست.</p>
  <?php } ?>
  <form method="post" action="<?php echo e(joma_url('index.php?p=admin_recovery')); ?>" style="margin-top:8px">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="issue">
    <input type="hidden" name="identifier" value="<?php echo e($target['username']); ?>">
    <p><button class="btn" type="submit">صدور کد ۶ رقمی (۱۵ دقیقه)</button></p>
    <p class="lede">اگر کد قبلی فعال باشد، با این کار باطل و کد تازه جایگزینش می‌شود.</p>
  </form>
</section>
<?php } ?>

<section class="card">
  <h2>آخرین رویدادها</h2>
  <?php if (!$log) { ?>
    <p class="lede">هنوز رویدادی ثبت نشده است.</p>
  <?php } else { ?>
    <table style="width:100%;border-collapse:collapse">
      <thead><tr><th style="text-align:right">رویداد</th><th style="text-align:right">کاربر</th><th style="text-align:right">زمان</th></tr></thead>
      <tbody>
      <?php foreach ($log as $row) {
          $uid = isset($row['user_id']) ? (int) $row['user_id'] : 0;
          $tu = ($uid && function_exists('get_user')) ? get_user($uid) : null;
          $label = isset($log_types[$row['type']]) ? $log_types[$row['type']] : $row['type'];
          echo '<tr>';
          echo '<td>' . e($label) . '</td>';
          echo '<td>' . ($tu ? e('@' . $tu['username']) : ('#' . fa_num($uid))) . '</td>';
          echo '<td><span dir="ltr">' . e(isset($row['at']) ? $row['at'] : '') . '</span></td>';
          echo '</tr>';
      } ?>
      </tbody>
    </table>
    <p class="lede">کدها در این گزارش ذخیره نمی‌شوند؛ فقط رویدادها.</p>
  <?php } ?>
</section>
<?php joma_footer(); ?>
