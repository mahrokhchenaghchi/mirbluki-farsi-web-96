import { displayActivityTitle } from "@/domain/seed/activityCodes";
import type { ActivityDefinition } from "@/domain/types";
import { isLocalMode } from "@/lib/mode";
import { getSupabase } from "@/lib/supabase";
import { localListActivities } from "@/persistence/local/db";
import { mapActivity } from "./mappers";

export async function listLibraryActivities(): Promise<ActivityDefinition[]> {
  if (isLocalMode()) return localListActivities();
  const supabase = getSupabase();
  const { data, error } = await supabase.from("joma_activities").select("*").order("code");
  if (error) throw error;
  return (data ?? []).map(mapActivity);
}

export function activityLabel(activity: ActivityDefinition): string {
  return displayActivityTitle(activity.code, activity.title, activity.titleSpecified);
}
