#!/usr/bin/env python3
"""
Convert the Home page source art to web-ready WebP.

    python3 scripts/brand/prepare-home-images.py

Reads assets/home/*.png|jpeg and writes assets/home/build/*.webp, which
scripts/seed/home-media.php then imports into the Media Library.

Nothing is upscaled: each image is capped at the largest size the layout can
actually show at 2x, so the hero and the category cards stay at their native
1254px and only the wide banner is reduced.
"""

import sys
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "assets" / "home"
OUT = SRC / "build"

QUALITY = 88

# source stem -> (max width, max height)
TARGETS = {
    "hero": (1254, 1254),
    "cat-classic": (1000, 1000),
    "cat-kids": (1000, 1000),
    "cat-black": (1000, 1000),
    "kids-spotlight": (1800, 1800),
}


def main():
    if not SRC.is_dir():
        sys.exit(f"missing source directory: {SRC}")

    OUT.mkdir(parents=True, exist_ok=True)
    total_before = total_after = 0

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

        total_before += before
        total_after += after
        print(
            f"  {stem:16s} {src.suffix[1:]:4s} -> webp  "
            f"{im.size[0]}x{im.size[1]}  {before // 1024:5d}KB -> {after // 1024:4d}KB"
        )

    if total_before:
        saved = 100 - (total_after * 100 // total_before)
        print(f"\n  total {total_before // 1024}KB -> {total_after // 1024}KB ({saved}% smaller)")


if __name__ == "__main__":
    main()
