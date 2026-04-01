<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin-area: menus, pages, script enqueuing.
 */
class SPSLIDER_Admin {

    public function print_icon_style() {
        echo '<style>#adminmenu .toplevel_page_syntekpro-slider .wp-menu-image{display:flex!important;align-items:center!important;justify-content:center!important;overflow:hidden!important;}#adminmenu .toplevel_page_syntekpro-slider .wp-menu-image img{display:block!important;width:22px!important;height:22px!important;max-width:22px!important;max-height:22px!important;min-width:22px!important;min-height:22px!important;object-fit:contain!important;margin:0!important;padding:0!important;}</style>';
    }

    public function add_menu_pages() {
        add_menu_page(
            __( 'SyntekPro Slider', 'syntekpro-slider' ),
            __( 'SyntekPro Slider', 'syntekpro-slider' ),
            'manage_options',
            'syntekpro-slider',
            [ $this, 'page_sliders' ],
            SPSLIDER_URL . 'assets/img/SyntekPro Slider Icon Grey for wordpress.png',
            58
        );
        add_submenu_page( 'syntekpro-slider', __( 'All Sliders',      'syntekpro-slider' ), __( 'All Sliders',      'syntekpro-slider' ), 'manage_options', 'syntekpro-slider',         [ $this, 'page_sliders' ] );
        add_submenu_page( 'syntekpro-slider', __( 'Slider Editor',    'syntekpro-slider' ), __( 'Editor',           'syntekpro-slider' ), 'manage_options', 'spslider-editor',           [ $this, 'page_editor' ] );
        add_submenu_page( 'syntekpro-slider', __( 'Templates',        'syntekpro-slider' ), __( 'Templates',        'syntekpro-slider' ), 'manage_options', 'spslider-templates',        [ $this, 'page_templates' ] );
        add_submenu_page( 'syntekpro-slider', __( 'Global Settings',  'syntekpro-slider' ), __( 'Settings',         'syntekpro-slider' ), 'manage_options', 'spslider-settings',         [ $this, 'page_settings' ] );
        add_submenu_page( 'syntekpro-slider', __( 'Analytics',        'syntekpro-slider' ), __( 'Analytics',        'syntekpro-slider' ), 'manage_options', 'spslider-analytics',        [ $this, 'page_analytics' ] );
        add_submenu_page( 'syntekpro-slider', __( 'About',            'syntekpro-slider' ), __( 'About',            'syntekpro-slider' ), 'manage_options', 'spslider-about',            [ $this, 'page_about' ] );
    }

    public function enqueue_scripts( $hook ) {
        // Match our plugin pages reliably regardless of WP hook-name generation.
        $is_our_page = (
            $hook === 'toplevel_page_syntekpro-slider'
            || strpos( $hook, '_page_spslider-' ) !== false
            || strpos( $hook, '_page_syntekpro-slider' ) !== false
        );
        if ( ! $is_our_page ) return;

        wp_enqueue_media();

        // Admin list page
        wp_enqueue_style(  'spslider-admin', SPSLIDER_URL . 'admin/css/admin.css', [], SPSLIDER_VERSION );
        wp_enqueue_script( 'spslider-admin', SPSLIDER_URL . 'admin/js/admin.js',   [ 'jquery' ], SPSLIDER_VERSION, true );
        wp_localize_script( 'spslider-admin', 'SPSLIDER_ADMIN', [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'rest_url'  => esc_url_raw( rest_url( 'syntekpro-slider/v1' ) ),
            'nonce'     => wp_create_nonce( 'spslider_nonce' ),
            'rest_nonce'=> wp_create_nonce( 'wp_rest' ),
            'edit_url'  => admin_url( 'admin.php?page=spslider-editor&slider_id=' ),
            'delete_confirm' => __( 'Are you sure you want to delete this slider?', 'syntekpro-slider' ),
            'i18n' => [
                'saving'         => __( 'Saving…',  'syntekpro-slider' ),
                'saved'          => __( 'Saved!',   'syntekpro-slider' ),
                'error'          => __( 'Error saving', 'syntekpro-slider' ),
                'undo'           => __( 'Undo',     'syntekpro-slider' ),
                'redo'           => __( 'Redo',     'syntekpro-slider' ),
                'creating'       => __( 'Creating…', 'syntekpro-slider' ),
                'create'         => __( 'Create',    'syntekpro-slider' ),
                'confirm_delete' => __( 'Delete slider "{name}"?', 'syntekpro-slider' ),
                'error_generic'  => __( 'Something went wrong. Please try again.', 'syntekpro-slider' ),
                'copied'         => __( 'Copied!',   'syntekpro-slider' ),
            ],
        ] );

        // Editor page — additional assets
        if ( strpos( $hook, '_page_spslider-editor' ) !== false || $hook === 'toplevel_page_spslider-editor' ) {
            wp_enqueue_style(  'spslider-editor', SPSLIDER_URL . 'admin/css/editor.css', [ 'spslider-admin' ], SPSLIDER_VERSION );
            wp_enqueue_script( 'spslider-editor', SPSLIDER_URL . 'admin/js/editor.js',   [ 'jquery', 'wp-util', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-resizable' ], SPSLIDER_VERSION, true );
            wp_localize_script( 'spslider-editor', 'SPSLIDER_EDITOR', [
                'ajax_url'    => admin_url( 'admin-ajax.php' ),
                'nonce'       => wp_create_nonce( 'spslider_nonce' ),
                'rest_url'    => esc_url_raw( rest_url( 'syntekpro-slider/v1' ) ),
                'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
                'upload_url'  => admin_url( 'admin-ajax.php?action=spslider_upload_image&nonce=' . wp_create_nonce( 'spslider_nonce' ) ),
                'slider_id'   => isset( $_GET['slider_id'] ) ? (int) $_GET['slider_id'] : 0,
                'plugin_url'  => SPSLIDER_URL,
                'transitions' => apply_filters( 'spslider_transitions', [
                    'slide'     => [ 'label' => 'Slide',         'css' => 'spslider-trans-slide' ],
                    'fade'      => [ 'label' => 'Fade',          'css' => 'spslider-trans-fade' ],
                    'zoom'      => [ 'label' => 'Zoom',          'css' => 'spslider-trans-zoom' ],
                    'crossfade' => [ 'label' => 'Crossfade',     'css' => 'spslider-trans-crossfade' ],
                    'parallax'  => [ 'label' => 'Parallax',      'css' => 'spslider-trans-parallax' ],
                    'kenburns'  => [ 'label' => 'Ken Burns',     'css' => 'spslider-trans-kenburns' ],
                    'cube3d'    => [ 'label' => '3D Cube',       'css' => 'spslider-trans-cube3d' ],
                    'flip'      => [ 'label' => 'Flip',          'css' => 'spslider-trans-flip' ],
                    'custom'    => [ 'label' => 'Custom CSS',    'css' => 'spslider-trans-custom' ],
                ] ),
                'layer_types' => [
                    'text'      => __( 'Text',      'syntekpro-slider' ),
                    'image'     => __( 'Image',     'syntekpro-slider' ),
                    'button'    => __( 'Button',    'syntekpro-slider' ),
                    'video'     => __( 'Video',     'syntekpro-slider' ),
                    'shape'     => __( 'Shape',     'syntekpro-slider' ),
                    'countdown' => __( 'Countdown', 'syntekpro-slider' ),
                    'icon'      => __( 'Icon',      'syntekpro-slider' ),
                    'lottie'    => __( 'Lottie',    'syntekpro-slider' ),
                    'html'      => __( 'HTML',      'syntekpro-slider' ),
                ],
                'animations' => [
                    'none'       => 'None',
                    'fade'       => 'Fade',
                    'slide-left' => 'Slide Left',
                    'slide-right'=> 'Slide Right',
                    'slide-up'   => 'Slide Up',
                    'slide-down' => 'Slide Down',
                    'zoom'       => 'Zoom In',
                    'zoom-out'   => 'Zoom Out',
                    'rotate'     => 'Rotate',
                    'flip-x'     => 'Flip X',
                    'flip-y'     => 'Flip Y',
                    'bounce'     => 'Bounce',
                ],
                'easings' => [
                    'ease'        => 'Ease',
                    'ease-in'     => 'Ease In',
                    'ease-out'    => 'Ease Out',
                    'ease-in-out' => 'Ease In Out',
                    'linear'      => 'Linear',
                    'cubic-bezier(0.34,1.56,0.64,1)' => 'Spring',
                ],
                'i18n' => [
                    'saving'           => __( 'Saving…', 'syntekpro-slider' ),
                    'saved'            => __( 'Saved!',  'syntekpro-slider' ),
                    'unsaved_changes'  => __( 'You have unsaved changes. Leave page?', 'syntekpro-slider' ),
                    'add_slide'        => __( 'Add Slide', 'syntekpro-slider' ),
                    'delete_slide'     => __( 'Delete this slide?', 'syntekpro-slider' ),
                    'add_layer'        => __( 'Add Layer', 'syntekpro-slider' ),
                    'delete_layer'     => __( 'Delete this layer?', 'syntekpro-slider' ),
                    'no_layers'        => __( 'No layers yet. Click "Add Layer" to begin.', 'syntekpro-slider' ),
                    'select_layer'     => __( 'Select a layer to edit its properties.', 'syntekpro-slider' ),
                    'desktop'          => __( 'Desktop', 'syntekpro-slider' ),
                    'tablet'           => __( 'Tablet', 'syntekpro-slider' ),
                    'mobile'           => __( 'Mobile', 'syntekpro-slider' ),
                    'preview'          => __( 'Preview', 'syntekpro-slider' ),
                    'template_library' => __( 'Template Library', 'syntekpro-slider' ),
                    'global_settings'  => __( 'Slider Settings', 'syntekpro-slider' ),
                    'dynamic_content'  => __( 'Dynamic Content', 'syntekpro-slider' ),
                    'timeline'         => __( 'Animation Timeline', 'syntekpro-slider' ),
                ],
            ] );
        }
    }

    public function page_sliders() {
        require_once SPSLIDER_DIR . 'admin/partials/admin-display.php';
    }

    public function page_editor() {
        $slider_id = isset( $_GET['slider_id'] ) ? (int) $_GET['slider_id'] : 0;
        if ( $slider_id ) {
            require_once SPSLIDER_DIR . 'admin/partials/editor-display.php';
        } else {
            wp_safe_redirect( admin_url( 'admin.php?page=syntekpro-slider' ) );
            exit;
        }
    }

    public function page_settings() {
        require_once SPSLIDER_DIR . 'admin/partials/settings-display.php';
    }

    public function page_templates() {
        require_once SPSLIDER_DIR . 'admin/partials/templates-display.php';
    }

    public function page_analytics() {
        require_once SPSLIDER_DIR . 'admin/partials/analytics-display.php';
    }

    public function page_about() {
        require_once SPSLIDER_DIR . 'admin/partials/about-display.php';
    }

    public function plugin_action_links( $links ) {
        $custom = [
            '<a href="' . esc_url( admin_url( 'admin.php?page=syntekpro-slider' ) ) . '">' . __( 'Sliders', 'syntekpro-slider' ) . '</a>',
            '<a href="' . esc_url( admin_url( 'admin.php?page=spslider-settings' ) ) . '">' . __( 'Settings', 'syntekpro-slider' ) . '</a>',
        ];
        return array_merge( $custom, $links );
    }
}
