#!/usr/bin/env python3
"""
Convert the product photography to web-ready WebP.

    python3 scripts/brand/prepare-product-images.py

Reads assets/products/*.png and writes assets/products/build/*.webp, which
scripts/seed/product-media.php then imports into the Media Library.

Nothing is upscaled. The product shots stay square at their native 1254px -
enough for the single-product gallery at 2x - and the hero keeps its native
width for the full-bleed banner.

Note: every product photo carries the 5Drs logo watermark in the top-left
corner, and the charcoal handles have "555" moulded into them. Both are part of
the client's real photography and are intentionally left untouched.
"""

import sys
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "assets" / "products"
OUT = SRC / "build"

QUALITY = 88

# stem -> (max width, max height)
TARGETS = {
    "hero-product": (1920, 1920),
    "classic": (1254, 1254),
    "premium": (1254, 1254),
    "charcoal": (1254, 1254),
    "kids": (1254, 1254),
}


def main():
    if not SRC.is_dir():
        sys.exit(f"missing source directory: {SRC}")

    OUT.mkdir(parents=True, exist_ok=True)
    before_total = after_total = 0

    for stem, (max_w, max_h) in TARGETS.items():
        matches = [p for p in SRC.iterdir() if p.is_file() and p.stem == stem]
        if not matches:
            print(f"  SKIP  {stem} - no source file")
            continue

        src = matches[0]
        im = Image.open(src).convert("RGB")
        before = src.stat().st_size

        im.thumbnail((max_w, max_h), Image.LANCZOS)  # never upscales

        dst = OUT / f"{stem}.webp"
        im.save(dst, "WEBP", quality=QUALITY, method=6)
        after = dst.stat().st_size

        before_total += before
        after_total += after
        print(
            f"  {stem:14s} -> webp  {im.size[0]}x{im.size[1]}  "
            f"{before // 1024:5d}KB -> {after // 1024:4d}KB"
        )

    if before_total:
        saved = 100 - (after_total * 100 // before_total)
        print(f"\n  total {before_total // 1024}KB -> {after_total // 1024}KB ({saved}% smaller)")


if __name__ == "__main__":
    main()
