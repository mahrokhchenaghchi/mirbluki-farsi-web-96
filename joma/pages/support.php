<?php
require_login();
joma_header('پشتیبانی', array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'پشتیبانی')));
?>
<h1>پشتیبانی</h1>
<p class="lede">ساختار ارتباط آماده است تا بعداً روش‌های واقعی وارد شود.</p>
<?php echo unspecified_notice('شناسه بله، شماره پیامک و ایمیل پشتیبانی هنوز ارائه نشده و حدس زده نشده است.'); ?>
<?php joma_footer(); ?>
