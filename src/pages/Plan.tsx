import { useEffect, useMemo, useState } from "react";
import type { ActivityDefinition, Frequency, Plan, PlanActivity } from "@/domain/types";
import { sumWeights } from "@/domain/rules/planRules";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { EmptyState } from "@/components/joma/EmptyState";
import { LoadingState } from "@/components/joma/LoadingState";
import { FrequencyBadge, StatusBadge } from "@/components/joma/StatusBadge";
import { activityLabel, listLibraryActivities } from "@/services/activityService";
import { addPlanActivity, listPlanActivities, removePlanActivity, transitionPlan } from "@/services/planService";
import { ensureCurrentPeriod } from "@/services/periodService";
import { toUserMessage } from "@/lib/errors";

export default function PlanPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [library, setLibrary] = useState<ActivityDefinition[]>([]);
  const [selectedCode, setSelectedCode] = useState("");
  const [frequency, setFrequency] = useState<Frequency>("DAILY");
  const [targetValue, setTargetValue] = useState("1");
  const [weight, setWeight] = useState("0");
  const [busy, setBusy] = useState(false);

  const reload = async () => {
    const [{ plan: nextPlan }, nextLibrary] = await Promise.all([ensureCurrentPeriod(), listLibraryActivities()]);
    const nextActivities = await listPlanActivities(nextPlan.id);
    setPlan(nextPlan);
    setLibrary(nextLibrary);
    setActivities(nextActivities);
    if (!selectedCode && nextLibrary[0]) setSelectedCode(nextLibrary[0].code);
  };

  useEffect(() => {
    reload()
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const editable = plan?.status === "DRAFT" || plan?.status === "PLANNING";
  const weightSum = useMemo(() => sumWeights(activities.map((item) => item.weight)), [activities]);

  const add = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!plan) return;
    const activity = library.find((item) => item.code === selectedCode);
    if (!activity) return;
    setBusy(true);
    setError(null);
    try {
      await addPlanActivity({
        plan,
        activity,
        frequency,
        targetValue: Number(targetValue),
        weight: Number(weight),
      });
      await reload();
    } catch (err) {
      setError(toUserMessage(err));
    } finally {
      setBusy(false);
    }
  };

  const changeStatus = async (next: "PLANNING" | "RUNNING") => {
    if (!plan) return;
    setBusy(true);
    setError(null);
    try {
      const updated = await transitionPlan(plan, next);
      setPlan(updated);
    } catch (err) {
      setError(toUserMessage(err));
    } finally {
      setBusy(false);
    }
  };

  if (loading) return <LoadingState />;
  if (!plan) return <p className="text-destructive">{error}</p>;

  return (
    <div className="space-y-8">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl font-black">برنامه {JomaCalendarService.formatPeriodLabel(plan.periodKey)}</h1>
          <p className="mt-2 text-sm text-muted-foreground">
            فعالیت‌های کتابخانه هنگام ورود به برنامه به‌صورت تصویر ثابت ذخیره می‌شوند.
          </p>
        </div>
        <StatusBadge status={plan.status} />
      </div>

      {error && <p className="text-sm text-destructive">{error}</p>}

      <section className="rounded-2xl border bg-card p-5">
        <div className="flex flex-wrap gap-2">
          {plan.status === "DRAFT" && (
            <Button disabled={busy || activities.length === 0} onClick={() => changeStatus("PLANNING")}>
              نهایی‌سازی برنامه
            </Button>
          )}
          {plan.status === "PLANNING" && (
            <Button disabled={busy || activities.length === 0} onClick={() => changeStatus("RUNNING")}>
              شروع اجرا
            </Button>
          )}
          {plan.status === "RUNNING" && (
            <p className="text-sm text-emerald-700">برنامه در حال اجراست و برای حفظ تاریخچه قفل شده است.</p>
          )}
        </div>
      </section>

      {editable && (
        <form className="space-y-4 rounded-2xl border bg-card p-5" onSubmit={add}>
          <h2 className="font-bold">افزودن فعالیت به برنامه</h2>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-2">
              <Label>فعالیت کتابخانه</Label>
              <select
                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                value={selectedCode}
                onChange={(e) => setSelectedCode(e.target.value)}
              >
                {library.map((item) => (
                  <option key={item.id} value={item.code}>
                    {activityLabel(item)}
                  </option>
                ))}
              </select>
            </div>
            <div className="space-y-2">
              <Label>تناوب</Label>
              <select
                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                value={frequency}
                onChange={(e) => setFrequency(e.target.value as Frequency)}
              >
                <option value="DAILY">روزانه</option>
                <option value="WEEKLY">هفتگی</option>
                <option value="MONTHLY">ماهانه</option>
              </select>
            </div>
            <div className="space-y-2">
              <Label>هدف عددی</Label>
              <Input dir="ltr" type="number" min={0.0001} step="any" value={targetValue} onChange={(e) => setTargetValue(e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>وزن</Label>
              <Input dir="ltr" type="number" min={0} step="any" value={weight} onChange={(e) => setWeight(e.target.value)} />
            </div>
          </div>
          <Button type="submit" disabled={busy}>
            افزودن به برنامه
          </Button>
        </form>
      )}

      <section className="space-y-3">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-bold">فعالیت‌های این دوره</h2>
          <span className="text-sm text-muted-foreground">
            جمع وزن: {JomaCalendarService.toPersianDigits(weightSum)}
          </span>
        </div>
        {activities.length === 0 ? (
          <EmptyState title="برنامه خالی است" description="هنوز فعالیتی به این دوره اضافه نشده است." />
        ) : (
          activities.map((item) => (
            <div key={item.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border bg-card p-4">
              <div>
                <div className="font-medium">{item.title}</div>
                <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                  <FrequencyBadge frequency={item.frequency} />
                  <span>هدف {JomaCalendarService.toPersianDigits(item.targetValue)}</span>
                  <span>وزن {JomaCalendarService.toPersianDigits(item.weight)}</span>
                </div>
              </div>
              {editable && (
                <Button
                  variant="outline"
                  onClick={async () => {
                    try {
                      await removePlanActivity(plan, item.id);
                      await reload();
                    } catch (err) {
                      setError(toUserMessage(err));
                    }
                  }}
                >
                  حذف
                </Button>
              )}
            </div>
          ))
        )}
      </section>
    </div>
  );
}
