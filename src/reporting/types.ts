import type { Frequency, PeriodKey } from "@/domain/types";
import { unspecifiedResult } from "@/domain/unspecified";

export interface ReportEventTrace {
  id: string;
  performanceDate: string;
  actualValue: number;
  createdAt: string;
}

export interface ActivityReportRow {
  planActivityId: string;
  activityCode: string;
  title: string;
  frequency: Frequency;
  targetValue: number;
  weight: number;
  actual: number;
  eventCount: number;
  events: ReportEventTrace[];
  achievement: ReturnType<typeof unspecifiedResult>;
  weightedAchievement: ReturnType<typeof unspecifiedResult>;
}

export interface CalendarDayProjection {
  date: string;
  weekday: string;
  eventCount: number;
  actualTotal: number;
  hasMood: boolean;
}

export interface ReportProjection {
  periodKey: PeriodKey;
  planId: string;
  planStatus: string;
  generatedAt: string;
  sourceEventCount: number;
  activities: ActivityReportRow[];
  overallSuccess: ReturnType<typeof unspecifiedResult>;
  calendarDays: CalendarDayProjection[];
  moodDates: string[];
  weightSum: number;
}

export interface BuildReportInput {
  periodKey: PeriodKey;
  planId: string;
  planStatus: string;
  generatedAt?: string;
  activities: Array<{
    id: string;
    activityCode: string;
    title: string;
    frequency: Frequency;
    targetValue: number;
    weight: number;
  }>;
  events: Array<{
    id: string;
    planActivityId: string;
    performanceDate: string;
    actualValue: number;
    createdAt: string;
  }>;
  moodDates?: string[];
}
