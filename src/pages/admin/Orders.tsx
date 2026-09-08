import { useEffect, useMemo, useState } from "react";
import { useSearchParams } from "react-router-dom";
import {
  Search, Phone, MapPin, CheckCircle2, ChefHat, Bike, PartyPopper,
  XCircle, Clock, CreditCard, Printer, StickyNote,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";
import { useOrders, useBranches, useInvalidate } from "@/hooks/useData";
import { api } from "@/lib/db";
import {
  faDateTime, faDigits, enDigits, ORDER_STATUS_COLOR, ORDER_STATUS_LABEL,
  ORDER_TYPE_LABEL, PAYMENT_LABEL, toman,
} from "@/lib/format";
import type { Order, OrderStatus } from "@/lib/types";
import { cn } from "@/lib/utils";

const STATUS_TABS: Array<{ v: string; label: string }> = [
  { v: "all", label: "همه" },
  { v: "pending", label: "در انتظار تایید" },
  { v: "confirmed", label: "تایید شده" },
  { v: "preparing", label: "آماده‌سازی" },
  { v: "delivering", label: "در مسیر" },
  { v: "done", label: "تحویل شده" },
  { v: "canceled", label: "لغو شده" },
];

const NEXT_ACTIONS: Partial<Record<OrderStatus, Array<{ to: OrderStatus; label: string; icon: typeof CheckCircle2; variant?: "default" | "destructive" | "secondary" }>>> = {
  pending: [
    { to: "confirmed", label: "تایید سفارش", icon: CheckCircle2 },
    { to: "canceled", label: "لغو سفارش", icon: XCircle, variant: "destructive" },
  ],
  confirmed: [
    { to: "preparing", label: "شروع آماده‌سازی", icon: ChefHat },
    { to: "canceled", label: "لغو سفارش", icon: XCircle, variant: "destructive" },
  ],
  preparing: [
    { to: "delivering", label: "تحویل به پیک", icon: Bike },
    { to: "done", label: "آماده تحویل", icon: PartyPopper },
  ],
  delivering: [{ to: "done", label: "تحویل شد", icon: PartyPopper }],
  done: [],
  canceled: [],
};

export default function AdminOrders() {
  const { data: orders, isLoading } = useOrders(true);
  const { data: branches } = useBranches();
  const invalidate = useInvalidate();
  const [params, setParams] = useSearchParams();

  const [tab, setTab] = useState("all");
  const [branchFilter, setBranchFilter] = useState("all");
  const [q, setQ] = useState("");
  const [selected, setSelected] = useState<Order | null>(null);

  // باز کردن خودکار سفارش از کوئری‌استرینگ (لینک از داشبورد)
  useEffect(() => {
    const code = params.get("code");
    if (code && orders) {
      const found = orders.find((o) => o.code === code);
      if (found) setSelected(found);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [params, orders]);

  const filtered = useMemo(() => {
    let list = orders ?? [];
    if (tab !== "all") list = list.filter((o) => o.status === tab);
    if (branchFilter !== "all") list = list.filter((o) => o.branch_id === branchFilter);
    const query = enDigits(q).trim().toLowerCase();
    if (query) {
      list = list.filter(
        (o) =>
          o.code.toLowerCase().includes(query) ||
          o.customer_name.includes(q) ||
          o.customer_phone.includes(query)
      );
    }
    return list;
  }, [orders, tab, branchFilter, q]);

  const counts = useMemo(() => {
    const c: Record<string, number> = { all: (orders ?? []).length };
    (orders ?? []).forEach((o) => { c[o.status] = (c[o.status] ?? 0) + 1; });
    return c;
  }, [orders]);

  const changeStatus = async (order: Order, to: OrderStatus) => {
    await api.updateOrderStatus(order.id, to);
    await invalidate();
    setSelected((s) => (s ? { ...s, status: to } : s));
  };

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-black">سفارش‌ها</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            مدیریت و پیگیری سفارش‌ها — به‌روزرسانی خودکار هر ۸ ثانیه
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <div className="relative">
            <Search className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder="کد سفارش، نام یا شماره..."
              className="h-10 w-56 rounded-full pr-9 text-sm"
            />
          </div>
          <Select value={branchFilter} onValueChange={setBranchFilter} dir="rtl">
            <SelectTrigger className="h-10 w-44 rounded-full text-sm">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">همه شعب</SelectItem>
              {(branches ?? []).map((b) => (
                <SelectItem key={b.id} value={b.id}>{b.name}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      {/* تب‌های وضعیت */}
      <div className="flex gap-2 overflow-x-auto no-scrollbar pb-1">
        {STATUS_TABS.map((t) => (
          <button
            key={t.v}
            onClick={() => setTab(t.v)}
            className={cn(
              "shrink-0 rounded-full border px-4 py-1.5 text-xs font-bold transition",
              tab === t.v
                ? "border-primary bg-primary text-primary-foreground"
                : "border-border bg-card text-muted-foreground hover:border-primary/40"
            )}
          >
            {t.label}
            <span className={cn("mr-1.5 rounded-full px-1.5 text-[10px]", tab === t.v ? "bg-white/20" : "bg-secondary")}>
              {faDigits(counts[t.v] ?? 0)}
            </span>
          </button>
        ))}
      </div>

      {/* لیست */}
      {isLoading ? (
        <div className="space-y-3">
          {Array.from({ length: 5 }).map((_, i) => <Skeleton key={i} className="h-24 rounded-2xl" />)}
        </div>
      ) : filtered.length === 0 ? (
        <div className="rounded-3xl border border-dashed p-14 text-center">
          <span className="text-5xl">📭</span>
          <p className="mt-3 font-bold">سفارشی با این فیلترها پیدا نشد</p>
          <p className="mt-1 text-sm text-muted-foreground">فیلتر وضعیت یا شعبه را تغییر دهید.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {filtered.map((o) => (
            <button
              key={o.id}
              onClick={() => setSelected(o)}
              className="card-hover flex w-full flex-wrap items-center justify-between gap-3 rounded-2xl border bg-card p-4 text-right"
            >
              <div className="flex min-w-0 items-center gap-3">
                <span className="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-secondary text-[10px] font-bold leading-tight">
                  <span>{faDigits(new Date(o.created_at).toLocaleDateString("fa-IR", { day: "numeric" }))}</span>
                  <span className="text-muted-foreground">{faDigits(new Date(o.created_at).toLocaleDateString("fa-IR", { month: "short" }))}</span>
                </span>
                <div className="min-w-0">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-mono font-bold" dir="ltr">{o.code}</span>
                    <span className={cn("rounded-full border px-2 py-0.5 text-[10px] font-bold", ORDER_STATUS_COLOR[o.status])}>
                      {ORDER_STATUS_LABEL[o.status]}
                    </span>
                    <Badge variant="outline" className="h-5 text-[10px]">{ORDER_TYPE_LABEL[o.type]}</Badge>
                  </div>
                  <p className="mt-1 truncate text-xs text-muted-foreground">
                    {o.customer_name} — {faDigits(o.customer_phone)} — {o.branch_name}
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-4">
                <div className="hidden text-left sm:block">
                  <p className="text-[10px] text-muted-foreground">{faDigits(o.items.reduce((s, i) => s + i.qty, 0))} قلم</p>
                  <p className="text-sm font-extrabold text-primary">{toman(o.total / 1000)} هزار</p>
                </div>
                {NEXT_ACTIONS[o.status]?.[0] && (
                  <span className="rounded-full bg-primary/10 px-3 py-1.5 text-[11px] font-bold text-primary">
                    {NEXT_ACTIONS[o.status]![0].label}
                  </span>
                )}
              </div>
            </button>
          ))}
        </div>
      )}

      {/* دیالوگ جزئیات */}
      <OrderDialog
        order={selected}
        onClose={() => {
          setSelected(null);
          if (params.get("code")) setParams({});
        }}
        onStatus={changeStatus}
      />
    </div>
  );
}

function OrderDialog({
  order, onClose, onStatus,
}: {
  order: Order | null;
  onClose: () => void;
  onStatus: (o: Order, to: OrderStatus) => Promise<void>;
}) {
  if (!order) return null;

  const actions = NEXT_ACTIONS[order.status] ?? [];

  return (
    <Dialog open={!!order} onOpenChange={(v) => !v && onClose()}>
      <DialogContent className="max-h-[92dvh] overflow-y-auto sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle className="flex flex-wrap items-center gap-2">
            سفارش <span className="font-mono text-primary" dir="ltr">{order.code}</span>
            <span className={cn("rounded-full border px-2.5 py-0.5 text-xs font-bold", ORDER_STATUS_COLOR[order.status])}>
              {ORDER_STATUS_LABEL[order.status]}
            </span>
          </DialogTitle>
          <DialogDescription className="flex flex-wrap items-center gap-x-4 gap-y-1">
            <span className="flex items-center gap-1"><Clock className="h-3.5 w-3.5" /> {faDateTime(order.created_at)}</span>
            <span>{ORDER_TYPE_LABEL[order.type]}</span>
            <span>{order.branch_name}</span>
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          {/* مشتری */}
          <div className="grid gap-3 rounded-2xl bg-secondary/50 p-4 text-sm sm:grid-cols-2">
            <p><span className="text-muted-foreground">مشتری: </span><span className="font-bold">{order.customer_name}</span></p>
            <p className="flex items-center gap-1.5">
              <Phone className="h-3.5 w-3.5 text-primary" />
              <a href={`tel:${order.customer_phone}`} className="font-bold text-primary hover:underline" dir="ltr">
                {faDigits(order.customer_phone)}
              </a>
            </p>
            {order.address && (
              <p className="col-span-full flex items-start gap-1.5">
                <MapPin className="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary" /> {order.address}
              </p>
            )}
            <p className="flex items-center gap-1.5">
              <CreditCard className="h-3.5 w-3.5 text-primary" /> {PAYMENT_LABEL[order.payment_method]}
            </p>
          </div>

          {/* اقلام */}
          <div className="rounded-2xl border p-4">
            <h4 className="mb-3 text-sm font-extrabold">اقلام سفارش</h4>
            <div className="space-y-2.5">
              {order.items.map((it, i) => (
                <div key={i} className="flex items-center justify-between gap-3 text-sm">
                  <div className="flex min-w-0 items-center gap-2.5">
                    {it.image && <img src={it.image} alt="" className="h-10 w-10 shrink-0 rounded-lg object-cover" loading="lazy" />}
                    <div className="min-w-0">
                      <p className="truncate font-medium">
                        {it.product_name}
                        <span className="mr-1 text-xs text-muted-foreground">× {faDigits(it.qty)}</span>
                      </p>
                      <p className="text-[11px] text-muted-foreground">{it.size_name}</p>
                      {it.note && <p className="text-[11px] text-amber-600">📝 {it.note}</p>}
                    </div>
                  </div>
                  <span className="shrink-0 text-xs text-muted-foreground">{toman(it.unit_price * it.qty / 1000)} هزار</span>
                </div>
              ))}
            </div>
            {order.note && (
              <p className="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 p-2.5 text-xs leading-5 text-amber-700">
                <StickyNote className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                یادداشت مشتری: {order.note}
              </p>
            )}
          </div>

          {/* مبلغ */}
          <div className="grid gap-2 rounded-2xl bg-secondary/50 p-4 text-sm">
            <div className="flex justify-between"><span className="text-muted-foreground">جمع اقلام</span><span>{toman(order.subtotal)} تومان</span></div>
            {order.discount > 0 && (
              <div className="flex justify-between text-accent">
                <span>تخفیف {order.coupon_code ? `(${order.coupon_code})` : ""}</span>
                <span>− {toman(order.discount)} تومان</span>
              </div>
            )}
            <div className="flex justify-between">
              <span className="text-muted-foreground">ارسال</span>
              <span>{order.delivery_fee === 0 ? "رایگان" : `${toman(order.delivery_fee)} تومان`}</span>
            </div>
            <div className="flex justify-between border-t border-border pt-2 font-extrabold">
              <span>مبلغ کل</span>
              <span className="text-primary">{toman(order.total)} تومان</span>
            </div>
          </div>

          {/* تاریخچه وضعیت */}
          <div className="rounded-2xl border p-4">
            <h4 className="mb-3 text-sm font-extrabold">تاریخچه وضعیت</h4>
            <div className="space-y-2">
              {order.status_history.map((h, i) => (
                <div key={i} className="flex items-center justify-between text-xs">
                  <span className={cn("rounded-full border px-2 py-0.5 font-bold", ORDER_STATUS_COLOR[h.status])}>
                    {ORDER_STATUS_LABEL[h.status]}
                  </span>
                  <span className="text-muted-foreground">{faDateTime(h.at)}</span>
                </div>
              ))}
            </div>
          </div>

          {/* اکشن‌ها */}
          <div className="flex flex-wrap gap-2 border-t pt-4">
            {actions.map((a) => (
              <Button
                key={a.to}
                variant={a.variant ?? "default"}
                className={cn(a.variant === "destructive" ? "" : "flex-1")}
                onClick={() => onStatus(order, a.to)}
              >
                <a.icon className="h-4 w-4 ml-1.5" />
                {a.label}
              </Button>
            ))}
            <Button variant="outline" onClick={() => window.print()} className="gap-1.5">
              <Printer className="h-4 w-4" /> چاپ
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
