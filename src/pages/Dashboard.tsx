import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { FREQUENCY_LABEL, MOOD_METRICS, formatValue } from "@/domain/catalog";
import { calculateActual, hasDailyRegistration } from "@/domain/rules/performanceRules";
import type { MoodRecord, PerformanceEvent, Period, Plan, PlanActivity } from "@/domain/types";
import { Button } from "@/components/ui/button";
import { LoadingState } from "@/components/joma/LoadingState";
import { StatusBadge } from "@/components/joma/StatusBadge";
import { useAuth } from "@/hooks/useAuth";
import { toUserMessage } from "@/lib/errors";
import { getMoodForDate } from "@/services/moodService";
import { listEventsForPlan } from "@/services/performanceService";
import { ensureWorkingPeriod } from "@/services/periodService";
import { listPlanActivities } from "@/services/planService";

export default function Dashboard() {
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [period, setPeriod] = useState<Period | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [events, setEvents] = useState<PerformanceEvent[]>([]);
  const [mood, setMood] = useState<MoodRecord | null>(null);
  const today = JomaCalendarService.todayJalaliString();

  useEffect(() => {
    Promise.all([ensureWorkingPeriod(), getMoodForDate(today)])
      .then(async ([{ period: nextPeriod, plan: nextPlan }, nextMood]) => {
        setPeriod(nextPeriod);
        setPlan(nextPlan);
        setMood(nextMood);
        const [nextActivities, nextEvents] = await Promise.all([
          listPlanActivities(nextPlan.id),
          listEventsForPlan(nextPlan.id),
        ]);
        setActivities(nextActivities);
        setEvents(nextEvents);
      })
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, [today]);

  if (loading) return <LoadingState />;
  if (error) return <p className="text-destructive">{error}</p>;
  if (!period || !plan) return null;

  const remaining = activities.filter(
    (item) => item.frequency !== "DAILY" || !hasDailyRegistration(events.filter((event) => event.planActivityId === item.id), today),
  ).length;

  return (
    <div className="space-y-6">
      <section className="joma-card bg-gradient-to-l from-violet-100 via-white to-orange-100 p-6">
        <p className="text-sm text-muted-foreground">{JomaCalendarService.formatJalaliDisplay(today)}</p>
        <h1 className="mt-1 text-3xl font-black">سلام {user?.fullName || user?.username}</h1>
        <div className="mt-3 flex flex-wrap items-center gap-2">
          <span className="joma-chip bg-white">{JomaCalendarService.formatPeriodLabel(period.periodKey)}</span>
          <StatusBadge status={plan.status} />
        </div>
      </section>

      <div className="grid gap-4 md:grid-cols-3">
        <Link to="/mood" className="joma-card p-5">
          <div className="text-sm text-muted-foreground">حال امروز</div>
          {mood ? (
            <div className="mt-3 flex gap-2 text-2xl">
              {MOOD_METRICS.map((metric) => (
                <span key={metric.key}>{metric.stickers[mood.scores[metric.key] - 1]}</span>
              ))}
            </div>
          ) : (
            <p className="mt-3 text-sm">هنوز ثبت نشده — ثبت حال</p>
          )}
        </Link>
        <div className="joma-card p-5">
          <div className="text-sm text-muted-foreground">فعالیت‌های برنامه</div>
          <div className="mt-2 text-4xl font-black">{JomaCalendarService.toPersianDigits(activities.length)}</div>
        </div>
        <div className="joma-card p-5">
          <div className="text-sm text-muted-foreground">قابل ثبت امروز</div>
          <div className="mt-2 text-4xl font-black">{JomaCalendarService.toPersianDigits(remaining)}</div>
        </div>
      </div>

      <div className="grid gap-3 sm:grid-cols-2">
        <Button asChild size="lg"><Link to="/app/today">ثبت عملکرد</Link></Button>
        <Button asChild size="lg" variant="outline"><Link to="/app/plan">برنامه دوره</Link></Button>
        <Button asChild variant="secondary"><Link to="/app/reports">گزارش‌ها</Link></Button>
        <Button asChild variant="secondary"><Link to="/app/periods">دوره‌های من</Link></Button>
      </div>

      <section className="space-y-3">
        <h2 className="text-xl font-black">فعالیت‌های امروز</h2>
        {activities.length === 0 ? (
          <div className="joma-card p-6 text-sm text-muted-foreground">هنوز فعالیتی به این دوره اضافه نشده است.</div>
        ) : (
          activities.slice(0, 6).map((activity) => {
            const related = events.filter((event) => event.planActivityId === activity.id);
            const actual = calculateActual(related);
            return (
              <div key={activity.id} className="joma-card flex items-center justify-between gap-3 p-4">
                <div className="flex items-center gap-3">
                  <div className="flex h-12 w-12 items-center justify-center rounded-2xl text-2xl" style={{ background: activity.color }}>
                    {activity.sticker}
                  </div>
                  <div>
                    <div className="font-bold">{activity.name}</div>
                    <div className="text-xs text-muted-foreground">
                      {FREQUENCY_LABEL[activity.frequency]} · {formatValue(activity.dataType, actual)} / {formatValue(activity.dataType, activity.targetValue)}
                    </div>
                  </div>
                </div>
              </div>
            );
          })
        )}
      </section>
    </div>
  );
}
