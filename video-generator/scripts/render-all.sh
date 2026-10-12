#!/usr/bin/env bash
# Encode all 6 training module videos to resources/videos/
# Uses PyAV (bundled ffmpeg) — no system ffmpeg required.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

echo "=== Endur Training Video Encoder ==="
echo "Output dir: $REPO_ROOT/resources/videos/"
echo ""

python3 "$REPO_ROOT/video-generator/scripts/encode-videos.py" "$@"
