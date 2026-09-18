<?php
/**
 * JOMA — تصویر «جوجهٔ من» و «تخم» (طرح نسخهٔ ۲۰)
 * از خانوادهٔ جغد جوما: بدن گرد یکپارچه · کاکل · شکم کرم · عینک طلایی · منقار نارنجی
 * این فایل فقط شکل می‌سازد؛ هیچ داده‌ای نمی‌سازد و هیچ حالتی را از خودش تعیین نمی‌کند.
 * stage: egg | crack | chick      state: calm | happy | sleep | faded | gray
 */

function joma_v2_chick_palette($state) {
    $p = array(
        'body' => '#5E93C8', 'body2' => '#3F6FA0', 'belly' => '#F6EBD9', 'bellyline' => '#E7D9C2',
        'ring' => '#E8B64A', 'pupil' => '#2E3A48', 'beak' => '#F0A63C', 'blush' => '#E07898',
        'opacity' => '1',
    );
    if ($state === 'faded') { $p['opacity'] = '.62'; }
    if ($state === 'gray') {
        $p['body'] = '#9FB0BC'; $p['body2'] = '#7C8C99'; $p['belly'] = '#EFEFEA';
        $p['bellyline'] = '#DADAD2'; $p['ring'] = '#C3AE7A'; $p['pupil'] = '#4A5560';
        $p['beak'] = '#C6A48A'; $p['blush'] = '#C9A7B0';
    }
    return $p;
}

/** تخم (مرحلهٔ egg / crack) — اندازهٔ صریح */
function joma_v2_egg_svg($cracked = false, $size = 130) {
    $w = (int) $size;
    $h = (int) round($size * 1.22);
    $svg = '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 120 146" aria-hidden="true" style="display:block">';
    $svg .= '<ellipse cx="60" cy="82" rx="43" ry="54" fill="#FFF7EA" stroke="#E5D5B4" stroke-width="2.5"/>';
    $svg .= '<circle cx="44" cy="62" r="3.4" fill="#F0E2C6"/><circle cx="72" cy="78" r="4.2" fill="#F0E2C6"/>';
    $svg .= '<circle cx="55" cy="104" r="3" fill="#F0E2C6"/><circle cx="80" cy="52" r="2.6" fill="#F0E2C6"/>';
    if ($cracked) {
        $svg .= '<path d="M28 74 L44 82 L36 92 L54 98 L46 108 L66 112 L62 96 L78 90 L70 78 L84 70" '
              . 'fill="none" stroke="#C9B387" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>';
    }
    $svg .= '</svg>';
    return $svg;
}

/** جوجهٔ جغد — اندازهٔ صریح */
function joma_v2_chick_svg($state = 'calm', $size = 150) {
    $p = joma_v2_chick_palette($state);
    $w = (int) $size;
    $h = (int) round($size * 1.12);
    $svg = '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 120 134" aria-hidden="true" style="display:block;opacity:' . $p['opacity'] . '">';

    // کاکل‌ها
    $svg .= '<path d="M34 30C36 18 43 10 53 7c-2 8-2 15 1 22Z" fill="' . $p['body2'] . '"/>';
    $svg .= '<path d="M86 30c-2-12-9-20-19-23 2 8 2 15-1 22Z" fill="' . $p['body2'] . '"/>';
    // بدن یکپارچه (سر و تنه یک فرم)
    $svg .= '<path d="M60 16c28 0 44 21 44 52 0 31-17 49-44 49S16 99 16 68c0-31 16-52 44-52Z" fill="' . $p['body'] . '"/>';
    // بال‌ها
    $svg .= '<path d="M20 66c-4 14 2 26 12 30-3-12-4-22-2-30Z" fill="' . $p['body2'] . '" opacity=".55"/>';
    $svg .= '<path d="M100 66c4 14-2 26-12 30 3-12 4-22 2-30Z" fill="' . $p['body2'] . '" opacity=".55"/>';
    // شکم کرم
    $svg .= '<ellipse cx="60" cy="86" rx="28" ry="27" fill="' . $p['belly'] . '"/>';
    // عینک طلایی (همیشه روی صورت)
    $svg .= '<circle cx="44" cy="54" r="15" fill="none" stroke="' . $p['ring'] . '" stroke-width="3"/>';
    $svg .= '<circle cx="76" cy="54" r="15" fill="none" stroke="' . $p['ring'] . '" stroke-width="3"/>';
    $svg .= '<path d="M59 52h2" stroke="' . $p['ring'] . '" stroke-width="3" stroke-linecap="round"/>';
    $svg .= '<path d="M29 50l-8-4M91 50l8-4" stroke="' . $p['ring'] . '" stroke-width="3" stroke-linecap="round"/>';

    $svg .= '<g class="pet-eyes" style="transform-box:fill-box;transform-origin:center">';
    if ($state === 'happy') {
        // چشم بستهٔ خندان
        $svg .= '<path d="M38 56c3-4 9-4 12 0" fill="none" stroke="' . $p['pupil'] . '" stroke-width="2.6" stroke-linecap="round"/>';
        $svg .= '<path d="M70 56c3-4 9-4 12 0" fill="none" stroke="' . $p['pupil'] . '" stroke-width="2.6" stroke-linecap="round"/>';
        $svg .= '<ellipse class="pet-blush" cx="30" cy="66" rx="6" ry="4" fill="' . $p['blush'] . '" opacity=".5"/>';
        $svg .= '<ellipse class="pet-blush" cx="90" cy="66" rx="6" ry="4" fill="' . $p['blush'] . '" opacity=".5"/>';
    } elseif ($state === 'sleep') {
        $svg .= '<path d="M38 54h12M70 54h12" stroke="' . $p['pupil'] . '" stroke-width="2.6" stroke-linecap="round"/>';
    } elseif ($state === 'faded' || $state === 'gray') {
        $svg .= '<circle cx="44" cy="54" r="7" fill="' . $p['pupil'] . '" opacity=".75"/>';
        $svg .= '<circle cx="76" cy="54" r="7" fill="' . $p['pupil'] . '" opacity=".75"/>';
    } else {
        $svg .= '<circle cx="44" cy="54" r="8" fill="' . $p['pupil'] . '"/>';
        $svg .= '<circle cx="76" cy="54" r="8" fill="' . $p['pupil'] . '"/>';
        $svg .= '<circle cx="46.5" cy="51" r="2.6" fill="#fff"/><circle cx="78.5" cy="51" r="2.6" fill="#fff"/>';
    }
    $svg .= '</g>';
    // منقار و پاها
    $svg .= '<path d="M60 60l6 7-6 5-6-5Z" fill="' . $p['beak'] . '"/>';
    $svg .= '<path d="M50 116v8M60 116v9M70 116v8" stroke="' . $p['beak'] . '" stroke-width="3.4" stroke-linecap="round"/>';
    // جرقه‌های شادی (بدون پاداش؛ فقط شکل)
    $svg .= '<g class="pet-sparks" opacity="0">'
          . '<circle cx="18" cy="26" r="2.6" fill="' . $p['ring'] . '"/>'
          . '<circle cx="102" cy="34" r="2.2" fill="' . $p['ring'] . '"/>'
          . '<circle cx="96" cy="16" r="1.8" fill="' . $p['ring'] . '"/></g>';
    $svg .= '</svg>';
    return $svg;
}

/** تصویر مرحله (تخم/ترک/جوجه) با اندازهٔ صریح */
function joma_v2_pet_svg($stage, $state = 'calm', $size = 150) {
    if ($stage === 'egg') return joma_v2_egg_svg(false, $size);
    if ($stage === 'crack') return joma_v2_egg_svg(true, $size);
    return joma_v2_chick_svg($state, $size);
}
