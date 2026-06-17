#!/usr/bin/env python3
"""
Extract narration scripts from PPTX slides and write them as
plain .txt files — one file per module, one section per slide.

Output: public/voiceovers/scripts/{module-id}.txt
"""

import re, zipfile, sys
from pathlib import Path

REPO_ROOT  = Path(__file__).resolve().parents[2]
PPTX_PATH  = REPO_ROOT / "Endur training.pptx"
OUT_DIR    = REPO_ROOT / "public" / "voiceovers" / "scripts"

MODULES = [
    ("introduction",         "Introduction to OpenLink and Endur",    list(range(1,  19))),
    ("common-functionality", "Common System Functionality",            list(range(19, 32))),
    ("admin-manager",        "Admin Manager",                          list(range(32, 40))),
    ("reference-manager",    "Reference Manager",                      list(range(40, 51))),
    ("market-manager",       "Market Manager",                         list(range(51, 58))),
    ("trading-manager",      "Trading Manager and Trade Lifecycle",    list(range(58, 86))),
]

SLIDE_SEC  = 8
TITLE_SEC  = 3

# --- XML entity cleanup ---------------------------------------------------
def clean(t: str) -> str:
    return (t
        .replace("&amp;",  "and")
        .replace("&lt;",   "<")
        .replace("&gt;",   ">")
        .replace("&quot;", '"')
        .replace("–", "-").replace("—", "-")
        .replace("‘", "'").replace("’", "'")
        .replace("“", '"').replace("”", '"')
        .strip())

# --- Extract all text runs from a slide XML --------------------------------
def slide_texts(z, snum: int) -> list[str]:
    path = f"ppt/slides/slide{snum}.xml"
    if path not in z.namelist():
        return []
    xml  = z.read(path).decode("utf-8", errors="ignore")
    raw  = [clean(t) for t in re.findall(r"<a:t>([^<]+)</a:t>", xml)]
    return [t for t in raw if len(t) > 1]

# --- Build a narration paragraph from raw text lines ----------------------
SKIP = {"any questions", "questions", "recap", "?", "q&a", "q & a"}

def narration(texts: list[str]) -> str:
    filtered = [t for t in texts
                if t.lower().strip("? ") not in SKIP and len(t) > 2]
    if not filtered:
        return "(no narration — transition slide)"

    title = filtered[0]
    body  = filtered[1:]

    sentences = [title.rstrip(".") + "."]
    for line in body[:8]:
        line = line.lstrip("•\-*· ").strip()
        if not line:
            continue
        if not line[-1] in ".!?":
            line += "."
        sentences.append(line)

    return "  ".join(sentences)

# --- Main -----------------------------------------------------------------
def main():
    OUT_DIR.mkdir(parents=True, exist_ok=True)

    with zipfile.ZipFile(PPTX_PATH) as z:
        for mod_id, title, slides in MODULES:
            out_path = OUT_DIR / f"{mod_id}.txt"
            lines    = []

            # Header
            lines += [
                "=" * 70,
                f"MODULE: {title}",
                f"Slides: {slides[0]}–{slides[-1]}  ({len(slides)} slides)",
                f"Estimated runtime: {TITLE_SEC + len(slides) * SLIDE_SEC} seconds",
                "=" * 70,
                "",
                f"[TITLE CARD — {TITLE_SEC}s]",
                f"Module narration: \"Welcome to {title}.\"",
                "",
            ]

            for i, snum in enumerate(slides):
                texts  = slide_texts(z, snum)
                script = narration(texts)

                lines += [
                    f"[SLIDE {snum}  —  {SLIDE_SEC}s  ({i+1}/{len(slides)})]",
                ]
                # Show raw text for reference
                if texts:
                    lines.append(f"  Raw text from slide:")
                    for t in texts[:10]:
                        lines.append(f"    • {t}")
                lines += [
                    f"  Narration script:",
                    f"    {script}",
                    "",
                ]

            out_path.write_text("\n".join(lines), encoding="utf-8")
            word_count = sum(len(l.split()) for l in lines)
            print(f"  {out_path.name}  ({len(slides)} slides, ~{word_count} words)")

    print(f"\nDone — scripts written to {OUT_DIR}")

if __name__ == "__main__":
    main()
