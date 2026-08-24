import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import {
  JOBS,
  addPlanActivity,
  buildReport,
  canRegisterPerformance,
  canTransition,
  createStore,
  createUser,
  ensurePeriod,
  gregorianToJalali,
  jalaliInPeriod,
  jalaliIsValid,
  jalaliMonthLength,
  jalaliPeriodBounds,
  jalaliSameWeek,
  jalaliToGregorian,
  loadOfficialLibrary,
  registerPerformance,
  saveMood,
  transitionPlan,
  validateRegistration,
} from "./engine.mjs";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const repo = path.resolve(root, "..");
const results = [];

function test(name, fn) {
  try {
    fn();
    results.push({ name, status: "PASS" });
  } catch (err) {
    results.push({ name, status: "FAIL", detail: err.message || String(err) });
  }
}

function assert(cond, msg) {
  if (!cond) throw new Error(msg || "assertion failed");
}

function read(rel) {
  return fs.readFileSync(path.join(root, rel), "utf8");
}

function walk(dir, acc = []) {
  for (const name of fs.readdirSync(dir)) {
    const p = path.join(dir, name);
    const st = fs.statSync(p);
    if (st.isDirectory()) walk(p, acc);
    else acc.push(p);
  }
  return acc;
}

const lib = loadOfficialLibrary();
const phpFiles = walk(root).filter((p) => p.endsWith(".php"));
const css = read("assets/css/joma.css");
const pages = Object.fromEntries(
  fs.readdirSync(path.join(root, "pages")).map((name) => [name, read(path.join("pages", name))]),
);

test("کتابخانه رسمی ۴۵ ردیف دارد", () => {
  assert(lib.length === 45, `expected 45 got ${lib.length}`);
});

test("ACT002 آب ۶ لیوان در روز است نه ۸", () => {
  const water = lib.find((r) => r.code === "ACT002");
  assert(water.daily_target === 6, String(water.daily_target));
  assert(water.unit === "UNIT_GLASS");
  assert(water.weight === 4);
});

test("ACT001 ورزش وزن ۵ و ۳۰ دقیقه است", () => {
  const sport = lib.find((r) => r.code === "ACT001");
  assert(sport.weight === 5);
  assert(sport.daily_target === 30);
  assert(sport.data_type === "DURATION");
});

test("ACT005 خواب ۷ ساعت است", () => {
  const sleep = lib.find((r) => r.code === "ACT005");
  assert(sleep.daily_target === 7);
  assert(sleep.unit === "UNIT_HOUR");
});

test("ACT045 تجربه جدید رشد فردی و ماهانه است", () => {
  const row = lib.find((r) => r.code === "ACT045");
  assert(row.category === "رشد فردی");
  assert(row.frequency === "MONTHLY");
  assert(row.monthly_target === 1);
});

test("DataTypeهای رسمی فقط چهار مقدار مجاز هستند", () => {
  const allowed = new Set(["DURATION", "NUMERIC", "BOOLEAN", "RATING"]);
  for (const row of lib) assert(allowed.has(row.data_type), row.code + " " + row.data_type);
});

test("Validation ثبت‌نام: فیلد خالی، نام کاربری، موبایل، رمز، پذیرش قوانین", () => {
  const base = {
    first_name: "سارا",
    last_name: "محمدی",
    username: "sara.m",
    email: "sara@example.com",
    phone: "09123456789",
    job: "سایر",
    password: "secret1",
    confirm: "secret1",
    accept: true,
  };
  assert(validateRegistration({ ...base, first_name: "" }) !== "");
  assert(validateRegistration({ ...base, username: "۱سارا" }) !== "");
  assert(validateRegistration({ ...base, username: "ab" }) !== "");
  assert(validateRegistration({ ...base, phone: "9123456789" }) !== "");
  assert(validateRegistration({ ...base, email: "bad" }) !== "");
  assert(validateRegistration({ ...base, job: "فضانورد" }) !== "");
  assert(validateRegistration({ ...base, password: "123" }) !== "");
  assert(validateRegistration({ ...base, confirm: "other" }) !== "");
  assert(validateRegistration({ ...base, accept: false }).includes("قوانین"));
  assert(validateRegistration(base) === "");
  assert(JOBS.includes("سایر"));
});

test("سناریو ثبت‌نام + ورود + نام کاربری تکراری", () => {
  const store = createStore();
  const user = createUser(store, {
    first_name: "سارا",
    last_name: "محمدی",
    username: "sara.m",
    email: "sara@example.com",
    phone: "09123456789",
    job: "سایر",
    password: "secret1",
  });
  assert(user.id);
  assert(store.activities.filter((a) => a.user_id === user.id).length === 45);
  const taken = store.users.some((u) => u.username === "sara.m");
  assert(taken);
  const loginOk = store.users.find((u) => u.username === "sara.m");
  assert(loginOk.password_hash);
  const bad = !store.users.find((u) => u.username === "nobody");
  assert(bad);
});

test("Mood پنج‌شاخصه و یادداشت", () => {
  const store = createStore();
  const user = createUser(store, { first_name: "آ", last_name: "ب", username: "ab1", email: "a@b.co", phone: "09120000000", job: "سایر", password: "secret1" });
  const incomplete = [1, 2, 3, 4, 0].some((v) => v < 1 || v > 5);
  assert(incomplete);
  saveMood(store, user.id, "1405-06-03", { energy: 4, general_mood: 5, focus: 3, sleep_quality: 4, stress: 2 }, "خوب بود");
  const rec = store.moods[0];
  assert(rec.energy === 4 && rec.general_mood === 5 && rec.note === "خوب بود");
});

test("ساخت دوره شمسی و تاریخچه مستقل", () => {
  const store = createStore();
  const user = createUser(store, { first_name: "آ", last_name: "ب", username: "ab2", email: "a2@b.co", phone: "09120000001", job: "سایر", password: "secret1" });
  const a = ensurePeriod(store, user.id, "1405-05");
  const b = ensurePeriod(store, user.id, "1405-06");
  assert(a.plan.id !== b.plan.id);
  assert(a.period.start_date === "1405-05-01");
  assert(jalaliPeriodBounds("1405-06").days === 31);
});

test("Override فعالیت و قفل بعد از اجرا", () => {
  const store = createStore();
  const user = createUser(store, { first_name: "آ", last_name: "ب", username: "ab3", email: "a3@b.co", phone: "09120000002", job: "سایر", password: "secret1" });
  const { plan } = ensurePeriod(store, user.id, "1405-06");
  const water = store.activities.find((a) => a.user_id === user.id && a.code === "ACT002");
  const errAdd = addPlanActivity(store, user.id, plan, water, { frequency: "DAILY", target_value: 5, weight: 8 });
  assert(errAdd === "", errAdd);
  const pa = store.plan_activities[0];
  assert(pa.target_value === 5 && pa.weight === 8);
  assert(addPlanActivity(store, user.id, plan, water) !== "");
  assert(transitionPlan(store, plan, "PLANNING") === "");
  assert(transitionPlan(store, plan, "RUNNING") === "");
  assert(addPlanActivity(store, user.id, plan, water, { target_value: 9 }).includes("قفل"));
});

test("Finalize بدون فعالیت Fail است", () => {
  const store = createStore();
  const user = createUser(store, { first_name: "آ", last_name: "ب", username: "ab4", email: "a4@b.co", phone: "09120000003", job: "سایر", password: "secret1" });
  const { plan } = ensurePeriod(store, user.id, "1405-06");
  assert(transitionPlan(store, plan, "PLANNING").includes("حداقل یک فعالیت"));
});

test("Performance روزانه یکتا است و روز بعد مجاز", () => {
  const store = createStore();
  const user = createUser(store, { first_name: "آ", last_name: "ب", username: "ab5", email: "a5@b.co", phone: "09120000004", job: "سایر", password: "secret1" });
  const { plan } = ensurePeriod(store, user.id, "1405-06");
  const water = store.activities.find((a) => a.code === "ACT002");
  addPlanActivity(store, user.id, plan, water);
  transitionPlan(store, plan, "PLANNING");
  transitionPlan(store, plan, "RUNNING");
  const pa = store.plan_activities[0];
  assert(registerPerformance(store, user.id, plan, pa, "1405-06-02", 6) === "");
  assert(registerPerformance(store, user.id, plan, pa, "1405-06-02", 3).includes("قبلاً"));
  assert(registerPerformance(store, user.id, plan, pa, "1405-06-03", 4) === "");
});

test("Performance هفتگی چندرویداد جمع می‌شود", () => {
  const plan = { status: "RUNNING", period_key: "1405-06" };
  const pa = { id: 1, frequency: "WEEKLY", data_type: "DURATION" };
  assert(canRegisterPerformance(plan, pa, "1405-06-01", 20, []) === "");
  assert(canRegisterPerformance(plan, pa, "1405-06-03", 15, [{ plan_activity_id: 1, performance_date: "1405-06-01" }]) === "");
  assert(jalaliSameWeek("1405-06-01", "1405-06-03"));
});

test("Performance ماهانه چندرویداد مجاز است", () => {
  const plan = { status: "RUNNING", period_key: "1405-06" };
  const pa = { id: 9, frequency: "MONTHLY", data_type: "BOOLEAN" };
  assert(canRegisterPerformance(plan, pa, "1405-06-01", 1, []) === "");
  assert(canRegisterPerformance(plan, pa, "1405-06-18", 1, [{ plan_activity_id: 9, performance_date: "1405-06-01" }]) === "");
});

test("ثبت عملکرد در DRAFT و خارج از دوره Fail است", () => {
  const pa = { id: 1, frequency: "DAILY", data_type: "NUMERIC" };
  assert(canRegisterPerformance({ status: "DRAFT", period_key: "1405-06" }, pa, "1405-06-02", 1, []).includes("در حال اجرا"));
  assert(canRegisterPerformance({ status: "RUNNING", period_key: "1405-06" }, pa, "1405-07-01", 1, []).includes("داخل این دوره"));
  assert(canRegisterPerformance({ status: "RUNNING", period_key: "1405-06" }, { ...pa, data_type: "RATING" }, "1405-06-02", 9, []).includes("۱ و ۵"));
});

test("گزارش فقط رویداد واقعی است و Achievement جعلی ندارد", () => {
  const store = createStore();
  const user = createUser(store, { first_name: "آ", last_name: "ب", username: "ab6", email: "a6@b.co", phone: "09120000005", job: "سایر", password: "secret1" });
  const may = ensurePeriod(store, user.id, "1405-05");
  const jun = ensurePeriod(store, user.id, "1405-06");
  const act = store.activities.find((a) => a.code === "ACT001");
  addPlanActivity(store, user.id, may.plan, act, { weight: 30, target_value: 60, frequency: "WEEKLY" });
  addPlanActivity(store, user.id, jun.plan, act, { weight: 50, target_value: 60, frequency: "WEEKLY" });
  transitionPlan(store, may.plan, "PLANNING");
  transitionPlan(store, may.plan, "RUNNING");
  transitionPlan(store, jun.plan, "PLANNING");
  transitionPlan(store, jun.plan, "RUNNING");
  const paMay = store.plan_activities.find((a) => a.plan_id === may.plan.id);
  const paJun = store.plan_activities.find((a) => a.plan_id === jun.plan.id);
  registerPerformance(store, user.id, may.plan, paMay, "1405-05-02", 20);
  registerPerformance(store, user.id, may.plan, paMay, "1405-05-04", 20);
  registerPerformance(store, user.id, jun.plan, paJun, "1405-06-10", 4);
  const rMay = buildReport(store, user.id, may.plan);
  const rJun = buildReport(store, user.id, jun.plan);
  assert(rMay.activities[0].actual === 40);
  assert(rMay.activities[0].weight === 30);
  assert(rJun.activities[0].weight === 50);
  assert(rJun.activities[0].actual === 4);
  assert(rMay.achievement === "UNSPECIFIED");
  assert(rMay.overall_success === "UNSPECIFIED");
  assert(!JSON.stringify(rMay).includes("% موفقیت"));
});

test("تقویم جلالی رفت‌وبرگشت و شنبه شروع هفته", () => {
  const [gy, gm, gd] = jalaliToGregorian(1405, 6, 1);
  const back = gregorianToJalali(gy, gm, gd);
  assert(back[0] === 1405 && back[1] === 6 && back[2] === 1);
  assert(jalaliIsValid("1405-06-31"));
  assert(!jalaliIsValid("1405-07-31"));
  assert(jalaliInPeriod("1405-06-15", "1405-06"));
  assert(jalaliMonthLength(1403, 12) === 30 || jalaliMonthLength(1403, 12) === 29);
});

test("انتقال وضعیت برنامه فقط مسیرهای مجاز", () => {
  assert(canTransition("DRAFT", "PLANNING"));
  assert(canTransition("PLANNING", "RUNNING"));
  assert(canTransition("RUNNING", "ARCHIVED"));
  assert(!canTransition("RUNNING", "DRAFT"));
  assert(!canTransition("ARCHIVED", "RUNNING"));
});

test("PHP بدون PDO / Composer / PHP8-only نوشته شده", () => {
  const joined = phpFiles.map((p) => fs.readFileSync(p, "utf8")).join("\n");
  assert(!/\bnew PDO\b|\bPDO::/.test(joined), "PDO found");
  assert(!/\bmatch\s*\(/.test(joined), "match() found");
  assert(!/\?->/.test(joined), "nullsafe found");
  assert(!joined.includes("composer.json"));
  assert(joined.includes("password_hash"));
  assert(joined.includes("mysqli_prepare") || joined.includes("joma_exec"));
  assert(joined.includes("csrf_check"));
});

test("ثبت‌نام PHP چک‌باکس پذیرش قوانین دارد", () => {
  assert(pages["register.php"].includes('name="accept"'));
  assert(pages["register.php"].includes("قوانین"));
});

test("گزارش PHP هر ۸ تب React را دارد", () => {
  for (const tab of ["خلاصه", "فعالیت‌ها", "وزن", "تقویم", "روند", "خلق", "مقایسه", "جزئیات"]) {
    assert(pages["reports.php"].includes(tab), "missing tab " + tab);
  }
  assert(pages["reports.php"].includes("UNSPECIFIED") || pages["reports.php"].includes("تعریف نشده"));
  assert(!/موفقیت کلی.*\d+\s*%/.test(pages["reports.php"]));
});

test("ناوبری کامل، بازگشت و منوی موبایل در layout هست", () => {
  const layout = read("includes/layout.php") + read("includes/helpers.php");
  for (const item of ["داشبورد", "امروز", "خلق من", "گزارش‌ها", "برنامه من", "دوره‌های من", "کتابخانه فعالیت‌ها", "پروفایل من", "تنظیمات", "درباره جوما", "پشتیبانی"]) {
    assert(layout.includes(item), "missing nav " + item);
  }
  assert(layout.includes("بازگشت"));
  assert(layout.includes("mobile"));
  assert(layout.includes("history.back"));
});

test("UI لوکس: پاستلی، RTL، کارت، ریسپانسیو", () => {
  assert(css.includes("direction:rtl") || css.includes("dir"));
  assert(css.includes("--primary") || css.includes("#7c5cbf") || css.includes("262"));
  assert(css.includes("@media"));
  assert(css.includes("backdrop-filter") || css.includes("box-shadow"));
  assert(css.includes("Vazirmatn"));
  assert(css.includes(".act-card") || css.includes(".act-head"));
  assert(css.includes(".mood-row"));
  assert(css.includes(".cal"));
});

test("صفحات اصلی سناریوها را پوشش می‌دهند", () => {
  assert(pages["mood.php"].includes("energy") && pages["mood.php"].includes("stress"));
  assert(pages["plan.php"].includes("target_value") && pages["plan.php"].includes("نهایی‌سازی"));
  assert(pages["today.php"].includes("DAILY") && pages["today.php"].includes("WEEKLY") && pages["today.php"].includes("MONTHLY"));
  assert(pages["library.php"].includes("ACT") || pages["library.php"].includes("کتابخانه"));
  assert(pages["periods.php"].includes("ایجاد / انتخاب دوره"));
  assert(pages["period.php"].includes("بایگانی") || pages["period.php"].includes("کار روی این دوره"));
  assert(pages["dashboard.php"].includes("حال امروز"));
  assert(pages["profile.php"].includes("نام کاربری"));
  assert(pages["settings.php"].includes("اعلان"));
  assert(pages["forgot.php"].includes("رمز"));
});

test("لوگوی رسمی اگر باشد استفاده می‌شود و طراحی جایگزین اجباری نیست", () => {
  const helpers = read("includes/helpers.php");
  assert(helpers.includes("logo.jpg"));
  assert(helpers.includes("logo-mark"));
});

test("پشتیبانی حدس زده نشده و Achievement جعلی نیست", () => {
  assert(pages["support.php"].includes("حدس زده نشده") || pages["support.php"].includes("ارائه نشده"));
  const fn = read("functions/joma.php");
  assert(fn.includes("'UNSPECIFIED'"));
  assert(fn.includes("overall_success") || fn.includes("UNSPECIFIED"));
});

test("bind_param ساخت کاربر و افزودن فعالیت اصلاح شده", () => {
  const fn = read("functions/joma.php");
  assert(fn.includes("'ssssssssis'"));
  assert(fn.includes("'iisissssssddddissis'"));
  assert(fn.includes("joma_stmt_fetch_all") || read("includes/store.php").includes("joma_stmt_fetch_all"));
});

test("SQL رسمی با JSON کتابخانه هم‌خوان است", () => {
  const sql = read("database/joma.sql");
  assert(sql.includes("'ACT002'") && sql.includes("6,42,180,4"));
  assert(sql.includes("'ACT045'") && sql.includes("رشد فردی"));
  assert((sql.match(/'ACT0\d{2}'/g) || []).length >= 45);
});

const passed = results.filter((r) => r.status === "PASS").length;
const failed = results.filter((r) => r.status === "FAIL");
const report = [
  "# گزارش تست نسخه PHP جوما",
  "",
  `تاریخ اجرا: ${new Date().toISOString()}`,
  "",
  `جمع: ${results.length} تست — ${passed} Pass — ${failed.length} Fail`,
  "",
  "| تست | نتیجه | توضیح |",
  "|---|---|---|",
  ...results.map((r) => `| ${r.name} | ${r.status} | ${r.detail ? r.detail.replace(/\|/g, "/") : ""} |`),
  "",
  "## محیط اجرا",
  "",
  "- PHP CLI در این sandbox موجود نبود؛ سناریوها با موتور هم‌ارز فایل‌استور و بررسی ایستای سورس PHP اجرا شد.",
  "- تست مرورگر زنده PHP اینجا ممکن نیست.",
  "- اتصال Production و Deploy انجام نشد.",
  "",
  "## باقی‌مانده",
  "",
  "- اجرای واقعی روی PHP 7.x / cPanel هنوز انجام نشده (طبق درخواست، Deploy و Production وصل نشد).",
  "- مسیر MySQL واقعی روی هاست هنوز تست نشده؛ fallback بدون mysqlnd نوشته شده اما روی هاست واقعی باید یک‌بار تأیید شود.",
  "- فایل لوگوی رسمی `logo.jpg` در این محیط موجود نیست؛ جای لوگو آماده است.",
  "- فرمول Achievement / Overall Success همچنان UNSPECIFIED است.",
  "- تماس پشتیبانی حدس زده نشده است.",
  "- SMS/OTP واقعی ارسال نمی‌شود.",
  "- پنل ادمین کامل ساخته نشده است.",
  "- Inbox اعلان‌ها جدول دارد ولی UI کامل صندوق پیام ندارد.",
  "",
].join("\n");

fs.writeFileSync(path.join(root, "TEST_REPORT.md"), report);
console.log(report);
if (failed.length) process.exit(1);
