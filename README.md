# جوما | JOMA

جوما یک Web Application مستقل فارسی و RTL است برای:

**برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود**

به سایت نوبت‌دهی یا mirbolouki.com وابسته نیست. در آینده فقط ممکن است روی مسیر `/joma` میزبانی شود.

## اجرا

```sh
npm install
npm run dev
```

بدون تنظیم Supabase، حالت Local/Test فعال است.

```sh
npm test
npm run build
```

برای زیرمسیر آینده:

```sh
VITE_BASE=/joma/ npm run build
```

لوگوی رسمی را در `public/logo.jpg` قرار دهید.

## مستندات

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- [docs/DATABASE.md](docs/DATABASE.md)
- [docs/BUSINESS_RULES.md](docs/BUSINESS_RULES.md)
- [docs/REPORTING_ENGINE.md](docs/REPORTING_ENGINE.md)
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
- [docs/LOCAL_TESTING.md](docs/LOCAL_TESTING.md)
- [docs/UNSPECIFIED.md](docs/UNSPECIFIED.md)
