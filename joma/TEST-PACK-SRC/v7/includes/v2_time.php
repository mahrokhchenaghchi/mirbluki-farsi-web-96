<?php
/**
 * JOMA — استیکر وقت روز (SPEC/11 §۰٫۱)
 * چهار حالت: خورشید تازه‌طلوع‌کرده · خورشید کامل · خورشید نزدیک افق با هالهٔ نارنجی · هلال ماه + سه ستاره + ابر.
 * همیشه SVG با اندازهٔ صریح · تزئینی (aria-hidden) · جغد نیست · رنگ از توکن‌های طرح.
 */

/** کدام وقت؟ (زمان مرجع سرور؛ ۵–۱۰:۵۹ صبح · ۱۱–۱۳:۵۹ ظهر · ۱۴–۱۸:۵۹ عصر · ۱۹–۴:۵۹ شب) */
function joma_v2_time_slot($hour = null) {
    $h = ($hour === null) ? (int) date('G') : (int) $hour;
    if ($h >= 5 && $h < 11) return 'sunrise';
    if ($h >= 11 && $h < 14) return 'noon';
    if ($h >= 14 && $h < 19) return 'sunset';
    return 'moon';
}

/** استیکر SVG با اندازهٔ صریح (پیش‌فرض ۴۴px؛ هرگز زیر ۲۸px). */
function joma_v2_time_sticker($slot = null, $size = 44) {
    if ($slot === null) $slot = joma_v2_time_slot();
    $w = max(28, (int) $size);
    $h = $w;
    $s = $w / 100; // مقیاس نسبت به viewBox 100×100

    $svg = '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 100 100" aria-hidden="true" style="display:block">';

    if ($slot === 'sunrise') {
        // نیم‌دایره بالای خط افق، پرتوهای کوتاه، لبخند
        $svg .= '<path d="M50 62 a20 20 0 0 1 20 20 H30 a20 20 0 0 1 20-20Z" fill="#F7C04A"/>';
        $svg .= '<path d="M50 34v9M32 42l6 6M68 42l-6 6M22 62h9M69 62h9" stroke="#F7C04A" stroke-width="4" stroke-linecap="round"/>';
        $svg .= '<path d="M42 72q8 7 16 0" fill="none" stroke="#B07C12" stroke-width="3" stroke-linecap="round"/>';
        $svg .= '<circle cx="43" cy="67" r="2.6" fill="#B07C12"/><circle cx="57" cy="67" r="2.6" fill="#B07C12"/>';
        $svg .= '<path d="M10 84h80" stroke="#E9C98A" stroke-width="4" stroke-linecap="round"/>';
    } elseif ($slot === 'noon') {
        // خورشید کامل بالا، پرتوهای بلند، لبخند
        $svg .= '<circle cx="50" cy="46" r="22" fill="#F6B93B"/>';
        $svg .= '<path d="M50 8v12M50 72v12M12 46h12M76 46h12M23 19l9 9M77 19l-9 9M23 73l9-9M77 73l-9-9" stroke="#F6B93B" stroke-width="4.5" stroke-linecap="round"/>';
        $svg .= '<circle cx="43" cy="41" r="3" fill="#A9760F"/><circle cx="57" cy="41" r="3" fill="#A9760F"/>';
        $svg .= '<path d="M41 54q9 9 18 0" fill="none" stroke="#A9760F" stroke-width="3" stroke-linecap="round"/>';
    } elseif ($slot === 'sunset') {
        // خورشید پایین‌تر با هالهٔ نارنجی، پرتوهای کوتاه
        $svg .= '<circle cx="50" cy="58" r="30" fill="#F9B593" opacity=".55"/>';
        $svg .= '<path d="M50 70 a18 18 0 0 1 18 18 H32 a18 18 0 0 1 18-18Z" fill="#F2784B"/>';
        $svg .= '<path d="M26 60l8 4M74 60l-8 4M50 44v8" stroke="#F2784B" stroke-width="4" stroke-linecap="round"/>';
        $svg .= '<circle cx="44" cy="80" r="2.4" fill="#8E3F1C"/><circle cx="56" cy="80" r="2.4" fill="#8E3F1C"/>';
        $svg .= '<path d="M43 86q7 6 14 0" fill="none" stroke="#8E3F1C" stroke-width="2.8" stroke-linecap="round"/>';
        $svg .= '<path d="M10 92h80" stroke="#E7B79B" stroke-width="4" stroke-linecap="round"/>';
    } else {
        // هلال ماه + سه ستاره + ابر کوچک
        $svg .= '<path d="M62 22a28 28 0 1 0 0 56 34 34 0 0 1 0-56Z" fill="#8B7CF6"/>';
        $svg .= '<path d="M26 26l2.6 5.4L34 34l-5.4 2.6L26 42l-2.6-5.4L18 34l5.4-2.6Z" fill="#F0B23A"/>';
        $svg .= '<path d="M74 18l1.8 3.8 3.8 1.8-3.8 1.8L74 29l-1.8-3.8-3.8-1.8 3.8-1.8Z" fill="#F0B23A"/>';
        $svg .= '<path d="M80 46l1.4 3 3 1.4-3 1.4-1.4 3-1.4-3-3-1.4 3-1.4Z" fill="#F0B23A"/>';
        $svg .= '<path d="M22 74q0-7 8-7 3-6 11-4 6-3 10 3 7 0 7 8Z" fill="#DDF0FC"/>';
    }
    $svg .= '</svg>';
    return $svg;
}

/** برچسب فارسی وقت (برای متن جانشین یا زیرنویس). */
function joma_v2_time_label($slot = null) {
    if ($slot === null) $slot = joma_v2_time_slot();
    $map = array('sunrise' => 'صبح', 'noon' => 'ظهر', 'sunset' => 'عصر', 'moon' => 'شب');
    return isset($map[$slot]) ? $map[$slot] : '';
}
