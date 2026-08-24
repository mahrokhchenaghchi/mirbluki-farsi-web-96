import { useEffect, useState } from "react";
import {
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { FREQUENCY_LABEL, MOOD_METRICS, formatValue } from "@/domain/catalog";
import type { Plan } from "@/domain/types";
import type { ReportProjection } from "@/reporting/types";
import { LoadingState } from "@/components/joma/LoadingState";
import { UnspecifiedNotice } from "@/components/joma/UnspecifiedNotice";
import { toUserMessage } from "@/lib/errors";
import { cn } from "@/lib/utils";
import { ensureWorkingPeriod, listPeriods } from "@/services/periodService";
import { loadReport } from "@/services/reportService";

const TABS = ["خلاصه", "فعالیت‌ها", "تقویم", "روند", "خلق", "جزئیات"] as const;

export default function ReportsPage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [plans, setPlans] = useState<Plan[]>([]);
  const [selectedPlanId, setSelectedPlanId] = useState("");
  const [report, setReport] = useState<ReportProjection | null>(null);
  const [tab, setTab] = useState<(typeof TABS)[number]>("خلاصه");

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

  if (loading) return <LoadingState />;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="text-3xl font-black">گزارش‌ها</h1>
          <p className="mt-2 text-sm text-muted-foreground">فقط دادهٔ همان دوره انتخاب‌شده.</p>
        </div>
        <select className="h-10 rounded-md border bg-background px-3 text-sm" value={selectedPlanId} onChange={async (e) => {
          setSelectedPlanId(e.target.value);
          setReport(await loadReport(e.target.value));
        }}>
          {plans.map((plan) => <option key={plan.id} value={plan.id}>{JomaCalendarService.formatPeriodLabel(plan.periodKey)}</option>)}
        </select>
      </div>
      {error && <p className="text-sm text-destructive">{error}</p>}
      {!report ? <p>دوره‌ای نیست.</p> : (
        <>
          <div className="flex flex-wrap gap-2">
            {TABS.map((item) => (
              <button key={item} type="button" className={cn("rounded-full px-3 py-1 text-sm", tab === item ? "bg-primary text-white" : "bg-white")} onClick={() => setTab(item)}>
                {item}
              </button>
            ))}
          </div>
          {tab === "خلاصه" && <Summary report={report} />}
          {tab === "فعالیت‌ها" && <Activities report={report} />}
          {tab === "تقویم" && <Calendar report={report} />}
          {tab === "روند" && <Trend report={report} />}
          {tab === "خلق" && <MoodCharts report={report} />}
          {tab === "جزئیات" && <Details report={report} />}
        </>
      )}
    </div>
  );
}

function Summary({ report }: { report: ReportProjection }) {
  return (
    <div className="space-y-4">
      <UnspecifiedNotice>فرمول Achievement و موفقیت کلی تعریف نشده است. درصد ساختگی نشان داده نمی‌شود.</UnspecifiedNotice>
      <div className="grid gap-4 md:grid-cols-3">
        <div className="joma-card p-5"><div className="text-sm text-muted-foreground">رویدادها</div><div className="mt-2 text-3xl font-black">{JomaCalendarService.toPersianDigits(report.sourceEventCount)}</div></div>
        <div className="joma-card p-5"><div className="text-sm text-muted-foreground">فعالیت‌ها</div><div className="mt-2 text-3xl font-black">{JomaCalendarService.toPersianDigits(report.activities.length)}</div></div>
        <div className="joma-card p-5"><div className="text-sm text-muted-foreground">روزهای ثبت خلق</div><div className="mt-2 text-3xl font-black">{JomaCalendarService.toPersianDigits(report.moodDates.length)}</div></div>
      </div>
    </div>
  );
}

function Activities({ report }: { report: ReportProjection }) {
  if (report.activities.length === 0) return <p className="text-sm text-muted-foreground">فعالیتی نیست.</p>;
  return (
    <div className="space-y-3">
      {report.activities.map((row) => (
        <div key={row.planActivityId} className="joma-card flex items-center justify-between gap-3 p-4">
          <div className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl text-2xl" style={{ background: row.color }}>{row.sticker}</div>
            <div>
              <div className="font-bold">{row.name}</div>
              <div className="text-xs text-muted-foreground">{FREQUENCY_LABEL[row.frequency]} · {formatValue(row.dataType as never, row.actual)} / {formatValue(row.dataType as never, row.targetValue)}</div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}

function Calendar({ report }: { report: ReportProjection }) {
  const first = report.calendarDays[0];
  const pad = first ? JomaCalendarService.weekdayIndexFromSaturday(first.date) : 0;
  return (
    <div className="joma-card p-5">
      <div className="grid grid-cols-7 gap-2 text-center text-xs text-muted-foreground">
        {["ش", "ی", "د", "س", "چ", "پ", "ج"].map((day) => <div key={day}>{day}</div>)}
      </div>
      <div className="mt-2 grid grid-cols-7 gap-2">
        {Array.from({ length: pad }).map((_, index) => <div key={index} />)}
        {report.calendarDays.map((day) => (
          <div key={day.date} className={cn("rounded-xl p-2 text-center text-xs", day.eventCount || day.hasMood ? "bg-primary/10" : "bg-muted/40")}>
            <div className="font-bold">{JomaCalendarService.toPersianDigits(Number(day.date.slice(8)))}</div>
            {day.eventCount > 0 && <div className="text-primary">●</div>}
            {day.hasMood && <div>🌸</div>}
          </div>
        ))}
      </div>
    </div>
  );
}

function Trend({ report }: { report: ReportProjection }) {
  const data = report.calendarDays.filter((day) => day.eventCount > 0).map((day) => ({
    date: JomaCalendarService.toPersianDigits(day.date.slice(8)),
    actual: day.actualTotal,
  }));
  if (data.length === 0) return <p className="text-sm text-muted-foreground">هنوز عملکردی ثبت نشده است.</p>;
  return (
    <div className="joma-card h-72 p-4" dir="ltr">
      <ResponsiveContainer width="100%" height="100%">
        <LineChart data={data}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis />
          <Tooltip />
          <Line type="monotone" dataKey="actual" stroke="#7c5cbf" strokeWidth={3} />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
}

function MoodCharts({ report }: { report: ReportProjection }) {
  if (report.moodSeries.length === 0) return <p className="text-sm text-muted-foreground">خلق این دوره ثبت نشده است.</p>;
  const data = report.moodSeries.map((item) => ({
    date: JomaCalendarService.toPersianDigits(item.date.slice(8)),
    ...item.scores,
  }));
  return (
    <div className="space-y-4">
      {MOOD_METRICS.map((metric) => (
        <div key={metric.key} className="joma-card p-4">
          <h3 className="mb-3 font-bold">{metric.title}</h3>
          <div className="h-48" dir="ltr">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={data}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="date" />
                <YAxis domain={[1, 5]} />
                <Tooltip />
                <Line type="monotone" dataKey={metric.key} stroke="#7c5cbf" strokeWidth={3} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
      ))}
    </div>
  );
}

function Details({ report }: { report: ReportProjection }) {
  return (
    <div className="space-y-4">
      {report.activities.map((row) => (
        <div key={row.planActivityId} className="joma-card p-4">
          <div className="font-bold">{row.sticker} {row.name}</div>
          <p className="mt-1 text-xs text-muted-foreground">رویداد خام ← مقدار واقعی ← تحقق (تعریف‌نشده) ← وزن {row.weight}</p>
          <ul className="mt-3 space-y-1 text-sm">
            {row.events.map((event) => (
              <li key={event.id}>{JomaCalendarService.formatJalaliDisplay(event.performanceDate)} · {event.actualValue}</li>
            ))}
            {row.events.length === 0 && <li className="text-muted-foreground">رویدادی نیست.</li>}
          </ul>
        </div>
      ))}
    </div>
  );
}
