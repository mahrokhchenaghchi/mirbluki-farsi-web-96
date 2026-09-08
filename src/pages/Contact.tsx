import { useState } from "react";
import { Phone, MapPin, Send, Instagram, Clock, MessageCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";
import { useSettings, useBranches } from "@/hooks/useData";
import { useToast } from "@/hooks/use-toast";
import { faDigits, isValidIranMobile, normalizePhone } from "@/lib/format";

const FAQS = [
  {
    q: "چقدر طول می‌کشد سفارشم برسد؟",
    a: "میانگین زمان تحویل ۳۵ تا ۴۵ دقیقه است و بسته به ترافیک محله متغیر خواهد بود. وضعیت سفارش را می‌توانید لحظه‌ای از صفحه پیگیری سفارش دنبال کنید.",
  },
  {
    q: "هزینه ارسال چقدر است و چه زمانی رایگان می‌شود؟",
    a: "هزینه ارسال بسته به شعبه بین ۳۰ تا ۴۰ هزار تومان است و برای سفارش‌های بالای مبلغ مشخصِ هر شعبه (معمولا ۵۰۰ تا ۷۰۰ هزار تومان) ارسال رایگان محاسبه می‌شود.",
  },
  {
    q: "چه روش‌های پرداختی دارید؟",
    a: "پرداخت آنلاین با تمام کارت‌های شتاب، کارت به کارت و پرداخت در محل (نقدی یا کارت‌خوان سیار پیک).",
  },
  {
    q: "امکان سفارش برای مهمانی و تعداد بالا وجود دارد؟",
    a: "بله! برای سفارش بالای ۱۰ نفر با ما تماس بگیرید تا هم تخفیف حجمی دریافت کنید و هم زمان تحویل دقیق هماهنگ شود.",
  },
  {
    q: "غذاهای گیاهی دارید؟",
    a: "بله، پیتزا سبزیجات، سالاد فصل، نان سیر، سیب‌زمینی و برخی دسرها کاملا گیاهی هستند و در منو با برچسب «گیاهی» مشخص شده‌اند.",
  },
];

export default function Contact() {
  const { data: settings } = useSettings();
  const { data: branches } = useBranches();
  const { toast } = useToast();
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [message, setMessage] = useState("");
  const [sending, setSending] = useState(false);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !message.trim()) {
      toast({ variant: "destructive", title: "لطفاً نام و متن پیام را وارد کنید." });
      return;
    }
    if (!isValidIranMobile(phone)) {
      toast({ variant: "destructive", title: "شماره موبایل معتبر نیست." });
      return;
    }
    setSending(true);
    // شبیه‌سازی ارسال — در نسخه متصل به بک‌اند اینجا درخواست POST ارسال می‌شود
    await new Promise((r) => setTimeout(r, 800));
    setSending(false);
    toast({
      title: "پیام شما ثبت شد ✓",
      description: "کارشناسان ما حداکثر تا ۲۴ ساعت آینده پاسخ می‌دهند.",
    });
    setName("");
    setPhone("");
    setMessage("");
  };

  return (
    <div className="container py-12">
      <div className="mx-auto max-w-xl text-center">
        <p className="text-xs font-bold uppercase tracking-wider text-primary">تماس با ما</p>
        <h1 className="mt-2 text-3xl font-black">حواستان به حرف زدن با ما هست؟</h1>
        <p className="persian-text mt-3 text-sm text-muted-foreground">
          پیشنهاد، انتقاد یا سفارش خاص؟ از هر راهی که راحت‌ترید در دسترس هستیم.
        </p>
      </div>

      <div className="mt-10 grid gap-6 lg:grid-cols-3">
        {/* اطلاعات تماس */}
        <div className="space-y-4">
          <Card>
            <CardContent className="flex items-start gap-3 p-5">
              <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <Phone className="h-5 w-5" />
              </span>
              <div>
                <h3 className="font-bold">تلفن سفارش</h3>
                <a href={`tel:${settings?.phone ?? ""}`} className="mt-1 block text-sm text-primary hover:underline" dir="ltr">
                  {settings?.phone}
                </a>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="flex items-start gap-3 p-5">
              <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <MapPin className="h-5 w-5" />
              </span>
              <div>
                <h3 className="font-bold">آدرس مرکزی</h3>
                <p className="mt-1 text-sm leading-6 text-muted-foreground">{settings?.address}</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="flex items-start gap-3 p-5">
              <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <Clock className="h-5 w-5" />
              </span>
              <div>
                <h3 className="font-bold">ساعات پاسخگویی</h3>
                <p className="mt-1 text-sm text-muted-foreground">
                  همه‌روزه {faDigits("12:00")} تا {faDigits("23:00")}
                </p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-5">
              <h3 className="mb-3 font-bold">ما را دنبال کنید</h3>
              <div className="flex gap-2">
                <a
                  href={`https://instagram.com/${settings?.instagram ?? ""}`}
                  target="_blank"
                  rel="noreferrer"
                  className="flex h-10 items-center gap-2 rounded-full border px-4 text-sm transition hover:border-primary hover:text-primary"
                >
                  <Instagram className="h-4 w-4" /> اینستاگرام
                </a>
                <a
                  href={`https://t.me/${settings?.telegram ?? ""}`}
                  target="_blank"
                  rel="noreferrer"
                  className="flex h-10 items-center gap-2 rounded-full border px-4 text-sm transition hover:border-primary hover:text-primary"
                >
                  <Send className="h-4 w-4" /> تلگرام
                </a>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* فرم */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MessageCircle className="h-5 w-5 text-primary" />
              فرم تماس و انتقادات
            </CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-1.5">
                  <Label htmlFor="c-name">نام و نام خانوادگی</Label>
                  <Input id="c-name" value={name} onChange={(e) => setName(e.target.value)} placeholder="مثلاً سارا محمدی" />
                </div>
                <div className="space-y-1.5">
                  <Label htmlFor="c-phone">شماره موبایل</Label>
                  <Input
                    id="c-phone"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                    inputMode="tel"
                    dir="ltr"
                    className="text-left"
                  />
                </div>
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="c-msg">پیام شما</Label>
                <Textarea
                  id="c-msg"
                  value={message}
                  onChange={(e) => setMessage(e.target.value)}
                  placeholder="بنویسید چه در ذهن دارید..."
                  className="min-h-32"
                />
              </div>
              <Button type="submit" disabled={sending} className="w-full sm:w-auto">
                <Send className="h-4 w-4 ml-1.5" />
                {sending ? "در حال ارسال..." : "ارسال پیام"}
              </Button>
            </form>
          </CardContent>
        </Card>
      </div>

      {/* سوالات متداول */}
      <section className="mx-auto mt-16 max-w-3xl">
        <h2 className="text-center text-2xl font-black">سوالات متداول</h2>
        <Accordion type="single" collapsible className="mt-6">
          {FAQS.map((f, i) => (
            <AccordionItem key={i} value={`f-${i}`}>
              <AccordionTrigger className="text-right text-sm font-bold hover:no-underline">
                {f.q}
              </AccordionTrigger>
              <AccordionContent className="persian-text text-sm leading-7 text-muted-foreground">
                {f.a}
              </AccordionContent>
            </AccordionItem>
          ))}
        </Accordion>
      </section>
    </div>
  );
}
