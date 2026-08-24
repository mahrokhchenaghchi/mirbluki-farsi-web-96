import { canRegisterPerformance } from "@/domain/rules/performanceRules";
import type { PerformanceEvent, Plan, PlanActivity } from "@/domain/types";
import { JomaError } from "@/lib/errors";
import { isLocalMode } from "@/lib/mode";
import { getSupabase } from "@/lib/supabase";
import { localListEventsForActivity, localListEventsForPlan, localRegisterPerformance } from "@/persistence/local/db";
import { mapEvent } from "./mappers";
import { rebuildAndStoreProjection } from "./reportService";

export async function listEventsForPlan(planId: string): Promise<PerformanceEvent[]> {
  if (isLocalMode()) return localListEventsForPlan(planId);
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_performance_events")
    .select("*")
    .eq("plan_id", planId)
    .order("performance_date")
    .order("created_at");
  if (error) throw error;
  return (data ?? []).map(mapEvent);
}

export async function listEventsForActivity(planActivityId: string): Promise<PerformanceEvent[]> {
  if (isLocalMode()) return localListEventsForActivity(planActivityId);
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_performance_events")
    .select("*")
    .eq("plan_activity_id", planActivityId)
    .order("performance_date")
    .order("created_at");
  if (error) throw error;
  return (data ?? []).map(mapEvent);
}

export async function registerPerformance(input: {
  plan: Plan;
  planActivity: PlanActivity;
  performanceDate: string;
  actualValue: number;
}): Promise<string> {
  if (isLocalMode()) return localRegisterPerformance(input);

  const existing = await listEventsForActivity(input.planActivity.id);
  const decision = canRegisterPerformance({
    plan: input.plan,
    planActivity: input.planActivity,
    performanceDate: input.performanceDate,
    actualValue: input.actualValue,
    existingEvents: existing,
  });
  if (!decision.ok) {
    throw new JomaError(decision.code, decision.message);
  }

  const supabase = getSupabase();
  const { data, error } = await supabase.rpc("joma_register_performance", {
    p_plan_activity_id: input.planActivity.id,
    p_performance_date: input.performanceDate,
    p_actual_value: input.actualValue,
  });
  if (error) throw error;

  await rebuildAndStoreProjection(input.plan.id);
  return data;
}
