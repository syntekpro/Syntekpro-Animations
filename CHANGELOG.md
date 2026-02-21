# Changelog - Syntekpro Animations

All notable changes to this project will be documented in this file.

---

## [2.2.3] - 2026-02-22

### Added
- Added an uninstall handler that removes all Syntekpro options (general settings, plugin toggles, and license data) plus the syntekpro-animations upload directory.

### Changed
- Admin footers now show only the plugin name, version, and contextual hints so the previous "Powered by SyntekPro" badge drops from every settings screen.

### Notes
- All version metadata was bumped to 2.2.3 for the WordPress.org listing.

## [2.2.2] - 2026-02-01

### Added
- Ten new block patterns (hero split, stats row, logo strip, steps, checklist, comparison table, newsletter band, gallery tiles, testimonial highlight, CTA minimal).
- Patterns card/link added to the dashboard for faster access.

### Changed
- Pattern Data page simplified to form-first controls without raw JSON blocks; textareas use friendly styling.
- Admin menu icon hover now stays neutral grey instead of blue.

### Fixed
- Pattern browser previews cover the new patterns with consistent variants and accents.

### Notes
- Version metadata aligned across plugin files to 2.2.2.

## [2.3.0] - 2026-01-31

### Added
- Design system token sheet (colors, typography, spacing, radius, shadows, motion) shared across frontend, editor, and admin.
- Block editor now consumes the design system for consistent block previews.
- Pricing, FAQ, and testimonial dynamic patterns now render with design-system tokens for colors and surfaces.

### Changed
- Frontend, block-rendered pages, and admin screens enqueue the shared design-system stylesheet ahead of existing styles.
- Admin styles (nav, cards, license badges, upgrade box) rethemed to the shared tokens.

### Notes
- Patterns will gradually be refit to the token set; existing layouts keep their current appearance until updated.

## [2.1.1] - 2026-01-30

## [2.1.2] - 2026-01-31

### Added
- Dynamic pricing, FAQ, and testimonial pattern blocks that render directly from shared Pattern Data options.
- Block pattern registrations now insert the dynamic blocks so new uses always reflect saved data.

### Changed
- Shared pattern data helpers consolidated in Gutenberg integration for reuse and defaults.

### Notes
- Existing pages that used the old static patterns should be re-inserted with the new dynamic patterns to auto-reflect data edits.

### Added
- New **Help** admin page with curated user, developer, and designer documentation blocks plus quick links to on-site resources.
- New **System Status** page showing environment report (WordPress/PHP/server), plugin version badge, and update notes.

### Changed
- Version metadata aligned across plugin files to 2.1.1.

## [1.2.0] - 2026-01-30

### 🎉 Major Update - Full-Fledged Animation Engine

This release transforms Syntekpro Animations into a complete, professional-grade animation solution for WordPress.

  - Zoom animations (zoomIn, zoomInUp, zoomInDown, zoomInLeft, zoomInRight)
  - Reveal animations (revealLeft, revealRight, revealUp, revealDown)
  - Wave animations (waveIn, ripple)
  - Swing animations (swingIn, pendulum)
  - Fold animations (unfoldHorizontal, unfoldVertical) - Pro
  - Peel animations (peelLeft, peelRight) - Pro
  - Instant code generation
  - Copy-to-clipboard functionality
  - Export timeline configurations
  - Sortable interface

- **📊 Enhanced Admin Dashboard**
  - Create complex animation sequences
  - Scrub support for scroll-linked animations
  - Custom trigger elements

  - Automatic element detection
  - Configurable delay patterns

- **Hover Effects** - `[sp_hover_effect]` shortcode
  - Interactive hover animations
  - Scale, lift, rotate, and glow effects
  - Advanced ScrollTrigger integration
  - Pin elements during scroll
  - Scrub animations
  - Debug markers option
  - Popular animations reference
  - Direct support links

- **Complete User Guide** - Comprehensive documentation
  - Step-by-step tutorials
  - All animation types documented
  - Code examples for every feature
  - Best practices guide
  - Troubleshooting section

- **In-Dashboard Documentation**
  - Enhanced documentation page
  - Interactive code examples
  - Visual guides
  - Video tutorials (coming soon)

### 🎨 Improved

#### Branding & Identity
- Removed all external GSAP references and links
- Rebranded as "Syntekpro Animation Engine"
- Updated all documentation to reflect Syntekpro branding
- Custom attribution throughout codebase
- Professional branded admin interface

#### User Interface
- Modern gradient-based design
- Improved color scheme (red/blue/green palette)
- Better responsive layouts
- Enhanced hover states and transitions
- Cleaner card-based layouts
- Improved typography and spacing

#### Admin Experience
- Faster navigation between pages
- Better visual hierarchy
- More intuitive controls
- Clear Pro feature indicators
- Improved settings organization

#### Performance
- Optimized JavaScript loading
- Conditional script enqueuing
- Reduced CSS file size
- Better animation presets structure
- Improved ScrollTrigger integration

### 🔧 Changed

#### Settings & Configuration
- Renamed "GSAP Library" to "Animation Engine"
- Updated plugin description and metadata
- Reorganized plugin settings
- Enhanced free vs pro distinction
- Better default settings

#### Code Structure
- Added `class-advanced-features.php` for new shortcodes
- Added `class-help-system.php` for help widget
- Enhanced `class-animation-presets.php` with 30+ new presets
- Improved `animations.js` with all new animation types
- Created `admin-preview.js` for interactive admin features
- Enhanced `admin-settings-ui.css` with new styles

#### Documentation
- Removed GSAP external documentation links
- Added comprehensive USER_GUIDE.md
- Updated all inline documentation
- Enhanced code comments
- Improved README files

### 🐛 Fixed
- Animation timing consistency
- ScrollTrigger initialization issues
- Admin page script conflicts
- Mobile responsive issues
- Copy-to-clipboard functionality

### 📝 Documentation

New documentation files:
- `USER_GUIDE.md` - Complete user guide with examples
- `CHANGELOG.md` - This file
- Enhanced inline PHP documentation
- Better JSDoc comments in JavaScript

### 🎯 Pro Features

Enhanced Pro offering:
- Timeline Builder with export
- Text Effects (character-level animations)
- SVG Morphing capabilities
- 3D Perspective animations
- Advanced easing functions
- Physics-based animations
- Priority support access

---

## [1.0.0] - Previous Release

### Initial Features
- 15 basic animation presets
- Simple shortcode system
- Basic admin settings
- ScrollTrigger integration
- Gutenberg blocks
- Free and Pro versions

---

## Upgrade Notes

### From 1.0.0 to 2.1.1

**What's Changed:**
- Plugin branding updated throughout
- New admin menu items (Builder, Timeline)
- 30+ new animations available
- New shortcodes added (`sp_timeline`, `sp_sequence`, `sp_hover_effect`, `sp_scroll_scene`)
- Enhanced admin interface

**Compatibility:**
- All existing shortcodes continue to work
- No breaking changes to existing animations
- Settings preserved during update
- Blocks remain functional

**Action Required:**
- No action required - update is fully backwards compatible
- Recommended: Visit Settings page to review new options
- Optional: Explore new Builder and Timeline tools

---

## Development Roadmap

### Version 1.3.0 (Planned)
- [ ] Visual Gutenberg block builder
- [ ] Animation presets marketplace
- [ ] Import/export animation library
- [ ] Animation templates
- [ ] Mobile-specific animations
- [ ] Accessibility enhancements

### Version 1.4.0 (Planned)
- [ ] Lottie animation support
- [ ] Video tutorials library
- [ ] AI-powered animation suggestions
- [ ] Performance profiler
- [ ] Multi-language support
- [ ] WooCommerce integration

### Version 2.0.0 (Future)
- [ ] Complete design system
- [ ] Animation recording tool
- [ ] Collaboration features
- [ ] Cloud sync
- [ ] Analytics integration
- [ ] API for developers

---

## Support & Feedback

We value your feedback! Help us improve:

- 🐛 **Report Bugs:** https://syntekpro.com/support
- 💡 **Feature Requests:** https://syntekpro.com/feature-requests
- ⭐ **Rate Plugin:** https://wordpress.org/plugins/syntekpro-animations/
- 📧 **Contact:** support@syntekpro.com

---

**Note:** This is a living document. We update it with every release to keep you informed of changes and improvements.

---

**Maintained by:** Syntekpro Team  
**Website:** https://syntekpro.com  
**License:** GPL v2 or later
