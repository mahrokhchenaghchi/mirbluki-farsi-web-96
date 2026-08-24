import { describe, expect, it } from "vitest";
import JomaCalendarService from "./JomaCalendarService";

describe("JomaCalendarService", () => {
  it("builds monthly period keys and bounds", () => {
    const bounds = JomaCalendarService.periodBounds("1405-06");
    expect(bounds.startDate).toBe("1405-06-01");
    expect(bounds.endDate).toBe("1405-06-31");
    expect(JomaCalendarService.isDateInPeriod("1405-06-15", "1405-06")).toBe(true);
    expect(JomaCalendarService.isDateInPeriod("1405-07-01", "1405-06")).toBe(false);
  });

  it("uses Saturday as week start", () => {
    const start = JomaCalendarService.weekStartSaturday("1405-06-02");
    const end = JomaCalendarService.weekEndFriday("1405-06-02");
    expect(JomaCalendarService.weekdayName(start)).toBe("شنبه");
    expect(JomaCalendarService.weekdayName(end)).toBe("جمعه");
    expect(JomaCalendarService.isDateInSameWeek(start, end)).toBe(true);
  });

  it("formats Persian period labels", () => {
    expect(JomaCalendarService.formatPeriodLabel("1405-05")).toContain("مرداد");
    expect(JomaCalendarService.iteratePeriodDays("1405-01")).toHaveLength(31);
  });

  it("converts gregorian sample to jalali consistently", () => {
    const date = new Date(2026, 7, 24);
    const jalali = JomaCalendarService.gregorianToJalali(date);
    const back = JomaCalendarService.jalaliToGregorian(jalali);
    expect(back.getFullYear()).toBe(2026);
    expect(back.getMonth()).toBe(7);
    expect(back.getDate()).toBe(24);
  });
});
