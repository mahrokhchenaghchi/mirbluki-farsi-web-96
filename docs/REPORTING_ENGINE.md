# موتور گزارش جوما

کد: `src/reporting/engine.ts`

## مسیر

```text
Events + PlanActivity snapshots + Mood dates
        ↓
Reporting Engine
        ↓
Report Projection
        ↓
UI (KPI / chart / calendar / detail)
```

Projection برای نمایش است. منبع حقیقت رویدادها و تصویر برنامه است.

## آنچه محاسبه می‌شود

- جمع مقدار واقعی هر فعالیت
- تعداد رویداد
- فهرست رویدادها برای ردیابی
- تقویم روزهای دوره با رویداد و خلق
- جمع وزن ذخیره‌شده

## آنچه محاسبه نمی‌شود

- Achievement
- Weighted Achievement
- Overall Program Success

این‌ها `UNSPECIFIED` هستند.

## داده ساختگی

موتور داده تصادفی یا درصد ثابت نمی‌سازد. اگر رویدادی نباشد، حالت خالی نمایش داده می‌شود.
