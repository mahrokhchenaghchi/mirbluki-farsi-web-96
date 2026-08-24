import { useEffect, useState } from "react";
import { NavLink, Outlet, useLocation, useNavigate } from "react-router-dom";
import {
  BarChart3,
  BookOpen,
  CalendarDays,
  Heart,
  Home,
  MoreHorizontal,
  NotebookPen,
  Settings,
  UserRound,
} from "lucide-react";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { Button } from "@/components/ui/button";
import { LoadingState } from "@/components/joma/LoadingState";
import { LocalModeBanner } from "@/components/joma/LocalModeBanner";
import { Logo } from "@/components/joma/Logo";
import { useAuth } from "@/hooks/useAuth";
import { cn } from "@/lib/utils";
import { getMoodForDate } from "@/services/moodService";

const PRIMARY = [
  { to: "/app", label: "داشبورد", icon: Home, end: true },
  { to: "/app/today", label: "امروز", icon: NotebookPen },
  { to: "/app/mood", label: "خلق", icon: Heart },
  { to: "/app/reports", label: "گزارش", icon: BarChart3 },
];

const SECONDARY = [
  { to: "/app/plan", label: "برنامه من" },
  { to: "/app/periods", label: "دوره‌های من", icon: CalendarDays },
  { to: "/app/library", label: "کتابخانه فعالیت‌ها", icon: BookOpen },
  { to: "/app/profile", label: "پروفایل من", icon: UserRound },
  { to: "/app/settings", label: "تنظیمات", icon: Settings },
  { to: "/app/about", label: "درباره جوما" },
  { to: "/app/support", label: "پشتیبانی" },
];

export function AppLayout() {
  const { user, signOut } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [checkingMood, setCheckingMood] = useState(true);
  const [moreOpen, setMoreOpen] = useState(false);

  useEffect(() => {
    let active = true;
    const skip = location.pathname === "/mood" || location.pathname === "/app/mood";
    getMoodForDate(JomaCalendarService.todayJalaliString())
      .then((record) => {
        if (!active || skip) return;
        if (!record) navigate("/mood", { replace: true });
      })
      .catch(() => {
        if (active && !skip) navigate("/mood", { replace: true });
      })
      .finally(() => {
        if (active) setCheckingMood(false);
      });
    return () => {
      active = false;
    };
  }, [location.pathname, navigate]);

  if (checkingMood) return <LoadingState />;

  return (
    <div className="min-h-screen">
      <LocalModeBanner />
      <aside className="fixed inset-y-0 right-0 z-30 hidden w-72 overflow-y-auto border-l border-white/60 bg-white/80 px-5 py-6 backdrop-blur lg:flex lg:flex-col">
        <Logo size={56} />
        <nav className="mt-8 flex flex-1 flex-col gap-1">
          {[...PRIMARY, ...SECONDARY].map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={"end" in item ? Boolean(item.end) : false}
              className={({ isActive }) =>
                cn("rounded-2xl px-3 py-2.5 text-sm", isActive ? "bg-primary/15 font-bold text-primary" : "hover:bg-white")
              }
            >
              {item.label}
            </NavLink>
          ))}
        </nav>
        <div className="space-y-3 border-t pt-4">
          <p className="truncate text-xs text-muted-foreground">{user?.fullName || user?.email}</p>
          <Button variant="outline" className="w-full" onClick={async () => { await signOut(); navigate("/auth", { replace: true }); }}>خروج</Button>
        </div>
      </aside>

      <div className="lg:pr-72">
        <header className="sticky top-0 z-20 flex items-center justify-between border-b bg-white/80 px-4 py-3 backdrop-blur lg:hidden">
          <Logo compact size={40} />
          <button type="button" className="rounded-xl p-2" onClick={() => setMoreOpen((value) => !value)} aria-label="منو">
            <MoreHorizontal />
          </button>
        </header>
        {moreOpen && (
          <div className="grid grid-cols-2 gap-2 border-b bg-white px-4 py-3 text-sm lg:hidden">
            {SECONDARY.map((item) => (
              <NavLink key={item.to} to={item.to} onClick={() => setMoreOpen(false)} className="rounded-xl bg-muted px-3 py-2">
                {item.label}
              </NavLink>
            ))}
            <button type="button" className="col-span-2 rounded-xl bg-destructive/10 px-3 py-2 text-destructive" onClick={async () => { await signOut(); navigate("/auth", { replace: true }); }}>
              خروج
            </button>
          </div>
        )}
        <main className="mx-auto w-full max-w-6xl px-4 py-6 pb-24 lg:pb-10">
          <Outlet />
        </main>
      </div>

      <nav className="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t bg-white/90 px-2 py-2 backdrop-blur lg:hidden">
        {PRIMARY.map((item) => (
          <NavLink key={item.to} to={item.to} end={item.end} className={({ isActive }) => cn("flex flex-col items-center gap-1 py-1 text-[11px]", isActive ? "text-primary" : "text-muted-foreground")}>
            <item.icon className="h-5 w-5" />
            {item.label}
          </NavLink>
        ))}
      </nav>
    </div>
  );
}
