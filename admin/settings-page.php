<?php
/**
 * Admin Settings Page Template
 * 
 * This file renders the main settings page for Syntekpro Animations
 * with branding and full functionality.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap syntekpro-settings-wrapper">
    <!-- Banner with Logo -->
    <div class="syntekpro-admin-banner">
        <div class="syntekpro-admin-branding">
            <img src="<?php echo esc_url(SYNTEKPRO_ANIM_PLUGIN_URL . 'assets/img/Syntekpro%20Animations%20Transparent%20Logo%20with%20Favicon.png'); ?>" alt="Syntekpro Logo" />
            <div class="syntekpro-brand-content">
                <div class="brand-title">Syntekpro Animations</div>
                <div class="brand-desc">Professional GSAP-powered animations for WordPress</div>
                <div class="brand-version">Version <?php echo SYNTEKPRO_ANIM_VERSION; ?></div>
            </div>
        </div>
    </div>
</div>
