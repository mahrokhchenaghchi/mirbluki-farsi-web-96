<?php
/**
 * JOMA — پوستهٔ طرح تازه (نسخهٔ ۲۰) — کمک‌تابع‌ها
 * این فایل هیچ منطق بک‌اندی ندارد؛ فقط ساخت ناوبری، سرصفحه و آیکون‌ها.
 * اگر نبود، layout به طرح قدیمی برمی‌گردد (fail-open).
 */

/** آیکون از اسپرایت: joma_v2_icon('i-heart', 'ic lg') */
function joma_v2_icon($id, $cls = 'ic') {
    return '<svg class="' . e($cls) . '" aria-hidden="true"><use href="#' . e($id) . '"/></svg>';
}

/** گروه‌های ناوبری طبق SPEC/02-navigation (مسیر من / بیشتر). */
function joma_v2_nav_groups() {
    return array(
        'main' => array(
            'dashboard' => array('خانه', 'i-compass'),
            'today' => array('کارهای امروز', 'i-checkc'),
            'mood' => array('حال من', 'i-heart'),
            'jooje' => array('جوجهٔ من', 'i-list'),
            'journal' => array('دفترچهٔ جوما', 'i-book'),
            'reports' => array('گزارش‌ها', 'i-chart'),
        ),
        'more' => array(
            'plan' => array('برنامهٔ من', 'i-target'),
            'library' => array('کتابخانه', 'i-list'),
            'learn' => array('آموزش', 'i-grad'),
            'hammasir' => array('هم‌مسیر', 'i-users'),
            'settings' => array('تنظیمات', 'i-gear'),
            'rights' => array('حقوق داده', 'i-file'),
        ),
    );
}

/** ۵ مقصد نوار پایین موبایل */
function joma_v2_tabbar_items() {
    return array(
        'dashboard' => array('خانه', 'i-compass'),
        'today' => array('امروز', 'i-checkc'),
        'mood' => array('حال', 'i-heart'),
        'jooje' => array('جوجهٔ من', 'i-list'),
        'reports' => array('گزارش', 'i-chart'),
    );
}

/** آیا صفحهٔ «هم‌مسیر» باید در ناوبری باشد؟ (فقط اگر ماژول فعال باشد) */
function joma_v2_hammasir_active() {
    try {
        $cfgFile = dirname(__FILE__) . '/../config/hammasir_config.php';
        if (!is_file($cfgFile)) return false;
        $HAMMASIR_CONFIG = array();
        include $cfgFile;
        return !empty($HAMMASIR_CONFIG['hammasir_enabled']);
    } catch (Throwable $e) {
        return false;
    }
}

/** کیکر + تیتر + زیرنویس + تراشهٔ تاریخ */
function joma_v2_pagehead($kicker, $title, $sub = '', $chip = null) {
    echo '<div class="pagehead">';
    echo '<div>';
    if ($kicker !== '') echo '<span class="kicker">' . e($kicker) . '</span>';
    echo '<h1>' . e($title) . '</h1>';
    if ($sub !== '') echo '<div class="sub">' . e($sub) . '</div>';
    echo '</div>';
    if ($chip === null && function_exists('jalali_format')) {
        $chip = joma_v2_icon('i-cal') . '<span>' . e(jalali_weekday_name(jalali_today()) . ' ' . jalali_format(jalali_today())) . '</span>';
    }
    if ($chip !== '') echo '<span class="datechip">' . $chip . '</span>';
    echo '</div>';
}

/** سلام هوشمند با نام کوچک (طبق سند: فامیلی هرگز) */
function joma_v2_greeting($user) {
    $name = '';
    if (!empty($user['first_name'])) $name = $user['first_name'];
    elseif (!empty($user['full_name'])) $name = $user['full_name'];
    elseif (!empty($user['username'])) $name = $user['username'];
    $h = (int) date('G');
    if ($h < 12) $part = 'صبح بخیر';
    elseif ($h < 17) $part = 'ظهر بخیر';
    elseif ($h < 20) $part = 'عصر بخیر';
    else $part = 'شب بخیر';
    return ($name !== '') ? ('سلام ' . $name . '، ' . $part) : ('سلام، ' . $part);
}

/** ایموجی/تصویر وقت روز (خورشید صبح، خورشید کامل، خورشید نزدیک افق، ماه) */
function joma_v2_time_icon_id() {
    $h = (int) date('G');
    if ($h >= 5 && $h < 11) return 'i-sun';
    if ($h >= 11 && $h < 16) return 'i-sun';
    if ($h >= 16 && $h < 20) return 'i-sun';
    return 'i-moon';
}
