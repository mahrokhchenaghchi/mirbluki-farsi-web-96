import JomaCalendarService from "@/calendar/JomaCalendarService";
import { targetOf } from "@/domain/catalog";
import { canRegisterPerformance } from "@/domain/rules/performanceRules";
import { canEditPlan, canTransition } from "@/domain/rules/planRules";
import { buildSeedActivities } from "@/domain/seed/activityLibrary.seed";
import type {
  ActivityDefinition,
  Frequency,
  JomaUser,
  MoodRecord,
  MoodScores,
  Period,
  PerformanceEvent,
  Plan,
  PlanActivity,
  PlanStatus,
} from "@/domain/types";
import { JomaError } from "@/lib/errors";
import { buildReportProjection } from "@/reporting/engine";
import type { ReportProjection } from "@/reporting/types";

const STORAGE_KEY = "joma.local.db.v2";

interface LocalState {
  users: JomaUser[];
  session: { userId: string } | null;
  activities: ActivityDefinition[];
  periods: Period[];
  plans: Plan[];
  planActivities: PlanActivity[];
  events: PerformanceEvent[];
  moods: MoodRecord[];
  projections: Array<{
    planId: string;
    userId: string;
    periodKey: string;
    payload: ReportProjection;
    sourceEventCount: number;
    generatedAt: string;
  }>;
}

function emptyState(): LocalState {
  return {
    users: [],
    session: null,
    activities: [],
    periods: [],
    plans: [],
    planActivities: [],
    events: [],
    moods: [],
    projections: [],
  };
}

function migrateUser(user: JomaUser & { fullName?: string }): JomaUser {
  const firstName = user.firstName || (user.fullName ? user.fullName.split(" ")[0] : "");
  const lastName = user.lastName || (user.fullName ? user.fullName.split(" ").slice(1).join(" ") : "");
  return {
    ...user,
    firstName,
    lastName,
    fullName: `${firstName} ${lastName}`.trim() || user.fullName || user.username,
    role: user.role || "member",
    accessLevel: user.accessLevel || 1,
    preferences: user.preferences || { compactCards: false, notificationsEnabled: false },
  };
}

function load(): LocalState {
  if (typeof window === "undefined") return emptyState();
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      const initial = emptyState();
      save(initial);
      return initial;
    }
    const parsed = JSON.parse(raw) as LocalState;
    parsed.users = (parsed.users ?? []).map((user) => migrateUser(user));
    parsed.activities = (parsed.activities ?? []).map((item) => ({ ...item, status: item.status || "ACTIVE" }));
    parsed.planActivities = (parsed.planActivities ?? []).map((item, index) => ({
      ...item,
      sortOrder: item.sortOrder ?? index,
    }));
    return parsed;
  } catch {
    const initial = emptyState();
    save(initial);
    return initial;
  }
}

function save(state: LocalState) {
  if (typeof window === "undefined") return;
  window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
}

function id(prefix: string): string {
  return `${prefix}-${crypto.randomUUID()}`;
}

export async function hashPassword(password: string): Promise<string> {
  const bytes = new TextEncoder().encode(`joma-local:${password}`);
  const digest = await crypto.subtle.digest("SHA-256", bytes);
  return Array.from(new Uint8Array(digest))
    .map((value) => value.toString(16).padStart(2, "0"))
    .join("");
}

function requireUser(state: LocalState): string {
  if (!state.session) throw new JomaError("UNAUTHENTICATED", "نشست شما منقضی شده است. دوباره وارد شوید.");
  return state.session.userId;
}

function ensureLibrary(state: LocalState, userId: string) {
  if (!state.activities.some((item) => item.userId === userId && item.isSeed)) {
    state.activities.push(...buildSeedActivities(userId));
  }
}

export function localUsernameAvailable(username: string): boolean {
  const state = load();
  const key = username.trim().toLowerCase();
  return !state.users.some((user) => user.username === key);
}

export async function localSignUp(input: {
  firstName: string;
  lastName: string;
  username: string;
  phone: string;
  email: string;
  job: string;
  password: string;
}): Promise<JomaUser> {
  const state = load();
  const email = input.email.trim().toLowerCase();
  const username = input.username.trim().toLowerCase();
  if (state.users.some((user) => user.email === email)) {
    throw new JomaError("ACCOUNT_EXISTS", "این ایمیل قبلاً ثبت شده است.");
  }
  if (state.users.some((user) => user.username === username)) {
    throw new JomaError("USERNAME_EXISTS", "این نام کاربری قبلاً گرفته شده است.");
  }
  const firstName = input.firstName.trim();
  const lastName = input.lastName.trim();
  const user: JomaUser = {
    id: id("user"),
    firstName,
    lastName,
    fullName: `${firstName} ${lastName}`.trim(),
    username,
    phone: input.phone.trim(),
    email,
    job: input.job.trim(),
    role: "member",
    accessLevel: 1,
    passwordHash: await hashPassword(input.password),
    createdAt: new Date().toISOString(),
    preferences: { compactCards: false, notificationsEnabled: false },
  };
  state.users.push(user);
  state.session = { userId: user.id };
  ensureLibrary(state, user.id);
  save(state);
  return user;
}

export async function localSignIn(identifier: string, password: string): Promise<JomaUser> {
  const state = load();
  const key = identifier.trim().toLowerCase();
  const user = state.users.find((item) => item.email === key || item.username === key);
  const passwordHash = await hashPassword(password);
  if (!user || user.passwordHash !== passwordHash) {
    throw new JomaError("INVALID_LOGIN", "نام کاربری/ایمیل یا رمز عبور نادرست است.");
  }
  state.session = { userId: user.id };
  ensureLibrary(state, user.id);
  save(state);
  return user;
}

export async function localResetPassword(identifier: string, password: string): Promise<void> {
  const state = load();
  const key = identifier.trim().toLowerCase();
  const user = state.users.find((item) => item.email === key || item.username === key);
  if (!user) throw new JomaError("NOT_FOUND", "حسابی با این مشخصات پیدا نشد.");
  user.passwordHash = await hashPassword(password);
  save(state);
}

export function localSignOut() {
  const state = load();
  state.session = null;
  save(state);
}

export function localGetSessionUser(): JomaUser | null {
  const state = load();
  if (!state.session) return null;
  const user = state.users.find((item) => item.id === state.session?.userId);
  return user ? migrateUser(user) : null;
}

export function localUpdateProfile(patch: Partial<Pick<JomaUser, "firstName" | "lastName" | "phone" | "job" | "preferences">>): JomaUser {
  const state = load();
  const userId = requireUser(state);
  const user = state.users.find((item) => item.id === userId);
  if (!user) throw new JomaError("NOT_FOUND", "کاربر پیدا نشد.");
  Object.assign(user, patch);
  user.fullName = `${user.firstName} ${user.lastName}`.trim();
  save(state);
  return migrateUser(user);
}

export function localListActivities(): ActivityDefinition[] {
  const state = load();
  const userId = requireUser(state);
  ensureLibrary(state, userId);
  save(state);
  return state.activities
    .filter((item) => item.userId === userId)
    .sort((a, b) => a.code.localeCompare(b.code, "en"));
}

export function localCreateActivity(input: Omit<ActivityDefinition, "id" | "userId" | "code" | "isSeed" | "createdAt" | "updatedAt">): ActivityDefinition {
  const state = load();
  const userId = requireUser(state);
  const existing = state.activities.filter((item) => item.userId === userId);
  const next = existing.length + 1;
  const now = new Date().toISOString();
  const row: ActivityDefinition = {
    ...input,
    id: id("activity"),
    userId,
    code: `ACT${String(next).padStart(3, "0")}`,
    status: input.status ?? "ACTIVE",
    isSeed: false,
    createdAt: now,
    updatedAt: now,
  };
  state.activities.push(row);
  save(state);
  return row;
}

export function localUpdateActivity(activityId: string, patch: Partial<ActivityDefinition>): ActivityDefinition {
  const state = load();
  const userId = requireUser(state);
  const row = state.activities.find((item) => item.id === activityId && item.userId === userId);
  if (!row) throw new JomaError("NOT_FOUND", "فعالیت پیدا نشد.");
  Object.assign(row, patch, { id: row.id, userId, code: row.code, isSeed: row.isSeed, updatedAt: new Date().toISOString() });
  save(state);
  return row;
}

export function localDeleteActivity(activityId: string) {
  const state = load();
  const userId = requireUser(state);
  state.activities = state.activities.filter((item) => !(item.id === activityId && item.userId === userId));
  save(state);
}

export function localEnsurePeriod(periodKey: string): { period: Period; plan: Plan } {
  const state = load();
  const userId = requireUser(state);
  ensureLibrary(state, userId);
  const bounds = JomaCalendarService.periodBounds(periodKey);
  let period = state.periods.find((item) => item.userId === userId && item.periodKey === periodKey);
  if (!period) {
    period = {
      id: id("period"),
      userId,
      periodKey,
      year: bounds.year,
      month: bounds.month,
      startDate: bounds.startDate,
      endDate: bounds.endDate,
    };
    state.periods.push(period);
  }
  let plan = state.plans.find((item) => item.userId === userId && item.periodId === period.id);
  if (!plan) {
    const now = new Date().toISOString();
    plan = {
      id: id("plan"),
      userId,
      periodId: period.id,
      periodKey,
      status: "DRAFT",
      createdAt: now,
      updatedAt: now,
      finalizedAt: null,
      startedAt: null,
      archivedAt: null,
    };
    state.plans.push(plan);
  }
  save(state);
  return { period, plan };
}

export function localListPeriods(): Array<{ period: Period; plan: Plan }> {
  const state = load();
  const userId = requireUser(state);
  return state.periods
    .filter((period) => period.userId === userId)
    .sort((a, b) => b.periodKey.localeCompare(a.periodKey))
    .map((period) => {
      const plan = state.plans.find((item) => item.userId === userId && item.periodId === period.id);
      if (!plan) throw new JomaError("MISSING_PLAN", "برنامه این دوره پیدا نشد.");
      return { period, plan };
    });
}

export function localGetPeriodByKey(periodKey: string): { period: Period; plan: Plan } | null {
  const state = load();
  const userId = requireUser(state);
  const period = state.periods.find((item) => item.userId === userId && item.periodKey === periodKey);
  if (!period) return null;
  const plan = state.plans.find((item) => item.userId === userId && item.periodId === period.id);
  if (!plan) return null;
  return { period, plan };
}

export function localGetPlan(planId: string): Plan {
  const state = load();
  const userId = requireUser(state);
  const plan = state.plans.find((item) => item.id === planId && item.userId === userId);
  if (!plan) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  return plan;
}

export function localListPlanActivities(planId: string): PlanActivity[] {
  const state = load();
  const userId = requireUser(state);
  return state.planActivities
    .filter((item) => item.planId === planId && item.userId === userId)
    .sort((a, b) => (a.sortOrder ?? 0) - (b.sortOrder ?? 0) || a.snapshotAt.localeCompare(b.snapshotAt));
}

export function localAddPlanActivity(
  plan: Plan,
  activity: ActivityDefinition,
  overrides?: { frequency?: Frequency; targetValue?: number; weight?: number },
): PlanActivity {
  const state = load();
  const userId = requireUser(state);
  const current = state.plans.find((item) => item.id === plan.id && item.userId === userId);
  if (!current) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const editable = canEditPlan(current.status);
  if (!editable.ok) throw new JomaError(editable.code, editable.message);
  if (state.planActivities.some((item) => item.planId === current.id && item.activityId === activity.id)) {
    throw new JomaError("DUPLICATE_ACTIVITY", "این فعالیت قبلاً به برنامه اضافه شده است.");
  }
  const now = new Date().toISOString();
  const frequency = overrides?.frequency ?? activity.frequency;
  const siblings = state.planActivities.filter((item) => item.planId === current.id);
  const row: PlanActivity = {
    id: id("pa"),
    userId,
    planId: current.id,
    periodKey: current.periodKey,
    activityId: activity.id,
    activityCode: activity.code,
    name: activity.name,
    category: activity.category,
    frequency,
    dataType: activity.dataType,
    dailyTarget: activity.dailyTarget,
    weeklyTarget: activity.weeklyTarget,
    monthlyTarget: activity.monthlyTarget,
    targetValue: overrides?.targetValue ?? targetOf({ ...activity, frequency }),
    weight: overrides?.weight ?? activity.weight,
    sticker: activity.sticker,
    color: activity.color,
    sortOrder: siblings.length,
    snapshotAt: now,
  };
  state.planActivities.push(row);
  save(state);
  return row;
}

export function localUpdatePlanActivity(
  plan: Plan,
  planActivityId: string,
  patch: Partial<Pick<PlanActivity, "frequency" | "targetValue" | "weight" | "sortOrder">>,
): PlanActivity {
  const state = load();
  const userId = requireUser(state);
  const current = state.plans.find((item) => item.id === plan.id && item.userId === userId);
  if (!current) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const editable = canEditPlan(current.status);
  if (!editable.ok) throw new JomaError(editable.code, editable.message);
  const row = state.planActivities.find((item) => item.id === planActivityId && item.userId === userId);
  if (!row) throw new JomaError("NOT_FOUND", "فعالیت برنامه پیدا نشد.");
  Object.assign(row, patch);
  save(state);
  return row;
}

export function localRemovePlanActivity(plan: Plan, planActivityId: string) {
  const state = load();
  const userId = requireUser(state);
  const current = state.plans.find((item) => item.id === plan.id && item.userId === userId);
  if (!current) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const editable = canEditPlan(current.status);
  if (!editable.ok) throw new JomaError(editable.code, editable.message);
  state.planActivities = state.planActivities.filter((item) => !(item.id === planActivityId && item.userId === userId));
  save(state);
}

export function localTransitionPlan(plan: Plan, next: PlanStatus): Plan {
  const state = load();
  const userId = requireUser(state);
  const current = state.plans.find((item) => item.id === plan.id && item.userId === userId);
  if (!current) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const allowed = canTransition(current.status, next);
  if (!allowed.ok) throw new JomaError(allowed.code, allowed.message);
  const now = new Date().toISOString();
  current.status = next;
  current.updatedAt = now;
  if (next === "PLANNING") current.finalizedAt = now;
  if (next === "RUNNING") current.startedAt = now;
  if (next === "ARCHIVED") current.archivedAt = now;
  save(state);
  return current;
}

export function localListEventsForPlan(planId: string): PerformanceEvent[] {
  const state = load();
  const userId = requireUser(state);
  return state.events
    .filter((item) => item.planId === planId && item.userId === userId)
    .sort((a, b) => a.performanceDate.localeCompare(b.performanceDate) || a.createdAt.localeCompare(b.createdAt));
}

export function localListEventsForActivity(planActivityId: string): PerformanceEvent[] {
  const state = load();
  const userId = requireUser(state);
  return state.events
    .filter((item) => item.planActivityId === planActivityId && item.userId === userId)
    .sort((a, b) => a.performanceDate.localeCompare(b.performanceDate) || a.createdAt.localeCompare(b.createdAt));
}

export function localRegisterPerformance(input: {
  plan: Plan;
  planActivity: PlanActivity;
  performanceDate: string;
  actualValue: number;
}): string {
  const state = load();
  const userId = requireUser(state);
  const plan = state.plans.find((item) => item.id === input.plan.id && item.userId === userId);
  const planActivity = state.planActivities.find((item) => item.id === input.planActivity.id && item.userId === userId);
  if (!plan || !planActivity) throw new JomaError("MISSING_PLAN_ACTIVITY", "فعالیت برنامه پیدا نشد.");
  const existing = state.events.filter((item) => item.planActivityId === planActivity.id && item.userId === userId);
  const decision = canRegisterPerformance({
    plan,
    planActivity,
    performanceDate: input.performanceDate,
    actualValue: input.actualValue,
    existingEvents: existing,
  });
  if (!decision.ok) throw new JomaError(decision.code, decision.message);
  const event: PerformanceEvent = {
    id: id("evt"),
    userId,
    planId: plan.id,
    planActivityId: planActivity.id,
    periodKey: plan.periodKey,
    frequency: planActivity.frequency,
    dataType: planActivity.dataType,
    eventType: "PERFORMANCE_REGISTERED",
    performanceDate: input.performanceDate,
    actualValue: input.actualValue,
    createdAt: new Date().toISOString(),
  };
  state.events.push(event);
  save(state);
  localRebuildProjection(plan.id);
  return event.id;
}

export function localGetMood(jalaliDate: string): MoodRecord | null {
  const state = load();
  const userId = requireUser(state);
  return state.moods.find((item) => item.userId === userId && item.jalaliDate === jalaliDate) ?? null;
}

export function localListMoods(start: string, end: string): MoodRecord[] {
  const state = load();
  const userId = requireUser(state);
  return state.moods
    .filter((item) => item.userId === userId && item.jalaliDate >= start && item.jalaliDate <= end)
    .sort((a, b) => a.jalaliDate.localeCompare(b.jalaliDate));
}

export function localRecordMood(jalaliDate: string, scores: MoodScores, note: string): MoodRecord {
  const state = load();
  const userId = requireUser(state);
  const existing = state.moods.find((item) => item.userId === userId && item.jalaliDate === jalaliDate);
  if (existing) {
    existing.scores = scores;
    existing.note = note;
    save(state);
    return existing;
  }
  const row: MoodRecord = {
    id: id("mood"),
    userId,
    jalaliDate,
    scores,
    note,
    createdAt: new Date().toISOString(),
  };
  state.moods.push(row);
  save(state);
  return row;
}

export function localRebuildProjection(planId: string): ReportProjection {
  const state = load();
  const userId = requireUser(state);
  const plan = state.plans.find((item) => item.id === planId && item.userId === userId);
  if (!plan) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const activities = state.planActivities.filter((item) => item.planId === planId && item.userId === userId);
  const events = state.events.filter((item) => item.planId === planId && item.userId === userId);
  const bounds = JomaCalendarService.periodBounds(plan.periodKey);
  const moods = state.moods.filter(
    (item) => item.userId === userId && item.jalaliDate >= bounds.startDate && item.jalaliDate <= bounds.endDate,
  );

  const projection = buildReportProjection({
    periodKey: plan.periodKey,
    planId: plan.id,
    planStatus: plan.status,
    activities: activities.map((item) => ({
      id: item.id,
      activityCode: item.activityCode,
      title: item.name,
      name: item.name,
      category: item.category,
      frequency: item.frequency,
      dataType: item.dataType,
      targetValue: item.targetValue,
      weight: item.weight,
      sticker: item.sticker,
      color: item.color,
    })),
    events: events.map((item) => ({
      id: item.id,
      planActivityId: item.planActivityId,
      performanceDate: item.performanceDate,
      actualValue: item.actualValue,
      createdAt: item.createdAt,
    })),
    moods: moods.map((item) => ({
      jalaliDate: item.jalaliDate,
      scores: item.scores,
      note: item.note,
    })),
  });

  const existing = state.projections.find((item) => item.planId === plan.id && item.userId === userId);
  if (existing) {
    existing.payload = projection;
    existing.sourceEventCount = projection.sourceEventCount;
    existing.generatedAt = projection.generatedAt;
    existing.periodKey = plan.periodKey;
  } else {
    state.projections.push({
      planId: plan.id,
      userId,
      periodKey: plan.periodKey,
      payload: projection,
      sourceEventCount: projection.sourceEventCount,
      generatedAt: projection.generatedAt,
    });
  }
  save(state);
  return projection;
}

export type { Frequency };
