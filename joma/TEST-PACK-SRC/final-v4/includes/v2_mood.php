<?php
/**
 * JOMA — تصویرهای ویزارد حال (SVG با اندازهٔ صریح، طبق SPEC/13)
 * battery · rings · moon · wave · face
 */
function joma_v2_mood_svg($kind, $value = 0, $size = 64) {
    $w = (int) $size;
    $h = (int) round($size * 0.78);
    $v = max(0, min(5, (int) $value));
    $svg = '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 80 62" aria-hidden="true" style="display:block">';

    if ($kind === 'battery') {
        $svg .= '<rect x="6" y="16" width="60" height="30" rx="8" fill="none" stroke="var(--ink-3)" stroke-width="2.5"/>';
        $svg .= '<rect x="68" y="25" width="6" height="12" rx="3" fill="var(--ink-3)"/>';
        $widths = array(0, 6, 12, 18, 24, 30);
        $colors = array('', 'var(--coral)', 'var(--coral)', 'var(--gold)', 'var(--sky)', 'var(--ok)');
        if ($v > 0) {
            $svg .= '<rect x="11" y="21" width="' . $widths[$v] . '" height="20" rx="5" fill="' . $colors[$v] . '"/>';
        }
    } elseif ($kind === 'rings') {
        $svg .= '<circle cx="40" cy="31" r="24" fill="none" stroke="var(--ring-track)" stroke-width="3"/>';
        $svg .= '<circle cx="40" cy="31" r="16" fill="none" stroke="var(--ring-track)" stroke-width="3"/>';
        if ($v > 0) $svg .= '<circle cx="40" cy="31" r="' . (1.5 + $v * 1.5) . '" fill="var(--sky)"/>';
        $svg .= '<path d="M40 2v8M40 52v8M11 31h8M61 31h8" stroke="var(--ink-3)" stroke-width="2.2" stroke-linecap="round"/>';
    } elseif ($kind === 'moon') {
        $svg .= '<path d="M46 8a22 22 0 1 0 0 44 26 26 0 0 1 0-44Z" fill="var(--lav)"/>';
        $pos = array(array(60, 14), array(70, 24), array(58, 30), array(72, 40), array(62, 48));
        for ($i = 0; $i < $v; $i++) {
            $p = $pos[$i];
            $svg .= '<path d="M' . $p[0] . ' ' . ($p[1] - 4) . 'l1.6 3.4 3.4 1.6-3.4 1.6L' . $p[0] . ' ' . ($p[1] + 4) . 'l-1.6-3.4L' . ($p[0] - 3.4) . ' ' . ($p[1] + 1.6) . 'Z" fill="var(--gold)"/>';
        }
    } elseif ($kind === 'wave') {
        $amp = 2 + $v * 2.4;
        $svg .= '<path d="M4 44 q8 ' . (-$amp) . ' 16 0 q8 ' . $amp . ' 16 0 q8 ' . (-$amp) . ' 16 0 q8 ' . $amp . ' 16 0" fill="none" stroke="var(--coral)" stroke-width="3" stroke-linecap="round"/>';
        $svg .= '<path d="M4 52 h72" stroke="var(--ring-track)" stroke-width="3" stroke-linecap="round"/>';
    } else { // face
        $faces = array(1 => 'M26 40q6-8 12 0', 2 => 'M26 42q6-5 12 0', 3 => 'M26 44h12', 4 => 'M26 44q6 6 12 0', 5 => 'M26 46q6 9 12 0');
        $svg .= '<circle cx="40" cy="31" r="26" fill="none" stroke="var(--rose)" stroke-width="3"/>';
        $svg .= '<circle cx="30" cy="24" r="3" fill="var(--ink-2)"/><circle cx="50" cy="24" r="3" fill="var(--ink-2)"/>';
        $svg .= '<path d="' . $faces[max(1, $v)] . '" fill="none" stroke="var(--ink-2)" stroke-width="2.6" stroke-linecap="round"/>';
    }
    $svg .= '</svg>';
    return $svg;
}
