# استقرار جوما

## متغیرهای محیطی

```env
VITE_SUPABASE_URL=https://your-project.supabase.co
VITE_SUPABASE_ANON_KEY=your-anon-key
```

رمزها را commit نکنید.

## Supabase

1. پروژه جدید بسازید.
2. فایل مهاجرت `supabase/migrations/20260824120000_joma_core.sql` را در SQL Editor اجرا کنید.
3. Authentication → Providers → Email را فعال کنید.
4. در Authentication → URL Configuration این‌ها را بگذارید:
   - Site URL: دامنه نهایی مثل `https://joma.example.com`
   - Redirect URLs: همان دامنه و `http://localhost:8080/**`

برای آزمایش محلی می‌توان Confirm email را خاموش کرد.

## اجرای محلی

```sh
npm install
cp .env.example .env
npm run dev
```

## زیرمسیر /joma

برای استقرار روی `mirbolouki.com/joma`:

```sh
VITE_BASE=/joma/ npm run build
```

Routing و مسیر دارایی‌ها از `import.meta.env.BASE_URL` خوانده می‌شوند.

## Build

```sh
npm run build
```

خروجی در `dist/` است.

## Vercel

1. مخزن را وصل کنید.
2. Framework: Vite
3. متغیرهای محیطی را اضافه کنید.
4. `vercel.json` بازنویسی SPA را انجام می‌دهد.

## Netlify

1. Build command: `npm run build`
2. Publish: `dist`
3. `netlify.toml` مسیرها را به `index.html` می‌فرستد.

## دامنه

DNS را به Vercel یا Netlify اشاره دهید و همان دامنه را در Supabase Auth ثبت کنید.
