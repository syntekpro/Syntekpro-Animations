<?php
/**
 * Page Side Navigation
 *
 * Usage:
 *   $spslider_sidenav_sections = [
 *       [ 'id' => 'section-id', 'label' => 'Section Label', 'icon' => '⚙' ],
 *   ];
 *   require_once SPSLIDER_DIR . 'admin/partials/page-sidenav.php';
 *
 * Then wrap your page content in:
 *   <div class="spslider-page-layout">
 *     <!-- sidenav is already rendered below -->
 *     <div class="spslider-page-content"> ... </div>
 *   </div>
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $spslider_sidenav_sections ) || ! is_array( $spslider_sidenav_sections ) ) {
    return;
}
?>
<nav class="spslider-sidenav" aria-label="<?php esc_attr_e( 'Page Sections', 'syntekpro-slider' ); ?>">
    <div class="spslider-sidenav-title"><?php esc_html_e( 'On This Page', 'syntekpro-slider' ); ?></div>
    <ul>
        <?php foreach ( $spslider_sidenav_sections as $section ) : ?>
        <li>
            <a href="#<?php echo esc_attr( $section['id'] ); ?>" data-target="<?php echo esc_attr( $section['id'] ); ?>">
                <?php if ( ! empty( $section['icon'] ) ) : ?>
                    <span class="sn-icon"><?php echo esc_html( $section['icon'] ); ?></span>
                <?php endif; ?>
                <?php echo esc_html( $section['label'] ); ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
<script>
(function(){
    document.addEventListener('DOMContentLoaded', function(){
        var nav = document.querySelector('.spslider-sidenav');
        if (!nav) return;
        var links = nav.querySelectorAll('a[data-target]');
        if (!links.length) return;

        // Collect section elements targeted by sidenav links
        var sections = [];
        links.forEach(function(link){
            var el = document.getElementById(link.dataset.target);
            if (el) sections.push(el);
        });

        // Single-section pages don't need tab behaviour
        if (sections.length <= 1) return;

        function activate(targetId){
            sections.forEach(function(sec){
                sec.style.display = (sec.id === targetId) ? '' : 'none';
            });
            links.forEach(function(l){
                l.classList.toggle('active', l.dataset.target === targetId);
            });
            var target = document.getElementById(targetId);
            if (target) {
                target.dispatchEvent(new CustomEvent('spslider:tab-shown', { bubbles: true }));
            }
        }

        links.forEach(function(link){
            link.addEventListener('click', function(e){
                e.preventDefault();
                activate(link.dataset.target);
                history.replaceState(null, '', '#' + link.dataset.target);
            });
        });

        // Restore tab from URL hash, otherwise default to first
        var hash = window.location.hash.replace('#','');
        var found = false;
        sections.forEach(function(sec){ if (sec.id === hash) found = true; });
        activate(found ? hash : sections[0].id);
    });
})();
</script>
