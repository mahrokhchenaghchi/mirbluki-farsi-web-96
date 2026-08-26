# 13 — Mood

## پنج شاخص (کد `pages/mood.php`)

| کلید فرم POST | ستون ذخیره | عنوان UI | استیکر ۱→۵ |
|---|---|---|---|
| energy | energy | انرژی | 😴 😐 🙂 😄 ⚡ |
| general | general_mood | حال عمومی | 😞 😐 🙂 😊 🤩 |
| focus | focus | تمرکز | 🌫️ 😐 🙂 🎯 🧠 |
| sleep | sleep_quality | کیفیت خواب | 😫 😐 🙂 😴 ✨ |
| stress | stress | سطح استرس | 😌 🙂 😐 😟 😣 |

مقدار مجاز: عدد صحیح **۱ تا ۵** (هر پنج‌تا اجباری).

یادداشت: `note` اختیاری.

## ذخیره

- جدول `joma_mood_records` یا آرایه `moods`
- یکتا: user + jalali_date (`jalali_today()`)
- ذخیره مجدد همان روز: UPDATE
- نوع ستون‌ها TINYINT ؛ note TEXT

## محدودیت

- Gate: اگر لاگین باشد و حال امروز نباشد، به‌جز صفحات skip به mood می‌رود  
  skip: mood, logout, about, login, register, forgot, home
- چند رکورد در یک روز از مسیر عادی ساخته نمی‌شود

## گزارش

- تب خلق: اگر Mood باشد، میانگین هر شاخص (جمع/تعداد، دامنه ۱–۵) به‌صورت نوار عرض `avg/5*100` — این **نوار مقیاس نمره است نه Achievement**
- لیست روزها با پنج عدد و note
- تقویم: گل 🌸 اگر آن روز Mood دارد
- همبستگی Mood با Performance: IMPLEMENTED نیست

## نمودار خطی جدا per metric مثل React Recharts

در PHP: نوار میانگین + لیست. نمودار Recharts IMPLEMENTED نیست.
