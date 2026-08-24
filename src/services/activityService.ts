import type { ActivityDefinition } from "@/domain/types";
import { isLocalMode } from "@/lib/mode";
import {
  localCreateActivity,
  localDeleteActivity,
  localListActivities,
  localUpdateActivity,
} from "@/persistence/local/db";

export async function listLibraryActivities(): Promise<ActivityDefinition[]> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localListActivities();
}

export async function createLibraryActivity(
  input: Omit<ActivityDefinition, "id" | "userId" | "code" | "isSeed" | "createdAt" | "updatedAt">,
): Promise<ActivityDefinition> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localCreateActivity(input);
}

export async function updateLibraryActivity(id: string, patch: Partial<ActivityDefinition>): Promise<ActivityDefinition> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localUpdateActivity(id, patch);
}

export async function deleteLibraryActivity(id: string): Promise<void> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  localDeleteActivity(id);
}
