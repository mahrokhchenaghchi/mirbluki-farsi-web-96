# 14 — Reporting Engine

## کد واقعی، نه دیاگرام ایده‌آل

```
list_plan_activities + list_events + list_moods
        ↓
build_report()     functions/joma.php حدود خط 693
        ↓
برای هر PlanActivity:
   events فیلترشده
   actual = sum(actual_value)
   event_count
   achievement = 'UNSPECIFIED'   ← محاسبه نمی‌شود
        ↓
calendar[] برای هر روز ماه: event_count, actual_total, has_mood
weight_sum = جمع weight اسنپ‌شات‌ها
overall_success = 'UNSPECIFIED'
        ↓
pages/reports.php  هشت تب HTML
```

`joma_report_projections` نوشته نمی‌شود.

## تب‌های UI (همه از همان آرایه)

| تب GET `tab` | برچسب | داده واقعی |
|---|---|---|
| overview | خلاصه | تعداد رویداد، تعداد فعالیت، تعداد روز خلق + اخطار UNSPECIFIED |
| acts | فعالیت‌ها | نام، تناوب، actual / target |
| weight | وزن | وزن Snapshot و نوار سهم از جمع وزن — **نه درصد موفقیت** |
| cal | تقویم | شنبه شروع؛ ● رویداد ؛ 🌸 خلق |
| trend | روند | sparkline جمع actual روزهایی که Event دارند |
| mood | خلق | میانگین شاخص‌ها + لیست |
| cmp | مقایسه | تعداد رویداد/فعالیت دو دوره — بدون درصد |
| det | جزئیات | متن «تحقق (تعریف‌نشده)» + لیست Event خام |

هیچ تبی Achievement عددی جعلی نشان نمی‌دهد.

## آیا Achievement محاسبه می‌شود؟

**خیر.** مقدار ثابت رشته `'UNSPECIFIED'`.

## آیا Overall Success محاسبه می‌شود؟

**خیر.** همان.

## نمودارها

- روند: SVG `sparkline_svg` در helpers
- وزن/خلق: CSS bar
- Chart کتابخانه JS جدا IMPLEMENTED نیست
