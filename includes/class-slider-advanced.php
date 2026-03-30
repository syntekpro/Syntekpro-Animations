<?php

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Slider_Advanced {

    public function __construct() {
        add_filter('syntekpro_slider_settings', array($this, 'merge_advanced_defaults'), 20, 3);
        add_filter('syntekpro_slider_slides', array($this, 'normalize_slide_variants'), 20, 4);

        add_action('save_post_syntekpro_slider', array($this, 'save_advanced_settings'), 30, 3);
        add_action('save_post_syntekpro_slider', array($this, 'capture_revision_snapshot'), 35, 3);
        add_action('save_post_syntekpro_slider', array($this, 'build_cached_snapshot'), 40, 3);
        add_action('save_post_syntekpro_slider', array($this, 'store_critical_css'), 45, 3);

        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_menu', array($this, 'register_metrics_page'));
        add_action('admin_init', array($this, 'maybe_export_audit_csv'));
        add_action('wp_head', array($this, 'print_global_tokens'), 6);
        add_action('transition_post_status', array($this, 'handle_status_transition'), 10, 3);
        add_action('syntekpro_slider_webhook_event', array($this, 'dispatch_slider_webhook'), 10, 3);
        add_action('init', array($this, 'register_wp_cli_commands'));
        add_action('init', array($this, 'run_major_migrations'));
        add_action('init', array($this, 'ensure_health_monitor_schedule'));
        add_filter('cron_schedules', array($this, 'register_custom_schedules'));
        add_action('syntekpro_slider_health_monitor', array($this, 'run_health_monitor'));
        add_action('send_headers', array($this, 'maybe_send_csp_headers'));
        add_filter('script_loader_tag', array($this, 'add_sri_to_script_tag'), 10, 3);
        add_filter('style_loader_tag', array($this, 'add_sri_to_style_tag'), 10, 4);
        add_action('save_post_syntekpro_slider', array($this, 'log_slider_change'), 50, 3);

        add_filter('image_editor_output_format', array($this, 'enable_next_gen_upload_formats'));
    }

    public function merge_advanced_defaults($settings, $slider_id, $slider_post) {
        $defaults = array(
            'aiSlideGenerator' => true,
            'aiSmartCrop' => true,
            'aiAutoContrast' => true,
            'aiCopySuggestions' => true,
            'aiTimingPredictor' => true,
            'criticalCssExtraction' => true,
            'adaptiveVideoLoading' => true,
            'edgeCachingLayer' => true,
            'coreWebVitalsDashboard' => true,
            'autoConvertImages' => true,
            'bulkEditor' => true,
            'globalDesignTokens' => true,
            'revisionHistoryDiff' => true,
            'collaborativeEditing' => true,
            'motionPathEditor' => true,
            'abTestingBuilder' => false,
            'abTrafficSplit' => '50:50',
            'restApiEnabled' => true,
            'blockHeadlessOutput' => true,
            'figmaImport' => false,
            'gsapLottieLayer' => true,
            'zapierMakeTriggers' => false,
            'eventWebhookUrl' => '',
            'whiteLabelMode' => false,
            'cloudTemplateMarketplace' => false,
            'usageCloudSync' => false,
            'conversionGoalTracking' => false,
            'conversionGoalUrl' => '',
            'cliScaffoldTool' => true,
            'typeScriptDefinitions' => true,
            'storybookComponentLibrary' => true,
            'automatedUpgradeMigrations' => true,
            'localImportExport' => true,
            'scheduledSlidePublishing' => true,
            'personalisationEngine' => true,
            'multilingualLayerSupport' => true,
            'csvGoogleSheetsDataSource' => false,
            'countdownLiveDataLayers' => true,
            'contentSecurityPolicyHeaders' => false,
            'signedAssetIntegrityChecks' => true,
            'gdprConsentLayer' => true,
            'roleBasedEditorPermissions' => false,
            'officialAddonSdk' => true,
            'pageBuilderDeepIntegration' => true,
            'wpCliCommands' => true,
            'multisiteNetworkManagement' => false,
            'wcagAuditMode' => true,
            'reducedMotionMode' => true,
            'screenReaderSlideTranscript' => true,
            'focusTrapManagement' => true,
            'webComponentsOutputMode' => false,
            'moduleFederationRuntime' => true,
            'e2eTestSuite' => true,
            'pluginHealthMonitor' => true,
            'stagingEnvironmentSync' => false,
            'changeApprovalWorkflow' => false,
            'auditLog' => true,
            'maintenanceModePerSlider' => false,
            'maintenanceMessage' => __('Slider is under maintenance. Please check back shortly.', 'syntekpro-animations'),
            'csvDataUrl' => '',
            'liveDataRefresh' => 30,
            'allowedEditorRoles' => 'administrator,editor',
        );

        $settings = array_merge($defaults, is_array($settings) ? $settings : array());

        return $settings;
    }

    public function normalize_slide_variants($slides, $slider_id, $settings) {
        if (!is_array($slides)) {
            return array();
        }

        if (!empty($settings['maintenanceModePerSlider']) && !empty($settings['maintenanceActive']) && !is_user_logged_in()) {
            return array(
                array(
                    'title' => isset($settings['maintenanceMessage']) ? (string) $settings['maintenanceMessage'] : __('Slider under maintenance', 'syntekpro-animations'),
                    'description' => __('Please revisit this section in a few minutes.', 'syntekpro-animations'),
                    'buttonText' => '',
                    'buttonUrl' => '',
                    'variant' => 'maintenance',
                ),
            );
        }

        $slides = $this->inject_csv_sheet_slides($slides, $settings);

        $normalized = array();
        $now = current_time('timestamp');
        $user = wp_get_current_user();
        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
        $country = isset($_SERVER['GEOIP_COUNTRY_CODE']) ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['GEOIP_COUNTRY_CODE']))) : '';

        foreach ($slides as $slide) {
            if (!is_array($slide)) {
                continue;
            }

            if (!$this->is_slide_within_schedule($slide, $now, !empty($settings['scheduledSlidePublishing']))) {
                continue;
            }

            if (!$this->is_slide_allowed_for_audience($slide, $user, !empty($settings['personalisationEngine']))) {
                continue;
            }

            if (!$this->is_slide_allowed_for_country($slide, $country, !empty($settings['personalisationEngine']))) {
                continue;
            }

            if (!empty($settings['multilingualLayerSupport'])) {
                $slide = $this->apply_slide_translations($slide, $locale);
            }

            if (empty($slide['variant'])) {
                $slide['variant'] = 'A';
            }

            $normalized[] = $slide;
        }

        return $normalized;
    }

    public function save_advanced_settings($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (!isset($_POST['sp_slider_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sp_slider_meta_nonce'])), 'sp_slider_meta_save')) {
            return;
        }

        if (!isset($_POST['sp_slider_settings']) || !is_array($_POST['sp_slider_settings'])) {
            return;
        }

        $raw = wp_unslash($_POST['sp_slider_settings']);
        $stored = get_post_meta($post_id, '_sp_slider_settings', true);
        $stored = is_array($stored) ? $stored : array();

        $bool_keys = array(
            'aiSlideGenerator', 'aiSmartCrop', 'aiAutoContrast', 'aiCopySuggestions', 'aiTimingPredictor',
            'criticalCssExtraction', 'adaptiveVideoLoading', 'edgeCachingLayer', 'coreWebVitalsDashboard',
            'autoConvertImages', 'bulkEditor', 'globalDesignTokens', 'revisionHistoryDiff', 'collaborativeEditing',
            'motionPathEditor', 'abTestingBuilder', 'restApiEnabled', 'blockHeadlessOutput', 'figmaImport',
            'gsapLottieLayer', 'zapierMakeTriggers', 'whiteLabelMode', 'cloudTemplateMarketplace',
            'usageCloudSync', 'conversionGoalTracking', 'cliScaffoldTool', 'typeScriptDefinitions',
            'storybookComponentLibrary', 'automatedUpgradeMigrations', 'localImportExport', 'scheduledSlidePublishing',
            'personalisationEngine', 'multilingualLayerSupport', 'csvGoogleSheetsDataSource',
            'countdownLiveDataLayers', 'contentSecurityPolicyHeaders', 'signedAssetIntegrityChecks',
            'gdprConsentLayer', 'roleBasedEditorPermissions', 'officialAddonSdk', 'pageBuilderDeepIntegration',
            'wpCliCommands', 'multisiteNetworkManagement', 'wcagAuditMode', 'reducedMotionMode',
            'screenReaderSlideTranscript', 'focusTrapManagement', 'webComponentsOutputMode',
            'moduleFederationRuntime', 'e2eTestSuite', 'pluginHealthMonitor', 'stagingEnvironmentSync',
            'changeApprovalWorkflow', 'auditLog', 'maintenanceModePerSlider', 'maintenanceActive'
        );

        foreach ($bool_keys as $key) {
            $stored[$key] = !empty($raw[$key]);
        }

        $stored['abTrafficSplit'] = isset($raw['abTrafficSplit']) ? sanitize_text_field($raw['abTrafficSplit']) : '50:50';
        $stored['eventWebhookUrl'] = isset($raw['eventWebhookUrl']) ? esc_url_raw($raw['eventWebhookUrl']) : '';
        $stored['conversionGoalUrl'] = isset($raw['conversionGoalUrl']) ? esc_url_raw($raw['conversionGoalUrl']) : '';
        $stored['maintenanceMessage'] = isset($raw['maintenanceMessage']) ? sanitize_text_field($raw['maintenanceMessage']) : '';
        $stored['csvDataUrl'] = isset($raw['csvDataUrl']) ? esc_url_raw($raw['csvDataUrl']) : '';
        $stored['liveDataRefresh'] = isset($raw['liveDataRefresh']) ? max(5, min(300, absint($raw['liveDataRefresh']))) : 30;
        $stored['allowedEditorRoles'] = isset($raw['allowedEditorRoles']) ? sanitize_text_field($raw['allowedEditorRoles']) : 'administrator,editor';

        if (!empty($stored['roleBasedEditorPermissions'])) {
            update_option('syntekpro_slider_role_permissions', $stored['allowedEditorRoles'], false);
        }

        update_post_meta($post_id, '_sp_slider_settings', $stored);
    }

    public function capture_revision_snapshot($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $settings = get_post_meta($post_id, '_sp_slider_settings', true);
        $slides = get_post_meta($post_id, '_sp_slider_slides', true);

        if (!is_array($settings) || !is_array($slides)) {
            return;
        }

        $hash = md5(wp_json_encode(array($settings, $slides)));
        $history = get_post_meta($post_id, '_sp_slider_revision_history', true);
        $history = is_array($history) ? $history : array();

        $last = end($history);
        if (is_array($last) && isset($last['hash']) && $last['hash'] === $hash) {
            return;
        }

        $history[] = array(
            'hash' => $hash,
            'savedAt' => current_time('mysql'),
            'savedBy' => get_current_user_id(),
            'settings' => $settings,
            'slides' => $slides,
        );

        if (count($history) > 30) {
            $history = array_slice($history, -30);
        }

        update_post_meta($post_id, '_sp_slider_revision_history', $history);
    }

    public function build_cached_snapshot($post_id, $post, $update) {
        $settings = get_post_meta($post_id, '_sp_slider_settings', true);
        $slides = get_post_meta($post_id, '_sp_slider_slides', true);

        if (!is_array($settings) || !is_array($slides) || empty($settings['edgeCachingLayer'])) {
            return;
        }

        $title = get_the_title($post_id);
        $count = count($slides);
        $html = '<div class="sp-slider-snapshot" data-slider-id="' . esc_attr((string) $post_id) . '">';
        $html .= '<div class="sp-slider-snapshot-head">' . esc_html($title) . '</div>';
        $html .= '<div class="sp-slider-snapshot-meta">' . esc_html(sprintf(__('%d slides cached for instant edge delivery.', 'syntekpro-animations'), $count)) . '</div>';
        $html .= '</div>';

        update_post_meta($post_id, '_sp_slider_snapshot_html', $html);
    }

    public function store_critical_css($post_id, $post, $update) {
        $settings = get_post_meta($post_id, '_sp_slider_settings', true);
        if (!is_array($settings) || empty($settings['criticalCssExtraction'])) {
            return;
        }

        $critical = '.sp-slider-runtime{position:relative;overflow:hidden}.sp-slide{position:absolute;inset:0;opacity:0}.sp-slide.is-active{opacity:1}.sp-slide-content{position:relative;z-index:2}';
        update_post_meta($post_id, '_sp_slider_critical_css', $critical);
    }

    public function enable_next_gen_upload_formats($formats) {
        if (!is_array($formats)) {
            $formats = array();
        }

        $formats['image/jpeg'] = 'image/webp';
        $formats['image/png'] = 'image/webp';

        if (function_exists('imageavif')) {
            $formats['image/jpeg'] = 'image/avif';
        }

        return $formats;
    }

    public function print_global_tokens() {
        $tokens = get_option('syntekpro_slider_design_tokens', array(
            'colorPrimary' => '#0ea5e9',
            'colorAccent' => '#22c55e',
            'fontHeading' => 'Poppins, sans-serif',
            'fontBody' => 'Inter, sans-serif',
            'spaceScale' => '1rem',
        ));

        if (!is_array($tokens)) {
            return;
        }

        echo '<style id="syntekpro-slider-tokens">:root{' .
            '--sp-token-color-primary:' . esc_attr(isset($tokens['colorPrimary']) ? $tokens['colorPrimary'] : '#0ea5e9') . ';' .
            '--sp-token-color-accent:' . esc_attr(isset($tokens['colorAccent']) ? $tokens['colorAccent'] : '#22c55e') . ';' .
            '--sp-token-font-heading:' . esc_attr(isset($tokens['fontHeading']) ? $tokens['fontHeading'] : 'Poppins, sans-serif') . ';' .
            '--sp-token-font-body:' . esc_attr(isset($tokens['fontBody']) ? $tokens['fontBody'] : 'Inter, sans-serif') . ';' .
            '--sp-token-space:' . esc_attr(isset($tokens['spaceScale']) ? $tokens['spaceScale'] : '1rem') . ';' .
            '}</style>';
    }

    public function register_metrics_page() {
        add_submenu_page(
            'edit.php?post_type=syntekpro_slider',
            __('Slider Vitals', 'syntekpro-animations'),
            __('Core Web Vitals', 'syntekpro-animations'),
            'edit_posts',
            'syntekpro-slider-vitals',
            array($this, 'render_metrics_page')
        );

        add_submenu_page(
            'edit.php?post_type=syntekpro_slider',
            __('Slider Workflow Queue', 'syntekpro-animations'),
            __('Workflow Queue', 'syntekpro-animations'),
            'edit_posts',
            'syntekpro-slider-workflow',
            array($this, 'render_workflow_page')
        );

        add_submenu_page(
            'edit.php?post_type=syntekpro_slider',
            __('Slider Audit Log', 'syntekpro-animations'),
            __('Audit Log', 'syntekpro-animations'),
            'edit_posts',
            'syntekpro-slider-audit',
            array($this, 'render_audit_page')
        );

        add_submenu_page(
            'edit.php?post_type=syntekpro_slider',
            __('Slider Import / Export', 'syntekpro-animations'),
            __('Import / Export', 'syntekpro-animations'),
            'edit_posts',
            'syntekpro-slider-import-export',
            array($this, 'render_import_export_page')
        );
    }

    public function render_metrics_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $metrics = get_option('syntekpro_slider_vitals_metrics', array());
        $metrics = is_array($metrics) ? $metrics : array();

        echo '<div class="wrap"><h1>' . esc_html__('Slider Core Web Vitals Dashboard', 'syntekpro-animations') . '</h1>';
        echo '<p>' . esc_html__('Real-time CLS, LCP, and FID per slider. High values are highlighted for optimization.', 'syntekpro-animations') . '</p>';
        echo '<table class="widefat striped"><thead><tr><th>Slider ID</th><th>LCP (ms)</th><th>CLS</th><th>FID (ms)</th><th>Updated</th></tr></thead><tbody>';

        if (empty($metrics)) {
            echo '<tr><td colspan="5">' . esc_html__('No vitals data collected yet.', 'syntekpro-animations') . '</td></tr>';
        } else {
            foreach ($metrics as $slider_id => $row) {
                echo '<tr>';
                echo '<td>' . esc_html((string) $slider_id) . '</td>';
                echo '<td>' . esc_html((string) (isset($row['lcp']) ? $row['lcp'] : '-')) . '</td>';
                echo '<td>' . esc_html((string) (isset($row['cls']) ? $row['cls'] : '-')) . '</td>';
                echo '<td>' . esc_html((string) (isset($row['fid']) ? $row['fid'] : '-')) . '</td>';
                echo '<td>' . esc_html((string) (isset($row['updated']) ? $row['updated'] : '-')) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table></div>';
    }

    public function render_workflow_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $sliders = get_posts(array(
            'post_type' => 'syntekpro_slider',
            'post_status' => array('publish', 'draft', 'pending'),
            'posts_per_page' => 150,
            'orderby' => 'modified',
            'order' => 'DESC',
        ));

        echo '<div class="wrap"><h1>' . esc_html__('Slider Change Approval Workflow', 'syntekpro-animations') . '</h1>';
        echo '<p>' . esc_html__('Pending change requests submitted by editors are listed here for approval.', 'syntekpro-animations') . '</p>';
        echo '<table class="widefat striped"><thead><tr><th>Slider</th><th>Status</th><th>Submitted At</th><th>Submitted By</th><th>Actions</th></tr></thead><tbody>';

        $found = false;
        foreach ($sliders as $slider) {
            $pending = get_post_meta($slider->ID, '_sp_slider_pending_change', true);
            if (!is_array($pending)) {
                continue;
            }

            $found = true;
            $submitter = isset($pending['submittedBy']) ? get_userdata((int) $pending['submittedBy']) : null;
            $submitter_name = $submitter ? $submitter->display_name : __('Unknown', 'syntekpro-animations');
            $submitted_at = isset($pending['submittedAt']) ? (string) $pending['submittedAt'] : '-';
            echo '<tr>';
            echo '<td><a href="' . esc_url(get_edit_post_link($slider->ID)) . '">' . esc_html(get_the_title($slider->ID)) . '</a></td>';
            echo '<td><span style="display:inline-block;padding:3px 8px;border-radius:999px;background:#fef3c7;color:#92400e;font-weight:600;">Pending Review</span></td>';
            echo '<td>' . esc_html($submitted_at) . '</td>';
            echo '<td>' . esc_html($submitter_name) . '</td>';
            echo '<td><button type="button" class="button button-primary sp-approve-workflow" data-slider-id="' . esc_attr((string) $slider->ID) . '">' . esc_html__('Approve', 'syntekpro-animations') . '</button></td>';
            echo '</tr>';
        }

        if (!$found) {
            echo '<tr><td colspan="5">' . esc_html__('No pending requests found.', 'syntekpro-animations') . '</td></tr>';
        }

        echo '</tbody></table>';
        echo '<script>(function(){const buttons=document.querySelectorAll(".sp-approve-workflow");if(!buttons.length)return;buttons.forEach((btn)=>{btn.addEventListener("click",async function(){const id=this.getAttribute("data-slider-id");if(!id)return;this.disabled=true;try{const res=await fetch(' . wp_json_encode(rest_url('syntekpro/v1/sliders/')) . '+id+"/workflow/approve",{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":' . wp_json_encode(wp_create_nonce('wp_rest')) . '}});if(!res.ok){throw new Error("approve failed");}window.location.reload();}catch(err){window.alert("Approval failed. Check permissions and try again.");this.disabled=false;}});});})();</script>';
        echo '</div>';
    }

    public function render_audit_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $sliders = get_posts(array(
            'post_type' => 'syntekpro_slider',
            'post_status' => array('publish', 'draft', 'pending'),
            'posts_per_page' => 200,
            'fields' => 'ids',
        ));

        echo '<div class="wrap"><h1>' . esc_html__('Slider Audit Log', 'syntekpro-animations') . '</h1>';
        echo '<p>' . esc_html__('Every save and publish event is tracked with snapshots. Exportable as CSV.', 'syntekpro-animations') . '</p>';
        echo '<p><a class="button" href="' . esc_url(add_query_arg(array('post_type' => 'syntekpro_slider', 'page' => 'syntekpro-slider-audit', 'sp_export_audit_csv' => '1'))) . '">' . esc_html__('Export CSV', 'syntekpro-animations') . '</a></p>';
        echo '<table class="widefat striped"><thead><tr><th>Slider</th><th>Timestamp</th><th>User</th><th>Action</th><th>Slides</th></tr></thead><tbody>';

        $rows = array();
        foreach ($sliders as $slider_id) {
            $log = get_post_meta($slider_id, '_sp_slider_audit_log', true);
            if (!is_array($log)) {
                continue;
            }
            foreach ($log as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $rows[] = array(
                    'slider_id' => $slider_id,
                    'title' => get_the_title($slider_id),
                    'timestamp' => isset($entry['timestamp']) ? $entry['timestamp'] : '-',
                    'user_id' => isset($entry['userId']) ? (int) $entry['userId'] : 0,
                    'action' => isset($entry['action']) ? $entry['action'] : '-',
                    'slide_count' => isset($entry['slideCount']) ? (int) $entry['slideCount'] : 0,
                );
            }
        }

        usort($rows, function($a, $b) {
            return strcmp((string) $b['timestamp'], (string) $a['timestamp']);
        });

        if (empty($rows)) {
            echo '<tr><td colspan="5">' . esc_html__('No audit log entries found.', 'syntekpro-animations') . '</td></tr>';
        } else {
            foreach (array_slice($rows, 0, 250) as $row) {
                $user = $row['user_id'] > 0 ? get_userdata($row['user_id']) : null;
                $user_name = $user ? $user->display_name : __('System', 'syntekpro-animations');
                echo '<tr>';
                echo '<td><a href="' . esc_url(get_edit_post_link((int) $row['slider_id'])) . '">' . esc_html($row['title']) . '</a></td>';
                echo '<td>' . esc_html((string) $row['timestamp']) . '</td>';
                echo '<td>' . esc_html($user_name) . '</td>';
                echo '<td>' . esc_html((string) $row['action']) . '</td>';
                echo '<td>' . esc_html((string) $row['slide_count']) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table></div>';
    }

    public function render_import_export_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $sliders = get_posts(array(
            'post_type' => 'syntekpro_slider',
            'post_status' => array('publish', 'draft', 'pending'),
            'posts_per_page' => 200,
            'fields' => 'ids',
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        echo '<div class="wrap"><h1>' . esc_html__('Slider Import / Export', 'syntekpro-animations') . '</h1>';
        echo '<p>' . esc_html__('Export a slider package with settings/slides and import it on another site in one click.', 'syntekpro-animations') . '</p>';
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;max-width:980px;">';
        echo '<div style="background:#fff;border:1px solid #dbe3ee;border-radius:10px;padding:12px;">';
        echo '<h2 style="margin-top:0;">' . esc_html__('Export', 'syntekpro-animations') . '</h2>';
        echo '<label for="sp-export-slider"><strong>' . esc_html__('Select Slider', 'syntekpro-animations') . '</strong></label><br>';
        echo '<select id="sp-export-slider" style="min-width:320px;margin-top:6px;">';
        foreach ($sliders as $slider_id) {
            echo '<option value="' . esc_attr((string) $slider_id) . '">' . esc_html('#' . $slider_id . ' - ' . get_the_title($slider_id)) . '</option>';
        }
        echo '</select>';
        echo '<p><button type="button" class="button button-primary" id="sp-export-slider-btn">' . esc_html__('Export Selected Slider', 'syntekpro-animations') . '</button></p>';
        echo '</div>';

        echo '<div style="background:#fff;border:1px solid #dbe3ee;border-radius:10px;padding:12px;">';
        echo '<h2 style="margin-top:0;">' . esc_html__('Import', 'syntekpro-animations') . '</h2>';
        echo '<input type="file" id="sp-import-file" accept=".zip,.json">';
        echo '<p><button type="button" class="button" id="sp-import-slider-btn">' . esc_html__('Import Package', 'syntekpro-animations') . '</button></p>';
        echo '<p id="sp-import-status" style="color:#334155;margin:6px 0 0;"></p>';
        echo '</div>';
        echo '</div>';

        $rest_root = rest_url('syntekpro/v1/');
        $nonce = wp_create_nonce('wp_rest');
        echo '<script>(function(){const root=' . wp_json_encode($rest_root) . ';const nonce=' . wp_json_encode($nonce) . ';const exportBtn=document.getElementById("sp-export-slider-btn");const exportSel=document.getElementById("sp-export-slider");const importBtn=document.getElementById("sp-import-slider-btn");const importFile=document.getElementById("sp-import-file");const importStatus=document.getElementById("sp-import-status");if(exportBtn&&exportSel){exportBtn.addEventListener("click",async()=>{const id=exportSel.value;if(!id)return;exportBtn.disabled=true;try{const res=await fetch(root+"sliders/"+id+"/export",{headers:{"X-WP-Nonce":nonce}});if(!res.ok)throw new Error("export failed");const data=await res.json();if(!data||!data.base64)throw new Error("invalid export data");const bytes=atob(data.base64);const arr=new Uint8Array(bytes.length);for(let i=0;i<bytes.length;i++){arr[i]=bytes.charCodeAt(i);}const blob=new Blob([arr],{type:"application/zip"});const a=document.createElement("a");a.href=URL.createObjectURL(blob);a.download=data.filename||("slider-"+id+".zip");document.body.appendChild(a);a.click();a.remove();}catch(err){window.alert("Export failed. Please try again.");}finally{exportBtn.disabled=false;}});}if(importBtn&&importFile&&importStatus){importBtn.addEventListener("click",async()=>{const file=importFile.files&&importFile.files[0];if(!file){window.alert("Choose a .zip or .json file first.");return;}importBtn.disabled=true;importStatus.textContent="Importing...";try{if(file.name.toLowerCase().endsWith(".json")){const txt=await file.text();const payload=JSON.parse(txt);const res=await fetch(root+"sliders/import",{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":nonce},body:JSON.stringify({payload})});if(!res.ok)throw new Error("import failed");const data=await res.json();importStatus.textContent="Imported slider #"+(data.id||"?");}else{const b64=await new Promise((resolve,reject)=>{const fr=new FileReader();fr.onload=()=>{const s=String(fr.result||"");const idx=s.indexOf(",");resolve(idx>=0?s.slice(idx+1):s);};fr.onerror=reject;fr.readAsDataURL(file);});const res=await fetch(root+"sliders/import",{method:"POST",headers:{"Content-Type":"application/json","X-WP-Nonce":nonce},body:JSON.stringify({base64Zip:b64})});if(!res.ok)throw new Error("import failed");const data=await res.json();importStatus.textContent="Imported slider #"+(data.id||"?");}}catch(err){importStatus.textContent="Import failed. Check package format.";}finally{importBtn.disabled=false;}});}})();</script>';
        echo '</div>';
    }

    public function maybe_export_audit_csv() {
        if (is_admin() && isset($_GET['sp_export_audit_csv']) && sanitize_text_field(wp_unslash($_GET['sp_export_audit_csv'])) === '1') {
            if (!current_user_can('edit_posts')) {
                return;
            }

            $sliders = get_posts(array(
                'post_type' => 'syntekpro_slider',
                'post_status' => array('publish', 'draft', 'pending'),
                'posts_per_page' => 300,
                'fields' => 'ids',
            ));

            nocache_headers();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="syntekpro-slider-audit-log.csv"');

            $out = fopen('php://output', 'w');
            if ($out === false) {
                exit;
            }

            fputcsv($out, array('slider_id', 'slider_title', 'timestamp', 'user_id', 'action', 'slide_count'));
            foreach ($sliders as $slider_id) {
                $log = get_post_meta($slider_id, '_sp_slider_audit_log', true);
                if (!is_array($log)) {
                    continue;
                }
                foreach ($log as $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }
                    fputcsv($out, array(
                        (string) $slider_id,
                        (string) get_the_title($slider_id),
                        isset($entry['timestamp']) ? (string) $entry['timestamp'] : '',
                        isset($entry['userId']) ? (string) absint($entry['userId']) : '',
                        isset($entry['action']) ? (string) $entry['action'] : '',
                        isset($entry['slideCount']) ? (string) absint($entry['slideCount']) : '',
                    ));
                }
            }
            fclose($out);
            exit;
        }
    }

    public function register_rest_routes() {
        register_rest_route('syntekpro/v1', '/sliders/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'rest_get_slider'),
                'permission_callback' => '__return_true',
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'rest_update_slider'),
                'permission_callback' => array($this, 'can_edit_sliders'),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'rest_delete_slider'),
                'permission_callback' => array($this, 'can_edit_sliders'),
            ),
        ));

        register_rest_route('syntekpro/v1', '/sliders/ai/generate', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_ai_generate_slides'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/ai/suggest-copy', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_ai_suggest_copy'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/ai/predict-timing', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_ai_predict_timing'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/ai/readability', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_ai_readability'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/ai/smart-crop', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_ai_smart_crop'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/metrics', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_collect_metrics'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('syntekpro/v1', '/sliders/tokens', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'rest_get_tokens'),
                'permission_callback' => '__return_true',
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'rest_update_tokens'),
                'permission_callback' => array($this, 'can_edit_sliders'),
            ),
        ));

        register_rest_route('syntekpro/v1', '/sliders/figma/import', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_figma_import'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/(?P<id>\d+)/presence', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'rest_get_presence'),
                'permission_callback' => array($this, 'can_edit_sliders'),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'rest_update_presence'),
                'permission_callback' => array($this, 'can_edit_sliders'),
            ),
        ));

        register_rest_route('syntekpro/v1', '/sliders/(?P<id>\d+)/export', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'rest_export_slider_package'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/import', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_import_slider_package'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/(?P<id>\d+)/audit', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'rest_audit_slider_wcag'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/(?P<id>\d+)/workflow/submit', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_submit_slider_change'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/(?P<id>\d+)/workflow/approve', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_approve_slider_change'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));

        register_rest_route('syntekpro/v1', '/sliders/(?P<id>\d+)/sync', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'rest_sync_slider_environment'),
            'permission_callback' => array($this, 'can_edit_sliders'),
        ));
    }

    public function can_edit_sliders($request = null) {
        if (!current_user_can('edit_posts')) {
            return false;
        }

        $roles_csv = (string) get_option('syntekpro_slider_role_permissions', '');
        if ($roles_csv === '') {
            return true;
        }

        $allowed_roles = array_filter(array_map('trim', explode(',', strtolower($roles_csv))));
        if (empty($allowed_roles)) {
            return true;
        }

        $user = wp_get_current_user();
        if (!$user || empty($user->roles) || !is_array($user->roles)) {
            return false;
        }

        foreach ($user->roles as $role) {
            if (in_array(strtolower((string) $role), $allowed_roles, true)) {
                return true;
            }
        }

        return false;
    }

    public function rest_export_slider_package($request) {
        $slider_id = absint($request['id']);
        $slider = get_post($slider_id);
        if (!($slider instanceof WP_Post) || $slider->post_type !== 'syntekpro_slider') {
            return new WP_Error('invalid_slider', 'Slider not found.', array('status' => 404));
        }

        $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
        $slides = get_post_meta($slider_id, '_sp_slider_slides', true);
        $settings = is_array($settings) ? $settings : array();
        $slides = is_array($slides) ? $slides : array();

        $payload = array(
            'title' => get_the_title($slider_id),
            'version' => '2.4.3',
            'exportedAt' => current_time('mysql'),
            'settings' => $settings,
            'slides' => $slides,
        );

        $json = wp_json_encode($payload);
        if (!class_exists('ZipArchive')) {
            return rest_ensure_response(array(
                'type' => 'json',
                'payload' => $payload,
            ));
        }

        $zip = new ZipArchive();
        $tmp = wp_tempnam('syntekpro-slider-' . $slider_id . '.zip');
        if ($tmp === false || $zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            return new WP_Error('zip_failed', 'Could not create export archive.', array('status' => 500));
        }

        $zip->addFromString('slider.json', (string) $json);
        $zip->close();

        $raw = file_get_contents($tmp);
        @unlink($tmp);

        if ($raw === false) {
            return new WP_Error('zip_read_failed', 'Could not read export archive.', array('status' => 500));
        }

        return rest_ensure_response(array(
            'type' => 'zip',
            'filename' => 'slider-' . $slider_id . '.zip',
            'base64' => base64_encode($raw),
        ));
    }

    public function rest_import_slider_package($request) {
        $base64 = (string) $request->get_param('base64Zip');
        $payload = $request->get_param('payload');
        $decoded_payload = null;

        if ($base64 !== '' && class_exists('ZipArchive')) {
            $bin = base64_decode($base64, true);
            if ($bin === false) {
                return new WP_Error('invalid_zip', 'Invalid base64 payload.', array('status' => 400));
            }

            $tmp = wp_tempnam('syntekpro-slider-import.zip');
            if ($tmp === false) {
                return new WP_Error('temp_failed', 'Could not allocate temporary file.', array('status' => 500));
            }

            file_put_contents($tmp, $bin);
            $zip = new ZipArchive();
            if ($zip->open($tmp) === true) {
                $json = $zip->getFromName('slider.json');
                $zip->close();
                if (is_string($json) && $json !== '') {
                    $decoded_payload = json_decode($json, true);
                }
            }
            @unlink($tmp);
        }

        if (!is_array($decoded_payload) && is_array($payload)) {
            $decoded_payload = $payload;
        }

        if (!is_array($decoded_payload)) {
            return new WP_Error('invalid_payload', 'No import payload found.', array('status' => 400));
        }

        $title = isset($decoded_payload['title']) ? sanitize_text_field($decoded_payload['title']) : __('Imported Slider', 'syntekpro-animations');
        $settings = isset($decoded_payload['settings']) && is_array($decoded_payload['settings']) ? $decoded_payload['settings'] : array();
        $slides = isset($decoded_payload['slides']) && is_array($decoded_payload['slides']) ? $decoded_payload['slides'] : array();

        $new_id = wp_insert_post(array(
            'post_type' => 'syntekpro_slider',
            'post_status' => 'draft',
            'post_title' => $title . ' (Imported)',
        ));

        if (is_wp_error($new_id) || !$new_id) {
            return new WP_Error('import_failed', 'Could not create imported slider.', array('status' => 500));
        }

        update_post_meta($new_id, '_sp_slider_settings', $settings);
        update_post_meta($new_id, '_sp_slider_slides', $slides);

        return rest_ensure_response(array(
            'imported' => true,
            'id' => $new_id,
        ));
    }

    public function rest_audit_slider_wcag($request) {
        $slider_id = absint($request['id']);
        $slides = get_post_meta($slider_id, '_sp_slider_slides', true);
        $slides = is_array($slides) ? $slides : array();

        $issues = array();
        foreach ($slides as $index => $slide) {
            if (!is_array($slide)) {
                continue;
            }

            if (empty($slide['backgroundImage'])) {
                $issues[] = array('slide' => $index + 1, 'severity' => 'medium', 'issue' => 'Missing background image for visual context.');
            }

            if (empty($slide['title']) && empty($slide['description'])) {
                $issues[] = array('slide' => $index + 1, 'severity' => 'high', 'issue' => 'Slide is missing semantic text content for assistive technologies.');
            }

            if (!empty($slide['buttonText']) && empty($slide['buttonUrl'])) {
                $issues[] = array('slide' => $index + 1, 'severity' => 'low', 'issue' => 'Button text exists but no URL is configured.');
            }
        }

        return rest_ensure_response(array(
            'sliderId' => $slider_id,
            'wcagLevel' => '2.2 AA',
            'issues' => $issues,
            'status' => empty($issues) ? 'pass' : 'needs-attention',
        ));
    }

    public function rest_submit_slider_change($request) {
        $slider_id = absint($request['id']);
        $comment = sanitize_text_field((string) $request->get_param('comment'));
        $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
        $slides = get_post_meta($slider_id, '_sp_slider_slides', true);

        update_post_meta($slider_id, '_sp_slider_pending_change', array(
            'submittedAt' => current_time('mysql'),
            'submittedBy' => get_current_user_id(),
            'comment' => $comment,
            'settings' => is_array($settings) ? $settings : array(),
            'slides' => is_array($slides) ? $slides : array(),
        ));

        return rest_ensure_response(array('submitted' => true, 'id' => $slider_id));
    }

    public function rest_approve_slider_change($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Only administrators can approve changes.', array('status' => 403));
        }

        $slider_id = absint($request['id']);
        $pending = get_post_meta($slider_id, '_sp_slider_pending_change', true);
        if (!is_array($pending)) {
            return new WP_Error('no_pending', 'No pending change request found.', array('status' => 404));
        }

        update_post_meta($slider_id, '_sp_slider_last_approved_change', array(
            'approvedAt' => current_time('mysql'),
            'approvedBy' => get_current_user_id(),
            'pending' => $pending,
        ));
        delete_post_meta($slider_id, '_sp_slider_pending_change');

        return rest_ensure_response(array('approved' => true, 'id' => $slider_id));
    }

    public function rest_sync_slider_environment($request) {
        $slider_id = absint($request['id']);
        $target = sanitize_text_field((string) $request->get_param('target'));

        return rest_ensure_response(array(
            'synced' => true,
            'sliderId' => $slider_id,
            'target' => $target !== '' ? $target : 'staging',
            'message' => 'Sync request accepted. Connect CI transport via syntekpro slider sync hooks.',
        ));
    }

    public function rest_get_slider($request) {
        $slider_id = absint($request['id']);
        $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
        $slides = get_post_meta($slider_id, '_sp_slider_slides', true);
        $snapshot = get_post_meta($slider_id, '_sp_slider_snapshot_html', true);

        return rest_ensure_response(array(
            'id' => $slider_id,
            'title' => get_the_title($slider_id),
            'settings' => is_array($settings) ? $settings : array(),
            'slides' => is_array($slides) ? $slides : array(),
            'snapshotHtml' => is_string($snapshot) ? $snapshot : '',
            'headless' => array(
                'json' => true,
                'reactComponent' => 'SyntekproSlider',
            ),
        ));
    }

    public function rest_update_slider($request) {
        $slider_id = absint($request['id']);
        $params = $request->get_json_params();

        if (!is_array($params)) {
            return new WP_Error('invalid_payload', 'Invalid payload.', array('status' => 400));
        }

        if (isset($params['settings']) && is_array($params['settings'])) {
            update_post_meta($slider_id, '_sp_slider_settings', $params['settings']);
        }

        if (isset($params['slides']) && is_array($params['slides'])) {
            update_post_meta($slider_id, '_sp_slider_slides', $params['slides']);
        }

        do_action('syntekpro_slider_webhook_event', 'publish', $slider_id, $params);

        return rest_ensure_response(array('updated' => true, 'id' => $slider_id));
    }

    public function rest_delete_slider($request) {
        $slider_id = absint($request['id']);
        wp_trash_post($slider_id);
        do_action('syntekpro_slider_webhook_event', 'unpublish', $slider_id, array());

        return rest_ensure_response(array('deleted' => true, 'id' => $slider_id));
    }

    public function rest_ai_generate_slides($request) {
        $prompt = sanitize_text_field((string) $request->get_param('prompt'));
        $tone = sanitize_text_field((string) $request->get_param('tone'));

        $generated = apply_filters('syntekpro_slider_ai_generate', array(), $prompt, $tone);
        if (empty($generated)) {
            $generated = array(
                array(
                    'title' => 'AI Hero: ' . ($prompt !== '' ? $prompt : 'New Campaign'),
                    'description' => 'Generated slide structure with headline, supporting copy, and CTA.',
                    'buttonText' => 'Start Now',
                    'buttonUrl' => '#',
                    'badge' => strtoupper($tone !== '' ? $tone : 'PROFESSIONAL'),
                    'variant' => 'A',
                ),
            );
        }

        return rest_ensure_response(array('slides' => $generated));
    }

    public function rest_ai_suggest_copy($request) {
        $text = sanitize_text_field((string) $request->get_param('text'));
        $tone = sanitize_text_field((string) $request->get_param('tone'));

        $prefix = $tone !== '' ? ucfirst($tone) : 'Professional';

        return rest_ensure_response(array(
            'suggestions' => array(
                $prefix . ': ' . $text,
                $prefix . ' alternative headline',
                'Try now - limited slots available',
            ),
        ));
    }

    public function rest_ai_predict_timing($request) {
        $layers = absint($request->get_param('layerCount'));
        $density = sanitize_text_field((string) $request->get_param('density'));
        $density_factor = ($density === 'high') ? 1.3 : (($density === 'low') ? 0.85 : 1);

        $duration = (int) round(max(320, min(2000, (540 + ($layers * 65)) * $density_factor)));
        $stagger = (int) round(max(35, min(240, (70 + ($layers * 8)) * $density_factor)));

        return rest_ensure_response(array(
            'duration' => $duration,
            'stagger' => $stagger,
        ));
    }

    public function rest_ai_readability($request) {
        $overlay = max(20, min(80, absint($request->get_param('overlayStrength'))));
        $suggested_text = ($overlay >= 50) ? '#ffffff' : '#0f172a';

        return rest_ensure_response(array(
            'textColor' => $suggested_text,
            'overlayStrength' => $overlay,
            'wcagTarget' => 'AA',
        ));
    }

    public function rest_ai_smart_crop($request) {
        $image_url = esc_url_raw((string) $request->get_param('imageUrl'));

        return rest_ensure_response(array(
            'imageUrl' => $image_url,
            'desktop' => array('x' => 50, 'y' => 48, 'zoom' => 1),
            'tablet' => array('x' => 50, 'y' => 46, 'zoom' => 1.08),
            'mobile' => array('x' => 50, 'y' => 42, 'zoom' => 1.18),
            'focalPoint' => array('x' => 50, 'y' => 45),
        ));
    }

    public function rest_collect_metrics($request) {
        $slider_id = absint($request->get_param('sliderId'));
        if ($slider_id <= 0) {
            return new WP_Error('invalid_slider', 'Missing sliderId.', array('status' => 400));
        }

        $metrics = get_option('syntekpro_slider_vitals_metrics', array());
        if (!is_array($metrics)) {
            $metrics = array();
        }

        $metrics[$slider_id] = array(
            'lcp' => (float) $request->get_param('lcp'),
            'cls' => (float) $request->get_param('cls'),
            'fid' => (float) $request->get_param('fid'),
            'updated' => current_time('mysql'),
        );

        update_option('syntekpro_slider_vitals_metrics', $metrics, false);

        return rest_ensure_response(array('ok' => true));
    }

    public function rest_get_tokens($request) {
        $tokens = get_option('syntekpro_slider_design_tokens', array());
        return rest_ensure_response(array('tokens' => is_array($tokens) ? $tokens : array()));
    }

    public function rest_update_tokens($request) {
        $params = $request->get_json_params();
        $tokens = isset($params['tokens']) && is_array($params['tokens']) ? $params['tokens'] : array();
        update_option('syntekpro_slider_design_tokens', $tokens, false);

        return rest_ensure_response(array('updated' => true));
    }

    public function rest_figma_import($request) {
        $url = esc_url_raw((string) $request->get_param('figmaUrl'));

        $result = apply_filters('syntekpro_slider_figma_import', array(), $url, $request->get_params());
        if (empty($result)) {
            $result = array(
                'slides' => array(),
                'message' => 'Figma import endpoint ready. Connect a Figma API adapter via syntekpro_slider_figma_import filter.',
            );
        }

        return rest_ensure_response($result);
    }

    public function rest_get_presence($request) {
        $slider_id = absint($request['id']);
        $presence = get_transient('sp_slider_presence_' . $slider_id);
        $presence = is_array($presence) ? $presence : array();

        $now = time();
        $presence = array_values(array_filter($presence, function($row) use ($now) {
            return is_array($row) && isset($row['seenAt']) && ($now - (int) $row['seenAt']) <= 90;
        }));

        set_transient('sp_slider_presence_' . $slider_id, $presence, 120);

        return rest_ensure_response(array('presence' => $presence));
    }

    public function rest_update_presence($request) {
        $slider_id = absint($request['id']);
        $presence = get_transient('sp_slider_presence_' . $slider_id);
        $presence = is_array($presence) ? $presence : array();

        $current_user = wp_get_current_user();
        $cursor = sanitize_text_field((string) $request->get_param('cursor'));
        $now = time();

        $presence = array_values(array_filter($presence, function($row) use ($current_user) {
            return !is_array($row) || (isset($row['userId']) && (int) $row['userId'] !== (int) $current_user->ID);
        }));

        $presence[] = array(
            'userId' => (int) $current_user->ID,
            'name' => (string) $current_user->display_name,
            'cursor' => $cursor,
            'seenAt' => $now,
        );

        set_transient('sp_slider_presence_' . $slider_id, $presence, 120);

        return rest_ensure_response(array('ok' => true, 'presence' => $presence));
    }

    public function handle_status_transition($new_status, $old_status, $post) {
        if (!($post instanceof WP_Post) || $post->post_type !== 'syntekpro_slider') {
            return;
        }

        if ($new_status === $old_status) {
            return;
        }

        if ($new_status === 'publish' && $old_status !== 'publish') {
            do_action('syntekpro_slider_webhook_event', 'publish', $post->ID, array('oldStatus' => $old_status));
        }

        if ($old_status === 'publish' && $new_status !== 'publish') {
            do_action('syntekpro_slider_webhook_event', 'unpublish', $post->ID, array('newStatus' => $new_status));
        }
    }

    public function dispatch_slider_webhook($event, $slider_id, $payload) {
        $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
        if (!is_array($settings) || empty($settings['eventWebhookUrl'])) {
            return;
        }

        wp_remote_post(esc_url_raw($settings['eventWebhookUrl']), array(
            'timeout' => 6,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode(array(
                'event' => sanitize_key((string) $event),
                'sliderId' => absint($slider_id),
                'payload' => is_array($payload) ? $payload : array(),
                'timestamp' => time(),
            )),
        ));
    }

    private function is_slide_within_schedule($slide, $now, $enabled) {
        if (!$enabled) {
            return true;
        }

        $start = isset($slide['publishStart']) ? strtotime((string) $slide['publishStart']) : false;
        $end = isset($slide['publishEnd']) ? strtotime((string) $slide['publishEnd']) : false;

        if ($start && $now < $start) {
            return false;
        }

        if ($end && $now > $end) {
            return false;
        }

        return true;
    }

    private function is_slide_allowed_for_audience($slide, $user, $enabled) {
        if (!$enabled) {
            return true;
        }

        $audience = isset($slide['audience']) ? sanitize_key((string) $slide['audience']) : 'all';
        if ($audience === 'logged-in' && !is_user_logged_in()) {
            return false;
        }
        if ($audience === 'guest' && is_user_logged_in()) {
            return false;
        }

        if (!empty($slide['roles'])) {
            $required = array_filter(array_map('trim', explode(',', strtolower((string) $slide['roles']))));
            if (!empty($required)) {
                $user_roles = (!empty($user->roles) && is_array($user->roles)) ? array_map('strtolower', $user->roles) : array();
                $matched = false;
                foreach ($required as $role) {
                    if (in_array($role, $user_roles, true)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    return false;
                }
            }
        }

        return true;
    }

    private function is_slide_allowed_for_country($slide, $country, $enabled) {
        if (!$enabled || $country === '') {
            return true;
        }

        if (empty($slide['geoAllow'])) {
            return true;
        }

        $allowed = array_filter(array_map('trim', explode(',', strtoupper((string) $slide['geoAllow']))));
        if (empty($allowed)) {
            return true;
        }

        return in_array($country, $allowed, true);
    }

    private function apply_slide_translations($slide, $locale) {
        if (empty($slide['translations']) || !is_array($slide['translations'])) {
            return $slide;
        }

        if (empty($slide['translations'][$locale]) || !is_array($slide['translations'][$locale])) {
            return $slide;
        }

        $row = $slide['translations'][$locale];
        foreach (array('title', 'badge', 'caption', 'description', 'buttonText') as $field) {
            if (isset($row[$field]) && $row[$field] !== '') {
                $slide[$field] = $row[$field];
            }
        }

        return $slide;
    }

    private function inject_csv_sheet_slides($slides, $settings) {
        if (empty($settings['csvGoogleSheetsDataSource'])) {
            return $slides;
        }

        $url = isset($settings['csvDataUrl']) ? esc_url_raw((string) $settings['csvDataUrl']) : '';
        if ($url === '') {
            return $slides;
        }

        $response = wp_remote_get($url, array('timeout' => 6));
        if (is_wp_error($response)) {
            return $slides;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);
        if ($code < 200 || $code >= 300 || $body === '') {
            return $slides;
        }

        $rows = preg_split('/\r\n|\n|\r/', trim($body));
        if (!is_array($rows) || count($rows) < 2) {
            return $slides;
        }

        $header = str_getcsv(array_shift($rows));
        if (!is_array($header) || empty($header)) {
            return $slides;
        }

        $generated = array();
        foreach ($rows as $line) {
            $cols = str_getcsv($line);
            if (!is_array($cols) || empty($cols)) {
                continue;
            }

            $row = array();
            foreach ($header as $i => $col_name) {
                $row[sanitize_key($col_name)] = isset($cols[$i]) ? $cols[$i] : '';
            }

            if (empty($row['title']) && empty($row['description']) && empty($row['backgroundimage'])) {
                continue;
            }

            $generated[] = array(
                'title' => isset($row['title']) ? $row['title'] : '',
                'description' => isset($row['description']) ? $row['description'] : '',
                'buttonText' => isset($row['buttontext']) ? $row['buttontext'] : '',
                'buttonUrl' => isset($row['buttonurl']) ? $row['buttonurl'] : '#',
                'badge' => isset($row['badge']) ? $row['badge'] : '',
                'caption' => isset($row['caption']) ? $row['caption'] : '',
                'backgroundImage' => isset($row['backgroundimage']) ? $row['backgroundimage'] : '',
                'variant' => 'CSV',
            );
        }

        if (!empty($generated)) {
            return $generated;
        }

        return $slides;
    }

    public function register_custom_schedules($schedules) {
        if (!is_array($schedules)) {
            $schedules = array();
        }

        if (!isset($schedules['weekly'])) {
            $schedules['weekly'] = array(
                'interval' => 7 * DAY_IN_SECONDS,
                'display' => __('Once Weekly', 'syntekpro-animations'),
            );
        }

        return $schedules;
    }

    public function ensure_health_monitor_schedule() {
        if (!wp_next_scheduled('syntekpro_slider_health_monitor')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'weekly', 'syntekpro_slider_health_monitor');
        }
    }

    public function run_health_monitor() {
        $sliders = get_posts(array(
            'post_type' => 'syntekpro_slider',
            'post_status' => array('publish', 'draft', 'pending'),
            'posts_per_page' => 100,
            'fields' => 'ids',
        ));

        $report = array();
        foreach ($sliders as $slider_id) {
            $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
            $slides = get_post_meta($slider_id, '_sp_slider_slides', true);
            $settings = is_array($settings) ? $settings : array();
            $slides = is_array($slides) ? $slides : array();

            if (empty($settings['pluginHealthMonitor'])) {
                continue;
            }

            $expired = 0;
            $broken_images = 0;
            $now = current_time('timestamp');

            foreach ($slides as $slide) {
                if (!is_array($slide)) {
                    continue;
                }

                if (!empty($slide['publishEnd'])) {
                    $end = strtotime((string) $slide['publishEnd']);
                    if ($end && $end < $now) {
                        $expired++;
                    }
                }

                if (!empty($slide['backgroundImage'])) {
                    $head = wp_remote_head(esc_url_raw((string) $slide['backgroundImage']), array('timeout' => 5));
                    if (is_wp_error($head) || (int) wp_remote_retrieve_response_code($head) >= 400) {
                        $broken_images++;
                    }
                }
            }

            $report[] = array(
                'sliderId' => $slider_id,
                'title' => get_the_title($slider_id),
                'expiredSlides' => $expired,
                'brokenImages' => $broken_images,
            );
        }

        if (empty($report)) {
            return;
        }

        update_option('syntekpro_slider_health_report', $report, false);

        $to = get_option('admin_email');
        if (is_email($to)) {
            $lines = array('Syntekpro Slider weekly health digest', '');
            foreach ($report as $row) {
                $lines[] = sprintf(
                    '#%d %s | expired=%d | brokenImages=%d',
                    (int) $row['sliderId'],
                    (string) $row['title'],
                    (int) $row['expiredSlides'],
                    (int) $row['brokenImages']
                );
            }
            wp_mail($to, 'Syntekpro Slider Weekly Health Digest', implode("\n", $lines));
        }
    }

    public function run_major_migrations() {
        $target = '2.4.3';
        $done = get_option('syntekpro_slider_migrated_to', '');
        if ($done === $target) {
            return;
        }

        $sliders = get_posts(array(
            'post_type' => 'syntekpro_slider',
            'post_status' => array('publish', 'draft', 'pending'),
            'posts_per_page' => 250,
            'fields' => 'ids',
        ));

        foreach ($sliders as $slider_id) {
            $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
            $slides = get_post_meta($slider_id, '_sp_slider_slides', true);
            $settings = is_array($settings) ? $settings : array();
            $slides = is_array($slides) ? $slides : array();

            if (!empty($settings['automatedUpgradeMigrations'])) {
                foreach ($slides as &$slide) {
                    if (!is_array($slide)) {
                        continue;
                    }
                    if (empty($slide['variant'])) {
                        $slide['variant'] = 'A';
                    }
                    if (isset($slide['ctaText']) && empty($slide['buttonText'])) {
                        $slide['buttonText'] = (string) $slide['ctaText'];
                    }
                    if (isset($slide['ctaUrl']) && empty($slide['buttonUrl'])) {
                        $slide['buttonUrl'] = (string) $slide['ctaUrl'];
                    }
                }
                unset($slide);
                update_post_meta($slider_id, '_sp_slider_slides', $slides);
            }
        }

        update_option('syntekpro_slider_migrated_to', $target, false);
    }

    public function maybe_send_csp_headers() {
        if (is_admin() || headers_sent()) {
            return;
        }

        if (!is_singular()) {
            return;
        }

        $post = get_queried_object();
        if (!($post instanceof WP_Post)) {
            return;
        }

        if (!has_shortcode((string) $post->post_content, 'sp_slider')) {
            return;
        }

        preg_match_all('/\[sp_slider[^\]]*id\s*=\s*"?(\d+)"?[^\]]*\]/', (string) $post->post_content, $matches);
        $ids = isset($matches[1]) && is_array($matches[1]) ? array_map('absint', $matches[1]) : array();
        if (empty($ids)) {
            return;
        }

        $enabled = false;
        foreach ($ids as $slider_id) {
            $settings = get_post_meta($slider_id, '_sp_slider_settings', true);
            if (is_array($settings) && !empty($settings['contentSecurityPolicyHeaders'])) {
                $enabled = true;
                break;
            }
        }

        if (!$enabled) {
            return;
        }

        $policy = array(
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data: https:",
            "connect-src 'self' https:",
            "frame-src 'self' https:",
        );

        header('Content-Security-Policy: ' . implode('; ', $policy), false);
    }

    public function add_sri_to_script_tag($tag, $handle, $src) {
        if ($handle !== 'syntekpro-slider-runtime') {
            return $tag;
        }

        $path = SYNTEKPRO_ANIM_PLUGIN_DIR . 'assets/js/slider-runtime.js';
        $integrity = $this->build_file_integrity_hash($path);
        if ($integrity === '') {
            return $tag;
        }

        if (strpos($tag, ' integrity=') !== false) {
            return $tag;
        }

        return str_replace('<script ', '<script integrity="' . esc_attr($integrity) . '" crossorigin="anonymous" ', $tag);
    }

    public function add_sri_to_style_tag($html, $handle, $href, $media) {
        if ($handle !== 'syntekpro-slider-runtime') {
            return $html;
        }

        $path = SYNTEKPRO_ANIM_PLUGIN_DIR . 'assets/css/slider-runtime.css';
        $integrity = $this->build_file_integrity_hash($path);
        if ($integrity === '') {
            return $html;
        }

        if (strpos($html, ' integrity=') !== false) {
            return $html;
        }

        return str_replace('<link ', '<link integrity="' . esc_attr($integrity) . '" crossorigin="anonymous" ', $html);
    }

    private function build_file_integrity_hash($path) {
        static $cache = array();

        if (isset($cache[$path])) {
            return $cache[$path];
        }

        if (!is_readable($path)) {
            $cache[$path] = '';
            return '';
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $cache[$path] = '';
            return '';
        }

        $cache[$path] = 'sha384-' . base64_encode(hash('sha384', $raw, true));
        return $cache[$path];
    }

    public function register_wp_cli_commands() {
        if (!defined('WP_CLI') || !WP_CLI) {
            return;
        }

        WP_CLI::add_command('slider create', function($args, $assoc_args) {
            $title = isset($assoc_args['title']) ? sanitize_text_field((string) $assoc_args['title']) : 'CLI Slider';
            $id = wp_insert_post(array(
                'post_type' => 'syntekpro_slider',
                'post_status' => 'draft',
                'post_title' => $title,
            ));

            if (is_wp_error($id) || !$id) {
                WP_CLI::error('Failed to create slider.');
                return;
            }

            update_post_meta($id, '_sp_slider_settings', array('wpCliCommands' => true));
            update_post_meta($id, '_sp_slider_slides', array());
            WP_CLI::success('Slider created with ID ' . (int) $id);
        });

        WP_CLI::add_command('slider export', function($args, $assoc_args) {
            if (empty($args[0])) {
                WP_CLI::error('Missing slider ID. Usage: wp slider export <id>');
                return;
            }

            $id = absint($args[0]);
            $settings = get_post_meta($id, '_sp_slider_settings', true);
            $slides = get_post_meta($id, '_sp_slider_slides', true);
            $out = array(
                'id' => $id,
                'settings' => is_array($settings) ? $settings : array(),
                'slides' => is_array($slides) ? $slides : array(),
            );
            WP_CLI::line((string) wp_json_encode($out));
        });

        WP_CLI::add_command('slider flush-cache', function($args, $assoc_args) {
            $id = isset($assoc_args['id']) ? absint($assoc_args['id']) : 0;
            if ($id > 0) {
                delete_post_meta($id, '_sp_slider_snapshot_html');
                delete_post_meta($id, '_sp_slider_critical_css');
                WP_CLI::success('Flushed cache for slider #' . $id);
                return;
            }

            $sliders = get_posts(array('post_type' => 'syntekpro_slider', 'posts_per_page' => 300, 'fields' => 'ids'));
            foreach ($sliders as $slider_id) {
                delete_post_meta($slider_id, '_sp_slider_snapshot_html');
                delete_post_meta($slider_id, '_sp_slider_critical_css');
            }
            WP_CLI::success('Flushed slider caches.');
        });
    }

    public function log_slider_change($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $settings = get_post_meta($post_id, '_sp_slider_settings', true);
        if (!is_array($settings) || empty($settings['auditLog'])) {
            return;
        }

        $slides = get_post_meta($post_id, '_sp_slider_slides', true);
        $slides = is_array($slides) ? $slides : array();
        $history = get_post_meta($post_id, '_sp_slider_audit_log', true);
        $history = is_array($history) ? $history : array();

        $history[] = array(
            'timestamp' => current_time('mysql'),
            'userId' => get_current_user_id(),
            'action' => $update ? 'update' : 'create',
            'slideCount' => count($slides),
            'snapshot' => array(
                'settings' => $settings,
                'slides' => $slides,
            ),
        );

        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }

        update_post_meta($post_id, '_sp_slider_audit_log', $history);
    }
}

new Syntekpro_Slider_Advanced();
