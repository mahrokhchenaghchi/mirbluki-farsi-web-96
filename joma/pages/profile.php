<?php
require_login();
$u = current_user();
joma_header('پروفایل', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'پروفایل')));
$rows = array(
    'نام'=>$u['first_name'],'نام خانوادگی'=>$u['last_name'],'نام کاربری'=>$u['username'],
    'ایمیل'=>$u['email'],'موبایل'=>$u['phone'],'شغل'=>$u['job'],
    'نقش'=>$u['role_key'],'سطح'=>$u['access_level'],'عضویت'=>$u['created_at'],
);
echo '<h1>پروفایل من</h1><div class="card">';
foreach ($rows as $k=>$v) echo '<p><span class="chip">'.e($k).'</span> '.e($v).'</p>';
echo '</div>';
joma_footer();
