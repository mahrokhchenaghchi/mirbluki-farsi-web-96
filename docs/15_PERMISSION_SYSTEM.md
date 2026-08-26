# 15 — Role / Access Level / Permission

## پاسخ مستقیم به سؤال «آیا درجه‌بندی واقعی است؟»

| سؤال | پاسخ از کد |
|---|---|
| آیا Role داریم؟ | فیلد `role_key` روی User هست. پیش‌فرض `member`. SQL چهار نقش Seed دارد. |
| آیا Access Level داریم؟ | فیلد `access_level` TINYINT هست. پیش‌فرض `1`. SQL: 1 عضو، 2 پلاس، 3 مربی، 4 مدیر. |
| آیا Level در Permission Check استفاده می‌شود؟ | **خیر.** `has_perm` فقط `store_role_permissions($u['role_key'])` را می‌بیند. `access_level` فقط نمایش پروفایل است. |
| آیا می‌توان User A را Level 1 و B را Level 2 کرد با امکانات متفاوت از UI؟ | **UI IMPLEMENTED نیست.** با UPDATE دستی DB روی `role_key` می‌توان (اگر Runtime را به SQL وصل کنید هنوز نه — Runtime آرایه ثابت است). الان member/plus/coach در PHP **یکسان**اند. فقط `admin` دو مجوز اضافه دارد. |
| آیا Admin می‌تواند Level را عوض کند؟ | پنل ادمین IMPLEMENTED نیست. |
| آیا Permissionها Database-driven در Runtime هستند؟ | جدول هست. Runtime: **خیر** (آرایه PHP). |
| آیا برای هر Role در UI مجموعه Permission تعریف می‌شود؟ | IMPLEMENTED نیست. |

## Runtime واقعی (`store_role_permissions`)

```
member, plus, coach:
  VIEW_DASHBOARD, CREATE_PLAN, EDIT_PLAN, RECORD_PERFORMANCE,
  VIEW_REPORT, VIEW_HISTORY, MANAGE_ACTIVITY_LIBRARY

admin: همان + MANAGE_USERS, ADMIN_ACCESS
```

صفحات:

| مجوز | کجا require می‌شود |
|---|---|
| VIEW_DASHBOARD | dashboard.php |
| EDIT_PLAN | plan.php |
| RECORD_PERFORMANCE | today.php |
| MANAGE_ACTIVITY_LIBRARY | library.php |
| VIEW_REPORT | reports.php |
| VIEW_HISTORY | period.php |
| CREATE_PLAN | **هیچ صفحه** |
| MANAGE_USERS / ADMIN_ACCESS | **هیچ صفحه** |

نتیجه عملی V1: هر عضو ثبت‌نام‌شده به کتابخانه، برنامه، عملکرد و گزارش دسترسی دارد.

## تفکیک آمادگی

| لایه | Role | Level | Permission matrix | Admin UI |
|---|---|---|---|---|
| Database | Ready (Seed) | Ready (ستون) | Ready (جداول) | — |
| Backend PHP Runtime | نیمه‌کاره (ثابت) | ذخیره می‌شود، چک نمی‌شود | آرایه ثابت | نیست |
| UI تغییر نقش | نیست | نمایش در پروفایل | نیست | نیست |

**Backend Ready (جزئی) / Database Ready / UI Ready نیست / Future-ready برای ماتریس SQL**
