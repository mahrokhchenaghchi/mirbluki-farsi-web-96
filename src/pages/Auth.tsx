import { useEffect, useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Logo } from "@/components/joma/Logo";
import { useAuth } from "@/hooks/useAuth";

export default function AuthPage() {
  const { user, signIn, signUp, resetPassword } = useAuth();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const initial = params.get("mode") === "signup" ? "signup" : params.get("mode") === "forgot" ? "forgot" : "signin";
  const [mode, setMode] = useState<"signin" | "signup" | "forgot">(initial);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const [form, setForm] = useState({
    fullName: "",
    username: "",
    phone: "",
    email: "",
    job: "",
    identifier: "",
    password: "",
    confirmPassword: "",
  });

  useEffect(() => {
    if (user) navigate("/mood", { replace: true });
  }, [navigate, user]);

  const set = (key: keyof typeof form) => (event: React.ChangeEvent<HTMLInputElement>) =>
    setForm((current) => ({ ...current, [key]: event.target.value }));

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
      <div className="joma-card w-full max-w-lg p-8">
        <Logo size={64} />
        <h1 className="mt-6 text-3xl font-black">
          {mode === "signin" ? "ورود به جوما" : mode === "signup" ? "ساخت حساب جوما" : "بازیابی رمز عبور"}
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">حالت آزمایشی محلی — داده در همین مرورگر می‌ماند.</p>

        <div className="mt-6 grid grid-cols-3 rounded-2xl bg-muted p-1 text-sm">
          <button type="button" className={`rounded-xl py-2 ${mode === "signin" ? "bg-white shadow" : ""}`} onClick={() => setMode("signin")}>
            ورود
          </button>
          <button type="button" className={`rounded-xl py-2 ${mode === "signup" ? "bg-white shadow" : ""}`} onClick={() => setMode("signup")}>
            ثبت‌نام
          </button>
          <button type="button" className={`rounded-xl py-2 ${mode === "forgot" ? "bg-white shadow" : ""}`} onClick={() => setMode("forgot")}>
            فراموشی
          </button>
        </div>

        <form className="mt-6 space-y-4" onSubmit={submit}>
          {mode === "signup" && (
            <>
              <Field label="نام و نام خانوادگی" value={form.fullName} onChange={set("fullName")} />
              <Field label="نام کاربری" value={form.username} onChange={set("username")} dir="ltr" />
              <Field label="شماره موبایل" value={form.phone} onChange={set("phone")} dir="ltr" placeholder="09123456789" />
              <Field label="ایمیل" value={form.email} onChange={set("email")} dir="ltr" type="email" />
              <Field label="شغل" value={form.job} onChange={set("job")} />
            </>
          )}
          {mode !== "signup" && (
            <Field label="نام کاربری یا ایمیل" value={form.identifier} onChange={set("identifier")} dir="ltr" />
          )}
          <Field label={mode === "forgot" ? "رمز عبور جدید" : "رمز عبور"} value={form.password} onChange={set("password")} type="password" />
          {(mode === "signup" || mode === "forgot") && (
            <Field label="تکرار رمز عبور" value={form.confirmPassword} onChange={set("confirmPassword")} type="password" />
          )}
          {error && <p className="text-sm text-destructive">{error}</p>}
          {message && <p className="text-sm text-emerald-700">{message}</p>}
          <Button type="submit" className="w-full" disabled={busy}>
            {busy ? "لطفاً صبر کنید..." : mode === "signin" ? "ورود" : mode === "signup" ? "ساخت حساب" : "ذخیره رمز جدید"}
          </Button>
        </form>
        <p className="mt-6 text-center text-sm">
          <Link to="/" className="text-primary">بازگشت</Link>
        </p>
      </div>
    </div>
  );
}

function Field({
  label,
  ...props
}: React.ComponentProps<typeof Input> & { label: string }) {
  return (
    <div className="space-y-2">
      <Label>{label}</Label>
      <Input {...props} required />
    </div>
  );
}
