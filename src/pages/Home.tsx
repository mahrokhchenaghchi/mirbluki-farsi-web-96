import { Link, useNavigate } from "react-router-dom";
import {
  Pizza, Star, Clock, Bike, MapPin, ChevronLeft, Wheat,
  ShieldCheck, BadgeCheck, Quote, Timer, Gift,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import ProductCard from "@/components/ProductCard";
import { useState } from "react";
import type { Product } from "@/lib/types";
import AddToCartDialog from "@/components/AddToCartDialog";
import { useBranches, useCategories, useProducts, useReviews, useSettings } from "@/hooks/useData";
import { toman, isBranchOpen, faDigits } from "@/lib/format";

export default function Home() {
  const navigate = useNavigate();
  const { data: settings } = useSettings();
  const { data: categories } = useCategories();
  const { data: products } = useProducts();
  const { data: branches } = useBranches();
  const { data: reviews } = useReviews();
  const [selected, setSelected] = useState<Product | null>(null);
  const [dialogOpen, setDialogOpen] = useState(false);

  const featured = (products ?? []).filter((p) => p.is_featured).slice(0, 8);
  const openNow = (branches ?? []).some((b) => isBranchOpen(b.open_time, b.close_time));

  const openProduct = (p: Product) => {
    setSelected(p);
    setDialogOpen(true);
  };

  const categoryIcons: Record<string, string> = {
    pizza: "🍕", burger: "🍔", fried: "🍗", sandwich: "🥪",
    appetizer: "🍟", salad: "🥗", pasta: "🍝", dessert: "🍰", drink: "🥤",
  };

  return (
    <div>
      {/* ---------------- Hero ---------------- */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0">
          <img src="/images/brand/hero.jpg" alt="" className="h-full w-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-l from-background via-background/85 to-background/40" />
        </div>
        <div className="container relative flex min-h-[520px] items-center py-16">
          <div className="max-w-2xl animate-fade-in-up">
            <div className="mb-5 flex flex-wrap items-center gap-2">
              <Badge className={
                openNow
                  ? "bg-emerald-600 text-white"
                  : "bg-amber-600 text-white"
              }>
                <span className={`ml-1.5 inline-block h-2 w-2 rounded-full bg-white ${openNow ? "animate-pulse-soft" : ""}`} />
                {openNow ? "الان باز هستیم" : "در حال حاضر بسته‌ایم — پیش‌سفارش ثبت کنید"}
              </Badge>
              <Badge variant="secondary" className="bg-white/80 backdrop-blur">
                <Star className="h-3.5 w-3.5 ml-1 fill-amber-500 text-amber-500" />
                ۴٫۸ از ۵ — رضایت مشتریان
              </Badge>
            </div>

            <h1 className="text-balance text-4xl font-black leading-[1.25] sm:text-5xl lg:text-6xl">
              طعم اصیل ایتالیایی،
              <br />
              <span className="text-primary">گرم مثل خانه</span>
            </h1>

            <p className="persian-text mt-5 max-w-xl text-base text-muted-foreground sm:text-lg">
              {settings?.tagline} — خمیر روزانه، پنیر موزارلا واقعی و ارسال داغ در
              {" "}{faDigits(35)} دقیقه. سفارش آنلاین بدون زنگ زدن، با پیگیری لحظه‌ای سفارش.
            </p>

            <div className="mt-8 flex flex-wrap gap-3">
              <Button size="lg" className="h-13 rounded-full px-8 text-base shadow-lg shadow-primary/30" onClick={() => navigate("/menu")}>
                <Pizza className="h-5 w-5 ml-2" />
                سفارش آنلاین
              </Button>
              <Button size="lg" variant="outline" className="h-13 rounded-full px-8 text-base bg-card/80 backdrop-blur" onClick={() => navigate("/branches")}>
                <MapPin className="h-5 w-5 ml-2" />
                شعب ما
              </Button>
            </div>

            <div className="mt-10 grid max-w-lg grid-cols-3 gap-4">
              {[
                { icon: Timer, title: `${faDigits(35)} دقیقه`, sub: "میانگین ارسال" },
                { icon: Wheat, title: "خمیر روزانه", sub: "تازه هر صبح" },
                { icon: Bike, title: "ارسال رایگان", sub: "بالای ۶۰۰ هزار" },
              ].map((f) => (
                <div key={f.title} className="flex flex-col items-center gap-1 rounded-2xl bg-card/70 p-3 text-center backdrop-blur">
                  <f.icon className="h-5 w-5 text-primary" />
                  <span className="text-xs font-extrabold">{f.title}</span>
                  <span className="text-[10px] text-muted-foreground">{f.sub}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ---------------- دسته‌بندی‌ها ---------------- */}
      <section className="container py-14">
        <SectionTitle title="از چی خورشدی؟" subtitle="دسته‌بندی منو" />
        <div className="mt-6 grid grid-cols-3 gap-3 sm:grid-cols-5 lg:grid-cols-9">
          {(categories ?? []).map((c) => (
            <Link
              key={c.id}
              to={`/menu?cat=${c.slug}`}
              className="card-hover group flex flex-col items-center gap-2 rounded-2xl border bg-card p-4"
            >
              <span className="text-3xl transition group-hover:scale-110">{categoryIcons[c.slug] ?? "🍽️"}</span>
              <span className="text-xs font-bold">{c.name}</span>
            </Link>
          ))}
        </div>
      </section>

      {/* ---------------- محبوب‌ترین‌ها ---------------- */}
      <section className="container py-6">
        <div className="flex items-center justify-between">
          <SectionTitle title="پرفروش‌های این هفته" subtitle="محبوب‌های مشتریان" />
          <Link to="/menu" className="flex items-center gap-1 text-sm font-bold text-primary hover:opacity-80">
            مشاهده همه <ChevronLeft className="h-4 w-4" />
          </Link>
        </div>
        <div className="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
          {featured.map((p) => (
            <ProductCard key={p.id} product={p} onOpen={openProduct} />
          ))}
        </div>
      </section>

      {/* ---------------- چرا ما ---------------- */}
      <section className="container py-14">
        <SectionTitle title="چرا پیتزا رویال؟" subtitle="چیزی که ما را متفاوت می‌کند" />
        <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {[
            { icon: Wheat, title: "مواد اولیه تازه", desc: "سبزیجات و پنیر هر صبح تحویل می‌شود؛ هیچ فریزری در کار نیست." },
            { icon: Timer, title: "ارسال سریع", desc: "میانگین زمان تحویل ۳۵ دقیقه — داغ و ترد به دست شما می‌رسد." },
            { icon: ShieldCheck, title: "ضمانت کیفیت", desc: "از سفارش راضی نبودید؟ بدون طرح سؤال سفارش را تکرار می‌کنیم." },
            { icon: BadgeCheck, title: "پرداخت امن", desc: "پرداخت اینترنتی، کارت به کارت یا در محل — هر طور راحت‌ترید." },
          ].map((f) => (
            <Card key={f.title} className="card-hover border-border/70">
              <CardContent className="flex flex-col items-center gap-3 p-6 text-center">
                <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                  <f.icon className="h-7 w-7" />
                </span>
                <h3 className="font-extrabold">{f.title}</h3>
                <p className="persian-text text-xs leading-6 text-muted-foreground">{f.desc}</p>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      {/* ---------------- شعب ---------------- */}
      <section className="container py-8">
        <div className="flex items-center justify-between">
          <SectionTitle title="شعبه نزدیک به شما" subtitle="سفارش از نزدیک‌ترین شعبه" />
          <Link to="/branches" className="flex items-center gap-1 text-sm font-bold text-primary hover:opacity-80">
            همه شعب <ChevronLeft className="h-4 w-4" />
          </Link>
        </div>
        <div className="mt-6 grid gap-4 md:grid-cols-3">
          {(branches ?? []).map((b) => {
            const open = isBranchOpen(b.open_time, b.close_time);
            return (
              <Card key={b.id} className="card-hover overflow-hidden">
                <div className="relative h-36">
                  <img src={b.image ?? "/images/brand/branch-default.jpg"} alt={b.name} className="h-full w-full object-cover" loading="lazy" />
                  <Badge className={`absolute right-3 top-3 ${open ? "bg-emerald-600" : "bg-rose-600"} text-white`}>
                    {open ? "باز است" : "بسته"}
                  </Badge>
                </div>
                <CardContent className="p-4">
                  <h3 className="font-extrabold">{b.name}</h3>
                  <p className="mt-1 flex items-start gap-1.5 text-xs leading-5 text-muted-foreground">
                    <MapPin className="mt-0.5 h-3.5 w-3.5 shrink-0" /> {b.address}
                  </p>
                  <Button
                    variant="secondary"
                    className="mt-3 w-full"
                    onClick={() => navigate(`/menu?branch=${b.slug}`)}
                  >
                    سفارش از این شعبه
                  </Button>
                </CardContent>
              </Card>
            );
          })}
        </div>
      </section>

      {/* ---------------- نظرات ---------------- */}
      <section className="container py-14">
        <SectionTitle title="مشتریان ما چه می‌گویند" subtitle="نظرات واقعی" />
        <div className="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {(reviews ?? []).map((r) => (
            <Card key={r.id} className="relative">
              <Quote className="absolute left-4 top-4 h-6 w-6 text-primary/15" />
              <CardContent className="flex h-full flex-col gap-3 p-5">
                <div className="flex gap-0.5">
                  {Array.from({ length: 5 }).map((_, i) => (
                    <Star
                      key={i}
                      className={`h-4 w-4 ${i < r.rating ? "fill-amber-500 text-amber-500" : "text-border"}`}
                    />
                  ))}
                </div>
                <p className="persian-text flex-1 text-sm leading-7 text-foreground/90">{r.comment}</p>
                <div>
                  <p className="text-sm font-bold">{r.name}</p>
                  <p className="text-[11px] text-muted-foreground">{r.food} — {r.date}</p>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      {/* ---------------- بنر CTA ---------------- */}
      <section className="container pb-4">
        <div className="relative overflow-hidden rounded-3xl bg-primary px-6 py-12 text-center text-primary-foreground sm:px-12">
          <Pizza className="absolute -right-8 -top-8 h-40 w-40 rotate-12 opacity-10" />
          <Pizza className="absolute -bottom-10 -left-10 h-44 w-44 -rotate-12 opacity-10" />
          <h2 className="text-2xl font-black sm:text-3xl">امشب حوصله آشپزی نداری؟</h2>
          <p className="mx-auto mt-3 max-w-lg text-sm leading-7 opacity-90">
            همین حالا سفارش بده؛ اولین سفارشت رو با کد <span className="rounded-md bg-white/20 px-2 py-0.5 font-mono font-bold" dir="ltr">WELCOME10</span> با ۱۰٪ تخفیف ثبت کن.
          </p>
          <div className="mt-6 flex flex-wrap justify-center gap-3">
            <Button size="lg" variant="secondary" className="rounded-full px-8" onClick={() => navigate("/menu")}>
              <Gift className="h-5 w-5 ml-2" />
              سفارش با تخفیف
            </Button>
          </div>
        </div>
      </section>

      <AddToCartDialog product={selected} open={dialogOpen} onOpenChange={setDialogOpen} />
    </div>
  );
}

function SectionTitle({ title, subtitle }: { title: string; subtitle: string }) {
  return (
    <div>
      <p className="text-xs font-bold uppercase tracking-wider text-primary">{subtitle}</p>
      <h2 className="mt-1 text-2xl font-black sm:text-3xl">{title}</h2>
    </div>
  );
}
