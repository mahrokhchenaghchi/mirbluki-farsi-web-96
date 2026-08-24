import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { Period, Plan } from "@/domain/types";
import { isLocalMode } from "@/lib/mode";
import { getSelectedPeriodKey, setSelectedPeriodKey } from "@/lib/selectedPeriod";
import { getSupabase } from "@/lib/supabase";
import { localEnsurePeriod, localGetPeriodByKey, localListPeriods } from "@/persistence/local/db";
import { mapPeriod, mapPlan } from "./mappers";

export async function ensurePeriod(periodKey: string): Promise<{ period: Period; plan: Plan }> {
  if (isLocalMode()) {
    const result = localEnsurePeriod(periodKey);
    setSelectedPeriodKey(periodKey);
    return result;
  }

  const supabase = getSupabase();
  const bounds = JomaCalendarService.periodBounds(periodKey);
  const { error: rpcError } = await supabase.rpc("joma_ensure_period", {
    p_period_key: periodKey,
    p_year: bounds.year,
    p_month: bounds.month,
    p_start_date: bounds.startDate,
    p_end_date: bounds.endDate,
  });
  if (rpcError) throw rpcError;

  const { data: periodRow, error: periodError } = await supabase
    .from("joma_periods")
    .select("*")
    .eq("period_key", periodKey)
    .single();
  if (periodError) throw periodError;

  const { data: planRow, error: planError } = await supabase
    .from("joma_plans")
    .select("*")
    .eq("period_id", periodRow.id)
    .single();
  if (planError) throw planError;

  setSelectedPeriodKey(periodKey);
  return { period: mapPeriod(periodRow), plan: mapPlan(planRow) };
}

export async function ensureCurrentPeriod(now: Date = new Date()): Promise<{ period: Period; plan: Plan }> {
  return ensurePeriod(JomaCalendarService.currentPeriodKey(now));
}

export async function ensureWorkingPeriod(periodKey?: string): Promise<{ period: Period; plan: Plan }> {
  const key = periodKey || getSelectedPeriodKey() || JomaCalendarService.currentPeriodKey();
  return ensurePeriod(key);
}

export async function listPeriods(): Promise<Array<{ period: Period; plan: Plan }>> {
  if (isLocalMode()) return localListPeriods();

  const supabase = getSupabase();
  const { data: periods, error } = await supabase
    .from("joma_periods")
    .select("*")
    .order("period_key", { ascending: false });
  if (error) throw error;

  const { data: plans, error: planError } = await supabase.from("joma_plans").select("*");
  if (planError) throw planError;

  return (periods ?? []).map((period) => {
    const plan = (plans ?? []).find((item) => item.period_id === period.id);
    if (!plan) {
      throw new Error("Period is missing its plan.");
    }
    return { period: mapPeriod(period), plan: mapPlan(plan) };
  });
}

export async function getPeriodByKey(periodKey: string): Promise<{ period: Period; plan: Plan } | null> {
  if (isLocalMode()) return localGetPeriodByKey(periodKey);

  const supabase = getSupabase();
  const { data: period, error } = await supabase
    .from("joma_periods")
    .select("*")
    .eq("period_key", periodKey)
    .maybeSingle();
  if (error) throw error;
  if (!period) return null;

  const { data: plan, error: planError } = await supabase
    .from("joma_plans")
    .select("*")
    .eq("period_id", period.id)
    .single();
  if (planError) throw planError;
  return { period: mapPeriod(period), plan: mapPlan(plan) };
}
