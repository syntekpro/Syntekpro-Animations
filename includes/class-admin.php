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
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Syntekpro Animations', 'syntekpro-animations'),
            __('SyntekPro Animations', 'syntekpro-animations'),
            'manage_options',
            'syntekpro-animations',
            array($this, 'settings_page'),
            'dashicons-image-filter',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'syntekpro-animations',
            __('Settings', 'syntekpro-animations'),
            __('Settings', 'syntekpro-animations'),
            'manage_options',
            'syntekpro-animations'
        );
        
        add_submenu_page(
            'syntekpro-animations',
            __('Animation Presets', 'syntekpro-animations'),
            __('Presets', 'syntekpro-animations'),
            'manage_options',
            'syntekpro-animations-presets',
            array($this, 'presets_page')
        );
        
        add_submenu_page(
            'syntekpro-animations',
            __('Animation Builder', 'syntekpro-animations'),
            __('🎨 Builder', 'syntekpro-animations'),
            'manage_options',
            'syntekpro-animations-builder',
            array($this, 'builder_page')
        );
        
        add_submenu_page(
            'syntekpro-animations',
            __('Timeline Creator', 'syntekpro-animations'),
            __('⏱️ Timeline', 'syntekpro-animations'),
            'manage_options',
            'syntekpro-animations-timeline',
            array($this, 'timeline_page')
        );
        
        add_submenu_page(
            'syntekpro-animations',
            __('Documentation', 'syntekpro-animations'),
            __('Documentation', 'syntekpro-animations'),
            'manage_options',
            'syntekpro-animations-docs',
            array($this, 'documentation_page')
        );
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
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['valid']) && $data['valid'] === true) {
            update_option('syntekpro_anim_license_status', 'valid');
            update_option('syntekpro_anim_license_expires', $data['expires'] ?? '');
            return true;
        } else {
            update_option('syntekpro_anim_license_status', 'invalid');
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
        
        // License status notice
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
            <!-- Banner with Logo -->
            <div class="syntekpro-admin-banner">
                <div class="syntekpro-admin-branding">
                    <img src="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animations%20Transparent%20Logo%20with%20Favicon.png'); ?>" alt="Syntekpro Logo" />
                    <div class="syntekpro-brand-content">
                        <div class="brand-title"><?php _e('Syntekpro Animations', 'syntekpro-animations'); ?></div>
                        <div class="brand-desc"><?php _e('Professional GSAP-powered animations for WordPress', 'syntekpro-animations'); ?></div>
                        <div class="brand-version"><?php echo sprintf(__('Version %s', 'syntekpro-animations'), SYNTEKPRO_ANIM_VERSION); ?></div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <h2 class="nav-tab-wrapper">
                <a href="?page=syntekpro-animations&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    ⚙️ <?php _e('General Settings', 'syntekpro-animations'); ?>
                </a>
                <a href="?page=syntekpro-animations&tab=plugins" class="nav-tab <?php echo $active_tab === 'plugins' ? 'nav-tab-active' : ''; ?>">
                    🎨 <?php _e('GSAP Plugins', 'syntekpro-animations'); ?>
                </a>
                <a href="?page=syntekpro-animations&tab=license" class="nav-tab <?php echo $active_tab === 'license' ? 'nav-tab-active' : ''; ?>">
                    🔐 <?php _e('License', 'syntekpro-animations'); ?> <?php if (syntekpro_animations()->is_pro_active()) echo '<span style="color:#46b450;"> ✓ Pro</span>'; ?>
                </a>
            </h2>

            <!-- Tab Content -->
            <div class="syntekpro-settings-section">
                <?php
                switch ($active_tab) {
                    case 'general':
                        $this->render_general_tab();
                        break;
                    case 'plugins':
                        $this->render_plugins_tab();
                        break;
                    case 'license':
                        $this->render_license_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render General tab
     */
    private function render_general_tab() {
        ?>
        <h2>⚙️ <?php _e('Core Settings', 'syntekpro-animations'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('syntekpro_anim_general'); ?>
            <input type="hidden" name="syntekpro_anim_general_nonce" value="<?php echo wp_create_nonce('syntekpro_anim_general_action'); ?>">
            
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_load_gsap"><?php _e('Animation Engine', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label class="syntekpro-toggle">
                            <input type="checkbox" id="syntekpro_anim_load_gsap" name="syntekpro_anim_load_gsap" value="yes" <?php checked(get_option('syntekpro_anim_load_gsap', 'yes'), 'yes'); ?>>
                            <strong><?php _e('Enable Animation Engine', 'syntekpro-animations'); ?></strong>
                        </label>
                        <p class="description">
                            <span style="color:#2e7d32;">✓</span> <?php _e('Required for all animations. Disabling this will break animations.', 'syntekpro-animations'); ?>
                        </p>
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
                        <p class="description">
                            <span style="color:#2e7d32;">✓</span> <?php _e('Trigger animations when scrolling. Highly recommended.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_smooth_scroll"><?php _e('Smooth Scrolling', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label class="syntekpro-toggle">
                            <input type="checkbox" id="syntekpro_anim_smooth_scroll" name="syntekpro_anim_smooth_scroll" value="yes" <?php checked(get_option('syntekpro_anim_smooth_scroll', 'no'), 'yes'); ?> <?php if (!syntekpro_animations()->is_pro_active()) echo 'disabled'; ?>>
                            <strong><?php _e('Enable Smooth Scrolling', 'syntekpro-animations'); ?></strong>
                            <?php if (!syntekpro_animations()->is_pro_active()) : ?>
                                <span style="color:#ec407a;"> 🔒 PRO</span>
                            <?php endif; ?>
                        </label>
                        <p class="description">
                            <?php _e('Creates buttery-smooth scrolling effects using ScrollSmoother.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            
            <h3 style="color:#1565c0;font-size:1.1em;margin-top:35px;border-bottom:2px solid #1565c0;padding-bottom:8px;">🚀 <?php _e('Performance Settings', 'syntekpro-animations'); ?></h3>
            
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_disable_mobile"><?php _e('Mobile Optimization', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label class="syntekpro-toggle">
                            <input type="checkbox" id="syntekpro_anim_disable_mobile" name="syntekpro_anim_disable_mobile" value="yes" <?php checked(get_option('syntekpro_anim_disable_mobile', 'no'), 'yes'); ?>>
                            <strong><?php _e('Disable Animations on Mobile Devices', 'syntekpro-animations'); ?></strong>
                        </label>
                        <p class="description">
                            <?php _e('Improves performance on mobile by disabling complex animations.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_lazy_load"><?php _e('Lazy Loading', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label class="syntekpro-toggle">
                            <input type="checkbox" id="syntekpro_anim_lazy_load" name="syntekpro_anim_lazy_load" value="yes" <?php checked(get_option('syntekpro_anim_lazy_load', 'yes'), 'yes'); ?>>
                            <strong><?php _e('Enable Lazy Loading for Animations', 'syntekpro-animations'); ?></strong>
                        </label>
                        <p class="description">
                            <?php _e('Only load animations when they\'re about to be visible. Improves page load speed.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_default_duration"><?php _e('Default Duration', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="syntekpro_anim_default_duration" name="syntekpro_anim_default_duration" value="<?php echo esc_attr(get_option('syntekpro_anim_default_duration', '1')); ?>" step="0.1" min="0.1" max="5" style="width:80px;"> <?php _e('seconds', 'syntekpro-animations'); ?>
                        <p class="description">
                            <?php _e('Default animation duration for all animations (0.5 - 2 seconds recommended).', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_default_ease"><?php _e('Default Easing', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <select id="syntekpro_anim_default_ease" name="syntekpro_anim_default_ease" style="width:200px;">
                            <option value="none" <?php selected(get_option('syntekpro_anim_default_ease', 'power2.out'), 'none'); ?>>Linear</option>
                            <option value="power1.out" <?php selected(get_option('syntekpro_anim_default_ease', 'power2.out'), 'power1.out'); ?>>Power 1 (Gentle)</option>
                            <option value="power2.out" <?php selected(get_option('syntekpro_anim_default_ease', 'power2.out'), 'power2.out'); ?>>Power 2 (Standard)</option>
                            <option value="power3.out" <?php selected(get_option('syntekpro_anim_default_ease', 'power2.out'), 'power3.out'); ?>>Power 3 (Strong)</option>
                            <option value="back.out(1.7)" <?php selected(get_option('syntekpro_anim_default_ease', 'power2.out'), 'back.out(1.7)'); ?>>Back (Bouncy)</option>
                            <option value="elastic.out(1,0.3)" <?php selected(get_option('syntekpro_anim_default_ease', 'power2.out'), 'elastic.out(1,0.3)'); ?>>Elastic</option>
                            <option value="bounce.out" <?php selected(get_option('syntekpro_anim_default_ease', 'power2.out'), 'bounce.out'); ?>>Bounce</option>
                        </select>
                        <p class="description">
                            <?php _e('Default easing function for all animations. Power 2 is recommended for most cases.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            
            <h3 style="color:#2e7d32;font-size:1.1em;margin-top:35px;border-bottom:2px solid #2e7d32;padding-bottom:8px;">♿ <?php _e('Accessibility Settings', 'syntekpro-animations'); ?></h3>
            
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_reduced_motion"><?php _e('Respect Reduced Motion', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label class="syntekpro-toggle">
                            <input type="checkbox" id="syntekpro_anim_reduced_motion" name="syntekpro_anim_reduced_motion" value="yes" <?php checked(get_option('syntekpro_anim_reduced_motion', 'yes'), 'yes'); ?>>
                            <strong><?php _e('Honor prefers-reduced-motion Setting', 'syntekpro-animations'); ?></strong>
                        </label>
                        <p class="description">
                            <?php _e('Automatically disable animations for users who prefer reduced motion. Recommended for accessibility.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            
            <h3 style="color:#ec407a;font-size:1.1em;margin-top:35px;border-bottom:2px solid #ec407a;padding-bottom:8px;">🔧 <?php _e('Developer Options', 'syntekpro-animations'); ?></h3>
            
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_enable_developer_mode"><?php _e('Developer Mode', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label class="syntekpro-toggle">
                            <input type="checkbox" id="syntekpro_anim_enable_developer_mode" name="syntekpro_anim_enable_developer_mode" value="yes" <?php checked(get_option('syntekpro_anim_enable_developer_mode', 'no'), 'yes'); ?>>
                            <strong><?php _e('Enable Developer Mode', 'syntekpro-animations'); ?></strong>
                        </label>
                        <p class="description">
                            <?php _e('Access advanced features, API hooks, and custom animation controls.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_debug_mode"><?php _e('Debug Mode', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label class="syntekpro-toggle">
                            <input type="checkbox" id="syntekpro_anim_debug_mode" name="syntekpro_anim_debug_mode" value="yes" <?php checked(get_option('syntekpro_anim_debug_mode', 'no'), 'yes'); ?>>
                            <strong><?php _e('Show Console Logs & Markers', 'syntekpro-animations'); ?></strong>
                        </label>
                        <p class="description">
                            <?php _e('Display ScrollTrigger markers and console logs for debugging animations.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="syntekpro_anim_enable_developer_mode"><?php _e('Developer Mode', 'syntekpro-animations'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="syntekpro_anim_enable_developer_mode" name="syntekpro_anim_enable_developer_mode" value="yes" <?php checked(get_option('syntekpro_anim_enable_developer_mode', 'no'), 'yes'); ?>>
                            <strong><?php _e('Enable Developer Hooks & Filters', 'syntekpro-animations'); ?></strong>
                        </label>
                        <p class="description">
                            <?php _e('For developers who want to write custom animation code via PHP/JavaScript hooks.', 'syntekpro-animations'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Save Settings', 'syntekpro-animations')); ?>
        </form>
        
        <div class="syntekpro-info-box">
            <strong>💡 Tip:</strong> Keep the Animation Engine and Scroll Animations enabled for the best experience. Disable other features only if you're not using them to improve performance.
        </div>
        <?php
    }
    
    /**
     * Render Plugins tab
     */
    private function render_plugins_tab() {
        ?>
        <h2><?php _e('Core Animation Features', 'syntekpro-animations'); ?></h2>
        <p><?php _e('These features are included free and provide additional animation capabilities.', 'syntekpro-animations'); ?></p>
        
        <form method="post" action="options.php">
            <?php settings_fields('syntekpro_anim_plugins'); ?>
            <input type="hidden" name="syntekpro_anim_plugins_nonce" value="<?php echo wp_create_nonce('syntekpro_anim_plugins_action'); ?>">
            
            <div class="syntekpro-plugins-grid">
                <?php
                $free_plugins = array(
                    'flip' => 'Flip',
                    'observer' => 'Observer',
                    'scrolltoplugin' => 'ScrollTo Plugin',
                    'textplugin' => 'Text Plugin',
                    'draggable' => 'Draggable',
                    'motionpathplugin' => 'MotionPath Plugin',
                    'easepack' => 'EasePack',
                    'customease' => 'CustomEase'
                );
                
                $free_descriptions = array(
                    'flip' => 'Smooth state transitions',
                    'observer' => 'Watch for element changes',
                    'scrolltoplugin' => 'Smooth scroll to elements',
                    'textplugin' => 'Animate text content',
                    'draggable' => 'Make elements draggable',
                    'motionpathplugin' => 'Animate along paths',
                    'easepack' => 'Additional easing functions',
                    'customease' => 'Custom easing curves'
                );
                
                foreach ($free_plugins as $key => $label) {
                    $option_value = get_option('syntekpro_anim_load_' . $key, 'no');
                    ?>
                    <div class="syntekpro-plugin-card">
                        <label>
                            <input type="checkbox" name="syntekpro_anim_load_<?php echo $key; ?>" value="yes" <?php checked($option_value, 'yes'); ?>>
                            <strong><?php echo esc_html($label); ?></strong>
                        </label>
                        <p class="plugin-description"><?php echo esc_html($free_descriptions[$key]); ?></p>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <?php submit_button(__('Save Plugins', 'syntekpro-animations')); ?>
        </form>

        <?php if (syntekpro_animations()->is_pro_active()) : ?>
            <h2 style="margin-top:40px;"><?php _e('Premium Animation Features', 'syntekpro-animations'); ?></h2>
            <p><?php _e('Unlock powerful professional features with your active license.', 'syntekpro-animations'); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields('syntekpro_anim_pro'); ?>
                <input type="hidden" name="syntekpro_anim_pro_nonce" value="<?php echo wp_create_nonce('syntekpro_anim_pro_action'); ?>">
                
                <div class="syntekpro-plugins-grid">
                    <?php
                    $pro_plugins = array(
                        'splittext' => 'SplitText',
                        'morphsvgplugin' => 'MorphSVG Plugin',
                        'drawsvgplugin' => 'DrawSVG Plugin',
                        'scrollsmoother' => 'ScrollSmoother',
                        'gsdevtools' => 'GSDevTools',
                        'inertiaplugin' => 'Inertia Plugin',
                        'scrambletextplugin' => 'ScrambleText Plugin',
                        'custombounce' => 'CustomBounce',
                        'customwiggle' => 'CustomWiggle'
                    );
                    
                    $pro_descriptions = array(
                        'splittext' => 'Animate text by characters, words, or lines',
                        'morphsvgplugin' => 'Morph between SVG shapes smoothly',
                        'drawsvgplugin' => 'Progressively draw SVG strokes',
                        'scrollsmoother' => 'Buttery smooth scrolling effects',
                        'gsdevtools' => 'Visual timeline editor and debugger',
                        'inertiaplugin' => 'Physics-based momentum scrolling',
                        'scrambletextplugin' => 'Scramble and unscramble text',
                        'custombounce' => 'Create custom bounce easing curves',
                        'customwiggle' => 'Add custom wiggle/jiggle animations'
                    );
                    
                    foreach ($pro_plugins as $key => $label) {
                        $option_value = get_option('syntekpro_anim_load_' . $key, 'no');
                        ?>
                        <div class="syntekpro-plugin-card pro-feature">
                            <label>
                                <input type="checkbox" name="syntekpro_anim_load_<?php echo $key; ?>" value="yes" <?php checked($option_value, 'yes'); ?>>
                                <strong><?php echo esc_html($label); ?></strong>
                                <span class="pro-badge">PRO</span>
                            </label>
                            <p class="plugin-description"><?php echo esc_html($pro_descriptions[$key]); ?></p>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <?php submit_button(__('Save Plugins', 'syntekpro-animations')); ?>
            </form>
        <?php else : ?>
            <div style="background:linear-gradient(135deg, #fff3cd 0%, #fffbea 100%);border:2px solid #ffc107;border-radius:8px;padding:25px;margin-top:40px;text-align:center;">
                <h3 style="color:#ff6b6b;margin-top:0;">🚀 <?php _e('Unlock Premium Animation Features', 'syntekpro-animations'); ?></h3>
                <p style="font-size:15px;color:#333;margin:10px 0;"><?php _e('Get access to Timeline Builder, Text Effects, SVG Morphing, Draw Effects, and 5+ more premium features.', 'syntekpro-animations'); ?></p>
                <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="margin-top:10px;padding:10px 30px;font-size:16px;" target="_blank">
                    ⭐ <?php _e('Upgrade to Pro', 'syntekpro-animations'); ?>
                </a>
            </div>
        <?php endif; ?>
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
        
        <?php if (!syntekpro_animations()->is_pro_active()) : ?>
            <div style="background:linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);border-left:5px solid #2196f3;border-radius:8px;padding:25px;margin-top:40px;">
                <h3 style="margin-top:0;color:#1565c0;">🎉 <?php _e('Get Syntekpro Animations Pro', 'syntekpro-animations'); ?></h3>
                <p><?php _e('Unlock all premium GSAP plugins and features:', 'syntekpro-animations'); ?></p>
                <ul style="columns: 2; gap: 20px;">
                    <li><strong>Timeline Builder</strong> - Visual animation sequencer</li>
                    <li><strong>Text Effects</strong> - Character & word animations</li>
                    <li><strong>SVG Morph</strong> - Smooth shape morphing</li>
                    <li><strong>Draw Effects</strong> - Progressive stroke drawing</li>
                    <li><strong>Smooth Scroll</strong> - Buttery smooth scrolling</li>
                    <li><strong>Animation Editor</strong> - Visual timeline editor</li>
                    <li><strong>Physics Engine</strong> - Realistic motion effects</li>
                    <li><strong>50+ Pro Animations</strong> - Premium effects library</li>
                </ul>
                <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="margin-top:15px;padding:10px 30px;font-size:16px;background:#2196f3;border-color:#2196f3;" target="_blank">
                    ⭐ <?php _e('Purchase Pro License', 'syntekpro-animations'); ?>
                </a>
            </div>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Presets page
     */
    public function presets_page() {
        $presets = Syntekpro_Animation_Presets::get_by_category();
        $categories = Syntekpro_Animation_Presets::get_categories();
        $selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <!-- Banner with Logo -->
            <div class="syntekpro-admin-banner">
                <div class="syntekpro-admin-branding">
                    <img src="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animations%20Transparent%20Logo%20with%20Favicon.png'); ?>" alt="Syntekpro Logo" />
                    <div class="syntekpro-brand-content">
                        <div class="brand-title"><?php _e('Animation Presets', 'syntekpro-animations'); ?></div>
                        <div class="brand-desc"><?php _e('Browse 50+ ready-to-use animation effects', 'syntekpro-animations'); ?></div>
                    </div>
                </div>
            </div>

            <div class="syntekpro-settings-section">
                <!-- Filter Controls -->
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #1565c0;">
                    <div style="flex: 1;">
                        <label for="preset-category-filter" style="font-weight: 600; display: block; margin-bottom: 8px; color: #333;">
                            🎨 <?php _e('Filter by Category', 'syntekpro-animations'); ?>
                        </label>
                        <select id="preset-category-filter" style="width: 100%; max-width: 300px; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; cursor: pointer;">
                            <option value=""><?php _e('✓ All Categories', 'syntekpro-animations'); ?></option>
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
                        <button type="button" id="copy-all-btn" class="button button-secondary" style="margin-top: 24px; padding: 8px 16px;">
                            📋 <?php _e('Copy All', 'syntekpro-animations'); ?>
                        </button>
                    </div>
                </div>

                <p style="color: #666; margin-bottom: 20px; font-style: italic;">
                    💡 <?php _e('Click any shortcode to copy it. Use in pages, posts, or the block editor.', 'syntekpro-animations'); ?>
                </p>
                
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
                            <h3 style="margin: 30px 0 20px 0; padding-bottom: 12px; border-bottom: 3px solid #e53935; color: #e53935; font-size: 1.1em;">
                                📚 <?php echo esc_html($cat_name); ?> <span style="color: #999; font-size: 0.9em; font-weight: normal;">(<?php echo count($cat_presets); ?>)</span>
                            </h3>
                            
                            <div class="syntekpro-presets-grid">
                                <?php foreach ($cat_presets as $key => $preset) : 
                                    $gradient_colors = [
                                        'fade' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                        'slide' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                                        'zoom' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                                        'scale' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                                        'rotate' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                                        'reveal' => 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
                                        'wave' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                                        'swing' => 'linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)',
                                        'attention' => 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                                        'bounce' => 'linear-gradient(135deg, #9be15d 0%, #00e3ae 100%)',
                                        'elastic' => 'linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%)',
                                        '3d' => 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)',
                                        'glitch' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                        'peel' => 'linear-gradient(135deg, #f97316 0%, #fb923c 100%)',
                                        'fold' => 'linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%)',
                                        'text' => 'linear-gradient(135deg, #a78bfa 0%, #c4b5fd 100%)',
                                        'blur' => 'linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)',
                                    ];
                                    $gradient = $gradient_colors[$cat_key] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                                ?>
                                    <div class="syntekpro-preset-card <?php echo $preset['free'] ? '' : 'pro-preset'; ?>" data-shortcode="[sp_animate type=&quot;<?php echo esc_attr($key); ?>&quot;]Content[/sp_animate]" data-preset-key="<?php echo esc_attr($key); ?>" data-preset-category="<?php echo esc_attr($cat_key); ?>">
                                        <div class="preset-preview-box" style="background: <?php echo esc_attr($gradient); ?>;">
                                            <div class="preset-preview-element" data-animation-trigger>
                                                ▶
                                            </div>
                                        </div>
                                        <div class="preset-info">
                                            <div class="preset-header">
                                                <div class="preset-title">
                                                    <strong><?php echo esc_html($preset['name']); ?></strong>
                                                    <?php if (!$preset['free']) : ?>
                                                        <span class="preset-badge pro">🔒 Pro</span>
                                                    <?php else : ?>
                                                        <span class="preset-badge free">✓ Free</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="preset-code">
                                                <code>[sp_animate type="<?php echo esc_attr($key); ?>"]</code>
                                            </div>
                                            <button type="button" class="copy-preset-btn button button-small" data-preset="<?php echo esc_attr($key); ?>" title="Copy to clipboard">
                                                📋 Copy
                                            </button>
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
        </div>

        <style>
            #preset-category-filter {
                min-width: 250px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                transition: border-color 0.3s ease;
            }

            #preset-category-filter:focus {
                border-color: #1565c0 !important;
                outline: none;
                box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
            }

            .syntekpro-presets-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .syntekpro-preset-card {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                border: 2px solid #e0e0e0;
                border-radius: 12px;
                padding: 0;
                transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                cursor: pointer;
                position: relative;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                display: flex;
                flex-direction: column;
            }

            .syntekpro-preset-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                transition: left 0.5s;
            }

            .syntekpro-preset-card:hover::before {
                left: 100%;
            }

            .syntekpro-preset-card:hover {
                border-color: #e53935;
                box-shadow: 0 8px 24px rgba(229, 57, 53, 0.25);
                transform: translateY(-6px);
            }

            .syntekpro-preset-card.pro-preset {
                background: linear-gradient(135deg, #fff8f0 0%, #fff4e6 100%);
                border-color: #ffb74d;
            }

            .syntekpro-preset-card.pro-preset:hover {
                border-color: #ff9800;
                box-shadow: 0 8px 24px rgba(255, 152, 0, 0.25);
            }

            .preset-preview-box {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                height: 140px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
                position: relative;
                overflow: hidden;
                font-weight: 600;
                color: white;
                font-size: 16px;
                text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }

            .preset-preview-element {
                width: 80px;
                height: 60px;
                background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                color: #667eea;
                font-size: 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }

            .preset-info {
                padding: 16px;
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .preset-header {
                margin-bottom: 12px;
            }

            .preset-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 15px;
                color: #333;
                margin-bottom: 8px;
            }

            .preset-title strong {
                flex: 1;
                word-break: break-word;
                font-weight: 700;
            }

            .preset-badge {
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 700;
                white-space: nowrap;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            .preset-badge.free {
                background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
                color: #1b5e20;
                box-shadow: 0 2px 4px rgba(27, 94, 32, 0.2);
            }

            .preset-badge.pro {
                background: linear-gradient(135deg, #ffccbc 0%, #ffab91 100%);
                color: #d84315;
                box-shadow: 0 2px 4px rgba(216, 67, 21, 0.2);
            }

            .preset-code {
                background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
                border: 1px solid #e0e0e0;
                border-radius: 6px;
                padding: 10px;
                margin-bottom: 12px;
                overflow-x: auto;
                flex-grow: 1;
            }

            .preset-code code {
                font-size: 12px;
                color: #1565c0;
                font-weight: 600;
                word-break: break-all;
                font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            }

            .copy-preset-btn {
                width: 100%;
                background: linear-gradient(135deg, #e53935 0%, #d32f2f 100%);
                color: white;
                border: none !important;
                border-radius: 8px;
                padding: 10px 12px !important;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 13px;
                letter-spacing: 0.5px;
            }

            .copy-preset-btn:hover {
                background: linear-gradient(135deg, #d32f2f 0%, #c62828 100%);
                transform: scale(1.02);
                box-shadow: 0 4px 12px rgba(229, 57, 53, 0.3);
            }

            .copy-preset-btn:active {
                transform: scale(0.98);
            }

            .copy-preset-btn.copied {
                background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
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
                background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
                color: white;
                padding: 14px 16px;
                margin: 30px 0 20px 0 !important;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 4px 12px rgba(229, 57, 53, 0.2);
            }

            .category-count {
                background: rgba(255,255,255,0.2);
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                backdrop-filter: blur(10px);
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
                background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
                border: 2px solid #9fa8da;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 30px;
                box-shadow: 0 4px 12px rgba(63, 81, 181, 0.1);
            }

            .preset-filter-label {
                font-weight: 700;
                color: #1565c0;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .preset-intro-text {
                background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
                border-left: 5px solid #2e7d32;
                padding: 12px 16px;
                border-radius: 6px;
                color: #1b5e20;
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
                    slideLeft: { from: { x: -100, opacity: 0 }, to: { x: 0, opacity: 1 } },
                    slideRight: { from: { x: 100, opacity: 0 }, to: { x: 0, opacity: 1 } },
                    slideUp: { from: { y: 100, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    slideDown: { from: { y: -100, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    zoomIn: { from: { scale: 0.5, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    zoomOut: { from: { scale: 1.5, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    scaleUp: { from: { scale: 0.8, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    scaleDown: { from: { scale: 1.2 }, to: { scale: 1 } },
                    rotateIn: { from: { rotate: -180, opacity: 0 }, to: { rotate: 0, opacity: 1 } },
                    rotateRight: { from: { rotate: -360 }, to: { rotate: 0 } },
                    revealLeft: { from: { xPercent: -100 }, to: { xPercent: 0 } },
                    revealRight: { from: { xPercent: 100 }, to: { xPercent: 0 } },
                    revealUp: { from: { yPercent: 100 }, to: { yPercent: 0 } },
                    waveHorizontal: { from: { x: -50, opacity: 0 }, to: { x: 50, opacity: 1 } },
                    waveVertical: { from: { y: -50, opacity: 0 }, to: { y: 50, opacity: 1 } },
                    swingIn: { from: { rotate: -45, opacity: 0 }, to: { rotate: 0, opacity: 1 } },
                    swingOut: { from: { rotate: 45, opacity: 0 }, to: { rotate: 0, opacity: 1 } },
                    heartBeat: { from: { scale: 1 }, to: { scale: 1.2 } },
                    pulse: { from: { opacity: 1 }, to: { opacity: 0.7 } },
                    tada: { from: { rotate: -10, scale: 0.9 }, to: { rotate: 10, scale: 1 } },
                    bounce: { from: { y: 0, opacity: 0 }, to: { y: 20, opacity: 1 } },
                    bounceDown: { from: { y: -50, opacity: 0 }, to: { y: 0, opacity: 1 } },
                    jello: { from: { skewX: -12.5, skewY: -12.5, opacity: 0 }, to: { skewX: 0, skewY: 0, opacity: 1 } },
                    elasticIn: { from: { scale: 0, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    elasticOut: { from: { scale: 1.5, opacity: 0 }, to: { scale: 1, opacity: 1 } },
                    flip: { from: { rotateY: -360, opacity: 0 }, to: { rotateY: 0, opacity: 1 } },
                    flip3d: { from: { rotateX: -360, opacity: 0 }, to: { rotateX: 0, opacity: 1 } },
                    glitch: { from: { x: -10, opacity: 0 }, to: { x: 0, opacity: 1 } },
                    glitchText: { from: { x: -8, opacity: 0 }, to: { x: 0, opacity: 1 } },
                    peel: { from: { rotateY: 90, opacity: 0 }, to: { rotateY: 0, opacity: 1 } },
                    fold: { from: { scaleY: 0, opacity: 0 }, to: { scaleY: 1, opacity: 1 } },
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
                        const card = this.closest('.syntekpro-preset-card');
                        const shortcode = card.getAttribute('data-shortcode');
                        const preset = this.getAttribute('data-preset');
                        
                        // Create a temporary textarea for copying
                        const tempTextarea = document.createElement('textarea');
                        tempTextarea.value = '[sp_animate type="' + preset + '"]Your content[/sp_animate]';
                        document.body.appendChild(tempTextarea);
                        tempTextarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempTextarea);
                        
                        // Show feedback
                        const originalText = this.textContent;
                        this.classList.add('copied');
                        this.textContent = '✓ Copied!';
                        
                        setTimeout(() => {
                            this.classList.remove('copied');
                            this.textContent = originalText;
                        }, 2000);
                    });
                });

                // Copy all button
                const copyAllBtn = document.getElementById('copy-all-btn');
                if (copyAllBtn) {
                    copyAllBtn.addEventListener('click', function() {
                        const allPresets = [];
                        document.querySelectorAll('.syntekpro-preset-card').forEach(card => {
                            const preset = card.querySelector('.copy-preset-btn').getAttribute('data-preset');
                            allPresets.push('[sp_animate type="' + preset + '"]Your content[/sp_animate]');
                        });
                        
                        const allCode = allPresets.join('\\n\\n');
                        const tempTextarea = document.createElement('textarea');
                        tempTextarea.value = allCode;
                        document.body.appendChild(tempTextarea);
                        tempTextarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempTextarea);
                        
                        const originalText = this.textContent;
                        this.textContent = '✓ Copied All!';
                        setTimeout(() => {
                            this.textContent = originalText;
                        }, 2000);
                    });
                }
            })();
        </script>
        <?php
    }
    
    /**
     * Documentation page
     */
    public function documentation_page() {
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <!-- Banner with Logo -->
            <div class="syntekpro-admin-banner">
                <div class="syntekpro-admin-branding">
                    <img src="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animations%20Transparent%20Logo%20with%20Favicon.png'); ?>" alt="Syntekpro Logo" />
                    <div class="syntekpro-brand-content">
                        <div class="brand-title"><?php _e('Documentation', 'syntekpro-animations'); ?></div>
                        <div class="brand-desc"><?php _e('Learn how to use Syntekpro Animations in your WordPress site', 'syntekpro-animations'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Getting Started -->
            <div class="syntekpro-docs-section">
                <h3 style="color: #e53935; font-size: 1.3em;">🚀 <?php _e('Quick Start - Basic Usage', 'syntekpro-animations'); ?></h3>
                <p><?php _e('Add animations to your content using simple shortcodes:', 'syntekpro-animations'); ?></p>
                <pre style="background: #f5f5f5; border-left: 4px solid #e53935; padding: 15px; border-radius: 4px; color: #1565c0;">[sp_animate type="fadeIn" duration="1" delay="0"]Your content here[/sp_animate]</pre>
            </div>

            <!-- Shortcode Parameters -->
            <div class="syntekpro-docs-section">
                <h3 style="color: #1565c0; font-size: 1.3em;">⚙️ <?php _e('Shortcode Parameters', 'syntekpro-animations'); ?></h3>
                <p><?php _e('Customize your animations with these parameters:', 'syntekpro-animations'); ?></p>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th style="color: #e53935; width: 15%;"><strong><?php _e('Parameter', 'syntekpro-animations'); ?></strong></th>
                            <th style="color: #1565c0; width: 35%;"><strong><?php _e('Description', 'syntekpro-animations'); ?></strong></th>
                            <th style="color: #2e7d32; width: 20%;"><strong><?php _e('Default', 'syntekpro-animations'); ?></strong></th>
                            <th style="width: 30%;"><strong><?php _e('Example', 'syntekpro-animations'); ?></strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>type</strong></td>
                            <td><?php _e('Animation effect to use', 'syntekpro-animations'); ?></td>
                            <td style="color: #2e7d32;">fadeIn</td>
                            <td><code>type="slideLeft"</code></td>
                        </tr>
                        <tr>
                            <td><strong>duration</strong></td>
                            <td><?php _e('Animation length in seconds', 'syntekpro-animations'); ?></td>
                            <td style="color: #2e7d32;">1</td>
                            <td><code>duration="2"</code></td>
                        </tr>
                        <tr>
                            <td><strong>delay</strong></td>
                            <td><?php _e('Wait before animation starts', 'syntekpro-animations'); ?></td>
                            <td style="color: #2e7d32;">0</td>
                            <td><code>delay="0.5"</code></td>
                        </tr>
                        <tr>
                            <td><strong>trigger</strong></td>
                            <td><?php _e('When animation starts', 'syntekpro-animations'); ?></td>
                            <td style="color: #2e7d32;">scroll</td>
                            <td><code>trigger="load"</code></td>
                        </tr>
                        <tr>
                            <td><strong>ease</strong></td>
                            <td><?php _e('Animation easing function', 'syntekpro-animations'); ?></td>
                            <td style="color: #2e7d32;">power2.out</td>
                            <td><code>ease="bounce.out"</code></td>
                        </tr>
                        <tr>
                            <td><strong>stagger</strong></td>
                            <td><?php _e('Delay between child elements', 'syntekpro-animations'); ?></td>
                            <td style="color: #2e7d32;">0</td>
                            <td><code>stagger="0.1"</code></td>
                        </tr>
                        <tr>
                            <td><strong>repeat</strong></td>
                            <td><?php _e('Number of times to repeat', 'syntekpro-animations'); ?></td>
                            <td style="color: #2e7d32;">0</td>
                            <td><code>repeat="2"</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pro Features -->
            <div class="syntekpro-docs-section">
                <h3 style="color: #e53935; font-size: 1.3em;">🔒 <?php _e('Pro Features', 'syntekpro-animations'); ?></h3>
                
                <h4 style="color: #1565c0; margin-top: 20px;"><?php _e('Text Animation (Pro)', 'syntekpro-animations'); ?></h4>
                <p><?php _e('Animate text character by character, word by word, or line by line:', 'syntekpro-animations'); ?></p>
                <pre style="background: #f5f5f5; border-left: 4px solid #1565c0; padding: 15px; border-radius: 4px; color: #2e7d32;">[sp_text_animate type="chars" effect="fadeIn" duration="0.05" stagger="0.03"]Animated Text[/sp_text_animate]</pre>

                <h4 style="color: #1565c0; margin-top: 20px;"><?php _e('SVG Animation (Pro)', 'syntekpro-animations'); ?></h4>
                <p><?php _e('Draw SVG strokes or morph shapes with professional animations:', 'syntekpro-animations'); ?></p>
                <pre style="background: #f5f5f5; border-left: 4px solid #1565c0; padding: 15px; border-radius: 4px; color: #2e7d32;">[sp_svg_animate type="draw" duration="2"]&lt;svg&gt;...&lt;/svg&gt;[/sp_svg_animate]</pre>

                <h4 style="color: #1565c0; margin-top: 20px;"><?php _e('Timeline Animation (Pro)', 'syntekpro-animations'); ?></h4>
                <p><?php _e('Create complex animation sequences with multiple elements:', 'syntekpro-animations'); ?></p>
                <pre style="background: #f5f5f5; border-left: 4px solid #1565c0; padding: 15px; border-radius: 4px; color: #2e7d32;">[sp_timeline]
    [sp_animate type="fadeIn"]First element[/sp_animate]
    [sp_animate type="slideLeft"]Second element[/sp_animate]
    [sp_animate type="scaleUp"]Third element[/sp_animate]
[/sp_timeline]</pre>
            </div>

            <!-- Code Examples -->
            <div class="syntekpro-docs-section">
                <h3 style="color: #2e7d32; font-size: 1.3em;">💻 <?php _e('Developer Examples', 'syntekpro-animations'); ?></h3>
                <p><?php _e('Enable Developer Mode in Settings to use these custom code examples:', 'syntekpro-animations'); ?></p>
                
                <h4 style="color: #1565c0; margin-top: 20px;"><?php _e('Custom JavaScript Animation', 'syntekpro-animations'); ?></h4>
                <pre style="background: #f5f5f5; border-left: 4px solid #2e7d32; padding: 15px; border-radius: 4px; color: #1565c0;">// Animate an element when page loads using Syntekpro Engine
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
            <div class="syntekpro-docs-section">
                <h3 style="color: #2e7d32; font-size: 1.3em;">📚 <?php _e('Additional Resources', 'syntekpro-animations'); ?></h3>
                <ul style="list-style-type: none; padding: 0;">

                    <li style="margin-bottom: 10px;">
                        <a href="https://syntekpro.com/animations-docs" target="_blank" style="color: #1565c0; text-decoration: none; font-weight: 600;">
                            🎓 Syntekpro Animations Full Docs
                        </a>
                        <p style="margin: 5px 0; color: #666; font-size: 0.9em;"><?php _e('Complete guides and tutorials for using Syntekpro Animations', 'syntekpro-animations'); ?></p>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="https://syntekpro.com/support" target="_blank" style="color: #1565c0; text-decoration: none; font-weight: 600;">
                            🆘 Support Center
                        </a>
                        <p style="margin: 5px 0; color: #666; font-size: 0.9em;"><?php _e('Get help from our support team or view frequently asked questions', 'syntekpro-animations'); ?></p>
                    </li>
                </ul>
            </div>

            <?php if (!syntekpro_animations()->is_pro_active()) : ?>
            <!-- Upgrade CTA -->
            <div style="background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); border: 2px solid #ffc107; border-radius: 8px; padding: 30px; margin-top: 30px; text-align: center;">
                <h3 style="color: #e53935; margin-top: 0;">⭐ <?php _e('Unlock Pro Features', 'syntekpro-animations'); ?></h3>
                <p style="color: #333; font-size: 15px;"><?php _e('Get access to Timeline Builder, Text Effects, SVG Morphing, Draw Effects, and 50+ premium animations.', 'syntekpro-animations'); ?></p>
                <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="background: #e53935; border-color: #e53935; padding: 10px 30px; font-size: 16px;" target="_blank">
                    🚀 <?php _e('Purchase Pro License', 'syntekpro-animations'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Animation Builder Page
     */
    public function builder_page() {
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <!-- Banner -->
            <div class="syntekpro-admin-banner">
                <div class="syntekpro-admin-branding">
                    <img src="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animations%20Transparent%20Logo%20with%20Favicon.png'); ?>" alt="Syntekpro Logo" />
                    <div class="syntekpro-brand-content">
                        <div class="brand-title"><?php _e('Animation Builder', 'syntekpro-animations'); ?></div>
                        <div class="brand-desc"><?php _e('Create custom animations with live preview', 'syntekpro-animations'); ?></div>
                    </div>
                </div>
            </div>

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
        </div>
        <?php
    }
    
    /**
     * Timeline Creator Page
     */
    public function timeline_page() {
        ?>
        <div class="wrap syntekpro-settings-wrapper">
            <!-- Banner -->
            <div class="syntekpro-admin-banner">
                <div class="syntekpro-admin-branding">
                    <img src="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animations%20Transparent%20Logo%20with%20Favicon.png'); ?>" alt="Syntekpro Logo" />
                    <div class="syntekpro-brand-content">
                        <div class="brand-title"><?php _e('Timeline Creator', 'syntekpro-animations'); ?></div>
                        <div class="brand-desc"><?php _e('Create complex animation sequences', 'syntekpro-animations'); ?></div>
                    </div>
                </div>
            </div>

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
            <?php if (!syntekpro_animations()->is_pro_active()) : ?>
            <div style="background:linear-gradient(135deg, #fff3cd 0%, #fffbea 100%);border:2px solid #ffc107;border-radius:8px;padding:25px;margin-top:30px;text-align:center;">
                <h3 style="color:#e53935;margin-top:0;">🔒 <?php _e('Timeline Builder is a Pro Feature', 'syntekpro-animations'); ?></h3>
                <p style="font-size:15px;color:#333;"><?php _e('Upgrade to Pro to save and export your timeline animations.', 'syntekpro-animations'); ?></p>
                <a href="https://syntekpro.com/animations-pro" class="button button-primary" style="background:#e53935;border-color:#e53935;padding:10px 30px;font-size:16px;" target="_blank">
                    ⭐ <?php _e('Upgrade to Pro', 'syntekpro-animations'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

// Initialize
new Syntekpro_Animations_Admin();