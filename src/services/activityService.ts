import { displayActivityTitle } from "@/domain/seed/activityCodes";
import type { ActivityDefinition } from "@/domain/types";
import { getSupabase } from "@/lib/supabase";
import { mapActivity } from "./mappers";

export async function listLibraryActivities(): Promise<ActivityDefinition[]> {
  const supabase = getSupabase();
  const { data, error } = await supabase.from("joma_activities").select("*").order("code");
  if (error) throw error;
  return (data ?? []).map(mapActivity);
}

export function activityLabel(activity: ActivityDefinition): string {
  return displayActivityTitle(activity.code, activity.title, activity.titleSpecified);
}
