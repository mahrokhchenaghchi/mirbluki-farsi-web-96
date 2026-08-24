import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { Period, Plan } from "@/domain/types";
import { EmptyState } from "@/components/joma/EmptyState";
import { LoadingState } from "@/components/joma/LoadingState";
import { StatusBadge } from "@/components/joma/StatusBadge";
import { ensureCurrentPeriod, ensurePeriod, listPeriods } from "@/services/periodService";
import { setSelectedPeriodKey } from "@/lib/selectedPeriod";
import { toUserMessage } from "@/lib/errors";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

export default function PeriodsPage() {
  const navigate = useNavigate();
  const today = JomaCalendarService.todayJalali();
  const [items, setItems] = useState<Array<{ period: Period; plan: Plan }>>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [year, setYear] = useState(String(today.year));
  const [month, setMonth] = useState(String(today.month).padStart(2, "0"));
  const [busy, setBusy] = useState(false);

  const reload = async () => {
    await ensureCurrentPeriod();
    setItems(await listPeriods());
  };

  useEffect(() => {
    reload()
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, []);

  const createPeriod = async (event: React.FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setError(null);
    try {
      const periodKey = `${year}-${month}`;
      await ensurePeriod(periodKey);
      await reload();
    } catch (err) {
      setError(toUserMessage(err));
    } finally {
      setBusy(false);
    }
  };

  if (loading) return <LoadingState />;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-black">دوره‌های من</h1>
        <p className="mt-2 text-sm text-muted-foreground">هر دوره تاریخچه مستقل خودش را حفظ می‌کند.</p>
      </div>

      <form className="grid gap-3 rounded-2xl border bg-card p-5 md:grid-cols-3" onSubmit={createPeriod}>
        <div className="space-y-2">
          <Label>سال شمسی</Label>
          <Input dir="ltr" value={year} onChange={(e) => setYear(e.target.value)} />
        </div>
        <div className="space-y-2">
          <Label>ماه</Label>
          <select
            className="h-10 w-full rounded-md border bg-background px-3 text-sm"
            value={month}
            onChange={(e) => setMonth(e.target.value)}
          >
            {Array.from({ length: 12 }, (_, index) => {
              const value = String(index + 1).padStart(2, "0");
              return (
                <option key={value} value={value}>
                  {JomaCalendarService.persianMonthName(index + 1)}
                </option>
              );
            })}
          </select>
        </div>
        <div className="flex items-end">
          <Button type="submit" className="w-full" disabled={busy}>
            ایجاد / انتخاب دوره
          </Button>
        </div>
      </form>

      {error && <p className="text-sm text-destructive">{error}</p>}
      {items.length === 0 ? (
        <EmptyState title="دوره‌ای نیست" description="هنوز دوره‌ای ساخته نشده است." />
      ) : (
        <div className="space-y-3">
          {items.map(({ period, plan }) => (
            <div key={period.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border bg-card p-5">
              <Link to={`/app/periods/${period.periodKey}`}>
                <div className="text-lg font-bold">{JomaCalendarService.formatPeriodLabel(period.periodKey)}</div>
                <div className="mt-1 text-xs text-muted-foreground" dir="ltr">
                  {period.startDate} — {period.endDate}
                </div>
              </Link>
              <div className="flex items-center gap-2">
                <StatusBadge status={plan.status} />
                <Button
                  variant="outline"
                  onClick={() => {
                    setSelectedPeriodKey(period.periodKey);
                    navigate("/app");
                  }}
                >
                  کار روی این دوره
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
