import JomaCalendarService from "@/calendar/JomaCalendarService";
import { calculateActual } from "@/domain/rules/performanceRules";
import { unspecifiedResult } from "@/domain/unspecified";
import type { BuildReportInput, ReportProjection } from "./types";

/**
 * Reporting Engine
 * Source of truth remains events + plan snapshots.
 * Achievement / overall success stay UNSPECIFIED — no invented formula.
 */
export function buildReportProjection(input: BuildReportInput): ReportProjection {
  const activities = input.activities.map((activity) => {
    const events = input.events
      .filter((event) => event.planActivityId === activity.id)
      .sort((a, b) => a.performanceDate.localeCompare(b.performanceDate) || a.createdAt.localeCompare(b.createdAt));

    return {
      planActivityId: activity.id,
      activityCode: activity.activityCode,
      title: activity.title,
      frequency: activity.frequency,
      targetValue: Number(activity.targetValue),
      weight: Number(activity.weight),
      actual: calculateActual(events),
      eventCount: events.length,
      events: events.map((event) => ({
        id: event.id,
        performanceDate: event.performanceDate,
        actualValue: Number(event.actualValue),
        createdAt: event.createdAt,
      })),
      achievement: unspecifiedResult("achievementFormula"),
      weightedAchievement: unspecifiedResult("weightedAchievementFormula"),
    };
  });

  const eventsByDate = new Map<string, { count: number; actual: number }>();
  for (const event of input.events) {
    const current = eventsByDate.get(event.performanceDate) ?? { count: 0, actual: 0 };
    current.count += 1;
    current.actual += Number(event.actualValue || 0);
    eventsByDate.set(event.performanceDate, current);
  }

  const moodSet = new Set(input.moodDates ?? []);
  const calendarDays = JomaCalendarService.iteratePeriodDays(input.periodKey).map((date) => {
    const dayEvents = eventsByDate.get(date);
    return {
      date,
      weekday: JomaCalendarService.weekdayName(date),
      eventCount: dayEvents?.count ?? 0,
      actualTotal: dayEvents?.actual ?? 0,
      hasMood: moodSet.has(date),
    };
  });

  return {
    periodKey: input.periodKey,
    planId: input.planId,
    planStatus: input.planStatus,
    generatedAt: input.generatedAt ?? new Date().toISOString(),
    sourceEventCount: input.events.length,
    activities,
    overallSuccess: unspecifiedResult("overallSuccessFormula"),
    calendarDays,
    moodDates: [...moodSet].sort(),
    weightSum: activities.reduce((sum, row) => sum + row.weight, 0),
  };
}

export function emptyReport(periodKey: string, planId: string, planStatus: string): ReportProjection {
  return buildReportProjection({
    periodKey,
    planId,
    planStatus,
    activities: [],
    events: [],
    moodDates: [],
  });
}
