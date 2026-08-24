/** Official seed identifiers from the JOMA specification. Titles were not provided in-repo. */
export const ACTIVITY_CODE_COUNT = 45;

export function activityCodeAt(index: number): string {
  if (index < 1 || index > ACTIVITY_CODE_COUNT) {
    throw new Error(`Activity index out of range: ${index}`);
  }
  return `ACT${String(index).padStart(3, "0")}`;
}

export function allActivityCodes(): string[] {
  return Array.from({ length: ACTIVITY_CODE_COUNT }, (_, i) => activityCodeAt(i + 1));
}

export function isActivityCode(code: string): boolean {
  return /^ACT\d{3}$/.test(code) && allActivityCodes().includes(code);
}

export function displayActivityTitle(code: string, title: string, titleSpecified: boolean): string {
  if (titleSpecified && title.trim()) return title;
  return `فعالیت ${code}`;
}
