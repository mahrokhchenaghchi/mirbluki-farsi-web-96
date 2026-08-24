import type { Frequency, PlanStatus } from "@/domain/types";
import { cn } from "@/lib/utils";

const STATUS_LABEL: Record<PlanStatus, string> = {
  DRAFT: "پیش‌نویس",
  PLANNING: "آماده‌سازی",
  RUNNING: "در حال اجرا",
  ARCHIVED: "بایگانی",
};

const FREQUENCY_LABEL: Record<Frequency, string> = {
  DAILY: "روزانه",
  WEEKLY: "هفتگی",
  MONTHLY: "ماهانه",
};

export function StatusBadge({ status }: { status: PlanStatus }) {
  return (
    <span
      className={cn(
        "inline-flex rounded-full px-2.5 py-1 text-xs font-medium",
        status === "RUNNING" && "bg-emerald-50 text-emerald-700",
        status === "DRAFT" && "bg-slate-100 text-slate-600",
        status === "PLANNING" && "bg-sky-50 text-sky-700",
        status === "ARCHIVED" && "bg-amber-50 text-amber-800",
      )}
    >
      {STATUS_LABEL[status]}
    </span>
  );
}

export function FrequencyBadge({ frequency }: { frequency: Frequency }) {
  return (
    <span className="inline-flex rounded-full bg-secondary px-2.5 py-1 text-xs text-secondary-foreground">
      {FREQUENCY_LABEL[frequency]}
    </span>
  );
}

export { STATUS_LABEL, FREQUENCY_LABEL };
