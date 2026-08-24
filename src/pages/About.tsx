import { Link } from "react-router-dom";
import { Logo } from "@/components/joma/Logo";

export default function AboutPage() {
  return (
    <article className="mx-auto max-w-3xl space-y-6 px-4 py-10 persian-text">
      <Logo size={88} />
      <Link to="/" className="text-sm text-primary">بازگشت</Link>
      <h1 className="text-4xl font-black">درباره جوما</h1>
      <p className="leading-9 text-muted-foreground">
        جوما یک محصول مستقل برای خودمدیریتی است: برنامه‌ریزی فعالیت‌ها در دوره‌های شمسی، ثبت عملکرد واقعی، ثبت حال روزانه و فهمیدن روند از روی داده.
      </p>
      <h2 className="text-2xl font-bold">چرا ساخته شده؟</h2>
      <p className="leading-9 text-muted-foreground">
        چون فهرست کارها کافی نیست. جوما دوره، برنامه، اجرا و تاریخچه را جدا نگه می‌دارد تا ماه بعد، ماه قبل را عوض نکند.
      </p>
      <h2 className="text-2xl font-bold">فلسفه</h2>
      <p className="leading-9 text-muted-foreground">برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود</p>
      <h2 className="text-2xl font-bold">طراح</h2>
      <p className="leading-9 text-muted-foreground">جواد میربلوکی</p>
    </article>
  );
}
