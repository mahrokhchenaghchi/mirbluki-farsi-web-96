import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { Frequency, PerformanceEvent, Plan, PlanActivity, RuleDecision } from "@/domain/types";

export function isAllowedFrequency(value: string): value is Frequency {
  return value === "DAILY" || value === "WEEKLY" || value === "MONTHLY";
}

export function canRegisterPerformance(input: {
  plan: Pick<Plan, "status" | "periodKey">;
  planActivity: Pick<PlanActivity, "id" | "frequency" | "periodKey">;
  performanceDate: string;
  actualValue: number;
  existingEvents: Array<Pick<PerformanceEvent, "planActivityId" | "performanceDate" | "frequency">>;
}): RuleDecision {
  if (!Number.isFinite(input.actualValue) || input.actualValue < 0) {
    return {
      ok: false,
      code: "INVALID_VALUE",
      message: "مقدار عملکرد باید یک عدد صفر یا بزرگ‌تر باشد.",
    };
  }

  if (!JomaCalendarService.isValidJalaliDateString(input.performanceDate)) {
    return {
      ok: false,
      code: "INVALID_DATE",
      message: "تاریخ عملکرد معتبر نیست.",
    };
  }

  if (input.plan.status !== "RUNNING") {
    return {
      ok: false,
      code: "PLAN_NOT_RUNNING",
      message: "فقط در دوره در حال اجرا می‌توان عملکرد ثبت کرد.",
    };
  }

  if (!JomaCalendarService.isDateInPeriod(input.performanceDate, input.plan.periodKey)) {
    return {
      ok: false,
      code: "DATE_OUTSIDE_PERIOD",
      message: "تاریخ انتخاب‌شده داخل این دوره نیست.",
    };
  }

  if (input.planActivity.frequency === "DAILY") {
    const duplicate = input.existingEvents.some(
      (event) =>
        event.planActivityId === input.planActivity.id &&
        event.frequency === "DAILY" &&
        event.performanceDate === input.performanceDate,
    );
    if (duplicate) {
      return {
        ok: false,
        code: "DAILY_DUPLICATE",
        message: "برای این فعالیت روزانه، امروز قبلاً عملکرد ثبت شده است.",
      };
    }
  }

  return { ok: true };
}

export function calculateActual(
  events: Array<Pick<PerformanceEvent, "actualValue" | "performanceDate">>,
  scope?: { from?: string; to?: string },
): number {
  return events
    .filter((event) => {
      if (scope?.from && event.performanceDate < scope.from) return false;
      if (scope?.to && event.performanceDate > scope.to) return false;
      return true;
    })
    .reduce((sum, event) => sum + Number(event.actualValue || 0), 0);
}

export function eventsInWeek(
  events: Array<Pick<PerformanceEvent, "actualValue" | "performanceDate">>,
  referenceDate: string,
): Array<Pick<PerformanceEvent, "actualValue" | "performanceDate">> {
  return events.filter((event) => JomaCalendarService.isDateInSameWeek(event.performanceDate, referenceDate));
}

export function weeklyActual(
  events: Array<Pick<PerformanceEvent, "actualValue" | "performanceDate">>,
  referenceDate: string,
): number {
  return calculateActual(eventsInWeek(events, referenceDate));
}

export function monthlyActual(
  events: Array<Pick<PerformanceEvent, "actualValue" | "performanceDate">>,
  periodKey: string,
): number {
  return calculateActual(
    events.filter((event) => JomaCalendarService.periodKeyFromDateString(event.performanceDate) === periodKey),
  );
}

export function hasDailyRegistration(
  events: Array<Pick<PerformanceEvent, "performanceDate">>,
  date: string,
): boolean {
  return events.some((event) => event.performanceDate === date);
}
