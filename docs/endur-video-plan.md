# Endur Training Video Generation Plan

## Overview

Convert `Endur training.pptx` (85 slides, 65 embedded images) into a set of watchable MP4 training videos, one per logical module, served from the Laravel training portal.

## Toolchain Decision

| Tool | Role | Status |
|---|---|---|
| LibreOffice (headless) | Convert PPTX → PNG images (1 per slide) | Available (`/usr/bin/libreoffice`) |
| Remotion (`@remotion/cli`) | React-based video renderer — stitches images into MP4 with transitions, timing, title cards | Needs `npm install` |
| Node.js 22 + npm | Runtime for Remotion | Available |
| python-pptx | Extract slide text/metadata for narration JSON | Needs `pip3 install python-pptx` |

**Why Remotion over alternatives:**
- Remotion ships its own `ffmpeg` binary (no separate install needed)
- Produces proper MP4 files playable in any browser
- Slide timing and transitions are code-controlled (easy to adjust)
- Can add text overlays, module title cards, and chapter markers in React
- Rendering is fully automated — re-run whenever slides change

## Slide Modules

The 85 slides break into 6 self-contained training modules:

| # | Module | Slides | Description |
|---|---|---|---|
| 1 | Introduction to OpenLink & Endur | 1–18 | Company overview, system overview, Endur manager roles |
| 2 | Common System Functionality | 19–31 | Browser window, query manager, table viewer, exports |
| 3 | Admin Manager | 32–39 | System config, compliance, data model extension, transport network |
| 4 | Reference Manager | 40–50 | Portfolios, personnel, party groups, legal entities, party agreements |
| 5 | Market Manager | 51–57 | Indexes, price curves, volatilities, viewing/saving prices |
| 6 | Trading Manager & Trade Lifecycle | 58–85 | Deal entry, trade numbering, validation, portfolio revaluations, lifecycle |

## Directory Structure

```
energytrm/
├── video-generator/              # Remotion project (standalone Node.js)
│   ├── package.json
│   ├── remotion.config.ts
│   ├── src/
│   │   ├── index.ts              # registers all compositions
│   │   ├── Root.tsx              # root component
│   │   ├── SlideShow.tsx         # generic slide sequence composition
│   │   ├── TitleCard.tsx         # module intro card
│   │   └── modules.ts            # slide grouping config
│   └── scripts/
│       ├── extract-slides.sh     # LibreOffice PNG export
│       ├── extract-metadata.py   # python-pptx text extraction
│       └── render-all.sh         # render all 6 module videos
│
├── public/
│   ├── slide-images/             # PNG exports (created by extract-slides.sh)
│   │   ├── slide-001.png … slide-085.png
│   └── videos/                   # final MP4 outputs (git-ignored, large)
│       ├── 01-introduction.mp4
│       ├── 02-common-functionality.mp4
│       ├── 03-admin-manager.mp4
│       ├── 04-reference-manager.mp4
│       ├── 05-market-manager.mp4
│       └── 06-trading-manager.mp4
│
└── app/Http/Controllers/Training/
    └── VideoController.php       # serves the video library page
```

## Implementation Steps

### Step 1 — Export slides to PNG

```bash
cd /path/to/energytrm
mkdir -p public/slide-images

# LibreOffice exports each slide as a numbered PNG
soffice --headless --convert-to png --outdir public/slide-images/ "Endur training.pptx"

# Rename to zero-padded names (LibreOffice names them Endur training1.png etc.)
python3 scripts/rename-slides.py
```

LibreOffice names output files `Endur training1.png` → rename to `slide-001.png` through `slide-085.png`.

### Step 2 — Create Remotion project

```bash
mkdir video-generator && cd video-generator
npm init -y
npm install @remotion/cli @remotion/renderer remotion react react-dom
npm install -D typescript @types/react @types/react-dom
```

### Step 3 — Build the slide composition

`SlideShow.tsx` is the core composition. It:
1. Receives an array of image paths + per-slide duration (default 8 seconds = 240 frames at 30fps)
2. Renders a `<Img>` for the current slide based on `useCurrentFrame()`
3. Adds a 15-frame cross-fade transition between slides
4. Prepends a `TitleCard` (module name + duration) for the first 3 seconds

```tsx
// src/SlideShow.tsx (sketch)
const SLIDE_DURATION = 240; // 8 seconds @ 30fps
const TRANSITION = 15;      // 0.5s cross-fade

export const SlideShow: React.FC<{ slides: string[]; title: string }> = ({ slides, title }) => {
  const frame = useCurrentFrame();
  const currentIndex = Math.floor(frame / SLIDE_DURATION);
  const slideFrame = frame % SLIDE_DURATION;
  const opacity = slideFrame < TRANSITION
    ? slideFrame / TRANSITION          // fade in
    : slideFrame > SLIDE_DURATION - TRANSITION
    ? (SLIDE_DURATION - slideFrame) / TRANSITION  // fade out
    : 1;

  return (
    <AbsoluteFill style={{ background: '#1a1a2e' }}>
      <Img src={slides[currentIndex]} style={{ width: '100%', opacity }} />
    </AbsoluteFill>
  );
};
```

### Step 4 — Register module compositions

`modules.ts` defines the 6 modules with slide ranges. `index.ts` registers each as a Remotion composition:

```ts
// src/modules.ts
export const MODULES = [
  { id: 'introduction',         title: 'Introduction to OpenLink & Endur', slides: range(1, 18) },
  { id: 'common-functionality', title: 'Common System Functionality',       slides: range(19, 31) },
  { id: 'admin-manager',        title: 'Admin Manager',                     slides: range(32, 39) },
  { id: 'reference-manager',    title: 'Reference Manager',                 slides: range(40, 50) },
  { id: 'market-manager',       title: 'Market Manager',                    slides: range(51, 57) },
  { id: 'trading-manager',      title: 'Trading Manager & Trade Lifecycle', slides: range(58, 85) },
];
```

Each composition's `durationInFrames` = `(slideCount * SLIDE_DURATION) + TITLE_CARD_FRAMES`.

### Step 5 — Render all videos

```bash
# video-generator/scripts/render-all.sh
for module in introduction common-functionality admin-manager reference-manager market-manager trading-manager; do
  npx remotion render src/index.ts $module \
    --output ../public/videos/${module}.mp4 \
    --codec h264 \
    --quality 85
done
```

Estimated render time: ~2–5 minutes per video (Remotion renders headless Chrome frames then encodes).

### Step 6 — Laravel integration

**Controller:**
```php
// app/Http/Controllers/Training/VideoController.php
class VideoController extends Controller
{
    public function index()
    {
        $modules = [
            ['id' => 'introduction',         'title' => 'Introduction to OpenLink & Endur', 'duration' => '2:24', 'slides' => 18],
            ['id' => 'common-functionality', 'title' => 'Common System Functionality',       'duration' => '1:44', 'slides' => 13],
            // ...
        ];
        return view('training.videos.index', compact('modules'));
    }

    public function show(string $id)
    {
        // returns the video page for a single module
        return view('training.videos.show', ['id' => $id]);
    }
}
```

**Routes** (added to `routes/web.php` inside the auth group):
```php
Route::prefix('training/videos')->name('training.videos.')->group(function () {
    Route::get('/', [VideoController::class, 'index'])->name('index');
    Route::get('/{module}', [VideoController::class, 'show'])->name('show');
});
```

**View:** A Bootstrap 5 video page with an HTML5 `<video>` player pointing to `/videos/{id}.mp4`, with a sidebar listing all 6 modules for easy navigation.

## Estimated Output

| Module | Slides | Video Length | File Size (est.) |
|---|---|---|---|
| Introduction | 18 | ~2:24 | ~25 MB |
| Common Functionality | 13 | ~1:44 | ~18 MB |
| Admin Manager | 8 | ~1:04 | ~11 MB |
| Reference Manager | 11 | ~1:28 | ~15 MB |
| Market Manager | 7 | ~0:56 | ~10 MB |
| Trading Manager | 28 | ~3:44 | ~50 MB |
| **Total** | **85** | **~11:20** | **~130 MB** |

At 8 seconds/slide, 30fps, H.264 quality 85.

## Enhancements (Post-MVP)

- **Narration sync:** Add a JSON manifest with per-slide narration timestamps; play audio track alongside the video using the Web Audio API or burn it into the MP4 with Remotion's `<Audio>` component.
- **Chapter markers:** Use MP4 chapter atom metadata so the browser's native `<video>` shows chapter thumbnails.
- **Slide highlights:** Add animated callout boxes or zoom-in effects for specific diagram areas using Remotion's `interpolate()`.
- **Auto-rebuild:** Add a `php artisan video:generate` Artisan command that runs the LibreOffice export and Remotion render pipeline on demand.
- **CDN storage:** For production, move MP4s to S3/Cloudflare R2 and update video `src` to CDN URLs to keep the repo clean.

## What Is Not In Scope

- Voiceover audio (no TTS or recording pipeline defined yet)
- Slide-level interactive quizzes (separate feature)
- Screen-recording of the actual ETRM portal (separate Guided Scenario module)
