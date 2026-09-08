import { useEffect, useMemo, useRef, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { Search, Leaf, Flame, Percent, Bike, MapPin, CheckCircle2 } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Toggle } from "@/components/ui/toggle";
import { Skeleton } from "@/components/ui/skeleton";
import ProductCard from "@/components/ProductCard";
import AddToCartDialog from "@/components/AddToCartDialog";
import { useBranches, useCategories, useProducts } from "@/hooks/useData";
import { useCart } from "@/store/CartContext";
import type { Product } from "@/lib/types";
import { faDigits, isBranchOpen, toman } from "@/lib/format";
import { cn } from "@/lib/utils";

export default function Menu() {
  const [params, setParams] = useSearchParams();
  const { data: categories, isLoading: catLoading } = useCategories();
  const { data: products, isLoading: prodLoading } = useProducts();
  const { data: branches } = useBranches();
  const { branchId, setBranch, count, setCartOpen } = useCart();

  const [query, setQuery] = useState("");
  const [vegOnly, setVegOnly] = useState(false);
  const [spicyOnly, setSpicyOnly] = useState(false);
  const [offersOnly, setOffersOnly] = useState(false);
  const [selected, setSelected] = useState<Product | null>(null);
  const [dialogOpen, setDialogOpen] = useState(false);
  const sectionRefs = useRef<Record<string, HTMLElement | null>>({});

  const activeCat = params.get("cat");
  const branchSlug = params.get("branch");

  // انتخاب شعبه از کوئری‌استرینگ (مثلا /menu?branch=royal-west)
  useEffect(() => {
    if (branchSlug && branches) {
      const b = branches.find((x) => x.slug === branchSlug);
      if (b && b.id !== branchId) {
        setBranch(b.id);
        setParams((p) => {
          const np = new URLSearchParams(p);
          np.delete("branch");
          return np;
        }, { replace: true });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [branchSlug, branches]);

  const branch = branches?.find((b) => b.id === branchId);

  // اگر هیچ شعبه‌ای انتخاب نشده، شعبه اول به صورت خودکار انتخاب می‌شود
  useEffect(() => {
    if (!branchId && branches && branches.length > 0) {
      setBranch(branches[0].id);
    }
  }, [branchId, branches, setBranch]);

  const filtered = useMemo(() => {
    let list = products ?? [];
    const q = query.trim();
    if (q) {
      list = list.filter(
        (p) => p.name.includes(q) || p.description.includes(q)
      );
    }
    if (vegOnly) list = list.filter((p) => p.vegetarian);
    if (spicyOnly) list = list.filter((p) => p.spicy);
    if (offersOnly) list = list.filter((p) => p.discount_percent > 0);
    return list;
  }, [products, query, vegOnly, spicyOnly, offersOnly]);

  const catsWithProducts = useMemo(() => {
    return (categories ?? []).map((c) => ({
      ...c,
      items: filtered.filter((p) => p.category_id === c.id),
    }));
  }, [categories, filtered]);

  const goToCategory = (slug: string) => {
    setParams(slug ? { cat: slug } : {});
    const el = sectionRefs.current[slug];
    if (el) {
      const y = el.getBoundingClientRect().top + window.scrollY - 150;
      window.scrollTo({ top: y, behavior: "smooth" });
    }
  };

  const openProduct = (p: Product) => {
    setSelected(p);
    setDialogOpen(true);
  };

  if (catLoading || prodLoading) {
    return (
      <div className="container space-y-6 py-10">
        <Skeleton className="h-24 w-full rounded-2xl" />
        <Skeleton className="h-12 w-full rounded-full" />
        <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
          {Array.from({ length: 8 }).map((_, i) => (
            <Skeleton key={i} className="h-72 rounded-2xl" />
          ))}
        </div>
      </div>
    );
  }

  const branchOpen = branch ? isBranchOpen(branch.open_time, branch.close_time) : false;

  return (
    <div className="container py-6 pb-28 lg:pb-10">
      {/* هدر شعبه */}
      <div className="flex flex-col gap-4 rounded-3xl border bg-card p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-start gap-3">
          <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary">
            <MapPin className="h-6 w-6" />
          </span>
          <div>
            <div className="flex flex-wrap items-center gap-2">
              <h1 className="text-lg font-extrabold">{branch?.name ?? "انتخاب شعبه"}</h1>
              <Badge variant="outline" className={branchOpen ? "border-emerald-300 bg-emerald-50 text-emerald-700" : "border-amber-300 bg-amber-50 text-amber-700"}>
                {branchOpen ? "باز است" : "بسته — پیش‌سفارش"}
              </Badge>
            </div>
            <p className="mt-1 text-xs text-muted-foreground">
              {branch ? `${branch.address} — ارسال ${toman(branch.delivery_fee)} تومان` : "برای شروع یک شعبه انتخاب کنید"}
            </p>
            {branch && (
              <p className="mt-0.5 text-[11px] text-muted-foreground">
                🛵 ارسال رایگان برای سفارش بالای {toman(branch.free_delivery_over)} تومان
                {" "}| حداقل سفارش {toman(branch.min_order)} تومان
              </p>
            )}
          </div>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <BranchPicker />
          {count > 0 && (
            <Button className="hidden lg:flex" onClick={() => setCartOpen(true)}>
              <Bike className="h-4 w-4 ml-1.5" />
              سبد ({toman(count)})
            </Button>
          )}
        </div>
      </div>

      {/* جستجو و فیلتر */}
      <div className="sticky top-[100px] z-30 -mx-4 mt-5 bg-pattern-warm/90 px-4 py-3 backdrop-blur-md sm:mx-0 sm:rounded-2xl sm:px-4">
        <div className="flex flex-col gap-3">
          <div className="relative">
            <Search className="absolute right-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-muted-foreground" />
            <Input
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="جستجو در منو... مثلاً پپرونی"
              className="h-12 rounded-full border-border bg-card pr-11 text-sm shadow-sm"
            />
          </div>
          <div className="flex items-center gap-2 overflow-x-auto no-scrollbar">
            {/* همه */}
            <button
              onClick={() => goToCategory("")}
              className={cn(
                "shrink-0 rounded-full border px-4 py-1.5 text-xs font-bold transition",
                !activeCat ? "border-primary bg-primary text-primary-foreground" : "bg-card hover:border-primary/40"
              )}
            >
              همه
            </button>
            {(categories ?? []).map((c) => (
              <button
                key={c.id}
                onClick={() => goToCategory(c.slug)}
                className={cn(
                  "shrink-0 rounded-full border px-4 py-1.5 text-xs font-bold transition",
                  activeCat === c.slug ? "border-primary bg-primary text-primary-foreground" : "bg-card hover:border-primary/40"
                )}
              >
                {c.name}
              </button>
            ))}
            <span className="mx-1 h-5 w-px shrink-0 bg-border" />
            <Toggle size="sm" pressed={vegOnly} onPressedChange={setVegOnly} className="shrink-0 rounded-full text-xs" aria-label="فقط گیاهی">
              <Leaf className="h-3.5 w-3.5 ml-1" /> گیاهی
            </Toggle>
            <Toggle size="sm" pressed={spicyOnly} onPressedChange={setSpicyOnly} className="shrink-0 rounded-full text-xs" aria-label="فقط تند">
              <Flame className="h-3.5 w-3.5 ml-1" /> تند
            </Toggle>
            <Toggle size="sm" pressed={offersOnly} onPressedChange={setOffersOnly} className="shrink-0 rounded-full text-xs" aria-label="فقط تخفیف‌دار">
              <Percent className="h-3.5 w-3.5 ml-1" /> تخفیف‌دار
            </Toggle>
          </div>
        </div>
      </div>

      {/* محصولات بر اساس دسته */}
      {catsWithProducts.map((c) =>
        c.items.length === 0 ? null : (
          <section
            key={c.id}
            id={`cat-${c.slug}`}
            ref={(el) => { sectionRefs.current[c.slug] = el; }}
            className="scroll-mt-44 pt-10"
          >
            <div className="flex items-center gap-3">
              <h2 className="text-xl font-black">{c.name}</h2>
              <span className="rounded-full bg-secondary px-2.5 py-0.5 text-[11px] font-bold text-muted-foreground">
                {faDigits(c.items.length)} مورد
              </span>
              <span className="h-px flex-1 bg-border" />
            </div>
            <div className="mt-5 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
              {c.items.map((p) => (
                <ProductCard key={p.id} product={p} onOpen={openProduct} />
              ))}
            </div>
          </section>
        )
      )}

      {catsWithProducts.every((c) => c.items.length === 0) && (
        <div className="flex flex-col items-center gap-3 py-20 text-center">
          <span className="text-5xl">🔍</span>
          <p className="font-bold">نتیجه‌ای پیدا نشد</p>
          <p className="text-sm text-muted-foreground">عبارت دیگری را جستجو کنید یا فیلترها را بردارید.</p>
          <Button
            variant="outline"
            onClick={() => { setQuery(""); setVegOnly(false); setSpicyOnly(false); setOffersOnly(false); }}
          >
            پاک کردن فیلترها
          </Button>
        </div>
      )}

      {/* نوار اطمینان */}
      <div className="mt-14 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 rounded-2xl border bg-card px-6 py-4 text-xs text-muted-foreground">
        <span className="flex items-center gap-1.5"><CheckCircle2 className="h-4 w-4 text-accent" /> خمیر و مواد تازه روزانه</span>
        <span className="flex items-center gap-1.5"><CheckCircle2 className="h-4 w-4 text-accent" /> بسته‌بندی بهداشتی و ضربه‌خور</span>
        <span className="flex items-center gap-1.5"><CheckCircle2 className="h-4 w-4 text-accent" /> ارسال با پیک اختصاصی</span>
      </div>

      <AddToCartDialog product={selected} open={dialogOpen} onOpenChange={setDialogOpen} />
    </div>
  );
}

/** انتخابگر شعبه در هدر منو */
function BranchPicker() {
  const { data: branches } = useBranches();
  const { branchId, setBranch } = useCart();
  const [open, setOpen] = useState(false);

  return (
    <div className="relative">
      <Button variant="outline" className="rounded-full" onClick={() => setOpen((o) => !o)}>
        <MapPin className="h-4 w-4 ml-1.5 text-primary" />
        تغییر شعبه
      </Button>
      {open && (
        <>
          <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
          <div className="absolute left-0 top-11 z-50 w-72 rounded-2xl border bg-card p-2 shadow-xl">
            {(branches ?? []).map((b) => (
              <button
                key={b.id}
                onClick={() => { setBranch(b.id); setOpen(false); }}
                className={cn(
                  "flex w-full flex-col items-start gap-0.5 rounded-xl px-3 py-2.5 text-right transition hover:bg-secondary",
                  b.id === branchId && "bg-primary/5"
                )}
              >
                <span className="flex w-full items-center justify-between text-sm font-bold">
                  {b.name}
                  {b.id === branchId && <CheckCircle2 className="h-4 w-4 text-primary" />}
                </span>
                <span className="text-[11px] text-muted-foreground">
                  ارسال {toman(b.delivery_fee)} تومان — حداقل {toman(b.min_order / 1000)} هزار تومان
                </span>
              </button>
            ))}
          </div>
        </>
      )}
    </div>
  );
}
