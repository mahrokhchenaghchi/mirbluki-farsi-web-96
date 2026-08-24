export type Frequency = "DAILY" | "WEEKLY" | "MONTHLY";
export type PlanStatus = "DRAFT" | "PLANNING" | "RUNNING" | "ARCHIVED";
export type EventType = "PERFORMANCE_REGISTERED";
export type DataType = "DURATION" | "NUMERIC" | "BOOLEAN" | "RATING";
export type PeriodKey = string;

export type ActivityCategory =
  | "سلامت جسم"
  | "خواب و استراحت"
  | "سلامت روان"
  | "تمرکز و ذهن"
  | "یادگیری"
  | "رشد فردی"
  | "روابط"
  | "خانواده"
  | "ذهن‌آگاهی"
  | "مراقبت از خود"
  | "بهره‌وری"
  | "سبک زندگی";

export interface ActivityColor {
  id: string;
  label: string;
  value: string;
}

export interface JalaliDateParts {
  year: number;
  month: number;
  day: number;
}

export interface ActivityDefinition {
  id: string;
  userId: string;
  code: string;
  name: string;
  category: ActivityCategory;
  frequency: Frequency;
  dataType: DataType;
  dailyTarget: number;
  weeklyTarget: number;
  monthlyTarget: number;
  weight: number;
  sticker: string;
  color: string;
  isSeed: boolean;
  createdAt: string;
  updatedAt: string;
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
  name: string;
  category: ActivityCategory;
  frequency: Frequency;
  dataType: DataType;
  dailyTarget: number;
  weeklyTarget: number;
  monthlyTarget: number;
  targetValue: number;
  weight: number;
  sticker: string;
  color: string;
  snapshotAt: string;
}

export interface PerformanceEvent {
  id: string;
  userId: string;
  planId: string;
  planActivityId: string;
  periodKey: PeriodKey;
  frequency: Frequency;
  dataType: DataType;
  eventType: EventType;
  performanceDate: string;
  actualValue: number;
  createdAt: string;
}

export interface MoodScores {
  energy: number;
  general: number;
  focus: number;
  sleep: number;
  stress: number;
}

export interface MoodRecord {
  id: string;
  userId: string;
  jalaliDate: string;
  scores: MoodScores;
  note: string;
  createdAt: string;
}

export interface JomaUser {
  id: string;
  fullName: string;
  username: string;
  phone: string;
  email: string;
  job: string;
  passwordHash: string;
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
  | "FREQUENCY_INVALID"
  | "DATATYPE_INVALID";
