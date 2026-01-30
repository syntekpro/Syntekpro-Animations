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
            <img src="<?php echo esc_url(Syntekpro_Animations_Admin::get_logo_url()); ?>" alt="Syntekpro Logo" />
            <div class="syntekpro-brand-content">
                <div class="brand-title">Syntekpro Animations</div>
                <div class="brand-desc">Professional GSAP-powered animations for WordPress</div>
                <div class="brand-version">Version <?php echo SYNTEKPRO_ANIM_VERSION; ?></div>
            </div>
        </div>
    </div>
</div>
