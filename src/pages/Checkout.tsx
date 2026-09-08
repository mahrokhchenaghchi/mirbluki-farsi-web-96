import { useMemo, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import {
  Bike, Store, ShoppingBag, CreditCard, Landmark, Banknote,
  MapPin, StickyNote, Loader2, AlertCircle, TicketPercent,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Separator } from "@/components/ui/separator";
import { useCart } from "@/store/CartContext";
import { useBranches } from "@/hooks/useData";
import { useAuth } from "@/hooks/useAuth";
import { useToast } from "@/hooks/use-toast";
import { api } from "@/lib/db";
import { isValidIranMobile, normalizePhone, generateOrderCode, uid, toman, faDigits } from "@/lib/format";
import type { Order, OrderType, PaymentMethod } from "@/lib/types";
import { cn } from "@/lib/utils";

const AREAS = [
  "ولیعصر", "پونک", "نارمک", "سعادت‌آباد", "میرداماد", "جردن",
  "شهرک غرب", "نازی‌آباد", "تهرانپارس", "ولنجک", "افسریه", "یوسف‌آباد",
];

export default function Checkout() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const { toast } = useToast();
  const {
    lines, subtotal, discount, payable, coupon,
    branchId, clear,
  } = useCart();
  const { data: branches } = useBranches();

  const [type, setType] = useState<OrderType>("delivery");
  const [name, setName] = useState(user?.name ?? "");
  const [phone, setPhone] = useState(user?.phone ?? "");
  const [area, setArea] = useState("");
  const [address, setAddress] = useState("");
  const [payment, setPayment] = useState<PaymentMethod>("online");
  const [note, setNote] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const branch = branches?.find((b) => b.id === branchId);

  const deliveryFee = useMemo(() => {
    if (type === "pickup") return 0;
    if (!branch) return 0;
    return payable >= branch.free_delivery_over ? 0 : branch.delivery_fee;
  }, [type, branch, payable]);

  const total = payable + deliveryFee;
  const belowMin = branch ? subtotal < branch.min_order : false;

  if (lines.length === 0) {
    return (
      <div className="container flex flex-col items-center gap-4 py-24 text-center">
        <span className="text-6xl">🛒</span>
        <h1 className="text-2xl font-black">سبد خرید شما خالی است</h1>
        <p className="text-sm text-muted-foreground">برای ثبت سفارش ابتدا از منو غذا انتخاب کنید.</p>
        <Button size="lg" className="rounded-full px-8" onClick={() => navigate("/menu")}>رفتن به منو</Button>
      </div>
    );
  }

  const submit = async () => {
    if (!name.trim()) return toast({ variant: "destructive", title: "نام گیرنده را وارد کنید." });
    if (!isValidIranMobile(phone)) return toast({ variant: "destructive", title: "شماره موبایل معتبر نیست." });
    if (type === "delivery" && (!area || address.trim().length < 10)) {
      return toast({ variant: "destructive", title: "آدرس کامل و محله را وارد کنید." });
    }
    if (belowMin) {
      return toast({
        variant: "destructive",
        title: "حداقل مبلغ سفارش رعایت نشده",
        description: `حداقل سفارش این شعبه ${toman(branch!.min_order)} تومان است.`,
      });
    }

    setSubmitting(true);
    const now = new Date().toISOString();
    const order: Order = {
      id: uid("or"),
      code: generateOrderCode(),
      branch_id: branch!.id,
      branch_name: branch!.name,
      type,
      status: "pending",
      customer_name: name.trim(),
      customer_phone: normalizePhone(phone),
      address: type === "delivery" ? `${area ? area + "، " : ""}${address.trim()}` : undefined,
      area: type === "delivery" ? area : undefined,
      items: lines.map((l) => ({
        product_id: l.product_id,
        product_name: l.product_name,
        image: l.image,
        size_name: l.size_name,
        unit_price: l.unit_price,
        qty: l.qty,
        note: l.note,
      })),
      subtotal,
      delivery_fee: deliveryFee,
      discount,
      total,
      coupon_code: coupon?.code,
      payment_method: payment,
      note: note.trim() || undefined,
      created_at: now,
      status_history: [{ status: "pending", at: now }],
      auto_advance: true,
    };

    await api.createOrder(order);
    clear();
    setSubmitting(false);
    toast({ title: "سفارش ثبت شد 🎉", description: `کد سفارش: ${order.code}` });
    navigate(`/track?code=${order.code}&new=1`, { replace: true });
  };

  return (
    <div className="container py-8 pb-28 lg:pb-8">
      <div className="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
        <Link to="/menu" className="hover:text-primary">منو</Link>
        <span>›</span>
        <span className="font-bold text-foreground">تکمیل سفارش</span>
      </div>

      <div className="grid gap-6 lg:grid-cols-5">
        {/* فرم */}
        <div className="space-y-5 lg:col-span-3">
          {/* نوع سفارش */}
          <Card>
            <CardHeader className="pb-3"><CardTitle className="text-base">نحوه دریافت سفارش</CardTitle></CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 gap-3">
                <button
                  onClick={() => setType("delivery")}
                  className={cn(
                    "flex flex-col items-center gap-2 rounded-2xl border-2 p-4 transition",
                    type === "delivery" ? "border-primary bg-primary/5" : "border-border hover:border-primary/40"
                  )}
                >
                  <Bike className={cn("h-7 w-7", type === "delivery" ? "text-primary" : "text-muted-foreground")} />
                  <span className="text-sm font-bold">ارسال با پیک</span>
                  <span className="text-[11px] text-muted-foreground">
                    {deliveryFee === 0 ? "رایگان 🎉" : `${toman(deliveryFee)} تومان`}
                  </span>
                </button>
                <button
                  onClick={() => setType("pickup")}
                  className={cn(
                    "flex flex-col items-center gap-2 rounded-2xl border-2 p-4 transition",
                    type === "pickup" ? "border-primary bg-primary/5" : "border-border hover:border-primary/40"
                  )}
                >
                  <Store className={cn("h-7 w-7", type === "pickup" ? "text-primary" : "text-muted-foreground")} />
                  <span className="text-sm font-bold">تحویل حضوری</span>
                  <span className="text-[11px] text-muted-foreground">بدون هزینه ارسال</span>
                </button>
              </div>
            </CardContent>
          </Card>

          {/* اطلاعات گیرنده */}
          <Card>
            <CardHeader className="pb-3"><CardTitle className="text-base">اطلاعات گیرنده</CardTitle></CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-1.5">
                  <Label>نام و نام خانوادگی</Label>
                  <Input value={name} onChange={(e) => setName(e.target.value)} placeholder="گیرنده سفارش" />
                </div>
                <div className="space-y-1.5">
                  <Label>شماره موبایل</Label>
                  <Input
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                    inputMode="tel"
                    dir="ltr"
                    className="text-left"
                  />
                </div>
              </div>

              {type === "delivery" && (
                <div className="space-y-4">
                  <div className="space-y-1.5">
                    <Label>محله</Label>
                    <Select value={area} onValueChange={setArea} dir="rtl">
                      <SelectTrigger><SelectValue placeholder="محله خود را انتخاب کنید" /></SelectTrigger>
                      <SelectContent>
                        {AREAS.map((a) => (
                          <SelectItem key={a} value={a}>{a}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="space-y-1.5">
                    <Label>آدرس دقیق</Label>
                    <Textarea
                      value={address}
                      onChange={(e) => setAddress(e.target.value)}
                      placeholder="خیابان، کوچه، پلاک، واحد... (برای پیدا کردن راحت‌تر آدرس کامل بنویسید)"
                      className="min-h-24"
                    />
                  </div>
                </div>
              )}

              {type === "pickup" && branch && (
                <p className="flex items-start gap-2 rounded-xl bg-secondary/60 p-3 text-xs leading-6 text-muted-foreground">
                  <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                  سفارش شما در {branch.address} آماده تحویل می‌شود. لطفاً همراه کد سفارش مراجعه کنید.
                </p>
              )}
            </CardContent>
          </Card>

          {/* پرداخت */}
          <Card>
            <CardHeader className="pb-3"><CardTitle className="text-base">روش پرداخت</CardTitle></CardHeader>
            <CardContent>
              <RadioGroup value={payment} onValueChange={(v) => setPayment(v as PaymentMethod)} className="gap-3">
                {[
                  { v: "online" as const, icon: CreditCard, title: "پرداخت اینترنتی", desc: "امن و فوری با تمام کارت‌های شتاب" },
                  { v: "card_transfer" as const, icon: Landmark, title: "کارت به کارت", desc: "شماره کارت پس از ثبت سفارش پیامک می‌شود" },
                  { v: "cash_on_delivery" as const, icon: Banknote, title: "پرداخت در محل", desc: "نقدی یا کارت‌خوان سیار پیک" },
                ].map((m) => (
                  <Label
                    key={m.v}
                    className={cn(
                      "flex cursor-pointer items-center gap-3 rounded-2xl border-2 p-3.5 font-normal transition",
                      payment === m.v ? "border-primary bg-primary/5" : "border-border hover:border-primary/40"
                    )}
                  >
                    <RadioGroupItem value={m.v} />
                    <m.icon className={cn("h-5 w-5", payment === m.v ? "text-primary" : "text-muted-foreground")} />
                    <span className="flex-1">
                      <span className="block text-sm font-bold">{m.title}</span>
                      <span className="block text-[11px] text-muted-foreground">{m.desc}</span>
                    </span>
                  </Label>
                ))}
              </RadioGroup>
            </CardContent>
          </Card>

          {/* یادداشت */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="flex items-center gap-2 text-base">
                <StickyNote className="h-4.5 w-4.5 text-primary" />
                یادداشت سفارش <span className="font-normal text-xs text-muted-foreground">(اختیاری)</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <Textarea
                value={note}
                onChange={(e) => setNote(e.target.value)}
                placeholder="مثلاً: زنگ نزنید، تماس بگیرید — تحویل به نگهبانی"
                className="min-h-20"
                maxLength={200}
              />
            </CardContent>
          </Card>
        </div>

        {/* خلاصه سفارش */}
        <div className="lg:col-span-2">
          <div className="space-y-4 lg:sticky lg:top-32">
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="flex items-center gap-2 text-base">
                  <ShoppingBag className="h-4.5 w-4.5 text-primary" />
                  خلاصه سفارش
                </CardTitle>
                <p className="text-xs text-muted-foreground">{branch?.name}</p>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="max-h-56 space-y-2.5 overflow-y-auto scrollbar-thin pl-2">
                  {lines.map((l, i) => (
                    <div key={i} className="flex items-center justify-between gap-2 text-sm">
                      <div className="flex min-w-0 items-center gap-2">
                        {l.image && <img src={l.image} alt="" className="h-9 w-9 shrink-0 rounded-lg object-cover" loading="lazy" />}
                        <div className="min-w-0">
                          <p className="truncate">{l.product_name} <span className="text-xs text-muted-foreground">×{faDigits(l.qty)}</span></p>
                          <p className="truncate text-[11px] text-muted-foreground">{l.size_name}</p>
                        </div>
                      </div>
                      <span className="shrink-0 text-xs text-muted-foreground">{toman(l.unit_price * l.qty)}</span>
                    </div>
                  ))}
                </div>

                <Separator />

                <div className="space-y-1.5 text-sm">
                  <div className="flex justify-between"><span className="text-muted-foreground">جمع اقلام</span><span>{toman(subtotal)} تومان</span></div>
                  {discount > 0 && (
                    <div className="flex justify-between text-accent">
                      <span className="flex items-center gap-1"><TicketPercent className="h-3.5 w-3.5" /> {coupon?.code}</span>
                      <span>− {toman(discount)} تومان</span>
                    </div>
                  )}
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">هزینه ارسال</span>
                    <span>{deliveryFee === 0 ? <span className="font-bold text-accent">رایگان</span> : `${toman(deliveryFee)} تومان`}</span>
                  </div>
                  <div className="flex justify-between border-t pt-2 text-base font-extrabold">
                    <span>مبلغ نهایی</span>
                    <span className="text-primary">{toman(total)} تومان</span>
                  </div>
                </div>

                {belowMin && (
                  <p className="flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-700">
                    <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
                    حداقل سفارش این شعبه {toman(branch!.min_order)} تومان است؛
                    {" "}<Link to="/menu" className="font-bold underline">بیشتر سفارش بده</Link>.
                  </p>
                )}

                <Button size="lg" className="w-full text-base" onClick={submit} disabled={submitting || belowMin}>
                  {submitting ? (
                    <><Loader2 className="h-5 w-5 ml-2 animate-spin" /> در حال ثبت سفارش...</>
                  ) : (
                    <>ثبت نهایی سفارش — {toman(total)} تومان</>
                  )}
                </Button>
                <p className="text-center text-[10px] leading-4 text-muted-foreground">
                  با ثبت سفارش، قوانین رستوران را می‌پذیرید.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
}
