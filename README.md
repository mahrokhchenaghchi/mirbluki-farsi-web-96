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

## اجرای محلی / آزمایشی

نیازمندی: Node.js 18+

بدون Supabase و بدون اطلاعات تولید:

```sh
npm install
npm run dev
```

اگر `VITE_SUPABASE_URL` خالی باشد، جوما به‌صورت خودکار در **حالت local** اجرا می‌شود.

- داده در `localStorage` همین مرورگر ذخیره می‌شود
- ثبت‌نام، ورود، خروج، دوره، عملکرد، خلق و گزارش واقعاً کار می‌کنند
- Refresh و ورود مجدد داده را نگه می‌دارد
- به پایگاه تولید وصل نمی‌شود

آدرس پیش‌فرض توسعه: `http://localhost:8080`

حساب آزمایشی از پیش ساخته نمی‌شود. از صفحه ثبت‌نام یک ایمیل و رمز بسازید.

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
