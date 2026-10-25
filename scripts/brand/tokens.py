#!/usr/bin/env python3
"""
Generate the 5drs colour scales from the logo anchors and audit them for
WCAG AA contrast.

    python3 scripts/brand/tokens.py            # print the scales + audit
    python3 scripts/brand/tokens.py --check    # audit only, non-zero on failure

The emitted hex values are mirrored by hand into:
    wp-content/themes/5drs/src/css/tokens.css   (Tailwind v4 @theme)
    wp-content/themes/5drs/theme.json           (editor presets)
Run --check after changing either so a shade cannot silently drop below AA.
"""

import colorsys
import sys

# Anchors sampled from assets/brand/logo5drs.jpeg - see extract-logo.py.
ANCHORS = {
    "navy": ("#0E3D4D", 800),
    "green": ("#82AE61", 400),
    "gold": ("#D0B572", 400),
}
STEPS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900]

# Target lightness per step. Anchors keep their own lightness; the rest are
# interpolated towards white and towards a near-black of the same hue.
LIGHTNESS = {
    50: 0.97, 100: 0.93, 200: 0.85, 300: 0.74, 400: 0.62,
    500: 0.52, 600: 0.42, 700: 0.32, 800: 0.22, 900: 0.14,
}


def hex_to_rgb(h):
    h = h.lstrip("#")
    return tuple(int(h[i:i + 2], 16) for i in (0, 2, 4))


def rgb_to_hex(rgb):
    return "#%02X%02X%02X" % rgb


def scale(anchor_hex, anchor_step):
    r, g, b = hex_to_rgb(anchor_hex)
    hue, light, sat = colorsys.rgb_to_hls(r / 255, g / 255, b / 255)
    out = {}
    for step in STEPS:
        if step == anchor_step:
            out[step] = anchor_hex.upper()
            continue
        target = LIGHTNESS[step]
        # Desaturate the palest tints and deepen the darkest shades slightly,
        # which keeps the ramp from looking chalky at one end and muddy at the other.
        if target > light:
            s = sat * (0.35 + 0.65 * (1 - (target - light) / max(1 - light, 1e-6)))
        else:
            s = min(1.0, sat * (1 + 0.25 * ((light - target) / max(light, 1e-6))))
        rr, gg, bb = colorsys.hls_to_rgb(hue, target, s)
        out[step] = rgb_to_hex(tuple(int(round(c * 255)) for c in (rr, gg, bb)))
    return out


def lum(rgb):
    def lin(c):
        c /= 255
        return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4
    r, g, b = rgb
    return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b)


def contrast(fg, bg):
    a, b = lum(hex_to_rgb(fg)) + 0.05, lum(hex_to_rgb(bg)) + 0.05
    return round(max(a, b) / min(a, b), 2)


def main():
    scales = {name: scale(h, s) for name, (h, s) in ANCHORS.items()}

    BG = "#FBFBF9"       # page background
    SURFACE = "#FFFFFF"  # cards
    TEXT = scales["navy"][800]
    MUTED = "#5A6B73"
    BORDER = "#E3E6E2"

    if "--check" not in sys.argv:
        for name, s in scales.items():
            print(f"\n{name}")
            for step in STEPS:
                hexv = s[step]
                print(f"  {name}-{step:<3} {hexv}  on-bg {contrast(hexv, BG):>5}:1"
                      f"  white-on {contrast('#FFFFFF', hexv):>5}:1")
        print(f"\nneutrals\n  bg {BG}  surface {SURFACE}  text {TEXT}  muted {MUTED}  border {BORDER}")

    # pair, foreground, background, minimum ratio
    checks = [
        ("body text on bg",            TEXT, BG, 4.5),
        ("body text on surface",       TEXT, SURFACE, 4.5),
        ("muted text on bg",           MUTED, BG, 4.5),
        ("muted text on surface",      MUTED, SURFACE, 4.5),
        ("white on primary button",    "#FFFFFF", scales["navy"][800], 4.5),
        ("white on primary hover",     "#FFFFFF", scales["navy"][900], 4.5),
        ("white on green button",      "#FFFFFF", scales["green"][700], 4.5),
        ("green text on bg",           scales["green"][700], BG, 4.5),
        ("green text on surface",      scales["green"][700], SURFACE, 4.5),
        ("link navy on bg",            scales["navy"][700], BG, 4.5),
        ("focus ring on bg",           scales["green"][600], BG, 3.0),
        ("border on bg (ui)",          BORDER, BG, 1.2),
        ("gold accent on navy (large)", scales["gold"][400], scales["navy"][800], 3.0),
        ("error on surface",           "#B3261E", SURFACE, 4.5),
        ("success on surface",         "#2E6B3E", SURFACE, 4.5),

        # --- text on dark surfaces (src/css/components/on-dark.css) ---------
        # The gradient hero runs navy-900 -> navy-800 -> navy-700, so every
        # pair is measured against navy-700, its lightest stop and worst case.
        ("white title on dark hero",   "#FFFFFF", scales["navy"][700], 4.5),
        ("hero lead on dark hero",     scales["navy"][100], scales["navy"][700], 4.5),
        ("hero eyebrow on dark hero",  scales["gold"][200], scales["navy"][700], 4.5),
        ("white on CTA band",          "#FFFFFF", scales["navy"][800], 4.5),
        ("CTA body on CTA band",       scales["navy"][100], scales["navy"][800], 4.5),
        ("footer text on footer",      "#FFFFFF", scales["navy"][800], 4.5),
        ("footer links on footer",     "#FFFFFF", scales["navy"][800], 4.5),
        ("footer tagline on footer",   scales["navy"][50], scales["navy"][800], 4.5),
        ("white on gold button",       "#FFFFFF", scales["gold"][700], 4.5),
        ("white on gold button hover", "#FFFFFF", scales["gold"][800], 4.5),
        ("outline button on dark",     "#FFFFFF", scales["navy"][800], 4.5),
        ("focus ring on dark",         scales["gold"][400], scales["navy"][800], 3.0),
    ]

    print("\nWCAG audit")
    failed = 0
    for label, fg, bg, need in checks:
        got = contrast(fg, bg)
        ok = got >= need
        failed += not ok
        print(f"  {'PASS' if ok else 'FAIL'}  {label:30s} {fg} on {bg}  {got:>5}:1 (need {need})")

    if failed:
        print(f"\n{failed} contrast failure(s)")
        return 1
    print("\nall pairs pass")
    return 0


if __name__ == "__main__":
    sys.exit(main())
