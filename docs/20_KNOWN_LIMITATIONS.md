# 20 — محدودیت‌ها، تناقض‌ها، چه چیز را عوض کنیم

## CONFLICTS & RISKS

1. **نقش SQL در برابر PHP ثابت**  
   جداول `joma_roles` / `joma_role_permissions` Seed می‌شوند اما `has_perm` از `store_role_permissions` می‌خواند. عوض کردن SQL بدون PHP بی‌اثر است.

2. **member ≈ plus ≈ coach در Runtime**  
   فقط admin دو مجوز اضافه دارد و آن‌ها UI ندارند.

3. **CREATE_PLAN هرگز require نمی‌شود**  
   در ماتریس هست؛ صفحه plan از EDIT_PLAN استفاده می‌کند.

4. **access_level ذخیره می‌شود، چک نمی‌شود**

5. **گزارش Projection جدول دارد، نوشته نمی‌شود**

6. **OTP و Notification جدول دارند، محصول کامل نیستند**

7. **support_contact در settings خالی است و صفحه support از جدول نمی‌خواند**

8. **یکتایی DAILY فقط در PHP است نه UNIQUE ایندکس SQL**  
   دو درخواست همزمان theoretically می‌توانند دو Event روزانه بسازند.

9. **هدف ۰ در Seed هفتگی/ماهانه**  
   `add_plan_activity` target<=0 را رد می‌کند. ACT018 و مشابه اگر Override نشود ممکن است با target پیش‌فرض weekly>0 OK باشند؛ اگر کسی frequency را DAILY کند و daily=0، افزودن رد می‌شود.

10. **رنگ در style خام**  
    اگر کاربر در Library مقدار غیر HEX بگذارد، ریسک HTML/CSS.

11. **دو فرانت**  
    React در `src/` و PHP در `joma/`. منبع حقیقت هاست: PHP. مستندات قدیمی `docs/ARCHITECTURE.md` مربوط به React است و ممکن است با PHP فرق کند.

12. **copy_seed_to_user فقط یک‌بار**  
    Seed جدید به کاربران قدیمی نمی‌رسد.

13. **حذف Activity Library**  
    Snapshot می‌ماند؛ activity_id ممکن است یتیم شود (FK فیزیکی نیست).

14. **install.php خطرناک روی Production اگر حذف نشود**

15. **Preview CSRF/session**  
    `joma_sid` روی URL؛ روی هاست عادی اگر کوکی کار کند sid اضافه نمی‌شود.

## IMPLEMENTED نیست (صریح)

- فرمول Achievement / Overall Success / درصد موفقیت کلی
- پنل ادمین کاربران
- ارسال SMS
- Inbox کامل اعلان
- API موبایل
- یادآوری push
- همبستگی Mood-Performance به‌عنوان گزارش جدا
- Remember-me
- تأیید ایمیل
- چندزبانه
- FOREIGN KEY دیتابیس

## WHAT I CAN SAFELY CHANGE

- متن و CSS و برچسب منو
- `jobs_list` / `categories_list` (اگر دادهٔ قدیمی شغل نامعتبر نشود)
- استیکر/رنگ یک فعالیت در Library برای ماه‌های **بعد**
- `pages/about.php` و متن support (بدون حدس تماس واقعی)
- اضافه کردن تب گزارش که فقط شمارش داده خام باشد
- فایل لوگو در مسیر استاندارد

## WHAT I SHOULD NOT CHANGE WITHOUT REVIEW

- `can_register_performance` و تفاوت DAILY/WEEKLY/MONTHLY
- `transition_plan` و قفل Snapshot
- `build_report` برای ساخت درصد بدون فرمول رسمی
- `store_role_permissions` بدون همگام‌سازی SQL و UI
- CSRF / session / `joma_sid`
- الگوریتم jalali و شروع هفته شنبه
- پاک کردن `user_id` از کوئری‌ها
- عوض کردن Seed کاربران Production بدون مهاجرت

## فایل‌های مستند قدیمی در همین پوشه docs/

`ARCHITECTURE.md`, `AUTHENTICATION.md`, `BUSINESS_RULES.md`, `DATABASE.md`, `DEPLOYMENT.md`, … مربوط به نسخه React/محلی قبلی‌اند. برای هاست PHP این مجموعهٔ شماره‌دار را مبنا بگیرید.
