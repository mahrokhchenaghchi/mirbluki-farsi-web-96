# پکیج بنر اینستاگرامی جوما (JOMA)

بنر تبلیغاتی فارسی برای اپ وب جوما با محوریت «از هدف تا عمل؛ مسیرت را ببین.» — همراه با جغد
برند واقعی، اسکرین‌شات‌های واقعی محصول و تایپوگرافی استاندارد فارسی.

## خروجی‌ها (`outputs/`)

| فایل | ابعاد | کاربرد |
|---|---|---|
| `JOMA_banner_1080x1350.png` | ۱۰۸۰×۱۳۵۰ (۴:۵) | بنر پست اینستاگرام — فایل اصلی انتشار |
| `JOMA_story_1080x1920.png` | ۱۰۸۰×۱۹۲۰ (۹:۱۶) | استوری، با چیدمان مستقل (نه نسخهٔ کشیده‌شده) |
| `base_banner_1080x1350.png` | ۱۰۸۰×۱۳۵۰ | تصویر پایهٔ بدون متن (مرحلهٔ اول گردش کار دو مرحله‌ای) |
| `base_story_1080x1920.png` | ۱۰۸۰×۱۹۲۰ | تصویر پایهٔ بدون متن استوری |
| `mobile_preview.png` | — | پیش‌نمایش کنارهم برای بازبینی |

## ساختار پوشه

```
joma-instagram-banner/
├── README.md
├── PRODUCTION_NOTE.md       # منابع واقعی، نحوهٔ حفظ جغد، صداقت داده
├── TEXT_LAYOUT_SPEC.md      # مختصات دقیق لایه‌های متن
├── FONT_NOTE.md             # مجوز فونت وزیرمتن (OFL 1.1)
├── assets/
│   ├── owl_brand_cutout.png       # کات‌اوت شفاف جغد برند (استخراج از drwithowl.jfif)
│   ├── app_icon.png               # آیکون برش‌خورده از اسکرین‌شات واقعی اپ
│   ├── source_screens/            # تنها اسکرین‌شات‌های مجاز: 2 (2).jpg و 21.jpg
│   └── fonts/                     # Vazirmatn TTF (وزن‌های موردنیاز)
├── tools/
│   ├── joma_lib.py          # کتابخانهٔ مشترک: پالت، رندر فارسی، ماکاپ، سایه
│   ├── extract_owl.py       # استخراج قابل‌بازتولید جغد از public/drwithowl.jfif
│   ├── build_banner.py      # ساخت بنر ۴:۵ (با فلگ --base برای نسخهٔ بدون متن)
│   ├── build_story.py       # ساخت استوری ۹:۱۶ (با فلگ --base)
│   ├── qa_check.py          # دروازه‌های کیفی خودکار
│   └── requirements.txt
└── outputs/
```

## بازتولید کامل

نیازمندی‌ها: پایتون ۳.۱۱+ و کتابخانه‌های `tools/requirements.txt`.

```bash
python -m venv .venv && source .venv/bin/activate
pip install -r marketing/joma-instagram-banner/tools/requirements.txt

cd marketing/joma-instagram-banner/tools
python extract_owl.py        # بازتولید assets/owl_brand_cutout.png از public/drwithowl.jfif
python build_banner.py       # outputs/JOMA_banner_1080x1350.png
python build_banner.py --base
python build_story.py        # outputs/JOMA_story_1080x1920.png
python build_story.py --base
python qa_check.py           # باید به ALL QA CHECKS PASSED برسد
```

`extract_owl.py` با مسیر نسبی از ریشهٔ مخزن، `public/drwithowl.jfif` را می‌خواند و خروجی را
در `assets/` می‌نویسد؛ بنابراین پکیج روی هر کلون تازهٔ مخزن قابل‌بازاجرا است.

## اصول طراحی رعایت‌شده

- جغد دقیقاً عروسک برند است؛ بدون بازطراحی یا تولید تصویری جدید (جزئیات در PRODUCTION_NOTE).
- فقط اسکرین‌شات‌های واقعی `2 (2).jpg` و `21.jpg`؛ بدون رابط/داده/آمار جعلی و بدون تحریف محتوا.
- پالت گرم کرم/سبز ملایم/آبی ملایم استخراج‌شده از خود محصول؛ بدون نئون، گلوهای سنگین یا کلیشه.
- متن فارسی با وزیرمتن، RTL و نیم‌فاصلهٔ صحیح؛ کپی‌رایتینگ دقیقاً مطابق متن مصوب، بدون قیمت/تخفیف/آمار.
- حاشیهٔ امن موبایل رعایت شده و نسخهٔ استوری ترکیب‌بندی مستقل دارد.
