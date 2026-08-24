import type { Frequency, PlanStatus, RuleDecision } from "@/domain/types";
import { isAllowedFrequency } from "./performanceRules";

const EDITABLE: PlanStatus[] = ["DRAFT", "PLANNING"];

export function canEditPlan(status: PlanStatus): RuleDecision {
  if (!EDITABLE.includes(status)) {
    return {
      ok: false,
      code: "PLAN_NOT_EDITABLE",
      message: "برنامه این دوره قفل است و برای حفظ تاریخچه قابل ویرایش نیست.",
    };
  }
  return { ok: true };
}

export function canTransition(from: PlanStatus, to: PlanStatus): RuleDecision {
  const allowed: Record<PlanStatus, PlanStatus[]> = {
    DRAFT: ["PLANNING", "ARCHIVED"],
    PLANNING: ["RUNNING", "DRAFT", "ARCHIVED"],
    RUNNING: ["ARCHIVED"],
    ARCHIVED: [],
  };
  if (!allowed[from].includes(to)) {
    return {
      ok: false,
      code: "PLAN_NOT_EDITABLE",
      message: "این تغییر وضعیت برنامه مجاز نیست.",
    };
  }
  return { ok: true };
}

export function validatePlanActivityInput(input: {
  frequency: string;
  targetValue: number;
  weight: number;
}): RuleDecision {
  if (!isAllowedFrequency(input.frequency)) {
    return {
      ok: false,
      code: "FREQUENCY_INVALID",
      message: "تناوب فقط می‌تواند روزانه، هفتگی یا ماهانه باشد.",
    };
  }
  if (!Number.isFinite(input.targetValue) || input.targetValue <= 0) {
    return {
      ok: false,
      code: "INVALID_VALUE",
      message: "هدف باید عددی بزرگ‌تر از صفر باشد.",
    };
  }
  if (!Number.isFinite(input.weight) || input.weight < 0) {
    return {
      ok: false,
      code: "WEIGHT_INVALID",
      message: "وزن فعالیت نمی‌تواند منفی باشد.",
    };
  }
  return { ok: true };
}

export function sumWeights(weights: number[]): number {
  return weights.reduce((sum, weight) => sum + Number(weight || 0), 0);
}

export function isFrequency(value: string): value is Frequency {
  return isAllowedFrequency(value);
}
