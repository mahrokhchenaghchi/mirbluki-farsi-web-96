<?php
require_login();
$u = current_user();
$prefs = get_prefs($u['id']);
$ok = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $phone = trim($_POST['phone']);
    if ($phone && !is_valid_iran_mobile($phone)) {
        $err = 'شماره موبایل باید مانند 09123456789 باشد.';
    } elseif (!in_array($_POST['job'], jobs_list(), true)) {
        $err = 'شغل را از فهرست انتخاب کنید.';
    } else {
        $nu = update_user($u['id'], array(
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'phone' => $phone,
            'job' => trim($_POST['job']),
        ));
        save_prefs($u['id'], !empty($_POST['compact']), !empty($_POST['notify']));
        $_SESSION['user'] = session_user_array($nu);
        $u = current_user();
        $prefs = get_prefs($u['id']);
        $ok = 'ذخیره شد.';
    }
}
joma_header('تنظیمات', array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'تنظیمات')));
?>
<?php joma_v2_pagehead('تنظیمات', 'تنظیمات', 'حساب، نمایش و اعلان‌ها.'); ?>
<form class="card" method="post">
  <?php echo csrf_field(); ?>
  <h2>تنظیمات حساب</h2>
  <div class="field-row">
    <div><label>نام</label><input name="first_name" value="<?php echo e($u['first_name']); ?>"></div>
    <div><label>نام خانوادگی</label><input name="last_name" value="<?php echo e($u['last_name']); ?>"></div>
  </div>
  <label>موبایل</label><input name="phone" dir="ltr" value="<?php echo e($u['phone']); ?>">
  <label>شغل</label>
  <select name="job"><?php foreach (jobs_list() as $j) echo '<option '.($u['job'] === $j ? 'selected' : '').'>'.e($j).'</option>'; ?></select>
  <h2 style="margin-top:14px">نمایش</h2>
  <label class="row" style="gap:8px;font-weight:700;color:var(--ink-2);margin-top:6px"><input type="checkbox" name="compact" style="width:auto" <?php echo !empty($prefs['compact_cards']) ? 'checked' : ''; ?>> کارت‌های فشرده</label>
  <h2 style="margin-top:14px">اعلان‌ها</h2>
  <label class="row" style="gap:8px;font-weight:700;color:var(--ink-2);margin-top:6px"><input type="checkbox" name="notify" style="width:auto" <?php echo !empty($prefs['notifications_enabled']) ? 'checked' : ''; ?>> اعلان‌ها</label>
  <p class="tiny">فعلاً فقط ترجیح ذخیره می‌شود؛ پیامک یا ایمیل خودکاری فرستاده نمی‌شود (زیرساختش آماده نیست).</p>
  <p style="margin-top:12px"><button class="btn">ذخیره</button></p>
  <?php if ($ok) echo '<p class="ok">'.e($ok).'</p>'; ?>
  <?php if ($err) echo '<p class="bad">'.e($err).'</p>'; ?>
</form>
<section class="card">
  <div class="k">داده و حریم خصوصی</div>
  <p class="lede" style="margin-top:6px">خروجی گرفتن از داده‌ات (گزارش · CSV · JSON) و درخواست پاک‌کردن حساب، در صفحهٔ «حقوق داده» است.</p>
  <div class="btn-row" style="margin-top:8px"><a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=rights')); ?>">رفتن به حقوق داده</a></div>
</section>
<?php joma_footer(); ?>
