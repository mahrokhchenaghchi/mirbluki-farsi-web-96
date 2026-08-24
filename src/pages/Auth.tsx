import { useEffect, useMemo, useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { JOBS } from "@/domain/catalog";
import { isValidEmail, isValidIranMobile, isValidUsername, passwordHints, usernameHints } from "@/domain/validation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Logo } from "@/components/joma/Logo";
import { useAuth } from "@/hooks/useAuth";
import { cn } from "@/lib/utils";

export default function AuthPage() {
  const { user, signIn, signUp, resetPassword, usernameAvailable } = useAuth();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const initial = params.get("mode") === "signup" ? "signup" : params.get("mode") === "forgot" ? "forgot" : "signin";
  const [mode, setMode] = useState<"signin" | "signup" | "forgot">(initial);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const [form, setForm] = useState({
    firstName: "",
    lastName: "",
    username: "",
    phone: "",
    email: "",
    job: "سایر",
    identifier: "",
    password: "",
    confirmPassword: "",
  });

  useEffect(() => {
    if (user) navigate("/mood", { replace: true });
  }, [navigate, user]);

  const set = (key: keyof typeof form) => (event: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) =>
    setForm((current) => ({ ...current, [key]: event.target.value }));

  const nameHints = useMemo(() => usernameHints(form.username), [form.username]);
  const passHints = useMemo(() => passwordHints(form.password, form.confirmPassword), [form.password, form.confirmPassword]);
  const usernameFree = !form.username || (isValidUsername(form.username) && usernameAvailable(form.username));

  const submit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError(null);
    setMessage(null);
    setBusy(true);
    if (mode === "signin") {
      const result = await signIn(form.identifier, form.password);
      if (result.error) setError(result.error);
    } else if (mode === "signup") {
      const result = await signUp(form);
      if (result.error) setError(result.error);
    } else {
      const result = await resetPassword(form.identifier, form.password, form.confirmPassword);
      if (result.error) setError(result.error);
      else {
        setMessage("رمز جدید ذخیره شد. حالا وارد شوید.");
        setMode("signin");
      }
    }
    setBusy(false);
  };

  return (
    <div className="flex min-h-screen items-center justify-center px-4 py-10">
      <div className="joma-card w-full max-w-xl p-8">
        <div className="flex items-center justify-between">
          <Logo size={64} />
          <Link to="/" className="text-sm text-primary">بازگشت به معرفی</Link>
        </div>
        <h1 className="mt-6 text-3xl font-black">
          {mode === "signin" ? "ورود به جوما" : mode === "signup" ? "ساخت حساب جوما" : "بازیابی رمز عبور"}
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">حالت آزمایشی محلی — داده در همین مرورگر می‌ماند.</p>

        <div className="mt-6 grid grid-cols-3 rounded-2xl bg-muted p-1 text-sm">
          {(["signin", "signup", "forgot"] as const).map((item) => (
            <button key={item} type="button" className={`rounded-xl py-2 ${mode === item ? "bg-white shadow" : ""}`} onClick={() => setMode(item)}>
              {item === "signin" ? "ورود" : item === "signup" ? "ثبت‌نام" : "فراموشی"}
            </button>
          ))}
        </div>

        <form className="mt-6 space-y-4" onSubmit={submit}>
          {mode === "signup" && (
            <>
              <div className="grid gap-3 sm:grid-cols-2">
                <Field label="نام" value={form.firstName} onChange={set("firstName")} />
                <Field label="نام خانوادگی" value={form.lastName} onChange={set("lastName")} />
              </div>
              <Field label="نام کاربری" value={form.username} onChange={set("username")} dir="ltr" />
              <HintList
                items={[
                  ...nameHints,
                  { ok: usernameFree, text: usernameFree ? "قابل استفاده است" : "قبلاً استفاده شده" },
                ]}
              />
              <Field label="شماره موبایل" value={form.phone} onChange={set("phone")} dir="ltr" placeholder="09123456789" />
              {form.phone && (
                <p className={cn("text-xs", isValidIranMobile(form.phone) ? "text-emerald-700" : "text-destructive")}>
                  {isValidIranMobile(form.phone) ? "✓ شماره معتبر است" : "✗ قالب صحیح: 09xxxxxxxxx"}
                </p>
              )}
              <Field label="ایمیل" value={form.email} onChange={set("email")} dir="ltr" type="email" />
              {form.email && (
                <p className={cn("text-xs", isValidEmail(form.email) ? "text-emerald-700" : "text-destructive")}>
                  {isValidEmail(form.email) ? "✓ ایمیل معتبر است" : "✗ ایمیل معتبر نیست"}
                </p>
              )}
              <div className="space-y-2">
                <Label>شغل</Label>
                <select className="h-10 w-full rounded-md border bg-background px-3 text-sm" value={form.job} onChange={set("job")}>
                  {JOBS.map((job) => <option key={job}>{job}</option>)}
                </select>
              </div>
            </>
          )}
          {mode !== "signup" && <Field label="نام کاربری یا ایمیل" value={form.identifier} onChange={set("identifier")} dir="ltr" />}
          <Field label={mode === "forgot" ? "رمز عبور جدید" : "رمز عبور"} value={form.password} onChange={set("password")} type="password" />
          {(mode === "signup" || mode === "forgot") && (
            <>
              <Field label="تکرار رمز عبور" value={form.confirmPassword} onChange={set("confirmPassword")} type="password" />
              <HintList items={passHints} />
            </>
          )}
          {error && <p className="text-sm text-destructive">{error}</p>}
          {message && <p className="text-sm text-emerald-700">{message}</p>}
          <Button type="submit" className="w-full" disabled={busy}>
            {busy ? "لطفاً صبر کنید..." : mode === "signin" ? "ورود" : mode === "signup" ? "ساخت حساب" : "ذخیره رمز جدید"}
          </Button>
        </form>
        <p className="mt-6 text-center text-sm">
          <Link to="/about" className="text-primary">درباره جوما</Link>
        </p>
      </div>
    </div>
  );
}

function Field(props: React.ComponentProps<typeof Input> & { label: string }) {
  const { label, ...rest } = props;
  return (
    <div className="space-y-2">
      <Label>{label}</Label>
      <Input {...rest} required />
    </div>
  );
}

function HintList({ items }: { items: Array<{ ok: boolean; text: string }> }) {
  return (
    <ul className="space-y-1 text-xs">
      {items.map((item) => (
        <li key={item.text} className={item.ok ? "text-emerald-700" : "text-muted-foreground"}>
          {item.ok ? "✓" : "✗"} {item.text}
        </li>
      ))}
    </ul>
  );
}
