import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Logo } from "@/components/joma/Logo";
import { useAuth } from "@/hooks/useAuth";

export default function Landing() {
  const { user } = useAuth();
  return (
    <div className="min-h-screen">
      <header className="mx-auto flex max-w-6xl items-center justify-between px-4 py-5">
        <Logo size={56} />
        <div className="flex gap-2">
          {user ? (
            <Button asChild><Link to="/app">ورود به برنامه</Link></Button>
          ) : (
            <>
              <Button variant="ghost" asChild><Link to="/auth">ورود</Link></Button>
              <Button asChild><Link to="/auth?mode=signup">شروع</Link></Button>
            </>
          )}
        </div>
      </header>

      <section className="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 lg:grid-cols-2">
        <div>
          <p className="mb-3 text-sm text-primary">محصول مستقل خودمدیریتی</p>
          <h1 className="text-5xl font-black leading-tight">جوما</h1>
          <p className="mt-4 text-xl text-muted-foreground persian-text">
            برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود
          </p>
          <p className="mt-4 max-w-xl leading-9 text-muted-foreground">
            جوما برای ثبت فعالیت‌ها، عملکرد واقعی و حال روزانه شماست. هر ماه شمسی تاریخچهٔ مستقل خودش را نگه می‌دارد.
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Button size="lg" asChild>
              <Link to={user ? "/app" : "/auth?mode=signup"}>شروع استفاده</Link>
            </Button>
            <Button size="lg" variant="outline" asChild>
              <Link to="/about">درباره جوما</Link>
            </Button>
          </div>
        </div>
        <div className="grid gap-3">
          {[
            ["🌸", "حال امروز را با استیکر ثبت کن"],
            ["📚", "کتابخانه ۴۵ فعالیت آماده"],
            ["📅", "دوره‌های شمسی مستقل"],
            ["📊", "گزارش فقط از داده واقعی"],
          ].map(([icon, text]) => (
            <div key={text} className="joma-card flex items-center gap-4 px-5 py-4">
              <span className="text-3xl">{icon}</span>
              <span className="font-medium">{text}</span>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}
