import { useCallback, useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { PerformanceEvent, Plan, PlanActivity } from "@/domain/types";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { EmptyState } from "@/components/joma/EmptyState";
import { LoadingState } from "@/components/joma/LoadingState";
import { RegisterPerformanceForm } from "@/features/performance/RegisterPerformanceForm";
import { toUserMessage } from "@/lib/errors";
import { listEventsForPlan } from "@/services/performanceService";
import { ensureWorkingPeriod } from "@/services/periodService";
import { listPlanActivities } from "@/services/planService";

export default function TodayPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [events, setEvents] = useState<PerformanceEvent[]>([]);
  const today = JomaCalendarService.todayJalaliString();
  const [performanceDate, setPerformanceDate] = useState(today);

  const refresh = useCallback(async () => {
    const current = await ensureWorkingPeriod();
    setPlan(current.plan);
    const defaultDate = JomaCalendarService.isDateInPeriod(today, current.plan.periodKey) ? today : current.period.startDate;
    setPerformanceDate((prev) => (JomaCalendarService.isDateInPeriod(prev, current.plan.periodKey) ? prev : defaultDate));
    const [nextActivities, nextEvents] = await Promise.all([
      listPlanActivities(current.plan.id),
      listEventsForPlan(current.plan.id),
    ]);
    setActivities(nextActivities);
    setEvents(nextEvents);
  }, [today]);

  useEffect(() => {
    refresh().catch((err) => setError(toUserMessage(err))).finally(() => setLoading(false));
  }, [refresh]);

  const days = useMemo(() => (plan ? JomaCalendarService.iteratePeriodDays(plan.periodKey) : []), [plan]);
  if (loading) return <LoadingState />;
  if (error) return <p className="text-destructive">{error}</p>;
  if (!plan) return null;

  const groups = {
    DAILY: activities.filter((item) => item.frequency === "DAILY"),
    WEEKLY: activities.filter((item) => item.frequency === "WEEKLY"),
    MONTHLY: activities.filter((item) => item.frequency === "MONTHLY"),
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-black">فعالیت‌های قابل ثبت</h1>
        <p className="mt-2 text-sm text-muted-foreground">دوره {JomaCalendarService.formatPeriodLabel(plan.periodKey)}</p>
      </div>
      <div className="max-w-xs space-y-2">
        <Label>تاریخ عملکرد</Label>
        <select className="h-10 w-full rounded-md border bg-background px-3 text-sm" value={performanceDate} onChange={(e) => setPerformanceDate(e.target.value)}>
          {days.map((day) => (
            <option key={day} value={day}>{JomaCalendarService.weekdayName(day)} {JomaCalendarService.formatJalaliDisplay(day)}</option>
          ))}
        </select>
      </div>
      {plan.status !== "RUNNING" && (
        <EmptyState title="دوره در حال اجرا نیست" description="ابتدا برنامه را نهایی و شروع کنید." action={<Button asChild><Link to="/app/plan">رفتن به برنامه</Link></Button>} />
      )}
      {(["DAILY", "WEEKLY", "MONTHLY"] as const).map((frequency) => (
        <section key={frequency} className="space-y-3">
          <h2 className="text-lg font-black">{frequency === "DAILY" ? "روزانه" : frequency === "WEEKLY" ? "هفتگی" : "ماهانه"}</h2>
          {groups[frequency].length === 0 ? (
            <p className="text-sm text-muted-foreground">فعالیتی با این تناوب نیست.</p>
          ) : (
            groups[frequency].map((activity) => (
              <RegisterPerformanceForm
                key={`${activity.id}-${performanceDate}`}
                plan={plan}
                activity={activity}
                events={events}
                performanceDate={performanceDate}
                onRegistered={refresh}
              />
            ))
          )}
        </section>
      ))}
    </div>
  );
}
