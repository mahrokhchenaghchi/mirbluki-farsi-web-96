import { useEffect, useState } from "react";
import { Plus, Pencil, Trash2, MapPin, Phone, Clock, Bike } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Switch } from "@/components/ui/switch";
import { Card, CardContent } from "@/components/ui/card";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { useBranches, useInvalidate } from "@/hooks/useData";
import { api } from "@/lib/db";
import { faDigits, toman, uid } from "@/lib/format";
import type { Branch } from "@/lib/types";

export default function AdminBranches() {
  const { data: branches } = useBranches();
  const invalidate = useInvalidate();
  const [editing, setEditing] = useState<Branch | null>(null);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [deleting, setDeleting] = useState<Branch | null>(null);

  const openNew = () => {
    setEditing({
      id: uid("br"),
      slug: `branch-${Date.now()}`,
      name: "",
      city: "تهران",
      address: "",
      phone: "",
      open_time: "12:00",
      close_time: "23:00",
      image: "/images/brand/branch-default.jpg",
      delivery_fee: 35000,
      min_order: 150000,
      free_delivery_over: 600000,
      is_active: true,
      sort: (branches?.length ?? 0) + 1,
    });
    setDialogOpen(true);
  };

  const save = async (b: Branch) => {
    await api.upsertBranch(b);
    await invalidate();
    setDialogOpen(false);
  };

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-black">شعب</h1>
          <p className="mt-1 text-sm text-muted-foreground">مدیریت شعبه‌ها، ساعات کاری و هزینه‌های ارسال</p>
        </div>
        <Button onClick={openNew}><Plus className="h-4 w-4 ml-1.5" /> افزودن شعبه</Button>
      </div>

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {(branches ?? []).map((b) => (
          <Card key={b.id} className="card-hover overflow-hidden">
            <div className="relative h-32">
              <img src={b.image ?? "/images/brand/branch-default.jpg"} alt="" className="h-full w-full object-cover" />
              <Badge className={`absolute right-3 top-3 ${b.is_active ? "bg-emerald-600" : "bg-muted text-muted-foreground"} text-white`}>
                {b.is_active ? "فعال" : "غیرفعال"}
              </Badge>
            </div>
            <CardContent className="space-y-2 p-4">
              <h3 className="font-extrabold">{b.name}</h3>
              <p className="flex items-start gap-1.5 text-xs text-muted-foreground">
                <MapPin className="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary" /> {b.address}
              </p>
              <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                <Phone className="h-3.5 w-3.5 text-primary" /> {faDigits(b.phone)}
              </p>
              <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                <Clock className="h-3.5 w-3.5 text-primary" /> {faDigits(b.open_time)} تا {faDigits(b.close_time)}
              </p>
              <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                <Bike className="h-3.5 w-3.5 text-primary" />
                ارسال {toman(b.delivery_fee / 1000)} هزار — حداقل {toman(b.min_order / 1000)} هزار — رایگان از {toman(b.free_delivery_over / 1000)} هزار
              </p>
              <div className="flex gap-2 pt-1">
                <Button variant="outline" size="sm" className="flex-1" onClick={() => { setEditing(b); setDialogOpen(true); }}>
                  <Pencil className="h-3.5 w-3.5 ml-1" /> ویرایش
                </Button>
                <Button
                  variant="outline" size="sm"
                  className="text-destructive hover:bg-destructive/10"
                  onClick={() => setDeleting(b)}
                >
                  <Trash2 className="h-3.5 w-3.5" />
                </Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      <BranchDialog branch={editing} open={dialogOpen} onOpenChange={setDialogOpen} onSave={save} />

      <AlertDialog open={!!deleting} onOpenChange={(v) => !v && setDeleting(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>حذف {deleting?.name}؟</AlertDialogTitle>
            <AlertDialogDescription>شعبه برای همیشه حذف می‌شود. سفارش‌های ثبت‌شده قبلی حفظ می‌شوند.</AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>انصراف</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onClick={async () => {
                if (deleting) { await api.deleteBranch(deleting.id); await invalidate(); }
                setDeleting(null);
              }}
            >
              حذف
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}

function BranchDialog({
  branch, open, onOpenChange, onSave,
}: {
  branch: Branch | null;
  open: boolean;
  onOpenChange: (v: boolean) => void;
  onSave: (b: Branch) => void;
}) {
  const [form, setForm] = useState<Branch | null>(branch);
  useEffect(() => setForm(branch), [branch]);
  if (!form) return null;

  const set = <K extends keyof Branch>(k: K, v: Branch[K]) => setForm((f) => (f ? { ...f, [k]: v } : f));

  const valid = form.name.trim() && form.address.trim() && form.phone.trim();

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-h-[92dvh] overflow-y-auto sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{branch?.name ? `ویرایش ${branch.name}` : "شعبه جدید"}</DialogTitle>
          <DialogDescription>مشخصات و سیاست‌های ارسال این شعبه را وارد کنید.</DialogDescription>
        </DialogHeader>
        <div className="grid gap-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1.5">
              <Label>نام شعبه *</Label>
              <Input value={form.name} onChange={(e) => set("name", e.target.value)} placeholder="شعبه مرکزی — ولیعصر" />
            </div>
            <div className="space-y-1.5">
              <Label>شهر</Label>
              <Input value={form.city} onChange={(e) => set("city", e.target.value)} />
            </div>
          </div>
          <div className="space-y-1.5">
            <Label>آدرس کامل *</Label>
            <Input value={form.address} onChange={(e) => set("address", e.target.value)} />
          </div>
          <div className="grid gap-4 sm:grid-cols-3">
            <div className="space-y-1.5">
              <Label>تلفن *</Label>
              <Input value={form.phone} onChange={(e) => set("phone", e.target.value)} dir="ltr" className="text-left" />
            </div>
            <div className="space-y-1.5">
              <Label>ساعت باز شدن</Label>
              <Input value={form.open_time} onChange={(e) => set("open_time", e.target.value)} dir="ltr" className="text-left" placeholder="12:00" />
            </div>
            <div className="space-y-1.5">
              <Label>ساعت بسته شدن</Label>
              <Input value={form.close_time} onChange={(e) => set("close_time", e.target.value)} dir="ltr" className="text-left" placeholder="23:30" />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-3">
            <div className="space-y-1.5">
              <Label>هزینه ارسال (تومان)</Label>
              <Input
                value={String(form.delivery_fee)}
                onChange={(e) => set("delivery_fee", Number(e.target.value.replace(/\D/g, "")) || 0)}
                inputMode="numeric" dir="ltr" className="text-left"
              />
            </div>
            <div className="space-y-1.5">
              <Label>حداقل سفارش</Label>
              <Input
                value={String(form.min_order)}
                onChange={(e) => set("min_order", Number(e.target.value.replace(/\D/g, "")) || 0)}
                inputMode="numeric" dir="ltr" className="text-left"
              />
            </div>
            <div className="space-y-1.5">
              <Label>ارسال رایگان از</Label>
              <Input
                value={String(form.free_delivery_over)}
                onChange={(e) => set("free_delivery_over", Number(e.target.value.replace(/\D/g, "")) || 0)}
                inputMode="numeric" dir="ltr" className="text-left"
              />
            </div>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1.5">
              <Label>عرض جغرافیایی (اختیاری)</Label>
              <Input
                value={form.lat?.toString() ?? ""}
                onChange={(e) => set("lat", Number(e.target.value) || undefined)}
                inputMode="decimal" dir="ltr" className="text-left"
              />
            </div>
            <div className="space-y-1.5">
              <Label>طول جغرافیایی (اختیاری)</Label>
              <Input
                value={form.lng?.toString() ?? ""}
                onChange={(e) => set("lng", Number(e.target.value) || undefined)}
                inputMode="decimal" dir="ltr" className="text-left"
              />
            </div>
          </div>
          <label className="flex cursor-pointer items-center justify-between rounded-2xl border p-4">
            <span className="text-sm font-bold">شعبه فعال باشد</span>
            <Switch checked={form.is_active} onCheckedChange={(v) => set("is_active", v)} />
          </label>
          <Button size="lg" disabled={!valid} onClick={() => valid && onSave(form)}>ذخیره شعبه</Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}
