<?php defined( 'ABSPATH' ) || exit;
require_once SPSLIDER_DIR . 'admin/partials/page-header.php';
$spslider_sidenav_sections = [
    [ 'id' => 'sp-all-sliders', 'label' => 'All Sliders',  'icon' => '🖼' ],
];
?>
<div class="wrap spslider-admin-wrap">
    <div class="spslider-page-layout">
    <?php require_once SPSLIDER_DIR . 'admin/partials/page-sidenav.php'; ?>
    <div class="spslider-page-content">
    <h1 class="spslider-admin-title spslider-subpage-title spslider-section-anchor" id="sp-all-sliders">
        <span class="spslider-logo">&#9654;</span>
        <?php esc_html_e( 'SyntekPro Slider', 'syntekpro-slider' ); ?>
        <button id="spslider-create-btn" class="page-title-action spslider-btn-primary">
            + <?php esc_html_e( 'New Slider', 'syntekpro-slider' ); ?>
        </button>
    </h1>

    <div id="spslider-create-form" class="spslider-card spslider-create-form spslider-section-anchor" data-anchor="sp-create-new" style="display:none;">
        <h3><?php esc_html_e( 'Create New Slider', 'syntekpro-slider' ); ?></h3>
        <input type="text" id="spslider-new-name" class="regular-text" placeholder="<?php esc_attr_e( 'Slider name…', 'syntekpro-slider' ); ?>">
        <button id="spslider-confirm-create" class="button button-primary"><?php esc_html_e( 'Create', 'syntekpro-slider' ); ?></button>
        <button id="spslider-cancel-create" class="button"><?php esc_html_e( 'Cancel', 'syntekpro-slider' ); ?></button>
    </div>

    <?php
    $sliders = SPSLIDER_Database::get_sliders();
    if ( empty( $sliders ) ) : ?>
        <div class="spslider-empty-state">
            <div class="spslider-empty-icon">&#9654;</div>
            <h2><?php esc_html_e( 'No sliders yet', 'syntekpro-slider' ); ?></h2>
            <p><?php esc_html_e( 'Create your first slider and start building amazing visual experiences.', 'syntekpro-slider' ); ?></p>
            <button class="button button-primary button-hero" onclick="document.getElementById('spslider-create-btn').click()">
                <?php esc_html_e( 'Create Your First Slider', 'syntekpro-slider' ); ?>
            </button>
        </div>
    <?php else : ?>
        <div class="spslider-grid" id="spslider-grid">
            <?php foreach ( $sliders as $slider ) :
                $slide_count = count( SPSLIDER_Database::get_slides( $slider->id ) );
                $edit_url    = admin_url( 'admin.php?page=spslider-editor&slider_id=' . $slider->id );
                $analytics_url = admin_url( 'admin.php?page=spslider-analytics&slider_id=' . $slider->id );
                $shortcode   = '[syntekpro_slider id="' . $slider->id . '"]';
            ?>
            <div class="spslider-card spslider-slider-card" data-id="<?php echo esc_attr( $slider->id ); ?>">
                <div class="spslider-card-header">
                    <div class="spslider-card-icon">&#9654;</div>
                    <div class="spslider-card-meta">
                        <h3 class="spslider-card-title"><?php echo esc_html( $slider->name ); ?></h3>
                        <span class="spslider-badge"><?php echo esc_html( sprintf( _n( '%d slide', '%d slides', $slide_count, 'syntekpro-slider' ), $slide_count ) ); ?></span>
                    </div>
                    <div class="spslider-card-status <?php echo $slider->status ? 'active' : 'inactive'; ?>">
                        <?php echo $slider->status ? esc_html__( 'Active', 'syntekpro-slider' ) : esc_html__( 'Inactive', 'syntekpro-slider' ); ?>
                    </div>
                </div>
                <div class="spslider-card-shortcode">
                    <code><?php echo esc_html( $shortcode ); ?></code>
                    <button class="spslider-copy-btn" data-copy="<?php echo esc_attr( $shortcode ); ?>" title="<?php esc_attr_e( 'Copy shortcode', 'syntekpro-slider' ); ?>">&#128203;</button>
                </div>
                <div class="spslider-card-info">
                    <small><?php echo esc_html( sprintf( __( 'Created: %s', 'syntekpro-slider' ), date_i18n( get_option( 'date_format' ), strtotime( $slider->created_at ) ) ) ); ?></small>
                </div>
                <div class="spslider-card-actions">
                    <a href="<?php echo esc_url( $edit_url ); ?>" class="button button-primary spslider-btn-edit">
                        &#9998; <?php esc_html_e( 'Edit', 'syntekpro-slider' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $analytics_url ); ?>" class="button">
                        &#128200; <?php esc_html_e( 'Analytics', 'syntekpro-slider' ); ?>
                    </a>
                    <button class="button spslider-btn-duplicate" data-id="<?php echo esc_attr( $slider->id ); ?>">
                        &#128203; <?php esc_html_e( 'Duplicate', 'syntekpro-slider' ); ?>
                    </button>
                    <button class="button spslider-btn-delete" data-id="<?php echo esc_attr( $slider->id ); ?>">
                        &#128465; <?php esc_html_e( 'Delete', 'syntekpro-slider' ); ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div><!-- /page-content -->
    </div><!-- /page-layout -->
</div>
