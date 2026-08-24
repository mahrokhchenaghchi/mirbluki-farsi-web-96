import { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { FREQUENCY_LABEL, FREQUENCIES, targetOf } from "@/domain/catalog";
import { sumWeights } from "@/domain/rules/planRules";
import type { ActivityDefinition, Frequency, Plan, PlanActivity } from "@/domain/types";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { EmptyState } from "@/components/joma/EmptyState";
import { LoadingState } from "@/components/joma/LoadingState";
import { PageHeader } from "@/components/joma/PageHeader";
import { StatusBadge } from "@/components/joma/StatusBadge";
import { toUserMessage } from "@/lib/errors";
import { listLibraryActivities } from "@/services/activityService";
import { ensureWorkingPeriod } from "@/services/periodService";
import { addPlanActivity, listPlanActivities, removePlanActivity, transitionPlan, updatePlanActivity } from "@/services/planService";

export default function PlanPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [library, setLibrary] = useState<ActivityDefinition[]>([]);
  const [selectedId, setSelectedId] = useState("");
  const [overrideFreq, setOverrideFreq] = useState<Frequency | "">("");
  const [overrideTarget, setOverrideTarget] = useState("");
  const [overrideWeight, setOverrideWeight] = useState("");
  const [busy, setBusy] = useState(false);

  const reload = async () => {
    const [{ plan: nextPlan }, nextLibrary] = await Promise.all([ensureWorkingPeriod(), listLibraryActivities()]);
    const nextActivities = await listPlanActivities(nextPlan.id);
    setPlan(nextPlan);
    setLibrary(nextLibrary.filter((item) => item.status !== "INACTIVE"));
    setActivities(nextActivities);
    if (!selectedId && nextLibrary[0]) setSelectedId(nextLibrary[0].id);
  };

  useEffect(() => {
    reload().catch((err) => setError(toUserMessage(err))).finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const selected = library.find((item) => item.id === selectedId);
  const editable = plan?.status === "DRAFT" || plan?.status === "PLANNING";
  const weightSum = useMemo(() => sumWeights(activities.map((item) => item.weight)), [activities]);

  if (loading) return <LoadingState />;
  if (!plan) return <p className="text-destructive">{error}</p>;

  return (
    <div className="space-y-6">
      <PageHeader
        title={`برنامه ${JomaCalendarService.formatPeriodLabel(plan.periodKey)}`}
        description="هدف و وزن را برای همین ماه می‌توانید عوض کنید. پس از نهایی‌سازی، تصویر ثابت قفل می‌شود."
        crumbs={[{ label: "داشبورد", to: "/app" }, { label: "برنامه من" }]}
        action={<StatusBadge status={plan.status} />}
      />
      {error && <p className="text-sm text-destructive">{error}</p>}

      <div className="joma-card flex flex-wrap gap-2 p-4">
        {plan.status === "DRAFT" && <Button disabled={busy || activities.length === 0} onClick={async () => setPlan(await transitionPlan(plan, "PLANNING"))}>نهایی‌سازی</Button>}
        {plan.status === "PLANNING" && <Button disabled={busy || activities.length === 0} onClick={async () => setPlan(await transitionPlan(plan, "RUNNING"))}>شروع اجرا</Button>}
        {plan.status === "RUNNING" && <p className="text-sm text-emerald-700">برنامه قفل است تا تاریخچه حفظ شود.</p>}
        <span className="mr-auto text-sm text-muted-foreground">جمع وزن: {JomaCalendarService.toPersianDigits(weightSum)}</span>
      </div>

      {editable && (
        <form
          className="joma-card grid gap-3 p-5 md:grid-cols-2"
          onSubmit={async (event) => {
            event.preventDefault();
            if (!selected) return;
            setBusy(true);
            try {
              await addPlanActivity(plan, selected, {
                frequency: overrideFreq || undefined,
                targetValue: overrideTarget ? Number(overrideTarget) : undefined,
                weight: overrideWeight ? Number(overrideWeight) : undefined,
              });
              setOverrideTarget("");
              setOverrideWeight("");
              setOverrideFreq("");
              await reload();
            } catch (err) {
              setError(toUserMessage(err));
            } finally {
              setBusy(false);
            }
          }}
        >
          <select className="h-10 rounded-md border bg-background px-3 text-sm md:col-span-2" value={selectedId} onChange={(e) => setSelectedId(e.target.value)}>
            {library.map((item) => <option key={item.id} value={item.id}>{item.sticker} {item.name}</option>)}
          </select>
          <select className="h-10 rounded-md border bg-background px-3 text-sm" value={overrideFreq} onChange={(e) => setOverrideFreq(e.target.value as Frequency | "")}>
            <option value="">تناوب پیش‌فرض کتابخانه</option>
            {FREQUENCIES.map((item) => <option key={item} value={item}>{FREQUENCY_LABEL[item]}</option>)}
          </select>
          <Input dir="ltr" placeholder={`هدف این ماه (پیش‌فرض ${selected ? targetOf(selected) : ""})`} value={overrideTarget} onChange={(e) => setOverrideTarget(e.target.value)} />
          <Input dir="ltr" placeholder={`وزن این ماه (پیش‌فرض ${selected?.weight ?? ""})`} value={overrideWeight} onChange={(e) => setOverrideWeight(e.target.value)} />
          <Button type="submit" disabled={busy}>افزودن به برنامه این دوره</Button>
        </form>
      )}

      {activities.length === 0 ? (
        <EmptyState title="برنامه خالی است" description="از کتابخانه یک فعالیت اضافه کنید." action={<Button asChild><Link to="/app/library">کتابخانه</Link></Button>} />
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          {activities.map((item, index) => (
            <article key={item.id} className="joma-card flex aspect-square flex-col p-4">
              <div className="flex items-start justify-between">
                <div className="flex h-14 w-14 items-center justify-center rounded-3xl text-3xl" style={{ background: item.color }}>{item.sticker}</div>
                <span className="joma-chip bg-muted">{FREQUENCY_LABEL[item.frequency]}</span>
              </div>
              <h3 className="mt-4 text-lg font-black">{item.name}</h3>
              <p className="text-xs text-muted-foreground">{item.category}</p>
              {editable ? (
                <div className="mt-auto grid grid-cols-2 gap-2 pt-4">
                  <Input dir="ltr" value={item.targetValue} onChange={async (e) => {
                    await updatePlanActivity(plan, item.id, { targetValue: Number(e.target.value) });
                    await reload();
                  }} />
                  <Input dir="ltr" value={item.weight} onChange={async (e) => {
                    await updatePlanActivity(plan, item.id, { weight: Number(e.target.value) });
                    await reload();
                  }} />
                  <Button variant="outline" onClick={async () => {
                    if (index === 0) return;
                    const prev = activities[index - 1];
                    await updatePlanActivity(plan, item.id, { sortOrder: prev.sortOrder });
                    await updatePlanActivity(plan, prev.id, { sortOrder: item.sortOrder });
                    await reload();
                  }}>بالا</Button>
                  <Button variant="ghost" onClick={async () => {
                    if (!window.confirm("این فعالیت از برنامه این دوره حذف شود؟")) return;
                    await removePlanActivity(plan, item.id);
                    await reload();
                  }}>حذف</Button>
                </div>
              ) : (
                <div className="mt-auto space-y-1 pt-4 text-sm">
                  <div>هدف: {item.targetValue}</div>
                  <div>وزن: {item.weight}</div>
                  <Button asChild className="mt-2 w-full"><Link to="/app/today">ثبت عملکرد</Link></Button>
                </div>
              )}
            </article>
          ))}
        </div>
      )}
    </div>
  );
}
