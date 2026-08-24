import { useEffect, useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Logo } from "@/components/joma/Logo";
import { useAuth } from "@/hooks/useAuth";

export default function AuthPage() {
  const { user, signIn, signUp, configured } = useAuth();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const [mode, setMode] = useState<"signin" | "signup">(params.get("mode") === "signup" ? "signup" : "signin");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (user) navigate("/app", { replace: true });
  }, [navigate, user]);

  const submit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError(null);
    setMessage(null);
    if (!email || !password) {
      setError("ایمیل و رمز عبور را وارد کنید.");
      return;
    }
    setBusy(true);
    if (mode === "signin") {
      const result = await signIn(email, password);
      if (result.error) setError(result.error);
    } else {
      const result = await signUp(email, password);
      if (result.error) setError(result.error);
      else if (result.needsConfirmation) setMessage("حساب ساخته شد. ایمیل تأیید را بررسی کنید.");
    }
    setBusy(false);
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-background to-secondary px-4">
      <div className="w-full max-w-md rounded-3xl border bg-card p-8 shadow-sm">
        <Logo />
        <h1 className="mt-6 text-2xl font-bold">{mode === "signin" ? "ورود به جوما" : "ساخت حساب جوما"}</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          نشست شما در این مرورگر ذخیره می‌شود تا با بستن پنجره از حساب خارج نشوید.
        </p>

        {!configured && (
          <p className="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-900">
            Supabase هنوز پیکربندی نشده است. فایل <span dir="ltr">.env.example</span> را ببینید.
          </p>
        )}

        <div className="mt-6 grid grid-cols-2 rounded-xl bg-muted p-1">
          <button
            type="button"
            className={`rounded-lg py-2 text-sm ${mode === "signin" ? "bg-white shadow-sm" : ""}`}
            onClick={() => setMode("signin")}
          >
            ورود
          </button>
          <button
            type="button"
            className={`rounded-lg py-2 text-sm ${mode === "signup" ? "bg-white shadow-sm" : ""}`}
            onClick={() => setMode("signup")}
          >
            ثبت‌نام
          </button>
        </div>

        <form className="mt-6 space-y-4" onSubmit={submit}>
          <div className="space-y-2">
            <Label htmlFor="email">ایمیل</Label>
            <Input id="email" type="email" dir="ltr" value={email} onChange={(e) => setEmail(e.target.value)} required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="password">رمز عبور</Label>
            <Input
              id="password"
              type="password"
              dir="ltr"
              minLength={6}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          {error && <p className="text-sm text-destructive">{error}</p>}
          {message && <p className="text-sm text-emerald-700">{message}</p>}
          <Button type="submit" className="w-full" disabled={busy || !configured}>
            {busy ? "لطفاً صبر کنید..." : mode === "signin" ? "ورود" : "ساخت حساب"}
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          <Link to="/" className="text-primary">
            بازگشت به صفحه معرفی
          </Link>
        </p>
      </div>
    </div>
  );
}
