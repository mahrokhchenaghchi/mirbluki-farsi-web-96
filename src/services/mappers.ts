import type { ActivityDefinition, MoodRecord, Period, PerformanceEvent, Plan, PlanActivity } from "@/domain/types";
import type { Database } from "@/lib/database.types";

type Tables = Database["public"]["Tables"];

export function mapPeriod(row: Tables["joma_periods"]["Row"]): Period {
  return {
    id: row.id,
    userId: row.user_id,
    periodKey: row.period_key,
    year: row.year,
    month: row.month,
    startDate: row.start_date,
    endDate: row.end_date,
  };
}

export function mapPlan(row: Tables["joma_plans"]["Row"]): Plan {
  return {
    id: row.id,
    userId: row.user_id,
    periodId: row.period_id,
    periodKey: row.period_key,
    status: row.status,
    createdAt: row.created_at,
    updatedAt: row.updated_at,
    finalizedAt: row.finalized_at,
    startedAt: row.started_at,
    archivedAt: row.archived_at,
  };
}

export function mapPlanActivity(row: Tables["joma_plan_activities"]["Row"]): PlanActivity {
  return {
    id: row.id,
    userId: row.user_id,
    planId: row.plan_id,
    periodKey: row.period_key,
    activityId: row.activity_id,
    activityCode: row.activity_code,
    title: row.title,
    description: row.description,
    frequency: row.frequency,
    targetValue: Number(row.target_value),
    weight: Number(row.weight),
    snapshotAt: row.snapshot_at,
  };
}

export function mapEvent(row: Tables["joma_performance_events"]["Row"]): PerformanceEvent {
  return {
    id: row.id,
    userId: row.user_id,
    planId: row.plan_id,
    planActivityId: row.plan_activity_id,
    periodKey: row.period_key,
    frequency: row.frequency,
    eventType: "PERFORMANCE_REGISTERED",
    performanceDate: row.performance_date,
    actualValue: Number(row.actual_value),
    createdAt: row.created_at,
  };
}

export function mapActivity(row: Tables["joma_activities"]["Row"]): ActivityDefinition {
  return {
    id: row.id,
    code: row.code,
    title: row.title,
    description: row.description,
    titleSpecified: row.title_specified,
  };
}

export function mapMood(row: Tables["joma_mood_records"]["Row"]): MoodRecord {
  return {
    id: row.id,
    userId: row.user_id,
    jalaliDate: row.jalali_date,
    metrics: (row.metrics as Record<string, unknown>) ?? {},
    metricsStatus: row.metrics_status === "DEFINED" ? "DEFINED" : "UNSPECIFIED",
    createdAt: row.created_at,
  };
}
