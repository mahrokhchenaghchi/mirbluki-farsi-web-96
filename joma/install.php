<?php
header('Content-Type: text/html; charset=utf-8');
$css = 'body{font-family:Tahoma,sans-serif;background:#fbf7f2;color:#332b3d;direction:rtl;margin:0}
.wrap{max-width:640px;margin:40px auto;padding:24px;background:#fff;border-radius:24px;box-shadow:0 16px 40px rgba(92,70,130,.08)}
input{width:100%;padding:10px;margin:6px 0 14px;border:1px solid #e5dceb;border-radius:12px}
button{background:#7c5cbf;color:#fff;border:0;border-radius:14px;padding:10px 16px;font-family:inherit}';
echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>نصب جوما</title><style>'.$css.'</style></head><body><div class="wrap">';
echo '<h1>نصب جوما</h1>';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<p>اگر MySQL دارید این فرم را پر کنید. برای تست بدون MySQL، فایل config.php همین حالا روی storage=file است و نیازی به این صفحه نیست.</p>';
    echo '<p>Deploy روی Production را فقط بعد از تأیید خودتان انجام دهید.</p>';
    echo '<form method="post">';
    echo 'هاست<input name="h" value="localhost">نام دیتابیس<input name="n">کاربر<input name="u">رمز<input type="password" name="p">مسیر<input name="b" value="/joma">';
    echo '<button>ساخت جداول</button></form></div></body></html>';
    exit;
}
$mysqli = @mysqli_connect($_POST['h'], $_POST['u'], $_POST['p'], $_POST['n']);
if (!$mysqli) {
    echo '<p>اتصال برقرار نشد: ' . htmlspecialchars(mysqli_connect_error()) . '</p></div></body></html>';
    exit;
}
mysqli_set_charset($mysqli, 'utf8mb4');
$sql = file_get_contents(dirname(__FILE__) . '/database/joma.sql');
mysqli_multi_query($mysqli, $sql);
while (mysqli_more_results($mysqli)) { mysqli_next_result($mysqli); }
$cfg = "<?php\n\$JOMA_CONFIG = array(\n  'db_host' => '" . addslashes($_POST['h']) . "',\n  'db_name' => '" . addslashes($_POST['n']) . "',\n  'db_user' => '" . addslashes($_POST['u']) . "',\n  'db_pass' => '" . addslashes($_POST['p']) . "',\n  'base_url' => '" . addslashes($_POST['b']) . "',\n  'storage' => 'mysql',\n);\n";
file_put_contents(dirname(__FILE__) . '/config/config.php', $cfg);
echo '<p>نصب انجام شد. فایل install.php را حذف کنید.</p><p><a href="index.php">ورود به جوما</a></p></div></body></html>';
