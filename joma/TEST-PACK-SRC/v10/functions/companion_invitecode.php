<?php
/**
 * جوما — کد دعوت یک‌بارمصرف «هم‌مسیر» (بستهٔ v10 · پاسخ مالک)
 * ------------------------------------------------------------------
 * مالک: ««کد دعوت دارم» — بسازش. جدول کد یک‌بارمصرف + مسیر پذیرش + ثبت «کی با کد آمد».»
 *
 * قاعده‌های سند ۲۴ §۶٫۳ و §۷ که عیناً رعایت شده:
 *   • کد یک‌بارمصرف · اعتبار ۷ روز · فقط برای یک کاربر
 *   • حداکثر ۵ کد فعال برای هر مشاور
 *   • کد منقضی → «این کد دیگر معتبر نیست.» · کد غلط → «کدی با این شماره پیدا نشد.»
 *   • هر ساخت و مصرف کد ثبت می‌شود (۱۷٫۱)
 *   • رابطهٔ یک‌طرفه ساخته نمی‌شود: طرف مشاور با «ساخت کد» رضایت داده است
 *     (کد فقط به دست خودش می‌رسد و به کسی داده نمی‌شود)، طرف کاربر هم در گام مجوزها
 *     رضایت می‌دهد؛ تا آن لحظه مشاور هیچ داده‌ای نمی‌بیند.
 *
 * ذخیره‌سازی: همان storage رسمی ماژول (دقیقاً مثل بقیهٔ موجودیت‌ها)
 *   • حالت فایل: کلید `invite_codes` در data/hammasir/store.json
 *   • حالت دیتابیس: جدول `joma_hammasir_invite_codes`
 *     (فایل اختیاری-2-hammasir_invite_codes.sql — فقط CREATE TABLE IF NOT EXISTS)
 * «کی با کد آمد» در خودِ ردیف کد ثبت می‌شود: used_by_user_id + used_at + link_id.
 * هیچ ستون و هیچ جدولِ موجودی تغییر نمی‌کند.
 */

if (!function_exists('joma_invitecode_ready')) :

    /** آمادگی: ماژول هم‌مسیر لود شده باشد */
    function joma_invitecode_ready() {
        if (function_exists('hammasir_store_mutate') && function_exists('hammasir_links_by_provider')) return true;
        if (!function_exists('joma_companion_module_ready')) return false;
        return joma_companion_module_ready();
    }

    /** طول کد و اعتبار — یک نقطهٔ تعریف */
    function joma_invitecode_rules() {
        return array('length' => 6, 'valid_days' => 7, 'max_active' => 5);
    }

    /** الفبای امن (بدون کاراکترهای شبیه‌به‌هم) */
    function joma_invitecode_alphabet() {
        return 'ACDEFGHJKLMNPQRSTUVWXYZ23456789';
    }

    function joma_invitecode_generate() {
        $a = joma_invitecode_alphabet();
        $n = strlen($a);
        $out = '';
        for ($i = 0; $i < (int) joma_invitecode_rules()['length']; $i++) {
            $out .= $a[random_int(0, $n - 1)];
        }
        return $out;
    }

    /** نرمال‌سازی ورودی کاربر: حذف فاصله/خط تیره، حروف بزرگ، ارقام فارسی → لاتین */
    function joma_invitecode_normalize($raw) {
        $s = (string) $raw;
        $s = str_replace(array(' ', "\t", "\n", '-', '–', '_', '.', '،'), '', $s);
        $fa = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $en = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $s = str_replace($fa, $en, $s);
        $s = strtoupper($s);
        return $s;
    }

    /** وضعیت‌های ممکن یک ردیف کد */
    function joma_invitecode_statuses() {
        return array('ACTIVE', 'USED', 'REVOKED', 'EXPIRED');
    }

    /* ------------------------------------------------------------------
     * خواندن‌ها
     * ------------------------------------------------------------------ */

    /** همهٔ کدهای یک مشاور (جدیدترین اول) — خطا → آرایهٔ خالی */
    function joma_invitecode_list_by_provider($provider_user_id) {
        if (!joma_invitecode_ready()) return array();
        $provider_user_id = (int) $provider_user_id;
        try {
            if (store_mode() === 'mysql') {
                $rows = joma_query(
                    'SELECT * FROM joma_hammasir_invite_codes WHERE provider_user_id=? ORDER BY id DESC',
                    'i',
                    array($provider_user_id)
                );
                return is_array($rows) ? $rows : array();
            }
            $store = hammasir_store_load();
            if ($store === null) return array();
            if (!isset($store['invite_codes']) || !is_array($store['invite_codes'])) return array();
            $out = array();
            foreach ($store['invite_codes'] as $r) {
                if ((int) $r['provider_user_id'] === $provider_user_id) $out[] = $r;
            }
            return array_reverse($out);
        } catch (Throwable $e) {
            return array();
        }
    }

    /** یک ردیف کد با کدِ نرمال‌شده — یا null */
    function joma_invitecode_find($code) {
        if (!joma_invitecode_ready()) return null;
        $code = joma_invitecode_normalize($code);
        if ($code === '') return null;
        try {
            if (store_mode() === 'mysql') {
                $row = joma_query_one('SELECT * FROM joma_hammasir_invite_codes WHERE code=? LIMIT 1', 's', array($code));
                return $row ? $row : null;
            }
            $store = hammasir_store_load();
            if ($store === null) return null;
            if (!isset($store['invite_codes']) || !is_array($store['invite_codes'])) return null;
            foreach ($store['invite_codes'] as $r) {
                if (strcasecmp((string) $r['code'], $code) === 0) return $r;
            }
            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /** وضعیت واقعی یک ردیف کد (انقضا با زمان سرور سنجیده می‌شود) */
    function joma_invitecode_row_status($row) {
        if (!is_array($row)) return 'REVOKED';
        if ($row['status'] === 'USED') return 'USED';
        if ($row['status'] === 'REVOKED') return 'REVOKED';
        $exp = isset($row['expires_at']) ? (string) $row['expires_at'] : '';
        if ($exp !== '' && strtotime($exp) !== false && time() > strtotime($exp)) return 'EXPIRED';
        return 'ACTIVE';
    }

    /** برچسب فارسی و رنگ وضعیت کد — یک نقطهٔ تعریف */
    function joma_invitecode_status_labels() {
        return array(
            'ACTIVE' => array('label' => 'فعال', 'color' => '#0F8A5C'),
            'USED' => array('label' => 'استفاده شده', 'color' => 'var(--ink-3)'),
            'REVOKED' => array('label' => 'لغو شده', 'color' => 'var(--ink-3)'),
            'EXPIRED' => array('label' => 'منقضی', 'color' => 'var(--ink-3)'),
        );
    }

    /** فهرست کدها با برچسب و وضعیت محاسبه‌شده (برای نمایش) */
    function joma_invitecode_view_list($provider_user_id) {
        $labels = joma_invitecode_status_labels();
        $out = array();
        foreach (joma_invitecode_list_by_provider($provider_user_id) as $r) {
            $st = joma_invitecode_row_status($r);
            $used_name = '';
            if ($st === 'USED' && !empty($r['used_by_user_id'])) {
                try {
                    $used_name = hammasir_user_display((int) $r['used_by_user_id']);
                } catch (Throwable $e) {
                    $used_name = '';
                }
            }
            $created = '';
            if (!empty($r['created_at']) && function_exists('hammasir_dt_to_jalali')) {
                $created = hammasir_dt_to_jalali($r['created_at']);
            }
            $used_at = '';
            if ($st === 'USED' && !empty($r['used_at']) && function_exists('hammasir_dt_to_jalali')) {
                $used_at = hammasir_dt_to_jalali($r['used_at']);
            }
            $out[] = array(
                'id' => (int) $r['id'],
                'code' => (string) $r['code'],
                'status' => $st,
                'status_label' => isset($labels[$st]) ? $labels[$st]['label'] : $st,
                'status_color' => isset($labels[$st]) ? $labels[$st]['color'] : 'var(--ink-3)',
                'created_jalali' => $created,
                'expires_at' => isset($r['expires_at']) ? (string) $r['expires_at'] : '',
                'used_name' => $used_name,
                'used_at_jalali' => $used_at,
                'is_active' => ($st === 'ACTIVE'),
            );
        }
        return $out;
    }

    /** شمار کدهای فعال (سقف ۵ طبق سند) */
    function joma_invitecode_active_count($provider_user_id) {
        $n = 0;
        foreach (joma_invitecode_list_by_provider($provider_user_id) as $r) {
            if (joma_invitecode_row_status($r) === 'ACTIVE') $n++;
        }
        return $n;
    }

    /* ------------------------------------------------------------------
     * نوشتن‌ها
     * ------------------------------------------------------------------ */

    /** ساخت کد تازه — خروجی: کد (رشته) یا کدِ خطا */
    function joma_invitecode_create($provider_user_id, $created_by_user_id = 0) {
        if (!joma_invitecode_ready()) return 'error';
        $provider_user_id = (int) $provider_user_id;
        $rules = joma_invitecode_rules();
        try {
            $prov = hammasir_provider_by_user_id($provider_user_id);
            if (!$prov || $prov['status'] !== 'ACTIVE') return 'invalid_provider';
        } catch (Throwable $e) {
            return 'error';
        }
        if (joma_invitecode_active_count($provider_user_id) >= (int) $rules['max_active']) return 'too_many';
        $now = function_exists('hammasir_now') ? hammasir_now() : date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', strtotime($now) + ((int) $rules['valid_days'] * 86400));
        $code = joma_invitecode_generate();
        try {
            if (store_mode() === 'mysql') {
                $ok = joma_exec(
                    'INSERT INTO joma_hammasir_invite_codes (provider_user_id, code, status, created_by_user_id, created_at, expires_at) VALUES (?, ?, ?, ?, ?, ?)',
                    'isssss',
                    array($provider_user_id, $code, 'ACTIVE', (int) $created_by_user_id, $now, $expires)
                );
                return $ok ? $code : 'error';
            }
            $res = hammasir_store_mutate(function (&$store) use ($provider_user_id, $code, $now, $expires, $created_by_user_id) {
                if (!isset($store['invite_codes']) || !is_array($store['invite_codes'])) $store['invite_codes'] = array();
                $store['invite_codes'][] = array(
                    'id' => hammasir_next_id($store),
                    'provider_user_id' => (int) $provider_user_id,
                    'code' => (string) $code,
                    'status' => 'ACTIVE',
                    'created_by_user_id' => (int) $created_by_user_id,
                    'created_at' => (string) $now,
                    'expires_at' => (string) $expires,
                    'used_by_user_id' => 0,
                    'used_at' => '',
                    'link_id' => 0,
                );
                return 'ok';
            });
            return ($res === 'ok') ? $code : 'error';
        } catch (Throwable $e) {
            return 'error';
        }
    }

    /** لغو کد فعال (فقط صاحب همان کد) */
    function joma_invitecode_revoke($provider_user_id, $code_id) {
        if (!joma_invitecode_ready()) return 'error';
        $provider_user_id = (int) $provider_user_id;
        $code_id = (int) $code_id;
        try {
            if (store_mode() === 'mysql') {
                $row = joma_query_one('SELECT * FROM joma_hammasir_invite_codes WHERE id=? AND provider_user_id=? LIMIT 1', 'ii', array($code_id, $provider_user_id));
                if (!$row) return 'not_found';
                if (joma_invitecode_row_status($row) !== 'ACTIVE') return 'not_active';
                return joma_exec('UPDATE joma_hammasir_invite_codes SET status=? WHERE id=?', 'si', array('REVOKED', $code_id)) ? 'ok' : 'error';
            }
            $res = hammasir_store_mutate(function (&$store) use ($code_id, $provider_user_id) {
                if (!isset($store['invite_codes']) || !is_array($store['invite_codes'])) return 'not_found';
                foreach ($store['invite_codes'] as $i => $r) {
                    if ((int) $r['id'] !== $code_id || (int) $r['provider_user_id'] !== $provider_user_id) continue;
                    if ($r['status'] !== 'ACTIVE') return 'not_active';
                    $store['invite_codes'][$i]['status'] = 'REVOKED';
                    return 'ok';
                }
                return 'not_found';
            });
            return is_string($res) ? $res : 'error';
        } catch (Throwable $e) {
            return 'error';
        }
    }

    /**
     * پذیرش کد توسط کاربر.
     * گام‌ها: پیدا کردن کد → سنجش وضعیت → ساخت درخواست با همان تابع رسمی ماژول
     *         (رضایت کاربر = همین لحظه) → تأیید طرف مشاور (رضایتش را با ساخت کد داده)
     *         → ثبت «کی با کد آمد» روی همان ردیف کد.
     * خروجی: ok | empty | not_found | not_active | expired | self_link | open_link_exists | error
     */
    function joma_invitecode_redeem($client_user_id, $raw_code, $view_perms, $msg_out, $msg_in, $share_status) {
        if (!joma_invitecode_ready()) return 'error';
        $client_user_id = (int) $client_user_id;
        $code = joma_invitecode_normalize($raw_code);
        if ($code === '') return 'empty';
        $row = joma_invitecode_find($code);
        if ($row === null) return 'not_found';
        $status = joma_invitecode_row_status($row);
        if ($status === 'EXPIRED') return 'expired';
        if ($status !== 'ACTIVE') return 'not_active';
        $provider_user_id = (int) $row['provider_user_id'];
        if ($provider_user_id === $client_user_id) return 'self_link';

        // ۱) درخواست رسمی ماژول (رضایت کاربر با مجوزهای تیک‌خورده؛ هیچ تیک پیش‌فرضی نیست)
        if (!function_exists('joma_companion_link_request')) return 'error';
        $res = joma_companion_link_request($client_user_id, $provider_user_id, $view_perms, $msg_out, $msg_in, $share_status);
        if ($res !== 'ok') return $res;

        // ۲) طرف مشاور، با «ساخت همین کد» رضایت داده است → رابطه دو‌طرفه می‌شود
        $link = null;
        try {
            $link = hammasir_link_open_by_client($client_user_id);
        } catch (Throwable $e) {
            $link = null;
        }
        if (!$link) return 'error';
        try {
            $accept = hammasir_link_respond($provider_user_id, (int) $link['id'], 'ACTIVE');
        } catch (Throwable $e) {
            $accept = 'error';
        }
        if ($accept !== 'ok') return is_string($accept) ? $accept : 'error';

        // ۳) ثبت «کی با کد آمد» + یک‌بارمصرف شدن کد
        joma_invitecode_mark_used((int) $row['id'], $client_user_id, (int) $link['id']);
        return 'ok';
    }

    /** ثبت مصرف کد (کی با کد آمد) */
    function joma_invitecode_mark_used($code_id, $client_user_id, $link_id) {
        if (!joma_invitecode_ready()) return false;
        $code_id = (int) $code_id;
        $now = function_exists('hammasir_now') ? hammasir_now() : date('Y-m-d H:i:s');
        try {
            if (store_mode() === 'mysql') {
                return (bool) joma_exec(
                    'UPDATE joma_hammasir_invite_codes SET status=?, used_by_user_id=?, used_at=?, link_id=? WHERE id=?',
                    'sisii',
                    array('USED', (int) $client_user_id, $now, (int) $link_id, $code_id)
                );
            }
            $res = hammasir_store_mutate(function (&$store) use ($code_id, $client_user_id, $link_id, $now) {
                if (!isset($store['invite_codes']) || !is_array($store['invite_codes'])) return false;
                foreach ($store['invite_codes'] as $i => $r) {
                    if ((int) $r['id'] !== $code_id) continue;
                    $store['invite_codes'][$i]['status'] = 'USED';
                    $store['invite_codes'][$i]['used_by_user_id'] = (int) $client_user_id;
                    $store['invite_codes'][$i]['used_at'] = (string) $now;
                    $store['invite_codes'][$i]['link_id'] = (int) $link_id;
                    return 'ok';
                }
                return false;
            });
            return ($res === 'ok');
        } catch (Throwable $e) {
            return false;
        }
    }

    /** پیام‌های رابط — یک نقطهٔ تعریف (عیناً متن‌های سند ۲۴ §۶٫۳) */
    function joma_invitecode_messages() {
        return array(
            'empty' => 'کد دعوت را وارد کن.',
            'not_found' => 'کدی با این شماره پیدا نشد.',
            'not_active' => 'این کد دیگر معتبر نیست.',
            'expired' => 'این کد دیگر معتبر نیست.',
            'self_link' => 'این کد برای خودت است؛ نمی‌توانی همراه خودت شوی.',
            'open_link_exists' => 'شما هم‌اکنون یک درخواست یا ارتباط باز دارید.',
            'invalid_provider' => 'همراه انتخاب‌شده در دسترس نیست.',
            'request_cooldown' => 'برای درخواست مجدد باید کمی صبر کنی.',
            'too_many' => 'سقف کدهای فعال تکمیل شده است.',
            'error' => 'امکان انجام این عملیات در حال حاضر وجود ندارد.',
            'ok' => 'کد پذیرفته شد. هر بخشی که اجازه بدهی، همراهت می‌بیند.',
        );
    }

endif;
