#!/usr/bin/env python3
# JOMA Instagram ad compositor. Does not modify the JOMA app.
# Reconstructs product UI from live copy/CSS because a browser was unavailable.
import os, math, subprocess
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageEnhance
import arabic_reshaper
from bidi.algorithm import get_display

W, H = 1080, 1920
ROOT = os.path.dirname(os.path.abspath(__file__))
ASSETS = os.path.join(ROOT, "assets")
FRAMES = os.path.join(ROOT, "frames")
os.makedirs(FRAMES, exist_ok=True)
os.makedirs(ASSETS, exist_ok=True)

FFMPEG = os.path.expanduser(
    "~/.local/lib/python3.11/site-packages/imageio_ffmpeg/binaries/ffmpeg-linux-x86_64-v7.0.2"
)
FONT_B = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
FONT_R = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"

CREAM = (251, 247, 242)
FG = (58, 50, 80)
MUTED = (92, 86, 110)
PRIMARY = (124, 92, 191)
SOFT = (239, 231, 251)
PEACH = (255, 232, 216)
WHITE = (255, 255, 255)
LINE = (226, 220, 232)
OK = (21, 115, 71)

def fa(s):
    return get_display(arabic_reshaper.reshape(s))

def font(path, size):
    return ImageFont.truetype(path, size)

def fit_cover(im, size):
    tw, th = size
    im = im.convert("RGB")
    s = max(tw / im.width, th / im.height)
    nw, nh = int(im.width * s) + 1, int(im.height * s) + 1
    im = im.resize((nw, nh), Image.Resampling.LANCZOS)
    x = (nw - tw) // 2
    y = (nh - th) // 2
    return im.crop((x, y, x + tw, y + th))

def load_bg(name):
    p = os.path.join(ASSETS, name)
    return fit_cover(Image.open(p), (W, H))

def rounded_mask(size, r):
    m = Image.new("L", size, 0)
    d = ImageDraw.Draw(m)
    d.rounded_rectangle((0, 0, size[0] - 1, size[1] - 1), r, fill=255)
    return m

def round_paste(base, img, xy, r):
    img = img.convert("RGBA")
    m = rounded_mask(img.size, r)
    img.putalpha(m)
    base.paste(img, xy, img)

def text_w(draw, text, fnt):
    b = draw.textbbox((0, 0), text, font=fnt)
    return b[2] - b[0], b[3] - b[1]

def draw_rtl(draw, text, y, fnt, fill, cx=W // 2, max_w=900):
    t = fa(text)
    tw, th = text_w(draw, t, fnt)
    x = int(cx - tw / 2)
    draw.text((x, y), t, font=fnt, fill=fill)
    return th

def card(draw, box, fill=WHITE, r=36, outline=None):
    draw.rounded_rectangle(box, r, fill=fill, outline=outline, width=1)

def phone_chrome(ui, title="جوما"):
    """Wrap a UI bitmap in a simple device frame."""
    pw, ph = ui.size
    frame = Image.new("RGBA", (pw + 36, ph + 72), (0, 0, 0, 0))
    d = ImageDraw.Draw(frame)
    d.rounded_rectangle((0, 0, pw + 35, ph + 71), 48, fill=(28, 24, 38, 255))
    d.rounded_rectangle((10, 10, pw + 25, ph + 61), 40, fill=(251, 247, 242, 255))
    ui = ui.convert("RGBA")
    frame.paste(ui, (18, 36), ui if ui.mode == "RGBA" else None)
    # speaker
    d.rounded_rectangle(((pw + 36) // 2 - 60, 14, (pw + 36) // 2 + 60, 26), 8, fill=(18, 16, 28, 255))
    return frame

def ui_home():
    im = Image.new("RGB", (900, 1480), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 40), fa("جوما"), font=font(FONT_B, 28), fill=FG)
    d.text((720, 44), fa("شروع"), font=font(FONT_B, 22), fill=PRIMARY)
    d.text((48, 160), fa("محصول مستقل خودمدیریتی"), font=font(FONT_B, 26), fill=PRIMARY)
    d.text((48, 210), fa("جوما"), font=font(FONT_B, 86), fill=FG)
    d.text((48, 330), fa("برنامه‌ریزی → اجرا → اندازه‌گیری"), font=font(FONT_R, 26), fill=MUTED)
    d.text((48, 372), fa("فهمیدن → بهبود"), font=font(FONT_R, 26), fill=MUTED)
    d.rounded_rectangle((48, 450, 360, 530), 22, fill=PRIMARY)
    d.text((88, 470), fa("شروع استفاده"), font=font(FONT_B, 26), fill=WHITE)
    bits = [
        ((242, 198, 214), "حال امروز را با استیکر ثبت کن"),
        ((196, 167, 231), "کتابخانه ۴۵ فعالیت آماده"),
        ((184, 212, 240), "دوره‌های شمسی مستقل"),
        ((158, 216, 210), "گزارش فقط از داده واقعی"),
    ]
    y = 580
    for col, lab in bits:
        d.rounded_rectangle((48, y, 852, y + 120), 32, fill=WHITE)
        d.rounded_rectangle((72, y + 32, 140, y + 96), 18, fill=col)
        d.text((170, y + 38), fa(lab), font=font(FONT_B, 26), fill=FG)
        y += 140
    return im

def ui_mood():
    im = Image.new("RGB", (900, 1480), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 48), fa("حال امروز"), font=font(FONT_B, 48), fill=FG)
    d.text((48, 118), fa("هر پنج شاخص را انتخاب کنید"), font=font(FONT_R, 24), fill=MUTED)
    rows = [
        ("انرژی", 3),
        ("حال عمومی", 4),
        ("تمرکز", 3),
        ("کیفیت خواب", 4),
        ("سطح استرس", 1),
    ]
    y = 190
    for title, on in rows:
        d.rounded_rectangle((40, y, 860, y + 210), 36, fill=WHITE)
        d.text((70, y + 18), fa(title), font=font(FONT_B, 28), fill=FG)
        for i in range(5):
            x = 70 + i * 150
            if i + 1 == on:
                d.rounded_rectangle((x, y + 78, x + 120, y + 178), 24, fill=PRIMARY)
                num_fill = WHITE
            else:
                d.rounded_rectangle((x, y + 78, x + 120, y + 178), 24, fill=SOFT)
                num_fill = FG
            t = fa(str(i + 1))
            tw, _ = text_w(d, t, font(FONT_B, 36))
            d.text((x + (120 - tw) / 2, y + 108), t, font=font(FONT_B, 36), fill=num_fill)
        y += 230
    return im

def ui_library():
    im = Image.new("RGB", (900, 1480), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 40), fa("کتابخانه فعالیت‌ها"), font=font(FONT_B, 40), fill=FG)
    d.text((48, 100), fa("۴۵ فعالیت رسمی جدول جوما"), font=font(FONT_R, 22), fill=MUTED)
    d.rounded_rectangle((48, 150, 852, 230), 24, fill=WHITE, outline=LINE)
    d.text((80, 172), fa("همه دسته‌ها"), font=font(FONT_R, 24), fill=MUTED)
    acts = [
        ("#B8D4F0", "ورزش", "سلامت جسم"),
        ("#9ED8D2", "نوشیدن آب", "سلامت جسم"),
        ("#7C6CE7", "مدیتیشن", "سلامت روان"),
        ("#F2C6DE", "ژورنال‌نویسی", "رشد فردی"),
        ("#C4A7E7", "هدف روزانه", "بهره‌وری"),
        ("#FFE0B5", "خواب کافی", "خواب و استراحت"),
    ]
    y = 260
    col = 0
    for color, name, cat in acts:
        x = 48 if col == 0 else 474
        rgb = tuple(int(color[i : i + 2], 16) for i in (1, 3, 5))
        d.rounded_rectangle((x, y, x + 378, y + 340), 32, fill=WHITE)
        d.rounded_rectangle((x, y, x + 378, y + 120), 32, fill=rgb)
        d.rectangle((x, y + 60, x + 378, y + 120), fill=rgb)
        d.rounded_rectangle((x + 20, y + 28, x + 92, y + 100), 18, fill=WHITE)
        d.text((x + 24, y + 150), fa(name), font=font(FONT_B, 28), fill=FG)
        d.text((x + 24, y + 200), fa(cat), font=font(FONT_R, 22), fill=MUTED)
        col = 1 - col
        if col == 0:
            y += 370
    return im

def ui_plan():
    im = Image.new("RGB", (900, 1480), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 40), fa("برنامه من"), font=font(FONT_B, 44), fill=FG)
    d.rounded_rectangle((48, 110, 300, 168), 20, fill=(229, 247, 238))
    d.text((78, 124), fa("در حال اجرا"), font=font(FONT_B, 22), fill=OK)
    d.rounded_rectangle((40, 200, 860, 980), 36, fill=WHITE)
    d.text((70, 230), fa("دسته‌بندی"), font=font(FONT_B, 24), fill=FG)
    d.rounded_rectangle((70, 275, 830, 355), 20, fill=CREAM, outline=LINE)
    d.text((100, 297), fa("سلامت روان"), font=font(FONT_R, 24), fill=FG)
    d.text((70, 390), fa("فعالیت کتابخانه"), font=font(FONT_B, 24), fill=FG)
    d.rounded_rectangle((70, 435, 830, 515), 20, fill=CREAM, outline=LINE)
    d.text((100, 457), fa("مدیتیشن"), font=font(FONT_R, 24), fill=FG)
    d.text((70, 555), fa("تناوب این دوره"), font=font(FONT_B, 22), fill=FG)
    d.rounded_rectangle((70, 595, 430, 670), 18, fill=CREAM, outline=LINE)
    d.text((100, 616), fa("روزانه"), font=font(FONT_R, 22), fill=FG)
    d.rounded_rectangle((70, 720, 830, 810), 22, fill=PRIMARY)
    d.text((300, 745), fa("افزودن به برنامه این دوره"), font=font(FONT_B, 24), fill=WHITE)
    # activity card
    d.rounded_rectangle((40, 1040, 860, 1400), 36, fill=WHITE)
    d.rounded_rectangle((40, 1040, 860, 1160), 36, fill=(124, 108, 231))
    d.rectangle((40, 1100, 860, 1160), fill=(124, 108, 231))
    d.rounded_rectangle((70, 1070, 150, 1150), 18, fill=WHITE)
    d.ellipse((86, 1086, 134, 1134), fill=PRIMARY)
    d.text((70, 1190), fa("مدیتیشن"), font=font(FONT_B, 32), fill=FG)
    d.text((70, 1245), fa("سلامت روان  ·  روزانه"), font=font(FONT_R, 22), fill=MUTED)
    d.text((70, 1300), fa("هدف ۱۰ دقیقه  ·  وزن ۴"), font=font(FONT_R, 22), fill=MUTED)
    return im

def ui_today():
    im = Image.new("RGB", (900, 1480), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 40), fa("فعالیت‌های قابل ثبت"), font=font(FONT_B, 40), fill=FG)
    d.text((48, 100), fa("دوره شهریور ۱۴۰۵"), font=font(FONT_R, 22), fill=MUTED)
    d.rounded_rectangle((40, 170, 860, 720), 36, fill=WHITE)
    d.rounded_rectangle((40, 170, 860, 320), 36, fill=(158, 216, 210))
    d.rectangle((40, 250, 860, 320), fill=(158, 216, 210))
    d.rounded_rectangle((70, 200, 150, 280), 18, fill=WHITE)
    d.ellipse((86, 216, 134, 264), fill=(80, 170, 165))
    d.text((180, 210), fa("نوشیدن آب"), font=font(FONT_B, 30), fill=FG)
    d.text((180, 255), fa("سلامت جسم  ·  روزانه"), font=font(FONT_R, 20), fill=MUTED)
    d.text((70, 360), fa("مقدار ثبت‌شده: ۶ لیوان از هدف ۶ لیوان"), font=font(FONT_R, 24), fill=MUTED)
    d.rounded_rectangle((70, 430, 830, 510), 20, fill=(236, 248, 241))
    d.text((300, 452), fa("ثبت شد ✓  ۶ لیوان"), font=font(FONT_B, 26), fill=OK)
    d.rounded_rectangle((40, 760, 860, 1320), 36, fill=WHITE)
    d.rounded_rectangle((40, 760, 860, 910), 36, fill=(124, 108, 231))
    d.rectangle((40, 840, 860, 910), fill=(124, 108, 231))
    d.rounded_rectangle((70, 790, 150, 870), 18, fill=WHITE)
    d.ellipse((86, 806, 134, 854), fill=PRIMARY)
    d.text((180, 800), fa("مدیتیشن"), font=font(FONT_B, 30), fill=FG)
    d.text((70, 950), fa("مدت را وارد کنید"), font=font(FONT_R, 22), fill=MUTED)
    d.rounded_rectangle((70, 1000, 830, 1080), 20, fill=CREAM, outline=LINE)
    d.text((100, 1024), "10", font=font(FONT_B, 28), fill=FG)
    d.rounded_rectangle((70, 1140, 830, 1235), 22, fill=PRIMARY)
    d.text((330, 1168), fa("ثبت عملکرد"), font=font(FONT_B, 28), fill=WHITE)
    return im

def ui_reports():
    im = Image.new("RGB", (900, 1480), CREAM)
    d = ImageDraw.Draw(im)
    d.text((48, 40), fa("گزارش‌ها"), font=font(FONT_B, 48), fill=FG)
    d.text((48, 108), fa("فقط دادهٔ همان دوره انتخاب‌شده."), font=font(FONT_R, 22), fill=MUTED)
    stats = [("رویدادها", "۱۲"), ("فعالیت‌ها", "۴"), ("روزهای ثبت خلق", "۸")]
    x = 40
    for k, v in stats:
        d.rounded_rectangle((x, 170, x + 270, 340), 32, fill=WHITE)
        d.text((x + 28, 195), fa(k), font=font(FONT_R, 20), fill=MUTED)
        d.text((x + 28, 235), fa(v), font=font(FONT_B, 56), fill=FG)
        x += 285
    d.rounded_rectangle((40, 380, 860, 1420), 36, fill=WHITE)
    d.text((70, 410), fa("تقویم دوره"), font=font(FONT_B, 28), fill=FG)
    days = ["ش", "ی", "د", "س", "چ", "پ", "ج"]
    for i, name in enumerate(days):
        d.text((90 + i * 105, 470), fa(name), font=font(FONT_B, 20), fill=MUTED)
    # 5 weeks fake-but-not-kpi calendar cells — visual only, no success %
    hot = {3, 4, 10, 11, 12, 17, 18, 24, 25}
    n = 1
    for r in range(5):
        for c in range(7):
            x0 = 70 + c * 105
            y0 = 530 + r * 160
            fill = SOFT if n in hot else (246, 241, 234)
            d.rounded_rectangle((x0, y0, x0 + 92, y0 + 140), 18, fill=fill)
            d.text((x0 + 28, y0 + 20), fa(str(n)), font=font(FONT_B, 22), fill=FG)
            if n in hot:
                d.text((x0 + 20, y0 + 70), "●", font=font(FONT_R, 18), fill=PRIMARY)
                if n % 2 == 0:
                    d.ellipse((x0 + 52, y0 + 78, x0 + 72, y0 + 98), fill=(242, 198, 214))
            n += 1
            if n > 31:
                break
        if n > 31:
            break
    return im

def overlay_caption(im, kicker, title, sub=None, light=False):
    out = im.convert("RGB")
    d = ImageDraw.Draw(out)
    fill = FG if light else WHITE
    if kicker:
        draw_rtl(d, kicker, 96, font(FONT_B, 26), PRIMARY)
    if title:
        draw_rtl(d, title, 140, font(FONT_B, 36 if len(title) > 18 else 42), fill)
    if sub:
        draw_rtl(d, sub, 1688, font(FONT_R, 26), MUTED)
    return out

def scene_hook():
    bg = load_bg("bg-dusk.jpg")
    d = ImageDraw.Draw(bg)
    draw_rtl(d, "فهرست کارها", 720, font(FONT_B, 72), WHITE)
    draw_rtl(d, "کافی نیست.", 820, font(FONT_B, 72), WHITE)
    draw_rtl(d, "شروع می‌کنی. مسیر را گم می‌کنی.", 980, font(FONT_R, 32), (230, 224, 240))
    return bg

def scene_problem():
    bg = load_bg("scattered-notes.jpg")
    # darken
    bg = ImageEnhance.Brightness(bg).enhance(0.62)
    d = ImageDraw.Draw(bg)
    draw_rtl(d, "برنامه‌ها پراکنده می‌شوند", 620, font(FONT_B, 40), WHITE)
    draw_rtl(d, "حال امروز ثبت نمی‌شود", 720, font(FONT_B, 40), WHITE)
    draw_rtl(d, "پیشرفت را نمی‌بینی", 820, font(FONT_B, 40), WHITE)
    draw_rtl(d, "چون فهرست کارها کافی نیست.", 1020, font(FONT_R, 28), (235, 228, 242))
    return bg

def place_phone(bg, ui, y=300, scale=0.92):
    ph = phone_chrome(ui)
    nw = int(ph.width * scale)
    nh = int(ph.height * scale)
    ph = ph.resize((nw, nh), Image.Resampling.LANCZOS)
    x = (W - nw) // 2
    shadow = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    sd = ImageDraw.Draw(shadow)
    sd.rounded_rectangle((x + 18, y + 28, x + nw + 18, y + nh + 28), 52, fill=(30, 20, 50, 70))
    shadow = shadow.filter(ImageFilter.GaussianBlur(18))
    out = bg.convert("RGBA")
    out = Image.alpha_composite(out, shadow)
    out.paste(ph, (x, y), ph)
    return out.convert("RGB")

def scene_solution():
    bg = load_bg("bg-cream.jpg")
    d = ImageDraw.Draw(bg)
    draw_rtl(d, "جوما", 160, font(FONT_B, 64), FG)
    draw_rtl(d, "محصول مستقل خودمدیریتی", 250, font(FONT_B, 28), PRIMARY)
    ui = ui_home()
    bg = place_phone(bg, ui, y=340, scale=0.86)
    d = ImageDraw.Draw(bg)
    draw_rtl(d, "برنامه‌ریزی → اجرا → اندازه‌گیری → فهمیدن → بهبود", 1680, font(FONT_R, 24), MUTED)
    return bg

def scene_with_ui(ui, kicker, title):
    bg = load_bg("bg-cream.jpg")
    d = ImageDraw.Draw(bg)
    draw_rtl(d, kicker, 88, font(FONT_B, 24), PRIMARY)
    draw_rtl(d, title, 128, font(FONT_B, 34), FG)
    bg = place_phone(bg, ui, y=210, scale=0.80)
    return bg

def scene_cycle():
    bg = load_bg("path-cycle.jpg")
    bg = ImageEnhance.Brightness(bg).enhance(0.78)
    d = ImageDraw.Draw(bg)
    draw_rtl(d, "مسیر جوما", 240, font(FONT_B, 28), (236, 228, 245))
    steps = ["برنامه‌ریزی", "اجرا", "اندازه‌گیری", "فهمیدن", "بهبود"]
    y = 520
    for i, s in enumerate(steps):
        tw, _ = text_w(d, fa(s), font(FONT_B, 48))
        x = (W - tw) // 2
        d.rounded_rectangle((x - 40, y - 10, x + tw + 40, y + 78), 28, fill=(255, 255, 255, ))
        # rounded_rectangle on RGB ignores alpha; use a patch
        y += 130
    # redraw with patches
    bg2 = bg.convert("RGBA")
    y = 520
    for s in steps:
        patch = Image.new("RGBA", (W, 110), (0, 0, 0, 0))
        pd = ImageDraw.Draw(patch)
        t = fa(s)
        tw, _ = text_w(pd, t, font(FONT_B, 48))
        x = (W - tw) // 2
        pd.rounded_rectangle((x - 48, 8, x + tw + 48, 96), 30, fill=(255, 255, 255, 210))
        pd.text((x, 22), t, font=font(FONT_B, 48), fill=FG)
        bg2.paste(patch, (0, y - 16), patch)
        y += 128
    return bg2.convert("RGB")

def scene_end():
    bg = load_bg("bg-dusk.jpg")
    d = ImageDraw.Draw(bg)
    # logo circle
    d.ellipse((W // 2 - 90, 520, W // 2 + 90, 700), fill=PRIMARY)
    t = fa("ج")
    tw, th = text_w(d, t, font(FONT_B, 84))
    d.text(((W - tw) // 2, 545), t, font=font(FONT_B, 84), fill=WHITE)
    draw_rtl(d, "جوما  |  JOMA", 760, font(FONT_B, 52), WHITE)
    draw_rtl(d, "برنامه. اجرا. فهم.", 860, font(FONT_R, 32), (230, 224, 240))
    # CTA pill
    label = fa("شروع استفاده")
    tw, _ = text_w(d, label, font(FONT_B, 30))
    x0 = (W - tw) // 2
    d.rounded_rectangle((x0 - 48, 1180, x0 + tw + 48, 1280), 28, fill=PRIMARY)
    d.text((x0, 1206), label, font=font(FONT_B, 30), fill=WHITE)
    draw_rtl(d, "joma.mirbolouki.com", 1340, font(FONT_R, 28), (214, 204, 230))
    return bg

def save(im, name):
    path = os.path.join(FRAMES, name)
    im = im.resize((W, H), Image.Resampling.LANCZOS)
    im.save(path, quality=92)
    print("wrote", path)
    return path

def main():
    print("building frames...")
    paths = []
    paths.append(save(scene_hook(), "01-hook.jpg"))
    paths.append(save(scene_problem(), "02-problem.jpg"))
    paths.append(save(scene_solution(), "03-solution.jpg"))
    paths.append(save(scene_with_ui(ui_library(), "کتابخانه", "۴۵ فعالیت آماده"), "04-library.jpg"))
    paths.append(save(scene_with_ui(ui_plan(), "برنامه دوره", "دسته و هدف همین ماه"), "05-plan.jpg"))
    paths.append(save(scene_with_ui(ui_today(), "اجرا", "عملکرد واقعی را ثبت کن."), "06-today.jpg"))
    paths.append(save(scene_with_ui(ui_mood(), "حال امروز", "پنج شاخص، با استیکر."), "07-mood.jpg"))
    paths.append(save(scene_with_ui(ui_reports(), "گزارش", "فقط از دادهٔ واقعی — نه درصد ساختگی."), "08-reports.jpg"))
    paths.append(save(scene_cycle(), "09-cycle.jpg"))
    paths.append(save(scene_end(), "10-end.jpg"))

    cover = Image.open(paths[0]).copy()
    cover.save(os.path.join(ROOT, "joma-instagram-ad-cover.jpg"), quality=92)

    durs = [3.4, 3.6, 3.4, 2.9, 2.9, 2.9, 2.8, 3.1, 3.4, 4.2]
    fade = 0.45
    # build ffmpeg
    args = [FFMPEG, "-y"]
    for i, p in enumerate(paths):
        args += ["-loop", "1", "-t", str(durs[i]), "-i", p]
    total = durs[0]
    offsets = []
    for i in range(1, len(durs)):
        off = total - fade
        offsets.append(off)
        total = off + durs[i]
    # filter
    n = len(paths)
    filt = []
    for i in range(n):
        filt.append(f"[{i}:v]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,setsar=1,fps=30,format=yuv420p[v{i}]")
    prev = "v0"
    for i in range(1, n):
        out = f"x{i}"
        filt.append(
            f"[{prev}][v{i}]xfade=transition=fade:duration={fade}:offset={offsets[i-1]:.2f}[{out}]"
        )
        prev = out
    filt.append(f"[{prev}]format=yuv420p[vout]")
    args += [
        "-f", "lavfi", "-t", f"{total:.2f}", "-i", "anullsrc=channel_layout=stereo:sample_rate=44100",
        "-filter_complex", ";".join(filt),
        "-map", "[vout]", "-map", f"{n}:a",
        "-c:v", "libx264", "-pix_fmt", "yuv420p", "-profile:v", "high", "-level", "4.1",
        "-crf", "18", "-preset", "medium", "-r", "30",
        "-c:a", "aac", "-b:a", "128k", "-shortest",
        "-movflags", "+faststart",
        os.path.join(ROOT, "joma-instagram-ad-v1.mp4"),
    ]
    print("ffmpeg duration", total)
    subprocess.check_call(args)
    print("done", os.path.getsize(os.path.join(ROOT, "joma-instagram-ad-v1.mp4")))

if __name__ == "__main__":
    main()
