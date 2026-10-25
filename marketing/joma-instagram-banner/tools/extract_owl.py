#!/usr/bin/env python3
"""Extract the exact brand owl from public/drwithowl.jfif (doctor + beige wall removed).
Robust strategy: the blue plush is colour-unambiguous, so it defines the silhouette; cream/gold
material is accepted only inside that silhouette or measured gold-part geometry. The beige wall
shares gold's hue/saturation, so geometry (not colour alone) is what excludes it. Reproducible.
"""
import cv2
import numpy as np
from pathlib import Path

TOOLS = Path(__file__).resolve().parent
PKG = TOOLS.parent
REPO = PKG.parents[1]
SRC = str(REPO / "public" / "drwithowl.jfif")
OUT = str(PKG / "assets" / "owl_brand_cutout.png")

img = cv2.imread(SRC)
H, W = img.shape[:2]
hsv = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)
h, s, v = hsv[:, :, 0], hsv[:, :, 1], hsv[:, :, 2]

# ============ 1. SILHOUETTE FROM BLUE PLUSH (wall/doctor never match blue) ============
blue = ((h >= 95) & (h <= 125) & (s > 60) & (v > 25)).astype(np.uint8) * 255
blue = cv2.morphologyEx(blue, cv2.MORPH_CLOSE,
                        cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (23, 23)))
n, lab, st, _ = cv2.connectedComponentsWithStats(blue, 8)
body = np.zeros_like(blue)
for i in range(1, n):
    if st[i, cv2.CC_STAT_AREA] > 1500:                      # head + body blue regions
        body[lab == i] = 255
# fill interior holes (eyes/face interrupt the blue)
ff = body.copy()
fm = np.zeros((H + 2, W + 2), np.uint8)
cv2.floodFill(ff, fm, (0, 0), 255)
body = cv2.bitwise_or(body, cv2.bitwise_not(ff))
blue_dil = cv2.dilate(body, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (7, 7)))
# distance of every pixel to the blue plush: gold tuft yarn hugs the blue (<=~18px),
# while the beige wall behind it sits >=30px away -> clean geometric separator
dist_blue = cv2.distanceTransform((1 - (body > 0)).astype(np.uint8), cv2.DIST_L2, 5)
# dilate so the silhouette wraps the white glasses frame and cream cheeks
inside = cv2.dilate(body, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (35, 35)))
# face ellipse covers both eyes / cream cheeks / glasses (a large non-blue bay the dilation can't reach)
face_ell = np.zeros((H, W), np.uint8)
cv2.ellipse(face_ell, (222, 362), (118, 100), 0, 0, 360, 255, -1)
# head ellipse covers the blue crown and the base of the top tuft
head_ell = np.zeros((H, W), np.uint8)
cv2.ellipse(head_ell, (214, 288), (108, 78), 0, 0, 360, 255, -1)
inside = cv2.bitwise_or(inside, cv2.bitwise_or(face_ell, head_ell))

# ============ 2. MEASURED GOLD-PART GEOMETRY (real owl parts only) ============
gold_geom = np.zeros((H, W), np.uint8)
cv2.ellipse(gold_geom, (230, 370), (48, 37), 0, 0, 360, 255, -1)      # beak x180-278 y335-406
cv2.circle(gold_geom, (268, 476), 30, 255, -1)                        # pendant medallion
cv2.rectangle(gold_geom, (198, 428), (302, 452), 255, -1)             # chain
cv2.ellipse(gold_geom, (162, 532), (54, 21), 0, 0, 360, 255, -1)      # left foot
cv2.ellipse(gold_geom, (260, 526), (34, 19), 0, 0, 360, 255, -1)      # right foot
inside = cv2.bitwise_or(inside, gold_geom)

# ---- gold ear tufts: warm yarn hugging the blue plush (dist<=22). The beige wall shares the
#      gold hue but sits >=30px from the blue, so the distance gate keeps yarn and drops wall.
warm = ((h >= 8) & (h <= 27) & (s > 70) & (v > 55)).astype(np.uint8) * 255
tuft_roi = np.zeros((H, W), np.uint8)
cv2.fillPoly(tuft_roi, [np.array([(50, 278), (72, 266), (118, 264), (158, 286),
                                  (154, 352), (102, 370), (52, 354)], np.int32)], 255)   # left tuft + wall pocket
cv2.fillPoly(tuft_roi, [np.array([(204, 258), (212, 198), (266, 194), (302, 216),
                                  (304, 252), (292, 272), (228, 270)], np.int32)], 255)  # top tuft + wall strip
near_blue = (dist_blue <= 22).astype(np.uint8) * 255
tuft_keep = cv2.bitwise_and(cv2.bitwise_and(warm, tuft_roi), near_blue)
inside = cv2.bitwise_or(inside, tuft_keep)

# generous ROI + clip below the feet
roi = np.zeros((H, W), np.uint8)
roi[200:547, 45:316] = 255
inside = cv2.bitwise_and(inside, roi)

# ============ 3. OWL MATERIAL, accepted only INSIDE the silhouette ============
blue_m = (h >= 95) & (h <= 125) & (s > 60) & (v > 25)
cream_m = (s < 78) & (v > 105)                                      # cream fur + white glasses
pink_m = ((h >= 160) | (h <= 4)) & (s > 90) & (v > 80)              # tongue
gold_m = (h >= 8) & (h <= 27) & (s > 70) & (v > 60)                 # gold, gated by geometry
material = (blue_m | cream_m | pink_m | (gold_m & (gold_geom > 0)) | (tuft_keep > 0)).astype(np.uint8) * 255
filled = cv2.bitwise_and(material, inside)
filled = cv2.morphologyEx(filled, cv2.MORPH_CLOSE,
                          cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (7, 7)))
# fill enclosed holes
ff = filled.copy()
fm = np.zeros((H + 2, W + 2), np.uint8)
cv2.floodFill(ff, fm, (0, 0), 255)
filled = cv2.bitwise_or(filled, cv2.bitwise_not(ff))

# ============ 4. PROTECTED FACE/GOLD for the colour cuts below ============
face = np.zeros((H, W), np.uint8)
cv2.ellipse(face, (222, 362), (116, 98), 0, 0, 360, 255, -1)
face[:, 296:] = 0
face[:, :120] = 0
face[:290, :] = 0
face[462:, :] = 0

# ============ 5. TOP-TUFT WALL / HAIR REMOVAL ============
tuft_block = np.zeros((H, W), np.uint8)
tuft_block[205:249, 228:293] = 255
wall_hair = ((s < 145) | (v < 55)).astype(np.uint8) * 255
filled = cv2.bitwise_and(filled, cv2.bitwise_not(cv2.bitwise_and(tuft_block, wall_hair)))

# ============ 6. HARD RIGHT BOUNDARY (grid-traced true owl edge) ============
bnd = [(205, 268), (235, 272), (250, 280), (283, 283), (292, 294), (300, 299),
       (306, 303), (312, 296), (320, 288), (330, 286), (345, 290), (380, 286),
       (435, 290), (448, 298), (505, 298), (512, 292), (546, 292)]
def bnd_x(y):
    if y <= bnd[0][0]:
        return bnd[0][1]
    if y >= bnd[-1][0]:
        return bnd[-1][1]
    for (y0, x0), (y1, x1) in zip(bnd, bnd[1:]):
        if y0 <= y <= y1:
            return int(round(x0 + (x1 - x0) * (y - y0) / (y1 - y0)))
    return bnd[-1][1]
hard = np.zeros((H, W), np.uint8)
hard[:, :] = 255
for y in range(205, 547):
    hard[y, bnd_x(y) + 1:] = 0
white_frame = ((s < 62) & (v > 168)).astype(np.uint8) * 255        # doctor frame hinge
frame_zone = np.zeros((H, W), np.uint8)
frame_zone[236:286, 268:296] = 255
hard = cv2.bitwise_and(hard, cv2.bitwise_not(cv2.bitwise_and(white_frame, frame_zone)))
cv2.circle(hard, (268, 476), 30, 255, -1)                           # pendant whole
foot_ell = np.zeros((H, W), np.uint8)
cv2.ellipse(foot_ell, (260, 526), (34, 19), 0, 0, 360, 255, -1)
gold_toe = ((h >= 9) & (h <= 26) & (s > 150) & (v > 85)).astype(np.uint8) * 255
hard = cv2.bitwise_or(hard, cv2.bitwise_and(foot_ell, gold_toe))
filled = cv2.bitwise_and(filled, hard)
filled[546:, :] = 0

# ============ 7. WARM BROWN DOCTOR SKIN/SHIRT BAND ============
brown = ((h >= 3) & (h <= 22) & (s >= 55) & (v >= 40) & (v <= 170)).astype(np.uint8) * 255
brown_band = np.zeros((H, W), np.uint8)
brown_band[283:546, 296:345] = 255
brown_cut = cv2.bitwise_and(brown, brown_band)
brown_cut = cv2.bitwise_and(brown_cut, cv2.bitwise_not(cv2.bitwise_or(gold_geom, face)))
filled = cv2.bitwise_and(filled, cv2.bitwise_not(brown_cut))
filled = cv2.bitwise_or(filled, cv2.bitwise_and(foot_ell, gold_toe))
filled[546:, :] = 0

# ============ 8. COMPONENT FILTER, HOLE FILL, PENDANT CORE, FEATHER ============
n4, lab4, st4, cen4 = cv2.connectedComponentsWithStats(filled, 8)
final = np.zeros_like(filled)
for i in range(1, n4):
    area = st4[i, cv2.CC_STAT_AREA]
    cx, cy = cen4[i]
    if area > 1000 or (area > 150 and cx < 300 and cy < 560):
        final[lab4 == i] = 255
ff = final.copy()
fm = np.zeros((H + 2, W + 2), np.uint8)
cv2.floodFill(ff, fm, (0, 0), 255)
final = cv2.bitwise_or(final, cv2.bitwise_not(ff))
pendant_core = np.zeros((H, W), np.uint8)
cv2.circle(pendant_core, (268, 476), 26, 255, -1)
final = cv2.bitwise_or(final, pendant_core)
# hole-fills can re-seal beige-wall pockets cupped inside the ear tufts. Colour alone can't split
# wall from gold yarn, so use two hand-traced polygons (from coordinate grids) over the exact wall
# pockets, gated to warm tan so blue plush / cream fur / dark eyes are never cut.
# The ear-tuft gold yarn and the beige wall behind it share a colour and interleave. Keep only a
# hand-traced tight polygon over the actual gold fibres; every tan pixel OUTSIDE it is wall and is
# removed. (Polygons traced on 10px coordinate grids of the source.)
yarn_keep = np.zeros((H, W), np.uint8)
cv2.fillPoly(yarn_keep, [np.array([(78, 288), (112, 286), (146, 302), (142, 348),
                                   (104, 360), (70, 344), (66, 312)], np.int32)], 255)   # left ear tuft fibres
cv2.fillPoly(yarn_keep, [np.array([(218, 204), (258, 200), (292, 216), (296, 244),
                                   (284, 262), (238, 262), (214, 240)], np.int32)], 255)  # crown tuft fibres
# Bright beige wall is both saturated (s>=100) AND bright (v>=150); the gold tuft yarn is darker
# (v<=~150) and, together with face/blue/gold geometry, sits inside protect_owl. So a brightness
# gate inside the tuft neighbourhoods removes wall without touching the owl.
wall_bright = ((h >= 8) & (h <= 27) & (s >= 100) & (v >= 150)).astype(np.uint8) * 255
wall_zones = np.zeros((H, W), np.uint8)
cv2.rectangle(wall_zones, (45, 255), (160, 370), 255, -1)     # whole left tuft + wall neighbourhood
cv2.rectangle(wall_zones, (200, 188), (300, 272), 255, -1)    # whole crown tuft + wall neighbourhood
# never cut owl parts: face ellipse (eyes/cream brows), blue plush, gold geometry, yarn polygons
blue_protect = cv2.dilate(body, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (15, 15)))
protect_owl = cv2.bitwise_or(cv2.bitwise_or(face_ell, blue_protect),
                             cv2.bitwise_or(gold_geom, yarn_keep))
wall_drop = cv2.bitwise_and(cv2.bitwise_and(wall_bright, wall_zones), cv2.bitwise_not(protect_owl))
final = cv2.bitwise_and(final, cv2.bitwise_not(wall_drop))
# Final wall-pocket cleanup: wall-coloured components inside the two exterior zones that are
# connected to the mask boundary are exterior wall and get removed. Gold tuft fibres and cream
# fur fully enclosed by the owl never touch the boundary, so they are preserved automatically;
# the face ellipse and blue-plush pixels are hard-protected regardless.
wall_warm = ((h >= 8) & (h <= 27) & (s >= 95) & (v >= 140)).astype(np.uint8)
ext_zones = np.zeros((H, W), np.uint8)
cv2.rectangle(ext_zones, (45, 205), (238, 380), 255, -1)   # left ear neighbourhood
cv2.rectangle(ext_zones, (195, 188), (272, 272), 255, -1)  # crown tuft neighbourhood
cand = cv2.bitwise_and(cv2.bitwise_and(wall_warm, ext_zones), (final > 0).astype(np.uint8))
m8 = (final > 0).astype(np.uint8)
boundary = cv2.dilate(m8, np.ones((3, 3), np.uint8)) - m8
seeds = cv2.bitwise_and(cand, cv2.dilate(boundary, np.ones((5, 5), np.uint8)))
nc, lc, _, _ = cv2.connectedComponentsWithStats(cand, 4)
blue_raw = ((h >= 95) & (h <= 125) & (s > 60)).astype(np.uint8) * 255
wall_parts = np.zeros((H, W), np.uint8)
for i in range(1, nc):
    if ((lc == i) & (seeds > 0)).any():
        wall_parts[lc == i] = 255
wall_parts = cv2.bitwise_and(wall_parts, cv2.bitwise_not(cv2.bitwise_or(face_ell, blue_raw)))
final = cv2.bitwise_and(final, cv2.bitwise_not(wall_parts))
# residual far-from-blue tan outside the owl is wall as well
wall_far = (((h >= 8) & (h <= 27) & (s > 78) & (v > 55) & (dist_blue > 22))
            .astype(np.uint8) * 255)
final = cv2.bitwise_and(final, cv2.bitwise_not(
    cv2.bitwise_and(cv2.bitwise_and(wall_far, wall_zones), cv2.bitwise_not(protect_owl))))
# doctor's dark hair beside the crown tuft — exterior only (blue plush and gold are protected)
dark_hair = ((v < 62) & ~((h >= 95) & (h <= 125))).astype(np.uint8) * 255
hair_zone = np.zeros((H, W), np.uint8)
cv2.rectangle(hair_zone, (250, 188), (305, 272), 255, -1)
final = cv2.bitwise_and(final, cv2.bitwise_not(
    cv2.bitwise_and(cv2.bitwise_and(dark_hair, hair_zone),
                    cv2.bitwise_not(cv2.bitwise_or(blue_protect, gold_geom)))))
# dark background sliver immediately to the right of the right foot. In this tight exterior
# rect the only real owl pixels are blue plush or the bright gold foot; everything else
# (brown/dark background, incl. soft fringe) is removed.
br_zone = np.zeros((H, W), np.uint8)
cv2.rectangle(br_zone, (271, 498), (309, 563), 255, -1)
blue_here = ((h >= 95) & (h <= 125) & (s > 60)).astype(np.uint8) * 255
gold_here = ((h >= 8) & (h <= 27) & (v >= 150)).astype(np.uint8) * 255
br_cut = cv2.bitwise_and(br_zone, cv2.bitwise_not(cv2.bitwise_or(blue_here, gold_here)))
final = cv2.bitwise_and(final, cv2.bitwise_not(br_cut))
alpha = cv2.GaussianBlur(final, (3, 3), 0)

rgba = cv2.cvtColor(img, cv2.COLOR_BGR2BGRA)
rgba[:, :, 3] = alpha
ys, xs = np.where(alpha > 20)
pad = 10
crop = rgba[max(ys.min() - pad, 0):min(ys.max() + pad, H),
            max(xs.min() - pad, 0):min(xs.max() + pad, W)]
cv2.imwrite(OUT, crop)
print("cutout", crop.shape[1], crop.shape[0], "maxx", xs.max(), "miny", ys.min(), "maxy", ys.max())
