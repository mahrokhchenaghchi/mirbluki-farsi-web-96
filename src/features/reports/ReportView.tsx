import { useMemo, useState } from "react";
import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import type { ReportProjection } from "@/reporting/types";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { EmptyState } from "@/components/joma/EmptyState";
import { FrequencyBadge } from "@/components/joma/StatusBadge";
import { UnspecifiedNotice } from "@/components/joma/UnspecifiedNotice";
import { cn } from "@/lib/utils";

export function ReportView({ report }: { report: ReportProjection }) {
  const [selectedId, setSelectedId] = useState<string | null>(report.activities[0]?.planActivityId ?? null);
  const selected = report.activities.find((item) => item.planActivityId === selectedId) ?? null;

  const firstWeekday = useMemo(() => {
    const first = report.calendarDays[0];
    return first ? JomaCalendarService.weekdayIndexFromSaturday(first.date) : 0;
  }, [report.calendarDays]);

  const chartData = report.calendarDays
    .filter((day) => day.eventCount > 0)
    .map((day) => ({
      date: JomaCalendarService.toPersianDigits(day.date.slice(8)),
      actual: day.actualTotal,
    }));

  return (
    <div className="space-y-8">
      <UnspecifiedNotice>
        فرمول Achievement و موفقیت کلی برنامه در مشخصات رسمی امتیازدهی تعریف نشده است.
        این گزارش فقط مقدار واقعی، وزن ذخیره‌شده و مسیر رویدادها را نشان می‌دهد.
      </UnspecifiedNotice>

      <section className="grid gap-4 md:grid-cols-3">
        <div className="rounded-2xl border bg-card p-5">
          <div className="text-sm text-muted-foreground">تعداد رویدادها</div>
          <div className="mt-2 text-3xl font-bold">
            {JomaCalendarService.toPersianDigits(report.sourceEventCount)}
          </div>
        </div>
        <div className="rounded-2xl border bg-card p-5">
          <div className="text-sm text-muted-foreground">فعالیت‌های برنامه</div>
          <div className="mt-2 text-3xl font-bold">
            {JomaCalendarService.toPersianDigits(report.activities.length)}
          </div>
        </div>
        <div className="rounded-2xl border bg-card p-5">
          <div className="text-sm text-muted-foreground">موفقیت کلی</div>
          <div className="mt-2 text-lg font-bold">تعریف‌نشده</div>
        </div>
      </section>

      <section className="rounded-2xl border bg-card p-5">
        <h2 className="font-bold">نمودار مقدار واقعی</h2>
        {chartData.length === 0 ? (
          <p className="mt-4 text-sm text-muted-foreground">هنوز عملکردی برای این دوره ثبت نشده است.</p>
        ) : (
          <div className="mt-4 h-64" dir="ltr">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="date" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="actual" fill="hsl(210 100% 45%)" radius={[6, 6, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        )}
      </section>

      <section className="rounded-2xl border bg-card p-5">
        <h2 className="font-bold">گزارش تقویمی</h2>
        <div className="mt-4 grid grid-cols-7 gap-2 text-center text-xs text-muted-foreground">
          {["ش", "ی", "د", "س", "چ", "پ", "ج"].map((day) => (
            <div key={day}>{day}</div>
          ))}
        </div>
        <div className="mt-2 grid grid-cols-7 gap-2">
          {Array.from({ length: firstWeekday }).map((_, index) => (
            <div key={`pad-${index}`} />
          ))}
          {report.calendarDays.map((day) => (
            <div
              key={day.date}
              className={cn(
                "rounded-xl border px-1 py-2 text-center text-xs",
                day.eventCount > 0 ? "border-primary/30 bg-primary/5" : "bg-muted/30",
              )}
            >
              <div className="font-semibold">{JomaCalendarService.toPersianDigits(Number(day.date.slice(8)))}</div>
              {day.eventCount > 0 && (
                <div className="mt-1 text-[10px] text-primary">
                  {JomaCalendarService.toPersianDigits(day.eventCount)} رویداد
                </div>
              )}
              {day.hasMood && <div className="mt-1 text-[10px] text-amber-700">خلق</div>}
            </div>
          ))}
        </div>
      </section>

      <section className="space-y-3">
        <h2 className="font-bold">جزئیات فعالیت‌ها</h2>
        {report.activities.length === 0 ? (
          <EmptyState title="فعالیتی نیست" description="برای این دوره برنامه یا رویدادی وجود ندارد." />
        ) : (
          report.activities.map((row) => (
            <button
              key={row.planActivityId}
              type="button"
              onClick={() => setSelectedId(row.planActivityId)}
              className={cn(
                "w-full rounded-2xl border bg-card p-4 text-right",
                selectedId === row.planActivityId && "ring-2 ring-primary/30",
              )}
            >
              <div className="flex items-center justify-between gap-3">
                <div>
                  <div className="font-medium">{row.title}</div>
                  <div className="mt-1 text-xs text-muted-foreground">{row.activityCode}</div>
                </div>
                <FrequencyBadge frequency={row.frequency} />
              </div>
              <div className="mt-3 grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
                <div>مقدار: {JomaCalendarService.toPersianDigits(row.actual)}</div>
                <div>هدف: {JomaCalendarService.toPersianDigits(row.targetValue)}</div>
                <div>وزن: {JomaCalendarService.toPersianDigits(row.weight)}</div>
                <div>تحقق: تعریف‌نشده</div>
              </div>
            </button>
          ))
        )}
      </section>

      {selected && (
        <section className="rounded-2xl border bg-card p-5">
          <h3 className="font-bold">مسیر داده: {selected.title}</h3>
          <p className="mt-2 text-sm leading-8 text-muted-foreground">
            رویداد خام ← مقدار واقعی ← تحقق (تعریف‌نشده) ← وزن ذخیره‌شده ← نتیجه نهایی (تعریف‌نشده)
          </p>
          {selected.events.length === 0 ? (
            <p className="mt-4 text-sm text-muted-foreground">رویدادی برای این فعالیت نیست.</p>
          ) : (
            <ul className="mt-4 space-y-2">
              {selected.events.map((event) => (
                <li key={event.id} className="rounded-xl bg-muted/50 px-4 py-3 text-sm">
                  <div>{JomaCalendarService.formatJalaliDisplay(event.performanceDate)}</div>
                  <div className="text-muted-foreground">
                    مقدار {JomaCalendarService.toPersianDigits(event.actualValue)} ·{" "}
                    <span dir="ltr">{event.id.slice(0, 8)}</span>
                  </div>
                </li>
              ))}
            </ul>
          )}
        </section>
      )}
    </div>
  );
}
