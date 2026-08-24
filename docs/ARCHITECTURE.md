# معماری جوما

## لایه‌ها

```text
UI (pages / layouts / features)
        ↓
Application services (src/services)
        ↓
Domain rules (src/domain)
        ↓
Reporting Engine (src/reporting)
        ↓
Persistence (Supabase PostgreSQL + RLS)
```

تقویم جلالی فقط از `src/calendar/JomaCalendarService.ts` عبور می‌کند.

## موجودیت‌ها

- **User**: حساب Supabase Auth + `joma_profiles`
- **Period**: ماه شمسی مثل `1405-06`
- **Activity**: تعریف کتابخانه (`ACT001` تا `ACT045`)
- **Plan**: برنامه یک کاربر برای یک دوره
- **PlanActivity**: تصویر ثابت فعالیت در همان برنامه
- **Performance Event**: رخداد ثبت عملکرد
- **Mood Record**: ثبت روزانه خلق
- **Report Projection**: خروجی محاسبه‌شده برای نمایش؛ جایگزین رویداد خام نیست

## احراز هویت

Supabase Auth با ایمیل و رمز. نشست در `localStorage` مرورگر می‌ماند و توکن به‌صورت خودکار تازه می‌شود. رمز عبور به صورت متن ساده ذخیره نمی‌شود.

پس از ورود:

ورود → وقت بخیر → امروز چطوری؟ → ثبت خلق (با وضعیت فعلی UNSPECIFIED) → داشبورد

## چندکاربره و امنیت

هر جدول شخصی `user_id` دارد و RLS فقط ردیف‌های همان کاربر را نشان می‌دهد. کتابخانه فعالیت برای کاربران واردشده خواندنی است.

## جریان ثبت عملکرد

```text
UI
 → Performance Service
 → canRegisterPerformance()
 → RPC joma_register_performance
 → Event row
 → rebuild Report Projection
```

## استقلال محصول

جوما به سایت نوبت‌دهی وابسته نیست. جدول‌ها با پیشوند `joma_` جدا هستند.
