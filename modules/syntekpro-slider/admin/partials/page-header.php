<?php defined( 'ABSPATH' ) || exit; ?>
<div class="spslider-page-header">
    <div class="spslider-header-left"></div>

    <div class="spslider-header-logo">
        <a href="https://plugins.syntekpro.com/slider" target="_blank" rel="noopener">
            <img src="<?php echo esc_url( SPSLIDER_URL . 'assets/img/SyntekPro Slider Logo.png' ); ?>"
                 alt="SyntekPro Slider" />
        </a>
    </div>

    <div class="spslider-header-right">
        <a href="https://plugins.syntekpro.com/support" target="_blank" rel="noopener" class="spslider-support-btn">
            &#9993; <?php esc_html_e( 'Support', 'syntekpro-slider' ); ?>
        </a>
        <a href="https://plugins.syntekpro.com/slider" target="_blank" rel="noopener" class="spslider-version-btn">
            v<?php echo esc_html( SPSLIDER_VERSION ); ?>
        </a>
    </div>
</div>
