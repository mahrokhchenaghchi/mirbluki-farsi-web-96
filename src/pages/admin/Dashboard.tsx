import { useMemo } from "react";
import { useNavigate } from "react-router-dom";
import {
  ClipboardList, Banknote, Timer, TrendingUp, ChevronLeft, Package,
} from "lucide-react";
import {
  ResponsiveContainer, AreaChart, Area, XAxis, YAxis, CartesianGrid,
  Tooltip, PieChart, Pie, Cell, BarChart, Bar,
} from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { useOrders, useBranches } from "@/hooks/useData";
import { faDate, faDigits, ORDER_STATUS_COLOR, ORDER_STATUS_LABEL, toman } from "@/lib/format";
import { cn } from "@/lib/utils";

const STATUS_COLORS: Record<string, string> = {
  pending: "#f59e0b",
  confirmed: "#3b82f6",
  preparing: "#8b5cf6",
  delivering: "#06b6d4",
  done: "#10b981",
  canceled: "#f43f5e",
};

export default function AdminDashboard() {
  const { data: orders, isLoading } = useOrders(true);
  const { data: branches } = useBranches();
  const navigate = useNavigate();

  const stats = useMemo(() => {
    const list = orders ?? [];
    const today = new Date().toDateString();
    const todays = list.filter((o) => new Date(o.created_at).toDateString() === today);
    const valid = todays.filter((o) => o.status !== "canceled");
    const revenue = valid.reduce((s, o) => s + o.total, 0);
    const active = list.filter((o) => !["done", "canceled"].includes(o.status));
    return {
      todayCount: todays.length,
      revenue,
      avg: valid.length ? Math.round(revenue / valid.length) : 0,
      activeCount: active.length,
      active,
    };
  }, [orders]);

  const weekData = useMemo(() => {
    const days: Array<{ label: string; orders: number; revenue: number }> = [];
    for (let i = 6; i >= 0; i--) {
      const d = new Date();
      d.setDate(d.getDate() - i);
      const key = d.toDateString();
      const dayOrders = (orders ?? []).filter(
        (o) => new Date(o.created_at).toDateString() === key && o.status !== "canceled"
      );
      days.push({
        label: faDigits(d.toLocaleDateString("fa-IR", { weekday: "short" })),
        orders: dayOrders.length,
        revenue: dayOrders.reduce((s, o) => s + o.total, 0) / 1000000,
      });
    }
    return days;
  }, [orders]);

  const statusData = useMemo(() => {
    const counts: Record<string, number> = {};
    (orders ?? []).forEach((o) => {
      counts[o.status] = (counts[o.status] ?? 0) + 1;
    });
    return Object.entries(counts).map(([status, value]) => ({
      name: ORDER_STATUS_LABEL[status as keyof typeof ORDER_STATUS_LABEL],
      status,
      value,
    }));
  }, [orders]);

  const topProducts = useMemo(() => {
    const counts: Record<string, { count: number; revenue: number }> = {};
    (orders ?? []).forEach((o) => {
      if (o.status === "canceled") return;
      o.items.forEach((it) => {
        if (!counts[it.product_name]) counts[it.product_name] = { count: 0, revenue: 0 };
        counts[it.product_name].count += it.qty;
        counts[it.product_name].revenue += it.unit_price * it.qty;
      });
    });
    return Object.entries(counts)
      .sort((a, b) => b[1].count - a[1].count)
      .slice(0, 6);
  }, [orders]);

  const recent = (orders ?? []).slice(0, 8);

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-28 rounded-2xl" />)}
        </div>
        <Skeleton className="h-80 rounded-2xl" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-black">داشبورد</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          نمای کلی عملکرد امروز — {faDate(new Date().toISOString())}
        </p>
      </div>

      {/* کارت‌های آماری */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard
          icon={ClipboardList}
          label="سفارش‌های امروز"
          value={faDigits(stats.todayCount)}
          sub="مورد ثبت‌شده"
          color="bg-blue-500"
        />
        <StatCard
          icon={Banknote}
          label="درآمد امروز"
          value={`${toman(stats.revenue / 1000)} هزار`}
          sub="تومان — بدون سفارش‌های لغوشده"
          color="bg-emerald-500"
        />
        <StatCard
          icon={Timer}
          label="در جریان"
          value={faDigits(stats.activeCount)}
          sub="سفارش فعال"
          color="bg-amber-500"
        />
        <StatCard
          icon={TrendingUp}
          label="میانگین سفارش"
          value={`${toman(stats.avg / 1000)} هزار`}
          sub="تومان"
          color="bg-violet-500"
        />
      </div>

      {/* نمودارها */}
      <div className="grid gap-4 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader className="pb-2">
            <CardTitle className="text-base">درآمد و سفارش‌های ۷ روز اخیر</CardTitle>
            <p className="text-xs text-muted-foreground">درآمد به میلیون تومان</p>
          </CardHeader>
          <CardContent className="h-72" dir="ltr">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={weekData} margin={{ top: 5, right: 5, left: -18, bottom: 0 }}>
                <defs>
                  <linearGradient id="rev" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="#d43a2b" stopOpacity={0.35} />
                    <stop offset="100%" stopColor="#d43a2b" stopOpacity={0.02} />
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#e7e0d8" />
                <XAxis dataKey="label" tick={{ fontSize: 12, fontFamily: "Vazirmatn" }} stroke="#a39b93" />
                <YAxis tick={{ fontSize: 11, fontFamily: "Vazirmatn" }} stroke="#a39b93" />
                <Tooltip
                  contentStyle={{ fontFamily: "Vazirmatn", direction: "rtl", borderRadius: 12, border: "1px solid #e7e0d8" }}
                  formatter={(value: number, name: string) =>
                    name === "درآمد (میلیون)" ? [`${faDigits(value)} میلیون`, "درآمد"] : [faDigits(value), "سفارش"]
                  }
                />
                <Area type="monotone" dataKey="revenue" stroke="#d43a2b" strokeWidth={2.5} fill="url(#rev)" name="درآمد (میلیون)" />
                <Bar dataKey="orders" fill="#f0c674" radius={[6, 6, 0, 0]} barSize={14} name="سفارش" />
              </AreaChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">سهم وضعیت سفارش‌ها</CardTitle>
          </CardHeader>
          <CardContent className="h-72" dir="ltr">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={statusData}
                  dataKey="value"
                  nameKey="name"
                  innerRadius={55}
                  outerRadius={85}
                  paddingAngle={3}
                >
                  {statusData.map((s) => (
                    <Cell key={s.status} fill={STATUS_COLORS[s.status]} />
                  ))}
                </Pie>
                <Tooltip
                  contentStyle={{ fontFamily: "Vazirmatn", direction: "rtl", borderRadius: 12 }}
                  formatter={(v: number) => [`${faDigits(v)} سفارش`, ""]}
                />
              </PieChart>
            </ResponsiveContainer>
            <div className="-mt-14 flex flex-wrap justify-center gap-2" dir="rtl">
              {statusData.map((s) => (
                <span key={s.status} className="flex items-center gap-1.5 text-[11px]">
                  <span className="h-2.5 w-2.5 rounded-full" style={{ background: STATUS_COLORS[s.status] }} />
                  {s.name} ({faDigits(s.value)})
                </span>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* پرفروش‌ها و سفارش‌های اخیر */}
      <div className="grid gap-4 lg:grid-cols-3">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">پرفروش‌ترین غذاها</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            {topProducts.length === 0 && (
              <p className="py-8 text-center text-sm text-muted-foreground">هنوز سفارشی ثبت نشده است.</p>
            )}
            {topProducts.map(([name, d], i) => (
              <div key={name} className="flex items-center gap-3">
                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-xs font-black text-primary">
                  {faDigits(i + 1)}
                </span>
                <div className="min-w-0 flex-1">
                  <p className="truncate text-sm font-bold">{name}</p>
                  <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-secondary">
                    <div
                      className="h-full rounded-full bg-primary"
                      style={{ width: `${Math.min(100, (d.count / topProducts[0][1].count) * 100)}%` }}
                    />
                  </div>
                </div>
                <span className="shrink-0 text-xs text-muted-foreground">{faDigits(d.count)} عدد</span>
              </div>
            ))}
          </CardContent>
        </Card>

        <Card className="lg:col-span-2">
          <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle className="text-base">آخرین سفارش‌ها</CardTitle>
            <button
              onClick={() => navigate("/admin/orders")}
              className="flex items-center gap-1 text-xs font-bold text-primary hover:opacity-80"
            >
              همه سفارش‌ها <ChevronLeft className="h-3.5 w-3.5" />
            </button>
          </CardHeader>
          <CardContent>
            {recent.length === 0 ? (
              <p className="py-8 text-center text-sm text-muted-foreground">
                هنوز سفارشی ثبت نشده است. از سایت یک سفارش تستی ثبت کنید!
              </p>
            ) : (
              <div className="space-y-2">
                {recent.map((o) => (
                  <button
                    key={o.id}
                    onClick={() => navigate(`/admin/orders?code=${o.code}`)}
                    className="flex w-full items-center justify-between gap-3 rounded-xl border bg-card px-4 py-3 text-right transition hover:border-primary/40"
                  >
                    <div className="flex min-w-0 items-center gap-3">
                      <span className="font-mono text-sm font-bold" dir="ltr">{o.code}</span>
                      <span className={cn("rounded-full border px-2 py-0.5 text-[10px] font-bold", ORDER_STATUS_COLOR[o.status])}>
                        {ORDER_STATUS_LABEL[o.status]}
                      </span>
                    </div>
                    <div className="flex shrink-0 items-center gap-4 text-xs text-muted-foreground">
                      <span className="hidden sm:inline">{faDigits(o.items.reduce((s, i) => s + i.qty, 0))} قلم</span>
                      <span className="font-extrabold text-foreground">{toman(o.total / 1000)} هزار</span>
                      <Package className="h-4 w-4" />
                    </div>
                  </button>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* شعب */}
      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base">وضعیت شعب</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-3 sm:grid-cols-3">
            {(branches ?? []).map((b) => {
              const count = (orders ?? []).filter((o) => o.branch_id === b.id).length;
              return (
                <div key={b.id} className="rounded-2xl border bg-card p-4">
                  <p className="text-sm font-bold">{b.name}</p>
                  <p className="mt-1 text-xs text-muted-foreground">
                    {faDigits(count)} سفارش ثبت‌شده — ارسال {toman(b.delivery_fee / 1000)} هزار تومان
                  </p>
                </div>
              );
            })}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

function StatCard({
  icon: Icon, label, value, sub, color,
}: {
  icon: typeof ClipboardList;
  label: string;
  value: string;
  sub: string;
  color: string;
}) {
  return (
    <Card className="card-hover">
      <CardContent className="flex items-center gap-4 p-5">
        <span className={cn("flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white", color)}>
          <Icon className="h-6 w-6" />
        </span>
        <div className="min-w-0">
          <p className="text-xs text-muted-foreground">{label}</p>
          <p className="truncate text-xl font-black">{value}</p>
          <p className="text-[10px] text-muted-foreground">{sub}</p>
        </div>
      </CardContent>
    </Card>
  );
}
