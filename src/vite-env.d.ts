/// <reference types="vite/client" />

/**
 * فقط در بیلد «دموی تک‌فایلی» (vite.singlefile.config.ts) مقدارش true است.
 * در بیلد عادی و محیط توسعه تعریف نمی‌شود (typeof-guard در App.tsx).
 */
declare const __DEMO_BUILD__: boolean;
