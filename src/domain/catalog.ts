import type { ActivityCategory, ActivityColor, DataType, Frequency } from "./types";

export const CATEGORIES: ActivityCategory[] = [
  "سلامت جسم",
  "خواب و استراحت",
  "سلامت روان",
  "تمرکز و ذهن",
  "یادگیری",
  "رشد فردی",
  "روابط",
  "خانواده",
  "ذهن‌آگاهی",
  "مراقبت از خود",
  "بهره‌وری",
  "سبک زندگی",
];

export const DATA_TYPES: DataType[] = ["DURATION", "NUMERIC", "BOOLEAN", "RATING"];

export const FREQUENCIES: Frequency[] = ["DAILY", "WEEKLY", "MONTHLY"];

export const FREQUENCY_LABEL: Record<Frequency, string> = {
  DAILY: "روزانه",
  WEEKLY: "هفتگی",
  MONTHLY: "ماهانه",
};

export const DATA_TYPE_LABEL: Record<DataType, string> = {
  DURATION: "مدت‌زمان",
  NUMERIC: "عددی",
  BOOLEAN: "انجام / عدم انجام",
  RATING: "امتیاز ۱ تا ۵",
};

export const STICKERS = [
  "🏃", "💧", "🚶", "🍎", "🌙", "⏰", "🧘", "🌬️", "📓", "🎯",
  "📵", "📝", "📚", "🛠️", "⭐", "💑", "🙏", "☕", "📞", "🏠",
  "✨", "🌸", "🛁", "🎨", "✅", "🔥", "📅", "📖", "📗", "📘",
  "🧘‍♀️", "🪞", "🧭", "🥗", "🍬", "🤸", "💭", "🌿", "💬", "🎧",
  "🖥️", "🧴", "🌇", "🔍", "🌈", "💪", "🧠", "💛", "🌤️", "🎵",
] as const;

export type Sticker = (typeof STICKERS)[number];

export const COLORS: ActivityColor[] = [
  { id: "mint", label: "نعنایی", value: "#B8E0C8" },
  { id: "sky", label: "آسمانی", value: "#B8D4F0" },
  { id: "lilac", label: "یاسی", value: "#E4C4E8" },
  { id: "lavender", label: "اسطوخودوس", value: "#C9B8E8" },
  { id: "peach", label: "هلویی", value: "#F8D0B0" },
  { id: "blush", label: "صورتی", value: "#F5C6D6" },
  { id: "rose", label: "گل‌سرخی", value: "#F0C8DC" },
  { id: "sun", label: "طلایی", value: "#F5D08A" },
  { id: "lemon", label: "لیمویی", value: "#F8D9A0" },
  { id: "aqua", label: "فیروزه‌ای", value: "#A8E0DC" },
  { id: "sage", label: "سبز ملایم", value: "#C5E6C5" },
  { id: "sea", label: "دریایی", value: "#B5D8D0" },
];

export const CATEGORY_COLOR: Record<ActivityCategory, string> = {
  "سلامت جسم": "#B8E0C8",
  "خواب و استراحت": "#C9B8E8",
  "سلامت روان": "#E4C4E8",
  "تمرکز و ذهن": "#B8D4F0",
  "یادگیری": "#F8D9A0",
  "رشد فردی": "#F5D08A",
  "روابط": "#F5C6D6",
  "خانواده": "#F8D0B0",
  "ذهن‌آگاهی": "#C5E6C5",
  "مراقبت از خود": "#F0C8DC",
  "بهره‌وری": "#A8E0DC",
  "سبک زندگی": "#B5D8D0",
};

export const MOOD_METRICS = [
  {
    key: "energy",
    title: "انرژی",
    stickers: ["😴", "😐", "🙂", "😄", "⚡"],
  },
  {
    key: "general",
    title: "حال عمومی",
    stickers: ["😞", "😐", "🙂", "😊", "🤩"],
  },
  {
    key: "focus",
    title: "تمرکز",
    stickers: ["🌫️", "😐", "🙂", "🎯", "🧠"],
  },
  {
    key: "sleep",
    title: "کیفیت خواب",
    stickers: ["😫", "😐", "🙂", "😴", "✨"],
  },
  {
    key: "stress",
    title: "سطح استرس",
    stickers: ["😌", "🙂", "😐", "😟", "😣"],
  },
] as const;

export type MoodMetricKey = (typeof MOOD_METRICS)[number]["key"];

export function targetFieldFor(frequency: Frequency): "dailyTarget" | "weeklyTarget" | "monthlyTarget" {
  if (frequency === "DAILY") return "dailyTarget";
  if (frequency === "WEEKLY") return "weeklyTarget";
  return "monthlyTarget";
}

export function targetOf(activity: {
  frequency: Frequency;
  dailyTarget: number;
  weeklyTarget: number;
  monthlyTarget: number;
}): number {
  if (activity.frequency === "DAILY") return activity.dailyTarget;
  if (activity.frequency === "WEEKLY") return activity.weeklyTarget;
  return activity.monthlyTarget;
}

export const JOBS = [
  "دانش‌آموز",
  "دانشجو",
  "کارمند",
  "مدیر",
  "کارآفرین",
  "پزشک",
  "روانشناس",
  "مهندس",
  "معلم",
  "وکیل",
  "حسابدار",
  "فروشنده",
  "فریلنسر",
  "خانه‌دار",
  "بازنشسته",
  "پژوهشگر",
  "مشاغل آزاد",
  "سایر",
] as const;

export type JobTitle = (typeof JOBS)[number];

export const ACTIVITY_STATUSES = ["ACTIVE", "INACTIVE"] as const;

export function formatValue(dataType: DataType, value: number): string {
  if (dataType === "BOOLEAN") return value >= 1 ? "انجام شد" : "انجام نشد";
  if (dataType === "DURATION") return `${value} دقیقه`;
  if (dataType === "RATING") return `امتیاز ${value}`;
  return String(value);
}
