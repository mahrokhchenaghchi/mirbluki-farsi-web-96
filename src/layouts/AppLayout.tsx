import { useEffect, useState } from "react";
import { NavLink, Outlet, useLocation, useNavigate } from "react-router-dom";
import { BarChart3, CalendarDays, Home, MoreHorizontal, NotebookPen } from "lucide-react";
import { Logo } from "@/components/joma/Logo";
import { LoadingState } from "@/components/joma/LoadingState";
import { useAuth } from "@/hooks/useAuth";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { getMoodForDate } from "@/services/moodService";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { LocalModeBanner } from "@/components/joma/LocalModeBanner";

const NAV = [
  { to: "/app", label: "خانه", icon: Home, end: true },
  { to: "/app/today", label: "امروز", icon: NotebookPen },
  { to: "/app/reports", label: "گزارش", icon: BarChart3 },
  { to: "/app/periods", label: "دوره‌ها", icon: CalendarDays },
];

export function AppLayout() {
  const { user, signOut } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [checkingMood, setCheckingMood] = useState(true);
  const [moreOpen, setMoreOpen] = useState(false);

  useEffect(() => {
    let active = true;
    const today = JomaCalendarService.todayJalaliString();
    getMoodForDate(today)
      .then((record) => {
        if (!active) return;
        if (!record && location.pathname !== "/mood") {
          navigate("/mood", { replace: true });
        }
      })
      .catch(() => {
        if (active && location.pathname !== "/mood") {
          navigate("/mood", { replace: true });
        }
      })
      .finally(() => {
        if (active) setCheckingMood(false);
      });
    return () => {
      active = false;
    };
  }, [location.pathname, navigate]);

  if (checkingMood) {
    return <LoadingState label="آماده‌سازی دوره جاری..." />;
  }

  return (
    <div className="min-h-screen bg-background">
      <LocalModeBanner />
      <aside className="fixed inset-y-0 right-0 z-30 hidden w-64 border-l bg-white/90 px-5 py-6 backdrop-blur lg:flex lg:flex-col">
        <Logo />
        <nav className="mt-10 flex flex-1 flex-col gap-1">
          {NAV.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.end}
              className={({ isActive }) =>
                cn(
                  "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors",
                  isActive ? "bg-primary/10 text-primary" : "text-foreground/80 hover:bg-muted",
                )
              }
            >
              <item.icon className="h-4 w-4" />
              {item.label}
            </NavLink>
          ))}
          <NavLink
            to="/app/plan"
            className={({ isActive }) =>
              cn(
                "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors",
                isActive ? "bg-primary/10 text-primary" : "text-foreground/80 hover:bg-muted",
              )
            }
          >
            برنامه دوره
          </NavLink>
          <NavLink
            to="/app/library"
            className={({ isActive }) =>
              cn(
                "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors",
                isActive ? "bg-primary/10 text-primary" : "text-foreground/80 hover:bg-muted",
              )
            }
          >
            کتابخانه فعالیت
          </NavLink>
          <NavLink
            to="/app/about"
            className={({ isActive }) =>
              cn(
                "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors",
                isActive ? "bg-primary/10 text-primary" : "text-foreground/80 hover:bg-muted",
              )
            }
          >
            درباره جوما
          </NavLink>
          <NavLink
            to="/app/support"
            className={({ isActive }) =>
              cn(
                "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors",
                isActive ? "bg-primary/10 text-primary" : "text-foreground/80 hover:bg-muted",
              )
            }
          >
            پشتیبانی
          </NavLink>
        </nav>
        <div className="mt-auto space-y-3 border-t pt-4">
          <p className="truncate text-xs text-muted-foreground">{user?.email}</p>
          <Button
            variant="outline"
            className="w-full"
            onClick={async () => {
              await signOut();
              navigate("/auth");
            }}
          >
            خروج
          </Button>
        </div>
      </aside>

      <div className="lg:pr-64">
        <header className="sticky top-0 z-20 flex items-center justify-between border-b bg-white/90 px-4 py-3 backdrop-blur lg:hidden">
          <Logo compact />
          <button
            type="button"
            className="rounded-xl p-2 text-foreground hover:bg-muted"
            onClick={() => setMoreOpen((value) => !value)}
            aria-label="منوی بیشتر"
          >
            <MoreHorizontal className="h-5 w-5" />
          </button>
        </header>

        {moreOpen && (
          <div className="border-b bg-white px-4 py-3 lg:hidden">
            <div className="grid grid-cols-2 gap-2 text-sm">
              <NavLink to="/app/plan" onClick={() => setMoreOpen(false)} className="rounded-xl bg-muted px-3 py-2">
                برنامه دوره
              </NavLink>
              <NavLink to="/app/library" onClick={() => setMoreOpen(false)} className="rounded-xl bg-muted px-3 py-2">
                کتابخانه
              </NavLink>
              <NavLink to="/app/about" onClick={() => setMoreOpen(false)} className="rounded-xl bg-muted px-3 py-2">
                درباره جوما
              </NavLink>
              <NavLink to="/app/support" onClick={() => setMoreOpen(false)} className="rounded-xl bg-muted px-3 py-2">
                پشتیبانی
              </NavLink>
              <button
                type="button"
                className="col-span-2 rounded-xl bg-destructive/10 px-3 py-2 text-destructive"
                onClick={async () => {
                  await signOut();
                  navigate("/auth");
                }}
              >
                خروج از حساب
              </button>
            </div>
          </div>
        )}

        <main className="mx-auto min-h-[calc(100vh-8rem)] w-full max-w-5xl px-4 py-6 pb-24 lg:pb-10">
          <Outlet />
        </main>
      </div>

      <nav className="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t bg-white/95 px-2 py-2 backdrop-blur lg:hidden">
        {NAV.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.end}
            className={({ isActive }) =>
              cn(
                "flex flex-col items-center gap-1 rounded-xl px-2 py-1 text-[11px]",
                isActive ? "text-primary" : "text-muted-foreground",
              )
            }
          >
            <item.icon className="h-5 w-5" />
            {item.label}
          </NavLink>
        ))}
      </nav>
    </div>
  );
}
