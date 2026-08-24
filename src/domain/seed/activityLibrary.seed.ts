import type { ActivityCategory, ActivityDefinition, DataType, Frequency } from "@/domain/types";
import { CATEGORY_COLOR } from "@/domain/catalog";

/**
 * Official names: ACT001–ACT045 from the JOMA specification.
 *
 * Frequency, DataType, Target and Weight were NOT provided as numbers in the
 * attached text spec and the Library PDF was not readable in this environment.
 * Values below are interim seed defaults so the product is usable and fully
 * editable in Activity Library. They are NOT a scoring formula.
 */
interface SeedRow {
  code: string;
  name: string;
  category: ActivityCategory;
  frequency: Frequency;
  dataType: DataType;
  dailyTarget: number;
  weeklyTarget: number;
  monthlyTarget: number;
  weight: number;
  sticker: string;
}

const ROWS: SeedRow[] = [
  { code: "ACT001", name: "ورزش", category: "سلامت جسم", frequency: "DAILY", dataType: "DURATION", dailyTarget: 30, weeklyTarget: 150, monthlyTarget: 600, weight: 1, sticker: "🏃" },
  { code: "ACT002", name: "نوشیدن آب", category: "سلامت جسم", frequency: "DAILY", dataType: "NUMERIC", dailyTarget: 8, weeklyTarget: 56, monthlyTarget: 240, weight: 1, sticker: "💧" },
  { code: "ACT003", name: "پیاده‌روی", category: "سلامت جسم", frequency: "DAILY", dataType: "DURATION", dailyTarget: 20, weeklyTarget: 120, monthlyTarget: 480, weight: 1, sticker: "🚶" },
  { code: "ACT004", name: "مصرف میوه", category: "سلامت جسم", frequency: "DAILY", dataType: "NUMERIC", dailyTarget: 2, weeklyTarget: 14, monthlyTarget: 60, weight: 1, sticker: "🍎" },
  { code: "ACT005", name: "خواب کافی", category: "خواب و استراحت", frequency: "DAILY", dataType: "DURATION", dailyTarget: 420, weeklyTarget: 2940, monthlyTarget: 12600, weight: 1, sticker: "🌙" },
  { code: "ACT006", name: "ساعت خواب منظم", category: "خواب و استراحت", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "⏰" },
  { code: "ACT007", name: "مدیتیشن", category: "سلامت روان", frequency: "DAILY", dataType: "DURATION", dailyTarget: 10, weeklyTarget: 70, monthlyTarget: 300, weight: 1, sticker: "🧘" },
  { code: "ACT008", name: "تمرین تنفس", category: "سلامت روان", frequency: "DAILY", dataType: "DURATION", dailyTarget: 5, weeklyTarget: 35, monthlyTarget: 150, weight: 1, sticker: "🌬️" },
  { code: "ACT009", name: "ثبت حال روزانه", category: "سلامت روان", frequency: "DAILY", dataType: "RATING", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "📓" },
  { code: "ACT010", name: "مطالعه بدون حواس‌پرتی", category: "تمرکز و ذهن", frequency: "DAILY", dataType: "DURATION", dailyTarget: 25, weeklyTarget: 150, monthlyTarget: 600, weight: 1, sticker: "🎯" },
  { code: "ACT011", name: "زمان بدون موبایل", category: "تمرکز و ذهن", frequency: "DAILY", dataType: "DURATION", dailyTarget: 30, weeklyTarget: 180, monthlyTarget: 720, weight: 1, sticker: "📵" },
  { code: "ACT012", name: "نوشتن برنامه روزانه", category: "تمرکز و ذهن", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "📝" },
  { code: "ACT013", name: "مطالعه", category: "یادگیری", frequency: "DAILY", dataType: "DURATION", dailyTarget: 30, weeklyTarget: 180, monthlyTarget: 720, weight: 1, sticker: "📚" },
  { code: "ACT014", name: "یادگیری مهارت جدید", category: "یادگیری", frequency: "WEEKLY", dataType: "DURATION", dailyTarget: 20, weeklyTarget: 60, monthlyTarget: 240, weight: 1, sticker: "🛠️" },
  { code: "ACT015", name: "نوشتن اهداف", category: "رشد فردی", frequency: "WEEKLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 4, weight: 1, sticker: "⭐" },
  { code: "ACT016", name: "گفت‌وگوی باکیفیت با همسر", category: "روابط", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "💑" },
  { code: "ACT017", name: "قدردانی از همسر", category: "روابط", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🙏" },
  { code: "ACT018", name: "قرار دونفره", category: "روابط", frequency: "WEEKLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 4, weight: 1, sticker: "☕" },
  { code: "ACT019", name: "تماس با خانواده", category: "خانواده", frequency: "WEEKLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 4, weight: 1, sticker: "📞" },
  { code: "ACT020", name: "دیدار با خانواده", category: "خانواده", frequency: "MONTHLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 1, weight: 1, sticker: "🏠" },
  { code: "ACT021", name: "نوشتن سه نکته مثبت", category: "ذهن‌آگاهی", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "✨" },
  { code: "ACT022", name: "تمرین شکرگزاری", category: "ذهن‌آگاهی", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🌸" },
  { code: "ACT023", name: "زمان شخصی", category: "مراقبت از خود", frequency: "DAILY", dataType: "DURATION", dailyTarget: 20, weeklyTarget: 120, monthlyTarget: 480, weight: 1, sticker: "🛁" },
  { code: "ACT024", name: "انجام یک فعالیت لذت‌بخش", category: "مراقبت از خود", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🎨" },
  { code: "ACT025", name: "اولویت‌بندی کارهای روز", category: "بهره‌وری", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "✅" },
  { code: "ACT026", name: "انجام مهم‌ترین کار روز", category: "بهره‌وری", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🔥" },
  { code: "ACT027", name: "مرور هفتگی", category: "بهره‌وری", frequency: "WEEKLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 4, weight: 1, sticker: "📅" },
  { code: "ACT028", name: "خواندن کتاب", category: "یادگیری", frequency: "DAILY", dataType: "DURATION", dailyTarget: 20, weeklyTarget: 120, monthlyTarget: 480, weight: 1, sticker: "📖" },
  { code: "ACT029", name: "تکمیل یک فصل کتاب", category: "یادگیری", frequency: "WEEKLY", dataType: "NUMERIC", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 4, weight: 1, sticker: "📗" },
  { code: "ACT030", name: "خواندن یک کتاب کامل", category: "یادگیری", frequency: "MONTHLY", dataType: "NUMERIC", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 1, weight: 1, sticker: "📘" },
  { code: "ACT031", name: "یک روز کامل بدون شبکه اجتماعی", category: "سبک زندگی", frequency: "WEEKLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 4, weight: 1, sticker: "📵" },
  { code: "ACT032", name: "مرور ماه و ارزیابی شخصی", category: "رشد فردی", frequency: "MONTHLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 1, weight: 1, sticker: "🪞" },
  { code: "ACT033", name: "تعیین اهداف ماه آینده", category: "رشد فردی", frequency: "MONTHLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 1, weight: 1, sticker: "🧭" },
  { code: "ACT034", name: "غذای سالم", category: "سلامت جسم", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🥗" },
  { code: "ACT035", name: "کاهش مصرف قند", category: "سلامت جسم", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🍬" },
  { code: "ACT036", name: "کشش بدن", category: "سلامت جسم", frequency: "DAILY", dataType: "DURATION", dailyTarget: 10, weeklyTarget: 50, monthlyTarget: 200, weight: 1, sticker: "🤸" },
  { code: "ACT037", name: "نوشتن افکار و احساسات", category: "سلامت روان", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "💭" },
  { code: "ACT038", name: "فاصله گرفتن آگاهانه هنگام تنش", category: "سلامت روان", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🌿" },
  { code: "ACT039", name: "گفت‌وگوی بدون موبایل", category: "روابط", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "💬" },
  { code: "ACT040", name: "یک کار در هر لحظه", category: "تمرکز و ذهن", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🎧" },
  { code: "ACT041", name: "خاموش‌کردن صفحه‌نمایش قبل از خواب", category: "خواب و استراحت", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🖥️" },
  { code: "ACT042", name: "مراقبت شخصی", category: "مراقبت از خود", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🧴" },
  { code: "ACT043", name: "جمع‌بندی پایان روز", category: "رشد فردی", frequency: "DAILY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 7, monthlyTarget: 30, weight: 1, sticker: "🌇" },
  { code: "ACT044", name: "بررسی یک رفتار و یادگیری از آن", category: "رشد فردی", frequency: "WEEKLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 4, weight: 1, sticker: "🔍" },
  { code: "ACT045", name: "انجام یک تجربه جدید", category: "سبک زندگی", frequency: "MONTHLY", dataType: "BOOLEAN", dailyTarget: 1, weeklyTarget: 1, monthlyTarget: 1, weight: 1, sticker: "🌈" },
];

export function buildSeedActivities(userId: string, now = new Date().toISOString()): ActivityDefinition[] {
  return ROWS.map((row) => ({
    id: `${userId}-${row.code.toLowerCase()}`,
    userId,
    code: row.code,
    name: row.name,
    category: row.category,
    frequency: row.frequency,
    dataType: row.dataType,
    dailyTarget: row.dailyTarget,
    weeklyTarget: row.weeklyTarget,
    monthlyTarget: row.monthlyTarget,
    weight: row.weight,
    sticker: row.sticker,
    color: CATEGORY_COLOR[row.category],
    status: "ACTIVE",
    isSeed: true,
    createdAt: now,
    updatedAt: now,
  }));
}

export const SEED_ACTIVITY_COUNT = ROWS.length;
