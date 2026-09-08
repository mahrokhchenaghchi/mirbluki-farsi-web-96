/**
 * مدل داده پلتفرم سفارش آنلاین رستوران
 * ------------------------------------------------------------
 * این فایل تنها منبع حقیقت (Single Source of Truth) برای تایپ‌های
 * دیتابیس است. جدول‌های SQL در supabase/migrations دقیقاً مطابق همین
 * مدل ساخته می‌شوند تا در صورت اتصال به Supabase همه‌چیز هم‌خوان باشد.
 */

export type OrderType = "delivery" | "pickup";

export type OrderStatus =
  | "pending"
  | "confirmed"
  | "preparing"
  | "delivering"
  | "done"
  | "canceled";

export type PaymentMethod =
  | "online"
  | "card_transfer"
  | "cash_on_delivery";

export interface Branch {
  id: string;
  slug: string;
  name: string;
  city: string;
  address: string;
  phone: string;
  phone2?: string;
  /** ساعت کاری به فرمت 24 ساعته، مثلا 12:00 و 23:30 */
  open_time: string;
  close_time: string;
  lat?: number;
  lng?: number;
  image?: string;
  delivery_fee: number;
  min_order: number;
  /** سفارش‌های بالای این مبلغ، ارسال رایگان دارند */
  free_delivery_over: number;
  is_active: boolean;
  sort: number;
}

export interface Category {
  id: string;
  slug: string;
  name: string;
  /** نام آیکون lucide (اختیاری) */
  icon?: string;
  is_active: boolean;
  sort: number;
}

export interface ProductSize {
  id: string;
  name: string;
  price: number;
}

export interface Product {
  id: string;
  category_id: string;
  name: string;
  description: string;
  image: string;
  /** در صورتی که sizes خالی باشد از base_price استفاده می‌شود */
  base_price: number;
  sizes: ProductSize[];
  is_available: boolean;
  is_featured: boolean;
  discount_percent: number;
  spicy: boolean;
  vegetarian: boolean;
  calories?: number;
  prep_minutes?: number;
  rating: number;
  rating_count: number;
  sort: number;
}

export interface OrderItem {
  product_id: string;
  product_name: string;
  image?: string;
  size_name: string;
  unit_price: number;
  qty: number;
  note?: string;
}

export interface OrderStatusEvent {
  status: OrderStatus;
  at: string; // ISO
}

export interface Order {
  id: string;
  /** کد کوتاه قابل به خاطر سپردن برای مشتری، مثل RP-1042 */
  code: string;
  branch_id: string;
  branch_name: string;
  type: OrderType;
  status: OrderStatus;
  customer_name: string;
  customer_phone: string;
  address?: string;
  area?: string;
  items: OrderItem[];
  subtotal: number;
  delivery_fee: number;
  discount: number;
  total: number;
  coupon_code?: string;
  payment_method: PaymentMethod;
  note?: string;
  created_at: string;
  status_history: OrderStatusEvent[];
  /** در حالت دمو: سفارش به صورت خودکار پیش می‌رود */
  auto_advance?: boolean;
}

export interface Coupon {
  id: string;
  code: string;
  type: "percent" | "amount";
  value: number;
  min_order: number;
  max_discount?: number;
  is_active: boolean;
  description: string;
}

export interface CustomerAddress {
  label: string;
  address: string;
  area: string;
}

export interface Customer {
  id: string;
  name: string;
  phone: string;
  addresses: CustomerAddress[];
  created_at: string;
}

export interface Review {
  id: string;
  name: string;
  food: string;
  rating: number;
  comment: string;
  date: string;
}

export interface StoreSettings {
  brand: string;
  tagline: string;
  phone: string;
  instagram: string;
  telegram: string;
  address: string;
  /** هزینه ارسال پیش‌فرض (وقتی شعبه مقدار ندارد) */
  default_delivery_fee: number;
  admin_phone: string;
  about_text: string;
}

/** مجموعه کامل داده‌ها برای حالت دمو / localStorage */
export interface AppData {
  branches: Branch[];
  categories: Category[];
  products: Product[];
  orders: Order[];
  coupons: Coupon[];
  customers: Customer[];
  reviews: Review[];
  settings: StoreSettings;
}

export interface CartLine {
  product_id: string;
  size_id: string;
  product_name: string;
  size_name: string;
  unit_price: number;
  qty: number;
  image?: string;
  note?: string;
}
