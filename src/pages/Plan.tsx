import { useEffect, useMemo, useState } from "react";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { FREQUENCY_LABEL } from "@/domain/catalog";
import { sumWeights } from "@/domain/rules/planRules";
import type { ActivityDefinition, Plan, PlanActivity } from "@/domain/types";
import { Button } from "@/components/ui/button";
import { EmptyState } from "@/components/joma/EmptyState";
import { LoadingState } from "@/components/joma/LoadingState";
import { StatusBadge } from "@/components/joma/StatusBadge";
import { toUserMessage } from "@/lib/errors";
import { listLibraryActivities } from "@/services/activityService";
import { ensureWorkingPeriod } from "@/services/periodService";
import { addPlanActivity, listPlanActivities, removePlanActivity, transitionPlan } from "@/services/planService";

export default function PlanPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [library, setLibrary] = useState<ActivityDefinition[]>([]);
  const [selectedId, setSelectedId] = useState("");
  const [busy, setBusy] = useState(false);

  const reload = async () => {
    const [{ plan: nextPlan }, nextLibrary] = await Promise.all([ensureWorkingPeriod(), listLibraryActivities()]);
    const nextActivities = await listPlanActivities(nextPlan.id);
    setPlan(nextPlan);
    setLibrary(nextLibrary);
    setActivities(nextActivities);
    if (!selectedId && nextLibrary[0]) setSelectedId(nextLibrary[0].id);
  };

  useEffect(() => {
    reload().catch((err) => setError(toUserMessage(err))).finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const editable = plan?.status === "DRAFT" || plan?.status === "PLANNING";
  const weightSum = useMemo(() => sumWeights(activities.map((item) => item.weight)), [activities]);

  if (loading) return <LoadingState />;
  if (!plan) return <p className="text-destructive">{error}</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl font-black">برنامه {JomaCalendarService.formatPeriodLabel(plan.periodKey)}</h1>
          <p className="mt-2 text-sm text-muted-foreground">با نهایی‌سازی، تصویر ثابت فعالیت‌ها برای همین دوره ذخیره می‌شود.</p>
        </div>
        <StatusBadge status={plan.status} />
      </div>
      {error && <p className="text-sm text-destructive">{error}</p>}

      <div className="joma-card flex flex-wrap gap-2 p-4">
        {plan.status === "DRAFT" && <Button disabled={busy || activities.length === 0} onClick={async () => setPlan(await transitionPlan(plan, "PLANNING"))}>نهایی‌سازی برنامه</Button>}
        {plan.status === "PLANNING" && <Button disabled={busy || activities.length === 0} onClick={async () => setPlan(await transitionPlan(plan, "RUNNING"))}>شروع اجرا</Button>}
        {plan.status === "RUNNING" && <p className="text-sm text-emerald-700">برنامه قفل شده تا تاریخچه حفظ شود.</p>}
      </div>

      {editable && (
        <form
          className="joma-card flex flex-col gap-3 p-5 md:flex-row"
          onSubmit={async (event) => {
            event.preventDefault();
            const activity = library.find((item) => item.id === selectedId);
            if (!activity) return;
            setBusy(true);
            try {
              await addPlanActivity(plan, activity);
              await reload();
            } catch (err) {
              setError(toUserMessage(err));
            } finally {
              setBusy(false);
            }
          }}
        >
          <select className="h-10 flex-1 rounded-md border bg-background px-3 text-sm" value={selectedId} onChange={(e) => setSelectedId(e.target.value)}>
            {library.map((item) => (
              <option key={item.id} value={item.id}>{item.sticker} {item.name}</option>
            ))}
          </select>
          <Button type="submit" disabled={busy}>افزودن به برنامه</Button>
        </form>
      )}

      <div className="flex items-center justify-between">
        <h2 className="text-lg font-bold">فعالیت‌های این دوره</h2>
        <span className="text-sm text-muted-foreground">جمع وزن: {JomaCalendarService.toPersianDigits(weightSum)}</span>
      </div>
      {activities.length === 0 ? (
        <EmptyState title="برنامه خالی است" description="از کتابخانه یک فعالیت اضافه کنید." />
      ) : (
        activities.map((item) => (
          <div key={item.id} className="joma-card flex items-center justify-between gap-3 p-4">
            <div className="flex items-center gap-3">
              <div className="flex h-12 w-12 items-center justify-center rounded-2xl text-2xl" style={{ background: item.color }}>{item.sticker}</div>
              <div>
                <div className="font-bold">{item.name}</div>
                <div className="text-xs text-muted-foreground">{FREQUENCY_LABEL[item.frequency]} · وزن {item.weight}</div>
              </div>
            </div>
            {editable && <Button variant="outline" onClick={async () => { await removePlanActivity(plan, item.id); await reload(); }}>حذف</Button>}
          </div>
        ))
      )}
    </div>
  );
}
