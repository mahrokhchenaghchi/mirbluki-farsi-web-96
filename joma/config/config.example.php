<?php
// این فایل را کپی کنید و نام آن را config.php بگذارید.
// مقادیر داخل گیومه را با اطلاعات واقعی هاست خودتان عوض کنید.
// رمز واقعی دیتابیس را اینجا در نسخه نمونه نگذارید.

$JOMA_CONFIG = array(
    // معمولاً روی cPanel همین localhost است
    'db_host' => 'localhost',

    // نام دیتابیسی که در cPanel ساخته‌اید (مثال: user_jomadb)
    'db_name' => 'YOUR_DATABASE_NAME',

    // نام کاربر دیتابیس که در cPanel ساخته‌اید (مثال: user_jomauser)
    'db_user' => 'YOUR_DATABASE_USER',

    // رمز همان کاربر دیتابیس — فقط روی سرور خودتان وارد کنید
    'db_pass' => 'YOUR_DATABASE_PASSWORD',

    // مسیر برنامه روی دامنه. برای mirbolouki.com/joma همین مقدار را بگذارید
    'base_url' => '/joma',

    // برای نصب نهایی روی هاست: mysql
    // برای تست بدون دیتابیس: file
    'storage' => 'mysql', // mysql | file
);
