# تست محلی جوما

بدون Supabase:

```sh
npm install
npm run dev
```

حالت پیش‌فرض وقتی کلید Supabase نباشد: `local`.

داده در `localStorage` کلید `joma.local.db.v2` است.

حساب از پیش ساخته نمی‌شود. از صفحه ثبت‌نام بسازید.

برای Production بعدی:

```
VITE_JOMA_MODE=supabase
VITE_SUPABASE_URL=...
VITE_SUPABASE_ANON_KEY=...
VITE_BASE=/joma/
```
