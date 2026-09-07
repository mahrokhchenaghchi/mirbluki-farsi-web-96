#!/usr/bin/env python3
# Premium JOMA Reel v2 — reconstructed from real source + ADD-ONLY schema/couple packs.
# No JOMA PHP/schema changes. No fake Achievement %.
import os, subprocess
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageEnhance
import arabic_reshaper
from bidi.algorithm import get_display

W, H = 1080, 1920
ROOT = os.path.dirname(os.path.abspath(__file__))
ASSETS = os.path.join(ROOT, "assets")
FRAMES = os.path.join(ROOT, "v2_frames")
os.makedirs(FRAMES, exist_ok=True)
FFMPEG = os.path.expanduser(
    "~/.local/lib/python3.11/site-packages/imageio_ffmpeg/binaries/ffmpeg-linux-x86_64-v7.0.2"
)
FB = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
FR = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"
CREAM, FG, MUTED = (251, 247, 242), (45, 38, 68), (90, 82, 110)
PRIMARY, SOFT, WHITE = (124, 92, 191), (239, 231, 251), (255, 255, 255)
LINE, OK, PEACH = (226, 220, 232), (21, 115, 71), (255, 232, 216)
GOLD = (138, 90, 18)

def fa(s):
    return get_display(arabic_reshaper.reshape(str(s)))

def pn(n):
    return str(n).translate(str.maketrans("0123456789", "۰۱۲۳۴۵۶۷۸۹"))

def F(size, bold=True):
    return ImageFont.truetype(FB if bold else FR, size)

def tw(d, t, f):
    b = d.textbbox((0, 0), t, font=f)
    return b[2] - b[0], b[3] - b[1]

def heavy(d, text, y, size, fill, cx=W // 2):
    t = fa(text)
    f = F(size, True)
    w, h = tw(d, t, f)
    x = int(cx - w / 2)
    for dx, dy in ((0, 0), (1, 0), (0, 1), (1, 1), (-1, 0), (0, -1)):
        d.text((x + dx, y + dy), t, font=f, fill=fill)
    return h

def cover(path, size=(W, H)):
    im = Image.open(path).convert("RGB")
    tw_, th = size
    s = max(tw_ / im.width, th / im.height)
    im = im.resize((int(im.width * s) + 1, int(im.height * s) + 1), Image.Resampling.LANCZOS)
    x, y = (im.width - tw_) // 2, (im.height - th) // 2
    return im.crop((x, y, x + tw_, y + th))

def dusk():
    p = os.path.join(ASSETS, "bg-dusk.jpg")
    return cover(p) if os.path.isfile(p) else Image.new("RGB", (W, H), (42, 34, 60))

def cream():
    p = os.path.join(ASSETS, "bg-cream.jpg")
    if os.path.isfile(p):
        return cover(p)
    im = Image.new("RGB", (W, H), CREAM)
    d = ImageDraw.Draw(im, "RGBA")
    d.ellipse((-200, -240, 700, 520), fill=(255, 232, 216, 70))
    d.ellipse((500, -180, 1300, 560), fill=(196, 167, 231, 55))
    return im.convert("RGB")

def shadow_card(im, box, r=28, fill=WHITE):
    x0, y0, x1, y1 = box
    layer = Image.new("RGBA", im.size, (0, 0, 0, 0))
    sd = ImageDraw.Draw(layer)
    sd.rounded_rectangle((x0 + 6, y0 + 10, x1 + 6, y1 + 10), r, fill=(70, 50, 90, 40))
    layer = layer.filter(ImageFilter.GaussianBlur(8))
    base = im.convert("RGBA")
    base = Image.alpha_composite(base, layer)
    d = ImageDraw.Draw(base)
    d.rounded_rectangle(box, r, fill=fill)
    return base.convert("RGB")

def phone(ui, scale=0.74, y=250):
    bg = cream()
    pw, ph = ui.size
    fr = Image.new("RGBA", (pw + 40, ph + 80), (0, 0, 0, 0))
    d = ImageDraw.Draw(fr)
    d.rounded_rectangle((0, 0, pw + 39, ph + 79), 52, fill=(32, 26, 44, 255))
    d.rounded_rectangle((12, 12, pw + 27, ph + 67), 44, fill=CREAM + (255,))
    fr.paste(ui.convert("RGBA"), (20, 40))
    d.rounded_rectangle(((pw + 40) // 2 - 64, 16, (pw + 40) // 2 + 64, 28), 8, fill=(16, 14, 24, 255))
    nw, nh = int(fr.width * scale), int(fr.height * scale)
    fr = fr.resize((nw, nh), Image.Resampling.LANCZOS)
    x = (W - nw) // 2
    sh = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    ImageDraw.Draw(sh).rounded_rectangle((x + 18, y + 28, x + nw + 18, y + nh + 28), 54, fill=(40, 24, 60, 80))
    sh = sh.filter(ImageFilter.GaussianBlur(18))
    out = bg.convert("RGBA")
    out = Image.alpha_composite(out, sh)
    out.paste(fr, (x, y), fr)
    return out.convert("RGB")

def head(im, kicker, title):
    d = ImageDraw.Draw(im)
    heavy(d, kicker, 78, 26, PRIMARY)
    heavy(d, title, 118, 36, FG)
    return im

def ui_new(h=1520):
    return Image.new("RGB", (900, h), CREAM)

def ui_specialties():
    im = ui_new()
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("کتابخانه فعالیت‌ها"), font=F(36), fill=FG)
    d.text((48, 92), fa("تمرین‌های تخصصی، از بستهٔ رسمی جوما"), font=F(20, False), fill=MUTED)
    # schema
    d.rounded_rectangle((36, 160, 864, 780), 36, fill=WHITE)
    d.rounded_rectangle((36, 160, 864, 250), 36, fill=(124, 92, 191))
    d.rectangle((36, 210, 864, 250), fill=(124, 92, 191))
    d.text((64, 182), fa("طرحواره‌درمانی"), font=F(30), fill=WHITE)
    items = [
        "دفتر طرحواره امروز",
        "شکستن الگو؛ رفتار جدید",
        "تمرین طرحواره نقص و شرم",
    ]
    y = 280
    for t in items:
        d.ellipse((70, y + 10, 102, y + 42), fill=SOFT)
        d.text((124, y), fa(t), font=F(26), fill=FG)
        y += 90
    d.text((64, 680), fa("از بستهٔ ACT081 تا ACT107"), font=F(18, False), fill=MUTED)
    # couple
    d.rounded_rectangle((36, 820, 864, 1440), 36, fill=WHITE)
    d.rounded_rectangle((36, 820, 864, 910), 36, fill=(196, 120, 150))
    d.rectangle((36, 870, 864, 910), fill=(196, 120, 150))
    d.text((64, 842), fa("زوج‌درمانی"), font=F(30), fill=WHITE)
    items = [
        "تمرین گفت‌وگوی بدون دعوا",
        "تمرین قدردانی از همسر",
        "شناخت الگوهای تکراری رابطه",
    ]
    y = 940
    for t in items:
        d.ellipse((70, y + 10, 102, y + 42), fill=(255, 232, 240))
        d.text((124, y), fa(t), font=F(26), fill=FG)
        y += 90
    d.text((64, 1340), fa("از بستهٔ ACT046 تا ACT080"), font=F(18, False), fill=MUTED)
    return im

def ui_plan():
    im = ui_new()
    d = ImageDraw.Draw(im)
    d.text((48, 32), fa("برنامه شهریور ۱۴۰۵"), font=F(34), fill=FG)
    d.rounded_rectangle((48, 90, 260, 148), 18, fill=(229, 247, 238))
    d.text((68, 104), fa("در حال اجرا"), font=F(20), fill=OK)
    rows = [
        ((124, 92, 191), "دفتر طرحواره امروز", "طرحواره‌درمانی · روزانه", "۱۵ دقیقه · وزن ۵"),
        ((124, 92, 191), "شکستن الگو؛ رفتار جدید", "طرحواره‌درمانی · روزانه", "انجام/عدم انجام · وزن ۵"),
        ((196, 120, 150), "گفت‌وگوی بدون دعوا", "زوج‌درمانی · روزانه", "۱۵ دقیقه · وزن ۵"),
        ((196, 120, 150), "قدردانی از همسر", "زوج‌درمانی · روزانه", "انجام/عدم انجام · وزن ۴"),
        ((158, 140, 200), "نقص و شرم — تمرین هفتگی", "طرحواره‌درمانی · هفتگی", "۲۰ دقیقه · وزن ۴"),
    ]
    y = 180
    for col, name, meta, goal in rows:
        d.rounded_rectangle((36, y, 864, y + 230), 30, fill=WHITE)
        d.rounded_rectangle((36, y, 864, y + 78), 30, fill=col)
        d.rectangle((36, y + 40, 864, y + 78), fill=col)
        d.text((60, y + 96), fa(name), font=F(26), fill=FG)
        d.text((60, y + 140), fa(meta), font=F(18, False), fill=MUTED)
        d.text((60, y + 176), fa(goal), font=F(18, False), fill=MUTED)
        y += 250
    return im

def ui_today(day, schema_min, broke, talk, thanks, tag):
    im = ui_new(1400)
    d = ImageDraw.Draw(im)
    d.text((48, 28), fa("ثبت عملکرد"), font=F(34), fill=FG)
    d.text((48, 80), fa("شهریور " + pn(day) + "  ·  سارا"), font=F(20, False), fill=MUTED)
    rows = [
        ((124, 92, 191), "دفتر طرحواره", pn(schema_min) + " دقیقه"),
        ((124, 92, 191), "شکستن الگو", "انجام شد" if broke else "انجام نشد"),
        ((196, 120, 150), "گفت‌وگوی بدون دعوا", pn(talk) + " دقیقه"),
        ((196, 120, 150), "قدردانی از همسر", "انجام شد" if thanks else "انجام نشد"),
    ]
    y = 140
    for col, name, val in rows:
        d.rounded_rectangle((36, y, 864, y + 250), 30, fill=WHITE)
        d.rounded_rectangle((36, y, 864, y + 86), 30, fill=col)
        d.rectangle((36, y + 50, 864, y + 86), fill=col)
        d.text((60, y + 108), fa(name), font=F(26), fill=FG)
        d.text((60, y + 154), fa(val), font=F(22, False), fill=MUTED)
        d.rounded_rectangle((60, y + 198, 280, y + 232), 12, fill=(236, 248, 241))
        d.text((80, y + 202), fa("ثبت شد"), font=F(18), fill=OK)
        y += 270
    return im

def ui_kpis(ev="۲۱"):
    im = ui_new(1200)
    d = ImageDraw.Draw(im)
    d.text((48, 36), fa("گزارش دوره"), font=F(40), fill=FG)
    d.text((48, 96), fa("فقط دادهٔ سارا در شهریور ۱۴۰۵"), font=F(20, False), fill=MUTED)
    d.rounded_rectangle((36, 150, 864, 270), 24, fill=(255, 248, 235))
    d.text((56, 172), fa("فرمول Achievement تعریف نشده."), font=F(20, False), fill=GOLD)
    d.text((56, 210), fa("درصد ساختگی نشان داده نمی‌شود."), font=F(20, False), fill=GOLD)
    cards = [("رویدادها", ev), ("فعالیت‌ها", "۵"), ("روزهای خلق", "۵")]
    x = 36
    for k, v in cards:
        d.rounded_rectangle((x, 310, x + 268, 560), 28, fill=WHITE)
        d.text((x + 24, 340), fa(k), font=F(18, False), fill=MUTED)
        d.text((x + 24, 400), fa(v), font=F(56), fill=PRIMARY)
        x += 286
    d.text((48, 620), fa("۲۱ رویداد = ثبت واقعی همین دوره"), font=F(22, False), fill=MUTED)
    return im

def ui_cal():
    im = ui_new()
    d = ImageDraw.Draw(im)
    d.text((48, 32), fa("تقویم عملکرد"), font=F(36), fill=FG)
    d.rounded_rectangle((36, 120, 864, 1420), 32, fill=WHITE)
    for i, n in enumerate(["ش", "ی", "د", "س", "چ", "پ", "ج"]):
        d.text((86 + i * 108, 150), fa(n), font=F(18), fill=MUTED)
    hot = {9, 10, 11, 12, 13}
    n = 1
    for r in range(5):
        for c in range(7):
            if n > 31:
                break
            x0, y0 = 64 + c * 108, 210 + r * 220
            fill = (232, 220, 250) if n in hot else (246, 241, 234)
            d.rounded_rectangle((x0, y0, x0 + 94, y0 + 190), 16, fill=fill)
            d.text((x0 + 28, y0 + 24), fa(pn(n)), font=F(22), fill=FG)
            if n in hot:
                d.ellipse((x0 + 34, y0 + 100, x0 + 60, y0 + 126), fill=PRIMARY)
            n += 1
    return im

def ui_trend():
    im = ui_new(1200)
    d = ImageDraw.Draw(im)
    d.text((48, 32), fa("روند مقدار ثبت‌شده"), font=F(34), fill=FG)
    d.text((48, 88), fa("جمع مقدار روزهایی که رویداد داشته‌اند"), font=F(20, False), fill=MUTED)
    pts = [38, 47, 60, 18, 55]
    d.rounded_rectangle((36, 160, 864, 820), 32, fill=WHITE)
    mx, mn = max(pts), min(pts)
    coords = []
    for i, p in enumerate(pts):
        x = 130 + i * 155
        y = 720 - int((p - mn) / (mx - mn) * 420)
        coords.append((x, y))
        d.ellipse((x - 12, y - 12, x + 12, y + 12), fill=PRIMARY)
        d.text((x - 22, y - 52), fa(pn(p)), font=F(20), fill=FG)
    d.line(coords, fill=PRIMARY, width=8)
    for i, lab in enumerate(["۹", "۱۰", "۱۱", "۱۲", "۱۳"]):
        d.text((118 + i * 155, 750), fa(lab), font=F(18, False), fill=MUTED)
    d.text((48, 900), fa("روز ۱۲ ضعیف‌تر؛ روز ۱۳ برگشت."), font=F(24), fill=FG)
    return im

def scene_hook():
    im = dusk()
    d = ImageDraw.Draw(im)
    heavy(d, "می‌دانی باید تغییر کنی…", 700, 42, WHITE)
    heavy(d, "مسیرت را چطور ادامه می‌دهی؟", 780, 42, WHITE)
    return im

def scene_brand():
    im = dusk()
    d = ImageDraw.Draw(im)
    d.ellipse((W // 2 - 88, 420, W // 2 + 88, 596), fill=PRIMARY)
    t = fa("ج")
    w, _ = tw(d, t, F(78))
    d.text(((W - w) // 2, 448), t, font=F(78), fill=WHITE)
    heavy(d, "جوما", 640, 64, WHITE)
    heavy(d, "خودمدیریتی", 740, 32, (230, 224, 240))
    heavy(d, "با تمرین طرحواره و زوج‌درمانی", 800, 30, (230, 224, 240))
    return im

def scene_specialty_photo():
    p = os.path.join(ASSETS, "two-journals.jpg")
    im = cover(p) if os.path.isfile(p) else cream()
    im = ImageEnhance.Brightness(im).enhance(0.62)
    d = ImageDraw.Draw(im)
    heavy(d, "تمرین تخصصی", 620, 28, (236, 228, 245))
    heavy(d, "طرحواره‌درمانی", 700, 48, WHITE)
    heavy(d, "و زوج‌درمانی", 780, 48, WHITE)
    heavy(d, "از کتابخانهٔ رسمی جوما", 900, 26, (230, 224, 240))
    return im

def scene_compute():
    p = os.path.join(ASSETS, "data-glow.jpg")
    im = cover(p) if os.path.isfile(p) else dusk()
    im = ImageEnhance.Brightness(im).enhance(0.75)
    d = ImageDraw.Draw(im)
    heavy(d, "ثبت‌ها جمع می‌شوند", 780, 40, FG)
    heavy(d, "گزارش همان دوره ساخته می‌شود", 860, 28, MUTED)
    return im

def scene_end():
    im = dusk()
    d = ImageDraw.Draw(im)
    d.ellipse((W // 2 - 90, 400, W // 2 + 90, 580), fill=PRIMARY)
    t = fa("ج")
    w, _ = tw(d, t, F(80))
    d.text(((W - w) // 2, 428), t, font=F(80), fill=WHITE)
    heavy(d, "مسیرت را فقط شروع نکن؛", 640, 32, WHITE)
    heavy(d, "آن را ثبت کن و ببین.", 710, 32, WHITE)
    heavy(d, "جوما", 860, 56, WHITE)
    en = "JOMA"
    w, _ = tw(d, en, F(28))
    d.text(((W - w) // 2, 940), en, font=F(28), fill=(230, 224, 240))
    lab = fa("شروع استفاده")
    w, _ = tw(d, lab, F(28))
    x0 = (W - w) // 2
    d.rounded_rectangle((x0 - 52, 1180, x0 + w + 52, 1284), 30, fill=PRIMARY)
    d.text((x0, 1210), lab, font=F(28), fill=WHITE)
    heavy(d, "joma.mirbolouki.com", 1348, 24, (214, 204, 230))
    return im

def save(im, name):
    path = os.path.join(FRAMES, name)
    im.convert("RGB").resize((W, H), Image.Resampling.LANCZOS).save(path, quality=93)
    print("wrote", name)
    return path

def make_music(path, seconds):
    cmd = [
        FFMPEG, "-y",
        "-f", "lavfi", "-i", f"sine=frequency=196:duration={seconds}:sample_rate=44100",
        "-f", "lavfi", "-i", f"sine=frequency=246.94:duration={seconds}:sample_rate=44100",
        "-f", "lavfi", "-i", f"sine=frequency=293.66:duration={seconds}:sample_rate=44100",
        "-f", "lavfi", "-i", f"anoisesrc=color=brown:amplitude=0.04:duration={seconds}:sample_rate=44100",
        "-filter_complex",
        "[0]volume=0.09[a];[1]volume=0.06[b];[2]volume=0.045[c];[3]lowpass=f=350,volume=0.35[n];"
        "[a][b][c][n]amix=inputs=4:normalize=0,highpass=f=70,lowpass=f=1200,"
        "tremolo=f=0.18:d=0.35,afade=t=in:st=0:d=1.2,afade=t=out:st=%.2f:d=2.8" % (seconds - 2.8),
        "-c:a", "aac", "-b:a", "160k", path,
    ]
    subprocess.check_call(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)

def main():
    paths = []
    paths.append(save(scene_hook(), "01.jpg"))
    paths.append(save(scene_brand(), "02.jpg"))
    paths.append(save(scene_specialty_photo(), "03.jpg"))
    im = phone(ui_specialties(), 0.72, 210)
    head(im, "کتابخانه", "تمرین تخصصی، نه فقط عادت روزانه")
    paths.append(save(im, "04.jpg"))
    im = phone(ui_plan(), 0.68, 200)
    head(im, "برنامه سارا", "طرحواره + زوج در شهریور ۱۴۰۵")
    paths.append(save(im, "05.jpg"))
    im = phone(ui_today(9, 12, 1, 10, 1, "متوسط"), 0.70, 210)
    head(im, "۹ شهریور", "شروع ثبت")
    paths.append(save(im, "06.jpg"))
    im = phone(ui_today(11, 18, 1, 20, 1, "قوی"), 0.70, 210)
    head(im, "۱۱ شهریور", "ثبت قوی‌تر")
    paths.append(save(im, "07.jpg"))
    im = phone(ui_today(12, 8, 0, 5, 0, "ضعیف"), 0.70, 210)
    head(im, "۱۲ شهریور", "ثبت ضعیف‌تر")
    paths.append(save(im, "08.jpg"))
    im = phone(ui_today(13, 20, 1, 18, 1, "برگشت"), 0.70, 210)
    head(im, "۱۳ شهریور", "برگشت")
    paths.append(save(im, "09.jpg"))
    paths.append(save(scene_compute(), "10.jpg"))
    im = phone(ui_kpis("۲۱"), 0.78, 220)
    head(im, "گزارش", "از همان ثبت‌ها")
    paths.append(save(im, "11.jpg"))
    im = phone(ui_cal(), 0.70, 200)
    head(im, "تقویم", "پنج روز رویداد")
    paths.append(save(im, "12.jpg"))
    im = phone(ui_trend(), 0.76, 220)
    head(im, "روند", "جمع مقدار — نه درصد موفقیت")
    paths.append(save(im, "13.jpg"))
    paths.append(save(scene_end(), "14.jpg"))

    cover = Image.open(paths[0])
    cover.save(os.path.join(ROOT, "JOMA_Reel_Ad_cover.jpg"), quality=93)

    durs = [3.8, 3.4, 3.2, 4.0, 3.8, 2.8, 2.6, 2.6, 2.8, 2.8, 3.6, 3.4, 3.6, 4.6]
    fade = 0.42
    total = durs[0]
    offsets = []
    for i in range(1, len(durs)):
        off = total - fade
        offsets.append(off)
        total = off + durs[i]
    music = os.path.join(FRAMES, "bed.m4a")
    make_music(music, total + 0.5)
    args = [FFMPEG, "-y"]
    for i, p in enumerate(paths):
        args += ["-loop", "1", "-t", str(durs[i]), "-i", p]
    args += ["-i", music]
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
        "-filter_complex", ";".join(filt),
        "-map", "[vout]", "-map", f"{n}:a",
        "-c:v", "libx264", "-pix_fmt", "yuv420p", "-profile:v", "high", "-level", "4.1",
        "-crf", "17", "-preset", "medium", "-r", "30",
        "-c:a", "aac", "-b:a", "160k", "-shortest", "-movflags", "+faststart",
        outp,
    ]
    print("duration", total)
    subprocess.check_call(args)
    print("size", os.path.getsize(outp))

if __name__ == "__main__":
    main()
