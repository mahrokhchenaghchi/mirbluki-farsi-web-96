#!/usr/bin/env python3
"""JOMA Instagram story, 1080x1920 (9:16) — an independent re-composition, not a stretched banner.

  python build_story.py          -> final story with Persian text layers
  python build_story.py --base   -> text-free base image
"""
import sys

from PIL import Image

from joma_lib import *

W, H = 1080, 1920
RIGHT_EDGE = W - 94
BASE_ONLY = "--base" in sys.argv


def build() -> Image.Image:
    base = gradient_bg(W, H).convert("RGBA")
    add_decor(base, ring_center=(330, 1180), ring_r=360, blobs=[
        (140, 1330, 760, MINT, 150),
        (1020, 1150, 620, (219, 235, 244), 150),
        (560, 330, 480, (236, 242, 235), 120),
    ])

    # -------- real product screenshots --------
    # dashboard goes first (back-right); the phone menu is placed last, in front of the owl
    paste_rotated(base, screen_dash(440), (520, 1150), angle=7)

    # -------- exact brand owl (fully inside the safe area, no edge clipping) --------
    owl_img = owl(520)
    paste_shadowed(base, owl_img, (40, 860))

    # phone menu screen, front-left, overlapping the owl's wing like a held device
    paste_rotated(base, screen_phone(200), (24, 1090), angle=-9, blur=16)

    # -------- app icon --------
    icon = app_icon(88)
    base.alpha_composite(icon, (W - 104 - icon.width, 104))

    if BASE_ONLY:
        return base

    d = ImageDraw.Draw(base)

    f_brand = font("SemiBold", 32)
    draw_fa(d, (W - 104 - icon.width - 28, 148), BRAND_LINE, f_brand, SLATE, anchor="rm")

    # headline
    f_head = font("Black", 94)
    y1 = 240
    draw_fa(d, (RIGHT_EDGE, y1), HEADLINE_1, f_head, INK)
    y2 = y1 + 118
    draw_fa(d, (RIGHT_EDGE, y2), HEADLINE_2, f_head, TEAL_DARK)

    # accent bar with a clear breathing gap under the second headline line
    w2 = text_w(f_head, HEADLINE_2)
    by = y2 + 134
    d.rounded_rectangle((RIGHT_EDGE - w2, by, RIGHT_EDGE, by + 11), radius=6, fill=TEAL)

    # sub-headline, auto-fit
    sub_lines = [SUB_1, SUB_2]
    sub_size = 40
    while sub_size >= 34:
        f_sub = font("Medium", sub_size)
        if all(text_w(f_sub, ln) <= RIGHT_EDGE - 80 for ln in sub_lines):
            break
        sub_size -= 1
    sy = by + 58
    for ln in sub_lines:
        draw_fa(d, (RIGHT_EDGE, sy), ln, f_sub, SLATE)
        sy += sub_size + 18

    # CTA + URL (kept clear of the phone card on the left)
    f_cta = font("Bold", 44)
    cta_pill(d, cx=W // 2 + 40, cy=1620, fnt=f_cta, pad_x=54, pad_y=22)
    f_url = font("SemiBold", 40)
    d.text((W // 2 + 40, 1750), URL, font=f_url, fill=INK, anchor="ma")

    return base


if __name__ == "__main__":
    img = build().convert("RGB")
    assert img.size == (W, H), img.size
    name = "base_story_1080x1920.png" if BASE_ONLY else "JOMA_story_1080x1920.png"
    img.save(OUT / name, quality=95)
    print("saved", OUT / name, img.size)
