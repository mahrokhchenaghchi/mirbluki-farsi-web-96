<?php
function e($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function joma_now() {
    return date('Y-m-d H:i:s');
}

function joma_compute_base() {
    if (!empty($_SERVER['SCRIPT_NAME'])) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $dir = rtrim(dirname($script), '/');
        if ($dir === '' || $dir === '.' || $dir === '/') return '';
        return $dir;
    }
    if (!empty($GLOBALS['JOMA_CONFIG']['base_url'])) {
        return rtrim($GLOBALS['JOMA_CONFIG']['base_url'], '/');
    }
    return '';
}

function joma_needs_sid() {
    $name = session_name();
    return empty($_COOKIE[$name]);
}

function joma_append_sid($url) {
    if ($url === '' || strpos($url, 'joma_sid=') !== false) return $url;
    if (strpos($url, 'javascript:') === 0) return $url;
    $sid = session_id();
    if ($sid === '' || !joma_needs_sid()) return $url;
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'joma_sid=' . rawurlencode($sid);
}

function joma_redirect($path) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header('Location: ' . joma_url($path));
    exit;
}

function joma_url($path) {
    $base = $GLOBALS['JOMA_BASE'];
    $url = rtrim($base, '/') . '/' . ltrim($path, '/');
    if (strpos($path, 'assets/') === 0) return $url;
    return joma_append_sid($url);
}

function joma_asset($path) {
    return joma_url('assets/' . ltrim($path, '/'));
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'
        . '<input type="hidden" name="joma_sid" value="' . e(session_id()) . '">';
}

function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = isset($_POST['csrf']) ? $_POST['csrf'] : '';
        if (!$t || !isset($_SESSION['csrf']) || $t !== $_SESSION['csrf']) {
            http_response_code(400);
            die('درخواست نامعتبر است.');
        }
    }
}

function current_user() {
    if (!isset($_SESSION['user'])) return null;
    $u = $_SESSION['user'];
    // (B1 — مرحلهٔ ۲): پس از تغییر رمز، نشست‌های قدیمی خودکار باطل می‌شوند.
    // اگر ماژول بازیابی نباشد یا شمارنده صفر باشد، هیچ‌کس بیرون نمی‌رود (رفتار قبلی).
    if (is_array($u) && function_exists('joma_auth_epoch_outdated') && joma_auth_epoch_outdated($u)) {
        unset($_SESSION['user']);
        $_SESSION['auth_epoch_boot'] = 1;
        return null;
    }
    return $u;
}

function require_login() {
    if (!current_user()) {
        joma_redirect('index.php?p=login');
    }
}

function has_perm($perm) {
    $u = current_user();
    if (!$u) return false;
    $map = store_role_permissions($u['role_key']);
    return in_array($perm, $map, true);
}

function require_perm($perm) {
    require_login();
    if (!has_perm($perm)) {
        die('دسترسی مجاز نیست.');
    }
}

function current_period_key() {
    if (!empty($_GET['period']) && preg_match('/^\d{4}-\d{2}$/', $_GET['period'])) {
        $_SESSION['period_key'] = $_GET['period'];
        return $_GET['period'];
    }
    if (!empty($_SESSION['period_key']) && preg_match('/^\d{4}-\d{2}$/', $_SESSION['period_key'])) {
        return $_SESSION['period_key'];
    }
    return jalali_period_key(jalali_today());
}

function set_current_period_key($key) {
    if (preg_match('/^\d{4}-\d{2}$/', $key)) {
        $_SESSION['period_key'] = $key;
    }
}

function maybe_mood_gate($page) {
    // (تصمیم مالک — B2): گیت ثبت حال فقط روی صفحهٔ «امروز» می‌ماند.
    // برنامه، گزارش‌ها و بقیهٔ صفحات آزادند؛ حال یک دعوت است نه مانع.
    if ($page !== 'today') return;
    $u = current_user();
    if (!$u) return;
    if (!get_mood($u['id'], jalali_today())) {
        joma_redirect('index.php?p=mood');
    }
}

function jobs_list() {
    return array('دانش‌آموز', 'دانشجو', 'کارمند', 'مدیر', 'کارآفرین', 'پزشک', 'روانشناس', 'مهندس', 'معلم', 'وکیل', 'حسابدار', 'فروشنده', 'فریلنسر', 'خانه‌دار', 'بازنشسته', 'پژوهشگر', 'مشاغل آزاد', 'سایر');
}

function categories_list() {
    return array('سلامت جسم', 'خواب و استراحت', 'سلامت روان', 'تمرکز و ذهن', 'یادگیری', 'رشد فردی', 'روابط', 'خانواده', 'ذهن‌آگاهی', 'مراقبت از خود', 'بهره‌وری', 'سبک زندگی');
}

function frequencies_list() {
    return array('DAILY' => 'روزانه', 'WEEKLY' => 'هفتگی', 'MONTHLY' => 'ماهانه');
}

function datatypes_list() {
    return array('DURATION' => 'مدت‌زمان', 'NUMERIC' => 'عددی', 'BOOLEAN' => 'انجام / عدم انجام', 'RATING' => 'امتیاز ۱ تا ۵');
}

function units_list() {
    return array(
        'UNIT_MIN' => 'دقیقه',
        'UNIT_HOUR' => 'ساعت',
        'UNIT_GLASS' => 'لیوان',
        'UNIT_GLA' => 'لیوان',
        'UNIT_TIMES' => 'مرتبه',
        'UNIT_TIM' => 'مرتبه',
        'UNIT_COUNT' => 'عدد',
        'UNIT_COU' => 'عدد',
        'UNIT_SCORE' => 'امتیاز',
        'UNIT_SCO' => 'امتیاز',
        'UNIT_NONE' => '—',
        'UNIT_HOU' => 'ساعت',
    );
}

function stickers_list() {
    return array('🏃', '💧', '🚶', '🍎', '🌙', '⏰', '🧘', '🌬️', '📓', '🎯', '📵', '📝', '📚', '🛠️', '⭐', '💑', '🙏', '☕', '📞', '🏠', '✨', '🌸', '🛁', '🎨', '✅', '🔥', '📅', '📖', '📗', '📘', '🥗', '🍬', '🤸', '💭', '🌿', '💬', '🎧', '🖥️', '🧴', '🌇', '🔍', '🌈', '🪞', '🧭');
}

function target_of($row) {
    if ($row['frequency'] === 'WEEKLY') return (float) $row['weekly_target'];
    if ($row['frequency'] === 'MONTHLY') return (float) $row['monthly_target'];
    return (float) $row['daily_target'];
}

function format_value($type, $value, $unit) {
    if ($type === 'BOOLEAN') return ((float) $value >= 1) ? 'انجام شد' : 'انجام نشد';
    if ($type === 'RATING') return 'امتیاز ' . fa_num($value);
    $u = units_list();
    $label = isset($u[$unit]) ? $u[$unit] : '';
    return fa_num($value) . ($label && $label !== '—' ? ' ' . $label : '');
}

function plan_editable($status) {
    return $status === 'DRAFT' || $status === 'PLANNING';
}

function status_label($s) {
    $m = array('DRAFT' => 'پیش‌نویس', 'PLANNING' => 'آماده‌سازی', 'RUNNING' => 'در حال اجرا', 'ARCHIVED' => 'بایگانی');
    return isset($m[$s]) ? $m[$s] : $s;
}

function status_badge($s) {
    $cls = 'badge badge-' . strtolower($s);
    return '<span class="' . e($cls) . '">' . e(status_label($s)) . '</span>';
}

function role_label($k) {
    $m = array('member' => 'عضو', 'plus' => 'پلاس', 'coach' => 'مربی', 'admin' => 'مدیر');
    return isset($m[$k]) ? $m[$k] : $k;
}

function access_label($n) {
    $m = array(1 => 'عضو', 2 => 'پلاس', 3 => 'مربی', 4 => 'مدیر');
    return isset($m[(int) $n]) ? $m[(int) $n] : (string) $n;
}

function greeting_fa() {
    $h = (int) date('G');
    if ($h < 12) return 'صبح بخیر';
    if ($h < 18) return 'وقت بخیر';
    return 'عصر بخیر';
}

function joma_contains($hay, $needle) {
    if ($needle === '' || $needle === null) return true;
    if (function_exists('mb_stripos')) {
        return mb_stripos((string) $hay, (string) $needle, 0, 'UTF-8') !== false;
    }
    return stripos((string) $hay, (string) $needle) !== false;
}

function unspecified_notice($text) {
    return '<div class="notice-unspecified">' . $text . '</div>';
}

function empty_state($title, $desc, $href = '', $cta = '') {
    $html = '<div class="empty card"><div class="empty-ico">🌱</div><h3>' . e($title) . '</h3><p>' . e($desc) . '</p>';
    if ($href !== '') {
        $html .= '<a class="btn" href="' . e($href) . '">' . e($cta) . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function joma_logo($size = 56, $compact = false) {
    $candidates = array(
        dirname(__FILE__) . '/../assets/images/logo.jpg',
        dirname(__FILE__) . '/../../public/logo.jpg',
    );
    $src = '';
    foreach ($candidates as $file) {
        if (file_exists($file)) {
            if (strpos($file, '/public/') !== false) {
                $src = joma_url('../public/logo.jpg');
            } else {
                $src = joma_asset('images/logo.jpg');
            }
            break;
        }
    }
    $html = '<a class="logo" href="' . e(joma_url(current_user() ? 'index.php?p=dashboard' : 'index.php?p=home')) . '">';
    if ($src !== '') {
        $html .= '<img src="' . e($src) . '" alt="لوگوی جوما" width="' . (int) $size . '" height="' . (int) $size . '">';
    } else {
        $html .= '<span class="logo-mark" style="width:' . (int) $size . 'px;height:' . (int) $size . 'px">ج</span>';
    }
    if (!$compact) {
        $html .= '<span class="logo-txt"><strong>جوما</strong><small>برنامه. اجرا. فهم.</small></span>';
    }
    $html .= '</a>';
    return $html;
}

function flash_set($type, $msg) {
    $_SESSION['flash'] = array('type' => $type, 'msg' => $msg);
}

function flash_get() {
    if (empty($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $cls = $f['type'] === 'ok' ? 'toast ok' : 'toast bad';
    return '<div class="' . $cls . '">' . e($f['msg']) . '</div>';
}

function nav_items() {
    return array(
        'dashboard' => array('داشبورد', '🏠'),
        'today' => array('امروز', '📝'),
        'mood' => array('خلق من', '💗'),
        'reports' => array('گزارش‌ها', '📊'),
        'jooje' => array('جوجهٔ من', '🐣'), // (B5) جوجهٔ من — مرحلهٔ ۱
        'plan' => array('برنامه من', '🗂️'),
        'periods' => array('دوره‌های من', '📅'),
        'library' => array('کتابخانه فعالیت‌ها', '📚'),
        'profile' => array('پروفایل من', '👤'),
        'settings' => array('تنظیمات', '⚙️'),
        'about' => array('درباره جوما', '🌸'),
        'support' => array('پشتیبانی', '💬'),
    );
}

function sparkline_svg($points, $w = 640, $h = 180) {
    $clean = array();
    foreach ($points as $p) $clean[] = (float) $p;
    if (!$clean) return '';
    $max = max($clean);
    $min = min($clean);
    if ($max === $min) {
        $max = $min + 1;
    }
    $n = count($clean);
    $step = $n > 1 ? ($w - 24) / ($n - 1) : 0;
    $parts = array();
    $circles = '';
    for ($i = 0; $i < $n; $i++) {
        $x = 12 + $i * $step;
        $y = 16 + ($h - 32) * (1 - (($clean[$i] - $min) / ($max - $min)));
        $parts[] = round($x, 1) . ',' . round($y, 1);
        $circles .= '<circle cx="' . round($x, 1) . '" cy="' . round($y, 1) . '" r="4" fill="#7c5cbf"></circle>';
    }
    $line = implode(' ', $parts);
    return '<svg class="spark" viewBox="0 0 ' . (int) $w . ' ' . (int) $h . '" preserveAspectRatio="none" role="img" aria-label="روند">'
        . '<polyline fill="none" stroke="#c4a7e7" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" points="' . $line . '"></polyline>'
        . '<polyline fill="none" stroke="#7c5cbf" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="' . $line . '"></polyline>'
        . $circles . '</svg>';
}

function is_valid_username($v) {
    return (bool) preg_match('/^[a-zA-Z][a-zA-Z0-9._]{2,19}$/', trim($v));
}

function is_valid_email_addr($v) {
    return (bool) filter_var(trim($v), FILTER_VALIDATE_EMAIL);
}

function is_valid_iran_mobile($v) {
    return (bool) preg_match('/^09[0-9]{9}$/', trim($v));
}

function validate_registration($in) {
    if (trim($in['first_name']) === '' || trim($in['last_name']) === '') return 'نام و نام خانوادگی را وارد کنید.';
    if (!is_valid_username($in['username'])) return 'نام کاربری باید با حرف انگلیسی شروع شود و ۳ تا ۲۰ نویسه باشد.';
    if (username_taken($in['username'])) return 'این نام کاربری قبلاً استفاده شده است.';
    if (!is_valid_email_addr($in['email'])) return 'ایمیل معتبر نیست.';
    if (email_taken($in['email'])) return 'این ایمیل قبلاً ثبت شده است.';
    if (!is_valid_iran_mobile($in['phone'])) return 'شماره موبایل باید مانند 09123456789 باشد.';
    if (!in_array($in['job'], jobs_list(), true)) return 'شغل را از فهرست انتخاب کنید.';
    if (strlen($in['password']) < 6) return 'رمز عبور باید حداقل ۶ نویسه باشد.';
    if ($in['password'] !== $in['confirm']) return 'رمز عبور و تکرار آن یکسان نیستند.';
    if (empty($in['accept'])) return 'پذیرش قوانین برای ساخت حساب لازم است.';
    return '';
}

function validate_activity_input($in) {
    if (trim($in['name']) === '') return 'نام فعالیت را وارد کنید.';
    if (!isset($in['weight']) || $in['weight'] < 0) return 'وزن باید صفر یا بزرگ‌تر باشد.';
    $freq = isset($in['frequency']) ? $in['frequency'] : 'DAILY';
    $target = $freq === 'WEEKLY' ? $in['weekly_target'] : ($freq === 'MONTHLY' ? $in['monthly_target'] : $in['daily_target']);
    if (!is_numeric($target) || (float) $target <= 0) return 'هدف باید بزرگ‌تر از صفر باشد.';
    return '';
}

function validate_performance_value($type, $value) {
    if (!is_numeric($value)) return 'مقدار معتبر نیست.';
    $value = (float) $value;
    if ($type === 'RATING' && ($value < 1 || $value > 5)) return 'امتیاز باید بین ۱ و ۵ باشد.';
    if ($type === 'BOOLEAN' && $value !== 0.0 && $value !== 1.0) return 'وضعیت انجام فقط می‌تواند انجام‌شده یا نشده باشد.';
    if ($value < 0) return 'مقدار نمی‌تواند منفی باشد.';
    return '';
}
