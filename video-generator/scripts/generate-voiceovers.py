#!/usr/bin/env python3
"""
Generate voiceover MP3s for each training module and produce
audio-merged MP4 videos.

Steps per module:
  1. Extract narration text from PPTX slide XML (python-pptx)
  2. Generate per-slide WAV with flite TTS
  3. Pad/trim each WAV to match slide duration (8 s)
  4. Prepend 3-second title-card narration
  5. Concatenate all WAVs → module WAV
  6. Encode WAV → AAC and mux into existing MP4 (PyAV)
  7. Save final MP4 with audio to public/videos/{id}-voiced.mp4
  8. Also save standalone public/voiceovers/{id}.mp3

Usage:
    python3 generate-voiceovers.py                 # all modules
    python3 generate-voiceovers.py admin-manager   # single module
"""

import sys, os, re, subprocess, struct, wave, tempfile, io, time
from pathlib import Path
import zipfile
import numpy as np
from PIL import Image   # already installed
import av

# ── Paths ────────────────────────────────────────────────────────────────────
REPO_ROOT    = Path(__file__).resolve().parents[2]
PPTX_PATH    = REPO_ROOT / "Endur training.pptx"
VIDEOS_DIR   = REPO_ROOT / "public" / "videos"
VOICEOVERS_DIR = REPO_ROOT / "public" / "voiceovers"
SLIDES_DIR   = REPO_ROOT / "public" / "slide-images"

FLITE        = "flite"
FLITE_VOICE  = "slt"          # female, clearest voice
FPS          = 30
SLIDE_SEC    = 8
TITLE_SEC    = 3
FADE_FRAMES  = 12
SAMPLE_RATE  = 22050           # flite default
CHANNELS     = 1

W, H = 1920, 1080
BG   = (6, 16, 32)

# ── Module definitions ───────────────────────────────────────────────────────
MODULES = [
    ("introduction",         "Introduction to OpenLink and Endur",    list(range(1,  19))),
    ("common-functionality", "Common System Functionality",            list(range(19, 32))),
    ("admin-manager",        "Admin Manager",                          list(range(32, 40))),
    ("reference-manager",    "Reference Manager",                      list(range(40, 51))),
    ("market-manager",       "Market Manager",                         list(range(51, 58))),
    ("trading-manager",      "Trading Manager and Trade Lifecycle",    list(range(58, 86))),
]

# ── Slide text extraction ────────────────────────────────────────────────────
def _clean(text: str) -> str:
    return (text
            .replace("&amp;", "and")
            .replace("&lt;", "<").replace("&gt;", ">")
            .replace("&quot;", '"').replace("–", "-").replace("—", "-")
            .replace("’", "'").replace("‘", "'")
            .strip())

def extract_slide_texts(slide_nums: list) -> dict:
    """Return {slide_num: [text_line, ...]} from the PPTX."""
    result = {}
    with zipfile.ZipFile(PPTX_PATH) as z:
        for snum in slide_nums:
            path = f"ppt/slides/slide{snum}.xml"
            if path not in z.namelist():
                result[snum] = []
                continue
            xml = z.read(path).decode("utf-8", errors="ignore")
            texts = [_clean(t) for t in re.findall(r"<a:t>([^<]+)</a:t>", xml)]
            texts = [t for t in texts if len(t) > 1]
            result[snum] = texts
    return result

# ── Narration script builder ─────────────────────────────────────────────────
SKIP_PHRASES = {"any questions", "questions", "recap", "?"}

def build_narration(texts: list) -> str:
    """Turn raw slide text lines into a natural narration sentence."""
    if not texts:
        return ""

    # Filter noise
    filtered = [t for t in texts if t.lower().strip("? ") not in SKIP_PHRASES and len(t) > 2]
    if not filtered:
        return ""

    # First item is usually the title/heading
    title  = filtered[0]
    body   = filtered[1:]

    parts = [title + "."]
    for line in body[:6]:          # limit to ~6 bullets
        # Clean bullet chars
        line = line.lstrip("•-*· ").strip()
        if not line:
            continue
        if not line.endswith((".", "!", "?")):
            line += "."
        parts.append(line)

    narration = "  ".join(parts)
    # Remove excessive whitespace
    narration = re.sub(r"\s{2,}", "  ", narration)
    return narration

# ── TTS via flite ────────────────────────────────────────────────────────────
def tts_to_wav(text: str, out_path: Path) -> bool:
    """Run flite on text, write WAV to out_path. Returns True on success."""
    if not text.strip():
        return False
    result = subprocess.run(
        [FLITE, "-voice", FLITE_VOICE, "-t", text, str(out_path)],
        capture_output=True, text=True
    )
    return result.returncode == 0 and out_path.exists() and out_path.stat().st_size > 100

# ── WAV helpers ──────────────────────────────────────────────────────────────
def wav_to_array(path: Path) -> np.ndarray:
    with wave.open(str(path), "rb") as wf:
        frames = wf.readframes(wf.getnframes())
        sr     = wf.getframerate()
        ch     = wf.getnchannels()
        sw     = wf.getsampwidth()
    dtype = {1: np.int8, 2: np.int16, 4: np.int32}.get(sw, np.int16)
    arr = np.frombuffer(frames, dtype=dtype).astype(np.float32)
    if ch > 1:
        arr = arr.reshape(-1, ch).mean(axis=1)
    # Resample to SAMPLE_RATE if needed
    if sr != SAMPLE_RATE:
        ratio  = SAMPLE_RATE / sr
        new_len = int(len(arr) * ratio)
        arr = np.interp(np.linspace(0, len(arr)-1, new_len),
                        np.arange(len(arr)), arr)
    return arr

def silence(seconds: float) -> np.ndarray:
    return np.zeros(int(SAMPLE_RATE * seconds), dtype=np.float32)

def pad_to(arr: np.ndarray, seconds: float) -> np.ndarray:
    target = int(SAMPLE_RATE * seconds)
    if len(arr) >= target:
        return arr[:target]
    return np.concatenate([arr, silence(seconds - len(arr) / SAMPLE_RATE)])

def save_wav(arr: np.ndarray, path: Path):
    scaled = np.clip(arr, -32767, 32767).astype(np.int16)
    with wave.open(str(path), "wb") as wf:
        wf.setnchannels(1)
        wf.setsampwidth(2)
        wf.setframerate(SAMPLE_RATE)
        wf.writeframes(scaled.tobytes())

# ── Font (reuse from render-slides) ─────────────────────────────────────────
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

# ── Video frame helpers ──────────────────────────────────────────────────────
ACCENT = (0, 168, 168)
WHITE  = (240, 245, 255)
MUTED  = (80, 110, 150)

def make_title_frame(module_num, title, slide_count, duration_str):
    img = Image.new("RGB", (W, H), BG)
    draw = ImageDraw.Draw(img)
    draw.rectangle([(0, 0), (W, 6)], fill=ACCENT)
    draw.rectangle([(0, H-4), (W, H)], fill=ACCENT)

    f_sm = load_font(26, bold=True)
    f_lg = load_font(66, bold=True)
    f_md = load_font(28)

    draw.text((W//2, H//2 - 110), f"MODULE {module_num}",
              font=f_sm, fill=ACCENT, anchor="mm")

    words = title.split()
    lines, cur = [], ""
    for w in words:
        test = (cur + " " + w).strip()
        if draw.textlength(test, font=f_lg) <= 1400:
            cur = test
        else:
            if cur: lines.append(cur)
            cur = w
    if cur: lines.append(cur)

    lh = 82
    sy = H//2 - (len(lines)*lh)//2
    for i, ln in enumerate(lines):
        draw.text((W//2, sy + i*lh), ln, font=f_lg, fill=WHITE, anchor="mm")

    draw.text((W//2, H//2 + (len(lines)*lh)//2 + 48),
              f"{slide_count} slides  ·  {duration_str}  ·  Endur Training Series",
              font=f_md, fill=MUTED, anchor="mm")
    return np.array(img)

def load_slide_frame(snum):
    p = SLIDES_DIR / f"slide-{snum:03d}.png"
    img = Image.open(p).convert("RGB").resize((W, H), Image.LANCZOS)
    return np.array(img)

def apply_fade(frame_arr, f, total_frames):
    if f < FADE_FRAMES:
        alpha = f / FADE_FRAMES
        dark  = np.full_like(frame_arr, BG)
        return (dark*(1-alpha) + frame_arr*alpha).astype(np.uint8)
    elif f > total_frames - FADE_FRAMES:
        alpha = (total_frames - f) / FADE_FRAMES
        dark  = np.full_like(frame_arr, BG)
        return (dark*(1-alpha) + frame_arr*alpha).astype(np.uint8)
    return frame_arr

# ── Module encoder ───────────────────────────────────────────────────────────
DURATIONS = {
    "introduction":         "2:24",
    "common-functionality": "1:44",
    "admin-manager":        "1:04",
    "reference-manager":    "1:28",
    "market-manager":       "0:56",
    "trading-manager":      "3:44",
}

def encode_module(mod_id, title, slide_nums, module_num, tmpdir):
    out_video    = VIDEOS_DIR  / f"{mod_id}-voiced.mp4"
    out_audio    = VOICEOVERS_DIR / f"{mod_id}.mp3"
    duration_str = DURATIONS.get(mod_id, "")

    print(f"\n{'='*60}")
    print(f"  Module {module_num}: {title}")
    print(f"  Slides: {slide_nums[0]}–{slide_nums[-1]}  ({len(slide_nums)} slides)")

    # ── 1. Extract slide texts ────────────────────────────────────────────
    print("  Extracting slide text...", flush=True)
    slide_texts = extract_slide_texts(slide_nums)

    # ── 2. Build narration scripts + TTS per slide ────────────────────────
    print("  Generating TTS audio...", flush=True)

    # Title card narration
    title_script = f"Module {module_num}. {title}. {len(slide_nums)} slides."
    title_wav    = Path(tmpdir) / "title.wav"
    tts_to_wav(title_script, title_wav)

    segments = []   # list of np.ndarray (float32 PCM at SAMPLE_RATE)

    # Title card segment (3 s)
    if title_wav.exists():
        arr = wav_to_array(title_wav)
        segments.append(pad_to(arr, TITLE_SEC))
    else:
        segments.append(silence(TITLE_SEC))

    # Per-slide segments (8 s each)
    for i, snum in enumerate(slide_nums):
        texts     = slide_texts.get(snum, [])
        narration = build_narration(texts)
        wav_path  = Path(tmpdir) / f"slide_{snum}.wav"

        if narration and tts_to_wav(narration, wav_path):
            arr = wav_to_array(wav_path)
            seg = pad_to(arr, SLIDE_SEC)
        else:
            seg = silence(SLIDE_SEC)

        segments.append(seg)
        print(f"    Slide {snum:3d} [{i+1}/{len(slide_nums)}] — {len(narration)} chars", end="\r", flush=True)

    print()

    full_audio = np.concatenate(segments)  # one long float32 array

    # ── 3. Save standalone voiceover MP3 ─────────────────────────────────
    print("  Saving voiceover audio...", flush=True)
    VOICEOVERS_DIR.mkdir(parents=True, exist_ok=True)
    wav_master = Path(tmpdir) / "master.wav"
    save_wav(full_audio, wav_master)

    # Encode WAV → MP3 using PyAV
    src_container  = av.open(str(wav_master))
    mp3_container  = av.open(str(out_audio), mode="w")
    mp3_stream     = mp3_container.add_stream("libmp3lame", rate=SAMPLE_RATE)
    mp3_stream.layout = "mono"
    for frame in src_container.decode(audio=0):
        frame.pts = None
        for pkt in mp3_stream.encode(frame):
            mp3_container.mux(pkt)
    for pkt in mp3_stream.encode():
        mp3_container.mux(pkt)
    mp3_container.close()
    src_container.close()

    # ── 4. Encode video + audio into new MP4 ─────────────────────────────
    print("  Encoding video with audio...", flush=True)
    out_container = av.open(str(out_video), mode="w")

    vid_stream = out_container.add_stream("h264", rate=FPS)
    vid_stream.width   = W
    vid_stream.height  = H
    vid_stream.pix_fmt = "yuv420p"
    vid_stream.options = {"crf": "22", "preset": "fast", "profile": "high"}

    aud_stream = out_container.add_stream("aac", rate=SAMPLE_RATE)
    aud_stream.layout = "mono"

    # Interleave video frames and audio samples
    audio_pos     = 0          # samples written so far
    audio_per_vid = SAMPLE_RATE / FPS   # samples per video frame
    audio_scaled  = np.clip(full_audio * 32767, -32767, 32767).astype(np.int16)

    def write_vid(arr, vframe_idx):
        frame = av.VideoFrame.from_ndarray(arr, format="rgb24")
        frame = frame.reformat(format="yuv420p")
        frame.pts = vframe_idx
        for pkt in vid_stream.encode(frame):
            out_container.mux(pkt)

    def flush_audio_to(target_sample):
        nonlocal audio_pos
        chunk_size = 1024
        while audio_pos < target_sample and audio_pos < len(audio_scaled):
            end = min(audio_pos + chunk_size, len(audio_scaled), target_sample)
            chunk = audio_scaled[audio_pos:end]
            a_frame = av.AudioFrame.from_ndarray(
                chunk.reshape(1, -1), format="s16", layout="mono"
            )
            a_frame.sample_rate = SAMPLE_RATE
            a_frame.pts         = audio_pos
            for pkt in aud_stream.encode(a_frame):
                out_container.mux(pkt)
            audio_pos = end

    vid_frame_idx = 0

    # Title card
    title_arr = make_title_frame(module_num, title, len(slide_nums), duration_str)
    title_total = TITLE_SEC * FPS
    for f in range(title_total):
        composited = apply_fade(title_arr, f, title_total)
        write_vid(composited, vid_frame_idx)
        vid_frame_idx += 1
        flush_audio_to(int(vid_frame_idx * audio_per_vid))

    # Slides
    slide_total = SLIDE_SEC * FPS
    prev_arr = None
    for si, snum in enumerate(slide_nums):
        print(f"    Video frame: slide {snum} [{si+1}/{len(slide_nums)}]", end="\r", flush=True)
        slide_arr = load_slide_frame(snum)
        for f in range(slide_total):
            composited = apply_fade(slide_arr, f, slide_total)
            write_vid(composited, vid_frame_idx)
            vid_frame_idx += 1
            flush_audio_to(int(vid_frame_idx * audio_per_vid))
        prev_arr = slide_arr

    # Flush encoders
    for pkt in vid_stream.encode():
        out_container.mux(pkt)
    flush_audio_to(len(audio_scaled))
    for pkt in aud_stream.encode():
        out_container.mux(pkt)
    out_container.close()

    vsize = out_video.stat().st_size / 1024 / 1024
    asize = out_audio.stat().st_size / 1024
    print(f"\n  Done — video: {out_video.name} ({vsize:.1f} MB), audio: {out_audio.name} ({asize:.0f} KB)")

# ── Main ─────────────────────────────────────────────────────────────────────
def main():
    VIDEOS_DIR.mkdir(parents=True, exist_ok=True)
    VOICEOVERS_DIR.mkdir(parents=True, exist_ok=True)

    target = sys.argv[1] if len(sys.argv) > 1 else None
    to_do  = [m for m in MODULES if target is None or m[0] == target]
    if not to_do:
        print(f"Unknown module: {target}")
        print("Available:", ", ".join(m[0] for m in MODULES))
        sys.exit(1)

    t0 = time.time()
    print(f"=== Endur Voiceover Generator ===")
    print(f"TTS engine: flite ({FLITE_VOICE} voice)")
    print(f"Modules: {len(to_do)}")

    for mod_id, title, slides in to_do:
        mod_num = [m[0] for m in MODULES].index(mod_id) + 1
        with tempfile.TemporaryDirectory() as tmpdir:
            encode_module(mod_id, title, slides, mod_num, tmpdir)

    elapsed = time.time() - t0
    print(f"\n=== All done in {elapsed:.0f}s ===")
    print("\nVoiced videos:")
    for f in sorted(VIDEOS_DIR.glob("*-voiced.mp4")):
        print(f"  {f.name}  ({f.stat().st_size/1024/1024:.1f} MB)")
    print("\nStandalone audio:")
    for f in sorted(VOICEOVERS_DIR.glob("*.mp3")):
        print(f"  {f.name}  ({f.stat().st_size/1024:.0f} KB)")

if __name__ == "__main__":
    main()
