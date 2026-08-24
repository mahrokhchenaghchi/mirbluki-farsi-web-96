import { describe, expect, it } from "vitest";
import { hasPermission } from "./permissions";

describe("permissions", () => {
  it("gives members plan and report access but not admin", () => {
    expect(hasPermission("member", "VIEW_DASHBOARD")).toBe(true);
    expect(hasPermission("member", "ADMIN_ACCESS")).toBe(false);
    expect(hasPermission("admin", "ADMIN_ACCESS")).toBe(true);
  });
});
