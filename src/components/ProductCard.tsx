import { Star, Clock, Flame, Leaf, Plus } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import type { Product } from "@/lib/types";
import { toman } from "@/lib/format";
import { useCart } from "@/store/CartContext";
import { useToast } from "@/hooks/use-toast";

interface Props {
  product: Product;
  onOpen: (p: Product) => void;
}

export default function ProductCard({ product, onOpen }: Props) {
  const { addLine } = useCart();
  const { toast } = useToast();

  const cheapest = product.sizes.length
    ? Math.min(...product.sizes.map((s) => s.price))
    : product.base_price;

  const finalPrice = (price: number) =>
    Math.round((price * (100 - product.discount_percent)) / 100);

  const singleSize = product.sizes.length <= 1;

  const quickAdd = () => {
    if (!singleSize) {
      onOpen(product);
      return;
    }
    const s = product.sizes[0];
    addLine({
      product_id: product.id,
      size_id: s?.id ?? "default",
      product_name: product.name,
      size_name: s?.name ?? "استاندارد",
      unit_price: finalPrice(s?.price ?? product.base_price),
      qty: 1,
      image: product.image,
    });
    toast({
      title: "به سبد اضافه شد ✓",
      description: `${product.name} — ${toman(finalPrice(s?.price ?? product.base_price))} تومان`,
    });
  };

  return (
    <div
      className="card-hover group relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border bg-card"
      onClick={() => onOpen(product)}
    >
      {/* تصویر */}
      <div className="relative aspect-[4/3] overflow-hidden bg-secondary">
        <img
          src={product.image}
          alt={product.name}
          loading="lazy"
          className="img-zoom h-full w-full object-cover"
        />
        {/* نشان‌ها */}
        <div className="absolute right-2 top-2 flex flex-col items-start gap-1.5">
          {product.discount_percent > 0 && (
            <Badge className="bg-primary text-primary-foreground shadow">
              {toman(product.discount_percent)}٪ تخفیف
            </Badge>
          )}
          {product.spicy && (
            <Badge variant="secondary" className="bg-rose-600 text-white shadow">
              <Flame className="h-3 w-3 ml-0.5" /> تند
            </Badge>
          )}
          {product.vegetarian && (
            <Badge variant="secondary" className="bg-emerald-600 text-white shadow">
              <Leaf className="h-3 w-3 ml-0.5" /> گیاهی
            </Badge>
          )}
        </div>
        {!product.is_available && (
          <div className="absolute inset-0 flex items-center justify-center bg-background/80 backdrop-blur-[2px]">
            <span className="rounded-full bg-foreground/90 px-4 py-1.5 text-xs font-bold text-background">
              موقتا ناموجود
            </span>
          </div>
        )}
      </div>

      {/* بدنه */}
      <div className="flex flex-1 flex-col p-3.5">
        <h3 className="font-bold leading-6">{product.name}</h3>
        <p className="mt-1 line-clamp-2 min-h-[2.4rem] text-xs leading-5 text-muted-foreground">
          {product.description}
        </p>

        <div className="mt-2 flex items-center gap-3 text-[11px] text-muted-foreground">
          <span className="flex items-center gap-1 font-medium text-amber-600">
            <Star className="h-3.5 w-3.5 fill-amber-500 text-amber-500" />
            {toman(product.rating)}
            <span className="text-muted-foreground/70">({toman(product.rating_count)})</span>
          </span>
          {product.prep_minutes && (
            <span className="flex items-center gap-1">
              <Clock className="h-3.5 w-3.5" />
              {toman(product.prep_minutes)} دقیقه
            </span>
          )}
          {product.calories ? (
            <span className="hidden sm:inline">{toman(product.calories)} کالری</span>
          ) : null}
        </div>

        <div className="mt-3 flex items-end justify-between gap-2">
          <div>
            {product.discount_percent > 0 && (
              <p className="text-[11px] text-muted-foreground line-through">
                {toman(cheapest)}
              </p>
            )}
            <p className="text-primary">
              <span className="text-lg font-extrabold">{toman(finalPrice(cheapest))}</span>
              <span className="mr-1 text-[10px] font-normal text-muted-foreground">تومان</span>
            </p>
            {product.sizes.length > 1 && (
              <p className="text-[10px] text-muted-foreground">از کوچک تا خانواده</p>
            )}
          </div>
          <Button
            size="icon"
            className="h-10 w-10 shrink-0 rounded-full shadow-md"
            disabled={!product.is_available}
            onClick={(e) => {
              e.stopPropagation();
              quickAdd();
            }}
            aria-label={`افزودن ${product.name} به سبد`}
          >
            <Plus className="h-5 w-5" />
          </Button>
        </div>
      </div>
    </div>
  );
}
