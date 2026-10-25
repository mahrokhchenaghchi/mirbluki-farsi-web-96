#!/usr/bin/env python3
"""Shared helpers for the JOMA Instagram banner build.

Everything is deterministic and file-based (Pillow + numpy):
  * brand palette sampled from the real product screenshots
  * correct Persian shaping (arabic-reshaper + python-bidi; no libraqm needed)
  * soft gradient background, subtle blobs / faint gold ring
  * rounded, rotated device-style screenshot mock-ups with soft shadows
"""
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageFilter, ImageFont
from bidi.algorithm import get_display
from arabic_reshaper import reshape

# ---------------------------------------------------------------- paths
TOOLS = Path(__file__).resolve().parent
PKG = TOOLS.parent
ASSETS = PKG / "assets"
FONTS = ASSETS / "fonts"
SCREENS = ASSETS / "source_screens"
OUT = PKG / "outputs"
OUT.mkdir(exist_ok=True)

# ---------------------------------------------------------------- palette (from real JOMA UI)
CREAM_TOP = (251, 252, 247)
CREAM_BOTTOM = (242, 247, 241)
TEAL = (44, 166, 167)
TEAL_DARK = (28, 120, 124)
BLUE_SOFT = (91, 158, 205)
INK = (31, 54, 46)
SLATE = (96, 118, 112)
MINT = (224, 243, 233)
GOLD = (214, 182, 120)
WHITE = (255, 255, 255)

# ---------------------------------------------------------------- verbatim Persian copy
HEADLINE_1 = "از هدف تا عمل؛"
HEADLINE_2 = "مسیرت را ببین."
SUB_1 = "جوما، همراه تو برای برنامه‌ریزی، پیگیری فعالیت‌ها"
SUB_2 = "و مشاهده روند پیشرفت."
BRAND_LINE = "جوما | با همراهی جغد دانا"
CTA = "جوما را امتحان کن"
URL = "joma.mirbolouki.com"


def fa(text: str) -> str:
    """Reshape + bidi so Persian renders with correct joins and RTL order."""
    return get_display(reshape(text))


def font(weight: str, size: int) -> ImageFont.FreeTypeFont:
    return ImageFont.truetype(str(FONTS / f"Vazirmatn-{weight}.ttf"), size)


def text_w(fnt: ImageFont.FreeTypeFont, text: str, shaped: bool = True) -> int:
    t = fa(text) if shaped else text
    box = fnt.getbbox(t)
    return box[2] - box[0]


def draw_fa(draw: ImageDraw.ImageDraw, xy, text: str, fnt, fill, anchor="ra"):
    """Draw Persian right-aligned by default (anchor 'ra' = right/ascender)."""
    draw.text(xy, fa(text), font=fnt, fill=fill, anchor=anchor)


# ---------------------------------------------------------------- background
def gradient_bg(w: int, h: int) -> Image.Image:
    top = np.array(CREAM_TOP, dtype=np.float32)
    bot = np.array(CREAM_BOTTOM, dtype=np.float32)
    t = np.linspace(0, 1, h, dtype=np.float32)[:, None, None]
    arr = (top[None, None, :] * (1 - t) + bot[None, None, :] * t)
    arr = np.repeat(arr, w, axis=1)
    return Image.fromarray(arr.astype(np.uint8), "RGB")


def radial_blob(size: int, color, alpha: int) -> Image.Image:
    """Soft radial colour blob on transparency."""
    y, x = np.mgrid[-1:1:complex(size), -1:1:complex(size)]
    r = np.sqrt(x * x + y * y)
    a = np.clip(1 - r, 0, 1) ** 1.6 * alpha
    arr = np.zeros((size, size, 4), dtype=np.uint8)
    arr[:, :, 0], arr[:, :, 1], arr[:, :, 2] = color
    arr[:, :, 3] = a.astype(np.uint8)
    return Image.fromarray(arr, "RGBA")


def add_decor(base: Image.Image, ring_center, ring_r: int, blobs):
    """blobs: list of (x, y, size, color, alpha)."""
    ov = Image.new("RGBA", base.size, (0, 0, 0, 0))
    for x, y, size, color, alpha in blobs:
        ov.alpha_composite(radial_blob(size, color, alpha), (x - size // 2, y - size // 2))
    d = ImageDraw.Draw(ov)
    x, y = ring_center
    d.ellipse((x - ring_r, y - ring_r, x + ring_r, y + ring_r),
              outline=GOLD + (55,), width=6)
    base.paste(Image.alpha_composite(base.convert("RGBA"), ov).convert("RGB"), (0, 0))


# ---------------------------------------------------------------- rounded mock-ups
def rounded(img: Image.Image, radius: int) -> Image.Image:
    img = img.convert("RGBA")
    mask = Image.new("L", img.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, img.size[0] - 1, img.size[1] - 1),
                                           radius=radius, fill=255)
    img.putalpha(mask)
    return img


def framed(src: Image.Image, width: int, frame_color, frame: int = 10, radius: int = 26) -> Image.Image:
    """Scale a screenshot to `width`, wrap it in a rounded device-like frame."""
    h = round(src.height * width / src.width)
    shot = src.resize((width, h), Image.LANCZOS).convert("RGBA")
    canvas = Image.new("RGBA", (width + 2 * frame, h + 2 * frame), frame_color + (255,))
    mask = Image.new("L", canvas.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, canvas.size[0] - 1, canvas.size[1] - 1),
                                           radius=radius + frame, fill=255)
    canvas.putalpha(mask)
    inner = rounded(shot, radius)
    canvas.alpha_composite(inner, (frame, frame))
    return canvas


def paste_rotated(base: Image.Image, rgbsrc: Image.Image, pos, angle: int,
                  shadow=(0, 38, 34, 70), blur=22, offset=(10, 16)):
    """Paste an RGBA image rotated around its centre, with a soft drop shadow.
    `pos` is the top-left of the rotated (expanded) bounding box."""
    rot = rgbsrc.rotate(angle, resample=Image.BICUBIC, expand=True)
    sh = Image.new("RGBA", rot.size, (0, 0, 0, 0))
    alpha = rot.split()[3].point(lambda p: int(p * (shadow[3] / 255)))
    solid = Image.new("RGBA", rot.size, shadow[:3] + (255,))
    sh.paste(solid, (0, 0), alpha)
    sh = sh.filter(ImageFilter.GaussianBlur(blur))
    base.alpha_composite(sh, (pos[0] + offset[0], pos[1] + offset[1]))
    base.alpha_composite(rot, pos)


def paste_shadowed(base: Image.Image, rgbsrc: Image.Image, pos,
                   shadow=(0, 38, 34, 60), blur=18, offset=(6, 12)):
    """Paste without rotation but with soft shadow (used for the owl cut-out)."""
    sh = Image.new("RGBA", rgbsrc.size, (0, 0, 0, 0))
    alpha = rgbsrc.split()[3].point(lambda p: int(p * (shadow[3] / 255)))
    solid = Image.new("RGBA", rgbsrc.size, shadow[:3] + (255,))
    sh.paste(solid, (0, 0), alpha)
    sh = sh.filter(ImageFilter.GaussianBlur(blur))
    base.alpha_composite(sh, (pos[0] + offset[0], pos[1] + offset[1]))
    base.alpha_composite(rgbsrc, pos)


# ---------------------------------------------------------------- reusable UI bits
def cta_pill(draw: ImageDraw.ImageDraw, cx: int, cy: int, fnt, pad_x=48, pad_y=20):
    t = fa(CTA)
    w = text_w(fnt, CTA)
    h = fnt.getbbox(t)[3] - fnt.getbbox(t)[1]
    box = (cx - w // 2 - pad_x, cy - h // 2 - pad_y,
           cx + w // 2 + pad_x, cy + h // 2 + pad_y)
    draw.rounded_rectangle(box, radius=(box[3] - box[1]) // 2, fill=TEAL)
    draw.text((cx, cy), t, font=fnt, fill=WHITE, anchor="mm")
    return box


def app_icon(size: int) -> Image.Image:
    icon = Image.open(ASSETS / "app_icon.png").convert("RGBA").resize((size, size), Image.LANCZOS)
    return rounded(icon, radius=size // 5)


def owl(width: int) -> Image.Image:
    src = Image.open(ASSETS / "owl_brand_cutout.png").convert("RGBA")
    h = round(src.height * width / src.width)
    return src.resize((width, h), Image.LANCZOS)


def screen_phone(width: int) -> Image.Image:
    src = Image.open(SCREENS / "2 (2).jpg").convert("RGB")
    return framed(src, width, frame_color=TEAL_DARK, frame=9, radius=26)


def screen_dash(width: int) -> Image.Image:
    src = Image.open(SCREENS / "21.jpg").convert("RGB")
    return framed(src, width, frame_color=WHITE, frame=11, radius=24)
