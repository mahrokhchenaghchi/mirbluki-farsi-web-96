import { createContext, useContext, useEffect, useMemo, useState, ReactNode, useCallback } from "react";
import type { CartLine, Coupon } from "@/lib/types";
import { api } from "@/lib/db";
import { toman } from "@/lib/format";

/**
 * سبد خرید — وابسته به شعبه انتخاب‌شده.
 * اگر کاربر شعبه را عوض کند سبد خالی می‌شود (مثل اکثر سایت‌های سفارش غذا).
 */

interface CartContextType {
  branchId: string | null;
  setBranch: (id: string | null) => void;
  lines: CartLine[];
  count: number;
  subtotal: number;
  addLine: (line: CartLine) => void;
  updateQty: (index: number, qty: number) => void;
  removeLine: (index: number) => void;
  clear: () => void;
  coupon: Coupon | null;
  couponError: string | null;
  applyCoupon: (code: string) => Promise<boolean>;
  removeCoupon: () => void;
  discount: number;
  /** مجموع با احتساب تخفیف (بدون هزینه ارسال) */
  payable: number;
  isCartOpen: boolean;
  setCartOpen: (open: boolean) => void;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

const LS_CART = "royal-pizza-cart-v1";
const LS_BRANCH = "royal-pizza-branch";

interface PersistedCart {
  branchId: string | null;
  lines: CartLine[];
  couponCode: string | null;
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<PersistedCart>(() => {
    try {
      const raw = localStorage.getItem(LS_CART);
      if (raw) return JSON.parse(raw);
    } catch {
      /* ignore */
    }
    return { branchId: null, lines: [], couponCode: null };
  });
  const [coupon, setCoupon] = useState<Coupon | null>(null);
  const [couponError, setCouponError] = useState<string | null>(null);
  const [isCartOpen, setCartOpen] = useState(false);

  // بازیابی کوپن ذخیره‌شده
  useEffect(() => {
    if (state.couponCode) {
      api.getCoupons().then((list) => {
        const found = list.find(
          (c) => c.code === state.couponCode && c.is_active
        );
        if (found) setCoupon(found);
        else setState((s) => ({ ...s, couponCode: null }));
      });
    } else {
      setCoupon(null);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [state.couponCode]);

  // ذخیره در localStorage
  useEffect(() => {
    localStorage.setItem(LS_CART, JSON.stringify(state));
  }, [state]);

  const subtotal = useMemo(
    () => state.lines.reduce((sum, l) => sum + l.unit_price * l.qty, 0),
    [state.lines]
  );

  const discount = useMemo(() => {
    if (!coupon || subtotal < coupon.min_order) return 0;
    if (coupon.type === "percent") {
      const d = Math.round((subtotal * coupon.value) / 100);
      return coupon.max_discount ? Math.min(d, coupon.max_discount) : d;
    }
    return Math.min(coupon.value, subtotal);
  }, [coupon, subtotal]);

  const setBranch = useCallback((id: string | null) => {
    setState((s) => (s.branchId === id ? s : { branchId: id, lines: [], couponCode: null }));
    if (id) localStorage.setItem(LS_BRANCH, id);
  }, []);

  const addLine = useCallback((line: CartLine) => {
    setState((s) => {
      const idx = s.lines.findIndex(
        (l) => l.product_id === line.product_id && l.size_id === line.size_id && l.note === line.note
      );
      if (idx >= 0) {
        const lines = [...s.lines];
        lines[idx] = { ...lines[idx], qty: lines[idx].qty + line.qty };
        return { ...s, lines };
      }
      return { ...s, lines: [...s.lines, line] };
    });
  }, []);

  const updateQty = useCallback((index: number, qty: number) => {
    setState((s) => {
      const lines = [...s.lines];
      if (qty <= 0) lines.splice(index, 1);
      else lines[index] = { ...lines[index], qty };
      return { ...s, lines };
    });
  }, []);

  const removeLine = useCallback((index: number) => {
    setState((s) => {
      const lines = [...s.lines];
      lines.splice(index, 1);
      return { ...s, lines };
    });
  }, []);

  const clear = useCallback(() => {
    setState((s) => ({ ...s, lines: [], couponCode: null }));
  }, []);

  const applyCoupon = useCallback(async (code: string) => {
    setCouponError(null);
    const list = await api.getCoupons();
    const found = list.find(
      (c) => c.code.toLowerCase() === code.trim().toLowerCase() && c.is_active
    );
    if (!found) {
      setCouponError("کد تخفیف نامعتبر است.");
      return false;
    }
    if (subtotal < found.min_order) {
      setCouponError(
        `حداقل مبلغ سفارش برای این کد ${
          "٫" + Math.round(found.min_order / 1000).toLocaleString("fa-IR")
        } هزار تومان است.`
      );
      return false;
    }
    setState((s) => ({ ...s, couponCode: found.code }));
    setCoupon(found);
    return true;
  }, [subtotal]);

  const removeCoupon = useCallback(() => {
    setState((s) => ({ ...s, couponCode: null }));
    setCoupon(null);
    setCouponError(null);
  }, []);

  const value: CartContextType = {
    branchId: state.branchId,
    setBranch,
    lines: state.lines,
    count: state.lines.reduce((n, l) => n + l.qty, 0),
    subtotal,
    addLine,
    updateQty,
    removeLine,
    clear,
    coupon,
    couponError,
    applyCoupon,
    removeCoupon,
    discount,
    payable: subtotal - discount,
    isCartOpen,
    setCartOpen,
  };

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error("useCart must be used within a CartProvider");
  }
  return context;
}
