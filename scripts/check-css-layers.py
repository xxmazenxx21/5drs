#!/usr/bin/env python3
"""
Guard the cascade-layer split in the built theme stylesheet.

WooCommerce's stylesheets are unlayered, and in the CSS cascade an unlayered
rule beats a layered one regardless of specificity. So:

  * anything whose job is to override WooCommerce must be UNLAYERED, and
  * the theme's own components must stay in @layer components, so that Tailwind
    utilities can still override them when pages get built.

Getting this backwards is silent - the CSS is valid, it just loses. It is how
the header logo ended up rendering at its intrinsic 534x364 on every store page.

    python3 scripts/check-css-layers.py
"""

import glob
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DIST = ROOT / "wp-content/themes/5drs/dist/assets"

MUST_BE_UNLAYERED = [
    ".woocommerce ul.products li.product{",
    ".woocommerce .fivedrs-brand img",
    ".woocommerce table.shop_table",
    # Text on dark surfaces has to beat the base layer's `a { color }` as well
    # as any unlayered plugin rule - see src/css/components/on-dark.css.
    ".fivedrs-btn:visited",
    ".fivedrs-footer a:link",
]
MUST_BE_LAYERED = [
    ".fivedrs-nav a{",
    ".fivedrs-cart{",
]


def enclosing_layer(css, index):
    """Return the @layer block containing `index`, or None if unlayered."""
    stack = []
    for i, ch in enumerate(css):
        if i == index:
            break
        if ch == "{":
            j = max(css.rfind("}", 0, i), css.rfind("{", 0, i))
            stack.append(css[j + 1:i].strip())
        elif ch == "}" and stack:
            stack.pop()
    return next((s for s in stack if s.startswith("@layer")), None)


def main():
    matches = sorted(glob.glob(str(DIST / "style-*.css")))
    if not matches:
        print("no built stylesheet found - run `make theme-build` first")
        return 1

    css = Path(matches[-1]).read_text(encoding="utf-8")
    failed = 0

    for needle in MUST_BE_UNLAYERED:
        i = css.find(needle)
        if i < 0:
            print(f"  FAIL  missing rule: {needle}")
            failed += 1
            continue
        layer = enclosing_layer(css, i)
        if layer:
            print(f"  FAIL  {needle} is inside {layer} - WooCommerce will win")
            failed += 1
        else:
            print(f"  PASS  {needle} is unlayered")

    for needle in MUST_BE_LAYERED:
        i = css.find(needle)
        if i < 0:
            print(f"  FAIL  missing rule: {needle}")
            failed += 1
            continue
        layer = enclosing_layer(css, i)
        if layer != "@layer components":
            print(f"  FAIL  {needle} should be in @layer components, found {layer}")
            failed += 1
        else:
            print(f"  PASS  {needle} is in @layer components")

    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
