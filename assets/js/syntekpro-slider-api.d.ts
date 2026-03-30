export interface SliderConfig {
  autoplay: boolean;
  autoplayDelay: number;
  loop: boolean;
  transition: string;
  transitionSpeed: number;
  reducedMotionMode?: boolean;
  liveDataRefresh?: number;
  [key: string]: unknown;
}

export interface SliderState {
  sliderId: string;
  index: number;
  slides: HTMLElement[];
  root: HTMLElement;
  config: SliderConfig;
  next(): void;
  prev(): void;
  goTo(index: number): void;
  destroy?(): void;
}

export interface SliderAnalyticsEvent {
  sliderId: string;
  event: string;
  index: number;
  ts: number;
  [key: string]: unknown;
}

export interface SyntekproSliderAPI {
  get(sliderId: string): SliderState | null;
  getAll(): Record<string, SliderState>;
  next(sliderId: string): void;
  prev(sliderId: string): void;
  goTo(sliderId: string, index: number): void;
  dragAndDropEditor(): boolean;
  layerSystem(): boolean;
  slideManagerPanel(): boolean;
  undoRedoHistory(): boolean;
  globalSliderSettings(): boolean;
  starterTemplateLibrary(): boolean;
  perLayerAnimations(): boolean;
  slideTransitionEffects(): boolean;
  parallaxAndScrollEffects(): boolean;
  hoverAndClickTriggers(): boolean;
  animationTimelineEditor(): boolean;
  breakpointEditor(): boolean;
  touchAndSwipeGestures(): boolean;
  fluidScalingModes(): boolean;
  perBreakpointVisibility(): boolean;
  lazyLoadingAndPreloading(): boolean;
  assetOptimization(): boolean;
  seoAndAccessibility(): boolean;
  dynamicContentSources(): boolean;
  developerHooksAndAPI(): boolean;
  builtInAnalytics(): boolean;
}

declare global {
  interface Window {
    SyntekproSliderAPI: SyntekproSliderAPI;
  }

  interface HTMLElementEventMap {
    "syntekpro:slider-ready": CustomEvent<{ sliderId: string; count: number }>;
    "syntekpro:slider-change": CustomEvent<{ sliderId: string; index: number }>;
    "syntekpro:slider-analytics": CustomEvent<SliderAnalyticsEvent>;
    "syntekpro:slider-popup": CustomEvent<{ target: string; sliderId: string }>;
  }
}

export {};
