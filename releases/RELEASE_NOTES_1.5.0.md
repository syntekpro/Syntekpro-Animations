# Syntekpro Animations 1.5.0 Release Notes

Release date: 2026-04-02

## Summary
Version 1.5.0 introduces the slider merge milestone: SyntekPro Slider is now embedded directly into Syntekpro Animations as an internal module.

## What is new
- Added embedded slider module under `modules/syntekpro-slider`.
- Added merge loader `includes/class-slider-merge.php` to bootstrap slider runtime from the internal module.
- Added fallback support to load from sibling standalone slider plugin path when internal module is unavailable.
- Added protection against duplicate runtime initialization when the standalone slider plugin is active.
- Mirrored merge/runtime bootstrapping to `dist/syntekpro-animations` for packaged release parity.

## Version updates
- Syntekpro Animations plugin version bumped to `1.5.0`.
- Embedded slider runtime default version constants bumped to `1.5.0`.
- Documentation updated:
  - `README.md`
  - `CHANGELOG.md`
  - `COMPLETE_GUIDE.md`

## Upgrade notes
- If using Syntekpro Animations 1.5.0+, slider features can run from the embedded module without activating standalone SyntekPro Slider.
- If standalone SyntekPro Slider is active, merge loader safely skips duplicate bootstrap.

## Packaging
Suggested release artifact name:
- `syntekpro-animations-1.5.0.zip`
