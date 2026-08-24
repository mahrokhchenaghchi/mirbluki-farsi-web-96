import JomaCalendarService from "@/calendar/JomaCalendarService";
import { allActivityCodes, displayActivityTitle } from "@/domain/seed/activityCodes";
import { canRegisterPerformance } from "@/domain/rules/performanceRules";
import { canEditPlan, canTransition, validatePlanActivityInput } from "@/domain/rules/planRules";
import { activityLabel } from "@/services/activityService";
import type {
  ActivityDefinition,
  Frequency,
  MoodRecord,
  Period,
  PerformanceEvent,
  Plan,
  PlanActivity,
  PlanStatus,
} from "@/domain/types";
import { JomaError } from "@/lib/errors";
import { UNSPECIFIED } from "@/domain/unspecified";
import { buildReportProjection } from "@/reporting/engine";
import type { ReportProjection } from "@/reporting/types";

const STORAGE_KEY = "joma.local.db.v1";

export interface LocalUser {
  id: string;
  email: string;
  passwordHash: string;
  createdAt: string;
}

interface LocalState {
  users: LocalUser[];
  session: { userId: string; email: string } | null;
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
    activities: allActivityCodes().map((code) => ({
      id: `activity-${code.toLowerCase()}`,
      code,
      title: code,
      description: null,
      titleSpecified: false,
    })),
    periods: [],
    plans: [],
    planActivities: [],
    events: [],
    moods: [],
    projections: [],
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
    if (!parsed.activities?.length) parsed.activities = emptyState().activities;
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

async function hashPassword(password: string): Promise<string> {
  const bytes = new TextEncoder().encode(`joma-local:${password}`);
  const digest = await crypto.subtle.digest("SHA-256", bytes);
  return Array.from(new Uint8Array(digest))
    .map((value) => value.toString(16).padStart(2, "0"))
    .join("");
}

function requireUser(state: LocalState): { userId: string; email: string } {
  if (!state.session) {
    throw new JomaError("UNAUTHENTICATED", "نشست شما منقضی شده است. دوباره وارد شوید.");
  }
  return state.session;
}

export async function localSignUp(email: string, password: string): Promise<LocalUser> {
  const state = load();
  const normalized = email.trim().toLowerCase();
  if (state.users.some((user) => user.email === normalized)) {
    throw new JomaError("ACCOUNT_EXISTS", "این ایمیل قبلاً ثبت شده است. وارد شوید.");
  }
  const user: LocalUser = {
    id: id("user"),
    email: normalized,
    passwordHash: await hashPassword(password),
    createdAt: new Date().toISOString(),
  };
  state.users.push(user);
  state.session = { userId: user.id, email: user.email };
  save(state);
  return user;
}

export async function localSignIn(email: string, password: string): Promise<LocalUser> {
  const state = load();
  const normalized = email.trim().toLowerCase();
  const user = state.users.find((item) => item.email === normalized);
  const passwordHash = await hashPassword(password);
  if (!user || user.passwordHash !== passwordHash) {
    throw new JomaError("INVALID_LOGIN", "ایمیل یا رمز عبور نادرست است.");
  }
  state.session = { userId: user.id, email: user.email };
  save(state);
  return user;
}

export function localSignOut() {
  const state = load();
  state.session = null;
  save(state);
}

export function localGetSession(): { userId: string; email: string } | null {
  return load().session;
}

export function localListActivities(): ActivityDefinition[] {
  return load().activities.slice().sort((a, b) => a.code.localeCompare(b.code));
}

export function localEnsurePeriod(periodKey: string): { period: Period; plan: Plan } {
  const state = load();
  const { userId } = requireUser(state);
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
  const { userId } = requireUser(state);
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
  const { userId } = requireUser(state);
  const period = state.periods.find((item) => item.userId === userId && item.periodKey === periodKey);
  if (!period) return null;
  const plan = state.plans.find((item) => item.userId === userId && item.periodId === period.id);
  if (!plan) return null;
  return { period, plan };
}

export function localGetPlan(planId: string): Plan {
  const state = load();
  const { userId } = requireUser(state);
  const plan = state.plans.find((item) => item.id === planId && item.userId === userId);
  if (!plan) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  return plan;
}

export function localListPlanActivities(planId: string): PlanActivity[] {
  const state = load();
  const { userId } = requireUser(state);
  return state.planActivities
    .filter((item) => item.planId === planId && item.userId === userId)
    .sort((a, b) => a.snapshotAt.localeCompare(b.snapshotAt));
}

export function localAddPlanActivity(input: {
  plan: Plan;
  activity: ActivityDefinition;
  frequency: Frequency;
  targetValue: number;
  weight: number;
}): PlanActivity {
  const state = load();
  const { userId } = requireUser(state);
  const plan = state.plans.find((item) => item.id === input.plan.id && item.userId === userId);
  if (!plan) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const editable = canEditPlan(plan.status);
  if (!editable.ok) throw new JomaError(editable.code, editable.message);
  const valid = validatePlanActivityInput(input);
  if (!valid.ok) throw new JomaError(valid.code, valid.message);
  if (state.planActivities.some((item) => item.planId === plan.id && item.activityId === input.activity.id)) {
    throw new JomaError("DUPLICATE_ACTIVITY", "این فعالیت قبلاً به برنامه اضافه شده است.");
  }
  const now = new Date().toISOString();
  const row: PlanActivity = {
    id: id("pa"),
    userId,
    planId: plan.id,
    periodKey: plan.periodKey,
    activityId: input.activity.id,
    activityCode: input.activity.code,
    title: displayActivityTitle(input.activity.code, input.activity.title, input.activity.titleSpecified),
    description: input.activity.description,
    frequency: input.frequency,
    targetValue: input.targetValue,
    weight: input.weight,
    snapshotAt: now,
  };
  state.planActivities.push(row);
  save(state);
  return row;
}

export function localRemovePlanActivity(plan: Plan, planActivityId: string) {
  const state = load();
  const { userId } = requireUser(state);
  const current = state.plans.find((item) => item.id === plan.id && item.userId === userId);
  if (!current) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const editable = canEditPlan(current.status);
  if (!editable.ok) throw new JomaError(editable.code, editable.message);
  state.planActivities = state.planActivities.filter((item) => !(item.id === planActivityId && item.userId === userId));
  save(state);
}

export function localTransitionPlan(plan: Plan, next: PlanStatus): Plan {
  const state = load();
  const { userId } = requireUser(state);
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
  const { userId } = requireUser(state);
  return state.events
    .filter((item) => item.planId === planId && item.userId === userId)
    .sort((a, b) => a.performanceDate.localeCompare(b.performanceDate) || a.createdAt.localeCompare(b.createdAt));
}

export function localListEventsForActivity(planActivityId: string): PerformanceEvent[] {
  const state = load();
  const { userId } = requireUser(state);
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
  const { userId } = requireUser(state);
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
  const { userId } = requireUser(state);
  return state.moods.find((item) => item.userId === userId && item.jalaliDate === jalaliDate) ?? null;
}

export function localListMoodDates(start: string, end: string): string[] {
  const state = load();
  const { userId } = requireUser(state);
  return state.moods
    .filter((item) => item.userId === userId && item.jalaliDate >= start && item.jalaliDate <= end)
    .map((item) => item.jalaliDate);
}

export function localRecordMood(jalaliDate: string): MoodRecord {
  const state = load();
  const { userId } = requireUser(state);
  const existing = state.moods.find((item) => item.userId === userId && item.jalaliDate === jalaliDate);
  if (existing) return existing;
  const row: MoodRecord = {
    id: id("mood"),
    userId,
    jalaliDate,
    metrics: {},
    metricsStatus: UNSPECIFIED,
    createdAt: new Date().toISOString(),
  };
  state.moods.push(row);
  save(state);
  return row;
}

export function localRebuildProjection(planId: string): ReportProjection {
  const state = load();
  const { userId } = requireUser(state);
  const plan = state.plans.find((item) => item.id === planId && item.userId === userId);
  if (!plan) throw new JomaError("MISSING_PLAN", "برنامه پیدا نشد.");
  const activities = state.planActivities.filter((item) => item.planId === planId && item.userId === userId);
  const events = state.events.filter((item) => item.planId === planId && item.userId === userId);
  const bounds = JomaCalendarService.periodBounds(plan.periodKey);
  const moodDates = state.moods
    .filter((item) => item.userId === userId && item.jalaliDate >= bounds.startDate && item.jalaliDate <= bounds.endDate)
    .map((item) => item.jalaliDate);

  const projection = buildReportProjection({
    periodKey: plan.periodKey,
    planId: plan.id,
    planStatus: plan.status,
    activities: activities.map((item) => ({
      id: item.id,
      activityCode: item.activityCode,
      title: item.title,
      frequency: item.frequency,
      targetValue: item.targetValue,
      weight: item.weight,
    })),
    events: events.map((item) => ({
      id: item.id,
      planActivityId: item.planActivityId,
      performanceDate: item.performanceDate,
      actualValue: item.actualValue,
      createdAt: item.createdAt,
    })),
    moodDates,
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
