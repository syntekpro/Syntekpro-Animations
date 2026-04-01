<?php defined( 'ABSPATH' ) || exit;
$settings = get_option( 'spslider_global_settings', [] );
require_once SPSLIDER_DIR . 'admin/partials/page-header.php';
$spslider_sidenav_sections = [
    [ 'id' => 'sp-general',   'label' => 'General',          'icon' => '⚙' ],
    [ 'id' => 'sp-shortcode', 'label' => 'Shortcode Usage',  'icon' => '🔗' ],
    [ 'id' => 'sp-jsapi',     'label' => 'JavaScript API',   'icon' => '📜' ],
    [ 'id' => 'sp-hooks',     'label' => 'PHP Hooks',        'icon' => '🧩' ],
];
?>
<div class="wrap spslider-admin-wrap">
    <div class="spslider-page-layout">
    <?php require_once SPSLIDER_DIR . 'admin/partials/page-sidenav.php'; ?>
    <div class="spslider-page-content">
    <h1 class="spslider-admin-title spslider-subpage-title">
        <span class="spslider-logo">&#9654;</span>
        <?php esc_html_e( 'SyntekPro Slider — Global Settings', 'syntekpro-slider' ); ?>
    </h1>
    <div class="spslider-card spslider-section-anchor" id="sp-general">
        <form id="spslider-global-settings-form" method="post">
            <?php wp_nonce_field( 'spslider_save_global', 'spslider_settings_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th><label><?php esc_html_e( 'Enable Analytics', 'syntekpro-slider' ); ?></label></th>
                        <td>
                            <input type="checkbox" name="analytics_enabled" value="1" <?php checked( ! empty( $settings['analytics_enabled'] ) ); ?>>
                            <p class="description"><?php esc_html_e( 'Track slide views, layer clicks, and navigation events.', 'syntekpro-slider' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php esc_html_e( 'Lazy Load Images', 'syntekpro-slider' ); ?></label></th>
                        <td>
                            <input type="checkbox" name="lazy_load" value="1" <?php checked( ! empty( $settings['lazy_load'] ) ); ?>>
                            <p class="description"><?php esc_html_e( 'Load slide images only when they are about to become visible.', 'syntekpro-slider' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php esc_html_e( 'Optimise Assets', 'syntekpro-slider' ); ?></label></th>
                        <td>
                            <input type="checkbox" name="optimize_assets" value="1" <?php checked( ! empty( $settings['optimize_assets'] ) ); ?>>
                            <p class="description"><?php esc_html_e( 'Combine and minify JS/CSS per slider. Scripts only enqueued on pages that use the slider.', 'syntekpro-slider' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php esc_html_e( 'Generate WebP Images', 'syntekpro-slider' ); ?></label></th>
                        <td>
                            <input type="checkbox" name="generate_webp" value="1" <?php checked( ! empty( $settings['generate_webp'] ) ); ?>>
                            <p class="description"><?php esc_html_e( 'Auto-generate WebP versions of uploaded images. Requires GD/Imagick with WebP support.', 'syntekpro-slider' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php esc_html_e( 'GA4 Measurement ID', 'syntekpro-slider' ); ?></label></th>
                        <td>
                            <input type="text" name="ga4_measurement_id" value="<?php echo esc_attr( $settings['ga4_measurement_id'] ?? '' ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                            <p class="description"><?php esc_html_e( 'Push analytics events to GA4 via Measurement Protocol.', 'syntekpro-slider' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php esc_html_e( 'GA4 API Secret', 'syntekpro-slider' ); ?></label></th>
                        <td>
                            <input type="password" name="ga4_api_secret" value="<?php echo esc_attr( $settings['ga4_api_secret'] ?? '' ); ?>" class="regular-text">
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'syntekpro-slider' ); ?></button>
            </p>
        </form>
        <div id="spslider-settings-msg" class="notice" style="display:none;"></div>
    </div>

    <div class="spslider-card spslider-section-anchor" id="sp-shortcode">
        <h2><?php esc_html_e( 'Shortcode Usage', 'syntekpro-slider' ); ?></h2>
        <p><?php esc_html_e( 'Place any slider on a page or post using its shortcode:', 'syntekpro-slider' ); ?></p>
        <code>[syntekpro_slider id="1"]</code>
        <p><?php esc_html_e( 'In a PHP template:', 'syntekpro-slider' ); ?></p>
        <code>&lt;?php echo do_shortcode(\'[syntekpro_slider id="1"]\'); ?&gt;</code>
    </div>

    <div class="spslider-card spslider-section-anchor" id="sp-jsapi">
        <h2><?php esc_html_e( 'JavaScript API', 'syntekpro-slider' ); ?></h2>
        <p><?php esc_html_e( 'Control sliders externally from your theme or plugin:', 'syntekpro-slider' ); ?></p>
        <pre><code>// Get a slider instance
var slider = SPSlider.get(1);

// Control methods
slider.play();
slider.pause();
slider.goTo(2);        // zero-based index
slider.next();
slider.prev();
slider.destroy();

// Events
slider.on('slideChange', function(data) {
    console.log('Now on slide', data.index);
});
slider.on('layerClick', function(data) {
    console.log('Layer clicked', data.layerId);
});</code></pre>
    </div>

    <div class="spslider-card spslider-section-anchor" id="sp-hooks">
        <h2><?php esc_html_e( 'PHP Developer Hooks', 'syntekpro-slider' ); ?></h2>
        <pre><code>// Register a custom layer type
spslider_register_layer_type('countdown', function($settings) {
    return '&lt;div class="my-countdown"&gt;' . esc_html($settings['content']) . '&lt;/div&gt;';
});

// Filter generated slider settings before output
add_filter('spslider_slider_settings', function($settings, $slider_id) {
    $settings['autoplay'] = false; // disable autoplay for all sliders
    return $settings;
}, 10, 2);

// Hook after slider is rendered
add_action('spslider_after_render', function($slider_id) {
    // custom logic
});</code></pre>
    </div>
</div>
<script>
jQuery(function($) {
    $('#spslider-global-settings-form').on('submit', function(e) {
        e.preventDefault();
        var data = {};
        $(this).serializeArray().forEach(function(f){ data[f.name] = true; });
        $.post(ajaxurl, {
            action: 'spslider_save_global',
            nonce: '<?php echo esc_js( wp_create_nonce( 'spslider_nonce' ) ); ?>',
            settings: JSON.stringify({
                analytics_enabled:  !!data.analytics_enabled,
                lazy_load:          !!data.lazy_load,
                optimize_assets:    !!data.optimize_assets,
                generate_webp:      !!data.generate_webp,
                ga4_measurement_id: $('[name=ga4_measurement_id]').val(),
                ga4_api_secret:     $('[name=ga4_api_secret]').val(),
            })
        }, function(res) {
            var $msg = $('#spslider-settings-msg');
            $msg.removeClass('notice-success notice-error').css('display','block');
            if (res.success) {
                $msg.addClass('notice-success').html('<p><?php echo esc_js( __( 'Settings saved!', 'syntekpro-slider' ) ); ?></p>');
            } else {
                $msg.addClass('notice-error').html('<p><?php echo esc_js( __( 'Error saving settings.', 'syntekpro-slider' ) ); ?></p>');
            }
        });
    });
});
</script>
    </div><!-- /page-content -->
    </div><!-- /page-layout -->
