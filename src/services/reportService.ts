import JomaCalendarService from "@/calendar/JomaCalendarService";
import { buildReportProjection } from "@/reporting/engine";
import type { ReportProjection } from "@/reporting/types";
import { isLocalMode } from "@/lib/mode";
import { getSupabase } from "@/lib/supabase";
import { localRebuildProjection } from "@/persistence/local/db";
import { mapEvent, mapPlan, mapPlanActivity } from "./mappers";
import { listMoodDates } from "./moodService";

export async function rebuildAndStoreProjection(planId: string): Promise<ReportProjection> {
  if (isLocalMode()) return localRebuildProjection(planId);

  const supabase = getSupabase();
  const { data: planRow, error } = await supabase.from("joma_plans").select("*").eq("id", planId).single();
  if (error) throw error;
  const plan = mapPlan(planRow);

  const [{ data: activityRows, error: activityError }, { data: eventRows, error: eventError }] = await Promise.all([
    supabase.from("joma_plan_activities").select("*").eq("plan_id", planId).order("created_at"),
    supabase.from("joma_performance_events").select("*").eq("plan_id", planId).order("performance_date"),
  ]);
  if (activityError) throw activityError;
  if (eventError) throw eventError;

  const activities = (activityRows ?? []).map(mapPlanActivity);
  const events = (eventRows ?? []).map(mapEvent);
  const bounds = JomaCalendarService.periodBounds(plan.periodKey);
  const moodDates = await listMoodDates(bounds.startDate, bounds.endDate);

  const projection = buildReportProjection({
    periodKey: plan.periodKey,
    planId: plan.id,
    planStatus: plan.status,
    activities: activities.map((item) => ({
      id: item.id,
      activityCode: item.activityCode,
      title: item.title,
      frequency: item.frequency,
      targetValue: item.targetValue,
      weight: item.weight,
    })),
    events: events.map((item) => ({
      id: item.id,
      planActivityId: item.planActivityId,
      performanceDate: item.performanceDate,
      actualValue: item.actualValue,
      createdAt: item.createdAt,
    })),
    moodDates,
  });

  const { error: upsertError } = await supabase.from("joma_report_projections").upsert(
    {
      user_id: plan.userId,
      plan_id: plan.id,
      period_key: plan.periodKey,
      payload: JSON.parse(JSON.stringify(projection)),
      source_event_count: projection.sourceEventCount,
      generated_at: projection.generatedAt,
    },
    { onConflict: "plan_id" },
  );
  if (upsertError) throw upsertError;
  return projection;
}

export async function loadReport(planId: string): Promise<ReportProjection> {
  return rebuildAndStoreProjection(planId);
}
