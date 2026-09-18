<?php
/**
 * JOMA — B7: موتور بینش (دفترچهٔ جوما)
 * =========================================================================
 * قواعد ثابت سند ۱۵:
 *   · هر بینش باید «شواهد» داشته باشد: شاخص‌ها · بازه · تعداد نمونه · جهت · قاعده و نسخه · زمان محاسبه.
 *   · جملهٔ عدم قطعیت («همراهی، دلیل نیست.») بخشی از خودِ متن است، نه راهنمای کوچک.
 *   · بینش بدون داده ساخته نمی‌شود؛ حالت صادق: «دادهٔ کافی نیست».
 *   · هیچ عدد دقت/اطمینان و هیچ نموداری داخل کارت نمی‌آید.
 *   · بینش هرگز با هم‌مسیر به اشتراک گذاشته نمی‌شود.
 *
 * این فایل هیچ نوشتنی انجام نمی‌دهد و هیچ درخواستی به شبکه ندارد؛ فقط از دادهٔ خود کاربر
 * (همان رخدادها و ثبت‌های حال) محاسبه می‌کند.
 * =========================================================================
 */

function joma_insight_rule_version() {
    return '۱٫۱';
}

/** حداقل نمونه برای هر نوع بینش (زیر آن، بینش ساخته نمی‌شود). */
function joma_insight_min_days() {
    return 5;   // حداقل روزهای دارای ثبت برای هر تحلیل
}

function joma_insight_min_pairs() {
    return 5;   // حداقل جفت داده برای رابطهٔ حال×کار
}

/** میانگین روزانهٔ موفقیت (فقط فعالیت‌های روزانهٔ وزن‌دار)، مثل موتور تحلیل. */
function joma_insight_daily_rate($acts, $events, $date) {
    $num = 0.0;
    $den = 0.0;
    foreach ($acts as $a) {
        if ($a['frequency'] !== 'DAILY') continue;
        $w = (float) $a['weight'];
        if ($w <= 0) continue;
        $target = (float) $a['target_value'];
        if ($target <= 0) continue;
        $val = 0.0;
        $has = false;
        foreach ($events as $e) {
            if ((int) $e['plan_activity_id'] !== (int) $a['id']) continue;
            if ($e['performance_date'] !== $date) continue;
            $val += (float) $e['actual_value'];
            $has = true;
        }
        if (!$has) continue; // روز بی‌ثبت، در مخرج نمی‌آید (بی‌داده ≠ صفر)
        $num += $w * min(1.0, $val / $target);
        $den += $w;
    }
    if ($den <= 0) return null;
    return $num / $den;
}

/**
 * ساخت بینش‌ها برای یک برنامه/دوره.
 * ورودی: $plan، $activities (اسنپ‌شات برنامه)، $events، $moods (همان دوره)، $today
 * خروجی: array('status' => OK|INSUFFICIENT_DATA, 'computedAt', 'insights' => [...])
 */
function joma_insights_build($plan, $activities, $events, $moods, $today) {
    $out = array(
        'status' => 'INSUFFICIENT_DATA',
        'computedAt' => function_exists('joma_now') ? joma_now() : date('Y-m-d H:i:s'),
        'ruleVersion' => joma_insight_rule_version(),
        'insights' => array(),
    );

    /* روزهای دارای ثبت + بازه */
    $dayHas = array();
    $names = array();
    foreach ($activities as $a) $names[(int) $a['id']] = $a['name'];
    foreach ($events as $e) $dayHas[$e['performance_date']] = true;
    $days = array_keys($dayHas);
    sort($days);
    if (!$days) return $out;
    $from = $days[0];
    $to = $days[count($days) - 1];
    $nDays = count($days);

    $insights = array();

    /* ---------- ۱) بهترین روز هفته ---------- */
    if ($nDays >= joma_insight_min_days()) {
        $byWeekday = array();
        foreach ($days as $d) {
            $rate = joma_insight_daily_rate($activities, $events, $d);
            if ($rate === null) continue;
            $wd = jalali_weekday_index($d);
            if (!isset($byWeekday[$wd])) $byWeekday[$wd] = array('sum' => 0.0, 'n' => 0);
            $byWeekday[$wd]['sum'] += $rate;
            $byWeekday[$wd]['n']++;
        }
        $best = null;
        foreach ($byWeekday as $wd => $b) {
            if ($b['n'] < 2) continue; // یک بار، الگو نیست
            $avg = $b['sum'] / $b['n'];
            if ($best === null || $avg > $best['avg']) $best = array('wd' => $wd, 'avg' => $avg, 'n' => $b['n']);
        }
        if ($best !== null) {
            $pct = (int) round($best['avg'] * 100);
            $insights[] = array(
                'id' => 'weekday_best',
                'title' => 'روزهای هفته',
                'text' => 'در ثبت‌های تو، ' . jalali_weekday_name($days[0] = joma_insight_day_with_weekday($days, $best['wd'])) . 'ها معمولاً جلوتر بوده‌اند (میانگین ' . fa_num($pct) . '٪ از هدف‌های روزانه). این یک الگوی مشاهده‌شده است، نه قانون.',
                'evidence' => array(
                    'indicators' => 'درصد موفقیت روزانه × روز هفته',
                    'from' => $from, 'to' => $to,
                    'sampleSize' => $best['n'],
                    'sampleLabel' => fa_num($best['n']) . ' روز از این روزِ هفته',
                    'droppedNote' => 'روزهای بی‌ثبت کنار گذاشته شدند.',
                    'direction' => 'بالاتر از میانگین بقیهٔ روزها',
                    'lag' => 'همان روز',
                    'ruleId' => 'weekday-best',
                    'ruleVersion' => joma_insight_rule_version(),
                    'computedAt' => $out['computedAt'],
                    'isValid' => true,
                ),
            );
        }
    }

    /* ---------- ۲) رابطهٔ حال و کار (همبستگی ساده، با جملهٔ الزامی) ---------- */
    $pairs = array();
    foreach ($moods as $m) {
        $d = $m['jalali_date'];
        $rate = joma_insight_daily_rate($activities, $events, $d);
        if ($rate === null) continue;
        $pairs[] = array('date' => $d, 'rate' => $rate, 'm' => $m);
    }
    if (count($pairs) >= joma_insight_min_pairs()) {
        $metrics = array(
            'sleep_quality' => 'کیفیت خواب', 'energy' => 'انرژی', 'general_mood' => 'حال عمومی',
            'focus' => 'تمرکز', 'stress' => 'استرس',
        );
        $bestRel = null;
        foreach ($metrics as $key => $label) {
            $xs = array(); $ys = array();
            foreach ($pairs as $p) {
                $xs[] = (float) $p['m'][$key];
                $ys[] = (float) $p['rate'];
            }
            $r = joma_insight_corr($xs, $ys);
            if ($r === null) continue;
            if ($bestRel === null || abs($r) > abs($bestRel['r'])) {
                $bestRel = array('key' => $key, 'label' => $label, 'r' => $r, 'n' => count($xs));
            }
        }
        if ($bestRel !== null) {
            $dir = ($bestRel['r'] > 0) ? 'هم‌جهت' : 'معکوس';
            $word = ($bestRel['r'] > 0) ? 'بالاتر' : 'پایین‌تر';
            $insights[] = array(
                'id' => 'mood_activity_relation',
                'title' => 'حال و کارها',
                'text' => 'در ثبت‌های این بازه، روزهایی که «' . $bestRel['label'] . '» بالاتر بوده، درصد موفقیت روزانه‌ات هم ' . $word . ' دیده می‌شود. **همراهی، دلیل نیست.**',
                'evidence' => array(
                    'indicators' => $bestRel['label'] . ' × درصد موفقیت روزانه',
                    'from' => $from, 'to' => $to,
                    'sampleSize' => $bestRel['n'],
                    'sampleLabel' => fa_num($bestRel['n']) . ' جفت داده',
                    'droppedNote' => 'روزهایی که یکی از دو مقدار ثبت نشده بود کنار گذاشته شدند.',
                    'direction' => $dir,
                    'lag' => 'همان روز',
                    'ruleId' => 'mood-success-relation',
                    'ruleVersion' => joma_insight_rule_version(),
                    'computedAt' => $out['computedAt'],
                    'isValid' => true,
                ),
            );
        }
    }

    /* ---------- ۳) پیوسته‌ترین رشتهٔ روزهای دارای ثبت ---------- */
    $streak = 1; $bestStreak = 1; $bestStart = $days[0];
    for ($i = 1; $i < count($days); $i++) {
        if (joma_insight_next_day($days[$i - 1]) === $days[$i]) {
            $streak++;
            if ($streak > $bestStreak) { $bestStreak = $streak; $bestStart = $days[$i - $streak + 1]; }
        } else {
            $streak = 1;
        }
    }
    if ($bestStreak >= 3) {
        $insights[] = array(
            'id' => 'streak',
            'title' => 'پیوستگی ثبت',
            'text' => 'بلندترین رشتهٔ ثبت پشت‌سرهم تو در این بازه ' . fa_num($bestStreak) . ' روز بوده است (' . jalali_format($bestStart) . ' به بعد). این فقط دربارهٔ «ثبت کردن» است، نه دربارهٔ کیفیت کارها.',
            'evidence' => array(
                'indicators' => 'روزهایی که حداقل یک ثبت داشته‌اند',
                'from' => $from, 'to' => $to,
                'sampleSize' => $nDays,
                'sampleLabel' => fa_num($nDays) . ' روز دارای ثبت',
                'droppedNote' => 'روزهای بدون ثبت، رشته را قطع می‌کنند.',
                'direction' => '—',
                'lag' => 'همان روز',
                'ruleId' => 'streak-longest',
                'ruleVersion' => joma_insight_rule_version(),
                'computedAt' => $out['computedAt'],
                'isValid' => true,
            ),
        );
    }

    /* ---------- ۴) پایدارترین فعالیت (پوشش) ---------- */
    $rows = array();
    foreach ($activities as $a) {
        $target = (float) $a['target_value'];
        if ($target <= 0) continue;
        $dates = array();
        $sum = 0.0;
        foreach ($events as $e) {
            if ((int) $e['plan_activity_id'] !== (int) $a['id']) continue;
            $dates[$e['performance_date']] = true;
            $sum += (float) $e['actual_value'];
        }
        if (!$dates) continue;
        $rows[] = array('name' => $a['name'], 'days' => count($dates), 'sum' => $sum, 'target' => $target, 'sticker' => $a['sticker']);
    }
    if (count($rows) && $nDays >= joma_insight_min_days()) {
        usort($rows, 'joma_insight_cmp_days');
        $top = $rows[0];
        $last = $rows[count($rows) - 1];
        if ($top['days'] > $last['days']) {
            $insights[] = array(
                'id' => 'coverage_most',
                'title' => 'پایدارترین فعالیت',
                'text' => '«' . $top['name'] . '» بیشتر از بقیه ثبت شده است (' . fa_num($top['days']) . ' روز). ' . ($last['name'] !== $top['name'] ? 'در مقابل، «' . $last['name'] . '» کمترین روزهای ثبت را داشته است (' . fa_num($last['days']) . ' روز).' : ''),
                'evidence' => array(
                    'indicators' => 'روزهای دارای ثبت، به تفکیک فعالیت',
                    'from' => $from, 'to' => $to,
                    'sampleSize' => (int) $top['days'],
                    'sampleLabel' => fa_num($top['days']) . ' روز برای این فعالیت',
                    'droppedNote' => 'فعالیت‌هایی که هدف معتبر ندارند کنار گذاشته شدند.',
                    'direction' => 'بیشترین پوشش',
                    'lag' => 'همان روز',
                    'ruleId' => 'coverage-most-consistent',
                    'ruleVersion' => joma_insight_rule_version(),
                    'computedAt' => $out['computedAt'],
                    'isValid' => true,
                ),
            );
        }
    }

    /* ---------- ۵) آب: سهم ثبت‌های قطعی‌شده (پیوند با B6) ---------- */
    if (function_exists('joma_water_state')) {
        $waterDays = 0; $finalDays = 0; $draftToday = false;
        foreach ($events as $e) {
            if (!isset($e['activity_code']) || $e['activity_code'] !== 'ACT002') continue;
            $waterDays++;
            if (joma_water_is_final($e['user_id'], $e['performance_date'])) $finalDays++;
            elseif ($e['performance_date'] === $today) $draftToday = true;
        }
        if ($waterDays > 0) {
            $insights[] = array(
                'id' => 'water_final',
                'title' => 'آب و ثبت نهایی',
                'text' => 'از ' . fa_num($waterDays) . ' روز ثبت آب، ' . fa_num($finalDays) . ' روز ثبت نهایی شده است.'
                    . ($draftToday ? ' ثبت امروز هنوز پیش‌نویس است و در سنجه‌ها شمرده نمی‌شود.' : ''),
                'evidence' => array(
                    'indicators' => 'ثبت‌های آب (ACT002) و وضعیت قطعی‌شدن',
                    'from' => $from, 'to' => $to,
                    'sampleSize' => $waterDays,
                    'sampleLabel' => fa_num($waterDays) . ' روز ثبت آب',
                    'droppedNote' => 'ثبت‌های پیش‌نویس در سنجه‌ها حساب نمی‌شوند.',
                    'direction' => '—',
                    'lag' => 'همان روز',
                    'ruleId' => 'water-finalized-ratio',
                    'ruleVersion' => joma_insight_rule_version(),
                    'computedAt' => $out['computedAt'],
                    'isValid' => true,
                ),
            );
        }
    }

    $out['insights'] = $insights;
    $out['status'] = $insights ? 'OK' : 'INSUFFICIENT_DATA';
    return $out;
}

function joma_insight_cmp_days($a, $b) {
    if ($a['days'] === $b['days']) return 0;
    return ($a['days'] > $b['days']) ? -1 : 1;
}

/** اولین روزی که در فهرست روزهای دارای ثبت، آن روزِ هفته را دارد. */
function joma_insight_day_with_weekday($days, $wd) {
    foreach ($days as $d) if (jalali_weekday_index($d) === (int) $wd) return $d;
    return $days[0];
}

/** روز جلالی بعدی (برای تشخیص پیوستگی). */
function joma_insight_next_day($date) {
    $p = explode('-', (string) $date);
    if (count($p) !== 3) return $date;
    $g = jalali_to_gregorian((int) $p[0], (int) $p[1], (int) $p[2]);
    $ts = mktime(0, 0, 0, (int) $g[1], (int) $g[2], (int) $g[0]) + 86400;
    $j = gregorian_to_jalali((int) date('Y', $ts), (int) date('n', $ts), (int) date('j', $ts));
    return $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
}

/** همبستگی پیرسون؛ اگر واریانس صفر باشد null برمی‌گردد (هیچ ادعایی ساخته نمی‌شود). */
function joma_insight_corr($xs, $ys) {
    $n = count($xs);
    if ($n < 3 || $n !== count($ys)) return null;
    $mx = array_sum($xs) / $n;
    $my = array_sum($ys) / $n;
    $sxy = 0.0; $sxx = 0.0; $syy = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $dx = $xs[$i] - $mx;
        $dy = $ys[$i] - $my;
        $sxy += $dx * $dy;
        $sxx += $dx * $dx;
        $syy += $dy * $dy;
    }
    if ($sxx <= 0 || $syy <= 0) return null;
    $r = $sxy / sqrt($sxx * $syy);
    if ($r > 1) $r = 1.0;
    if ($r < -1) $r = -1.0;
    return $r;
}

/* ------------------------------------------------------------------ */
/* یادداشت‌های دفترچه (همان یادداشت حال — فقط خود کاربر)               */
/* ------------------------------------------------------------------ */

/** ثبت/ویرایش یادداشت یک روز. فقط متنی که خود کاربر نوشته؛ هیچ‌جای دیگر نمی‌رود. */
function joma_journal_note_save($user_id, $date, $note) {
    if (!jalali_is_valid($date)) return array('ok' => false, 'error' => 'تاریخ معتبر نیست.');
    $note = trim((string) $note);
    if (function_exists('mb_strlen') && mb_strlen($note, 'UTF-8') > 2000) {
        return array('ok' => false, 'error' => 'یادداشت حداکثر ۲۰۰۰ نویسه باشد.');
    }
    $ex = get_mood($user_id, $date);
    if (!$ex) return array('ok' => false, 'error' => 'برای این روز حالی ثبت نشده است؛ اول حال روز را ثبت کن.');
    if (store_mode() === 'mysql') {
        joma_exec('UPDATE joma_mood_records SET note = ? WHERE id = ? AND user_id = ?', 'sii', array($note, (int) $ex['id'], (int) $user_id));
    } else {
        $data = store_load();
        foreach ($data['moods'] as $i => $row) {
            if ((int) $row['id'] === (int) $ex['id'] && (int) $row['user_id'] === (int) $user_id) {
                $data['moods'][$i]['note'] = $note;
            }
        }
        store_save($data);
    }
    return array('ok' => true, 'error' => '');
}

/** پاک‌کردن یادداشت (خود رکورد حال دست‌نخورده می‌ماند). */
function joma_journal_note_delete($user_id, $date) {
    $res = joma_journal_note_save($user_id, $date, '');
    return $res;
}

/** فهرست یادداشت‌های دارای متن، جدیدترین اول. */
function joma_journal_notes($user_id, $q = '', $limit = 30, $offset = 0) {
    $rows = array();
    if (store_mode() === 'mysql') {
        if ($q !== '') {
            $raw = joma_query('SELECT * FROM joma_mood_records WHERE user_id = ? AND note IS NOT NULL AND note <> ? AND note LIKE ? ORDER BY jalali_date DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset, 'iss', array((int) $user_id, '', '%' . $q . '%'));
        } else {
            $raw = joma_query('SELECT * FROM joma_mood_records WHERE user_id = ? AND note IS NOT NULL AND note <> ? ORDER BY jalali_date DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset, 'is', array((int) $user_id, ''));
        }
        return is_array($raw) ? $raw : array();
    }
    $data = store_load();
    foreach ($data['moods'] as $m) {
        if ((int) $m['user_id'] !== (int) $user_id) continue;
        $note = isset($m['note']) ? trim((string) $m['note']) : '';
        if ($note === '') continue;
        if ($q !== '' && strpos($note, $q) === false) continue;
        $rows[] = $m;
    }
    usort($rows, 'joma_journal_cmp_date_desc');
    return array_slice($rows, (int) $offset, (int) $limit);
}

function joma_journal_cmp_date_desc($a, $b) {
    return strcmp((string) $b['jalali_date'], (string) $a['jalali_date']);
}

/** تعداد یادداشت‌ها (برای صفحه‌بندی). */
function joma_journal_notes_count($user_id, $q = '') {
    if (store_mode() === 'mysql') {
        if ($q !== '') {
            $r = joma_query_one('SELECT COUNT(*) AS c FROM joma_mood_records WHERE user_id = ? AND note IS NOT NULL AND note <> ? AND note LIKE ?', 'iss', array((int) $user_id, '', '%' . $q . '%'));
        } else {
            $r = joma_query_one('SELECT COUNT(*) AS c FROM joma_mood_records WHERE user_id = ? AND note IS NOT NULL AND note <> ?', 'is', array((int) $user_id, ''));
        }
        return $r ? (int) $r['c'] : 0;
    }
    $n = 0;
    $data = store_load();
    foreach ($data['moods'] as $m) {
        if ((int) $m['user_id'] !== (int) $user_id) continue;
        $note = isset($m['note']) ? trim((string) $m['note']) : '';
        if ($note === '') continue;
        if ($q !== '' && strpos($note, $q) === false) continue;
        $n++;
    }
    return $n;
}

/* ------------------------------------------------------------------ */
/* «دیده‌شدن» بینش‌ها (نشانگر تازه در منو)                              */
/* ------------------------------------------------------------------ */

function joma_journal_seen_key($user_id) {
    return 'journal_seen_' . (int) $user_id;
}

function joma_journal_mark_seen($user_id, $stamp) {
    if (function_exists('joma_kv_set')) joma_kv_set(joma_journal_seen_key($user_id), (string) $stamp);
}

function joma_journal_unseen_count($user_id, $insights) {
    if (!function_exists('joma_kv_get') || !is_array($insights)) return 0;
    $seen = joma_kv_get(joma_journal_seen_key($user_id));
    $n = 0;
    foreach ($insights as $in) {
        $at = isset($in['evidence']['computedAt']) ? (string) $in['evidence']['computedAt'] : '';
        if ($seen === null || $seen === '' || strcmp($at, (string) $seen) > 0) $n++;
    }
    return $n;
}
