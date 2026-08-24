import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import JomaCalendarService from "@/calendar/JomaCalendarService";
import { MOOD_METRICS, type MoodMetricKey } from "@/domain/catalog";
import type { MoodScores } from "@/domain/types";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Logo } from "@/components/joma/Logo";
import { LoadingState } from "@/components/joma/LoadingState";
import { getMoodForDate, recordMood } from "@/services/moodService";
import { toUserMessage } from "@/lib/errors";
import { useAuth } from "@/hooks/useAuth";
import { cn } from "@/lib/utils";

const EMPTY: MoodScores = { energy: 0, general: 0, focus: 0, sleep: 0, stress: 0 };

export default function MoodPage() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const today = JomaCalendarService.todayJalaliString();
  const [loading, setLoading] = useState(true);
  const [scores, setScores] = useState<MoodScores>(EMPTY);
  const [note, setNote] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    getMoodForDate(today)
      .then((record) => {
        if (record) {
          setScores(record.scores);
          setNote(record.note);
        }
      })
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, [today]);

  const hour = new Date().getHours();
  const hello = hour < 12 ? "صبح بخیر" : hour < 18 ? "وقت بخیر" : "عصر بخیر";
  const complete = MOOD_METRICS.every((metric) => scores[metric.key] >= 1);

  const save = async () => {
    if (!complete) {
      setError("هر پنج شاخص را انتخاب کنید.");
      return;
    }
    setBusy(true);
    setError(null);
    try {
      await recordMood(today, scores, note);
      navigate("/app", { replace: true });
    } catch (err) {
      setError(toUserMessage(err));
    } finally {
      setBusy(false);
    }
  };

  if (loading) return <LoadingState label="آماده‌سازی حال امروز..." />;

  return (
    <div className="min-h-screen px-4 py-8">
      <div className="mx-auto max-w-2xl space-y-6">
        <Logo size={72} />
        <div>
          <h1 className="text-4xl font-black">{hello}{user?.fullName ? ` ${user.fullName.split(" ")[0]}` : ""}</h1>
          <p className="mt-2 text-lg text-muted-foreground">امروز چطوری؟</p>
          <p className="text-sm text-muted-foreground">{JomaCalendarService.formatJalaliDisplay(today)}</p>
        </div>

        {MOOD_METRICS.map((metric) => (
          <section key={metric.key} className="joma-card p-5">
            <h2 className="mb-4 font-bold">{metric.title}</h2>
            <div className="grid grid-cols-5 gap-2">
              {metric.stickers.map((sticker, index) => {
                const score = index + 1;
                const selected = scores[metric.key] === score;
                return (
                  <button
                    key={sticker}
                    type="button"
                    className={cn(
                      "flex h-16 flex-col items-center justify-center rounded-2xl text-2xl transition",
                      selected ? "scale-105 bg-primary text-white shadow-lg" : "bg-muted hover:bg-white",
                    )}
                    onClick={() => setScores((current) => ({ ...current, [metric.key]: score } as MoodScores))}
                  >
                    <span>{sticker}</span>
                  </button>
                );
              })}
            </div>
          </section>
        ))}

        <section className="joma-card p-5">
          <h2 className="mb-3 font-bold">یادداشت امروز (اختیاری)</h2>
          <Textarea value={note} onChange={(e) => setNote(e.target.value)} rows={3} placeholder="اگر چیزی روی دلت است بنویس..." />
        </section>

        {error && <p className="text-sm text-destructive">{error}</p>}
        <Button size="lg" className="w-full" onClick={save} disabled={busy}>
          {busy ? "در حال ذخیره..." : "ادامه به داشبورد"}
        </Button>
      </div>
    </div>
  );
}
