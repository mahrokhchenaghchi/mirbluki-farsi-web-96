import { useCallback, useEffect, useState } from "react";
import { Link } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { PerformanceEvent, Plan, PlanActivity } from "@/domain/types";
import { EmptyState } from "@/components/joma/EmptyState";
import { LoadingState } from "@/components/joma/LoadingState";
import { RegisterPerformanceForm } from "@/features/performance/RegisterPerformanceForm";
import { ensureCurrentPeriod } from "@/services/periodService";
import { listPlanActivities } from "@/services/planService";
import { listEventsForPlan } from "@/services/performanceService";
import { toUserMessage } from "@/lib/errors";
import { Button } from "@/components/ui/button";

export default function TodayPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [events, setEvents] = useState<PerformanceEvent[]>([]);
  const today = JomaCalendarService.todayJalaliString();

  const refresh = useCallback(async () => {
    const current = await ensureCurrentPeriod();
    setPlan(current.plan);
    const [nextActivities, nextEvents] = await Promise.all([
      listPlanActivities(current.plan.id),
      listEventsForPlan(current.plan.id),
    ]);
    setActivities(nextActivities);
    setEvents(nextEvents);
  }, []);

  useEffect(() => {
    refresh()
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, [refresh]);

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
        <h1 className="text-3xl font-black">فعالیت‌های امروز</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          {JomaCalendarService.weekdayName(today)} {JomaCalendarService.formatJalaliDisplay(today)}
        </p>
      </div>

      {plan.status !== "RUNNING" && (
        <EmptyState
          title="دوره هنوز در حال اجرا نیست"
          description="برای ثبت عملکرد، ابتدا برنامه را نهایی و سپس اجرا کنید."
          action={
            <Button asChild>
              <Link to="/app/plan">رفتن به برنامه</Link>
            </Button>
          }
        />
      )}

      {activities.length === 0 && plan.status === "RUNNING" && (
        <EmptyState
          title="فعالیتی برای ثبت وجود ندارد"
          description="هنوز عملکردی برای این دوره قابل ثبت نیست چون فعالیتی در برنامه نیست."
          action={
            <Button asChild>
              <Link to="/app/plan">افزودن فعالیت</Link>
            </Button>
          }
        />
      )}

      {(["DAILY", "WEEKLY", "MONTHLY"] as const).map((frequency) => (
        <section key={frequency} className="space-y-3">
          <h2 className="text-lg font-bold">
            {frequency === "DAILY" ? "روزانه" : frequency === "WEEKLY" ? "هفتگی" : "ماهانه"}
          </h2>
          {groups[frequency].length === 0 ? (
            <p className="text-sm text-muted-foreground">فعالیتی با این تناوب در برنامه نیست.</p>
          ) : (
            groups[frequency].map((activity) => (
              <RegisterPerformanceForm
                key={activity.id}
                plan={plan}
                activity={activity}
                events={events}
                performanceDate={today}
                onRegistered={refresh}
              />
            ))
          )}
        </section>
      ))}
    </div>
  );
}
