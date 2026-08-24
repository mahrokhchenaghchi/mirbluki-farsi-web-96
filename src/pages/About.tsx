import { Link } from "react-router-dom";
import { Logo } from "@/components/joma/Logo";

export default function AboutPage() {
  return (
    <article className="mx-auto max-w-3xl px-4 py-10 prose prose-slate persian-text">
      <Logo />
      <div className="mt-6">
        <Link to="/" className="text-sm text-primary no-underline">
          بازگشت
        </Link>
      </div>
      <h1 className="text-3xl font-black">درباره جوما</h1>
      <p className="mt-4 leading-9 text-muted-foreground">
        جوما محصولی مستقل برای برنامه‌ریزی فعالیت‌ها در دوره‌های زمانی مشخص، ثبت عملکرد واقعی و خلق،
        نگهداری تاریخچه مستقل هر دوره و تولید گزارش‌های مبتنی بر داده است.
      </p>
      <h2 className="mt-8 text-xl font-bold">طراح</h2>
      <p className="mt-2 leading-9 text-muted-foreground">جواد میربلوکی</p>
      <h2 className="mt-8 text-xl font-bold">ایده شکل‌گیری</h2>
      <p className="mt-2 leading-9 text-muted-foreground">
        جوما از نیاز به دیدن مسیر واقعی کارها آمده است: نه فقط فهرست کارها، بلکه دوره، برنامه، اجرای ثبت‌شده و فهمیدن آنچه رخ داده.
      </p>
      <h2 className="mt-8 text-xl font-bold">هدف</h2>
      <p className="mt-2 leading-9 text-muted-foreground">
        کمک به کاربر برای چرخه‌ای پایدار از برنامه‌ریزی، اجرا، اندازه‌گیری، فهمیدن و بهبود؛ بدون مخلوط شدن تاریخچه دوره‌ها.
      </p>
    </article>
  );
}
