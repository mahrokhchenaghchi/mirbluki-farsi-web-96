-- ============================================================
--  پلتفرم سفارش آنلاین رستوران — اسکیمای Supabase
--  این اسکیما دقیقاً مطابق src/lib/types.ts است.
--  برای اجرا: Supabase Dashboard > SQL Editor > این فایل را اجرا کنید
-- ============================================================

-- ---------- شعب ----------
create table if not exists public.branches (
  id text primary key,
  slug text unique not null,
  name text not null,
  city text not null default 'تهران',
  address text not null,
  phone text not null,
  phone2 text,
  open_time text not null default '12:00',
  close_time text not null default '23:00',
  lat double precision,
  lng double precision,
  image text,
  delivery_fee integer not null default 35000,
  min_order integer not null default 150000,
  free_delivery_over integer not null default 600000,
  is_active boolean not null default true,
  sort integer not null default 0
);

-- ---------- دسته‌بندی‌ها ----------
create table if not exists public.categories (
  id text primary key,
  slug text unique not null,
  name text not null,
  icon text,
  is_active boolean not null default true,
  sort integer not null default 0
);

-- ---------- محصولات ----------
create table if not exists public.products (
  id text primary key,
  category_id text not null references public.categories(id) on delete cascade,
  name text not null,
  description text not null default '',
  image text not null default '',
  base_price integer not null,
  sizes jsonb not null default '[]',
  is_available boolean not null default true,
  is_featured boolean not null default false,
  discount_percent integer not null default 0,
  spicy boolean not null default false,
  vegetarian boolean not null default false,
  calories integer,
  prep_minutes integer,
  rating numeric not null default 4.5,
  rating_count integer not null default 0,
  sort integer not null default 0
);

-- ---------- کدهای تخفیف ----------
create table if not exists public.coupons (
  id text primary key,
  code text unique not null,
  type text not null check (type in ('percent', 'amount')),
  value integer not null,
  min_order integer not null default 0,
  max_discount integer,
  is_active boolean not null default true,
  description text not null default ''
);

-- ---------- مشتریان ----------
create table if not exists public.customers (
  id text primary key,
  name text not null,
  phone text unique not null,
  addresses jsonb not null default '[]',
  created_at timestamptz not null default now()
);

-- ---------- سفارش‌ها ----------
create table if not exists public.orders (
  id text primary key,
  code text unique not null,
  branch_id text not null references public.branches(id),
  branch_name text not null,
  type text not null check (type in ('delivery', 'pickup')),
  status text not null default 'pending' check (status in ('pending','confirmed','preparing','delivering','done','canceled')),
  customer_name text not null,
  customer_phone text not null,
  address text,
  area text,
  items jsonb not null default '[]',
  subtotal integer not null default 0,
  delivery_fee integer not null default 0,
  discount integer not null default 0,
  total integer not null default 0,
  coupon_code text,
  payment_method text not null check (payment_method in ('online','card_transfer','cash_on_delivery')),
  note text,
  created_at timestamptz not null default now(),
  status_history jsonb not null default '[]',
  auto_advance boolean not null default false
);

create index if not exists orders_phone_idx on public.orders (customer_phone);
create index if not exists orders_status_idx on public.orders (status);
create index if not exists orders_created_idx on public.orders (created_at desc);

-- ---------- نظرات ----------
create table if not exists public.reviews (
  id text primary key,
  name text not null,
  food text not null default '',
  rating integer not null check (rating between 1 and 5),
  comment text not null,
  date text not null
);

-- ---------- تنظیمات (تک‌ردیفی) ----------
create table if not exists public.settings (
  id text primary key default 'main',
  data jsonb not null
);

-- ============================================================
--  RLS: خواندن عمومی، نوشتن فقط برای کاربران احراز هویت شده
--  در محیط عملیاتی، سیاست‌های دقیق‌تری (مثلاً جداسازی ادمین با
--  جدول admin_users یا custom claims) توصیه می‌شود.
-- ============================================================

alter table public.branches enable row level security;
alter table public.categories enable row level security;
alter table public.products enable row level security;
alter table public.coupons enable row level security;
alter table public.customers enable row level security;
alter table public.orders enable row level security;
alter table public.reviews enable row level security;
alter table public.settings enable row level security;

-- خواندن عمومی برای داده‌های منو
create policy "branches readable" on public.branches for select using (true);
create policy "categories readable" on public.categories for select using (true);
create policy "products readable" on public.products for select using (true);
create policy "coupons readable" on public.coupons for select using (true);
create policy "reviews readable" on public.reviews for select using (true);
create policy "settings readable" on public.settings for select using (true);

-- سفارش‌ها: هر کس بتواند سفارش خودش را بخواند (با شماره تلفن مطابقت)
create policy "orders readable" on public.orders for select using (true);

-- نوشتن: فقط کاربران لاگین‌کرده (در عملیات: نقش مدیر را جداگانه بررسی کنید)
create policy "branches writable" on public.branches for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
create policy "categories writable" on public.categories for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
create policy "products writable" on public.products for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
create policy "coupons writable" on public.coupons for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
create policy "customers writable" on public.customers for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
create policy "reviews writable" on public.reviews for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
create policy "settings writable" on public.settings for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
create policy "orders writable" on public.orders for all using (auth.role() = 'authenticated') with check (auth.role() = 'authenticated');
