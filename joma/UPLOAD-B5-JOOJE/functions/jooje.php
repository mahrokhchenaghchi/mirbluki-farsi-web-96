<?php
/**
 * JOMA — «جوجهٔ من» (B5 — مرحلهٔ ۱)
 * =========================================================================
 * قواعد ثابت این فایل (تصمیم مالک و سند فرانت):
 *   ۱) هیچ عددی جعل نمی‌شود. هرچه برمی‌گردد از ثبت واقعی خود کاربر است.
 *   ۲) سنجهٔ چهارم («وقت حمام / تمرین تنفس») تا ساخته‌شدن منبعش
 *      با available=false برمی‌گردد و نمایش داده نمی‌شود.
 *   ۳) آب: هر ثبت موجود ACT002 «قطعی» حساب می‌شود (تصمیم مالک — B5).
 *   ۴) جوجه نمی‌میرد، بیمار نمی‌شود و هرگز به تخم برنمی‌گردد؛
 *      کم‌رنگ/خاکستری فقط نشانهٔ «نیازمند توجه» است، بی‌عدد و بی‌سرزنش.
 *   ۵) تولد فقط یک‌بار. واحد (دونه) = تعداد ثبت معتبر عملکرد که هرگز
 *      حذف نمی‌شود (event log منبع حقیقت است)، پس مرحله هرگز عقب نمی‌رود.
 *   ۶) وضعیت جوجه با «هم‌مسیر» به اشتراک گذاشته نمی‌شود (sharing = private).
 *
 * خروجی این فایل یک آرایهٔ داده است (DTO)؛ هیچ تصمیم ظاهری در آن نیست.
 * =========================================================================
 */

function jooje_version() {
    return 'JOMA_JOOJE_V1';
}

/** تنظیمات جوجه (config/jooje_config.php) با پیش‌فرض‌های ایمن. */
function jooje_config() {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $cfg = array(
        'enabled' => true,
        'crack_units' => 6,
        'birth_units' => 12,
        'daily_water_fallback' => 0,
        'night_start_hour' => 22,
        'night_end_hour' => 6,
        'faded_days' => 3,
        'gray_days' => 6,
        'growth' => array(
            array('key' => 'small',     'label' => 'جوجهٔ کوچک', 'from_units' => 12),
            array('key' => 'fledgling', 'label' => 'نوپا',       'from_units' => 40),
            array('key' => 'adult',     'label' => 'بالغ',       'from_units' => 90),
        ),
    );
    $file = dirname(__FILE__) . '/../config/jooje_config.php';
    if (is_file($file)) {
        $JOOJE_CONFIG = array();
        include $file;
        if (is_array($JOOJE_CONFIG)) {
            foreach ($JOOJE_CONFIG as $k => $v) {
                $cfg[$k] = $v;
            }
        }
    }
    // پاک‌سازی: آستانه‌ها باید عدد درست و صعودی باشند (پیکربندی خراب = پیش‌فرض ایمن).
    $cfg['crack_units'] = max(0, (int) $cfg['crack_units']);
    $cfg['birth_units'] = max(0, (int) $cfg['birth_units']);
    if ($cfg['birth_units'] > 0 && $cfg['crack_units'] > $cfg['birth_units']) {
        $cfg['crack_units'] = $cfg['birth_units'];
    }
    $cfg['faded_days'] = max(1, (int) $cfg['faded_days']);
    $cfg['gray_days'] = max($cfg['faded_days'], (int) $cfg['gray_days']);
    $growth = array();
    if (is_array($cfg['growth'])) {
        foreach ($cfg['growth'] as $g) {
            if (!is_array($g) || !isset($g['key'])) continue;
            $growth[] = array(
                'key' => (string) $g['key'],
                'label' => isset($g['label']) ? (string) $g['label'] : (string) $g['key'],
                'from_units' => max(0, (int) (isset($g['from_units']) ? $g['from_units'] : 0)),
            );
        }
    }
    usort($growth, 'jooje_cmp_from_units');
    $cfg['growth'] = $growth;
    return $cfg;
}

function jooje_cmp_from_units($a, $b) {
    if ($a['from_units'] === $b['from_units']) return 0;
    return ($a['from_units'] < $b['from_units']) ? -1 : 1;
}

/* ------------------------------------------------------------------ */
/* تاریخ (جلالی)                                                       */
/* ------------------------------------------------------------------ */

/** تاریخ جلالی یک datetime میلادی ذخیره‌شده در دیتابیس؛ null اگر نامعتبر باشد. */
function jooje_jalali_of_datetime($datetime) {
    if (!$datetime) return null;
    $ts = strtotime((string) $datetime);
    if ($ts === false) return null;
    $j = gregorian_to_jalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
}

/** فاصلهٔ روز بین دو تاریخ جلالی (to - from)؛ null اگر ورودی نامعتبر باشد. */
function jooje_date_diff_days($from, $to) {
    $a = explode('-', (string) $from);
    $b = explode('-', (string) $to);
    if (count($a) !== 3 || count($b) !== 3) return null;
    if (!is_numeric($a[0]) || !is_numeric($b[0])) return null;
    $ga = jalali_to_gregorian((int) $a[0], (int) $a[1], (int) $a[2]);
    $gb = jalali_to_gregorian((int) $b[0], (int) $b[1], (int) $b[2]);
    $ta = mktime(0, 0, 0, (int) $ga[1], (int) $ga[2], (int) $ga[0]);
    $tb = mktime(0, 0, 0, (int) $gb[1], (int) $gb[2], (int) $gb[0]);
    if ($ta === false || $tb === false) return null;
    return (int) round(($tb - $ta) / 86400);
}

/* ------------------------------------------------------------------ */
/* خواندن دادهٔ خام کاربر (بدون فیلتر دوره — جوجه به کل مسیر نگاه می‌کند) */
/* ------------------------------------------------------------------ */

function jooje_event_cmp($a, $b) {
    $c = strcmp((string) $a['performance_date'], (string) $b['performance_date']);
    if ($c !== 0) return $c;
    $c = strcmp((string) $a['created_at'], (string) $b['created_at']);
    if ($c !== 0) return $c;
    return ((int) $a['id'] < (int) $b['id']) ? -1 : 1;
}

/**
 * همهٔ ثبت‌های عملکرد کاربر، به ترتیب زمانی، به‌همراه کد فعالیتی که
 * در لحظهٔ ثبت روی آن بوده (از اسنپ‌شات برنامه، نه کتابخانهٔ زنده).
 */
function jooje_event_rows($user_id) {
    $user_id = (int) $user_id;
    $rows = array();
    if (store_mode() === 'mysql') {
        $raw = joma_query(
            'SELECT e.id, e.user_id, e.plan_id, e.plan_activity_id, e.period_key, e.performance_date, e.actual_value, e.created_at,'
            . ' pa.activity_code, pa.unit, pa.name'
            . ' FROM joma_performance_events e'
            . ' LEFT JOIN joma_plan_activities pa ON pa.id = e.plan_activity_id'
            . ' WHERE e.user_id = ?'
            . ' ORDER BY e.performance_date, e.created_at, e.id',
            'i',
            array($user_id)
        );
        if (is_array($raw)) $rows = $raw;
    } else {
        $data = store_load();
        $map = array();
        foreach ($data['plan_activities'] as $pa) {
            $map[(int) $pa['id']] = array(
                'activity_code' => isset($pa['activity_code']) ? $pa['activity_code'] : '',
                'unit' => isset($pa['unit']) ? $pa['unit'] : '',
                'name' => isset($pa['name']) ? $pa['name'] : '',
            );
        }
        foreach ($data['events'] as $e) {
            if ((int) $e['user_id'] !== $user_id) continue;
            $pid = (int) $e['plan_activity_id'];
            $rows[] = array(
                'id' => isset($e['id']) ? $e['id'] : 0,
                'user_id' => $user_id,
                'plan_id' => isset($e['plan_id']) ? $e['plan_id'] : 0,
                'plan_activity_id' => $pid,
                'period_key' => isset($e['period_key']) ? $e['period_key'] : '',
                'performance_date' => isset($e['performance_date']) ? $e['performance_date'] : '',
                'actual_value' => isset($e['actual_value']) ? $e['actual_value'] : 0,
                'created_at' => isset($e['created_at']) ? $e['created_at'] : '',
                'activity_code' => isset($map[$pid]) ? $map[$pid]['activity_code'] : '',
                'unit' => isset($map[$pid]) ? $map[$pid]['unit'] : '',
                'name' => isset($map[$pid]) ? $map[$pid]['name'] : '',
            );
        }
        usort($rows, 'jooje_event_cmp');
    }
    foreach ($rows as $i => $r) {
        // «معتبر» = رکوردی که از مسیر ثبت سالم گذشته است؛ AMBIGUOUS_DUPLICATE فقط
        // در موتور موفقیت معنا دارد و اینجا دوباره قضاوت نمی‌شود.
        $rows[$i]['is_valid'] = true;
    }
    return $rows;
}

/** رکوردهای حال کاربر (جلالی)، صعودی. */
function jooje_mood_rows($user_id) {
    $user_id = (int) $user_id;
    $rows = array();
    if (store_mode() === 'mysql') {
        $raw = joma_query('SELECT id, jalali_date, created_at FROM joma_mood_records WHERE user_id = ? ORDER BY jalali_date', 'i', array($user_id));
        if (is_array($raw)) $rows = $raw;
    } else {
        $data = store_load();
        foreach ($data['moods'] as $m) {
            if ((int) $m['user_id'] !== $user_id) continue;
            $rows[] = array(
                'id' => isset($m['id']) ? $m['id'] : 0,
                'jalali_date' => isset($m['jalali_date']) ? $m['jalali_date'] : '',
                'created_at' => isset($m['created_at']) ? $m['created_at'] : '',
            );
        }
        usort($rows, 'jooje_mood_cmp');
    }
    return $rows;
}

function jooje_mood_cmp($a, $b) {
    return strcmp((string) $a['jalali_date'], (string) $b['jalali_date']);
}

/**
 * منبع سنجهٔ آب: فعالیت آب (ACT002) در آخرین برنامهٔ کاربر
 * (ترجیح: دورهٔ جاری). هدف از اسنپ‌شات همان برنامه می‌آید، نه از کتابخانهٔ زنده.
 */
function jooje_water_source($user_id) {
    $user_id = (int) $user_id;
    $out = array('has_source' => false, 'target' => null, 'unit' => 'UNIT_GLASS', 'name' => 'نوشیدن آب', 'period_key' => null);
    $current_key = function_exists('current_period_key') ? current_period_key() : null;
    $plans = array();
    if (store_mode() === 'mysql') {
        $raw = joma_query('SELECT id, period_key, status FROM joma_plans WHERE user_id = ? ORDER BY period_key DESC', 'i', array($user_id));
        if (is_array($raw)) $plans = $raw;
    } else {
        $data = store_load();
        foreach ($data['plans'] as $p) {
            if ((int) $p['user_id'] !== $user_id) continue;
            $plans[] = array('id' => $p['id'], 'period_key' => $p['period_key'], 'status' => $p['status']);
        }
        usort($plans, 'jooje_plan_cmp');
    }
    if (!$plans) return $out;
    // ترتیب بررسی: دورهٔ جاری، بعد تازه‌ترین دوره‌ها
    $ordered = array();
    foreach ($plans as $p) {
        if ($current_key !== null && $p['period_key'] === $current_key) $ordered[] = $p;
    }
    foreach ($plans as $p) {
        if ($current_key !== null && $p['period_key'] === $current_key) continue;
        $ordered[] = $p;
    }
    foreach ($ordered as $p) {
        $pa = null;
        if (store_mode() === 'mysql') {
            $pa = joma_query_one('SELECT id, activity_code, unit, name, target_value, daily_target FROM joma_plan_activities WHERE plan_id = ? AND user_id = ? AND activity_code = ? LIMIT 1', 'iis', array((int) $p['id'], $user_id, 'ACT002'));
        } else {
            $data = store_load();
            foreach ($data['plan_activities'] as $row) {
                if ((int) $row['plan_id'] === (int) $p['id'] && (int) $row['user_id'] === $user_id && isset($row['activity_code']) && $row['activity_code'] === 'ACT002') {
                    $pa = $row;
                    break;
                }
            }
        }
        if ($pa) {
            $target = isset($pa['target_value']) ? (float) $pa['target_value'] : 0.0;
            if ($target <= 0 && isset($pa['daily_target'])) $target = (float) $pa['daily_target'];
            $out['has_source'] = true;
            $out['target'] = ($target > 0) ? $target : null;
            $out['unit'] = isset($pa['unit']) && $pa['unit'] !== '' ? $pa['unit'] : 'UNIT_GLASS';
            $out['name'] = isset($pa['name']) && $pa['name'] !== '' ? $pa['name'] : 'نوشیدن آب';
            $out['period_key'] = $p['period_key'];
            return $out;
        }
    }
    return $out;
}

function jooje_plan_cmp($a, $b) {
    return strcmp((string) $b['period_key'], (string) $a['period_key']);
}

/* ------------------------------------------------------------------ */
/* نام جوجه (پردهٔ ۹ سند: «یک بار گذاشتن + یک بار عوض‌کردن»، در بک‌اند)  */
/* ------------------------------------------------------------------ */

function jooje_name_key($user_id) {
    return 'jooje_name_' . (int) $user_id;
}

/** انبار کلید/مقدار آماده است؟ (اگر ماژول بازیابی/انبار نباشد، نام نمایش داده نمی‌شود) */
function jooje_store_ready() {
    return function_exists('joma_kv_get') && function_exists('joma_kv_set');
}

function jooje_strlen($s) {
    if (function_exists('mb_strlen')) return (int) mb_strlen((string) $s, 'UTF-8');
    return (int) preg_match_all('/./us', (string) $s);
}

/** وضعیت نام: نام فعلی + آیا می‌تواند بگذارد/عوض کند. */
function jooje_name_state($user_id) {
    $out = array(
        'name' => null,
        'changes' => 0,
        'can_set' => false,
        'can_change' => false,
        'status' => 'awaiting_backend_storage',
    );
    if (!jooje_store_ready()) return $out;
    $rec = joma_kv_get_json(jooje_name_key($user_id));
    if (!is_array($rec) || empty($rec['name'])) {
        $out['can_set'] = true;
        $out['status'] = 'available';
        return $out;
    }
    $changes = isset($rec['changes']) ? (int) $rec['changes'] : 0;
    $out['name'] = (string) $rec['name'];
    $out['changes'] = $changes;
    $out['can_change'] = ($changes < 1);
    $out['status'] = $out['can_change'] ? 'set' : 'locked_after_change';
    return $out;
}

/** ثبت/تغییر نام. خروجی: آرایه با ok و error. */
function jooje_name_save($user_id, $name) {
    if (!jooje_store_ready()) return array('ok' => false, 'error' => 'ذخیره‌سازی نام در این نصب فعال نیست.');
    $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $name));
    $len = jooje_strlen($name);
    if ($len < 2) return array('ok' => false, 'error' => 'نام باید حداقل ۲ نویسه باشد.');
    if ($len > 16) return array('ok' => false, 'error' => 'نام باید حداکثر ۱۶ نویسه باشد.');
    $rec = joma_kv_get_json(jooje_name_key($user_id));
    $is_first = !is_array($rec) || empty($rec['name']);
    if (!$is_first && (int) $rec['changes'] >= 1) {
        return array('ok' => false, 'error' => 'نام جوجه یک‌بار عوض شده است؛ دیگر قابل تغییر نیست.');
    }
    $now = date('Y-m-d H:i:s');
    $save = array(
        'name' => $name,
        'changes' => $is_first ? 0 : ((int) $rec['changes'] + 1),
        'set_at' => $is_first ? $now : (isset($rec['set_at']) ? $rec['set_at'] : $now),
        'changed_at' => $is_first ? null : $now,
    );
    joma_kv_set_json(jooje_name_key($user_id), $save);
    $check = jooje_name_state($user_id);
    if ($check['name'] !== $name) return array('ok' => false, 'error' => 'ذخیره‌سازی نام ممکن نشد.');
    return array('ok' => true, 'error' => '', 'name' => $name);
}

/* ------------------------------------------------------------------ */
/* محاسبهٔ وضعیت جوجه                                                  */
/* ------------------------------------------------------------------ */

function jooje_unit_label($unit_key) {
    if (function_exists('units_list')) {
        $labels = units_list();
        if (isset($labels[$unit_key])) return $labels[$unit_key];
    }
    return '';
}

function jooje_is_night($cfg) {
    $hour = (int) date('G');
    $start = (int) $cfg['night_start_hour'];
    $end = (int) $cfg['night_end_hour'];
    if ($start === $end) return false;
    if ($start < $end) return ($hour >= $start && $hour < $end);
    return ($hour >= $start || $hour < $end);
}

/**
 * وضعیت کامل جوجه برای یک کاربر.
 * خروجی: آرایهٔ داده — همان قرارداد JSONی که صفحه و API و فرانت مصرف می‌کنند.
 */
function jooje_state($user_id) {
    $cfg = jooje_config();
    $user_id = (int) $user_id;
    if (empty($cfg['enabled'])) {
        return array('enabled' => false, 'version' => jooje_version());
    }

    $today = jalali_today();
    $events = jooje_event_rows($user_id);
    $moods = jooje_mood_rows($user_id);
    $units = count($events);
    $name_state = jooje_name_state($user_id);

    /* --- مرحله: تخم → ترک → جوجه (تولد یک‌بار و برگشت‌ناپذیر) --- */
    $stage = 'egg';
    if ($cfg['birth_units'] > 0 && $units >= $cfg['birth_units']) {
        $stage = 'chick';
    } elseif ($cfg['crack_units'] > 0 && $units >= $cfg['crack_units']) {
        $stage = 'crack';
    }

    $cracked_at = null;
    if ($cfg['crack_units'] > 0 && $units >= $cfg['crack_units']) {
        $cracked_at = jooje_jalali_of_datetime($events[$cfg['crack_units'] - 1]['created_at']);
    }
    $born_at = null;
    if ($stage === 'chick') {
        $born_at = jooje_jalali_of_datetime($events[$cfg['birth_units'] - 1]['created_at']);
    }

    $user = function_exists('get_user') ? get_user($user_id) : null;
    $laid_at = $user && !empty($user['created_at']) ? jooje_jalali_of_datetime($user['created_at']) : null;
    if ($laid_at === null) $laid_at = $today;

    /* --- آخرین فعالیت واقعی کاربر (ثبت عملکرد یا ثبت حال) --- */
    $last_date = null;
    if ($events) {
        $last_evt = $events[count($events) - 1];
        $d = jooje_jalali_of_datetime($last_evt['created_at']);
        if ($d === null) $d = $last_evt['performance_date'];
        if ($d) $last_date = $d;
    }
    if ($moods) {
        $d = $moods[count($moods) - 1]['jalali_date'];
        if ($d && ($last_date === null || strcmp($d, $last_date) > 0)) $last_date = $d;
    }
    $days_since_last = ($last_date !== null) ? jooje_date_diff_days($last_date, $today) : null;
    if ($days_since_last !== null && $days_since_last < 0) $days_since_last = 0;

    /* --- شش حالت (نوازش فقط سمت رابط است و اینجا برنمی‌گردد) --- */
    // «امروز ثبت کرد» = امروز چیزی ثبت کرده (لحظهٔ ثبت)، نه فقط ثبتِ امروز.
    // ثبت امروز برای یک روز گذشته هم یعنی کاربر امروز آمده و کار کرده است.
    $did_today = false;
    foreach ($events as $e) {
        $d = jooje_jalali_of_datetime($e['created_at']);
        if ($d === null) $d = $e['performance_date'];
        if ($d === $today) { $did_today = true; break; }
    }
    if (!$did_today) {
        foreach ($moods as $m) {
            if ($m['jalali_date'] === $today) { $did_today = true; break; }
        }
    }
    if ($stage !== 'chick') {
        // تخم و تخمِ ترک‌خورده هرگز «خاکستری» نمی‌شوند؛ منتظر است، بی‌سرزنش.
        $state = 'calm';
    } elseif ($did_today) {
        $state = 'happy';
    } elseif ($days_since_last !== null && $days_since_last >= (int) $cfg['gray_days']) {
        $state = 'gray';
    } elseif ($days_since_last !== null && $days_since_last >= (int) $cfg['faded_days']) {
        $state = 'faded';
    } elseif (jooje_is_night($cfg)) {
        $state = 'sleep';
    } else {
        $state = 'calm';
    }

    /* --- سه سنجهٔ موجود --- */
    $water_src = jooje_water_source($user_id);
    $seed_today = 0;
    $water_today = 0.0;
    $water_total = 0.0;
    foreach ($events as $e) {
        $is_today = ($e['performance_date'] === $today);
        if ($is_today) $seed_today++;
        if (isset($e['activity_code']) && $e['activity_code'] === 'ACT002') {
            $water_total += (float) $e['actual_value'];
            if ($is_today) $water_today += (float) $e['actual_value'];
        }
    }
    $mood_today = 0;
    foreach ($moods as $m) {
        if ($m['jalali_date'] === $today) { $mood_today = 1; break; }
    }

    $seed_metric = array(
        'key' => 'seed',
        'label' => 'ظرف دونه',
        'icon' => '🌾',
        'available' => true,
        'source' => 'ثبت معتبر کارها',
        'today' => $seed_today,
        'total' => $units,
        'unit' => 'دونه',
        'unit_key' => 'UNIT_COUNT',
        'target' => null,            // هدف روزانهٔ دونه تعیین نشده؛ عدد واقعی نمایش داده می‌شود
        'target_status' => 'not_defined',
        'progress' => null,
    );
    $water_progress = null;
    if ($water_src['has_source'] && $water_src['target'] !== null && (float) $water_src['target'] > 0) {
        $water_progress = $water_today / (float) $water_src['target'];
        if ($water_progress > 1) $water_progress = 1.0;
        if ($water_progress < 0) $water_progress = 0.0;
    }
    $water_metric = array(
        'key' => 'water',
        'label' => 'ظرف آب',
        'icon' => '💧',
        'available' => true,
        'source' => 'ثبت آب (ACT002) — هر ثبت موجود قطعی حساب می‌شود',
        'has_source' => (bool) $water_src['has_source'],
        'source_name' => $water_src['name'],
        'today' => $water_src['has_source'] ? $water_today : null, // No Data ≠ Zero
        'total' => $water_src['has_source'] ? $water_total : null,
        'unit' => jooje_unit_label($water_src['unit']),
        'unit_key' => $water_src['unit'],
        'target' => $water_src['target'],
        'target_status' => ($water_src['has_source'] ? (($water_src['target'] !== null) ? 'from_plan_snapshot' : 'no_target') : 'not_in_plan'),
        'progress' => $water_progress,
    );
    $home_metric = array(
        'key' => 'home',
        'label' => 'خانهٔ تمیز',
        'icon' => '🏠',
        'available' => true,
        'source' => 'ثبت حال روز',
        'today' => $mood_today,
        'total' => count($moods),
        'unit' => 'ثبت حال',
        'unit_key' => 'UNIT_TIMES',
        'target' => 1,
        'target_status' => 'daily_binary',
        'progress' => $mood_today ? 1.0 : 0.0,
    );
    $bath_metric = array(
        'key' => 'bath',
        'label' => 'وقت حمام',
        'icon' => '🛁',
        'available' => false,
        'reason' => 'منبع این سنجه هنوز ساخته نشده است؛ تا آن روز نمایش داده نمی‌شود.',
    );

    /* --- رشد (بعد از تولد) --- */
    $growth = null;
    $growth_next_at = null;
    if ($stage === 'chick') {
        $current = null;
        foreach ($cfg['growth'] as $g) {
            if ($units >= $g['from_units']) $current = $g;
        }
        if ($current === null && $cfg['growth']) $current = $cfg['growth'][0];
        $next = null;
        if ($current !== null) {
            foreach ($cfg['growth'] as $g) {
                if ($g['from_units'] > $current['from_units']) { $next = $g; break; }
            }
        }
        if ($current !== null) {
            $progress = 1.0;
            if ($next !== null) {
                $span = $next['from_units'] - $current['from_units'];
                $progress = ($span > 0) ? (($units - $current['from_units']) / $span) : 1.0;
                if ($progress > 1) $progress = 1.0;
                if ($progress < 0) $progress = 0.0;
            }
            $growth = array(
                'key' => $current['key'],
                'label' => $current['label'],
                'from_units' => $current['from_units'],
                'next_key' => $next !== null ? $next['key'] : null,
                'next_label' => $next !== null ? $next['label'] : null,
                'next_at_units' => $next !== null ? $next['from_units'] : null,
                'progress' => $progress,
            );
            $growth_next_at = $next !== null ? $next['from_units'] : null;
        }
    }

    /* --- خط زمانی (هرگز پاک نمی‌شود) --- */
    $timeline = array(array('icon' => '🥚', 'date' => $laid_at, 'label' => 'تخم گذاشته شد'));
    if ($cracked_at) $timeline[] = array('icon' => '🥚', 'date' => $cracked_at, 'label' => 'تخم ترک خورد');
    if ($born_at) $timeline[] = array('icon' => '🐣', 'date' => $born_at, 'label' => 'به دنیا آمد');

    return array(
        'enabled' => true,
        'version' => jooje_version(),
        'today' => $today,
        'stage' => $stage,          // egg | crack | chick
        'state' => $state,          // calm | happy | sleep | faded | gray  (نوازش سمت رابط است)
        'units' => $units,          // دونه‌ها = ثبت‌های معتبر
        'units_to_birth' => max(0, (int) $cfg['birth_units'] - $units),
        'laid_at' => $laid_at,
        'cracked_at' => $cracked_at,
        'born_at' => $born_at,
        'days_since_last' => $days_since_last,   // null = هنوز هیچ ثبتی نیست (صفر نیست)
        'metrics' => array($seed_metric, $water_metric, $home_metric, $bath_metric),
        'timeline' => $timeline,
        'growth' => $growth,
        'growth_next_at_units' => $growth_next_at,
        'thresholds' => array(
            'crack_units' => (int) $cfg['crack_units'],
            'birth_units' => (int) $cfg['birth_units'],
            'faded_days' => (int) $cfg['faded_days'],
            'gray_days' => (int) $cfg['gray_days'],
        ),
        'pet_name' => $name_state['name'],
        'pet_name_status' => $name_state['status'],
        'pet_name_can_set' => (bool) $name_state['can_set'],
        'pet_name_can_change' => (bool) $name_state['can_change'],
        'sharing' => 'private',                          // هرگز با هم‌مسیر به اشتراک گذاشته نمی‌شود
        'rules' => array(
            'never_dies' => true,
            'one_time_birth' => true,
            'no_points_for_touch' => true,
            'no_reward_for_water_draft' => true,
        ),
    );
}
