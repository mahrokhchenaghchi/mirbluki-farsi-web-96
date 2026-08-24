import { describe, expect, it } from "vitest";
import { isValidIranMobile, isValidUsername, validateRegistration } from "./validation";

describe("registration validation", () => {
  it("accepts a complete valid form", () => {
    expect(
      validateRegistration({
        firstName: "سارا",
        lastName: "محمدی",
        username: "sara.m",
        phone: "09121234567",
        email: "sara@test.com",
        job: "معلم",
        password: "secret1",
        confirmPassword: "secret1",
      }),
    ).toBeNull();
  });

  it("rejects mismatched passwords and invalid phone", () => {
    expect(isValidIranMobile("0912")).toBe(false);
    expect(isValidUsername("ab")).toBe(false);
    expect(
      validateRegistration({
        firstName: "سارا",
        lastName: "محمدی",
        username: "sara.m",
        phone: "09121234567",
        email: "sara@test.com",
        job: "معلم",
        password: "secret1",
        confirmPassword: "other",
      }),
    ).toMatch(/یکسان/);
  });
});
