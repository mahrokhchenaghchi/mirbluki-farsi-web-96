<?php
/**
 * JOMA — لیوان آب (B6)
 * SVG با اندازهٔ صریح؛ پر/خالی فقط با موقعیت گروه آب عوض می‌شود.
 */

/**
 * لیوان آب (B6) — SVG با اندازهٔ صریح، مطابق مشخصات سند (بدنهٔ تقریباً راست، لبهٔ نازک، ته ضخیم).
 * پر/خالی فقط با translateY گروه آب عوض می‌شود.
 */
function joma_v2_glass_svg($filled = false, $size = 30, $uid = '') {
    $w = (int) $size;
    $h = (int) round($size * 58 / 44);
    $gid = 'g' . ($uid !== '' ? preg_replace('/[^a-z0-9]/i', '', (string) $uid) : mt_rand(1000, 9999));
    $svg = '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 44 58" aria-hidden="true" style="display:block">';
    $svg .= '<defs>'
          . '<linearGradient id="wg' . $gid . '" x1="0" y1="0" x2="0" y2="1">'
          . '<stop offset="0%" stop-color="#A8DCF4"/><stop offset="55%" stop-color="#3DA8E0"/><stop offset="100%" stop-color="#1B6A9B"/>'
          . '</linearGradient>'
          . '<clipPath id="wc' . $gid . '">'
          . '<path d="M5.4 5.4 L38.6 5.4 L36.6 50 Q36.4 53.6 32.6 53.6 L11.4 53.6 Q7.6 53.6 7.4 50 Z"/></clipPath>'
          . '</defs>';
    $svg .= '<ellipse cx="22" cy="55.6" rx="14.5" ry="2.6" fill="rgba(20,56,46,.10)"/>';
    if ($filled) {
        $svg .= '<g clip-path="url(#wc' . $gid . ')">'
              . '<rect x="0" y="18.6" width="44" height="42" fill="url(#wg' . $gid . ')"/>'
              // موج سطح آب (دو لایهٔ سینوسی که با CSS حرکت می‌کنند)
              . '<g class="wg-waves">'
              . '<path class="wg-w1" d="M-16 20 q6 -2.4 12 0 q6 2.4 12 0 q6 -2.4 12 0 q6 2.4 12 0 q6 -2.4 12 0 q6 2.4 12 0 L84 40 L-16 40 Z" fill="#CDEBFA" opacity=".75"/>'
              . '<path class="wg-w2" d="M-16 21.4 q6 2.2 12 0 q6 -2.2 12 0 q6 2.2 12 0 q6 -2.2 12 0 q6 2.2 12 0 q6 -2.2 12 0 L84 42 L-16 42 Z" fill="#8FD0EF" opacity=".45"/>'
              . '</g>'
              . '</g>';
    }
    $svg .= '<path d="M5.4 5.4 L38.6 5.4 L36.6 50 Q36.4 53.6 32.6 53.6 L11.4 53.6 Q7.6 53.6 7.4 50 Z" fill="none" stroke="#BBD8E8" stroke-width=".95"/>';
    $svg .= '<ellipse cx="22" cy="50.8" rx="11.6" ry="2.5" fill="#DCEBF4" opacity=".75"/>';
    $svg .= '<ellipse cx="22" cy="5.4" rx="16.6" ry="3.5" fill="none" stroke="#9CC2DA" stroke-width="1.05"/>';
    $svg .= '<ellipse cx="22" cy="5.4" rx="14.9" ry="2.9" fill="none" stroke="#BBD8E8" stroke-width=".9"/>';
    $svg .= '<ellipse cx="15" cy="4.6" rx="4.6" ry="1.3" fill="#FFFFFF" opacity=".85"/>';
    $svg .= '<path d="M12.4 12 Q11.6 30 12.6 44" fill="none" stroke="#FFFFFF" stroke-width="1.7" opacity=".7"/>';
    $svg .= '<path d="M15.6 14 Q15 30 15.8 42" fill="none" stroke="#FFFFFF" stroke-width=".9" opacity=".38"/>';
    $svg .= '</svg>';
    return $svg;
}
