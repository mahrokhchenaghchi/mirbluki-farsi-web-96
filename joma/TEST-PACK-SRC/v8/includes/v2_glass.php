<?php
/**
 * JOMA — لیوان آب (B6) — نمودار و رفتار «هر لمس = یک گذار»
 * =========================================================================
 * قواعد ثابت (تصمیم مالک v8):
 *   · وضعیت = یک عدد N از ۰ تا هدف. هر لمس **دقیقاً یک** گذار می‌سازد:
 *        لمس k وقتی N<k   ⇒ N=k        (پر شدن تا k)
 *        لمس همان لیوانِ پر (k=N>0) ⇒ N=k−1   (خالی‌شدن، بی‌صدا)
 *        لمس لیوان عقب‌تر (k<N)     ⇒ N=k   (پایین آمدن، بی‌صدا)
 *        بدون تغییر (k=N=0)        ⇒ هیچ انیمیشنی
 *   · فقط یک رویداد: click (نه pointerup/pointerdown همزمان)
 *   · قفل ضد دوباره‌شلیک تا پایان انیمیشن (~460ms)
 *   · انیمیشن transform از N قدیم به N جدید · SVG **بازسازی نمی‌شود**
 *   · سیلوئت خوانا در حالت خالی (خط #BBD8E8 · 0.95px)
 *   · سطح آب ۱۳٫۲px زیر لبه · دو موج سینوسی ۳٫۶s یک‌طرفه و ۵٫۴s مخالف · برق شیشهٔ نازک
 *
 * ساختار SVG برای انیمیشن:
 *   .glass-water  ← گروه آب (clip داخل بدنه) که فقط translateY می‌گیرد
 *   ارتفاع هر لیوان = GLASS_STEP (فاصلهٔ پله‌ها) = ۱۳٫۲ + ۶٫۸ = ۲۰٫۰ در واحد viewBox
 * =========================================================================
 */

/** یک لیوان SVG با گروه آبِ مستقل (بدون بازسازی در کلیک). */
function joma_v2_glass_svg($filled = false, $size = 30, $uid = '') {
    $w = (int) $size;
    $h = (int) round($size * 58 / 44);
    $gid = 'g' . ($uid !== '' ? preg_replace('/[^a-z0-9]/i', '', (string) $uid) : mt_rand(1000, 9999));
    // سطح آب در حالت پر: ۱۳٫۲ زیر لبه → y = 5.4 + 13.2 = 18.6 (طبق سند)
    $surfFull = 18.6;
    $svg = '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 44 58" aria-hidden="true" style="display:block">';
    $svg .= '<defs>'
          . '<linearGradient id="wg' . $gid . '" x1="0" y1="0" x2="0" y2="1">'
          . '<stop offset="0%" stop-color="#A8DCF4"/><stop offset="55%" stop-color="#3DA8E0"/><stop offset="100%" stop-color="#1B6A9B"/>'
          . '</linearGradient>'
          . '<clipPath id="wc' . $gid . '">'
          . '<path d="M5.4 5.4 L38.6 5.4 L36.6 50 Q36.4 53.6 32.6 53.6 L11.4 53.6 Q7.6 53.6 7.4 50 Z"/></clipPath>'
          . '</defs>';
    // سایه روی میز
    $svg .= '<ellipse cx="22" cy="55.6" rx="14.5" ry="2.6" fill="rgba(20,56,46,.10)"/>';
    // گروه آب — همیشه وجود دارد؛ فقط transform عوض می‌شود (بدون بازسازی)
    $svg .= '<g class="glass-water" clip-path="url(#wc' . $gid . ')">'
          . '<rect class="gw-body" x="0" y="' . $surfFull . '" width="44" height="44" fill="url(#wg' . $gid . ')"/>'
          . '<g class="gw-waves">'
          . '<path class="gw-w1" d="M-16 ' . $surfFull . ' q6 -2.4 12 0 q6 2.4 12 0 q6 -2.4 12 0 q6 2.4 12 0 q6 -2.4 12 0 q6 2.4 12 0 L84 ' . ($surfFull + 20) . ' L-16 ' . ($surfFull + 20) . ' Z" fill="#CDEBFA" opacity=".75"/>'
          . '<path class="gw-w2" d="M-16 ' . ($surfFull + 1.4) . ' q6 2.2 12 0 q6 -2.2 12 0 q6 2.2 12 0 q6 -2.2 12 0 q6 2.2 12 0 q6 -2.2 12 0 L84 ' . ($surfFull + 22) . ' L-16 ' . ($surfFull + 22) . ' Z" fill="#8FD0EF" opacity=".45"/>'
          . '</g></g>';
    // سیلوئت شیشه — همیشه دیده می‌شود (حتی خالی)
    $svg .= '<path d="M5.4 5.4 L38.6 5.4 L36.6 50 Q36.4 53.6 32.6 53.6 L11.4 53.6 Q7.6 53.6 7.4 50 Z" '
          . 'fill="none" stroke="#BBD8E8" stroke-width="0.95"/>';
    // ته ضخیم
    $svg .= '<ellipse cx="22" cy="50.8" rx="11.6" ry="2.5" fill="#DCEBF4" opacity=".75"/>';
    // لبهٔ بیضی نازک
    $svg .= '<ellipse cx="22" cy="5.4" rx="16.6" ry="3.5" fill="none" stroke="#9CC2DA" stroke-width="1.05"/>';
    $svg .= '<ellipse cx="22" cy="5.4" rx="14.9" ry="2.9" fill="none" stroke="#BBD8E8" stroke-width="0.9"/>';
    // درخشش لبه
    $svg .= '<ellipse cx="15" cy="4.6" rx="4.6" ry="1.3" fill="#FFFFFF" opacity=".85"/>';
    // برق شیشهٔ نازک
    $svg .= '<path d="M12.4 12 Q11.6 30 12.6 44" fill="none" stroke="#FFFFFF" stroke-width="1.7" opacity=".7"/>';
    $svg .= '<path d="M15.6 14 Q15 30 15.8 42" fill="none" stroke="#FFFFFF" stroke-width="0.9" opacity=".38"/>';
    $svg .= '</svg>';
    return $svg;
}

/** لیوان بدون گروه آب (برای حالت نهایی/فقط‌خواندنی). */
function joma_v2_glass_svg_static($filled = false, $size = 30, $uid = '') {
    return joma_v2_glass_svg($filled, $size, $uid);
}
