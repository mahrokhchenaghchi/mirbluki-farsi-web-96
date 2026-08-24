import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import type { Plan, PlanActivity } from "@/domain/types";
import type { ReportProjection } from "@/reporting/types";
import { LoadingState } from "@/components/joma/LoadingState";
import { StatusBadge } from "@/components/joma/StatusBadge";
import { ReportView } from "@/features/reports/ReportView";
import { getPeriodByKey } from "@/services/periodService";
import { listPlanActivities } from "@/services/planService";
import { loadReport } from "@/services/reportService";
import { toUserMessage } from "@/lib/errors";

export default function PeriodDetailPage() {
  const { periodKey } = useParams();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plan, setPlan] = useState<Plan | null>(null);
  const [activities, setActivities] = useState<PlanActivity[]>([]);
  const [report, setReport] = useState<ReportProjection | null>(null);

  useEffect(() => {
    if (!periodKey) return;
    (async () => {
      try {
        const found = await getPeriodByKey(periodKey);
        if (!found) {
          setError("این دوره پیدا نشد.");
          return;
        }
        setPlan(found.plan);
        setActivities(await listPlanActivities(found.plan.id));
        setReport(await loadReport(found.plan.id));
      } catch (err) {
        setError(toUserMessage(err));
      } finally {
        setLoading(false);
      }
    })();
  }, [periodKey]);

  if (loading) return <LoadingState />;
  if (error) return <p className="text-destructive">{error}</p>;
  if (!plan || !periodKey) return null;

  return (
    <div className="space-y-8">
      <div>
        <Link to="/app/periods" className="text-sm text-primary">
          بازگشت به دوره‌ها
        </Link>
        <div className="mt-3 flex flex-wrap items-center gap-3">
          <h1 className="text-3xl font-black">{JomaCalendarService.formatPeriodLabel(periodKey)}</h1>
          <StatusBadge status={plan.status} />
        </div>
      </div>

      <section className="rounded-2xl border bg-card p-5">
        <h2 className="font-bold">برنامه این دوره</h2>
        <ul className="mt-4 space-y-2 text-sm">
          {activities.map((item) => (
            <li key={item.id} className="rounded-xl bg-muted/40 px-4 py-3">
              {item.title} · هدف {JomaCalendarService.toPersianDigits(item.targetValue)} · وزن{" "}
              {JomaCalendarService.toPersianDigits(item.weight)}
            </li>
          ))}
          {activities.length === 0 && <li className="text-muted-foreground">فعالیتی در این دوره نیست.</li>}
        </ul>
      </section>

      {report && <ReportView report={report} />}
    </div>
  );
}
