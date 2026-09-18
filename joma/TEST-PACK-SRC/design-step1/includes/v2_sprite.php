<?php
/**
 * JOMA — اسپرایت SVG (جغد + آیکون‌ها)
 * مستقیماً از دارایی مرجع فرانت نسخهٔ ۲۰ (sprite.js) استخراج شده؛ دستی بازکشیده نشده.
 * یک‌بار در layout چاپ می‌شود و همه‌جا با <svg class="ic"><use href="#i-..."/></svg> صدا زده می‌شود.
 */
function joma_sprite() {
    return <<<'JOMA_SPRITE'
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><symbol id="owl-core" viewBox="0 0 120 120">
    <path d="M27 25C29 15 35 8 44 5c-2 7-2 13 1 19Z" fill="var(--owl-b)"/>
    <path d="M93 25c-2-10-8-17-17-20 2 7 2 13-1 19Z" fill="var(--owl-b)"/>
    <path d="M60 12c27 0 41 20 41 49 0 30-16 46-41 46S19 91 19 61c0-29 14-49 41-49Z" fill="var(--owl-a)"/>
    <ellipse cx="60" cy="75" rx="27" ry="25" fill="var(--owl-cream)"/>
    <ellipse cx="37" cy="63" rx="5" ry="3" fill="var(--owl-beak)" opacity=".35"/>
    <ellipse cx="83" cy="63" rx="5" ry="3" fill="var(--owl-beak)" opacity=".35"/>
    <circle cx="45" cy="50" r="10.5" fill="var(--owl-ink)"/><circle cx="75" cy="50" r="10.5" fill="var(--owl-ink)"/>
    <circle cx="48.5" cy="46.5" r="3.5" fill="#fff"/><circle cx="78.5" cy="46.5" r="3.5" fill="#fff"/>
    <circle cx="42.5" cy="53.5" r="1.6" fill="#fff" opacity=".8"/><circle cx="72.5" cy="53.5" r="1.6" fill="#fff" opacity=".8"/>
    <path d="M31 47 21 42M89 47l10-5" stroke="var(--owl-gold)" stroke-width="3" stroke-linecap="round"/>
    <circle cx="45" cy="50" r="14" fill="none" stroke="var(--owl-gold)" stroke-width="3"/>
    <circle cx="75" cy="50" r="14" fill="none" stroke="var(--owl-gold)" stroke-width="3"/>
    <path d="M58.5 44Q60 41.5 61.5 44" fill="none" stroke="var(--owl-gold)" stroke-width="3" stroke-linecap="round"/>
    <path d="M53.5 63Q60 60.5 66.5 63 63.5 70.5 60 71.5 56.5 70.5 53.5 63Z" fill="var(--owl-beak)"/>
    <path d="M45 106.5q5 7 10 0M65 106.5q5 7 10 0" fill="none" stroke="var(--owl-beak)" stroke-width="3.5" stroke-linecap="round"/>
  </symbol><symbol id="owl" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M21.5 52C11 61 9 77 16 89c6.5-4 11.5-16 12.5-29Z" fill="var(--owl-b)"/>
    <path d="M98.5 52C109 61 111 77 104 89c-6.5-4-11.5-16-12.5-29Z" fill="var(--owl-b)"/>
  </symbol><symbol id="owl-hi" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M21.5 52C11 61 9 77 16 89c6.5-4 11.5-16 12.5-29Z" fill="var(--owl-b)"/>
    <path d="M95 52c10-10 13-23 7.5-32-7 5-13 17-14.5 29Z" fill="var(--owl-b)"/>
    <path transform="translate(106 13)" d="M0-5 1.3-1.3 5 0 1.3 1.3 0 5-1.3 1.3-5 0-1.3-1.3Z" fill="var(--owl-gold)"/>
    <circle cx="93" cy="7" r="2" fill="var(--owl-cream)"/>
  </symbol><symbol id="owl-cheer" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M25 52C15 42 12 29 17.5 20c7 5 13 17 14.5 29Z" fill="var(--owl-b)"/>
    <path d="M95 52c10-10 13-23 7.5-32-7 5-13 17-14.5 29Z" fill="var(--owl-b)"/>
    <circle cx="18" cy="12" r="2.5" fill="var(--owl-rose)"/><circle cx="60" cy="6" r="2.5" fill="var(--owl-gold)"/>
    <circle cx="102" cy="12" r="2.5" fill="#7CB9E8"/><circle cx="8" cy="30" r="2" fill="#7CB9E8"/><circle cx="112" cy="30" r="2" fill="var(--owl-rose)"/>
  </symbol><symbol id="owl-think" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M21.5 52C11 61 9 77 16 89c6.5-4 11.5-16 12.5-29Z" fill="var(--owl-b)"/>
    <path d="M98.5 52C109 61 111 77 104 89c-6.5-4-11.5-16-12.5-29Z" fill="var(--owl-b)"/>
    <path d="M50 70q10-7 20 0-7 9-17 7Z" fill="var(--owl-b)"/>
    <circle cx="91" cy="32" r="6" fill="var(--owl-cream)" stroke="var(--owl-b)" stroke-width="1.5"/>
    <circle cx="101" cy="17" r="9.5" fill="var(--owl-cream)" stroke="var(--owl-b)" stroke-width="1.5"/>
    <circle cx="101" cy="17" r="3.5" fill="var(--owl-gold)"/>
  </symbol><symbol id="owl-read" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M21.5 52C11 61 9 77 16 89c6.5-4 11.5-16 12.5-29Z" fill="var(--owl-b)"/>
    <path d="M98.5 52C109 61 111 77 104 89c-6.5-4-11.5-16-12.5-29Z" fill="var(--owl-b)"/>
    <path d="M60 78c-8-6-20-8-30-6v24c10-2 22 0 30 6Z" fill="#fff" stroke="#D8D2C4" stroke-width="1.5"/>
    <path d="M60 78c8-6 20-8 30-6v24c-10-2-22 0-30 6Z" fill="#fff" stroke="#D8D2C4" stroke-width="1.5"/>
    <path d="M60 78v24" stroke="#D8D2C4" stroke-width="1.5"/>
    <path d="M36 80h16M36 86h16M68 80h16M68 86h16" stroke="#C9C2B2" stroke-width="1.5" stroke-linecap="round"/>
    <circle cx="31" cy="83" r="5.5" fill="var(--owl-b)"/><circle cx="89" cy="83" r="5.5" fill="var(--owl-b)"/>
  </symbol><symbol id="owl-moon" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M21.5 52C11 61 9 77 16 89c6.5-4 11.5-16 12.5-29Z" fill="var(--owl-b)"/>
    <path d="M98.5 52C109 61 111 77 104 89c-6.5-4-11.5-16-12.5-29Z" fill="var(--owl-b)"/>
    <circle cx="45" cy="50" r="11.5" fill="var(--owl-cream)"/><circle cx="75" cy="50" r="11.5" fill="var(--owl-cream)"/>
    <path d="M36.5 50q8.5 7.5 17 0M66.5 50q8.5 7.5 17 0" fill="none" stroke="var(--owl-ink)" stroke-width="3" stroke-linecap="round"/>
    <text x="94" y="32" font-size="13" font-weight="800" fill="var(--owl-b)" font-family="inherit">z</text>
    <text x="102" y="20" font-size="9" font-weight="800" fill="var(--owl-b)" font-family="inherit">z</text>
  </symbol><symbol id="owl-lotus" viewBox="0 0 120 120"><use href="#owl-core"/>
    <circle cx="45" cy="50" r="11.5" fill="var(--owl-cream)"/><circle cx="75" cy="50" r="11.5" fill="var(--owl-cream)"/>
    <path d="M36.5 50q8.5 7.5 17 0M66.5 50q8.5 7.5 17 0" fill="none" stroke="var(--owl-ink)" stroke-width="3" stroke-linecap="round"/>
    <path d="M34 85q26 12 52 0" fill="none" stroke="var(--owl-b)" stroke-width="6" stroke-linecap="round"/>
    <path d="M95 22c-2-2.6-6-1.6-6 1.6 0 2.2 3 4 6 6 3-2 6-3.8 6-6 0-3.2-4-4.2-6-1.6Z" fill="var(--owl-rose)"/>
  </symbol><symbol id="owl-fit" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M21.5 52C11 61 9 77 16 89c6.5-4 11.5-16 12.5-29Z" fill="var(--owl-b)"/>
    <path d="M98.5 52C109 61 111 77 104 89c-6.5-4-11.5-16-12.5-29Z" fill="var(--owl-b)"/>
    <path d="M26 27q34-17 68 0-2.5 8-5 8-30-15-58 0-2.5 0-5-8Z" fill="var(--owl-rose)"/>
    <path d="M22 40q3 4 0 7-3-3 0-7Z" fill="#7CB9E8"/>
    <rect x="38" y="87" width="44" height="6" rx="3" fill="var(--owl-ink)"/>
    <rect x="30" y="80" width="8" height="20" rx="3" fill="var(--owl-gold)"/>
    <rect x="82" y="80" width="8" height="20" rx="3" fill="var(--owl-gold)"/>
    <ellipse cx="46" cy="90" rx="5" ry="4" fill="var(--owl-b)"/><ellipse cx="74" cy="90" rx="5" ry="4" fill="var(--owl-b)"/>
  </symbol><symbol id="owl-water" viewBox="0 0 120 120"><use href="#owl-core"/>
    <path d="M21.5 52C11 61 9 77 16 89c6.5-4 11.5-16 12.5-29Z" fill="var(--owl-b)"/>
    <path d="M98.5 52C109 61 111 77 104 89c-6.5-4-11.5-16-12.5-29Z" fill="var(--owl-b)"/>
    <path d="M13.4 71 25.6 71 24.3 88 14.7 88Z" fill="#7CB9E8" opacity=".85"/>
    <path d="M12 62 27 62 24.5 90 14.5 90Z" fill="none" stroke="var(--owl-b)" stroke-width="2"/>
    <circle cx="18" cy="76" r="1.2" fill="#fff"/><circle cx="21" cy="81" r="1" fill="#fff"/>
  </symbol><symbol id="owl-logo" viewBox="0 0 24 24">
    <circle cx="12" cy="13" r="9" fill="var(--owl-a)"/>
    <path d="M5.5 8.5 7 3.5l3.5 3.5Z" fill="var(--owl-b)"/><path d="M18.5 8.5 17 3.5 13.5 7Z" fill="var(--owl-b)"/>
    <path d="M4.4 11.4 2.6 10.4M19.6 11.4l1.8-1" stroke="var(--owl-gold)" stroke-width="1.6" stroke-linecap="round"/>
    <circle cx="8.7" cy="12" r="3.2" fill="none" stroke="var(--owl-gold)" stroke-width="1.7"/>
    <circle cx="15.3" cy="12" r="3.2" fill="none" stroke="var(--owl-gold)" stroke-width="1.7"/>
    <path d="M11.6 10.8Q12 10.2 12.4 10.8" fill="none" stroke="var(--owl-gold)" stroke-width="1.4" stroke-linecap="round"/>
    <circle cx="8.7" cy="12" r="1.7" fill="var(--owl-ink)"/><circle cx="15.3" cy="12" r="1.7" fill="var(--owl-ink)"/>
    <circle cx="9.3" cy="11.4" r=".55" fill="#fff"/><circle cx="15.9" cy="11.4" r=".55" fill="#fff"/>
    <path d="M10.7 14.8h2.6L12 16.6Z" fill="var(--owl-beak)"/>
  </symbol><symbol id="i-arr-l" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></symbol><symbol id="i-chev-l" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m14.5 6-6 6 6 6"/></symbol><symbol id="i-chev-r" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9.5 6 6 6-6 6"/></symbol><symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></symbol><symbol id="i-checkc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.4 2.4 4.6-4.9"/></symbol><symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol><symbol id="i-heart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20.5s-7.5-4.7-9.3-9.3C1.5 8 3.6 4.9 6.9 4.9c2 0 3.6 1.1 4.4 2.7.8-1.6 2.4-2.7 4.4-2.7 3.3 0 5.4 3.1 4.2 6.3-1.8 4.6-7.9 9.3-7.9 9.3Z"/></symbol><symbol id="i-cal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3.5" y="5" width="17" height="16" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/></symbol><symbol id="i-list" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4.5" cy="6" r="1.2"/><circle cx="4.5" cy="12" r="1.2"/><circle cx="4.5" cy="18" r="1.2"/></symbol><symbol id="i-chart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 20v-7M12 20V5M19 20v-10"/></symbol><symbol id="i-trend" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 5.5-5.5 3.5 3.5L21 6"/><path d="M15 6h6v6"/></symbol><symbol id="i-compass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/></symbol><symbol id="i-book" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6c-1.5-1.4-3.8-2-6.5-2v14c2.7 0 5 .6 6.5 2 1.5-1.4 3.8-2 6.5-2V4c-2.7 0-5 .6-6.5 2Z"/><path d="M12 6v14"/></symbol><symbol id="i-grad" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m12 4 10 4.5L12 13 2 8.5 12 4Z"/><path d="M6.5 10.5V15c0 1.4 2.5 3 5.5 3s5.5-1.6 5.5-3v-4.5"/></symbol><symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="8.5" r="3.2"/><path d="M3.5 19.5c.9-3 3-4.5 5.5-4.5s4.6 1.5 5.5 4.5"/><circle cx="17" cy="9.5" r="2.4"/><path d="M16.5 15.2c2.1.2 3.6 1.5 4.2 3.8"/></symbol><symbol id="i-help" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.2A2.5 2.5 0 0 1 14.5 10c0 1.6-2.4 2-2.4 3.4"/><circle cx="12" cy="16.8" r=".4"/></symbol><symbol id="i-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><circle cx="12" cy="7.8" r=".4"/></symbol><symbol id="i-gear" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="3.2"/><path d="M12 2.8v2.4M12 18.8v2.4M2.8 12h2.4M18.8 12h2.4M5.5 5.5l1.7 1.7M16.8 16.8l1.7 1.7M18.5 5.5l-1.7 1.7M7.2 16.8l-1.7 1.7"/></symbol><symbol id="i-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9a6 6 0 1 0-12 0c0 6-2.5 7-2.5 7h17S18 15 18 9Z"/><path d="M10 19.5a2.2 2.2 0 0 0 4 0"/></symbol><symbol id="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5 5l1.4 1.4M17.6 17.6 19 19M19 5l-1.4 1.4M6.4 17.6 5 19"/></symbol><symbol id="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4 8.5 8.5 0 1 0 20 14.5Z"/></symbol><symbol id="i-drop" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 3.5s6 6.2 6 10.5a6 6 0 0 1-12 0C6 9.7 12 3.5 12 3.5Z"/></symbol><symbol id="i-phoneoff" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="7" y="3" width="10" height="18" rx="2.5"/><path d="M11 18h2M4 4l16 16"/></symbol><symbol id="i-lotus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="2.6"/><path d="M12 10.5c-2.8 0-5 2.2-5 5.5M12 10.5c2.8 0 5 2.2 5 5.5M4.5 19.5c4.8 1.8 10.2 1.8 15 0"/></symbol><symbol id="i-walk" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="13" cy="4.5" r="1.8"/><path d="M12.5 21v-4.5l-2.5-3 .8-5 2.2 3.5 3 1.5M9 21l1-3.5M10.8 8.5 8 10l-1 3"/></symbol><symbol id="i-apple" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8.5c-1-.8-2.4-1.1-3.7-.6-2.4.9-3.3 3.8-2.2 6.6 1 2.6 3 4.5 4.7 4 .4-.1.8-.3 1.2-.3s.8.2 1.2.3c1.7.5 3.7-1.4 4.7-4 1.1-2.8.2-5.7-2.2-6.6-1.3-.5-2.7-.2-3.7.6Z"/><path d="M12 8.5c0-2 .8-3.5 2.5-4.5"/></symbol><symbol id="i-target" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/></symbol><symbol id="i-save" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="14" height="17" rx="2.5"/><path d="M9 4.5V3h6v1.5"/><path d="m9 13 2.2 2.2L15.5 11"/></symbol><symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 5.5 18.5 9.5 8 20H4v-4L14.5 5.5Z"/><path d="m13 7 4 4"/></symbol><symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></symbol><symbol id="i-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 5.5h15v10h-8.5l-4.5 3.5v-3.5h-2Z"/><path d="M8.5 9.5h7M8.5 12.5h4"/></symbol><symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10.5" width="14" height="9.5" rx="2.4"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"/></symbol><symbol id="i-star" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4l2.4 5 5.6.7-4 3.9 1 5.4-5-2.7-5 2.7 1-5.4-4-3.9 5.6-.7Z"/></symbol><symbol id="i-link" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14.5 14.5 10"/><path d="M9 7.5 11 5.5a3.5 3.5 0 0 1 5 5l-2 2"/><path d="M15 16.5l-2 2a3.5 3.5 0 0 1-5-5l2-2"/></symbol><symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.6"/></symbol><symbol id="i-download" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v10"/><path d="m8 10.5 4 4 4-4"/><path d="M5 19.5h14"/></symbol><symbol id="i-file" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 3.5h7L18 8v12.5H6.5Z"/><path d="M13.5 3.5V8H18"/></symbol><symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7.5h14M9.5 7.5V5h5v2.5"/><path d="M7 7.5 8 20h8l1-12.5"/></symbol><symbol id="i-bolt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 3 6 13h5l-1 8 7-10h-5Z"/></symbol><symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></symbol><symbol id="i-play" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"><path d="M8 5.5 18 12 8 18.5Z"/></symbol><symbol id="i-x" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></symbol><symbol id="i-vol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9.5h3.5L12 5.5v13l-4.5-4H4Z"/><path d="M15.5 9.5a3.6 3.6 0 0 1 0 5"/><path d="M18 7.5a6.4 6.4 0 0 1 0 9"/></symbol></defs></svg>
JOMA_SPRITE;
}
