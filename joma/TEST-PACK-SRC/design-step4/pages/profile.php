<?php
require_login();
$u = current_user();
joma_header('پروفایل', array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'پروفایل')));
$rows = array(
    'نام' => $u['first_name'],
    'نام خانوادگی' => $u['last_name'],
    'نام کاربری' => $u['username'],
    'موبایل' => $u['phone'],
    'ایمیل' => $u['email'],
    'شغل' => $u['job'],
    'سطح دسترسی' => access_label($u['access_level']),
    'نقش' => role_label($u['role_key']),
    'تاریخ عضویت' => substr($u['created_at'], 0, 10),
);
?>
<?php joma_v2_pagehead('پروفایل من', 'پروفایل من', 'این اطلاعات در گزارش‌ها و هم‌مسیر دیده می‌شود؛ رمز و موبایل در تنظیمات قابل ویرایش است.'); ?>
<div class="grid grid-2">
  <div class="card profile-list">
    <?php foreach ($rows as $k => $v) echo '<div class="row" style="justify-content:space-between;border-bottom:1px solid var(--card-brd);padding:8px 0"><span class="k">'.e($k).'</span><b style="font-size:12.5px">'.e($v ? $v : '—').'</b></div>'; ?>
  </div>
  <div class="card">
    <div class="k">چه چیزی خصوصی می‌ماند</div>
    <p class="lede" style="margin-top:6px">یادداشت‌های حال تو هیچ‌وقت برای «هم‌مسیر» فرستاده نمی‌شود و وضعیت «جوجهٔ من» هم اتاق شخصی خودت است.</p>
    <div class="btn-row" style="margin-top:10px">
      <a class="btn sec" href="<?php echo e(joma_url('index.php?p=settings')); ?>">ویرایش در تنظیمات</a>
      <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=support')); ?>">پشتیبانی</a>
    </div>
  </div>
</div>
<?php joma_footer(); ?>
