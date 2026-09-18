<?php
/**
 * JOMA — B6: آب، پیش‌نویس و قطعی‌سازی
 * =========================================================================
 * قواعد ثابت (سند فرانت ۱۲٫۸ و WTR-10):
 *   · پیش‌نویس آب، پاداش نمی‌سازد و سنجهٔ جوجه را پر نمی‌کند.
 *   · فقط «ثبت نهایی» حساب می‌شود.
 *   · روزهای گذشته خودکار «قطعی» حساب می‌شوند (چون cron نداریم و ثبت گذشته
 *     قابل تغییر نیست؛ این همان تصمیم قبلی مالک است: ثبت موجود = قطعی).
 *   · اگر وضعیتی ذخیره نشده باشد (سازگاری با داده‌های قبلی) → قطعی.
 *
 * انبار وضعیت: همان کلید/مقدار موجود (joma_settings در MySQL یا store.json در حالت فایلی).
 * اگر ماژول انبار در دسترس نباشد، این فایل «همه‌چیز قطعی» فرض می‌کند — یعنی رفتار قبلی،
 * بدون هیچ تغییری و بدون خطا (fail-open).
 * =========================================================================
 */

function joma_water_store_ready() {
    return function_exists('joma_kv_get') && function_exists('joma_kv_set');
}

function joma_water_status_key($user_id, $date) {
    return 'water_' . (int) $user_id . '_' . preg_replace('/[^0-9\-]/', '', (string) $date);
}

/**
 * وضعیت آب یک روز.
 * خروجی: status = none | draft | final   ·   effective_final = آیا در محاسبه می‌آید؟
 */
function joma_water_state($user_id, $date) {
    $out = array('status' => 'none', 'value' => null, 'updated_at' => null, 'effective_final' => true);
    if (!joma_water_store_ready()) {
        // بدون انبار: هیچ وضعیتی نگه‌داری نمی‌شود → همه‌چیز قطعی (رفتار قبلی).
        $out['status'] = 'final';
        $out['effective_final'] = true;
        return $out;
    }
    $rec = joma_kv_get_json(joma_water_status_key($user_id, $date));
    if (!is_array($rec) || empty($rec['status'])) {
        // ثبت قدیمی بدون وضعیت: «قطعی» حساب می‌شود (سازگاری با دادهٔ قبلی).
        $out['status'] = 'none';
        $out['effective_final'] = true;
        return $out;
    }
    $out['status'] = ($rec['status'] === 'draft') ? 'draft' : 'final';
    $out['value'] = isset($rec['value']) ? $rec['value'] : null;
    $out['updated_at'] = isset($rec['updated_at']) ? $rec['updated_at'] : null;
    $today = function_exists('jalali_today') ? jalali_today() : '';
    // پیش‌نویس روزهای گذشته عملاً قطعی است (گذشته قابل ویرایش نیست و cron نداریم).
    if ($out['status'] === 'draft' && $today !== '' && strcmp($date, $today) < 0) {
        $out['effective_final'] = true;
    } else {
        $out['effective_final'] = ($out['status'] === 'final');
    }
    return $out;
}

/** آیا آب این روز در محاسبهٔ سنجه‌ها می‌آید؟ (پیش‌نویس امروز = نه) */
function joma_water_is_final($user_id, $date) {
    $st = joma_water_state($user_id, $date);
    return !empty($st['effective_final']);
}

function joma_water_save_state($user_id, $date, $status, $value = null) {
    if (!joma_water_store_ready()) return false;
    $rec = array(
        'status' => ($status === 'draft') ? 'draft' : 'final',
        'value' => $value,
        'updated_at' => function_exists('joma_now') ? joma_now() : date('Y-m-d H:i:s'),
    );
    joma_kv_set_json(joma_water_status_key($user_id, $date), $rec);
    return true;
}

/** ثبت پیش‌نویس (بعد از ذخیرهٔ مقدار آب). */
function joma_water_mark_draft($user_id, $date, $value) {
    return joma_water_save_state($user_id, $date, 'draft', $value);
}

/**
 * قطعی‌کردن ثبت همان روز.
 * $has_record باید از خود رخدادها بیاید (نه از وضعیت ذخیره‌شده) تا روی روز خالی
 * هیچ وضعیتی نوشته نشود.
 */
function joma_water_finalize($user_id, $date, $has_record = true) {
    if (!$has_record) {
        return array('ok' => false, 'error' => 'برای این روز آبی ثبت نشده است.');
    }
    $st = joma_water_state($user_id, $date);
    joma_water_save_state($user_id, $date, 'final', $st['value']);
    return array('ok' => true, 'error' => '');
}

/* ------------------------------------------------------------------ */
/* ویرایش مقدار پیش‌نویس (فقط آب · فقط امروز · فقط تا وقتی قطعی نشده)   */
/* ------------------------------------------------------------------ */

/**
 * به‌روزرسانی مقدار ثبت آب همان روز وقتی «پیش‌نویس» است.
 * خروجی: '' یا پیام خطا (هم‌شکل با بقیهٔ توابع اعتبارسنجی).
 */
function joma_water_update_draft($user_id, $plan, $pa, $date, $value) {
    if (!isset($pa['activity_code']) || $pa['activity_code'] !== 'ACT002') {
        return 'این ویرایش فقط برای فعالیت آب است.';
    }
    $today = jalali_today();
    if (strcmp($date, $today) !== 0) {
        return 'فقط پیش‌نویس امروز را می‌توان ویرایش کرد.';
    }
    $st = joma_water_state($user_id, $date);
    if ($st['status'] === 'final') return 'این ثبت قطعی شده است و تغییر نمی‌کند.';
    if ($st['status'] === 'none') return 'این ثبت قطعی حساب می‌شود (بدون پیش‌نویس ذخیره شده است).';
    if (!is_numeric($value)) return 'مقدار عملکرد معتبر نیست.';
    $value = (float) $value;
    $err = validate_performance_value($pa['data_type'], $value);
    if ($err) return $err;

    $existing = list_events($plan['id'], $user_id);
    $target = null;
    foreach ($existing as $e) {
        if ((int) $e['plan_activity_id'] === (int) $pa['id'] && $e['performance_date'] === $date) $target = $e;
    }
    if (!$target) return 'رکورد این روز پیدا نشد.';

    if (store_mode() === 'mysql') {
        joma_exec('UPDATE joma_performance_events SET actual_value = ? WHERE id = ? AND user_id = ?', 'dii', array($value, (int) $target['id'], (int) $user_id));
    } else {
        $data = store_load();
        foreach ($data['events'] as $i => $row) {
            if ((int) $row['id'] === (int) $target['id'] && (int) $row['user_id'] === (int) $user_id) {
                $data['events'][$i]['actual_value'] = $value;
            }
        }
        store_save($data);
    }
    joma_water_mark_draft($user_id, $date, $value);
    return '';
}

/**
 * خلاصهٔ یک روز برای کارت خانه: مقدار قطعی + مقدار پیش‌نویس.
 * (خواندن در چند جا، نوشتن فقط در «کارهای امروز» — قاعدهٔ سند.)
 */
function joma_water_day_summary($user_id, $date, $events, $pa) {
    $today = jalali_today();
    $value = 0.0;
    $has = false;
    if ($pa) {
        foreach ($events as $e) {
            if ((int) $e['plan_activity_id'] !== (int) $pa['id']) continue;
            if ($e['performance_date'] !== $date) continue;
            $value += (float) $e['actual_value'];
            $has = true;
        }
    }
    $st = joma_water_state($user_id, $date);
    $isToday = ($date === $today);
    $draft = ($has && $isToday && $st['status'] === 'draft');
    return array(
        'has' => $has,
        'value' => $value,
        'target' => $pa ? (float) $pa['target_value'] : 0.0,
        'unit' => $pa && isset($pa['unit']) ? $pa['unit'] : 'UNIT_GLASS',
        'is_draft' => $draft,
        'is_final' => ($has && !$draft),
        'needs_final' => $draft, // فقط پیش‌نویسِ امروز نیاز به قطعی‌سازی دارد
    );
}

/** تعداد لیوان‌هایی که سنجهٔ جوجه می‌شمارد (فقط قطعی‌ها). */
function joma_water_final_glasses($user_id, $events, $date = null) {
    $sum = 0.0;
    foreach ($events as $e) {
        if (!isset($e['activity_code']) || $e['activity_code'] !== 'ACT002') continue;
        if ($date !== null && $e['performance_date'] !== $date) continue;
        if (!joma_water_is_final($user_id, $e['performance_date'])) continue;
        $sum += (float) $e['actual_value'];
    }
    return $sum;
}
