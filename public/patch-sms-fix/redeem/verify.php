<?php
/** مرحلهٔ ۲: تأیید کد پیامک‌شده و فعال‌سازی سریال */
require __DIR__ . '/lib.php';

$phone  = norm_phone($_REQUEST['p'] ?? '');
$serial = norm_serial($_REQUEST['s'] ?? '');
$src    = preg_replace('/[^a-z0-9_-]/i', '', (string)($_REQUEST['src'] ?? 'tract')) ?: 'tract';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone  = norm_phone($_POST['p'] ?? $phone);
    $serial = norm_serial($_POST['s'] ?? $serial);
    $code   = preg_replace('/\D/', '', (string)($_POST['code'] ?? ''));
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $err = 'نشست منقضی شده است. صفحه را دوباره باز کنید.';
    } elseif (!valid_phone($phone) || strlen($code) !== 6) {
        $err = 'شماره یا کد تأیید درست نیست.';
    } else {
        $st = db()->prepare('SELECT * FROM otp WHERE phone=? AND consumed=0 ORDER BY id DESC LIMIT 1');
        $st->execute([$phone]);
        $row = $st->fetch();
        if (!$row) {
            $err = 'کدی برای این شماره ثبت نشده است. دوباره درخواست کن.';
        } elseif ((int)$row['expires_at'] < now_ts()) {
            $err = 'کد منقضی شده است. کد تازه بگیر.';
        } elseif ((int)$row['tries'] >= (int)cfg('otp')['max_tries']) {
            $err = 'تعداد تلاش‌ها بیش از حد شد. کد تازه بگیر.';
        } elseif (!password_verify($code, (string)$row['code_hash'])) {
            db()->prepare('UPDATE otp SET tries=tries+1 WHERE id=?')->execute([$row['id']]);
            log_event('otp_fail', $phone, ['serial' => $serial]);
            $err = 'کد تأیید اشتباه است.';
        } else {
            db()->prepare('UPDATE otp SET consumed=1 WHERE id=?')->execute([$row['id']]);

            // فعال‌سازی سریال
            $si = serial_info($serial);
            if ($si && ($si['status'] ?? '') !== 'used') {
                db()->prepare("UPDATE serials SET status='used', phone=?, activated_at=? WHERE code=?")
                   ->execute([$phone, now_str(), $serial]);
                log_event('serial_activated', $phone, ['serial' => $serial, 'src' => $src]);
            } else {
                log_event('otp_ok_no_serial', $phone, ['serial' => $serial, 'src' => $src]);
            }

            // حساب کاربر + توکن یکتای عبور به سایت تست
            $u = user_by_phone($phone);
            if (!$u) {
                db()->prepare('INSERT INTO users (phone,serial,src,created_at) VALUES (?,?,?,?)')
                   ->execute([$phone, $serial, $src, now_str()]);
                $u = user_by_phone($phone);
            }
            if (empty($u['test_token'])) {
                $tk = gen_token();
                db()->prepare('UPDATE users SET test_token=?, serial=?, src=? WHERE phone=?')
                   ->execute([$tk, $serial ?: $u['serial'], $src, $phone]);
            }
            if (!empty($_SESSION['gate_phone'])) unset($_SESSION['gate_phone']);
            header('Location: welcome.php?p=' . urlencode($phone) . '&s=' . urlencode($serial) . '&src=' . urlencode($src));
            exit;
        }
    }
}

$dev_mode = (otp_mode() === 'dev');
$dev_hint = '';
$fallback_hint = '';
if ($dev_mode) {
    $log = __DIR__ . '/data/dev-otp.log';
    if (is_readable($log)) {
        $lines = array_filter(explode("\n", trim((string)file_get_contents($log))));
        $last = trim((string)end($lines));
        if ($last !== '' && str_contains($last, $phone)) {
            $dev_hint = 'حالت تست (بدون پیامک واقعی): ' . $last;
        } else {
            $dev_hint = 'حالت تست روشن است؛ کد در فایل redeem/data/dev-otp.log ثبت می‌شود.';
        }
    }
}

if (!$dev_mode && otp_fallback()) {
    // پیامک واقعی نرفت؟ برای اینکه مخاطب گیر نکند، کد پشتیبان (با همان اعتبار ۲ دقیقه) نشان داده می‌شود.
    $flog = __DIR__ . '/data/fallback-otp.log';
    if (is_readable($flog)) {
        $flines = array_filter(explode("\n", trim((string)file_get_contents($flog))));
        $flast = trim((string)end($flines));
        if ($flast !== '' && str_contains($flast, $phone)) {
            $fts = strtotime(substr($flast, 0, 19));
            $fttl = (int)cfg('otp')['ttl'];
            if ($fts !== false && (time() - $fts) <= $fttl) {
                $fcode = trim(substr($flast, (int)strrpos($flast, ':') + 1));
                $fallback_hint = 'ارسال پیامک ناموفق بود. کد پشتیبان: ' . $fcode . ' — همین را وارد کن و به پشتیبانی اطلاع بده.';
            }
        }
    }
}

page_head('تأیید کد پیامک', 'کد ۶ رقمی را وارد کن.');
?>
<div class="card">
  <div class="progress"><i class="done"></i><i class="on"></i><i></i><i></i></div>
  <h1>کد تأیید را وارد کن</h1>
  <p class="sub"><?= $dev_mode ? 'در حالت تست، کد پیامک واقعی نمی‌شود.' : 'کد به شمارهٔ <span class="mono">' . h($phone) . '</span> پیامک شد.' ?> اعتبار: <?= h((string)cfg('otp')['ttl']) ?> ثانیه.</p>

  <?php if ($err !== ''): ?><div class="msg err"><?= h($err) ?></div><?php endif; ?>
  <?php if ($dev_hint !== ''): ?><div class="msg info"><?= h($dev_hint) ?></div><?php endif; ?>
  <?php if (!empty($fallback_hint)): ?><div class="msg err"><?= h($fallback_hint) ?></div><?php endif; ?>

  <form method="post" action="verify.php" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="p" value="<?= h($phone) ?>">
    <input type="hidden" name="s" value="<?= h($serial) ?>">
    <input type="hidden" name="src" value="<?= h($src) ?>">
    <label for="code">کد ۶ رقمی</label>
    <input id="code" class="mono" type="text" name="code" inputmode="numeric" maxlength="6" placeholder="------" style="text-align:center;font-size:24px;letter-spacing:8px" required autofocus>
    <button class="btn" type="submit">تأیید و فعال‌سازی</button>
  </form>
  <a class="btn ghost small" href="index.php?s=<?= h($serial) ?>&src=<?= h($src) ?>&p=<?= h($phone) ?>" style="margin-top:12px">تغییر شماره / دریافت کد تازه</a>
</div>
<?php page_foot();
