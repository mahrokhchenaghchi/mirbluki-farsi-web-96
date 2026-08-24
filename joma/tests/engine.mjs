import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import { fileURLToPath } from "node:url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");

export function loadOfficialLibrary() {
  return JSON.parse(fs.readFileSync(path.join(root, "database/library_official.json"), "utf8"));
}

export function faNum(v) {
  return String(v).replace(/[0-9]/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[d]);
}

export function jalaliPad(n) {
  return String(n).padStart(2, "0");
}

function div(a, b) {
  return Math.trunc(a / b);
}

export function gregorianToJalali(gy, gm, gd) {
  const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
  const gy2 = gm > 2 ? gy + 1 : gy;
  let days = 355666 + 365 * gy + div(gy2 + 3, 4) - div(gy2 + 99, 100) + div(gy2 + 399, 400) + gd + g_d_m[gm - 1];
  let jy = -1595 + 33 * div(days, 12053);
  days %= 12053;
  jy += 4 * div(days, 1461);
  days %= 1461;
  if (days > 365) {
    jy += div(days - 1, 365);
    days = (days - 1) % 365;
  }
  let jm;
  let jd;
  if (days < 186) {
    jm = 1 + div(days, 31);
    jd = 1 + (days % 31);
  } else {
    jm = 7 + div(days - 186, 30);
    jd = 1 + ((days - 186) % 30);
  }
  return [jy, jm, jd];
}

export function jalaliToGregorian(jy, jm, jd) {
  jy += 1595;
  let days = -355668 + 365 * jy + div(jy, 33) * 8 + div((jy % 33) + 3, 4) + jd + (jm < 7 ? (jm - 1) * 31 : (jm - 7) * 30 + 186);
  let gy = 400 * div(days, 146097);
  days %= 146097;
  if (days > 36524) {
    gy += 100 * div(--days, 36524);
    days %= 36524;
    if (days >= 365) days++;
  }
  gy += 4 * div(days, 1461);
  days %= 1461;
  if (days > 365) {
    gy += div(days - 1, 365);
    days = (days - 1) % 365;
  }
  let gd = days + 1;
  const sal_a = [0, 31, (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  let gm = 0;
  while (gm < 13 && gd > sal_a[gm]) {
    gd -= sal_a[gm];
    gm++;
  }
  return [gy, gm, gd];
}

export function jalaliIsLeap(jy) {
  const breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
  let leapJ = -14;
  let jp = breaks[0];
  let jump = 0;
  for (let i = 1; i < breaks.length; i++) {
    const jm = breaks[i];
    jump = jm - jp;
    if (jy < jm) break;
    leapJ = leapJ + div(jump, 33) * 8 + div(jump % 33, 4);
    jp = jm;
  }
  let n = jy - jp;
  if (jump - n < 6) n = n - jump + div(jump + 4, 33) * 33;
  let leap = (((n + 1) % 33) - 1) % 4;
  if (leap === -1) leap = 4;
  return leap === 0;
}

export function jalaliMonthLength(jy, jm) {
  if (jm <= 6) return 31;
  if (jm <= 11) return 30;
  return jalaliIsLeap(jy) ? 30 : 29;
}

export function jalaliPeriodBounds(periodKey) {
  const [y, m] = periodKey.split("-").map(Number);
  const len = jalaliMonthLength(y, m);
  return {
    year: y,
    month: m,
    start: `${y}-${jalaliPad(m)}-01`,
    end: `${y}-${jalaliPad(m)}-${jalaliPad(len)}`,
    days: len,
  };
}

export function jalaliInPeriod(date, periodKey) {
  const b = jalaliPeriodBounds(periodKey);
  return date >= b.start && date <= b.end;
}

export function jalaliIsValid(date) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return false;
  const [y, m, d] = date.split("-").map(Number);
  if (m < 1 || m > 12) return false;
  return d >= 1 && d <= jalaliMonthLength(y, m);
}

export function jalaliWeekStart(date) {
  const [jy, jm, jd] = date.split("-").map(Number);
  const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
  const ts = Date.UTC(gy, gm - 1, gd);
  const w = new Date(ts).getUTCDay();
  const fromSat = (w + 1) % 7;
  const sat = new Date(ts - fromSat * 86400000);
  const [y, m, d] = gregorianToJalali(sat.getUTCFullYear(), sat.getUTCMonth() + 1, sat.getUTCDate());
  return `${y}-${jalaliPad(m)}-${jalaliPad(d)}`;
}

export function jalaliSameWeek(a, b) {
  return jalaliWeekStart(a) === jalaliWeekStart(b);
}

export const JOBS = ["دانش‌آموز", "دانشجو", "کارمند", "مدیر", "کارآفرین", "پزشک", "روانشناس", "مهندس", "معلم", "وکیل", "حسابدار", "فروشنده", "فریلنسر", "خانه‌دار", "بازنشسته", "پژوهشگر", "مشاغل آزاد", "سایر"];

export function isValidUsername(v) {
  return /^[a-zA-Z][a-zA-Z0-9._]{2,19}$/.test(String(v).trim());
}
export function isValidEmail(v) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v).trim());
}
export function isValidIranMobile(v) {
  return /^09\d{9}$/.test(String(v).trim());
}

export function validateRegistration(inObj, takenUser, takenEmail) {
  if (!inObj.first_name?.trim() || !inObj.last_name?.trim()) return "نام و نام خانوادگی را وارد کنید.";
  if (!isValidUsername(inObj.username)) return "نام کاربری باید با حرف انگلیسی شروع شود و ۳ تا ۲۰ نویسه باشد.";
  if (takenUser) return "این نام کاربری قبلاً استفاده شده است.";
  if (!isValidEmail(inObj.email)) return "ایمیل معتبر نیست.";
  if (takenEmail) return "این ایمیل قبلاً ثبت شده است.";
  if (!isValidIranMobile(inObj.phone)) return "شماره موبایل باید مانند 09123456789 باشد.";
  if (!JOBS.includes(inObj.job)) return "شغل را از فهرست انتخاب کنید.";
  if (String(inObj.password).length < 6) return "رمز عبور باید حداقل ۶ نویسه باشد.";
  if (inObj.password !== inObj.confirm) return "رمز عبور و تکرار آن یکسان نیستند.";
  if (!inObj.accept) return "پذیرش قوانین برای ساخت حساب لازم است.";
  return "";
}

export function validatePerformanceValue(type, value) {
  if (value === "" || Number.isNaN(Number(value))) return "مقدار معتبر نیست.";
  value = Number(value);
  if (type === "RATING" && (value < 1 || value > 5)) return "امتیاز باید بین ۱ و ۵ باشد.";
  if (type === "BOOLEAN" && value !== 0 && value !== 1) return "وضعیت انجام فقط می‌تواند انجام‌شده یا نشده باشد.";
  if (value < 0) return "مقدار نمی‌تواند منفی باشد.";
  return "";
}

export function canTransition(from, to) {
  const ok = {
    DRAFT: ["PLANNING", "ARCHIVED"],
    PLANNING: ["RUNNING", "DRAFT", "ARCHIVED"],
    RUNNING: ["ARCHIVED"],
    ARCHIVED: [],
  };
  return (ok[from] || []).includes(to);
}

export function canRegisterPerformance(plan, pa, date, value, existing) {
  const err = validatePerformanceValue(pa.data_type, value);
  if (err) return err;
  if (!jalaliIsValid(date)) return "تاریخ عملکرد معتبر نیست.";
  if (plan.status !== "RUNNING") return "فقط در دوره در حال اجرا می‌توان عملکرد ثبت کرد.";
  if (!jalaliInPeriod(date, plan.period_key)) return "تاریخ داخل این دوره نیست.";
  if (pa.frequency === "DAILY") {
    if (existing.some((e) => Number(e.plan_activity_id) === Number(pa.id) && e.performance_date === date)) {
      return "برای این فعالیت روزانه، در این تاریخ قبلاً عملکرد ثبت شده است.";
    }
  }
  return "";
}

export function targetOf(row) {
  if (row.frequency === "WEEKLY") return Number(row.weekly_target);
  if (row.frequency === "MONTHLY") return Number(row.monthly_target);
  return Number(row.daily_target);
}

export function createStore() {
  return {
    users: [],
    preferences: [],
    activities: [],
    periods: [],
    plans: [],
    plan_activities: [],
    events: [],
    moods: [],
    seq: 1,
  };
}

export function nextId(store) {
  const id = store.seq;
  store.seq += 1;
  return id;
}

export function hashPassword(password) {
  return crypto.createHash("sha256").update(String(password)).digest("hex");
}

export function createUser(store, input) {
  const id = nextId(store);
  store.users.push({
    id,
    first_name: input.first_name,
    last_name: input.last_name,
    username: input.username,
    email: input.email,
    phone: input.phone,
    job: input.job,
    password_hash: hashPassword(input.password),
    role_key: "member",
    access_level: 1,
    created_at: new Date().toISOString(),
  });
  store.preferences.push({ user_id: id, compact_cards: 0, notifications_enabled: 0 });
  for (const row of loadOfficialLibrary()) {
    store.activities.push({ ...row, id: nextId(store), user_id: id, is_seed: 1 });
  }
  return store.users.find((u) => u.id === id);
}

export function ensurePeriod(store, userId, periodKey) {
  const b = jalaliPeriodBounds(periodKey);
  let period = store.periods.find((p) => p.user_id === userId && p.period_key === periodKey);
  if (!period) {
    period = { id: nextId(store), user_id: userId, period_key: periodKey, ...b, start_date: b.start, end_date: b.end };
    store.periods.push(period);
  }
  let plan = store.plans.find((p) => p.user_id === userId && p.period_id === period.id);
  if (!plan) {
    plan = { id: nextId(store), user_id: userId, period_id: period.id, period_key: periodKey, status: "DRAFT" };
    store.plans.push(plan);
  }
  return { period, plan };
}

export function addPlanActivity(store, userId, plan, activity, over = {}) {
  if (!["DRAFT", "PLANNING"].includes(plan.status)) return "برنامه این دوره قفل است.";
  if (store.plan_activities.some((a) => a.plan_id === plan.id && a.activity_id === activity.id)) {
    return "این فعالیت قبلاً اضافه شده است.";
  }
  const frequency = over.frequency || activity.frequency;
  const tmp = { ...activity, frequency };
  const target_value = over.target_value !== undefined && over.target_value !== "" ? Number(over.target_value) : targetOf(tmp);
  const weight = over.weight !== undefined && over.weight !== "" ? Number(over.weight) : Number(activity.weight);
  if (target_value <= 0) return "هدف باید عددی بزرگ‌تر از صفر باشد.";
  store.plan_activities.push({
    id: nextId(store),
    user_id: userId,
    plan_id: plan.id,
    period_key: plan.period_key,
    activity_id: activity.id,
    activity_code: activity.code,
    name: activity.name,
    category: activity.category,
    frequency,
    data_type: activity.data_type,
    unit: activity.unit,
    daily_target: activity.daily_target,
    weekly_target: activity.weekly_target,
    monthly_target: activity.monthly_target,
    target_value,
    weight,
    sticker: activity.sticker,
    color: activity.color,
    sort_order: store.plan_activities.filter((a) => a.plan_id === plan.id).length,
  });
  return "";
}

export function transitionPlan(store, plan, next) {
  if (!canTransition(plan.status, next)) return "این تغییر وضعیت مجاز نیست.";
  const count = store.plan_activities.filter((a) => a.plan_id === plan.id).length;
  if ((next === "PLANNING" || next === "RUNNING") && count === 0) {
    return "برای نهایی‌سازی یا شروع اجرا حداقل یک فعالیت لازم است.";
  }
  plan.status = next;
  return "";
}

export function registerPerformance(store, userId, plan, pa, date, value) {
  const existing = store.events.filter((e) => e.plan_id === plan.id);
  const err = canRegisterPerformance(plan, pa, date, value, existing);
  if (err) return err;
  store.events.push({
    id: nextId(store),
    user_id: userId,
    plan_id: plan.id,
    plan_activity_id: pa.id,
    period_key: plan.period_key,
    frequency: pa.frequency,
    data_type: pa.data_type,
    performance_date: date,
    actual_value: Number(value),
  });
  return "";
}

export function saveMood(store, userId, date, scores, note = "") {
  const ex = store.moods.find((m) => m.user_id === userId && m.jalali_date === date);
  if (ex) Object.assign(ex, scores, { note });
  else store.moods.push({ id: nextId(store), user_id: userId, jalali_date: date, note, ...scores });
}

export function buildReport(store, userId, plan) {
  const acts = store.plan_activities.filter((a) => a.plan_id === plan.id);
  const evs = store.events.filter((e) => e.plan_id === plan.id);
  const b = jalaliPeriodBounds(plan.period_key);
  const moods = store.moods.filter((m) => m.user_id === userId && m.jalali_date >= b.start && m.jalali_date <= b.end);
  const activities = acts.map((a) => {
    const list = evs.filter((e) => e.plan_activity_id === a.id);
    return {
      ...a,
      actual: list.reduce((s, e) => s + Number(e.actual_value), 0),
      event_count: list.length,
      events: list,
      achievement: "UNSPECIFIED",
    };
  });
  return {
    period_key: plan.period_key,
    activities,
    events: evs,
    moods,
    source_event_count: evs.length,
    achievement: "UNSPECIFIED",
    overall_success: "UNSPECIFIED",
    weight_sum: acts.reduce((s, a) => s + Number(a.weight), 0),
  };
}
