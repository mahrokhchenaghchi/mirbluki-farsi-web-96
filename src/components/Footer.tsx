import { Link } from "react-router-dom";
import { Pizza, Phone, MapPin, Instagram, Send, Clock } from "lucide-react";
import { useSettings, useBranches } from "@/hooks/useData";
import { faDigits } from "@/lib/format";

export default function Footer() {
  const { data: settings } = useSettings();
  const { data: branches } = useBranches();

  const brand = settings?.brand ?? "پیتزا رویال";

  return (
    <footer className="mt-20 bg-[hsl(20_14%_10%)] text-[hsl(35_25%_88%)]">
      <div className="container grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
        {/* برند */}
        <div>
          <div className="mb-4 flex items-center gap-2.5">
            <span className="flex h-11 w-11 items-center justify-center rounded-full bg-primary text-primary-foreground">
              <Pizza className="h-6 w-6" />
            </span>
            <div>
              <p className="text-lg font-extrabold text-white">{brand}</p>
              <p className="text-xs opacity-70">{settings?.tagline}</p>
            </div>
          </div>
          <p className="persian-text text-sm leading-7 opacity-75">
            {settings?.about_text?.slice(0, 180)}…
          </p>
          <div className="mt-4 flex gap-2">
            <a
              href={`https://instagram.com/${settings?.instagram ?? ""}`}
              target="_blank"
              rel="noreferrer"
              aria-label="اینستاگرام"
              className="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 transition hover:bg-primary"
            >
              <Instagram className="h-4.5 w-4.5" />
            </a>
            <a
              href={`https://t.me/${settings?.telegram ?? ""}`}
              target="_blank"
              rel="noreferrer"
              aria-label="تلگرام"
              className="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 transition hover:bg-primary"
            >
              <Send className="h-4.5 w-4.5" />
            </a>
          </div>
        </div>

        {/* دسترسی سریع */}
        <div>
          <h3 className="mb-4 font-bold text-white">دسترسی سریع</h3>
          <ul className="space-y-2.5 text-sm opacity-80">
            <li><Link to="/menu" className="hover:text-primary transition">منو و سفارش آنلاین</Link></li>
            <li><Link to="/branches" className="hover:text-primary transition">شعب ما</Link></li>
            <li><Link to="/track" className="hover:text-primary transition">پیگیری سفارش</Link></li>
            <li><Link to="/about" className="hover:text-primary transition">درباره ما</Link></li>
            <li><Link to="/contact" className="hover:text-primary transition">تماس با ما</Link></li>
            <li><Link to="/auth" className="hover:text-primary transition">ورود / ثبت‌نام</Link></li>
          </ul>
        </div>

        {/* شعب */}
        <div>
          <h3 className="mb-4 font-bold text-white">ساعات کاری شعب</h3>
          <ul className="space-y-3 text-sm opacity-80">
            {(branches ?? []).map((b) => (
              <li key={b.id} className="flex items-start gap-2">
                <Clock className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                <div>
                  <p className="opacity-90">{b.name}</p>
                  <p className="text-xs opacity-70">
                    همه‌روزه {faDigits(b.open_time)} تا {faDigits(b.close_time)}
                  </p>
                </div>
              </li>
            ))}
          </ul>
        </div>

        {/* تماس */}
        <div>
          <h3 className="mb-4 font-bold text-white">تماس با ما</h3>
          <ul className="space-y-3 text-sm opacity-80">
            <li className="flex items-center gap-2">
              <Phone className="h-4 w-4 shrink-0 text-primary" />
              <a href={`tel:${settings?.phone ?? ""}`} className="hover:text-primary">{settings?.phone}</a>
            </li>
            <li className="flex items-start gap-2">
              <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
              <span>{settings?.address}</span>
            </li>
          </ul>
          <div className="mt-5 rounded-xl bg-white/5 p-3 text-xs leading-6 opacity-80">
            سفارش تلفنی هر روز از ساعت {faDigits("12:00")} تا {faDigits("23:00")} پاسخگویی می‌شود.
          </div>
        </div>
      </div>

      <div className="border-t border-white/10">
        <div className="container flex flex-col items-center justify-between gap-2 py-5 text-xs opacity-60 sm:flex-row">
          <p>© {faDigits(new Date().getFullYear() - 621)} {brand} — تمامی حقوق محفوظ است.</p>
          <p>طراحی‌شده با ❤️ و کمی پنیر موزارلا</p>
        </div>
      </div>
    </footer>
  );
}
