import React from "react";
import { Composition } from "remotion";
import { SlideShow } from "./SlideShow";
import {
  MODULES,
  FPS,
  SLIDE_DURATION_FRAMES,
  TITLE_CARD_FRAMES,
} from "./modules";

export const RemotionRoot: React.FC = () => {
  return (
    <>
      {MODULES.map((mod, idx) => {
        const totalFrames =
          TITLE_CARD_FRAMES + mod.slides.length * SLIDE_DURATION_FRAMES;

        return (
          <Composition
            key={mod.id}
            id={mod.id}
            component={SlideShow}
            durationInFrames={totalFrames}
            fps={FPS}
            width={1920}
            height={1080}
            defaultProps={{
              module: mod,
              moduleIndex: idx,
              slideDuration: SLIDE_DURATION_FRAMES,
            }}
          />
        );
      })}
    </>
  );
};
