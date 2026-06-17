#!/usr/bin/env python3
"""
Encode training module videos from slide PNGs using PyAV (bundled ffmpeg).

Usage:
    python3 encode-videos.py                    # all modules
    python3 encode-videos.py admin-manager      # single module
"""

import sys
import os
import time
from pathlib import Path
from PIL import Image
import av
import numpy as np

# ------------------------------------------------------------------
# Config
# ------------------------------------------------------------------
REPO_ROOT  = Path(__file__).resolve().parents[2]
SLIDES_DIR = REPO_ROOT / "public" / "slide-images"
OUT_DIR    = REPO_ROOT / "public" / "videos"

FPS              = 30
SLIDE_SECONDS    = 8
TITLE_SECONDS    = 3
FADE_FRAMES      = 12        # 0.4s fade between slides
TRANSITION_SEC   = 0.5       # cross-fade overlap

W, H = 1920, 1080

# Module definitions (id, title, slides range)
MODULES = [
    ("introduction",        "Introduction to OpenLink & Endur",    list(range(1,  19))),
    ("common-functionality","Common System Functionality",          list(range(19, 32))),
    ("admin-manager",       "Admin Manager",                        list(range(32, 40))),
    ("reference-manager",   "Reference Manager",                    list(range(40, 51))),
    ("market-manager",      "Market Manager",                       list(range(51, 58))),
    ("trading-manager",     "Trading Manager & Trade Lifecycle",    list(range(58, 86))),
]

# Brand colours
BG       = (6,  16,  32)
TITLE_BG = (10, 21,  42)
ACCENT   = (0,  168, 168)
WHITE    = (240, 245, 255)
MUTED    = (80, 110, 150)

# ------------------------------------------------------------------
# Font helpers
# ------------------------------------------------------------------
from PIL import ImageDraw, ImageFont

def load_font(size, bold=False):
    candidates = (
        ["/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf",
         "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"]
        if bold else
        ["/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
         "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"]
    )
    for p in candidates:
        if os.path.exists(p):
            try:
                return ImageFont.truetype(p, size)
            except Exception:
                pass
    return ImageFont.load_default()

# ------------------------------------------------------------------
# Title card generator
# ------------------------------------------------------------------
def make_title_card(module_num: int, title: str, slide_count: int, duration: str) -> np.ndarray:
    img = Image.new("RGB", (W, H), BG)
    draw = ImageDraw.Draw(img)

    # Gradient-ish background: darker strip
    draw.rectangle([(0, 0), (W, H)], fill=BG)

    # Top accent bar
    draw.rectangle([(0, 0), (W, 6)], fill=ACCENT)

    # "MODULE N" label
    f_small  = load_font(26, bold=True)
    f_title  = load_font(66, bold=True)
    f_detail = load_font(28)

    draw.text((W//2, H//2 - 110), f"MODULE {module_num}",
              font=f_small, fill=ACCENT, anchor="mm")

    # Title (word-wrap at 1400px)
    title_words = title.split()
    lines, cur = [], ""
    for w in title_words:
        test = (cur + " " + w).strip()
        bbox = draw.textbbox((0, 0), test, font=f_title)
        if bbox[2] - bbox[0] <= 1400:
            cur = test
        else:
            if cur:
                lines.append(cur)
            cur = w
    if cur:
        lines.append(cur)

    line_h = 82
    total_h = len(lines) * line_h
    start_y = H // 2 - total_h // 2
    for li, ln in enumerate(lines):
        draw.text((W//2, start_y + li * line_h), ln,
                  font=f_title, fill=WHITE, anchor="mm")

    # Meta: slides & duration
    meta = f"{slide_count} slides  ·  {duration}  ·  Endur Training Series"
    draw.text((W//2, H//2 + total_h//2 + 48), meta,
              font=f_detail, fill=MUTED, anchor="mm")

    # Bottom accent bar
    draw.rectangle([(0, H-4), (W, H)], fill=ACCENT)

    return np.array(img)

# ------------------------------------------------------------------
# Encode one module
# ------------------------------------------------------------------
DURATIONS = {
    "introduction":         "2:24",
    "common-functionality": "1:44",
    "admin-manager":        "1:04",
    "reference-manager":    "1:28",
    "market-manager":       "0:56",
    "trading-manager":      "3:44",
}

def encode_module(mod_id: str, title: str, slide_nums: list):
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    out_path = OUT_DIR / f"{mod_id}.mp4"

    duration_str = DURATIONS.get(mod_id, "?:??")
    print(f"\n--- {title} ({len(slide_nums)} slides) ---")
    print(f"    Output: {out_path}")

    # Open output container
    container = av.open(str(out_path), mode="w")
    stream = container.add_stream("h264", rate=FPS)
    stream.width  = W
    stream.height = H
    stream.pix_fmt = "yuv420p"
    stream.options = {
        "crf":     "22",
        "preset":  "fast",
        "profile": "high",
    }

    def write_frame(arr: np.ndarray):
        frame = av.VideoFrame.from_ndarray(arr, format="rgb24")
        frame = frame.reformat(format="yuv420p")
        for pkt in stream.encode(frame):
            container.mux(pkt)

    # --- Title card ---
    print("    Writing title card...", flush=True)
    title_frame = make_title_card(
        module_num=MODULES.index(next(m for m in MODULES if m[0] == mod_id)) + 1,
        title=title,
        slide_count=len(slide_nums),
        duration=duration_str,
    )

    total_title_frames = TITLE_SECONDS * FPS
    for f in range(total_title_frames):
        # Fade in first 12 frames, fade out last 12
        if f < FADE_FRAMES:
            alpha = f / FADE_FRAMES
            dark  = np.full_like(title_frame, BG, dtype=np.uint8)
            composited = (dark * (1 - alpha) + title_frame * alpha).astype(np.uint8)
        elif f > total_title_frames - FADE_FRAMES:
            alpha = (total_title_frames - f) / FADE_FRAMES
            dark  = np.full_like(title_frame, BG, dtype=np.uint8)
            composited = (dark * (1 - alpha) + title_frame * alpha).astype(np.uint8)
        else:
            composited = title_frame
        write_frame(composited)

    # --- Slides ---
    total_slide_frames = SLIDE_SECONDS * FPS
    loaded_prev = None

    for si, snum in enumerate(slide_nums):
        png_path = SLIDES_DIR / f"slide-{snum:03d}.png"
        if not png_path.exists():
            print(f"    WARNING: {png_path} not found, skipping")
            continue

        img = Image.open(png_path).convert("RGB").resize((W, H), Image.LANCZOS)
        slide_arr = np.array(img)

        print(f"    Slide {snum:3d}/{slide_nums[-1]} [{si+1}/{len(slide_nums)}]", end="\r", flush=True)

        for f in range(total_slide_frames):
            if f < FADE_FRAMES:
                # Fade in from dark (or previous slide)
                alpha = f / FADE_FRAMES
                if loaded_prev is not None:
                    bg = loaded_prev
                else:
                    bg = np.full_like(slide_arr, BG, dtype=np.uint8)
                composited = (bg * (1 - alpha) + slide_arr * alpha).astype(np.uint8)
            elif f > total_slide_frames - FADE_FRAMES:
                # Fade out to dark
                alpha = (total_slide_frames - f) / FADE_FRAMES
                dark = np.full_like(slide_arr, BG, dtype=np.uint8)
                composited = (dark * (1 - alpha) + slide_arr * alpha).astype(np.uint8)
            else:
                composited = slide_arr
            write_frame(composited)

        loaded_prev = slide_arr

    # Flush encoder
    for pkt in stream.encode():
        container.mux(pkt)
    container.close()

    size_mb = out_path.stat().st_size / 1024 / 1024
    print(f"\n    Done: {out_path.name} ({size_mb:.1f} MB)")

# ------------------------------------------------------------------
# Main
# ------------------------------------------------------------------
def main():
    target = sys.argv[1] if len(sys.argv) > 1 else None

    to_render = [m for m in MODULES if target is None or m[0] == target]
    if not to_render:
        print(f"Unknown module: {target}")
        print("Available:", ", ".join(m[0] for m in MODULES))
        sys.exit(1)

    print(f"=== Endur Training Video Encoder ===")
    print(f"Modules to render: {len(to_render)}")

    t0 = time.time()
    for mod_id, title, slides in to_render:
        encode_module(mod_id, title, slides)

    elapsed = time.time() - t0
    print(f"\n=== Done in {elapsed:.0f}s ===")
    import subprocess
    result = subprocess.run(["ls", "-lh", str(OUT_DIR)], capture_output=True, text=True)
    print(result.stdout)

if __name__ == "__main__":
    main()
