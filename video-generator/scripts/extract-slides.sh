#!/usr/bin/env bash
# Re-export slide PNGs from the PPTX (run this if the PPTX changes)
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PPTX="$REPO_ROOT/Endur training.pptx"
OUT_DIR="$REPO_ROOT/public/slide-images"

mkdir -p "$OUT_DIR"

echo "Rendering slides from PPTX using Python..."
python3 "$(dirname "$0")/render-slides.py"

echo "Done — $(ls "$OUT_DIR"/*.png 2>/dev/null | wc -l) PNGs in $OUT_DIR"
