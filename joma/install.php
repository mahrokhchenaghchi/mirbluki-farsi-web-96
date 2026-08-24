<?php
// Browser installer for cPanel. No SSH required.
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><title>نصب جوما</title></head><body style="font-family:Tahoma;max-width:640px;margin:40px auto">';
echo '<h1>نصب جوما</h1>';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<p>اگر MySQL دارید این فرم را پر کنید. برای تست بدون MySQL، فایل config.php همین حالا روی storage=file است و نیازی به این صفحه نیست.</p>';
    echo '<form method="post">';
    echo 'هاست<br><input name="h" value="localhost"><br>نام دیتابیس<br><input name="n"><br>کاربر<br><input name="u"><br>رمز<br><input type="password" name="p"><br>مسیر<br><input name="b" value="/joma"><br><button>ساخت جداول</button></form></body></html>';
    exit;
}
$mysqli = @mysqli_connect($_POST['h'], $_POST['u'], $_POST['p'], $_POST['n']);
if (!$mysqli) {
    echo 'اتصال برقرار نشد: ' . htmlspecialchars(mysqli_connect_error());
    exit;
}
mysqli_set_charset($mysqli, 'utf8mb4');
$sql = file_get_contents(dirname(__FILE__) . '/database/joma.sql');
mysqli_multi_query($mysqli, $sql);
while (mysqli_more_results($mysqli)) { mysqli_next_result($mysqli); }
$cfg = "<?php\n\$JOMA_CONFIG = array(\n  'db_host' => '" . addslashes($_POST['h']) . "',\n  'db_name' => '" . addslashes($_POST['n']) . "',\n  'db_user' => '" . addslashes($_POST['u']) . "',\n  'db_pass' => '" . addslashes($_POST['p']) . "',\n  'base_url' => '" . addslashes($_POST['b']) . "',\n  'storage' => 'mysql',\n);\n";
file_put_contents(dirname(__FILE__) . '/config/config.php', $cfg);
echo '<p>نصب انجام شد. فایل install.php را حذف کنید.</p><p><a href="index.php">ورود به جوما</a></p></body></html>';
