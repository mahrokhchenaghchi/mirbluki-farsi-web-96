import { useEffect, useState } from "react";
import { useSearchParams, Link } from "react-router-dom";
import {
  ClipboardList, CheckCircle2, ChefHat, Bike, PartyPopper,
  XCircle, Search, ShoppingBag, MapPin, Phone, CreditCard, Clock,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { useOrderByCode } from "@/hooks/useData";
import {
  faDigits, faDateTime, faTime, ORDER_STATUS_LABEL,
  ORDER_TYPE_LABEL, PAYMENT_LABEL, toman,
} from "@/lib/format";
import type { Order, OrderStatus } from "@/lib/types";
import { cn } from "@/lib/utils";

const STEPS: Array<{ status: OrderStatus; label: string; icon: typeof CheckCircle2 }> = [
  { status: "pending", label: "ثبت سفارش", icon: ClipboardList },
  { status: "confirmed", label: "تایید رستوران", icon: CheckCircle2 },
  { status: "preparing", label: "آماده‌سازی", icon: ChefHat },
  { status: "delivering", label: "ارسال", icon: Bike },
  { status: "done", label: "تحویل", icon: PartyPopper },
];

export default function Track() {
  const [params, setParams] = useSearchParams();
  const [input, setInput] = useState(params.get("code") ?? "");
  const code = params.get("code");
  const isNew = params.get("new") === "1";

  const { data: order, isLoading, isError } = useOrderByCode(code || undefined);

  useEffect(() => {
    if (code) setInput(code);
  }, [code]);

  const search = () => {
    const c = input.trim().toUpperCase();
    if (!c) return;
    setParams({ code: c });
  };

  return (
    <div className="container max-w-3xl py-12">
      {/* جستجوی کد سفارش */}
      <div className="text-center">
        <p className="text-xs font-bold uppercase tracking-wider text-primary">پیگیری سفارش</p>
        <h1 className="mt-2 text-3xl font-black">سفارشت کجاست؟ 🛵</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          کد سفارش (مثل <span className="font-mono font-bold" dir="ltr">RP-1042</span>) را وارد کنید تا وضعیت لحظه‌ای آن را ببینید.
        </p>
        <div className="mx-auto mt-6 flex max-w-sm gap-2">
          <div className="relative flex-1">
            <Search className="absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              value={input}
              onChange={(e) => setInput(e.target.value)}
              onKeyDown={(e) => e.key === "Enter" && search()}
              placeholder="کد سفارش"
              dir="ltr"
              className="h-12 rounded-full pr-11 text-center font-mono uppercase"
            />
          </div>
          <Button size="lg" className="rounded-full px-8" onClick={search}>
            پیگیری
          </Button>
        </div>
      </div>

      {/* بنر موفقیت سفارش جدید */}
      {isNew && order && (
        <div className="mt-8 flex items-center gap-3 rounded-2xl border border-accent/30 bg-accent/10 p-4 text-accent">
          <PartyPopper className="h-8 w-8 shrink-0" />
          <div>
            <p className="font-extrabold">سفارش شما با موفقیت ثبت شد!</p>
            <p className="text-xs opacity-80">
              کد سفارش <span className="font-mono font-bold" dir="ltr">{order.code}</span> را نگه دارید. این صفحه هر چند ثانیه به‌روزرسانی می‌شود.
            </p>
          </div>
        </div>
      )}

      {/* نتایج */}
      <div className="mt-8">
        {!code && (
          <div className="rounded-3xl border border-dashed p-10 text-center text-sm text-muted-foreground">
            📦 سفارش‌های خود را از بخش <Link to="/account" className="font-bold text-primary hover:underline">حساب کاربری</Link> هم می‌توانید ببینید.
          </div>
        )}

        {code && isLoading && (
          <div className="space-y-4">
            <Skeleton className="h-40 w-full rounded-3xl" />
            <Skeleton className="h-64 w-full rounded-3xl" />
          </div>
        )}

        {code && !isLoading && (!order || isError) && (
          <div className="rounded-3xl border p-10 text-center">
            <span className="text-5xl">🤔</span>
            <p className="mt-3 font-bold">سفارشی با این کد پیدا نشد</p>
            <p className="mt-1 text-sm text-muted-foreground">
              کد را دوباره بررسی کنید. کد سفارش در پیامک تایید و بخش سفارش‌های من موجود است.
            </p>
          </div>
        )}

        {code && !isLoading && order && <OrderTimeline order={order} />}
      </div>
    </div>
  );
}

function OrderTimeline({ order }: { order: Order }) {
  const canceled = order.status === "canceled";
  // برای سفارش حضوری، مرحله «ارسال» حذف می‌شود
  const steps = STEPS.filter((s) => order.type === "delivery" || s.status !== "delivering");
  const currentIdx = steps.findIndex((s) => s.status === order.status);
  const historyFor = (s: OrderStatus) =>
    order.status_history.find((h) => h.status === s)?.at;

  return (
    <div className="space-y-6">
      {/* کارت وضعیت */}
      <Card className="overflow-hidden">
        <CardHeader className="flex flex-row items-center justify-between space-y-0 bg-secondary/50 pb-4">
          <div>
            <CardTitle className="text-lg">
              سفارش <span className="font-mono text-primary" dir="ltr">{order.code}</span>
            </CardTitle>
            <p className="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
              <Clock className="h-3.5 w-3.5" />
              {faDateTime(order.created_at)} — {ORDER_TYPE_LABEL[order.type]}
            </p>
          </div>
          <Badge
            className={
              order.status === "done"
                ? "bg-emerald-600 text-white"
                : order.status === "canceled"
                  ? "bg-rose-600 text-white"
                  : "bg-primary text-white"
            }
          >
            {ORDER_STATUS_LABEL[order.status]}
          </Badge>
        </CardHeader>
        <CardContent className="pt-6">
          {canceled ? (
            <div className="flex items-center gap-3 rounded-2xl bg-rose-50 p-4 text-rose-700">
              <XCircle className="h-8 w-8 shrink-0" />
              <div>
                <p className="font-extrabold">این سفارش لغو شده است</p>
                <p className="text-xs opacity-80">برای اطلاعات بیشتر با رستوران تماس بگیرید.</p>
              </div>
            </div>
          ) : (
            <>
              {/* تایم‌لاین */}
              <div className="flex items-start">
                {steps.map((s, i) => {
                  const done = i < currentIdx;
                  const active = i === currentIdx;
                  const at = historyFor(s.status);
                  return (
                    <div key={s.status} className="relative flex flex-1 flex-col items-center">
                      {i > 0 && (
                        <span
                          className={cn(
                            "absolute right-1/2 top-5 -mr-[0.5px] h-0.5 w-full",
                            i <= currentIdx ? "bg-primary" : "bg-border"
                          )}
                        />
                      )}
                      <span
                        className={cn(
                          "relative z-10 flex h-10 w-10 items-center justify-center rounded-full border-2 transition",
                          done && "border-primary bg-primary text-primary-foreground",
                          active && "border-primary bg-card text-primary",
                          !done && !active && "border-border bg-card text-muted-foreground"
                        )}
                      >
                        <s.icon className={cn("h-5 w-5", active && "animate-pulse-soft")} />
                      </span>
                      <span
                        className={cn(
                          "mt-2 text-center text-[11px] leading-4 font-bold",
                          done || active ? "text-foreground" : "text-muted-foreground"
                        )}
                      >
                        {s.label}
                        {at && (done || active) && (
                          <span className="block text-[10px] font-normal text-muted-foreground">
                            {faTime(at)}
                          </span>
                        )}
                      </span>
                    </div>
                  );
                })}
              </div>

              {order.status === "preparing" && (
                <p className="mt-5 rounded-xl bg-secondary/60 p-3 text-center text-xs leading-6 text-muted-foreground">
                  👨‍🍳 آشپز ما در حال پخت سفارش شماست؛ تقریباً {faDigits(15)} دقیقه دیگر آماده می‌شود.
                </p>
              )}
              {order.status === "delivering" && (
                <p className="mt-5 rounded-xl bg-secondary/60 p-3 text-center text-xs leading-6 text-muted-foreground">
                  🛵 پیک ما در راه است! لطفاً گوشی خود را در دسترس نگه دارید.
                </p>
              )}
            </>
          )}
        </CardContent>
      </Card>

      {/* جزئیات سفارش */}
      <Card>
        <CardHeader className="pb-3">
          <CardTitle className="flex items-center gap-2 text-base">
            <ShoppingBag className="h-4.5 w-4.5 text-primary" />
            جزئیات سفارش
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2.5">
            {order.items.map((it, i) => (
              <div key={i} className="flex items-center justify-between gap-3 text-sm">
                <div className="flex min-w-0 items-center gap-2.5">
                  {it.image && (
                    <img src={it.image} alt="" className="h-10 w-10 shrink-0 rounded-lg object-cover" loading="lazy" />
                  )}
                  <div className="min-w-0">
                    <p className="truncate font-medium">
                      {it.product_name}
                      <span className="mr-1.5 text-xs text-muted-foreground">× {faDigits(it.qty)}</span>
                    </p>
                    <p className="text-[11px] text-muted-foreground">{it.size_name}</p>
                  </div>
                </div>
                <span className="shrink-0 text-muted-foreground">{toman(it.unit_price * it.qty)}</span>
              </div>
            ))}
          </div>

          <div className="grid gap-2 rounded-2xl bg-secondary/50 p-4 text-xs text-muted-foreground">
            <div className="flex justify-between"><span>جمع اقلام</span><span>{toman(order.subtotal)} تومان</span></div>
            {order.discount > 0 && (
              <div className="flex justify-between text-accent"><span>تخفیف {order.coupon_code ? `(${order.coupon_code})` : ""}</span><span>− {toman(order.discount)} تومان</span></div>
            )}
            <div className="flex justify-between">
              <span>هزینه ارسال</span>
              <span>{order.delivery_fee === 0 ? "رایگان" : `${toman(order.delivery_fee)} تومان`}</span>
            </div>
            <div className="flex justify-between border-t border-border pt-2 text-sm font-extrabold text-foreground">
              <span>مبلغ کل</span>
              <span className="text-primary">{toman(order.total)} تومان</span>
            </div>
          </div>

          <div className="grid gap-2 text-xs text-muted-foreground sm:grid-cols-2">
            <p className="flex items-center gap-1.5"><MapPin className="h-3.5 w-3.5 shrink-0 text-primary" /> {order.branch_name}</p>
            <p className="flex items-center gap-1.5"><Phone className="h-3.5 w-3.5 shrink-0 text-primary" /> {faDigits(order.customer_phone)}</p>
            <p className="flex items-center gap-1.5"><CreditCard className="h-3.5 w-3.5 shrink-0 text-primary" /> {PAYMENT_LABEL[order.payment_method]}</p>
            {order.address && <p className="col-span-full">📍 {order.address}</p>}
            {order.note && <p className="col-span-full">📝 {order.note}</p>}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
