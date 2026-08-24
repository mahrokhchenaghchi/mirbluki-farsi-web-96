<?php
function store_mode() {
    return isset($GLOBALS['JOMA_CONFIG']['storage']) ? $GLOBALS['JOMA_CONFIG']['storage'] : 'file';
}

function store_path() {
    return dirname(__FILE__) . '/../data/store.json';
}

function store_load() {
    $path = store_path();
    if (!file_exists($path)) {
        $init = array(
            'users' => array(),
            'preferences' => array(),
            'activities' => array(),
            'periods' => array(),
            'plans' => array(),
            'plan_activities' => array(),
            'events' => array(),
            'moods' => array(),
            'projections' => array(),
            'seq' => 1,
        );
        $seed = json_decode(file_get_contents(dirname(__FILE__) . '/../database/library_official.json'), true);
        foreach ($seed as $row) {
            $row['id'] = $init['seq']++;
            $row['user_id'] = null;
            $row['is_seed'] = 1;
            $row['created_at'] = date('c');
            $row['updated_at'] = date('c');
            $init['activities'][] = $row;
        }
        store_save($init);
        return $init;
    }
    return json_decode(file_get_contents($path), true);
}

function store_save($data) {
    $dir = dirname(store_path());
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    file_put_contents(store_path(), json_encode($data, JSON_UNESCAPED_UNICODE));
}

function store_next_id(&$data) {
    $id = isset($data['seq']) ? (int) $data['seq'] : 1;
    $data['seq'] = $id + 1;
    return $id;
}

function db() {
    static $mysqli = null;
    if (store_mode() !== 'mysql') return null;
    if ($mysqli) return $mysqli;
    $c = $GLOBALS['JOMA_CONFIG'];
    $mysqli = @mysqli_connect($c['db_host'], $c['db_user'], $c['db_pass'], $c['db_name']);
    if (!$mysqli) {
        die('اتصال پایگاه داده برقرار نشد.');
    }
    mysqli_set_charset($mysqli, 'utf8mb4');
    return $mysqli;
}

function store_role_permissions($role) {
    $base = array('VIEW_DASHBOARD','CREATE_PLAN','EDIT_PLAN','RECORD_PERFORMANCE','VIEW_REPORT','VIEW_HISTORY','MANAGE_ACTIVITY_LIBRARY');
    if ($role === 'admin') {
        $base[] = 'MANAGE_USERS';
        $base[] = 'ADMIN_ACCESS';
    }
    return $base;
}
