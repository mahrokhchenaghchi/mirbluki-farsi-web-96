import { useEffect, useMemo, useState } from "react";
import {
  Plus, Pencil, Trash2, Search, Star, Flame, Leaf, Sparkles, X,
  UtensilsCrossed, ImagePlus,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { Switch } from "@/components/ui/switch";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import {
  AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
  AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { useCategories, useProducts, useInvalidate } from "@/hooks/useData";
import { api } from "@/lib/db";
import { faDigits, toman, uid } from "@/lib/format";
import type { Category, Product, ProductSize } from "@/lib/types";
import { cn } from "@/lib/utils";

const PRESET_IMAGES = [
  "/images/menu/pizza-margherita.jpg", "/images/menu/pizza-pepperoni.jpg",
  "/images/menu/pizza-supreme.jpg", "/images/menu/pizza-veggie.jpg",
  "/images/menu/burger-classic.jpg", "/images/menu/fried-chicken.jpg",
  "/images/menu/club-sandwich.jpg", "/images/menu/fries-cheese.jpg",
  "/images/menu/pasta-alffredo.jpg", "/images/menu/salad-caesar.jpg",
  "/images/menu/dessert-lava.jpg", "/images/menu/drinks.jpg",
];

export default function AdminMenu() {
  const { data: products, isLoading } = useProducts();
  const { data: categories } = useCategories();
  const invalidate = useInvalidate();

  const [catFilter, setCatFilter] = useState("all");
  const [q, setQ] = useState("");
  const [editing, setEditing] = useState<Product | null>(null);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [deleting, setDeleting] = useState<Product | null>(null);
  const [catDialogOpen, setCatDialogOpen] = useState(false);

  const filtered = useMemo(() => {
    let list = products ?? [];
    if (catFilter !== "all") list = list.filter((p) => p.category_id === catFilter);
    if (q.trim()) list = list.filter((p) => p.name.includes(q.trim()));
    return list;
  }, [products, catFilter, q]);

  const openNew = () => {
    setEditing({
      id: uid("pz"),
      category_id: categories?.[0]?.id ?? "",
      name: "",
      description: "",
      image: PRESET_IMAGES[0],
      base_price: 100000,
      sizes: [{ id: uid("sz"), name: "تک", price: 100000 }],
      is_available: true,
      is_featured: false,
      discount_percent: 0,
      spicy: false,
      vegetarian: false,
      calories: undefined,
      prep_minutes: 15,
      rating: 4.5,
      rating_count: 0,
      sort: (products?.length ?? 0) + 1,
    });
    setDialogOpen(true);
  };

  const save = async (p: Product) => {
    await api.upsertProduct(p);
    await invalidate();
    setDialogOpen(false);
  };

  const remove = async (id: string) => {
    await api.deleteProduct(id);
    await invalidate();
    setDeleting(null);
  };

  const toggleAvailable = async (p: Product) => {
    await api.upsertProduct({ ...p, is_available: !p.is_available });
    await invalidate();
  };

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-black">مدیریت منو</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            {faDigits(products?.length ?? 0)} آیتم در {faDigits(categories?.length ?? 0)} دسته‌بندی
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="outline" onClick={() => setCatDialogOpen(true)}>
            <UtensilsCrossed className="h-4 w-4 ml-1.5" /> دسته‌بندی‌ها
          </Button>
          <Button onClick={openNew}>
            <Plus className="h-4 w-4 ml-1.5" /> افزودن آیتم جدید
          </Button>
        </div>
      </div>

      {/* فیلترها */}
      <div className="flex flex-wrap items-center gap-2">
        <div className="relative">
          <Search className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="جستجوی آیتم..."
            className="h-10 w-52 rounded-full pr-9 text-sm"
          />
        </div>
        <div className="flex gap-2 overflow-x-auto no-scrollbar">
          <button
            onClick={() => setCatFilter("all")}
            className={cn(
              "shrink-0 rounded-full border px-4 py-1.5 text-xs font-bold transition",
              catFilter === "all" ? "border-primary bg-primary text-primary-foreground" : "border-border bg-card"
            )}
          >
            همه
          </button>
          {(categories ?? []).map((c) => (
            <button
              key={c.id}
              onClick={() => setCatFilter(c.id)}
              className={cn(
                "shrink-0 rounded-full border px-4 py-1.5 text-xs font-bold transition",
                catFilter === c.id ? "border-primary bg-primary text-primary-foreground" : "border-border bg-card"
              )}
            >
              {c.name}
            </button>
          ))}
        </div>
      </div>

      {/* جدول */}
      {isLoading ? (
        <div className="space-y-3">
          {Array.from({ length: 6 }).map((_, i) => <Skeleton key={i} className="h-20 rounded-2xl" />)}
        </div>
      ) : (
        <div className="space-y-3">
          {filtered.map((p) => (
            <div
              key={p.id}
              className="flex flex-wrap items-center gap-4 rounded-2xl border bg-card p-4"
            >
              <img src={p.image} alt="" className="h-16 w-16 shrink-0 rounded-xl object-cover" loading="lazy" />
              <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                  <p className="font-bold">{p.name}</p>
                  {p.is_featured && <Badge className="h-5 gap-1 text-[10px]"><Sparkles className="h-3 w-3" /> ویژه</Badge>}
                  {p.discount_percent > 0 && <Badge className="h-5 bg-primary text-[10px]">{faDigits(p.discount_percent)}٪ تخفیف</Badge>}
                  {p.spicy && <Flame className="h-3.5 w-3.5 text-rose-500" />}
                  {p.vegetarian && <Leaf className="h-3.5 w-3.5 text-emerald-500" />}
                </div>
                <p className="mt-1 flex items-center gap-3 text-xs text-muted-foreground">
                  <span>{categories?.find((c) => c.id === p.category_id)?.name}</span>
                  <span className="flex items-center gap-0.5">
                    <Star className="h-3 w-3 fill-amber-500 text-amber-500" /> {faDigits(p.rating)}
                  </span>
                  <span>{p.sizes.length > 1 ? `${faDigits(p.sizes.length)} سایز` : "تک‌سایز"}</span>
                </p>
              </div>
              <div className="text-left">
                <p className="text-sm font-extrabold text-primary">{toman(p.base_price / 1000)} هزار</p>
              </div>
              <div className="flex items-center gap-3">
                <label className="flex cursor-pointer items-center gap-2 text-xs">
                  <Switch checked={p.is_available} onCheckedChange={() => toggleAvailable(p)} />
                  <span className={p.is_available ? "text-accent" : "text-muted-foreground"}>
                    {p.is_available ? "موجود" : "ناموجود"}
                  </span>
                </label>
                <Button variant="outline" size="icon" className="h-9 w-9" onClick={() => { setEditing(p); setDialogOpen(true); }} aria-label="ویرایش">
                  <Pencil className="h-4 w-4" />
                </Button>
                <Button variant="outline" size="icon" className="h-9 w-9 text-destructive hover:bg-destructive/10" onClick={() => setDeleting(p)} aria-label="حذف">
                  <Trash2 className="h-4 w-4" />
                </Button>
              </div>
            </div>
          ))}
          {filtered.length === 0 && (
            <div className="rounded-3xl border border-dashed p-12 text-center text-sm text-muted-foreground">
              آیتمی یافت نشد.
            </div>
          )}
        </div>
      )}

      {/* دیالوگ ویرایش محصول */}
      <ProductDialog
        product={editing}
        categories={categories ?? []}
        open={dialogOpen}
        onOpenChange={setDialogOpen}
        onSave={save}
      />

      {/* دیالوگ دسته‌بندی‌ها */}
      <CategoryDialog open={catDialogOpen} onOpenChange={setCatDialogOpen} />

      {/* تایید حذف */}
      <AlertDialog open={!!deleting} onOpenChange={(v) => !v && setDeleting(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>حذف {deleting?.name}؟</AlertDialogTitle>
            <AlertDialogDescription>
              این آیتم برای همیشه از منو حذف می‌شود. اگر موقتاً نمی‌خواهید قابل سفارش باشد،
              گزینه «موجود» را خاموش کنید.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>انصراف</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onClick={() => deleting && remove(deleting.id)}
            >
              حذف کن
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}

/* ------------------------------------------------------------------ */

function ProductDialog({
  product, categories, open, onOpenChange, onSave,
}: {
  product: Product | null;
  categories: Category[];
  open: boolean;
  onOpenChange: (v: boolean) => void;
  onSave: (p: Product) => void;
}) {
  const [form, setForm] = useState<Product | null>(product);

  useEffect(() => setForm(product), [product]);

  if (!form) return null;

  const set = <K extends keyof Product>(key: K, value: Product[K]) =>
    setForm((f) => (f ? { ...f, [key]: value } : f));

  const setSize = (i: number, patch: Partial<ProductSize>) => {
    const sizes = [...form.sizes];
    sizes[i] = { ...sizes[i], ...patch };
    set("sizes", sizes);
    if (i === 0 && patch.price !== undefined) set("base_price", patch.price);
  };

  const addSize = () => {
    set("sizes", [...form.sizes, { id: uid("sz"), name: "", price: form.base_price }]);
  };

  const removeSize = (i: number) => {
    if (form.sizes.length <= 1) return;
    set("sizes", form.sizes.filter((_, x) => x !== i));
  };

  const valid = form.name.trim().length > 1 && form.category_id && form.sizes.every((s) => s.name.trim() && s.price > 0);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-h-[92dvh] overflow-y-auto sm:max-w-xl">
        <DialogHeader>
          <DialogTitle>{product?.name ? `ویرایش ${product.name}` : "آیتم جدید منو"}</DialogTitle>
          <DialogDescription>اطلاعات غذا را تکمیل کنید؛ تغییرات بلافاصله در سایت اعمال می‌شود.</DialogDescription>
        </DialogHeader>

        <div className="grid gap-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1.5">
              <Label>نام آیتم *</Label>
              <Input value={form.name} onChange={(e) => set("name", e.target.value)} placeholder="مثلاً پیتزا پپرونی" />
            </div>
            <div className="space-y-1.5">
              <Label>دسته‌بندی *</Label>
              <Select value={form.category_id} onValueChange={(v) => set("category_id", v)} dir="rtl">
                <SelectTrigger><SelectValue placeholder="انتخاب" /></SelectTrigger>
                <SelectContent>
                  {categories.map((c) => <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>)}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="space-y-1.5">
            <Label>توضیحات</Label>
            <Textarea
              value={form.description}
              onChange={(e) => set("description", e.target.value)}
              placeholder="مواد تشکیل‌دهنده و توضیح کوتاه..."
              className="min-h-16"
            />
          </div>

          <div className="space-y-1.5">
            <Label>تصویر</Label>
            <div className="flex items-center gap-3">
              <img src={form.image} alt="" className="h-16 w-16 rounded-xl border object-cover" />
              <Input
                value={form.image}
                onChange={(e) => set("image", e.target.value)}
                placeholder="/images/menu/... یا URL"
                dir="ltr"
                className="text-xs"
              />
            </div>
            <div className="mt-1 flex gap-1.5 overflow-x-auto no-scrollbar pb-1">
              {PRESET_IMAGES.map((img) => (
                <button
                  key={img}
                  onClick={() => set("image", img)}
                  className={cn(
                    "h-12 w-12 shrink-0 overflow-hidden rounded-lg border-2 transition",
                    form.image === img ? "border-primary" : "border-transparent opacity-60 hover:opacity-100"
                  )}
                >
                  <img src={img} alt="" className="h-full w-full object-cover" />
                </button>
              ))}
            </div>
          </div>

          {/* سایزها */}
          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <Label>سایزها و قیمت‌ها (تومان) *</Label>
              <Button type="button" variant="outline" size="sm" className="h-8 text-xs" onClick={addSize}>
                <Plus className="h-3.5 w-3.5 ml-1" /> افزودن سایز
              </Button>
            </div>
            {form.sizes.map((s, i) => (
              <div key={s.id} className="flex gap-2">
                <Input
                  value={s.name}
                  onChange={(e) => setSize(i, { name: e.target.value })}
                  placeholder="نام سایز (متوسط...)"
                  className="flex-1"
                />
                <Input
                  value={String(s.price)}
                  onChange={(e) => setSize(i, { price: Number(e.target.value.replace(/\D/g, "")) || 0 })}
                  inputMode="numeric"
                  dir="ltr"
                  className="w-36 text-left"
                />
                <Button
                  type="button"
                  variant="outline"
                  size="icon"
                  className="shrink-0 text-destructive"
                  disabled={form.sizes.length <= 1}
                  onClick={() => removeSize(i)}
                  aria-label="حذف سایز"
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>
            ))}
          </div>

          <div className="grid gap-4 sm:grid-cols-3">
            <div className="space-y-1.5">
              <Label>درصد تخفیف</Label>
              <Input
                value={String(form.discount_percent)}
                onChange={(e) => set("discount_percent", Math.min(90, Number(e.target.value.replace(/\D/g, "")) || 0))}
                inputMode="numeric"
                dir="ltr"
                className="text-left"
              />
            </div>
            <div className="space-y-1.5">
              <Label>زمان آماده‌سازی (دقیقه)</Label>
              <Input
                value={String(form.prep_minutes ?? "")}
                onChange={(e) => set("prep_minutes", Number(e.target.value.replace(/\D/g, "")) || undefined)}
                inputMode="numeric"
                dir="ltr"
                className="text-left"
              />
            </div>
            <div className="space-y-1.5">
              <Label>کالری</Label>
              <Input
                value={String(form.calories ?? "")}
                onChange={(e) => set("calories", Number(e.target.value.replace(/\D/g, "")) || undefined)}
                inputMode="numeric"
                dir="ltr"
                className="text-left"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3 rounded-2xl border p-4 sm:grid-cols-4">
            <ToggleField label="موجود" checked={form.is_available} onChange={(v) => set("is_available", v)} />
            <ToggleField label="ویژه (صفحه اصلی)" checked={form.is_featured} onChange={(v) => set("is_featured", v)} />
            <ToggleField label="تند 🌶️" checked={form.spicy} onChange={(v) => set("spicy", v)} />
            <ToggleField label="گیاهی 🌿" checked={form.vegetarian} onChange={(v) => set("vegetarian", v)} />
          </div>

          <Button className="w-full" size="lg" disabled={!valid} onClick={() => valid && onSave(form)}>
            ذخیره آیتم
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}

function ToggleField({ label, checked, onChange }: { label: string; checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <label className="flex cursor-pointer flex-col items-center gap-2 text-xs font-bold">
      <Switch checked={checked} onCheckedChange={onChange} />
      {label}
    </label>
  );
}

/* ------------------------------------------------------------------ */

function CategoryDialog({ open, onOpenChange }: { open: boolean; onOpenChange: (v: boolean) => void }) {
  const { data: categories } = useCategories();
  const invalidate = useInvalidate();
  const [name, setName] = useState("");

  const add = async () => {
    if (!name.trim()) return;
    await api.upsertCategory({
      id: uid("cat"),
      slug: `cat-${Date.now()}`,
      name: name.trim(),
      is_active: true,
      sort: (categories?.length ?? 0) + 1,
    });
    await invalidate();
    setName("");
  };

  const remove = async (id: string) => {
    await api.deleteCategory(id);
    await invalidate();
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <ImagePlus className="h-4.5 w-4.5 text-primary" /> مدیریت دسته‌بندی‌ها
          </DialogTitle>
          <DialogDescription>دسته‌بندی جدید اضافه کنید یا حذف کنید (آیتم‌های دسته حذف‌شده هم حذف می‌شوند).</DialogDescription>
        </DialogHeader>
        <div className="space-y-3">
          <div className="flex gap-2">
            <Input value={name} onChange={(e) => setName(e.target.value)} placeholder="نام دسته (مثلاً کباب)" onKeyDown={(e) => e.key === "Enter" && add()} />
            <Button onClick={add}><Plus className="h-4 w-4" /></Button>
          </div>
          <div className="space-y-2">
            {(categories ?? []).map((c) => (
              <div key={c.id} className="flex items-center justify-between rounded-xl border px-4 py-2.5">
                <span className="text-sm font-bold">{c.name}</span>
                <Button variant="outline" size="icon" className="h-8 w-8 text-destructive" onClick={() => remove(c.id)} aria-label="حذف">
                  <Trash2 className="h-3.5 w-3.5" />
                </Button>
              </div>
            ))}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
