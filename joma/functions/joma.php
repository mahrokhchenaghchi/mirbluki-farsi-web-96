<?php
function official_library() {
    return json_decode(file_get_contents(dirname(__FILE__) . '/../database/library_official.json'), true);
}

function copy_seed_to_user($user_id) {
    $data = store_load();
    foreach ($data['activities'] as $a) {
        if (!empty($a['user_id']) && (int) $a['user_id'] === (int) $user_id && !empty($a['is_seed'])) return;
    }
    $now = joma_now();
    if (store_mode() === 'mysql') {
        $seed = official_library();
        $sql = 'INSERT INTO joma_activities (user_id,code,group_code,name,category,data_type,track_mode,unit,daily_target,weekly_target,monthly_target,weight,frequency,sticker,color,status,is_seed,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)';
        foreach ($seed as $r) {
            $stmt = mysqli_prepare(db(), $sql);
            $uid = (int) $user_id;
            mysqli_stmt_bind_param($stmt, 'isssssssdddissssss', $uid, $r['code'], $r['group_code'], $r['name'], $r['category'], $r['data_type'], $r['track_mode'], $r['unit'], $r['daily_target'], $r['weekly_target'], $r['monthly_target'], $r['weight'], $r['frequency'], $r['sticker'], $r['color'], $r['status'], $now, $now);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        return;
    }
    $seed = official_library();
    foreach ($seed as $r) {
        $r['id'] = store_next_id($data);
        $r['user_id'] = (int) $user_id;
        $r['is_seed'] = 1;
        $r['created_at'] = $now;
        $r['updated_at'] = $now;
        $data['activities'][] = $r;
    }
    store_save($data);
}

function user_by_username($username) {
    $username = strtolower(trim($username));
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_users WHERE username=? OR email=? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return $row;
    }
    $data = store_load();
    foreach ($data['users'] as $u) {
        if ($u['username'] === $username || $u['email'] === $username) return $u;
    }
    return null;
}

function username_taken($username, $except = 0) {
    $username = strtolower(trim($username));
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT id FROM joma_users WHERE username=? AND id<>? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'si', $username, $except);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return (bool) $row;
    }
    $data = store_load();
    foreach ($data['users'] as $u) {
        if ($u['username'] === $username && (int) $u['id'] !== (int) $except) return true;
    }
    return false;
}

function email_taken($email, $except = 0) {
    $email = strtolower(trim($email));
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT id FROM joma_users WHERE email=? AND id<>? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'si', $email, $except);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return (bool) $row;
    }
    $data = store_load();
    foreach ($data['users'] as $u) {
        if ($u['email'] === $email && (int) $u['id'] !== (int) $except) return true;
    }
    return false;
}

function create_user($in) {
    $now = joma_now();
    $hash = password_hash($in['password'], PASSWORD_DEFAULT);
    $role = 'member';
    $level = 1;
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'INSERT INTO joma_users (first_name,last_name,username,email,phone,job,password_hash,role_key,access_level,mobile_verified,created_at) VALUES (?,?,?,?,?,?,?,?,?,0,?)');
        mysqli_stmt_bind_param($stmt, 'sssssssiss', $in['first_name'], $in['last_name'], $in['username'], $in['email'], $in['phone'], $in['job'], $hash, $role, $level, $now);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id(db());
        mysqli_stmt_close($stmt);
        $p = mysqli_prepare(db(), 'INSERT INTO joma_user_preferences (user_id,compact_cards,notifications_enabled) VALUES (?,0,0)');
        mysqli_stmt_bind_param($p, 'i', $id);
        mysqli_stmt_execute($p);
        mysqli_stmt_close($p);
    } else {
        $data = store_load();
        $id = store_next_id($data);
        $data['users'][] = array(
            'id' => $id,
            'first_name' => $in['first_name'],
            'last_name' => $in['last_name'],
            'username' => $in['username'],
            'email' => $in['email'],
            'phone' => $in['phone'],
            'job' => $in['job'],
            'password_hash' => $hash,
            'role_key' => $role,
            'access_level' => $level,
            'mobile_verified' => 0,
            'created_at' => $now,
        );
        store_save($data);
    }
    copy_seed_to_user($id);
    return get_user($id);
}

function get_user($id) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_users WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return $row;
    }
    $data = store_load();
    foreach ($data['users'] as $u) if ((int) $u['id'] === (int) $id) return $u;
    return null;
}

function update_user($id, $patch) {
    $u = get_user($id);
    if (!$u) return null;
    foreach ($patch as $k => $v) $u[$k] = $v;
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'UPDATE joma_users SET first_name=?, last_name=?, phone=?, job=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssssi', $u['first_name'], $u['last_name'], $u['phone'], $u['job'], $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return get_user($id);
    }
    $data = store_load();
    foreach ($data['users'] as $i => $row) {
        if ((int) $row['id'] === (int) $id) $data['users'][$i] = $u;
    }
    store_save($data);
    return $u;
}

function session_user_array($u) {
    return array(
        'id' => $u['id'],
        'first_name' => $u['first_name'],
        'last_name' => $u['last_name'],
        'full_name' => trim($u['first_name'] . ' ' . $u['last_name']),
        'username' => $u['username'],
        'email' => $u['email'],
        'phone' => $u['phone'],
        'job' => $u['job'],
        'role_key' => $u['role_key'],
        'access_level' => $u['access_level'],
        'created_at' => $u['created_at'],
    );
}

function list_user_activities($user_id) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_activities WHERE user_id=? ORDER BY code');
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $out = array();
        while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
        mysqli_stmt_close($stmt);
        return $out;
    }
    $data = store_load();
    $out = array();
    foreach ($data['activities'] as $a) {
        if ((int) $a['user_id'] === (int) $user_id) $out[] = $a;
    }
    usort($out, function ($x, $y) { return strcmp($x['code'], $y['code']); });
    return $out;
}

function get_activity($id, $user_id) {
    foreach (list_user_activities($user_id) as $a) if ((int) $a['id'] === (int) $id) return $a;
    return null;
}

function save_activity($user_id, $in, $id = 0) {
    $now = joma_now();
    if (store_mode() === 'mysql') {
        if ($id) {
            $stmt = mysqli_prepare(db(), 'UPDATE joma_activities SET name=?,category=?,data_type=?,track_mode=?,unit=?,daily_target=?,weekly_target=?,monthly_target=?,weight=?,frequency=?,sticker=?,color=?,status=?,updated_at=? WHERE id=? AND user_id=?');
            mysqli_stmt_bind_param($stmt, 'sssssdddisssssii', $in['name'], $in['category'], $in['data_type'], $in['data_type'], $in['unit'], $in['daily_target'], $in['weekly_target'], $in['monthly_target'], $in['weight'], $in['frequency'], $in['sticker'], $in['color'], $in['status'], $now, $id, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $id;
        }
        $code = 'ACTU' . time();
        $stmt = mysqli_prepare(db(), 'INSERT INTO joma_activities (user_id,code,group_code,name,category,data_type,track_mode,unit,daily_target,weekly_target,monthly_target,weight,frequency,sticker,color,status,is_seed,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,?,?)');
        $g = 'ACT_USR';
        mysqli_stmt_bind_param($stmt, 'isssssssdddissssss', $user_id, $code, $g, $in['name'], $in['category'], $in['data_type'], $in['data_type'], $in['unit'], $in['daily_target'], $in['weekly_target'], $in['monthly_target'], $in['weight'], $in['frequency'], $in['sticker'], $in['color'], $in['status'], $now, $now);
        mysqli_stmt_execute($stmt);
        $nid = mysqli_insert_id(db());
        mysqli_stmt_close($stmt);
        return $nid;
    }
    $data = store_load();
    if ($id) {
        foreach ($data['activities'] as $i => $a) {
            if ((int) $a['id'] === (int) $id && (int) $a['user_id'] === (int) $user_id) {
                foreach ($in as $k => $v) $data['activities'][$i][$k] = $v;
                $data['activities'][$i]['track_mode'] = $in['data_type'];
                $data['activities'][$i]['updated_at'] = $now;
            }
        }
        store_save($data);
        return $id;
    }
    $row = $in;
    $row['id'] = store_next_id($data);
    $row['user_id'] = (int) $user_id;
    $row['code'] = 'ACTU' . $row['id'];
    $row['group_code'] = 'ACT_USR';
    $row['track_mode'] = $in['data_type'];
    $row['is_seed'] = 0;
    $row['created_at'] = $now;
    $row['updated_at'] = $now;
    $data['activities'][] = $row;
    store_save($data);
    return $row['id'];
}

function delete_activity($id, $user_id) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'DELETE FROM joma_activities WHERE id=? AND user_id=?');
        mysqli_stmt_bind_param($stmt, 'ii', $id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return;
    }
    $data = store_load();
    $keep = array();
    foreach ($data['activities'] as $a) {
        if (!((int) $a['id'] === (int) $id && (int) $a['user_id'] === (int) $user_id)) $keep[] = $a;
    }
    $data['activities'] = $keep;
    store_save($data);
}

function ensure_period($user_id, $period_key) {
    $b = jalali_period_bounds($period_key);
    $now = joma_now();
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_periods WHERE user_id=? AND period_key=?');
        mysqli_stmt_bind_param($stmt, 'is', $user_id, $period_key);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $period = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        if (!$period) {
            $stmt = mysqli_prepare(db(), 'INSERT INTO joma_periods (user_id,period_key,year,month,start_date,end_date,created_at) VALUES (?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'isiisss', $user_id, $period_key, $b['year'], $b['month'], $b['start'], $b['end'], $now);
            mysqli_stmt_execute($stmt);
            $pid = mysqli_insert_id(db());
            mysqli_stmt_close($stmt);
            $period = array('id'=>$pid,'user_id'=>$user_id,'period_key'=>$period_key,'year'=>$b['year'],'month'=>$b['month'],'start_date'=>$b['start'],'end_date'=>$b['end']);
        }
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_plans WHERE user_id=? AND period_id=?');
        mysqli_stmt_bind_param($stmt, 'ii', $user_id, $period['id']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $plan = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        if (!$plan) {
            $st = 'DRAFT';
            $stmt = mysqli_prepare(db(), 'INSERT INTO joma_plans (user_id,period_id,period_key,status,created_at,updated_at) VALUES (?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'iissss', $user_id, $period['id'], $period_key, $st, $now, $now);
            mysqli_stmt_execute($stmt);
            $plan = get_plan_by_period($user_id, $period['id']);
            mysqli_stmt_close($stmt);
        }
        return array('period'=>$period,'plan'=>$plan);
    }
    $data = store_load();
    $period = null;
    foreach ($data['periods'] as $p) if ((int)$p['user_id']===(int)$user_id && $p['period_key']===$period_key) $period = $p;
    if (!$period) {
        $period = array('id'=>store_next_id($data),'user_id'=>(int)$user_id,'period_key'=>$period_key,'year'=>$b['year'],'month'=>$b['month'],'start_date'=>$b['start'],'end_date'=>$b['end'],'created_at'=>$now);
        $data['periods'][] = $period;
    }
    $plan = null;
    foreach ($data['plans'] as $p) if ((int)$p['user_id']===(int)$user_id && (int)$p['period_id']===(int)$period['id']) $plan = $p;
    if (!$plan) {
        $plan = array('id'=>store_next_id($data),'user_id'=>(int)$user_id,'period_id'=>$period['id'],'period_key'=>$period_key,'status'=>'DRAFT','created_at'=>$now,'updated_at'=>$now,'finalized_at'=>null,'started_at'=>null,'archived_at'=>null);
        $data['plans'][] = $plan;
    }
    store_save($data);
    return array('period'=>$period,'plan'=>$plan);
}

function get_plan_by_period($user_id, $period_id) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_plans WHERE user_id=? AND period_id=?');
        mysqli_stmt_bind_param($stmt, 'ii', $user_id, $period_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return $row;
    }
    $data = store_load();
    foreach ($data['plans'] as $p) if ((int)$p['user_id']===(int)$user_id && (int)$p['period_id']===(int)$period_id) return $p;
    return null;
}

function list_periods($user_id) {
    $cur = jalali_period_key(jalali_today());
    ensure_period($user_id, $cur);
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT pe.*, pl.id AS plan_id, pl.status AS plan_status FROM joma_periods pe JOIN joma_plans pl ON pl.period_id=pe.id WHERE pe.user_id=? ORDER BY pe.period_key DESC');
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $out = array();
        while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
        mysqli_stmt_close($stmt);
        return $out;
    }
    $data = store_load();
    $out = array();
    foreach ($data['periods'] as $pe) {
        if ((int)$pe['user_id'] !== (int)$user_id) continue;
        foreach ($data['plans'] as $pl) {
            if ((int)$pl['period_id'] === (int)$pe['id']) {
                $pe['plan_id'] = $pl['id'];
                $pe['plan_status'] = $pl['status'];
                $out[] = $pe;
            }
        }
    }
    usort($out, function ($a, $b) { return strcmp($b['period_key'], $a['period_key']); });
    return $out;
}

function get_plan($plan_id, $user_id) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_plans WHERE id=? AND user_id=?');
        mysqli_stmt_bind_param($stmt, 'ii', $plan_id, $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return $row;
    }
    $data = store_load();
    foreach ($data['plans'] as $p) if ((int)$p['id']===(int)$plan_id && (int)$p['user_id']===(int)$user_id) return $p;
    return null;
}

function list_plan_activities($plan_id, $user_id) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_plan_activities WHERE plan_id=? AND user_id=? ORDER BY sort_order, id');
        mysqli_stmt_bind_param($stmt, 'ii', $plan_id, $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $out = array();
        while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
        mysqli_stmt_close($stmt);
        return $out;
    }
    $data = store_load();
    $out = array();
    foreach ($data['plan_activities'] as $a) if ((int)$a['plan_id']===(int)$plan_id && (int)$a['user_id']===(int)$user_id) $out[] = $a;
    usort($out, function ($x, $y) { return ((int)$x['sort_order']) - ((int)$y['sort_order']); });
    return $out;
}

function add_plan_activity($user_id, $plan, $activity, $over) {
    if (!plan_editable($plan['status'])) return 'برنامه این دوره قفل است.';
    foreach (list_plan_activities($plan['id'], $user_id) as $ex) {
        if ((int)$ex['activity_id'] === (int)$activity['id']) return 'این فعالیت قبلاً اضافه شده است.';
    }
    $freq = !empty($over['frequency']) ? $over['frequency'] : $activity['frequency'];
    $tmp = $activity;
    $tmp['frequency'] = $freq;
    $target = isset($over['target_value']) && $over['target_value'] !== '' ? (float)$over['target_value'] : target_of($tmp);
    $weight = isset($over['weight']) && $over['weight'] !== '' ? (int)$over['weight'] : (int)$activity['weight'];
    $now = joma_now();
    $sort = count(list_plan_activities($plan['id'], $user_id));
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'INSERT INTO joma_plan_activities (user_id,plan_id,period_key,activity_id,activity_code,name,category,frequency,data_type,unit,daily_target,weekly_target,monthly_target,target_value,weight,sticker,color,sort_order,snapshot_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'iisisissssddddissis', $user_id, $plan['id'], $plan['period_key'], $activity['id'], $activity['code'], $activity['name'], $activity['category'], $freq, $activity['data_type'], $activity['unit'], $activity['daily_target'], $activity['weekly_target'], $activity['monthly_target'], $target, $weight, $activity['sticker'], $activity['color'], $sort, $now);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return '';
    }
    $data = store_load();
    $data['plan_activities'][] = array(
        'id' => store_next_id($data),
        'user_id' => (int)$user_id,
        'plan_id' => (int)$plan['id'],
        'period_key' => $plan['period_key'],
        'activity_id' => (int)$activity['id'],
        'activity_code' => $activity['code'],
        'name' => $activity['name'],
        'category' => $activity['category'],
        'frequency' => $freq,
        'data_type' => $activity['data_type'],
        'unit' => $activity['unit'],
        'daily_target' => $activity['daily_target'],
        'weekly_target' => $activity['weekly_target'],
        'monthly_target' => $activity['monthly_target'],
        'target_value' => $target,
        'weight' => $weight,
        'sticker' => $activity['sticker'],
        'color' => $activity['color'],
        'sort_order' => $sort,
        'snapshot_at' => $now,
    );
    store_save($data);
    return '';
}

function update_plan_activity($user_id, $plan, $pa_id, $patch) {
    if (!plan_editable($plan['status'])) return 'برنامه قفل است.';
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'UPDATE joma_plan_activities SET frequency=?, target_value=?, weight=?, sort_order=? WHERE id=? AND user_id=?');
        mysqli_stmt_bind_param($stmt, 'sdiiii', $patch['frequency'], $patch['target_value'], $patch['weight'], $patch['sort_order'], $pa_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return '';
    }
    $data = store_load();
    foreach ($data['plan_activities'] as $i => $a) {
        if ((int)$a['id']===(int)$pa_id && (int)$a['user_id']===(int)$user_id) {
            foreach ($patch as $k=>$v) $data['plan_activities'][$i][$k] = $v;
        }
    }
    store_save($data);
    return '';
}

function remove_plan_activity($user_id, $plan, $pa_id) {
    if (!plan_editable($plan['status'])) return 'برنامه قفل است.';
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'DELETE FROM joma_plan_activities WHERE id=? AND user_id=?');
        mysqli_stmt_bind_param($stmt, 'ii', $pa_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return '';
    }
    $data = store_load();
    $keep = array();
    foreach ($data['plan_activities'] as $a) {
        if (!((int)$a['id']===(int)$pa_id && (int)$a['user_id']===(int)$user_id)) $keep[] = $a;
    }
    $data['plan_activities'] = $keep;
    store_save($data);
    return '';
}

function transition_plan($user_id, $plan, $next) {
    $ok = array(
        'DRAFT' => array('PLANNING','ARCHIVED'),
        'PLANNING' => array('RUNNING','DRAFT','ARCHIVED'),
        'RUNNING' => array('ARCHIVED'),
        'ARCHIVED' => array(),
    );
    if (!in_array($next, $ok[$plan['status']], true)) return 'این تغییر وضعیت مجاز نیست.';
    $now = joma_now();
    if (store_mode() === 'mysql') {
        $sql = 'UPDATE joma_plans SET status=?, updated_at=?';
        if ($next==='PLANNING') $sql .= ', finalized_at=?';
        if ($next==='RUNNING') $sql .= ', started_at=?';
        if ($next==='ARCHIVED') $sql .= ', archived_at=?';
        $sql .= ' WHERE id=? AND user_id=?';
        $stmt = mysqli_prepare(db(), $sql);
        if ($next==='PLANNING' || $next==='RUNNING' || $next==='ARCHIVED') {
            mysqli_stmt_bind_param($stmt, 'sssii', $next, $now, $now, $plan['id'], $user_id);
        } else {
            mysqli_stmt_bind_param($stmt, 'ssii', $next, $now, $plan['id'], $user_id);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return '';
    }
    $data = store_load();
    foreach ($data['plans'] as $i => $p) {
        if ((int)$p['id']===(int)$plan['id']) {
            $data['plans'][$i]['status'] = $next;
            $data['plans'][$i]['updated_at'] = $now;
            if ($next==='PLANNING') $data['plans'][$i]['finalized_at'] = $now;
            if ($next==='RUNNING') $data['plans'][$i]['started_at'] = $now;
            if ($next==='ARCHIVED') $data['plans'][$i]['archived_at'] = $now;
        }
    }
    store_save($data);
    return '';
}

function list_events($plan_id, $user_id) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_performance_events WHERE plan_id=? AND user_id=? ORDER BY performance_date, created_at');
        mysqli_stmt_bind_param($stmt, 'ii', $plan_id, $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $out = array();
        while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
        mysqli_stmt_close($stmt);
        return $out;
    }
    $data = store_load();
    $out = array();
    foreach ($data['events'] as $e) if ((int)$e['plan_id']===(int)$plan_id && (int)$e['user_id']===(int)$user_id) $out[] = $e;
    return $out;
}

function can_register_performance($plan, $pa, $date, $value, $existing) {
    if ($plan['status'] !== 'RUNNING') return 'فقط در دوره در حال اجرا می‌توان عملکرد ثبت کرد.';
    if (!jalali_in_period($date, $plan['period_key'])) return 'تاریخ داخل این دوره نیست.';
    if ($pa['data_type'] === 'RATING' && ($value < 1 || $value > 5)) return 'امتیاز باید بین ۱ و ۵ باشد.';
    if ($value < 0) return 'مقدار نمی‌تواند منفی باشد.';
    if ($pa['frequency'] === 'DAILY') {
        foreach ($existing as $e) {
            if ((int)$e['plan_activity_id']===(int)$pa['id'] && $e['performance_date']===$date) {
                return 'برای این فعالیت روزانه، در این تاریخ قبلاً عملکرد ثبت شده است.';
            }
        }
    }
    return '';
}

function register_performance($user_id, $plan, $pa, $date, $value) {
    $existing = list_events($plan['id'], $user_id);
    $err = can_register_performance($plan, $pa, $date, $value, $existing);
    if ($err) return $err;
    $now = joma_now();
    if (store_mode() === 'mysql') {
        $et = 'PERFORMANCE_REGISTERED';
        $stmt = mysqli_prepare(db(), 'INSERT INTO joma_performance_events (user_id,plan_id,plan_activity_id,period_key,frequency,data_type,event_type,performance_date,actual_value,created_at) VALUES (?,?,?,?,?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'iiisssssds', $user_id, $plan['id'], $pa['id'], $plan['period_key'], $pa['frequency'], $pa['data_type'], $et, $date, $value, $now);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return '';
    }
    $data = store_load();
    $data['events'][] = array(
        'id' => store_next_id($data),
        'user_id' => (int)$user_id,
        'plan_id' => (int)$plan['id'],
        'plan_activity_id' => (int)$pa['id'],
        'period_key' => $plan['period_key'],
        'frequency' => $pa['frequency'],
        'data_type' => $pa['data_type'],
        'event_type' => 'PERFORMANCE_REGISTERED',
        'performance_date' => $date,
        'actual_value' => $value,
        'created_at' => $now,
    );
    store_save($data);
    return '';
}

function get_mood($user_id, $date) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_mood_records WHERE user_id=? AND jalali_date=?');
        mysqli_stmt_bind_param($stmt, 'is', $user_id, $date);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        return $row;
    }
    $data = store_load();
    foreach ($data['moods'] as $m) if ((int)$m['user_id']===(int)$user_id && $m['jalali_date']===$date) return $m;
    return null;
}

function list_moods($user_id, $start, $end) {
    if (store_mode() === 'mysql') {
        $stmt = mysqli_prepare(db(), 'SELECT * FROM joma_mood_records WHERE user_id=? AND jalali_date>=? AND jalali_date<=? ORDER BY jalali_date');
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $start, $end);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $out = array();
        while ($row = mysqli_fetch_assoc($res)) $out[] = $row;
        mysqli_stmt_close($stmt);
        return $out;
    }
    $data = store_load();
    $out = array();
    foreach ($data['moods'] as $m) {
        if ((int)$m['user_id']===(int)$user_id && $m['jalali_date']>=$start && $m['jalali_date']<=$end) $out[] = $m;
    }
    return $out;
}

function save_mood($user_id, $date, $scores, $note) {
    $now = joma_now();
    $ex = get_mood($user_id, $date);
    if (store_mode() === 'mysql') {
        if ($ex) {
            $stmt = mysqli_prepare(db(), 'UPDATE joma_mood_records SET energy=?, general_mood=?, focus=?, sleep_quality=?, stress=?, note=? WHERE id=?');
            mysqli_stmt_bind_param($stmt, 'iiiiisi', $scores['energy'], $scores['general'], $scores['focus'], $scores['sleep'], $scores['stress'], $note, $ex['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare(db(), 'INSERT INTO joma_mood_records (user_id,jalali_date,energy,general_mood,focus,sleep_quality,stress,note,created_at) VALUES (?,?,?,?,?,?,?,?,?)');
            mysqli_stmt_bind_param($stmt, 'isiiiiiss', $user_id, $date, $scores['energy'], $scores['general'], $scores['focus'], $scores['sleep'], $scores['stress'], $note, $now);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        return;
    }
    $data = store_load();
    if ($ex) {
        foreach ($data['moods'] as $i => $m) {
            if ((int)$m['id']===(int)$ex['id']) {
                $data['moods'][$i]['energy'] = $scores['energy'];
                $data['moods'][$i]['general_mood'] = $scores['general'];
                $data['moods'][$i]['focus'] = $scores['focus'];
                $data['moods'][$i]['sleep_quality'] = $scores['sleep'];
                $data['moods'][$i]['stress'] = $scores['stress'];
                $data['moods'][$i]['note'] = $note;
            }
        }
    } else {
        $data['moods'][] = array(
            'id' => store_next_id($data),
            'user_id' => (int)$user_id,
            'jalali_date' => $date,
            'energy' => $scores['energy'],
            'general_mood' => $scores['general'],
            'focus' => $scores['focus'],
            'sleep_quality' => $scores['sleep'],
            'stress' => $scores['stress'],
            'note' => $note,
            'created_at' => $now,
        );
    }
    store_save($data);
}

function build_report($user_id, $plan) {
    $acts = list_plan_activities($plan['id'], $user_id);
    $evs = list_events($plan['id'], $user_id);
    $b = jalali_period_bounds($plan['period_key']);
    $moods = list_moods($user_id, $b['start'], $b['end']);
    $rows = array();
    foreach ($acts as $a) {
        $list = array();
        $sum = 0;
        foreach ($evs as $e) if ((int)$e['plan_activity_id']===(int)$a['id']) {
            $list[] = $e;
            $sum += (float)$e['actual_value'];
        }
        $a['actual'] = $sum;
        $a['event_count'] = count($list);
        $a['events'] = $list;
        $rows[] = $a;
    }
    return array(
        'period_key' => $plan['period_key'],
        'plan' => $plan,
        'activities' => $rows,
        'events' => $evs,
        'moods' => $moods,
        'bounds' => $b,
        'source_event_count' => count($evs),
        'achievement' => 'UNSPECIFIED',
    );
}
