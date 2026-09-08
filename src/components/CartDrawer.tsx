import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Minus, Plus, Trash2, ShoppingBag, TicketPercent, X, Bike } from "lucide-react";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Separator } from "@/components/ui/separator";
import { useCart } from "@/store/CartContext";
import { toman } from "@/lib/format";
import { useBranches } from "@/hooks/useData";

export default function CartDrawer() {
  const {
    lines, count, subtotal, discount, payable,
    updateQty, removeLine, isCartOpen, setCartOpen,
    coupon, couponError, applyCoupon, removeCoupon, branchId,
  } = useCart();
  const { data: branches } = useBranches();
  const navigate = useNavigate();
  const [code, setCode] = useState("");

  const branch = branches?.find((b) => b.id === branchId);

  const goCheckout = () => {
    setCartOpen(false);
    navigate("/checkout");
  };

  return (
    <Sheet open={isCartOpen} onOpenChange={setCartOpen}>
      <SheetContent side="left" className="flex w-full flex-col p-0 sm:max-w-md">
        <SheetHeader className="border-b p-4 pb-3">
          <SheetTitle className="flex items-center gap-2">
            <ShoppingBag className="h-5 w-5 text-primary" />
            سبد خرید
            {count > 0 && (
              <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">
                {toman(count)} قلم
              </span>
            )}
          </SheetTitle>
          <SheetDescription className="text-xs">
            {branch ? `سفارش از ${branch.name}` : "ابتدا یک شعبه و آیتم انتخاب کنید"}
          </SheetDescription>
        </SheetHeader>

        {lines.length === 0 ? (
          <div className="flex flex-1 flex-col items-center justify-center gap-4 p-8 text-center">
            <span className="text-6xl">🍕</span>
            <p className="font-bold">سبد خرید شما خالی است</p>
            <p className="text-sm text-muted-foreground">
              هنوز گرسنه‌ای؟ از منو یکی از غذاهای محبوب ما را انتخاب کن.
            </p>
            <Button onClick={() => { setCartOpen(false); navigate("/menu"); }}>
              رفتن به منو
            </Button>
          </div>
        ) : (
          <>
            {/* آیتم‌ها */}
            <div className="flex-1 space-y-3 overflow-y-auto p-4">
              {lines.map((l, i) => (
                <div key={`${l.product_id}-${l.size_id}-${i}`} className="flex gap-3 rounded-2xl border bg-card p-3">
                  {l.image && (
                    <img
                      src={l.image}
                      alt={l.product_name}
                      className="h-16 w-16 shrink-0 rounded-xl object-cover"
                      loading="lazy"
                    />
                  )}
                  <div className="flex min-w-0 flex-1 flex-col">
                    <div className="flex items-start justify-between gap-2">
                      <div className="min-w-0">
                        <p className="truncate text-sm font-bold">{l.product_name}</p>
                        <p className="text-xs text-muted-foreground">{l.size_name}</p>
                        {l.note && (
                          <p className="mt-0.5 truncate text-[11px] text-muted-foreground/80">📝 {l.note}</p>
                        )}
                      </div>
                      <button
                        onClick={() => removeLine(i)}
                        className="text-muted-foreground transition hover:text-destructive"
                        aria-label="حذف"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                    <div className="mt-auto flex items-center justify-between pt-2">
                      <div className="flex items-center gap-1 rounded-full border px-1">
                        <button
                          onClick={() => updateQty(i, l.qty + 1)}
                          className="flex h-7 w-7 items-center justify-center rounded-full text-primary transition hover:bg-primary/10"
                          aria-label="افزایش"
                        >
                          <Plus className="h-3.5 w-3.5" />
                        </button>
                        <span className="w-6 text-center text-sm font-bold">{toman(l.qty)}</span>
                        <button
                          onClick={() => updateQty(i, l.qty - 1)}
                          className="flex h-7 w-7 items-center justify-center rounded-full text-muted-foreground transition hover:bg-secondary"
                          aria-label="کاهش"
                        >
                          <Minus className="h-3.5 w-3.5" />
                        </button>
                      </div>
                      <p className="text-sm font-extrabold text-primary">
                        {toman(l.unit_price * l.qty)}
                        <span className="mr-1 text-[10px] font-normal text-muted-foreground">تومان</span>
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* کوپن و جمع‌بندی */}
            <div className="space-y-3 border-t bg-secondary/40 p-4">
              {coupon ? (
                <div className="flex items-center justify-between rounded-xl border border-dashed border-primary/40 bg-primary/5 px-3 py-2">
                  <span className="flex items-center gap-1.5 text-sm font-bold text-primary">
                    <TicketPercent className="h-4 w-4" />
                    کد {coupon.code} اعمال شد
                  </span>
                  <button onClick={removeCoupon} className="text-muted-foreground hover:text-destructive" aria-label="حذف کد">
                    <X className="h-4 w-4" />
                  </button>
                </div>
              ) : (
                <div className="flex gap-2">
                  <Input
                    value={code}
                    onChange={(e) => setCode(e.target.value)}
                    placeholder="کد تخفیف دارید؟"
                    className="h-10 bg-card"
                  />
                  <Button
                    variant="secondary"
                    className="h-10 shrink-0"
                    onClick={async () => {
                      if (await applyCoupon(code)) setCode("");
                    }}
                  >
                    اعمال
                  </Button>
                </div>
              )}
              {couponError && <p className="text-xs text-destructive">{couponError}</p>}

              <Separator />

              <div className="space-y-1.5 text-sm">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">جمع اقلام</span>
                  <span className="font-medium">{toman(subtotal)} تومان</span>
                </div>
                {discount > 0 && (
                  <div className="flex justify-between text-accent">
                    <span>تخفیف</span>
                    <span className="font-medium">− {toman(discount)} تومان</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-muted-foreground">هزینه ارسال</span>
                  <span className="flex items-center gap-1 text-xs text-muted-foreground">
                    <Bike className="h-3.5 w-3.5" />
                    در مرحله بعد
                  </span>
                </div>
                <div className="flex justify-between border-t pt-2 text-base font-extrabold">
                  <span>مبلغ قابل پرداخت</span>
                  <span className="text-primary">{toman(payable)} تومان</span>
                </div>
              </div>

              <Button size="lg" className="w-full text-base" onClick={goCheckout}>
                ثبت سفارش
              </Button>
            </div>
          </>
        )}
      </SheetContent>
    </Sheet>
  );
}
