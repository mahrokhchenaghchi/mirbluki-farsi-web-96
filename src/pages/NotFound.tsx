import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";

export default function NotFound() {
  return (
    <div className="container flex min-h-[70vh] flex-col items-center justify-center py-16 text-center">
      <div className="relative">
        <span className="text-[120px] leading-none">🍕</span>
        <span className="absolute -right-4 -top-2 rotate-12 rounded-full bg-primary px-3 py-1 text-sm font-black text-primary-foreground">
          ۴۰۴
        </span>
      </div>
      <h1 className="mt-6 text-3xl font-black">این صفحه را یکی خورده!</h1>
      <p className="persian-text mt-3 max-w-md text-sm text-muted-foreground">
        صفحه‌ای که دنبالش بودید پیدا نشد. شاید آدرس را اشتباه وارد کرده‌اید یا صفحه جابه‌جا شده است.
        بیایید به جای گم شدن، یه پیتزا سفارش بدهیم.
      </p>
      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <Link to="/"><Button size="lg" className="rounded-full px-8">بازگشت به خانه</Button></Link>
        <Link to="/menu"><Button size="lg" variant="outline" className="rounded-full px-8">مشاهده منو</Button></Link>
      </div>
    </div>
  );
}
