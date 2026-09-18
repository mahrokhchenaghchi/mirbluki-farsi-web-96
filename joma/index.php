<?php
require dirname(__FILE__) . '/includes/bootstrap.php';
$p = isset($_GET['p']) ? $_GET['p'] : 'home';
$allowed = array(
    'home', 'login', 'register', 'forgot', 'logout',
    'mood', 'dashboard', 'plan', 'today', 'library',
    'periods', 'period', 'reports', 'profile', 'settings', 'about', 'support',
    'jooje', // (B5) جوجهٔ من — مرحلهٔ ۱
);
if (!in_array($p, $allowed, true)) $p = 'home';
maybe_mood_gate($p);
$file = dirname(__FILE__) . '/pages/' . $p . '.php';
if (!file_exists($file)) $p = 'home';
require dirname(__FILE__) . '/pages/' . $p . '.php';
