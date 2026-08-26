# 16 — Seed Data واقعی (حدس زده نشده)

منبع: `joma/database/library_official.json` (با `joma.sql` هم‌خوان).  
ID عددی Auto Increment است؛ شناسه پایدار **Code** است.

TrackMode در Seed برابر DataType است.

| Code | Name | Category | DataType | Unit | Daily | Weekly | Monthly | Weight | Frequency | Sticker | Color | Group |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| ACT001 | ورزش | سلامت جسم | DURATION | UNIT_MIN | 30 | 150 | 600 | 5 | DAILY | 🏃 | #E85D75 | ACT_HEA |
| ACT002 | نوشیدن آب | سلامت جسم | NUMERIC | UNIT_GLASS | 6 | 42 | 180 | 4 | DAILY | 💧 | #4F9FC4 | ACT_HEA |
| ACT003 | پیاده‌روی | سلامت جسم | DURATION | UNIT_MIN | 20 | 100 | 400 | 4 | DAILY | 🚶 | #5AA469 | ACT_HEA |
| ACT004 | مصرف میوه | سلامت جسم | NUMERIC | UNIT_TIMES | 2 | 14 | 60 | 3 | DAILY | 🍎 | #D95D5D | ACT_HEA |
| ACT005 | خواب کافی | خواب و استراحت | DURATION | UNIT_HOUR | 7 | 49 | 210 | 5 | DAILY | 🌙 | #5964B4 | ACT_SLE |
| ACT006 | ساعت خواب منظم | خواب و استراحت | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | ⏰ | #6D75B8 | ACT_SLE |
| ACT007 | مدیتیشن | سلامت روان | DURATION | UNIT_MIN | 10 | 50 | 200 | 4 | DAILY | 🧘 | #7C6CE7 | ACT_MEN |
| ACT008 | تمرین تنفس | سلامت روان | DURATION | UNIT_MIN | 5 | 35 | 150 | 4 | DAILY | 🌬️ | #769FCD | ACT_MEN |
| ACT009 | ثبت حال روزانه | سلامت روان | RATING | UNIT_SCORE | 1 | 7 | 30 | 5 | DAILY | 📓 | #E9A23B | ACT_MEN |
| ACT010 | مطالعه بدون حواس‌پرتی | تمرکز و ذهن | DURATION | UNIT_MIN | 30 | 150 | 600 | 5 | DAILY | 🎯 | #4F7CAC | ACT_FOC |
| ACT011 | زمان بدون موبایل | تمرکز و ذهن | DURATION | UNIT_MIN | 30 | 150 | 600 | 4 | DAILY | 📵 | #5B8E7D | ACT_FOC |
| ACT012 | نوشتن برنامه روزانه | تمرکز و ذهن | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 📝 | #7C8C6C | ACT_FOC |
| ACT013 | مطالعه | یادگیری | DURATION | UNIT_MIN | 30 | 150 | 600 | 4 | DAILY | 📚 | #D99A2B | ACT_GRO |
| ACT014 | یادگیری مهارت جدید | رشد فردی | DURATION | UNIT_MIN | 20 | 100 | 400 | 4 | DAILY | 🛠️ | #5AA469 | ACT_GRO |
| ACT015 | نوشتن اهداف | رشد فردی | BOOLEAN | UNIT_NONE | 1 | 3 | 4 | 4 | WEEKLY | ⭐ | #8C6BB1 | ACT_GRO |
| ACT016 | گفت‌وگوی باکیفیت با همسر | روابط | DURATION | UNIT_MIN | 15 | 105 | 450 | 5 | DAILY | 💑 | #D66BA0 | ACT_REL |
| ACT017 | قدردانی از همسر | روابط | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🙏 | #C7D4A0 | ACT_REL |
| ACT018 | قرار دونفره | روابط | DURATION | UNIT_HOUR | 0 | 2 | 8 | 5 | WEEKLY | ☕ | #C06C84 | ACT_REL |
| ACT019 | تماس با خانواده | خانواده | DURATION | UNIT_MIN | 0 | 30 | 120 | 3 | WEEKLY | 📞 | #C7D4A0 | ACT_FAM |
| ACT020 | دیدار با خانواده | خانواده | DURATION | UNIT_HOUR | 0 | 2 | 8 | 4 | WEEKLY | 🏠 | #B97855 | ACT_FAM |
| ACT021 | نوشتن سه نکته مثبت | ذهن‌آگاهی | NUMERIC | UNIT_COUNT | 3 | 21 | 90 | 4 | DAILY | ✨ | #6D8B74 | ACT_MIN |
| ACT022 | تمرین شکرگزاری | ذهن‌آگاهی | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🌸 | #9A8C98 | ACT_MIN |
| ACT023 | زمان شخصی | مراقبت از خود | DURATION | UNIT_MIN | 20 | 140 | 600 | 4 | DAILY | 🛁 | #C08497 | ACT_SEL |
| ACT024 | انجام یک فعالیت لذت‌بخش | مراقبت از خود | BOOLEAN | UNIT_NONE | 1 | 3 | 12 | 3 | WEEKLY | 🎨 | #B565A7 | ACT_SEL |
| ACT025 | اولویت‌بندی کارهای روز | بهره‌وری | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | ✅ | #E09F3E | ACT_PRO |
| ACT026 | انجام مهم‌ترین کار روز | بهره‌وری | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 5 | DAILY | 🔥 | #D9A441 | ACT_PRO |
| ACT027 | مرور هفتگی | بهره‌وری | DURATION | UNIT_MIN | 0 | 30 | 120 | 4 | WEEKLY | 📅 | #4F7CAC | ACT_PRO |
| ACT028 | خواندن کتاب | یادگیری | DURATION | UNIT_MIN | 20 | 140 | 600 | 4 | DAILY | 📖 | #B07D3C | ACT_LEA |
| ACT029 | تکمیل یک فصل کتاب | یادگیری | NUMERIC | UNIT_COUNT | 0 | 1 | 4 | 3 | WEEKLY | 📗 | #A86F52 | ACT_LEA |
| ACT030 | خواندن یک کتاب کامل | یادگیری | NUMERIC | UNIT_COUNT | 0 | 0 | 1 | 5 | MONTHLY | 📘 | #D99A2B | ACT_MON |
| ACT031 | یک روز کامل بدون شبکه اجتماعی | سبک زندگی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 4 | MONTHLY | 📵 | #5B8E7D | ACT_MON |
| ACT032 | مرور ماه و ارزیابی شخصی | رشد فردی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 5 | MONTHLY | 🪞 | #7C6CE7 | ACT_MON |
| ACT033 | تعیین اهداف ماه آینده | رشد فردی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 5 | MONTHLY | 🧭 | #4F7CAC | ACT_MON |
| ACT034 | غذای سالم | سلامت جسم | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🥗 | #6A994E | ACT_HEA |
| ACT035 | کاهش مصرف قند | سلامت جسم | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🍬 | #BC6C25 | ACT_HEA |
| ACT036 | کشش بدن | سلامت جسم | DURATION | UNIT_MIN | 10 | 50 | 200 | 3 | DAILY | 🤸 | #76A5AF | ACT_HEA |
| ACT037 | نوشتن افکار و احساسات | سلامت روان | DURATION | UNIT_MIN | 10 | 50 | 200 | 4 | DAILY | 💭 | #8064A2 | ACT_MEN |
| ACT038 | فاصله گرفتن آگاهانه هنگام تنش | سلامت روان | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 5 | DAILY | 🌿 | #739E9B | ACT_MEN |
| ACT039 | گفت‌وگوی بدون موبایل | روابط | DURATION | UNIT_MIN | 15 | 105 | 450 | 4 | DAILY | 💬 | #C45B8A | ACT_REL |
| ACT040 | یک کار در هر لحظه | تمرکز و ذهن | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 4 | DAILY | 🎧 | #547AA5 | ACT_FOC |
| ACT041 | خاموش‌کردن صفحه‌نمایش قبل از خواب | خواب و استراحت | DURATION | UNIT_MIN | 30 | 210 | 900 | 4 | DAILY | 🖥️ | #5964B4 | ACT_SLE |
| ACT042 | مراقبت شخصی | مراقبت از خود | BOOLEAN | UNIT_NONE | 1 | 7 | 30 | 3 | DAILY | 🧴 | #C08497 | ACT_SEL |
| ACT043 | جمع‌بندی پایان روز | بهره‌وری | DURATION | UNIT_MIN | 5 | 35 | 150 | 3 | DAILY | 🌇 | #7A9E7E | ACT_PRO |
| ACT044 | بررسی یک رفتار و یادگیری از آن | رشد فردی | DURATION | UNIT_MIN | 10 | 70 | 300 | 4 | DAILY | 🔍 | #6B7AA1 | ACT_GRO |
| ACT045 | انجام یک تجربه جدید | رشد فردی | BOOLEAN | UNIT_NONE | 0 | 0 | 1 | 3 | MONTHLY | 🌈 | #D17A22 | ACT_MON |

Status همه: **ACTIVE**. CreatedBy در JSON نیست؛ در SQL `is_seed=1` و `user_id=NULL` سپس کپی per-user.

## Seed دیگر SQL

- نقش‌ها و مجوزها (پرونده ۱۵)
- settings: product_name=جوما ، support_contact خالی

## مشاغل (`jobs_list`)

دانش‌آموز، دانشجو، کارمند، مدیر، کارآفرین، پزشک، روانشناس، مهندس، معلم، وکیل، حسابدار، فروشنده، فریلنسر، خانه‌دار، بازنشسته، پژوهشگر، مشاغل آزاد، سایر
