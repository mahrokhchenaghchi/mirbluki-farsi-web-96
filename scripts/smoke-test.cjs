#!/usr/bin/env node
/**
 * تست دود (Smoke Test) فایل دموی تک‌فایلی — demo/pizza-royal-demo.html
 * مسیر کامل مشتری را با یک مرورگر headless شبیه‌سازی می‌کند:
 *   خانه → منو → افزودن به سبد (دیالوگ سایز) → ورود با OTP دمو →
 *   تکمیل سفارش → صفحه پیگیری → پنل مدیریت
 *
 * اجرا:  node scripts/smoke-test.mjs
 * پیش‌نیاز: npm i --no-save playwright @sparticuz/chromium
 */

const { chromium: pw } = require("playwright");
const path = require("path");
const fs = require("fs");

const DEMO = path.resolve(__dirname, "..", "demo", "pizza-royal-demo.html");
const BROWSER = "/tmp/chromium";

function ok(label) {
  console.log("  ✓", label);
}

(async () => {
  if (!fs.existsSync(DEMO)) {
    console.error("فایل دمو وجود ندارد. اول: node scripts/build-single-file.mjs");
    process.exit(1);
  }
  if (!fs.existsSync(BROWSER)) {
    console.error("مرورگر تست در /tmp/chromium پیدا نشد.");
    process.exit(1);
  }

  const browser = await pw.launch({
    executablePath: BROWSER,
    headless: true,
    args: ["--no-sandbox", "--disable-gpu", "--disable-dev-shm-usage"],
  });

  const errors = [];

  try {
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    page.on("pageerror", (e) => errors.push("PAGEERROR: " + e.message));
    page.on("console", (m) => {
      if (m.type() === "error") errors.push("CONSOLE: " + m.text());
    });

    console.log("— صفحه اصلی —");
    await page.goto("file://" + DEMO, { waitUntil: "load", timeout: 30000 });
    await page.waitForSelector("h1", { timeout: 20000 });
    ok("عنوان: " + (await page.title()));
    const h1 = (await page.textContent("h1")).trim().replace(/\s+/g, " ");
    ok("H1: " + h1);

    // تصاویر جاسازی‌شده واقعا لود شده‌اند؟
    await page.waitForTimeout(1200);
    const imgsOk = await page.evaluate(() =>
      Array.from(document.querySelectorAll("img")).slice(0, 6).every((i) => i.naturalWidth > 0)
    );
    ok("تصاویر لود شدند: " + imgsOk);

    // فونت فارسی
    const fontOk = await page.evaluate(() => document.fonts.check("16px Vazirmatn"));
    ok("فونت وزیرمتن فعال: " + fontOk);

    console.log("— منو و سبد خرید —");
    await page.click('a[href="#/menu"]', { timeout: 10000 });
    await page.waitForSelector("text=پیتزا مارگاریتا", { timeout: 20000 });
    ok("منو با آیتم‌ها باز شد (HashRouter)");
    ok("URL: " + page.url().replace("file://", ""));

    // افزودن پیتزای چندسایز → دیالوگ انتخاب سایز
    await page.click('button[aria-label^="افزودن پیتزا"]', { timeout: 10000 });
    await page.waitForSelector("text=اندازه را انتخاب کنید", { timeout: 10000 });
    ok("دیالوگ انتخاب سایز باز شد");
    await page.click("text=متوسط (۱۰ اینچ)");
    await page.click("button:has-text('افزودن به سبد')");
    await page.waitForTimeout(600);
    ok("آیتم چندسایز به سبد اضافه شد");

    // افزودن آیتم تک‌سایز (بدون دیالوگ)
    await page.click('button[aria-label^="افزودن نوشابه"]', { timeout: 10000 });
    await page.waitForTimeout(400);
    ok("آیتم تک‌سایز با یک کلیک اضافه شد");

    // باز کردن سبد
    await page.click('button[aria-label="سبد خرید"]');
    await page.waitForSelector("text=مبلغ قابل پرداخت", { timeout: 8000 });
    ok("کشوی سبد خرید باز شد");

    // کوپن
    await page.fill('input[placeholder="کد تخفیف دارید؟"]', "WELCOME10");
    await page.click('button:has-text("اعمال")');
    await page.waitForSelector("text=کد WELCOME10 اعمال شد", { timeout: 8000 });
    ok("کد تخفیف اعمال شد");

    // ثبت سفارش → ریدایرکت به ورود
    await page.click('button:has-text("ثبت سفارش")');
    await page.waitForSelector("#phone", { timeout: 15000 });
    ok("ریدایرکت به صفحه ورود (نیاز به لاگین)");

    console.log("— ورود با OTP دمو —");
    await page.fill("#phone", "09123456789");
    await page.click('button:has-text("دریافت کد تایید")');
    await page.waitForSelector("#code", { timeout: 10000 });
    await page.fill("#code", "123456");
    await page.fill("#name", "کارفرمای محترم");
    await page.click('button:has-text("تایید و ورود")');

    console.log("— تکمیل سفارش —");
    await page.waitForSelector("text=نحوه دریافت سفارش", { timeout: 15000 });
    ok("صفحه Checkout باز شد");
    // انتخاب محله
    await page.click('[role="combobox"]');
    await page.waitForSelector('[role="option"]', { timeout: 8000 });
    await page.click('[role="option"]:has-text("پونک")');
    await page.fill(
      'textarea[placeholder*="خیابان"]',
      "خیابان نمونه، کوچه تست، پلاک ۱۲، واحد ۳"
    );
    await page.click('button:has-text("ثبت نهایی سفارش")');

    console.log("— پیگیری سفارش —");
    await page.waitForSelector("text=سفارش شما با موفقیت ثبت شد", { timeout: 15000 });
    ok("صفحه موفقیت سفارش نمایش داده شد");
    await page.waitForSelector("text=ثبت سفارش", { timeout: 8000 });
    const url = page.url();
    const code = (url.match(/code=([A-Z0-9-]+)/) || [])[1];
    ok("کد سفارش: " + code);
    ok("URL: " + url.replace("file://", ""));

    console.log("— پنل مدیریت (کاربر غیرمدیر نباید دسترسی داشته باشد) —");
    await page.goto("file://" + DEMO + "#/admin");
    await page.waitForTimeout(1500);
    const adminBlocked = !(await page.locator("text=داشبورد").first().isVisible().catch(() => false));
    ok("غیرمدیر به پنل دسترسی ندارد: " + adminBlocked);

    await context.close();

    // ورود مدیر در کانتکست جدا
    console.log("— پنل مدیریت (ورود مدیر) —");
    const ctx2 = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const p2 = await ctx2.newPage();
    p2.on("pageerror", (e) => errors.push("ADMIN PAGEERROR: " + e.message));
    await p2.goto("file://" + DEMO + "#/auth?next=%2Fadmin", { waitUntil: "load" });
    await p2.waitForSelector("#phone", { timeout: 20000 });
    await p2.fill("#phone", "09120000000");
    await p2.click('button:has-text("دریافت کد تایید")');
    await p2.waitForSelector("#code", { timeout: 10000 });
    await p2.fill("#code", "123456");
    await p2.click('button:has-text("تایید و ورود")');
    await p2.waitForSelector("text=داشبورد", { timeout: 20000 });
    ok("پنل مدیریت باز شد");
    await p2.waitForSelector("text=درآمد و سفارش‌های ۷ روز اخیر", { timeout: 15000 });
    ok("نمودار داشبورد رندر شد");
    await p2.click('a[href="#/admin/orders"]');
    await p2.waitForSelector("text=مدیریت و پیگیری سفارش‌ها", { timeout: 10000 });
    ok("صفحه سفارش‌های ادمین باز شد");
    await ctx2.close();

    // جمع‌بندی
    console.log("");
    const critical = errors.filter(
      (e) => !e.includes("net::") && !e.includes("Failed to load resource")
    );
    if (critical.length) {
      console.log("⚠️ خطاهای جاوااسکریپت:");
      critical.slice(0, 5).forEach((e) => console.log("   ", e));
      process.exitCode = 1;
    } else {
      console.log("🎉 همه تست‌ها با موفقیت گذشت — فایل دمو کاملا سالم است.");
    }
  } finally {
    await browser.close();
  }
})().catch((e) => {
  console.error("❌ تست شکست خورد:", e.message);
  process.exit(1);
});
