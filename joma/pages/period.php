<?php
require_login();
$key = isset($_GET['period']) ? $_GET['period'] : jalali_period_key(jalali_today());
joma_redirect('index.php?p=reports&period=' . urlencode($key));
