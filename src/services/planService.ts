import { canEditPlan, canTransition, validatePlanActivityInput } from "@/domain/rules/planRules";
import type { ActivityDefinition, Frequency, Plan, PlanActivity, PlanStatus } from "@/domain/types";
import { JomaError } from "@/lib/errors";
import { isLocalMode } from "@/lib/mode";
import { getSupabase } from "@/lib/supabase";
import {
  localAddPlanActivity,
  localGetPlan,
  localListPlanActivities,
  localRemovePlanActivity,
  localTransitionPlan,
} from "@/persistence/local/db";
import { activityLabel } from "./activityService";
import { mapPlan, mapPlanActivity } from "./mappers";

export async function getPlan(planId: string): Promise<Plan> {
  if (isLocalMode()) return localGetPlan(planId);
  const supabase = getSupabase();
  const { data, error } = await supabase.from("joma_plans").select("*").eq("id", planId).single();
  if (error) throw error;
  return mapPlan(data);
}

export async function listPlanActivities(planId: string): Promise<PlanActivity[]> {
  if (isLocalMode()) return localListPlanActivities(planId);
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_plan_activities")
    .select("*")
    .eq("plan_id", planId)
    .order("created_at");
  if (error) throw error;
  return (data ?? []).map(mapPlanActivity);
}

export async function addPlanActivity(input: {
  plan: Plan;
  activity: ActivityDefinition;
  frequency: Frequency;
  targetValue: number;
  weight: number;
}): Promise<PlanActivity> {
  if (isLocalMode()) return localAddPlanActivity(input);

  const editable = canEditPlan(input.plan.status);
  if (!editable.ok) throw new JomaError(editable.code, editable.message);
  const valid = validatePlanActivityInput(input);
  if (!valid.ok) throw new JomaError(valid.code, valid.message);

  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_plan_activities")
    .insert({
      plan_id: input.plan.id,
      period_key: input.plan.periodKey,
      activity_id: input.activity.id,
      activity_code: input.activity.code,
      title: activityLabel(input.activity),
      description: input.activity.description,
      frequency: input.frequency,
      target_value: input.targetValue,
      weight: input.weight,
    })
    .select("*")
    .single();
  if (error) {
    if (error.code === "23505") {
      throw new JomaError("DUPLICATE_ACTIVITY", "این فعالیت قبلاً به برنامه اضافه شده است.");
    }
    throw error;
  }
  return mapPlanActivity(data);
}

export async function removePlanActivity(plan: Plan, planActivityId: string): Promise<void> {
  if (isLocalMode()) {
    localRemovePlanActivity(plan, planActivityId);
    return;
  }
  const editable = canEditPlan(plan.status);
  if (!editable.ok) throw new JomaError(editable.code, editable.message);
  const supabase = getSupabase();
  const { error } = await supabase.from("joma_plan_activities").delete().eq("id", planActivityId);
  if (error) throw error;
}

export async function transitionPlan(plan: Plan, next: PlanStatus): Promise<Plan> {
  if (isLocalMode()) return localTransitionPlan(plan, next);
  const allowed = canTransition(plan.status, next);
  if (!allowed.ok) throw new JomaError(allowed.code, allowed.message);

  const now = new Date().toISOString();
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_plans")
    .update({
      status: next,
      finalized_at: next === "PLANNING" ? now : plan.finalizedAt,
      started_at: next === "RUNNING" ? now : plan.startedAt,
      archived_at: next === "ARCHIVED" ? now : plan.archivedAt,
    })
    .eq("id", plan.id)
    .select("*")
    .single();
  if (error) throw error;
  return mapPlan(data);
}
