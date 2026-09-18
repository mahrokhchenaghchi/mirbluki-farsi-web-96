<?php
function joma_header($title, $crumbs = array(), $opts = array()) {
    $u = current_user();
    $page = isset($_GET['p']) ? $_GET['p'] : 'home';
    $compact = false;
    $prefs = array();
    if ($u) {
        $prefs = get_prefs($u['id']);
        $compact = !empty($prefs['compact_cards']);
    }
    $bodyClass = array();
    if ($u) $bodyClass[] = 'authed';
    if ($compact) $bodyClass[] = 'compact';
    if (!empty($opts['public'])) $bodyClass[] = 'is-public';
    echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="theme-color" content="#fbf7f2">';
    echo '<title>' . e($title) . ' | جوما</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="stylesheet" href="' . e(joma_url('assets/css/joma.css')) . '">';
    echo '</head><body class="' . e(implode(' ', $bodyClass)) . '">';
    if ($u && empty($opts['public'])) {
        echo '<div class="topbar">حالت تست محلی — داده روی همین سرور ذخیره می‌شود و Deploy نشده است.</div>';
        echo '<aside class="side">';
        echo joma_logo(52);
        echo '<nav class="side-nav">';
        foreach (nav_items() as $k => $item) {
            $cls = $page === $k ? 'nav-link active' : 'nav-link';
            echo '<a class="' . $cls . '" href="' . e(joma_url('index.php?p=' . $k)) . '"><span>' . $item[1] . '</span>' . e($item[0]) . '</a>';
        }
        // (B1 — مرحلهٔ ۲) میان‌بر مدیر برای صدور کد بازیابی رمز — فقط نقش مدیر.
        if (isset($u['role_key']) && $u['role_key'] === 'admin' && function_exists('has_perm') && has_perm('ADMIN_ACCESS')) {
            $__ar_cls = ($page === 'admin_recovery') ? 'nav-link active' : 'nav-link';
            echo '<a class="' . $__ar_cls . '" href="' . e(joma_url('index.php?p=admin_recovery')) . '"><span>🔑</span>بازیابی رمز کاربران</a>';
        }
        echo '</nav>';
        echo '<div class="side-foot">';
        echo '<div class="who"><strong>' . e($u['full_name']) . '</strong><small>@' . e($u['username']) . '</small></div>';
        echo '<a class="btn btn-ghost btn-block" href="' . e(joma_url('index.php?p=logout')) . '">خروج</a>';
        echo '</div></aside>';
        echo '<header class="mob-head">';
        echo joma_logo(40, true);
        echo '<button class="icon-btn" type="button" data-more-toggle aria-label="منو">⋯</button>';
        echo '</header>';
        echo '<div class="mob-more" hidden>';
        $more = array('plan', 'periods', 'library', 'profile', 'settings', 'about', 'support');
        foreach ($more as $k) {
            $item = nav_items();
            echo '<a href="' . e(joma_url('index.php?p=' . $k)) . '">' . e($item[$k][0]) . '</a>';
        }
        echo '<a class="danger" href="' . e(joma_url('index.php?p=logout')) . '">خروج</a>';
        echo '</div>';
        echo '<main class="wrap">';
        echo '<div class="crumbs">';
        echo '<a class="back" href="javascript:history.back()">بازگشت</a>';
        foreach ($crumbs as $c) {
            echo '<span class="sep">/</span>';
            if (!empty($c['href'])) echo '<a href="' . e($c['href']) . '">' . e($c['label']) . '</a>';
            else echo '<span>' . e($c['label']) . '</span>';
        }
        echo '</div>';
        echo flash_get();
    } else {
        echo '<main class="public-shell">';
        echo flash_get();
    }
}

function joma_footer() {
    $u = current_user();
    echo '</main>';
    if ($u) {
        $page = isset($_GET['p']) ? $_GET['p'] : 'dashboard';
        $items = array(
            'dashboard' => array('خانه', '🏠'),
            'today' => array('امروز', '📝'),
            'mood' => array('خلق', '💗'),
            'reports' => array('گزارش', '📊'),
        );
        echo '<nav class="mobile">';
        foreach ($items as $k => $item) {
            $cls = $page === $k ? 'on' : '';
            echo '<a class="' . $cls . '" href="' . e(joma_url('index.php?p=' . $k)) . '"><span>' . $item[1] . '</span>' . e($item[0]) . '</a>';
        }
        echo '</nav>';
    }
    echo '<script src="' . e(joma_url('assets/js/joma.js')) . '"></script></body></html>';
}
