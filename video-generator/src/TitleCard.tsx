import React from "react";
import { AbsoluteFill, interpolate, useCurrentFrame } from "remotion";

type Props = {
  title: string;
  moduleNumber: number;
  totalSlides: number;
  duration: string;
};

export const TitleCard: React.FC<Props> = ({
  title,
  moduleNumber,
  totalSlides,
  duration,
}) => {
  const frame = useCurrentFrame();

  const opacity = interpolate(frame, [0, 18, 72, 90], [0, 1, 1, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  const translateY = interpolate(frame, [0, 18], [24, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  return (
    <AbsoluteFill
      style={{
        background: "linear-gradient(135deg, #0f2139 0%, #0a1529 60%, #061020 100%)",
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        fontFamily: "'Arial', sans-serif",
        overflow: "hidden",
      }}
    >
      {/* Accent line top */}
      <div
        style={{
          position: "absolute",
          top: 0,
          left: 0,
          right: 0,
          height: 6,
          background: "linear-gradient(90deg, #00a8a8, #0066cc)",
        }}
      />

      {/* Content */}
      <div
        style={{
          opacity,
          transform: `translateY(${translateY}px)`,
          textAlign: "center",
          padding: "0 120px",
        }}
      >
        <div
          style={{
            fontSize: 22,
            color: "#00c8c8",
            letterSpacing: 8,
            textTransform: "uppercase",
            marginBottom: 32,
            fontWeight: 600,
          }}
        >
          Module {moduleNumber}
        </div>

        <div
          style={{
            fontSize: 68,
            color: "#f0f5ff",
            fontWeight: 700,
            lineHeight: 1.15,
            marginBottom: 40,
          }}
        >
          {title}
        </div>

        <div
          style={{
            display: "flex",
            gap: 48,
            justifyContent: "center",
            alignItems: "center",
          }}
        >
          <div style={{ textAlign: "center" }}>
            <div style={{ fontSize: 36, color: "#00a8a8", fontWeight: 700 }}>
              {totalSlides}
            </div>
            <div style={{ fontSize: 18, color: "rgba(200,215,235,0.7)", marginTop: 4 }}>
              slides
            </div>
          </div>
          <div
            style={{
              width: 1,
              height: 48,
              background: "rgba(255,255,255,0.2)",
            }}
          />
          <div style={{ textAlign: "center" }}>
            <div style={{ fontSize: 36, color: "#00a8a8", fontWeight: 700 }}>
              {duration}
            </div>
            <div style={{ fontSize: 18, color: "rgba(200,215,235,0.7)", marginTop: 4 }}>
              duration
            </div>
          </div>
        </div>
      </div>

      {/* Endur branding bottom */}
      <div
        style={{
          position: "absolute",
          bottom: 36,
          fontSize: 18,
          color: "rgba(255,255,255,0.3)",
          letterSpacing: 3,
          textTransform: "uppercase",
        }}
      >
        Endur Training Series
      </div>

      {/* Accent line bottom */}
      <div
        style={{
          position: "absolute",
          bottom: 0,
          left: 0,
          right: 0,
          height: 4,
          background: "linear-gradient(90deg, #0066cc, #00a8a8)",
        }}
      />
    </AbsoluteFill>
  );
};
