<?php
defined( 'ABSPATH' ) || exit;

require_once SPSLIDER_DIR . 'includes/class-templates.php';

/* Handle "Use Template" form submission */
if ( isset( $_POST['spslider_template_id'], $_POST['spslider_tpl_nonce'] )
     && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spslider_tpl_nonce'] ) ), 'spslider_use_template' )
) {
    $template_id   = sanitize_text_field( wp_unslash( $_POST['spslider_template_id'] ) );
    $template_name = isset( $_POST['spslider_template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['spslider_template_name'] ) ) : 'Template Slider';

    /* Create a new slider */
    require_once SPSLIDER_DIR . 'includes/class-database.php';
    $slider_id = SPSLIDER_Database::create_slider( [
        'name'     => $template_name,
        'settings' => [ 'width' => 1200, 'height' => 600, 'autoplay' => true ],
    ] );

    if ( $slider_id ) {
        /* Import the template into this slider */
        SPSLIDER_Templates::import( $slider_id, $template_id );
        wp_safe_redirect( admin_url( 'admin.php?page=spslider-editor&slider_id=' . $slider_id ) );
        exit;
    }
}

$categories = SPSLIDER_Templates::get_all();
$all_templates = [];
foreach ( $categories as $cat => $templates ) {
    foreach ( $templates as $tpl ) {
        $tpl['category'] = $cat;
        $all_templates[]  = $tpl;
    }
}

require_once SPSLIDER_DIR . 'admin/partials/page-header.php';
?>
<div class="wrap spslider-admin-wrap">
    <div class="spslider-page-content" style="max-width:1200px;margin:0 auto;">

    <h1 class="spslider-admin-title spslider-subpage-title">
        <span class="spslider-logo">&#128247;</span>
        <?php esc_html_e( 'Starter Templates', 'syntekpro-slider' ); ?>
    </h1>

    <p class="spslider-page-desc"><?php esc_html_e( 'Choose a starter template to create a new slider with pre-designed slides and layers.', 'syntekpro-slider' ); ?></p>

    <!-- Category Filters -->
    <div class="spslider-tpl-filters" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
        <button class="button spslider-tpl-filter active" data-cat="all"><?php esc_html_e( 'All', 'syntekpro-slider' ); ?></button>
        <?php foreach ( array_keys( $categories ) as $cat ) : ?>
            <button class="button spslider-tpl-filter" data-cat="<?php echo esc_attr( $cat ); ?>">
                <?php echo esc_html( ucfirst( $cat ) ); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Templates Grid -->
    <div class="spslider-tpl-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
        <?php if ( empty( $all_templates ) ) : ?>
            <p><?php esc_html_e( 'No templates available.', 'syntekpro-slider' ); ?></p>
        <?php else : ?>
            <?php foreach ( $all_templates as $tpl ) :
                $thumb = ! empty( $tpl['thumb'] ) ? $tpl['thumb'] : '';
            ?>
            <div class="spslider-tpl-card" data-cat="<?php echo esc_attr( $tpl['category'] ); ?>" data-id="<?php echo esc_attr( $tpl['id'] ); ?>"
                 style="background:var(--spe-mid,#fff);border:1px solid #ddd;border-radius:10px;overflow:hidden;cursor:pointer;transition:box-shadow .2s;">
                <?php if ( $thumb ) : ?>
                    <div style="height:180px;overflow:hidden;">
                        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $tpl['name'] ); ?>"
                             style="width:100%;height:100%;object-fit:cover;" loading="lazy" />
                    </div>
                <?php else : ?>
                    <div style="height:180px;display:flex;align-items:center;justify-content:center;background:#1a1a2e;color:#fff;font-size:48px;font-weight:700;">
                        <?php echo esc_html( mb_substr( $tpl['name'], 0, 1 ) ); ?>
                    </div>
                <?php endif; ?>
                <div style="padding:14px;">
                    <h3 style="margin:0 0 4px;font-size:15px;"><?php echo esc_html( $tpl['name'] ); ?></h3>
                    <span style="font-size:12px;color:#888;text-transform:capitalize;"><?php echo esc_html( $tpl['category'] ); ?></span>
                    <span style="font-size:12px;color:#888;margin-left:6px;">&bull; <?php echo (int) count( $tpl['layers'] ?? [] ); ?> layers</span>
                </div>
                <div style="padding:0 14px 14px;">
                    <form method="post" class="spslider-tpl-use-form">
                        <?php wp_nonce_field( 'spslider_use_template', 'spslider_tpl_nonce' ); ?>
                        <input type="hidden" name="spslider_template_id" value="<?php echo esc_attr( $tpl['id'] ); ?>" />
                        <input type="hidden" name="spslider_template_name" value="<?php echo esc_attr( $tpl['name'] ); ?>" />
                        <button type="submit" class="button button-primary" style="width:100%;">
                            <?php esc_html_e( 'Use Template', 'syntekpro-slider' ); ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    </div>
</div>

<script>
(function(){
    /* Category filter */
    var btns  = document.querySelectorAll('.spslider-tpl-filter');
    var cards = document.querySelectorAll('.spslider-tpl-card');
    btns.forEach(function(btn){
        btn.addEventListener('click', function(){
            btns.forEach(function(b){ b.classList.remove('active'); });
            btn.classList.add('active');
            var cat = btn.getAttribute('data-cat');
            cards.forEach(function(c){
                c.style.display = (cat === 'all' || c.getAttribute('data-cat') === cat) ? '' : 'none';
            });
        });
    });
})();
</script>
