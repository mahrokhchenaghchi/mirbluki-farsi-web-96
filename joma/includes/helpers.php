<?php
function e($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function joma_now() {
    return date('Y-m-d H:i:s');
}

function joma_redirect($path) {
    $base = $GLOBALS['JOMA_BASE'];
    header('Location: ' . $base . '/' . ltrim($path, '/'));
    exit;
}

function joma_url($path) {
    $base = $GLOBALS['JOMA_BASE'];
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = isset($_POST['csrf']) ? $_POST['csrf'] : '';
        if (!$t || !isset($_SESSION['csrf']) || $t !== $_SESSION['csrf']) {
            die('درخواست نامعتبر است.');
        }
    }
}

function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
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

function jobs_list() {
    return array('دانش‌آموز','دانشجو','کارمند','مدیر','کارآفرین','پزشک','روانشناس','مهندس','معلم','وکیل','حسابدار','فروشنده','فریلنسر','خانه‌دار','بازنشسته','پژوهشگر','مشاغل آزاد','سایر');
}

function categories_list() {
    return array('سلامت جسم','خواب و استراحت','سلامت روان','تمرکز و ذهن','یادگیری','رشد فردی','روابط','خانواده','ذهن‌آگاهی','مراقبت از خود','بهره‌وری','سبک زندگی');
}

function frequencies_list() {
    return array('DAILY'=>'روزانه','WEEKLY'=>'هفتگی','MONTHLY'=>'ماهانه');
}

function datatypes_list() {
    return array('DURATION'=>'مدت‌زمان','NUMERIC'=>'عددی','BOOLEAN'=>'انجام / عدم انجام','RATING'=>'امتیاز ۱ تا ۵');
}

function units_list() {
    return array('UNIT_MIN'=>'دقیقه','UNIT_HOUR'=>'ساعت','UNIT_GLASS'=>'لیوان','UNIT_TIMES'=>'مرتبه','UNIT_COUNT'=>'عدد','UNIT_SCORE'=>'امتیاز','UNIT_NONE'=>'—');
}

function stickers_list() {
    return array('🏃','💧','🚶','🍎','🌙','⏰','🧘','🌬️','📓','🎯','📵','📝','📚','🛠️','⭐','💑','🙏','☕','📞','🏠','✨','🌸','🛁','🎨','✅','🔥','📅','📖','📗','📘','🥗','🍬','🤸','💭','🌿','💬','🎧','🖥️','🧴','🌇','🔍','🌈');
}

function target_of($row) {
    if ($row['frequency'] === 'WEEKLY') return (float) $row['weekly_target'];
    if ($row['frequency'] === 'MONTHLY') return (float) $row['monthly_target'];
    return (float) $row['daily_target'];
}

function format_value($type, $value, $unit) {
    if ($type === 'BOOLEAN') return ((float) $value >= 1) ? 'انجام شد' : 'انجام نشد';
    if ($type === 'RATING') return 'امتیاز ' . $value;
    $u = units_list();
    $label = isset($u[$unit]) ? $u[$unit] : '';
    return $value . ($label && $label !== '—' ? ' ' . $label : '');
}

function plan_editable($status) {
    return $status === 'DRAFT' || $status === 'PLANNING';
}

function status_label($s) {
    $m = array('DRAFT'=>'پیش‌نویس','PLANNING'=>'آماده‌سازی','RUNNING'=>'در حال اجرا','ARCHIVED'=>'بایگانی');
    return isset($m[$s]) ? $m[$s] : $s;
}
