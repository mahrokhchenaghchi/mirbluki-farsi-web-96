import { describe, expect, it } from "vitest";
import {
  calculateActual,
  canRegisterPerformance,
  hasDailyRegistration,
  weeklyActual,
} from "./performanceRules";

const runningPlan = { status: "RUNNING" as const, periodKey: "1405-06" };
const dailyActivity = { id: "pa-daily", frequency: "DAILY" as const, periodKey: "1405-06" };
const weeklyActivity = { id: "pa-weekly", frequency: "WEEKLY" as const, periodKey: "1405-06" };
const monthlyActivity = { id: "pa-monthly", frequency: "MONTHLY" as const, periodKey: "1405-06" };

describe("DAILY rules", () => {
  it("accepts the first registration of the day", () => {
    const result = canRegisterPerformance({
      plan: runningPlan,
      planActivity: dailyActivity,
      performanceDate: "1405-06-02",
      actualValue: 1,
      existingEvents: [],
    });
    expect(result.ok).toBe(true);
  });

  it("rejects a second registration on the same day", () => {
    const result = canRegisterPerformance({
      plan: runningPlan,
      planActivity: dailyActivity,
      performanceDate: "1405-06-02",
      actualValue: 1,
      existingEvents: [
        {
          planActivityId: "pa-daily",
          performanceDate: "1405-06-02",
          frequency: "DAILY",
        },
      ],
    });
    expect(result.ok).toBe(false);
    if (!result.ok) expect(result.code).toBe("DAILY_DUPLICATE");
  });

  it("accepts the next day after a successful daily registration", () => {
    const result = canRegisterPerformance({
      plan: runningPlan,
      planActivity: dailyActivity,
      performanceDate: "1405-06-03",
      actualValue: 1,
      existingEvents: [
        {
          planActivityId: "pa-daily",
          performanceDate: "1405-06-02",
          frequency: "DAILY",
        },
      ],
    });
    expect(result.ok).toBe(true);
  });
});

describe("WEEKLY rules", () => {
  it("accepts multiple registrations in the same week and sums them", () => {
    const first = canRegisterPerformance({
      plan: runningPlan,
      planActivity: weeklyActivity,
      performanceDate: "1405-06-01",
      actualValue: 20,
      existingEvents: [],
    });
    const second = canRegisterPerformance({
      plan: runningPlan,
      planActivity: weeklyActivity,
      performanceDate: "1405-06-03",
      actualValue: 15,
      existingEvents: [],
    });
    expect(first.ok).toBe(true);
    expect(second.ok).toBe(true);
    expect(
      weeklyActual(
        [
          { actualValue: 20, performanceDate: "1405-06-01" },
          { actualValue: 15, performanceDate: "1405-06-03" },
          { actualValue: 25, performanceDate: "1405-06-05" },
        ],
        "1405-06-03",
      ),
    ).toBe(60);
  });
});

describe("MONTHLY rules", () => {
  it("accepts multiple registrations and aggregates them", () => {
    const events = [
      { actualValue: 10, performanceDate: "1405-06-01" },
      { actualValue: 15, performanceDate: "1405-06-10" },
      { actualValue: 20, performanceDate: "1405-06-18" },
    ];
    expect(
      canRegisterPerformance({
        plan: runningPlan,
        planActivity: monthlyActivity,
        performanceDate: "1405-06-18",
        actualValue: 20,
        existingEvents: [],
      }).ok,
    ).toBe(true);
    expect(calculateActual(events)).toBe(45);
  });
});

describe("guards", () => {
  it("rejects registration when plan is not running", () => {
    const result = canRegisterPerformance({
      plan: { status: "DRAFT", periodKey: "1405-06" },
      planActivity: dailyActivity,
      performanceDate: "1405-06-02",
      actualValue: 1,
      existingEvents: [],
    });
    expect(result.ok).toBe(false);
    if (!result.ok) expect(result.code).toBe("PLAN_NOT_RUNNING");
  });

  it("rejects dates outside the period", () => {
    const result = canRegisterPerformance({
      plan: runningPlan,
      planActivity: dailyActivity,
      performanceDate: "1405-07-01",
      actualValue: 1,
      existingEvents: [],
    });
    expect(result.ok).toBe(false);
    if (!result.ok) expect(result.code).toBe("DATE_OUTSIDE_PERIOD");
  });

  it("detects an existing daily registration", () => {
    expect(hasDailyRegistration([{ performanceDate: "1405-06-02" }], "1405-06-02")).toBe(true);
  });
});
