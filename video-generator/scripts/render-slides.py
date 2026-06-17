#!/usr/bin/env python3
"""
Render each PPTX slide to a PNG using python-pptx + Pillow.
Extracts text, images, and layout data and composites them onto
a 1920x1080 canvas per slide.
"""

import os
import sys
import zipfile
import re
import io
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

# ------------------------------------------------------------------
# Configuration
# ------------------------------------------------------------------
REPO_ROOT = Path(__file__).resolve().parents[2]
PPTX_PATH = REPO_ROOT / "Endur training.pptx"
OUT_DIR   = REPO_ROOT / "public" / "slide-images"
OUT_W, OUT_H = 1920, 1080

# Brand palette (dark blue/teal theme matching the portal)
BG_COLOR      = (15,  33,  62)   # dark navy
TITLE_BG      = (10,  21,  42)   # darker strip
ACCENT_COLOR  = (0,  168, 168)   # teal
TEXT_COLOR    = (240, 245, 255)  # near-white
BODY_COLOR    = (200, 215, 235)  # light blue-grey
FOOTER_COLOR  = (80,  110, 150)  # muted blue

TITLE_H = 130       # pixels for title strip
FOOTER_H = 40
PADDING = 50

# ------------------------------------------------------------------
# Font helpers (use default PIL bitmap fonts as fallback)
# ------------------------------------------------------------------
def load_font(size, bold=False):
    font_names = (
        ["/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf",
         "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"]
        if bold else
        ["/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
         "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"]
    )
    for path in font_names:
        if os.path.exists(path):
            try:
                return ImageFont.truetype(path, size)
            except Exception:
                continue
    return ImageFont.load_default()

# ------------------------------------------------------------------
# Text wrapping
# ------------------------------------------------------------------
def wrap_text(draw, text, font, max_width):
    words = text.split()
    lines, current = [], ""
    for word in words:
        test = (current + " " + word).strip()
        bbox = draw.textbbox((0, 0), test, font=font)
        if bbox[2] - bbox[0] <= max_width:
            current = test
        else:
            if current:
                lines.append(current)
            current = word
    if current:
        lines.append(current)
    return lines

# ------------------------------------------------------------------
# Draw a single slide
# ------------------------------------------------------------------
def render_slide(title_lines, body_lines, images, slide_num, total):
    img = Image.new("RGB", (OUT_W, OUT_H), BG_COLOR)
    draw = ImageDraw.Draw(img)

    # Title strip
    draw.rectangle([(0, 0), (OUT_W, TITLE_H)], fill=TITLE_BG)
    draw.rectangle([(0, TITLE_H), (OUT_W, TITLE_H + 4)], fill=ACCENT_COLOR)

    title_font = load_font(44, bold=True)
    body_font  = load_font(28)
    small_font = load_font(20)

    # Title text
    ty = 20
    for tl in title_lines[:3]:
        draw.text((PADDING, ty), tl, font=title_font, fill=TEXT_COLOR)
        ty += 52

    # Body area
    content_top = TITLE_H + 30
    content_h   = OUT_H - content_top - FOOTER_H - 20
    content_w   = OUT_W - 2 * PADDING

    # If there are images, split content area
    img_area_x = PADDING
    text_area_w = content_w

    placed_images = []
    if images:
        # Reserve right half for images if we have body text too
        if body_lines:
            img_area_x   = OUT_W // 2 + 20
            text_area_w  = OUT_W // 2 - PADDING - 20
            img_slot_w   = OUT_W // 2 - 40
        else:
            img_area_x   = PADDING
            img_slot_w   = content_w

        img_slot_h = content_h // max(1, len(images))
        for i, raw in enumerate(images[:4]):
            try:
                pil_img = Image.open(io.BytesIO(raw)).convert("RGBA")
                ratio = min(img_slot_w / pil_img.width, img_slot_h / pil_img.height)
                nw = int(pil_img.width * ratio)
                nh = int(pil_img.height * ratio)
                pil_img = pil_img.resize((nw, nh), Image.LANCZOS)
                ix = img_area_x + (img_slot_w - nw) // 2
                iy = content_top + i * img_slot_h + (img_slot_h - nh) // 2
                # Paste onto a matching-bg backing to handle transparency
                backing = Image.new("RGB", (nw, nh), BG_COLOR)
                backing.paste(pil_img, mask=pil_img.split()[3] if pil_img.mode == "RGBA" else None)
                img.paste(backing, (ix, iy))
                placed_images.append((ix, iy, nw, nh))
            except Exception:
                pass

    # Body text
    bx = PADDING
    by = content_top
    for line in body_lines:
        wrapped = wrap_text(draw, line, body_font, text_area_w)
        for wl in wrapped:
            if by + 34 > OUT_H - FOOTER_H - 10:
                break
            # Bullet points get an accent dot
            prefix_x = bx
            if line.startswith("•") or line.startswith("-") or line.startswith("*"):
                draw.ellipse([(bx, by + 10), (bx + 8, by + 18)], fill=ACCENT_COLOR)
                prefix_x = bx + 20
                wl = wl.lstrip("•-* ")
            draw.text((prefix_x, by), wl, font=body_font, fill=BODY_COLOR)
            by += 36
        by += 4  # extra gap between paragraphs

    # Footer
    footer_y = OUT_H - FOOTER_H
    draw.rectangle([(0, footer_y), (OUT_W, OUT_H)], fill=TITLE_BG)
    draw.text((PADDING, footer_y + 10),
              "Endur Training",
              font=small_font, fill=FOOTER_COLOR)
    draw.text((OUT_W - 120, footer_y + 10),
              f"{slide_num} / {total}",
              font=small_font, fill=FOOTER_COLOR)

    return img

# ------------------------------------------------------------------
# Extract text from slide XML
# ------------------------------------------------------------------
def extract_texts(xml_bytes):
    text = xml_bytes.decode("utf-8", errors="ignore")
    texts = re.findall(r"<a:t>([^<]+)</a:t>", text)
    # Clean up XML entities
    cleaned = []
    for t in texts:
        t = t.replace("&amp;", "&").replace("&lt;", "<").replace("&gt;", ">").replace("&quot;", '"')
        t = t.strip()
        if t:
            cleaned.append(t)
    return cleaned

# ------------------------------------------------------------------
# Main
# ------------------------------------------------------------------
def main():
    OUT_DIR.mkdir(parents=True, exist_ok=True)

    with zipfile.ZipFile(PPTX_PATH) as z:
        all_files = z.namelist()

        # Find and sort slides
        slide_files = sorted(
            [f for f in all_files if re.match(r"ppt/slides/slide\d+\.xml", f)],
            key=lambda x: int(re.search(r"(\d+)", x).group(1))
        )
        total = len(slide_files)

        # Read slide-to-image relationship files
        # ppt/slides/_rels/slideN.xml.rels maps rId -> media file
        rels_map = {}
        for f in all_files:
            m = re.match(r"ppt/slides/_rels/slide(\d+)\.xml\.rels", f)
            if m:
                snum = int(m.group(1))
                rels_xml = z.read(f).decode("utf-8", errors="ignore")
                targets = re.findall(r'Id="(rId\d+)"[^>]+Target="([^"]+)"', rels_xml)
                rels_map[snum] = {rid: tgt for rid, tgt in targets}

        for idx, slide_file in enumerate(slide_files):
            slide_num = int(re.search(r"(\d+)", slide_file).group(1))
            print(f"  Slide {slide_num:2d}/{total} ...", end=" ", flush=True)

            xml_bytes = z.read(slide_file)
            texts = extract_texts(xml_bytes)

            # Heuristic: first text block is usually the title
            if texts:
                # Collect consecutive short strings as title (until we hit a long one or list)
                title_parts = []
                body_parts  = []
                for i, t in enumerate(texts):
                    if i < 4 and len(t) < 80 and not t.startswith(("•", "-", "*")):
                        title_parts.append(t)
                    else:
                        body_parts.append(t)
            else:
                title_parts = [f"Slide {slide_num}"]
                body_parts  = []

            # Merge title parts into at most 2 display lines
            title_merged = " — ".join(title_parts[:2]) if len(title_parts) > 1 else (title_parts[0] if title_parts else "")
            title_lines  = [title_merged] if title_merged else []

            # Format body text (add bullets where it makes sense)
            formatted_body = []
            for t in body_parts:
                if len(t) > 3:
                    formatted_body.append("• " + t if not t.startswith(("•", "-")) else t)

            # Extract images referenced by this slide
            slide_images = []
            rels = rels_map.get(slide_num, {})
            for rid, target in rels.items():
                if any(target.lower().endswith(ext) for ext in (".png", ".jpg", ".jpeg")):
                    # target is like "../media/imageN.png"
                    media_path = "ppt/media/" + Path(target).name
                    if media_path in all_files:
                        raw = z.read(media_path)
                        if len(raw) > 2000:  # skip tiny decorative images
                            slide_images.append(raw)

            img = render_slide(title_lines, formatted_body, slide_images, slide_num, total)

            out_path = OUT_DIR / f"slide-{slide_num:03d}.png"
            img.save(out_path, "PNG", optimize=False)
            print(f"saved ({img.width}x{img.height})")

    print(f"\nDone — {total} slides in {OUT_DIR}")

if __name__ == "__main__":
    main()
