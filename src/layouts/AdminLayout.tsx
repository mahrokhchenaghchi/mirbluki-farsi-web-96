import { useState } from "react";
import { NavLink, Outlet, useNavigate } from "react-router-dom";
import {
  LayoutDashboard, ClipboardList, UtensilsCrossed, Store,
  TicketPercent, Users, Settings, Pizza, LogOut, Menu as MenuIcon, ArrowRight,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/ui/sheet";
import { useAuth } from "@/hooks/useAuth";
import { useSettings } from "@/hooks/useData";
import { cn } from "@/lib/utils";

const nav = [
  { to: "/admin", label: "داشبورد", icon: LayoutDashboard, end: true },
  { to: "/admin/orders", label: "سفارش‌ها", icon: ClipboardList, end: false },
  { to: "/admin/menu", label: "مدیریت منو", icon: UtensilsCrossed, end: false },
  { to: "/admin/branches", label: "شعب", icon: Store, end: false },
  { to: "/admin/coupons", label: "کدهای تخفیف", icon: TicketPercent, end: false },
  { to: "/admin/customers", label: "مشتریان", icon: Users, end: false },
  { to: "/admin/settings", label: "تنظیمات", icon: Settings, end: false },
];

function NavLinks({ onNavigate }: { onNavigate?: () => void }) {
  return (
    <nav className="flex flex-col gap-1">
      {nav.map((n) => (
        <NavLink
          key={n.to}
          to={n.to}
          end={n.end}
          onClick={onNavigate}
          className={({ isActive }) =>
            cn(
              "flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors",
              isActive
                ? "bg-sidebar-primary text-sidebar-primary-foreground shadow-lg shadow-black/20"
                : "text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-foreground"
            )
          }
        >
          <n.icon className="h-4.5 w-4.5" />
          {n.label}
        </NavLink>
      ))}
    </nav>
  );
}

export default function AdminLayout() {
  const { user, signOut } = useAuth();
  const { data: settings } = useSettings();
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);

  return (
    <div className="flex min-h-dvh bg-[hsl(30_15%_95%)]">
      {/* سایدبار دسکتاپ */}
      <aside className="sticky top-0 hidden h-dvh w-64 shrink-0 flex-col bg-sidebar p-4 lg:flex">
        <div className="mb-6 flex items-center gap-2.5 px-2 pt-2">
          <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-sidebar-primary text-sidebar-primary-foreground">
            <Pizza className="h-5 w-5" />
          </span>
          <div>
            <p className="text-sm font-extrabold text-white">{settings?.brand ?? "پنل مدیریت"}</p>
            <p className="text-[11px] text-sidebar-foreground/60">پنل مدیریت رستوران</p>
          </div>
        </div>

        <NavLinks />

        <div className="mt-auto space-y-2">
          <div className="rounded-xl bg-sidebar-accent p-3">
            <p className="text-xs font-bold text-sidebar-foreground">{user?.name}</p>
            <p className="mt-0.5 text-[11px] text-sidebar-foreground/60" dir="ltr">{user?.phone}</p>
          </div>
          <button
            onClick={() => { signOut(); navigate("/"); }}
            className="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-sidebar-foreground/70 transition hover:bg-destructive/15 hover:text-red-300"
          >
            <LogOut className="h-4.5 w-4.5" /> خروج از حساب
          </button>
          <button
            onClick={() => navigate("/")}
            className="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-sidebar-foreground/70 transition hover:bg-sidebar-accent"
          >
            <ArrowRight className="h-4.5 w-4.5" /> بازگشت به سایت
          </button>
        </div>
      </aside>

      {/* محتوا */}
      <div className="flex min-w-0 flex-1 flex-col">
        {/* نوار موبایل */}
        <header className="sticky top-0 z-40 flex h-14 items-center justify-between border-b bg-sidebar px-4 lg:hidden">
          <div className="flex items-center gap-2">
            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
              <Pizza className="h-4 w-4" />
            </span>
            <span className="text-sm font-extrabold text-white">پنل مدیریت</span>
          </div>
          <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon" className="text-white hover:bg-sidebar-accent" aria-label="منو">
                <MenuIcon className="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" className="w-72 border-0 bg-sidebar p-4">
              <SheetTitle className="sr-only">منوی مدیریت</SheetTitle>
              <div className="mb-6 flex items-center gap-2.5 px-2 pt-2">
                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-sidebar-primary text-sidebar-primary-foreground">
                  <Pizza className="h-5 w-5" />
                </span>
                <div>
                  <p className="text-sm font-extrabold text-white">{settings?.brand}</p>
                  <p className="text-[11px] text-sidebar-foreground/60">پنل مدیریت</p>
                </div>
              </div>
              <NavLinks onNavigate={() => setOpen(false)} />
              <div className="mt-auto space-y-2 pt-4">
                <button
                  onClick={() => { signOut(); navigate("/"); }}
                  className="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-sidebar-foreground/70 transition hover:bg-destructive/15 hover:text-red-300"
                >
                  <LogOut className="h-4.5 w-4.5" /> خروج
                </button>
                <button
                  onClick={() => navigate("/")}
                  className="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-sidebar-foreground/70 transition hover:bg-sidebar-accent"
                >
                  <ArrowRight className="h-4.5 w-4.5" /> بازگشت به سایت
                </button>
              </div>
            </SheetContent>
          </Sheet>
        </header>

        <main className="flex-1 p-4 sm:p-6 lg:p-8">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
