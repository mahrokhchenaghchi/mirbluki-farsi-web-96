<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}
$configFile = dirname(__FILE__) . '/../config/config.php';
if (file_exists($configFile)) {
    require $configFile;
} else {
    $JOMA_CONFIG = array('storage' => 'file', 'base_url' => '/joma');
}
$GLOBALS['JOMA_CONFIG'] = $JOMA_CONFIG;
$GLOBALS['JOMA_BASE'] = isset($JOMA_CONFIG['base_url']) ? rtrim($JOMA_CONFIG['base_url'], '/') : '';
require dirname(__FILE__) . '/jalali.php';
require dirname(__FILE__) . '/helpers.php';
require dirname(__FILE__) . '/store.php';
require dirname(__FILE__) . '/../functions/joma.php';
require dirname(__FILE__) . '/layout.php';
