#!/usr/bin/env python3
"""JOMA Instagram post banner, 1080x1350 (4:5).

Two-stage build per brief:
  python build_banner.py          -> final flattened banner with Persian text layers
  python build_banner.py --base   -> text-free base image (whitespace reserved for type)
"""
import sys

from PIL import Image

from joma_lib import *

W, H = 1080, 1350
RIGHT_EDGE = W - 72          # RTL text / accent bar right margin
BASE_ONLY = "--base" in sys.argv


def build() -> Image.Image:
    base = gradient_bg(W, H).convert("RGBA")
    add_decor(base, ring_center=(300, 872), ring_r=268, blobs=[
        (150, 1080, 560, MINT, 150),          # soft mint wash behind the owl / phone
        (980, 760, 520, (219, 235, 244), 150),  # soft blue wash behind the dashboard
        (560, 250, 420, (236, 242, 235), 120),
    ])

    # -------- real product screenshots, presented as tilted device cards --------
    paste_rotated(base, screen_phone(206), (66, 556), angle=-8)
    paste_rotated(base, screen_dash(468), (480, 624), angle=6)

    # -------- the exact brand owl, preserved from drwithowl.jfif --------
    owl_img = owl(398)
    paste_shadowed(base, owl_img, (341 - owl_img.width // 2, 546))

    # -------- app icon (crop of the real in-app owl icon) --------
    icon = app_icon(84)
    base.alpha_composite(icon, (W - 60 - icon.width, 60))

    if BASE_ONLY:
        return base

    d = ImageDraw.Draw(base)

    # brand line, top row (ends just before the icon)
    f_brand = font("SemiBold", 30)
    draw_fa(d, (W - 60 - icon.width - 26, 102), BRAND_LINE, f_brand, SLATE, anchor="rm")

    # headline, two lines (second line in deep teal)
    f_head = font("Black", 82)
    y1 = 182
    draw_fa(d, (RIGHT_EDGE, y1), HEADLINE_1, f_head, INK)
    lh = 104
    y2 = y1 + lh
    draw_fa(d, (RIGHT_EDGE, y2), HEADLINE_2, f_head, TEAL_DARK)

    # short teal accent bar, right-aligned under the second headline line
    w2 = text_w(f_head, HEADLINE_2)
    by = y2 + 96
    d.rounded_rectangle((RIGHT_EDGE - w2, by, RIGHT_EDGE, by + 10), radius=5, fill=TEAL)

    # sub-headline, two lines, auto-fit to the safe text column
    sub_lines = [SUB_1, SUB_2]
    sub_size = 36
    while sub_size >= 30:
        f_sub = font("Medium", sub_size)
        if all(text_w(f_sub, ln) <= RIGHT_EDGE - 72 for ln in sub_lines):
            break
        sub_size -= 1
    sy = by + 44
    for ln in sub_lines:
        draw_fa(d, (RIGHT_EDGE, sy), ln, f_sub, SLATE)
        sy += sub_size + 16

    # CTA pill + URL
    f_cta = font("Bold", 40)
    cta_pill(d, cx=W // 2, cy=1112, fnt=f_cta)
    f_url = font("SemiBold", 37)
    d.text((W // 2, 1232), URL, font=f_url, fill=INK, anchor="ma")

    return base


if __name__ == "__main__":
    img = build().convert("RGB")
    assert img.size == (W, H), img.size
    name = "base_banner_1080x1350.png" if BASE_ONLY else "JOMA_banner_1080x1350.png"
    img.save(OUT / name, quality=95)
    print("saved", OUT / name, img.size)
