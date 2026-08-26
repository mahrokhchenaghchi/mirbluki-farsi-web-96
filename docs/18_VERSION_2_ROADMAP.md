# 18 — قابلیت‌های JOMA V2 (بر اساس معماری فعلی)

برچسب **RECOMMENDATION**. هیچ‌کدام در V1 کامل نیستند مگر جایی که نوشته شده IMPLEMENTED.

زیرساخت قابل استفادهٔ مشترک: User, Period, Plan, PlanActivity Snapshot, Events, Mood 1–5, build_report, نقش SQL، OTP table، notifications table، settings، Jalali، CSRF.

---

## حداقل ۲۰ پیشنهاد

### User / Admin
1. **مدیریت کاربران ادمین** — دیدن لیست، تغییر `role_key`. جدول users آماده. UI و خواندن SQL permissions نیست. سختی متوسط. اولویت بالا اگر چند سطح می‌خواهید.
2. **Permission از دیتابیس** — `store_role_permissions` را به `joma_role_permissions` وصل کنید. جداول Ready. سختی متوسط.
3. **تأیید موبایل** — ستون `mobile_verified` و جدول OTP Ready؛ ارسال SMS نیست. سختی بالا (سرویس خارجی).

### Planning / Activity
4. **کپی برنامه ماه قبل** — Snapshotها مستقل‌اند؛ خواندن plan_activities دوره قبل و insert در DRAFT جدید. بدون جدول جدید. سختی کم. اولویت بالا.
5. **قالب برنامه آماده** — چند PlanActivity پیش‌فرض. می‌توان جدول کوچک `joma_plan_templates` یا JSON. سختی متوسط.
6. **مرتب‌سازی کامل Library** — الان sort در plan است؛ فیلتر دسته IMPLEMENTED. سختی کم.

### Performance / Mood
7. **ویرایش/حذف Event** — جدول events هست؛ UI حذف نیست. سختی کم-متوسط (باید قوانین DAILY حفظ شود).
8. **یادآوری ثبت امروز** — `notifications` + cron/هاست. جدول هست. SMS نیست. سختی متوسط.
9. **همبستگی Mood و عملکرد** — هر دو داده روزانه موجود است؛ تابع جدید روی calendar report. بدون جدول اجباری. سختی متوسط.

### Reporting
10. **ذخیره Projection** — جدول `joma_report_projections` خالی است. `build_report` را JSON ذخیره کنید. سختی کم.
11. **خروجی CSV/Excel از Event خام** — داده در events. سختی کم. PDF نیاز کتابخانه/سرویس دارد (متوسط-بالا روی هاست بدون Composer).
12. **فرمول Achievement وقتی مالک تعریف کرد** — الان عمداً UNSPECIFIED. فقط بعد از تعریف رسمی. سختی متوسط + ریسک محصول.

### Gamification (با احتیاط)
13. **Streak ثبت روزانه** — از events DAILY قابل محاسبه بدون جدول؛ برای پایداری جدول `joma_streaks`. سختی متوسط. Achievement جعلی نشود.
14. **نشان (Badge) ساده** — مثلاً «۷ روز Mood». نیاز جدول badges. سختی متوسط.

### Notification
15. **صندوق درون‌برنامه** — جدول notifications Ready. صفحه inbox جدید. سختی کم-متوسط.
16. **ایمیل یادآوری** — نیاز SMTP خارجی. سختی متوسط.

### Mobile / API
17. **JSON API برای اپ** — الان صفحه HTML است. می‌توان endpointهای نازک روی همان توابع `register_performance` ساخت. سختی بالا. Session/توکن جدید.

### AI (فقط روی داده موجود)
18. **مرور هفتگی متنی از Event+Mood** — بدون مدل هم می‌توان خلاصه آماری از `build_report` ساخت. مدل زبانی = سرویس خارجی. سختی متوسط تا بالا.
19. **پیشنهاد فعالیت از Library بر اساس ثبت کم** — مقایسه target/actual بدون درصد دروغین. سختی متوسط.

### Social / Coaching
20. **نقش coach دیدن گزارش با اجازه** — role در SQL هست؛ اشتراک plan بین کاربران IMPLEMENTED نیست. نیاز جدول اشتراک. سختی بالا.
21. **هدف شخصی جدا از Library** — فیلد اضافه روی plan یا جدول goals. سختی متوسط.

### تقویم
22. **خروجی ICS** — از events تاریخ شمسی→میلادی (`jalali_to_gregorian` موجود). سختی متوسط.

---

## Roadmap پیشنهادی

```
V1.1  سریع: کپی برنامه ماه قبل، حذف/ویرایش Event، Inbox اعلان، CSV
V1.2  اتصال permissions به SQL + صفحه ادمین نقش
V1.5  OTP موبایل (سرویس SMS) + صندوق و ایمیل
V2.0  API موبایل + فرمول Achievement رسمی (فقط اگر مالک نوشت)
V2.5  Coach share + همبستگی Mood/عملکرد + ICS
```

| نوع | مثال |
|---|---|
| سریع | کپی ماه قبل، CSV، inbox |
| متوسط | permissions SQL، streak، همبستگی |
| پیچیده | API موبایل، SMS، AI خارجی |
| نیاز DB | badges, shares, گاهی streak |
| نیاز API/سرویس | SMS, email, LLM |
| نیاز Admin | تغییر role |

**RECOMMENDATION:** درصد موفقیت نسازید تا فرمول رسمی نیاید.
