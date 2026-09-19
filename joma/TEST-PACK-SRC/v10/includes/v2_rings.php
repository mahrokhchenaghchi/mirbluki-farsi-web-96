<?php
/**
 * جوما — حلقهٔ پیشرفت (بستهٔ v10 · F1)
 * ------------------------------------------------------------------
 * طبق SPEC/03-components §۳ (سند ۲۰): حلقه فقط با عددِ بک‌اند رسم می‌شود؛
 * اگر عدد نبود، حلقه **حذف** می‌شود — هرگز با صفر پر نمی‌شود.
 * اندازهٔ `mring` طبق سند: ۶۰px · شعاع ۲۶.
 * این فایل فقط «رسم» است؛ هیچ محاسبه و هیچ عددی این‌جا ساخته نمی‌شود.
 */

if (!function_exists('joma_v2_mring')) :
    /**
     * @param int|null $pct    درصد ۰ تا ۱۰۰ از بک‌اند؛ null = حلقه رسم نشود
     * @param string   $label  زیرنویس حلقه (متن ثابت رابط)
     * @param string   $variant 'a' (سبز) یا 'b' (آبی)
     * @return string HTML حلقه یا رشتهٔ خالی
     */
    function joma_v2_mring($pct, $label = '', $variant = 'a') {
        if ($pct === null) return '';
        $pct = (int) round((float) $pct);
        if ($pct < 0) $pct = 0;
        if ($pct > 100) $pct = 100;
        $r = 26;
        $c = 2 * M_PI * $r;                      // ۱۶۳٫۳۶
        $dash = round($c * $pct / 100, 2);
        $svg = '<svg viewBox="0 0 60 60" aria-hidden="true">'
             . '<circle class="rt" cx="30" cy="30" r="' . $r . '"/>'
             . '<circle class="rp' . ($variant === 'b' ? ' g2' : '') . '" cx="30" cy="30" r="' . $r . '"'
             . ' stroke-dasharray="' . $dash . ' ' . round($c, 2) . '"/></svg>';
        return '<div class="mring" role="img" aria-label="' . e(fa_num($pct) . ' درصد ' . $label) . '">'
             . $svg
             . '<div class="lbl"><b class="num">' . e(fa_num($pct) . '٪') . '</b>'
             . ($label !== '' ? '<span>' . e($label) . '</span>' : '')
             . '</div></div>';
    }
endif;
