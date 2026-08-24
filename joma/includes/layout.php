<?php
function joma_header($title, $crumbs = array()) {
    $u = current_user();
    $base = joma_url('');
    echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' | جوما</title>';
    echo '<link rel="stylesheet" href="' . e(joma_url('assets/css/joma.css')) . '">';
    echo '</head><body>';
    if ($u) {
        echo '<div class="topbar">حالت تست محلی — داده روی همین سرور ذخیره می‌شود و Deploy نشده است.</div>';
        echo '<aside class="side">';
        echo '<div class="brand">جوما</div>';
        $nav = array(
            'dashboard'=>'داشبورد','plan'=>'برنامه من','today'=>'امروز','mood'=>'خلق من',
            'periods'=>'دوره‌های من','reports'=>'گزارش‌ها','library'=>'کتابخانه فعالیت‌ها',
            'profile'=>'پروفایل من','settings'=>'تنظیمات','about'=>'درباره جوما','support'=>'پشتیبانی',
        );
        foreach ($nav as $k=>$lab) {
            echo '<a href="' . e(joma_url('index.php?p=' . $k)) . '">' . e($lab) . '</a>';
        }
        echo '<a class="out" href="' . e(joma_url('index.php?p=logout')) . '">خروج</a>';
        echo '</aside><main class="wrap">';
        echo '<div class="crumbs"><a href="javascript:history.back()">بازگشت</a>';
        foreach ($crumbs as $c) {
            echo ' / ';
            if (!empty($c['href'])) echo '<a href="' . e($c['href']) . '">' . e($c['label']) . '</a>';
            else echo '<span>' . e($c['label']) . '</span>';
        }
        echo '</div>';
    } else {
        echo '<main class="public">';
    }
}

function joma_footer() {
    echo '</main><nav class="mobile">';
    echo '<a href="' . e(joma_url('index.php?p=dashboard')) . '">خانه</a>';
    echo '<a href="' . e(joma_url('index.php?p=today')) . '">امروز</a>';
    echo '<a href="' . e(joma_url('index.php?p=mood')) . '">خلق</a>';
    echo '<a href="' . e(joma_url('index.php?p=reports')) . '">گزارش</a>';
    echo '</nav><script src="' . e(joma_url('assets/js/joma.js')) . '"></script></body></html>';
}
