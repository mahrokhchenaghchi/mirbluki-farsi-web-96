/**
 * JOMA — items that are DEFINED vs UNSPECIFIED.
 * Do not invent formulas, frequencies, mood metrics, or extra features.
 */

export const UNSPECIFIED = "UNSPECIFIED" as const;
export type Unspecified = typeof UNSPECIFIED;

export const DEFINED = {
  frequencies: ["DAILY", "WEEKLY", "MONTHLY"] as const,
  planStatuses: ["DRAFT", "PLANNING", "RUNNING", "ARCHIVED"] as const,
  eventTypePerformanceRegistered: "PERFORMANCE_REGISTERED" as const,
  periodGranularity: "MONTHLY_JALALI" as const,
  performanceValue: "NUMERIC_ACTUAL" as const,
  actualAggregation: "SUM_OF_VALID_EVENTS" as const,
  weekStart: "SATURDAY" as const,
  activityCodes: "ACT001_TO_ACT045" as const,
} as const;

export const UNSPECIFIED_ITEMS = {
  achievementFormula: UNSPECIFIED,
  weightedAchievementFormula: UNSPECIFIED,
  overallSuccessFormula: UNSPECIFIED,
  moodMetrics: UNSPECIFIED,
  activityLibraryTitles: UNSPECIFIED,
  activityDataTypes: UNSPECIFIED,
  notifications: UNSPECIFIED,
  adminPanel: UNSPECIFIED,
  reasonRules: UNSPECIFIED,
  extraFrequencies: UNSPECIFIED,
} as const;

export function unspecifiedResult<T extends string>(field: T) {
  return { status: UNSPECIFIED, field } as const;
}
