import { useEffect, useState } from "react";
import { Minus, Plus, Star, Clock, Flame, Leaf } from "lucide-react";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import type { Product } from "@/lib/types";
import { toman } from "@/lib/format";
import { useCart } from "@/store/CartContext";
import { cn } from "@/lib/utils";

interface Props {
  product: Product | null;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export default function AddToCartDialog({ product, open, onOpenChange }: Props) {
  const { addLine } = useCart();
  const [sizeIdx, setSizeIdx] = useState(0);
  const [qty, setQty] = useState(1);
  const [note, setNote] = useState("");

  useEffect(() => {
    if (open) {
      setSizeIdx(0);
      setQty(1);
      setNote("");
    }
  }, [open, product?.id]);

  if (!product) return null;

  const sizes = product.sizes.length
    ? product.sizes
    : [{ id: "default", name: "استاندارد", price: product.base_price }];

  const unit = Math.round((sizes[sizeIdx].price * (100 - product.discount_percent)) / 100);
  const total = unit * qty;

  const add = () => {
    addLine({
      product_id: product.id,
      size_id: sizes[sizeIdx].id,
      product_name: product.name,
      size_name: sizes[sizeIdx].name,
      unit_price: unit,
      qty,
      image: product.image,
      note: note.trim() || undefined,
    });
    onOpenChange(false);
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-h-[92dvh] overflow-y-auto p-0 sm:max-w-lg">
        <div className="relative aspect-[16/10] w-full overflow-hidden sm:rounded-t-lg">
          <img src={product.image} alt={product.name} className="h-full w-full object-cover" />
          <div className="absolute right-3 top-3 flex gap-1.5">
            {product.discount_percent > 0 && (
              <Badge className="bg-primary text-primary-foreground">
                {toman(product.discount_percent)}٪ تخفیف
              </Badge>
            )}
            {product.spicy && (
              <Badge className="bg-rose-600 text-white"><Flame className="h-3 w-3 ml-0.5" /> تند</Badge>
            )}
            {product.vegetarian && (
              <Badge className="bg-emerald-600 text-white"><Leaf className="h-3 w-3 ml-0.5" /> گیاهی</Badge>
            )}
          </div>
        </div>

        <DialogHeader className="px-5 pt-4">
          <DialogTitle className="text-xl">{product.name}</DialogTitle>
          <DialogDescription className="flex flex-wrap items-center gap-3">
            <span className="flex items-center gap-1 text-amber-600">
              <Star className="h-4 w-4 fill-amber-500 text-amber-500" />
              {toman(product.rating)} از {toman(product.rating_count)} نظر
            </span>
            {product.prep_minutes && (
              <span className="flex items-center gap-1">
                <Clock className="h-4 w-4" /> آماده‌سازی {toman(product.prep_minutes)} دقیقه
              </span>
            )}
            {product.calories ? <span>{toman(product.calories)} کالری</span> : null}
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4 px-5 pb-5 pt-2">
          <p className="persian-text text-sm text-muted-foreground">{product.description}</p>

          {sizes.length > 1 && (
            <div>
              <Label className="mb-2 block text-sm font-bold">اندازه را انتخاب کنید</Label>
              <div className="grid grid-cols-3 gap-2">
                {sizes.map((s, i) => {
                  const price = Math.round((s.price * (100 - product.discount_percent)) / 100);
                  return (
                    <button
                      key={s.id}
                      onClick={() => setSizeIdx(i)}
                      className={cn(
                        "rounded-xl border-2 p-2.5 text-center transition",
                        sizeIdx === i
                          ? "border-primary bg-primary/5"
                          : "border-border hover:border-primary/40"
                      )}
                    >
                      <span className="block text-xs font-bold">{s.name}</span>
                      <span className={cn("mt-1 block text-xs", sizeIdx === i ? "text-primary font-bold" : "text-muted-foreground")}>
                        {toman(price)}
                      </span>
                    </button>
                  );
                })}
              </div>
            </div>
          )}

          <div>
            <Label className="mb-2 block text-sm font-bold">
              یادداشت برای آشپز <span className="font-normal text-muted-foreground">(اختیاری)</span>
            </Label>
            <Textarea
              value={note}
              onChange={(e) => setNote(e.target.value)}
              placeholder="مثلاً: بدون پیاز، پنیر اضافه، سس تند جدا..."
              className="min-h-16 bg-secondary/40 text-sm"
              maxLength={120}
            />
          </div>

          <div className="flex items-center justify-between rounded-2xl bg-secondary/60 p-3">
            <div className="flex items-center gap-2 rounded-full border bg-card px-1.5 py-1">
              <button
                onClick={() => setQty((q) => Math.max(1, q - 1))}
                className="flex h-8 w-8 items-center justify-center rounded-full transition hover:bg-secondary"
                aria-label="کاهش تعداد"
              >
                <Minus className="h-4 w-4" />
              </button>
              <span className="w-8 text-center font-extrabold">{toman(qty)}</span>
              <button
                onClick={() => setQty((q) => Math.min(20, q + 1))}
                className="flex h-8 w-8 items-center justify-center rounded-full text-primary transition hover:bg-primary/10"
                aria-label="افزایش تعداد"
              >
                <Plus className="h-4 w-4" />
              </button>
            </div>
            <Button size="lg" className="flex-1 justify-between px-5 text-base sm:flex-none sm:justify-center sm:px-10" onClick={add}>
              <span>افزودن به سبد</span>
              <span className="border-r border-primary-foreground/30 pr-3 sm:mr-3">{toman(total)} تومان</span>
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
