import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { Button } from "@/components/ui/button";
import { Logo } from "@/components/joma/Logo";
import { LoadingState } from "@/components/joma/LoadingState";
import { UnspecifiedNotice } from "@/components/joma/UnspecifiedNotice";
import { getMoodForDate, listMoodMetricDefinitions, recordUnspecifiedMoodCheckIn } from "@/services/moodService";
import { toUserMessage } from "@/lib/errors";
import { useAuth } from "@/hooks/useAuth";

export default function MoodPage() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const today = JomaCalendarService.todayJalaliString();
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [definedMetrics, setDefinedMetrics] = useState(0);

  useEffect(() => {
    Promise.all([getMoodForDate(today), listMoodMetricDefinitions()])
      .then(([record, definitions]) => {
        setDefinedMetrics(definitions.filter((item) => item.specified).length);
        if (record) navigate("/app", { replace: true });
      })
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, [navigate, today]);

  const greeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return "صبح بخیر";
    if (hour < 18) return "وقت بخیر";
    return "عصر بخیر";
  };

  const continueToApp = async () => {
    setBusy(true);
    setError(null);
    try {
      await recordUnspecifiedMoodCheckIn(today);
      navigate("/app", { replace: true });
    } catch (err) {
      setError(toUserMessage(err));
    } finally {
      setBusy(false);
    }
  };

  if (loading) return <LoadingState label="آماده‌سازی ورود روزانه..." />;

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-background to-secondary px-4">
      <div className="w-full max-w-lg rounded-3xl border bg-card p-8">
        <Logo />
        <h1 className="mt-8 text-3xl font-black">{greeting()}</h1>
        <p className="mt-3 text-lg text-muted-foreground">امروز چطوری؟</p>
        <p className="mt-2 text-sm text-muted-foreground">
          {user?.email} · {JomaCalendarService.formatJalaliDisplay(today)}
        </p>

        <div className="mt-6">
          {definedMetrics === 0 ? (
            <UnspecifiedNotice>
              پنج شاخص خلق در مشخصات فعلی محصول تعریف نشده‌اند
              (<span dir="ltr">MOOD_METRICS = UNSPECIFIED</span>).
              ساختار ذخیرهٔ خلق آماده است و بعداً بدون بازنویسی معماری اضافه می‌شود.
            </UnspecifiedNotice>
          ) : (
            <p>شاخص‌های تعریف‌شده آماده ثبت هستند.</p>
          )}
        </div>

        {error && <p className="mt-4 text-sm text-destructive">{error}</p>}

        <Button className="mt-8 w-full" size="lg" onClick={continueToApp} disabled={busy}>
          {busy ? "در حال ثبت..." : "ادامه به داشبورد"}
        </Button>
      </div>
    </div>
  );
}
