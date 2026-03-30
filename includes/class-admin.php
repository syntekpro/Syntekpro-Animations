<?php
/**
 * Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Animations_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_branding_css'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_item'), 80);
        add_action('admin_head', array($this, 'menu_icon_sizing_css'));
    }

    /**
     * Lightweight Pattern Browser: defines data for reuse.
     */
    private function get_pattern_browser_items() {
        return array(
            array(
                'slug' => 'syntekpro/hero-spotlight',
                'title' => __('Hero Spotlight', 'syntekpro-animations'),
                'desc' => __('Centered hero with CTA buttons and subtle fade-up.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/card-stagger',
                'title' => __('Card Stagger Grid', 'syntekpro-animations'),
                'desc' => __('Three-column feature grid with staggered entrance.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/cta-banner',
                'title' => __('CTA Banner', 'syntekpro-animations'),
                'desc' => __('Full-width CTA bar with fade-up motion.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/pricing-grid',
                'title' => __('Pricing Grid', 'syntekpro-animations'),
                'desc' => __('Three-plan pricing layout with animated emphasis.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/faq-accordion',
                'title' => __('FAQ Accordion', 'syntekpro-animations'),
                'desc' => __('Clean FAQ with collapsible items and soft motion.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/testimonial-stack',
                'title' => __('Testimonials Stack', 'syntekpro-animations'),
                'desc' => __('Stacked testimonials with staggered reveal.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/hero-split',
                'title' => __('Hero Split', 'syntekpro-animations'),
                'desc' => __('Two-column hero with media and CTA.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/stats-row',
                'title' => __('Stats Row', 'syntekpro-animations'),
                'desc' => __('Key metrics with subtle entrance.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/logo-strip',
                'title' => __('Logo Strip', 'syntekpro-animations'),
                'desc' => __('Trusted-by logos with soft motion.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/steps-walkthrough',
                'title' => __('Steps Walkthrough', 'syntekpro-animations'),
                'desc' => __('Three-step process with icons.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/feature-checklist',
                'title' => __('Feature Checklist', 'syntekpro-animations'),
                'desc' => __('Icon list with animated reveal.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/comparison-table',
                'title' => __('Comparison Table', 'syntekpro-animations'),
                'desc' => __('Side-by-side plan comparison.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/newsletter-band',
                'title' => __('Newsletter Band', 'syntekpro-animations'),
                'desc' => __('Wide email capture with CTA.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/gallery-tiles',
                'title' => __('Gallery Tiles', 'syntekpro-animations'),
                'desc' => __('Masonry-style gallery tiles.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/testimonial-highlight',
                'title' => __('Testimonial Highlight', 'syntekpro-animations'),
                'desc' => __('Single spotlight quote with badge.', 'syntekpro-animations')
            ),
            array(
                'slug' => 'syntekpro/cta-minimal',
                'title' => __('CTA Minimal', 'syntekpro-animations'),
                'desc' => __('Clean call-to-action band.', 'syntekpro-animations')
            )
        );
    }

    private function render_page_header($title, $subtitle = '', $meta = '') {
        $logo_url = SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animations%20Transparent%20Logo%20with%20Favicon.png';
        ?>
        <style>
            .syntekpro-settings-wrapper{background:#f5fff5 !important;padding:16px;border-radius:12px;}
            .syntekpro-settings-section{background:#f5fff5 !important;}
            .syntekpro-page-intro h1{color:#b91c1c;font-weight:700;}
            .syntekpro-page-intro p{color:#b91c1c;font-weight:700;}
        </style>
        <div class="syntekpro-page-hero" style="text-align:center;margin:0 auto 14px;padding:16px 0;background:#ffd9e6;border-radius:12px;">
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr__('Syntekpro Animations Logo', 'syntekpro-animations'); ?>" style="width:200px;max-width:100%;height:auto;display:block;margin:0 auto;" />
        </div>
        <?php if (!empty($title) || !empty($subtitle) || !empty($meta)) : ?>
            <div class="syntekpro-page-intro" style="margin:0 auto 18px 0;text-align:center;color:#0f172a;">
                <?php if (!empty($title)) : ?><h1 style="margin:0 0 6px 0;font-size:24px;line-height:1.25;"><?php echo esc_html($title); ?></h1><?php endif; ?>
                <?php if (!empty($subtitle)) : ?><p style="margin:0 0 6px 0;font-size:14px;"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
                <?php if (!empty($meta)) : ?><div style="color:#475569;font-size:12px;"><?php echo esc_html($meta); ?></div><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php
    }

    private function render_page_footer($extra = '') {
        ?>
        <div class="syntekpro-page-footer" style="margin-top:28px;padding:14px 16px;text-align:center;background:#ffd9e6;border-radius:12px;color:#0f172a;font-size:13px;font-weight:600;">
            <strong><?php _e('Syntekpro Animations', 'syntekpro-animations'); ?></strong>
            <span style="margin-left:6px;"><?php echo esc_html(sprintf(__('Version %s', 'syntekpro-animations'), SYNTEKPRO_ANIM_VERSION)); ?></span>
            <?php if (!empty($extra)) : ?>
                <span style="margin-left:10px;color:#475569;font-weight:400;"><?php echo esc_html($extra); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Keep the custom menu icon scaled to a small footprint in the WP sidebar.
     */
    public function menu_icon_sizing_css() {
        echo '<style>
            #toplevel_page_syntekpro-animations .wp-menu-image img{
                width:28px;height:28px;object-fit:contain;
                filter:grayscale(0) brightness(1.2);opacity:0.95;
                position:relative;top:0;
            }
            #toplevel_page_syntekpro-animations:hover .wp-menu-image img,
            #toplevel_page_syntekpro-animations.wp-has-current-submenu .wp-menu-image img,
            #toplevel_page_syntekpro-animations.current .wp-menu-image img,
            #toplevel_page_syntekpro-animations.wp-has-current-submenu:hover .wp-menu-image img{
                filter: grayscale(1) brightness(1.05) contrast(92%);
                opacity:1;
            }
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations-plus"]{
                color:#f4c542 !important;
                font-weight:700;
            }
            #toplevel_page_syntekpro-animations .wp-submenu li.current a[href="admin.php?page=syntekpro-animations-plus"]{
                color:#ffd84d !important;
            }
            #toplevel_page_syntekpro-animations .wp-submenu a{
                display:flex;
                align-items:center;
                gap:8px;
            }
            #toplevel_page_syntekpro-animations .wp-submenu a::before{
                font-family:dashicons;
                font-size:14px;
                width:16px;
                color:#94a3b8;
                line-height:1;
            }
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations"]::before{content:"\\f226";}
            #toplevel_page_syntekpro-animations .wp-submenu a[href="edit.php?post_type=syntekpro_slider"]::before{content:"\\f161";}
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations-patterns"]::before{content:"\\f538";}
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations-builder"]::before{content:"\\f540";}
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations-presets"]::before{content:"\\f479";}
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations-settings"]::before{content:"\\f111";}
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations-about"]::before{content:"\\f223";}
            #toplevel_page_syntekpro-animations .wp-submenu a[href="admin.php?page=syntekpro-animations-plus"]::before{content:"\\f155";color:#f4c542;}
        </style>';
    }

    /**
     * Enqueue admin branding CSS
     */
    public function enqueue_admin_branding_css($hook) {
        if (isset($_GET['page']) && strpos($_GET['page'], 'syntekpro-animations') === 0) {
            wp_enqueue_style('syntekpro-admin-branding', SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/admin-branding.css', array(), SYNTEKPRO_ANIM_VERSION);
            wp_enqueue_style('syntekpro-admin-settings-ui', SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/css/admin-settings-ui.css', array(), SYNTEKPRO_ANIM_VERSION);
        }
    }

    /**
     * Dashboard landing page
     */
    public function dashboard_page() {
        $cards = array(
            array('label' => __('Sliders', 'syntekpro-animations'), 'icon' => '🎠', 'desc' => __('Build interactive slider experiences', 'syntekpro-animations'), 'url' => admin_url('edit.php?post_type=syntekpro_slider'), 'style' => 'linear-gradient(135deg,#ecfeff,#f0f9ff)'),
            array('label' => __('Presets', 'syntekpro-animations'), 'icon' => '🗂️', 'desc' => __('Browse ready animations', 'syntekpro-animations'), 'url' => admin_url('admin.php?page=syntekpro-animations-presets'), 'style' => 'linear-gradient(135deg,#f8fafc,#eef2ff)'),
            array('label' => __('Patterns', 'syntekpro-animations'), 'icon' => '🧩', 'desc' => __('Drop ready-made page sections', 'syntekpro-animations'), 'url' => admin_url('admin.php?page=syntekpro-animations-patterns'), 'style' => 'linear-gradient(135deg,#f1f5f9,#e2e8f0)'),
            array('label' => __('Builder', 'syntekpro-animations'), 'icon' => '🎨', 'desc' => __('Visual animation builder', 'syntekpro-animations'), 'url' => admin_url('admin.php?page=syntekpro-animations-builder'), 'style' => 'linear-gradient(135deg,#fff7ed,#ffe4e6)'),
            array('label' => __('Settings', 'syntekpro-animations'), 'icon' => '⚙️', 'desc' => __('Engine and options', 'syntekpro-animations'), 'url' => admin_url('admin.php?page=syntekpro-animations-settings'), 'style' => 'linear-gradient(135deg,#f8f9fa,#e9ecef)'),
            array('label' => __('About', 'syntekpro-animations'), 'icon' => 'ℹ️', 'desc' => __('Docs, help, and system status in one place', 'syntekpro-animations'), 'url' => admin_url('admin.php?page=syntekpro-animations-about'), 'style' => 'linear-gradient(135deg,#eff6ff,#e0f2fe)')
        );
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Syntekpro Animations', 'syntekpro-animations'), __('Pick your next step: sliders, presets, patterns, builder, and settings.', 'syntekpro-animations'), sprintf(__('Version %s', 'syntekpro-animations'), esc_html(SYNTEKPRO_ANIM_VERSION))); ?>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
                <?php foreach ($cards as $card) : ?>
                    <a href="<?php echo esc_url($card['url']); ?>" class="syntekpro-dash-card" style="text-decoration:none;background:<?php echo esc_attr($card['style']); ?>;border-radius:12px;padding:16px;border:1px solid #e5e7eb;box-shadow:0 10px 30px rgba(15,23,42,0.08);display:flex;gap:12px;align-items:flex-start;">
                        <span style="font-size:22px;line-height:1;"><?php echo esc_html($card['icon']); ?></span>
                        <div>
                            <div style="font-size:15px;font-weight:700;color:#0f172a;"><?php echo esc_html($card['label']); ?></div>
                            <div style="color:#475569;margin-top:4px;font-size:13px;"><?php echo esc_html($card['desc']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
                <div style="background:#0f172a;color:#fff;border-radius:12px;padding:16px;">
                    <h4 style="margin:0 0 6px 0;">🚀 <?php _e('Start with presets', 'syntekpro-animations'); ?></h4>
                    <p style="margin:0 0 10px 0;opacity:0.85;"><?php _e('Apply a prebuilt animation in seconds, then tweak timing.', 'syntekpro-animations'); ?></p>
                    <a class="button" style="background:#fff;color:#0f172a;border-color:#fff;" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-presets')); ?>"><?php _e('Open Presets', 'syntekpro-animations'); ?></a>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                    <h4 style="margin:0 0 6px 0;">🧩 <?php _e('Pattern library', 'syntekpro-animations'); ?></h4>
                    <p style="margin:0 0 10px 0;color:#334155;"><?php _e('Insert ready-made sections with motion baked in.', 'syntekpro-animations'); ?></p>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-patterns')); ?>"><?php _e('Open Patterns', 'syntekpro-animations'); ?></a>
                </div>
                <div style="background:#fff7ed;border:1px solid #ffedd5;border-radius:12px;padding:16px;">
                    <h4 style="margin:0 0 6px 0;">🧭 <?php _e('Need guidance?', 'syntekpro-animations'); ?></h4>
                    <p style="margin:0 0 10px 0;color:#7c2d12;"><?php _e('Open About to access docs, help center, and system status.', 'syntekpro-animations'); ?></p>
                    <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-about')); ?>"><?php _e('Open About', 'syntekpro-animations'); ?></a>
                </div>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>
        <?php
    }

    /**
     * Add quick access to the admin bar (block editor)
     */
    public function add_admin_bar_item($admin_bar) {
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }
        if (!function_exists('get_current_screen')) {
            return;
        }
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'post') {
            return;
        }

        $admin_bar->add_node(array(
            'id' => 'syntekpro-animations-toolbar',
            'title' => '<span class="ab-icon dashicons dashicons-image-filter"></span><span class="ab-label">Syntekpro Animation</span>',
            'href' => admin_url('admin.php?page=syntekpro-animations'),
            'meta' => array('title' => __('Open Syntekpro Animations settings', 'syntekpro-animations'))
        ));
    }

    /**
     * Register admin menu pages
     */
    public function add_admin_menu() {
        $capability = 'manage_options';
        $icon = SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animation%20Favicon%20Greyed%20for%20WP.png';

        add_menu_page(
            __('Syntekpro Animations', 'syntekpro-animations'),
            __('SyntekPro Animations', 'syntekpro-animations'),
            $capability,
            'syntekpro-animations',
            array($this, 'dashboard_page'),
            $icon,
            58
        );

        add_submenu_page(
            'syntekpro-animations',
            __('Dashboard', 'syntekpro-animations'),
            __('Dashboard', 'syntekpro-animations'),
            $capability,
            'syntekpro-animations',
            array($this, 'dashboard_page')
        );

        add_submenu_page(
            'syntekpro-animations',
            __('Sliders', 'syntekpro-animations'),
            __('Sliders', 'syntekpro-animations'),
            $capability,
            'edit.php?post_type=syntekpro_slider'
        );

        add_submenu_page('syntekpro-animations', __('Patterns', 'syntekpro-animations'), __('Patterns', 'syntekpro-animations'), $capability, 'syntekpro-animations-patterns', array($this, 'patterns_page'));
        add_submenu_page('syntekpro-animations', __('Builder', 'syntekpro-animations'), __('Builder', 'syntekpro-animations'), $capability, 'syntekpro-animations-builder', array($this, 'builder_page'));
        add_submenu_page('syntekpro-animations', __('Presets', 'syntekpro-animations'), __('Presets', 'syntekpro-animations'), $capability, 'syntekpro-animations-presets', array($this, 'presets_page'));

        add_submenu_page(
            'syntekpro-animations',
            __('Settings', 'syntekpro-animations'),
            __('Settings', 'syntekpro-animations'),
            $capability,
            'syntekpro-animations-settings',
            array($this, 'settings_page')
        );

        add_submenu_page('syntekpro-animations', __('About', 'syntekpro-animations'), __('About', 'syntekpro-animations'), $capability, 'syntekpro-animations-about', array($this, 'about_page'));
        add_submenu_page('syntekpro-animations', __('Animations+', 'syntekpro-animations'), __('★ Animations+', 'syntekpro-animations'), $capability, 'syntekpro-animations-plus', array($this, 'animations_plus_page'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General Settings
        register_setting('syntekpro_anim_general', 'syntekpro_anim_load_gsap');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_load_scrolltrigger');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_smooth_scroll');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_enable_developer_mode');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_disable_mobile');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_lazy_load');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_debug_overlay');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_debug_overlay_persist_role');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_silence_console');
        // Engine preference: auto (default), css (light), gsap (force GSAP)
        register_setting('syntekpro_anim_general', 'syntekpro_anim_engine');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_reduced_motion');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_debug_mode');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_default_duration');
        register_setting('syntekpro_anim_general', 'syntekpro_anim_default_ease');
        
        // Free Plugins
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_flip');
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_observer');
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_scrolltoplugin');
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_textplugin');
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_draggable');
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_motionpathplugin');
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_easepack');
        register_setting('syntekpro_anim_plugins', 'syntekpro_anim_load_customease');
        
        // Pro Plugins
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_splittext');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_morphsvgplugin');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_drawsvgplugin');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_scrollsmoother');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_gsdevtools');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_inertiaplugin');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_scrambletextplugin');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_custombounce');
        register_setting('syntekpro_anim_pro', 'syntekpro_anim_load_customwiggle');
        
        // License
        register_setting('syntekpro_anim_license', 'syntekpro_anim_license_key', array($this, 'sanitize_license'));
        register_setting('syntekpro_anim_license', 'syntekpro_anim_free_preset_limit', array($this, 'sanitize_free_preset_limit'));
    }

    /**
     * Sanitize free preset limit for Animations+ model.
     */
    public function sanitize_free_preset_limit($value) {
        $limit = absint($value);
        if ($limit < 1) {
            $limit = 15;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        return $limit;
    }
    
    /**
     * Sanitize license key
     */
    public function sanitize_license($new_value) {
        $old_value = get_option('syntekpro_anim_license_key');
        
        if ($new_value !== $old_value) {
            // License key changed, validate it
            delete_option('syntekpro_anim_license_status');
            $this->activate_license($new_value);
        }
        
        return sanitize_text_field($new_value);
    }
    
    /**
     * Activate license
     */
    private function activate_license($license_key) {
        // API endpoint for license validation
        $api_url = 'https://syntekpro.com/wp-json/syntekpro/v1/validate-license';

        update_option('syntekpro_anim_license_last_checked', current_time('mysql'));
        
        $response = wp_remote_post($api_url, array(
            'body' => array(
                'license_key' => $license_key,
                'product' => 'syntekpro-animations',
                'url' => home_url()
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            update_option('syntekpro_anim_license_status', 'error');
            update_option('syntekpro_anim_license_last_result', 'error');
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['valid']) && $data['valid'] === true) {
            update_option('syntekpro_anim_license_status', 'valid');
            update_option('syntekpro_anim_license_expires', $data['expires'] ?? '');
            update_option('syntekpro_anim_license_last_result', 'valid');
            return true;
        } else {
            update_option('syntekpro_anim_license_status', 'invalid');
            update_option('syntekpro_anim_license_last_result', 'invalid');
            return false;
        }
    }
    /**
     * Admin notices
     */
    public function admin_notices() {
        $screen = get_current_screen();
        if ($screen->id !== 'toplevel_page_syntekpro-animations') {
            return;
        }

        $license_status = get_option('syntekpro_anim_license_status');
        if ($license_status === 'valid') {
            echo '<div class="notice notice-success"><p><strong>✓ Pro License Active</strong> - All premium features are unlocked!</p></div>';
        } elseif ($license_status === 'invalid') {
            echo '<div class="notice notice-error"><p><strong>Invalid License Key</strong> - Please check your license key and try again.</p></div>';
        } elseif ($license_status === 'expired') {
            echo '<div class="notice notice-warning"><p><strong>License Expired</strong> - Please renew your license to continue using Pro features.</p></div>';
        }
    }

    /**
     * Settings page
     */
    public function settings_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Settings', 'syntekpro-animations'), __('Configure the animation engine, GSAP plugins, and license details.', 'syntekpro-animations'), sprintf(__('Version %s', 'syntekpro-animations'), SYNTEKPRO_ANIM_VERSION)); ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=syntekpro-animations-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">⚙️ <?php _e('General Settings', 'syntekpro-animations'); ?></a>
                <a href="?page=syntekpro-animations-settings&tab=plugins" class="nav-tab <?php echo $active_tab === 'plugins' ? 'nav-tab-active' : ''; ?>">🎨 <?php _e('GSAP Plugins', 'syntekpro-animations'); ?></a>
                <a href="?page=syntekpro-animations-settings&tab=license" class="nav-tab <?php echo $active_tab === 'license' ? 'nav-tab-active' : ''; ?>">🔐 <?php _e('License', 'syntekpro-animations'); ?> <?php if (function_exists('syntekpro_animations') && syntekpro_animations()->is_pro_active()) echo '<span style="color:#46b450;"> ✓ Pro</span>'; ?></a>
            </h2>

            <div class="syntekpro-settings-section">
                <?php
                switch ($active_tab) {
                    case 'plugins':
                        $this->render_plugins_tab();
                        break;
                    case 'license':
                        $this->render_license_tab();
                        break;
                    case 'general':
                    default:
                        $this->render_general_tab();
                        break;
                }
                ?>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>
        <?php
    }

    /**
     * Render General tab
     */
    private function render_general_tab() {
        $release = get_site_transient('syntekpro_anim_github_release');
        $latest_version = get_option('syntekpro_anim_update_latest_version', '');
        if (empty($latest_version) && is_array($release) && !empty($release['version'])) {
            $latest_version = (string) $release['version'];
        }
        $last_checked = (string) get_option('syntekpro_anim_update_last_checked', '');
        $last_result = (string) get_option('syntekpro_anim_update_last_result', 'not_checked');

        $update_state = __('Not checked yet', 'syntekpro-animations');
        if ($last_result === 'ok') {
            $update_state = __('Connected to GitHub release feed', 'syntekpro-animations');
        } elseif ($last_result === 'error') {
            $update_state = __('Last check failed (network/API)', 'syntekpro-animations');
        } elseif ($last_result === 'invalid_payload') {
            $update_state = __('Last check returned unexpected release data', 'syntekpro-animations');
        }

        $last_checked_label = !empty($last_checked) ? $last_checked : __('Never', 'syntekpro-animations');
        $latest_label = !empty($latest_version) ? $latest_version : __('Unknown', 'syntekpro-animations');
        ?>
        <style>
            .syntekpro-persona-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin:10px 0 16px;}
            .syntekpro-persona-card{border:1px solid #e5e7eb;border-radius:10px;padding:12px;background:#fff;display:flex;align-items:flex-start;gap:10px;box-shadow:0 8px 20px rgba(15,23,42,0.05);text-decoration:none;color:#0f172a;}
            .syntekpro-persona-card:hover{border-color:#0f172a;box-shadow:0 10px 24px rgba(15,23,42,0.08);}    
            .syntekpro-section-heading{background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;}
            .syntekpro-section-heading h3{margin:0;font-size:15px;}
            .syntekpro-section-heading p{margin:4px 0 0 0;color:#475569;}
        </style>

        <h2>⚙️ <?php _e('Core Settings', 'syntekpro-animations'); ?></h2>

        <div style="margin:12px 0 16px;border:1px solid #dbeafe;background:#eff6ff;border-radius:10px;padding:14px;">
            <h3 style="margin:0 0 8px 0;color:#1e3a8a;">🔄 <?php _e('Update Channel Status', 'syntekpro-animations'); ?></h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
                <div><strong><?php _e('Installed Version', 'syntekpro-animations'); ?>:</strong> <?php echo esc_html(SYNTEKPRO_ANIM_VERSION); ?></div>
                <div><strong><?php _e('Latest GitHub Release', 'syntekpro-animations'); ?>:</strong> <?php echo esc_html($latest_label); ?></div>
                <div><strong><?php _e('Last Update Check', 'syntekpro-animations'); ?>:</strong> <?php echo esc_html($last_checked_label); ?></div>
                <div><strong><?php _e('Connection Status', 'syntekpro-animations'); ?>:</strong> <?php echo esc_html($update_state); ?></div>
            </div>
            <p style="margin:10px 0 0 0;color:#334155;"><?php _e('WordPress checks for plugin updates on its normal schedule. You can also trigger checks from Dashboard > Updates.', 'syntekpro-animations'); ?></p>
        </div>

        <div class="syntekpro-persona-grid">
            <a class="syntekpro-persona-card" href="#user-options">
                <span style="font-size:24px;line-height:1;">🙋</span>
                <div>
                    <div style="font-weight:700;"><?php _e('User Options', 'syntekpro-animations'); ?></div>
                    <div style="color:#475569;font-size:13px;"><?php _e('Safer defaults for editors and marketers.', 'syntekpro-animations'); ?></div>
                </div>
            </a>
            <a class="syntekpro-persona-card" href="#designer-options">
                <span style="font-size:24px;line-height:1;">🎨</span>
                <div>
                    <div style="font-weight:700;"><?php _e('Designer Options', 'syntekpro-animations'); ?></div>
                    <div style="color:#475569;font-size:13px;"><?php _e('Default easing and timing controls.', 'syntekpro-animations'); ?></div>
                </div>
            </a>
            <a class="syntekpro-persona-card" href="#developer-options">
                <span style="font-size:24px;line-height:1;">👩‍💻</span>
                <div>
                    <div style="font-weight:700;"><?php _e('Developer Options', 'syntekpro-animations'); ?></div>
                    <div style="color:#475569;font-size:13px;"><?php _e('Debug helpers, overlays, and logs.', 'syntekpro-animations'); ?></div>
                </div>
            </a>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('syntekpro_anim_general'); ?>
            <input type="hidden" name="syntekpro_anim_general_nonce" value="<?php echo wp_create_nonce('syntekpro_anim_general_action'); ?>">

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th colspan="2" class="syntekpro-section-heading" id="user-options">
                            <h3>🙋 <?php _e('User Options', 'syntekpro-animations'); ?></h3>
                            <p class="description"><?php _e('Keep content editors fast and safe with sensible defaults.', 'syntekpro-animations'); ?></p>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_load_gsap"><?php _e('Animation Engine', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <label class="syntekpro-toggle">
                                <input type="checkbox" id="syntekpro_anim_load_gsap" name="syntekpro_anim_load_gsap" value="yes" <?php checked(get_option('syntekpro_anim_load_gsap', 'yes'), 'yes'); ?>>
                                <strong><?php _e('Enable Animation Engine', 'syntekpro-animations'); ?></strong>
                            </label>
                            <p class="description"><?php _e("Turns on Syntekpro's runtime across the site.", 'syntekpro-animations'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_load_scrolltrigger"><?php _e('Scroll Animations', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <label class="syntekpro-toggle">
                                <input type="checkbox" id="syntekpro_anim_load_scrolltrigger" name="syntekpro_anim_load_scrolltrigger" value="yes" <?php checked(get_option('syntekpro_anim_load_scrolltrigger', 'yes'), 'yes'); ?>>
                                <strong><?php _e('Enable Scroll-Based Animations', 'syntekpro-animations'); ?></strong>
                            </label>
                            <p class="description"><span style="color:#2e7d32;">✓</span> <?php _e('Trigger animations when scrolling. Highly recommended.', 'syntekpro-animations'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_smooth_scroll"><?php _e('Smooth Scroll', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_smooth_scroll" name="syntekpro_anim_smooth_scroll" value="yes" <?php checked(get_option('syntekpro_anim_smooth_scroll', 'no'), 'yes'); ?>>
                            <span class="description"><?php _e('Enable smooth scrolling for long pages.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_disable_mobile"><?php _e('Disable on Mobile', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_disable_mobile" name="syntekpro_anim_disable_mobile" value="yes" <?php checked(get_option('syntekpro_anim_disable_mobile', 'no'), 'yes'); ?>>
                            <span class="description"><?php _e('Skip animations on small screens.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_lazy_load"><?php _e('Lazy Load', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_lazy_load" name="syntekpro_anim_lazy_load" value="yes" <?php checked(get_option('syntekpro_anim_lazy_load', 'no'), 'yes'); ?>>
                            <span class="description"><?php _e('Load animation assets only when needed.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_reduced_motion"><?php _e('Respect Reduced Motion', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_reduced_motion" name="syntekpro_anim_reduced_motion" value="yes" <?php checked(get_option('syntekpro_anim_reduced_motion', 'yes'), 'yes'); ?>>
                            <span class="description"><?php _e('Lower motion intensity for users who prefer reduced motion.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2" class="syntekpro-section-heading" id="designer-options">
                            <h3>🎨 <?php _e('Designer Options', 'syntekpro-animations'); ?></h3>
                            <p class="description"><?php _e('Control the default feel of animations before fine-tuning blocks.', 'syntekpro-animations'); ?></p>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_engine"><?php _e('Default Engine Mode', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <select id="syntekpro_anim_engine" name="syntekpro_anim_engine" style="min-width:240px;">
                                <option value="auto" <?php selected(get_option('syntekpro_anim_engine', 'auto'), 'auto'); ?>><?php _e('Auto (smart choose CSS/GSAP)', 'syntekpro-animations'); ?></option>
                                <option value="css" <?php selected(get_option('syntekpro_anim_engine', 'auto'), 'css'); ?>><?php _e('CSS Only (light, fast)', 'syntekpro-animations'); ?></option>
                                <option value="gsap" <?php selected(get_option('syntekpro_anim_engine', 'auto'), 'gsap'); ?>><?php _e('GSAP Only (full power)', 'syntekpro-animations'); ?></option>
                            </select>
                            <p class="description"><?php _e('Auto uses CSS for common fades/slides/zooms and GSAP for advanced effects.', 'syntekpro-animations'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_default_duration"><?php _e('Default Duration (seconds)', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.1" min="0" id="syntekpro_anim_default_duration" name="syntekpro_anim_default_duration" value="<?php echo esc_attr(get_option('syntekpro_anim_default_duration', '0.8')); ?>" style="width:120px;">
                            <p class="description"><?php _e('Base duration used when a block does not specify one.', 'syntekpro-animations'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_default_ease"><?php _e('Default Ease', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="syntekpro_anim_default_ease" name="syntekpro_anim_default_ease" value="<?php echo esc_attr(get_option('syntekpro_anim_default_ease', 'power2.out')); ?>" class="regular-text">
                            <p class="description"><?php _e('Fallback easing when none is set on the block.', 'syntekpro-animations'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2" class="syntekpro-section-heading" id="developer-options">
                            <h3>👩‍💻 <?php _e('Developer Options', 'syntekpro-animations'); ?></h3>
                            <p class="description"><?php _e('Debugging helpers for building and troubleshooting animations.', 'syntekpro-animations'); ?></p>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_enable_developer_mode"><?php _e('Developer Mode', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_enable_developer_mode" name="syntekpro_anim_enable_developer_mode" value="yes" <?php checked(get_option('syntekpro_anim_enable_developer_mode', 'no'), 'yes'); ?>>
                            <span class="description"><?php _e('Show debugging helpers and extra console detail.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_debug_overlay"><?php _e('Debug Overlay', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_debug_overlay" name="syntekpro_anim_debug_overlay" value="yes" <?php checked(get_option('syntekpro_anim_debug_overlay', 'no'), 'yes'); ?>>
                            <span class="description"><?php _e('Show on-page debug overlay.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_debug_overlay_persist_role"><?php _e('Persist Overlay For Role', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="syntekpro_anim_debug_overlay_persist_role" name="syntekpro_anim_debug_overlay_persist_role" value="<?php echo esc_attr(get_option('syntekpro_anim_debug_overlay_persist_role', '')); ?>" class="regular-text" placeholder="administrator">
                            <p class="description"><?php _e('Keep the debug overlay visible for this role.', 'syntekpro-animations'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_debug_mode"><?php _e('Debug Mode', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_debug_mode" name="syntekpro_anim_debug_mode" value="yes" <?php checked(get_option('syntekpro_anim_debug_mode', 'no'), 'yes'); ?>>
                            <span class="description"><?php _e('Enable verbose logging for troubleshooting.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="syntekpro_anim_silence_console"><?php _e('Silence Console', 'syntekpro-animations'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="syntekpro_anim_silence_console" name="syntekpro_anim_silence_console" value="yes" <?php checked(get_option('syntekpro_anim_silence_console', 'no'), 'yes'); ?>>
                            <span class="description"><?php _e('Suppress non-essential console logs.', 'syntekpro-animations'); ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(__('Save Settings', 'syntekpro-animations')); ?>
        </form>
        <?php
    }

    /**
     * Render Plugins tab
     */
    private function render_plugins_tab() {
                $free_plugins = array(
                    'flip' => 'Flip',
                    'observer' => 'Observer',
                    'scrolltoplugin' => 'ScrollToPlugin',
                    'textplugin' => 'TextPlugin',
                    'motionpathplugin' => 'MotionPathPlugin',
                    'easepack' => 'EasePack',
                    'customease' => 'CustomEase'
                );

                $pro_plugins = array(
                    'splittext' => 'SplitText',
                    'morphsvgplugin' => 'MorphSVGPlugin',
                    'drawsvgplugin' => 'DrawSVGPlugin',
                    'scrollsmoother' => 'ScrollSmoother',
                    'gsdevtools' => 'GSDevTools',
                    'inertiaplugin' => 'InertiaPlugin',
                    'scrambletextplugin' => 'ScrambleTextPlugin',
                    'custombounce' => 'CustomBounce',
                    'customwiggle' => 'CustomWiggle'
                );

                $pro_descriptions = array(
                    'splittext' => __('Animate text by characters, words, or lines', 'syntekpro-animations'),
                    'morphsvgplugin' => __('Morph between SVG shapes smoothly', 'syntekpro-animations'),
                    'drawsvgplugin' => __('Progressively draw SVG strokes', 'syntekpro-animations'),
                    'scrollsmoother' => __('Buttery smooth scrolling effects', 'syntekpro-animations'),
                    'gsdevtools' => __('Visual timeline editor and debugger', 'syntekpro-animations'),
                    'inertiaplugin' => __('Physics-based momentum scrolling', 'syntekpro-animations'),
                    'scrambletextplugin' => __('Scramble and unscramble text', 'syntekpro-animations'),
                    'custombounce' => __('Create custom bounce easing curves', 'syntekpro-animations'),
                    'customwiggle' => __('Add custom wiggle/jiggle animations', 'syntekpro-animations')
                );

                $is_pro = function_exists('syntekpro_animations') && method_exists(syntekpro_animations(), 'is_pro_active') ? syntekpro_animations()->is_pro_active() : false;
                ?>
                <h2>🧩 <?php _e('GSAP Plugins', 'syntekpro-animations'); ?></h2>

                <form method="post" action="options.php" style="margin-bottom:22px;">
                    <?php settings_fields('syntekpro_anim_plugins'); ?>
                    <table class="form-table" role="presentation">
                        <tbody>
                        <?php foreach ($free_plugins as $key => $label) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($label); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="syntekpro_anim_load_<?php echo esc_attr($key); ?>" value="yes" <?php checked(get_option('syntekpro_anim_load_' . $key, 'no'), 'yes'); ?>>
                                        <span class="description"><?php _e('Load this plugin in the editor and front end.', 'syntekpro-animations'); ?></span>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php submit_button(__('Save Plugin Loading', 'syntekpro-animations')); ?>
                </form>

                <form method="post" action="options.php">
                    <?php settings_fields('syntekpro_anim_pro'); ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
                        <?php foreach ($pro_plugins as $key => $label) :
                            $option_value = get_option('syntekpro_anim_load_' . $key, 'no');
                            ?>
                            <div class="syntekpro-plugin-card pro-feature" style="border:1px solid #e5e7eb;border-radius:10px;padding:12px;background:#fff;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="syntekpro_anim_load_<?php echo esc_attr($key); ?>" value="yes" <?php checked($option_value, 'yes'); ?>>
                                    <strong><?php echo esc_html($label); ?></strong>
                                    <span class="pro-badge">PRO</span>
                                </label>
                                <p class="plugin-description" style="margin:6px 0 0 0;color:#475569;"><?php echo esc_html($pro_descriptions[$key]); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php submit_button(__('Save Pro Plugins', 'syntekpro-animations')); ?>
                </form>

        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:18px;margin-top:24px;text-align:center;">
            <h3 style="margin:0 0 8px 0;color:#0f172a;">🚀 <?php _e('Upgrade to Pro', 'syntekpro-animations'); ?></h3>
            <p style="margin:0 0 10px 0;color:#334155;"><?php _e('Production sites need a Pro license. This test site is fully unlocked for evaluation.', 'syntekpro-animations'); ?></p>
            <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="background:#0f172a;border-color:#0f172a;padding:10px 26px;" target="_blank">
                ⭐ <?php _e('Purchase Pro License', 'syntekpro-animations'); ?>
            </a>
        </div>
        <?php
    }
    
    /**
     * Render License tab
     */
    private function render_license_tab() {
        $license_key = get_option('syntekpro_anim_license_key', '');
        $license_status = get_option('syntekpro_anim_license_status', '');
        $license_expires = get_option('syntekpro_anim_license_expires', '');
        ?>
        <h2><?php _e('License Management', 'syntekpro-animations'); ?></h2>
        
        <?php if ($license_status === 'valid') : ?>
            <div class="syntekpro-license-status active">
                <strong>✓ <?php _e('Pro License Active', 'syntekpro-animations'); ?></strong>
                <p><?php _e('All premium features are unlocked!', 'syntekpro-animations'); ?></p>
                <?php if ($license_expires) : ?>
                    <p style="font-size:13px;margin:0;"><?php echo sprintf(__('Expires: %s', 'syntekpro-animations'), esc_html($license_expires)); ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($license_status === 'invalid') : ?>
            <div class="syntekpro-license-status inactive">
                <strong>✗ <?php _e('Invalid License Key', 'syntekpro-animations'); ?></strong>
                <p><?php _e('Please check your license key and try again.', 'syntekpro-animations'); ?></p>
            </div>
        <?php elseif ($license_status === 'expired') : ?>
            <div class="syntekpro-license-status inactive">
                <strong>⚠ <?php _e('License Expired', 'syntekpro-animations'); ?></strong>
                <p><?php _e('Your license has expired. Please renew to continue using premium features.', 'syntekpro-animations'); ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="options.php" style="margin-top:20px;">
            <?php settings_fields('syntekpro_anim_license'); ?>
            <input type="hidden" name="syntekpro_anim_license_nonce" value="<?php echo wp_create_nonce('syntekpro_anim_license_action'); ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_license_key"><?php _e('License Key', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <input 
                            type="password" 
                            id="syntekpro_anim_license_key" 
                            name="syntekpro_anim_license_key" 
                            value="<?php echo esc_attr($license_key); ?>" 
                            class="regular-text" 
                            placeholder="<?php _e('Enter your license key', 'syntekpro-animations'); ?>">
                        <p class="description">
                            <?php _e('Paste your Syntekpro Animations Pro license key here.', 'syntekpro-animations'); ?>
                            <br><a href="https://syntekpro.com/account" target="_blank"><?php _e('Find your license key →', 'syntekpro-animations'); ?></a>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Activate License', 'syntekpro-animations')); ?>
        </form>
        
        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:20px;margin-top:24px;">
            <h3 style="margin-top:0;color:#0f172a;">🎉 <?php _e('Get Syntekpro Animations Pro', 'syntekpro-animations'); ?></h3>
            <p style="color:#334155;"><?php _e('Unlock all premium GSAP plugins and features. This test site is already unlocked for evaluation.', 'syntekpro-animations'); ?></p>
            <ul style="columns: 2; gap: 20px; margin:12px 0; color:#0f172a;">
                <li><strong>Timeline Builder</strong> - Visual animation sequencer</li>
                <li><strong>Text Effects</strong> - Character & word animations</li>
                <li><strong>SVG Morph</strong> - Smooth shape morphing</li>
                <li><strong>Draw Effects</strong> - Progressive stroke drawing</li>
                <li><strong>Smooth Scroll</strong> - Buttery smooth scrolling</li>
                <li><strong>Animation Editor</strong> - Visual timeline editor</li>
                <li><strong>Physics Engine</strong> - Realistic motion effects</li>
                <li><strong>50+ Pro Animations</strong> - Premium effects library</li>
            </ul>
            <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="margin-top:10px;padding:10px 26px;font-size:15px;background:#0f172a;border-color:#0f172a;" target="_blank">
                ⭐ <?php _e('Purchase Pro License', 'syntekpro-animations'); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Animations+ page with free-vs-paid details and license controls.
     */
    public function animations_plus_page() {
        $license_key = get_option('syntekpro_anim_license_key', '');
        $license_status = get_option('syntekpro_anim_license_status', '');
        $last_checked = get_option('syntekpro_anim_license_last_checked', '');
        $last_result = get_option('syntekpro_anim_license_last_result', '');
        $is_pro = function_exists('syntekpro_animations') && syntekpro_animations()->is_pro_active();
        $free_limit = (int) get_option('syntekpro_anim_free_preset_limit', 15);

        if (
            isset($_POST['syntekpro_plus_validate_nonce'])
            && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['syntekpro_plus_validate_nonce'])), 'syntekpro_plus_validate')
            && current_user_can('manage_options')
        ) {
            $stored_key = trim((string) get_option('syntekpro_anim_license_key', ''));
            if ($stored_key === '') {
                echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('Please enter and save your key first, then validate.', 'syntekpro-animations') . '</p></div>';
            } else {
                $this->activate_license($stored_key);
                $license_status = get_option('syntekpro_anim_license_status', '');
                $last_checked = get_option('syntekpro_anim_license_last_checked', '');
                $last_result = get_option('syntekpro_anim_license_last_result', '');
                $is_pro = function_exists('syntekpro_animations') && syntekpro_animations()->is_pro_active();
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('License validation request completed.', 'syntekpro-animations') . '</p></div>';
            }
        }

        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Animations+', 'syntekpro-animations'), __('Use the first presets for free and unlock all remaining presets and premium engines with Animations+.', 'syntekpro-animations')); ?>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                    <h3 style="margin-top:0;">🔐 <?php _e('License & Validation', 'syntekpro-animations'); ?></h3>
                    <p style="margin-top:0;color:#475569;"><?php _e('Save your Animations+ key and validate it to unlock paid presets and Pro runtime features.', 'syntekpro-animations'); ?></p>

                    <div style="margin:0 0 12px 0;">
                        <strong><?php _e('Current Status:', 'syntekpro-animations'); ?></strong>
                        <?php if ($is_pro) : ?>
                            <span style="color:#16a34a;font-weight:700;"><?php _e('Active', 'syntekpro-animations'); ?></span>
                        <?php else : ?>
                            <span style="color:#b45309;font-weight:700;"><?php echo esc_html($license_status ? ucfirst($license_status) : __('Free Mode', 'syntekpro-animations')); ?></span>
                        <?php endif; ?>
                    </div>

                    <div style="margin:0 0 12px 0;color:#475569;font-size:12px;">
                        <div><strong><?php _e('Last Validation:', 'syntekpro-animations'); ?></strong> <?php echo esc_html($last_checked ? $last_checked : __('Never', 'syntekpro-animations')); ?></div>
                        <div><strong><?php _e('Last Result:', 'syntekpro-animations'); ?></strong> <?php echo esc_html($last_result ? ucfirst($last_result) : __('N/A', 'syntekpro-animations')); ?></div>
                    </div>

                    <form method="post" action="options.php" style="margin-bottom:12px;">
                        <?php settings_fields('syntekpro_anim_license'); ?>
                        <label for="syntekpro_anim_license_key_plus" style="display:block;font-weight:600;margin-bottom:6px;"><?php _e('Animations+ Key', 'syntekpro-animations'); ?></label>
                        <input
                            type="password"
                            id="syntekpro_anim_license_key_plus"
                            name="syntekpro_anim_license_key"
                            value="<?php echo esc_attr($license_key); ?>"
                            class="regular-text"
                            placeholder="<?php esc_attr_e('Enter your Animations+ key', 'syntekpro-animations'); ?>"
                        >
                        <label for="syntekpro_anim_free_preset_limit" style="display:block;font-weight:600;margin:10px 0 6px;"><?php _e('Free Preset Count', 'syntekpro-animations'); ?></label>
                        <input
                            type="number"
                            id="syntekpro_anim_free_preset_limit"
                            name="syntekpro_anim_free_preset_limit"
                            value="<?php echo esc_attr($free_limit); ?>"
                            min="1"
                            max="200"
                            style="width:120px;"
                        >
                        <p class="description" style="margin:6px 0 0 0;"><?php _e('How many presets remain available in free mode before Animations+ is required.', 'syntekpro-animations'); ?></p>
                        <?php submit_button(__('Save Key', 'syntekpro-animations'), 'secondary', 'submit', false, array('style' => 'margin-top:10px;')); ?>
                    </form>

                    <form method="post">
                        <?php wp_nonce_field('syntekpro_plus_validate', 'syntekpro_plus_validate_nonce'); ?>
                        <?php submit_button(__('Validate Key Now', 'syntekpro-animations'), 'primary', 'submit', false); ?>
                    </form>
                </div>

                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                    <h3 style="margin-top:0;">✨ <?php _e('Animations+ Enhancements', 'syntekpro-animations'); ?></h3>
                    <ul style="margin:0;padding-left:18px;color:#0f172a;line-height:1.7;">
                        <li><?php echo esc_html(sprintf(__('First %d presets stay free for every site.', 'syntekpro-animations'), $free_limit)); ?></li>
                        <li><?php _e('All additional preset families unlock with Animations+.', 'syntekpro-animations'); ?></li>
                        <li><?php _e('Premium GSAP plugins can be enabled when your key is active.', 'syntekpro-animations'); ?></li>
                        <li><?php _e('Timeline, advanced text/SVG effects, and Pro motion controls unlock together.', 'syntekpro-animations'); ?></li>
                        <li><?php _e('License-based runtime enforcement for frontend and shortcode usage.', 'syntekpro-animations'); ?></li>
                    </ul>
                    <a href="https://syntekpro.com/animations-pro" target="_blank" class="button button-primary" style="margin-top:14px;"><?php _e('Upgrade to Animations+', 'syntekpro-animations'); ?></a>
                </div>
            </div>

            <?php $this->render_page_footer(__('Animations+ centralizes paid features, key management, and validation.', 'syntekpro-animations')); ?>
        </div>
        <?php
    }
    
    /**
     * Presets page
     */
    public function presets_page() {
        $presets = Syntekpro_Animation_Presets::get_by_category();
        $categories = Syntekpro_Animation_Presets::get_categories();
        $selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $is_pro = function_exists('syntekpro_animations') && syntekpro_animations()->is_pro_active();
        $free_limit = (int) get_option('syntekpro_anim_free_preset_limit', 15);
        $free_count = count(array_filter($presets, function($preset) {
            return isset($preset['free']) && $preset['free'] === true;
        }));
        $paid_count = max(count($presets) - $free_count, 0);
        ?>
        <div class="wrap syntekpro-settings-wrapper syntekpro-presets-page">
            <?php $this->render_page_header(__('Animation Presets', 'syntekpro-animations'), __('Browse 50+ ready-to-use animation effects', 'syntekpro-animations')); ?>

            <div class="syntekpro-settings-section" style="padding:14px 12px;">
                <!-- Filter Controls -->
                    <div style="display:flex;align-items:flex-end;gap:12px;margin-bottom:18px;padding:12px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;">
                    <div style="flex:1;">
                        <label for="preset-category-filter" style="font-weight:600;display:block;margin-bottom:8px;color:#0f172a;">
                            <?php _e('Filter by Category', 'syntekpro-animations'); ?>
                        </label>
                            <select id="preset-category-filter" style="width:100%;max-width:260px;padding:9px;border:1px solid #d0d7de;border-radius:8px;font-size:13px;cursor:pointer;background:#fff;">
                            <option value=""><?php _e('All Categories', 'syntekpro-animations'); ?></option>
                            <?php foreach ($categories as $cat_key => $cat_name) : 
                                $cat_presets = Syntekpro_Animation_Presets::get_by_category($cat_key);
                                if (empty($cat_presets)) continue;
                                $count = count($cat_presets);
                            ?>
                                <option value="<?php echo esc_attr($cat_key); ?>" <?php selected($selected_category, $cat_key); ?>>
                                    <?php echo esc_html($cat_name); ?> (<?php echo $count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                            <button type="button" id="copy-all-btn" class="button button-secondary" style="padding:8px 14px;">
                            <?php _e('Copy All', 'syntekpro-animations'); ?>
                        </button>
                    </div>
                        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
                            <button type="button" id="presets-export-json" class="button" style="margin:0;">
                            <?php _e('Export Presets JSON', 'syntekpro-animations'); ?>
                        </button>
                        <button type="button" id="presets-import-json" class="button">
                            <?php _e('Import Presets JSON', 'syntekpro-animations'); ?>
                        </button>
                        <input type="file" id="presets-import-file" accept="application/json" style="display:none;" />
                        <a href="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'presets/json/hero-card-cta.sample.json'); ?>" class="button button-secondary" target="_blank" style="margin-top:2px;">
                            <?php _e('Download Sample', 'syntekpro-animations'); ?>
                        </a>
                    </div>
                </div>

                <p style="color:#475569;margin:0 0 14px 0;font-size:13px;">
                    <?php _e('Click any shortcode to copy it. Use in pages, posts, or the block editor.', 'syntekpro-animations'); ?>
                </p>

                <?php if (!$is_pro) : ?>
                    <div class="notice notice-info" style="margin:0 0 14px 0;">
                        <p>
                            <?php
                            echo esc_html(
                                sprintf(
                                    __('Free mode active: first %1$d presets are available. %2$d presets are locked and will fall back to fadeIn on frontend until you activate Animations+.', 'syntekpro-animations'),
                                    $free_limit,
                                    $paid_count
                                )
                            );
                            ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Presets Grid -->
                <div id="presets-container">
                    <?php 
                    $total_count = 0;
                    foreach ($categories as $cat_key => $cat_name) : 
                        $cat_presets = Syntekpro_Animation_Presets::get_by_category($cat_key);
                        if (empty($cat_presets)) continue;
                        
                        // Filter if category selected
                        if ($selected_category && $selected_category !== $cat_key) continue;
                        
                        $total_count += count($cat_presets);
                    ?>
                        <div class="syntekpro-presets-category" data-category="<?php echo esc_attr($cat_key); ?>">
                            <h3 style="margin:18px 0 10px 0;padding-bottom:10px;border-bottom:1px solid #e5e7eb;color:#0f172a;font-size:15px;font-weight:700;">
                                <?php echo esc_html($cat_name); ?> <span style="color:#6b7280;font-size:12px;font-weight:400;">(<?php echo count($cat_presets); ?>)</span>
                            </h3>
                            
                            <div class="syntekpro-presets-grid">
                                <?php 
                                $css_ready_types = array(
                                    'fadeIn','fadeInUp','fadeInDown','fadeInLeft','fadeInRight',
                                    'slideLeft','slideRight','slideUp','slideDown',
                                    'zoomIn','zoomInUp','zoomInDown','zoomInLeft','zoomInRight',
                                    'scaleUp','scaleDown','scaleX','scaleY',
                                    'rotateIn','pulse','revealLeft','revealRight','revealUp','revealDown'
                                );
                                foreach ($cat_presets as $key => $preset) : 
                                    // Subtle, unified background tint to keep the presets grid calm.
                                    $gradient_colors = array_fill_keys(array_keys($categories), 'linear-gradient(135deg, #f7f9fb 0%, #eef2f7 100%)');
                                    $gradient = $gradient_colors[$cat_key] ?? 'linear-gradient(135deg, #f7f9fb 0%, #eef2f7 100%)';
                                    $css_ready = in_array($key, $css_ready_types, true);
                                    $locked_in_free_mode = (!$is_pro && empty($preset['free']));
                                ?>
                                    <div class="syntekpro-preset-card <?php echo $preset['free'] ? '' : 'pro-preset'; ?> <?php echo $locked_in_free_mode ? 'is-locked' : ''; ?>" data-shortcode="[sp_animate type=&quot;<?php echo esc_attr($key); ?>&quot;]Content[/sp_animate]" data-preset-key="<?php echo esc_attr($key); ?>" data-preset-category="<?php echo esc_attr($cat_key); ?>">
                                        <div class="preset-preview-box">
                                            <div class="preset-preview-element" data-animation-trigger>
                                                <img src="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Animation%20Favicon%20Colored%20.png'); ?>" alt="<?php esc_attr_e('Syntekpro logo', 'syntekpro-animations'); ?>" style="max-height:40px;width:auto;">
                                            </div>
                                        </div>
                                        <div class="preset-info">
                                            <div class="preset-title-row">
                                                <span class="preset-name"><?php echo esc_html($preset['name']); ?></span>
                                                <span class="preset-pill <?php echo $preset['free'] ? 'free' : 'pro'; ?>"><?php echo $preset['free'] ? __('Free', 'syntekpro-animations') : __('Pro', 'syntekpro-animations'); ?></span>
                                            </div>
                                            <?php if (!$is_pro && empty($preset['free'])) : ?>
                                                <div style="margin:4px 0 0 0;font-size:12px;color:#9a3412;font-weight:600;">
                                                    <?php _e('Locked in free mode. Frontend falls back to fadeIn.', 'syntekpro-animations'); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="preset-code-row">
                                                <code>[sp_animate type="<?php echo esc_attr($key); ?>"]</code>
                                                <button
                                                    type="button"
                                                    class="copy-preset-btn <?php echo $locked_in_free_mode ? 'is-disabled' : ''; ?>"
                                                    data-preset="<?php echo esc_attr($key); ?>"
                                                    data-locked="<?php echo $locked_in_free_mode ? '1' : '0'; ?>"
                                                    data-tooltip="<?php echo esc_attr($locked_in_free_mode ? __('Upgrade to Animations+ to unlock this preset', 'syntekpro-animations') : __('Copy', 'syntekpro-animations')); ?>"
                                                    aria-disabled="<?php echo $locked_in_free_mode ? 'true' : 'false'; ?>"
                                                    title="<?php echo esc_attr($locked_in_free_mode ? __('Locked preset - requires Animations+', 'syntekpro-animations') : __('Copy shortcode', 'syntekpro-animations')); ?>"
                                                >
                                                    📋
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_count === 0) : ?>
                    <div style="padding: 40px 20px; text-align: center; background: #f9f9f9; border-radius: 8px;">
                        <p style="font-size: 16px; color: #666;">
                            <?php _e('No animations found in this category.', 'syntekpro-animations'); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>

        <style>
            .syntekpro-presets-page .syntekpro-settings-section {
                background:#fff;
                border-radius:10px;
                border:1px solid #e5e7eb;
                box-shadow:none;
            }

            #preset-category-filter {
                min-width:200px;
                box-shadow:none;
                transition:border-color 0.2s ease;
            }

            #preset-category-filter:focus {
                border-color:#0f172a !important;
                outline:none;
                box-shadow:0 0 0 2px rgba(15,23,42,0.08);
            }

            .syntekpro-presets-grid {
                display:grid;
                grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
                gap:12px;
                margin-bottom:12px;
            }

            .syntekpro-preset-card {
                background:#fff;
                border:1px solid #e5e7eb;
                border-radius:12px;
                padding:0;
                transition:transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
                cursor:pointer;
                overflow:hidden;
                display:flex;
                flex-direction:column;
            }

            .syntekpro-preset-card:hover {
                border-color:#cbd5e1;
                box-shadow:0 10px 22px rgba(15,23,42,0.07);
                transform:translateY(-2px);
            }

            .preset-preview-box {
                background:#f6f8fb;
                height:110px;
                display:flex;
                align-items:center;
                justify-content:center;
                padding:14px 12px;
                border-bottom:1px solid #e5e7eb;
            }

            .preset-preview-element {
                width:80px;
                height:64px;
                background:#fff;
                border:1px solid #d9dde5;
                border-radius:12px;
                display:flex;
                align-items:center;
                justify-content:center;
                box-shadow:0 6px 16px rgba(15,23,42,0.08);
            }

            .preset-info {
                padding:10px 12px 12px;
                display:flex;
                flex-direction:column;
                gap:8px;
            }

            .preset-title-row {
                display:flex;
                align-items:center;
                gap:8px;
            }

            .preset-name {
                font-weight:700;
                font-size:14px;
                color:#0f172a;
                flex:1;
            }

            .preset-pill {
                padding:4px 10px;
                border-radius:999px;
                font-size:11px;
                font-weight:700;
                border:1px solid #e2e8f0;
                background:#f8fafc;
                color:#0f172a;
                text-transform:uppercase;
                letter-spacing:0.3px;
            }

            .preset-pill.pro { color:#b91c1c; border-color:#fecdd3; background:#fff1f2; }
            .preset-pill.free { color:#166534; border-color:#bbf7d0; background:#ecfdf3; }

            .preset-code-row {
                display:flex;
                align-items:center;
                gap:10px;
            }

            .preset-code-row code {
                flex:1;
                background:#f8fafc;
                border:1px solid #e5e7eb;
                border-radius:8px;
                padding:7px 9px;
                font-size:12px;
                color:#0f172a;
                font-weight:600;
                word-break:break-all;
                font-family:'Monaco','Menlo','Ubuntu Mono',monospace;
            }

            .copy-preset-btn {
                border:1px solid #d9dde5;
                background:#f8fafc;
                color:#0f172a;
                padding:6px 8px;
                border-radius:6px;
                font-weight:700;
                font-size:14px;
                cursor:pointer;
                transition:all 0.12s ease;
                width:32px;
                height:32px;
                display:flex;
                align-items:center;
                justify-content:center;
                position:relative;
            }

            .copy-preset-btn.is-disabled {
                background:#f3f4f6;
                border-color:#e5e7eb;
                color:#94a3b8;
                cursor:not-allowed;
            }

            .syntekpro-preset-card.is-locked .preset-code-row code {
                background:#f8fafc;
                color:#64748b;
                border-style:dashed;
            }

            .copy-preset-btn::before {
                content: attr(data-tooltip);
                position: absolute;
                background: #0f172a;
                color: #fff;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.12s ease;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                margin-bottom: 6px;
                z-index: 1000;
            }

            .copy-preset-btn::after {
                content: '';
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 4px solid transparent;
                border-top-color: #0f172a;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.12s ease;
                margin-bottom: -2px;
            }

            .copy-preset-btn:hover {
                border-color:#cbd5e1;
                background:#e1e6ef;
                transform:scale(1.08);
            }

            .copy-preset-btn.is-disabled:hover {
                border-color:#e5e7eb;
                background:#f3f4f6;
                transform:none;
            }

            .copy-preset-btn:hover::before,
            .copy-preset-btn:hover::after {
                opacity: 1;
            }

            .copy-preset-btn.show-upgrade-tip::before,
            .copy-preset-btn.show-upgrade-tip::after {
                opacity: 1;
            }

            .copy-preset-btn:active {
                transform:scale(0.95);
            }

            .copy-preset-btn.copied {
                background:#d7e8dc;
                border-color:#166534;
            }

            .copy-preset-btn.copied::before {
                content: '✓ Copied!';
                opacity: 1;
            }

            /* FORCE HIDE ALL PREVIEW AREAS - aggressive override */
            .syntekpro-preset-card .preview-area {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }

            .syntekpro-preset-card .preview-btn {
                display: none !important;
                visibility: hidden !important;
            }

            .syntekpro-preset-card > div:has(.preview-area),
            .syntekpro-preset-card > div:has(.preview-btn) {
                display: none !important;
            }

            .syntekpro-presets-category {
                animation: fadeInUp 0.6s ease-out;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .syntekpro-presets-category h3 {
                background: #f6f8fb;
                color: #0f172a;
                padding: 14px 16px;
                margin: 30px 0 20px 0 !important;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border: 1px solid #e5e7eb;
                box-shadow: none;
            }

            .category-count {
                background: #e6ebf3;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 600;
                color: #0f172a;
                backdrop-filter: none;
            }

            #copy-all-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
                border: none !important;
                padding: 10px 20px !important;
                font-weight: 700 !important;
                border-radius: 8px !important;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
                transition: all 0.3s ease !important;
                cursor: pointer !important;
            }

            #copy-all-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4) !important;
            }

            .preset-filter-container {
                background: #f6f8fb;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 30px;
                box-shadow: none;
            }

            .preset-filter-label {
                font-weight: 700;
                color: #0f172a;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .preset-intro-text {
                background: #f2f6f9;
                border-left: 4px solid #94a3b8;
                padding: 12px 16px;
                border-radius: 6px;
                color: #0f172a;
                font-weight: 500;
                margin-bottom: 24px;
                font-size: 13px;
            }
        </style>

        <script>
            (function() {
                // Animation presets configuration (must match class-animation-presets.php)
                const animationPresets = {
                    fadeIn: { from: { opacity: 0 }, to: { opacity: 1 } },
                    fadeOut: { from: { opacity: 1 }, to: { opacity: 0 } },
                    fadeInUp: { from: { opacity: 0, y: 50 }, to: { opacity: 1, y: 0 } },
                    fadeInDown: { from: { opacity: 0, y: -50 }, to: { opacity: 1, y: 0 } },
                    fadeInLeft: { from: { opacity: 0, x: -50 }, to: { opacity: 1, x: 0 } },
                    fadeInRight: { from: { opacity: 0, x: 50 }, to: { opacity: 1, x: 0 } },
                    slideLeft: { from: { x: 100, opacity: 0 }, to: { x: 0, opacity: 1 } },
                    slideRight: { from: { x: -100, opacity: 0 }, to: { x: 0, opacity: 1 } },
                    slideUp: { from: { y: 100, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    slideDown: { from: { y: -100, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    zoomIn: { from: { scale: 0.5, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    zoomOut: { from: { scale: 1.5, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    zoomInUp: { from: { scale: 0.5, y: 50, opacity: 0 }, to: { scale: 1, y: 0, opacity: 1 } },
                    zoomInDown: { from: { scale: 0.5, y: -50, opacity: 0 }, to: { scale: 1, y: 0, opacity: 1 } },
                    zoomInLeft: { from: { scale: 0.5, x: -50, opacity: 0 }, to: { scale: 1, x: 0, opacity: 1 } },
                    zoomInRight: { from: { scale: 0.5, x: 50, opacity: 0 }, to: { scale: 1, x: 0, opacity: 1 } },
                    scaleUp: { from: { scale: 0.8, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    scaleDown: { from: { scale: 1.2 }, to: { scale: 1 } },
                    scaleX: { from: { scaleX: 0, opacity: 0 }, to: { scaleX: 1, opacity: 1 } },
                    scaleY: { from: { scaleY: 0, opacity: 0 }, to: { scaleY: 1, opacity: 1 } },
                    rotateIn: { from: { rotate: -180, opacity: 0 }, to: { rotate: 0, opacity: 1 } },
                    rotateRight: { from: { rotate: -360 }, to: { rotate: 0 } },
                    revealLeft: { from: { xPercent: -100 }, to: { xPercent: 0 } },
                    revealRight: { from: { xPercent: 100 }, to: { xPercent: 0 } },
                    revealUp: { from: { yPercent: 100 }, to: { yPercent: 0 } },
                    revealDown: { from: { yPercent: -100 }, to: { yPercent: 0 } },
                    waveIn: { from: { y: 50, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    waveHorizontal: { from: { x: -50, opacity: 0 }, to: { x: 50, opacity: 1 } },
                    waveVertical: { from: { y: -50, opacity: 0 }, to: { y: 50, opacity: 1 } },
                    ripple: { from: { scale: 0.8, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    swingIn: { from: { rotate: -15, opacity: 0 }, to: { rotate: 0, opacity: 1 } },
                    pendulum: { from: { rotate: -45 }, to: { rotate: 0 } },
                    heartBeat: { from: { scale: 1 }, to: { scale: 1.2 } },
                    pulse: { from: { opacity: 1 }, to: { opacity: 0.7 } },
                    tada: { from: { rotate: -10, scale: 0.9 }, to: { rotate: 10, scale: 1 } },
                    bounce: { from: { y: 0, opacity: 0 }, to: { y: 20, opacity: 1 } },
                    bounceIn: { from: { scale: 0, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    bounceInUp: { from: { y: 100, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    bounceDown: { from: { y: -50, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    jello: { from: { skewX: -12.5, skewY: -12.5, opacity: 0 }, to: { skewX: 0, skewY: 0, opacity: 1 } },
                    elasticIn: { from: { scale: 0 }, to: { scale: 1 } },
                    elasticOut: { from: { scale: 1.5, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    blurIn: { from: { opacity: 0, filter: 'blur(10px)' }, to: { opacity: 1, filter: 'blur(0px)' } },
                    flip: { from: { rotateY: -360, opacity: 0 }, to: { rotateY: 0, opacity: 1 } },
                    flip3d: { from: { rotateX: -360, opacity: 0 }, to: { rotateX: 0, opacity: 1 } },
                    flipInX: { from: { rotateX: -90, opacity: 0 }, to: { rotateX: 0, opacity: 1 } },
                    flipInY: { from: { rotateY: -90, opacity: 0 }, to: { rotateY: 0, opacity: 1 } },
                    cardFlip: { from: { rotateY: 180, opacity: 0 }, to: { rotateY: 0, opacity: 1 } },
                    perspective3D: { from: { rotationX: -90, opacity: 0 }, to: { rotationX: 0, opacity: 1 } },
                    glitchIn: { from: { x: -20, opacity: 0 }, to: { x: 0, opacity: 1 } },
                    digitalReveal: { from: { scale: 0.95, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    peelLeft: { from: { rotateY: 180, x: -100 }, to: { rotateY: 0, x: 0 } },
                    peelRight: { from: { rotateY: -180, x: 100 }, to: { rotateY: 0, x: 0 } },
                    unfoldHorizontal: { from: { scaleX: 0 }, to: { scaleX: 1 } },
                    unfoldVertical: { from: { scaleY: 0 }, to: { scaleY: 1 } },
                    typewriter: { from: { width: 0, opacity: 0 }, to: { width: '100%', opacity: 1 } },
                    textReveal: { from: { yPercent: 100, opacity: 0 }, to: { yPercent: 0, opacity: 1 } },
                    blur: { from: { filter: 'blur(10px)', opacity: 0 }, to: { filter: 'blur(0px)', opacity: 1 } },
                };

                // Initialize animations for preview elements
                function initPreviewAnimations() {
                    if (typeof gsap === 'undefined') {
                        console.warn('GSAP not loaded yet');
                        setTimeout(initPreviewAnimations, 100);
                        return;
                    }

                    document.querySelectorAll('[data-animation-trigger]').forEach(element => {
                        const card = element.closest('.syntekpro-preset-card');
                        const presetKey = card.getAttribute('data-preset-key');
                        const preset = animationPresets[presetKey];

                        if (!preset) return;

                        // Create a timeline for continuous looping preview
                        const timeline = gsap.timeline({ repeat: -1, repeatDelay: 0.5 });

                        // Animate the preview element
                        timeline.fromTo(
                            element,
                            {
                                opacity: preset.from.opacity ?? 1,
                                x: preset.from.x ?? 0,
                                y: preset.from.y ?? 0,
                                scale: preset.from.scale ?? 1,
                                rotate: preset.from.rotate ?? 0,
                                scaleX: preset.from.scaleX ?? 1,
                                scaleY: preset.from.scaleY ?? 1,
                                skewX: preset.from.skewX ?? 0,
                                skewY: preset.from.skewY ?? 0,
                                rotateX: preset.from.rotateX ?? 0,
                                rotateY: preset.from.rotateY ?? 0,
                                xPercent: preset.from.xPercent ?? 0,
                                yPercent: preset.from.yPercent ?? 0,
                                filter: preset.from.filter ?? 'blur(0px)',
                                width: preset.from.width ?? '80px',
                            },
                            {
                                opacity: preset.to.opacity ?? 1,
                                x: preset.to.x ?? 0,
                                y: preset.to.y ?? 0,
                                scale: preset.to.scale ?? 1,
                                rotate: preset.to.rotate ?? 0,
                                scaleX: preset.to.scaleX ?? 1,
                                scaleY: preset.to.scaleY ?? 1,
                                skewX: preset.to.skewX ?? 0,
                                skewY: preset.to.skewY ?? 0,
                                rotateX: preset.to.rotateX ?? 0,
                                rotateY: preset.to.rotateY ?? 0,
                                xPercent: preset.to.xPercent ?? 0,
                                yPercent: preset.to.yPercent ?? 0,
                                filter: preset.to.filter ?? 'blur(0px)',
                                width: preset.to.width ?? '80px',
                                duration: 0.8,
                                ease: 'power2.inOut'
                            }
                        );
                    });
                }

                // Wait for DOM ready and GSAP loaded
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initPreviewAnimations);
                } else {
                    initPreviewAnimations();
                }

                // Category filter
                const categoryFilter = document.getElementById('preset-category-filter');
                const presetsContainer = document.getElementById('presets-container');
                
                if (categoryFilter) {
                    categoryFilter.addEventListener('change', function(e) {
                        const selectedCat = this.value;
                        const url = new URL(window.location);
                        
                        if (selectedCat) {
                            url.searchParams.set('category', selectedCat);
                        } else {
                            url.searchParams.delete('category');
                        }
                        
                        window.location.href = url.toString();
                    });
                }

                // Copy preset buttons
                document.querySelectorAll('.copy-preset-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const isLocked = this.getAttribute('data-locked') === '1';

                        if (isLocked) {
                            this.classList.add('show-upgrade-tip');
                            setTimeout(() => {
                                this.classList.remove('show-upgrade-tip');
                            }, 2200);
                            return;
                        }

                        const preset = this.getAttribute('data-preset');
                        
                        // Create a temporary textarea for copying
                        const tempTextarea = document.createElement('textarea');
                        tempTextarea.value = '[sp_animate type="' + preset + '"]Your content[/sp_animate]';
                        document.body.appendChild(tempTextarea);
                        tempTextarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempTextarea);
                        
                        // Show feedback
                        this.classList.add('copied');
                        
                        setTimeout(() => {
                            this.classList.remove('copied');
                        }, 2000);
                    });
                });

                // Copy all button
                const copyAllBtn = document.getElementById('copy-all-btn');
                if (copyAllBtn) {
                    copyAllBtn.addEventListener('click', function() {
                        const allPresets = [];
                        let lockedCount = 0;
                        document.querySelectorAll('.syntekpro-preset-card').forEach(card => {
                            const btn = card.querySelector('.copy-preset-btn');
                            if (!btn) return;
                            if (btn.getAttribute('data-locked') === '1') {
                                lockedCount++;
                                return;
                            }
                            const preset = btn.getAttribute('data-preset');
                            allPresets.push('[sp_animate type="' + preset + '"]Your content[/sp_animate]');
                        });

                        if (allPresets.length === 0) {
                            const originalText = this.textContent;
                            this.textContent = 'Upgrade to copy paid presets';
                            setTimeout(() => {
                                this.textContent = originalText;
                            }, 2200);
                            return;
                        }
                        
                        const allCode = allPresets.join('\\n\\n');
                        const tempTextarea = document.createElement('textarea');
                        tempTextarea.value = allCode;
                        document.body.appendChild(tempTextarea);
                        tempTextarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempTextarea);
                        
                        const originalText = this.textContent;
                        this.textContent = lockedCount > 0 ? '✓ Copied free presets only' : '✓ Copied All!';
                        setTimeout(() => {
                            this.textContent = originalText;
                        }, 2000);
                    });
                }
            })();
        </script>

        <!-- Force remove any preview areas that managed to sneak through -->
        <script>
            // Final cleanup - run after everything else
            (function() {
                function cleanupPreviewAreas() {
                    // Remove all preview areas
                    document.querySelectorAll('.preview-area, [style*="fce4ec"], [style*="f3e5f5"]').forEach(el => {
                        if (el.closest('.syntekpro-preset-card')) {
                            el.remove();
                        }
                    });
                    
                    // Hide via display:none as fallback
                    document.querySelectorAll('.syntekpro-preset-card .preview-area').forEach(el => {
                        el.style.display = 'none !important';
                        el.parentNode.removeChild(el);
                    });
                }
                
                // Run immediately
                cleanupPreviewAreas();
                
                // Run on DOMContentLoaded
                document.addEventListener('DOMContentLoaded', cleanupPreviewAreas);
                
                // Run on complete
                window.addEventListener('load', cleanupPreviewAreas);
                
                // Run after a short delay to catch any late injections
                setTimeout(cleanupPreviewAreas, 500);
                setTimeout(cleanupPreviewAreas, 1000);
            })();
        </script>
        <?php
    }
    
    /**
     * Help Center page
     */
    public function help_page() {
        wp_safe_redirect(admin_url('admin.php?page=syntekpro-animations-about&tab=help'));
        exit;

        $theme = wp_get_theme();
        $help_cards = array(
            array(
                'title' => __('User Documentation', 'syntekpro-animations'),
                'icon' => '🙋',
                'summary' => __('Guides for editors, marketers, and site owners to ship animations fast.', 'syntekpro-animations'),
                'links' => array(
                    __('Quick start and presets', 'syntekpro-animations'),
                    __('Visual builder walkthrough', 'syntekpro-animations'),
                    __('How to add animations in pages', 'syntekpro-animations'),
                    __('Troubleshooting common issues', 'syntekpro-animations')
                ),
                'cta' => array(
                    'label' => __('Open User Guide', 'syntekpro-animations'),
                    'url' => 'https://syntekpro.com/animations-docs'
                )
            ),
            array(
                'title' => __('Developer Documentation', 'syntekpro-animations'),
                'icon' => '👩‍💻',
                'summary' => __('APIs, hooks, and code samples for extending Syntekpro Animations.', 'syntekpro-animations'),
                'links' => array(
                    __('PHP hooks and filters', 'syntekpro-animations'),
                    __('JavaScript API and events', 'syntekpro-animations'),
                    __('Registering custom presets', 'syntekpro-animations'),
                    __('Enqueue rules and performance', 'syntekpro-animations')
                ),
                'cta' => array(
                    'label' => __('Open Developer Docs', 'syntekpro-animations'),
                    'url' => 'https://syntekpro.com/animations-dev-docs'
                )
            ),
            array(
                'title' => __('Designer Documentation', 'syntekpro-animations'),
                'icon' => '🎨',
                'summary' => __('Brand-safe animation recipes, timing curves, and layout guidance.', 'syntekpro-animations'),
                'links' => array(
                    __('Recommended easing and timing', 'syntekpro-animations'),
                    __('Layered animation patterns', 'syntekpro-animations'),
                    __('Accessibility-friendly motion', 'syntekpro-animations'),
                    __('Design tokens and brand colors', 'syntekpro-animations')
                ),
                'cta' => array(
                    'label' => __('Open Designer Docs', 'syntekpro-animations'),
                    'url' => 'https://syntekpro.com/animations-design-docs'
                )
            )
        );
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Help Center', 'syntekpro-animations'), __('Everything you need to ship, extend, and design animations confidently.', 'syntekpro-animations'), sprintf(__('Theme: %s • Plugin %s', 'syntekpro-animations'), esc_html($theme->get('Name')), esc_html(SYNTEKPRO_ANIM_VERSION))); ?>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;align-items:stretch;">
                <?php foreach ($help_cards as $card) : ?>
                    <div style="background:#fff;border:1px solid #ececec;border-radius:10px;padding:20px;box-shadow:0 12px 35px rgba(12,16,31,0.06);display:flex;flex-direction:column;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                            <span style="font-size:24px;line-height:1;"><?php echo esc_html($card['icon']); ?></span>
                            <h3 style="margin:0;font-size:1.2em;color:#111;"><?php echo esc_html($card['title']); ?></h3>
                        </div>
                        <p style="margin:4px 0 12px;color:#444;"><?php echo esc_html($card['summary']); ?></p>
                        <ul style="margin:0 0 14px 18px;list-style:disc;color:#333;line-height:1.6;">
                            <?php foreach ($card['links'] as $link) : ?>
                                <li><?php echo esc_html($link); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div style="margin-top:auto;">
                            <a class="button button-primary" style="background:#e53935;border-color:#e53935;box-shadow:0 8px 18px rgba(229,57,53,0.25);" href="<?php echo esc_url($card['cta']['url']); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html($card['cta']['label']); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
                <div style="background:#0f172a;color:#fff;border-radius:10px;padding:18px;">
                    <h4 style="margin-top:0;">🧭 <?php _e('Need hands-on help?', 'syntekpro-animations'); ?></h4>
                    <p style="margin-bottom:10px;opacity:0.9;"><?php _e('Chat with support, share screenshots, or request guided setup.', 'syntekpro-animations'); ?></p>
                    <a class="button" style="background:#fff;color:#0f172a;border-color:#fff;" href="https://syntekpro.com/support" target="_blank" rel="noopener noreferrer"><?php _e('Open Support', 'syntekpro-animations'); ?></a>
                </div>
                <div style="background:#f8fafc;border:1px dashed #d7e3f4;border-radius:10px;padding:18px;">
                    <h4 style="margin-top:0;">📬 <?php _e('Release notes and updates', 'syntekpro-animations'); ?></h4>
                    <p style="margin-bottom:10px;color:#334155;"><?php _e('Stay in sync with new animations, fixes, and platform changes.', 'syntekpro-animations'); ?></p>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a class="button button-secondary" href="https://syntekpro.com/animations/changelog" target="_blank" rel="noopener noreferrer"><?php _e('View Changelog', 'syntekpro-animations'); ?></a>
                        <a class="button" style="background:#e53935;border-color:#e53935;color:#fff;" href="https://syntekpro.com/animations-newsletter" target="_blank" rel="noopener noreferrer"><?php _e('Subscribe', 'syntekpro-animations'); ?></a>
                    </div>
                </div>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>
        <?php
    }

    /**
     * Documentation page
     */
    public function documentation_page() {
        wp_safe_redirect(admin_url('admin.php?page=syntekpro-animations-about&tab=docs'));
        exit;

        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Documentation', 'syntekpro-animations'), __('Learn how to use Syntekpro Animations in your WordPress site', 'syntekpro-animations'), sprintf(__('Version %s', 'syntekpro-animations'), SYNTEKPRO_ANIM_VERSION)); ?>

            <!-- Getting Started -->
            <div class="syntekpro-docs-section" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;">
                <h3 style="color:#0f172a;font-size:1.15em;margin:0 0 8px 0;">🚀 <?php _e('Quick Start - Basic Usage', 'syntekpro-animations'); ?></h3>
                <p style="color:#334155;margin:0 0 10px 0;"><?php _e('Add animations to your content using simple shortcodes:', 'syntekpro-animations'); ?></p>
                <pre style="background:#f8fafc;border:1px solid #e5e7eb;padding:14px;border-radius:8px;color:#0f172a;margin:0;">[sp_animate type="fadeIn" duration="1" delay="0"]Your content here[/sp_animate]</pre>
            </div>

            <!-- Shortcode Parameters -->
            <div class="syntekpro-docs-section" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;">
                <h3 style="color:#0f172a;font-size:1.15em;margin:0 0 8px 0;">⚙️ <?php _e('Shortcode Parameters', 'syntekpro-animations'); ?></h3>
                <p style="color:#334155;margin:0 0 10px 0;"><?php _e('Customize your animations with these parameters:', 'syntekpro-animations'); ?></p>
                <table class="wp-list-table widefat" style="margin:0;">
                    <thead>
                        <tr>
                            <th style="width:15%;"><strong><?php _e('Parameter', 'syntekpro-animations'); ?></strong></th>
                            <th style="width:35%;"><strong><?php _e('Description', 'syntekpro-animations'); ?></strong></th>
                            <th style="width:20%;"><strong><?php _e('Default', 'syntekpro-animations'); ?></strong></th>
                            <th style="width:30%;"><strong><?php _e('Example', 'syntekpro-animations'); ?></strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>type</strong></td>
                            <td><?php _e('Animation effect to use', 'syntekpro-animations'); ?></td>
                            <td style="color:#0f172a;">fadeIn</td>
                            <td><code>type="slideLeft"</code></td>
                        </tr>
                        <tr>
                            <td><strong>duration</strong></td>
                            <td><?php _e('Animation length in seconds', 'syntekpro-animations'); ?></td>
                            <td style="color:#0f172a;">1</td>
                            <td><code>duration="2"</code></td>
                        </tr>
                        <tr>
                            <td><strong>delay</strong></td>
                            <td><?php _e('Wait before animation starts', 'syntekpro-animations'); ?></td>
                            <td style="color:#0f172a;">0</td>
                            <td><code>delay="0.5"</code></td>
                        </tr>
                        <tr>
                            <td><strong>trigger</strong></td>
                            <td><?php _e('When animation starts', 'syntekpro-animations'); ?></td>
                            <td style="color:#0f172a;">scroll</td>
                            <td><code>trigger="load"</code></td>
                        </tr>
                        <tr>
                            <td><strong>ease</strong></td>
                            <td><?php _e('Animation easing function', 'syntekpro-animations'); ?></td>
                            <td style="color:#0f172a;">power2.out</td>
                            <td><code>ease="bounce.out"</code></td>
                        </tr>
                        <tr>
                            <td><strong>stagger</strong></td>
                            <td><?php _e('Delay between child elements', 'syntekpro-animations'); ?></td>
                            <td style="color:#0f172a;">0</td>
                            <td><code>stagger="0.1"</code></td>
                        </tr>
                        <tr>
                            <td><strong>repeat</strong></td>
                            <td><?php _e('Number of times to repeat', 'syntekpro-animations'); ?></td>
                            <td style="color:#0f172a;">0</td>
                            <td><code>repeat="2"</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pro Features -->
            <div class="syntekpro-docs-section" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:14px;">
                <h3 style="color:#0f172a;font-size:1.15em;margin:0;">🔒 <?php _e('Pro Features', 'syntekpro-animations'); ?></h3>
                
                <div>
                    <h4 style="color:#0f172a;margin:0 0 6px 0;"><?php _e('Text Animation (Pro)', 'syntekpro-animations'); ?></h4>
                    <p style="color:#334155;margin:0 0 8px 0;"><?php _e('Animate text character by character, word by word, or line by line:', 'syntekpro-animations'); ?></p>
                    <pre style="background:#f8fafc;border:1px solid #e5e7eb;padding:14px;border-radius:8px;color:#0f172a;margin:0;">[sp_text_animate type="chars" effect="fadeIn" duration="0.05" stagger="0.03"]Animated Text[/sp_text_animate]</pre>
                </div>

                <div>
                    <h4 style="color:#0f172a;margin:0 0 6px 0;"><?php _e('SVG Animation (Pro)', 'syntekpro-animations'); ?></h4>
                    <p style="color:#334155;margin:0 0 8px 0;"><?php _e('Draw SVG strokes or morph shapes with professional animations:', 'syntekpro-animations'); ?></p>
                    <pre style="background:#f8fafc;border:1px solid #e5e7eb;padding:14px;border-radius:8px;color:#0f172a;margin:0;">[sp_svg_animate type="draw" duration="2"]&lt;svg&gt;...&lt;/svg&gt;[/sp_svg_animate]</pre>
                </div>

                <div>
                    <h4 style="color:#0f172a;margin:0 0 6px 0;"><?php _e('Timeline Animation (Pro)', 'syntekpro-animations'); ?></h4>
                    <p style="color:#334155;margin:0 0 8px 0;"><?php _e('Create complex animation sequences with multiple elements:', 'syntekpro-animations'); ?></p>
                    <pre style="background:#f8fafc;border:1px solid #e5e7eb;padding:14px;border-radius:8px;color:#0f172a;margin:0;">[sp_timeline]
    [sp_animate type="fadeIn"]First element[/sp_animate]
    [sp_animate type="slideLeft"]Second element[/sp_animate]
    [sp_animate type="scaleUp"]Third element[/sp_animate]
[/sp_timeline]</pre>
                </div>
            </div>

            <!-- Code Examples -->
            <div class="syntekpro-docs-section" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:12px;">
                <h3 style="color:#0f172a;font-size:1.15em;margin:0;">💻 <?php _e('Developer Examples', 'syntekpro-animations'); ?></h3>
                <p style="color:#334155;margin:0;"><?php _e('Enable Developer Mode in Settings to use these custom code examples:', 'syntekpro-animations'); ?></p>
                
                <h4 style="color:#0f172a;margin:6px 0 6px 0;"><?php _e('Custom JavaScript Animation', 'syntekpro-animations'); ?></h4>
                <pre style="background:#f8fafc;border:1px solid #e5e7eb;padding:14px;border-radius:8px;color:#0f172a;margin:0;">// Animate an element when page loads using Syntekpro Engine
document.addEventListener('DOMContentLoaded', function() {
    gsap.to('.my-element', {
        x: 100,
        y: 50,
        opacity: 1,
        duration: 1,
        ease: 'power2.out'
    });
});

// Animate on scroll trigger
gsap.to('.scroll-element', {
    scrollTrigger: {
        trigger: '.scroll-element',
        start: 'top center',
        toggleActions: 'play none none none'
    },
    duration: 1,
    y: -50,
    opacity: 1
});</pre>
            </div>

            <!-- Resources -->
            <div class="syntekpro-docs-section" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;">
                <h3 style="color:#0f172a;font-size:1.15em;margin:0 0 10px 0;">📚 <?php _e('Additional Resources', 'syntekpro-animations'); ?></h3>
                <ul style="list-style-type:none;padding:0;margin:0;display:grid;gap:10px;">

                    <li style="margin:0;">
                        <a href="https://syntekpro.com/animations-docs" target="_blank" style="color:#0f172a;text-decoration:none;font-weight:600;">
                            🎓 Syntekpro Animations Full Docs
                        </a>
                        <p style="margin:4px 0;color:#475569;font-size:13px;"><?php _e('Complete guides and tutorials for using Syntekpro Animations', 'syntekpro-animations'); ?></p>
                    </li>
                    <li style="margin:0;">
                        <a href="https://syntekpro.com/support" target="_blank" style="color:#0f172a;text-decoration:none;font-weight:600;">
                            🆘 Support Center
                        </a>
                        <p style="margin:4px 0;color:#475569;font-size:13px;"><?php _e('Get help from our support team or view frequently asked questions', 'syntekpro-animations'); ?></p>
                    </li>
                </ul>
            </div>

            <!-- Upgrade CTA -->
            <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:20px;margin-top:20px;text-align:center;">
                <h3 style="color:#0f172a;margin:0 0 6px 0;">⭐ <?php _e('Unlock Pro Features', 'syntekpro-animations'); ?></h3>
                <p style="color:#334155;font-size:14px;margin:0 0 12px 0;"><?php _e('Get access to Timeline Builder, Text Effects, SVG Morphing, Draw Effects, and 50+ premium animations. This test site is already unlocked for evaluation.', 'syntekpro-animations'); ?></p>
                <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="background:#0f172a;border-color:#0f172a;padding:8px 20px;font-size:14px;" target="_blank">
                    🚀 <?php _e('Purchase Pro License', 'syntekpro-animations'); ?>
                </a>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>
        <?php
    }
    
    /**
     * Animation Builder Page
     */
    public function builder_page() {
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Animation Builder', 'syntekpro-animations'), __('Create custom animations with live preview', 'syntekpro-animations'), sprintf(__('Version %s', 'syntekpro-animations'), SYNTEKPRO_ANIM_VERSION)); ?>

            <!-- Animation Builder -->
            <div class="animation-builder" id="animation-builder">
                <h3>🎨 <?php _e('Build Your Custom Animation', 'syntekpro-animations'); ?></h3>
                <p><?php _e('Customize animation parameters and see the results in real-time.', 'syntekpro-animations'); ?></p>
                
                <div class="builder-controls">
                    <div class="builder-control">
                        <label><?php _e('Animation Type', 'syntekpro-animations'); ?></label>
                        <select id="builder-animation-type">
                            <option value="fadeIn">Fade In</option>
                            <option value="fadeInUp">Fade In Up</option>
                            <option value="fadeInDown">Fade In Down</option>
                            <option value="slideLeft">Slide Left</option>
                            <option value="slideRight">Slide Right</option>
                            <option value="scaleUp">Scale Up</option>
                            <option value="rotateIn">Rotate In</option>
                            <option value="zoomIn">Zoom In</option>
                            <option value="bounceIn">Bounce In</option>
                            <option value="pulse">Pulse</option>
                        </select>
                    </div>
                    
                    <div class="builder-control">
                        <label><?php _e('Duration (seconds)', 'syntekpro-animations'); ?></label>
                        <input type="number" id="builder-duration" value="1" step="0.1" min="0.1" max="10">
                    </div>
                    
                    <div class="builder-control">
                        <label><?php _e('Delay (seconds)', 'syntekpro-animations'); ?></label>
                        <input type="number" id="builder-delay" value="0" step="0.1" min="0" max="5">
                    </div>
                    
                    <div class="builder-control">
                        <label><?php _e('Easing Function', 'syntekpro-animations'); ?></label>
                        <select id="builder-ease">
                            <option value="none">Linear</option>
                            <option value="power1.out">Power 1</option>
                            <option value="power2.out" selected>Power 2</option>
                            <option value="power3.out">Power 3</option>
                            <option value="power4.out">Power 4</option>
                            <option value="back.out(1.7)">Back</option>
                            <option value="elastic.out(1, 0.3)">Elastic</option>
                            <option value="bounce.out">Bounce</option>
                            <option value="sine.inOut">Sine</option>
                            <option value="expo.out">Expo</option>
                            <option value="circ.out">Circ</option>
                        </select>
                    </div>
                    
                    <div class="builder-control">
                        <label><?php _e('Stagger (for multiple)', 'syntekpro-animations'); ?></label>
                        <input type="number" id="builder-stagger" value="0" step="0.05" min="0" max="1">
                    </div>
                </div>
                
                <!-- Preview Box -->
                <div id="builder-preview-box">
                    <?php _e('Your Animated Element', 'syntekpro-animations'); ?>
                </div>
                
                <!-- Actions -->
                <div class="builder-actions">
                    <button id="play-builder-animation" class="button button-primary">
                        ▶ <?php _e('Play Animation', 'syntekpro-animations'); ?>
                    </button>
                </div>
                
                <!-- Generated Code -->
                <h4 style="margin-top: 30px; color: var(--sp-tertiary);"><?php _e('Generated Shortcode', 'syntekpro-animations'); ?></h4>
                <div class="code-output">
                    <code id="generated-shortcode">[sp_animate type="fadeIn" duration="1" delay="0" ease="power2.out"]Your Content[/sp_animate]</code>
                    <button class="copy-shortcode"><?php _e('Copy', 'syntekpro-animations'); ?></button>
                </div>
            </div>
            
            <!-- Quick Tips -->
            <div class="syntekpro-settings-section">
                <h3>💡 <?php _e('Quick Tips', 'syntekpro-animations'); ?></h3>
                <ul style="line-height: 2;">
                    <li><strong><?php _e('Duration:', 'syntekpro-animations'); ?></strong> <?php _e('Controls how long the animation takes (1 second is standard)', 'syntekpro-animations'); ?></li>
                    <li><strong><?php _e('Delay:', 'syntekpro-animations'); ?></strong> <?php _e('Wait time before animation starts', 'syntekpro-animations'); ?></li>
                    <li><strong><?php _e('Easing:', 'syntekpro-animations'); ?></strong> <?php _e('Controls the animation timing curve (Power 2 is recommended)', 'syntekpro-animations'); ?></li>
                    <li><strong><?php _e('Stagger:', 'syntekpro-animations'); ?></strong> <?php _e('Delay between multiple child elements', 'syntekpro-animations'); ?></li>
                </ul>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>
        <?php
    }
    
    /**
     * Pattern Browser Page
     */
    public function patterns_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'browser';
        if ($active_tab === 'data') {
            $this->pattern_data_page();
            return;
        }

        $items = $this->get_pattern_browser_items();
        $registry = class_exists('WP_Block_Patterns_Registry') ? \WP_Block_Patterns_Registry::get_instance() : null;

        $available_count = 0;
        foreach ($items as &$item) {
            $item['available'] = $registry ? $registry->is_registered($item['slug']) : false;
            if ($item['available']) {
                $available_count++;
            }
        }
        unset($item);

        $total = count($items);
        $coming_count = max($total - $available_count, 0);
        $pattern_snippet = function($slug) {
            return '<!-- wp:pattern {"slug":"' . $slug . '"} /-->';
        };
        $first_item = reset($items);
        $first_snippet = $first_item ? $pattern_snippet($first_item['slug']) : '';
        $preview_styles = array(
            'syntekpro/hero-spotlight' => array('bg' => 'linear-gradient(135deg,#eef2ff,#f8fafc)', 'accent' => '#0f172a'),
            'syntekpro/card-stagger' => array('bg' => 'linear-gradient(135deg,#ecfdf3,#f8fafc)', 'accent' => '#0f766e'),
            'syntekpro/cta-banner' => array('bg' => 'linear-gradient(135deg,#0f172a,#0b1220)', 'accent' => '#ffffff'),
            'syntekpro/pricing-grid' => array('bg' => 'linear-gradient(135deg,#f1f5f9,#e2e8f0)', 'accent' => '#0f172a'),
            'syntekpro/faq-accordion' => array('bg' => 'linear-gradient(135deg,#fefce8,#f8fafc)', 'accent' => '#92400e'),
                'syntekpro/testimonial-stack' => array('bg' => 'linear-gradient(135deg,#ffffff,#f8fafc)', 'accent' => '#0f172a'),
                'syntekpro/hero-split' => array('bg' => 'linear-gradient(135deg,#eef2ff,#f8fafc)', 'accent' => '#0f172a'),
                'syntekpro/stats-row' => array('bg' => 'linear-gradient(135deg,#f8fafc,#e2e8f0)', 'accent' => '#0f172a'),
                'syntekpro/logo-strip' => array('bg' => 'linear-gradient(135deg,#f1f5f9,#e2e8f0)', 'accent' => '#0f172a'),
                'syntekpro/steps-walkthrough' => array('bg' => 'linear-gradient(135deg,#ecfdf3,#f8fafc)', 'accent' => '#0f766e'),
                'syntekpro/feature-checklist' => array('bg' => 'linear-gradient(135deg,#ffffff,#f8fafc)', 'accent' => '#0f172a'),
                'syntekpro/comparison-table' => array('bg' => 'linear-gradient(135deg,#f8fafc,#e2e8f0)', 'accent' => '#0f172a'),
                'syntekpro/newsletter-band' => array('bg' => 'linear-gradient(135deg,#0f172a,#0b1220)', 'accent' => '#ffffff'),
                'syntekpro/gallery-tiles' => array('bg' => 'linear-gradient(135deg,#f8fafc,#e2e8f0)', 'accent' => '#0f172a'),
                'syntekpro/testimonial-highlight' => array('bg' => 'linear-gradient(135deg,#ffffff,#f8fafc)', 'accent' => '#0f172a'),
                'syntekpro/cta-minimal' => array('bg' => 'linear-gradient(135deg,#f8fafc,#eef2ff)', 'accent' => '#0f172a'),
        );
        $preview_variants = array(
            'syntekpro/hero-spotlight' => 'hero',
            'syntekpro/card-stagger' => 'cards',
            'syntekpro/cta-banner' => 'cta',
            'syntekpro/pricing-grid' => 'pricing',
            'syntekpro/faq-accordion' => 'faq',
                'syntekpro/testimonial-stack' => 'testimonials',
                'syntekpro/hero-split' => 'hero',
                'syntekpro/stats-row' => 'cards',
                'syntekpro/logo-strip' => 'cards',
                'syntekpro/steps-walkthrough' => 'cards',
                'syntekpro/feature-checklist' => 'generic',
                'syntekpro/comparison-table' => 'generic',
                'syntekpro/newsletter-band' => 'cta',
                'syntekpro/gallery-tiles' => 'cards',
                'syntekpro/testimonial-highlight' => 'testimonials',
                'syntekpro/cta-minimal' => 'cta',
        );
        ?>
        <div class="wrap syntekpro-settings-wrapper syntekpro-patterns-page">
            <?php $this->render_page_header(__('Pattern Browser', 'syntekpro-animations'), __('Drop-ready block patterns with Syntekpro motion baked in.', 'syntekpro-animations'), sprintf(__('Registered: %d · Coming soon: %d', 'syntekpro-animations'), $available_count, $coming_count)); ?>

            <h2 class="nav-tab-wrapper" style="margin-bottom:12px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-patterns&tab=browser')); ?>" class="nav-tab nav-tab-active">🧩 <?php _e('Pattern Browser', 'syntekpro-animations'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-patterns&tab=data')); ?>" class="nav-tab">🗂️ <?php _e('Pattern Data', 'syntekpro-animations'); ?></a>
            </h2>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;align-items:flex-start;">
                <div style="padding:14px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;box-shadow:none;">
                    <h3 style="margin-top:0;color:#0f172a;">🧩 <?php _e('How to insert', 'syntekpro-animations'); ?></h3>
                    <p style="margin:0 0 10px 0;color:#334155;">
                        <?php _e('Copy the pattern snippet, open the block editor, paste into the code editor, or search the pattern name in the inserter.', 'syntekpro-animations'); ?>
                    </p>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <a class="button button-primary" style="background:#0f172a;border-color:#0f172a;" href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" target="_blank">✏️ <?php _e('Open block editor', 'syntekpro-animations'); ?></a>
                        <button type="button" class="button button-secondary" id="pattern-copy-first" data-snippet="<?php echo esc_attr($first_snippet); ?>">📋 <?php _e('Copy first pattern', 'syntekpro-animations'); ?></button>
                    </div>
                </div>
                <div style="padding:14px;border:1px solid #e5e7eb;border-radius:10px;background:#f8fafc;box-shadow:none;">
                    <h3 style="margin-top:0;color:#0f172a;">🔍 <?php _e('Search patterns', 'syntekpro-animations'); ?></h3>
                    <input type="search" id="pattern-search" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;" placeholder="<?php esc_attr_e('Search by name or slug...', 'syntekpro-animations'); ?>">
                    <p style="margin:8px 0 0 0;color:#475569;font-size:12px;">
                        <?php _e('Live filter by title, slug, or description.', 'syntekpro-animations'); ?>
                    </p>
                </div>
            </div>

            <div class="syntekpro-settings-section" style="margin-top:14px;">
                <div class="syntekpro-patterns-grid">
                    <?php foreach ($items as $item) : ?>
                        <?php 
                        $snippet = $pattern_snippet($item['slug']);
                        $preview = isset($preview_styles[$item['slug']]) ? $preview_styles[$item['slug']] : array('bg' => 'linear-gradient(135deg,#f8fafc,#e2e8f0)', 'accent' => '#0f172a');
                        $variant = isset($preview_variants[$item['slug']]) ? $preview_variants[$item['slug']] : 'generic';
                        ?>
                        <div class="pattern-card" data-pattern-title="<?php echo esc_attr($item['title']); ?>" data-pattern-slug="<?php echo esc_attr($item['slug']); ?>" data-pattern-desc="<?php echo esc_attr($item['desc']); ?>" data-pattern-available="<?php echo $item['available'] ? '1' : '0'; ?>">
                            <div class="pattern-card__header">
                                <div>
                                    <div class="pattern-card__title"><?php echo esc_html($item['title']); ?></div>
                                    <div class="pattern-card__slug">Slug: <?php echo esc_html($item['slug']); ?></div>
                                </div>
                                <span class="pattern-card__badge <?php echo $item['available'] ? 'is-live' : 'is-soon'; ?>">
                                    <?php echo $item['available'] ? __('Live', 'syntekpro-animations') : __('Coming soon', 'syntekpro-animations'); ?>
                                </span>
                            </div>
                            <div class="pattern-card__preview variant-<?php echo esc_attr($variant); ?>" style="background:<?php echo esc_attr($preview['bg']); ?>;--preview-accent:<?php echo esc_attr($preview['accent']); ?>;">
                                <?php if ($variant === 'hero') : ?>
                                    <div class="preview-hero-title"></div>
                                    <div class="preview-hero-sub"></div>
                                    <div class="preview-hero-cta"><span></span><span></span></div>
                                <?php elseif ($variant === 'cards') : ?>
                                    <div class="preview-grid">
                                        <span></span><span></span><span></span>
                                    </div>
                                <?php elseif ($variant === 'cta') : ?>
                                    <div class="preview-bar">
                                        <div class="preview-bar-title"></div>
                                        <div class="preview-bar-cta"></div>
                                    </div>
                                <?php elseif ($variant === 'pricing') : ?>
                                    <div class="preview-pricing">
                                        <div class="preview-price-card"><div class="price-tag"></div><div class="price-lines"></div></div>
                                        <div class="preview-price-card is-accent"><div class="price-tag"></div><div class="price-lines"></div></div>
                                        <div class="preview-price-card"><div class="price-tag"></div><div class="price-lines"></div></div>
                                    </div>
                                <?php elseif ($variant === 'faq') : ?>
                                    <div class="preview-faq">
                                        <div class="faq-row"></div>
                                        <div class="faq-row"></div>
                                        <div class="faq-row"></div>
                                    </div>
                                <?php elseif ($variant === 'testimonials') : ?>
                                    <div class="preview-testimonials">
                                        <div class="testimonial-row"></div>
                                        <div class="testimonial-row"></div>
                                        <div class="testimonial-row"></div>
                                    </div>
                                <?php else : ?>
                                    <div class="pattern-card__preview-top"></div>
                                    <div class="pattern-card__preview-pills"><span></span><span></span></div>
                                    <div class="pattern-card__preview-grid"><span></span><span></span><span></span></div>
                                <?php endif; ?>
                            </div>
                            <p class="pattern-card__desc"><?php echo esc_html($item['desc']); ?></p>
                            <div class="pattern-card__actions">
                                <button type="button" class="button button-primary copy-pattern-snippet" data-snippet="<?php echo esc_attr($snippet); ?>" <?php disabled(!$item['available']); ?>>📋 <?php _e('Copy pattern snippet', 'syntekpro-animations'); ?></button>
                                <button type="button" class="button button-secondary copy-pattern-slug" data-slug="<?php echo esc_attr($item['slug']); ?>">🏷️ <?php _e('Copy slug', 'syntekpro-animations'); ?></button>
                                <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>" target="_blank">➕ <?php _e('Insert in editor', 'syntekpro-animations'); ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php $this->render_page_footer(__('Patterns rely on Gutenberg. Use the code editor to paste the snippet if you prefer.', 'syntekpro-animations')); ?>
        </div>

        <style>
            .syntekpro-patterns-page .syntekpro-settings-section {
                background:#fff;
                border:1px solid #e5e7eb;
                border-radius:12px;
                box-shadow:none;
            }
            .syntekpro-patterns-grid {
                display:grid;
                grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
                gap:12px;
            }
            .pattern-card {
                border:1px solid #e5e7eb;
                border-radius:12px;
                padding:14px;
                background:#f8fafc;
                display:flex;
                flex-direction:column;
                gap:10px;
                transition:transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
            }
            .pattern-card:hover {
                border-color:#cbd5e1;
                transform:translateY(-2px);
                box-shadow:0 10px 30px rgba(15,23,42,0.08);
            }
            .pattern-card__header {
                display:flex;
                justify-content:space-between;
                gap:12px;
                align-items:flex-start;
            }
            .pattern-card__title {
                font-weight:700;
                font-size:15px;
                color:#0f172a;
            }
            .pattern-card__slug {
                font-size:12px;
                color:#475569;
            }
            .pattern-card__desc {
                margin:0;
                color:#334155;
                font-size:13px;
            }
            .pattern-card__badge {
                padding:4px 10px;
                border-radius:30px;
                font-size:12px;
                font-weight:700;
                border:1px solid transparent;
                white-space:nowrap;
            }
            .pattern-card__badge.is-live {
                background:#ecfdf3;
                color:#166534;
                border-color:#bbf7d0;
            }
            .pattern-card__badge.is-soon {
                background:#fff7ed;
                color:#9a3412;
                border-color:#fed7aa;
            }
            .pattern-card__preview {
                border:1px solid #e2e8f0;
                border-radius:10px;
                padding:12px;
                margin:4px 0 4px;
                height:120px;
                display:flex;
                flex-direction:column;
                gap:8px;
                box-shadow:inset 0 1px 0 rgba(255,255,255,0.4);
            }
            .pattern-card__preview.variant-hero {display:grid;gap:8px;}
            .pattern-card__preview.variant-hero .preview-hero-title {height:14px;border-radius:8px;background:rgba(15,23,42,0.2);}
            .pattern-card__preview.variant-hero .preview-hero-sub {height:10px;border-radius:8px;background:rgba(15,23,42,0.14);width:70%;}
            .pattern-card__preview.variant-hero .preview-hero-cta {display:flex;gap:8px;}
            .pattern-card__preview.variant-hero .preview-hero-cta span {flex:1;height:12px;border-radius:999px;background:var(--preview-accent,#0f172a);opacity:0.35;}

            .pattern-card__preview.variant-cards .preview-grid,
            .pattern-card__preview.variant-generic .preview-grid {display:grid;grid-template-columns:repeat(3,1fr);gap:8px;flex:1;}
            .pattern-card__preview.variant-cards .preview-grid span,
            .pattern-card__preview.variant-generic .preview-grid span {background:rgba(15,23,42,0.08);border-radius:10px;min-height:36px;position:relative;}
            .pattern-card__preview.variant-cards .preview-grid span::after,
            .pattern-card__preview.variant-generic .preview-grid span::after {content:'';position:absolute;inset:6px;border-radius:8px;background:var(--preview-accent,#0f172a);opacity:0.16;}

            .pattern-card__preview.variant-cta .preview-bar {display:flex;align-items:center;gap:10px;justify-content:space-between;}
            .pattern-card__preview.variant-cta .preview-bar-title {flex:1;height:12px;border-radius:8px;background:rgba(255,255,255,0.6);}
            .pattern-card__preview.variant-cta .preview-bar-cta {width:60px;height:12px;border-radius:999px;background:#ffffff;opacity:0.85;}

            .pattern-card__preview.variant-pricing .preview-pricing {display:grid;grid-template-columns:repeat(3,1fr);gap:8px;}
            .pattern-card__preview.variant-pricing .preview-price-card {background:rgba(15,23,42,0.06);border-radius:10px;padding:8px;position:relative;}
            .pattern-card__preview.variant-pricing .preview-price-card.is-accent {background:rgba(15,23,42,0.12);}
            .pattern-card__preview.variant-pricing .price-tag {height:14px;border-radius:8px;background:var(--preview-accent,#0f172a);opacity:0.22;margin-bottom:6px;}
            .pattern-card__preview.variant-pricing .price-lines {height:10px;border-radius:6px;background:rgba(15,23,42,0.12);width:70%;}

            .pattern-card__preview.variant-faq .preview-faq {display:grid;gap:6px;}
            .pattern-card__preview.variant-faq .faq-row {height:12px;border-radius:8px;background:rgba(146,64,14,0.22);}

            .pattern-card__preview.variant-testimonials .preview-testimonials {display:grid;gap:6px;}
            .pattern-card__preview.variant-testimonials .testimonial-row {height:14px;border-radius:10px;background:rgba(15,23,42,0.18);}

            .pattern-card__preview-top {
                height:12px;
                border-radius:6px;
                background:rgba(15,23,42,0.14);
            }
            .pattern-card__preview-pills {
                display:flex;
                gap:6px;
            }
            .pattern-card__preview-pills span {
                height:10px;
                width:26px;
                background:rgba(15,23,42,0.16);
                border-radius:999px;
                display:inline-block;
            }
            .pattern-card__preview-grid {
                display:grid;
                grid-template-columns:repeat(3,1fr);
                gap:8px;
                flex:1;
                align-items:start;
            }
            .pattern-card__preview-grid span {
                background:rgba(15,23,42,0.1);
                border-radius:10px;
                min-height:28px;
                position:relative;
            }
            .pattern-card__preview-grid span::after {
                content:'';
                position:absolute;
                inset:6px;
                border-radius:6px;
                background:var(--preview-accent,#0f172a);
                opacity:0.12;
            }
            .pattern-card__actions {
                display:flex;
                gap:8px;
                flex-wrap:wrap;
            }
            .pattern-card .button:disabled {
                opacity:0.5;
                cursor:not-allowed;
            }
        </style>

        <script>
            (function(){
                const cards = Array.from(document.querySelectorAll('.syntekpro-patterns-page .pattern-card'));
                const searchInput = document.getElementById('pattern-search');
                const copyFirst = document.getElementById('pattern-copy-first');
                function copyText(txt){
                    if (!txt) return;
                    navigator.clipboard.writeText(txt).then(() => {
                        if (window.wp && wp.toast && typeof wp.toast.success === 'function') {
                            wp.toast.success('<?php echo esc_js(__('Copied to clipboard', 'syntekpro-animations')); ?>');
                        }
                    }).catch(() => {
                        alert('<?php echo esc_js(__('Copied pattern data', 'syntekpro-animations')); ?>');
                    });
                }
                document.addEventListener('click', function(e){
                    const copySnippet = e.target.closest('.copy-pattern-snippet');
                    const copySlug = e.target.closest('.copy-pattern-slug');
                    if (copySnippet) {
                        copyText(copySnippet.getAttribute('data-snippet'));
                    }
                    if (copySlug) {
                        copyText(copySlug.getAttribute('data-slug'));
                    }
                });
                if (copyFirst) {
                    copyFirst.addEventListener('click', function(){
                        copyText(copyFirst.getAttribute('data-snippet'));
                    });
                }
                if (searchInput) {
                    searchInput.addEventListener('input', function(){
                        const term = this.value.toLowerCase();
                        cards.forEach(card => {
                            const match = (card.dataset.patternTitle + ' ' + card.dataset.patternSlug + ' ' + card.dataset.patternDesc).toLowerCase().includes(term);
                            card.style.display = match ? '' : 'none';
                        });
                    });
                }
            })();
        </script>
        <?php
    }

    /**
     * Pattern Data Page: manage shared data for pricing, FAQ, testimonials.
     */
    public function pattern_data_page() {
        $defaults = array(
            'pricing' => array(
                array('title' => 'Starter', 'price' => '$12', 'items' => array('Core animations', 'Scroll triggers', 'Email support')),
                array('title' => 'Pro', 'price' => '$29', 'items' => array('All animations unlocked', 'ScrollSmoother + timelines', 'Priority chat support')),
                array('title' => 'Agency', 'price' => '$59', 'items' => array('Unlimited sites', 'Team seats included', 'Dedicated success manager')),
            ),
            'faq' => array(
                array('q' => 'Will animations respect reduced motion?', 'a' => 'Yes. We detect prefers-reduced-motion and fall back to minimal motion.'),
                array('q' => 'Do these patterns work with any theme?', 'a' => 'They rely on core blocks and inherit your typography and colors.'),
                array('q' => 'Can I adjust timing after inserting?', 'a' => 'Yes—select the animation block and tweak duration, delay, or stagger.'),
            ),
            'testimonials' => array(
                array('quote' => '“We shipped a refreshed homepage in a day—animations just worked.”', 'name' => 'Amira Lopez, Product Marketing Lead'),
                array('quote' => '“The presets respect reduced motion out of the box. Huge win.”', 'name' => 'Jamal Everett, Accessibility Lead'),
                array('quote' => '“Our agency can reuse these patterns across clients and stay on-brand.”', 'name' => 'Priya Menon, Creative Director'),
            ),
            'custom_patterns' => array(
                array(
                    'slug' => 'syntekpro/custom-two-up',
                    'title' => 'Two-up feature row',
                    'description' => 'Two columns with heading and bullets.',
                    'content' => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->\n<div class="wp-block-group" style="padding-top:20px;padding-bottom:20px"><!-- wp:columns {"style":{"spacing":{"blockGap":"16px"}}} --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>Left Feature</h3><!-- /wp:heading --><!-- wp:list --><ul><li>Point one</li><li>Point two</li><li>Point three</li></ul><!-- /wp:list --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>Right Feature</h3><!-- /wp:heading --><!-- wp:list --><ul><li>Point one</li><li>Point two</li><li>Point three</li></ul><!-- /wp:list --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->'
                )
            ),
        );

        if (isset($_POST['syntekpro_pattern_data_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['syntekpro_pattern_data_nonce'])), 'syntekpro_pattern_data_save')) {
            // Form-mode arrays (preferred, no JSON editing required)
            $pricing_from_form = isset($_POST['pricing_items']) && is_array($_POST['pricing_items']) ? $_POST['pricing_items'] : array();
            $faq_from_form = isset($_POST['faq_items']) && is_array($_POST['faq_items']) ? $_POST['faq_items'] : array();
            $testimonials_from_form = isset($_POST['testimonial_items']) && is_array($_POST['testimonial_items']) ? $_POST['testimonial_items'] : array();
            $custom_from_form = isset($_POST['custom_pattern_items']) && is_array($_POST['custom_pattern_items']) ? $_POST['custom_pattern_items'] : array();

            $pricing_json = isset($_POST['syntekpro_pattern_pricing']) ? wp_unslash($_POST['syntekpro_pattern_pricing']) : '';
            $faq_json = isset($_POST['syntekpro_pattern_faq']) ? wp_unslash($_POST['syntekpro_pattern_faq']) : '';
            $testimonials_json = isset($_POST['syntekpro_pattern_testimonials']) ? wp_unslash($_POST['syntekpro_pattern_testimonials']) : '';
            $custom_patterns_json = isset($_POST['syntekpro_pattern_custom_patterns']) ? wp_unslash($_POST['syntekpro_pattern_custom_patterns']) : '';

            $pricing = array();
            if (!empty($pricing_from_form)) {
                foreach ($pricing_from_form as $plan) {
                    $title = isset($plan['title']) ? sanitize_text_field($plan['title']) : '';
                    $price = isset($plan['price']) ? sanitize_text_field($plan['price']) : '';
                    $items_raw = array();
                    if (isset($plan['items'])) {
                        $items_raw = is_array($plan['items']) ? $plan['items'] : array($plan['items']);
                    }
                    $items = array();
                    foreach ($items_raw as $items_entry) {
                        $normalized = sanitize_textarea_field($items_entry);
                        $lines = preg_split('/\r\n|\r|\n/', $normalized);
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line !== '') {
                                $items[] = $line;
                            }
                        }
                    }
                    if ($title || $price || !empty($items)) {
                        $pricing[] = array('title' => $title, 'price' => $price, 'items' => $items);
                    }
                }
            } else {
                $pricing = json_decode($pricing_json, true);
            }

            $faq = array();
            if (!empty($faq_from_form)) {
                foreach ($faq_from_form as $faq_item) {
                    $q = isset($faq_item['q']) ? sanitize_text_field($faq_item['q']) : '';
                    $a = isset($faq_item['a']) ? sanitize_text_field($faq_item['a']) : '';
                    if ($q || $a) {
                        $faq[] = array('q' => $q, 'a' => $a);
                    }
                }
            } else {
                $faq = json_decode($faq_json, true);
            }

            $testimonials = array();
            if (!empty($testimonials_from_form)) {
                foreach ($testimonials_from_form as $t_item) {
                    $quote = isset($t_item['quote']) ? sanitize_text_field($t_item['quote']) : '';
                    $name = isset($t_item['name']) ? sanitize_text_field($t_item['name']) : '';
                    if ($quote || $name) {
                        $testimonials[] = array('quote' => $quote, 'name' => $name);
                    }
                }
            } else {
                $testimonials = json_decode($testimonials_json, true);
            }

            $custom_patterns = array();
            if (!empty($custom_from_form)) {
                foreach ($custom_from_form as $c_item) {
                    $slug = isset($c_item['slug']) ? sanitize_title($c_item['slug']) : '';
                    $title = isset($c_item['title']) ? sanitize_text_field($c_item['title']) : '';
                    $desc = isset($c_item['description']) ? sanitize_text_field($c_item['description']) : '';
                    $content = isset($c_item['content']) ? wp_kses_post($c_item['content']) : '';
                    if ($slug && $title && $content) {
                        $custom_patterns[] = array(
                            'slug' => $slug,
                            'title' => $title,
                            'description' => $desc,
                            'content' => $content,
                        );
                    }
                }
            } else {
                $custom_patterns = json_decode($custom_patterns_json, true);
            }

            if (is_array($pricing)) {
                update_option('syntekpro_pattern_pricing', $pricing);
            }
            if (is_array($faq)) {
                update_option('syntekpro_pattern_faq', $faq);
            }
            if (is_array($testimonials)) {
                update_option('syntekpro_pattern_testimonials', $testimonials);
            }
            if (is_array($custom_patterns)) {
                update_option('syntekpro_pattern_custom', $custom_patterns);
            }

            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Pattern data saved.', 'syntekpro-animations') . '</p></div>';
        }

        $pricing_value = get_option('syntekpro_pattern_pricing', $defaults['pricing']);
        $faq_value = get_option('syntekpro_pattern_faq', $defaults['faq']);
        $testimonials_value = get_option('syntekpro_pattern_testimonials', $defaults['testimonials']);
        $custom_patterns_value = get_option('syntekpro_pattern_custom', $defaults['custom_patterns']);

        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Pattern Data', 'syntekpro-animations'), __('Edit shared data used by pricing, FAQ, and testimonial patterns.', 'syntekpro-animations')); ?>

            <h2 class="nav-tab-wrapper" style="margin-bottom:12px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-patterns&tab=browser')); ?>" class="nav-tab">🧩 <?php _e('Pattern Browser', 'syntekpro-animations'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-patterns&tab=data')); ?>" class="nav-tab nav-tab-active">🗂️ <?php _e('Pattern Data', 'syntekpro-animations'); ?></a>
            </h2>

            <form method="post">
                <?php wp_nonce_field('syntekpro_pattern_data_save', 'syntekpro_pattern_data_nonce'); ?>

                <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:1fr;gap:16px;">
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                        <h3 style="margin-top:0;display:flex;align-items:center;gap:8px;">💰 <?php _e('Pricing Plans', 'syntekpro-animations'); ?></h3>
                        <p class="description" style="margin-top:4px;"><?php _e('Add plans with title, price, and bullet list. No coding needed.', 'syntekpro-animations'); ?></p>
                        <div id="sp-pricing-repeater" data-repeater="pricing">
                            <?php foreach ($pricing_value as $idx => $plan) : ?>
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;">
                                        <div>
                                            <label style="font-weight:600;"><?php _e('Title', 'syntekpro-animations'); ?></label>
                                            <input type="text" name="pricing_items[<?php echo esc_attr($idx); ?>][title]" value="<?php echo esc_attr(isset($plan['title']) ? $plan['title'] : ''); ?>" class="regular-text" />
                                        </div>
                                        <div>
                                            <label style="font-weight:600;"><?php _e('Price', 'syntekpro-animations'); ?></label>
                                            <input type="text" name="pricing_items[<?php echo esc_attr($idx); ?>][price]" value="<?php echo esc_attr(isset($plan['price']) ? $plan['price'] : ''); ?>" class="regular-text" />
                                        </div>
                                    </div>
                                    <label style="font-weight:600;display:block;margin-top:8px;"><?php _e('Bullet items (one per line)', 'syntekpro-animations'); ?></label>
                                    <textarea name="pricing_items[<?php echo esc_attr($idx); ?>][items][]" rows="3" style="width:100%;" placeholder="<?php esc_attr_e('Add bullet points', 'syntekpro-animations'); ?>"><?php echo esc_textarea(implode("\n", isset($plan['items']) ? (array) $plan['items'] : array())); ?></textarea>
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="pricing">&times; <?php _e('Remove', 'syntekpro-animations'); ?></button></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p><button type="button" class="button sp-repeater-add" data-target="pricing">+ <?php _e('Add plan', 'syntekpro-animations'); ?></button></p>
                    </div>

                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                        <h3 style="margin-top:0;display:flex;align-items:center;gap:8px;">❓ <?php _e('FAQs', 'syntekpro-animations'); ?></h3>
                        <p class="description" style="margin-top:4px;"><?php _e('Add question and answer pairs.', 'syntekpro-animations'); ?></p>
                        <div id="sp-faq-repeater" data-repeater="faq">
                            <?php foreach ($faq_value as $idx => $faq_item) : ?>
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <label style="font-weight:600;"><?php _e('Question', 'syntekpro-animations'); ?></label>
                                    <input type="text" name="faq_items[<?php echo esc_attr($idx); ?>][q]" value="<?php echo esc_attr(isset($faq_item['q']) ? $faq_item['q'] : ''); ?>" class="regular-text" />
                                    <label style="font-weight:600;display:block;margin-top:6px;"><?php _e('Answer', 'syntekpro-animations'); ?></label>
                                    <textarea name="faq_items[<?php echo esc_attr($idx); ?>][a]" rows="3" style="width:100%;"><?php echo esc_textarea(isset($faq_item['a']) ? $faq_item['a'] : ''); ?></textarea>
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="faq">&times; <?php _e('Remove', 'syntekpro-animations'); ?></button></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p><button type="button" class="button sp-repeater-add" data-target="faq">+ <?php _e('Add FAQ', 'syntekpro-animations'); ?></button></p>
                    </div>

                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                        <h3 style="margin-top:0;display:flex;align-items:center;gap:8px;">💬 <?php _e('Testimonials', 'syntekpro-animations'); ?></h3>
                        <p class="description" style="margin-top:4px;"><?php _e('Add quote and name/title.', 'syntekpro-animations'); ?></p>
                        <div id="sp-testimonial-repeater" data-repeater="testimonial">
                            <?php foreach ($testimonials_value as $idx => $t_item) : ?>
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <label style="font-weight:600;"><?php _e('Quote', 'syntekpro-animations'); ?></label>
                                    <textarea name="testimonial_items[<?php echo esc_attr($idx); ?>][quote]" rows="2" style="width:100%;"><?php echo esc_textarea(isset($t_item['quote']) ? $t_item['quote'] : ''); ?></textarea>
                                    <label style="font-weight:600;display:block;margin-top:6px;"><?php _e('Name / Title', 'syntekpro-animations'); ?></label>
                                    <input type="text" name="testimonial_items[<?php echo esc_attr($idx); ?>][name]" value="<?php echo esc_attr(isset($t_item['name']) ? $t_item['name'] : ''); ?>" class="regular-text" />
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="testimonial">&times; <?php _e('Remove', 'syntekpro-animations'); ?></button></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p><button type="button" class="button sp-repeater-add" data-target="testimonial">+ <?php _e('Add testimonial', 'syntekpro-animations'); ?></button></p>
                    </div>

                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                        <h3 style="margin-top:0;display:flex;align-items:center;gap:8px;">🧩 <?php _e('Custom Patterns', 'syntekpro-animations'); ?></h3>
                        <p class="description" style="margin-top:4px;"><?php _e('Add reusable patterns with slug, title, description, and block HTML content.', 'syntekpro-animations'); ?></p>
                        <div id="sp-custom-repeater" data-repeater="custom">
                            <?php foreach ($custom_patterns_value as $idx => $c_item) : ?>
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <label style="font-weight:600;"><?php _e('Slug (unique)', 'syntekpro-animations'); ?></label>
                                    <input type="text" name="custom_pattern_items[<?php echo esc_attr($idx); ?>][slug]" value="<?php echo esc_attr(isset($c_item['slug']) ? $c_item['slug'] : ''); ?>" class="regular-text" placeholder="syntekpro/custom-hero" />
                                    <label style="font-weight:600;display:block;margin-top:6px;"><?php _e('Title', 'syntekpro-animations'); ?></label>
                                    <input type="text" name="custom_pattern_items[<?php echo esc_attr($idx); ?>][title]" value="<?php echo esc_attr(isset($c_item['title']) ? $c_item['title'] : ''); ?>" class="regular-text" />
                                    <label style="font-weight:600;display:block;margin-top:6px;"><?php _e('Description (optional)', 'syntekpro-animations'); ?></label>
                                    <input type="text" name="custom_pattern_items[<?php echo esc_attr($idx); ?>][description]" value="<?php echo esc_attr(isset($c_item['description']) ? $c_item['description'] : ''); ?>" class="regular-text" />
                                    <label style="font-weight:600;display:block;margin-top:6px;"><?php _e('Pattern content', 'syntekpro-animations'); ?></label>
                                    <textarea name="custom_pattern_items[<?php echo esc_attr($idx); ?>][content]" rows="4" style="width:100%;" placeholder="<?php esc_attr_e('Paste the block markup for this pattern', 'syntekpro-animations'); ?>"><?php echo esc_textarea(isset($c_item['content']) ? $c_item['content'] : ''); ?></textarea>
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="custom">&times; <?php _e('Remove', 'syntekpro-animations'); ?></button></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p><button type="button" class="button sp-repeater-add" data-target="custom">+ <?php _e('Add custom pattern', 'syntekpro-animations'); ?></button></p>
                    </div>

                </div>

                <p>
                    <button type="submit" class="button button-primary">💾 <?php _e('Save pattern data', 'syntekpro-animations'); ?></button>
                </p>
            </form>

            <script>
                (function(){
                    const addButtons = document.querySelectorAll('.sp-repeater-add');
                    const removeHandler = function(e){
                        e.preventDefault();
                        const card = this.closest('.sp-repeater-card');
                        if (card) { card.remove(); }
                    };

                    addButtons.forEach(btn => {
                        btn.addEventListener('click', function(){
                            const target = this.getAttribute('data-target');
                            const wrap = document.getElementById('sp-' + target + '-repeater');
                            if (!wrap) return;
                            const index = wrap.querySelectorAll('.sp-repeater-card').length;
                            let html = '';
                            if (target === 'pricing') {
                                html = `
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;">
                                        <div>
                                            <label style="font-weight:600;">Title</label>
                                            <input type="text" name="pricing_items[${index}][title]" class="regular-text" />
                                        </div>
                                        <div>
                                            <label style="font-weight:600;">Price</label>
                                            <input type="text" name="pricing_items[${index}][price]" class="regular-text" />
                                        </div>
                                    </div>
                                    <label style="font-weight:600;display:block;margin-top:8px;">Bullet items (one per line)</label>
                                    <textarea name="pricing_items[${index}][items][]" rows="3" style="width:100%;" placeholder="Add bullet points"></textarea>
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="pricing">&times; Remove</button></div>
                                </div>`;
                            }
                            if (target === 'faq') {
                                html = `
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <label style="font-weight:600;">Question</label>
                                    <input type="text" name="faq_items[${index}][q]" class="regular-text" />
                                    <label style="font-weight:600;display:block;margin-top:6px;">Answer</label>
                                    <textarea name="faq_items[${index}][a]" rows="3" style="width:100%;"></textarea>
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="faq">&times; Remove</button></div>
                                </div>`;
                            }
                            if (target === 'testimonial') {
                                html = `
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <label style="font-weight:600;">Quote</label>
                                    <textarea name="testimonial_items[${index}][quote]" rows="2" style="width:100%;"></textarea>
                                    <label style="font-weight:600;display:block;margin-top:6px;">Name / Title</label>
                                    <input type="text" name="testimonial_items[${index}][name]" class="regular-text" />
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="testimonial">&times; Remove</button></div>
                                </div>`;
                            }
                            if (target === 'custom') {
                                html = `
                                <div class="sp-repeater-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                                    <label style="font-weight:600;">Slug (unique)</label>
                                    <input type="text" name="custom_pattern_items[${index}][slug]" class="regular-text" placeholder="syntekpro/custom-hero" />
                                    <label style="font-weight:600;display:block;margin-top:6px;">Title</label>
                                    <input type="text" name="custom_pattern_items[${index}][title]" class="regular-text" />
                                    <label style="font-weight:600;display:block;margin-top:6px;">Description (optional)</label>
                                    <input type="text" name="custom_pattern_items[${index}][description]" class="regular-text" />
                                    <label style="font-weight:600;display:block;margin-top:6px;">Block HTML</label>
                                    <textarea name="custom_pattern_items[${index}][content]" rows="4" style="width:100%;" placeholder="Paste the block markup for this pattern"></textarea>
                                    <div style="text-align:right;margin-top:6px;"><button type="button" class="button button-link-delete sp-repeater-remove" data-target="custom">&times; Remove</button></div>
                                </div>`;
                            }
                            wrap.insertAdjacentHTML('beforeend', html);
                            wrap.querySelectorAll('.sp-repeater-remove').forEach(btn => btn.removeEventListener('click', removeHandler));
                            wrap.querySelectorAll('.sp-repeater-remove').forEach(btn => btn.addEventListener('click', removeHandler));
                            updateCounts();
                        });
                    });

                    document.querySelectorAll('.sp-repeater-remove').forEach(btn => {
                        btn.addEventListener('click', removeHandler);
                    });

                    if (densitySelect && root) {
                        densitySelect.addEventListener('change', function(){
                            root.classList.toggle('sp-density-compact', this.value === 'compact');
                        });
                    }

                    if (sectionJump) {
                        sectionJump.addEventListener('change', function(){
                            const targetSection = document.querySelector('details.sp-section[data-section="' + this.value + '"]');
                            if (targetSection) {
                                targetSection.open = true;
                                targetSection.scrollIntoView({behavior:'smooth', block:'start'});
                            }
                        });
                    }

                    if (expandAll) {
                        expandAll.addEventListener('click', function(){
                            document.querySelectorAll('details.sp-section').forEach(sec => sec.open = true);
                        });
                    }

                    if (collapseAll) {
                        collapseAll.addEventListener('click', function(){
                            document.querySelectorAll('details.sp-section').forEach(sec => sec.open = false);
                        });
                    }

                    updateCounts();
                })();
            </script>
            <?php $this->render_page_footer(__('New pattern inserts will use this data. Existing pages keep their current content.', 'syntekpro-animations')); ?>
        </div>
        <?php
    }

    /**
     * System Status page
     */
    public function system_status_page() {
        wp_safe_redirect(admin_url('admin.php?page=syntekpro-animations-about&tab=status'));
        exit;

        $theme = wp_get_theme();
        $memory_limit = defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : ini_get('memory_limit');
        $license_status = get_option('syntekpro_anim_license_status', '');
        $license_key = trim((string) get_option('syntekpro_anim_license_key', ''));
        $last_checked = get_option('syntekpro_anim_license_last_checked', '');
        $last_result = get_option('syntekpro_anim_license_last_result', '');
        $free_limit = (int) get_option('syntekpro_anim_free_preset_limit', 15);
        $is_pro = function_exists('syntekpro_animations') && syntekpro_animations()->is_pro_active();
        $status_rows = array(
            __('Plugin Version', 'syntekpro-animations') => SYNTEKPRO_ANIM_VERSION,
            __('WordPress Version', 'syntekpro-animations') => get_bloginfo('version'),
            __('PHP Version', 'syntekpro-animations') => PHP_VERSION,
            __('Memory Limit', 'syntekpro-animations') => $memory_limit ? $memory_limit : __('Unavailable', 'syntekpro-animations'),
            __('Server Software', 'syntekpro-animations') => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : __('Unavailable', 'syntekpro-animations'),
            __('Active Theme', 'syntekpro-animations') => $theme->get('Name') . ' ' . $theme->get('Version'),
            __('Active Plugins', 'syntekpro-animations') => count((array) get_option('active_plugins')),
            __('Site URL', 'syntekpro-animations') => home_url(),
            __('Timezone', 'syntekpro-animations') => wp_timezone_string(),
        );

        $highlights = array(
            __('Ten new Syntekpro block patterns for heroes, stats, logos, steps, and CTAs.', 'syntekpro-animations'),
            __('Pattern Data page is now form-first with no raw JSON required.', 'syntekpro-animations'),
            __('Menu icon hover adjusted to a neutral grey, and dashboard links include Patterns.', 'syntekpro-animations')
        );
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('System Status', 'syntekpro-animations'), __('Quick health report and version details for support and debugging.', 'syntekpro-animations'), sprintf(__('Current version %s', 'syntekpro-animations'), esc_html(SYNTEKPRO_ANIM_VERSION))); ?>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:14px;">
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;">
                    <h3 style="margin:0 0 10px 0;color:#0f172a;">📊 <?php _e('System information', 'syntekpro-animations'); ?></h3>
                    <table class="widefat striped" style="margin:0;">
                        <tbody>
                            <?php foreach ($status_rows as $label => $value) : ?>
                                <tr>
                                    <th scope="row" style="width:40%;font-weight:600;color:#0f172a;"><?php echo esc_html($label); ?></th>
                                    <td style="color:#334155;"><?php echo esc_html($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:18px;display:flex;flex-direction:column;gap:10px;">
                    <h3 style="margin:0;color:#0f172a;">ℹ️ <?php _e('About version 2.4.0', 'syntekpro-animations'); ?></h3>
                    <p style="margin:0;color:#334155;">
                        <?php _e('This release expands the pattern library, cleans up Pattern Data editing, and polishes the admin icon experience.', 'syntekpro-animations'); ?>
                    </p>
                    <ul style="margin:0 0 0 18px;list-style:disc;line-height:1.6;color:#334155;">
                        <?php foreach ($highlights as $note) : ?>
                            <li><?php echo esc_html($note); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a class="button" style="background:#0f172a;color:#fff;border-color:#0f172a;" href="https://syntekpro.com/animations/changelog" target="_blank" rel="noopener noreferrer"><?php _e('Full changelog', 'syntekpro-animations'); ?></a>
                        <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-help')); ?>"><?php _e('Open Help Center', 'syntekpro-animations'); ?></a>
                    </div>
                </div>
            </div>

            <div class="syntekpro-settings-section" style="margin-top:14px;">
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px;">
                    <h3 style="margin:0 0 10px 0;color:#0f172a;">➕ <?php _e('Animations+ status', 'syntekpro-animations'); ?></h3>
                    <table class="widefat striped" style="margin:0;">
                        <tbody>
                            <tr>
                                <th scope="row" style="width:40%;font-weight:600;color:#0f172a;"><?php _e('Activation', 'syntekpro-animations'); ?></th>
                                <td style="color:#334155;"><?php echo esc_html($is_pro ? __('Active', 'syntekpro-animations') : __('Free mode', 'syntekpro-animations')); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" style="font-weight:600;color:#0f172a;"><?php _e('License status', 'syntekpro-animations'); ?></th>
                                <td style="color:#334155;"><?php echo esc_html($license_status ? ucfirst($license_status) : __('Not validated', 'syntekpro-animations')); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" style="font-weight:600;color:#0f172a;"><?php _e('Key saved', 'syntekpro-animations'); ?></th>
                                <td style="color:#334155;"><?php echo esc_html($license_key !== '' ? __('Yes', 'syntekpro-animations') : __('No', 'syntekpro-animations')); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" style="font-weight:600;color:#0f172a;"><?php _e('Free preset count', 'syntekpro-animations'); ?></th>
                                <td style="color:#334155;"><?php echo esc_html((string) $free_limit); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" style="font-weight:600;color:#0f172a;"><?php _e('Last validation check', 'syntekpro-animations'); ?></th>
                                <td style="color:#334155;"><?php echo esc_html($last_checked ? $last_checked : __('Never', 'syntekpro-animations')); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" style="font-weight:600;color:#0f172a;"><?php _e('Last validation result', 'syntekpro-animations'); ?></th>
                                <td style="color:#334155;"><?php echo esc_html($last_result ? ucfirst($last_result) : __('N/A', 'syntekpro-animations')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
                        <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-plus')); ?>"><?php _e('Open Animations+', 'syntekpro-animations'); ?></a>
                    </div>
                </div>
            </div>

            <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
                <div style="background:#f8fafc;border:1px dashed #d7e3f4;border-radius:10px;padding:16px;">
                    <h4 style="margin-top:0;">📄 <?php _e('Generate support summary', 'syntekpro-animations'); ?></h4>
                    <p style="margin-bottom:10px;color:#334155;"><?php _e('Copy the details above when opening a support ticket so we can help faster.', 'syntekpro-animations'); ?></p>
                    <button class="button" onclick="window.print();"><?php _e('Print or Save as PDF', 'syntekpro-animations'); ?></button>
                </div>
                <div style="background:#fff4ed;border:1px solid #ffd7ba;border-radius:10px;padding:16px;">
                    <h4 style="margin-top:0;">⬆️ <?php _e('Check for updates', 'syntekpro-animations'); ?></h4>
                    <p style="margin-bottom:10px;color:#7a341e;"><?php _e('Visit the Plugins page to install updates or re-run the updater.', 'syntekpro-animations'); ?></p>
                    <a class="button button-primary" style="background:#0f172a;border-color:#0f172a;" href="<?php echo esc_url(admin_url('plugins.php')); ?>"><?php _e('Go to Plugins', 'syntekpro-animations'); ?></a>
                </div>
                <div style="background:#e8f5ff;border:1px solid #cce4ff;border-radius:10px;padding:16px;">
                    <h4 style="margin-top:0;">🔍 <?php _e('Troubleshooting tips', 'syntekpro-animations'); ?></h4>
                    <ul style="margin:6px 0 0 18px;line-height:1.6;">
                        <li><?php _e('Clear any caching layers after updating.', 'syntekpro-animations'); ?></li>
                        <li><?php _e('Ensure GSAP assets are not blocked by security plugins.', 'syntekpro-animations'); ?></li>
                        <li><?php _e('Disable conflicting animation plugins when testing.', 'syntekpro-animations'); ?></li>
                    </ul>
                </div>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>
        <?php
    }

    /**
     * About page hub for docs, help, and status.
     */
    public function about_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'overview';
        if (!in_array($active_tab, array('overview', 'docs', 'help', 'other', 'status'), true)) {
            $active_tab = 'overview';
        }

        $theme = wp_get_theme();
        $memory_limit = defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : ini_get('memory_limit');
        $license_status = get_option('syntekpro_anim_license_status', '');
        $is_pro = function_exists('syntekpro_animations') && syntekpro_animations()->is_pro_active();

        $icon_url_for = function($candidates) {
            foreach ((array) $candidates as $filename) {
                $path = SYNTEKPRO_ANIM_PLUGIN_DIR . 'assets/img/' . $filename;
                if (file_exists($path)) {
                    return SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/' . str_replace(' ', '%20', $filename);
                }
            }
            return SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/SyntekPro%20Plugins%20Icon%20.png';
        };

        $other_plugins = array(
            array(
                'name' => 'SyntekPro Forms',
                'url' => 'https://plugins.syntekpro.com/forms',
                'description' => __('Drag-and-drop form workflows with clean, conversion-focused design.', 'syntekpro-animations'),
                'icon' => $icon_url_for(array('Syntekpro Forms Icon.png')),
            ),
            array(
                'name' => 'SyntekPro Chat',
                'url' => 'https://plugins.syntekpro.com/chat',
                'description' => __('Live chat and support widgets for faster customer conversations.', 'syntekpro-animations'),
                'icon' => $icon_url_for(array('SyntekPro Chat Icon.png')),
            ),
            array(
                'name' => 'SyntekPro Toggle',
                'url' => 'https://plugins.syntekpro.com/toggle',
                'description' => __('Interactive toggle blocks for pricing, FAQs, and feature comparisons.', 'syntekpro-animations'),
                'icon' => $icon_url_for(array('SyntekPro Toggle Icon Color.png', 'SyntekPro Toggle Icon.png', 'SyntekPro Plugins Icon .png')),
            ),
            array(
                'name' => 'SyntekPro License Server',
                'url' => 'https://plugins.syntekpro.com/license-server',
                'description' => __('License management and activation infrastructure for premium products.', 'syntekpro-animations'),
                'icon' => $icon_url_for(array('SyntekPro License Server Icon.png')),
            ),
            array(
                'name' => 'SyntekPro Plugins Support',
                'url' => 'https://plugins.syntekpro.com/support',
                'description' => __('Central support portal for plugin help, onboarding, and troubleshooting.', 'syntekpro-animations'),
                'icon' => $icon_url_for(array('SyntekPro Plugins Support Icon.png')),
            ),
            array(
                'name' => 'SyntekPro WordPress Themes',
                'url' => 'https://themes.syntekpro.com',
                'description' => __('Theme collection designed to pair with SyntekPro plugin experiences.', 'syntekpro-animations'),
                'icon' => $icon_url_for(array('SyntekPro Themes Icon.png', 'SyntekPro Plugins Logo.png')),
            ),
            array(
                'name' => 'Main Website',
                'url' => 'https://syntekpro.com',
                'description' => __('Company home for products, updates, and ecosystem announcements.', 'syntekpro-animations'),
                'icon' => $icon_url_for(array('syntekpro-logo.png', 'SYNTEK PRO LOGO Transparent HD 1563x402.png')),
            ),
        );

        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('About SyntekPro Animations', 'syntekpro-animations'), __('One place for product story, documentation, help center, and system health.', 'syntekpro-animations'), sprintf(__('Version %s', 'syntekpro-animations'), esc_html(SYNTEKPRO_ANIM_VERSION))); ?>

            <h2 class="nav-tab-wrapper" style="margin-bottom:14px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-about&tab=overview')); ?>" class="nav-tab <?php echo $active_tab === 'overview' ? 'nav-tab-active' : ''; ?>">✨ <?php _e('Overview', 'syntekpro-animations'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-about&tab=docs')); ?>" class="nav-tab <?php echo $active_tab === 'docs' ? 'nav-tab-active' : ''; ?>">📖 <?php _e('Documentation', 'syntekpro-animations'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-about&tab=help')); ?>" class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>">💡 <?php _e('Help Center', 'syntekpro-animations'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-about&tab=other')); ?>" class="nav-tab <?php echo $active_tab === 'other' ? 'nav-tab-active' : ''; ?>">🧰 <?php _e('Other Plugins', 'syntekpro-animations'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-about&tab=status')); ?>" class="nav-tab <?php echo $active_tab === 'status' ? 'nav-tab-active' : ''; ?>">🩺 <?php _e('System Status', 'syntekpro-animations'); ?></a>
            </h2>

            <?php if ($active_tab === 'overview') : ?>
                <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
                    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;color:#0f172a;">🎯 <?php _e('What this plugin delivers', 'syntekpro-animations'); ?></h3>
                        <ul style="margin:0 0 0 18px;line-height:1.7;color:#334155;">
                            <li><?php _e('Smart slider workflows with layered timing controls.', 'syntekpro-animations'); ?></li>
                            <li><?php _e('Animation presets for quick production builds.', 'syntekpro-animations'); ?></li>
                            <li><?php _e('Pattern browser plus editable pattern data.', 'syntekpro-animations'); ?></li>
                            <li><?php _e('Animations+ unlock flow for premium capabilities.', 'syntekpro-animations'); ?></li>
                        </ul>
                    </div>
                    <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;color:#0f172a;">🚀 <?php _e('Quick navigation', 'syntekpro-animations'); ?></h3>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=syntekpro_slider')); ?>"><?php _e('Open Sliders', 'syntekpro-animations'); ?></a>
                            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-patterns')); ?>"><?php _e('Open Patterns', 'syntekpro-animations'); ?></a>
                            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-builder')); ?>"><?php _e('Open Builder', 'syntekpro-animations'); ?></a>
                            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-presets')); ?>"><?php _e('Open Presets', 'syntekpro-animations'); ?></a>
                        </div>
                    </div>
                    <div style="background:linear-gradient(135deg,#0f172a,#1f2937);border-radius:12px;padding:16px;color:#fff;">
                        <h3 style="margin:0 0 8px 0;color:#fff;">⭐ <?php _e('Animations+ Status', 'syntekpro-animations'); ?></h3>
                        <p style="margin:0 0 8px 0;opacity:.9;"><?php echo esc_html($is_pro ? __('Active and unlocked', 'syntekpro-animations') : __('Free mode is active', 'syntekpro-animations')); ?></p>
                        <p style="margin:0 0 10px 0;opacity:.9;"><?php echo esc_html($license_status ? ucfirst($license_status) : __('Not validated yet', 'syntekpro-animations')); ?></p>
                        <a class="button" style="background:#fff;border-color:#fff;color:#0f172a;" href="<?php echo esc_url(admin_url('admin.php?page=syntekpro-animations-plus')); ?>"><?php _e('Manage Animations+', 'syntekpro-animations'); ?></a>
                    </div>
                </div>
            <?php elseif ($active_tab === 'docs') : ?>
                <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;">
                    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;">🚀 <?php _e('Quick Start', 'syntekpro-animations'); ?></h3>
                        <pre style="background:#f8fafc;border:1px solid #e5e7eb;padding:12px;border-radius:8px;margin:0;">[sp_animate type="fadeIn" duration="1" delay="0"]Your content[/sp_animate]</pre>
                    </div>
                    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;">📘 <?php _e('Reference Links', 'syntekpro-animations'); ?></h3>
                        <ul style="margin:0 0 0 18px;line-height:1.7;">
                            <li><a href="https://plugins.syntekpro.com/animations" target="_blank" rel="noopener noreferrer"><?php _e('SyntekPro Animations', 'syntekpro-animations'); ?></a></li>
                            <li><a href="https://plugins.syntekpro.com/docs" target="_blank" rel="noopener noreferrer"><?php _e('Documentation', 'syntekpro-animations'); ?></a></li>
                            <li><a href="https://syntekpro.com" target="_blank" rel="noopener noreferrer"><?php _e('Main Website', 'syntekpro-animations'); ?></a></li>
                        </ul>
                    </div>
                </div>
            <?php elseif ($active_tab === 'help') : ?>
                <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;">
                    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;">🧭 <?php _e('Support Channels', 'syntekpro-animations'); ?></h3>
                        <p style="margin:0 0 10px 0;color:#334155;"><?php _e('Get direct product support, release notes, and onboarding guidance.', 'syntekpro-animations'); ?></p>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a class="button button-primary" style="background:#e53935;border-color:#e53935;" href="https://plugins.syntekpro.com/support" target="_blank" rel="noopener noreferrer"><?php _e('Open Support', 'syntekpro-animations'); ?></a>
                            <a class="button" href="https://plugins.syntekpro.com/docs" target="_blank" rel="noopener noreferrer"><?php _e('Open Docs', 'syntekpro-animations'); ?></a>
                        </div>
                        <div style="margin-top:10px;color:#334155;font-size:13px;line-height:1.7;">
                            <strong><?php _e('Email:', 'syntekpro-animations'); ?></strong>
                            <a href="mailto:support@syntekpro.com">support@syntekpro.com</a>,
                            <a href="mailto:feedback@syntekpro.com">feedback@syntekpro.com</a>
                        </div>
                    </div>
                    <div style="background:#fefce8;border:1px solid #fde68a;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;color:#92400e;">💬 <?php _e('Before you contact support', 'syntekpro-animations'); ?></h3>
                        <ul style="margin:0 0 0 18px;line-height:1.7;color:#7c2d12;">
                            <li><?php _e('Include your plugin version and WordPress version.', 'syntekpro-animations'); ?></li>
                            <li><?php _e('Describe exact reproduction steps.', 'syntekpro-animations'); ?></li>
                            <li><?php _e('Attach screenshots or short recordings where possible.', 'syntekpro-animations'); ?></li>
                        </ul>
                    </div>
                </div>
            <?php elseif ($active_tab === 'other') : ?>
                <div class="syntekpro-settings-section" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
                        <?php foreach ($other_plugins as $plugin) : ?>
                            <a href="<?php echo esc_url($plugin['url']); ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;gap:12px;align-items:flex-start;transition:all .15s ease;">
                                <img src="<?php echo esc_url($plugin['icon']); ?>" alt="<?php echo esc_attr($plugin['name']); ?>" style="width:42px;height:42px;object-fit:contain;border-radius:10px;background:#fff;border:1px solid #e5e7eb;padding:4px;" />
                                <div>
                                    <h3 style="margin:0 0 6px 0;font-size:15px;color:#0f172a;"><?php echo esc_html($plugin['name']); ?></h3>
                                    <p style="margin:0;color:#475569;font-size:13px;line-height:1.55;"><?php echo esc_html($plugin['description']); ?></p>
                                    <div style="margin-top:8px;color:#2563eb;font-size:12px;font-weight:600;"><?php echo esc_html(parse_url($plugin['url'], PHP_URL_HOST)); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top:14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:14px;">
                        <h3 style="margin:0 0 8px 0;color:#7c2d12;"><?php _e('SyntekPro Animations Links', 'syntekpro-animations'); ?></h3>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                            <a class="button" href="https://plugins.syntekpro.com/animations" target="_blank" rel="noopener noreferrer"><?php _e('Plugin Page', 'syntekpro-animations'); ?></a>
                            <a class="button" href="https://plugins.syntekpro.com/docs" target="_blank" rel="noopener noreferrer"><?php _e('Documentation', 'syntekpro-animations'); ?></a>
                            <a class="button" href="mailto:support@syntekpro.com"><?php _e('Email Support', 'syntekpro-animations'); ?></a>
                            <a class="button" href="mailto:feedback@syntekpro.com"><?php _e('Send Feedback', 'syntekpro-animations'); ?></a>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="syntekpro-settings-section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;">
                    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;">📊 <?php _e('Environment', 'syntekpro-animations'); ?></h3>
                        <table class="widefat striped" style="margin:0;">
                            <tbody>
                                <tr><th><?php _e('Plugin Version', 'syntekpro-animations'); ?></th><td><?php echo esc_html(SYNTEKPRO_ANIM_VERSION); ?></td></tr>
                                <tr><th><?php _e('WordPress', 'syntekpro-animations'); ?></th><td><?php echo esc_html(get_bloginfo('version')); ?></td></tr>
                                <tr><th><?php _e('PHP', 'syntekpro-animations'); ?></th><td><?php echo esc_html(PHP_VERSION); ?></td></tr>
                                <tr><th><?php _e('Theme', 'syntekpro-animations'); ?></th><td><?php echo esc_html($theme->get('Name') . ' ' . $theme->get('Version')); ?></td></tr>
                                <tr><th><?php _e('Memory Limit', 'syntekpro-animations'); ?></th><td><?php echo esc_html($memory_limit ? $memory_limit : __('Unavailable', 'syntekpro-animations')); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px;">
                        <h3 style="margin:0 0 8px 0;color:#1e3a8a;">🛠 <?php _e('Troubleshooting', 'syntekpro-animations'); ?></h3>
                        <ul style="margin:0 0 0 18px;line-height:1.7;color:#1e3a8a;">
                            <li><?php _e('Clear caches after plugin updates.', 'syntekpro-animations'); ?></li>
                            <li><?php _e('Disable conflicting animation plugins during testing.', 'syntekpro-animations'); ?></li>
                            <li><?php _e('Verify theme and builder scripts are not minifying GSAP twice.', 'syntekpro-animations'); ?></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php $this->render_page_footer(__('About now centralizes docs, help, and system status.', 'syntekpro-animations')); ?>
        </div>
        <?php
    }

    /**
     * Timeline Creator Page
     */
    public function timeline_page() {
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <?php $this->render_page_header(__('Timeline Creator', 'syntekpro-animations'), __('Create complex animation sequences', 'syntekpro-animations'), sprintf(__('Version %s', 'syntekpro-animations'), SYNTEKPRO_ANIM_VERSION)); ?>

            <!-- Timeline Builder -->
            <div class="timeline-builder" id="timeline-builder">
                <h3>⏱️ <?php _e('Animation Timeline', 'syntekpro-animations'); ?></h3>
                <p><?php _e('Build multi-step animation sequences. Drag to reorder steps.', 'syntekpro-animations'); ?></p>
                
                <div id="timeline-steps">
                    <!-- Timeline steps will be added here dynamically -->
                    <div class="timeline-step" data-step="1">
                        <div class="timeline-handle">⋮⋮</div>
                        <div class="step-content">
                            <h4><?php _e('Step 1', 'syntekpro-animations'); ?></h4>
                            <label><?php _e('Animation:', 'syntekpro-animations'); ?> 
                                <select class="step-animation">
                                    <option value="fadeIn">Fade In</option>
                                    <option value="slideLeft">Slide Left</option>
                                    <option value="scaleUp">Scale Up</option>
                                    <option value="rotateIn">Rotate In</option>
                                    <option value="zoomIn">Zoom In</option>
                                </select>
                            </label>
                            <label><?php _e('Duration:', 'syntekpro-animations'); ?> 
                                <input type="number" class="step-duration" value="1" step="0.1" min="0">
                            </label>
                            <label><?php _e('Delay:', 'syntekpro-animations'); ?> 
                                <input type="number" class="step-delay" value="0" step="0.1" min="0">
                            </label>
                            <button class="remove-timeline-step"><?php _e('Remove', 'syntekpro-animations'); ?></button>
                        </div>
                    </div>
                </div>
                
                <button id="add-timeline-step" class="button button-secondary" style="margin: 15px 0;">
                    + <?php _e('Add Step', 'syntekpro-animations'); ?>
                </button>

                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:8px 0 4px;">
                    <button id="timeline-export" class="button">📤 <?php _e('Export JSON', 'syntekpro-animations'); ?></button>
                    <button id="timeline-import" class="button">📥 <?php _e('Import JSON', 'syntekpro-animations'); ?></button>
                    <button id="timeline-save-local" class="button button-secondary">💾 <?php _e('Save to Browser', 'syntekpro-animations'); ?></button>
                    <button id="timeline-load-local" class="button button-secondary">⬆ <?php _e('Load from Browser', 'syntekpro-animations'); ?></button>
                    <input type="file" id="timeline-import-file" accept="application/json" style="display:none;" />
                </div>
                
                <!-- Preview Box -->
                <div id="timeline-preview-box">
                    <?php _e('Timeline Preview', 'syntekpro-animations'); ?>
                </div>
                
                <!-- Actions -->
                <div class="builder-actions">
                    <button id="play-timeline" class="button button-primary">
                        ▶ <?php _e('Play Timeline', 'syntekpro-animations'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Pro Feature Notice -->
            <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:20px;margin-top:24px;text-align:center;">
                <h3 style="color:#0f172a;margin-top:0;">🔒 <?php _e('Timeline Builder is a Pro Feature', 'syntekpro-animations'); ?></h3>
                <p style="font-size:14px;color:#334155;margin:0 0 10px 0;">
                    <?php _e('Upgrade to Pro to save and export your timeline animations. This test site is fully unlocked for evaluation.', 'syntekpro-animations'); ?>
                </p>
                <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="background:#0f172a;border-color:#0f172a;padding:9px 24px;font-size:15px;" target="_blank">
                    ⭐ <?php _e('Purchase Pro License', 'syntekpro-animations'); ?>
                </a>
            </div>
            <?php $this->render_page_footer(); ?>
        </div>
        <?php
    }
}

// Initialize
new Syntekpro_Animations_Admin();