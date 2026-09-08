import { useEffect, useRef, useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { Phone, ShieldCheck, Pizza, ArrowRight, Info } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useAuth } from "@/hooks/useAuth";
import { useToast } from "@/hooks/use-toast";
import { isValidIranMobile, normalizePhone } from "@/lib/format";

export default function Auth() {
  const { requestOtp, verifyOtp, user } = useAuth();
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const { toast } = useToast();

  const next = params.get("next") || "/";

  const [step, setStep] = useState<"phone" | "code">("phone");
  const [phone, setPhone] = useState("");
  const [code, setCode] = useState("");
  const [name, setName] = useState("");
  const [loading, setLoading] = useState(false);
  const [resendIn, setResendIn] = useState(0);
  const codeRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (user) navigate(next, { replace: true });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [user]);

  useEffect(() => {
    if (resendIn <= 0) return;
    const t = setTimeout(() => setResendIn((s) => s - 1), 1000);
    return () => clearTimeout(t);
  }, [resendIn]);

  useEffect(() => {
    if (step === "code") codeRef.current?.focus();
  }, [step]);

  const sendCode = async () => {
    if (!isValidIranMobile(phone)) {
      toast({ variant: "destructive", title: "شماره موبایل معتبر نیست", description: "فرمت صحیح: ۰۹۱۲۳۴۵۶۷۸۹" });
      return;
    }
    setLoading(true);
    const res = await requestOtp(phone);
    setLoading(false);
    if (res.error) {
      toast({ variant: "destructive", title: "خطا", description: res.error });
      return;
    }
    setStep("code");
    setResendIn(90);
    if (res.demoCode) {
      toast({
        title: `کد تایید (حالت دمو): ${res.demoCode}`,
        description: "در نسخه متصل به پیامک، این کد به شماره شما SMS می‌شود.",
        duration: 10000,
      });
    } else {
      toast({ title: "کد تایید ارسال شد", description: "کد ۶ رقمی را وارد کنید." });
    }
  };

  const confirm = async () => {
    setLoading(true);
    const res = await verifyOtp(phone, code, name);
    setLoading(false);
    if (res.error) {
      toast({ variant: "destructive", title: "خطا", description: res.error });
      return;
    }
    toast({ title: "خوش آمدید 🎉", description: "ورود شما با موفقیت انجام شد." });
    navigate(next, { replace: true });
  };

  return (
    <div className="container flex items-center justify-center py-14">
      <div className="w-full max-w-md">
        <div className="mb-6 text-center">
          <span className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-primary text-primary-foreground shadow-lg shadow-primary/30">
            <Pizza className="h-8 w-8" />
          </span>
          <h1 className="mt-4 text-2xl font-black">ورود / ثبت‌نام</h1>
          <p className="mt-1.5 text-sm text-muted-foreground">
            {step === "phone"
              ? "با شماره موبایل وارد شوید؛ در کمتر از ۳۰ ثانیه حساب شما آماده است."
              : `کد ۶ رقمی ارسال‌شده به ${phone} را وارد کنید.`}
          </p>
        </div>

        <Card className="border-border/70 shadow-sm">
          <CardHeader className="pb-3">
            <CardTitle className="flex items-center gap-2 text-base">
              {step === "phone" ? (
                <><Phone className="h-4.5 w-4.5 text-primary" /> شماره موبایل</>
              ) : (
                <><ShieldCheck className="h-4.5 w-4.5 text-primary" /> کد تایید</>
              )}
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {step === "phone" ? (
              <>
                <div className="space-y-1.5">
                  <Label htmlFor="phone">شماره موبایل</Label>
                  <Input
                    id="phone"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    onKeyDown={(e) => e.key === "Enter" && sendCode()}
                    placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                    inputMode="tel"
                    dir="ltr"
                    className="h-12 text-center text-lg tracking-widest"
                  />
                </div>
                <Button className="w-full" size="lg" onClick={sendCode} disabled={loading}>
                  {loading ? "در حال ارسال..." : "دریافت کد تایید"}
                </Button>
                <p className="flex items-start gap-2 rounded-xl bg-secondary/60 p-3 text-[11px] leading-5 text-muted-foreground">
                  <Info className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                  با ورود، قوانین و مقررات سایت را می‌پذیرید. شماره شما فقط برای پیگیری سفارش‌ها استفاده می‌شود.
                </p>
              </>
            ) : (
              <>
                <div className="space-y-1.5">
                  <Label htmlFor="code">کد تایید ۶ رقمی</Label>
                  <Input
                    id="code"
                    ref={codeRef}
                    value={code}
                    onChange={(e) => setCode(e.target.value)}
                    onKeyDown={(e) => e.key === "Enter" && confirm()}
                    placeholder="------"
                    inputMode="numeric"
                    dir="ltr"
                    className="h-12 text-center text-lg font-mono tracking-[0.5em]"
                    maxLength={6}
                  />
                </div>
                <div className="space-y-1.5">
                  <Label htmlFor="name">
                    نام و نام خانوادگی <span className="font-normal text-muted-foreground">(برای ثبت سفارش)</span>
                  </Label>
                  <Input
                    id="name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="مثلاً سارا محمدی"
                  />
                </div>
                <Button className="w-full" size="lg" onClick={confirm} disabled={loading}>
                  {loading ? "در حال بررسی..." : "تایید و ورود"}
                </Button>
                <div className="flex items-center justify-between text-xs">
                  <button
                    className="flex items-center gap-1 font-bold text-primary hover:opacity-80"
                    onClick={() => setStep("phone")}
                  >
                    <ArrowRight className="h-3.5 w-3.5" />
                    تغییر شماره
                  </button>
                  {resendIn > 0 ? (
                    <span className="text-muted-foreground">ارسال مجدد تا {resendIn} ثانیه</span>
                  ) : (
                    <button className="font-bold text-primary hover:opacity-80" onClick={sendCode}>
                      ارسال مجدد کد
                    </button>
                  )}
                </div>
              </>
            )}
          </CardContent>
        </Card>

        <p className="mt-5 text-center text-[11px] leading-6 text-muted-foreground">
          💡 حالت دمو: با هر شماره معتبر و کد <span className="font-mono font-bold">123456</span> وارد شوید.
          <br />
          برای ورود به پنل مدیریت از شماره مدیر (پیش‌فرض: <span className="font-mono" dir="ltr">09120000000</span>) استفاده کنید.
        </p>
      </div>
    </div>
  );
}
