#!/usr/bin/env python3
"""
Derive the 5drs brand assets and colour anchors from the supplied logo.

The source is a JPEG on a near-white ground with wide empty margins, so this
script crops to the artwork, knocks the background out to transparency and
writes the two PNGs the theme uses.

    python3 scripts/brand/extract-logo.py

Outputs (wp-content/themes/5drs/assets/images/):
    logo-lockup.png  "5Drs" + "5Doctors", used in the header and footer
    logo-full.png    the complete lockup incl. tagline, Customizer default

The printed colour table is the source of truth for the tokens in
src/css/tokens.css and theme.json.
"""

import colorsys
import sys
from collections import Counter
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "assets" / "brand" / "logo5drs.jpeg"
OUT = ROOT / "wp-content" / "themes" / "5drs" / "assets" / "images"

# Luminance at or below which a pixel is fully opaque artwork; between this and
# the background luminance the alpha ramps, which keeps the anti-aliased edges.
OPAQUE_AT = 240


def content_bbox(im, bg, tol=18):
    """Bounding box of everything that is not background."""
    px = im.load()
    w, h = im.size
    minx, miny, maxx, maxy = w, h, 0, 0
    for y in range(h):
        for x in range(w):
            p = px[x, y]
            if any(abs(p[i] - bg[i]) > tol for i in range(3)):
                minx, miny = min(minx, x), min(miny, y)
                maxx, maxy = max(maxx, x), max(maxy, y)
    return minx, miny, maxx + 1, maxy + 1


def row_bands(im, bg, gap=4):
    """Vertical bands of content, used to split the stacked lockup."""
    px = im.load()
    w, h = im.size
    rows = [
        any(sum(px[x, y]) / 3 < 235 for x in range(w))
        for y in range(h)
    ]
    bands, start = [], None
    for y, filled in enumerate(rows):
        if filled and start is None:
            start = y
        elif not filled and start is not None:
            if y - start > gap:
                bands.append((start, y))
            start = None
    if start is not None:
        bands.append((start, h))
    return bands


def knockout(im, bg_lum):
    """White ground to alpha, un-premultiplying so no white halo remains."""
    im = im.convert("RGB")
    out = Image.new("RGBA", im.size)
    src, dst = im.load(), out.load()
    w, h = im.size
    span = max(bg_lum - OPAQUE_AT, 1)
    for y in range(h):
        for x in range(w):
            r, g, b = src[x, y]
            lum = 0.299 * r + 0.587 * g + 0.114 * b
            a = 1.0 if lum <= OPAQUE_AT else max(0.0, (bg_lum - lum) / span)
            if a <= 0.0:
                dst[x, y] = (255, 255, 255, 0)
                continue
            if a < 1.0:
                # P = C*a + 255*(1-a)  ->  C = (P - 255*(1-a)) / a
                r, g, b = (
                    int(max(0, min(255, (c - 255 * (1 - a)) / a)))
                    for c in (r, g, b)
                )
            dst[x, y] = (r, g, b, int(round(a * 255)))
    return out


def srgb_lum(rgb):
    def lin(c):
        c /= 255
        return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4
    r, g, b = rgb
    return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b)


def contrast(fg, bg):
    a, b = srgb_lum(fg) + 0.05, srgb_lum(bg) + 0.05
    return round(max(a, b) / min(a, b), 2)


def sample_families(im):
    """Modal colour per hue family, ignoring greys and near-white."""
    families = {"navy": (180, 220), "green": (70, 130), "gold": (25, 60)}
    buckets = {k: Counter() for k in families}
    px = im.load()
    w, h = im.size
    for y in range(h):
        for x in range(w):
            r, g, b = px[x, y][:3]
            hue, light, sat = colorsys.rgb_to_hls(r / 255, g / 255, b / 255)
            if sat < 0.18 or not 0.08 < light < 0.9:
                continue
            deg = hue * 360
            for name, (lo, hi) in families.items():
                if lo <= deg <= hi:
                    buckets[name][(r, g, b)] += 1
    return {k: (v.most_common(1)[0] if v else None) for k, v in buckets.items()}


def main():
    if not SRC.exists():
        sys.exit(f"missing source logo: {SRC}")

    im = Image.open(SRC).convert("RGB")
    bg = im.getpixel((2, 2))
    bg_lum = 0.299 * bg[0] + 0.587 * bg[1] + 0.114 * bg[2]
    print(f"source      {SRC.name}  {im.size[0]}x{im.size[1]}  ground=#{bg[0]:02X}{bg[1]:02X}{bg[2]:02X}")

    box = content_bbox(im, bg)
    art = im.crop(box)
    print(f"artwork     crop={box}  {art.size[0]}x{art.size[1]}")

    bands = row_bands(art, bg)
    for i, (a, b) in enumerate(bands, 1):
        print(f"  band {i}    y {a}-{b}  h={b - a}")
    if len(bands) < 3:
        sys.exit("expected three bands (5Drs / 5Doctors / tagline)")

    OUT.mkdir(parents=True, exist_ok=True)

    # Header + footer mark: the two legible bands, tagline dropped because at
    # header size it renders ~3px tall. The theme prints it as real text.
    pad = 6
    lockup = art.crop((0, max(0, bands[0][0] - pad), art.size[0], bands[1][1] + pad))
    knockout(lockup, bg_lum).save(OUT / "logo-lockup.png", optimize=True)
    print(f"wrote       logo-lockup.png  {lockup.size[0]}x{lockup.size[1]}")

    full = art.crop((0, 0, art.size[0], art.size[1]))
    knockout(full, bg_lum).save(OUT / "logo-full.png", optimize=True)
    print(f"wrote       logo-full.png    {full.size[0]}x{full.size[1]}")

    print("\nsampled brand colours (modal pixel per hue family)")
    white, offwhite = (255, 255, 255), (0xFB, 0xFB, 0xF9)
    for name, hit in sample_families(art).items():
        if not hit:
            continue
        (r, g, b), n = hit
        print(
            f"  {name:6s} #{r:02X}{g:02X}{b:02X}  n={n:6d}"
            f"  on-white={contrast((r, g, b), white):>5}:1"
            f"  on-bg={contrast((r, g, b), offwhite):>5}:1"
        )


if __name__ == "__main__":
    main()
