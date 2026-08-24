import { beforeEach, describe, expect, it } from "vitest";

const memory = new Map<string, string>();

beforeEach(() => {
  memory.clear();
  const localStorageMock = {
    getItem: (key: string) => memory.get(key) ?? null,
    setItem: (key: string, value: string) => memory.set(key, value),
    removeItem: (key: string) => memory.delete(key),
    clear: () => memory.clear(),
  };
  Object.defineProperty(globalThis, "window", { value: { localStorage: localStorageMock }, configurable: true });
  Object.defineProperty(globalThis, "localStorage", { value: localStorageMock, configurable: true });
});

describe("local persistence v2", () => {
  it("registers users uniquely and isolates daily performance", async () => {
    const db = await import("./db");
    await db.localSignUp({
      firstName: "کاربر",
      lastName: "آزمایشی",
      username: "tester",
      phone: "09123456789",
      email: "a@test.com",
      job: "tester",
      password: "secret1",
    });
    const activities = db.localListActivities();
    expect(activities).toHaveLength(45);
    expect(activities[0].name).toBe("ورزش");

    const { plan } = db.localEnsurePeriod("1405-06");
    const daily = db.localAddPlanActivity(plan, activities[0]);
    const running = db.localTransitionPlan(db.localTransitionPlan(plan, "PLANNING"), "RUNNING");
    db.localRegisterPerformance({
      plan: running,
      planActivity: daily,
      performanceDate: "1405-06-02",
      actualValue: 30,
    });
    expect(db.localListEventsForActivity(daily.id)).toHaveLength(1);
    expect(() =>
      db.localRegisterPerformance({
        plan: running,
        planActivity: daily,
        performanceDate: "1405-06-02",
        actualValue: 10,
      }),
    ).toThrow(/روزانه/);
  });
});
