<?php
/**
 * JOMA — پوستهٔ صفحه‌ها (طرح تازه، نسخهٔ ۲۰)
 * قرارداد قبلی دست‌نخورده است: joma_header($title, $crumbs, $opts) و joma_footer().
 * هر صفحه‌ای که قبلاً کار می‌کرد، با همین پوسته هم کار می‌کند.
 * اگر فایل v2_shell.php نبود، همه‌چیز با پیش‌فرض‌های ساده ادامه می‌یابد (fail-open).
 */

$_joma_v2_shell = dirname(__FILE__) . '/v2_shell.php';
if (is_file($_joma_v2_shell)) require_once $_joma_v2_shell;
$_joma_v2_sprite = dirname(__FILE__) . '/v2_sprite.php';
if (is_file($_joma_v2_sprite)) require_once $_joma_v2_sprite;
unset($_joma_v2_shell, $_joma_v2_sprite);

if (!function_exists('joma_v2_icon')) {
    function joma_v2_icon($id, $cls = 'ic') { return ''; }
}
if (!function_exists('joma_v2_nav_groups')) {
    function joma_v2_nav_groups() { return array('main' => array(), 'more' => array()); }
}
if (!function_exists('joma_v2_tabbar_items')) {
    function joma_v2_tabbar_items() { return array(); }
}
if (!function_exists('joma_v2_hammasir_active')) {
    function joma_v2_hammasir_active() { return false; }
}
if (!function_exists('joma_v2_pagehead')) {
    function joma_v2_pagehead($kicker, $title, $sub = '', $chip = null) {
        echo '<div class="pagehead"><div><h1>' . e($title) . '</h1>';
        if ($sub !== '') echo '<div class="sub">' . e($sub) . '</div>';
        echo '</div></div>';
    }
}
if (!function_exists('joma_v2_greeting')) {
    function joma_v2_greeting($user) { return 'سلام'; }
}

/** آیتم ناوبری */
function joma_v2_navlink($key, $item, $page) {
    $on = ($page === $key) ? ' on' : '';
    return '<a class="' . trim($on) . '" href="' . e(joma_url('index.php?p=' . $key)) . '">'
        . joma_v2_icon($item[1]) . '<span>' . e($item[0]) . '</span></a>';
}

function joma_header($title, $crumbs = array(), $opts = array()) {
    $u = current_user();
    $page = isset($_GET['p']) ? $_GET['p'] : 'home';
    $compact = false;
    if ($u) {
        $prefs = get_prefs($u['id']);
        $compact = !empty($prefs['compact_cards']);
    }
    $bodyClass = array();
    if ($u) $bodyClass[] = 'authed';
    if ($compact) $bodyClass[] = 'compact';
    if (!empty($opts['public'])) $bodyClass[] = 'is-public';

    echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">';
    echo '<meta name="theme-color" content="#FDFEFB">';
    echo '<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">';
    echo '<title>' . e($title) . ' | جوما</title>';
    echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">';
    echo '<link rel="stylesheet" href="' . e(joma_url('assets/css/joma-v2.css?v=20')) . '">';
    echo '</head><body class="' . e(implode(' ', $bodyClass)) . '">';
    if (function_exists('joma_sprite')) echo joma_sprite();

    if (!empty($opts['public'])) {
        /* ---------- صفحه‌های عمومی (ورود/ثبت‌نام/فراموشی/معرفی) ---------- */
        echo '<div class="public-shell">';
        echo '<div class="public-top">';
        echo '<a class="logo" href="' . e(joma_url('index.php?p=home')) . '"><span class="logo-tile">' . joma_v2_icon('owl-hi', 'lg') . '</span>'
            . '<span><b>جوما</b><small>برنامه. اجرا. فهم.</small></span></a>';
        if ($u) {
            echo '<a class="btn sec sm" href="' . e(joma_url('index.php?p=dashboard')) . '">داشبورد من</a>';
        } else {
            echo '<div class="btn-row">'
                . '<a class="btn sec sm" href="' . e(joma_url('index.php?p=login')) . '">ورود</a>'
                . '<a class="btn sm" href="' . e(joma_url('index.php?p=register')) . '">شروع رایگان</a>'
                . '</div>';
        }
        echo '</div>';
        echo flash_get();
        $GLOBALS['JOMA_V2_SHELL_PUBLIC'] = true;
        return;
    }

    /* ---------- پوستهٔ کاربر واردشده ---------- */
    $groups = joma_v2_nav_groups();
    $hammasirOn = joma_v2_hammasir_active();
    echo '<div class="wrap">';
    echo '<aside class="side">';
    echo '<a class="logo" href="' . e(joma_url('index.php?p=dashboard')) . '"><span class="logo-tile">' . joma_v2_icon('owl-hi', 'lg') . '</span>'
        . '<span><b>جوما</b><small>برنامه. اجرا. فهم.</small></span></a>';

    // کارت دورهٔ فعال
    if ($u) {
        // کارت دورهٔ فعال — اگر خطایی رخ دهد، فقط همین کارت نمی‌آید و صفحه سالم می‌ماند.
        try {
            $key = current_period_key();
            $plan = null;
            $wp = ensure_period($u['id'], $key);
            if ($wp && isset($wp['plan'])) $plan = $wp['plan'];
            echo '<div class="dura"><small>دورهٔ فعال</small><b><span>' . e(jalali_period_label($key)) . '</span>';
            if ($plan) echo status_badge($plan['status']);
            echo '</b></div>';
        } catch (Throwable $e) {
            error_log('v2 side period card skipped: safe load failed.');
        }
    }

    echo '<div class="slabel">مسیر من</div><nav class="nav">';
    foreach ($groups['main'] as $k => $item) echo joma_v2_navlink($k, $item, $page);
    echo '</nav>';
    echo '<div class="slabel">بیشتر</div><nav class="nav">';
    foreach ($groups['more'] as $k => $item) {
        if ($k === 'hammasir' && !$hammasirOn) continue;
        echo joma_v2_navlink($k, $item, $page);
    }
    // میان‌بر مدیر (B1-2)
    if ($u && isset($u['role_key']) && $u['role_key'] === 'admin' && function_exists('has_perm') && has_perm('ADMIN_ACCESS')) {
        echo joma_v2_navlink('admin_recovery', array('بازیابی رمز کاربران', 'i-lock'), $page);
    }
    echo '</nav>';

    if ($u) {
        $initial = '؟';
        $nm = !empty($u['first_name']) ? $u['first_name'] : $u['username'];
        if (function_exists('mb_substr')) $initial = mb_substr($nm, 0, 1, 'UTF-8');
        echo '<a class="sprof" href="' . e(joma_url('index.php?p=profile')) . '">'
            . '<span class="av">' . e($initial) . '</span>'
            . '<span><b>' . e($u['full_name'] ? $u['full_name'] : $u['username']) . '</b><small>پروفایل من</small></span>'
            . joma_v2_icon('i-chev-l', 'chev') . '</a>';
    }
    echo '</aside>';

    echo '<main class="main">';
    $GLOBALS['JOMA_V2_SHELL_PUBLIC'] = false;
    echo flash_get();
}

/**
 * پایان صفحه + نوار پایین موبایل + برگهٔ «بیشتر» + فوتر عمومی.
 * قرارداد قبلی (joma_footer()) دست‌نخورده است.
 */
function joma_footer() {
    $u = current_user();
    $page = isset($_GET['p']) ? $_GET['p'] : 'home';
    $public = !empty($GLOBALS['JOMA_V2_SHELL_PUBLIC']);
    if ($public) {
        echo '</div>'; // بستن .public-shell
    } else {
        echo '</main></div>'; // بستن .main و .wrap
    }

    if ($u && !$public) {
        /* ---------- نوار پایین موبایل + برگهٔ «بیشتر» ---------- */
        echo '<nav class="tabbar">';
        foreach (joma_v2_tabbar_items() as $k => $item) {
            $cls = ($page === $k) ? 'on' : '';
            echo '<a class="' . $cls . '" href="' . e(joma_url('index.php?p=' . $k)) . '">'
                . joma_v2_icon($item[1]) . '<span>' . e($item[0]) . '</span></a>';
        }
        echo '<a href="#more" data-more="1">' . joma_v2_icon('i-list') . '<span>بیشتر</span></a>';
        echo '</nav>';

        echo '<div class="mob-sheet" id="mob-sheet"><div class="inner">';
        echo '<div class="mob-head"><b>بیشتر</b><button class="btn ghost sm" type="button" data-more-close="1">بستن</button></div>';
        $groups = joma_v2_nav_groups();
        foreach ($groups['more'] as $k => $item) {
            if ($k === 'hammasir' && !joma_v2_hammasir_active()) continue;
            echo '<a href="' . e(joma_url('index.php?p=' . $k)) . '">' . joma_v2_icon($item[1]) . e($item[0]) . '</a>';
        }
        $extras = array(
            'journal' => array('دفترچهٔ جوما', 'i-book'),
            'periods' => array('دوره‌های من', 'i-cal'),
            'profile' => array('پروفایل من', 'i-eye'),
            'about' => array('درباره جوما', 'i-info'),
            'support' => array('پشتیبانی', 'i-help'),
        );
        foreach ($extras as $k => $item) {
            echo '<a href="' . e(joma_url('index.php?p=' . $k)) . '">' . joma_v2_icon($item[1]) . e($item[0]) . '</a>';
        }
        if (isset($u['role_key']) && $u['role_key'] === 'admin' && function_exists('has_perm') && has_perm('ADMIN_ACCESS')) {
            echo '<a href="' . e(joma_url('index.php?p=admin_recovery')) . '">' . joma_v2_icon('i-lock') . 'بازیابی رمز کاربران</a>';
        }
        echo '<a href="' . e(joma_url('index.php?p=logout')) . '">' . joma_v2_icon('i-x') . 'خروج</a>';
        echo '</div></div>';
    } elseif ($public && $page === 'home') {
        /* فوتر صفحهٔ معرفی */
        echo '<div class="lfoot">';
        echo '<div><b>جوما</b><span>برنامه‌ریزی، اجرا، فهم — محصول مستقل خودمدیریتی.</span></div>';
        echo '<div><b>مسیرهای سریع</b>'
            . '<a href="' . e(joma_url('index.php?p=login')) . '">ورود</a>'
            . '<a href="' . e(joma_url('index.php?p=register')) . '">ساخت حساب</a>'
            . '<a href="' . e(joma_url('index.php?p=forgot')) . '">بازیابی رمز</a></div>';
        echo '<div><b>پشتیبانی</b><span dir="ltr">09967979471</span><span>پیامک و پیام‌رسان بله</span></div>';
        echo '</div>';
        echo '<p class="tiny" style="margin-top:14px">جوما ابزار خودمدیریتی است، نه درمان. اگر حال تو بد است یا فکر آسیب به خودت داری، همین حالا با اورژانس اجتماعی ۱۲۳ یا یک متخصص تماس بگیر.</p>';
        echo '</div>';
    }
    echo '<script src="' . e(joma_url('assets/js/joma-v2.js?v=20')) . '"></script>';
    echo '</body></html>';
}
