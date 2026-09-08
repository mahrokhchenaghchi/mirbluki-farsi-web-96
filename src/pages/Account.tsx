import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import {
  User, Phone, LogOut, Package, MapPin, ShieldCheck,
  ChevronLeft, LayoutDashboard, Pencil, Check,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { useAuth } from "@/hooks/useAuth";
import { useOrdersByPhone } from "@/hooks/useData";
import { faDigits, faDateTime, ORDER_STATUS_COLOR, ORDER_STATUS_LABEL, toman, ORDER_TYPE_LABEL } from "@/lib/format";

export default function Account() {
  const { user, isAdmin, signOut, updateName } = useAuth();
  const navigate = useNavigate();
  const { data: orders, isLoading } = useOrdersByPhone(user?.phone);
  const [editing, setEditing] = useState(false);
  const [name, setName] = useState(user?.name ?? "");

  useEffect(() => {
    if (user) setName(user.name);
  }, [user?.name]);

  if (!user) return null;

  const addresses = Array.from(
    new Set((orders ?? []).filter((o) => o.address).map((o) => o.address!))
  ).slice(0, 5);

  const totalSpent = (orders ?? []).reduce((s, o) => s + (o.status === "canceled" ? 0 : o.total), 0);

  return (
    <div className="container max-w-4xl py-12">
      {/* پروفایل */}
      <Card className="overflow-hidden">
        <div className="h-24 bg-gradient-to-l from-primary to-primary/60" />
        <CardContent className="-mt-10 pb-6">
          <div className="flex flex-wrap items-end justify-between gap-4">
            <div className="flex items-end gap-4">
              <span className="flex h-20 w-20 items-center justify-center rounded-3xl border-4 border-card bg-primary/10 text-primary">
                <User className="h-10 w-10" />
              </span>
              <div className="pb-1">
                {editing ? (
                  <div className="flex items-center gap-2">
                    <Input value={name} onChange={(e) => setName(e.target.value)} className="h-9 w-48" autoFocus />
                    <Button
                      size="icon"
                      className="h-9 w-9"
                      onClick={() => { updateName(name.trim() || "مشتری"); setEditing(false); }}
                      aria-label="ذخیره"
                    >
                      <Check className="h-4 w-4" />
                    </Button>
                  </div>
                ) : (
                  <div className="flex items-center gap-2">
                    <h1 className="text-2xl font-black">{user.name}</h1>
                    <button onClick={() => setEditing(true)} className="text-muted-foreground hover:text-primary" aria-label="ویرایش نام">
                      <Pencil className="h-4 w-4" />
                    </button>
                  </div>
                )}
                <p className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground" dir="ltr">
                  <Phone className="h-3.5 w-3.5" /> {faDigits(user.phone)}
                </p>
                {isAdmin && (
                  <Badge className="mt-2 gap-1">
                    <ShieldCheck className="h-3 w-3" /> مدیر سیستم
                  </Badge>
                )}
              </div>
            </div>
            <div className="flex flex-wrap gap-2 pb-1">
              {isAdmin && (
                <Button variant="secondary" onClick={() => navigate("/admin")}>
                  <LayoutDashboard className="h-4 w-4 ml-1.5" /> پنل مدیریت
                </Button>
              )}
              <Button
                variant="outline"
                className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                onClick={() => { signOut(); navigate("/"); }}
              >
                <LogOut className="h-4 w-4 ml-1.5" /> خروج
              </Button>
            </div>
          </div>

          {/* آمار */}
          <div className="mt-6 grid grid-cols-3 gap-3">
            {[
              { label: "سفارش‌ها", value: faDigits(orders?.length ?? 0) },
              { label: "مجموع خرید", value: `${toman(totalSpent / 1000)} هزار تومان` },
              {
                label: "آخرین سفارش",
                value: orders?.[0] ? faDigits(new Date(orders[0].created_at).toLocaleDateString("fa-IR")) : "—",
              },
            ].map((s) => (
              <div key={s.label} className="rounded-2xl bg-secondary/60 p-4 text-center">
                <p className="text-lg font-black">{s.value}</p>
                <p className="mt-0.5 text-[11px] text-muted-foreground">{s.label}</p>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* سفارش‌ها */}
      <div className="mt-8">
        <h2 className="flex items-center gap-2 text-lg font-extrabold">
          <Package className="h-5 w-5 text-primary" />
          سفارش‌های من
        </h2>

        {isLoading ? (
          <div className="mt-4 space-y-3">
            {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-24 rounded-2xl" />)}
          </div>
        ) : (orders ?? []).length === 0 ? (
          <div className="mt-4 flex flex-col items-center gap-3 rounded-3xl border border-dashed p-10 text-center">
            <span className="text-5xl">🍕</span>
            <p className="font-bold">هنوز سفارشی ثبت نکرده‌اید</p>
            <p className="text-sm text-muted-foreground">اولین سفارشت را با کد WELCOME10 با ۱۰٪ تخفیف ثبت کن!</p>
            <Button onClick={() => navigate("/menu")} className="rounded-full px-8">شروع سفارش</Button>
          </div>
        ) : (
          <div className="mt-4 space-y-3">
            {(orders ?? []).map((o) => (
              <Link
                key={o.id}
                to={`/track?code=${o.code}`}
                className="card-hover flex items-center justify-between gap-4 rounded-2xl border bg-card p-4"
              >
                <div className="min-w-0">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-mono font-bold" dir="ltr">{o.code}</span>
                    <span className={`rounded-full border px-2 py-0.5 text-[11px] font-bold ${ORDER_STATUS_COLOR[o.status]}`}>
                      {ORDER_STATUS_LABEL[o.status]}
                    </span>
                  </div>
                  <p className="mt-1 truncate text-xs text-muted-foreground">
                    {o.items.map((i) => `${i.product_name}×${faDigits(i.qty)}`).join("، ")}
                  </p>
                  <p className="mt-0.5 text-[11px] text-muted-foreground">
                    {faDateTime(o.created_at)} — {ORDER_TYPE_LABEL[o.type]}
                  </p>
                </div>
                <div className="flex shrink-0 flex-col items-end gap-1">
                  <span className="font-extrabold text-primary">{toman(o.total)}</span>
                  <span className="flex items-center gap-0.5 text-xs font-bold text-muted-foreground">
                    پیگیری <ChevronLeft className="h-3.5 w-3.5" />
                  </span>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* آدرس‌ها */}
      {addresses.length > 0 && (
        <div className="mt-8">
          <h2 className="flex items-center gap-2 text-lg font-extrabold">
            <MapPin className="h-5 w-5 text-primary" />
            آدرس‌های اخیر
          </h2>
          <div className="mt-4 grid gap-3 sm:grid-cols-2">
            {addresses.map((a, i) => (
              <Card key={i}>
                <CardContent className="flex items-start gap-2 p-4 text-sm text-muted-foreground">
                  <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                  <span className="leading-6">{a}</span>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
