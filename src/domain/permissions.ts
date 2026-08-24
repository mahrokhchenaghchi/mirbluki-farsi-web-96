export const PERMISSIONS = [
  "VIEW_DASHBOARD",
  "CREATE_PLAN",
  "EDIT_PLAN",
  "RECORD_PERFORMANCE",
  "VIEW_REPORT",
  "VIEW_HISTORY",
  "MANAGE_ACTIVITY_LIBRARY",
  "MANAGE_USERS",
  "ADMIN_ACCESS",
] as const;

export type Permission = (typeof PERMISSIONS)[number];

export type AccessLevel = 1 | 2 | 3 | 4;
export type UserRole = "member" | "plus" | "coach" | "admin";

export const ROLE_LEVEL: Record<UserRole, AccessLevel> = {
  member: 1,
  plus: 2,
  coach: 3,
  admin: 4,
};

export const ROLE_PERMISSIONS: Record<UserRole, Permission[]> = {
  member: [
    "VIEW_DASHBOARD",
    "CREATE_PLAN",
    "EDIT_PLAN",
    "RECORD_PERFORMANCE",
    "VIEW_REPORT",
    "VIEW_HISTORY",
    "MANAGE_ACTIVITY_LIBRARY",
  ],
  plus: [
    "VIEW_DASHBOARD",
    "CREATE_PLAN",
    "EDIT_PLAN",
    "RECORD_PERFORMANCE",
    "VIEW_REPORT",
    "VIEW_HISTORY",
    "MANAGE_ACTIVITY_LIBRARY",
  ],
  coach: [
    "VIEW_DASHBOARD",
    "CREATE_PLAN",
    "EDIT_PLAN",
    "RECORD_PERFORMANCE",
    "VIEW_REPORT",
    "VIEW_HISTORY",
    "MANAGE_ACTIVITY_LIBRARY",
  ],
  admin: [...PERMISSIONS],
};

export const ACCESS_LEVEL_LABEL: Record<AccessLevel, string> = {
  1: "سطح ۱ — عضو",
  2: "سطح ۲ — پلاس",
  3: "سطح ۳ — مربی",
  4: "مدیر",
};

export function hasPermission(role: UserRole, permission: Permission): boolean {
  return ROLE_PERMISSIONS[role].includes(permission);
}
