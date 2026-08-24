const KEY = "joma.ui.selectedPeriodKey";

export function getSelectedPeriodKey(): string | null {
  if (typeof window === "undefined") return null;
  return window.localStorage.getItem(KEY);
}

export function setSelectedPeriodKey(periodKey: string) {
  if (typeof window === "undefined") return;
  window.localStorage.setItem(KEY, periodKey);
}
