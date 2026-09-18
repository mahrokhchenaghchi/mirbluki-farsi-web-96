<?php
/**
 * JOMA — ساخت دادهٔ نمونه برای کپی تست  (فقط همین یک‌بار اجرا کن)
 * =========================================================================
 * چه می‌سازد:
 *   · سه حساب نمونه: مدیر · مراجع · مشاور (هم‌مسیر)
 *   · برای مراجع: برنامهٔ همین ماه با ۵ فعالیت (شامل زوج‌درمانی و طرحواره)
 *     + حدود دو هفته ثبت عملکرد و ۱۰ روز ثبت حال → جوجه متولد می‌شود و
 *       گزارش‌ها و نمودارها پر می‌شوند.
 *   · یک ارتباط «هم‌مسیر» بین مراجع و مشاور با مجوزهای دید (بدون یادداشت خصوصی).
 *
 * چه نمی‌کند: به هیچ دیتابیسی دست نمی‌زند (فقط حالت فایلی)، هیچ دادهٔ سایت
 * اصلی را نمی‌بیند، و اگر کپی تست نباشد اجرا نمی‌شود.
 *
 * بعد از اجرا: همین فایل را از هاست پاک کن.
 * =========================================================================
 */

require dirname(__FILE__) . '/includes/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');

function seed_out($s) { echo '<p style="margin:6px 0">' . $s . '</p>'; }
/** توابع «هم‌مسیر» در موفقیت رشتهٔ ok برمی‌گردانند. */
function h_ok($r) { return ($r === 'ok' || $r === 'duplicate'); }
function h_txt($r) { return h_ok($r) ? 'انجام شد' : e((string) $r); }
function seed_head($s) { echo '<h2 style="margin:18px 0 6px;font-family:sans-serif">' . $s . '</h2>'; }

if (store_mode() !== 'file') {
    echo '<meta charset="utf-8"><div style="font:16px sans-serif;padding:24px">';
    echo '<b>این فایل فقط برای کپی تست است.</b><br>چون این نصب با دیتابیس کار می‌کند، اجرا نشد.';
    echo '</div>';
    exit;
}

$__hfn = dirname(__FILE__) . '/functions/hammasir.php';
if (is_file($__hfn)) require_once $__hfn;
$__sfn = dirname(__FILE__) . '/functions/success.php';
if (is_file($__sfn)) require_once $__sfn;
$__jfn = dirname(__FILE__) . '/functions/jooje.php';
if (is_file($__jfn)) require_once $__jfn;

$PASS = 'JomaTest1405!';
$notes = array();

/** تاریخ جلالی چند روز قبل/بعد. */
function seed_day($days) {
    $ts = time() + ((int) $days * 86400);
    $j = gregorian_to_jalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
}

/** ساخت (یا پیدا کردن) حساب نمونه. */
function seed_user($username, $first, $last, $job, $pass, $role) {
    $ex = user_by_username($username);
    if ($ex) return array($ex, true);
    $u = create_user(array(
        'first_name' => $first, 'last_name' => $last, 'username' => $username,
        'email' => $username . '@example.com', 'phone' => '091200000' . rand(10, 99),
        'job' => $job, 'password' => $pass, 'confirm' => $pass, 'accept' => 1,
    ));
    if ($role === 'admin') {
        // فقط در حالت فایلی: ارتقا به مدیر (کپی تست)
        $data = store_load();
        foreach ($data['users'] as $i => $row) {
            if ((int) $row['id'] === (int) $u['id']) $data['users'][$i]['role_key'] = 'admin';
        }
        store_save($data);
        $u = get_user($u['id']);
    }
    return array($u, false);
}

echo '<meta charset="utf-8"><div style="font:15px/1.9 sans-serif;max-width:820px;margin:24px auto;padding:0 16px">';
echo '<h1 style="font-size:22px">ساخت دادهٔ نمونهٔ جوما — کپی تست</h1>';

/* ---------------------------------------------------------------- */
seed_head('۱) حساب‌ها');
$created = array();
list($modir, $ex1) = seed_user('modir', 'مدیر', 'تست', 'مدیر', $PASS, 'admin');
list($maryam, $ex2) = seed_user('maryam', 'مریم', 'نمونه', 'کارمند', $PASS, 'member');
list($hamrah, $ex3) = seed_user('moshaver', 'همراه', 'نمونه', 'روان‌شناس', $PASS, 'member');
foreach (array(array('modir', $modir, $ex1), array('maryam', $maryam, $ex2), array('moshaver', $hamrah, $ex3)) as $row) {
    seed_out('· <b dir="ltr">' . $row[0] . '</b> — ' . ($row[2] ? 'از قبل بود' : 'ساخته شد') . ' (شناسه ' . (int) $row[1]['id'] . ')');
}

/* ---------------------------------------------------------------- */
seed_head('۲) برنامهٔ ماه برای مراجع');
$key = jalali_period_key(jalali_today());
$wp = ensure_period((int) $maryam['id'], $key);
$plan = $wp['plan'];
$lib = array();
foreach (list_user_activities((int) $maryam['id']) as $a) $lib[$a['code']] = $a;

$wanted = array(
    // کد    هدف                                وزن   توضیح
    'ACT002' => array('target' => 6,  'weight' => 4, 'label' => 'نوشیدن آب'),
    'ACT001' => array('target' => 30, 'weight' => 5, 'label' => 'ورزش'),
    'ACT003' => array('target' => 20, 'weight' => 4, 'label' => 'پیاده‌روی'),
    'ACT050' => array('target' => 1,  'weight' => 5, 'label' => 'بیان نیازها بدون سرزنش (زوج‌درمانی)'),
    'ACT081' => array('target' => 15, 'weight' => 5, 'label' => 'دفتر طرحواره امروز (طرحواره‌درمانی)'),
);
$added = 0;
foreach ($wanted as $code => $cfg) {
    if (!isset($lib[$code])) { $notes[] = 'کد ' . $code . ' در کتابخانه نبود و اضافه نشد.'; continue; }
    $res = add_plan_activity((int) $maryam['id'], $plan, $lib[$code], array(
        'frequency' => $lib[$code]['frequency'],
        'target_value' => $cfg['target'],
        'weight' => $cfg['weight'],
    ));
    if ($res === '') $added++;
    else $notes[] = 'افزودن ' . $cfg['label'] . ' → ' . $res;
}
seed_out('· ' . $added . ' فعالیت به برنامهٔ ' . e(jalali_period_label($key)) . ' اضافه شد.');
$wp = ensure_period((int) $maryam['id'], $key);
$plan = $wp['plan'];
$t1 = transition_plan((int) $maryam['id'], $plan, 'PLANNING');
$wp = ensure_period((int) $maryam['id'], $key);
$plan = $wp['plan'];
$t2 = transition_plan((int) $maryam['id'], $plan, 'RUNNING');
$wp = ensure_period((int) $maryam['id'], $key);
$plan = $wp['plan'];
seed_out('· وضعیت برنامه: <b>' . e($plan['status']) . '</b>' . (($t1 || $t2) ? ' (' . e($t1 . ' ' . $t2) . ')' : ''));

/* ---------------------------------------------------------------- */
seed_head('۳) ثبت‌های نمونه (دو هفته)');
$bounds = jalali_period_bounds($key);
$today = jalali_today();
$pas = array();
foreach (list_plan_activities($plan['id'], (int) $maryam['id']) as $p) $pas[$p['activity_code']] = $p;
$days = array();
for ($i = 13; $i >= 0; $i--) {
    $d = seed_day(-$i);
    if (strcmp($d, $bounds['start']) < 0) continue;   // قبل از شروع دوره را رد کن
    if (strcmp($d, $today) > 0) continue;             // آینده ممنوع (قانون B3)
    $days[] = $d;
}
$okCount = 0; $skipCount = 0;
$values = array(
    'ACT002' => array(5, 6, 7, 4, 8, 6, 6, 5, 7, 6, 8, 6, 5, 6),
    'ACT001' => array(30, 0, 25, 40, 20, 35, 0, 30, 45, 25, 30, 20, 35, 30),
    'ACT003' => array(20, 25, 0, 15, 30, 20, 25, 20, 0, 30, 20, 25, 15, 20),
    'ACT050' => array(1, 0, 1, 1, 0, 1, 1, 0, 1, 1, 0, 1, 1, 1),
    'ACT081' => array(15, 10, 0, 20, 15, 15, 0, 15, 20, 10, 15, 15, 0, 15),
);
foreach ($days as $i => $d) {
    foreach ($values as $code => $series) {
        if (!isset($pas[$code])) continue;
        $v = isset($series[$i]) ? $series[$i] : 0;
        if ($v <= 0) { $skipCount++; continue; }   // روز بی‌ثبت = بدون رخداد (نه صفر)
        $err = register_performance((int) $maryam['id'], $plan, $pas[$code], $d, $v);
        if ($err === '') $okCount++; else { $skipCount++; $notes[] = $d . ' / ' . $code . ': ' . $err; }
    }
    // ثبت حال (۱۰ روز آخر)
    if ($i >= count($days) - 10) {
        save_mood((int) $maryam['id'], $d, array(
            'energy' => 3 + ($i % 3), 'general' => 3 + ($i % 2), 'focus' => 3 + (($i + 1) % 3),
            'sleep' => 3 + ($i % 3), 'stress' => 1 + ($i % 4),
        ), ($i % 4 === 0) ? 'یادداشت خصوصی نمونه — این متن به مشاور نمی‌رود.' : '');
    }
}
seed_out('· ' . $okCount . ' ثبت عملکرد ساخته شد (' . $skipCount . ' روز/مورد بی‌ثبت ماند — عمدی، برای تست «بی‌داده ≠ صفر»)');
seed_out('· ۱۰ روز ثبت حال ساخته شد (دو یادداشت خصوصی هم دارد)');

/* ---------------------------------------------------------------- */
seed_head('۴) هم‌مسیر (مراجع ↔ مشاور)');
if (function_exists('hammasir_provider_register')) {
    $r1 = hammasir_provider_register((int) $hamrah['id'], 'همراه نمونه');
    $r2 = hammasir_provider_set_status((int) $hamrah['id'], 'ACTIVE');
    seed_out('· ثبت مشاور: ' . h_txt($r1) . ' · فعال‌سازی: ' . h_txt($r2));

    $link = hammasir_link_open_by_client((int) $maryam['id']);
    if (!$link) {
        $r3 = hammasir_link_request((int) $maryam['id'], (int) $hamrah['id'], array(
            'VIEW_PROGRESS' => 1, 'VIEW_ACTIVITY_DETAILS' => 1, 'VIEW_MOOD' => 1,
        ));
        if (h_ok($r3)) {
            $link = hammasir_link_open_by_client((int) $maryam['id']);
            seed_out('· درخواست هم‌مسیر: ارسال شد');
        } else {
            seed_out('· درخواست هم‌مسیر انجام نشد: ' . e($r3));
        }
    }
    if ($link) {
        if ($link['status'] === 'PENDING') {
            $r4 = hammasir_link_respond((int) $hamrah['id'], (int) $link['id'], 'ACTIVE');
            $link = hammasir_link_get((int) $link['id']);
            seed_out('· پذیرش مشاور: ' . h_txt($r4) . ' → وضعیت: ' . e($link['status']));
        } else {
            seed_out('· وضعیت لینک: ' . e($link['status']));
        }
        if ($link['status'] === 'ACTIVE') {
            $r5 = hammasir_messaging_set((int) $maryam['id'], (int) $link['id'], true);
            if (h_ok($r5)) {
                $m1 = hammasir_message_send((int) $maryam['id'], (int) $link['id'], 'سلام، این هفته روی خواب و آب بهتر کار کردم.');
                $m2 = hammasir_message_send((int) $hamrah['id'], (int) $link['id'], 'خوب است. ثبت‌هایت را دیدم؛ روند آب صعودی است.');
                seed_out('· پیام‌های نمونه: ' . ((h_ok($m1) && h_ok($m2)) ? 'ارسال شد' : e($m1 . ' / ' . $m2)));
            } else {
                seed_out('· فعال‌سازی پیام: ' . e($r5));
            }
        }
    }
    // حالت پیش‌فرض مشاور = میز کار
    hammasir_user_flag_set((int) $hamrah['id'], 'mode', 2);
} else {
    seed_out('· ماژول هم‌مسیر در این کپی فعال نیست.');
}

/* ---------------------------------------------------------------- */
$jstate = function_exists('jooje_state') ? jooje_state((int) $maryam['id']) : null;
seed_head('۵) وضعیت جوجه برای حساب مراجع');
if ($jstate && !empty($jstate['enabled'])) {
    seed_out('· مرحله: <b>' . e($jstate['stage']) . '</b> · دونه‌ها: <b>' . fa_num($jstate['units']) . '</b> · حال: ' . e($jstate['state']));
} else {
    $notes[] = 'موتور جوجه در این کپی پیدا نشد.';
}

/* ---------------------------------------------------------------- */
seed_head('حساب‌های نمونه (رمز همه یکسان است)');
echo '<table style="border-collapse:collapse;font:14px sans-serif" border="1" cellpadding="6">';
echo '<tr style="background:#f3f3f3"><th>نقش</th><th>نام کاربری</th><th>رمز</th></tr>';
echo '<tr><td>مدیر (پنل بازیابی رمز)</td><td dir="ltr">modir</td><td dir="ltr">' . $PASS . '</td></tr>';
echo '<tr><td>مراجع (جوجه/برنامه/گزارش)</td><td dir="ltr">maryam</td><td dir="ltr">' . $PASS . '</td></tr>';
echo '<tr><td>مشاور (میز کار هم‌مسیر)</td><td dir="ltr">moshaver</td><td dir="ltr">' . $PASS . '</td></tr>';
echo '</table>';

if ($notes) {
    seed_head('یادداشت‌ها');
    foreach (array_slice($notes, 0, 15) as $n) seed_out('· ' . e($n));
}

echo '<div style="margin:20px 0;padding:12px;background:#fff8e1;border:1px solid #f0d58c;border-radius:8px">';
echo '<b>همین حالا این فایل را پاک کن:</b> در File Manager فایل <code>_SEED-DEMO.php</code> را Delete کن. ';
echo '(اجرای دوبارهٔ آن داده‌های تکراری نمی‌سازد، ولی نگه‌داشتنش لازم نیست.)';
echo '</div>';
echo '<p><a href="index.php">ورود به اپ تست</a> · <a href="index.php?p=login">صفحهٔ ورود</a> · <a href="../FRONTEND-PREVIEW/index.html">پیش‌نمایش طرح جدید (فرانت)</a></p>';
echo '</div>';
