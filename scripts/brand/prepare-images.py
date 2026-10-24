#!/usr/bin/env python3
"""
Convert a set of source photographs to web-ready WebP.

    python3 scripts/brand/prepare-images.py home
    python3 scripts/brand/prepare-images.py products
    python3 scripts/brand/prepare-images.py about
    python3 scripts/brand/prepare-images.py all

Reads assets/<set>/*.png|jpeg and writes assets/<set>/build/*.webp, which the
matching scripts/seed/*-media.php then imports into the Media Library.

Nothing is upscaled: each image is capped at the largest size its layout can
actually show at 2x, so most stay at their native resolution.

Note on the product photographs: every one carries the 5Drs logo watermark in
the top-left corner, and the charcoal handles have "555" moulded into them.
Both are part of the client's real photography and are left untouched.
"""

import sys
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[2]
QUALITY = 88

# set -> { source stem: (max width, max height) }
SETS = {
    "home": {
        "hero": (1254, 1254),
        "cat-classic": (1000, 1000),
        "cat-kids": (1000, 1000),
        "cat-black": (1000, 1000),
        "kids-spotlight": (1800, 1800),
    },
    "products": {
        "hero-product": (1920, 1920),
        "classic": (1254, 1254),
        "premium": (1254, 1254),
        "charcoal": (1254, 1254),
        "kids": (1254, 1254),
    },
    "about": {
        "hero-about": (1920, 1920),
    },
}


def convert(name, targets):
    src_dir = ROOT / "assets" / name
    out_dir = src_dir / "build"

    if not src_dir.is_dir():
        print(f"  SKIP  {name} - no assets/{name} directory")
        return 0, 0

    out_dir.mkdir(parents=True, exist_ok=True)
    before_total = after_total = 0

    for stem, (max_w, max_h) in targets.items():
        matches = [p for p in src_dir.iterdir() if p.is_file() and p.stem == stem]

        if not matches:
            print(f"  SKIP  {name}/{stem} - no source file")
            continue

        src = matches[0]
        im = Image.open(src).convert("RGB")
        before = src.stat().st_size

        im.thumbnail((max_w, max_h), Image.LANCZOS)  # never upscales

        dst = out_dir / f"{stem}.webp"
        im.save(dst, "WEBP", quality=QUALITY, method=6)
        after = dst.stat().st_size

        before_total += before
        after_total += after
        print(
            f"  {name}/{stem:16s} -> webp  {im.size[0]}x{im.size[1]}  "
            f"{before // 1024:5d}KB -> {after // 1024:4d}KB"
        )

    return before_total, after_total


def main():
    wanted = sys.argv[1] if len(sys.argv) > 1 else "all"

    if wanted != "all" and wanted not in SETS:
        sys.exit(f"unknown set '{wanted}' - choose from: {', '.join(SETS)}, all")

    names = list(SETS) if wanted == "all" else [wanted]
    before = after = 0

    for name in names:
        b, a = convert(name, SETS[name])
        before += b
        after += a

    if before:
        saved = 100 - (after * 100 // before)
        print(f"\n  total {before // 1024}KB -> {after // 1024}KB ({saved}% smaller)")


if __name__ == "__main__":
    main()
