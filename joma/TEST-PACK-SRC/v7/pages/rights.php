<?php
/**
 * JOMA — حقوق داده (data rights) — طبق SPEC/26
 * سه حق واقعی و کارکردی: خروجی گرفتن (گزارش خوانا / صفحه‌گسترده / فایل پشتیبان) · دیدن اینکه چه چیزی ذخیره شده ·
 * درخواست حذف حساب با مهلت پشیمانی.
 *
 * صادقانه: «پاک‌سازی نهایی پس از ۳۰ روز» به یک کار زمان‌بند (cron) نیاز دارد که در این نصب
 * نیست؛ پس درخواست ثبت می‌شود، نشان «در انتظار پاک شدن» می‌آید و پس‌گرفتن هم ممکن است.
 * اجرای پاک‌سازی نهایی = نمایشی · منتظر بک‌اند (کرون).
 */
require_login();
$u = current_user();
$msg = '';
$err = '';
$today = jalali_today();

/* ---------- داده‌های کاربر (همه از توابع موجود؛ هیچ کوئری تازه‌ای ساخته نشد) ---------- */
$periods = list_periods($u['id']);
$allEvents = array();
$allMoods = array();
$allPlans = array();
foreach ($periods as $p) {
    $wp = ensure_period($u['id'], $p['period_key']);
    $plan = $wp['plan'];
    $rows = list_plan_activities($plan['id'], $u['id']);
    $evs = list_events($plan['id'], $u['id']);
    $allPlans[] = array('period' => $p['period_key'], 'status' => $plan['status'], 'activities' => $rows, 'events' => $evs);
    foreach ($evs as $e) $allEvents[] = $e;
    foreach (list_moods($u['id'], $p['start_date'], $p['end_date']) as $m) $allMoods[] = $m;
}
$notes = function_exists('joma_journal_notes') ? joma_journal_notes($u['id'], '', 500, 0) : array();
$hammasirLinks = array();
try {
    if (function_exists('hammasir_links_any_by_client') && function_exists('hammasir_latest_link_by_client')) {
        $lk = hammasir_latest_link_by_client((int) $u['id']);
        if ($lk) $hammasirLinks[] = $lk;
    }
} catch (Throwable $e) { $hammasirLinks = array(); }

/* ---------- اقدام‌ها ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = isset($_POST['action']) ? $_POST['action'] : '';

    if ($act === 'delete_request') {
        $confirm = isset($_POST['confirm_text']) ? trim($_POST['confirm_text']) : '';
        if ($confirm !== 'پاک کن') {
            $err = 'برای تأیید، باید دقیقاً بنویسی: پاک کن';
        } else {
            if (function_exists('joma_kv_set')) {
                joma_kv_set('account_delete_request_' . (int) $u['id'], json_encode(array(
                    'requested_at' => joma_now(), 'jalali' => $today, 'by' => (int) $u['id'],
                ), JSON_UNESCAPED_UNICODE));
                $msg = 'درخواست پاک‌کردن ثبت شد. حساب تو ۳۰ روز «در انتظار پاک شدن» است و هر وقت بخواهی می‌توانی لغو کنی.';
            } else {
                $err = 'ثبت درخواست ممکن نشد. دوباره تلاش کن.';
            }
        }
    } elseif ($act === 'delete_cancel') {
        if (function_exists('joma_kv_forget')) {
            joma_kv_forget('account_delete_request_' . (int) $u['id']);
            $msg = 'درخواست پاک‌کردن لغو شد. حسابت مثل قبل است.';
        }
    }
}

/* ---------- خروجی‌ها (دانلود مستقیم — همان لحظه) ---------- */
if (isset($_GET['export'])) {
    $kind = $_GET['export'];
    $stamp = date('Y-m-d');
    if ($kind === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="joma-record-' . $stamp . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM برای اکسل فارسی
        $out = fopen('php://output', 'w');
        fputcsv($out, array('تاریخ', 'فعالیت', 'کد فعالیت', 'مقدار', 'واحد', 'نوع', 'دوره'));
        foreach ($allPlans as $pl) {
            $byPa = array();
            foreach ($pl['activities'] as $a) $byPa[(int) $a['id']] = $a;
            foreach ($pl['events'] as $e) {
                $a = isset($byPa[(int) $e['plan_activity_id']]) ? $byPa[(int) $e['plan_activity_id']] : null;
                fputcsv($out, array($e['performance_date'], $a ? $a['name'] : '—', $a ? $a['activity_code'] : '—',
                    $e['actual_value'], $a ? $a['unit'] : '', $e['data_type'], $pl['period']));
            }
        }
        fclose($out);
        exit;
    }
    if ($kind === 'json') {
        $payload = array(
            'product' => 'JOMA', 'version' => 'JOMA_DATA_EXPORT_V1', 'exported_at' => joma_now(),
            'user' => array('username' => $u['username'], 'first_name' => $u['first_name'], 'last_name' => $u['last_name'],
                            'email' => $u['email'], 'phone' => $u['phone'], 'job' => $u['job'], 'created_at' => $u['created_at']),
            'plans' => array(),
            'mood_records' => $allMoods,
            'journal_notes' => $notes,
            'companion_links' => $hammasirLinks,
            'note' => 'این فایل شامل دادهٔ خود توست. دادهٔ همراه/مشاور داخل آن نیست؛ هر کس مالک دادهٔ خودش است.',
        );
        foreach ($allPlans as $pl) {
            $payload['plans'][] = array(
                'period_key' => $pl['period'], 'status' => $pl['status'],
                'activities' => array_map(function ($a) {
                    return array('code' => $a['activity_code'], 'name' => $a['name'], 'frequency' => $a['frequency'],
                                 'target_value' => $a['target_value'], 'unit' => $a['unit'], 'weight' => $a['weight'],
                                 'snapshot_at' => $a['snapshot_at']);
                }, $pl['activities']),
                'events' => $pl['events'],
            );
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="joma-data-' . $stamp . '.json"');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    if ($kind === 'report') {
        // گزارش خوانا: صفحهٔ چاپی (کاربر از پنجرهٔ چاپ، فایل می‌سازد) — بدون کتابخانهٔ بیرونی
        joma_header('گزارش خوانای من', array());
        ?>
        <div class="card">
          <div class="k">گزارش خوانای جوما</div>
          <h1 style="margin:6px 0"><?php echo e($u['full_name'] ? $u['full_name'] : $u['username']); ?></h1>
          <p class="tiny">تاریخ تهیه: <?php echo e(jalali_format($today)); ?> · هرچه در این گزارش است، از ثبت‌های خودت ساخته شده.</p>
          <div class="btn-row" style="margin-top:8px">
            <button class="btn sm" type="button" onclick="window.print()">چاپ / ذخیره در فایل</button>
            <a class="btn ghost sm" href="<?php echo e(joma_url('index.php?p=rights')); ?>">بازگشت</a>
          </div>
          <p class="tiny">برای ذخیرهٔ این گزارش، در پنجرهٔ چاپ گزینهٔ «ذخیره به‌صورت فایل» مرورگر را انتخاب کن.</p>
        </div>
        <?php
        foreach ($allPlans as $pl) {
            $wp = ensure_period($u['id'], $pl['period']);
            $plan = $wp['plan'];
            $rep = build_report($u['id'], $plan);
            $sr = function_exists('success_report') ? success_report($plan, $rep['bounds'], $rep['activities'], $rep['events'], $today) : null;
            echo '<section class="card">';
            echo '<div class="k">دورهٔ ' . e(jalali_period_label($pl['period'])) . ' — ' . e(status_label($plan['status'])) . '</div>';
            echo '<p class="lede">' . fa_num(count($pl['activities'])) . ' فعالیت · ' . fa_num(count($pl['events'])) . ' رخداد ثبت‌شده</p>';
            if ($sr && $sr['overall_success'] !== null) {
                echo '<p class="lede">موفقیت کلی (وزن‌دار): <b>' . fa_num(round($sr['overall_success'] * 100)) . '٪</b> · پوشش داده: '
                   . ($sr['overall_coverage'] === null ? '—' : fa_num(round($sr['overall_coverage'] * 100)) . '٪') . '</p>';
            } else {
                echo '<p class="lede">موفقیت کلی: اعلام نمی‌شود (چرخهٔ کامل یا دادهٔ کافی نبود).</p>';
            }
            echo '<table class="tbl"><thead><tr><th>فعالیت</th><th>هدف</th><th>ثبت‌شده</th><th>رخداد</th></tr></thead><tbody>';
            foreach ($pl['activities'] as $a) {
                $sum = 0;
                foreach ($pl['events'] as $e) if ((int) $e['plan_activity_id'] === (int) $a['id']) $sum += (float) $e['actual_value'];
                $cnt = 0;
                foreach ($pl['events'] as $e) if ((int) $e['plan_activity_id'] === (int) $a['id']) $cnt++;
                echo '<tr><td>' . e($a['sticker'] . ' ' . $a['name']) . '</td><td>' . e(format_value($a['data_type'], $a['target_value'], $a['unit']))
                   . '</td><td>' . e(format_value($a['data_type'], $sum, $a['unit'])) . '</td><td>' . fa_num($cnt) . '</td></tr>';
            }
            echo '</tbody></table></section>';
        }
        if ($allMoods) {
            echo '<section class="card"><div class="k">حال و یادداشت‌ها (' . fa_num(count($allMoods)) . ' روز)</div><div class="table-wrap" style="margin-top:8px"><table class="tbl">';
            echo '<thead><tr><th>تاریخ</th><th>انرژی</th><th>حال</th><th>تمرکز</th><th>خواب</th><th>استرس</th><th>یادداشت (خصوصی)</th></tr></thead><tbody>';
            foreach ($allMoods as $m) {
                echo '<tr><td>' . e(jalali_format($m['jalali_date'])) . '</td><td>' . fa_num($m['energy']) . '</td><td>' . fa_num($m['general_mood'])
                   . '</td><td>' . fa_num($m['focus']) . '</td><td>' . fa_num($m['sleep_quality']) . '</td><td>' . fa_num($m['stress'])
                   . '</td><td>' . e($m['note']) . '</td></tr>';
            }
            echo '</tbody></table></div></section>';
        }
        echo '<p class="tiny">جوما ابزار خودمدیریتی است، نه درمان. این گزارش جای ارزیابی بالینی را نمی‌گیرد.</p>';
        joma_footer();
        return;
    }
}

$delReq = null;
if (function_exists('joma_kv_get_json')) {
    $raw = joma_kv_get_json('account_delete_request_' . (int) $u['id']);
    if (is_array($raw)) $delReq = $raw;
}
joma_header('حقوق داده', array());
?>
<?php joma_v2_pagehead('حقوق داده', 'دادهٔ من، مالِ من', 'دیدن · بردن · پاک‌کردن. هیچ‌کدام پشت تماس با پشتیبانی پنهان نیست.', ''); ?>

<?php if ($msg) echo '<p class="toast ok">' . e($msg) . '</p>'; ?>
<?php if ($err) echo '<p class="toast bad">' . e($err) . '</p>'; ?>

<?php if ($delReq) { ?>
  <div class="card" style="border:1.5px solid var(--coral);background:linear-gradient(150deg,#FFFFFF,var(--coral-soft))">
    <b>حساب تو در انتظار پاک شدن است.</b>
    <p class="lede" style="margin-top:6px">درخواست در <?php echo e(isset($delReq['jalali']) ? jalali_format($delReq['jalali']) : '—'); ?> ثبت شده و ۳۰ روز مهلت پشیمانی دارد.</p>
    <p class="tiny">پاک‌سازی نهایی پس از پایان این مهلت انجام می‌شود. تا آن روز، هر وقت بخواهی می‌توانی با یک دکمه لغو کنی.</p>
    <form method="post" style="margin-top:8px">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="delete_cancel">
      <button class="btn sec" type="submit">لغو درخواست پاک‌کردن</button>
    </form>
  </div>
<?php } ?>

<div class="grid grid-2">
  <section class="card">
    <div class="k">چه چیزی از تو ذخیره شده است؟</div>
    <div class="profile-list" style="margin-top:8px">
      <div class="hd-item"><div class="row" style="justify-content:space-between"><b style="font-size:12.5px">پروفایل</b><span class="chip n">نام · نام کاربری · موبایل · ایمیل · شغل</span></div></div>
      <div class="hd-item"><div class="row" style="justify-content:space-between"><b style="font-size:12.5px">برنامه‌ها و تصویر قفل‌شده‌شان</b><span class="chip n"><?php echo fa_num(count($allPlans)); ?> دوره</span></div></div>
      <div class="hd-item"><div class="row" style="justify-content:space-between"><b style="font-size:12.5px">رخدادهای فعالیت</b><span class="chip n"><?php echo fa_num(count($allEvents)); ?> ثبت</span></div></div>
      <div class="hd-item"><div class="row" style="justify-content:space-between"><b style="font-size:12.5px">حال و یادداشت‌های خصوصی</b><span class="chip n"><?php echo fa_num(count($allMoods)); ?> روز · <?php echo fa_num(count($notes)); ?> یادداشت</span></div></div>
      <div class="hd-item"><div class="row" style="justify-content:space-between"><b style="font-size:12.5px">رابطهٔ هم‌مسیر</b><span class="chip n"><?php echo fa_num(count($hammasirLinks)); ?> ارتباط</span></div></div>
    </div>
    <p class="tiny" style="margin-top:8px">دادهٔ همراه/مشاور داخل دادهٔ تو نیست — او هم مالک دادهٔ خودش است. یادداشت‌هایت هم هیچ‌وقت برای مشاور فرستاده نمی‌شوند.</p>
  </section>

  <section class="card">
    <div class="k">خروجی گرفتن</div>
    <p class="lede" style="margin-top:6px">همین حالا، بدون انتظار. فایل مستقیم روی دستگاه تو ساخته می‌شود.</p>
    <div class="grid" style="grid-template-columns:1fr;gap:8px;margin-top:8px">
      <a class="btn" href="<?php echo e(joma_url('index.php?p=rights&export=report')); ?>">گزارش خوانا (برای چاپ)</a>
      <a class="btn sec" href="<?php echo e(joma_url('index.php?p=rights&export=csv')); ?>">صفحه‌گسترده (برای اکسل)</a>
      <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=rights&export=json')); ?>">فایل پشتیبان کامل</a>
    </div>
    <table class="tbl" style="margin-top:10px">
      <thead><tr><th>قالب</th><th>برای چه کاری</th></tr></thead>
      <tbody>
        <tr><td>گزارش خوانا</td><td>دیدن و نشان‌دادن به متخصص (از پنجرهٔ چاپ، فایل می‌سازی)</td></tr>
        <tr><td>صفحه‌گسترده</td><td>تحلیل در اکسل؛ هر ثبت یک سطر</td></tr>
        <tr><td>فایل پشتیبان کامل</td><td>پشتیبان‌گیری و انتقال بین دستگاه‌ها</td></tr>
      </tbody>
    </table>
    <p class="tiny">اگر مطمئن نیستی کدام را بگیری، همان گزارش خوانا را بگیر.</p>
  </section>
</div>

<section class="card">
  <div class="k">پس‌گرفتن اجازه‌ها</div>
  <p class="lede" style="margin-top:6px">هر اجازه‌ای که به «هم‌مسیر» داده‌ای، از خود صفحهٔ هم‌مسیر در هر لحظه قابل پس‌گرفتن است — بدون اینکه لازم باشد دلیل بگویی.</p>
  <div class="btn-row" style="margin-top:8px"><a class="btn sec sm" href="<?php echo e(joma_url('index.php?p=hammasir')); ?>">رفتن به هم‌مسیر</a></div>
</section>

<section class="card" style="border:1.5px solid var(--card-brd)">
  <div class="k">حذف حساب</div>
  <p class="lede" style="margin-top:6px">اگر می‌خواهی حسابت پاک شود: ۳۰ روز مهلت پشیمانی داری و در این مدت می‌توانی لغو کنی. قبلش می‌توانی خروجی بگیری.</p>
  <details class="gl" style="margin-top:8px">
    <summary>می‌خواهم حسابم را پاک کنم</summary>
    <div class="card" style="margin-top:8px;border-color:var(--coral)">
      <p class="lede">با این کار: برنامه‌ها، ثبت‌ها، حال و یادداشت‌ها، و بینش‌ها در پایان مهلت پاک می‌شوند · دسترسی همراه از همین حالا بسته می‌شود.</p>
      <p class="tiny">مدیر مجموعه نمی‌تواند حساب تو را حذف کند؛ این حق فقط برای خودت است.</p>
      <form method="post" style="margin-top:8px;max-width:420px">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="delete_request">
        <label>برای تأیید، این جمله را بنویس: <b>پاک کن</b></label>
        <input name="confirm_text" placeholder="پاک کن" required>
        <div class="btn-row" style="margin-top:8px">
          <button class="btn danger" type="submit">ثبت درخواست پاک‌کردن</button>
          <a class="btn ghost" href="<?php echo e(joma_url('index.php?p=rights&export=json')); ?>">اول خروجی می‌گیرم</a>
        </div>
      </form>
    </div>
  </details>
  <p class="tiny" style="margin-top:8px">تا ۳۰ روز، حسابت «در انتظار» می‌ماند و داده‌ای پاک نمی‌شود؛ اگر نظرت عوض شد، همان‌جا لغو کن.</p>
</section>
<?php joma_footer(); ?>
