<?php
/**
 * صفحهٔ ورود سریال — «دروازهٔ تراکت»
 * آدرس: https://test.mirbolouki.com/redeem/?s=ML-1404-0001&src=tract
 */
require __DIR__ . '/lib.php';

// آدرس آماده‌سازی: /redeem/?setup=1  → جدول‌ها ساخته و سریال‌ها از serials.csv درج می‌شوند
if (isset($_GET['setup'])) {
    $n = (int)db()->query('SELECT COUNT(*) c FROM serials')->fetch()['c'];
    $used = (int)db()->query("SELECT COUNT(*) c FROM serials WHERE status='used'")->fetch()['c'];
    log_event('setup_run', null, ['serials' => $n]);
    page_head('آماده‌سازی انجام شد', 'پایگاه داده ساخته شد.');
    ?>
    <div class="card">
      <h1>همه‌چیز آماده است ✅</h1>
      <p class="sub">پایگاه داده ساخته شد و <b><?= h((string)$n) ?></b> سریال درج شد (فعال‌شده تا حالا: <?= h((string)$used) ?>).</p>
      <a class="btn" href="../check.php">برگشت به صفحهٔ بررسی نصب</a>
      <a class="btn ghost" href="?s=ML-1404-0001&src=setup">امتحان دروازه با سریال ۰۰۰۱</a>
      <a class="btn ghost" href="admin.php">پنل مدیریت</a>
    </div>
    <?php page_foot(); exit;
}

$serial = norm_serial($_GET['s'] ?? $_POST['s'] ?? '');
$src    = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['src'] ?? $_POST['src'] ?? 'tract')) ?: 'tract';
$err = '';
$prefill_phone = norm_phone($_GET['p'] ?? $_POST['p'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? null)) {
        $err = 'نشست منقضی شده است. لطفاً صفحه را دوباره باز کنید.';
    } else {
        $phone = norm_phone($_POST['phone'] ?? '');
        if (!valid_phone($phone)) {
            $err = 'شمارهٔ موبایل را به شکل ۰۹xxxxxxxxx وارد کنید.';
        } elseif ($serial !== '' && !serial_info($serial)) {
            $err = 'این کد شناسایی پیدا نشد. کد روی تراکت را دوباره بررسی کنید.';
        } else {
            $serial = $serial !== '' ? $serial : 'OPEN-' . date('ymdHis');
            if (!serial_info($serial)) {
                try { db()->prepare('INSERT INTO serials (code,batch,status) VALUES (?,?,?)')->execute([$serial, 'open', 'new']); } catch (Throwable $e) {}
            }
            $si = serial_info($serial);
            if (($si['status'] ?? '') === 'used' && ($si['phone'] ?? '') !== $phone) {
                $err = 'این کد پیش‌تر با شمارهٔ دیگری فعال شده است. برای پیگیری به پشتیبانی پیام دهید.';
            } else {
                if (!rate_limit('otp_sent', $phone, (int)cfg('otp')['send_per_hr'], 3600)) {
                    $err = 'تعداد درخواست کد برای این شماره زیاد بوده است. یک ساعت دیگر تلاش کنید.';
                } else {
                    $code = gen_code(6);
                    db()->prepare('INSERT INTO otp (phone,code_hash,created_at,expires_at,tries,consumed) VALUES (?,?,?,?,0,0)')
                        ->execute([$phone, password_hash($code, PASSWORD_DEFAULT), now_ts(), now_ts() + (int)cfg('otp')['ttl']]);
                    $sent = send_otp_sms($phone, $code);
                    if (!$sent && otp_fallback()) log_fallback_code($phone, $code);
                    log_event('otp_sent', $phone, ['serial' => $serial, 'src' => $src, 'ok' => $sent, 'fallback' => (!$sent && otp_fallback())]);
                    $u = user_by_phone($phone);
                    if (!$u) {
                        try {
                            db()->prepare('INSERT INTO users (phone,serial,src,created_at) VALUES (?,?,?,?)')
                               ->execute([$phone, $serial, $src, now_str()]);
                        } catch (Throwable $e) { /* شماره هم‌زمان ثبت شده؛ مسیر کاربر باید ادامه پیدا کند */ }
                    } else {
                        // باگ نسخهٔ قبل: یک «?» اضافه باعث خطای SQL (و صفحهٔ سفید ۵۰۰) برای شمارهٔ تکراری می‌شد
                        try {
                            db()->prepare('UPDATE users SET serial=COALESCE(NULLIF(serial,\'\'),?) WHERE phone=?')
                               ->execute([$serial, $phone]);
                        } catch (Throwable $e) { /* ثبت سریال نباید مسیر مخاطب را ببندد */ }
                    }
                    if (!empty($_SESSION['gate_ok'])) unset($_SESSION['gate_ok']);
                    header('Location: verify.php?p=' . urlencode($phone) . '&s=' . urlencode($serial) . '&src=' . urlencode($src));
                    exit;
                }
            }
        }
    }
}

page_head('فعال‌سازی کد مسیر', 'یک کد شناسایی روی برگهٔ ایونت داری؟ اینجا فعالش کن.');
?>
<div class="card">
  <div class="progress"><i class="on"></i><i></i><i></i><i></i></div>
  <h1>شماره‌ات را بنویس</h1>
  <p class="sub">کد یک‌بارمصرف برایت پیامک می‌شود. همین شماره، حساب ورود تو به پنل جوما می‌شود.</p>

  <?php if ($err !== ''): ?><div class="msg err"><?= h($err) ?></div><?php endif; ?>

  <form method="post" action="index.php" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="src" value="<?= h($src) ?>">
    <label for="s">کد شناسایی روی تراکت</label>
    <input id="s" class="mono" type="text" name="s" value="<?= h($serial) ?>" placeholder="ML-1404-0001" inputmode="text">
    <label for="phone">شمارهٔ موبایل</label>
    <input id="phone" class="mono" type="tel" name="phone" value="<?= h($prefill_phone) ?>" placeholder="09xxxxxxxxx" inputmode="numeric" maxlength="11" required>
    <div class="hint">اگر کد را نداری و از این صفحه رسیدی، خالی بگذار؛ مسیر «درخواست پکیج» باز می‌ماند.</div>
    <button class="btn" type="submit">دریافت کد تأیید</button>
  </form>

  <div class="perks"><span class="perk">تست طرحوارهٔ رایگان</span><span class="perk">گزارش ۶ حوزه</span><span class="perk">پیشنهاد پکیج تمرین</span></div>
</div>
<?php page_foot();
