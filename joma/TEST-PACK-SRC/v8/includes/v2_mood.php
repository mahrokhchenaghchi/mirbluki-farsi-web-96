<?php
/**
 * JOMA — تصویرهای ویزارد «حال من» (SPEC/13 §۳)
 * =========================================================================
 * قاعدهٔ مالک: تصویرِ هر گزینه **داخل خودِ گزینه** می‌آید و تصویر دو گزینهٔ
 * بغل‌هم **یکسان نیست**. پس هر «سطح» تصویر مستقل خودش را دارد:
 *
 *   حال (general) : پنج چهرهٔ متفاوت
 *   انرژی (energy): باتری با پرشدگی [6,12,18,24,30] و رنگ ۱-۲ کورال · ۳ طلایی · ۴ آسمانی · ۵ نعنایی
 *   تمرکز (focus) : حلقه‌های هدف با مرکز شعاع 1.5+l×1.5 + چهار خط نشانه
 *   خواب (sleep)  : ماه --lav + ۱ تا ۵ ستارهٔ طلایی (برچسب «خواب دیشب»)
 *   استرس (stress): بادکنک شعاع 5+(l−1)×2.4 · رنگ ۱-۲ آسمانی · ۳ طلایی · ۴ کورال · ۵ رز
 *                   + بند منحنی + از سطح ۴ دو قوس هوا
 *
 * همه SVG با اندازهٔ صریح، بدون تصویر بیرونی، رنگ‌ها از توکن‌های طرح.
 * =========================================================================
 */

/** رنگ پرشدگی باتری: ۱-۲ کورال · ۳ طلایی · ۴ آسمانی · ۵ نعنایی */
function joma_mood_battery_color($level) {
    $l = max(1, min(5, (int) $level));
    if ($l <= 2) return 'var(--coral)';
    if ($l == 3) return 'var(--gold)';
    if ($l == 4) return 'var(--sky)';
    return 'var(--ok)';
}

/** رنگ بادکنک: ۱-۲ آسمانی · ۳ طلایی · ۴ کورال · ۵ رز */
function joma_mood_balloon_color($level) {
    $l = max(1, min(5, (int) $level));
    if ($l <= 2) return 'var(--sky)';
    if ($l == 3) return 'var(--gold)';
    if ($l == 4) return 'var(--coral)';
    return 'var(--rose)';
}

/** باتری — پرشدگی [6,12,18,24,30] */
function joma_mood_svg_battery($level, $size = 40) {
    $l = max(1, min(5, (int) $level));
    $widths = array(1 => 6, 2 => 12, 3 => 18, 4 => 24, 5 => 30);
    $w = $widths[$l];
    $col = joma_mood_battery_color($l);
    $h = (int) round($size * 0.62);
    $svg = '<svg width="' . (int) $size . '" height="' . $h . '" viewBox="0 0 78 48" aria-hidden="true" style="display:block">';
    $svg .= '<rect x="3" y="9" width="64" height="30" rx="8" fill="none" stroke="var(--ink-3)" stroke-width="2.5"/>';
    $svg .= '<rect x="69" y="18" width="6" height="12" rx="3" fill="var(--ink-3)"/>';
    $svg .= '<rect x="8" y="14" width="' . $w . '" height="20" rx="5" fill="' . $col . '"/>';
    $svg .= '</svg>';
    return $svg;
}

/** حلقه‌های هدف (سیبل) — مرکز با شعاع 1.5+l×1.5 و چهار خط نشانه */
function joma_mood_svg_focus($level, $size = 40) {
    $l = max(1, min(5, (int) $level));
    $r = 1.5 + ($l * 1.5);          // ۳ … ۹ (در مقیاس viewBox ۲ برابر می‌شود)
    $h = (int) round($size * 0.78);
    $svg = '<svg width="' . (int) $size . '" height="' . $h . '" viewBox="0 0 80 62" aria-hidden="true" style="display:block">';
    $svg .= '<circle cx="40" cy="31" r="24" fill="none" stroke="var(--ring-track)" stroke-width="3"/>';
    $svg .= '<circle cx="40" cy="31" r="16" fill="none" stroke="var(--ring-track)" stroke-width="3"/>';
    $svg .= '<circle cx="40" cy="31" r="' . round($r * 2.2, 1) . '" fill="var(--sky)" opacity=".22"/>';
    $svg .= '<circle cx="40" cy="31" r="' . round($r * 1.36, 1) . '" fill="var(--sky)" opacity=".55"/>';
    $svg .= '<circle cx="40" cy="31" r="' . round($r * 0.66, 1) . '" fill="var(--sky)"/>';
    $svg .= '<path d="M40 2v8M40 52v8M11 31h8M61 31h8" stroke="var(--ink-3)" stroke-width="2.2" stroke-linecap="round"/>';
    $svg .= '</svg>';
    return $svg;
}

/** ماه --lav + ۱ تا ۵ ستارهٔ طلایی */
function joma_mood_svg_sleep($level, $size = 40) {
    $l = max(1, min(5, (int) $level));
    $h = (int) round($size * 0.78);
    $svg = '<svg width="' . (int) $size . '" height="' . $h . '" viewBox="0 0 80 62" aria-hidden="true" style="display:block">';
    $svg .= '<path d="M44 8a22 22 0 1 0 0 44 26 26 0 0 1 0-44Z" fill="var(--lav)"/>';
    $stars = array(array(62, 12), array(72, 22), array(58, 30), array(74, 38), array(62, 48));
    for ($i = 0; $i < $l; $i++) {
        $p = $stars[$i];
        $svg .= '<path d="M' . $p[0] . ' ' . ($p[1] - 4) . 'l1.5 3.2 3.2 1.5-3.2 1.5L' . $p[0] . ' ' . ($p[1] + 4)
              . 'l-1.5-3.2L' . ($p[0] - 3.2) . ' ' . ($p[1] + 1.5) . 'Z" fill="var(--gold)"/>';
    }
    $svg .= '</svg>';
    return $svg;
}

/** بادکنک — شعاع 5+(l−1)×2.4 · بند منحنی · از سطح ۴ دو قوس هوا */
function joma_mood_svg_balloon($level, $size = 40) {
    $l = max(1, min(5, (int) $level));
    $r = 5 + (($l - 1) * 2.4);       // ۵ … ۱۴٫۶
    $r *= 2.0;                        // مقیاس viewBox
    $col = joma_mood_balloon_color($l);
    $h = (int) round($size * 0.92);
    $cx = 40; $cy = 26;
    $svg = '<svg width="' . (int) $size . '" height="' . $h . '" viewBox="0 0 80 74" aria-hidden="true" style="display:block">';
    $svg .= '<ellipse cx="' . $cx . '" cy="' . $cy . '" rx="' . round($r * 0.9, 1) . '" ry="' . round($r, 1) . '" fill="' . $col . '" opacity=".9"/>';
    $svg .= '<ellipse cx="' . round($cx - $r * 0.3, 1) . '" cy="' . round($cy - $r * 0.35, 1) . '" rx="' . round($r * 0.2, 1) . '" ry="' . round($r * 0.28, 1) . '" fill="#FFFFFF" opacity=".45"/>';
    $svg .= '<path d="M' . $cx . ' ' . round($cy + $r + 1, 1) . ' q6 5 -2 9 q-7 4 1 8" fill="none" stroke="var(--ink-3)" stroke-width="1.8" stroke-linecap="round"/>';
    if ($l >= 4) {
        $svg .= '<path d="M' . round($cx + $r + 3, 1) . ' ' . ($cy - 2) . ' q9 -7 14 -2" fill="none" stroke="var(--coral)" stroke-width="2" stroke-linecap="round"/>';
        $svg .= '<path d="M' . round($cx - $r - 4, 1) . ' ' . ($cy + 3) . ' h-6 M' . round($cx - $r - 4, 1) . ' ' . ($cy + 9) . ' h-8" stroke="var(--coral)" stroke-width="2" stroke-linecap="round"/>';
    }
    $svg .= '</svg>';
    return $svg;
}

/** چهره — پنج حالت متفاوت */
function joma_mood_svg_face($level, $size = 40) {
    $l = max(1, min(5, (int) $level));
    $mouths = array(
        1 => 'M26 44 q7 -8 14 0',      // غمگین
        2 => 'M26 43 q7 -4 14 0',      // کم‌دل
        3 => 'M27 44 h13',             // معمولی
        4 => 'M26 42 q7 6 14 0',       // خوب
        5 => 'M25 41 q8 10 16 0',      // عالی
    );
    $eyes = array(
        1 => array(2.4, 2.4), 2 => array(2.8, 2.8), 3 => array(3, 3), 4 => array(3.2, 3.2), 5 => array(3.4, 3.4),
    );
    $h = (int) round($size * 0.78);
    $svg = '<svg width="' . (int) $size . '" height="' . $h . '" viewBox="0 0 80 62" aria-hidden="true" style="display:block">';
    $svg .= '<circle cx="40" cy="31" r="25" fill="none" stroke="var(--rose)" stroke-width="3"/>';
    $svg .= '<circle cx="31" cy="25" r="' . $eyes[$l][0] . '" fill="var(--ink-2)"/><circle cx="49" cy="25" r="' . $eyes[$l][1] . '" fill="var(--ink-2)"/>';
    if ($l >= 4) $svg .= '<ellipse cx="24" cy="33" rx="4" ry="2.5" fill="var(--rose)" opacity=".45"/><ellipse cx="56" cy="33" rx="4" ry="2.5" fill="var(--rose)" opacity=".45"/>';
    $svg .= '<path d="' . $mouths[$l] . '" fill="none" stroke="var(--ink-2)" stroke-width="2.6" stroke-linecap="round"/>';
    $svg .= '</svg>';
    return $svg;
}

/**
 * تصویر یک گزینه: joma_mood_option_svg('general', 3)
 * kind: general | energy | focus | sleep | stress
 */
function joma_mood_option_svg($kind, $level, $size = 40) {
    if ($kind === 'energy') return joma_mood_svg_battery($level, $size);
    if ($kind === 'focus') return joma_mood_svg_focus($level, $size);
    if ($kind === 'sleep') return joma_mood_svg_sleep($level, $size);
    if ($kind === 'stress') return joma_mood_svg_balloon($level, $size);
    return joma_mood_svg_face($level, $size);
}

/** سازگاری با کدهای قدیمی (صفحه‌های دیگر از این استفاده نمی‌کنند). */
function joma_v2_mood_svg($kind, $value = 0, $size = 64) {
    $map = array('battery' => 'energy', 'rings' => 'focus', 'moon' => 'sleep', 'balloon' => 'stress', 'face' => 'general');
    $k = isset($map[$kind]) ? $map[$kind] : 'general';
    return joma_mood_option_svg($k, max(1, (int) $value), $size);
}
