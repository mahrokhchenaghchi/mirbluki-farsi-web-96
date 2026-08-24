import { isLocalMode } from "@/lib/mode";
import { localRebuildProjection } from "@/persistence/local/db";
import type { ReportProjection } from "@/reporting/types";

export async function rebuildAndStoreProjection(planId: string): Promise<ReportProjection> {
  if (!isLocalMode()) throw new Error("حالت فعلی فقط Local/Test است.");
  return localRebuildProjection(planId);
}

export async function loadReport(planId: string): Promise<ReportProjection> {
  return rebuildAndStoreProjection(planId);
}
