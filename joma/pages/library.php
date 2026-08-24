<?php
require_login(); require_perm('MANAGE_ACTIVITY_LIBRARY');
$u = current_user();
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $act = $_POST['action'];
    if ($act==='save') {
        $in = array(
            'name'=>trim($_POST['name']),
            'category'=>$_POST['category'],
            'frequency'=>$_POST['frequency'],
            'data_type'=>$_POST['data_type'],
            'unit'=>$_POST['unit'],
            'daily_target'=>(float)$_POST['daily_target'],
            'weekly_target'=>(float)$_POST['weekly_target'],
            'monthly_target'=>(float)$_POST['monthly_target'],
            'weight'=>(int)$_POST['weight'],
            'sticker'=>$_POST['sticker'],
            'color'=>$_POST['color'],
            'status'=>$_POST['status'],
        );
        if ($in['name']==='') $msg='نام فعالیت را وارد کنید.';
        else save_activity($u['id'], $in, (int)$_POST['id']);
    } elseif ($act==='del' && !empty($_POST['confirm'])) {
        delete_activity($_POST['id'], $u['id']);
    } elseif ($act==='toggle') {
        $a = get_activity($_POST['id'], $u['id']);
        if ($a) {
            $a['status'] = $a['status']==='INACTIVE' ? 'ACTIVE' : 'INACTIVE';
            save_activity($u['id'], $a, $a['id']);
        }
    }
}
$q = isset($_GET['q']) ? $_GET['q'] : '';
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$fr = isset($_GET['fr']) ? $_GET['fr'] : '';
$list = list_user_activities($u['id']);
$edit = isset($_GET['edit']) ? get_activity($_GET['edit'], $u['id']) : null;
joma_header('کتابخانه', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'کتابخانه')));
?>
<h1>کتابخانه فعالیت‌ها</h1>
<p>۴۵ فعالیت رسمی جدول JOMA. Frequency/Target/Weight از همان جدول است نه seed موقت.</p>
<form class="card grid grid-3" method="get">
<input type="hidden" name="p" value="library">
<input name="q" placeholder="جستجو" value="<?php echo e($q); ?>">
<select name="cat"><option value="">همه دسته‌ها</option><?php foreach(categories_list() as $c) echo '<option '.($cat===$c?'selected':'').'>'.e($c).'</option>'; ?></select>
<select name="fr"><option value="">همه تناوب‌ها</option><?php foreach(frequencies_list() as $k=>$v) echo '<option value="'.$k.'" '.($fr===$k?'selected':'').'>'.$v.'</option>'; ?></select>
<button class="btn sec">فیلتر</button>
</form>
<p><a class="btn" href="<?php echo e(joma_url('index.php?p=library&new=1')); ?>">+ فعالیت جدید</a></p>
<?php if ($edit || isset($_GET['new'])) { $e=$edit; ?>
<form class="card" method="post">
<?php echo csrf_field(); ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?php echo $e?e($e['id']):'0'; ?>">
<label>نام فعالیت</label><input name="name" value="<?php echo $e?e($e['name']):''; ?>" required>
<label>دسته</label><select name="category"><?php foreach(categories_list() as $c) echo '<option '.(($e&&$e['category']===$c)?'selected':'').'>'.e($c).'</option>'; ?></select>
<label>تناوب</label><select name="frequency"><?php foreach(frequencies_list() as $k=>$v) echo '<option value="'.$k.'" '.(($e&&$e['frequency']===$k)?'selected':'').'>'.$v.'</option>'; ?></select>
<label>نوع داده</label><select name="data_type"><?php foreach(datatypes_list() as $k=>$v) echo '<option value="'.$k.'" '.(($e&&$e['data_type']===$k)?'selected':'').'>'.$v.'</option>'; ?></select>
<label>واحد</label><select name="unit"><?php foreach(units_list() as $k=>$v) echo '<option value="'.$k.'" '.(($e&&$e['unit']===$k)?'selected':'').'>'.$v.'</option>'; ?></select>
<label>هدف روزانه</label><input name="daily_target" value="<?php echo $e?e($e['daily_target']):'1'; ?>">
<label>هدف هفتگی</label><input name="weekly_target" value="<?php echo $e?e($e['weekly_target']):'1'; ?>">
<label>هدف ماهانه</label><input name="monthly_target" value="<?php echo $e?e($e['monthly_target']):'1'; ?>">
<label>وزن</label><select name="weight"><?php for($i=1;$i<=8;$i++) echo '<option '.(($e&&(int)$e['weight']===$i)?'selected':'').'>'.$i.'</option>'; ?></select>
<label>استیکر</label><select name="sticker"><?php foreach(stickers_list() as $s) echo '<option '.(($e&&$e['sticker']===$s)?'selected':'').'>'.$s.'</option>'; ?></select>
<label>رنگ</label><input name="color" value="<?php echo $e?e($e['color']):'#B8D4F0'; ?>">
<label>وضعیت</label><select name="status"><option value="ACTIVE">فعال</option><option value="INACTIVE" <?php echo ($e&&$e['status']==='INACTIVE')?'selected':''; ?>>غیرفعال</option></select>
<button class="btn">ذخیره</button>
</form>
<?php } if ($msg) echo '<p>'.e($msg).'</p>'; ?>
<div class="grid grid-2">
<?php foreach ($list as $a) {
    if ($q && mb_strpos($a['name'].$a['code'], $q)===false) continue;
    if ($cat && $a['category']!==$cat) continue;
    if ($fr && $a['frequency']!==$fr) continue;
    echo '<div class="card"><div class="sticker" style="background:'.$a['color'].'">'.e($a['sticker']).'</div><h3>'.e($a['name']).'</h3><p dir="ltr">'.e($a['code']).'</p><p>'.e($a['category']).' · '.e($a['frequency']).' · '.e($a['data_type']).' · وزن '.e($a['weight']).'</p>';
    echo '<a class="btn sec" href="'.e(joma_url('index.php?p=library&edit='.$a['id'])).'">ویرایش</a> ';
    echo '<form style="display:inline" method="post">'.csrf_field().'<input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="'.$a['id'].'"><button class="btn ghost">'.($a['status']==='INACTIVE'?'فعال‌سازی':'غیرفعال').'</button></form> ';
    echo '<form style="display:inline" method="post" onsubmit="return confirm(\'حذف از کتابخانه؟ تصویر دوره‌های قبلی می‌ماند.\')">'.csrf_field().'<input type="hidden" name="action" value="del"><input type="hidden" name="confirm" value="1"><input type="hidden" name="id" value="'.$a['id'].'"><button class="btn ghost">حذف</button></form></div>';
} ?>
</div>
<?php joma_footer(); ?>
