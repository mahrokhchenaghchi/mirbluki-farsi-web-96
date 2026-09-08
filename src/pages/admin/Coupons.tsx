import { useEffect, useState } from "react";
import { Plus, Pencil, Trash2, TicketPercent, Copy } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Switch } from "@/components/ui/switch";
import { Card, CardContent } from "@/components/ui/card";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { useToast } from "@/hooks/use-toast";
import { useCoupons, useInvalidate } from "@/hooks/useData";
import { api } from "@/lib/db";
import { toman, uid } from "@/lib/format";
import type { Coupon } from "@/lib/types";

export default function AdminCoupons() {
  const { data: coupons } = useCoupons();
  const invalidate = useInvalidate();
  const { toast } = useToast();
  const [editing, setEditing] = useState<Coupon | null>(null);
  const [open, setOpen] = useState(false);

  const openNew = () => {
    setEditing({
      id: uid("cp"),
      code: "",
      type: "percent",
      value: 10,
      min_order: 200000,
      max_discount: undefined,
      is_active: true,
      description: "",
    });
    setOpen(true);
  };

  const save = async (c: Coupon) => {
    await api.upsertCoupon({ ...c, code: c.code.toUpperCase() });
    await invalidate();
    setOpen(false);
  };

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-black">کدهای تخفیف</h1>
          <p className="mt-1 text-sm text-muted-foreground">ایجاد و مدیریت کمپین‌های تخفیف</p>
        </div>
        <Button onClick={openNew}><Plus className="h-4 w-4 ml-1.5" /> کد تخفیف جدید</Button>
      </div>

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {(coupons ?? []).map((c) => (
          <Card key={c.id} className="card-hover">
            <CardContent className="space-y-3 p-5">
              <div className="flex items-center justify-between">
                <button
                  className="flex items-center gap-2 font-mono text-lg font-black text-primary"
                  dir="ltr"
                  onClick={() => {
                    navigator.clipboard?.writeText(c.code).catch(() => undefined);
                    toast({ title: "کد کپی شد", description: c.code });
                  }}
                  title="کپی کد"
                >
                  {c.code}
                  <Copy className="h-3.5 w-3.5 opacity-50" />
                </button>
                <Badge className={c.is_active ? "bg-emerald-600 text-white" : "bg-muted text-muted-foreground"}>
                  {c.is_active ? "فعال" : "غیرفعال"}
                </Badge>
              </div>
              <p className="text-sm font-bold">
                {c.type === "percent"
                  ? `${toman(c.value)}٪ تخفیف`
                  : `${toman(c.value / 1000)} هزار تومان تخفیف`}
              </p>
              <p className="text-xs text-muted-foreground">{c.description}</p>
              <p className="text-xs text-muted-foreground">حداقل سفارش: {toman(c.min_order / 1000)} هزار تومان</p>
              {c.max_discount && (
                <p className="text-xs text-muted-foreground">حداکثر تخفیف: {toman(c.max_discount / 1000)} هزار تومان</p>
              )}
              <div className="flex gap-2 pt-1">
                <Button variant="outline" size="sm" className="flex-1" onClick={() => { setEditing(c); setOpen(true); }}>
                  <Pencil className="h-3.5 w-3.5 ml-1" /> ویرایش
                </Button>
                <Button
                  variant="outline" size="sm"
                  className="text-destructive hover:bg-destructive/10"
                  onClick={async () => { await api.deleteCoupon(c.id); await invalidate(); }}
                >
                  <Trash2 className="h-3.5 w-3.5" />
                </Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      <CouponDialog coupon={editing} open={open} onOpenChange={setOpen} onSave={save} />
    </div>
  );
}

function CouponDialog({
  coupon, open, onOpenChange, onSave,
}: {
  coupon: Coupon | null;
  open: boolean;
  onOpenChange: (v: boolean) => void;
  onSave: (c: Coupon) => void;
}) {
  const [form, setForm] = useState<Coupon | null>(coupon);
  useEffect(() => setForm(coupon), [coupon]);
  if (!form) return null;

  const set = <K extends keyof Coupon>(k: K, v: Coupon[K]) => setForm((f) => (f ? { ...f, [k]: v } : f));

  const valid = /^[A-Z0-9]{4,15}$/i.test(form.code.trim()) && form.value > 0;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <TicketPercent className="h-5 w-5 text-primary" />
            {coupon?.code ? `ویرایش ${coupon.code}` : "کد تخفیف جدید"}
          </DialogTitle>
          <DialogDescription>مشتریان می‌توانند این کد را در سبد خرید وارد کنند.</DialogDescription>
        </DialogHeader>
        <div className="grid gap-4">
          <div className="space-y-1.5">
            <Label>کد (حروف انگلیسی) *</Label>
            <Input
              value={form.code}
              onChange={(e) => set("code", e.target.value.toUpperCase())}
              placeholder="WELCOME10"
              dir="ltr"
              className="text-left font-mono"
            />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1.5">
              <Label>نوع تخفیف</Label>
              <div className="flex gap-2">
                <Button
                  type="button"
                  variant={form.type === "percent" ? "default" : "outline"}
                  size="sm"
                  className="flex-1"
                  onClick={() => set("type", "percent")}
                >
                  درصدی
                </Button>
                <Button
                  type="button"
                  variant={form.type === "amount" ? "default" : "outline"}
                  size="sm"
                  className="flex-1"
                  onClick={() => set("type", "amount")}
                >
                  مبلغی
                </Button>
              </div>
            </div>
            <div className="space-y-1.5">
              <Label>{form.type === "percent" ? "درصد تخفیف" : "مبلغ (تومان)"}</Label>
              <Input
                value={String(form.value)}
                onChange={(e) => set("value", Number(e.target.value.replace(/\D/g, "")) || 0)}
                inputMode="numeric"
                dir="ltr"
                className="text-left"
              />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1.5">
              <Label>حداقل سفارش (تومان)</Label>
              <Input
                value={String(form.min_order)}
                onChange={(e) => set("min_order", Number(e.target.value.replace(/\D/g, "")) || 0)}
                inputMode="numeric"
                dir="ltr"
                className="text-left"
              />
            </div>
            {form.type === "percent" && (
              <div className="space-y-1.5">
                <Label>حداکثر تخفیف (اختیاری)</Label>
                <Input
                  value={form.max_discount?.toString() ?? ""}
                  onChange={(e) => set("max_discount", Number(e.target.value.replace(/\D/g, "")) || undefined)}
                  inputMode="numeric"
                  dir="ltr"
                  className="text-left"
                />
              </div>
            )}
          </div>
          <div className="space-y-1.5">
            <Label>توضیح برای مشتری</Label>
            <Input
              value={form.description}
              onChange={(e) => set("description", e.target.value)}
              placeholder="۱۰٪ تخفیف اولین سفارش"
            />
          </div>
          <label className="flex cursor-pointer items-center justify-between rounded-2xl border p-4">
            <span className="text-sm font-bold">کد فعال باشد</span>
            <Switch checked={form.is_active} onCheckedChange={(v) => set("is_active", v)} />
          </label>
          <Button size="lg" disabled={!valid} onClick={() => valid && onSave(form)}>ذخیره کد تخفیف</Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}
