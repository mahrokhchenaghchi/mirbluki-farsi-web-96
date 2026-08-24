import { UNSPECIFIED } from "@/domain/unspecified";
import type { MoodRecord } from "@/domain/types";
import { isLocalMode } from "@/lib/mode";
import { getSupabase } from "@/lib/supabase";
import { localGetMood, localListMoodDates, localRecordMood } from "@/persistence/local/db";
import { mapMood } from "./mappers";

export async function getMoodForDate(jalaliDate: string): Promise<MoodRecord | null> {
  if (isLocalMode()) return localGetMood(jalaliDate);
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_mood_records")
    .select("*")
    .eq("jalali_date", jalaliDate)
    .maybeSingle();
  if (error) throw error;
  return data ? mapMood(data) : null;
}

export async function listMoodDates(periodStart: string, periodEnd: string): Promise<string[]> {
  if (isLocalMode()) return localListMoodDates(periodStart, periodEnd);
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_mood_records")
    .select("jalali_date")
    .gte("jalali_date", periodStart)
    .lte("jalali_date", periodEnd);
  if (error) throw error;
  return (data ?? []).map((row) => row.jalali_date);
}

export async function listMoodMetricDefinitions() {
  if (isLocalMode()) return [];
  const supabase = getSupabase();
  const { data, error } = await supabase
    .from("joma_mood_metric_definitions")
    .select("*")
    .order("sort_order");
  if (error) throw error;
  return data ?? [];
}

export async function recordUnspecifiedMoodCheckIn(jalaliDate: string): Promise<MoodRecord> {
  if (isLocalMode()) return localRecordMood(jalaliDate);

  const supabase = getSupabase();
  const { data: userData, error: userError } = await supabase.auth.getUser();
  if (userError) throw userError;
  if (!userData.user) throw new Error("not authenticated");

  const { data, error } = await supabase
    .from("joma_mood_records")
    .upsert(
      {
        user_id: userData.user.id,
        jalali_date: jalaliDate,
        metrics: {},
        metrics_status: UNSPECIFIED,
      },
      { onConflict: "user_id,jalali_date" },
    )
    .select("*")
    .single();
  if (error) throw error;
  return mapMood(data);
}
