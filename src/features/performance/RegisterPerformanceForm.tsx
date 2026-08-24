import { useState } from "react";
import type { PerformanceEvent, Plan, PlanActivity } from "@/domain/types";
import { calculateActual, canRegisterPerformance, hasDailyRegistration, weeklyActual } from "@/domain/rules/performanceRules";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { FrequencyBadge } from "@/components/joma/StatusBadge";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { registerPerformance } from "@/services/performanceService";
import { toUserMessage } from "@/lib/errors";

export function RegisterPerformanceForm({
  plan,
  activity,
  events,
  performanceDate,
  onRegistered,
}: {
  plan: Plan;
  activity: PlanActivity;
  events: PerformanceEvent[];
  performanceDate: string;
  onRegistered: () => Promise<void> | void;
}) {
  const [value, setValue] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [done, setDone] = useState(false);

  const related = events.filter((event) => event.planActivityId === activity.id);
  const actual =
    activity.frequency === "WEEKLY"
      ? weeklyActual(related, performanceDate)
      : calculateActual(related);
  const decision = canRegisterPerformance({
    plan,
    planActivity: activity,
    performanceDate,
    actualValue: Number(value || 0),
    existingEvents: related,
  });
  const dailyLocked = activity.frequency === "DAILY" && hasDailyRegistration(related, performanceDate);

  const submit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError(null);
    setBusy(true);
    try {
      await registerPerformance({
        plan,
        planActivity: activity,
        performanceDate,
        actualValue: Number(value),
      });
      setValue("");
      setDone(true);
      await onRegistered();
    } catch (err) {
      setError(toUserMessage(err));
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="rounded-2xl border bg-card p-5">
      <div className="flex items-start justify-between gap-3">
        <div>
          <h3 className="font-bold">{activity.title}</h3>
          <p className="mt-1 text-xs text-muted-foreground">{activity.activityCode}</p>
        </div>
        <FrequencyBadge frequency={activity.frequency} />
      </div>
      <p className="mt-3 text-sm text-muted-foreground">
        مقدار ثبت‌شده: {JomaCalendarService.toPersianDigits(actual)} / هدف{" "}
        {JomaCalendarService.toPersianDigits(activity.targetValue)}
      </p>
      {dailyLocked ? (
        <p className="mt-4 rounded-xl bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
          ثبت امروز انجام شده است. ثبت مجدد روزانه ممکن نیست.
        </p>
      ) : (
        <form className="mt-4 flex flex-col gap-3 sm:flex-row" onSubmit={submit}>
          <Input
            type="number"
            min={0}
            step="any"
            dir="ltr"
            placeholder="مقدار واقعی"
            value={value}
            onChange={(e) => setValue(e.target.value)}
            required
          />
          <Button type="submit" disabled={busy || (value !== "" && !decision.ok)}>
            {busy ? "در حال ثبت..." : "ثبت عملکرد"}
          </Button>
        </form>
      )}
      {error && <p className="mt-3 text-sm text-destructive">{error}</p>}
      {done && !error && <p className="mt-3 text-sm text-emerald-700">رویداد عملکرد ذخیره شد.</p>}
    </div>
  );
}
