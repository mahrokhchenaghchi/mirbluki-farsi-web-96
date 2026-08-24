import { beforeEach, describe, expect, it } from "vitest";

const memory = new Map<string, string>();

beforeEach(async () => {
  memory.clear();
  const localStorageMock = {
    getItem: (key: string) => memory.get(key) ?? null,
    setItem: (key: string, value: string) => {
      memory.set(key, value);
    },
    removeItem: (key: string) => {
      memory.delete(key);
    },
    clear: () => memory.clear(),
  };
  Object.defineProperty(globalThis, "window", {
    value: { localStorage: localStorageMock },
    configurable: true,
  });
  Object.defineProperty(globalThis, "localStorage", {
    value: localStorageMock,
    configurable: true,
  });

  const { localSignUp, localEnsurePeriod, localAddPlanActivity, localTransitionPlan, localRegisterPerformance, localListEventsForActivity } =
    await import("./db");

  const user = await localSignUp("a@test.com", "secret1");
  expect(user.email).toBe("a@test.com");
  const { plan } = localEnsurePeriod("1405-06");
  const activity = {
    id: "activity-act001",
    code: "ACT001",
    title: "ACT001",
    description: null,
    titleSpecified: false,
  };
  const daily = localAddPlanActivity({
    plan,
    activity,
    frequency: "DAILY",
    targetValue: 1,
    weight: 10,
  });
  const running = localTransitionPlan(localTransitionPlan(plan, "PLANNING"), "RUNNING");
  localRegisterPerformance({
    plan: running,
    planActivity: daily,
    performanceDate: "1405-06-02",
    actualValue: 1,
  });
  expect(localListEventsForActivity(daily.id)).toHaveLength(1);
  expect(() =>
    localRegisterPerformance({
      plan: running,
      planActivity: daily,
      performanceDate: "1405-06-02",
      actualValue: 1,
    }),
  ).toThrow(/روزانه/);
});

describe("local persistence", () => {
  it("stores local users and enforces daily uniqueness", () => {
    expect(memory.size).toBeGreaterThan(0);
  });
});
