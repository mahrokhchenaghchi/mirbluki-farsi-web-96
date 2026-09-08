/**
 * لایه دسترسی به داده.
 * ------------------------------------------------------------
 * دو حالت دارد:
 *  1) حالت دمو (پیش‌فرض): داده‌ها در localStorage ذخیره می‌شوند و
 *     کاملاً با پنل مدیریت قابل تغییر هستند. برای نمایش و تست فوری.
 *  2) حالت Supabase: اگر متغیرهای محیطی تنظیم شده باشند، همان
 *     توابع به جداول واقعی وصل می‌شوند (اسکیما در migrationها).
 *
 * همه کامپوننت‌ها فقط از طریق همین ماژول با داده کار می‌کنند؛
 * بنابراین جابه‌جایی بین دمو و بک‌اند واقعی شفاف است.
 */

import { seedData } from "@/data/seed";
import type {
  AppData,
  Branch,
  Coupon,
  Customer,
  Order,
  OrderStatus,
  Product,
  Review,
  StoreSettings,
} from "./types";
import { isSupabaseConfigured, supabase } from "@/integrations/supabase/client";
import { uid } from "./format";

const LS_KEY = "royal-pizza-db-v1";

let cache: AppData | null = null;

function load(): AppData {
  if (cache) return cache;
  try {
    const raw = localStorage.getItem(LS_KEY);
    if (raw) {
      const parsed = JSON.parse(raw) as AppData;
      // ادغام با seed برای مقاوم بودن در برابر تغییر نسخه
      cache = { ...seedData, ...parsed, settings: { ...seedData.settings, ...parsed.settings } };
      return cache;
    }
  } catch {
    /* ignore */
  }
  cache = JSON.parse(JSON.stringify(seedData));
  persist();
  return cache;
}

function persist() {
  if (!cache) return;
  try {
    localStorage.setItem(LS_KEY, JSON.stringify(cache));
  } catch {
    /* storage full — ignore */
  }
}

export function resetDemoData(): void {
  cache = JSON.parse(JSON.stringify(seedData));
  persist();
}

export function exportData(): string {
  return JSON.stringify(load(), null, 2);
}

/* ------------------------------------------------------------------ */
/*  پیشرفت خودکار وضعیت سفارش در حالت دمو                              */
/* ------------------------------------------------------------------ */

const ADVANCE_STEPS: Array<{ status: OrderStatus; afterSec: number }> = [
  { status: "confirmed", afterSec: 40 },
  { status: "preparing", afterSec: 120 },
  { status: "delivering", afterSec: 260 },
  { status: "done", afterSec: 420 },
];

function autoAdvanceOrders(orders: Order[]): Order[] {
  const now = Date.now();
  let changed = false;
  const result = orders.map((o) => {
    if (!o.auto_advance || o.status === "done" || o.status === "canceled") return o;
    const age = (now - new Date(o.created_at).getTime()) / 1000;
    let target: OrderStatus | null = null;
    for (const step of ADVANCE_STEPS) {
      if (age >= step.afterSec) target = step.status;
    }
    if (o.type === "pickup" && target === "delivering") target = "preparing";
    if (target && o.status !== target) {
      changed = true;
      return {
        ...o,
        status: target,
        status_history: [...o.status_history, { status: target, at: new Date().toISOString() }],
      };
    }
    return o;
  });
  if (changed) {
    cache.orders = result;
    persist();
  }
  return result;
}

/* ------------------------------------------------------------------ */
/*  API عمومی                                                          */
/* ------------------------------------------------------------------ */

export const api = {
  getMode(): "demo" | "supabase" {
    return isSupabaseConfigured ? "supabase" : "demo";
  },

  async getBranches(): Promise<Branch[]> {
    if (isSupabaseConfigured) {
      try {
        const { data, error } = await supabase
          .from("branches")
          .select("*")
          .eq("is_active", true)
          .order("sort");
        if (!error && data) return data as unknown as Branch[];
      } catch {
        /* fallback به دمو */
      }
    }
    return load().branches.filter((b) => b.is_active).sort((a, b) => a.sort - b.sort);
  },

  async getCategories() {
    return load().categories.filter((c) => c.is_active).sort((a, b) => a.sort - b.sort);
  },

  async getProducts(): Promise<Product[]> {
    if (isSupabaseConfigured) {
      try {
        const { data, error } = await supabase
          .from("products")
          .select("*")
          .order("sort");
        if (!error && data) return data as unknown as Product[];
      } catch {
        /* fallback به دمو */
      }
    }
    return [...load().products].sort((a, b) => a.sort - b.sort);
  },

  async getReviews(): Promise<Review[]> {
    return load().reviews;
  },

  async getCoupons(): Promise<Coupon[]> {
    return load().coupons;
  },

  async getSettings(): Promise<StoreSettings> {
    return load().settings;
  },

  async getOrders(): Promise<Order[]> {
    return autoAdvanceOrders(load().orders).sort(
      (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    );
  },

  async getOrdersByPhone(phone: string): Promise<Order[]> {
    const all = await this.getOrders();
    return all.filter((o) => o.customer_phone === phone);
  },

  async getOrderByCode(code: string): Promise<Order | null> {
    const all = await this.getOrders();
    return all.find((o) => o.code.toLowerCase() === code.trim().toLowerCase()) ?? null;
  },

  async createOrder(order: Order): Promise<Order> {
    const db = load();
    db.orders.unshift(order);
    // ثبت یا به‌روزرسانی مشتری
    const existing = db.customers.find((c) => c.phone === order.customer_phone);
    if (!existing) {
      db.customers.push({
        id: uid("cu"),
        name: order.customer_name,
        phone: order.customer_phone,
        addresses: order.address
          ? [{ label: "آدرس سفارش", address: order.address, area: order.area || "" }]
          : [],
        created_at: new Date().toISOString(),
      });
    } else if (order.address && !existing.addresses.some((a) => a.address === order.address)) {
      existing.addresses.push({
        label: `آدرس ${fa(existing.addresses.length + 1)}`,
        address: order.address,
        area: order.area || "",
      });
    }
    persist();
    return order;
  },

  async updateOrderStatus(orderId: string, status: OrderStatus): Promise<void> {
    const db = load();
    const o = db.orders.find((x) => x.id === orderId);
    if (!o) return;
    o.status = status;
    o.auto_advance = false; // تغییر دستی ادمین، پیشرفت خودکار را متوقف می‌کند
    o.status_history.push({ status, at: new Date().toISOString() });
    persist();
  },

  /* ---------------- عملیات مدیریتی ---------------- */

  async upsertProduct(p: Product): Promise<void> {
    const db = load();
    const i = db.products.findIndex((x) => x.id === p.id);
    if (i >= 0) db.products[i] = p;
    else db.products.push(p);
    persist();
  },

  async deleteProduct(id: string): Promise<void> {
    const db = load();
    db.products = db.products.filter((x) => x.id !== id);
    persist();
  },

  async upsertBranch(b: Branch): Promise<void> {
    const db = load();
    const i = db.branches.findIndex((x) => x.id === b.id);
    if (i >= 0) db.branches[i] = b;
    else db.branches.push(b);
    persist();
  },

  async deleteBranch(id: string): Promise<void> {
    const db = load();
    db.branches = db.branches.filter((x) => x.id !== id);
    persist();
  },

  async upsertCoupon(c: Coupon): Promise<void> {
    const db = load();
    const i = db.coupons.findIndex((x) => x.id === c.id);
    if (i >= 0) db.coupons[i] = c;
    else db.coupons.push(c);
    persist();
  },

  async deleteCoupon(id: string): Promise<void> {
    const db = load();
    db.coupons = db.coupons.filter((x) => x.id !== id);
    persist();
  },

  async updateSettings(s: StoreSettings): Promise<void> {
    const db = load();
    db.settings = s;
    persist();
  },

  async upsertCategory(c: AppData["categories"][number]): Promise<void> {
    const db = load();
    const i = db.categories.findIndex((x) => x.id === c.id);
    if (i >= 0) db.categories[i] = c;
    else db.categories.push(c);
    persist();
  },

  async deleteCategory(id: string): Promise<void> {
    const db = load();
    db.categories = db.categories.filter((x) => x.id !== id);
    db.products = db.products.filter((x) => x.category_id !== id);
    persist();
  },
};

function fa(n: number): string {
  return String(n).replace(/\d/g, (d) => "۰۱۲۳۴۵۶۷۸۹"[Number(d)]);
}
