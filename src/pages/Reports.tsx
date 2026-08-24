import { useEffect, useState } from "react";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { Plan } from "@/domain/types";
import type { ReportProjection } from "@/reporting/types";
import { LoadingState } from "@/components/joma/LoadingState";
import { ReportView } from "@/features/reports/ReportView";
import { ensureCurrentPeriod, listPeriods } from "@/services/periodService";
import { loadReport } from "@/services/reportService";
import { toUserMessage } from "@/lib/errors";

export default function ReportsPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plans, setPlans] = useState<Plan[]>([]);
  const [selectedPlanId, setSelectedPlanId] = useState<string>("");
  const [report, setReport] = useState<ReportProjection | null>(null);

  useEffect(() => {
    (async () => {
      try {
        await ensureWorkingPeriod();
        const items = await listPeriods();
        setPlans(items.map((item) => item.plan));
        const current = items[0]?.plan;
        if (current) {
          setSelectedPlanId(current.id);
          setReport(await loadReport(current.id));
        }
      } catch (err) {
        setError(toUserMessage(err));
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  const changePeriod = async (planId: string) => {
    setSelectedPlanId(planId);
    setError(null);
    try {
      setReport(await loadReport(planId));
    } catch (err) {
      setError(toUserMessage(err));
    }
  };

  if (loading) return <LoadingState />;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="text-3xl font-black">گزارش‌ها</h1>
          <p className="mt-2 text-sm text-muted-foreground">فقط دادهٔ همان دوره انتخاب‌شده نمایش داده می‌شود.</p>
        </div>
        <select
          className="h-10 rounded-md border bg-background px-3 text-sm"
          value={selectedPlanId}
          onChange={(e) => changePeriod(e.target.value)}
        >
          {plans.map((plan) => (
            <option key={plan.id} value={plan.id}>
              {JomaCalendarService.formatPeriodLabel(plan.periodKey)}
            </option>
          ))}
        </select>
      </div>
      {error && <p className="text-sm text-destructive">{error}</p>}
      {report ? <ReportView report={report} /> : <p>دوره‌ای برای گزارش وجود ندارد.</p>}
    </div>
  );
}
