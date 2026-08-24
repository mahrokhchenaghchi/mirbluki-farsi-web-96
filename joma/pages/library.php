<?php
require_login();
require_perm('MANAGE_ACTIVITY_LIBRARY');
$u = current_user();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'];
    if ($act === 'save') {
        $in = array(
            'name' => trim($_POST['name']),
            'category' => $_POST['category'],
            'frequency' => $_POST['frequency'],
            'data_type' => $_POST['data_type'],
            'unit' => $_POST['unit'],
            'daily_target' => (float) $_POST['daily_target'],
            'weekly_target' => (float) $_POST['weekly_target'],
            'monthly_target' => (float) $_POST['monthly_target'],
            'weight' => (int) $_POST['weight'],
            'sticker' => $_POST['sticker'],
            'color' => $_POST['color'],
            'status' => $_POST['status'],
        );
        $msg = validate_activity_input($in);
        if ($msg === '') save_activity($u['id'], $in, (int) $_POST['id']);
    } elseif ($act === 'del' && !empty($_POST['confirm'])) {
        delete_activity($_POST['id'], $u['id']);
    } elseif ($act === 'toggle') {
        $a = get_activity($_POST['id'], $u['id']);
        if ($a) {
            $a['status'] = $a['status'] === 'INACTIVE' ? 'ACTIVE' : 'INACTIVE';
            save_activity($u['id'], $a, $a['id']);
        }
    }
}
$q = isset($_GET['q']) ? $_GET['q'] : '';
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$fr = isset($_GET['fr']) ? $_GET['fr'] : '';
$list = list_user_activities($u['id']);
$edit = isset($_GET['edit']) ? get_activity($_GET['edit'], $u['id']) : null;
joma_header('کتابخانه', array(array('label' => 'داشبورد', 'href' => joma_url('index.php?p=dashboard')), array('label' => 'کتابخانه')));
?>
<div class="page-head">
  <div>
    <h1>کتابخانه فعالیت‌ها</h1>
    <p class="lede">۴۵ فعالیت رسمی جدول JOMA. Frequency / Target / Weight از همان جدول است نه seed موقت.</p>
  </div>
  <a class="btn" href="<?php echo e(joma_url('index.php?p=library&new=1')); ?>">+ فعالیت جدید</a>
</div>
<form class="card grid grid-3" method="get">
  <input type="hidden" name="p" value="library">
  <input name="q" placeholder="جستجو نام یا ACT..." value="<?php echo e($q); ?>">
  <select name="cat"><option value="">همه دسته‌ها</option><?php foreach (categories_list() as $c) echo '<option '.($cat === $c ? 'selected' : '').'>'.e($c).'</option>'; ?></select>
  <select name="fr"><option value="">همه تناوب‌ها</option><?php foreach (frequencies_list() as $k => $v) echo '<option value="'.$k.'" '.($fr === $k ? 'selected' : '').'>'.$v.'</option>'; ?></select>
  <button class="btn sec">فیلتر</button>
</form>
<?php if ($msg) echo '<p class="toast bad">'.e($msg).'</p>'; ?>
<?php if ($edit || isset($_GET['new'])) { $row = $edit; ?>
<form class="card" method="post">
  <?php echo csrf_field(); ?>
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="id" value="<?php echo $row ? e($row['id']) : '0'; ?>">
  <h2><?php echo $row ? 'ویرایش فعالیت' : 'فعالیت جدید'; ?></h2>
  <label>نام فعالیت</label><input name="name" value="<?php echo $row ? e($row['name']) : ''; ?>" required>
  <div class="grid grid-2">
    <div><label>دسته</label><select name="category"><?php foreach (categories_list() as $c) echo '<option '.(($row && $row['category'] === $c) ? 'selected' : '').'>'.e($c).'</option>'; ?></select></div>
    <div><label>تناوب</label><select name="frequency"><?php foreach (frequencies_list() as $k => $v) echo '<option value="'.$k.'" '.(($row && $row['frequency'] === $k) ? 'selected' : '').'>'.$v.'</option>'; ?></select></div>
    <div><label>نوع داده</label><select name="data_type"><?php foreach (datatypes_list() as $k => $v) echo '<option value="'.$k.'" '.(($row && $row['data_type'] === $k) ? 'selected' : '').'>'.$v.'</option>'; ?></select></div>
    <div><label>واحد</label><select name="unit"><?php foreach (units_list() as $k => $v) echo '<option value="'.$k.'" '.(($row && $row['unit'] === $k) ? 'selected' : '').'>'.$v.'</option>'; ?></select></div>
  </div>
  <div class="grid grid-3">
    <div><label>هدف روزانه</label><input name="daily_target" dir="ltr" value="<?php echo $row ? e($row['daily_target']) : '1'; ?>"></div>
    <div><label>هدف هفتگی</label><input name="weekly_target" dir="ltr" value="<?php echo $row ? e($row['weekly_target']) : '1'; ?>"></div>
    <div><label>هدف ماهانه</label><input name="monthly_target" dir="ltr" value="<?php echo $row ? e($row['monthly_target']) : '1'; ?>"></div>
  </div>
  <label>وزن</label>
  <select name="weight"><?php for ($i = 1; $i <= 8; $i++) echo '<option '.(($row && (int) $row['weight'] === $i) ? 'selected' : '').'>'.$i.'</option>'; ?></select>
  <label>استیکر</label>
  <select name="sticker"><?php foreach (stickers_list() as $s) echo '<option '.(($row && $row['sticker'] === $s) ? 'selected' : '').'>'.$s.'</option>'; ?></select>
  <label>رنگ</label><input name="color" dir="ltr" value="<?php echo $row ? e($row['color']) : '#B8D4F0'; ?>">
  <label>وضعیت</label>
  <select name="status"><option value="ACTIVE">فعال</option><option value="INACTIVE" <?php echo ($row && $row['status'] === 'INACTIVE') ? 'selected' : ''; ?>>غیرفعال</option></select>
  <div class="btn-row" style="margin-top:14px">
    <button class="btn">ذخیره</button>
    <a class="btn sec" href="<?php echo e(joma_url('index.php?p=library')); ?>">انصراف</a>
  </div>
</form>
<?php } ?>
<div class="grid grid-2">
<?php
$shown = 0;
foreach ($list as $a) {
    if ($q && !joma_contains($a['name'] . $a['code'], $q)) continue;
    if ($cat && $a['category'] !== $cat) continue;
    if ($fr && $a['frequency'] !== $fr) continue;
    $shown++;
    $dim = $a['status'] === 'INACTIVE' ? 'opacity:.6' : '';
    echo '<article class="card" style="padding:0;'.$dim.'">';
    echo '<div class="act-head" style="background:'.$a['color'].'66"><div class="sticker">'.e($a['sticker']).'</div><span class="chip" dir="ltr">'.e($a['code']).'</span></div>';
    echo '<div class="act-body"><h3>'.e($a['name']).'</h3>';
    echo '<p class="meta">'.e($a['category']).'</p>';
    echo '<p class="meta">'.e(frequencies_list()[$a['frequency']]).' · '.e(datatypes_list()[$a['data_type']]).' · وزن '.fa_num($a['weight']).'</p>';
    echo '<p class="meta">هدف روز '.fa_num($a['daily_target']).' · هفته '.fa_num($a['weekly_target']).' · ماه '.fa_num($a['monthly_target']).'</p>';
    echo '<div class="btn-row grow">';
    echo '<a class="btn sec" href="'.e(joma_url('index.php?p=library&edit='.$a['id'])).'">ویرایش</a>';
    echo '<form method="post">'.csrf_field().'<input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="'.$a['id'].'"><button class="btn ghost">'.($a['status'] === 'INACTIVE' ? 'فعال‌سازی' : 'غیرفعال').'</button></form>';
    echo '<form method="post" onsubmit="return confirm(\'حذف از کتابخانه؟ تصویر دوره‌های قبلی می‌ماند.\')">'.csrf_field().'<input type="hidden" name="action" value="del"><input type="hidden" name="confirm" value="1"><input type="hidden" name="id" value="'.$a['id'].'"><button class="btn ghost">حذف</button></form>';
    echo '</div></div></article>';
}
if (!$shown) echo empty_state('موردی نیست', 'فیلتر را عوض کنید یا فعالیت جدید بسازید.');
?>
</div>
<?php joma_footer(); ?>
