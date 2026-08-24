import type { Unspecified } from "./unspecified";

export type Frequency = "DAILY" | "WEEKLY" | "MONTHLY";
export type PlanStatus = "DRAFT" | "PLANNING" | "RUNNING" | "ARCHIVED";
export type EventType = "PERFORMANCE_REGISTERED";

export type PeriodKey = string;

export interface JalaliDateParts {
  year: number;
  month: number;
  day: number;
}

export interface ActivityDefinition {
  id: string;
  code: string;
  title: string;
  description: string | null;
  titleSpecified: boolean;
}

export interface Period {
  id: string;
  userId: string;
  periodKey: PeriodKey;
  year: number;
  month: number;
  startDate: string;
  endDate: string;
}

export interface Plan {
  id: string;
  userId: string;
  periodId: string;
  periodKey: PeriodKey;
  status: PlanStatus;
  createdAt: string;
  updatedAt: string;
  finalizedAt: string | null;
  startedAt: string | null;
  archivedAt: string | null;
}

export interface PlanActivity {
  id: string;
  userId: string;
  planId: string;
  periodKey: PeriodKey;
  activityId: string;
  activityCode: string;
  title: string;
  description: string | null;
  frequency: Frequency;
  targetValue: number;
  weight: number;
  snapshotAt: string;
}

export interface PerformanceEvent {
  id: string;
  userId: string;
  planId: string;
  planActivityId: string;
  periodKey: PeriodKey;
  frequency: Frequency;
  eventType: EventType;
  performanceDate: string;
  actualValue: number;
  createdAt: string;
}

export interface MoodRecord {
  id: string;
  userId: string;
  jalaliDate: string;
  metrics: Record<string, unknown>;
  metricsStatus: Unspecified | "DEFINED";
  createdAt: string;
}

export type RuleDecision =
  | { ok: true }
  | { ok: false; code: RuleErrorCode; message: string };

export type RuleErrorCode =
  | "PLAN_NOT_RUNNING"
  | "DATE_OUTSIDE_PERIOD"
  | "DAILY_DUPLICATE"
  | "INVALID_VALUE"
  | "INVALID_DATE"
  | "MISSING_PLAN_ACTIVITY"
  | "PLAN_NOT_EDITABLE"
  | "WEIGHT_INVALID"
  | "FREQUENCY_INVALID";
