# Syntekpro Animations

Version: 2.4.0  
Requires: WordPress 5.8+, PHP 7.4+

Professional animation and slider toolkit for WordPress with visual builders, presets, timeline controls, and flexible frontend runtime features.

## Highlights

- 50+ animation presets with free and Pro tiers
- Visual builders for animations, timelines, and slider content
- Shortcodes for animation wrappers, sequences, and sliders
- GitHub release update notifications for installed plugin sites
- License-aware Pro feature controls

## Quick Start

1. Activate the plugin.
2. Go to WP Admin > SyntekPro Animations > Settings.
3. Enable Animation Engine and Scroll Animations.
4. Add your first shortcode:

```text
[sp_animate type="fadeIn"]Your content here[/sp_animate]
```

## Core Shortcodes

```text
[sp_animate type="fadeInUp" duration="1"]Content[/sp_animate]
[sp_timeline auto="yes"]...[/sp_timeline]
[sp_sequence type="fadeInUp" stagger="0.15"]...[/sp_sequence]
[sp_hover_effect type="scale" scale="1.1"]...[/sp_hover_effect]
[sp_slider id="123"]
```

## Admin Areas

- Settings: engine and runtime options
- Presets: browse free/locked animation presets
- Builder: visual animation builder
- Timeline: sequence builder
- Patterns: insert ready-made sections
- Animations+: license key and validation
- System Status: environment and license diagnostics

## GitHub Updates

The plugin now checks GitHub Releases for new versions and surfaces WordPress-native update notifications across installed sites.

To publish an update:

1. Update version metadata in plugin files.
2. Commit and push to main.
3. Create and push a version tag (example: `v2.4.0`).
4. Publish a GitHub Release with a plugin zip asset named like `syntekpro-animations-2.4.0.zip`.

Installed sites will detect the release during routine WordPress update checks.

## Documentation

- Full guide: [COMPLETE_GUIDE.md](COMPLETE_GUIDE.md)
- Changelog: [CHANGELOG.md](CHANGELOG.md)

## Support

- Website: https://syntekpro.com
- Email: support@syntekpro.com
- Premium support: https://syntekpro.com/support
