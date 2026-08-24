import { Link } from "react-router-dom";
import { ArrowLeft, CheckCircle2, LineChart, Shield } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Logo } from "@/components/joma/Logo";
import { useAuth } from "@/hooks/useAuth";

export default function Landing() {
  const { user, configured, localMode } = useAuth();

  return (
    <div className="min-h-screen bg-background">
      <header className="mx-auto flex max-w-6xl items-center justify-between px-4 py-5">
        <Logo />
        <div className="flex gap-2">
          {user ? (
            <Button asChild>
              <Link to="/app">ورود به برنامه</Link>
            </Button>
          ) : (
            <>
              <Button variant="ghost" asChild>
                <Link to="/auth">ورود</Link>
              </Button>
              <Button asChild>
                <Link to="/auth?mode=signup">شروع</Link>
              </Button>
            </>
          )}
        </div>
      </header>

      <section className="bg-gradient-hero text-white">
        <div className="mx-auto grid max-w-6xl gap-10 px-4 py-20 lg:grid-cols-2 lg:items-center">
          <div>
            <p className="mb-4 text-sm text-white/80">Web Application · فارسی · راست‌چین</p>
            <h1 className="text-4xl font-black leading-tight md:text-5xl">جوما</h1>
            <p className="mt-5 max-w-xl text-lg leading-9 text-blue-50 persian-text">
              برنامه را بچین، اجرا کن، عملکرد واقعی را ثبت کن، تاریخچه را نگه دار و از دادهٔ واقعی بفهم چه اتفاقی افتاده است.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Button size="lg" variant="secondary" asChild>
                <Link to={user ? "/app" : "/auth?mode=signup"}>
                  شروع استفاده
                  <ArrowLeft className="h-4 w-4" />
                </Link>
              </Button>
              <Button size="lg" variant="outline" className="border-white text-white hover:bg-white hover:text-primary" asChild>
                <Link to="/app/about">درباره جوما</Link>
              </Button>
            </div>
            {localMode && (
              <p className="mt-6 text-sm text-yellow-100">
                نسخه آزمایشی محلی آماده است. ثبت‌نام کنید و بدون اتصال پایگاه تولید تست کنید.
              </p>
            )}
            {!configured && !localMode && (
              <p className="mt-6 text-sm text-yellow-100">
                برای اجرای کامل، متغیرهای محیطی Supabase باید تنظیم شوند.
              </p>
            )}
          </div>
          <div className="grid gap-3">
            {["برنامه‌ریزی دوره", "ثبت عملکرد", "اندازه‌گیری", "فهمیدن", "بهبود"].map((step, index) => (
              <div key={step} className="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">
                <div className="text-xs text-white/70">گام {index + 1}</div>
                <div className="mt-1 text-lg font-semibold">{step}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="mx-auto grid max-w-6xl gap-6 px-4 py-16 md:grid-cols-3">
        {[
          {
            icon: CheckCircle2,
            title: "دوره‌های مستقل",
            text: "هر ماه شمسی برنامه، رویدادها و گزارش خودش را نگه می‌دارد.",
          },
          {
            icon: LineChart,
            title: "گزارش مبتنی بر داده",
            text: "نمودار و تقویم فقط از رویدادهای واقعی ساخته می‌شوند. دادهٔ ساختگی وجود ندارد.",
          },
          {
            icon: Shield,
            title: "چندکاربره و خصوصی",
            text: "هر کاربر فقط دادهٔ خودش را می‌بیند. رمز عبور به صورت متن ساده ذخیره نمی‌شود.",
          },
        ].map((item) => (
          <div key={item.title} className="rounded-2xl border bg-card p-6">
            <item.icon className="mb-4 h-6 w-6 text-primary" />
            <h2 className="text-lg font-bold">{item.title}</h2>
            <p className="mt-2 text-sm leading-8 text-muted-foreground">{item.text}</p>
          </div>
        ))}
      </section>
    </div>
  );
}
