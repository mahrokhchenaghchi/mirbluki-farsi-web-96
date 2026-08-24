<?php
require_login();
$u = current_user();
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['year'])) {
    csrf_check();
    $key = sprintf('%04d-%02d', (int)$_POST['year'], (int)$_POST['month']);
    ensure_period($u['id'], $key);
    joma_redirect('index.php?p=plan&period='.$key);
}
$list = list_periods($u['id']);
$t = explode('-', jalali_today());
joma_header('دوره‌های من', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'دوره‌ها')));
?>
<h1>دوره‌های من</h1>
<form class="card" method="post"><?php echo csrf_field(); ?>
<label>سال</label><input name="year" value="<?php echo e($t[0]); ?>">
<label>ماه</label><select name="month"><?php for($i=1;$i<=12;$i++) echo '<option value="'.$i.'" '.((int)$t[1]===$i?'selected':'').'>'.jalali_month_name($i).'</option>'; ?></select>
<button class="btn">ایجاد / انتخاب دوره</button>
</form>
<?php foreach ($list as $p) {
    echo '<div class="card"><h3>'.e(jalali_period_label($p['period_key'])).'</h3><p>'.e(status_label($p['plan_status'])).'</p>';
    echo '<a class="btn sec" href="'.e(joma_url('index.php?p=plan&period='.$p['period_key'])).'">برنامه</a> ';
    echo '<a class="btn" href="'.e(joma_url('index.php?p=reports&period='.$p['period_key'])).'">گزارش</a></div>';
}
if (!$list) echo '<div class="card">هنوز دوره‌ای ایجاد نکرده‌اید.</div>';
joma_footer();
