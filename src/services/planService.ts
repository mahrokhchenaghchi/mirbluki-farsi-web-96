import type { ActivityDefinition, Frequency, Plan, PlanActivity, PlanStatus } from "@/domain/types";
import { isLocalMode } from "@/lib/mode";
import {
  localAddPlanActivity,
  localGetPlan,
  localListPlanActivities,
  localRemovePlanActivity,
  localTransitionPlan,
  localUpdatePlanActivity,
} from "@/persistence/local/db";

export async function getPlan(planId: string): Promise<Plan> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localGetPlan(planId);
}

export async function listPlanActivities(planId: string): Promise<PlanActivity[]> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localListPlanActivities(planId);
}

export async function addPlanActivity(
  plan: Plan,
  activity: ActivityDefinition,
  overrides?: { frequency?: Frequency; targetValue?: number; weight?: number },
): Promise<PlanActivity> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localAddPlanActivity(plan, activity, overrides);
}

export async function updatePlanActivity(
  plan: Plan,
  planActivityId: string,
  patch: { frequency?: Frequency; targetValue?: number; weight?: number; sortOrder?: number },
): Promise<PlanActivity> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localUpdatePlanActivity(plan, planActivityId, patch);
}

export async function removePlanActivity(plan: Plan, planActivityId: string): Promise<void> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  localRemovePlanActivity(plan, planActivityId);
}

export async function transitionPlan(plan: Plan, next: PlanStatus): Promise<Plan> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localTransitionPlan(plan, next);
}
