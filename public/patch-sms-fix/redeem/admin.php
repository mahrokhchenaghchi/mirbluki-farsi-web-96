<?php
/** پنل مدیریت دروازه: سریال‌ها · کاربران · تست‌ها · درخواست‌ها · رویدادها + CSV */
require __DIR__ . '/lib.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$tab = preg_replace('/[^a-z]/', '', (string)($_GET['tab'] ?? 'dash'));
$err = '';

if (isset($_GET['logout'])) { unset($_SESSION['admin_ok']); header('Location: admin.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if (hash_equals((string)cfg('admin_pass'), (string)$_POST['pass'])) { $_SESSION['admin_ok'] = true; header('Location: admin.php'); exit; }
    $err = 'رمز اشتباه است.';
}

if (empty($_SESSION['admin_ok'])) {
    page_head('ورود مدیر', 'دسترسی محدود');
    ?>
    <div class="card" style="max-width:400px;margin-top:60px">
      <h1>پنل دروازه</h1>
      <?php if ($err): ?><div class="msg err"><?= h($err) ?></div><?php endif; ?>
      <form method="post"><label for="pass">رمز مدیر</label>
      <input id="pass" type="password" name="pass" autofocus><button class="btn" type="submit">ورود</button></form>
    </div>
    <?php page_foot(); exit;
}

// ---------------- خروجی CSV ----------------
if (isset($_GET['csv'])) {
    $map = [
        'serials' => ['SELECT code,batch,status,phone,activated_at FROM serials ORDER BY code', ['کد', 'بچ', 'وضعیت', 'موبایل', 'فعال‌سازی']],
        'users' => ['SELECT phone,serial,src,test_completed,created_at FROM users ORDER BY id', ['موبایل', 'سریال', 'منبع', 'تست تمام', 'ثبت']],
        'results' => ['SELECT phone,top1,top2,top3,scores_json,created_at FROM results ORDER BY id', ['موبایل', 'اول', 'دوم', 'سوم', 'نمره‌ها', 'تاریخ']],
        'requests' => ['SELECT id,phone,name,package_key,note,status,created_at FROM requests ORDER BY id', ['شناسه', 'موبایل', 'نام', 'پکیج', 'یادداشت', 'وضعیت', 'تاریخ']],
        'events' => ['SELECT ts,name,phone,meta FROM events ORDER BY id', ['زمان', 'رویداد', 'موبایل', 'جزئیات']],
    ];
    $key = (string)$_GET['csv'];
    if (!isset($map[$key])) { http_response_code(400); exit('bad csv key'); }
    [$sql, $head] = $map[$key];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="gate-' . $key . '-' . date('Ymd-Hi') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF" . implode(',', $head) . "\n");
    foreach (db()->query($sql) as $row) {
        foreach ($row as $k => $v) { if ($k === 'ts') $row[$k] = date('Y-m-d H:i', (int)$v); }
        fputcsv($out, array_values($row));
    }
    fclose($out);
    exit;
}

// ---------------- عملیات سریع ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'reset_serial') {
    $code = norm_serial($_POST['code'] ?? '');
    db()->prepare("UPDATE serials SET status='new', phone=NULL, activated_at=NULL WHERE code=?")->execute([$code]);
    log_event('admin_reset_serial', null, ['serial' => $code]);
    header('Location: admin.php?tab=serials'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'add_batch') {
    $prefix = strtoupper(trim((string)($_POST['prefix'] ?? 'ML-1404-')));
    $from = max(1, (int)($_POST['from'] ?? 1)); $to = max($from, (int)($_POST['to'] ?? 20));
    $st = db()->prepare('INSERT INTO serials (code,batch,status) VALUES (?,?,?)');
    for ($i = $from; $i <= $to; $i++) {
        $code = $prefix . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
        try { $st->execute([$code, date('Ymd'), 'new']); } catch (Throwable $e) {}
    }
    header('Location: admin.php?tab=serials'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'test_sms') {
    $to = norm_phone($_POST['test_phone'] ?? '');
    if (!valid_phone($to)) {
        set_setting('sms_last_attempt', date('Y-m-d H:i') . ' · ناموفق ✗ · شمارهٔ نامعتبر');
    } elseif (otp_mode() === 'dev') {
        set_setting('sms_last_attempt', date('Y-m-d H:i') . ' · موفق ✓ (حالت تست) · پیامک واقعی نرفت');
    } else {
        $demo = '123456';
        $r = sms_dispatch($to, $demo, ['code' => $demo, 'value' => $demo]);
        set_setting('sms_last_attempt', date('Y-m-d H:i') . ' · ' . ($r['ok'] ? 'موفق ✓ · ' : 'ناموفق ✗ · ') . (string)$r['detail']);
    }
    header('Location: admin.php?tab=dash&sent=1'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'save_settings') {
    // حالت کد تأیید
    if (isset($_POST['otp_mode'])) {
        set_setting('otp_mode', in_array((string)$_POST['otp_mode'], ['dev', 'sms'], true) ? (string)$_POST['otp_mode'] : 'dev');
    }
    $allow = ['sms_provider', 'sms_key', 'sms_user', 'sms_pass', 'sms_sender', 'sms_url',
              'sms_template_id', 'sms_param_code', 'sms_send_welcome', 'sms_template_welcome', 'sms_param_welcome',
              'otp_fallback_onscreen', 'sms_text_otp', 'sms_text_welcome'];
    $saved = []; $skipped = [];
    foreach ($allow as $k) {
        if (!array_key_exists($k, $_POST)) continue;
        $v = trim(str_replace(["\r", "\n", "\t"], '', (string)$_POST[$k]));
        if (strncmp($v, "\xEF\xBB\xBF", 3) === 0) $v = trim(substr($v, 3));
        if ($k === 'sms_key' && $v === '') { $skipped[] = $k; continue; }   // خالی = «تغییر نده» (تا کلید اشتباهی پاک نشود)
        set_setting($k, $v === SMS_KEY_PLACEHOLDER ? '' : $v);
        if ($k === 'sms_key') { set_setting('applied_sms_key', $v); }
        // با این برچسب، config.php دیگر مقدارِ تازهٔ پنل را بازنویسی نمی‌کند
        if ((string)setting('applied:' . $k, '') !== $v) set_setting('applied:' . $k, $v);
        $saved[] = $k;
    }
    log_event('admin_sms_settings_saved', null, ['fields' => $saved, 'skipped' => $skipped, 'mode' => (string)($_POST['otp_mode'] ?? '')]);
    header('Location: admin.php?tab=settings&saved=' . count($saved) . ($skipped ? '&skipped=1' : '')); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'check_key') {
    $r = sms_check_credit();
    set_setting('sms_key_check', date('Y-m-d H:i') . ' · ' . ($r['ok'] ? 'موفق ✓ · ' : 'ناموفق ✗ · ') . (string)$r['detail']);
    log_event('admin_sms_key_check', null, ['ok' => $r['ok'], 'detail' => $r['detail']]);
    header('Location: admin.php?tab=settings&checked=1'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['op'] ?? '') === 'req_status') {
    db()->prepare('UPDATE requests SET status=?, updated_at=? WHERE id=?')
       ->execute([preg_replace('/[^a-z_]/', '', (string)$_POST['status']), now_str(), (int)$_POST['id']]);
    header('Location: admin.php?tab=requests'); exit;
}

$stats = [
    'serials'  => (int)db()->query('SELECT COUNT(*) c FROM serials')->fetch()['c'],
    'used'     => (int)db()->query("SELECT COUNT(*) c FROM serials WHERE status='used'")->fetch()['c'],
    'users'    => (int)db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'],
    'tests'    => (int)db()->query('SELECT COUNT(*) c FROM results')->fetch()['c'],
    'requests' => (int)db()->query('SELECT COUNT(*) c FROM requests')->fetch()['c'],
    'events'   => (int)db()->query('SELECT COUNT(*) c FROM events')->fetch()['c'],
];

page_head('پنل دروازه', 'ایونت کتابخانه نیمه‌شب');
?>
<div class="stat">
  <div><b><?= h((string)$stats['serials']) ?></b><span>سریال</span></div>
  <div><b><?= h((string)$stats['used']) ?></b><span>فعال‌شده</span></div>
  <div><b><?= h((string)$stats['users']) ?></b><span>کاربر</span></div>
  <div><b><?= h((string)$stats['tests']) ?></b><span>تست انجام‌شده</span></div>
  <div><b><?= h((string)$stats['requests']) ?></b><span>درخواست پکیج</span></div>
</div>

<div class="card">
<div class="brandbar" style="margin-bottom:8px">
  <span>
    <a href="admin.php?tab=dash" style="<?= $tab === 'dash' ? 'font-weight:700' : '' ?>">خلاصه</a> ·
    <a href="admin.php?tab=serials" style="<?= $tab === 'serials' ? 'font-weight:700' : '' ?>">سریال‌ها</a> ·
    <a href="admin.php?tab=users" style="<?= $tab === 'users' ? 'font-weight:700' : '' ?>">کاربران</a> ·
    <a href="admin.php?tab=results" style="<?= $tab === 'results' ? 'font-weight:700' : '' ?>">تست‌ها</a> ·
    <a href="admin.php?tab=requests" style="<?= $tab === 'requests' ? 'font-weight:700' : '' ?>">درخواست‌ها</a> ·
    <a href="admin.php?tab=events" style="<?= $tab === 'events' ? 'font-weight:700' : '' ?>">رویدادها</a> ·
    <a href="admin.php?tab=settings" style="<?= $tab === 'settings' ? 'font-weight:700' : '' ?>">تنظیمات پیامک</a>
  </span>
  <a href="admin.php?logout=1" style="font-size:12.5px">خروج</a>
</div>
<hr style="border:0;border-top:1px solid #f0eade;margin:10px 0 16px">

<?php if ($tab === 'dash'): ?>
  <h1>وضعیت کمپین</h1>
  <table>
    <tr><th>سنجه</th><th>مقدار</th></tr>
    <tr><td>نرخ فعال‌سازی</td><td class="mono"><?= $stats['serials'] ? h((string)round($stats['used'] / $stats['serials'] * 100)) . '٪' : '—' ?></td></tr>
    <tr><td>نرخ تکمیل تست (از کاربران)</td><td class="mono"><?= $stats['users'] ? h((string)round($stats['tests'] / $stats['users'] * 100)) . '٪' : '—' ?></td></tr>
    <tr><td>درخواست پکیج باز</td><td class="mono"><?= h((string)(int)db()->query("SELECT COUNT(*) c FROM requests WHERE status='new'")->fetch()['c']) ?></td></tr>
    <tr><td>حالت پیامک</td><td><?= h(otp_mode()) ?> <?= (otp_mode() === 'dev') ? '<span class="badge wait">تست</span>' : '<span class="badge used">واقعی</span>' ?></td></tr>
  </table>
  <?php
  $smsc = sms_settings();
  $provNames = sms_providers();
  $last = (string)setting('sms_last_attempt', '');
  ?>
  <h2 style="font-size:16px;margin:22px 0 8px">وضعیت پیامک</h2>
  <table>
    <tr><td>پنل</td><td><?= h($provNames[$smsc['provider']] ?? $smsc['provider']) ?>
        <?= $smsc['provider'] === 'smsir_pattern' ? ' · قالب <span class="mono">' . h($smsc['template_id']) . '</span> · متغیر <span class="mono">' . h($smsc['param_code']) . '</span>' : '' ?></td></tr>
    <tr><td>حالت</td><td><?= otp_mode() === 'dev' ? '<span class="badge wait">تست</span> (پیامک نمی‌رود)' : '<span class="badge used">واقعی</span>' ?></td></tr>
    <?php $ks = sms_key_state(); ?>
    <tr><td>کلید وب‌سرویس</td><td>
      <?php if ($ks['state'] === 'empty'): ?><b>ست نشده ✖</b> — بدون کلید، هیچ پیامکی برای مخاطب نمی‌رود.
        <a href="admin.php?tab=settings">تنظیمات پیامک را باز کن</a>
      <?php elseif ($ks['state'] === 'suspicious'): ?>
        <span class="badge wait">مشکوک</span> طول کلید <span class="mono"><?= (int)$ks['len'] ?></span> کاراکتر — کلید sms.ir باید کامل کپی شود (۶۴ کاراکتر) <span class="mono"><?= h($ks['masked']) ?></span>
      <?php else: ?><span class="badge used">ثبت شده ✅</span> <span class="mono"><?= h($ks['masked']) ?></span> · طول <span class="mono"><?= (int)$ks['len'] ?></span>
        <a href="admin.php?tab=settings">ویرایش</a>
      <?php endif; ?></td></tr>
    <tr><td>کد پشتیبان روی صفحه</td><td><?= otp_fallback() ? 'روشن' : 'خاموش' ?></td></tr>
    <tr><td>آخرین نتیجهٔ ارسال</td><td class="mono" style="font-size:12px;word-break:break-all"><?= h($last !== '' ? $last : '—') ?></td></tr>
  </table>
  <form method="post" class="row" style="margin-top:10px">
    <input type="hidden" name="op" value="test_sms">
    <div><label for="tp">آزمایش پیامک — شمارهٔ خودت</label><input id="tp" class="mono" type="tel" name="test_phone" placeholder="09xxxxxxxxx" maxlength="11"></div>
    <button class="btn small" type="submit">ارسال آزمایشی</button>
  </form>
  <p class="hint">برای اتصال سایت تست: آدرس <span class="mono">api.php?action=check&amp;s=…</span> با هدر <span class="mono">X-Api-Secret</span>.</p>

<?php elseif ($tab === 'serials'): ?>
  <h1>سریال‌ها <a class="btn ghost small" href="admin.php?csv=serials">CSV</a></h1>
  <form method="post" class="row" style="margin-bottom:14px">
    <input type="hidden" name="op" value="add_batch">
    <div><label>پیشوند</label><input type="text" name="prefix" value="ML-1404-" class="mono"></div>
    <div><label>از</label><input type="text" name="from" value="1" class="mono"></div>
    <div><label>تا</label><input type="text" name="to" value="20" class="mono"></div>
    <button class="btn small" type="submit">افزودن</button>
  </form>
  <table><tr><th>کد</th><th>وضعیت</th><th>موبایل</th><th>فعال‌سازی</th><th>QR</th><th></th></tr>
  <?php foreach (db()->query('SELECT * FROM serials ORDER BY code') as $s): $qrp = __DIR__ . '/data/qr/' . $s['code'] . '.png'; ?>
    <tr><td class="mono"><?= h($s['code']) ?></td>
      <td><span class="badge <?= $s['status'] === 'used' ? 'used' : 'new' ?>"><?= $s['status'] === 'used' ? 'فعال‌شده' : 'آزاد' ?></span></td>
      <td class="mono"><?= h((string)$s['phone']) ?></td><td class="mono" style="font-size:12px"><?= h((string)$s['activated_at']) ?></td>
      <td><?php if (is_file($qrp)): ?><a href="data/qr/<?= h($s['code']) ?>.png" target="_blank">PNG</a><?php else: ?><span class="hint">—</span><?php endif; ?></td>
      <td><?php if ($s['status'] === 'used'): ?>
        <form method="post" style="margin:0"><input type="hidden" name="op" value="reset_serial"><input type="hidden" name="code" value="<?= h($s['code']) ?>">
        <button class="btn ghost small" type="submit">آزادسازی</button></form><?php endif; ?></td></tr>
  <?php endforeach; ?></table>

<?php elseif ($tab === 'users'): ?>
  <h1>کاربران <a class="btn ghost small" href="admin.php?csv=users">CSV</a></h1>
  <table><tr><th>موبایل</th><th>سریال</th><th>منبع</th><th>تست</th><th>ثبت</th><th>توکن</th></tr>
  <?php foreach (db()->query('SELECT * FROM users ORDER BY id DESC') as $u): ?>
    <tr><td class="mono"><?= h($u['phone']) ?></td><td class="mono"><?= h((string)$u['serial']) ?></td><td><?= h((string)$u['src']) ?></td>
    <td><?= ((int)$u['test_completed']) ? '✅' : '—' ?></td><td class="mono" style="font-size:12px"><?= h((string)$u['created_at']) ?></td>
    <td class="mono" style="font-size:11px"><?= h(substr((string)$u['test_token'], 0, 10)) ?>…</td></tr>
  <?php endforeach; ?></table>

<?php elseif ($tab === 'results'): ?>
  <h1>تست‌ها <a class="btn ghost small" href="admin.php?csv=results">CSV</a></h1>
  <table><tr><th>موبایل</th><th>۱</th><th>۲</th><th>۳</th><th>نمره‌ها</th><th>تاریخ</th></tr>
  <?php foreach (db()->query('SELECT * FROM results ORDER BY id DESC') as $r): ?>
    <tr><td class="mono"><?= h($r['phone']) ?></td><td><?= h((string)$r['top1']) ?></td><td><?= h((string)$r['top2']) ?></td><td><?= h((string)$r['top3']) ?></td>
    <td class="mono" style="font-size:11.5px"><?= h((string)$r['scores_json']) ?></td><td class="mono" style="font-size:12px"><?= h((string)$r['created_at']) ?></td></tr>
  <?php endforeach; ?></table>

<?php elseif ($tab === 'requests'): ?>
  <h1>درخواست‌های پکیج <a class="btn ghost small" href="admin.php?csv=requests">CSV</a></h1>
  <table><tr><th>#</th><th>موبایل</th><th>نام</th><th>پکیج</th><th>یادداشت</th><th>وضعیت</th></tr>
  <?php foreach (db()->query('SELECT * FROM requests ORDER BY id DESC') as $r): ?>
    <tr><td class="mono"><?= h((string)$r['id']) ?></td><td class="mono"><?= h($r['phone']) ?></td><td><?= h((string)$r['name']) ?></td>
    <td><?= h((string)$r['package_key']) ?></td><td style="font-size:12.5px"><?= h((string)$r['note']) ?></td>
    <td><form method="post" style="margin:0;display:flex;gap:6px">
      <input type="hidden" name="op" value="req_status"><input type="hidden" name="id" value="<?= h((string)$r['id']) ?>">
      <select name="status" style="padding:6px 8px;border-radius:8px;border:1px solid var(--line);font-family:inherit">
        <?php foreach (['new' => 'جدید', 'contacted' => 'تماس گرفته شد', 'paid' => 'پرداخت شد', 'closed' => 'بسته'] as $k => $v): ?>
          <option value="<?= h($k) ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= h($v) ?></option>
        <?php endforeach; ?>
      </select><button class="btn small" type="submit">ثبت</button></form></td></tr>
  <?php endforeach; ?></table>

<?php elseif ($tab === 'settings'): ?>
  <h1>تنظیمات پیامک</h1>
  <?php if (isset($_GET['saved'])): ?><div class="msg ok">تنظیمات ذخیره شد ✅ (<?= h((string)(int)$_GET['saved']) ?> فیلد)</div><?php endif; ?>
  <?php if (isset($_GET['skipped'])): ?><div class="msg warn">کادر «کلید وب‌سرویس» خالی بود؛ مقدار قبلی دست‌نخورده ماند.</div><?php endif; ?>
  <?php
  $smsc = sms_settings();
  $ks = sms_key_state();
  $keyCheck = (string)setting('sms_key_check', '');
  $last = (string)setting('sms_last_attempt', '');
  ?>
  <div class="msg <?= $ks['state'] === 'empty' ? 'err' : ($ks['state'] === 'suspicious' ? 'warn' : 'ok') ?>">
    وضعیت کلید وب‌سرویس: <b><?= h($ks['state'] === 'empty' ? 'ست نشده ✖' : ($ks['state'] === 'suspicious' ? 'کوتاه/نیمه‌کپی‌شده (' . (int)$ks['len'] . ' کاراکتر)' : 'ثبت شده ✅ · ' . $ks['masked'] . ' · ' . (int)$ks['len'] . ' کاراکتر')) ?></b>
  </div>

  <form method="post">
    <input type="hidden" name="op" value="save_settings">
    <label for="otp_mode">حالت کد تأیید</label>
    <select id="otp_mode" name="otp_mode" style="width:100%;padding:11px 13px;border:1.4px solid var(--line);border-radius:11px;font-family:inherit;font-size:15px">
      <option value="sms" <?= otp_mode() === 'sms' ? 'selected' : '' ?>>واقعی (sms) — کد برای مخاطب پیامک می‌شود</option>
      <option value="dev" <?= otp_mode() === 'dev' ? 'selected' : '' ?>>تست (dev) — پیامک واقعی نمی‌رود؛ کد در data/dev-otp.log</option>
    </select>

    <label for="sms_provider">پنل پیامک من</label>
    <select id="sms_provider" name="sms_provider" style="width:100%;padding:11px 13px;border:1.4px solid var(--line);border-radius:11px;font-family:inherit;font-size:15px">
      <?php foreach (sms_providers() as $pk => $pv): ?>
        <option value="<?= h($pk) ?>" <?= $smsc['provider'] === $pk ? 'selected' : '' ?>><?= h($pv) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="sms_key">کلید وب‌سرویس (x-api-key)</label>
    <input id="sms_key" type="text" name="sms_key" class="mono" dir="ltr" autocomplete="off"
           placeholder="<?= h($ks['state'] === 'empty' ? 'کلید را همین‌جا پیست کن' : 'خالی بگذار تا کلید فعلی (' . h($ks['masked']) . ') حفظ شود') ?>">
    <div class="hint">در پنل sms.ir: «برنامه‌نویسان → لیست کلیدهای API». کلید باید <b>فعال</b> و از نوع <b>واقعی</b> باشد (کلید <span class="mono">Sandbox</span> پیامک واقعی نمی‌فرستد) و اگر <b>محدودیت IP</b> دارد، آی‌پی سرور در فهرست باشد.</div>

    <div class="row" style="gap:10px">
      <div style="flex:1"><label for="sms_template_id">شناسهٔ الگو (templateId)</label>
        <input id="sms_template_id" type="text" name="sms_template_id" class="mono" dir="ltr" value="<?= h($smsc['template_id']) ?>"></div>
      <div style="flex:1"><label for="sms_param_code">نام متغیر کد</label>
        <input id="sms_param_code" type="text" name="sms_param_code" class="mono" dir="ltr" value="<?= h($smsc['param_code']) ?>"></div>
    </div>
    <div class="row" style="gap:10px">
      <div style="flex:1"><label for="sms_sender">شمارهٔ فرستنده / خط (اختیاری برای الگو)</label>
        <input id="sms_sender" type="text" name="sms_sender" class="mono" dir="ltr" value="<?= h($smsc['sender']) ?>"></div>
      <div style="flex:1"><label for="sms_fallback">کد پشتیبان روی صفحه</label>
        <select id="sms_fallback" name="otp_fallback_onscreen" style="width:100%;padding:11px 13px;border:1.4px solid var(--line);border-radius:11px;font-family:inherit;font-size:15px">
          <option value="1" <?= otp_fallback() ? 'selected' : '' ?>>روشن — اگر پیامک نرفت کد روی صفحه دیده شود</option>
          <option value="0" <?= !otp_fallback() ? 'selected' : '' ?>>خاموش — فقط پیامک (مناسب روز ایونت)</option>
        </select></div>
    </div>

    <label for="sms_text_otp">متن پیامک کد تأیید (برای پنل‌های متن‌آزاد)</label>
    <input id="sms_text_otp" type="text" name="sms_text_otp" value="<?= h((string)setting('sms_text_otp', (string)(cfg('sms')['text_otp'] ?? ''))) ?>">

    <button class="btn" type="submit">ذخیرهٔ تنظیمات</button>
  </form>
  <form method="post" style="margin-top:8px">
    <input type="hidden" name="op" value="check_key">
    <button class="btn ghost" type="submit">آزمون کلید — دریافت اعتبار از sms.ir</button>
  </form>

  <hr style="border:0;border-top:1px solid #f0eade;margin:18px 0">
  <h2 style="font-size:15px;margin:0 0 8px">آخرین پاسخ‌ها</h2>
  <table>
    <tr><th style="width:26%">مورد</th><th>مقدار</th></tr>
    <tr><td>آخرین نتیجهٔ ارسال</td><td class="mono" style="font-size:12px;word-break:break-all"><?= h($last !== '' ? $last : '—') ?></td></tr>
    <tr><td>آخرین آزمون کلید</td><td class="mono" style="font-size:12px;word-break:break-all"><?= h($keyCheck !== '' ? $keyCheck : '—') ?></td></tr>
  </table>
  <form method="post" class="row" style="margin-top:10px">
    <input type="hidden" name="op" value="test_sms">
    <div><label for="tp">ارسال آزمایشی — شمارهٔ خودت</label><input id="tp" class="mono" type="tel" name="test_phone" placeholder="09xxxxxxxxx" maxlength="11"></div>
    <button class="btn small" type="submit">ارسال آزمایشی</button>
  </form>
  <p class="hint">ابتدا «آزمون کلید» را بزن: اگر اعتبار پنل نمایش داده شد، کلید سالم است و مشکل از الگو/خط است. اگر خطای کلید داد، اول کلید را درست کن.</p>

<?php else: ?>
  <h1>رویدادها <a class="btn ghost small" href="admin.php?csv=events">CSV</a></h1>
  <table><tr><th>زمان</th><th>رویداد</th><th>موبایل</th><th>جزئیات</th></tr>
  <?php foreach (db()->query('SELECT * FROM events ORDER BY id DESC LIMIT 300') as $e): ?>
    <tr><td class="mono" style="font-size:12px"><?= h(date('Y-m-d H:i', (int)$e['ts'])) ?></td><td><?= h((string)$e['name']) ?></td>
    <td class="mono"><?= h((string)$e['phone']) ?></td><td class="mono" style="font-size:11.5px"><?= h((string)$e['meta']) ?></td></tr>
  <?php endforeach; ?></table>
<?php endif; ?>
</div>
<?php page_foot();
