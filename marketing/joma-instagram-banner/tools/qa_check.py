#!/usr/bin/env python3
"""Deterministic acceptance checks for the JOMA banner package.

Run from the tools/ directory with the project venv active:
    python qa_check.py
Exits non-zero on any failure.
"""
import sys
from pathlib import Path

import numpy as np
from PIL import Image

sys.path.insert(0, str(Path(__file__).resolve().parent))
from joma_lib import (OUT, ASSETS, SCREENS, HEADLINE_1, HEADLINE_2, SUB_1, SUB_2,
                      BRAND_LINE, CTA, URL)

failures = []


def check(cond, label):
    print(("PASS" if cond else "FAIL"), "-", label)
    if not cond:
        failures.append(label)


# 1. exact output dimensions
for name, size in [("JOMA_banner_1080x1350.png", (1080, 1350)),
                   ("JOMA_story_1080x1920.png", (1080, 1920)),
                   ("base_banner_1080x1350.png", (1080, 1350)),
                   ("base_story_1080x1920.png", (1080, 1920))]:
    p = OUT / name
    check(p.exists(), f"{name} exists")
    if p.exists():
        check(Image.open(p).size == size, f"{name} dimensions == {size}")

# 2. verbatim Persian copy (exact strings, including the ZWNJ in برنامه‌ریزی)
expected = [HEADLINE_1, HEADLINE_2, SUB_1, SUB_2, BRAND_LINE, CTA, URL]
for t in expected:
    check(bool(t), f"copy string non-empty: {t}")
check("‌" in SUB_1, "ZWNJ (نیم‌فاصله) present in برنامه‌ریزی")
banned = ["٪", "٪", "تخفیف", "قیمت", "کاربر", "درصد", "هزار", "میلیون"]
joined = " ".join(expected)
for b in banned:
    check(b not in joined, f"no banned claim/stat token: {b!r}")

# 3. only the two approved screenshots are referenced by the build tools
tools = Path(__file__).resolve().parent
build_files = [p for p in tools.glob("*.py") if p.name != "qa_check.py"]
referenced = set()
for py in build_files:
    txt = py.read_text(encoding="utf-8")
    for f in SCREENS.iterdir():
        if f.name in txt:
            referenced.add(f.name)
check(referenced == {"2 (2).jpg", "21.jpg"}, f"only approved screenshots used: {sorted(referenced)}")
all_tool_text = "".join(p.read_text(encoding="utf-8") for p in build_files)
check("dr1.jpg" not in all_tool_text, "dr1.jpg is never referenced")

# 4. owl cut-out integrity: both eye lenses fully opaque, doctor-side edge clean
owl = Image.open(ASSETS / "owl_brand_cutout.png").convert("RGBA")
a = np.array(owl)[:, :, 3]
# crop origin of the 249x336 cut-out: source x=61, y=230 (see extract_owl.py printout)
GX, GY = 61, 230
import cv2
for fx, fy, nm in [(250, 335, "right"), (158, 378, "left")]:
    lc = (fx - GX, fy - GY)
    pm = np.zeros(a.shape, np.uint8)
    cv2.circle(pm, lc, 22, 255, -1)
    frac = (a[pm == 255] > 200).mean()
    check(abs(frac - 1.0) < 1e-9, f"{nm} eye lens fully opaque ({frac:.2f})")

bgr = cv2.cvtColor(np.array(owl)[:, :, :3], cv2.COLOR_RGB2BGR)
hsv = cv2.cvtColor(bgr, cv2.COLOR_BGR2HSV)
h, s, v = hsv[:, :, 0], hsv[:, :, 1], hsv[:, :, 2]
m = a > 40
ox, oy = np.indices(a.shape)[1] + GX, np.indices(a.shape)[0] + GY
brown = m & (h >= 3) & (h <= 22) & (s >= 55) & (v >= 40) & (v <= 170) & (ox >= 296)
white = m & (s < 60) & (v > 150) & (ox >= 302) & (oy < 325)
check(int(brown.sum()) == 0, f"no doctor brown edge pixels ({int(brown.sum())})")
check(int(white.sum()) == 0, f"no white temple pixels ({int(white.sum())})")

print()
if failures:
    print(f"{len(failures)} FAILURE(S)")
    sys.exit(1)
print("ALL QA CHECKS PASSED")
