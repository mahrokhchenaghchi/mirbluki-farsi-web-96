<?php
require_login();
$u = current_user();
$today = jalali_today();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $scores = array(
        'energy' => (int) $_POST['energy'],
        'general' => (int) $_POST['general'],
        'focus' => (int) $_POST['focus'],
        'sleep' => (int) $_POST['sleep'],
        'stress' => (int) $_POST['stress'],
    );
    foreach ($scores as $v) {
        if ($v < 1 || $v > 5) { $err = 'هر پنج شاخص را انتخاب کنید.'; break; }
    }
    if ($err === '') {
        save_mood($u['id'], $today, $scores, isset($_POST['note']) ? $_POST['note'] : '');
        joma_redirect('index.php?p=dashboard');
    }
}
$ex = get_mood($u['id'], $today);
$metrics = array(
    'energy' => array('انرژی', array('😴','😐','🙂','😄','⚡')),
    'general' => array('حال عمومی', array('😞','😐','🙂','😊','🤩')),
    'focus' => array('تمرکز', array('🌫️','😐','🙂','🎯','🧠')),
    'sleep' => array('کیفیت خواب', array('😫','😐','🙂','😴','✨')),
    'stress' => array('سطح استرس', array('😌','🙂','😐','😟','😣')),
);
joma_header('خلق من', array(array('label'=>'داشبورد','href'=>joma_url('index.php?p=dashboard')), array('label'=>'خلق')));
?>
<h1>وقت بخیر 🌱</h1>
<p>امروز چطوری؟ <?php echo e(jalali_format($today)); ?></p>
<form method="post">
<?php echo csrf_field();
foreach ($metrics as $key => $m) {
    $cur = $ex ? (int)$ex[$key === 'general' ? 'general_mood' : ($key === 'sleep' ? 'sleep_quality' : $key)] : 0;
    echo '<div class="card"><h3>'.e($m[0]).'</h3><div class="mood-row">';
    for ($i=1;$i<=5;$i++) {
        $id = $key.'_'.$i;
        echo '<label><input type="radio" name="'.e($key).'" value="'.$i.'" '.($cur===$i?'checked':'').' style="display:none">';
        echo '<button type="button" class="moodbtn '.($cur===$i?'on':'').'" data-name="'.e($key).'" data-v="'.$i.'">'.$m[1][$i-1].'</button></label>';
    }
    echo '</div></div>';
}
?>
<div class="card"><label>یادداشت اختیاری</label><textarea name="note"><?php echo $ex ? e($ex['note']) : ''; ?></textarea></div>
<?php if ($err) echo '<p class="bad">'.e($err).'</p>'; ?>
<button class="btn" type="submit">ذخیره و ادامه</button>
</form>
<script>
document.querySelectorAll('.moodbtn').forEach(function(b){
  b.addEventListener('click', function(){
    var n=b.getAttribute('data-name');
    document.querySelectorAll('input[name="'+n+'"]').forEach(function(i){ i.checked = i.value===b.getAttribute('data-v'); });
    b.parentNode.parentNode.querySelectorAll('.moodbtn').forEach(function(x){ x.classList.remove('on'); });
    b.classList.add('on');
  });
});
</script>
<?php joma_footer(); ?>
