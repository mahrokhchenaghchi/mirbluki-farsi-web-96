import jalaali from "jalaali-js";
import type { JalaliDateParts, PeriodKey } from "@/domain/types";

const PERSIAN_MONTHS = [
  "فروردین",
  "اردیبهشت",
  "خرداد",
  "تیر",
  "مرداد",
  "شهریور",
  "مهر",
  "آبان",
  "آذر",
  "دی",
  "بهمن",
  "اسفند",
] as const;

const PERSIAN_WEEKDAYS = [
  "شنبه",
  "یکشنبه",
  "دوشنبه",
  "سه‌شنبه",
  "چهارشنبه",
  "پنجشنبه",
  "جمعه",
] as const;

function pad(value: number): string {
  return String(value).padStart(2, "0");
}

export function toJalaliDateString(parts: JalaliDateParts): string {
  return `${parts.year}-${pad(parts.month)}-${pad(parts.day)}`;
}

export function parseJalaliDateString(value: string): JalaliDateParts {
  const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
  if (!match) {
    throw new Error(`Invalid jalali date: ${value}`);
  }
  const year = Number(match[1]);
  const month = Number(match[2]);
  const day = Number(match[3]);
  if (!jalaali.isValidJalaaliDate(year, month, day)) {
    throw new Error(`Invalid jalali date: ${value}`);
  }
  return { year, month, day };
}

export function isValidJalaliDateString(value: string): boolean {
  try {
    parseJalaliDateString(value);
    return true;
  } catch {
    return false;
  }
}

export function gregorianToJalali(date: Date): JalaliDateParts {
  const converted = jalaali.toJalaali(date.getFullYear(), date.getMonth() + 1, date.getDate());
  return { year: converted.jy, month: converted.jm, day: converted.jd };
}

export function jalaliToGregorian(parts: JalaliDateParts): Date {
  const converted = jalaali.toGregorian(parts.year, parts.month, parts.day);
  return new Date(converted.gy, converted.gm - 1, converted.gd);
}

export function todayJalali(now: Date = new Date()): JalaliDateParts {
  return gregorianToJalali(now);
}

export function todayJalaliString(now: Date = new Date()): string {
  return toJalaliDateString(todayJalali(now));
}

export function periodKeyFromParts(parts: Pick<JalaliDateParts, "year" | "month">): PeriodKey {
  return `${parts.year}-${pad(parts.month)}`;
}

export function periodKeyFromDateString(value: string): PeriodKey {
  const parts = parseJalaliDateString(value);
  return periodKeyFromParts(parts);
}

export function parsePeriodKey(periodKey: PeriodKey): { year: number; month: number } {
  const match = /^(\d{4})-(\d{2})$/.exec(periodKey);
  if (!match) {
    throw new Error(`Invalid period key: ${periodKey}`);
  }
  const year = Number(match[1]);
  const month = Number(match[2]);
  if (month < 1 || month > 12) {
    throw new Error(`Invalid period key: ${periodKey}`);
  }
  return { year, month };
}

export function currentPeriodKey(now: Date = new Date()): PeriodKey {
  return periodKeyFromParts(todayJalali(now));
}

export function daysInJalaliMonth(year: number, month: number): number {
  return jalaali.jalaaliMonthLength(year, month);
}

export function periodBounds(periodKey: PeriodKey): {
  year: number;
  month: number;
  start: JalaliDateParts;
  end: JalaliDateParts;
  startDate: string;
  endDate: string;
} {
  const { year, month } = parsePeriodKey(periodKey);
  const start = { year, month, day: 1 };
  const end = { year, month, day: daysInJalaliMonth(year, month) };
  return {
    year,
    month,
    start,
    end,
    startDate: toJalaliDateString(start),
    endDate: toJalaliDateString(end),
  };
}

export function isDateInPeriod(date: string, periodKey: PeriodKey): boolean {
  if (!isValidJalaliDateString(date)) return false;
  const bounds = periodBounds(periodKey);
  return date >= bounds.startDate && date <= bounds.endDate;
}

/**
 * Iranian / Jalali week starts on Saturday.
 * This is calendar convention for JOMA, not a new Frequency.
 */
export function weekStartSaturday(date: string): string {
  const parts = parseJalaliDateString(date);
  const gregorian = jalaliToGregorian(parts);
  const jsDay = gregorian.getDay();
  const daysFromSaturday = (jsDay + 1) % 7;
  gregorian.setDate(gregorian.getDate() - daysFromSaturday);
  return toJalaliDateString(gregorianToJalali(gregorian));
}

export function weekEndFriday(date: string): string {
  const start = parseJalaliDateString(weekStartSaturday(date));
  const gregorian = jalaliToGregorian(start);
  gregorian.setDate(gregorian.getDate() + 6);
  return toJalaliDateString(gregorianToJalali(gregorian));
}

export function isSameJalaliDay(a: string, b: string): boolean {
  return a === b;
}

export function isDateInSameWeek(date: string, reference: string): boolean {
  return weekStartSaturday(date) === weekStartSaturday(reference);
}

export function iteratePeriodDays(periodKey: PeriodKey): string[] {
  const bounds = periodBounds(periodKey);
  const days: string[] = [];
  for (let day = 1; day <= bounds.end.day; day += 1) {
    days.push(toJalaliDateString({ year: bounds.year, month: bounds.month, day }));
  }
  return days;
}

export function persianMonthName(month: number): string {
  return PERSIAN_MONTHS[month - 1] ?? "";
}

export function formatPeriodLabel(periodKey: PeriodKey): string {
  const { year, month } = parsePeriodKey(periodKey);
  return `${persianMonthName(month)} ${toPersianDigits(String(year))}`;
}

export function formatJalaliDisplay(date: string): string {
  const parts = parseJalaliDateString(date);
  return `${toPersianDigits(String(parts.day))} ${persianMonthName(parts.month)} ${toPersianDigits(String(parts.year))}`;
}

export function weekdayName(date: string): string {
  const gregorian = jalaliToGregorian(parseJalaliDateString(date));
  const jsDay = gregorian.getDay();
  const saturdayIndex = (jsDay + 1) % 7;
  return PERSIAN_WEEKDAYS[saturdayIndex];
}

export function weekdayIndexFromSaturday(date: string): number {
  const gregorian = jalaliToGregorian(parseJalaliDateString(date));
  return (gregorian.getDay() + 1) % 7;
}

export function toPersianDigits(value: string | number): string {
  const map = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
  return String(value).replace(/\d/g, (digit) => map[Number(digit)]);
}

export function comparePeriodKeys(a: PeriodKey, b: PeriodKey): number {
  return a.localeCompare(b);
}

export function isPeriodBefore(a: PeriodKey, b: PeriodKey): boolean {
  return a < b;
}

export const JomaCalendarService = {
  todayJalali,
  todayJalaliString,
  gregorianToJalali,
  jalaliToGregorian,
  toJalaliDateString,
  parseJalaliDateString,
  isValidJalaliDateString,
  periodKeyFromParts,
  periodKeyFromDateString,
  parsePeriodKey,
  currentPeriodKey,
  daysInJalaliMonth,
  periodBounds,
  isDateInPeriod,
  weekStartSaturday,
  weekEndFriday,
  isSameJalaliDay,
  isDateInSameWeek,
  iteratePeriodDays,
  persianMonthName,
  formatPeriodLabel,
  formatJalaliDisplay,
  weekdayName,
  weekdayIndexFromSaturday,
  toPersianDigits,
  comparePeriodKeys,
  isPeriodBefore,
};

export default JomaCalendarService;
