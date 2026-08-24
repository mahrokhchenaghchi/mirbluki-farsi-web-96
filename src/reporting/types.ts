import type { ActivityCategory, DataType, Frequency, MoodScores, PeriodKey } from "@/domain/types";
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
  name: string;
  category: ActivityCategory | string;
  frequency: Frequency;
  dataType: DataType | string;
  targetValue: number;
  weight: number;
  sticker: string;
  color: string;
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
  mood?: MoodScores;
}

export interface MoodPoint {
  date: string;
  scores: MoodScores;
  note: string;
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
  moodSeries: MoodPoint[];
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
    name?: string;
    category?: string;
    frequency: Frequency;
    dataType?: string;
    targetValue: number;
    weight: number;
    sticker?: string;
    color?: string;
  }>;
  events: Array<{
    id: string;
    planActivityId: string;
    performanceDate: string;
    actualValue: number;
    createdAt: string;
  }>;
  moodDates?: string[];
  moods?: Array<{ jalaliDate: string; scores: MoodScores; note: string }>;
}
