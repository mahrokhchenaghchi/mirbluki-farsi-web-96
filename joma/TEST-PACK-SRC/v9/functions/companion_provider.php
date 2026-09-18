<?php
/**
 * جوما — داشبورد مشاور (بستهٔ C3)
 * ------------------------------------------------------------------
 * سه قاعدهٔ سخت جوجه (سند ۲۴ §۴٫۳):
 *   ۱) مبنا فقط فعالیت است، نه حال — هیچ عدد خلق، هیچ یادداشت، هیچ بینش.
 *   ۲) کاربر می‌داند و می‌تواند خاموش کند — نشانگر فقط با رضایت جداگانه دیده می‌شود
 *      (پرچم status_share، همان کادر مستقل جریان رضایت).
 *   ۳) کارت مشاور هیچ عدد حال، هیچ متن یادداشت و هیچ بینشی نشان نمی‌دهد.
 *
 * آستانه‌ها از بک‌اند می‌آیند؛ فرانت روز نمی‌شمارد:
 *   تازه‌وارد  = کمتر از ۷ روز از شروع همراهی
 *   آرام      = ۳ تا ۵ روز بی‌ثبت
 *   نیازمند توجه = ۶ روز یا بیشتر بی‌ثبت
 *   در مسیر   = در دو روز گذشته ثبت داشته
 */

/** تعریف حالت‌ها — تنها نقطهٔ تعریف متن و ظاهر */
function joma_provider_state_defs() {
    return array(
        'good' => array(
            'label' => 'در مسیر است',
            'text' => 'ثبت‌هایش پیوسته است',
            'chick' => 'happy',
            'order' => 2,
        ),
        'calm' => array(
            'label' => 'این هفته کم‌فعال بوده',
            'text' => 'چند روزی است ثبت نکرده',
            'chick' => 'faded',
            'order' => 1,
        ),
        'attention' => array(
            'label' => 'نیازمند توجه',
            'text' => '', // متن با شمار روز ساخته می‌شود
            'chick' => 'gray',
            'order' => 0,
        ),
        'new' => array(
            'label' => 'تازه شروع کرده',
            'text' => 'کمتر از ۷ روز از شروع همراهی',
            'chick' => 'egg',
            'order' => 3,
        ),
        'off' => array(
            'label' => 'نشانگر وضعیت',
            'text' => 'روشن نشده است',
            'chick' => '',
            'order' => 4,
        ),
    );
}

/** اختلاف دو تاریخ شمسی بر حسب روز (شمسی → میلادی → اختلاف) */
function joma_days_between_jalali($from, $to) {
    if (!function_exists('jalali_to_gregorian')) return null;
    $f = explode('-', (string) $from);
    $t = explode('-', (string) $to);
    if (count($f) !== 3 || count($t) !== 3) return null;
    $fg = jalali_to_gregorian((int) $f[0], (int) $f[1], (int) $f[2]);
    $tg = jalali_to_gregorian((int) $t[0], (int) $t[1], (int) $t[2]);
    if (!is_array($fg) || !is_array($tg)) return null;
    $a = strtotime($fg[0] . '-' . jalali_pad($fg[1]) . '-' . jalali_pad($fg[2]) . ' 00:00:00');
    $b = strtotime($tg[0] . '-' . jalali_pad($tg[1]) . '-' . jalali_pad($tg[2]) . ' 00:00:00');
    if ($a === false || $b === false) return null;
    return (int) floor(($b - $a) / 86400);
}

/**
 * آخرین تاریخی که مراجع «عملکرد» ثبت کرده — فقط عملکرد، نه حال و نه پیام.
 * خروجی: 'Y-m-d' شمسی یا null (بدون ثبت) یا false (خطای خواندن → نمایش داده نمی‌شود)
 */
function joma_provider_last_perf_date($client_user_id) {
    $client_user_id = (int) $client_user_id;
    try {
        if (store_mode() === 'mysql') {
            $r = joma_query_one('SELECT MAX(performance_date) AS m FROM joma_performance_events WHERE user_id=?', 'i', array($client_user_id));
            if (!$r || empty($r['m'])) return null;
            return (string) $r['m'];
        }
        // رخدادهای عملکرد در storage خودِ جوما هستند (همان منبعی که گزارش‌ها از آن می‌خوانند)
        $st = store_load();
        if ($st === null) return false;
        if (!isset($st['events']) || !is_array($st['events'])) return null;
        $best = null;
        foreach ($st['events'] as $e) {
            if ((int) $e['user_id'] !== $client_user_id) continue;
            $d = isset($e['performance_date']) ? (string) $e['performance_date'] : '';
            if ($d === '') continue;
            if ($best === null || strcmp($d, $best) > 0) $best = $d;
        }
        return $best;
    } catch (Throwable $e) {
        return false;
    }
}

/** روزهای با ثبت عملکرد در همین دوره (فقط شمار روز، بدون محتوا) */
function joma_provider_period_record_days($client_user_id) {
    $client_user_id = (int) $client_user_id;
    $key = current_period_key();
    $out = array();
    try {
        if (store_mode() === 'mysql') {
            $rows = joma_query('SELECT DISTINCT performance_date FROM joma_performance_events WHERE user_id=?', 'i', array($client_user_id));
            foreach ($rows as $r) $out[] = (string) $r['performance_date'];
        } else {
            $st = store_load();
            if ($st === null || !isset($st['events']) || !is_array($st['events'])) return array();
            foreach ($st['events'] as $e) {
                if ((int) $e['user_id'] !== $client_user_id) continue;
                if (isset($e['performance_date'])) $out[] = (string) $e['performance_date'];
            }
        }
    } catch (Throwable $e) {
        return array();
    }
    $days = array();
    foreach ($out as $d) {
        if (function_exists('jalali_in_period') && jalali_in_period($d, $key)) $days[$d] = true;
    }
    return array_keys($days);
}

/** کارت وضعیت هفته/ماه یک مراجع — فقط با مجوز VIEW_PROGRESS */
function joma_provider_progress($client_user_id) {
    $key = current_period_key();
    $b = jalali_period_bounds($key);
    $days = joma_provider_period_record_days((int) $client_user_id);
    $today = jalali_today();
    $elapsed = joma_days_between_jalali($b['start'], $today);
    if ($elapsed === null) $elapsed = $b['days'];
    $elapsed = max(1, min((int) $b['days'], $elapsed + 1));
    return array(
        'recorded' => count($days),
        'elapsed' => $elapsed,
        'total' => (int) $b['days'],
        'period_label' => jalali_period_label($key),
    );
}

/**
 * وضعیت یک مراجع — از بک‌اند، بدون شمارش در فرانت.
 * خروجی: array('key','label','text','chick','days','shared')
 */
function joma_provider_client_state($link) {
    $defs = joma_provider_state_defs();
    $client = isset($link['client_user_id']) ? (int) $link['client_user_id'] : 0;

    // ۱) رضایت نشانگر — بدون آن، هیچ وضعیتی ساخته نمی‌شود
    $shared = function_exists('joma_status_share_get') ? joma_status_share_get($client) : false;
    if (!$shared) {
        $d = $defs['off'];
        return array('key' => 'off', 'label' => $d['label'], 'text' => $d['text'], 'chick' => '', 'days' => null, 'shared' => false);
    }

    // ۲) شروع همراهی (تاریخ پذیرش؛ اگر نبود، تاریخ درخواست)
    $start_dt = '';
    if (!empty($link['accepted_at'])) $start_dt = (string) $link['accepted_at'];
    elseif (!empty($link['created_at'])) $start_dt = (string) $link['created_at'];
    $start_j = '';
    if ($start_dt !== '' && function_exists('hammasir_dt_to_jalali')) {
        $start_j = (string) hammasir_dt_to_jalali($start_dt);
    }
    $days_start = ($start_j !== '') ? joma_days_between_jalali($start_j, jalali_today()) : null;

    // ۳) تازه‌وارد — کمتر از ۷ روز از شروع همراهی
    if ($days_start !== null && $days_start < 7) {
        $d = $defs['new'];
        return array('key' => 'new', 'label' => $d['label'], 'text' => $d['text'], 'chick' => $d['chick'], 'days' => $days_start, 'shared' => true);
    }

    // ۴) فاصله از آخرین ثبت عملکرد (فقط فعالیت)
    $last = joma_provider_last_perf_date($client);
    if ($last === false) {
        $d = $defs['off'];
        return array('key' => 'off', 'label' => $d['label'], 'text' => 'در دسترس نیست', 'chick' => '', 'days' => null, 'shared' => false);
    }
    $days = null;
    if ($last !== null) {
        $days = joma_days_between_jalali($last, jalali_today());
    } elseif ($days_start !== null) {
        $days = $days_start; // هرگز ثبت نکرده و بیش از ۷ روز گذشته
    }

    if ($days === null) {
        $d = $defs['off'];
        return array('key' => 'off', 'label' => $d['label'], 'text' => 'در دسترس نیست', 'chick' => '', 'days' => null, 'shared' => false);
    }
    if ($days >= 6) {
        $d = $defs['attention'];
        return array('key' => 'attention', 'label' => $d['label'], 'text' => fa_num($days) . ' روز است چیزی ثبت نکرده', 'chick' => $d['chick'], 'days' => $days, 'shared' => true);
    }
    if ($days >= 3) {
        $d = $defs['calm'];
        return array('key' => 'calm', 'label' => $d['label'], 'text' => fa_num($days) . ' روز است ثبت نکرده', 'chick' => $d['chick'], 'days' => $days, 'shared' => true);
    }
    $d = $defs['good'];
    return array('key' => 'good', 'label' => $d['label'], 'text' => $d['text'], 'chick' => $d['chick'], 'days' => $days, 'shared' => true);
}

/** فیلترهای سرصفحه — ترتیب مالک: نیازمند توجه · همه · کم‌فعال · فعال */
function joma_provider_filter_defs() {
    return array(
        'attention' => 'نیازمند توجه',
        'all' => 'همه',
        'calm' => 'کم‌فعال',
        'active' => 'فعال',
    );
}

/** یک کارت مراجع (بدون هیچ دادهٔ حال/یادداشت/بینش) */
function joma_provider_card($link) {
    $client = (int) $link['client_user_id'];
    $name = '';
    try {
        $name = hammasir_user_display($client);
    } catch (Throwable $e) {
        $name = '';
    }
    $start_dt = !empty($link['accepted_at']) ? (string) $link['accepted_at'] : (!empty($link['created_at']) ? (string) $link['created_at'] : '');
    $start_j = '';
    if ($start_dt !== '' && function_exists('hammasir_dt_to_jalali')) {
        $start_j = (string) hammasir_dt_to_jalali($start_dt);
    }
    $perms_on = array();
    try {
        $perms = hammasir_perm_map((int) $link['id']);
        $labels = hammasir_perm_view_labels();
        if (is_array($perms)) {
            foreach (array('VIEW_SUMMARY', 'VIEW_PROGRESS', 'VIEW_ACTIVITY_DETAILS', 'VIEW_MOOD') as $k) {
                if (!empty($perms[$k])) {
                    $perms_on[] = isset($labels[$k]) ? $labels[$k] : $k;
                }
            }
        }
    } catch (Throwable $e) {
        $perms_on = array();
    }
    return array(
        'link_id' => (int) $link['id'],
        'client_id' => $client,
        'name' => $name,
        'initial' => (function_exists('mb_substr') && $name !== '') ? mb_substr($name, 0, 1, 'UTF-8') : '؟',
        'start_jalali' => $start_j,
        'permissions' => $perms_on,
        'state' => joma_provider_client_state($link),
    );
}

/** صفحه‌بندی‌شدهٔ کارت‌های مراجعان با مرتب‌سازی مالک */
function joma_provider_cards($provider_user_id, $filter = 'all') {
    $defs = joma_provider_state_defs();
    $cards = array();
    try {
        $links = hammasir_links_by_provider((int) $provider_user_id, 'ACTIVE');
        if (is_array($links)) {
            foreach ($links as $l) $cards[] = joma_provider_card($l);
        }
    } catch (Throwable $e) {
        $cards = array();
    }
    // مرتب‌سازی: نیازمند توجه → آرام → در مسیر → تازه‌وارد → بدون نشانگر
    usort($cards, function ($a, $b) use ($defs) {
        $oa = isset($defs[$a['state']['key']]) ? $defs[$a['state']['key']]['order'] : 9;
        $ob = isset($defs[$b['state']['key']]) ? $defs[$b['state']['key']]['order'] : 9;
        if ($oa === $ob) {
            $da = ($a['state']['days'] === null) ? 999 : (int) $a['state']['days'];
            $db = ($b['state']['days'] === null) ? 999 : (int) $b['state']['days'];
            if ($da === $db) return strcmp($a['name'], $b['name']);
            return ($da > $db) ? -1 : 1;
        }
        return ($oa < $ob) ? -1 : 1;
    });
    // شمارش هر فیلتر (از بک‌اند)
    $counts = array('attention' => 0, 'all' => count($cards), 'calm' => 0, 'active' => 0);
    foreach ($cards as $c) {
        $k = $c['state']['key'];
        if ($k === 'attention') $counts['attention']++;
        if ($k === 'calm') $counts['calm']++;
        if ($k === 'good') $counts['active']++;
    }
    // اعمال فیلتر
    if ($filter !== 'all') {
        $want = ($filter === 'active') ? 'good' : $filter;
        $kept = array();
        foreach ($cards as $c) {
            if ($c['state']['key'] === $want) $kept[] = $c;
        }
        $cards = $kept;
    }
    return array('cards' => $cards, 'counts' => $counts);
}

/**
 * تصویر جوجهٔ نشانگر وضعیت — فایل شکل‌ها فقط در صورت نیاز لود می‌شود.
 * اگر فایل نبود، هیچ شکلی رسم نمی‌شود (fail-open؛ کارت سالم می‌ماند).
 */
function joma_provider_chick_svg($chick, $size = 46) {
    if ($chick === '') return '';
    if (!function_exists('joma_v2_pet_svg')) {
        $f = dirname(__FILE__) . '/../includes/v2_chick.php';
        if (is_file($f)) require_once $f;
    }
    if (!function_exists('joma_v2_pet_svg')) return '';
    if ($chick === 'egg') return joma_v2_pet_svg('egg', 'calm', $size);
    return joma_v2_pet_svg('chick', $chick, $size);
}

/**
 * چهار شمار داشبورد مشاور — همه از منبع خودِ جوما و بدون هیچ شمارش در فرانت.
 * (نسخهٔ قبلی، شمار «ثبت عملکرد هفته» را از storage ماژول می‌خواند که در حالت
 *  فایلی کلید رخداد را ندارد و هشدار PHP می‌داد؛ این‌جا همان عدد از storage
 *  خودِ جوما محاسبه می‌شود. هیچ داده یا مجوزی عوض نشده است.)
 */
function joma_provider_kpis($provider_user_id) {
    $out = array(
        'active_count' => 0,
        'pending_count' => 0,
        'unread_count' => 0,
        'week_performance_count' => 0,
        'has_progress_view' => false,
    );
    $provider_user_id = (int) $provider_user_id;
    try {
        $data = joma_provider_cards($provider_user_id, 'all');
        $out['active_count'] = (int) $data['counts']['all'];
    } catch (Throwable $e) { /* صفر */ }
    try {
        $pend = hammasir_links_by_provider($provider_user_id, 'PENDING');
        $out['pending_count'] = is_array($pend) ? count($pend) : 0;
    } catch (Throwable $e) { /* صفر */ }
    try {
        $badge = hammasir_unread_badge($provider_user_id);
        if (is_array($badge) && isset($badge['msgs'])) $out['unread_count'] = (int) $badge['msgs'];
    } catch (Throwable $e) { /* صفر */ }
    // ثبت‌های هفت روز اخیر، فقط برای مراجعانی که مجوز پیشرفت داده‌اند
    try {
        $clients = array();
        $links = hammasir_links_by_provider($provider_user_id, 'ACTIVE');
        if (is_array($links)) {
            foreach ($links as $l) {
                $perms = hammasir_perm_map((int) $l['id']);
                if (is_array($perms) && isset($perms['VIEW_PROGRESS']) && (int) $perms['VIEW_PROGRESS'] === 1) {
                    $clients[] = (int) $l['client_user_id'];
                }
            }
        }
        if (count($clients) > 0) {
            $out['has_progress_view'] = true;
            $today = jalali_today();
            $from = joma_week_start_jalali($today, -6);
            $st = store_load();
            if ($st !== null && isset($st['events']) && is_array($st['events'])) {
                foreach ($st['events'] as $e) {
                    if (!in_array((int) $e['user_id'], $clients, true)) continue;
                    $d = isset($e['performance_date']) ? (string) $e['performance_date'] : '';
                    if ($d === '' || strcmp($d, $from) < 0 || strcmp($d, $today) > 0) continue;
                    $out['week_performance_count']++;
                }
            }
        }
    } catch (Throwable $e) { /* صفر */ }
    return $out;
}

/** تاریخ شمسی n روز قبل (کمکی؛ خطا → همان تاریخ ورودی) */
function joma_week_start_jalali($date, $days) {
    if (!function_exists('jalali_to_gregorian')) return $date;
    $p = explode('-', (string) $date);
    if (count($p) !== 3) return $date;
    $g = jalali_to_gregorian((int) $p[0], (int) $p[1], (int) $p[2]);
    if (!is_array($g)) return $date;
    $ts = strtotime($g[0] . '-' . jalali_pad($g[1]) . '-' . jalali_pad($g[2]) . ' 12:00:00') + ((int) $days * 86400);
    $j = gregorian_to_jalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
}

/** درخواست‌های در انتظار — بالای همه (بدون هیچ داده‌ای از مراجع) */
function joma_provider_pending($provider_user_id) {
    $out = array();
    try {
        $links = hammasir_links_by_provider((int) $provider_user_id, 'PENDING');
        if (!is_array($links)) return $out;
        foreach ($links as $l) {
            $name = '';
            try {
                $name = hammasir_user_display((int) $l['client_user_id']);
            } catch (Throwable $e) {
                $name = '';
            }
            $perms_on = array();
            try {
                $perms = hammasir_perm_map((int) $l['id']);
                $labels = hammasir_perm_view_labels();
                if (is_array($perms)) {
                    foreach (array('VIEW_SUMMARY', 'VIEW_PROGRESS', 'VIEW_ACTIVITY_DETAILS', 'VIEW_MOOD') as $k) {
                        if (!empty($perms[$k])) $perms_on[] = isset($labels[$k]) ? $labels[$k] : $k;
                    }
                }
            } catch (Throwable $e) {
                $perms_on = array();
            }
            $ago = '';
            if (function_exists('hammasir_time_ago')) {
                $ago = hammasir_time_ago(isset($l['requested_at']) ? $l['requested_at'] : null);
            }
            $out[] = array(
                'link_id' => (int) $l['id'],
                'name' => $name,
                'initial' => (function_exists('mb_substr') && $name !== '') ? mb_substr($name, 0, 1, 'UTF-8') : '؟',
                'permissions' => $perms_on,
                'time_ago' => $ago,
            );
        }
    } catch (Throwable $e) {
        return array();
    }
    return $out;
}

