import type { MoodRecord, MoodScores } from "@/domain/types";
import { isLocalMode } from "@/lib/mode";
import { localGetMood, localListMoods, localRecordMood } from "@/persistence/local/db";

export async function getMoodForDate(jalaliDate: string): Promise<MoodRecord | null> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localGetMood(jalaliDate);
}

export async function listMoodRecords(start: string, end: string): Promise<MoodRecord[]> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localListMoods(start, end);
}

export async function listMoodDates(start: string, end: string): Promise<string[]> {
  const rows = await listMoodRecords(start, end);
  return rows.map((row) => row.jalaliDate);
}

export async function recordMood(jalaliDate: string, scores: MoodScores, note: string): Promise<MoodRecord> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localRecordMood(jalaliDate, scores, note);
}
