<?php
/**
 * JOMA — B8: کپی برنامهٔ یک ماه به ماه بعد
 * =========================================================================
 * پنج شرط سند (SPEC/18 §۸٫۱) — اینجا همه رعایت می‌شود:
 *   ۱) مقصد فقط برنامهٔ **ماه دیگر** و در وضعیت DRAFT یا PLANNING باشد.
 *   ۲) مقصد **اسنپ‌شات تازهٔ خودش** را می‌گیرد (snapshot_at = همین حالا).
 *   ۳) منبع، **اسنپ‌شات همان دورهٔ مبدأ** است — نه کتابخانهٔ زنده.
 *   ۴) **هیچ رخدادی و هیچ حالی کپی نمی‌شود**؛ دادهٔ ماه قبل سرجای خودش می‌ماند.
 *   ۵) فقط وقتی دورهٔ مبدأ **قفل** است (RUNNING یا ARCHIVED).
 *
 * هیچ نوشتنی روی دورهٔ مبدأ انجام نمی‌شود و هیچ داده‌ای حذف/تغییر نمی‌شود.
 * =========================================================================
 */

/** کلید دورهٔ بعدی (ماه بعد) از یک کلید جلالی مثل 1405-06 → 1405-07 (و 1405-12 → 1406-01). */
function joma_next_period_key($period_key) {
    if (!preg_match('/^(\d{4})-(\d{2})$/', (string) $period_key, $m)) {
        return jalali_period_key(jalali_today());
    }
    $y = (int) $m[1];
    $mo = (int) $m[2];
    $mo++;
    if ($mo > 12) { $mo = 1; $y++; }
    return sprintf('%04d-%02d', $y, $mo);
}

/** کلید دورهٔ قبلی (برای پیام‌ها و کارت‌ها). */
function joma_prev_period_key($period_key) {
    if (!preg_match('/^(\d{4})-(\d{2})$/', (string) $period_key, $m)) return null;
    $y = (int) $m[1];
    $mo = (int) $m[2];
    $mo--;
    if ($mo < 1) { $mo = 12; $y--; }
    return sprintf('%04d-%02d', $y, $mo);
}

/**
 * وضعیت کپی برای یک دورهٔ مبدأ — برای ساخت رابط، بدون هیچ نوشتنی.
 * خروجی: can_copy(bool) · reason(code) · message · source_count · target_key · target_label
 */
function joma_copy_plan_status($user_id, $plan) {
    $out = array(
        'can_copy' => false,
        'reason' => '',
        'message' => '',
        'source_count' => 0,
        'target_key' => joma_next_period_key($plan['period_key']),
        'target_label' => '',
    );
    $out['target_label'] = jalali_period_label($out['target_key']);

    $source = list_plan_activities($plan['id'], $user_id);
    $out['source_count'] = count($source);

    if (!in_array($plan['status'], array('RUNNING', 'ARCHIVED'), true)) {
        $out['reason'] = 'SOURCE_NOT_LOCKED';
        $out['message'] = 'کپی فقط وقتی معنا دارد که دورهٔ فعلی شروع شده باشد؛ الان می‌توانی همین دوره را ویرایش کنی.';
        return $out;
    }
    if ($out['source_count'] < 1) {
        $out['reason'] = 'SOURCE_EMPTY';
        $out['message'] = 'این دوره هیچ فعالیتی ندارد؛ اول از کتابخانه چند فعالیت اضافه کن.';
        return $out;
    }

    $wp = ensure_period($user_id, $out['target_key']);
    $target = $wp['plan'];
    $targetRows = list_plan_activities($target['id'], $user_id);

    if (!plan_editable($target['status'])) {
        $out['reason'] = 'TARGET_LOCKED';
        $out['message'] = 'دورهٔ ' . $out['target_label'] . ' از قبل شروع شده است؛ نمی‌شود روی آن چیزی اضافه کرد.';
        return $out;
    }
    if (count($targetRows) > 0) {
        $out['reason'] = 'TARGET_EXISTS';
        $out['message'] = $out['target_label'] . ' از قبل ساخته شده است.';
        return $out;
    }

    $out['can_copy'] = true;
    return $out;
}

/**
 * انجام کپی. خروجی: array('ok'=>bool,'error'=>string,'copied'=>int,'target_key'=>...)
 */
function joma_copy_plan_to_next($user_id, $plan) {
    $st = joma_copy_plan_status($user_id, $plan);
    if (empty($st['can_copy'])) {
        return array('ok' => false, 'error' => $st['message'], 'copied' => 0, 'target_key' => $st['target_key']);
    }

    $source = list_plan_activities($plan['id'], $user_id);
    $wp = ensure_period($user_id, $st['target_key']);
    $target = $wp['plan'];
    $now = joma_now();
    $copied = 0;

    foreach ($source as $row) {
        // هیچ دادهٔ ثبت/حالی منتقل نمی‌شود؛ فقط تصویر قفل‌شدهٔ برنامه.
        $fields = array(
            'user_id' => (int) $user_id,
            'plan_id' => (int) $target['id'],
            'period_key' => $target['period_key'],
            'activity_id' => (int) $row['activity_id'],
            'activity_code' => $row['activity_code'],
            'name' => $row['name'],
            'category' => $row['category'],
            'frequency' => $row['frequency'],
            'data_type' => $row['data_type'],
            'unit' => $row['unit'],
            'daily_target' => $row['daily_target'],
            'weekly_target' => $row['weekly_target'],
            'monthly_target' => $row['monthly_target'],
            'target_value' => $row['target_value'],
            'weight' => (int) $row['weight'],
            'sticker' => $row['sticker'],
            'color' => $row['color'],
            'sort_order' => (int) $row['sort_order'],
            'snapshot_at' => $now, // اسنپ‌شات تازهٔ خودِ مقصد
        );

        if (store_mode() === 'mysql') {
            joma_exec(
                'INSERT INTO joma_plan_activities (user_id,plan_id,period_key,activity_id,activity_code,name,category,frequency,data_type,unit,daily_target,weekly_target,monthly_target,target_value,weight,sticker,color,sort_order,snapshot_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                'iisissssssddddissis',
                array(
                    $fields['user_id'], $fields['plan_id'], $fields['period_key'], $fields['activity_id'], $fields['activity_code'],
                    $fields['name'], $fields['category'], $fields['frequency'], $fields['data_type'], $fields['unit'],
                    $fields['daily_target'], $fields['weekly_target'], $fields['monthly_target'], $fields['target_value'],
                    $fields['weight'], $fields['sticker'], $fields['color'], $fields['sort_order'], $fields['snapshot_at'],
                )
            );
        } else {
            $data = store_load();
            $fields['id'] = store_next_id($data);
            $data['plan_activities'][] = $fields;
            store_save($data);
        }
        $copied++;
    }

    return array(
        'ok' => $copied > 0,
        'error' => ($copied > 0) ? '' : 'هیچ فعالیتی کپی نشد.',
        'copied' => $copied,
        'target_key' => $st['target_key'],
    );
}
