import { useMemo, useState } from "react";
import { Search, Users, Phone, Repeat, Banknote, MessageCircle } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Card, CardContent } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Badge } from "@/components/ui/badge";
import { useOrders } from "@/hooks/useData";
import { enDigits, faDate, faDigits, toman } from "@/lib/format";
import { cn } from "@/lib/utils";

export default function AdminCustomers() {
  const { data: orders, isLoading } = useOrders();
  const [q, setQ] = useState("");

  const customers = useMemo(() => {
    const map = new Map<
      string,
      { name: string; phone: string; count: number; spent: number; last: string; canceled: number }
    >();
    (orders ?? []).forEach((o) => {
      const key = o.customer_phone;
      const c =
        map.get(key) ??
        { name: o.customer_name, phone: o.customer_phone, count: 0, spent: 0, last: o.created_at, canceled: 0 };
      if (o.status === "canceled") {
        c.canceled++;
      } else {
        c.count++;
        c.spent += o.total;
      }
      if (new Date(o.created_at) > new Date(c.last)) c.last = o.created_at;
      map.set(key, c);
    });
    const list = Array.from(map.values()).sort((a, b) => b.spent - a.spent);
    const query = enDigits(q).trim().toLowerCase();
    if (query) {
      return list.filter(
        (c) => c.name.toLowerCase().includes(query) || c.phone.includes(query)
      );
    }
    return list;
  }, [orders, q]);

  const totalRevenue = customers.reduce((s, c) => s + c.spent, 0);

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-black">مشتریان</h1>
          <p className="mt-1 text-sm text-muted-foreground">بر اساس سفارش‌های ثبت‌شده به صورت خودکار به‌روز می‌شود</p>
        </div>
        <div className="relative">
          <Search className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="نام یا شماره..."
            className="h-10 w-56 rounded-full pr-9 text-sm"
          />
        </div>
      </div>

      {/* خلاصه */}
      <div className="grid gap-4 sm:grid-cols-3">
        {[
          { icon: Users, label: "کل مشتریان", value: faDigits(customers.length), color: "bg-blue-500" },
          { icon: Repeat, label: "مشتریان دوباره‌خرید", value: faDigits(customers.filter((c) => c.count > 1).length), color: "bg-violet-500" },
          { icon: Banknote, label: "مجموع فروش", value: `${toman(totalRevenue / 1000)} هزار تومان`, color: "bg-emerald-500" },
        ].map((s) => (
          <Card key={s.label}>
            <CardContent className="flex items-center gap-4 p-5">
              <span className={cn("flex h-12 w-12 items-center justify-center rounded-2xl text-white", s.color)}>
                <s.icon className="h-6 w-6" />
              </span>
              <div>
                <p className="text-xs text-muted-foreground">{s.label}</p>
                <p className="text-xl font-black">{s.value}</p>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* لیست */}
      {isLoading ? (
        <div className="space-y-3">
          {Array.from({ length: 5 }).map((_, i) => <Skeleton key={i} className="h-20 rounded-2xl" />)}
        </div>
      ) : customers.length === 0 ? (
        <div className="rounded-3xl border border-dashed p-14 text-center">
          <span className="text-5xl">👥</span>
          <p className="mt-3 font-bold">هنوز مشتری‌ای ثبت نشده است</p>
          <p className="mt-1 text-sm text-muted-foreground">با ثبت اولین سفارش، مشتریان اینجا نمایش داده می‌شوند.</p>
        </div>
      ) : (
        <div className="space-y-3">
          {customers.map((c, i) => (
            <div key={c.phone} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border bg-card p-4">
              <div className="flex min-w-0 items-center gap-3">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-black text-primary">
                  {faDigits(i + 1)}
                </span>
                <div className="min-w-0">
                  <div className="flex flex-wrap items-center gap-2">
                    <p className="truncate font-bold">{c.name}</p>
                    {c.count >= 3 && <Badge className="h-5 bg-amber-500 text-[10px] text-white">مشتری وفادار</Badge>}
                    {c.count === 0 && c.canceled > 0 && (
                      <Badge variant="secondary" className="h-5 text-[10px]">فقط سفارش لغوشده</Badge>
                    )}
                  </div>
                  <p className="mt-0.5 flex items-center gap-1 text-xs text-muted-foreground" dir="ltr">
                    <Phone className="h-3 w-3" /> {faDigits(c.phone)}
                  </p>
                </div>
              </div>
              <div className="flex flex-wrap items-center gap-x-6 gap-y-1 text-xs text-muted-foreground">
                <span>{faDigits(c.count)} سفارش موفق</span>
                <span className="font-extrabold text-foreground">{toman(c.spent / 1000)} هزار تومان</span>
                <span className="hidden sm:inline">آخرین: {faDate(c.last)}</span>
                <a
                  href={`tel:${c.phone}`}
                  className="flex h-9 w-9 items-center justify-center rounded-full bg-accent/10 text-accent transition hover:bg-accent hover:text-white"
                  aria-label="تماس"
                >
                  <MessageCircle className="h-4 w-4" />
                </a>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
