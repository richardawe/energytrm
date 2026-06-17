#!/usr/bin/env python3
"""
Full voiceover pipeline — Kokoro TTS (am_michael / af_heart) + ambient
background music + caption overlay + H.264+AAC MP4 output.

Usage:
    python3 generate-voiceovers-v2.py                  # all modules
    python3 generate-voiceovers-v2.py admin-manager    # single module
"""

import sys, os, re, time, zipfile, tempfile
from pathlib import Path
import numpy as np
from PIL import Image, ImageDraw, ImageFont
import av
import soundfile as sf
from kokoro_onnx import Kokoro

# ── Paths ─────────────────────────────────────────────────────────────────────
REPO_ROOT      = Path(__file__).resolve().parents[2]
PPTX_PATH      = REPO_ROOT / "Endur training.pptx"
SLIDES_DIR     = REPO_ROOT / "public" / "slide-images"
VIDEOS_DIR     = REPO_ROOT / "public" / "videos"
VOICEOVERS_DIR = REPO_ROOT / "public" / "voiceovers"
KOKORO_MODEL   = Path("/tmp/kokoro/kokoro-v1.0-int8.onnx")
KOKORO_VOICES  = Path("/tmp/kokoro/voices-v1.0.bin")

# ── Timing / video ────────────────────────────────────────────────────────────
FPS          = 30
SLIDE_SEC    = 8
TITLE_SEC    = 3
FADE_FRAMES  = 12
SAMPLE_RATE  = 24000     # Kokoro native rate
MUSIC_VOL    = 0.14      # background music at 14% of voice
W, H         = 1920, 1080

# ── Colours ───────────────────────────────────────────────────────────────────
BG     = (6,  16,  32)
ACCENT = (0,  168, 168)
WHITE  = (240, 245, 255)
MUTED  = (80, 110, 150)

# Caption bar
CAP_BG_ALPHA  = 195       # 0-255, semi-transparent black
CAP_BG_COLOR  = (0, 0, 0)
CAP_TEXT      = (255, 255, 255)
CAP_HIGHLIGHT = (0, 220, 220)   # teal for speaker cue dots
CAP_H         = 130             # caption bar height (pixels)
CAP_FONT_SZ   = 34
CAP_PADDING   = 40

# ── Module definitions ─────────────────────────────────────────────────────────
MODULES = [
    ("introduction",         "Introduction to OpenLink and Endur",    list(range(1,  19)), "am_michael"),
    ("common-functionality", "Common System Functionality",            list(range(19, 32)), "af_heart"),
    ("admin-manager",        "Admin Manager",                          list(range(32, 40)), "am_michael"),
    ("reference-manager",    "Reference Manager",                      list(range(40, 51)), "af_heart"),
    ("market-manager",       "Market Manager",                         list(range(51, 58)), "am_michael"),
    ("trading-manager",      "Trading Manager and Trade Lifecycle",    list(range(58, 86)), "af_heart"),
]

DURATIONS = {
    "introduction":         "2:24",
    "common-functionality": "1:44",
    "admin-manager":        "1:04",
    "reference-manager":    "1:28",
    "market-manager":       "0:56",
    "trading-manager":      "3:44",
}

# ── Slide text extraction ──────────────────────────────────────────────────────
def _clean(t: str) -> str:
    return (t.replace("&amp;", "and").replace("&lt;", "<").replace("&gt;", ">")
             .replace("&quot;", '"').replace("–", "-").replace("—", "-")
             .replace("‘", "'").replace("’", "'")
             .replace("“", '"').replace("”", '"').strip())

def slide_texts(z, snum: int) -> list:
    path = f"ppt/slides/slide{snum}.xml"
    if path not in z.namelist():
        return []
    xml = z.read(path).decode("utf-8", errors="ignore")
    raw = [_clean(t) for t in re.findall(r"<a:t>([^<]+)</a:t>", xml)]
    return [t for t in raw if len(t) > 1]

# ── Narration builder ──────────────────────────────────────────────────────────
SKIP   = {"any questions", "questions", "recap", "q&a", "q & a", "??", "?"}
FILLER = {"", "-", "•", "*"}

def build_narration(texts: list, mod_short: str) -> str:
    filtered = [t for t in texts
                if t.lower().strip("? ") not in SKIP
                and t.strip() not in FILLER and len(t) > 2]
    if not filtered:
        return ""

    # Drop bare module-name repetition at slide top
    mod_word = mod_short.split()[0].lower()
    if filtered[0].lower().split()[0] == mod_word and len(filtered) > 1:
        filtered = filtered[1:]
    if not filtered:
        return ""

    parts = [p.rstrip(":").lstrip("•-*· ").strip() for p in filtered]
    parts = [p for p in parts if p]
    if not parts:
        return ""

    heading = parts[0]
    rest    = parts[1:]

    if not rest:
        return heading.rstrip(".") + "."
    if len(rest) == 1:
        return f"{heading.rstrip('.')}. {rest[0].rstrip('.')}."
    if len(rest) <= 3:
        joined = ", ".join(r.rstrip(".") for r in rest[:-1]) + f", and {rest[-1].rstrip('.')}."
        return f"{heading.rstrip('.')}. This covers {joined}"
    top    = ", ".join(r.rstrip(".") for r in rest[:4])
    suffix = ", among others." if len(rest) > 4 else "."
    return f"{heading.rstrip('.')}. Key topics include {top}{suffix}"

# ── Font cache ─────────────────────────────────────────────────────────────────
_font_cache: dict = {}

def load_font(size, bold=False):
    key = (size, bold)
    if key in _font_cache:
        return _font_cache[key]
    candidates = (
        ["/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf",
         "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"] if bold else
        ["/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
         "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"]
    )
    for p in candidates:
        if os.path.exists(p):
            try:
                f = ImageFont.truetype(p, size)
                _font_cache[key] = f
                return f
            except Exception:
                pass
    f = ImageFont.load_default()
    _font_cache[key] = f
    return f

# ── Caption overlay ────────────────────────────────────────────────────────────
def wrap_text(draw, text: str, font, max_w: int) -> list:
    words  = text.split()
    lines, cur = [], ""
    for word in words:
        test = (cur + " " + word).strip()
        if draw.textlength(test, font=font) <= max_w:
            cur = test
        else:
            if cur:
                lines.append(cur)
            cur = word
    if cur:
        lines.append(cur)
    return lines

def render_caption(base_arr: np.ndarray, caption: str, voice_label: str,
                   frame_idx: int, total_frames: int) -> np.ndarray:
    """Composite a caption bar onto base_arr (RGB numpy). Returns new array."""
    img  = Image.fromarray(base_arr, "RGB")
    draw = ImageDraw.Draw(img, "RGBA")

    # Fade caption in/out with slide
    fade_f = min(FADE_FRAMES, total_frames // 4)
    if frame_idx < fade_f:
        alpha = int(CAP_BG_ALPHA * frame_idx / fade_f)
    elif frame_idx > total_frames - fade_f:
        alpha = int(CAP_BG_ALPHA * (total_frames - frame_idx) / fade_f)
    else:
        alpha = CAP_BG_ALPHA

    if alpha <= 0 or not caption:
        return np.array(img)

    # Semi-transparent caption background
    bar_y = H - CAP_H
    draw.rectangle([(0, bar_y), (W, H)], fill=(*CAP_BG_COLOR, alpha))

    # Wrap text
    font     = load_font(CAP_FONT_SZ)
    max_w    = W - CAP_PADDING * 2
    lines    = wrap_text(draw, caption, font, max_w)

    lh       = CAP_FONT_SZ + 10
    total_th = len(lines) * lh
    start_y  = bar_y + (CAP_H - total_th) // 2

    txt_alpha = alpha
    for i, line in enumerate(lines[:3]):   # max 3 caption lines
        y = start_y + i * lh
        draw.text((W // 2, y), line, font=font,
                  fill=(*CAP_TEXT, txt_alpha), anchor="mm")

    # Small speaker indicator bottom-right
    dot_font = load_font(20)
    speaker  = f"♪  {voice_label}"
    draw.text((W - CAP_PADDING, H - 22), speaker, font=dot_font,
              fill=(*CAP_HIGHLIGHT, alpha // 2), anchor="rm")

    return np.array(img.convert("RGB"))

# ── Ambient music generator ────────────────────────────────────────────────────
def generate_music(duration_sec: float, sr: int = SAMPLE_RATE) -> np.ndarray:
    n      = int(duration_sec * sr)
    music  = np.zeros(n, dtype=np.float32)

    chord_freqs = [
        [110.0, 130.8, 164.8],   # Am
        [ 87.3, 110.0, 130.8],   # F
        [130.8, 164.8, 196.0],   # C
        [ 98.0, 123.5, 146.8],   # G
    ]
    chord_n = int(SLIDE_SEC * sr)
    rng     = np.random.default_rng(42)
    ci      = 0
    pos     = 0

    while pos < n:
        freqs  = chord_freqs[ci % len(chord_freqs)]
        end    = min(pos + chord_n, n)
        seg_n  = end - pos
        seg_t  = np.linspace(0, SLIDE_SEC, seg_n, endpoint=False)

        env    = np.ones(seg_n, dtype=np.float32)
        att    = int(0.8 * sr)
        rel    = int(1.2 * sr)
        if att < seg_n: env[:att]  = np.linspace(0, 1, att)
        if rel < seg_n: env[-rel:] *= np.linspace(1, 0, rel)

        seg = np.zeros(seg_n, dtype=np.float32)
        for freq in freqs:
            d = 1 + rng.uniform(-0.002, 0.002)
            f = freq * d
            seg += np.sin(2 * np.pi * f       * seg_t) * 0.50
            seg += np.sin(2 * np.pi * f * 2   * seg_t) * 0.15
            seg += np.sin(2 * np.pi * f * 3   * seg_t) * 0.06
            seg += np.sin(2 * np.pi * f * 0.5 * seg_t) * 0.10
        seg *= env
        music[pos:end] += seg
        pos = end
        ci += 1

    # Simple reverb
    for delay_ms, decay in [(60, 0.25), (120, 0.15), (240, 0.08)]:
        d = int(delay_ms * sr / 1000)
        if d < len(music):
            music[d:] += music[:-d] * decay

    # Low-pass smooth
    music = np.convolve(music, np.ones(32) / 32, mode="same")
    peak  = np.max(np.abs(music)) + 1e-8
    return (music / peak * 0.5).astype(np.float32)

# ── Video frame helpers ────────────────────────────────────────────────────────
def make_title_frame(num, title, slides, dur_str):
    img  = Image.new("RGB", (W, H), BG)
    draw = ImageDraw.Draw(img)
    draw.rectangle([(0, 0), (W, 6)],    fill=ACCENT)
    draw.rectangle([(0, H - 4), (W, H)], fill=ACCENT)

    f_sm = load_font(26, bold=True)
    f_lg = load_font(66, bold=True)
    f_md = load_font(28)

    draw.text((W // 2, H // 2 - 120), f"MODULE {num}",
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
    sy = H // 2 - (len(lines) * lh) // 2
    for i, ln in enumerate(lines):
        draw.text((W // 2, sy + i * lh), ln, font=f_lg, fill=WHITE, anchor="mm")

    draw.text((W // 2, H // 2 + (len(lines) * lh) // 2 + 52),
              f"{slides} slides  ·  {dur_str}  ·  Endur Training Series",
              font=f_md, fill=MUTED, anchor="mm")
    return np.array(img)

def load_slide_frame(snum):
    p = SLIDES_DIR / f"slide-{snum:03d}.png"
    return np.array(Image.open(p).convert("RGB").resize((W, H), Image.LANCZOS))

def apply_fade(arr, f, total):
    if f < FADE_FRAMES:
        a = f / FADE_FRAMES
        return (np.full_like(arr, BG) * (1 - a) + arr * a).astype(np.uint8)
    if f > total - FADE_FRAMES:
        a = (total - f) / FADE_FRAMES
        return (np.full_like(arr, BG) * (1 - a) + arr * a).astype(np.uint8)
    return arr

# ── Module encoder ─────────────────────────────────────────────────────────────
def encode_module(kokoro, mod_id, title, slide_nums, mod_num, voice):
    out_path   = VIDEOS_DIR / f"{mod_id}-voiced.mp4"
    dur_str    = DURATIONS.get(mod_id, "")
    mod_short  = title.split()[0]
    voice_label = "Michael" if voice == "am_michael" else "Heart"

    print(f"\n{'─' * 60}")
    print(f"  Module {mod_num}: {title}  [{voice_label}]")
    print(f"  Slides {slide_nums[0]}–{slide_nums[-1]}  ({len(slide_nums)} slides)")

    # ── 1. Extract slide text + build narrations ──────────────────────────
    with zipfile.ZipFile(PPTX_PATH) as z:
        texts_map = {s: slide_texts(z, s) for s in slide_nums}

    narrations = {s: build_narration(texts_map[s], mod_short) for s in slide_nums}

    # ── 2. TTS synthesis ──────────────────────────────────────────────────
    print("  TTS synthesis...", flush=True)
    segments = []

    title_script  = f"Module {mod_num}. {title}."
    tc_audio, _   = kokoro.create(title_script, voice=voice, speed=0.95, lang="en-us")
    tc_audio      = tc_audio.astype(np.float32)
    tc_n          = int(TITLE_SEC * SAMPLE_RATE)
    tc_audio      = np.concatenate([tc_audio, np.zeros(max(0, tc_n - len(tc_audio)))])[:tc_n]
    segments.append(tc_audio)

    for i, snum in enumerate(slide_nums):
        narr   = narrations[snum]
        tgt_n  = int(SLIDE_SEC * SAMPLE_RATE)
        if narr:
            audio, _ = kokoro.create(narr, voice=voice, speed=0.95, lang="en-us")
            audio = audio.astype(np.float32)
            audio = np.concatenate([audio, np.zeros(max(0, tgt_n - len(audio)))])[:tgt_n]
        else:
            audio = np.zeros(tgt_n, dtype=np.float32)
        segments.append(audio)
        print(f"    [{i+1:2d}/{len(slide_nums)}] slide {snum:3d}  {len(narr)} chars", end="\r", flush=True)

    print()

    # ── 3. Music + mix ────────────────────────────────────────────────────
    voice_full = np.concatenate(segments).astype(np.float32)
    total_sec  = len(voice_full) / SAMPLE_RATE
    print(f"  Generating music ({total_sec:.0f}s)...", flush=True)

    music  = generate_music(total_sec, SAMPLE_RATE)
    if len(music) < len(voice_full):
        music = np.tile(music, int(np.ceil(len(voice_full) / len(music))))
    music  = music[:len(voice_full)]
    mixed  = voice_full + music * MUSIC_VOL
    peak   = np.max(np.abs(mixed)) + 1e-8
    if peak > 0.95:
        mixed = mixed / peak * 0.95
    mixed  = mixed.astype(np.float32)
    audio_i16 = np.clip(mixed * 32767, -32767, 32767).astype(np.int16)

    # ── 4. Encode MP4 ─────────────────────────────────────────────────────
    print("  Encoding MP4...", flush=True)
    VIDEOS_DIR.mkdir(parents=True, exist_ok=True)
    out_c = av.open(str(out_path), mode="w")
    vid   = out_c.add_stream("h264", rate=FPS)
    vid.width, vid.height, vid.pix_fmt = W, H, "yuv420p"
    vid.options = {"crf": "22", "preset": "fast", "profile": "high"}
    aud   = out_c.add_stream("aac", rate=SAMPLE_RATE)
    aud.layout = "mono"

    audio_pos       = 0
    samples_per_frm = SAMPLE_RATE / FPS
    vfidx           = 0

    def write_vframe(arr):
        nonlocal vfidx
        frame = av.VideoFrame.from_ndarray(arr, format="rgb24")
        frame = frame.reformat(format="yuv420p")
        frame.pts = vfidx
        for pkt in vid.encode(frame):
            out_c.mux(pkt)
        vfidx += 1

    def flush_audio(target):
        nonlocal audio_pos
        chunk = 1024
        while audio_pos < target and audio_pos < len(audio_i16):
            end  = min(audio_pos + chunk, len(audio_i16), target)
            data = audio_i16[audio_pos:end]
            af   = av.AudioFrame.from_ndarray(data.reshape(1, -1), format="s16", layout="mono")
            af.sample_rate = SAMPLE_RATE
            af.pts         = audio_pos
            for pkt in aud.encode(af):
                out_c.mux(pkt)
            audio_pos = end

    # Title card (no content caption — just title narration text)
    title_arr = make_title_frame(mod_num, title, len(slide_nums), dur_str)
    tc_frames = TITLE_SEC * FPS
    title_caption = title_script
    for f in range(tc_frames):
        base = apply_fade(title_arr, f, tc_frames)
        captioned = render_caption(base, title_caption, voice_label, f, tc_frames)
        write_vframe(captioned)
        flush_audio(int(vfidx * samples_per_frm))

    # Slides
    sf_n = SLIDE_SEC * FPS
    for si, snum in enumerate(slide_nums):
        print(f"    video: slide {snum} [{si+1}/{len(slide_nums)}]", end="\r", flush=True)
        sarr    = load_slide_frame(snum)
        caption = narrations[snum]
        for f in range(sf_n):
            base      = apply_fade(sarr, f, sf_n)
            captioned = render_caption(base, caption, voice_label, f, sf_n)
            write_vframe(captioned)
            flush_audio(int(vfidx * samples_per_frm))

    for pkt in vid.encode():   out_c.mux(pkt)
    flush_audio(len(audio_i16))
    for pkt in aud.encode():   out_c.mux(pkt)
    out_c.close()

    # Standalone voice WAV
    VOICEOVERS_DIR.mkdir(parents=True, exist_ok=True)
    sf.write(str(VOICEOVERS_DIR / f"{mod_id}.wav"), voice_full, SAMPLE_RATE)

    mb = out_path.stat().st_size / 1024 / 1024
    print(f"\n  ✓ {out_path.name} ({mb:.1f} MB)")

# ── Main ───────────────────────────────────────────────────────────────────────
def main():
    target = sys.argv[1] if len(sys.argv) > 1 else None
    to_do  = [m for m in MODULES if target is None or m[0] == target]
    if not to_do:
        print(f"Unknown module: {target}")
        print("Available:", ", ".join(m[0] for m in MODULES))
        sys.exit(1)

    print("Loading Kokoro TTS model...")
    kokoro = Kokoro(str(KOKORO_MODEL), str(KOKORO_VOICES))
    print("Model loaded.\n")

    t0 = time.time()
    print("=== Endur Voiceover Generator v2 ===")
    print("Voices: am_michael (male, modules 1/3/5) + af_heart (female, modules 2/4/6)")
    print(f"Background music: ambient Am-F-C-G pad @ {int(MUSIC_VOL*100)}% volume")
    print("Captions: narration text overlaid bottom of each frame")

    for mod_id, title, slides, voice in to_do:
        mod_num = [m[0] for m in MODULES].index(mod_id) + 1
        encode_module(kokoro, mod_id, title, slides, mod_num, voice)

    elapsed = time.time() - t0
    print(f"\n=== All done in {elapsed:.0f}s ===")
    print("\nVoiced videos:")
    for f in sorted(VIDEOS_DIR.glob("*-voiced.mp4")):
        print(f"  {f.name}  ({f.stat().st_size/1024/1024:.1f} MB)")

if __name__ == "__main__":
    main()
