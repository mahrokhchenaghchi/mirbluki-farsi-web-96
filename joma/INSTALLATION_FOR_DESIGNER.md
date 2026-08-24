# راهنمای نصب جوما برای طراح سایت

این بسته، **سورس کامل PHP** محصول مستقل جوما است.  
مسیر نهایی مورد نظر مالک: `https://mirbolouki.com/joma`

- نیازی به SSH نیست.
- نیازی به Composer نیست.
- نیازی به Node.js / npm / Vite / React نیست.
- نیازی به Arena نیست.
- اجرا با **PHP 7.2+** (توصیه: ۷.۴) و **MySQL از طریق MySQLi**.

جوما سایت روانشناسی یا سیستم نوبت‌دهی mirbolouki.com نیست. یک برنامه خودمدیریتی جداست.

---

## ۱) ZIP را کجا Extract کنید

1. وارد cPanel → **File Manager** شوید.
2. پوشه `public_html` را باز کنید.
3. اگر پوشه `joma` وجود ندارد، آن را بسازید.
4. محتویات ZIP را طوری باز کنید که نتیجه این باشد:

```text
public_html/joma/index.php
public_html/joma/config/
public_html/joma/pages/
public_html/joma/database/joma.sql
```

اگر داخل ZIP یک پوشه به نام `joma` هست، همان پوشه را داخل `public_html` بگذارید.  
نباید مسیر این‌طور شود: `public_html/joma/joma/index.php`

---

## ۲) مسیر صحیح روی هاست

| مورد | مقدار |
|---|---|
| پوشه روی دیسک | `public_html/joma/` |
| آدرس مرورگر | `https://mirbolouki.com/joma/` |
| فایل ورود برنامه | `public_html/joma/index.php` |
| `base_url` در تنظیمات | `/joma` |

---

## ۳) ساخت Database در cPanel

1. cPanel → **MySQL Databases** (یا MySQL Database Wizard).
2. یک Database جدید بسازید. نام کامل را یادداشت کنید (معمولاً با پیشوند حساب cPanel شروع می‌شود).  
   مثال ساختگی: `cpaneluser_jomadb`
3. یک Database User جدید بسازید و یک رمز قوی انتخاب کنید.  
   مثال ساختگی: `cpaneluser_jomauser`
4. **هرگز** این رمز را داخل گیت یا پیام عمومی نگذارید.

---

## ۴) وصل کردن User به Database

1. در همان صفحه MySQL Databases بخش **Add User To Database** را پیدا کنید.
2. همان User را به همان Database وصل کنید.
3. دسترسی‌ها را **ALL PRIVILEGES** بدهید و ذخیره کنید.

بدون این مرحله، Import و اتصال برنامه شکست می‌خورد.

---

## ۵) Import فایل SQL از phpMyAdmin

1. cPanel → **phpMyAdmin**.
2. از ستون چپ، دیتابیسی که ساختید را انتخاب کنید.
3. تب **Import**.
4. فایل را انتخاب کنید: `joma/database/joma.sql`
5. Go / Import.

این فایل جداول و کتابخانه رسمی ۴۵ فعالیت را می‌سازد.

اگر خطای «table already exists» دیدید، یعنی قبلاً Import شده است. دوباره Import نکنید مگر اینکه عمداً بخواهید از اول بسازید.

---

## ۶) تنظیم فایل Configuration

1. فایل `config/config.example.php` را کپی کنید.
2. نام کپی را بگذارید: `config/config.php`
3. این چهار مقدار را با اطلاعات واقعی هاست عوض کنید:

| کلید | چه بگذارید |
|---|---|
| `db_host` | معمولاً `localhost` |
| `db_name` | نام کامل دیتابیس cPanel |
| `db_user` | نام کامل کاربر دیتابیس |
| `db_pass` | رمز همان کاربر |

4. این دو مقدار را همین‌طور بگذارید مگر مالک چیز دیگری بگوید:

```php
'base_url' => '/joma',
'storage'  => 'mysql',
```

`storage = mysql` یعنی برنامه از MySQL واقعی استفاده می‌کند.  
`storage = file` فقط برای تست بدون دیتابیس است و داده را در `data/store.json` می‌نویسد. برای تحویل نهایی روی mirbolouki.com از `mysql` استفاده کنید.

---

## ۷) اتصال MySQLi

برنامه PDO یا ORM ندارد. اتصال فقط با **MySQLi** و Prepared Statement است.

بعد از ذخیره `config.php`، آدرس زیر را باز کنید:

`https://mirbolouki.com/joma/`

اگر صفحه معرفی جوما آمد، مسیر فایل‌ها درست است.  
اگر نوشت «اتصال پایگاه داده برقرار نشد»، مقادیر `db_*` یا Privilege کاربر را دوباره چک کنید.

روش جایگزین: باز کردن `https://mirbolouki.com/joma/install.php` و پر کردن فرم. این روش `config.php` را می‌سازد. **بعد از نصب موفق، `install.php` را حذف کنید.**

---

## ۸) مجوز پوشه‌ها (Permission)

| مسیر | مجوز پیشنهادی |
|---|---|
| بیشتر فایل‌ها و پوشه‌ها | `755` پوشه / `644` فایل |
| `joma/data/` | `755` یا `775` (باید قابل نوشتن باشد) |
| `joma/data/sessions/` | `755` یا `775` |
| `joma/config/config.php` | `644` (قابل خواندن برای PHP، نه عمومی‌تر) |

معمولاً لازم نیست Permission عجیب بدهید. اگر Session یا ذخیره فایل خطا داد، فقط نوشتنی بودن `data/` را بررسی کنید.

پوشه `data/` با `.htaccess` از وب بسته شده است. پوشه `config/` هم اجرای PHP از بیرون را محدود می‌کند.

---

## ۹) آدرس نهایی

بعد از نصب:

**https://mirbolouki.com/joma**

صفحات از همین ریشه باز می‌شوند، مثلاً:

- `https://mirbolouki.com/joma/index.php?p=login`
- `https://mirbolouki.com/joma/index.php?p=register`

---

## ۱۰) تست‌هایی که بعد از نصب انجام دهید

1. صفحه اصلی باز شود (RTL، فارسی، ظاهر پاستلی).
2. ثبت‌نام با پذیرش قوانین.
3. Validation فیلدها (نام کاربری، موبایل، رمز، ایمیل).
4. ورود با حساب درست.
5. ورود با رمز غلط — باید پیام خطا بدهد.
6. خروج و ورود دوباره.
7. Mood پنج‌شاخصه با استیکر.
8. داشبورد.
9. ساخت / انتخاب دوره شمسی.
10. افزودن فعالیت به برنامه با Override هدف / تناوب / وزن.
11. نهایی‌سازی و شروع اجرا.
12. ثبت Performance روزانه / هفتگی / ماهانه.
13. کتابخانه ۴۵ فعالیت رسمی.
14. گزارش‌ها: خلاصه، فعالیت‌ها، وزن، تقویم، روند، خلق، مقایسه، جزئیات.
15. پروفایل و تنظیمات.
16. منو، بازگشت، و نمایش موبایل.

Achievement یا درصد موفقیت کلی نباید ساخته شود؛ این مورد هنوز تعریف نشده است.

---

## ۱۱) اگر خطا رخ داد

| نشانه | کجا را ببینید |
|---|---|
| صفحه سفید / ۵۰۰ | نسخه PHP در cPanel (۷.۴ یا ۸.x). خطاهای PHP را موقتاً روشن کنید. |
| اتصال پایگاه داده برقرار نشد | `config/config.php` مقدار `storage`، `db_host`، `db_name`، `db_user`، `db_pass` و Privilege کاربر |
| ۴۰۴ روی `/joma/` | پوشه باید `public_html/joma` باشد نه یک سطح تو در تو |
| CSS/ظاهر خراب | فایل `assets/css/joma.css` آپلود شده باشد؛ `base_url` برابر `/joma` باشد |
| Session / ورود قطع می‌شود | مجوز نوشتن `data/sessions` |
| جداول پیدا نشد | `database/joma.sql` را در phpMyAdmin روی همان دیتابیس Import کنید |
| لوگو نیست | فایل `assets/images/logo.jpg` را طبق `PUT_LOGO_HERE.txt` بگذارید |

بعد از نصب موفق، **`install.php` را حذف کنید.**

---

## لوگو

مسیر استاندارد:

`public_html/joma/assets/images/logo.jpg`

اگر این فایل نباشد برنامه کار می‌کند و نشان موقت نشان می‌دهد.

---

## چیزهایی که نباید آپلود یا commit شوند

- رمز دیتابیس در جایی غیر از `config.php` روی خود هاست
- `data/store.json` محیط تست دیگران
- پوشه `preview/` (فقط برای محیط توسعه Arena بود و در این بسته نیست)
- Node / React / Vite
