import type { OrderStatus, PaymentMethod, OrderType } from "./types";

/** تبدیل ارقام لاتین به فارسی */
export function faDigits(input: string | number): string {
  const fa = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
  return String(input).replace(/\d/g, (d) => fa[Number(d)]);
}

/** تبدیل ارقام فارسی به لاتین (برای inputهای شماره‌ای) */
export function enDigits(input: string): string {
  return input
    .replace(/[۰-۹]/g, (d) => String("۰۱۲۳۴۵۶۷۸۹".indexOf(d)))
    .replace(/[٠-٩]/g, (d) => String("٠١٢٣٤٥٦٧٨٩".indexOf(d)));
}

/** قیمت به تومان با جداکننده هزارگان فارسی */
export function toman(n: number): string {
  return faDigits(Math.round(n).toLocaleString("en-US"));
}

/** تاریخ و ساعت شمسی */
export function faDate(iso: string): string {
  try {
    return new Date(iso).toLocaleDateString("fa-IR", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  } catch {
    return "";
  }
}

export function faTime(iso: string): string {
  try {
    return new Date(iso).toLocaleTimeString("fa-IR", {
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch {
    return "";
  }
}

export function faDateTime(iso: string): string {
  return `${faDate(iso)} — ${faTime(iso)}`;
}

/** برچسب وضعیت سفارش */
export const ORDER_STATUS_LABEL: Record<OrderStatus, string> = {
  pending: "در انتظار تایید",
  confirmed: "تایید شده",
  preparing: "در حال آماده‌سازی",
  delivering: "در مسیر ارسال",
  done: "تحویل شده",
  canceled: "لغو شده",
};

/** رنگ تِیلویند برای هر وضعیت */
export const ORDER_STATUS_COLOR: Record<OrderStatus, string> = {
  pending: "bg-amber-100 text-amber-800 border-amber-200",
  confirmed: "bg-blue-100 text-blue-800 border-blue-200",
  preparing: "bg-violet-100 text-violet-800 border-violet-200",
  delivering: "bg-cyan-100 text-cyan-800 border-cyan-200",
  done: "bg-emerald-100 text-emerald-800 border-emerald-200",
  canceled: "bg-rose-100 text-rose-700 border-rose-200",
};

export const PAYMENT_LABEL: Record<PaymentMethod, string> = {
  online: "پرداخت اینترنتی",
  card_transfer: "کارت به کارت",
  cash_on_delivery: "پرداخت در محل",
};

export const ORDER_TYPE_LABEL: Record<OrderType, string> = {
  delivery: "ارسال با پیک",
  pickup: "تحویل حضوری",
};

/** آیا رستوران همین الان باز است؟ */
export function isBranchOpen(open: string, close: string): boolean {
  const now = new Date();
  const [oh, om] = open.split(":").map(Number);
  const [ch, cm] = close.split(":").map(Number);
  const minutes = now.getHours() * 60 + now.getMinutes();
  const openM = oh * 60 + (om || 0);
  const closeM = ch * 60 + (cm || 0);
  return minutes >= openM && minutes <= closeM;
}

/** اعتبارسنجی شماره موبایل ایران */
export function isValidIranMobile(phone: string): boolean {
  return /^09\d{9}$/.test(enDigits(phone).trim());
}

/** نرمال‌سازی شماره موبایل */
export function normalizePhone(phone: string): string {
  return enDigits(phone).replace(/[^\d]/g, "");
}

/** تولید کد کوتاه سفارش مثل RP-1042 */
export function generateOrderCode(): string {
  return `RP-${Math.floor(1000 + Math.random() * 9000)}`;
}

export function uid(prefix: string): string {
  return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`;
}
