<?php
/**
 * جوما — نقش‌ها و جابه‌جایی نقش (بستهٔ C2 — سند ۲۴)
 * ------------------------------------------------------------------
 * قاعده‌های همین فایل، عیناً طبق سند:
 *   • منبع حالت، همان چیزی است که ماژول هم‌مسیر نگه داشته (نشست + user_flags.mode).
 *     فرانت از role_key حدس نمی‌زند و هیچ رشتهٔ counselor/moshaver نمی‌سازد.
 *   • قابلیت مشاور = ردیف ACTIVE در هم‌مسیر (نه role_key).
 *   • کاربر تک‌نقشه حتی یک پیکسل از کلید نقش نمی‌بیند.
 *   • جابه‌جایی فقط ناوبری را عوض می‌کند؛ هیچ مجوزی اینجا ساخته نمی‌شود.
 * این فایل فقط «خواندن» است؛ نوشتن حالت، از مسیر موجود ماژول (mode_set) انجام می‌شود.
 */

/** آیا توابع ماژول هم‌مسیر در دسترس است؟ (fail-closed؛ فقط با Flag روشن) */
function joma_companion_module_ready() {
    if (function_exists('hammasir_active_mode')) return true;
    $cfgFile = dirname(__FILE__) . '/../config/hammasir_config.php';
    if (!is_file($cfgFile)) return false;
    $HAMMASIR_CONFIG = array();
    include $cfgFile;
    if (empty($HAMMASIR_CONFIG['hammasir_enabled'])) return false;
    $fn = dirname(__FILE__) . '/hammasir.php';
    if (!is_file($fn)) return false;
    if (!function_exists('store_mode') || !function_exists('current_user')) return false;
    include_once $fn;
    return function_exists('hammasir_active_mode');
}

/** برچسب نمایشی نقش‌ها (تنها نقطهٔ تعریف متن) */
function joma_role_labels() {
    return array('client' => 'کاربری', 'provider' => 'مشاور', 'admin' => 'مدیر');
}

/** نقش‌هایی که کاربر «واقعاً» دارد — کاربری همیشه هست */
function joma_roles_available() {
    $out = array('client');
    if (joma_companion_module_ready()) {
        try {
            $cap = hammasir_capabilities();
        } catch (Throwable $e) {
            $cap = array();
        }
        if (!empty($cap['can_provider'])) $out[] = 'provider';
        if (!empty($cap['can_admin'])) $out[] = 'admin';
    }
    return $out;
}

/** حالت فعال — از بک‌اند، بدون حدس */
function joma_role_current() {
    if (!joma_companion_module_ready()) return 'client';
    try {
        $m = hammasir_active_mode();
    } catch (Throwable $e) {
        return 'client';
    }
    return in_array($m, array('client', 'provider', 'admin'), true) ? $m : 'client';
}

/** چند نقشه؟ (تک‌نقشه = هیچ نشانه‌ای در رابط) */
function joma_role_is_multi() {
    return (count(joma_roles_available()) > 1);
}

/** شمار مراجع فعال یک مشاور — از بک‌اند؛ اگر عدد نیست، null (نمایش داده نمی‌شود) */
function joma_role_client_count($user_id) {
    if (!joma_companion_module_ready()) return null;
    try {
        $links = hammasir_links_by_provider((int) $user_id, 'ACTIVE');
        if (!is_array($links)) return null;
        return count($links);
    } catch (Throwable $e) {
        return null;
    }
}

/** داشبورد هر نقش (مقصد جابه‌جایی) */
function joma_role_dashboard_url($role) {
    if ($role === 'provider') return joma_url('index.php?p=hammasir');
    if ($role === 'admin') return joma_url('index.php?p=admin_console');
    return joma_url('index.php?p=dashboard');
}

/** زیرنویس هر نقش در پنل «نقش‌های من» */
function joma_role_sub_label($role, $user_id) {
    if ($role === 'client') return 'داشبورد من';
    if ($role === 'provider') {
        $n = joma_role_client_count($user_id);
        if ($n === null) return 'داشبورد مشاور';
        return fa_num($n) . ' مراجع';
    }
    return 'کنسول';
}

/** در حالت مشاور، صفحه‌های شخصی کنار می‌روند (نه خاکستری، نه قفل) */
function joma_role_hides_personal() {
    return (joma_role_current() === 'provider');
}

/** صفحه‌های شخصیِ ممنوع در حالت مشاور (سند ۲۴ §۲٫۲٫۱) */
function joma_role_personal_pages() {
    return array('mood', 'jooje', 'plan');
}

/**
 * گارد آدرس مستقیم: در حالت مشاور، صفحه‌های شخصی باز نمی‌شوند.
 * از Router صدا زده می‌شود (index.php) — یک نقطه، نه سه فایلِ صفحه.
 */
function joma_role_guard($page) {
    if (!in_array($page, joma_role_personal_pages(), true)) return;
    if (!joma_role_hides_personal()) return;
    $view = dirname(__FILE__) . '/../includes/v2_roles.php';
    if (is_file($view)) require_once $view;
    if (function_exists('joma_v2_role_guard_render')) {
        joma_v2_role_guard_render($page);
        exit;
    }
    // اگر لایهٔ نمایش نبود: پیام کوتاه، بدون صفحهٔ خالی
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><title>حالت مشاور</title></head><body>';
    echo '<p>این صفحه در حالت مشاور باز نمی‌شود</p>';
    echo '</body></html>';
    exit;
}
