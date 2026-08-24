import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { calculateActual, hasDailyRegistration, weeklyActual } from "@/domain/rules/performanceRules";
import type { PerformanceEvent, Period, Plan, PlanActivity } from "@/domain/types";
import { Button } from "@/components/ui/button";
import { LoadingState } from "@/components/joma/LoadingState";
import { StatusBadge } from "@/components/joma/StatusBadge";
import { ensureCurrentPeriod } from "@/services/periodService";
import { listPlanActivities } from "@/services/planService";
import { listEventsForPlan } from "@/services/performanceService";
import { toUserMessage } from "@/lib/errors";

export default function Dashboard() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [period, setPeriod] = useState<Period | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [events, setEvents] = useState<PerformanceEvent[]>([]);

  useEffect(() => {
    ensureCurrentPeriod()
      .then(async ({ period: nextPeriod, plan: nextPlan }) => {
        setPeriod(nextPeriod);
        setPlan(nextPlan);
        const [nextActivities, nextEvents] = await Promise.all([
          listPlanActivities(nextPlan.id),
          listEventsForPlan(nextPlan.id),
        ]);
        setActivities(nextActivities);
        setEvents(nextEvents);
      })
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingState />;
  if (error) return <p className="text-destructive">{error}</p>;
  if (!period || !plan) return null;

  const today = JomaCalendarService.todayJalaliString();
  const daily = activities.filter((item) => item.frequency === "DAILY");
  const remainingDaily = daily.filter(
    (item) => !hasDailyRegistration(events.filter((event) => event.planActivityId === item.id), today),
  ).length;

  return (
    <div className="space-y-6">
      <div>
        <p className="text-sm text-muted-foreground">دوره جاری</p>
        <div className="mt-1 flex flex-wrap items-center gap-3">
          <h1 className="text-3xl font-black">{JomaCalendarService.formatPeriodLabel(period.periodKey)}</h1>
          <StatusBadge status={plan.status} />
        </div>
        <p className="mt-2 text-sm text-muted-foreground">
          {JomaCalendarService.weekdayName(today)} {JomaCalendarService.formatJalaliDisplay(today)}
        </p>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <div className="rounded-2xl border bg-card p-5">
          <div className="text-sm text-muted-foreground">فعالیت‌های برنامه</div>
          <div className="mt-2 text-3xl font-bold">{JomaCalendarService.toPersianDigits(activities.length)}</div>
        </div>
        <div className="rounded-2xl border bg-card p-5">
          <div className="text-sm text-muted-foreground">رویدادهای ثبت‌شده</div>
          <div className="mt-2 text-3xl font-bold">{JomaCalendarService.toPersianDigits(events.length)}</div>
        </div>
        <div className="rounded-2xl border bg-card p-5">
          <div className="text-sm text-muted-foreground">روزانه باقی‌مانده امروز</div>
          <div className="mt-2 text-3xl font-bold">{JomaCalendarService.toPersianDigits(remainingDaily)}</div>
        </div>
      </div>

      <div className="grid gap-3 md:grid-cols-2">
        <Button asChild size="lg">
          <Link to="/app/today">ثبت عملکرد امروز</Link>
        </Button>
        <Button asChild size="lg" variant="outline">
          <Link to="/app/plan">مشاهده یا تنظیم برنامه</Link>
        </Button>
        <Button asChild variant="secondary">
          <Link to="/app/reports">گزارش این دوره</Link>
        </Button>
        <Button asChild variant="secondary">
          <Link to="/app/periods">دوره‌های من</Link>
        </Button>
      </div>

      <section className="rounded-2xl border bg-card p-5">
        <h2 className="font-bold">فعالیت‌های قابل ثبت</h2>
        {activities.length === 0 ? (
          <p className="mt-3 text-sm leading-8 text-muted-foreground">
            هنوز فعالیتی به برنامه این دوره اضافه نشده است.
          </p>
        ) : (
          <ul className="mt-4 space-y-3">
            {activities.map((activity) => {
              const related = events.filter((event) => event.planActivityId === activity.id);
              const actual =
                activity.frequency === "WEEKLY"
                  ? weeklyActual(related, today)
                  : calculateActual(related);
              return (
                <li key={activity.id} className="flex items-center justify-between rounded-xl bg-muted/50 px-4 py-3">
                  <div>
                    <div className="font-medium">{activity.title}</div>
                    <div className="text-xs text-muted-foreground">
                      مقدار ثبت‌شده: {JomaCalendarService.toPersianDigits(actual)} از{" "}
                      {JomaCalendarService.toPersianDigits(activity.targetValue)}
                    </div>
                  </div>
                  <span className="text-xs text-muted-foreground">{activity.frequency}</span>
                </li>
              );
            })}
          </ul>
        )}
      </section>
    </div>
  );
}
