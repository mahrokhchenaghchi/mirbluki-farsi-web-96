<?php
require dirname(__FILE__) . '/includes/bootstrap.php';
$p = isset($_GET['p']) ? $_GET['p'] : 'home';
$allowed = array('home','login','register','logout','mood','dashboard','plan','today','library','periods','period','reports','profile','settings','about','support');
if (!in_array($p, $allowed, true)) $p = 'home';
$file = dirname(__FILE__) . '/pages/' . $p . '.php';
if (!file_exists($file)) $p = 'home';
require dirname(__FILE__) . '/pages/' . $p . '.php';
