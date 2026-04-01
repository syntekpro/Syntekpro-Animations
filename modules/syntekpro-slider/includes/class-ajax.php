<?php
defined( 'ABSPATH' ) || exit;

/**
 * All WP AJAX handlers (admin-side editor saves + frontend analytics).
 */
class SPSLIDER_Ajax {

    /**
     * Return an associative [ action => callable ] map for the core to register.
     * All actions that need auth are wrapped with nonce + capability checks.
     */
    public function get_actions() {
        return [
            // Editor (requires manage_options)
            'spslider_save'               => [ $this, 'handle_save' ],
            'spslider_load'               => [ $this, 'handle_load' ],
            'spslider_create_slider'      => [ $this, 'handle_create_slider' ],
            'spslider_delete_slider'      => [ $this, 'handle_delete_slider' ],
            'spslider_duplicate_slider'   => [ $this, 'handle_duplicate_slider' ],
            'spslider_get_templates'      => [ $this, 'handle_get_templates' ],
            'spslider_import_template'    => [ $this, 'handle_import_template' ],
            'spslider_dynamic_sources'    => [ $this, 'handle_dynamic_sources' ],
            'spslider_dynamic_import'     => [ $this, 'handle_dynamic_import' ],
            'spslider_export_csv'         => [ $this, 'handle_export_csv' ],
            'spslider_upload_image'       => [ $this, 'handle_upload_image' ],
            'spslider_save_global'        => [ $this, 'handle_save_global' ],

            // Revisions
            'spslider_get_revisions'      => [ $this, 'handle_get_revisions' ],
            'spslider_restore_revision'   => [ $this, 'handle_restore_revision' ],
            'spslider_diff_revisions'     => [ $this, 'handle_diff_revisions' ],

            // Webhooks
            'spslider_get_webhooks'       => [ $this, 'handle_get_webhooks' ],
            'spslider_save_webhooks'      => [ $this, 'handle_save_webhooks' ],

            // White-label
            'spslider_get_white_label'    => [ $this, 'handle_get_white_label' ],
            'spslider_save_white_label'   => [ $this, 'handle_save_white_label' ],

            // Permissions
            'spslider_get_permissions'    => [ $this, 'handle_get_permissions' ],
            'spslider_save_permissions'   => [ $this, 'handle_save_permissions' ],

            // Export / Import
            'spslider_export_json'        => [ $this, 'handle_export_json' ],
            'spslider_export_all'         => [ $this, 'handle_export_all' ],
            'spslider_import_json'        => [ $this, 'handle_import_json' ],

            // Scheduler
            'spslider_schedule_slide'     => [ $this, 'handle_schedule_slide' ],

            // A/B Testing
            'spslider_ab_create'          => [ $this, 'handle_ab_create' ],
            'spslider_ab_list'            => [ $this, 'handle_ab_list' ],
            'spslider_ab_delete'          => [ $this, 'handle_ab_delete' ],
            'spslider_ab_results'         => [ $this, 'handle_ab_results' ],

            // Audit log
            'spslider_get_audit_log'      => [ $this, 'handle_get_audit_log' ],

            // Conversions
            'spslider_get_conversions'    => [ $this, 'handle_get_conversions' ],

            // Cache management
            'spslider_flush_cache'        => [ $this, 'handle_flush_cache' ],

            // Frontend (no auth required)
            'spslider_track'              => [ $this, 'handle_track' ],
            'spslider_convert'            => [ $this, 'handle_convert' ],
        ];
    }

    // ── Auth helpers ──────────────────────────────────────────────────────────

    private function verify_admin() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized', 'syntekpro-slider' ) ], 403 );
        }
        check_ajax_referer( 'spslider_nonce', 'nonce' );
    }

    private function verify_public() {
        check_ajax_referer( 'spslider_public_nonce', 'nonce' );
    }

    // ── Editor handlers ───────────────────────────────────────────────────────

    public function handle_save() {
        $this->verify_admin();

        $slider_id       = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        $slider_settings = isset( $_POST['slider_settings'] ) ? json_decode( wp_unslash( $_POST['slider_settings'] ), true ) : [];
        $slides_json     = isset( $_POST['slides'] )          ? json_decode( wp_unslash( $_POST['slides'] ),          true ) : [];

        if ( ! $slider_id ) wp_send_json_error( [ 'message' => 'Missing slider ID' ] );

        $ok = SPSLIDER_Database::save_full_slider( $slider_id, $slider_settings, $slides_json );
        $ok ? wp_send_json_success( [ 'message' => __( 'Slider saved.', 'syntekpro-slider' ) ] )
            : wp_send_json_error(   [ 'message' => __( 'Save failed.',  'syntekpro-slider' ) ] );
    }

    public function handle_load() {
        $this->verify_admin();
        $slider_id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        if ( ! $slider_id ) wp_send_json_error( [ 'message' => 'Missing slider ID' ] );

        $data = SPSLIDER_Database::load_full_slider( $slider_id );
        $data ? wp_send_json_success( $data ) : wp_send_json_error( [ 'message' => 'Slider not found' ] );
    }

    public function handle_create_slider() {
        $this->verify_admin();
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ?? 'New Slider' ) );
        $id   = SPSLIDER_Database::create_slider( [ 'name' => $name ] );
        if ( $id ) {
            wp_send_json_success( [
                'id'         => $id,
                'name'       => $name,
                'editor_url' => admin_url( 'admin.php?page=spslider-editor&slider_id=' . $id ),
            ] );
        } else {
            wp_send_json_error( [ 'message' => 'Could not create slider' ] );
        }
    }

    public function handle_delete_slider() {
        $this->verify_admin();
        $id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        if ( ! $id ) wp_send_json_error();
        SPSLIDER_Database::delete_slider( $id );
        wp_send_json_success();
    }

    public function handle_duplicate_slider() {
        $this->verify_admin();
        $id     = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        $new_id = SPSLIDER_Database::duplicate_slider( $id );
        $new_id ? wp_send_json_success( [ 'id' => $new_id ] )
                : wp_send_json_error( [ 'message' => 'Duplication failed' ] );
    }

    public function handle_get_templates() {
        $this->verify_admin();
        $category = sanitize_key( wp_unslash( $_POST['category'] ?? '' ) );
        $data     = $category ? SPSLIDER_Templates::get_by_category( $category ) : SPSLIDER_Templates::get_all();
        wp_send_json_success( $data );
    }

    public function handle_import_template() {
        $this->verify_admin();
        $slider_id   = isset( $_POST['slider_id'] )   ? (int) $_POST['slider_id'] : 0;
        $template_id = sanitize_key( wp_unslash( $_POST['template_id'] ?? '' ) );
        $slide_id    = SPSLIDER_Templates::import( $slider_id, $template_id );
        $slide_id    ? wp_send_json_success( [ 'slide_id' => $slide_id ] )
                     : wp_send_json_error( [ 'message' => 'Template not found' ] );
    }

    public function handle_dynamic_sources() {
        $this->verify_admin();
        wp_send_json_success( [
            'sources'    => SPSLIDER_Dynamic_Content::get_sources(),
            'post_types' => SPSLIDER_Dynamic_Content::get_post_types(),
        ] );
    }

    public function handle_dynamic_import() {
        $this->verify_admin();
        $slider_id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        $config    = isset( $_POST['config'] ) ? json_decode( wp_unslash( $_POST['config'] ), true ) : [];
        if ( ! $slider_id || ! $config ) wp_send_json_error();

        $slides = SPSLIDER_Dynamic_Content::build_slides( $config );
        if ( empty( $slides ) ) {
            wp_send_json_error( [ 'message' => 'No posts found for the given criteria.' ] );
        }

        $created = [];
        foreach ( $slides as $slide ) {
            $layers = $slide['layers'] ?? [];
            unset( $slide['layers'] );
            $slide_id = SPSLIDER_Database::create_slide( array_merge( $slide, [ 'slider_id' => $slider_id ] ) );
            if ( $slide_id ) {
                foreach ( $layers as $layer ) {
                    SPSLIDER_Database::create_layer( array_merge( $layer, [ 'slide_id' => $slide_id ] ) );
                }
                $created[] = $slide_id;
            }
        }
        wp_send_json_success( [ 'created' => count( $created ) ] );
    }

    public function handle_export_csv() {
        $this->verify_admin();
        $slider_id = isset( $_GET['slider_id'] ) ? (int) $_GET['slider_id'] : 0;
        $days      = isset( $_GET['days'] )       ? (int) $_GET['days']      : 30;
        if ( ! $slider_id ) wp_die( 'Missing slider ID' );
        // export_csv() calls exit()
        SPSLIDER_Analytics::export_csv( $slider_id, $days );
    }

    public function handle_upload_image() {
        $this->verify_admin();
        if ( ! function_exists( 'wp_handle_upload' ) ) require_once ABSPATH . 'wp-admin/includes/file.php';
        if ( ! function_exists( 'media_handle_upload' ) ) require_once ABSPATH . 'wp-admin/includes/media.php';
        if ( ! function_exists( 'wp_read_image_metadata' ) ) require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload( 'file', 0 );
        if ( is_wp_error( $attachment_id ) ) {
            wp_send_json_error( [ 'message' => $attachment_id->get_error_message() ] );
        }
        wp_send_json_success( [
            'id'  => $attachment_id,
            'url' => wp_get_attachment_url( $attachment_id ),
        ] );
    }

    public function handle_save_global() {
        $this->verify_admin();
        $raw      = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : [];
        $settings = [
            'analytics_enabled'  => ! empty( $raw['analytics_enabled'] ),
            'lazy_load'          => ! empty( $raw['lazy_load'] ),
            'optimize_assets'    => ! empty( $raw['optimize_assets'] ),
            'generate_webp'      => ! empty( $raw['generate_webp'] ),
            'generate_avif'      => ! empty( $raw['generate_avif'] ),
            'enable_cache'       => ! empty( $raw['enable_cache'] ),
            'enable_revisions'   => ! empty( $raw['enable_revisions'] ),
            'enable_audit_log'   => ! empty( $raw['enable_audit_log'] ),
            'ga4_measurement_id' => sanitize_text_field( $raw['ga4_measurement_id'] ?? '' ),
            'ga4_api_secret'     => sanitize_text_field( $raw['ga4_api_secret'] ?? '' ),
        ];
        update_option( 'spslider_global_settings', $settings );
        wp_send_json_success();
    }

    // ── Revisions handlers ───────────────────────────────────────────────────

    public function handle_get_revisions() {
        $this->verify_admin();
        $slider_id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        if ( ! $slider_id ) wp_send_json_error();
        wp_send_json_success( SPSLIDER_Revisions::get_revisions( $slider_id ) );
    }

    public function handle_restore_revision() {
        $this->verify_admin();
        $revision_id = isset( $_POST['revision_id'] ) ? (int) $_POST['revision_id'] : 0;
        if ( ! $revision_id ) wp_send_json_error();
        $ok = SPSLIDER_Revisions::restore( $revision_id );
        $ok ? wp_send_json_success() : wp_send_json_error( [ 'message' => 'Restore failed' ] );
    }

    public function handle_diff_revisions() {
        $this->verify_admin();
        $a = isset( $_POST['revision_a'] ) ? (int) $_POST['revision_a'] : 0;
        $b = isset( $_POST['revision_b'] ) ? (int) $_POST['revision_b'] : 0;
        if ( ! $a || ! $b ) wp_send_json_error();
        wp_send_json_success( SPSLIDER_Revisions::diff( $a, $b ) );
    }

    // ── Webhooks handlers ─────────────────────────────────────────────────────

    public function handle_get_webhooks() {
        $this->verify_admin();
        wp_send_json_success( SPSLIDER_Webhooks::get_webhooks() );
    }

    public function handle_save_webhooks() {
        $this->verify_admin();
        $hooks = isset( $_POST['webhooks'] ) ? json_decode( wp_unslash( $_POST['webhooks'] ), true ) : [];
        if ( ! is_array( $hooks ) ) wp_send_json_error();
        SPSLIDER_Webhooks::save_webhooks( $hooks );
        wp_send_json_success();
    }

    // ── White-label handlers ──────────────────────────────────────────────────

    public function handle_get_white_label() {
        $this->verify_admin();
        wp_send_json_success( SPSLIDER_White_Label::get_settings() );
    }

    public function handle_save_white_label() {
        $this->verify_admin();
        $raw = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : [];
        if ( ! is_array( $raw ) ) wp_send_json_error();
        SPSLIDER_White_Label::save( $raw );
        wp_send_json_success();
    }

    // ── Permissions handlers ──────────────────────────────────────────────────

    public function handle_get_permissions() {
        $this->verify_admin();
        wp_send_json_success( SPSLIDER_Permissions::get_map() );
    }

    public function handle_save_permissions() {
        $this->verify_admin();
        $map = isset( $_POST['permissions'] ) ? json_decode( wp_unslash( $_POST['permissions'] ), true ) : [];
        if ( ! is_array( $map ) ) wp_send_json_error();
        SPSLIDER_Permissions::save( $map );
        wp_send_json_success();
    }

    // ── Export / Import handlers ──────────────────────────────────────────────

    public function handle_export_json() {
        $this->verify_admin();
        $slider_id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        if ( ! $slider_id ) wp_send_json_error();
        $data = SPSLIDER_Export::export_json( $slider_id );
        $data ? wp_send_json_success( $data ) : wp_send_json_error( [ 'message' => 'Slider not found' ] );
    }

    public function handle_export_all() {
        $this->verify_admin();
        wp_send_json_success( SPSLIDER_Export::export_all_json() );
    }

    public function handle_import_json() {
        $this->verify_admin();
        if ( empty( $_FILES['file']['tmp_name'] ) ) {
            wp_send_json_error( [ 'message' => 'No file uploaded' ] );
        }
        $result = SPSLIDER_Export::import_json( $_FILES['file']['tmp_name'] );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }
        wp_send_json_success( $result );
    }

    // ── Scheduler handler ─────────────────────────────────────────────────────

    public function handle_schedule_slide() {
        $this->verify_admin();
        $slide_id   = isset( $_POST['slide_id'] )   ? (int) $_POST['slide_id'] : 0;
        $publish_at = isset( $_POST['publish_at'] )  ? sanitize_text_field( wp_unslash( $_POST['publish_at'] ) )  : '';
        $expire_at  = isset( $_POST['expire_at'] )   ? sanitize_text_field( wp_unslash( $_POST['expire_at'] ) )   : '';
        if ( ! $slide_id ) wp_send_json_error();

        if ( $publish_at ) {
            SPSLIDER_Scheduler::schedule_publish( $slide_id, $publish_at );
        }
        if ( $expire_at ) {
            SPSLIDER_Scheduler::schedule_expire( $slide_id, $expire_at );
        }
        if ( ! $publish_at && ! $expire_at ) {
            SPSLIDER_Scheduler::clear_schedule( $slide_id );
        }
        wp_send_json_success( SPSLIDER_Scheduler::get_schedule( $slide_id ) );
    }

    // ── A/B Testing handlers ──────────────────────────────────────────────────

    public function handle_ab_create() {
        $this->verify_admin();
        $name       = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
        $slider_a   = isset( $_POST['slider_a'] ) ? (int) $_POST['slider_a'] : 0;
        $slider_b   = isset( $_POST['slider_b'] ) ? (int) $_POST['slider_b'] : 0;
        if ( ! $name || ! $slider_a || ! $slider_b ) wp_send_json_error();
        $test = SPSLIDER_AB_Test::create_test( $name, $slider_a, $slider_b );
        wp_send_json_success( $test );
    }

    public function handle_ab_list() {
        $this->verify_admin();
        wp_send_json_success( SPSLIDER_AB_Test::get_tests() );
    }

    public function handle_ab_delete() {
        $this->verify_admin();
        $test_id = isset( $_POST['test_id'] ) ? (int) $_POST['test_id'] : 0;
        if ( ! $test_id ) wp_send_json_error();
        SPSLIDER_AB_Test::delete_test( $test_id );
        wp_send_json_success();
    }

    public function handle_ab_results() {
        $this->verify_admin();
        $test_id = isset( $_POST['test_id'] ) ? (int) $_POST['test_id'] : 0;
        if ( ! $test_id ) wp_send_json_error();
        wp_send_json_success( SPSLIDER_AB_Test::get_results( $test_id ) );
    }

    // ── Audit log handler ─────────────────────────────────────────────────────

    public function handle_get_audit_log() {
        $this->verify_admin();
        $args = [
            'per_page' => isset( $_POST['per_page'] ) ? (int) $_POST['per_page'] : 50,
            'page'     => isset( $_POST['page'] )     ? (int) $_POST['page']     : 1,
        ];
        if ( ! empty( $_POST['action_type'] ) ) {
            $args['action'] = sanitize_key( wp_unslash( $_POST['action_type'] ) );
        }
        if ( ! empty( $_POST['user_id'] ) ) {
            $args['user_id'] = (int) $_POST['user_id'];
        }
        wp_send_json_success( SPSLIDER_Audit_Log::get_entries( $args ) );
    }

    // ── Conversions handler ───────────────────────────────────────────────────

    public function handle_get_conversions() {
        $this->verify_admin();
        $slider_id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        $days      = isset( $_POST['days'] )       ? (int) $_POST['days']      : 30;
        if ( ! $slider_id ) wp_send_json_error();
        wp_send_json_success( SPSLIDER_Conversions::get_stats( $slider_id, $days ) );
    }

    // ── Cache management handler ──────────────────────────────────────────────

    public function handle_flush_cache() {
        $this->verify_admin();
        $slider_id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        if ( $slider_id ) {
            SPSLIDER_Cache::flush( $slider_id );
        } else {
            SPSLIDER_Cache::flush_all();
        }
        wp_send_json_success();
    }

    // ── Public / frontend handlers ────────────────────────────────────────────

    public function handle_track() {
        $this->verify_public();
        $slider_id  = isset( $_POST['slider_id'] )  ? (int) $_POST['slider_id']                       : 0;
        $slide_id   = isset( $_POST['slide_id'] )   ? (int) $_POST['slide_id']                        : null;
        $layer_id   = isset( $_POST['layer_id'] )   ? (int) $_POST['layer_id']                        : null;
        $event_type = isset( $_POST['event_type'] ) ? sanitize_key( wp_unslash( $_POST['event_type'] ) ) : '';
        $meta       = isset( $_POST['meta'] )       ? json_decode( wp_unslash( $_POST['meta'] ), true ) : [];

        if ( ! $slider_id || ! $event_type ) wp_send_json_error();

        SPSLIDER_Analytics::track( $slider_id, $event_type, $slide_id, $layer_id, $meta );
        wp_send_json_success();
    }

    public function handle_convert() {
        $this->verify_public();
        $slider_id = isset( $_POST['slider_id'] ) ? (int) $_POST['slider_id'] : 0;
        $slide_id  = isset( $_POST['slide_id'] )  ? (int) $_POST['slide_id']  : 0;
        $layer_id  = isset( $_POST['layer_id'] )  ? (int) $_POST['layer_id']  : 0;
        $goal      = isset( $_POST['goal'] )       ? sanitize_key( wp_unslash( $_POST['goal'] ) ) : 'click';
        if ( ! $slider_id ) wp_send_json_error();
        SPSLIDER_Conversions::record( $slider_id, $slide_id, $layer_id, $goal );
        wp_send_json_success();
    }
}
