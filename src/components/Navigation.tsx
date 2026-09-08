import { useState } from "react";
import { Link, NavLink, useNavigate } from "react-router-dom";
import { Pizza, ShoppingBag, User, Menu, Phone, LogOut, Package, LayoutDashboard } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/ui/sheet";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useCart } from "@/store/CartContext";
import { useAuth } from "@/hooks/useAuth";
import { useSettings } from "@/hooks/useData";
import { toman } from "@/lib/format";
import { cn } from "@/lib/utils";

const links = [
  { to: "/", label: "خانه" },
  { to: "/menu", label: "منو و سفارش" },
  { to: "/branches", label: "شعب" },
  { to: "/about", label: "درباره ما" },
  { to: "/contact", label: "تماس" },
];

export default function Navigation() {
  const { count, setCartOpen } = useCart();
  const { user, isAdmin, signOut } = useAuth();
  const { data: settings } = useSettings();
  const navigate = useNavigate();
  const [mobileOpen, setMobileOpen] = useState(false);

  const brand = settings?.brand ?? "پیتزا رویال";

  const navLinkClass = ({ isActive }: { isActive: boolean }) =>
    cn(
      "relative px-1 py-2 text-sm font-medium transition-colors hover:text-primary",
      isActive ? "text-primary" : "text-foreground/80"
    );

  return (
    <header className="sticky top-0 z-50 w-full">
      {/* نوار بالایی */}
      <div className="bg-primary text-primary-foreground">
        <div className="container flex h-9 items-center justify-between text-xs">
          <p className="truncate">
            🛵 ارسال رایگان برای سفارش‌های بالای {toman(600000)} تومان
          </p>
          <a href={`tel:${settings?.phone ?? ""}`} className="hidden items-center gap-1 sm:flex hover:opacity-80">
            <Phone className="h-3 w-3" />
            <span>{settings?.phone}</span>
          </a>
        </div>
      </div>

      {/* نوار اصلی */}
      <div className="border-b border-border/70 bg-background/85 backdrop-blur-md">
        <div className="container flex h-16 items-center justify-between gap-3">
          {/* لوگو */}
          <Link to="/" className="flex items-center gap-2.5 shrink-0">
            <span className="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-md">
              <Pizza className="h-5.5 w-5.5" />
            </span>
            <span className="leading-tight">
              <span className="block text-lg font-extrabold">{brand}</span>
              <span className="block text-[11px] text-muted-foreground">سفارش آنلاین غذا</span>
            </span>
          </Link>

          {/* لینک‌های دسکتاپ */}
          <nav className="hidden items-center gap-6 lg:flex">
            {links.map((l) => (
              <NavLink key={l.to} to={l.to} className={navLinkClass} end={l.to === "/"}>
                {({ isActive }) => (
                  <>
                    {l.label}
                    {isActive && (
                      <span className="absolute inset-x-0 -bottom-0.5 h-0.5 rounded-full bg-primary" />
                    )}
                  </>
                )}
              </NavLink>
            ))}
          </nav>

          {/* اکشن‌ها */}
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="icon"
              className="relative h-10 w-10 rounded-full"
              onClick={() => setCartOpen(true)}
              aria-label="سبد خرید"
            >
              <ShoppingBag className="h-4.5 w-4.5" />
              {count > 0 && (
                <span className="absolute -top-1.5 -right-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-primary-foreground">
                  {toman(count)}
                </span>
              )}
            </Button>

            {user ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="outline" className="hidden h-10 rounded-full gap-2 sm:flex">
                    <User className="h-4 w-4" />
                    <span className="max-w-24 truncate">{user.name}</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-52">
                  <DropdownMenuLabel className="text-xs text-muted-foreground">
                    {user.phone}
                  </DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={() => navigate("/account")}>
                    <User className="h-4 w-4 ml-2" /> حساب کاربری
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => navigate("/track")}>
                    <Package className="h-4 w-4 ml-2" /> پیگیری سفارش
                  </DropdownMenuItem>
                  {isAdmin && (
                    <DropdownMenuItem onClick={() => navigate("/admin")}>
                      <LayoutDashboard className="h-4 w-4 ml-2" /> پنل مدیریت
                    </DropdownMenuItem>
                  )}
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={() => { signOut(); navigate("/"); }}>
                    <LogOut className="h-4 w-4 ml-2" /> خروج
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            ) : (
              <Button className="hidden h-10 rounded-full sm:flex" onClick={() => navigate("/auth")}>
                <User className="h-4 w-4 ml-1" />
                ورود / ثبت‌نام
              </Button>
            )}

            {/* منوی موبایل */}
            <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
              <SheetTrigger asChild>
                <Button variant="outline" size="icon" className="h-10 w-10 rounded-full lg:hidden" aria-label="منو">
                  <Menu className="h-5 w-5" />
                </Button>
              </SheetTrigger>
              <SheetContent side="right" className="w-72 p-0">
                <SheetTitle className="sr-only">منوی اصلی</SheetTitle>
                <div className="flex items-center gap-2.5 border-b p-4">
                  <span className="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground">
                    <Pizza className="h-5 w-5" />
                  </span>
                  <div>
                    <p className="font-extrabold">{brand}</p>
                    <p className="text-xs text-muted-foreground">سفارش آنلاین غذا</p>
                  </div>
                </div>
                <nav className="flex flex-col p-3">
                  {links.map((l) => (
                    <NavLink
                      key={l.to}
                      to={l.to}
                      end={l.to === "/"}
                      onClick={() => setMobileOpen(false)}
                      className={({ isActive }) =>
                        cn(
                          "rounded-xl px-4 py-3 text-sm font-medium transition-colors",
                          isActive ? "bg-primary/10 text-primary" : "hover:bg-secondary"
                        )
                      }
                    >
                      {l.label}
                    </NavLink>
                  ))}
                  {user ? (
                    <>
                      <NavLink
                        to="/account"
                        onClick={() => setMobileOpen(false)}
                        className="rounded-xl px-4 py-3 text-sm font-medium hover:bg-secondary"
                      >
                        حساب کاربری
                      </NavLink>
                      <NavLink
                        to="/track"
                        onClick={() => setMobileOpen(false)}
                        className="rounded-xl px-4 py-3 text-sm font-medium hover:bg-secondary"
                      >
                        پیگیری سفارش
                      </NavLink>
                      {isAdmin && (
                        <NavLink
                          to="/admin"
                          onClick={() => setMobileOpen(false)}
                          className="rounded-xl px-4 py-3 text-sm font-medium hover:bg-secondary"
                        >
                          پنل مدیریت
                        </NavLink>
                      )}
                      <button
                        className="mt-2 rounded-xl px-4 py-3 text-right text-sm font-medium text-destructive hover:bg-destructive/10"
                        onClick={() => { signOut(); setMobileOpen(false); navigate("/"); }}
                      >
                        خروج از حساب
                      </button>
                    </>
                  ) : (
                    <Button
                      className="mt-3"
                      onClick={() => { setMobileOpen(false); navigate("/auth"); }}
                    >
                      ورود / ثبت‌نام
                    </Button>
                  )}
                </nav>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </div>
    </header>
  );
}
