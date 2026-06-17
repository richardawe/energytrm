export type Module = {
  id: string;
  title: string;
  description: string;
  slides: number[];
};

const range = (start: number, end: number): number[] =>
  Array.from({ length: end - start + 1 }, (_, i) => start + i);

export const MODULES: Module[] = [
  {
    id: "introduction",
    title: "Introduction to OpenLink & Endur",
    description:
      "Overview of the Endur platform, its modules, and how Front Office, Middle Office, and Back Office roles interact.",
    slides: range(1, 18),
  },
  {
    id: "common-functionality",
    title: "Common System Functionality",
    description:
      "Browser window navigation, query manager, table viewer configuration, and data export.",
    slides: range(19, 31),
  },
  {
    id: "admin-manager",
    title: "Admin Manager",
    description:
      "System configuration, compliance tools, activity audit, data model extension, and transport network.",
    slides: range(32, 39),
  },
  {
    id: "reference-manager",
    title: "Reference Manager",
    description:
      "Managing portfolios, personnel, party groups, legal entities, business units, and party agreements.",
    slides: range(40, 50),
  },
  {
    id: "market-manager",
    title: "Market Manager",
    description:
      "Defining indexes, price curves, volatilities, correlations, and viewing/saving market prices.",
    slides: range(51, 57),
  },
  {
    id: "trading-manager",
    title: "Trading Manager & Trade Lifecycle",
    description:
      "Deal entry, trade numbering, power trading products, validation, portfolio revaluations, and the full trade lifecycle.",
    slides: range(58, 85),
  },
];

export const SLIDE_DURATION_FRAMES = 240; // 8 seconds @ 30 fps
export const TITLE_CARD_FRAMES = 90;      // 3 seconds
export const FPS = 30;
