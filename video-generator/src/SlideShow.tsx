import React from "react";
import {
  AbsoluteFill,
  Img,
  Series,
  interpolate,
  staticFile,
  useCurrentFrame,
  useVideoConfig,
} from "remotion";
import { TitleCard } from "./TitleCard";
import { Module, TITLE_CARD_FRAMES } from "./modules";

const FADE_FRAMES = 12;

const DURATIONS: Record<string, string> = {
  introduction: "2:24",
  "common-functionality": "1:44",
  "admin-manager": "1:04",
  "reference-manager": "1:28",
  "market-manager": "0:56",
  "trading-manager": "3:44",
};

// ------------------------------------------------------------------
// A single slide frame: fades in, holds, fades out
// ------------------------------------------------------------------
const SlideFrame: React.FC<{ src: string }> = ({ src }) => {
  const frame = useCurrentFrame();
  const { durationInFrames } = useVideoConfig();

  const opacity = interpolate(
    frame,
    [0, FADE_FRAMES, durationInFrames - FADE_FRAMES, durationInFrames],
    [0, 1, 1, 0],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  return (
    <AbsoluteFill style={{ background: "#061020" }}>
      <Img
        src={src}
        style={{
          width: "100%",
          height: "100%",
          objectFit: "contain",
          opacity,
        }}
      />
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------
// Main composition: title card + slide sequence
// ------------------------------------------------------------------
type Props = {
  module: Module;
  moduleIndex: number;
  slideDuration: number;
};

export const SlideShow: React.FC<Props> = ({
  module,
  moduleIndex,
  slideDuration,
}) => {
  const duration = DURATIONS[module.id] ?? "";

  return (
    <Series>
      <Series.Sequence durationInFrames={TITLE_CARD_FRAMES}>
        <TitleCard
          title={module.title}
          moduleNumber={moduleIndex + 1}
          totalSlides={module.slides.length}
          duration={duration}
        />
      </Series.Sequence>

      {module.slides.map((slideNum) => (
        <Series.Sequence key={slideNum} durationInFrames={slideDuration}>
          <SlideFrame
            src={staticFile(
              `slide-images/slide-${String(slideNum).padStart(3, "0")}.png`
            )}
          />
        </Series.Sequence>
      ))}
    </Series>
  );
};
