#!/usr/bin/env node
/**
 * ساخت فایل دموی تک‌فایلی (Standalone Demo)
 * ------------------------------------------------------------
 * خروجی: demo/pizza-royal-demo.html
 * یک فایل HTML که همه‌چیز داخلش جاسازی شده است:
 *   - کد جاوااسکریپت و CSS باندل‌شده
 *   - همه تصاویر منو (base64)
 *   - فونت وزیرمتن (woff2, base64)
 *   - favicon
 *
 * این فایل بدون سرور و بدون اینترنت با دابل‌کلیک باز می‌شود و کل اپ
 * (سایت مشتری + پنل مدیریت + حالت دمو) داخلش کار می‌کند.
 *
 * اجرا:  node scripts/build-single-file.mjs
 */

import { execSync } from "node:child_process";
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const dist = path.join(root, "dist-single");

console.log("==> 1/5 در حال ساخت با Vite (حالت تک‌فایلی)...");
execSync("npx vite build --config vite.singlefile.config.ts", {
  cwd: root,
  stdio: "inherit",
});

console.log("==> 2/5 خواندن باندل...");
let html = fs.readFileSync(path.join(dist, "index.html"), "utf8");

const assetsDir = path.join(dist, "assets");
const cssFiles = fs.readdirSync(assetsDir).filter((f) => f.endsWith(".css"));
const jsFiles = fs.readdirSync(assetsDir).filter((f) => f.endsWith(".js"));

let css = cssFiles.map((f) => fs.readFileSync(path.join(assetsDir, f), "utf8")).join("\n");
let js = jsFiles.map((f) => fs.readFileSync(path.join(assetsDir, f), "utf8")).join("\n");

console.log(`    CSS: ${cssFiles.length} فایل | JS: ${jsFiles.length} فایل`);

// ------------------------------------------------------------
// 3/5 جاسازی فونت وزیرمتن (برای کارکرد کاملا آفلاین)
// ------------------------------------------------------------
console.log("==> 3/5 جاسازی فونت وزیرمتن...");
const fontDir = path.join(root, "scripts", "fonts");
const ARABIC_UR =
  "U+0600-06FF,U+0750-077F,U+0870-088E,U+0890-0891,U+0897-08E1,U+08E3-08FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FBC1,U+FC00-FC64,U+FE70-FE74,U+FE76-FEFC";
const LATIN_UR =
  "U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD";

const fontFaces = fs
  .readdirSync(fontDir)
  .filter((f) => f.endsWith(".woff2"))
  .map((f) => {
    const m = f.match(/vazirmatn-(arabic|latin)-(\d+)-normal\.woff2/);
    if (!m) return "";
    const [, subset, weight] = m;
    const b64 = fs.readFileSync(path.join(fontDir, f)).toString("base64");
    const ur = subset === "arabic" ? ARABIC_UR : LATIN_UR;
    return `@font-face{font-family:'Vazirmatn';font-style:normal;font-weight:${weight};font-display:swap;src:url(data:font/woff2;base64,${b64}) format('woff2');unicode-range:${ur};}`;
  })
  .filter(Boolean)
  .join("\n");

css = fontFaces + "\n" + css;

// ------------------------------------------------------------
// 4/5 جاسازی تصاویر به صورت data URI
//     هر تصویر فقط یک بار در جدول __DEMO_IMGS ذخیره می‌شود و
//     رشته‌های مسیر در کد به فراخوانی کمکی تبدیل می‌شوند
//     (نتیجه: حجم نهایی چند برابر کوچک‌تر از کپی پایه۶۴ تکراری)
// ------------------------------------------------------------
console.log("==> 4/5 جاسازی تصاویر...");
const imagesDir = path.join(dist, "images");
const imgMap = {};

if (fs.existsSync(imagesDir)) {
  const walk = (dir) =>
    fs.readdirSync(dir, { withFileTypes: true }).flatMap((e) => {
      const p = path.join(dir, e.name);
      return e.isDirectory() ? walk(p) : [p];
    });
  for (const imgPath of walk(imagesDir)) {
    const rel = "/" + path.relative(dist, imgPath).split(path.sep).join("/");
    const b64 = fs.readFileSync(imgPath).toString("base64");
    const mime = imgPath.endsWith(".png") ? "image/png" : "image/jpeg";
    imgMap[rel] = `data:${mime};base64,${b64}`;
  }
}

// تبدیل رشته‌های "…/images/…" به __DEMO_IMG("…/images/…")
let replacedCount = 0;
for (const rel of Object.keys(imgMap)) {
  const needle = `"${rel}"`;
  if (js.includes(needle)) {
    replacedCount += js.split(needle).length - 1;
    js = js.replaceAll(needle, `__DEMO_IMG("${rel}")`);
  }
}

// جدول تصاویر + تابع کمکی — ابتدای باندل تزریق می‌شود
const imgTable = `window.__DEMO_IMGS=${JSON.stringify(imgMap)};const __DEMO_IMG=(p)=>window.__DEMO_IMGS[p]||p;\n`;
js = imgTable + js;
console.log(`    ${Object.keys(imgMap).length} تصویر یکتا، ${replacedCount} ارجاع بازنویسی شد`);

// جلوگیری از شکستن تگ‌ها توسط محتوای اسکریپت/استایل
js = js.replace(/<\/script/gi, "<\\/script");
css = css.replace(/<\/style/gi, "<\\/style");

// ------------------------------------------------------------
// 5/5 ساخت HTML نهایی
// ------------------------------------------------------------
console.log("==> 5/5 ساخت HTML نهایی...");

// حذف لینک‌های فونت گوگل (فونت جاسازی‌شده جایگزین می‌شود)
html = html.replace(/<link[^>]*fonts\.googleapis[^>]*>\s*/g, "");
html = html.replace(/<link[^>]*fonts\.gstatic[^>]*>\s*/g, "");

// favicon پیتزا 🍕
html = html.replace(
  /<link[^>]*rel="icon"[^>]*>/g,
  `<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E%F0%9F%8D%95%3C/text%3E%3C/svg%3E">`
);

// حذف og:image (مسیر تصویر محلی در فایل مستقل معنا ندارد)
html = html.replace(/<meta[^>]*property="og:image"[^>]*>\s*/g, "");

// حذف لینک‌های استایل و preload (جایگزین با محتوای inline)
html = html.replace(/<link[^>]*rel="stylesheet"[^>]*>\s*/g, "");
html = html.replace(/<link[^>]*rel="modulepreload"[^>]*>\s*/g, "");

// تزریق CSS جاسازی‌شده
html = html.replace("</head>", `<style>${css}\n</style>\n</head>`);

// تزریق JS جاسازی‌شده (جایگزین تگ اسکریپت باندل)
const scriptTagCount = (html.match(/<script[^>]*type="module"[^>]*src=/g) || []).length;
if (scriptTagCount !== 1) {
  console.warn(`    ⚠️ تعداد تگ اسکریپت غیرمنتظره: ${scriptTagCount}`);
}
html = html.replace(
  /<script[^>]*type="module"[^>]*src="[^"]*"[^>]*><\/script>/,
  () => `<script type="module">\n${js}\n</script>`
);

// نوشتن خروجی‌ها
const outDir = path.join(root, "demo");
fs.mkdirSync(outDir, { recursive: true });
const out = path.join(outDir, "pizza-royal-demo.html");
fs.writeFileSync(out, html);

// نسخه هاست وب — GitHub Pages از پوشه docs سرو می‌کند و همین فایل مستقل
// روی هر هاست استاتیک/CDN (jsDelivr، raw.githack و...) هم قابل ارائه است
const docsDir = path.join(root, "docs");
fs.mkdirSync(docsDir, { recursive: true });
fs.writeFileSync(path.join(docsDir, "index.html"), html);
fs.writeFileSync(path.join(docsDir, ".nojekyll"), "");

const sizeMB = (fs.statSync(out).size / (1024 * 1024)).toFixed(2);
console.log(`\n✅ فایل دمو ساخته شد: demo/pizza-royal-demo.html (${sizeMB} مگابایت)`);
console.log("   با دابل‌کلیک و بدون اینترنت باز می‌شود (فایل محلی، gitignore شده)");
console.log("✅ نسخه هاست وب: docs/index.html + docs/.nojekyll (برای GitHub Pages / CDN)");
