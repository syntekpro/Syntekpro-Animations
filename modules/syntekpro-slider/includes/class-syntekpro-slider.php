<?php
defined( 'ABSPATH' ) || exit;

/**
 * Core plugin loader — registers all hooks, admin, and public integrations.
 */
class SPSLIDER_Core {

    private $admin;
    private $public;

    public function run() {
        $this->load_includes();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
        $this->init_updater();
        $this->init_features();
    }

    private function load_includes() {
        if ( is_admin() ) {
            require_once SPSLIDER_DIR . 'admin/class-admin.php';
            $this->admin = new SPSLIDER_Admin();
        }
        require_once SPSLIDER_DIR . 'public/class-public.php';
        $this->public = new SPSLIDER_Public();
    }

    private function define_admin_hooks() {
        if ( ! $this->admin ) return;
        add_action( 'admin_menu',            [ $this->admin, 'add_menu_pages' ] );
        add_action( 'admin_enqueue_scripts', [ $this->admin, 'enqueue_scripts' ] );
        add_action( 'admin_head',            [ $this->admin, 'print_icon_style' ] );
        add_filter( 'plugin_action_links_' . SPSLIDER_BASENAME, [ $this->admin, 'plugin_action_links' ] );

        // AJAX hooks (admin + frontend for logged-in users)
        $ajax = new SPSLIDER_Ajax();
        foreach ( $ajax->get_actions() as $action => $callback ) {
            add_action( 'wp_ajax_' . $action,        $callback );
            add_action( 'wp_ajax_nopriv_' . $action, $callback );
        }
    }

    private function define_public_hooks() {
        add_action( 'wp_enqueue_scripts', [ $this->public, 'enqueue_scripts' ] );

        $shortcode = new SPSLIDER_Shortcode();
        add_shortcode( 'syntekpro_slider',  [ $shortcode, 'render' ] );
        add_shortcode( 'spslider',          [ $shortcode, 'render' ] );

        // Block editor support
        add_action( 'init', [ $this, 'register_block' ] );
    }

    private function define_api_hooks() {
        add_action( 'rest_api_init', [ new SPSLIDER_API(), 'register_routes' ] );

        // Developer action hooks
        do_action( 'spslider_loaded' );
    }

    /**
     * Self-hosted update checker — checks GitHub releases.
     */
    private function init_updater() {
        if ( ! is_admin() ) return;
        $updater = new SPSLIDER_Updater( SPSLIDER_FILE, SPSLIDER_VERSION );
        $updater->init();
    }

    /**
     * Initialize all feature modules.
     */
    private function init_features() {
        SPSLIDER_Cache::init();
        SPSLIDER_Webhooks::init();
        SPSLIDER_White_Label::init();
        SPSLIDER_Audit_Log::init();
        SPSLIDER_Scheduler::init();
        SPSLIDER_Permissions::init();

        // Conversion URL tracking on frontend
        if ( ! is_admin() ) {
            add_action( 'wp', [ 'SPSLIDER_Conversions', 'check_page_goals' ] );
        }

        // Save revision on slider save
        add_action( 'spslider_after_save', [ 'SPSLIDER_Revisions', 'save_revision' ] );

        // Delete revisions when slider is deleted
        add_action( 'spslider_deleted', [ 'SPSLIDER_Revisions', 'delete_for_slider' ] );

        // Weekly analytics prune cron
        if ( ! wp_next_scheduled( 'spslider_weekly_prune' ) ) {
            wp_schedule_event( time(), 'weekly', 'spslider_weekly_prune' );
        }
        add_action( 'spslider_weekly_prune', function () {
            SPSLIDER_Analytics::prune( 365 );
            SPSLIDER_Audit_Log::prune( 365 );
        } );
    }

    public function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) return;

        wp_register_script(
            'spslider-block',
            SPSLIDER_URL . 'admin/js/block.js',
            [ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch', 'wp-server-side-render' ],
            SPSLIDER_VERSION,
            true
        );

        wp_localize_script( 'spslider-block', 'SPSLIDER_BLOCK', [
            'edit_url' => admin_url( 'admin.php?page=spslider-editor&slider_id=' ),
        ] );

        wp_register_style(
            'spslider-block-editor',
            SPSLIDER_URL . 'admin/css/block.css',
            [ 'wp-edit-blocks' ],
            SPSLIDER_VERSION
        );

        register_block_type( 'syntekpro/slider', [
            'editor_script'   => 'spslider-block',
            'editor_style'    => 'spslider-block-editor',
            'render_callback' => [ new SPSLIDER_Shortcode(), 'render_block' ],
            'attributes'      => [
                'sliderId'      => [ 'type' => 'number',  'default' => 0 ],
                'autoplay'      => [ 'type' => 'string',  'default' => '' ],
                'autoplaySpeed' => [ 'type' => 'number',  'default' => 0 ],
                'loop'          => [ 'type' => 'string',  'default' => '' ],
                'arrows'        => [ 'type' => 'string',  'default' => '' ],
                'dots'          => [ 'type' => 'string',  'default' => '' ],
                'transition'    => [ 'type' => 'string',  'default' => '' ],
                'speed'         => [ 'type' => 'number',  'default' => 0 ],
                'pauseOnHover'  => [ 'type' => 'string',  'default' => '' ],
                'touch'         => [ 'type' => 'string',  'default' => '' ],
                'keyboardNav'   => [ 'type' => 'string',  'default' => '' ],
                'lazyLoad'      => [ 'type' => 'string',  'default' => '' ],
                'parallax'      => [ 'type' => 'string',  'default' => '' ],
                'scalingMode'   => [ 'type' => 'string',  'default' => '' ],
                'width'         => [ 'type' => 'number',  'default' => 0 ],
                'height'        => [ 'type' => 'number',  'default' => 0 ],
                'cssClass'      => [ 'type' => 'string',  'default' => '' ],
            ],
        ] );
    }
}
