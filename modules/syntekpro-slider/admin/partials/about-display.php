<?php
defined( 'ABSPATH' ) || exit;

$plugin_data = get_plugin_data( SPSLIDER_FILE );

require_once SPSLIDER_DIR . 'admin/partials/page-header.php';

$spslider_sidenav_sections = [
    [ 'id' => 'sp-overview',      'label' => 'Overview',       'icon' => '🏠' ],
    [ 'id' => 'sp-features',      'label' => 'Features',       'icon' => '⭐' ],
    [ 'id' => 'sp-developer',     'label' => 'Developer API',  'icon' => '⚙' ],
    [ 'id' => 'sp-changelog',     'label' => 'Changelog',      'icon' => '📋' ],
    [ 'id' => 'sp-other-plugins', 'label' => 'Other Plugins',  'icon' => '🔌' ],
];
?>
<div class="wrap spslider-admin-wrap">
    <!-- Page Layout -->
    <div class="spslider-page-layout">

        <?php require_once SPSLIDER_DIR . 'admin/partials/page-sidenav.php'; ?>

        <div class="spslider-page-content">

            <h1 class="spslider-admin-title spslider-subpage-title">
                <span class="spslider-logo">&#9654;</span>
                <?php esc_html_e( 'SyntekPro Slider — About', 'syntekpro-slider' ); ?>
            </h1>

            <!-- ─── Overview ─────────────────────────────────────────── -->
            <div id="sp-overview" class="spslider-section-anchor spslider-card spslider-about-section">
                <h2><?php esc_html_e( 'Overview', 'syntekpro-slider' ); ?></h2>
                <p><strong><?php esc_html_e( 'SyntekPro Slider', 'syntekpro-slider' ); ?></strong> <?php esc_html_e( 'is a feature-rich WordPress slider plugin built by the SyntekPro team. It delivers a pixel-perfect drag-and-drop canvas editor, advanced layer animations, built-in analytics, and full developer API — all inside your WordPress dashboard.', 'syntekpro-slider' ); ?></p>

                <table class="form-table spslider-sysinfo spslider-about-table" style="margin-top:20px;">
                    <tr>
                        <td><?php esc_html_e( 'Plugin Version', 'syntekpro-slider' ); ?></td>
                        <td><strong><?php echo esc_html( SPSLIDER_VERSION ); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'Author', 'syntekpro-slider' ); ?></td>
                        <td><a href="https://syntekpro.com" target="_blank" rel="noopener">SyntekPro</a></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'Plugin URI', 'syntekpro-slider' ); ?></td>
                        <td><a href="https://plugins.syntekpro.com/slider" target="_blank" rel="noopener">plugins.syntekpro.com/slider</a></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'License', 'syntekpro-slider' ); ?></td>
                        <td>GPL-2.0+</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'WordPress Version', 'syntekpro-slider' ); ?></td>
                        <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'PHP Version', 'syntekpro-slider' ); ?></td>
                        <td>
                            <?php
                            $php_ver = phpversion();
                            $ok      = version_compare( $php_ver, '7.4', '>=' );
                            echo '<span class="' . ( $ok ? 'spslider-status-ok' : 'spslider-status-bad' ) . '">' . esc_html( $php_ver ) . '</span>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'MySQL Version', 'syntekpro-slider' ); ?></td>
                        <td><?php global $wpdb; echo esc_html( $wpdb->db_version() ); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'WordPress Memory Limit', 'syntekpro-slider' ); ?></td>
                        <td><?php echo esc_html( WP_MEMORY_LIMIT ); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'REST API', 'syntekpro-slider' ); ?></td>
                        <td><span class="spslider-status-ok"><?php esc_html_e( 'Active', 'syntekpro-slider' ); ?></span></td>
                    </tr>
                </table>
            </div><!-- /overview -->

            <!-- ─── Features ─────────────────────────────────────────── -->
            <div id="sp-features" class="spslider-section-anchor spslider-card spslider-about-section" style="margin-top:24px;">
                <h2><?php esc_html_e( '21 Powerful Features', 'syntekpro-slider' ); ?></h2>
                <div class="spslider-features-grid">
                    <?php
                    $features = [
                        [ 'icon' => '🖼', 'title' => 'Drag-and-Drop Editor',     'desc' => 'Visual canvas editor with pixel-precise layer placement.' ],
                        [ 'icon' => '🎞', 'title' => '9 Slide Transitions',       'desc' => 'Slide, Fade, Zoom, Crossfade, Parallax, Ken Burns, 3D Cube, Flip, Custom CSS.' ],
                        [ 'icon' => '✨', 'title' => 'Layer Animations',          'desc' => '12 entrance/exit animations per layer with easing controls.' ],
                        [ 'icon' => '📱', 'title' => 'Fully Responsive',          'desc' => 'Desktop, Tablet & Mobile preview modes with per-breakpoint settings.' ],
                        [ 'icon' => '🎬', 'title' => 'Video Slides',              'desc' => 'Embed YouTube, Vimeo or self-hosted video as slide backgrounds.' ],
                        [ 'icon' => '🖱', 'title' => 'Touch & Swipe Support',     'desc' => 'Native swipe navigation for mobile and tablet users.' ],
                        [ 'icon' => '♿', 'title' => 'Accessibility Ready',       'desc' => 'ARIA attributes, keyboard navigation, and pause-on-focus support.' ],
                        [ 'icon' => '🚀', 'title' => 'Asset Optimisation',        'desc' => 'Scripts/styles only enqueued on pages that use the slider.' ],
                        [ 'icon' => '🌐', 'title' => 'WebP Auto-Generation',      'desc' => 'Automatically create WebP versions of uploaded images.' ],
                        [ 'icon' => '📊', 'title' => 'Built-in Analytics',        'desc' => 'Track slide views, layer clicks and navigation events.' ],
                        [ 'icon' => '📤', 'title' => 'CSV Export',                'desc' => 'Export analytics data to CSV for offline analysis.' ],
                        [ 'icon' => '📐', 'title' => 'Aspect-Ratio Locking',      'desc' => 'Lock layer dimensions during resizing with shift-key support.' ],
                        [ 'icon' => '↔', 'title' => 'Drag-to-Reorder Slides',    'desc' => 'jQuery UI sortable slide panel for intuitive ordering.' ],
                        [ 'icon' => '🎨', 'title' => 'Template Library',          'desc' => 'One-click starter templates to launch sliders quickly.' ],
                        [ 'icon' => '⚡', 'title' => 'Dynamic Content',           'desc' => 'Inject WP post data, ACF fields, and custom meta into layers.' ],
                        [ 'icon' => '🔗', 'title' => 'Shortcode & Block',         'desc' => '[syntekpro_slider id="X"] shortcode + Gutenberg block.' ],
                        [ 'icon' => '🧩', 'title' => 'REST API',                  'desc' => 'Full REST API for headless / decoupled implementations.' ],
                        [ 'icon' => '🔔', 'title' => 'WordPress Hooks',           'desc' => 'Extensive filter and action hooks for developers.' ],
                        [ 'icon' => '📂', 'title' => 'Multi-Slider Management',   'desc' => 'Manage unlimited sliders from a central dashboard.' ],
                        [ 'icon' => '🗂', 'title' => 'Undo / Redo',               'desc' => '20-step undo/redo history in the editor.' ],
                        [ 'icon' => '🔒', 'title' => 'WP Nonce Security',         'desc' => 'All AJAX and REST endpoints protected with WP nonces.' ],
                    ];
                    foreach ( $features as $f ) :
                    ?>
                    <div class="spslider-feature-item">
                        <div class="spslider-feature-icon"><?php echo esc_html( $f['icon'] ); ?></div>
                        <div>
                            <h4><?php echo esc_html( $f['title'] ); ?></h4>
                            <p><?php echo esc_html( $f['desc'] ); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div><!-- /features -->

            <!-- ─── Developer API ─────────────────────────────────────── -->
            <div id="sp-developer" class="spslider-section-anchor spslider-card spslider-about-section" style="margin-top:24px;">
                <h2><?php esc_html_e( 'Developer API', 'syntekpro-slider' ); ?></h2>

                <h3><?php esc_html_e( 'Shortcode', 'syntekpro-slider' ); ?></h3>
                <pre>[syntekpro_slider id="1"]
[syntekpro_slider id="1" width="100%" height="500px"]</pre>

                <h3 style="margin-top:20px;"><?php esc_html_e( 'WordPress Filters', 'syntekpro-slider' ); ?></h3>
                <table class="form-table spslider-sysinfo">
                    <tr>
                        <td><code>spslider_transitions</code></td>
                        <td><?php esc_html_e( 'Register custom slide transition types.', 'syntekpro-slider' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>spslider_render_html</code></td>
                        <td><?php esc_html_e( 'Filter the final slider HTML before output.', 'syntekpro-slider' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>spslider_dynamic_tags</code></td>
                        <td><?php esc_html_e( 'Register additional dynamic content tags.', 'syntekpro-slider' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>spslider_rest_slider_response</code></td>
                        <td><?php esc_html_e( 'Modify the REST API slider response object.', 'syntekpro-slider' ); ?></td>
                    </tr>
                </table>

                <h3 style="margin-top:20px;"><?php esc_html_e( 'WordPress Actions', 'syntekpro-slider' ); ?></h3>
                <table class="form-table spslider-sysinfo">
                    <tr>
                        <td><code>spslider_before_render</code></td>
                        <td><?php esc_html_e( 'Fires before slider HTML is rendered. Passes $slider_id.', 'syntekpro-slider' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>spslider_after_render</code></td>
                        <td><?php esc_html_e( 'Fires after slider HTML is rendered. Passes $slider_id.', 'syntekpro-slider' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>spslider_activated</code></td>
                        <td><?php esc_html_e( 'Fires on plugin activation.', 'syntekpro-slider' ); ?></td>
                    </tr>
                </table>

                <h3 style="margin-top:20px;"><?php esc_html_e( 'REST API Endpoints', 'syntekpro-slider' ); ?></h3>
                <table class="form-table spslider-sysinfo">
                    <tr><td><code>GET  /wp-json/syntekpro-slider/v1/sliders</code></td><td><?php esc_html_e( 'List all sliders', 'syntekpro-slider' ); ?></td></tr>
                    <tr><td><code>POST /wp-json/syntekpro-slider/v1/sliders</code></td><td><?php esc_html_e( 'Create a slider', 'syntekpro-slider' ); ?></td></tr>
                    <tr><td><code>GET  /wp-json/syntekpro-slider/v1/sliders/{id}</code></td><td><?php esc_html_e( 'Get a slider', 'syntekpro-slider' ); ?></td></tr>
                    <tr><td><code>PUT  /wp-json/syntekpro-slider/v1/sliders/{id}</code></td><td><?php esc_html_e( 'Update a slider', 'syntekpro-slider' ); ?></td></tr>
                    <tr><td><code>DELETE /wp-json/syntekpro-slider/v1/sliders/{id}</code></td><td><?php esc_html_e( 'Delete a slider', 'syntekpro-slider' ); ?></td></tr>
                    <tr><td><code>GET  /wp-json/syntekpro-slider/v1/sliders/{id}/slides</code></td><td><?php esc_html_e( 'Get slides for a slider', 'syntekpro-slider' ); ?></td></tr>
                </table>

                <h3 style="margin-top:20px;"><?php esc_html_e( 'JavaScript API', 'syntekpro-slider' ); ?></h3>
                <pre>// Initialize programmatically
SyntekProSlider.init('#my-slider', { loop: true, autoplay: 5000 });

// Control playback
SyntekProSlider.pause('#my-slider');
SyntekProSlider.play('#my-slider');
SyntekProSlider.goTo('#my-slider', 2); // 0-indexed

// Events
document.querySelector('#my-slider').addEventListener('spslider:change', function(e){
    console.log('Active slide:', e.detail.index);
});</pre>
            </div><!-- /developer -->

            <!-- ─── Changelog ─────────────────────────────────────────── -->
            <div id="sp-changelog" class="spslider-section-anchor spslider-card spslider-about-section spslider-changelog" style="margin-top:24px;">
                <h2><?php esc_html_e( 'Changelog', 'syntekpro-slider' ); ?></h2>

                <div class="spslider-cl-version">
                    <div class="spslider-cl-num">1.4.0</div>
                    <div class="spslider-cl-body">
                        <h4><?php esc_html_e( 'Editor UI Overhaul — 2026', 'syntekpro-slider' ); ?></h4>
                        <ul>
                            <li><?php esc_html_e( 'Complete editor UI overhaul — all emoji/unicode icons replaced with crisp SVG icons', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Toolbar reorganised with grouped sections and vertical separators', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Layer panel: collapsible add-layer drawer with icon+label buttons', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Layer list: SVG type badges, drag handles, hover-reveal action buttons', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Properties panel: collapsible sections with animated chevrons', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Slide manager: hover-reveal controls, layer count per slide', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Settings modal: 5-tab interface with iOS-style toggle switches', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Animation & background modals: tabbed layouts with structured fields', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Toast notifications with slide-in animation', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Full CSS rewrite for modern, professional appearance', 'syntekpro-slider' ); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="spslider-cl-version">
                    <div class="spslider-cl-num">1.3.0</div>
                    <div class="spslider-cl-body">
                        <h4><?php esc_html_e( 'Gutenberg Block Enhancements — 2025', 'syntekpro-slider' ); ?></h4>
                        <ul>
                            <li><?php esc_html_e( 'Rich sidebar with 6 inspector panels for per-block overrides', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Block toolbar with preview toggle and ServerSideRender', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( '16 new block attributes for slider customisation', 'syntekpro-slider' ); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="spslider-cl-version">
                    <div class="spslider-cl-num">1.2.0</div>
                    <div class="spslider-cl-body">
                        <h4><?php esc_html_e( 'Templates & Modals — 2025', 'syntekpro-slider' ); ?></h4>
                        <ul>
                            <li><?php esc_html_e( 'Templates submenu page', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Generic modal overlay system', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Editor HTML ID fixes, admin list page JS fixes', 'syntekpro-slider' ); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="spslider-cl-version">
                    <div class="spslider-cl-num">1.1.0</div>
                    <div class="spslider-cl-body">
                        <h4><?php esc_html_e( 'Database & Tracking — 2025', 'syntekpro-slider' ); ?></h4>
                        <ul>
                            <li><?php esc_html_e( 'Migration runner for schema upgrades', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Revision, conversion, audit log, and scheduling tables', 'syntekpro-slider' ); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="spslider-cl-version">
                    <div class="spslider-cl-num">1.0.0</div>
                    <div class="spslider-cl-body">
                        <h4><?php esc_html_e( 'Initial Release — 2025', 'syntekpro-slider' ); ?></h4>
                        <ul>
                            <li><?php esc_html_e( 'Drag-and-drop visual canvas editor', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( '9 built-in slide transition effects', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( '12 layer animation types with easing controls', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Fully responsive — Desktop, Tablet & Mobile preview', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Video background slides (YouTube / Vimeo / self-hosted)', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Touch & swipe navigation', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Accessibility: ARIA, keyboard navigation, pause-on-focus', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Asset optimisation: scripts only loaded where slider is used', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Auto WebP image generation (GD/Imagick)', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Built-in analytics with daily chart', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'CSV analytics export', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Aspect-ratio locking while resizing layers', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Drag-to-reorder slides (jQuery UI Sortable)', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Starter template library', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Dynamic content from WP/ACF fields', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Shortcode [syntekpro_slider] + Gutenberg block', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Full REST API', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'WordPress action & filter hooks for developers', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'Multi-slider dashboard management', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( '20-step undo / redo in editor', 'syntekpro-slider' ); ?></li>
                            <li><?php esc_html_e( 'WP Nonce security on all AJAX & REST endpoints', 'syntekpro-slider' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div><!-- /changelog -->

            <!-- ─── Other Plugins ─────────────────────────────────────── -->
            <div id="sp-other-plugins" class="spslider-section-anchor spslider-card spslider-about-section" style="margin-top:24px;">
                <h2><?php esc_html_e( 'Other Plugins by SyntekPro', 'syntekpro-slider' ); ?></h2>
                <p class="spslider-about-intro"><?php esc_html_e( 'Explore our full suite of WordPress plugins to supercharge your site.', 'syntekpro-slider' ); ?></p>

                <div class="spslider-plugins-grid">
                    <?php
                    $other_plugins = [
                        [
                            'name'    => 'SyntekPro Forms',
                            'desc'    => 'Build powerful, conversion-optimised forms with drag-and-drop ease. Multi-step, conditional logic, email integrations and more.',
                            'icon'    => 'Syntekpro Forms Icon.png',
                            'url'     => 'https://plugins.syntekpro.com/forms',
                            'class'   => 'spcard-forms',
                        ],
                        [
                            'name'    => 'SyntekPro Chat',
                            'desc'    => 'Add a fully customisable live-chat widget to your WordPress site. Real-time messaging, chatbots, and visitor tracking built-in.',
                            'icon'    => 'SyntekPro Chat Icon.png',
                            'url'     => 'https://plugins.syntekpro.com/chat',
                            'class'   => 'spcard-chat',
                        ],
                        [
                            'name'    => 'SyntekPro Toggle',
                            'desc'    => 'Beautifully animated toggle switches and accordions. Lightweight, accessible, and fully style-able to match any theme.',
                            'icon'    => 'SyntekPro Toggle Icon Color.png',
                            'url'     => 'https://plugins.syntekpro.com/toggle',
                            'class'   => 'spcard-toggle',
                        ],
                        [
                            'name'    => 'SyntekPro License Server',
                            'desc'    => 'Sell and manage software licenses directly from WordPress. API-based activation, expiry control, and usage limits.',
                            'icon'    => 'SyntekPro License Server Icon.png',
                            'url'     => 'https://plugins.syntekpro.com/license-server',
                            'class'   => 'spcard-license',
                        ],
                        [
                            'name'    => 'SyntekPro Support',
                            'desc'    => 'A complete ticket-based support system for your WordPress site. Email piping, priorities, agents, and a client portal.',
                            'icon'    => 'SyntekPro Plugins Support Icon.png',
                            'url'     => 'https://plugins.syntekpro.com/support',
                            'class'   => 'spcard-support',
                        ],
                        [
                            'name'    => 'SyntekPro Themes',
                            'desc'    => 'Premium WordPress themes crafted for performance, aesthetics, and seamless SyntekPro plugin integration.',
                            'icon'    => 'SyntekPro Themes Icon.png',
                            'url'     => 'https://themes.syntekpro.com',
                            'class'   => 'spcard-themes',
                        ],
                        [
                            'name'    => 'SyntekPro',
                            'desc'    => 'Visit our main site to discover all our products, tutorials, documentation, and community resources.',
                            'icon'    => 'SYNTEK PRO LOGO Transparent HD 1563x402.png',
                            'url'     => 'https://syntekpro.com',
                            'class'   => 'spcard-main',
                        ],
                    ];

                    foreach ( $other_plugins as $plugin ) :
                    ?>
                    <a href="<?php echo esc_url( $plugin['url'] ); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="spslider-plugin-card <?php echo esc_attr( $plugin['class'] ); ?>">
                        <div class="spslider-plugin-card-icon">
                            <img src="<?php echo esc_url( SPSLIDER_URL . 'assets/img/' . $plugin['icon'] ); ?>"
                                 alt="<?php echo esc_attr( $plugin['name'] ); ?>">
                        </div>
                        <div class="spslider-plugin-card-name"><?php echo esc_html( $plugin['name'] ); ?></div>
                        <div class="spslider-plugin-card-desc"><?php echo esc_html( $plugin['desc'] ); ?></div>
                        <div class="spslider-plugin-card-link">
                            <?php esc_html_e( 'Learn more', 'syntekpro-slider' ); ?> &rarr;
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div><!-- /other-plugins -->

        </div><!-- /page-content -->
    </div><!-- /page-layout -->
</div><!-- /wrap -->
