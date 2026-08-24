<?php
// پیش‌فرض بسته تحویل: تست بدون MySQL.
// برای Production این فایل را از روی config.example.php بسازید و storage=mysql بگذارید.
// رمز واقعی دیتابیس را در این فایل نمونه نگذارید.

$JOMA_CONFIG = array(
    'db_host' => 'localhost',
    'db_name' => 'joma_db',
    'db_user' => 'joma_user',
    'db_pass' => '',
    'base_url' => '/joma',
    'storage' => 'file',
);
