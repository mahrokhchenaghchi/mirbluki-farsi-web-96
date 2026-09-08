import { Wheat, Users, Store, HeartHandshake } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { useSettings, useBranches } from "@/hooks/useData";
import { faDigits } from "@/lib/format";

export default function About() {
  const { data: settings } = useSettings();
  const { data: branches } = useBranches();

  return (
    <div>
      {/* هدر */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0">
          <img src="/images/brand/store.jpg" alt="" className="h-full w-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-l from-background via-background/85 to-background/40" />
        </div>
        <div className="container relative py-20">
          <div className="max-w-xl animate-fade-in-up">
            <p className="text-xs font-bold uppercase tracking-wider text-primary">درباره ما</p>
            <h1 className="mt-2 text-3xl font-black sm:text-4xl">
              داستان {settings?.brand ?? "ما"}
            </h1>
            <p className="persian-text mt-4 text-sm leading-8 text-muted-foreground sm:text-base">
              {settings?.about_text}
            </p>
          </div>
        </div>
      </section>

      {/* آمار */}
      <section className="container -mt-6 pb-4">
        <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
          {[
            { icon: Store, value: faDigits(branches?.length ?? 3), label: "شعبه فعال" },
            { icon: Users, value: `${faDigits(12)}٬۰۰۰+`, label: "مشتری وفادار" },
            { icon: Wheat, value: `${faDigits(11)} سال`, label: "تجربه پخت" },
            { icon: HeartHandshake, value: `${faDigits(98)}٪`, label: "رضایت مشتریان" },
          ].map((s) => (
            <Card key={s.label} className="card-hover">
              <CardContent className="flex flex-col items-center gap-1 p-6 text-center">
                <s.icon className="h-7 w-7 text-primary" />
                <span className="text-2xl font-black">{s.value}</span>
                <span className="text-xs text-muted-foreground">{s.label}</span>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      {/* ارزش‌ها */}
      <section className="container py-14">
        <div className="mx-auto max-w-xl text-center">
          <p className="text-xs font-bold uppercase tracking-wider text-primary">ارزش‌های ما</p>
          <h2 className="mt-2 text-2xl font-black">سه قانونی که هرگز نمی‌شکنیم</h2>
        </div>
        <div className="mt-8 grid gap-4 md:grid-cols-3">
          {[
            {
              emoji: "🧀",
              title: "مواد اولیه، غیرقابل مذاکره",
              desc: "پنیر موزارلا واقعی، گوشت تازه و سبزیجات روز؛ اگر ماده‌ای کیفیت لازم را نداشته باشد، آن روز از منو حذف می‌شود نه اینکه جایگزین ارزان‌تر بیاوریم.",
            },
            {
              emoji: "⏱️",
              title: "وقت شما، اعتبار ماست",
              desc: "زمان تحویل وعده‌ای است که به آن پایبندیم. اگر سفارش با تاخیر رسید، هزینه ارسال برمی‌گردد.",
            },
            {
              emoji: "🤝",
              title: "مشتری، مهمان خانه",
              desc: "هر نارضایتی ظرف ۲۴ ساعت پاسخ می‌گیرد و در صورت تکرار سفارش، سفارش جدید با تخفیف ارسال می‌شود.",
            },
          ].map((v) => (
            <Card key={v.title}>
              <CardContent className="p-6">
                <span className="text-4xl">{v.emoji}</span>
                <h3 className="mt-4 font-extrabold">{v.title}</h3>
                <p className="persian-text mt-2 text-sm leading-7 text-muted-foreground">{v.desc}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      {/* گالری */}
      <section className="container pb-16">
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
          {[
            "/images/brand/hero.jpg",
            "/images/menu/pizza-supreme.jpg",
            "/images/brand/store.jpg",
            "/images/menu/pizza-pepperoni.jpg",
          ].map((src, i) => (
            <img
              key={i}
              src={src}
              alt=""
              loading="lazy"
              className={`h-48 w-full rounded-2xl object-cover ${i % 2 ? "md:translate-y-4" : ""}`}
            />
          ))}
        </div>
      </section>
    </div>
  );
}
