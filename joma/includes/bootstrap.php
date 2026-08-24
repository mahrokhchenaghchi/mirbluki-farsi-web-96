<?php
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$preview = (getenv('JOMA_PREVIEW') === '1') || (strpos($host, 'e2b.app') !== false);
$sessionDir = dirname(__FILE__) . '/../data/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0775, true);
}
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.save_path', $sessionDir);
    if ($preview) {
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_samesite', 'None');
        ini_set('session.cookie_path', '/');
    }
    session_start();
}
$configFile = dirname(__FILE__) . '/../config/config.php';
if (file_exists($configFile)) {
    require $configFile;
} else {
    $JOMA_CONFIG = array('storage' => 'file', 'base_url' => '/joma');
}
$GLOBALS['JOMA_CONFIG'] = $JOMA_CONFIG;
require dirname(__FILE__) . '/jalali.php';
require dirname(__FILE__) . '/helpers.php';
$GLOBALS['JOMA_BASE'] = joma_compute_base();
require dirname(__FILE__) . '/store.php';
require dirname(__FILE__) . '/../functions/joma.php';
require dirname(__FILE__) . '/layout.php';
