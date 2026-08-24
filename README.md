# جوما | JOMA

جوما یک Web Application فارسی و راست‌چین است برای:

**برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود**

کاربر برای هر دوره ماهانه شمسی یک برنامه مستقل دارد، عملکرد واقعی و خلق را ثبت می‌کند، و گزارش همان دوره را بر اساس داده واقعی می‌بیند. تاریخچه دوره‌های قبل با تغییر کتابخانه فعالیت عوض نمی‌شود.

جوما برنامه Windows نیست و به نصب EXE نیاز ندارد.

## معماری

```text
UI → Services → Domain / Business Rules → Reporting Engine → Supabase / PostgreSQL
```

جزئیات در:

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- [docs/DATABASE.md](docs/DATABASE.md)
- [docs/BUSINESS_RULES.md](docs/BUSINESS_RULES.md)
- [docs/REPORTING_ENGINE.md](docs/REPORTING_ENGINE.md)
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
- [docs/UNSPECIFIED.md](docs/UNSPECIFIED.md)

## اجرای محلی

نیازمندی: Node.js 18+

```sh
npm install
cp .env.example .env
```

مقادیر `VITE_SUPABASE_URL` و `VITE_SUPABASE_ANON_KEY` را از پروژه Supabase بگذارید.

سپس مهاجرت را در SQL Editor سوپابیس اجرا کنید:

`supabase/migrations/20260824120000_joma_core.sql`

```sh
npm run dev
```

آدرس پیش‌فرض توسعه: `http://localhost:8080`

## ساخت و تست

```sh
npm test
npm run build
npm run preview
```

## استقرار

پروژه یک SPA است و برای Vercel و Netlify آماده است. راهنما: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)

## مالکیت کد

تمام سورس‌کد، مهاجرت‌ها، قوانین کسب‌وکار، موتور گزارش و مستندات در همین مخزن است و برای اجرا به Agent وابسته نیست.
