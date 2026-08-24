import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { Period, Plan } from "@/domain/types";
import { EmptyState } from "@/components/joma/EmptyState";
import { LoadingState } from "@/components/joma/LoadingState";
import { StatusBadge } from "@/components/joma/StatusBadge";
import { ensureCurrentPeriod, listPeriods } from "@/services/periodService";
import { toUserMessage } from "@/lib/errors";

export default function PeriodsPage() {
  const [items, setItems] = useState<Array<{ period: Period; plan: Plan }>>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    ensureCurrentPeriod()
      .then(() => listPeriods())
      .then(setItems)
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingState />;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-black">دوره‌های من</h1>
        <p className="mt-2 text-sm text-muted-foreground">هر دوره تاریخچه مستقل خودش را حفظ می‌کند.</p>
      </div>
      {error && <p className="text-sm text-destructive">{error}</p>}
      {items.length === 0 ? (
        <EmptyState title="دوره‌ای نیست" description="هنوز دوره‌ای ساخته نشده است." />
      ) : (
        <div className="space-y-3">
          {items.map(({ period, plan }) => (
            <Link
              key={period.id}
              to={`/app/periods/${period.periodKey}`}
              className="flex items-center justify-between rounded-2xl border bg-card p-5"
            >
              <div>
                <div className="text-lg font-bold">{JomaCalendarService.formatPeriodLabel(period.periodKey)}</div>
                <div className="mt-1 text-xs text-muted-foreground" dir="ltr">
                  {period.startDate} — {period.endDate}
                </div>
              </div>
              <StatusBadge status={plan.status} />
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
