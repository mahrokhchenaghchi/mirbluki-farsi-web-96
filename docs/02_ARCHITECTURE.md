# 02 — معماری واقعی

## دیاگرام درخواست (کد فعلی)

```
کاربر (مرورگر RTL)
  ↓  GET/POST  /joma/index.php?p=...
index.php
  ↓
includes/bootstrap.php
  ├ session_start (کوکی PHPSESSID یا joma_sid)
  ├ خواندن config.php
  ├ jalali.php + helpers.php + store.php + functions/joma.php + layout.php
  ↓
maybe_mood_gate($p)     اگر لاگین است و حال امروز نیست → mood
  ↓
pages/{p}.php
  ├ require_login / require_perm   (بعضی صفحات)
  ├ csrf_check()                   (اگر POST)
  ├ Validation (helpers یا خود صفحه)
  ├ توابع functions/joma.php
  │     ├ store_mode()==file  → store.json
  │     └ store_mode()==mysql → mysqli_prepare / joma_query
  ↓
joma_header() … HTML … joma_footer()
  ↓
CSS assets/css/joma.css
JS  assets/js/joma.js   (فقط hint؛ Submit فرم معمولی است)
  ↓
پاسخ HTML به کاربر
```

**Frontend جدا با API JSON برای صفحات اصلی IMPLEMENTED نیست.**  
صفحات PHP خودشان HTML می‌سازند. تنها API: `api/username.php` (JSON برای hint نام کاربری).

## ارتباط PHP و MySQL

فقط وقتی `storage === 'mysql'`:

1. `db()` در `store.php` با `mysqli_connect`.
2. `joma_query` / `joma_exec` با `mysqli_prepare` و `joma_stmt_bind`.
3. PDO و ORM در کد **IMPLEMENTED نیست**.

اگر اتصال قطع شود: `die('اتصال پایگاه داده برقرار نشد.')`.

## Session

در `bootstrap.php` قبل از هر صفحه:

- `session.save_path` = `joma/data/sessions`
- کوکی HttpOnly
- اگر Preview/HTTPS: SameSite=None و در HTTPS: Secure + Partitioned
- اگر کوکی نرسد: `joma_sid` از POST یا GET
- `session.use_only_cookies = 0` تا sid در URL/فرم کار کند

`$_SESSION['user']` آرایهٔ `session_user_array` است (بدون رمز).

## قالب

- کاربر لاگین: سایدبار + breadcrumb + منوی پایین موبایل (`layout.php`)
- مهمان: `public-shell` (خانه، ورود، ثبت‌نام، فراموشی، درباره)

بازگشت: `javascript:history.back()` در crumbs.

## فایل‌های Config

| فایل | نقش |
|---|---|
| `config/config.php` | تنظیم واقعی سرور |
| `config/config.example.php` | نمونه بدون رمز واقعی |
| `install.php` | ساخت جداول از SQL و نوشتن config — بعد از نصب باید حذف شود |

## امنیت لایه درخواست

- صفحات خارج از `$allowed` به `home` می‌روند.
- XSS: خروجی با `e()` = `htmlspecialchars`.
- CSRF: فیلد `csrf` در فرم‌های POST.
- دادهٔ کاربر با `user_id` در کوئری‌ها جدا می‌شود (در مسیر MySQL و فایل).
