# Analytics / Debug Overlay

Opt-in tools for debugging animations (markers, engine info, trigger states). No telemetry by default.

Usage
- Enable via Settings → Developer Mode or append `?syntekpro_debug=1` to the URL.
- Toggle overlay with Shift+D or the floating "Show debug overlay" button.
- Overlay lists each `.sp-animate` node with resolved engine (CSS vs GSAP), trigger, duration/delay, markers/once flags, and counts per engine.
- When active, outline/markers render on animated elements for quick scanning; markers-only mode keeps outlines without the panel.
- Per-role persistence is available when enabled in settings; console silencing optionally mutes non-critical logs.

TODO (2.0)
- Persist overlay preference per user role.
- Render marker layer for active animations.
- Provide console-silencing toggle for production.
