# Syntekpro Animations 2.0 Roadmap (Working Draft)

## Tracks
- Builder UX: visual timeline, keyframes, playhead, scrubbing; grouped sequences.
- Engines: Auto / CSS / GSAP with compatibility matrix and badges.
- Performance: lazy init, reduced-motion fallback, per-page asset loading, CSS-first where possible.
- Blocks & Presets: hero/card/CTA/stats/timeline presets, export/import JSON.
- Integration: compat shims, hooks/filters, shortcode parity.
- Analytics/Debug: optional overlay, markers, and console toggle.

## Milestones
1) **Engine & Perf**: finalize CSS coverage matrix; add GSAP-only badge; per-page asset loading hooks.
2) **Builder Alpha**: stub UI under `builder/` with timeline skeleton and play/pause/scrub.
3) **Presets Library**: JSON-driven presets under `presets/json`; import/export UI.
4) **Blocks**: ship hero/card/CTA presets in inserter variations; stats counter and parallax hero.
5) **Integration**: compat mode toggles; hooks to register custom presets.
6) **Debug**: overlay for engine/trigger markers; reduced motion handling.

## Folder map (added)
- `builder/` (js, css) – visual timeline/editor assets.
- `presets/json/` – preset definitions for import/export.
- `integration/` – compat helpers and shims.
- `css-animations/` – CSS keyframes/maps for light mode.
- `analytics/` – telemetry/debug overlay (opt-in).
- `docs/` – roadmap and future guides.

## Notes
- Keep ASCII-only; avoid heavy dependencies until needed.
- Default to Auto engine; allow CSS-only and GSAP-only overrides at block and global levels.
