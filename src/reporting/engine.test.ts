import { describe, expect, it } from "vitest";
import { buildReportProjection } from "./engine";

describe("Reporting Engine", () => {
  it("aggregates only real events and keeps achievement unspecified", () => {
    const report = buildReportProjection({
      periodKey: "1405-05",
      planId: "plan-may",
      planStatus: "ARCHIVED",
      generatedAt: "2026-08-01T00:00:00.000Z",
      activities: [
        {
          id: "snap-a",
          activityCode: "ACT001",
          title: "فعالیت ACT001",
          frequency: "WEEKLY",
          targetValue: 60,
          weight: 30,
        },
      ],
      events: [
        {
          id: "e1",
          planActivityId: "snap-a",
          performanceDate: "1405-05-02",
          actualValue: 20,
          createdAt: "2026-07-24T00:00:00.000Z",
        },
        {
          id: "e2",
          planActivityId: "snap-a",
          performanceDate: "1405-05-04",
          actualValue: 20,
          createdAt: "2026-07-26T00:00:00.000Z",
        },
      ],
      moodDates: ["1405-05-02"],
    });

    expect(report.activities[0].actual).toBe(40);
    expect(report.activities[0].weight).toBe(30);
    expect(report.activities[0].eventCount).toBe(2);
    expect(report.activities[0].achievement.status).toBe("UNSPECIFIED");
    expect(report.overallSuccess.status).toBe("UNSPECIFIED");
    expect(report.sourceEventCount).toBe(2);
    expect(report.calendarDays.some((day) => day.date === "1405-05-02" && day.hasMood)).toBe(true);
  });

  it("keeps historical snapshot weight when a later period uses a different weight", () => {
    const may = buildReportProjection({
      periodKey: "1405-05",
      planId: "plan-may",
      planStatus: "ARCHIVED",
      activities: [
        {
          id: "may-a",
          activityCode: "ACT001",
          title: "فعالیت ACT001",
          frequency: "MONTHLY",
          targetValue: 10,
          weight: 30,
        },
      ],
      events: [
        {
          id: "e1",
          planActivityId: "may-a",
          performanceDate: "1405-05-10",
          actualValue: 10,
          createdAt: "2026-07-01T00:00:00.000Z",
        },
      ],
    });

    const june = buildReportProjection({
      periodKey: "1405-06",
      planId: "plan-june",
      planStatus: "RUNNING",
      activities: [
        {
          id: "june-a",
          activityCode: "ACT001",
          title: "فعالیت ACT001",
          frequency: "MONTHLY",
          targetValue: 10,
          weight: 50,
        },
      ],
      events: [
        {
          id: "e2",
          planActivityId: "june-a",
          performanceDate: "1405-06-10",
          actualValue: 4,
          createdAt: "2026-08-01T00:00:00.000Z",
        },
      ],
    });

    expect(may.activities[0].weight).toBe(30);
    expect(june.activities[0].weight).toBe(50);
    expect(may.activities[0].actual).toBe(10);
    expect(june.activities[0].actual).toBe(4);
    expect(may.periodKey).toBe("1405-05");
    expect(june.periodKey).toBe("1405-06");
  });

  it("shows no invented KPI when there is no data", () => {
    const report = buildReportProjection({
      periodKey: "1405-07",
      planId: "plan-empty",
      planStatus: "DRAFT",
      activities: [],
      events: [],
    });
    expect(report.activities).toHaveLength(0);
    expect(report.sourceEventCount).toBe(0);
    expect(report.overallSuccess.status).toBe("UNSPECIFIED");
  });
});
