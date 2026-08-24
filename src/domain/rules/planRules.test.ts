import { describe, expect, it } from "vitest";
import { canEditPlan, canTransition, sumWeights, validatePlanActivityInput } from "./planRules";

describe("plan lifecycle", () => {
  it("allows editing only in DRAFT and PLANNING", () => {
    expect(canEditPlan("DRAFT").ok).toBe(true);
    expect(canEditPlan("PLANNING").ok).toBe(true);
    expect(canEditPlan("RUNNING").ok).toBe(false);
    expect(canEditPlan("ARCHIVED").ok).toBe(false);
  });

  it("allows the defined lifecycle transitions", () => {
    expect(canTransition("DRAFT", "PLANNING").ok).toBe(true);
    expect(canTransition("PLANNING", "RUNNING").ok).toBe(true);
    expect(canTransition("RUNNING", "ARCHIVED").ok).toBe(true);
    expect(canTransition("ARCHIVED", "RUNNING").ok).toBe(false);
    expect(canTransition("RUNNING", "DRAFT").ok).toBe(false);
  });

  it("rejects invented frequencies and invalid numbers", () => {
    expect(
      validatePlanActivityInput({ frequency: "YEARLY", targetValue: 10, weight: 10 }).ok,
    ).toBe(false);
    expect(
      validatePlanActivityInput({ frequency: "DAILY", targetValue: 0, weight: 10 }).ok,
    ).toBe(false);
    expect(
      validatePlanActivityInput({ frequency: "WEEKLY", targetValue: 60, weight: 34 }).ok,
    ).toBe(true);
    expect(sumWeights([34, 33, 33])).toBe(100);
  });
});
