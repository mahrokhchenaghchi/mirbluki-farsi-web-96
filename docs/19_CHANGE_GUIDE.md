# 19 — اگر فردا بخواهم قابلیت X را اضافه کنم

کد برنامه را در این مرحله عوض نکرده‌ایم؛ این آموزش «چطور دست بزنید» است.

## مثال 1 — فعالیت رسمی جدید ACT046

1. `database/library_official.json` یک آبجکت مثل بقیه.  
2. همان ردیف را به INSERT انتهای `database/joma.sql` اضافه کنید.  
3. Database: بله برای نصب تازه. کاربران قبلی: `copy_seed_to_user` فقط اگر هنوز Seed ندارند کپی می‌کند — کاربران قدیمی این فعالیت را **خودکار نمی‌گیرند** مگر منطق جدا بنویسید.  
4. Validation: همان فیلدهای Seed.  
5. UI: کتابخانه از لیست کاربر می‌خواند.  
6. تست: کاربر جدید باید ACT046 را ببیند؛ Snapshot ماه قبل نباید عوض شود.

## مثال 2 — فیلد جدید روی User (مثلاً city)

1. ستون در `joma.sql` + کلید در آرایه `create_user` / `store.json`.  
2. `pages/register.php` و `validate_registration` و `session_user_array` و پروفایل/تنظیمات.  
3. Database: بله ALTER برای دیتابیس موجود.  
4. Validation سرور اجباری.  
5. فرم register + settings.  
6. تست ثبت‌نام و ذخیره تنظیمات؛ Session بعد ورود فیلد را دارد.

## مثال 3 — Permission جدید مثلاً VIEW_COACH

1. INSERT در `joma_permissions` و `joma_role_permissions`.  
2. **حتماً** `store_role_permissions` در `store.php` — وگرنه Runtime نمی‌فهمد.  
3. Database: بله.  
4. `require_perm('VIEW_COACH')` در صفحه جدید.  
5. منو: `nav_items` + `index.php` allowed.  
6. تست با user member (باید رد شود اگر به member ندادید) و admin.

الان member و admin در PHP فرق کمی دارند؛ بدون تغییر `store_role_permissions` نقش SQL بی‌اثر است.

## مثال 4 — گزارش جدید «تعداد روزهای دارای Event»

1. یا داخل `build_report` فیلد جدید بشمارید (از `calendar`) یا در `reports.php` تب جدید از همان calendar.  
2. تابع: ترجیحاً `build_report` تا یک منبع داده بماند.  
3. DB لازم نیست اگر از events موجود است. Projection جدول را هنوز اجباری نکنید.  
4. درصد نسازید مگر فرمول رسمی.  
5. `$tabs` در `reports.php`.  
6. تست با صفر Event و با چند Event.

## مثال 5 — SMS OTP

1. جدول `joma_otp_codes` آماده است.  
2. توابع جدید بسازید (ارسال/verify) — الان نیستند.  
3. DB: جدول هست؛ سرویس SMS خارجی لازم است.  
4. Validation کد و انقضا.  
5. UI ثبت‌نام/ورود.  
6. تست: بدون ارسال واقعی در dev؛ `mobile_verified`.  
سختی بالا؛ هاست اشتراکی cron/queue ندارد مگر سرویس بیرونی.

## الگوی کلی هر تغییر

1. صفحه یا Seed؟  
2. تابع در `functions/joma.php` یا helpers؟  
3. آیا هر دو حالت file و mysql را باید لمس کنید؟ **بله اگر داده است.**  
4. CSRF برای POST.  
5. `e()` برای خروجی.  
6. Snapshot دوره قبل را نشکنید.
