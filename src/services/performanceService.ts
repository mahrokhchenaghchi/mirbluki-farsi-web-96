import { canRegisterPerformance } from "@/domain/rules/performanceRules";
import type { PerformanceEvent, Plan, PlanActivity } from "@/domain/types";
import { JomaError } from "@/lib/errors";
import { isLocalMode } from "@/lib/mode";
import { localListEventsForActivity, localListEventsForPlan, localRegisterPerformance } from "@/persistence/local/db";

export async function listEventsForPlan(planId: string): Promise<PerformanceEvent[]> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localListEventsForPlan(planId);
}

export async function listEventsForActivity(planActivityId: string): Promise<PerformanceEvent[]> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localListEventsForActivity(planActivityId);
}

export async function registerPerformance(input: {
  plan: Plan;
  planActivity: PlanActivity;
  performanceDate: string;
  actualValue: number;
}): Promise<string> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  const existing = await listEventsForActivity(input.planActivity.id);
  const decision = canRegisterPerformance({
    plan: input.plan,
    planActivity: input.planActivity,
    performanceDate: input.performanceDate,
    actualValue: input.actualValue,
    existingEvents: existing,
  });
  if (!decision.ok) throw new JomaError(decision.code, decision.message);
  return localRegisterPerformance(input);
}
