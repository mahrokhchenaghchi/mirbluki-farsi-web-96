<?php
/**
 * جوما — کارت دعوت «انتخاب هم‌مسیر» (بستهٔ بندِ ۴)
 * ------------------------------------------------------------------
 * قانون مالک: کارت در «خانهٔ من»، یک‌بار — نه مودال، نه پاپ‌آپ، نه نوار چسبان؛
 * نه پیامک، نه ایمیل، نه اعلان، نه بَج عددی، نه یادآور تکرارشونده.
 *
 * منبع تصمیم: پرچم بک‌اند  companionInvite ∈ READY | DONE | HIDDEN
 *   READY  = کاربری که هرگز ارتباطی نداشته + کارت قبلاً بسته نشده + لحظهٔ معنادار رسیده
 *   DONE   = «بعداً» یا «انتخاب هم‌مسیر» (برای همیشه)
 *   HIDDEN = هر ارتباطی وجود دارد، یا هنوز لحظهٔ معنادار نرسیده، یا روز اول ثبت‌نام است
 *
 * این فایل هیچ‌چیز قفل‌شده‌ای را عوض نمی‌کند؛ فقط می‌خواند و پرچم per-user می‌نویسد
 * (همان storage رسمی ماژول: user_flags — بدون جدول تازه، بدون تغییر schema).
 */

/** توابع این فایل به ماژول هم‌مسیر وابسته‌اند (fail-closed) */
function joma_invite_module_ready() {
    if (function_exists('hammasir_user_flag_get') && function_exists('hammasir_links_any_by_client')) return true;
    if (!function_exists('joma_companion_module_ready')) {
        $f = dirname(__FILE__) . '/companion_roles.php';
        if (is_file($f)) require_once $f;
    }
    if (function_exists('joma_companion_module_ready')) return joma_companion_module_ready();
    return function_exists('hammasir_user_flag_get');
}

/** خواندن یک پرچم per-user با مهار خطا (پیش‌فرض امن: ۰) */
function joma_invite_flag_get($user_id, $key) {
    if (!joma_invite_module_ready()) return 0;
    try {
        return (int) hammasir_user_flag_get((int) $user_id, (string) $key);
    } catch (Throwable $e) {
        return 0;
    }
}

/** نوشتن یک پرچم per-user با مهار خطا */
function joma_invite_flag_set($user_id, $key, $value) {
    if (!joma_invite_module_ready()) return false;
    try {
        return (bool) hammasir_user_flag_set((int) $user_id, (string) $key, $value ? 1 : 0);
    } catch (Throwable $e) {
        return false;
    }
}

/** آیا کاربر روز اول ثبت‌نامش است؟ (روز اول دعوت نمی‌شود) */
function joma_invite_is_first_day($user_id) {
    try {
        $u = get_user((int) $user_id);
        if (!$u || empty($u['created_at'])) return false;
        $created = substr((string) $u['created_at'], 0, 10);
        $p = explode('-', $created);
        if (count($p) !== 3) return false;
        $j = gregorian_to_jalali((int) $p[0], (int) $p[1], (int) $p[2]);
        $jalali = $j[0] . '-' . jalali_pad($j[1]) . '-' . jalali_pad($j[2]);
        return ($jalali === jalali_today());
    } catch (Throwable $e) {
        return false;
    }
}

/** «لحظهٔ معنادار»: برنامه در PLANNING/RUNNING یا حداقل یک ثبت */
function joma_invite_meaningful_moment($user_id) {
    try {
        $key = current_period_key();
        $wp = ensure_period((int) $user_id, $key);
        if (!$wp || empty($wp['plan'])) return false;
        $plan = $wp['plan'];
        if (isset($plan['status']) && in_array($plan['status'], array('PLANNING', 'RUNNING'), true)) return true;
        $events = list_events($plan['id'], (int) $user_id);
        return (is_array($events) && count($events) > 0);
    } catch (Throwable $e) {
        return false;
    }
}

/** آیا کاربر تا حالا «هر» ارتباطی داشته؟ (رد شدن یا قطع شدن هم یعنی داشته) */
function joma_invite_any_link($user_id) {
    if (!joma_invite_module_ready()) return true; // fail-closed: کارت نمایش داده نمی‌شود
    try {
        return (hammasir_links_any_by_client((int) $user_id) === true);
    } catch (Throwable $e) {
        return true;
    }
}

/**
 * پرچم بک‌اند: READY | DONE | HIDDEN
 * هیچ‌جای فرانت این وضعیت را حدس نمی‌زند.
 */
function joma_invite_state($user_id) {
    $user_id = (int) $user_id;
    if (!joma_invite_module_ready()) return 'HIDDEN';
    if (joma_invite_flag_get($user_id, 'invite_done') === 1) return 'DONE';
    if (joma_invite_any_link($user_id)) return 'HIDDEN';
    // در حالت مشاور/مدیر کارت دعوت معنا ندارد
    if (function_exists('joma_role_current') && joma_role_current() !== 'client') return 'HIDDEN';
    if (joma_invite_is_first_day($user_id)) return 'HIDDEN';
    if (!joma_invite_meaningful_moment($user_id)) return 'HIDDEN';
    return 'READY';
}

/** «بعداً» یا «انتخاب هم‌مسیر» → برای همیشه DONE */
function joma_invite_mark_done($user_id) {
    $ok = true;
    if (!joma_invite_flag_set($user_id, 'invite_done', 1)) $ok = false;
    // خاموش‌کردن کارت قدیمی (Onboarding) تا هیچ کارت دومی وجود نداشته باشد
    if (!joma_invite_flag_set($user_id, 'onboarding_seen', 1)) $ok = false;
    return $ok;
}

/** «انتخاب هم‌مسیر»: کارت همان لحظه دیده‌شده می‌شود و راه چهار گام باز می‌شود */
function joma_invite_open_intent($user_id) {
    $ok = true;
    if (!joma_invite_flag_set($user_id, 'invite_done', 1)) $ok = false;
    if (!joma_invite_flag_set($user_id, 'onboarding_seen', 1)) $ok = false;
    if (!joma_invite_flag_set($user_id, 'onboarding_intent', 1)) $ok = false;
    return $ok;
}

/* ------------------------------------------------------------------
 * نشانگر وضعیت ثبت — رضایت جداگانه (سند ۲۰ §۳٫۴)
 * مبنا فقط فعالیت است؛ هرگز حال/خلق/یادداشت. یک‌کلیک خاموش می‌شود.
 * ------------------------------------------------------------------ */

function joma_status_share_get($user_id) {
    return (joma_invite_flag_get($user_id, 'status_share') === 1);
}

function joma_status_share_set($user_id, $on) {
    return joma_invite_flag_set($user_id, 'status_share', $on ? 1 : 0);
}

/* ------------------------------------------------------------------
 * چهار گام دعوت — متن‌های ثابت و سهمیه‌ها (همه از بک‌اند)
 * ------------------------------------------------------------------ */

/** متن رضایت از بک‌اند ماژول (نسخه‌دار) */
function joma_companion_consent_text() {
    if (function_exists('hammasir_consent_text')) {
        try {
            return (string) hammasir_consent_text();
        } catch (Throwable $e) {
            return '';
        }
    }
    return '';
}

function joma_companion_consent_version() {
    if (function_exists('hammasir_consent_version')) {
        try {
            return (string) hammasir_consent_version();
        } catch (Throwable $e) {
            return '';
        }
    }
    return '';
}

/** سهمیه‌های واقعی از policy بک‌اند (اگر نیست، خط سهمیه نمایش داده نمی‌شود) */
function joma_companion_quota_line() {
    $client = function_exists('hammasir_message_daily_limit') ? hammasir_message_daily_limit() : null;
    $companion = function_exists('hammasir_companion_daily_limit') ? hammasir_companion_daily_limit() : null;
    $gap = function_exists('hammasir_message_cooldown_seconds') ? hammasir_message_cooldown_seconds() : null;
    if ($client === null || $companion === null || $gap === null) return '';
    return 'مراجع: ' . fa_num($client) . ' پیام در روز · همراه: ' . fa_num($companion)
        . ' پیام در روز · فاصلهٔ حداقلی: ' . fa_num($gap) . ' ثانیه';
}

/** برچسب‌های گام ۲ — هر مجوز با توضیح خودش (سند ۲۰ §۳٫۵) */
function joma_companion_perm_rows() {
    return array(
        'VIEW_SUMMARY' => array('خلاصهٔ وضعیت و پیشرفت کلی', 'شمار روزهای ثبت‌شده، وضعیت فعالیت‌ها', 'base'),
        'VIEW_PROGRESS' => array('پیشرفت و پوشش تجمیعی', 'درصدها و نمودارهای دوره', 'choice'),
        'VIEW_ACTIVITY_DETAILS' => array('جزئیات فعالیت‌ها', 'کدام فعالیت‌ها را چقدر انجام داده‌ای', 'choice'),
        'VIEW_MOOD' => array('شاخص‌های حال من', 'عددها و نمودارهای حال', 'choice'),
    );
}

/**
 * اعمال سلیقهٔ پیامِ کاربر روی لینک تازه (اختیاری).
 * از primitiveهای رسمی ماژول استفاده می‌کند — هیچ قرارداد تازه‌ای ساخته نمی‌شود:
 * تا وقتی هر دو طرف نخواهند، گفت‌وگو کار نمی‌کند (سند ۲۰ §۵).
 */
function joma_companion_apply_message_prefs($link_id, $client_user_id, $out, $in) {
    if (!joma_invite_module_ready()) return false;
    $out = $out ? 1 : 0;
    $in = $in ? 1 : 0;
    if ($out === 0 && $in === 0) return true;
    $link_id = (int) $link_id;
    $client_user_id = (int) $client_user_id;
    $pairs = array();
    if ($out === 1) $pairs[] = 'CLIENT_CAN_MESSAGE_COMPANION';
    if ($in === 1) $pairs[] = 'COMPANION_CAN_MESSAGE_CLIENT';
    try {
        if (store_mode() === 'mysql') {
            foreach ($pairs as $k) {
                if (!hammasir_db_permission_set($link_id, $k, 1, $client_user_id)) return false;
                if (!hammasir_db_permission_history_add($link_id, $k, 0, 1, $client_user_id, 'INITIAL_CONSENT')) return false;
            }
            return true;
        }
        $res = hammasir_store_mutate(function (&$store) use ($link_id, $client_user_id, $pairs) {
            foreach ($pairs as $k) {
                hammasir_file_permission_set($store, $link_id, $k, 1, $client_user_id);
                hammasir_file_permission_history_add($store, $link_id, $k, 0, 1, $client_user_id, 'INITIAL_CONSENT');
            }
            return 'ok';
        });
        return ($res === 'ok');
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * درخواست همراهی + سلیقه‌های گام ۲.
 * خودِ درخواست را همان تابع رسمی ماژول می‌سازد (هیچ قراردادی عوض نمی‌شود)؛
 * سپس فقط مجوزهای پیامِ انتخابی و پرچم نشانگر وضعیت نوشته می‌شوند.
 */
function joma_companion_link_request($client_user_id, $provider_user_id, $view_perms, $msg_out, $msg_in, $share_status) {
    if (!function_exists('hammasir_link_request')) return 'error';
    $res = hammasir_link_request((int) $client_user_id, (int) $provider_user_id, $view_perms);
    if ($res !== 'ok') return $res;
    // نشانگر وضعیت: کادر مستقل و خاموش‌به‌طور‌پیش‌فرض
    joma_status_share_set($client_user_id, $share_status ? 1 : 0);
    $link = null;
    try {
        $link = hammasir_link_open_by_client((int) $client_user_id);
    } catch (Throwable $e) {
        $link = null;
    }
    if ($link && ($msg_out || $msg_in)) {
        joma_companion_apply_message_prefs((int) $link['id'], (int) $client_user_id, $msg_out, $msg_in);
    }
    return 'ok';
}
