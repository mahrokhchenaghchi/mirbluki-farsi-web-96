import { useEffect, useState } from "react";
import { Save, RotateCcw, Download, Database, Info, ShieldCheck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { useToast } from "@/hooks/use-toast";
import { useSettings, useInvalidate } from "@/hooks/useData";
import { api, exportData, resetDemoData } from "@/lib/db";
import { isValidIranMobile } from "@/lib/format";
import type { StoreSettings } from "@/lib/types";

export default function AdminSettings() {
  const { data: settings } = useSettings();
  const invalidate = useInvalidate();
  const { toast } = useToast();
  const [form, setForm] = useState<StoreSettings | null>(null);

  useEffect(() => {
    if (settings) setForm(settings);
  }, [settings]);

  if (!form) return <div className="py-20 text-center text-sm text-muted-foreground">در حال بارگذاری...</div>;

  const set = <K extends keyof StoreSettings>(k: K, v: StoreSettings[K]) =>
    setForm((f) => (f ? { ...f, [k]: v } : f));

  const save = async () => {
    if (!isValidIranMobile(form.admin_phone)) {
      toast({ variant: "destructive", title: "شماره مدیر معتبر نیست", description: "فرمت: 09123456789" });
      return;
    }
    await api.updateSettings(form);
    await invalidate();
    toast({ title: "تنظیمات ذخیره شد ✓" });
  };

  const downloadData = () => {
    const blob = new Blob([exportData()], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `royal-pizza-backup-${new Date().toISOString().slice(0, 10)}.json`;
    a.click();
    URL.revokeObjectURL(url);
  };

  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <div>
        <h1 className="text-2xl font-black">تنظیمات</h1>
        <p className="mt-1 text-sm text-muted-foreground">اطلاعات برند، دسترسی مدیر و داده‌ها</p>
      </div>

      {/* برند */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">اطلاعات برند</CardTitle>
          <CardDescription>این اطلاعات در سراسر سایت (هدر، فوتر، درباره ما و...) استفاده می‌شود.</CardDescription>
        </CardHeader>
        <CardContent className="grid gap-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1.5">
              <Label>نام برند</Label>
              <Input value={form.brand} onChange={(e) => set("brand", e.target.value)} />
            </div>
            <div className="space-y-1.5">
              <Label>شماره تماس اصلی</Label>
              <Input value={form.phone} onChange={(e) => set("phone", e.target.value)} dir="ltr" className="text-left" />
            </div>
          </div>
          <div className="space-y-1.5">
            <Label>شعار (Tagline)</Label>
            <Input value={form.tagline} onChange={(e) => set("tagline", e.target.value)} />
          </div>
          <div className="space-y-1.5">
            <Label>آدرس مرکزی</Label>
            <Input value={form.address} onChange={(e) => set("address", e.target.value)} />
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1.5">
              <Label>آیدی اینستاگرام</Label>
              <Input value={form.instagram} onChange={(e) => set("instagram", e.target.value)} dir="ltr" className="text-left" />
            </div>
            <div className="space-y-1.5">
              <Label>آیدی تلگرام</Label>
              <Input value={form.telegram} onChange={(e) => set("telegram", e.target.value)} dir="ltr" className="text-left" />
            </div>
          </div>
          <div className="space-y-1.5">
            <Label>متن درباره ما</Label>
            <Textarea value={form.about_text} onChange={(e) => set("about_text", e.target.value)} className="min-h-28" />
          </div>
        </CardContent>
      </Card>

      {/* مدیر و ارسال */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">مدیریت و ارسال</CardTitle>
          <CardDescription>شخصی با این شماره پس از ورود، دسترسی پنل مدیریت خواهد داشت.</CardDescription>
        </CardHeader>
        <CardContent className="grid gap-4 sm:grid-cols-2">
          <div className="space-y-1.5">
            <Label className="flex items-center gap-1.5">
              <ShieldCheck className="h-4 w-4 text-primary" /> شماره موبایل مدیر
            </Label>
            <Input
              value={form.admin_phone}
              onChange={(e) => set("admin_phone", e.target.value)}
              placeholder="09123456789"
              dir="ltr"
              className="text-left"
            />
          </div>
          <div className="space-y-1.5">
            <Label>هزینه ارسال پیش‌فرض (تومان)</Label>
            <Input
              value={String(form.default_delivery_fee)}
              onChange={(e) => set("default_delivery_fee", Number(e.target.value.replace(/\D/g, "")) || 0)}
              inputMode="numeric"
              dir="ltr"
              className="text-left"
            />
          </div>
        </CardContent>
      </Card>

      <Button size="lg" className="w-full" onClick={save}>
        <Save className="h-4.5 w-4.5 ml-2" /> ذخیره تنظیمات
      </Button>

      {/* داده‌ها */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-base">
            <Database className="h-4.5 w-4.5 text-primary" /> داده‌ها
          </CardTitle>
          <CardDescription>
            در حالت دمو داده‌ها در مرورگر (localStorage) ذخیره می‌شوند. با اتصال Supabase داده‌ها به دیتابیس ابری منتقل می‌شوند.
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-3">
          <Button variant="outline" onClick={downloadData}>
            <Download className="h-4 w-4 ml-1.5" /> دریافت پشتیبان (JSON)
          </Button>
          <AlertDialog>
            <AlertDialogTrigger asChild>
              <Button variant="destructive">
                <RotateCcw className="h-4 w-4 ml-1.5" /> بازنشانی داده‌های دمو
              </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>بازنشانی همه داده‌های دمو؟</AlertDialogTitle>
                <AlertDialogDescription>
                  همه سفارش‌ها، مشتریان و تغییرات منو/شعب حذف شده و داده اولیه برمی‌گردد. این عمل قابل بازگشت نیست.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>انصراف</AlertDialogCancel>
                <AlertDialogAction
                  className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                  onClick={async () => {
                    resetDemoData();
                    await invalidate();
                    toast({ title: "داده‌ها بازنشانی شد", description: "صفحه را دوباره بارگذاری کنید." });
                  }}
                >
                  بازنشانی کن
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </CardContent>
      </Card>

      {/* راهنما */}
      <Card className="border-primary/30 bg-primary/5">
        <CardContent className="flex items-start gap-3 p-5 text-sm leading-7">
          <Info className="mt-1 h-5 w-5 shrink-0 text-primary" />
          <div>
            <p className="font-bold">اتصال به بک‌اند واقعی (Supabase)</p>
            <p className="mt-1 text-muted-foreground">
              فایل <code className="rounded bg-background px-1.5 py-0.5 text-xs" dir="ltr">.env.local</code> را در ریشه پروژه بسازید
              و دو متغیر <code className="rounded bg-background px-1.5 py-0.5 text-xs" dir="ltr">VITE_SUPABASE_URL</code> و
              {" "}<code className="rounded bg-background px-1.5 py-0.5 text-xs" dir="ltr">VITE_SUPABASE_ANON_KEY</code> را مقدار دهید.
              سپس فایل migration موجود در پوشه supabase را در پروژه Supabase خود اجرا کنید. جزئیات کامل در README پروژه آمده است.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
