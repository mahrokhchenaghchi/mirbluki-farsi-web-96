#!/usr/bin/env python3
# JOMA Reel DEMO — reconstructed from live source copy/CSS/logic. No app code changes.
import os, subprocess
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageEnhance
import arabic_reshaper
from bidi.algorithm import get_display

W, H = 1080, 1920
ROOT = os.path.dirname(os.path.abspath(__file__))
ASSETS = os.path.join(ROOT, "assets")
FRAMES = os.path.join(ROOT, "demo_frames")
os.makedirs(FRAMES, exist_ok=True)
os.makedirs(ASSETS, exist_ok=True)
FFMPEG = os.path.expanduser(
    "~/.local/lib/python3.11/site-packages/imageio_ffmpeg/binaries/ffmpeg-linux-x86_64-v7.0.2"
)
FONT_B = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
FONT_R = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"
CREAM, FG, MUTED = (251, 247, 242), (58, 50, 80), (92, 86, 110)
PRIMARY, SOFT, WHITE = (124, 92, 191), (239, 231, 251), (255, 255, 255)
LINE, OK, PEACH = (226, 220, 232), (21, 115, 71), (255, 232, 216)

def fa(s):
    return get_display(arabic_reshaper.reshape(str(s)))

def pn(n):
    return str(n).translate(str.maketrans("0123456789", "۰۱۲۳۴۵۶۷۸۹"))

def fnt(bold, size):
    return ImageFont.truetype(FONT_B if bold else FONT_R, size)

def tw(d, t, f):
    b = d.textbbox((0, 0), t, font=f)
    return b[2] - b[0], b[3] - b[1]

def rtl(d, text, y, f, fill, cx=W // 2):
    t = fa(text)
    w, h = tw(d, t, f)
    d.text((int(cx - w / 2), y), t, font=f, fill=fill)
    return h

def cream_bg():
    im = Image.new("RGB", (W, H), CREAM)
    px = im.load()
    for y in range(H):
        for x in range(W):
            u, v = x / W, y / H
            r = int(251 + 4 * (1 - v) * u)
            g = int(247 - 8 * u * (1 - v) + 4 * v)
            b = int(242 + 18 * (1 - v) * (1 - u) - 6 * v)
            px[x, y] = (min(255, r), min(255, max(220, g)), min(255, max(220, b)))
    return im

def dusk_bg():
    p = os.path.join(ASSETS, "bg-dusk.jpg")
    if os.path.isfile(p):
        im = Image.open(p).convert("RGB")
        s = max(W / im.width, H / im.height)
        im = im.resize((int(im.width * s) + 1, int(im.height * s) + 1), Image.Resampling.LANCZOS)
        x, y = (im.width - W) // 2, (im.height - H) // 2
        return im.crop((x, y, x + W, y + H))
    im = Image.new("RGB", (W, H), (40, 32, 58))
    return im

def phone(ui, scale=0.78, y=240):
    bg = cream_bg()
    pw, ph = ui.size
    frame = Image.new("RGBA", (pw + 36, ph + 72), (0, 0, 0, 0))
    d = ImageDraw.Draw(frame)
    d.rounded_rectangle((0, 0, pw + 35, ph + 71), 48, fill=(28, 24, 38, 255))
    d.rounded_rectangle((10, 10, pw + 25, ph + 61), 40, fill=CREAM + (255,))
    ui2 = ui.convert("RGBA")
    frame.paste(ui2, (18, 36))
    d.rounded_rectangle(((pw + 36) // 2 - 58, 14, (pw + 36) // 2 + 58, 26), 8, fill=(18, 16, 28, 255))
    nw, nh = int(frame.width * scale), int(frame.height * scale)
    frame = frame.resize((nw, nh), Image.Resampling.LANCZOS)
    x = (W - nw) // 2
    sh = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    ImageDraw.Draw(sh).rounded_rectangle((x + 16, y + 24, x + nw + 16, y + nh + 24), 48, fill=(30, 20, 50, 70))
    sh = sh.filter(ImageFilter.GaussianBlur(16))
    out = bg.convert("RGBA")
    out = Image.alpha_composite(out, sh)
    out.paste(frame, (x, y), frame)
    return out.convert("RGB")

def caption(im, kicker, title):
    d = ImageDraw.Draw(im)
    rtl(d, kicker, 88, fnt(True, 24), PRIMARY)
    rtl(d, title, 128, fnt(True, 34), FG)
    return im

def ui_base():
    return Image.new("RGB", (900, 1500), CREAM), None

def ui_register():
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 40), fa("ساخت حساب جوما"), font=fnt(True, 40), fill=FG)
    fields = [("نام", "سارا"), ("نام خانوادگی", "محمدی"), ("نام کاربری", "sara.demo"), ("ایمیل", "sara.demo@example.com")]
    y = 130
    for lab, val in fields:
        d.text((48, y), fa(lab), font=fnt(True, 22), fill=FG)
        d.rounded_rectangle((48, y + 36, 852, y + 110), 18, fill=WHITE, outline=LINE)
        d.text((70, y + 56), fa(val) if lab != "ایمیل" else val, font=fnt(False, 24), fill=FG)
        y += 150
    d.rounded_rectangle((48, y + 20, 852, y + 120), 22, fill=PRIMARY)
    t = fa("ساخت حساب")
    w, _ = tw(d, t, fnt(True, 28))
    d.text(((900 - w) // 2, y + 48), t, font=fnt(True, 28), fill=WHITE)
    return im

def ui_login():
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 80), fa("ورود به جوما"), font=fnt(True, 48), fill=FG)
    d.text((48, 160), fa("حساب محلی سارا"), font=fnt(False, 24), fill=MUTED)
    d.text((48, 280), fa("نام کاربری یا ایمیل"), font=fnt(True, 22), fill=FG)
    d.rounded_rectangle((48, 320, 852, 400), 18, fill=WHITE, outline=LINE)
    d.text((70, 342), "sara.demo", font=fnt(False, 24), fill=FG)
    d.text((48, 440), fa("رمز عبور"), font=fnt(True, 22), fill=FG)
    d.rounded_rectangle((48, 480, 852, 560), 18, fill=WHITE, outline=LINE)
    d.text((70, 500), "••••••••", font=fnt(True, 24), fill=FG)
    d.rounded_rectangle((48, 640, 852, 740), 22, fill=PRIMARY)
    t = fa("ورود")
    w, _ = tw(d, t, fnt(True, 28))
    d.text(((900 - w) // 2, 668), t, font=fnt(True, 28), fill=WHITE)
    return im

def ui_plan(status="پیش‌نویس"):
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("برنامه شهریور ۱۴۰۵"), font=fnt(True, 36), fill=FG)
    badge = (229, 247, 238) if status == "در حال اجرا" else ((255, 241, 214) if status == "آماده‌سازی" else (241, 240, 244))
    bfill = OK if status == "در حال اجرا" else ((138, 90, 18) if status == "آماده‌سازی" else MUTED)
    d.rounded_rectangle((48, 96, 280, 154), 18, fill=badge)
    d.text((68, 110), fa(status), font=fnt(True, 20), fill=bfill)
    acts = [
        ((184, 212, 240), "نوشیدن آب", "سلامت جسم · روزانه", "هدف ۶ لیوان · وزن ۴"),
        ((158, 216, 210), "پیاده‌روی", "سلامت جسم · روزانه", "هدف ۲۰ دقیقه · وزن ۴"),
        ((124, 108, 231), "مدیتیشن", "سلامت روان · روزانه", "هدف ۱۰ دقیقه · وزن ۴"),
        ((196, 167, 231), "نوشتن افکار و احساسات", "سلامت روان · روزانه", "هدف ۱۰ دقیقه · وزن ۴"),
    ]
    y = 190
    for col, name, meta, goal in acts:
        d.rounded_rectangle((40, y, 860, y + 250), 32, fill=WHITE)
        d.rounded_rectangle((40, y, 860, y + 88), 32, fill=col)
        d.rectangle((40, y + 44, 860, y + 88), fill=col)
        d.ellipse((70, y + 22, 126, y + 78), fill=WHITE)
        d.text((70, y + 108), fa(name), font=fnt(True, 28), fill=FG)
        d.text((70, y + 158), fa(meta), font=fnt(False, 20), fill=MUTED)
        d.text((70, y + 198), fa(goal), font=fnt(False, 20), fill=MUTED)
        y += 270
    return im

def ui_today(day, water, walk, med, note, done=True):
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("فعالیت‌های قابل ثبت"), font=fnt(True, 34), fill=FG)
    d.text((48, 90), fa("شهریور " + pn(day) + " · سارا"), font=fnt(False, 22), fill=MUTED)
    rows = [
        ((80, 170, 165), "نوشیدن آب", pn(water) + " لیوان از ۶"),
        ((80, 170, 165), "پیاده‌روی", pn(walk) + " دقیقه از ۲۰"),
        ((124, 108, 231), "مدیتیشن", pn(med) + " دقیقه از ۱۰"),
        ((196, 167, 231), "نوشتن افکار", pn(note) + " دقیقه از ۱۰"),
    ]
    y = 150
    for col, name, val in rows:
        d.rounded_rectangle((40, y, 860, y + 250), 32, fill=WHITE)
        d.rounded_rectangle((40, y, 860, y + 90), 32, fill=col)
        d.rectangle((40, y + 50, 860, y + 90), fill=col)
        d.text((70, y + 110), fa(name), font=fnt(True, 26), fill=FG)
        d.text((70, y + 155), fa(val), font=fnt(False, 22), fill=MUTED)
        if done:
            d.rounded_rectangle((70, y + 195, 360, y + 235), 14, fill=(236, 248, 241))
            d.text((90, y + 202), fa("ثبت شد"), font=fnt(True, 20), fill=OK)
        y += 270
    return im

def ui_dash():
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("سلام سارا محمدی"), font=fnt(True, 36), fill=FG)
    d.text((48, 90), fa("۱۳ شهریور ۱۴۰۵"), font=fnt(False, 22), fill=MUTED)
    stats = [("فعالیت‌های برنامه", "۴"), ("قابل ثبت امروز", "۰"), ("رویدادهای دوره", "۲۰")]
    x = 40
    for k, v in stats:
        d.rounded_rectangle((x, 150, x + 270, 330), 28, fill=WHITE)
        d.text((x + 20, 175), fa(k), font=fnt(False, 18), fill=MUTED)
        d.text((x + 20, 220), fa(v), font=fnt(True, 48), fill=FG)
        x += 285
    acts = [
        ("نوشیدن آب", "۲۶ لیوان / ۶ لیوان"),
        ("پیاده‌روی", "۱۰۰ دقیقه / ۲۰ دقیقه"),
        ("مدیتیشن", "۵۰ دقیقه / ۱۰ دقیقه"),
        ("نوشتن افکار", "۵۰ دقیقه / ۱۰ دقیقه"),
    ]
    y = 370
    for name, val in acts:
        d.rounded_rectangle((40, y, 860, y + 150), 28, fill=WHITE)
        d.text((70, y + 28), fa(name), font=fnt(True, 26), fill=FG)
        d.text((70, y + 80), fa(val), font=fnt(False, 22), fill=MUTED)
        y += 170
    d.text((48, 1080), fa("جمع مقدار، طبق منطق فعلی جوما"), font=fnt(False, 18), fill=MUTED)
    d.text((48, 1116), fa("از رویدادهای ثبت‌شدهٔ همین دوره است."), font=fnt(False, 18), fill=MUTED)
    return im

def ui_report_overview():
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("گزارش‌ها"), font=fnt(True, 44), fill=FG)
    d.text((48, 100), fa("فقط دادهٔ همان دوره انتخاب‌شده."), font=fnt(False, 22), fill=MUTED)
    d.rounded_rectangle((40, 160, 860, 280), 24, fill=(255, 248, 235), outline=(243, 209, 156))
    d.text((64, 188), fa("فرمول Achievement تعریف نشده."), font=fnt(False, 22), fill=(122, 83, 16))
    d.text((64, 226), fa("درصد ساختگی نشان داده نمی‌شود."), font=fnt(False, 22), fill=(122, 83, 16))
    stats = [("رویدادها", "۲۰"), ("فعالیت‌ها", "۴"), ("روزهای ثبت خلق", "۵")]
    x = 40
    for k, v in stats:
        d.rounded_rectangle((x, 320, x + 270, 500), 28, fill=WHITE)
        d.text((x + 22, 350), fa(k), font=fnt(False, 20), fill=MUTED)
        d.text((x + 22, 400), fa(v), font=fnt(True, 52), fill=FG)
        x += 285
    d.text((48, 560), fa("این اعداد از ۲۰ ثبت سارا در شهریور است."), font=fnt(False, 22), fill=MUTED)
    return im

def ui_calendar():
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("تقویم عملکرد"), font=fnt(True, 40), fill=FG)
    d.text((48, 96), fa("نقطه = رویداد  ·  بدون درصد موفقیت"), font=fnt(False, 20), fill=MUTED)
    d.rounded_rectangle((40, 160, 860, 1420), 32, fill=WHITE)
    days = ["ش", "ی", "د", "س", "چ", "پ", "ج"]
    for i, n in enumerate(days):
        d.text((90 + i * 105, 190), fa(n), font=fnt(True, 20), fill=MUTED)
    hot = {9, 10, 11, 12, 13}
    n = 1
    for r in range(5):
        for c in range(7):
            if n > 31:
                break
            x0, y0 = 70 + c * 105, 250 + r * 210
            fill = SOFT if n in hot else (246, 241, 234)
            d.rounded_rectangle((x0, y0, x0 + 92, y0 + 180), 16, fill=fill)
            d.text((x0 + 24, y0 + 20), fa(pn(n)), font=fnt(True, 22), fill=FG)
            if n in hot:
                d.ellipse((x0 + 36, y0 + 90, x0 + 56, y0 + 110), fill=PRIMARY)
            n += 1
        if n > 31:
            break
    return im

def ui_trend():
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("روند"), font=fnt(True, 44), fill=FG)
    d.text((48, 100), fa("جمع مقدار روزهایی که رویداد داشته‌اند"), font=fnt(False, 22), fill=MUTED)
    d.text((48, 140), fa("نه درصد موفقیت."), font=fnt(False, 22), fill=MUTED)
    pts = [37, 46, 53, 23, 67]
    d.rounded_rectangle((40, 220, 860, 820), 32, fill=WHITE)
    maxp, minp = max(pts), min(pts)
    coords = []
    for i, p in enumerate(pts):
        x = 120 + i * 160
        y = 720 - int((p - minp) / (maxp - minp) * 380)
        coords.append((x, y))
        d.ellipse((x - 10, y - 10, x + 10, y + 10), fill=PRIMARY)
        d.text((x - 24, y - 48), fa(pn(p)), font=fnt(True, 20), fill=FG)
    d.line(coords, fill=PRIMARY, width=6)
    labels = ["۹", "۱۰", "۱۱", "۱۲", "۱۳"]
    for i, lab in enumerate(labels):
        d.text((108 + i * 160, 750), fa(lab), font=fnt(False, 20), fill=MUTED)
    d.text((48, 900), fa("روز ۱۲ ضعیف‌تر ثبت شد؛ روز ۱۳ بیشتر."), font=fnt(False, 24), fill=MUTED)
    return im

def ui_weight():
    im = Image.new("RGB", (900, 1500), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("وزن فعالیت‌ها"), font=fnt(True, 40), fill=FG)
    d.text((48, 100), fa("وزن ذخیره‌شدهٔ همین دوره"), font=fnt(False, 22), fill=MUTED)
    d.text((48, 140), fa("فرمول موفقیت کلی محاسبه نمی‌شود."), font=fnt(False, 22), fill=MUTED)
    rows = [("نوشیدن آب", 4), ("پیاده‌روی", 4), ("مدیتیشن", 4), ("نوشتن افکار", 4)]
    y = 220
    for name, wgt in rows:
        d.rounded_rectangle((40, y, 860, y + 160), 28, fill=WHITE)
        d.text((70, y + 24), fa(name), font=fnt(True, 26), fill=FG)
        d.text((620, y + 24), fa("وزن " + pn(wgt)), font=fnt(True, 24), fill=PRIMARY)
        d.rounded_rectangle((70, y + 90, 790, y + 112), 10, fill=SOFT)
        d.rounded_rectangle((70, y + 90, 70 + int(720 * wgt / 16), y + 112), 10, fill=PRIMARY)
        y += 190
    d.text((48, 1020), fa("جمع وزن این دوره: ۱۶"), font=fnt(True, 24), fill=FG)
    return im

def scene_hook():
    im = dusk_bg()
    d = ImageDraw.Draw(im)
    rtl(d, "برای خودت برنامه داری…", 680, fnt(True, 44), WHITE)
    rtl(d, "چقدر واقعاً ثبت می‌کنی؟", 760, fnt(True, 44), WHITE)
    rtl(d, "جوما", 980, fnt(True, 64), WHITE)
    rtl(d, "محصول مستقل خودمدیریتی", 1080, fnt(False, 28), (230, 224, 240))
    return im

def scene_end():
    im = dusk_bg()
    d = ImageDraw.Draw(im)
    d.ellipse((W // 2 - 90, 480, W // 2 + 90, 660), fill=PRIMARY)
    t = fa("ج")
    w, _ = tw(d, t, fnt(True, 80))
    d.text(((W - w) // 2, 510), t, font=fnt(True, 80), fill=WHITE)
    rtl(d, "جوما", 720, fnt(True, 56), WHITE)
    en = "JOMA"
    w, _ = tw(d, en, fnt(True, 32))
    d.text(((W - w) // 2, 800), en, font=fnt(True, 32), fill=(230, 224, 240))
    rtl(d, "جوما فقط برای برنامه‌ریزی نیست؛", 920, fnt(False, 26), (230, 224, 240))
    rtl(d, "عملکردت را تا گزارش همان دوره دنبال می‌کند.", 980, fnt(False, 26), (230, 224, 240))
    lab = fa("شروع استفاده")
    w, _ = tw(d, lab, fnt(True, 28))
    x0 = (W - w) // 2
    d.rounded_rectangle((x0 - 48, 1180, x0 + w + 48, 1280), 28, fill=PRIMARY)
    d.text((x0, 1208), lab, font=fnt(True, 28), fill=WHITE)
    rtl(d, "joma.mirbolouki.com", 1340, fnt(False, 26), (214, 204, 230))
    return im

def scene_cycle():
    im = dusk_bg()
    im = ImageEnhance.Brightness(im).enhance(0.85)
    d = ImageDraw.Draw(im)
    rtl(d, "مسیر واقعی جوما", 240, fnt(True, 28), (230, 224, 240))
    steps = ["برنامه‌ریزی", "اجرا", "اندازه‌گیری", "فهمیدن", "بهبود"]
    y = 480
    out = im.convert("RGBA")
    for s in steps:
        patch = Image.new("RGBA", (W, 110), (0, 0, 0, 0))
        pd = ImageDraw.Draw(patch)
        t = fa(s)
        w, _ = tw(pd, t, fnt(True, 44))
        x = (W - w) // 2
        pd.rounded_rectangle((x - 48, 8, x + w + 48, 92), 28, fill=(255, 255, 255, 220))
        pd.text((x, 22), t, font=fnt(True, 44), fill=FG)
        out.paste(patch, (0, y), patch)
        y += 120
    return out.convert("RGB")

def save(im, name):
    path = os.path.join(FRAMES, name)
    im.convert("RGB").resize((W, H), Image.Resampling.LANCZOS).save(path, quality=92)
    print("wrote", name)
    return path

def main():
    paths = []
    paths.append(save(scene_hook(), "01.jpg"))
    im = phone(ui_register(), 0.80, 220)
    caption(im, "سارا", "ثبت‌نام")
    paths.append(save(im, "02.jpg"))
    im = phone(ui_login(), 0.80, 220)
    caption(im, "سارا", "ورود به حساب خودش")
    paths.append(save(im, "03.jpg"))
    im = phone(ui_plan("پیش‌نویس"), 0.72, 200)
    caption(im, "شهریور ۱۴۰۵", "چهار فعالیت از کتابخانه")
    paths.append(save(im, "04.jpg"))
    im = phone(ui_plan("در حال اجرا"), 0.72, 200)
    caption(im, "پیش‌نویس → آماده‌سازی → اجرا", "دوره شروع شد")
    paths.append(save(im, "05.jpg"))
    im = phone(ui_today(9, 4, 15, 8, 10), 0.72, 200)
    caption(im, "۹ شهریور", "ثبت متوسط")
    paths.append(save(im, "06.jpg"))
    im = phone(ui_today(11, 6, 25, 12, 10), 0.72, 200)
    caption(im, "۱۱ شهریور", "ثبت بهتر")
    paths.append(save(im, "07.jpg"))
    im = phone(ui_today(12, 3, 10, 5, 5), 0.72, 200)
    caption(im, "۱۲ شهریور", "ثبت ضعیف‌تر")
    paths.append(save(im, "08.jpg"))
    im = phone(ui_today(13, 7, 30, 15, 15), 0.72, 200)
    caption(im, "۱۳ شهریور", "ثبت بیشتر")
    paths.append(save(im, "09.jpg"))
    im = phone(ui_dash(), 0.72, 200)
    caption(im, "داشبورد", "جمع همان رویدادها")
    paths.append(save(im, "10.jpg"))
    im = phone(ui_report_overview(), 0.78, 210)
    caption(im, "گزارش", "از دادهٔ سارا — نه درصد ساختگی")
    paths.append(save(im, "11.jpg"))
    im = phone(ui_calendar(), 0.72, 200)
    caption(im, "تقویم", "پنج روز ثبت")
    paths.append(save(im, "12.jpg"))
    im = phone(ui_trend(), 0.76, 210)
    caption(im, "روند", "جمع مقدار روزهای دارای رویداد")
    paths.append(save(im, "13.jpg"))
    im = phone(ui_weight(), 0.76, 210)
    caption(im, "وزن", "وزن دوره؛ موفقیت کلی محاسبه نمی‌شود")
    paths.append(save(im, "14.jpg"))
    paths.append(save(scene_cycle(), "15.jpg"))
    paths.append(save(scene_end(), "16.jpg"))

    cover = Image.open(paths[0])
    cover.save(os.path.join(ROOT, "JOMA_Reel_Ad_cover.jpg"), quality=92)

    durs = [3.6, 3.0, 2.6, 3.4, 3.0, 2.8, 2.6, 2.6, 2.8, 3.2, 3.6, 3.2, 3.4, 3.2, 3.4, 4.2]
    fade = 0.4
    args = [FFMPEG, "-y"]
    for p in paths:
        args += ["-loop", "1", "-t", str(durs[paths.index(p)]), "-i", p]
    # unique durs by index
    args = [FFMPEG, "-y"]
    for i, p in enumerate(paths):
        args += ["-loop", "1", "-t", str(durs[i]), "-i", p]
    total = durs[0]
    offsets = []
    for i in range(1, len(durs)):
        off = total - fade
        offsets.append(off)
        total = off + durs[i]
    n = len(paths)
    filt = []
    for i in range(n):
        filt.append(f"[{i}:v]scale=1080:1920,setsar=1,fps=30,format=yuv420p[v{i}]")
    prev = "v0"
    for i in range(1, n):
        out = f"x{i}"
        filt.append(f"[{prev}][v{i}]xfade=transition=fade:duration={fade}:offset={offsets[i-1]:.2f}[{out}]")
        prev = out
    filt.append(f"[{prev}]format=yuv420p[vout]")
    outp = os.path.join(ROOT, "JOMA_Reel_Ad.mp4")
    args += [
        "-f", "lavfi", "-t", f"{total:.2f}", "-i", "anullsrc=channel_layout=stereo:sample_rate=44100",
        "-filter_complex", ";".join(filt),
        "-map", "[vout]", "-map", f"{n}:a",
        "-c:v", "libx264", "-pix_fmt", "yuv420p", "-profile:v", "high", "-level", "4.1",
        "-crf", "18", "-preset", "medium", "-r", "30",
        "-c:a", "aac", "-b:a", "128k", "-shortest", "-movflags", "+faststart",
        outp,
    ]
    print("duration", total)
    subprocess.check_call(args)
    print("out", os.path.getsize(outp), outp)

if __name__ == "__main__":
    main()
