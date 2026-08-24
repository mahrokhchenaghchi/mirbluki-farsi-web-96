import { useState } from "react";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { DATA_TYPE_LABEL, FREQUENCY_LABEL, formatValue } from "@/domain/catalog";
import { calculateActual, canRegisterPerformance, hasDailyRegistration, weeklyActual } from "@/domain/rules/performanceRules";
import type { PerformanceEvent, Plan, PlanActivity } from "@/domain/types";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
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
  const [value, setValue] = useState(activity.dataType === "BOOLEAN" ? "1" : activity.dataType === "RATING" ? "3" : "");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const related = events.filter((event) => event.planActivityId === activity.id);
  const actual =
    activity.frequency === "WEEKLY" ? weeklyActual(related, performanceDate) : calculateActual(related);
  const dailyLocked = activity.frequency === "DAILY" && hasDailyRegistration(related, performanceDate);
  const lastToday = related.find((event) => event.performanceDate === performanceDate);
  const decision = canRegisterPerformance({
    plan,
    planActivity: activity,
    performanceDate,
    actualValue: Number(value || 0),
    existingEvents: related,
  });

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
      await onRegistered();
    } catch (err) {
      setError(toUserMessage(err));
    } finally {
      setBusy(false);
    }
  };

  return (
    <article className="joma-card overflow-hidden" style={{ borderColor: activity.color }}>
      <div className="flex items-start justify-between gap-3 p-5" style={{ background: `${activity.color}55` }}>
        <div className="flex items-center gap-3">
          <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-3xl shadow-sm">
            {activity.sticker}
          </div>
          <div>
            <h3 className="text-lg font-black">{activity.name}</h3>
            <p className="text-xs text-muted-foreground">
              {activity.category} · {FREQUENCY_LABEL[activity.frequency]} · {DATA_TYPE_LABEL[activity.dataType]}
            </p>
          </div>
        </div>
        <span className="joma-chip bg-white/80">وزن {JomaCalendarService.toPersianDigits(activity.weight)}</span>
      </div>
      <div className="space-y-3 p-5">
        <p className="text-sm text-muted-foreground">
          مقدار ثبت‌شده: {formatValue(activity.dataType, actual)} از هدف{" "}
          {formatValue(activity.dataType, activity.targetValue)}
        </p>
        {dailyLocked ? (
          <div className="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            ثبت شد ✓ {lastToday ? formatValue(activity.dataType, lastToday.actualValue) : ""}
          </div>
        ) : (
          <form className="space-y-3" onSubmit={submit}>
            {activity.dataType === "BOOLEAN" && (
              <div className="grid grid-cols-2 gap-2">
                <Button type="button" variant={value === "1" ? "default" : "outline"} onClick={() => setValue("1")}>
                  انجام شد
                </Button>
                <Button type="button" variant={value === "0" ? "default" : "outline"} onClick={() => setValue("0")}>
                  انجام نشد
                </Button>
              </div>
            )}
            {activity.dataType === "RATING" && (
              <div className="flex justify-between gap-1">
                {[1, 2, 3, 4, 5].map((score) => (
                  <button
                    key={score}
                    type="button"
                    className={`flex h-11 flex-1 items-center justify-center rounded-2xl text-lg ${
                      Number(value) === score ? "bg-primary text-white" : "bg-muted"
                    }`}
                    onClick={() => setValue(String(score))}
                  >
                    {score === 1 ? "😞" : score === 2 ? "😐" : score === 3 ? "🙂" : score === 4 ? "😊" : "🤩"}
                  </button>
                ))}
              </div>
            )}
            {(activity.dataType === "DURATION" || activity.dataType === "NUMERIC") && (
              <Input
                type="number"
                min={0}
                step="any"
                dir="ltr"
                placeholder={activity.dataType === "DURATION" ? "دقیقه" : "مقدار"}
                value={value}
                onChange={(e) => setValue(e.target.value)}
                required
              />
            )}
            <Button type="submit" className="w-full" disabled={busy || !decision.ok}>
              {busy ? "در حال ثبت..." : "ثبت عملکرد"}
            </Button>
          </form>
        )}
        {error && <p className="text-sm text-destructive">{error}</p>}
      </div>
    </article>
  );
}
