<?php
/*
 * JOMA Analytics Reports — فاز R1 («گزارش تحلیلی»)
 * ------------------------------------------------------------------
 * سند مادر: docs/HAMMASIR-REPORTS.md (بازنگری R1.0 — Preflight کد واقعی)
 * حکم PO: فقط گزارش شخصی کاربر جاری؛ read-only؛ بدون Cache/Audit/AJAX؛
 * فرم‌ها بدون JS هم جدول می‌دهند؛ Chart فقط assets/js/chart.min.js محلی
 * (نسخهٔ واقعی: Chart.js v4.5.1 — تأیید از banner فایل).
 *
 * PC-1..PC-6؛ PHP 7.0-safe (بدون type declaration/کلاس)؛ mysqli Prepared؛
 * file/mysql هر دو؛ بدون side effect هنگام include؛ خطا → کد معنایی
 * (exception خام به UI نمی‌رسد — D-1)؛ بدون subquery سنگین؛ سقف ۹۰ روز.
 *
 * این فایل فقط از pages/reports.php داخل try/catch(Throwable) لود می‌شود
 * (fail-closed). هیچ فایل هسته‌ای تغییر نمی‌کند و هیچ write‌ای انجام نمی‌دهد.
 */

if (!function_exists('joma_header')) {
    // دسترسی مستقیم وب به فایل توابع → 403 خشک
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/* ------------------------------------------------------------------ */
/* Allowlistهای مرکزی (§۱۲ حکم)                                        */
/* ------------------------------------------------------------------ */

function analytics_reports_mood_metrics()
{
    // ۵ شاخص واقعی خلق جوما (pages/mood.php + joma_mood_records / store moods)
    // metric_id = نام فیلد واقعی ذخیره‌سازی (allowlist POST — حکم §۱۲)
    return array(
        'energy' => array('label' => 'انرژی', 'unit' => '۱..۵', 'field' => 'energy', 'inverted' => false),
        'general_mood' => array('label' => 'حال عمومی', 'unit' => '۱..۵', 'field' => 'general_mood', 'inverted' => false),
        'focus' => array('label' => 'تمرکز', 'unit' => '۱..۵', 'field' => 'focus', 'inverted' => false),
        'sleep_quality' => array('label' => 'کیفیت خواب', 'unit' => '۱..۵', 'field' => 'sleep_quality', 'inverted' => false),
        'stress' => array('label' => 'سطح استرس', 'unit' => '۱..۵', 'field' => 'stress', 'inverted' => true),
    );
}

function analytics_reports_error_messages()
{
    // پیام‌های فارسی ثابت (§۱۲ حکم — بدون تغییر)
    return array(
        'type' => 'نوع گزارش نامعتبر است.',
        'month' => 'ماه انتخاب‌شده معتبر نیست.',
        'week' => 'هفته انتخاب‌شده معتبر نیست.',
        'range' => 'بازه زمانی انتخاب‌شده معتبر نیست.',
        'cap90' => 'حداکثر بازه قابل انتخاب ۹۰ روز است.',
        'activity' => 'فعالیت انتخاب‌شده معتبر نیست.',
        'mood_metric' => 'شاخص خلق انتخاب‌شده معتبر نیست.',
        'empty' => 'در این دوره داده‌ای ثبت نشده است. دوره دیگری انتخاب کنید.',
        'unavailable' => 'امکان تهیه گزارش در حال حاضر وجود ندارد.',
        'metric' => 'شاخص انتخاب‌شده معتبر نیست.',
        'none' => 'حداقل یک شاخص انتخاب کنید.',
        'max5' => 'حداکثر ۵ شاخص.',
        'corr_same' => 'دو شاخص متفاوت انتخاب کنید.',
        'corr_insufficient' => 'داده کافی برای محاسبه همبستگی وجود ندارد.',
    );
}

/* ------------------------------------------------------------------ */
/* ابزارهای تاریخ (فقط helpers هسته — بدون تابع موازی ناسازگار)          */
/* ------------------------------------------------------------------ */

function analytics_reports_today()
{
    // آینهٔ hammasir_jalali_today() (D35 — Asia/Tehran) به‌صورت self-contained؛
    // functions/hammasir.php فریز است و در صفحه‌ی reports لود نمی‌شود.
    if (!function_exists('gregorian_to_jalali') || !function_exists('jalali_pad')) {
        return null;
    }
    $dt = new DateTime('now', new DateTimeZone('Asia/Tehran'));
    $p = gregorian_to_jalali((int) $dt->format('Y'), (int) $dt->format('n'), (int) $dt->format('j'));
    return $p[0] . '-' . jalali_pad($p[1]) . '-' . jalali_pad($p[2]);
}

function analytics_reports_day_axis($from, $to)
{
    // محور روزهای بازه (سرورساخته — اصل ۵)؛ سقف ایمن ۱۲۰
    $days = array();
    if (!function_exists('success_jalali_add_days')) return $days;
    $from = (string) $from;
    $to = (string) $to;
    if (strlen($from) !== 10 || strlen($to) !== 10) return $days;
    $cur = $from;
    $guard = 0;
    while (strcmp($cur, $to) <= 0 && $guard < 120) {
        $days[] = $cur;
        $cur = success_jalali_add_days($cur, 1);
        $guard++;
    }
    return $days;
}

/* ------------------------------------------------------------------ */
/* ۱) report_weeks_in_month — هفته‌های شنبه‌محور ماه شمسی (§۴ سند)       */
/* ------------------------------------------------------------------ */

function report_weeks_in_month($yyyy_mm)
{
    $yyyy_mm = (string) $yyyy_mm;
    if (!preg_match('/^\d{4}-\d{2}$/', $yyyy_mm)) return array();
    if (!function_exists('jalali_period_bounds') || !function_exists('jalali_weekday_index') || !function_exists('success_jalali_add_days')) return array();
    if (!function_exists('fa_num')) return array();
    $mm = (int) substr($yyyy_mm, 5, 2);
    $yy = (int) substr($yyyy_mm, 0, 4);
    if ($mm < 1 || $mm > 12 || $yy < 1300 || $yy > 1500) return array();
    $b = jalali_period_bounds($yyyy_mm);
    if (!is_array($b) || !isset($b['start']) || !isset($b['end'])) return array();
    $weeks = array();
    $idx = 0;
    $from = null;
    $cnt = 0;
    $cur = (string) $b['start'];
    $guard = 0;
    // هفته فقط داخل همین ماه بسته می‌شود (روی جمعه یا روز آخر ماه) —
    // هیچ روزی از ماه قبل/بعد وارد بازه نمی‌شود؛ تعداد واقعی (۴ یا ۵) محاسبه می‌شود.
    while (strcmp($cur, (string) $b['end']) <= 0 && $guard < 40) {
        if ($from === null) {
            $from = $cur;
            $cnt = 0;
        }
        $cnt++;
        $is_friday = ((int) jalali_weekday_index($cur) === 6);
        $is_last = (strcmp($cur, (string) $b['end']) === 0);
        if ($is_friday || $is_last) {
            $idx++;
            $weeks[] = array(
                'index' => $idx,
                'from' => $from,
                'to' => $cur,
                'days_count' => $cnt,
                'label' => 'هفتهٔ ' . fa_num($idx) . (($cnt < 3) ? ' (ناقص)' : ''),
                'incomplete' => ($cnt < 3),
            );
            $from = null;
        }
        $cur = success_jalali_add_days($cur, 1);
        $guard++;
    }
    return $weeks;
}

/* ------------------------------------------------------------------ */
/* ۲) hammasir_report_period_bounds — بازهٔ سرورساخته (§۵ حکم)          */
/* ------------------------------------------------------------------ */

function hammasir_report_period_bounds($preset, $year_month, $week_index)
{
    $out = array('ok' => false, 'error' => 'type', 'from' => '', 'to' => '', 'days' => 0, 'week_label' => '', 'axis' => array());
    $today = analytics_reports_today();
    if ($today === null) {
        $out['error'] = 'unavailable';
        return $out;
    }
    $preset = (string) $preset;
    if ($preset === 'last_4_weeks') {
        if (!function_exists('success_jalali_add_days')) {
            $out['error'] = 'unavailable';
            return $out;
        }
        $out['from'] = success_jalali_add_days($today, -27);
        $out['to'] = $today;
    } elseif ($preset === 'month' || $preset === 'month_week') {
        $ym = (string) $year_month;
        if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
            $out['error'] = 'month';
            return $out;
        }
        $mm = (int) substr($ym, 5, 2);
        $yy = (int) substr($ym, 0, 4);
        if ($mm < 1 || $mm > 12 || $yy < 1300 || $yy > 1500) {
            $out['error'] = 'month';
            return $out;
        }
        if (!function_exists('jalali_period_bounds')) {
            $out['error'] = 'unavailable';
            return $out;
        }
        $b = jalali_period_bounds($ym);
        if (!is_array($b) || !isset($b['start']) || !isset($b['end'])) {
            $out['error'] = 'month';
            return $out;
        }
        if ($preset === 'month') {
            $out['from'] = (string) $b['start'];
            // آینده وارد بازه نمی‌شود — حداکثر تا امروز
            $out['to'] = (strcmp((string) $b['end'], $today) > 0) ? $today : (string) $b['end'];
        } else {
            $weeks = report_weeks_in_month($ym);
            if (count($weeks) === 0) {
                $out['error'] = 'month';
                return $out;
            }
            $wi = (int) $week_index;
            $found = null;
            foreach ($weeks as $w) {
                if ((int) $w['index'] === $wi) {
                    $found = $w;
                    break;
                }
            }
            if ($found === null) {
                $out['error'] = 'week';
                return $out;
            }
            $out['from'] = (string) $found['from'];
            $out['to'] = (strcmp((string) $found['to'], $today) > 0) ? $today : (string) $found['to'];
            $out['week_label'] = (string) $found['label'];
        }
    } else {
        return $out;
    }
    if (strcmp($out['to'], $out['from']) < 0) {
        $out['error'] = 'range';
        return $out;
    }
    $axis = analytics_reports_day_axis($out['from'], $out['to']);
    $n = count($axis);
    if ($n < 1) {
        $out['error'] = 'range';
        return $out;
    }
    if ($n > 90) {
        $out['error'] = 'cap90';
        return $out;
    }
    $out['axis'] = $axis;
    $out['days'] = $n;
    $out['ok'] = true;
    $out['error'] = '';
    return $out;
}

/* ------------------------------------------------------------------ */
/* ۳) hammasir_metric_catalog — whitelist                              */
/* ------------------------------------------------------------------ */

function hammasir_metric_catalog()
{
    $cat = array();
    foreach (analytics_reports_mood_metrics() as $k => $m) {
        $cat[$k] = array(
            'label' => $m['label'],
            'unit' => $m['unit'],
            'group' => 'mood',
            'required_perm' => null,
            'phase' => 'R1',
            'inverted' => $m['inverted'],
        );
    }
    $cat['perf_activity'] = array(
        'label' => 'مقدار ثبت‌شده‌ی فعالیت',
        'unit' => '',
        'group' => 'perf',
        'required_perm' => null,
        'phase' => 'R1',
        'inverted' => false,
    );
    $cat['mood_composite'] = array(
        'label' => 'حال‌وهوای ترکیبی',
        'unit' => '۱..۸',
        'group' => 'mood',
        'required_perm' => null,
        'phase' => 'R2 (اختیاری — فقط استرس را با 6-stress معکوس می‌کند)',
        'inverted' => false,
    );
    $cat['perf_count'] = array(
        'label' => 'تعداد ثبت‌های عملکرد',
        'unit' => 'عدد',
        'group' => 'perf',
        'required_perm' => null,
        'phase' => 'R2',
        'inverted' => false,
    );
    return $cat;
}

/* ------------------------------------------------------------------ */
/* ۴) hammasir_report_validate_request — CSRF بیرون (لایه‌ی هندلر)      */
/* ------------------------------------------------------------------ */

function hammasir_report_validate_request($post)
{
    $clean = array(
        'report_tab' => 'activity',
        'period_preset' => 'last_4_weeks',
        'year_month' => '',
        'week_index' => 0,
        'activity_id' => 0,
        'overlay_mood_metric' => '',
    );
    $out = array('ok' => false, 'error' => 'type', 'clean' => $clean);
    if (!is_array($post)) return $out;
    $tab = isset($post['report_tab']) ? (string) $post['report_tab'] : 'activity';
    if ($tab !== 'activity' && $tab !== 'mood') {
        $out['error'] = 'type';
        return $out;
    }
    $clean['report_tab'] = $tab;
    $preset = isset($post['period_preset']) ? (string) $post['period_preset'] : 'last_4_weeks';
    if ($preset !== 'month' && $preset !== 'month_week' && $preset !== 'last_4_weeks') {
        $out['error'] = 'type';
        return $out;
    }
    $clean['period_preset'] = $preset;
    $clean['year_month'] = isset($post['year_month']) ? (string) $post['year_month'] : '';
    $clean['week_index'] = isset($post['week_index']) ? (int) $post['week_index'] : 0;
    $act = isset($post['activity_id']) ? (int) $post['activity_id'] : 0;
    $clean['activity_id'] = $act;
    $ov = isset($post['overlay_mood_metric']) ? (string) $post['overlay_mood_metric'] : '';
    if ($tab === 'mood') {
        $clean['activity_id'] = 0;
        $clean['overlay_mood_metric'] = '';
    } else {
        $clean['overlay_mood_metric'] = $ov;
    }
    if ($tab === 'activity' && $act <= 0) {
        $out['error'] = 'activity';
        $out['clean'] = $clean;
        return $out;
    }
    $metrics = analytics_reports_mood_metrics();
    if ($tab === 'activity' && $ov !== '' && !isset($metrics[$ov])) {
        $out['error'] = 'mood_metric';
        $out['clean'] = $clean;
        return $out;
    }
    $out['clean'] = $clean;
    $out['ok'] = true;
    $out['error'] = '';
    return $out;
}

/* ------------------------------------------------------------------ */
/* file-mode: یک load اصلی در هر request (memo)                        */
/* ------------------------------------------------------------------ */

function analytics_reports_store()
{
    static $cache = null;
    if ($cache === null) {
        $cache = store_load();
        if (!is_array($cache)) $cache = array();
    }
    return $cache;
}

function analytics_reports_mood_rows($user_id, $from, $to)
{
    // list_moods هسته (read-only، file/mysql) — یک بار در هر request
    static $cache = array();
    $key = $user_id . '|' . $from . '|' . $to;
    if (!isset($cache[$key])) {
        $rows = array();
        if (function_exists('list_moods')) {
            $rows = list_moods($user_id, $from, $to);
            if (!is_array($rows)) $rows = array();
        }
        $cache[$key] = $rows;
    }
    return $cache[$key];
}

/* ------------------------------------------------------------------ */
/* ۵) hammasir_activity_options — گزینه‌های select (مالکیت کاربر)       */
/* ------------------------------------------------------------------ */

function hammasir_activity_options($subject_user_id, $from, $to)
{
    $uid = (int) $subject_user_id;
    $out = array();
    $from = (string) $from;
    $to = (string) $to;
    if ($uid <= 0 || strlen($from) !== 10 || strlen($to) !== 10) return $out;
    // ماه‌های درگیر بازه (برای ۹۰ روز حداکثر ۴ ماه)
    $months = array();
    $m = substr($from, 0, 7);
    $end = substr($to, 0, 7);
    $guard = 0;
    while ($guard < 6) {
        $months[] = $m;
        if ($m === $end) break;
        $yy = (int) substr($m, 0, 4);
        $mm = (int) substr($m, 5, 2);
        $mm++;
        if ($mm > 12) {
            $mm = 1;
            $yy++;
        }
        $m = $yy . '-' . (($mm < 10) ? '0' . $mm : (string) $mm);
        $guard++;
    }
    $seen = array();
    foreach ($months as $mk) {
        $plan = null;
        if (store_mode() === 'mysql') {
            $plan = joma_query_one(
                'SELECT pe.*, pl.id AS plan_id FROM joma_periods pe JOIN joma_plans pl ON pl.period_id=pe.id WHERE pe.user_id=? AND pe.period_key=? LIMIT 1',
                'is',
                array($uid, $mk)
            );
        } else {
            $store = analytics_reports_store();
            $period_row = null;
            if (isset($store['periods']) && is_array($store['periods'])) {
                foreach ($store['periods'] as $pe) {
                    if (isset($pe['user_id'], $pe['period_key']) && (int) $pe['user_id'] === $uid && (string) $pe['period_key'] === $mk) {
                        $period_row = $pe;
                        break;
                    }
                }
            }
            if ($period_row !== null && isset($store['plans']) && is_array($store['plans'])) {
                foreach ($store['plans'] as $pl) {
                    if (isset($pl['period_id']) && (int) $pl['period_id'] === (int) $period_row['id']) {
                        $plan = $period_row;
                        $plan['plan_id'] = $pl['id'];
                        break;
                    }
                }
            }
        }
        if (!$plan || !isset($plan['plan_id'])) continue;
        $pas = list_plan_activities((int) $plan['plan_id'], $uid);
        if (!is_array($pas)) continue;
        foreach ($pas as $pa) {
            $pid = isset($pa['id']) ? (int) $pa['id'] : 0;
            if ($pid <= 0 || isset($seen[$pid])) continue;
            $seen[$pid] = true;
            $out[] = array(
                'plan_activity_id' => $pid,
                'title' => isset($pa['name']) ? (string) $pa['name'] : '',
                'unit' => isset($pa['unit']) ? (string) $pa['unit'] : '',
                'plan_id' => (int) $plan['plan_id'],
                'period_key' => $mk,
            );
            if (count($out) >= 50) break;
        }
        if (count($out) >= 50) break;
    }
    return $out;
}

/* ------------------------------------------------------------------ */
/* سازنده‌های سری (داخلی)                                              */
/* ------------------------------------------------------------------ */

function analytics_reports_mood_series($user_id, $metric_id, $axis, $from, $to)
{
    $metrics = analytics_reports_mood_metrics();
    if (!isset($metrics[$metric_id])) return null;
    $spec = $metrics[$metric_id];
    $field = $spec['field'];
    $by_date = array();
    $rows = analytics_reports_mood_rows((int) $user_id, (string) $from, (string) $to);
    foreach ($rows as $r) {
        if (isset($r['jalali_date'], $r[$field])) {
            $by_date[(string) $r['jalali_date']] = (int) $r[$field];
        }
    }
    $points = array();
    $has = false;
    foreach ($axis as $d) {
        if (array_key_exists($d, $by_date)) {
            $points[] = array('jalali_date' => $d, 'value' => $by_date[$d]);
            $has = true;
        } else {
            // خلق ثبت‌نشده = null (نه صفر — §۶ سند)
            $points[] = array('jalali_date' => $d, 'value' => null);
        }
    }
    return array(
        'id' => $metric_id,
        'label' => $spec['label'],
        'unit' => $spec['unit'],
        'inverted' => $spec['inverted'],
        'direction_note' => $spec['inverted'] ? '۱ = آرام‌ترین، ۵ = پرتنش‌ترین' : '',
        'points' => $points,
        'has_data' => $has,
    );
}

function analytics_reports_perf_series($user_id, $plan_activity_id, $axis, $from, $to)
{
    $uid = (int) $user_id;
    $pa_id = (int) $plan_activity_id;
    if ($uid <= 0 || $pa_id <= 0) return null;
    // مالکیت سمت سرور — re-check (AC-R1.7)
    $pa = null;
    if (store_mode() === 'mysql') {
        $pa = joma_query_one('SELECT id, name, unit FROM joma_plan_activities WHERE id=? AND user_id=? LIMIT 1', 'ii', array($pa_id, $uid));
    } else {
        $store = analytics_reports_store();
        if (isset($store['plan_activities']) && is_array($store['plan_activities'])) {
            foreach ($store['plan_activities'] as $row) {
                if (isset($row['id'], $row['user_id']) && (int) $row['id'] === $pa_id && (int) $row['user_id'] === $uid) {
                    $pa = $row;
                    break;
                }
            }
        }
    }
    if (!$pa) return null;
    $by_date = array();
    if (store_mode() === 'mysql') {
        $rows = joma_query(
            'SELECT performance_date, SUM(actual_value) AS s FROM joma_performance_events WHERE user_id=? AND plan_activity_id=? AND performance_date>=? AND performance_date<=? GROUP BY performance_date',
            'iiss',
            array($uid, $pa_id, (string) $from, (string) $to)
        );
        if (is_array($rows)) {
            foreach ($rows as $r) {
                if (isset($r['performance_date'])) $by_date[(string) $r['performance_date']] = isset($r['s']) ? (float) $r['s'] : 0.0;
            }
        }
    } else {
        $store = analytics_reports_store();
        if (isset($store['events']) && is_array($store['events'])) {
            foreach ($store['events'] as $ev) {
                if (!isset($ev['user_id'], $ev['plan_activity_id'], $ev['performance_date'])) continue;
                if ((int) $ev['user_id'] !== $uid || (int) $ev['plan_activity_id'] !== $pa_id) continue;
                $d = (string) $ev['performance_date'];
                if (strcmp($d, (string) $from) < 0 || strcmp($d, (string) $to) > 0) continue;
                if (!isset($by_date[$d])) $by_date[$d] = 0.0;
                $by_date[$d] += isset($ev['actual_value']) ? (float) $ev['actual_value'] : 0.0;
            }
        }
    }
    // معیار واحد R1: مجموع مقدارهای ثبت‌شده‌ی فعالیت در هر روز (سازگار با
    // daySum در build_report و sum_actual موتور موفقیت)؛ روز بدون ثبت = null.
    $points = array();
    $has = false;
    foreach ($axis as $d) {
        if (array_key_exists($d, $by_date)) {
            $points[] = array('jalali_date' => $d, 'value' => $by_date[$d]);
            $has = true;
        } else {
            $points[] = array('jalali_date' => $d, 'value' => null);
        }
    }
    $unit = isset($pa['unit']) ? (string) $pa['unit'] : '';
    $unit_label = $unit;
    if ($unit !== '' && $unit !== 'UNIT_NONE' && function_exists('units_list')) {
        $ul = units_list();
        if (isset($ul[$unit])) $unit_label = $ul[$unit];
    }
    if ($unit === 'UNIT_NONE') $unit_label = '';
    return array(
        'id' => 'perf_activity',
        'label' => isset($pa['name']) ? (string) $pa['name'] : 'فعالیت',
        'unit' => $unit_label,
        'inverted' => false,
        'direction_note' => '',
        'points' => $points,
        'has_data' => $has,
    );
}

function analytics_reports_r2_metric_options()
{
    // گزینه‌های شاخص R2 (حکم §۱ R2): ۵ شاخص خلق + ترکیبی + دو شاخص عملکرد واقعی
    $out = array();
    foreach (analytics_reports_mood_metrics() as $k => $m) {
        $out[$k] = $m['label'];
    }
    $out['mood_composite'] = 'حال‌وهوای ترکیبی';
    $out['perf_activity'] = 'مقدار/عملکرد فعالیت';
    $out['perf_count'] = 'تعداد ثبت‌های عملکرد';
    return $out;
}

function analytics_reports_mood_composite_series($user_id, $axis, $from, $to)
{
    // حال‌وهوای ترکیبی — عین فرمول hammasir_mood_wellbeing موجود (فاز A):
    // میانگین ۴ شاخص مثبت + (6 - stress) در [1,5] سپس round(×8/5) در 1..8.
    // فقط همین metric حق معکوس‌کردن استرس را دارد (حکم §۲ R2).
    $rows = analytics_reports_mood_rows((int) $user_id, (string) $from, (string) $to);
    $by_date = array();
    foreach ($rows as $r) {
        if (isset($r['jalali_date'])) $by_date[(string) $r['jalali_date']] = $r;
    }
    $points = array();
    $has = false;
    foreach ($axis as $d) {
        if (isset($by_date[$d])) {
            $m = $by_date[$d];
            $sum = 0.0;
            $n = 0;
            foreach (array('energy', 'general_mood', 'focus', 'sleep_quality') as $k) {
                if (isset($m[$k]) && (int) $m[$k] > 0) { $sum += (int) $m[$k]; $n++; }
            }
            if (isset($m['stress']) && (int) $m['stress'] > 0) { $sum += (6 - (int) $m['stress']); $n++; }
            if ($n === 0) {
                $points[] = array('jalali_date' => $d, 'value' => null);
                continue;
            }
            $v = (int) round(($sum / $n) * 8 / 5);
            if ($v < 1) $v = 1;
            if ($v > 8) $v = 8;
            $points[] = array('jalali_date' => $d, 'value' => $v);
            $has = true;
        } else {
            $points[] = array('jalali_date' => $d, 'value' => null);
        }
    }
    return array(
        'id' => 'mood_composite',
        'label' => 'حال‌وهوای ترکیبی',
        'unit' => '۱..۸',
        'inverted' => false,
        'direction_note' => 'در حال‌وهوای ترکیبی، استرس برای هم‌جهت‌شدن با شاخص‌های مثبت معکوس شده است.',
        'points' => $points,
        'has_data' => $has,
    );
}

function analytics_reports_perf_count_series($user_id, $axis, $from, $to)
{
    // تعداد ثبت‌های عملکرد کاربر در هر روز (منبع واقعی: joma_performance_events /
    // store['events']) — شمارش است: روز بدون ثبت = 0 (نه null) — حکم §۱ R2.
    $uid = (int) $user_id;
    $by_date = array();
    if (store_mode() === 'mysql') {
        $rows = joma_query(
            'SELECT performance_date, COUNT(*) AS c FROM joma_performance_events WHERE user_id=? AND performance_date>=? AND performance_date<=? GROUP BY performance_date',
            'iss',
            array($uid, (string) $from, (string) $to)
        );
        if (is_array($rows)) {
            foreach ($rows as $r) {
                if (isset($r['performance_date'])) $by_date[(string) $r['performance_date']] = (int) $r['c'];
            }
        }
    } else {
        $store = analytics_reports_store();
        if (isset($store['events']) && is_array($store['events'])) {
            foreach ($store['events'] as $ev) {
                if (!isset($ev['user_id'], $ev['performance_date'])) continue;
                if ((int) $ev['user_id'] !== $uid) continue;
                $d = (string) $ev['performance_date'];
                if (strcmp($d, (string) $from) < 0 || strcmp($d, (string) $to) > 0) continue;
                if (!isset($by_date[$d])) $by_date[$d] = 0;
                $by_date[$d]++;
            }
        }
    }
    $points = array();
    $has = false;
    foreach ($axis as $d) {
        $c = isset($by_date[$d]) ? (int) $by_date[$d] : 0;
        $points[] = array('jalali_date' => $d, 'value' => $c);
        if ($c > 0) $has = true;
    }
    return array(
        'id' => 'perf_count',
        'label' => 'تعداد ثبت‌های عملکرد',
        'unit' => 'عدد',
        'inverted' => false,
        'direction_note' => '',
        'points' => $points,
        'has_data' => $has,
    );
}

/* ------------------------------------------------------------------ */
/* ۶) hammasir_report_series — R1: فقط گزارش شخصی                      */
/* ------------------------------------------------------------------ */

function hammasir_report_series($viewer_user_id, $subject_user_id, $link_id_or_null, $from, $to, $metric_ids, $activity_id)
{
    // R1 (حکم §۱۳): viewer=subject=کاربر جاری؛ link_id پذیرفته نمی‌شود؛
    // هیچ داده‌ی کاربر دیگر خوانده نمی‌شود.
    $viewer_user_id = (int) $viewer_user_id;
    $subject_user_id = (int) $subject_user_id;
    if ($viewer_user_id <= 0 || $viewer_user_id !== $subject_user_id) return null;
    if ($link_id_or_null !== null && (int) $link_id_or_null !== 0) return null;
    $from = (string) $from;
    $to = (string) $to;
    if (strlen($from) !== 10 || strlen($to) !== 10) return null;
    $today = analytics_reports_today();
    if ($today === null) return null;
    if (strcmp($to, $today) > 0) $to = $today;
    if (strcmp($to, $from) < 0) return null;
    $axis = analytics_reports_day_axis($from, $to);
    $n = count($axis);
    if ($n < 1 || $n > 90) return null;
    if (!is_array($metric_ids)) return null;
    $out = array();
    foreach ($metric_ids as $mid) {
        $mid = (string) $mid;
        $s = null;
        if ($mid === 'perf_activity') {
            $s = analytics_reports_perf_series($subject_user_id, (int) $activity_id, $axis, $from, $to);
        } elseif ($mid === 'mood_composite') {
            $s = analytics_reports_mood_composite_series($subject_user_id, $axis, $from, $to);
        } elseif ($mid === 'perf_count') {
            $s = analytics_reports_perf_count_series($subject_user_id, $axis, $from, $to);
        } else {
            $s = analytics_reports_mood_series($subject_user_id, $mid, $axis, $from, $to);
        }
        if ($s !== null) $out[] = $s;
    }
    return $out;
}

/* ------------------------------------------------------------------ */
/* ۷) hammasir_report_overlay — ساختار نمایش دو سری (مقادیر خام)       */
/* ------------------------------------------------------------------ */

function hammasir_report_overlay($series_a, $series_b)
{
    if (!is_array($series_a) || !is_array($series_b)) return null;
    if (!isset($series_a['points']) || !isset($series_b['points'])) return null;
    if (!is_array($series_a['points']) || !is_array($series_b['points'])) return null;
    $n = count($series_a['points']);
    if ($n !== count($series_b['points']) || $n < 1) return null;
    for ($i = 0; $i < $n; $i++) {
        $da = isset($series_a['points'][$i]['jalali_date']) ? $series_a['points'][$i]['jalali_date'] : '';
        $db = isset($series_b['points'][$i]['jalali_date']) ? $series_b['points'][$i]['jalali_date'] : '';
        if ($da !== $db || $da === '') return null;
    }
    // مقایسه‌ی بصری — نرمال‌سازی مخفی وجود ندارد؛ هر سری روی محور خودش.
    return array(
        'ok' => true,
        'a' => $series_a,
        'b' => $series_b,
        'axis_left' => array('label' => 'فعالیت: ' . (isset($series_a['label']) ? $series_a['label'] : ''), 'unit' => isset($series_a['unit']) ? $series_a['unit'] : ''),
        'axis_right' => array('label' => isset($series_b['label']) ? $series_b['label'] : '', 'unit' => isset($series_b['unit']) ? $series_b['unit'] : ''),
    );
}

/* ------------------------------------------------------------------ */
/* ۸) hammasir_report_dto — DTO رندر-آمیز                              */
/* ------------------------------------------------------------------ */

function hammasir_report_dto($period, $series, $meta)
{
    if (!is_array($period) || !isset($period['axis']) || !is_array($series)) {
        return array('ok' => false, 'errors' => array('unavailable'));
    }
    $labels = array();
    foreach ($period['axis'] as $d) {
        $labels[] = function_exists('fa_num') ? fa_num((int) substr((string) $d, 8, 2)) : (string) ((int) substr((string) $d, 8, 2));
    }
    return array(
        'ok' => true,
        'from' => isset($period['from']) ? $period['from'] : '',
        'to' => isset($period['to']) ? $period['to'] : '',
        'labels' => $labels,
        'dates' => $period['axis'],
        'series' => $series,
        'meta' => is_array($meta) ? $meta : array(),
    );
}

/* ------------------------------------------------------------------ */
/* فاز R2 — همبستگی / روزهای هفته / ترکیب آزاد (حکم PO)                */
/* ------------------------------------------------------------------ */

function hammasir_report_correlation($series_a, $series_b)
{
    // پیرسون فقط روی روزهایِ زوج (هر دو شاخص مقدار معتبر دارند)؛ null حذف می‌شود
    // نه صفر؛ حداقل ۳ زوج؛ denominator صفر → insufficient. بدون تحلیل علّی.
    $out = array('r' => null, 'paired_points' => array(), 'label' => '', 'sample_count' => 0, 'error' => '');
    if (!is_array($series_a) || !is_array($series_b)) { $out['error'] = 'invalid'; return $out; }
    $pa = isset($series_a['points']) && is_array($series_a['points']) ? $series_a['points'] : array();
    $pb = isset($series_b['points']) && is_array($series_b['points']) ? $series_b['points'] : array();
    $n = count($pa);
    if ($n !== count($pb) || $n < 1) { $out['error'] = 'invalid'; return $out; }
    $xs = array();
    $ys = array();
    for ($i = 0; $i < $n; $i++) {
        $va = (isset($pa[$i]['value']) && $pa[$i]['value'] !== null) ? (float) $pa[$i]['value'] : null;
        $vb = (isset($pb[$i]['value']) && $pb[$i]['value'] !== null) ? (float) $pb[$i]['value'] : null;
        if ($va === null || $vb === null) continue;
        $xs[] = $va;
        $ys[] = $vb;
        $out['paired_points'][] = array(
            'jalali_date' => isset($pa[$i]['jalali_date']) ? $pa[$i]['jalali_date'] : '',
            'a' => $pa[$i]['value'],
            'b' => $pb[$i]['value'],
        );
    }
    $m = count($xs);
    $out['sample_count'] = $m;
    if ($m < 3) { $out['error'] = 'insufficient'; return $out; }
    $mx = array_sum($xs) / $m;
    $my = array_sum($ys) / $m;
    $num = 0.0;
    $da = 0.0;
    $db = 0.0;
    for ($i = 0; $i < $m; $i++) {
        $dxa = $xs[$i] - $mx;
        $dyb = $ys[$i] - $my;
        $num += $dxa * $dyb;
        $da += $dxa * $dxa;
        $db += $dyb * $dyb;
    }
    if ($da <= 0 || $db <= 0) { $out['error'] = 'insufficient'; return $out; }
    $r = $num / sqrt($da * $db);
    if ($r > 1.0) $r = 1.0;
    if ($r < -1.0) $r = -1.0;
    $out['r'] = $r;
    if ($r >= 0.7) { $out['label'] = 'همبستگی مثبت قوی'; }
    elseif ($r >= 0.4) { $out['label'] = 'همبستگی مثبت متوسط'; }
    elseif ($r > -0.4) { $out['label'] = 'همبستگی ضعیف یا نامشخص'; }
    elseif ($r > -0.7) { $out['label'] = 'همبستگی منفی متوسط'; }
    else { $out['label'] = 'همبستگی منفی قوی'; }
    return $out;
}

function hammasir_report_weekday_averages($series)
{
    // هفت ردیف شنبه(0)..جمعه(6) — فقط مقادیر غیر null؛ count<2 → insufficient
    $names = array('شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه');
    $rows = array();
    for ($i = 0; $i < 7; $i++) {
        $rows[] = array('index' => $i, 'name' => $names[$i], 'count' => 0, 'sum' => 0.0, 'avg' => null, 'min' => null, 'max' => null, 'status' => 'insufficient');
    }
    if (!is_array($series) || !isset($series['points']) || !is_array($series['points'])) return $rows;
    if (!function_exists('jalali_weekday_index')) return $rows;
    foreach ($series['points'] as $p) {
        if (!isset($p['jalali_date']) || !isset($p['value']) || $p['value'] === null) continue;
        $wd = (int) jalali_weekday_index((string) $p['jalali_date']);
        if ($wd < 0 || $wd > 6) continue;
        $v = (float) $p['value'];
        $rows[$wd]['count']++;
        $rows[$wd]['sum'] += $v;
        if ($rows[$wd]['min'] === null || $v < $rows[$wd]['min']) $rows[$wd]['min'] = $v;
        if ($rows[$wd]['max'] === null || $v > $rows[$wd]['max']) $rows[$wd]['max'] = $v;
    }
    for ($i = 0; $i < 7; $i++) {
        if ($rows[$i]['count'] >= 2) {
            $rows[$i]['avg'] = $rows[$i]['sum'] / $rows[$i]['count'];
            $rows[$i]['status'] = 'ok';
        }
        unset($rows[$i]['sum']);
    }
    return $rows;
}

function hammasir_report_normalize_series($series)
{
    // مقدار نموداری ۰..۱۰۰ برای «ترکیب آزاد» — مقدار خام همیشه حفظ می‌شود؛
    // null نرمال نمی‌شود و null می‌ماند (حکم §۶ R2).
    if (!is_array($series) || !isset($series['points']) || !is_array($series['points'])) return null;
    $id = isset($series['id']) ? (string) $series['id'] : '';
    $mood5 = in_array($id, array('energy', 'general_mood', 'focus', 'sleep_quality', 'stress'), true);
    $max = null;
    if ($id === 'perf_activity' || $id === 'perf_count') {
        $max = 0.0;
        foreach ($series['points'] as $p) {
            if (isset($p['value']) && $p['value'] !== null && (float) $p['value'] > $max) $max = (float) $p['value'];
        }
    }
    $out = $series;
    $out['points'] = array();
    foreach ($series['points'] as $p) {
        $norm = null;
        if (isset($p['value']) && $p['value'] !== null) {
            $v = (float) $p['value'];
            if ($mood5) {
                $norm = (($v - 1) / 4) * 100;
            } elseif ($id === 'mood_composite') {
                $norm = (($v - 1) / 7) * 100;
            } else {
                if ($max !== null && $max > 0) $norm = ($v / $max) * 100;
            }
        }
        $out['points'][] = array(
            'jalali_date' => isset($p['jalali_date']) ? $p['jalali_date'] : '',
            'value' => isset($p['value']) ? $p['value'] : null,
            'normalized' => ($norm === null) ? null : round($norm, 1),
        );
    }
    return $out;
}

function hammasir_report_multi_index($viewer_user_id, $subject_user_id, $from, $to, $metric_ids, $activity_id)
{
    // ترکیب آزاد: ۱..۵ شاخص؛ همان hammasir_report_series (گزارش شخصی).
    if (!is_array($metric_ids)) return null;
    $metric_ids = array_values(array_unique($metric_ids));
    $c = count($metric_ids);
    if ($c < 1 || $c > 5) return null;
    return hammasir_report_series($viewer_user_id, $subject_user_id, null, $from, $to, $metric_ids, $activity_id);
}

function hammasir_report_validate_r2_request($post)
{
    $clean = array(
        'report_tab' => 'correlation',
        'period_preset' => 'last_4_weeks',
        'year_month' => '',
        'week_index' => 0,
        'metric_a' => 'energy',
        'metric_b' => 'general_mood',
        'metric_single' => 'energy',
        'metrics' => array(),
        'activity_id' => 0,
    );
    $out = array('ok' => false, 'error' => 'type', 'clean' => $clean);
    if (!is_array($post)) return $out;
    $tab = isset($post['report_tab']) ? (string) $post['report_tab'] : 'correlation';
    if ($tab !== 'correlation' && $tab !== 'weekday' && $tab !== 'multi') {
        $out['error'] = 'type';
        return $out;
    }
    $clean['report_tab'] = $tab;
    $preset = isset($post['period_preset']) ? (string) $post['period_preset'] : 'last_4_weeks';
    if ($preset !== 'month' && $preset !== 'month_week' && $preset !== 'last_4_weeks') {
        $out['error'] = 'type';
        return $out;
    }
    $clean['period_preset'] = $preset;
    $clean['year_month'] = isset($post['year_month']) ? (string) $post['year_month'] : '';
    $clean['week_index'] = isset($post['week_index']) ? (int) $post['week_index'] : 0;
    $clean['activity_id'] = isset($post['activity_id']) ? (int) $post['activity_id'] : 0;
    $opts = analytics_reports_r2_metric_options();
    if ($tab === 'correlation') {
        $a = isset($post['metric_a']) ? (string) $post['metric_a'] : 'energy';
        $b = isset($post['metric_b']) ? (string) $post['metric_b'] : 'general_mood';
        if (!isset($opts[$a]) || !isset($opts[$b])) { $out['error'] = 'metric'; $out['clean'] = $clean; return $out; }
        if ($a === $b) { $out['error'] = 'corr_same'; $out['clean'] = $clean; return $out; }
        $clean['metric_a'] = $a;
        $clean['metric_b'] = $b;
    } elseif ($tab === 'weekday') {
        $s = isset($post['metric_single']) ? (string) $post['metric_single'] : 'energy';
        if (!isset($opts[$s])) { $out['error'] = 'metric'; $out['clean'] = $clean; return $out; }
        $clean['metric_single'] = $s;
    } else {
        $list = array();
        if (isset($post['metrics']) && is_array($post['metrics'])) {
            foreach ($post['metrics'] as $mk) {
                $mk = (string) $mk;
                if (!isset($opts[$mk])) { $out['error'] = 'metric'; $out['clean'] = $clean; return $out; }
                if (!in_array($mk, $list, true)) $list[] = $mk;
            }
        }
        if (count($list) === 0) { $out['error'] = 'none'; $out['clean'] = $clean; return $out; }
        if (count($list) > 5) { $out['error'] = 'max5'; $out['clean'] = $clean; return $out; }
        $clean['metrics'] = $list;
    }
    $out['clean'] = $clean;
    $out['ok'] = true;
    $out['error'] = '';
    return $out;
}

function analytics_reports_direction_notes($metric_ids)
{
    // زیرنویس‌های جهت (حکم §۲ R2): استرس همیشه خام؛ فقط ترکیبی معکوس.
    $notes = array();
    if (in_array('stress', $metric_ids, true)) $notes[] = 'برای استرس، مقدار بالاتر یعنی تنش بیشتر.';
    if (in_array('mood_composite', $metric_ids, true)) $notes[] = 'در حال‌وهوای ترکیبی، استرس برای هم‌جهت‌شدن با شاخص‌های مثبت معکوس شده است.';
    return $notes;
}

function analytics_reports_corr_table($corr, $label_a, $label_b)
{
    echo '<table class="ar-tbl">';
    echo '<thead><tr><th>تاریخ شمسی</th><th>' . e($label_a) . '</th><th>' . e($label_b) . '</th></tr></thead><tbody>';
    foreach ($corr['paired_points'] as $p) {
        $date_label = function_exists('jalali_format') ? jalali_format($p['jalali_date']) : $p['jalali_date'];
        echo '<tr><td>' . e($date_label) . '</td><td>' . e(analytics_reports_fmt_value($p['a'])) . '</td><td>' . e(analytics_reports_fmt_value($p['b'])) . '</td></tr>';
    }
    echo '</tbody></table>';
}

function analytics_reports_weekday_table($rows)
{
    echo '<table class="ar-tbl">';
    echo '<thead><tr><th>روز هفته</th><th>تعداد نمونه</th><th>میانگین</th><th>کمترین</th><th>بیشترین</th><th>وضعیت</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . e($r['name']) . '</td>';
        echo '<td>' . e(fa_num((int) $r['count'])) . '</td>';
        echo '<td>' . e($r['avg'] === null ? '—' : fa_num(round($r['avg'], 1))) . '</td>';
        echo '<td>' . e($r['min'] === null ? '—' : analytics_reports_fmt_value($r['min'])) . '</td>';
        echo '<td>' . e($r['max'] === null ? '—' : analytics_reports_fmt_value($r['max'])) . '</td>';
        echo '<td>' . ($r['status'] === 'ok' ? '✓' : 'داده کافی نیست.') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function analytics_reports_multi_table($series_list)
{
    // جدول همیشه مقدار خام + مقدار نموداری ۰..۱۰۰ (حکم §۶ R2)
    echo '<table class="ar-tbl">';
    echo '<thead><tr><th>تاریخ شمسی</th><th>شاخص</th><th>مقدار خام</th><th>مقدار نموداری ۰ تا ۱۰۰</th></tr></thead><tbody>';
    foreach ($series_list as $s) {
        if (!$s['has_data']) continue;
        foreach ($s['points'] as $p) {
            $date_label = function_exists('jalali_format') ? jalali_format($p['jalali_date']) : $p['jalali_date'];
            $norm = (is_array($p) && array_key_exists('normalized', $p)) ? $p['normalized'] : null;
            echo '<tr>';
            echo '<td>' . e($date_label) . '</td>';
            echo '<td>' . e(isset($s['label']) ? $s['label'] : '') . '</td>';
            echo '<td>' . e(analytics_reports_fmt_value($p['value'])) . '</td>';
            echo '<td>' . e($norm === null ? '—' : fa_num(round((float) $norm, 1))) . '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table>';
}

function hammasir_report_render_r2($state)
{
    // رندر نتیجه‌ی تب‌های R2 (فرم مشترک در analytics_reports_render رندر شده است)
    $uid = (int) $state['uid'];
    $clean = $state['clean'];
    $bounds = $state['bounds'];
    $activity_options = $state['activity_options'];
    $errors = $state['errors'];
    $period_label = isset($state['period_label']) ? $state['period_label'] : '';
    $tab = (string) $clean['report_tab'];
    $from = (string) $bounds['from'];
    $to = (string) $bounds['to'];
    $activity_id = (int) $clean['activity_id'];

    // آیا شاخص عملکردِ وابسته به فعالیت انتخاب شده؟ (soft-sync مثل R1)
    $needs_activity = false;
    $metric_ids_used = array();
    if ($tab === 'correlation') {
        $metric_ids_used = array($clean['metric_a'], $clean['metric_b']);
    } elseif ($tab === 'weekday') {
        $metric_ids_used = array($clean['metric_single']);
    } else {
        $metric_ids_used = is_array($clean['metrics']) ? $clean['metrics'] : array();
    }
    $needs_activity = in_array('perf_activity', $metric_ids_used, true);
    if ($needs_activity) {
        if (count($activity_options) === 0) {
            echo '<p class="lede">' . e('فعالیتی در این دوره نیست') . '</p>';
            return;
        }
        $owned = false;
        foreach ($activity_options as $ao) {
            if ((int) $ao['plan_activity_id'] === $activity_id) { $owned = true; break; }
        }
        if (!$owned) $activity_id = (int) $activity_options[0]['plan_activity_id'];
    }

    if ($tab === 'correlation') {
        $series = hammasir_report_series($uid, $uid, null, $from, $to, $metric_ids_used, $activity_id);
        if (!is_array($series) || count($series) < 2) {
            echo '<p class="lede">' . e($errors['unavailable']) . '</p>';
            return;
        }
        $corr = hammasir_report_correlation($series[0], $series[1]);
        if ($corr['error'] !== '') {
            echo '<p class="lede">' . e($errors['corr_insufficient']) . '</p>';
            return;
        }
        $dto = hammasir_report_dto($bounds, $series, array('tab' => 'correlation', 'period' => $period_label));
        echo '<div class="ar-card">';
        echo '<h3 style="margin:0 0 4px;">' . e($series[0]['label']) . ' × ' . e($series[1]['label']) . ' <small style="color:#888;">(' . e($period_label) . ')</small></h3>';
        echo '<div class="ar-chart"><canvas id="ar-ch-corr"></canvas></div>';
        $specs = array(array(
            'id' => 'ar-ch-corr',
            'mode' => 'scatter',
            'x_title' => $series[0]['label'],
            'y_title' => $series[1]['label'],
            'a_si' => 0,
            'b_si' => 1,
            'color' => '#7C6AA8',
        ));
        analytics_reports_chart_script($dto, $specs);
        echo '<p class="ar-note">ضریب همبستگی: ' . e(fa_num(number_format($corr['r'], 2, '.', ''))) . ' — ' . e($corr['label']) . ' (' . e(fa_num((int) $corr['sample_count'])) . ' زوج داده)</p>';
        echo '<p class="ar-note">همبستگی به معنای رابطه علت و معلولی نیست.</p>';
        foreach (analytics_reports_direction_notes($metric_ids_used) as $note) {
            echo '<p class="ar-note">' . e($note) . '</p>';
        }
        analytics_reports_corr_table($corr, $series[0]['label'], $series[1]['label']);
        echo '</div>';
        return;
    }

    if ($tab === 'weekday') {
        $series = hammasir_report_series($uid, $uid, null, $from, $to, $metric_ids_used, $activity_id);
        if (!is_array($series) || count($series) < 1) {
            echo '<p class="lede">' . e($errors['unavailable']) . '</p>';
            return;
        }
        $s = $series[0];
        if (!$s['has_data']) {
            echo '<p class="lede">' . e($errors['empty']) . '</p>';
            return;
        }
        $rows = hammasir_report_weekday_averages($s);
        $dto = hammasir_report_dto($bounds, $series, array('tab' => 'weekday', 'period' => $period_label, 'weekday_rows' => $rows));
        echo '<div class="ar-card">';
        echo '<h3 style="margin:0 0 4px;">' . e($s['label']) . ' بر اساس روز هفته <small style="color:#888;">(' . e($period_label) . ')</small></h3>';
        echo '<div class="ar-chart"><canvas id="ar-ch-wd"></canvas></div>';
        $specs = array(array(
            'id' => 'ar-ch-wd',
            'mode' => 'bar',
            'rows_key' => 'weekday_rows',
            'label' => $s['label'],
            'y_title' => 'میانگین',
            'color' => '#4E9B94',
        ));
        analytics_reports_chart_script($dto, $specs);
        // بینش ساده (فقط توصیفی — بدون توصیه درمانی)
        $best = null;
        $worst = null;
        foreach ($rows as $r) {
            if ($r['status'] !== 'ok') continue;
            if ($best === null || $r['avg'] > $best['avg']) $best = $r;
            if ($worst === null || $r['avg'] < $worst['avg']) $worst = $r;
        }
        if ($best !== null) {
            echo '<p class="ar-note">بهترین میانگین در این بازه مربوط به ' . e($best['name']) . ' است.</p>';
        }
        if ($worst !== null) {
            echo '<p class="ar-note">کمترین میانگین در این بازه مربوط به ' . e($worst['name']) . ' است.</p>';
        }
        foreach (analytics_reports_direction_notes($metric_ids_used) as $note) {
            echo '<p class="ar-note">' . e($note) . '</p>';
        }
        analytics_reports_weekday_table($rows);
        echo '</div>';
        return;
    }

    // ---- ترکیب آزاد ----
    $series = hammasir_report_multi_index($uid, $uid, $from, $to, $metric_ids_used, $activity_id);
    if (!is_array($series) || count($series) === 0) {
        echo '<p class="lede">' . e($errors['unavailable']) . '</p>';
        return;
    }
    $any = false;
    foreach ($series as $s) {
        if ($s['has_data']) { $any = true; break; }
    }
    if (!$any) {
        echo '<p class="lede">' . e($errors['empty']) . '</p>';
        return;
    }
    $colors = array('#4E9B94', '#7C6AA8', '#C49A3C', '#2f7a52', '#C97B94');
    $norm_series = array();
    foreach ($series as $s) {
        $ns = hammasir_report_normalize_series($s);
        if ($ns !== null) $norm_series[] = $ns;
    }
    $has_null = false;
    foreach ($norm_series as $s) {
        if (!$s['has_data']) continue;
        foreach ($s['points'] as $p) {
            if ($p['value'] === null) { $has_null = true; break 2; }
        }
    }
    $dto = hammasir_report_dto($bounds, $norm_series, array('tab' => 'multi', 'period' => $period_label));
    echo '<div class="ar-card">';
    echo '<h3 style="margin:0 0 4px;">ترکیب آزاد <small style="color:#888;">(' . e($period_label) . ')</small></h3>';
    echo '<div class="ar-chart"><canvas id="ar-ch-multi"></canvas></div>';
    $specs_datasets = array();
    $ci = 0;
    foreach ($norm_series as $i => $s) {
        $legend = $s['label'] . ($s['has_data'] ? '' : ' (بدون داده)');
        $specs_datasets[] = array(
            'si' => $i,
            'label' => $legend,
            'axis' => 'y',
            'color' => $colors[$ci % 5],
            'field' => 'normalized',
            'span' => true,
        );
        $ci++;
    }
    $specs = array(array('id' => 'ar-ch-multi', 'mode' => 'multi', 'datasets' => $specs_datasets));
    analytics_reports_chart_script($dto, $specs);
    echo '<p class="ar-note">برای مقایسه بصری، مقادیر این نمودار به مقیاس ۰ تا ۱۰۰ تبدیل شده‌اند. مقدار خام در جدول زیر نمایش داده شده است.</p>';
    if ($has_null) {
        echo '<p class="ar-note">روزهایی که داده‌ای ثبت نشده، در جدول با — مشخص شده‌اند. خط نمودار فقط برای خوانایی روند به هم متصل شده است.</p>';
    }
    foreach (analytics_reports_direction_notes($metric_ids_used) as $note) {
        echo '<p class="ar-note">' . e($note) . '</p>';
    }
    analytics_reports_multi_table($norm_series);
    echo '</div>';
}

/* ------------------------------------------------------------------ */
/* رندر تب (فرم + جدول بدون JS + Chart.js v4.5.1 محلی)                 */
/* ------------------------------------------------------------------ */

function analytics_reports_fmt_value($v)
{
    if ($v === null) return '—';
    if (!function_exists('fa_num')) return (string) $v;
    if ((float) $v == 0.0) return fa_num(0);
    if ((float) $v == (float) (int) $v) return fa_num((int) $v);
    return fa_num(number_format((float) $v, 1, '.', ''));
}

function analytics_reports_month_options()
{
    // ۶ ماه اخیر (سرورساخته) — بدون هیچ خواندن/ساختن period (بدون mutation)
    $out = array();
    $today = analytics_reports_today();
    if ($today === null) return $out;
    $yy = (int) substr($today, 0, 4);
    $mm = (int) substr($today, 5, 2);
    for ($i = 0; $i < 6; $i++) {
        $key = $yy . '-' . (($mm < 10) ? '0' . $mm : (string) $mm);
        $label = function_exists('jalali_period_label') ? jalali_period_label($key) : $key;
        $out[] = array('key' => $key, 'label' => $label);
        $mm--;
        if ($mm < 1) {
            $mm = 12;
            $yy--;
        }
    }
    return $out;
}

function analytics_reports_style_once()
{
    static $done = false;
    if ($done) return;
    $done = true;
    echo '<style>';
    echo '.ar-card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);padding:20px;margin-bottom:16px;}';
    echo '.ar-chart{position:relative;height:260px;max-width:100%;}';
    echo '.ar-tbl{width:100%;border-collapse:collapse;font-size:12px;margin-top:10px;}';
    echo '.ar-tbl th,.ar-tbl td{padding:4px 8px;border-bottom:1px solid #f1ece4;text-align:right;}';
    echo '.ar-tbl th{color:#888;font-weight:600;}';
    echo '.ar-note{font-size:12px;color:#888;margin:6px 0 0;}';
    echo '</style>';
}

function analytics_reports_series_table($series)
{
    // جدول HTML بدون JS — تاریخ شمسی | نام شاخص | مقدار | واحد (§۹ حکم)
    if (!is_array($series) || !isset($series['points']) || !is_array($series['points'])) return;
    echo '<table class="ar-tbl">';
    echo '<thead><tr><th>تاریخ شمسی</th><th>نام شاخص</th><th>مقدار</th><th>واحد</th></tr></thead><tbody>';
    foreach ($series['points'] as $p) {
        $date_label = function_exists('jalali_format') ? jalali_format($p['jalali_date']) : $p['jalali_date'];
        echo '<tr>';
        echo '<td>' . e($date_label) . '</td>';
        echo '<td>' . e(isset($series['label']) ? $series['label'] : '') . '</td>';
        echo '<td>' . e(analytics_reports_fmt_value($p['value'])) . '</td>';
        echo '<td>' . e(isset($series['unit']) ? $series['unit'] : '') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function analytics_reports_chart_script($dto, $canvas_specs)
{
    // Chart.js v4.5.1 (نسخهٔ واقعی فایل محلی) — الگوی includes/analytics_view.php.
    // $canvas_specs: آرایه‌ای از {id, mode:'single'|'dual'|'mood'|'multi'|'scatter'|'bar', ...}
    //   line (single/dual/mood/multi): datasets:[{si,label,axis,color,field?,span?}]
    //   scatter: {a_si,b_si,x_title,y_title,color} — فقط زوج‌های غیر null
    //   bar: {rows_key,label,y_title,color} — از d.meta (میانگین روزهای هفته)
    if (!is_array($canvas_specs) || count($canvas_specs) === 0) return;
    if (!is_array($dto) || !isset($dto['series'])) return;
    $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
    $json = json_encode($dto, $flags);
    $specs_json = json_encode($canvas_specs, $flags);
    if ($json === false || $specs_json === false) return;
    if (function_exists('analytics_chartjs_tag')) {
        echo analytics_chartjs_tag();
    } else {
        echo '<script src="' . e(joma_url('assets/js/chart.min.js')) . '"></script>';
    }
    echo '<script>' . "\n";
    echo '(function () {' . "\n";
    echo '  if (typeof Chart === "undefined") { return; }' . "\n";
    echo '  var d = ' . $json . ';' . "\n";
    echo '  var specs = ' . $specs_json . ';' . "\n";
    echo '  function vals(si, field) {' . "\n";
    echo '    var out = [];' . "\n";
    echo '    var pts = (d.series[si] && d.series[si].points) ? d.series[si].points : [];' . "\n";
    echo '    for (var i = 0; i < pts.length; i++) {' . "\n";
    echo '      var v = pts[i][field];' . "\n";
    echo '      out.push((v === null || typeof v === "undefined") ? null : v);' . "\n";
    echo '    }' . "\n";
    echo '    return out;' . "\n";
    echo '  }' . "\n";
    echo '  for (var c = 0; c < specs.length; c++) {' . "\n";
    echo '    var spec = specs[c];' . "\n";
    echo '    var el = document.getElementById(spec.id);' . "\n";
    echo '    if (!el) { continue; }' . "\n";
    echo '    try {' . "\n";
    echo '      if (typeof Chart.getChart === "function") { var old = Chart.getChart(el); if (old) { old.destroy(); } }' . "\n";
    echo '      if (spec.mode === "scatter") {' . "\n";
    echo '        var pa = (d.series[spec.a_si] && d.series[spec.a_si].points) ? d.series[spec.a_si].points : [];' . "\n";
    echo '        var pb = (d.series[spec.b_si] && d.series[spec.b_si].points) ? d.series[spec.b_si].points : [];' . "\n";
    echo '        var pts = [];' . "\n";
    echo '        for (var i = 0; i < pa.length && i < pb.length; i++) {' . "\n";
    echo '          if (pa[i].value === null || pb[i].value === null || typeof pa[i].value === "undefined" || typeof pb[i].value === "undefined") { continue; }' . "\n";
    echo '          pts.push({ x: pa[i].value, y: pb[i].value });' . "\n";
    echo '        }' . "\n";
    echo '        new Chart(el, { type: "scatter", data: { datasets: [{ label: spec.x_title + " / " + spec.y_title, data: pts, backgroundColor: spec.color, pointRadius: 4 }] },' . "\n";
    echo '          options: { responsive: true, maintainAspectRatio: false, resizeDelay: 80,' . "\n";
    echo '            scales: { x: { position: "right", title: { display: true, text: spec.x_title } }, y: { position: "right", title: { display: true, text: spec.y_title } } },' . "\n";
    echo '            plugins: { legend: { position: "bottom", labels: { boxWidth: 10, font: { size: 11 } } }, tooltip: { callbacks: { label: function (c) { return spec.x_title + ": " + c.parsed.x + " / " + spec.y_title + ": " + c.parsed.y; } } } } } });' . "\n";
    echo '      } else if (spec.mode === "bar") {' . "\n";
    echo '        var rows = (d.meta && d.meta[spec.rows_key]) ? d.meta[spec.rows_key] : [];' . "\n";
    echo '        var labels = [];' . "\n";
    echo '        var data = [];' . "\n";
    echo '        for (var i = 0; i < rows.length; i++) { labels.push(rows[i].name); data.push((rows[i].avg === null || typeof rows[i].avg === "undefined") ? null : rows[i].avg); }' . "\n";
    echo '        new Chart(el, { type: "bar", data: { labels: labels, datasets: [{ label: spec.label, data: data, backgroundColor: spec.color, borderRadius: 6 }] },' . "\n";
    echo '          options: { responsive: true, maintainAspectRatio: false, resizeDelay: 80,' . "\n";
    echo '            scales: { y: { position: "right", beginAtZero: true, title: { display: true, text: spec.y_title } }, x: { grid: { display: false } } },' . "\n";
    echo '            plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return (c.parsed.y === null || typeof c.parsed.y === "undefined") ? "\u2014" : String(c.parsed.y); } } } } } });' . "\n";
    echo '      } else {' . "\n";
    echo '        var sets = [];' . "\n";
    echo '        for (var i = 0; i < spec.datasets.length; i++) {' . "\n";
    echo '          var ds = spec.datasets[i];' . "\n";
    echo '          var field = ds.field || "value";' . "\n";
    echo '          var span = (typeof ds.span === "boolean") ? ds.span : false;' . "\n";
    echo '          sets.push({ type: "line", label: ds.label, data: vals(ds.si, field), borderColor: ds.color, backgroundColor: ds.color, yAxisID: ds.axis, tension: 0.35, pointRadius: 2, borderWidth: 2, fill: false, spanGaps: span });' . "\n";
    echo '        }' . "\n";
    echo '        var scales = { x: { grid: { display: false }, ticks: { maxTicksLimit: 12, font: { size: 10 } } } };' . "\n";
    echo '        if (spec.mode === "dual") {' . "\n";
    echo '          scales.y = { position: "left", title: { display: true, text: spec.left } };' . "\n";
    echo '          scales.y1 = { position: "right", min: 1, max: 5, title: { display: true, text: spec.right }, grid: { display: false }, ticks: { stepSize: 1 } };' . "\n";
    echo '        } else if (spec.mode === "mood") {' . "\n";
    echo '          scales.y = { position: "right", min: 1, max: 5, title: { display: true, text: spec.right }, ticks: { stepSize: 1 } };' . "\n";
    echo '        } else if (spec.mode === "multi") {' . "\n";
    echo '          scales.y = { position: "right", min: 0, max: 100, title: { display: true, text: "0-100" }, ticks: { stepSize: 25 } };' . "\n";
    echo '        } else {' . "\n";
    echo '          scales.y = { position: "left", title: { display: true, text: spec.left } };' . "\n";
    echo '        }' . "\n";
    echo '        new Chart(el, { type: "line", data: { labels: d.labels, datasets: sets },' . "\n";
    echo '          options: { responsive: true, maintainAspectRatio: false, resizeDelay: 80, interaction: { mode: "index", intersect: false }, scales: scales,' . "\n";
    echo '            plugins: { legend: { position: "bottom", labels: { boxWidth: 10, font: { size: 11 } } }, tooltip: { callbacks: { label: function (c) { return c.dataset.label + ": " + ((c.parsed.y === null || typeof c.parsed.y === "undefined") ? "\u2014" : c.parsed.y); } } } } } });' . "\n";
    echo '      }' . "\n";
    echo '    } catch (err) { if (window.console && console.error) { console.error("analytics chart", spec.id, err); } }' . "\n";
    echo '  }' . "\n";
    echo '})();' . "\n";
    echo '</script>' . "\n";
}

function analytics_reports_render($u)
{
    analytics_reports_style_once();
    // عنوان تب (حکم tabfix — بند ۳ مورد ۱: عنوان «گزارش تحلیلی» دیده شود)
    echo '<h2 style="margin:0 0 4px;">گزارش تحلیلی</h2>';
    $errors = analytics_reports_error_messages();
    if (!is_array($u) || !isset($u['id']) || (int) $u['id'] <= 0) {
        echo '<p class="lede">' . e($errors['unavailable']) . '</p>';
        return;
    }
    $uid = (int) $u['id'];

    // ---------- ورودی POST (فیلتر خواندنی؛ بدون mutation) ----------
    $submitted = false;
    $post = array();
    $csrf_failed = false;
    if (isset($_POST['report_action']) && $_POST['report_action'] === 'analytics_r1' && is_array($_POST)) {
        // CSRF (هم‌ارز csrf_check هسته — همان مقایسه؛ بدون die وسط صفحه؛ پیام ثابت)
        $csrf_post = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
        if ($csrf_post === '' || !isset($_SESSION['csrf']) || $csrf_post !== (string) $_SESSION['csrf']) {
            $submitted = false;
            $post = array();
            $csrf_failed = true;
        } else {
            $post = $_POST;
            // گزینه‌ی اختیاری overlay فقط با تیک «نمایش خلق روی این نمودار» فعال است
            if (!isset($post['overlay_on']) || (int) $post['overlay_on'] !== 1) {
                $post['overlay_mood_metric'] = '';
            }
            $submitted = true;
        }
    }
    // (R2) پیش‌نمایش فرم: preview=1 (دکمه‌ی «به‌روزرسانی فرم» یا onchange نوع گزارش)
    $is_preview = false;
    if ($submitted && isset($_POST['preview']) && $_POST['preview'] === '1') {
        $is_preview = true;
    }
    // (R2) تب‌های جدید اعتبارسنج مخصوص خودشان را دارند (حکم §۷)
    $raw_tab = (is_array($post) && isset($post['report_tab'])) ? (string) $post['report_tab'] : 'activity';
    if ($raw_tab === 'correlation' || $raw_tab === 'weekday' || $raw_tab === 'multi') {
        $v = hammasir_report_validate_r2_request($post);
    } else {
        $v = hammasir_report_validate_request($post);
    }
    $clean = $v['clean'];

    // (fieldfix) همگام‌سازی نرم هفته: preset=هفتهٔ ماه ولی هفته‌ی انتخابی معتبر
    // نیست/انتخاب نشده → به‌جای خطای سخت، هفتهٔ اول همان ماه انتخاب می‌شود.
    if ($clean['period_preset'] === 'month_week') {
        $__ymw = $clean['year_month'];
        if (!preg_match('/^\d{4}-\d{2}$/', (string) $__ymw)) {
            $__tw = analytics_reports_today();
            $__ymw = ($__tw !== null) ? substr($__tw, 0, 7) : '';
        }
        if ($__ymw !== '') {
            $__wks = report_weeks_in_month($__ymw);
            $__wk_ok = false;
            foreach ($__wks as $__w) {
                if ((int) $__w['index'] === (int) $clean['week_index']) { $__wk_ok = true; break; }
            }
            if (!$__wk_ok && count($__wks) > 0) {
                $clean['week_index'] = (int) $__wks[0]['index'];
            }
            if ($clean['year_month'] === '') {
                $clean['year_month'] = $__ymw;
            }
        }
        unset($__ymw, $__tw, $__wks, $__wk_ok, $__w);
    }

    // بازه (سرورساخته) — پیش‌فرض: ۴ هفتهٔ اخیر
    $bounds = hammasir_report_period_bounds($clean['period_preset'], $clean['year_month'], $clean['week_index']);
    $error_code = '';
    if ($csrf_failed) {
        $error_code = 'unavailable';
    } elseif ($submitted && !$v['ok']) {
        $error_code = $v['error'];
    } elseif (!$bounds['ok']) {
        $error_code = ($bounds['error'] !== '') ? $bounds['error'] : 'type';
    }
    // (R2) پیش‌نمایش: هیچ خطای فعالیت/داده‌ی کافی نشان نمی‌دهد؛ فقط فرم وابسته
    if ($is_preview) {
        $error_code = '';
        if (!$bounds['ok']) {
            $clean['period_preset'] = 'last_4_weeks';
            $clean['week_index'] = 0;
            $bounds = hammasir_report_period_bounds('last_4_weeks', '', 0);
        }
        if (!$v['ok']) {
            $__ropts = analytics_reports_r2_metric_options();
            if (isset($clean['metric_a']) && !isset($__ropts[$clean['metric_a']])) $clean['metric_a'] = 'energy';
            if (isset($clean['metric_b']) && !isset($__ropts[$clean['metric_b']])) $clean['metric_b'] = 'general_mood';
            if (isset($clean['metric_single']) && !isset($__ropts[$clean['metric_single']])) $clean['metric_single'] = 'energy';
            if (isset($clean['metrics']) && is_array($clean['metrics'])) {
                $__mlist = array();
                foreach ($clean['metrics'] as $__mk) {
                    if (isset($__ropts[(string) $__mk])) $__mlist[] = (string) $__mk;
                }
                $clean['metrics'] = $__mlist;
                unset($__mlist);
            }
            unset($__ropts);
        }
    }

    // ماه برای select هفته‌ها (انتخاب جاری یا ماه جاری)
    $ym_for_weeks = $clean['year_month'];
    if (!preg_match('/^\d{4}-\d{2}$/', (string) $ym_for_weeks)) {
        $today = analytics_reports_today();
        $ym_for_weeks = ($today !== null) ? substr($today, 0, 7) : '';
    }
    $weeks_for_select = ($ym_for_weeks !== '') ? report_weeks_in_month($ym_for_weeks) : array();

    // گزینه‌های فعالیت روی بازهٔ جاری (مالکیت کاربر جاری)
    $activity_options = array();
    if ($bounds['ok']) {
        try {
            $activity_options = hammasir_activity_options($uid, $bounds['from'], $bounds['to']);
        } catch (Throwable $e) {
            $activity_options = array();
        }
    }

    // (R2) آیا این تب به انتخاب فعالیت نیاز دارد؟
    $__tab_needs_activity = false;
    if ($clean['report_tab'] === 'activity') {
        $__tab_needs_activity = true;
    } elseif ($clean['report_tab'] === 'correlation') {
        $__tab_needs_activity = (isset($clean['metric_a']) && $clean['metric_a'] === 'perf_activity') || (isset($clean['metric_b']) && $clean['metric_b'] === 'perf_activity');
    } elseif ($clean['report_tab'] === 'weekday') {
        $__tab_needs_activity = (isset($clean['metric_single']) && $clean['metric_single'] === 'perf_activity');
    } elseif ($clean['report_tab'] === 'multi') {
        $__tab_needs_activity = (isset($clean['metrics']) && is_array($clean['metrics']) && in_array('perf_activity', $clean['metrics'], true));
    }

    // (fieldfix) همگام‌سازی نرم فعالیت: اگر فعالیتِ انتخاب‌شده به بازه/تبِ جدید
    // تعلق ندارد (مثلاً بعد از تغییر ماه)، به‌جای خطای سخت، نخستین فعالیتِ معتبرِ
    // همان بازه انتخاب می‌شود تا فیلدها با یک بار ارسال به‌روز شوند.
    if ($bounds['ok'] && $__tab_needs_activity && (int) $clean['activity_id'] > 0) {
        $__owned_now = false;
        foreach ($activity_options as $__ao) {
            if ((int) $__ao['plan_activity_id'] === (int) $clean['activity_id']) { $__owned_now = true; break; }
        }
        if (!$__owned_now && count($activity_options) > 0) {
            $clean['activity_id'] = (int) $activity_options[0]['plan_activity_id'];
        }
        unset($__owned_now, $__ao);
    }

    // ---------- فرم (بدون JS کار می‌کند — PC-1) ----------
    $action_url = joma_url('index.php?p=reports&tab=analytics');
    echo '<form method="post" action="' . e($action_url) . '" class="card" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">';
    echo '<input type="hidden" name="report_action" value="analytics_r1">';
    echo '<input type="hidden" name="preview" value="0">';
    echo csrf_field();
    echo (function_exists('joma_sid_field') ? joma_sid_field() : '');
    // نوع گزارش
    // (R2) تغییر نوع گزارش = پیش‌نمایش فرم (preview=1) — بدون تولید گزارش/خطا
    echo '<label>نوع گزارش<select name="report_tab" onchange="this.form.preview.value=&#39;1&#39;;this.form.submit();">';
    echo '<option value="activity"' . ($clean['report_tab'] === 'activity' ? ' selected' : '') . '>فعالیت‌ها</option>';
    echo '<option value="mood"' . ($clean['report_tab'] === 'mood' ? ' selected' : '') . '>خلق</option>';
    echo '<option value="correlation"' . ($clean['report_tab'] === 'correlation' ? ' selected' : '') . '>همبستگی</option>';
    echo '<option value="weekday"' . ($clean['report_tab'] === 'weekday' ? ' selected' : '') . '>روزهای هفته</option>';
    echo '<option value="multi"' . ($clean['report_tab'] === 'multi' ? ' selected' : '') . '>ترکیب آزاد</option>';
    echo '</select></label>';
    // دوره
    echo '<label>دوره<select name="period_preset" onchange="this.form.submit()">';
    echo '<option value="month"' . ($clean['period_preset'] === 'month' ? ' selected' : '') . '>ماه</option>';
    echo '<option value="month_week"' . ($clean['period_preset'] === 'month_week' ? ' selected' : '') . '>هفتهٔ ماه</option>';
    echo '<option value="last_4_weeks"' . ($clean['period_preset'] === 'last_4_weeks' ? ' selected' : '') . '>۴ هفتهٔ اخیر</option>';
    echo '</select></label>';
    // ماه
    echo '<label>ماه<select name="year_month" onchange="this.form.submit()">';
    $month_opts = analytics_reports_month_options();
    if (count($month_opts) === 0 && $ym_for_weeks !== '') {
        $month_opts = array(array('key' => $ym_for_weeks, 'label' => $ym_for_weeks));
    }
    $ym_selected = $clean['year_month'];
    if ($ym_selected === '' && count($month_opts) > 0) {
        $ym_selected = $month_opts[0]['key'];
    }
    foreach ($month_opts as $mo) {
        echo '<option value="' . e($mo['key']) . '"' . ($mo['key'] === $ym_selected ? ' selected' : '') . '>' . e($mo['label']) . '</option>';
    }
    echo '</select></label>';
    // هفته (از خروجی واقعی report_weeks_in_month — با نشان «(ناقص)»)
    echo '<label>هفته<select name="week_index" onchange="this.form.submit()">';
    echo '<option value="0">—</option>';
    foreach ($weeks_for_select as $w) {
        echo '<option value="' . (int) $w['index'] . '"' . ((int) $clean['week_index'] === (int) $w['index'] ? ' selected' : '') . '>' . e($w['label']) . '</option>';
    }
    echo '</select></label>';
    // فعالیت (تب فعالیت‌ها + هر شاخص R2 که perf_activity دارد)
    if ($__tab_needs_activity) {
        echo '<label>فعالیت<select name="activity_id">';
        if (count($activity_options) === 0) {
            echo '<option value="0">فعالیتی در این دوره نیست</option>';
        }
        foreach ($activity_options as $ao) {
            echo '<option value="' . (int) $ao['plan_activity_id'] . '"' . ((int) $clean['activity_id'] === (int) $ao['plan_activity_id'] ? ' selected' : '') . '>' . e($ao['title']) . '</option>';
        }
        echo '</select></label>';
    }
    if ($clean['report_tab'] === 'activity') {
        // گزینه‌ی اختیاری: نمایش خلق روی همین نمودار (overlay)
        $mood_metrics = analytics_reports_mood_metrics();
        echo '<label style="display:flex;flex-direction:column;gap:4px;"><span><input type="checkbox" name="overlay_on" value="1"' . ($clean['overlay_mood_metric'] !== '' ? ' checked' : '') . '> نمایش خلق روی این نمودار</span>';
        echo '<select name="overlay_mood_metric">';
        echo '<option value="">—</option>';
        foreach ($mood_metrics as $mk => $mm) {
            echo '<option value="' . e($mk) . '"' . ($clean['overlay_mood_metric'] === $mk ? ' selected' : '') . '>' . e($mm['label']) . '</option>';
        }
        echo '</select></label>';
    }
    // (R2) فیلدهای خاص هر تب
    $__r2opts = analytics_reports_r2_metric_options();
    if ($clean['report_tab'] === 'correlation') {
        echo '<label>شاخص اول<select name="metric_a">';
        $__sel_a = isset($clean['metric_a']) ? (string) $clean['metric_a'] : 'energy';
        foreach ($__r2opts as $__k => $__lbl) {
            echo '<option value="' . e($__k) . '"' . ($__sel_a === (string) $__k ? ' selected' : '') . '>' . e($__lbl) . '</option>';
        }
        echo '</select></label>';
        echo '<label>شاخص دوم<select name="metric_b">';
        $__sel_b = isset($clean['metric_b']) ? (string) $clean['metric_b'] : 'general_mood';
        foreach ($__r2opts as $__k => $__lbl) {
            echo '<option value="' . e($__k) . '"' . ($__sel_b === (string) $__k ? ' selected' : '') . '>' . e($__lbl) . '</option>';
        }
        echo '</select></label>';
        unset($__sel_a, $__sel_b, $__k, $__lbl);
    } elseif ($clean['report_tab'] === 'weekday') {
        echo '<label>شاخص<select name="metric_single">';
        $__sel_s = isset($clean['metric_single']) ? (string) $clean['metric_single'] : 'energy';
        foreach ($__r2opts as $__k => $__lbl) {
            echo '<option value="' . e($__k) . '"' . ($__sel_s === (string) $__k ? ' selected' : '') . '>' . e($__lbl) . '</option>';
        }
        echo '</select></label>';
        unset($__sel_s, $__k, $__lbl);
    } elseif ($clean['report_tab'] === 'multi') {
        echo '<fieldset style="display:flex;flex-direction:column;gap:4px;border:none;padding:0;margin:0;">';
        echo '<legend style="font-size:12px;color:#888;">شاخص‌ها</legend>';
        $__sel_m = (isset($clean['metrics']) && is_array($clean['metrics'])) ? $clean['metrics'] : array();
        foreach ($__r2opts as $__k => $__lbl) {
            echo '<label style="font-size:13px;"><input type="checkbox" name="metrics[]" value="' . e($__k) . '"' . (in_array((string) $__k, $__sel_m, true) ? ' checked' : '') . '> ' . e($__lbl) . '</label>';
        }
        echo '</fieldset>';
        unset($__sel_m, $__k, $__lbl);
    }
    unset($__r2opts);
    $__primary = 'نمایش گزارش';
    if ($clean['report_tab'] === 'correlation') {
        $__primary = 'نمایش همبستگی';
    } elseif ($clean['report_tab'] === 'weekday') {
        $__primary = 'نمایش روزهای هفته';
    } elseif ($clean['report_tab'] === 'multi') {
        $__primary = 'نمایش ترکیب';
    }
    echo '<button class="btn" type="submit">' . e($__primary) . '</button>';
    echo '<button class="btn sec" type="submit" name="preview" value="1">به‌روزرسانی فرم</button>';
    echo '</form>';

    // (R2) پیش‌نمایش: فقط فرمِ به‌روزشده — بدون گزارش و بدون خطا
    if ($is_preview) {
        return;
    }

    // ---------- خطا (پیام ثابت) ----------
    if ($error_code !== '') {
        $msg = isset($errors[$error_code]) ? $errors[$error_code] : $errors['type'];
        echo '<p class="lede">' . e($msg) . '</p>';
        return;
    }

    // فرود بدون ارسال (GET): فقط فرم — نتایج تنها با «نمایش گزارش»
    if (!$submitted) {
        return;
    }

    $from = $bounds['from'];
    $to = $bounds['to'];

    // برچسب دوره
    $period_label = '۴ هفتهٔ اخیر';
    if ($clean['period_preset'] === 'month' && preg_match('/^\d{4}-\d{2}$/', (string) $clean['year_month'])) {
        $period_label = function_exists('jalali_period_label') ? jalali_period_label($clean['year_month']) : $clean['year_month'];
    } elseif ($clean['period_preset'] === 'month_week') {
        $period_label = (function_exists('jalali_period_label') ? jalali_period_label($ym_for_weeks) : $ym_for_weeks) . (($bounds['week_label'] !== '') ? ' · ' . $bounds['week_label'] : '');
    }

    try {
        if ($clean['report_tab'] === 'correlation' || $clean['report_tab'] === 'weekday' || $clean['report_tab'] === 'multi') {
            hammasir_report_render_r2(array(
                'uid' => $uid,
                'clean' => $clean,
                'bounds' => $bounds,
                'activity_options' => $activity_options,
                'errors' => $errors,
                'period_label' => $period_label,
            ));
            return;
        }
        if ($clean['report_tab'] === 'activity') {
            // ---------- تب فعالیت‌ها ----------
            if (count($activity_options) === 0) {
                // این دوره فعالیتی ندارد — فرم بالا رندر شده؛ بدون خطای سخت
                echo '<p class="lede">' . e('فعالیتی در این دوره نیست') . '</p>';
                return;
            }
            $pa_title = '';
            foreach ($activity_options as $ao) {
                if ((int) $ao['plan_activity_id'] === (int) $clean['activity_id']) {
                    $pa_title = $ao['title'];
                    break;
                }
            }
            if ($pa_title === '') {
                // ایمنی دوچندان: همیشه یک فعالیتِ معتبر انتخاب می‌شود
                $clean['activity_id'] = (int) $activity_options[0]['plan_activity_id'];
                $pa_title = $activity_options[0]['title'];
            }
            $metric_ids = array('perf_activity');
            if ($clean['overlay_mood_metric'] !== '') {
                $metric_ids[] = $clean['overlay_mood_metric'];
            }
            $series = hammasir_report_series($uid, $uid, null, $from, $to, $metric_ids, $clean['activity_id']);
            if ($series === null || count($series) === 0) {
                echo '<p class="lede">' . e($errors['unavailable']) . '</p>';
                return;
            }
            $perf = $series[0];
            $mood_series = null;
            if (count($series) > 1) {
                $mood_series = $series[1];
            }
            $dto = hammasir_report_dto($bounds, $series, array('tab' => 'activity', 'period' => $period_label));
            echo '<div class="ar-card">';
            echo '<h3 style="margin:0 0 4px;">' . e($pa_title) . ' <small style="color:#888;">(' . e($period_label) . ')</small></h3>';
            if ($perf['has_data']) {
                $overlay_struct = null;
                if ($mood_series !== null) {
                    $overlay_struct = hammasir_report_overlay($perf, $mood_series);
                }
                echo '<div class="ar-chart"><canvas id="ar-ch-act"></canvas></div>';
                $left_title = 'مقدار ثبت‌شده' . (($perf['unit'] !== '') ? ' (' . $perf['unit'] . ')' : '');
                $specs = array();
                if ($overlay_struct !== null) {
                    $mood_color = ($mood_series['id'] === 'stress') ? '#C97B94' : '#7C6AA8';
                    $specs[] = array(
                        'id' => 'ar-ch-act',
                        'mode' => 'dual',
                        'left' => $left_title,
                        'right' => $mood_series['label'] . ' (۱..۵)',
                        'datasets' => array(
                            array('si' => 0, 'label' => $perf['label'], 'axis' => 'y', 'color' => '#4E9B94'),
                            array('si' => 1, 'label' => $mood_series['label'], 'axis' => 'y1', 'color' => $mood_color),
                        ),
                    );
                } else {
                    $specs[] = array(
                        'id' => 'ar-ch-act',
                        'mode' => 'single',
                        'left' => $left_title,
                        'datasets' => array(
                            array('si' => 0, 'label' => $perf['label'], 'axis' => 'y', 'color' => '#4E9B94'),
                        ),
                    );
                }
                analytics_reports_chart_script($dto, $specs);
                // فرمول معیار (یک جملهٔ کوتاه — §۶ حکم)
                echo '<p class="ar-note">مقدار هر نقطه: مجموع مقدارهای ثبت‌شده‌ی این فعالیت در آن روز (بدون ثبت = —).</p>';
                if ($mood_series !== null && $mood_series['id'] === 'stress') {
                    // جهت استرس خام — زیرنویس ثابت (§۷ حکم)
                    echo '<p class="ar-note">استرس به‌صورت مقدار ثبت‌شده نمایش داده می‌شود؛ مقدار بالاتر یعنی تنش بیشتر. (۱ = آرام‌ترین، ۵ = پرتنش‌ترین)</p>';
                }
            } else {
                // Empty State (§۲.۳ حکم — canvas ساخته نمی‌شود)
                echo '<p class="lede">' . e($errors['empty']) . '</p>';
            }
            // جدول بدون JS — همیشه
            analytics_reports_series_table($perf);
            if ($mood_series !== null && $mood_series['has_data']) {
                analytics_reports_series_table($mood_series);
            }
            echo '</div>';
        } else {
            // ---------- تب خلق — ۵ نمودار مستقل ----------
            $metric_ids = array();
            foreach (analytics_reports_mood_metrics() as $mk => $mm) {
                $metric_ids[] = $mk;
            }
            $series = hammasir_report_series($uid, $uid, null, $from, $to, $metric_ids, 0);
            if ($series === null || count($series) === 0) {
                echo '<p class="lede">' . e($errors['unavailable']) . '</p>';
                return;
            }
            $any = false;
            foreach ($series as $s) {
                if ($s['has_data']) { $any = true; break; }
            }
            if (!$any) {
                // Empty State سراسری (§۸ حکم)
                echo '<p class="lede">' . e($errors['empty']) . '</p>';
                return;
            }
            $dto = hammasir_report_dto($bounds, $series, array('tab' => 'mood', 'period' => $period_label));
            $specs = array();
            $si = 0;
            foreach ($series as $s) {
                $color = ($s['id'] === 'stress') ? '#C97B94' : '#7C6AA8';
                $legend = $s['label'] . ($s['has_data'] ? '' : ' (بدون داده)');
                echo '<div class="ar-card">';
                echo '<h3 style="margin:0 0 4px;">' . e($s['label']) . ' <small style="color:#888;">(' . e($period_label) . ')</small></h3>';
                if ($s['has_data']) {
                    echo '<div class="ar-chart"><canvas id="ar-ch-mood-' . e($s['id']) . '"></canvas></div>';
                    $specs[] = array(
                        'id' => 'ar-ch-mood-' . $s['id'],
                        'mode' => 'mood',
                        'right' => $s['label'] . ' (۱..۵)',
                        'datasets' => array(
                            array('si' => $si, 'label' => $legend, 'axis' => 'y', 'color' => $color),
                        ),
                    );
                    analytics_reports_series_table($s);
                } else {
                    // سری بدون داده: Empty State خودش — بدون canvas و بدون تبدیل null به صفر
                    echo '<p class="lede">' . e($errors['empty']) . '</p>';
                }
                if ($s['id'] === 'stress') {
                    // زیرنویس جهت مقیاس استرس (خام — §۸ حکم)
                    echo '<p class="ar-note">۱ = آرام‌ترین، ۵ = پرتنش‌ترین</p>';
                }
                echo '</div>';
                $si++;
            }
            analytics_reports_chart_script($dto, $specs);
        }
    } catch (Throwable $e) {
        // مهار کامل — گزارش فعلی صفحه سالم می‌ماند (D-1)
        error_log('analytics r1 render failed: fixed message.');
        echo '<p class="lede">' . e($errors['unavailable']) . '</p>';
        return;
    }
}
