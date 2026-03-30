# Syntekpro Animations -> Smart Slider-Style Transformation Plan

## Goal
Evolve Syntekpro Animations from an animation toolkit into a full slider platform similar to Smart Slider 3, with:
- visual slide builder
- slider library and reusable templates
- layers (heading, text, image, button, video, shape)
- responsive controls (desktop/tablet/mobile)
- timeline-based animation and transitions
- performance-focused frontend runtime

## Current Baseline (What You Already Have)
- Block foundation and custom blocks: `includes/blocks/`
- Existing carousel block: `includes/blocks/class-carousel-block.php`
- Preset/animation engine runtime: `assets/js/animations.js`
- Admin dashboard with multiple tools: `includes/class-admin.php`
- Pattern data/pattern browser infrastructure: usable for templates

## Product Direction
Build this as a "Slider Platform" inside the plugin with three pillars:
1. Slider Builder (editor experience)
2. Slider Runtime (frontend output and playback)
3. Slider Library (templates/import/export/reuse)

## Recommended Information Architecture (Admin)
Top-level: SyntekPro Animations
- Dashboard
- Sliders
- Slide Templates
- Animations+
- Presets
- Settings
- Help
- System Status

## Data Model (MVP)
Use CPT + post meta first, then optimize.

### Custom Post Type
- `syntekpro_slider` (public false, show_ui true)

### Core Meta (JSON)
- `_sp_slider_settings`
- `_sp_slider_breakpoints`
- `_sp_slider_slides`
- `_sp_slider_version`

### Slider Settings Schema (MVP)
- layout: boxed/fullwidth/fullscreen
- heightDesktop/Tablet/Mobile
- autoplay: bool
- autoplayDelay
- loop
- pauseOnHover
- navigation: arrows/dots/none
- transition: slide/fade/cube/coverflow
- transitionDuration
- lazyLoadImages
- preloadStrategy

### Slide Schema (MVP)
- id
- background: color/image/video/gradient
- layers: []
- duration
- kenBurns (optional)

### Layer Schema (MVP)
- id
- type: heading/text/image/button/video/shape
- content
- styles per breakpoint
- position per breakpoint
- entrance animation
- exit animation
- timeline offset/duration/ease

## Frontend Runtime Plan
Create a dedicated runtime bundle:
- `assets/js/slider-runtime.js`
- `assets/css/slider-runtime.css`

Runtime responsibilities:
- parse slider JSON
- render layers and transitions
- handle autoplay/interaction
- touch/drag support
- lazy loading and visibility-based init
- respect reduced motion and mobile settings

## Builder Plan
Phase in builder complexity:
1. Form-based builder (fast to ship)
2. Visual canvas (drag/drop layer editor)
3. Timeline panel (keyframes and sequencing)

Builder files:
- `builder/js/slider-builder.js`
- `builder/css/slider-builder.css`

## Template/Import/Export Plan
Use your existing preset/pattern approach as inspiration.

MVP:
- Save slider as template
- Export single slider JSON
- Import slider JSON with validation
- Versioned schema with migration handlers

## Performance Targets
- No runtime loaded unless slider present
- LCP-safe image loading
- Defer non-critical scripts
- CSS-first where possible
- JS timeline only when enabled

## Accessibility Targets
- keyboard navigation
- ARIA labels for controls
- pause/play controls for autoplay
- reduced motion mode
- focus-visible support

## Security/Hardening
- capability checks for all save/import actions
- nonce checks for admin actions
- strict JSON schema validation on import
- sanitize all layer content

## Feature Parity Matrix (High-Level)
Smart Slider-like must-have:
- visual slide builder
- layer animations
- responsive breakpoints
- slider templates
- export/import
- autoplay + navigation controls
- SEO-friendly output

Differentiators for SyntekPro:
- deeper GSAP timeline integration
- CSS/GSAP engine switch
- block-native insertion workflow
- design-token-driven styling

## 5 Milestones

### Milestone 1 - Slider Core (1-2 weeks)
- register `syntekpro_slider` CPT
- admin list and create/edit screens
- slider shortcode: `[sp_slider id="123"]`
- basic runtime output (background + text/image/button)

### Milestone 2 - Builder MVP (2-3 weeks)
- form-based slide/layer editor
- save/load JSON meta
- preview panel in admin

### Milestone 3 - Runtime Pro (2 weeks)
- transition engine
- autoplay/loop/nav/touch
- lazy loading/performance hardening

### Milestone 4 - Templates + Library (1-2 weeks)
- save as template
- import/export with validation
- template gallery page

### Milestone 5 - Advanced Features (2-4 weeks)
- timeline editor
- per-layer keyframes
- motion paths/video layers/parallax
- Animations+ premium packs

## Immediate Implementation Backlog (Start Here)
1. Add new file: `includes/class-slider-core.php`
2. Register `syntekpro_slider` CPT
3. Add slider rendering shortcode `[sp_slider]`
4. Add frontend enqueue logic for slider runtime
5. Add "Sliders" admin submenu and list link
6. Add slider settings meta box (JSON schema v1)
7. Add safe save handler with nonce + capability checks

## Definition of Done for MVP
- user can create a slider in admin
- user can add at least 3 slides with title/image/button
- slider renders on frontend via shortcode
- autoplay + nav + loop configurable
- no JS/CSS loaded on pages without sliders

## Suggested Next Commit Sequence
1. `feat(slider): add slider CPT and shortcode renderer`
2. `feat(slider): add slider runtime enqueue and frontend assets`
3. `feat(slider): add slider settings meta box and save handler`
4. `feat(slider): add sliders admin menu and listing entry points`
5. `feat(slider): add template import-export schema v1`

## Notes
- Keep existing animation presets and blocks intact during migration.
- Build slider platform as additive module first, then decide what to deprecate.
- Keep schema versioned from day one for safe upgrades.
