=== Syntekpro Animations ===
Contributors: syntekpro
Tags: animations, scroll effects, visual effects, page builder, animated blocks, timeline animations
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional animation engine for WordPress. Create stunning scroll-triggered animations, timeline sequences, and visual effects with no coding required.

== Description ==

**Syntekpro Animations** is a powerful animation engine built specifically for WordPress, making it easy to create professional, buttery-smooth animations with industry-leading performance.

### ✨ Features

**Free Version:**
* 15+ pre-built animation presets
* Scroll-triggered animations with ScrollTrigger
* Simple shortcode-based system
* Visual settings panel
* Fade, slide, scale, and rotate animations
* Developer-friendly hooks and filters
* Performance optimized
* Mobile responsive

**Pro Version:**
* 🎯 **SplitText** - Animate text character by character, word by word
* 🎨 **MorphSVG** - Seamlessly morph between SVG shapes
* ✏️ **DrawSVG** - Progressively draw SVG strokes
* 🚀 **ScrollSmoother** - Buttery smooth scrolling effects
* ⚡ **Advanced Animations** - Bounce, elastic, 3D transforms
* 🎬 **Timeline Builder** - Create complex animation sequences
* 🛠️ **Developer Tools** - GSDevTools for visual timeline editing
* 📞 **Priority Support** - Get help from our expert team

### 🚀 Quick Start

1. Install and activate the plugin
2. Go to **Animations** in your WordPress admin
3. Use shortcodes in your posts/pages:

`[sp_animate type="fadeInUp" duration="1"]
Your content here
[/sp_animate]`

### 📖 Shortcode Examples

**Basic Fade In:**
`[sp_animate type="fadeIn"]Content[/sp_animate]`

**Slide from Right:**
`[sp_animate type="slideRight" duration="1.5" delay="0.3"]Content[/sp_animate]`

**Scale Up Animation:**
`[sp_animate type="scaleUp" ease="back.out(1.7)"]Content[/sp_animate]`

**Text Animation (Pro):**
`[sp_text_animate type="chars" effect="fadeIn" stagger="0.05"]
Animated Text
[/sp_text_animate]`

### 🎨 Animation Types

**Fade Animations:**
* fadeIn, fadeInUp, fadeInDown, fadeInLeft, fadeInRight

**Slide Animations:**
* slideLeft, slideRight, slideUp, slideDown

**Scale Animations:**
* scaleUp, scaleDown, scaleX, scaleY

**Rotate Animations:**
* rotateIn, rotate360, flipX, flipY

**Pro Animations:**
* bounceIn, elasticIn, blurIn, wiggle, glitch, and more!

### 💻 Developer Features

Enable Developer Mode for advanced customization:

`// Custom Syntekpro animation
document.addEventListener('DOMContentLoaded', function() {
    SyntekproAnimations.animate('.my-element', {
        x: 100,
        rotation: 360,
        duration: 2,
        ease: 'power2.out'
    });
});`

### 🔧 Requirements

* WordPress 5.8 or higher
* PHP 7.4 or higher
* Modern browser with JavaScript enabled

### 🌟 Why Choose Syntekpro Animations?

* **Industry Standard** - Built with performance-optimized animation engine, trusted by professionals
* **Performance Optimized** - Hardware accelerated, 60fps smooth animations
* **No Coding Required** - User-friendly interface for beginners
* **Developer Friendly** - Powerful API for advanced users
* **Active Support** - Regular updates and dedicated support team
* **SEO Friendly** - Animations don't impact page load or SEO

### 🎓 Documentation & Support

* [Documentation](https://syntekpro.com/animations-docs)
* [Video Tutorials](https://syntekpro.com/animations-tutorials)
* [Support Forum](https://wordpress.org/support/plugin/syntekpro-animations/)
* [Premium Support](https://syntekpro.com/support) (Pro users)

== Installation ==

### Automatic Installation

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "Syntekpro Animations"
4. Click "Install Now" and then "Activate"

### Manual Installation

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded ZIP file and click "Install Now"
5. Activate the plugin

### After Activation

1. Go to **Animations** in the WordPress admin menu
2. Configure your settings
3. Start using animations with shortcodes!

== Frequently Asked Questions ==

= Is coding knowledge required? =

No! Syntekpro Animations is designed for both beginners and developers. Use simple shortcodes for basic animations, or write custom code for advanced effects.

= Does it work with page builders? =

Yes! Syntekpro Animations works with all major page builders including Elementor, Beaver Builder, Divi, and WPBakery.

= Will animations slow down my site? =

No. Our animation engine is highly optimized for performance, and we've engineered the plugin to load only what you need.

= Can I use this on client sites? =

Yes! The free version can be used on unlimited sites. The Pro version requires a license per site.

= What's the difference between Free and Pro? =

The free version includes 30+ animations and core features. Pro unlocks premium features like Timeline Builder, Text Effects, SVG Morphing, Draw Effects, Advanced Easing, and 50+ additional animations.

= Do animations work on mobile? =

Yes! All animations are fully responsive and optimized for mobile devices.

= Can I animate existing content? =

Absolutely! Just wrap your existing content with the shortcode and it will animate.

= Is it compatible with WordPress 6.4? =

Yes! We regularly test and update for the latest WordPress versions.

== Screenshots ==

1. Admin settings panel with easy-to-use interface
2. Animation presets library with visual examples
3. Simple shortcode usage in the editor
4. Smooth scroll-triggered animations on the frontend
5. Pro text animation with SplitText
6. SVG drawing animation example
7. Developer mode with custom code options
8. License activation page

== Changelog ==

= 2.2.3 - 2026-02-22 =
* Simplified every admin footer so the page now only displays the name, version, and any context note (the old "Powered by" badge is no longer forced).
* Added an uninstall handler that removes plugin options and the dedicated uploads directory so a clean uninstall is possible.
* Bumped the version metadata in preparation for the WordPress.org listing.

= 2.2.2 - 2026-02-01 =
* Added 10 new Syntekpro block patterns (hero split, stats, logo strip, steps, checklist, comparison, newsletter band, gallery tiles, testimonial highlight, CTA minimal).
* Pattern Data page simplified to friendly form controls with no raw JSON panels shown.
* Added a Patterns quick link on the dashboard and kept the admin menu icon hover neutral grey.

= 1.0.0 - 2025-01-27 =
* Initial release
* 15+ animation presets
* ScrollTrigger integration
* Shortcode system
* Admin settings panel
* Pro version support
* Developer mode
* Performance optimizations

== Upgrade Notice ==

= 1.0.0 =
Initial release of Syntekpro Animations. Install and start creating amazing animations!

== Privacy Policy ==

Syntekpro Animations does not collect any personal data. License validation (Pro version) only sends your license key and site URL to our servers for verification.

== Credits ==

This plugin uses the following open-source libraries:

* Syntekpro Animation Engine - Built with high-performance JavaScript
* Licensed under GPL v2 or later for WordPress usage

== Support ==

For support questions, feature requests, or bug reports:

* Free Version: [WordPress Support Forum](https://wordpress.org/support/plugin/syntekpro-animations/)
* Pro Version: [Premium Support](https://syntekpro.com/support)
* Documentation: [https://syntekpro.com/animations-docs](https://syntekpro.com/animations-docs)

== Pro Version ==

Upgrade to Syntekpro Animations Pro for:

* SplitText animations
* MorphSVG shape morphing
* DrawSVG stroke animations
* ScrollSmoother smooth scrolling
* Advanced bounce and elastic effects
* Timeline builder
* GSDevTools visual editor
* Priority support
* Lifetime updates

[Get Syntekpro Animations Pro →](https://syntekpro.com/animations-pro)