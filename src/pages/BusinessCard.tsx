import { useCallback, useEffect, useRef, useState } from "react";
import { QRCodeSVG } from "qrcode.react";
import { toPng } from "html-to-image";
import { toast } from "sonner";
import {
  RefreshCw,
  UserPlus,
  Share2,
  Download,
  Globe,
  Instagram,
  Send,
  MessageCircle,
  MessageSquare,
  BookOpen,
  Plane,
  Flower2,
  Library,
  CalendarRange,
  BarChart3,
  HeartHandshake,
  NotebookPen,
  Sparkles,
  ScanLine,
  Wifi,
  Loader2,
} from "lucide-react";
import drImage from "@/assets/dr-javad-mirbluki.jpg";
import persianPattern from "@/assets/persian-pattern-bg.jpg";

/* ------------------------------------------------------------------ */
/*  Brand data                                                         */
/* ------------------------------------------------------------------ */
const PROFILE = {
  nameFa: "جواد میربلوکی",
  nameEn: "JAVAD MIRBOLOUKI",
  roleFa: "روانشناس · زوج‌درمانگر · مشاور تخصصی روابط",
  roleEn: "PSYCHOLOGIST · COUPLES THERAPIST",
  quoteFa:
    "«رابطه‌ها به بن‌بست نمی‌رسند؛ فقط گاهی راهِ دوباره دیدنِ هم را گم می‌کنند.»",
  phone: "+98996797947",
  site: "https://mirbolouki.com",
  ig: "https://instagram.com/javad_mirbolouki",
  igHandle: "javad_mirbolouki",
  bale: "https://ble.ir/mirbolouki",
  rubika: "https://rubika.ir/Mirbolouki_com",
  sms: "sms:+98996797947",
};

const JOMA = {
  nameFa: "جوما",
  nameEn: "JOMA PLANNER",
  tagFa: "پلنر هوشمند روانشناسی",
  mottoFa: "برنامه. اجرا. فهم.",
  mottoEn: "PLAN · DO · UNDERSTAND",
  descFa:
    "پلنر هوشمند خودمدیریتی روانشناختی؛ ثبت فعالیت و حال روزانه و گزارشِ بر پایهٔ دادهٔ واقعی",
  site: "https://joma.mirbolouki.com",
  ig: "https://instagram.com/joma.mirbolouki",
  igHandle: "joma.mirbolouki",
};

/* Ratio of a standard business card (3.5in × 2in) */
const EXPORT_W = 1050;
const EXPORT_H = 600;
const BASE_FS = 16;
const EXPORT_SCALE = EXPORT_W / 640;

const GOLD = "#F2C14E";

/* ------------------------------------------------------------------ */
/*  Small building blocks (em-scaled so faces resize proportionally)   */
/* ------------------------------------------------------------------ */
const stopFlip = (e: React.SyntheticEvent) => e.stopPropagation();

const Chip = ({
  href,
  icon: Icon,
  children,
  ltr = false,
}: {
  href: string;
  icon: React.ElementType;
  children: React.ReactNode;
  ltr?: boolean;
}) => (
  <a
    href={href}
    target={href.startsWith("http") ? "_blank" : undefined}
    rel="noreferrer"
    onClick={stopFlip}
    className="flex items-center gap-[0.4em] rounded-full border border-white/15 bg-white/10 px-[0.75em] py-[0.34em] text-[0.62em] font-medium text-white/90 transition-colors hover:bg-white/25 hover:text-white"
  >
    <Icon className="h-[1.15em] w-[1.15em] shrink-0 text-[#F7CE5F]" strokeWidth={2.2} />
    <span dir={ltr ? "ltr" : undefined}>{children}</span>
  </a>
);

const Badge = ({
  icon: Icon,
  children,
}: {
  icon: React.ElementType;
  children: React.ReactNode;
}) => (
  <span className="flex items-center gap-[0.4em] rounded-full border border-[#F2C14E]/35 bg-[#F2C14E]/10 px-[0.7em] py-[0.3em] text-[0.6em] font-medium text-[#FBE3A6]">
    <Icon className="h-[1.2em] w-[1.2em] shrink-0" strokeWidth={2.2} />
    {children}
  </span>
);

const QrTile = ({ value, caption, scale }: { value: string; caption: string; scale: number }) => (
  <div
    onClick={stopFlip}
    className="flex flex-col items-center gap-[0.35em] rounded-[0.9em] bg-white p-[0.55em] shadow-[0_10px_30px_-10px_rgba(0,0,0,0.55)]"
  >
    <QRCodeSVG value={value} size={Math.round(74 * scale)} fgColor="#0B2E59" level="M" includeMargin={false} />
    <span dir="ltr" className="text-[0.55em] font-bold tracking-[0.06em] text-[#0B2E59]/75">
      {caption}
    </span>
  </div>
);

/* ------------------------------------------------------------------ */
/*  Face contents — pure em-based sizing so they scale with fontSize   */
/* ------------------------------------------------------------------ */
const FrontFaceContent = ({ scale }: { scale: number }) => (
  <div
    className="relative h-full w-full overflow-hidden text-white"
    style={{
      background:
        "linear-gradient(135deg, hsl(216 72% 15%) 0%, hsl(210 100% 37%) 52%, hsl(190 95% 37%) 100%)",
    }}
  >
    {/* Persian pattern + glow décor */}
    <div
      className="absolute inset-0 opacity-[0.05]"
      style={{ backgroundImage: `url(${persianPattern})`, backgroundSize: "cover", backgroundPosition: "center" }}
    />
    <div className="absolute -left-[7em] -top-[7em] h-[17em] w-[17em] rounded-full bg-[radial-gradient(circle,rgba(242,193,78,0.35),transparent_70%)] blur-2xl" />
    <div className="absolute -bottom-[8em] -right-[5em] h-[16em] w-[16em] rounded-full bg-[radial-gradient(circle,rgba(56,224,210,0.28),transparent_70%)] blur-2xl" />
    {/* Giant watermark */}
    <div
      dir="ltr"
      className="pointer-events-none select-none absolute -bottom-[0.42em] left-[0.15em] text-[5.4em] font-black leading-none tracking-[-0.02em] text-white/[0.045]"
    >
      MIRBOLOUKI
    </div>
    {/* Hairline inner frame + gold corners */}
    <div className="pointer-events-none absolute inset-[0.55em] rounded-[1.1em] border border-white/15" />
    <div className="pointer-events-none absolute left-[0.55em] top-[0.55em] h-[1.6em] w-[1.6em] rounded-ss-[1.1em] border-s-[0.14em] border-t-[0.14em] border-[#F2C14E]/80" />
    <div className="pointer-events-none absolute bottom-[0.55em] right-[0.55em] h-[1.6em] w-[1.6em] rounded-ee-[1.1em] border-b-[0.14em] border-e-[0.14em] border-[#F2C14E]/80" />

    {/* Content — in RTL the first grid column sits on the right */}
    <div className="relative grid h-full w-full grid-cols-[1fr_auto] items-center gap-[1.3em] px-[1.6em] py-[1.25em]">
      {/* Text column */}
      <div className="flex h-full min-w-0 flex-col justify-between py-[0.15em]">
        <div>
          <div className="flex items-center gap-[0.7em]">
            <span className="h-[0.1em] w-[2.4em] rounded-full bg-gradient-to-l from-transparent to-[#F2C14E]" />
            <span dir="ltr" className="text-[0.68em] font-semibold tracking-[0.22em] text-white/75">
              MIRBOLOUKI.COM
            </span>
          </div>
          <h1 className="mt-[0.35em] text-[1.85em] font-extrabold leading-[1.15]">
            {PROFILE.nameFa}
          </h1>
          <p dir="ltr" className="mt-[0.15em] text-[0.62em] font-bold uppercase tracking-[0.34em] text-white/55">
            {PROFILE.nameEn}
          </p>
          <p className="mt-[0.55em] text-[0.8em] font-semibold text-[#F7CE5F]">{PROFILE.roleFa}</p>
          <div className="mt-[0.7em] flex items-start gap-[0.5em]">
            <Sparkles className="mt-[0.35em] h-[1.05em] w-[1.05em] shrink-0 text-[#F2C14E]" strokeWidth={2.2} />
            <p className="max-w-[30em] text-[0.68em] leading-[1.9] text-white/88">{PROFILE.quoteFa}</p>
          </div>
        </div>

        <div className="mt-[0.6em]">
          <div className="flex flex-wrap gap-[0.45em]">
            <Badge icon={BookOpen}>نویسندهٔ کتاب «عشق و رابطه»</Badge>
            <Badge icon={Plane}>خلبان هواپیمای سبک</Badge>
          </div>
          <div className="mt-[0.7em] flex flex-wrap gap-[0.4em]">
            <Chip href={PROFILE.site} icon={Globe} ltr>Mirbolouki.com</Chip>
            <Chip href={PROFILE.ig} icon={Instagram} ltr>@{PROFILE.igHandle}</Chip>
            <Chip href={PROFILE.bale} icon={Send}>بله</Chip>
            <Chip href={PROFILE.rubika} icon={MessageCircle}>روبیکا</Chip>
            <Chip href={PROFILE.sms} icon={MessageSquare}>پیامک</Chip>
          </div>
        </div>
      </div>

      {/* Photo + QR column */}
      <div className="flex flex-col items-center gap-[0.9em]">
        <div className="relative">
          <div className="rounded-full bg-[conic-gradient(from_140deg,#F2C14E,rgba(255,255,255,0.35),#3fd8d2,#F2C14E)] p-[0.26em]">
            <img
              src={drImage}
              alt="جواد میربلوکی"
              className="h-[7em] w-[7em] rounded-full border-[0.16em] border-white/70 object-cover"
            />
          </div>
          <div
            className="absolute -bottom-[0.35em] -left-[0.35em] rounded-full bg-[#F2C14E] p-[0.38em] text-[#08203F] shadow-lg"
            title="قابل اتصال به NFC"
          >
            <Wifi className="h-[1em] w-[1em] rotate-90" strokeWidth={2.6} />
          </div>
        </div>
        <QrTile value={PROFILE.site} caption="mirbolouki.com" scale={scale} />
      </div>
    </div>
  </div>
);

const BackFaceContent = ({ scale }: { scale: number }) => (
  <div
    className="relative h-full w-full overflow-hidden text-white"
    style={{
      background:
        "linear-gradient(215deg, hsl(216 70% 14%) 0%, hsl(196 90% 32%) 50%, hsl(170 75% 30%) 100%)",
    }}
  >
    <div
      className="absolute inset-0 opacity-[0.05]"
      style={{ backgroundImage: `url(${persianPattern})`, backgroundSize: "cover", backgroundPosition: "center" }}
    />
    <div className="absolute -right-[7em] -top-[7em] h-[17em] w-[17em] rounded-full bg-[radial-gradient(circle,rgba(63,216,210,0.34),transparent_70%)] blur-2xl" />
    <div className="absolute -bottom-[8em] -left-[5em] h-[16em] w-[16em] rounded-full bg-[radial-gradient(circle,rgba(242,193,78,0.28),transparent_70%)] blur-2xl" />
    <div
      dir="ltr"
      className="pointer-events-none select-none absolute -bottom-[0.35em] right-[0.15em] text-[6.2em] font-black leading-none tracking-[0.04em] text-white/[0.05]"
    >
      JOMA
    </div>
    <div className="pointer-events-none absolute inset-[0.55em] rounded-[1.1em] border border-white/15" />
    <div className="pointer-events-none absolute right-[0.55em] top-[0.55em] h-[1.6em] w-[1.6em] rounded-se-[1.1em] border-e-[0.14em] border-t-[0.14em] border-[#3fd8d2]/80" />
    <div className="pointer-events-none absolute bottom-[0.55em] left-[0.55em] h-[1.6em] w-[1.6em] rounded-es-[1.1em] border-b-[0.14em] border-s-[0.14em] border-[#3fd8d2]/80" />

    <div className="relative grid h-full w-full grid-cols-[1fr_auto] items-center gap-[1.3em] px-[1.6em] py-[1.25em]">
      {/* Text column */}
      <div className="flex h-full min-w-0 flex-col justify-between py-[0.15em]">
        <div>
          <div className="flex items-center gap-[0.7em]">
            <span className="h-[0.1em] w-[2.4em] rounded-full bg-gradient-to-l from-transparent to-[#3fd8d2]" />
            <span dir="ltr" className="text-[0.66em] font-semibold tracking-[0.18em] text-white/75">
              JOMA.MIRBOLOUKI.COM
            </span>
          </div>
          <div className="mt-[0.15em] flex items-baseline gap-[0.6em]">
            <h2 className="text-[1.9em] font-black leading-[1.1]">{JOMA.nameFa}</h2>
            <span dir="ltr" className="text-[0.6em] font-bold uppercase tracking-[0.4em] text-white/50">
              JOMA
            </span>
          </div>
          <p className="mt-[0.15em] text-[0.8em] font-semibold text-[#8FF0E6]">
            {JOMA.tagFa} <span className="mx-[0.3em] text-white/40">|</span> {JOMA.mottoFa}
          </p>
          <p dir="ltr" className="mt-[0.2em] text-[0.58em] font-semibold uppercase tracking-[0.3em] text-white/45">
            {JOMA.mottoEn}
          </p>
          <p className="mt-[0.55em] max-w-[34em] text-[0.64em] leading-[1.9] text-white/85">{JOMA.descFa}</p>
        </div>

        <div className="mt-[0.5em]">
          <div className="grid grid-cols-2 gap-[0.4em]">
            {[
              { icon: Flower2, label: "ثبت حال روزانه با استیکر" },
              { icon: Library, label: "کتابخانهٔ ۱۰۷+ تمرین آماده" },
              { icon: CalendarRange, label: "دوره‌های شمسی مستقل" },
              { icon: BarChart3, label: "گزارش از دادهٔ واقعی" },
            ].map((f) => (
              <span
                key={f.label}
                className="flex items-center gap-[0.45em] rounded-[0.6em] border border-white/12 bg-white/[0.08] px-[0.6em] py-[0.4em] text-[0.57em] font-medium text-white/90"
              >
                <f.icon className="h-[1.25em] w-[1.25em] shrink-0 text-[#8FF0E6]" strokeWidth={2.2} />
                {f.label}
              </span>
            ))}
          </div>
          <div className="mt-[0.5em] flex flex-wrap gap-[0.4em]">
            <span className="flex items-center gap-[0.4em] rounded-full border border-[#F2C14E]/40 bg-[#F2C14E]/10 px-[0.7em] py-[0.32em] text-[0.58em] font-semibold text-[#FBE3A6]">
              <HeartHandshake className="h-[1.2em] w-[1.2em]" strokeWidth={2.2} />
              زوج‌درمانی · ۳۵ تمرین تخصصی
            </span>
            <span className="flex items-center gap-[0.4em] rounded-full border border-[#F2C14E]/40 bg-[#F2C14E]/10 px-[0.7em] py-[0.32em] text-[0.58em] font-semibold text-[#FBE3A6]">
              <NotebookPen className="h-[1.2em] w-[1.2em]" strokeWidth={2.2} />
              طرحواره‌درمانی · ۲۷ تمرین تخصصی
            </span>
          </div>
        </div>
      </div>

      {/* QR + links column */}
      <div className="flex flex-col items-center gap-[0.7em]">
        <QrTile value={JOMA.site} caption="joma.mirbolouki.com" scale={scale} />
        <Chip href={JOMA.ig} icon={Instagram} ltr>@{JOMA.igHandle}</Chip>
        <span className="text-[0.52em] font-medium text-white/50">از مجموعهٔ Mirbolouki.com</span>
      </div>
    </div>
  </div>
);

/* ------------------------------------------------------------------ */
/*  Page                                                               */
/* ------------------------------------------------------------------ */
const BusinessCard = () => {
  const [flipped, setFlipped] = useState(false);
  const [hasFlipped, setHasFlipped] = useState(false);
  const [exporting, setExporting] = useState<"front" | "back" | null>(null);

  const sceneRef = useRef<HTMLDivElement>(null);
  const cardRef = useRef<HTMLDivElement>(null);
  const frontExportRef = useRef<HTMLDivElement>(null);
  const backExportRef = useRef<HTMLDivElement>(null);

  const flippedRef = useRef(false);
  const target = useRef({ rx: 0, ry: 0 });
  const current = useRef({ rx: 0, ry: 0 });

  useEffect(() => {
    document.title = "کارت ویزیت دیجیتال | جواد میربلوکی × جوما";
  }, []);

  /* Buttery physics loop — mutates the DOM node directly (no re-render) */
  useEffect(() => {
    let raf = 0;
    const tick = () => {
      current.current.rx += (target.current.rx - current.current.rx) * 0.11;
      current.current.ry += (target.current.ry - current.current.ry) * 0.11;
      const flipDeg = flippedRef.current ? 180 : 0;
      if (cardRef.current) {
        cardRef.current.style.transform = `rotateY(${flipDeg + current.current.ry}deg) rotateX(${current.current.rx}deg)`;
      }
      raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, []);

  const handlePointerMove = useCallback((e: React.PointerEvent) => {
    const rect = sceneRef.current?.getBoundingClientRect();
    if (!rect) return;
    const px = (e.clientX - rect.left) / rect.width - 0.5;
    const py = (e.clientY - rect.top) / rect.height - 0.5;
    target.current = { rx: -py * 9, ry: px * 11 };
    cardRef.current?.style.setProperty("--mx", `${(px + 0.5) * 100}%`);
    cardRef.current?.style.setProperty("--my", `${(py + 0.5) * 100}%`);
  }, []);

  const resetTilt = useCallback(() => {
    target.current = { rx: 0, ry: 0 };
  }, []);

  const flip = useCallback(() => {
    setFlipped((f) => {
      flippedRef.current = !f;
      return !f;
    });
    setHasFlipped(true);
    if ("vibrate" in navigator) navigator.vibrate?.(15);
  }, []);

  /* ---------- actions ---------- */
  const saveContact = useCallback(() => {
    const vcf = [
      "BEGIN:VCARD",
      "VERSION:3.0",
      "N:میربلوکی;جواد;;;",
      "FN:جواد میربلوکی | Javad Mirbolouki",
      "TITLE:روانشناس، زوج‌درمانگر و مشاور تخصصی روابط",
      "ORG:Mirbolouki.com ; Joma Planner",
      "TEL;TYPE=CELL,VOICE:+98996797947",
      "URL;TYPE=WORK:https://mirbolouki.com",
      "URL:https://joma.mirbolouki.com",
      "item1.URL:https://instagram.com/javad_mirbolouki",
      "item1.X-ABLABEL:Instagram",
      "item2.URL:https://instagram.com/joma.mirbolouki",
      "item2.X-ABLABEL:Instagram Joma",
      "NOTE:پلنر هوشمند روانشناسی «جوما» — https://joma.mirbolouki.com",
      "END:VCARD",
    ].join("\r\n");
    const blob = new Blob([vcf], { type: "text/vcard;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "Javad-Mirbolouki.vcf";
    a.click();
    URL.revokeObjectURL(url);
    toast.success("مخاطب ذخیره شد — فایل vCard دانلود شد");
  }, []);

  const shareCard = useCallback(async () => {
    const url = window.location.href;
    const payload = {
      title: "کارت ویزیت دیجیتال جواد میربلوکی",
      text: "کارت ویزیت دیجیتال جواد میربلوکی و پلنر جوما",
      url,
    };
    try {
      if (navigator.share) {
        await navigator.share(payload);
      } else {
        await navigator.clipboard.writeText(url);
        toast.success("لینک کارت کپی شد");
      }
    } catch {
      /* user dismissed */
    }
  }, []);

  const exportFace = useCallback(async (side: "front" | "back") => {
    const node = side === "front" ? frontExportRef.current : backExportRef.current;
    if (!node || exporting) return;
    setExporting(side);
    try {
      await document.fonts.ready;
      const dataUrl = await toPng(node, { pixelRatio: 2, cacheBust: true });
      const a = document.createElement("a");
      a.download = side === "front" ? "mirbolouki-card-front.png" : "joma-card-back.png";
      a.href = dataUrl;
      a.click();
      toast.success(side === "front" ? "تصویر روی کارت ذخیره شد" : "تصویر پشت کارت ذخیره شد");
    } catch {
      toast.error("خطا در ساخت تصویر — دوباره تلاش کنید");
    } finally {
      setExporting(null);
    }
  }, [exporting]);

  const dockBtn =
    "flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white/90 backdrop-blur-md transition-all hover:bg-white/20 hover:text-white active:scale-95";

  return (
    <div className="relative min-h-screen overflow-hidden bg-[#050e1d]" dir="rtl">
      {/* Aurora background */}
      <div className="pointer-events-none absolute inset-0">
        <div className="absolute -top-32 right-[-10%] h-[34rem] w-[34rem] rounded-full bg-[#1673e8]/25 blur-3xl animate-card-drift-a" />
        <div className="absolute bottom-[-15%] left-[-8%] h-[30rem] w-[30rem] rounded-full bg-[#0fd0c0]/20 blur-3xl animate-card-drift-b" />
        <div className="absolute left-1/2 top-1/3 h-[26rem] w-[26rem] -translate-x-1/2 rounded-full bg-[#f2c14e]/10 blur-3xl animate-card-drift-c" />
        <div
          className="absolute inset-0 opacity-[0.05]"
          style={{ backgroundImage: `url(${persianPattern})`, backgroundSize: "48rem", backgroundPosition: "center" }}
        />
      </div>

      <main className="relative z-10 mx-auto flex min-h-screen w-full max-w-3xl flex-col items-center justify-center px-4 py-12">
        {/* Masthead */}
        <header className="mb-8 text-center animate-fade-in-up">
          <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-medium tracking-wide text-white/70">
            <Sparkles className="h-3.5 w-3.5 text-[#F2C14E]" />
            کارت ویزیت دیجیتال تعاملی
            <span dir="ltr" className="text-white/40">DIGITAL CARD</span>
          </div>
          <h1 className="text-2xl font-extrabold text-white md:text-3xl">
            جواد میربلوکی <span className="mx-1 text-[#F2C14E]">×</span> جوما
          </h1>
        </header>

        {/* Flip hint */}
        <div
          className={`pointer-events-none mb-4 flex items-center gap-2 rounded-full bg-[#F2C14E] px-4 py-1.5 text-xs font-bold text-[#08203f] shadow-lg transition-opacity duration-500 animate-float-hint ${
            hasFlipped ? "opacity-0" : "opacity-100"
          }`}
        >
          <ScanLine className="h-4 w-4" />
          برای دیدن پشت کارت، روی آن ضربه بزنید
        </div>

        {/* 3-D card */}
        <div
          ref={sceneRef}
          className="w-full max-w-[40rem] animate-card-enter [perspective:1800px]"
          onPointerMove={handlePointerMove}
          onPointerLeave={resetTilt}
        >
          <div
            ref={cardRef}
            role="button"
            tabIndex={0}
            aria-label="کارت ویزیت — برای چرخش کلیک کنید"
            onClick={flip}
            onKeyDown={(e) => {
              if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                flip();
              }
            }}
            className="relative aspect-[7/4] w-full cursor-pointer select-none [transform-style:preserve-3d]"
            style={{ fontSize: "clamp(10.5px, 2.45vw, 16px)" }}
          >
            {/* specular sheen follows the pointer */}
            <div
              className="pointer-events-none absolute inset-0 z-30 rounded-[1.6em]"
              style={{
                background:
                  "radial-gradient(38em circle at var(--mx, 50%) var(--my, 50%), rgba(255,255,255,0.14), transparent 55%)",
              }}
            />
            <div className="absolute inset-0 rounded-[1.6em] shadow-[0_45px_90px_-25px_rgba(0,0,0,0.75)] [backface-visibility:hidden]">
              <FrontFaceContent scale={1} />
            </div>
            <div className="absolute inset-0 rounded-[1.6em] shadow-[0_45px_90px_-25px_rgba(0,0,0,0.75)] [backface-visibility:hidden] [transform:rotateY(180deg)]">
              <BackFaceContent scale={1} />
            </div>
          </div>
        </div>

        {/* Action dock */}
        <div className="mt-9 flex flex-wrap items-center justify-center gap-2.5 rounded-2xl border border-white/12 bg-white/[0.06] p-3 backdrop-blur-xl animate-fade-in-up">
          <button onClick={flip} className={`${dockBtn} border-[#F2C14E]/40 bg-[#F2C14E]/15 text-[#FBE3A6] hover:bg-[#F2C14E]/25`}>
            <RefreshCw className="h-4 w-4" />
            برگرداندن کارت
          </button>
          <button onClick={saveContact} className={dockBtn}>
            <UserPlus className="h-4 w-4 text-[#8FF0E6]" />
            ذخیره مخاطب
          </button>
          <button onClick={shareCard} className={dockBtn}>
            <Share2 className="h-4 w-4 text-[#8FF0E6]" />
            اشتراک‌گذاری
          </button>
          <button onClick={() => exportFace("front")} disabled={exporting !== null} className={dockBtn}>
            {exporting === "front" ? <Loader2 className="h-4 w-4 animate-spin" /> : <Download className="h-4 w-4 text-[#8FF0E6]" />}
            رو (PNG)
          </button>
          <button onClick={() => exportFace("back")} disabled={exporting !== null} className={dockBtn}>
            {exporting === "back" ? <Loader2 className="h-4 w-4 animate-spin" /> : <Download className="h-4 w-4 text-[#8FF0E6]" />}
            پشت (PNG)
          </button>
        </div>

        {/* Direct channels */}
        <nav className="mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm animate-fade-in-up">
          {[
            { href: PROFILE.bale, label: "بله" },
            { href: PROFILE.rubika, label: "روبیکا" },
            { href: PROFILE.sms, label: "پیامک" },
            { href: PROFILE.ig, label: "اینستاگرام جواد" },
            { href: JOMA.ig, label: "اینستاگرام جوما" },
            { href: PROFILE.site, label: "Mirbolouki.com", ltr: true },
            { href: JOMA.site, label: "joma.mirbolouki.com", ltr: true },
          ].map((l) => (
            <a
              key={l.href}
              href={l.href}
              target={l.href.startsWith("http") ? "_blank" : undefined}
              rel="noreferrer"
              dir={l.ltr ? "ltr" : undefined}
              className="text-white/60 underline-offset-4 transition-colors hover:text-[#F2C14E] hover:underline"
            >
              {l.label}
            </a>
          ))}
        </nav>

        {/* NFC hint */}
        <p className="mt-7 flex max-w-md items-center justify-center gap-2 text-center text-xs leading-6 text-white/40 animate-fade-in-up">
          <Wifi className="h-4 w-4 shrink-0 rotate-90" />
          این صفحه آمادهٔ اتصال به کارت NFC است؛ آدرس همین صفحه را روی تگ بنویسید تا با یک لمس باز شود.
        </p>
      </main>

      {/* Off-screen, flat, print-quality export stage (1050×600 = 3.5"×2") */}
      <div aria-hidden className="pointer-events-none fixed left-[-10000px] top-0">
        <div
          ref={frontExportRef}
          className="overflow-hidden rounded-[3rem]"
          style={{ width: EXPORT_W, height: EXPORT_H, fontSize: BASE_FS * EXPORT_SCALE }}
        >
          <FrontFaceContent scale={EXPORT_SCALE} />
        </div>
        <div
          ref={backExportRef}
          className="overflow-hidden rounded-[3rem]"
          style={{ width: EXPORT_W, height: EXPORT_H, fontSize: BASE_FS * EXPORT_SCALE }}
        >
          <BackFaceContent scale={EXPORT_SCALE} />
        </div>
      </div>
    </div>
  );
};

export default BusinessCard;
