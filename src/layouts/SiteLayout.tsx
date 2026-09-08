import { useEffect } from "react";
import { Outlet, useLocation, useNavigate } from "react-router-dom";
import { ShoppingBag } from "lucide-react";
import Navigation from "@/components/Navigation";
import Footer from "@/components/Footer";
import CartDrawer from "@/components/CartDrawer";
import { useCart } from "@/store/CartContext";
import { toman } from "@/lib/format";

export default function SiteLayout() {
  const { count, payable, setCartOpen } = useCart();
  const location = useLocation();
  const navigate = useNavigate();

  // اسکرول به بالا هنگام تغییر صفحه
  useEffect(() => {
    window.scrollTo({ top: 0 });
  }, [location.pathname]);

  return (
    <div className="flex min-h-dvh flex-col bg-pattern-warm">
      <Navigation />
      <main className="flex-1">
        <Outlet />
      </main>
      <Footer />
      <CartDrawer />

      {/* نوار سبد خرید موبایل */}
      {count > 0 && (
        <div className="fixed inset-x-3 bottom-3 z-40 lg:hidden">
          <button
            onClick={() => (location.pathname === "/checkout" ? setCartOpen(true) : navigate("/checkout"))}
            className="flex w-full items-center justify-between rounded-2xl bg-primary px-4 py-3 text-primary-foreground shadow-2xl shadow-primary/30 active:scale-[0.99] transition"
          >
            <span className="flex items-center gap-2 text-sm font-bold">
              <span className="relative">
                <ShoppingBag className="h-5 w-5" />
                <span className="absolute -top-2 -right-2 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-white px-1 text-[10px] font-extrabold text-primary">
                  {toman(count)}
                </span>
              </span>
              مشاهده سبد خرید
            </span>
            <span className="text-sm font-extrabold">{toman(payable)} تومان</span>
          </button>
        </div>
      )}
    </div>
  );
}
