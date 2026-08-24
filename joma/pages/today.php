<?php
require_login(); require_perm('RECORD_PERFORMANCE');
$u = current_user();
$key = isset($_GET['period']) ? $_GET['period'] : jalali_period_key(jalali_today());
$wp = ensure_period($u['id'], $key);
$plan = $wp['plan'];
$date = isset($_POST['date']) ? $_POST['date'] : (jalali_in_period(jalali_today(), $key) ? jalali_today() : $wp['period']['start_date']);
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['pa_id'])) {
    csrf_check();
    $pas = list_plan_activities($plan['id'], $u['id']);
    $pa = null;
    foreach ($pas as $x) if ((int)$x['id']===(int)$_POST['pa_id']) $pa = $x;
    if ($pa) $msg = register_performance($u['id'], $plan, $pa, $_POST['date'], (float)$_POST['value']);
    if ($msg==='') $msg = 'ثبت شد.';
}
$pas = list_plan_activities($plan['id'], $u['id']);
$evs = list_events($plan['id'], $u['id']);
$b = jalali_period_bounds($key);
joma_header('امروز', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'امروز')));
?>
<h1>ثبت عملکرد</h1>
<form method="get" class="card"><input type="hidden" name="p" value="today"><label>تاریخ</label>
<select name="date" onchange="this.form.submit()"><?php
if (isset($_GET['date'])) $date = $_GET['date'];
for ($d=1;$d<=$b['days'];$d++) {
    $ds = $b['year'].'-'.jalali_pad($b['month']).'-'.jalali_pad($d);
    echo '<option value="'.$ds.'" '.($ds===$date?'selected':'').'>'.e(jalali_format($ds)).'</option>';
}
?></select></form>
<?php if ($plan['status']!=='RUNNING') echo '<div class="card">دوره در حال اجرا نیست. <a href="'.e(joma_url('index.php?p=plan')).'">رفتن به برنامه</a></div>';
if ($msg) echo '<p>'.e($msg).'</p>';
foreach ($pas as $a) {
    $related=array(); $sum=0; $todayDone=false;
    foreach ($evs as $e) if ((int)$e['plan_activity_id']===(int)$a['id']) { $related[]=$e; $sum+=(float)$e['actual_value']; if ($e['performance_date']===$date) $todayDone=true; }
    echo '<div class="card" style="border-right:8px solid '.$a['color'].'">';
    echo '<h3>'.e($a['sticker'].' '.$a['name']).'</h3><p>'.e($a['category']).' · '.e($a['frequency']).'</p>';
    echo '<p>ثبت‌شده: '.e(format_value($a['data_type'],$sum,$a['unit'])).' / هدف '.e(format_value($a['data_type'],$a['target_value'],$a['unit'])).'</p>';
    if ($plan['status']==='RUNNING') {
        if ($a['frequency']==='DAILY' && $todayDone) {
            echo '<p class="ok">ثبت شد ✓</p>';
        } else {
            echo '<form method="post">'.csrf_field().'<input type="hidden" name="pa_id" value="'.e($a['id']).'"><input type="hidden" name="date" value="'.e($date).'">';
            if ($a['data_type']==='BOOLEAN') echo '<select name="value"><option value="1">انجام شد</option><option value="0">انجام نشد</option></select>';
            elseif ($a['data_type']==='RATING') echo '<select name="value"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option></select>';
            else echo '<input name="value" type="number" step="any" required placeholder="مقدار">';
            echo '<button class="btn" type="submit">ثبت عملکرد</button></form>';
        }
    }
    echo '</div>';
}
joma_footer();
