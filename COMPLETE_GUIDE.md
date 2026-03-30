# 📖 SYNTEKPRO ANIMATIONS - COMPLETE GUIDE

**Version 2.4.3** | Professional Animation Engine for WordPress

---

## 📑 TABLE OF CONTENTS

1. [Quick Start](#quick-start)
2. [Installation](#installation)
3. [All 50+ Animations](#animation-library)
4. [Basic Usage](#basic-usage)
5. [Advanced Features](#advanced-features)
6. [Visual Builders](#visual-builders)
7. [Shortcode Reference](#shortcode-reference)
8. [Developer API](#developer-api)
9. [Troubleshooting](#troubleshooting)
10. [Best Practices](#best-practices)

---

## 🚀 QUICK START

### 30-Second Start

Add this to any page/post:

```
[sp_animate type="fadeIn"]Your content here[/sp_animate]
```

**Done!** Your content will fade in smoothly.

### Using the Visual Builder (Easiest)

1. Go to **WP Admin → SyntekPro Animations → Builder**
2. Select animation type and adjust settings
3. Preview in real-time
4. Copy generated shortcode
5. Paste into your content

---

## 📥 INSTALLATION

### Standard Installation

1. Upload plugin folder to `/wp-content/plugins/`
2. Activate via **Plugins** menu in WordPress
3. Go to **SyntekPro Animations** in admin menu
4. Enable **Animation Engine** and **Scroll Animations**
5. Start animating!

### First-Time Configuration

**Required Settings:**
- ✅ Enable Animation Engine
- ✅ Enable Scroll Animations

**Optional Settings:**
- Smooth Scrolling (Get+)
- Developer Mode
- Additional animation features

### Update Distribution (GitHub Releases)

This plugin distribution now uses GitHub Releases as its update source.

Release flow:
1. Bump plugin version metadata.
2. Push code updates.
3. Push a version tag (for example `v2.4.3`).
4. Publish a GitHub Release with a plugin zip asset.

Installed sites will receive native WordPress update notifications when a newer release is detected.

---

## 🎨 ANIMATION LIBRARY

### All 50+ Animations

#### Fade Animations (Free)
- `fadeIn` - Simple fade in
- `fadeInUp` - Fade in from below
- `fadeInDown` - Fade in from above
- `fadeInLeft` - Fade in from left
- `fadeInRight` - Fade in from right

#### Slide Animations (Free)
- `slideLeft` - Slide from right
- `slideRight` - Slide from left
- `slideUp` - Slide from bottom
- `slideDown` - Slide from top

#### Zoom Animations (Free)
- `zoomIn` - Zoom in
- `zoomInUp` - Zoom in from below
- `zoomInDown` - Zoom in from above
- `zoomInLeft` - Zoom in from left
- `zoomInRight` - Zoom in from right

#### Scale Animations (Free)
- `scaleUp` - Scale up from small
- `scaleDown` - Scale down from large
- `scaleX` - Scale horizontally
- `scaleY` - Scale vertically

#### Rotate Animations (Free)
- `rotateIn` - Rotate while fading in
- `rotate360` - Full 360° rotation
- `flipX` - Flip on X axis
- `flipY` - Flip on Y axis

#### Reveal Animations (Free)
- `revealLeft` - Reveal from left edge
- `revealRight` - Reveal from right edge
- `revealUp` - Reveal from bottom edge
- `revealDown` - Reveal from top edge

#### Wave Animations (Free)
- `waveIn` - Wave-like entrance
- `ripple` - Ripple effect

#### Swing Animations (Free)
- `swingIn` - Swing in from top
- `pendulum` - Pendulum swing

#### Attention Seekers (Free)
- `pulse` - Pulsing effect
- `shake` - Shaking effect
- `wobble` - Wobbling effect
- `heartbeat` - Heartbeat effect

#### Bounce Animations (Get+) 🔒
- `bounceIn` - Bounce entrance
- `bounceInUp` - Bounce from below
- `bounceInLeft` - Bounce from left
- `bounceInRight` - Bounce from right

#### Elastic Animations (Get+) 🔒
- `elasticIn` - Elastic entrance
- `elasticScale` - Elastic scaling

#### 3D Perspective (Get+) 🔒
- `perspective3D` - 3D perspective
- `flipInX` - 3D flip horizontal
- `flipInY` - 3D flip vertical
- `cardFlip` - Card flip effect

#### Glitch Effects (Get+) 🔒
- `glitchIn` - Glitch entrance
- `digitalReveal` - Digital reveal

#### Peel Animations (Get+) 🔒
- `peelLeft` - Peel from left
- `peelRight` - Peel from right

#### Fold Animations (Get+) 🔒
- `unfoldHorizontal` - Unfold horizontally
- `unfoldVertical` - Unfold vertically

#### Advanced Easing (Get+) 🔒
- `smoothBounce` - Smooth bounce
- `backSlide` - Back slide effect

#### Text Effects (Get+) 🔒
- `typewriter` - Typewriter effect
- `textWave` - Wave text animation
- `textRotate` - Rotating text

#### Blur Effects (Get+) 🔒
- `blurIn` - Blur to clear

---

## 📝 BASIC USAGE

### Simple Animation

```
[sp_animate type="fadeIn"]
    <h2>Hello World!</h2>
    <p>This will fade in.</p>
[/sp_animate]
```

### With Custom Duration

```
[sp_animate type="slideLeft" duration="2"]
    Your content
[/sp_animate]
```

### With Delay

```
[sp_animate type="zoomIn" duration="1" delay="0.5"]
    Appears after 0.5 seconds
[/sp_animate]
```

### Scroll-Triggered

```
[sp_animate type="fadeInUp" trigger="scroll"]
    Animates when scrolled into view
[/sp_animate]
```

### Load-Triggered

```
[sp_animate type="rotateIn" trigger="load"]
    Animates immediately on page load
[/sp_animate]
```

---

## 🎬 ADVANCED FEATURES

### 1. Timeline Animations

Create multi-step animation sequences:

```
[sp_timeline auto="yes"]
    [sp_animate type="fadeIn" duration="1"]Step 1[/sp_animate]
    [sp_animate type="slideLeft" duration="1"]Step 2[/sp_animate]
    [sp_animate type="zoomIn" duration="1"]Step 3[/sp_animate]
[/sp_timeline]
```

**Parameters:**
- `auto` - Auto-play on load (yes/no)
- `scrub` - Scroll-linked animation (yes/no)
- `trigger` - Custom trigger element
- `start` - Start position (e.g., "top 80%")
- `end` - End position (e.g., "bottom 20%")

### 2. Sequence Animations

Stagger animations on child elements:

```
[sp_sequence type="fadeInUp" stagger="0.15" duration="1"]
    <div class="item">Item 1</div>
    <div class="item">Item 2</div>
    <div class="item">Item 3</div>
    <div class="item">Item 4</div>
[/sp_sequence]
```

**Parameters:**
- `type` - Animation type
- `stagger` - Delay between items (seconds)
- `duration` - Animation duration
- `delay` - Initial delay
- `trigger` - When to start (scroll/load)
- `ease` - Easing function

### 3. Hover Effects

Add interactive hover animations:

```
[sp_hover_effect type="scale" scale="1.1" duration="0.3"]
    <button class="my-button">Hover Me</button>
[/sp_hover_effect]
```

**Types:**
- `scale` - Scale up on hover
- `lift` - Lift with shadow
- `rotate` - Rotate on hover
- `glow` - Add glow effect

**Parameters:**
- `type` - Effect type
- `scale` - Scale amount (e.g., 1.1)
- `x` - Horizontal movement
- `y` - Vertical movement
- `rotation` - Rotation degrees
- `duration` - Animation speed
- `ease` - Easing function

### 4. Scroll Scenes

Advanced scroll-based animations:

```
[sp_scroll_scene pin="yes" scrub="1" start="top top" end="bottom top"]
    [sp_animate type="fadeIn"]Pinned content[/sp_animate]
    [sp_animate type="slideLeft"]Animated content[/sp_animate]
[/sp_scroll_scene]
```

**Parameters:**
- `pin` - Pin element during scroll (yes/no)
- `scrub` - Smooth scrubbing (0-3, or false)
- `start` - Start trigger point
- `end` - End trigger point
- `markers` - Show debug markers (yes/no)

---

## 🎨 VISUAL BUILDERS

### Animation Builder

**Location:** WP Admin → SyntekPro Animations → Builder

**Features:**
- ✅ Choose from 50+ animations
- ✅ Adjust duration, delay, easing
- ✅ Live preview in real-time
- ✅ Instant code generation
- ✅ Copy to clipboard

**How to Use:**
1. Select animation type from dropdown
2. Adjust duration slider (0.1 - 10 seconds)
3. Set delay (0 - 5 seconds)
4. Choose easing function
5. Watch live preview
6. Copy generated shortcode
7. Paste into your content

### Timeline Creator

**Location:** WP Admin → SyntekPro Animations → Timeline

**Features:**
- ✅ Multi-step sequences
- ✅ Drag to reorder steps
- ✅ Add/remove steps dynamically
- ✅ Visual preview
- ✅ Timeline playback

**How to Use:**
1. Start with default step
2. Click "Add Step" for more
3. Configure each step's animation
4. Drag handles to reorder
5. Click "Play Timeline" to preview
6. Use the generated code

---

## 📋 SHORTCODE REFERENCE

### [sp_animate] - Basic Animation

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `type` | string | fadeIn | Animation type |
| `duration` | number | 1 | Duration in seconds |
| `delay` | number | 0 | Delay before start |
| `trigger` | string | scroll | When to start (scroll/load) |
| `start` | string | top 80% | ScrollTrigger start position |
| `ease` | string | power2.out | Easing function |
| `stagger` | number | 0 | Stagger for child elements |
| `repeat` | number | 0 | Number of repeats |

**Example:**
```
[sp_animate type="fadeInUp" duration="1.5" delay="0.3" ease="back.out(1.7)"]
    Your content
[/sp_animate]
```

### [sp_timeline] - Animation Sequences

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | string | auto | Unique timeline ID |
| `auto` | string | yes | Auto-play on load |
| `scrub` | string | no | Scroll-linked animation |
| `trigger` | string | - | Custom trigger element |
| `start` | string | top 80% | Start position |
| `end` | string | bottom 20% | End position |

### [sp_sequence] - Staggered Animations

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `type` | string | fadeIn | Animation type |
| `duration` | number | 1 | Animation duration |
| `stagger` | number | 0.1 | Delay between items |
| `delay` | number | 0 | Initial delay |
| `trigger` | string | scroll | Trigger type |
| `ease` | string | power2.out | Easing function |
| `from` | string | start | Direction (start/end) |

### [sp_hover_effect] - Interactive Hovers

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `type` | string | scale | Effect type |
| `scale` | number | 1.1 | Scale amount |
| `x` | number | 0 | Horizontal movement |
| `y` | number | 0 | Vertical movement |
| `rotation` | number | 0 | Rotation degrees |
| `duration` | number | 0.3 | Animation speed |
| `ease` | string | power2.out | Easing function |

### [sp_scroll_scene] - Advanced Scrolling

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | string | auto | Scene ID |
| `pin` | string | no | Pin element |
| `scrub` | number/string | 0 | Scrub smoothness |
| `start` | string | top 80% | Start position |
| `end` | string | bottom 20% | End position |
| `markers` | string | no | Debug markers |

---

## 🔧 DEVELOPER API

### JavaScript API

Access the API via `window.SyntekproAnimations`:

#### animate(selector, options)

```javascript
SyntekproAnimations.animate('.box', {
    x: 100,
    y: 50,
    rotation: 45,
    duration: 1.5,
    ease: 'back.out(1.7)'
});
```

#### animateFrom(selector, from, to)

```javascript
SyntekproAnimations.animateFrom('.box',
    { opacity: 0, scale: 0 },
    { opacity: 1, scale: 1, duration: 1 }
);
```

#### timeline()

```javascript
const tl = SyntekproAnimations.timeline();
tl.to('.box1', { x: 100 })
  .to('.box2', { x: 100 })
  .to('.box3', { x: 100 });
```

#### scrollTrigger(options)

```javascript
SyntekproAnimations.scrollTrigger({
    trigger: '.section',
    start: 'top center',
    end: 'bottom center',
    onEnter: () => console.log('Entered')
});
```

#### getPreset(type)

```javascript
const preset = SyntekproAnimations.getPreset('fadeIn');
// Returns: { from: { opacity: 0 }, to: { opacity: 1 } }
```

### WordPress Hooks

#### Add Custom Animation Preset

```php
add_filter('syntekpro_animation_presets', function($presets) {
    $presets['myCustom'] = array(
        'name' => 'My Custom Animation',
        'category' => 'custom',
        'free' => true,
        'from' => array('opacity' => 0, 'scale' => 0),
        'to' => array('opacity' => 1, 'scale' => 1)
    );
    return $presets;
});
```

#### Add Custom Category

```php
add_filter('syntekpro_animation_categories', function($categories) {
    $categories['custom'] = 'Custom Animations';
    return $categories;
});
```

#### Modify Animation Config

```php
add_filter('syntekpro_animation_config', function($config) {
    $config['default_duration'] = 2;
    $config['default_ease'] = 'elastic.out(1, 0.3)';
    return $config;
});
```

### Custom Shortcode Example

```php
add_shortcode('my_anim', function($atts, $content) {
    $atts = shortcode_atts(array(
        'type' => 'fadeIn',
        'duration' => '1'
    ), $atts);
    
    return sprintf(
        '<div class="sp-animate" data-animation="%s" data-duration="%s">%s</div>',
        esc_attr($atts['type']),
        esc_attr($atts['duration']),
        do_shortcode($content)
    );
});
```

---

## 🔍 TROUBLESHOOTING

### Animation Not Working

**Check:**
1. ✅ Settings → Animation Engine is enabled
2. ✅ Scroll Animations is enabled (for scroll trigger)
3. ✅ Clear browser cache
4. ✅ Check browser console (F12) for errors
5. ✅ Verify shortcode syntax

### Animation Too Fast/Slow

**Solution:**
- Adjust `duration` parameter
- Smaller = faster (0.5)
- Larger = slower (2.0)
- Recommended range: 0.5 - 2 seconds

### Animation Not Triggering on Scroll

**Check:**
1. ✅ `trigger="scroll"` is set
2. ✅ ScrollTrigger is enabled in settings
3. ✅ Element is actually scrolling into view
4. ✅ Check `start` parameter (default: "top 80%")

### Multiple Animations Conflicting

**Solution:**
- Use `[sp_timeline]` for sequences
- Use `[sp_sequence]` for staggered groups
- Set different `delay` values
- Use unique IDs for timelines

### Gutenberg Block Not Showing

**Check:**
1. ✅ Plugin is activated
2. ✅ Clear block cache
3. ✅ Refresh editor
4. ✅ Check block category: "Syntekpro"

---

## ✨ BEST PRACTICES

### Duration Guidelines

- **Hero sections:** 1-1.5 seconds
- **Content blocks:** 0.8-1.2 seconds
- **Small elements:** 0.3-0.6 seconds
- **Attention seekers:** 0.5-1 second
- **Hover effects:** 0.2-0.4 seconds

### Easing Functions

**Recommended:**
- `power2.out` - General purpose (default)
- `back.out(1.7)` - Bouncy, playful
- `elastic.out(1, 0.3)` - Very bouncy
- `sine.inOut` - Smooth, subtle
- `bounce.out` - Fun, energetic

### Performance Tips

✅ **DO:**
- Use `trigger="scroll"` for content
- Batch similar animations
- Use `stagger` for groups
- Limit animations per page (10-20)
- Use `will-change` CSS property

❌ **DON'T:**
- Animate width/height (use scale)
- Animate top/left (use x/y)
- Create 100+ animations
- Use very long durations (>3s)
- Animate on mobile excessively

### Common Mistakes

❌ Duration too long (>3 seconds)  
❌ Too many animations on one page  
❌ Forgetting closing tag `[/sp_animate]`  
❌ Using wrong animation name  
❌ Not testing on mobile  

✅ Check Presets page for correct names  
✅ Test on different screen sizes  
✅ Use visual builder to experiment  
✅ Preview before publishing  

---

## 📊 ANIMATION TIMING GUIDE

### When to Use Each Trigger

**`trigger="load"`**
- Hero sections
- Above-the-fold content
- Navigation menus
- Important announcements

**`trigger="scroll"`**
- Main content sections
- Feature blocks
- Testimonials
- Footer elements

### Stagger Timing

- **Fast:** 0.05-0.08 seconds
- **Normal:** 0.1-0.15 seconds
- **Slow:** 0.2-0.3 seconds
- **Very slow:** 0.4+ seconds

### Delay Timing

- **No delay:** Immediate impact
- **0.2-0.5s:** Sequential elements
- **0.5-1s:** Dramatic reveal
- **1s+:** Special emphasis

---

## 🎓 EXAMPLES & USE CASES

### Hero Section

```
[sp_animate type="fadeInDown" duration="1.2" ease="power2.out"]
    <h1>Welcome to Our Site</h1>
[/sp_animate]

[sp_animate type="fadeInUp" duration="1.2" delay="0.3"]
    <p>Your journey starts here</p>
[/sp_animate]

[sp_animate type="zoomIn" duration="0.8" delay="0.6"]
    <button>Get Started</button>
[/sp_animate]
```

### Feature Cards

```
[sp_sequence type="fadeInUp" stagger="0.2" trigger="scroll"]
    <div class="feature">Feature 1</div>
    <div class="feature">Feature 2</div>
    <div class="feature">Feature 3</div>
    <div class="feature">Feature 4</div>
[/sp_sequence]
```

### Interactive Button

```
[sp_hover_effect type="scale" scale="1.1" duration="0.3"]
    <button class="cta-button">Click Me</button>
[/sp_hover_effect]
```

### Testimonial Reveal

```
[sp_animate type="slideLeft" trigger="scroll" duration="1" ease="back.out(1.7)"]
    <blockquote>
        "This product changed my life!"
        <cite>- Happy Customer</cite>
    </blockquote>
[/sp_animate]
```

### Parallax Section

```
[sp_scroll_scene scrub="1" start="top center" end="bottom center"]
    [sp_animate type="fadeIn"]
        <div class="parallax-content">
            Scroll to see the effect
        </div>
    [/sp_animate]
[/sp_scroll_scene]
```

---

## 🆘 SUPPORT

### Getting Help

- **📚 Documentation:** This guide (bookmark it!)
- **❓ Help Widget:** Click **?** button in WordPress admin
- **🌐 Website:** https://syntekpro.com
- **📧 Email:** support@syntekpro.com
- **💬 Support Portal:** https://syntekpro.com/support

### Get+ Features

**Upgrade to Get+ for:**
- 🔒 20+ additional animations
- 🔒 Timeline Builder export
- 🔒 Text effects (character-level)
- 🔒 3D perspective animations
- 🔒 Advanced easing functions
- 🔒 Priority support
- 🔒 Future updates

[Upgrade to Get+ →](https://syntekpro.com/animations-plus)

---

## 📱 MOBILE OPTIMIZATION

### Mobile-Specific Tips

1. **Reduce animation duration** by 20-30%
2. **Simplify effects** (use fadeIn over complex 3D)
3. **Limit animations** (5-10 per page max)
4. **Test on real devices**
5. **Consider touch interactions**

### Responsive Animation Example

```php
// Detect mobile
$is_mobile = wp_is_mobile();
$duration = $is_mobile ? '0.8' : '1.2';

echo do_shortcode('[sp_animate type="fadeIn" duration="' . $duration . '"]Content[/sp_animate]');
```

---

## 🎯 ADMIN PAGES REFERENCE

### Settings Page
**Location:** SyntekPro Animations → Settings

Configure:
- Animation Engine (required)
- Scroll Animations (recommended)
- Smooth Scrolling (Get+)
- Developer Mode
- Additional features

### Presets Page
**Location:** SyntekPro Animations → Presets

Browse all 50+ animations organized by category with example shortcodes.

### Builder Page
**Location:** SyntekPro Animations → Builder

Visual animation creator with:
- Live preview
- Parameter controls
- Code generation
- Copy to clipboard

### Timeline Page
**Location:** SyntekPro Animations → Timeline

Multi-step sequence builder:
- Add/remove steps
- Drag to reorder
- Live playback
- Visual preview

### Documentation Page
**Location:** SyntekPro Animations → Documentation

In-dashboard guides with examples and tutorials.

---

## 🔐 SECURITY & PERFORMANCE

### Security Features

✅ Input sanitization  
✅ Output escaping  
✅ Nonce verification  
✅ Capability checks  
✅ SQL injection prevention  

### Performance Optimizations

✅ Conditional script loading  
✅ Minified assets  
✅ No database bloat  
✅ Efficient selectors  
✅ Optimized animation engine  

---

## 📦 FILE STRUCTURE

```
syntekpro-animations/
├── includes/
│   ├── class-admin.php              - Admin interface
│   ├── class-animation-presets.php  - 50+ animations
│   ├── class-advanced-features.php  - Advanced shortcodes
│   ├── class-enqueue.php            - Script management
│   ├── class-gutenberg.php          - Block editor
│   ├── class-help-system.php        - Help widget
│   └── class-shortcodes.php         - Basic shortcodes
├── assets/
│   ├── js/
│   │   ├── animations.js            - Frontend animations
│   │   ├── admin-preview.js         - Admin builders
│   │   └── admin.js                 - Admin scripts
│   ├── css/
│   │   ├── style.css                - Frontend styles
│   │   ├── admin-settings-ui.css    - Admin UI
│   │   └── admin-branding.css       - Branding
│   └── gsap/                        - Animation engine
├── COMPLETE_GUIDE.md                - This file
├── CHANGELOG.md                     - Version history
├── README.md                        - Quick overview
└── syntekpro-animations.php         - Main plugin file
```

---

## 🚀 ENHANCEMENT PACK (30 REQUESTS)

The slider system now includes an "Enhancement Matrix" in the slider editor that maps to the latest 30 requested capabilities.

### Implemented Foundations

- Developer Experience: CLI scaffold starter, TypeScript API definitions, Storybook starter stories, migration runner, and local import/export REST package flow.
- Content and Data: scheduled publishing filters, personalization filters (login/role/country), multilingual layer translation mapping, CSV/Sheets source injection, countdown/live-data runtime layers.
- Security: optional CSP header emitter, SRI runtime asset tags, GDPR consent overlay behavior, and role-based editor access checks.
- Ecosystem: SDK-ready hooks/endpoints, page builder integration toggles, WP-CLI slider commands, multisite-ready setting tracks.
- Accessibility: WCAG audit endpoint, reduced-motion runtime handling, screen-reader transcript output, and focus trap utility.
- Architecture: Web Components output wrapper (`<slider-pro>`), module-federation readiness toggle, and Playwright E2E skeleton.
- CMS and Workflow: staging sync handoff endpoint, submit/approve workflow endpoints, audit trail snapshots, and per-slider maintenance mode support.

### New Developer Assets

- `assets/js/syntekpro-slider-api.d.ts`
- `scripts/create-slider/package.json`
- `scripts/create-slider/index.mjs`
- `docs/storybook/README.md`
- `docs/storybook/stories/layers.stories.js`
- `playwright.config.js`
- `tests/e2e/slider-runtime.spec.js`

---

## ✅ QUICK CHECKLIST

### After Installation
- [ ] Plugin activated
- [ ] Animation Engine enabled
- [ ] Scroll Animations enabled
- [ ] Tested first animation
- [ ] Explored Builder page
- [ ] Bookmarked this guide

### Before Going Live
- [ ] Tested on mobile
- [ ] Checked all browsers
- [ ] Limited animations per page
- [ ] Optimized durations
- [ ] Cleared cache
- [ ] Tested on slow connection

---

**Version:** 2.4.3
**Last Updated:** March 31, 2026  
**© 2026 Syntekpro. All rights reserved.**

---

**🎉 You're all set! Start creating amazing animations!**

Need help? Click the **?** button in your WordPress admin or visit https://syntekpro.com/support
