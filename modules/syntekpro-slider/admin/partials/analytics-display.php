<?php
defined( 'ABSPATH' ) || exit;

$slider_id = isset( $_GET['slider_id'] ) ? (int) $_GET['slider_id'] : 0;
$days      = isset( $_GET['days'] )      ? (int) $_GET['days']      : 30;
$sliders   = SPSLIDER_Database::get_sliders();

$stats = null;
if ( $slider_id ) {
    $stats = SPSLIDER_Analytics::get_stats( $slider_id, $days );
}

$export_url = admin_url( 'admin-ajax.php?action=spslider_export_csv&slider_id=' . $slider_id . '&days=' . $days . '&nonce=' . wp_create_nonce( 'spslider_nonce' ) );
require_once SPSLIDER_DIR . 'admin/partials/page-header.php';
$spslider_sidenav_sections = [
    [ 'id' => 'sp-filters',  'label' => 'Filters',       'icon' => '🔍' ],
    [ 'id' => 'sp-overview', 'label' => 'KPI Overview',  'icon' => '📊' ],
    [ 'id' => 'sp-daily',    'label' => 'Daily Views',   'icon' => '📈' ],
    [ 'id' => 'sp-stats',    'label' => 'Slide & Layer Stats', 'icon' => '📄' ],
];
?>
<div class="wrap spslider-admin-wrap">
    <div class="spslider-page-layout">
    <?php require_once SPSLIDER_DIR . 'admin/partials/page-sidenav.php'; ?>
    <div class="spslider-page-content">
    <h1 class="spslider-admin-title spslider-subpage-title">
        <span class="spslider-logo">&#9654;</span>
        <?php esc_html_e( 'SyntekPro Slider — Analytics', 'syntekpro-slider' ); ?>
    </h1>

    <!-- Filters -->
    <div class="spslider-card spslider-analytics-filters spslider-section-anchor" id="sp-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="spslider-analytics">
            <label><?php esc_html_e( 'Slider:', 'syntekpro-slider' ); ?>
                <select name="slider_id">
                    <option value=""><?php esc_html_e( '— Select Slider —', 'syntekpro-slider' ); ?></option>
                    <?php foreach ( $sliders as $s ) : ?>
                    <option value="<?php echo esc_attr( $s->id ); ?>" <?php selected( $s->id, $slider_id ); ?>>
                        <?php echo esc_html( $s->name ); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><?php esc_html_e( 'Period:', 'syntekpro-slider' ); ?>
                <select name="days">
                    <option value="7"  <?php selected( 7,  $days ); ?>><?php esc_html_e( 'Last 7 days',  'syntekpro-slider' ); ?></option>
                    <option value="30" <?php selected( 30, $days ); ?>><?php esc_html_e( 'Last 30 days', 'syntekpro-slider' ); ?></option>
                    <option value="90" <?php selected( 90, $days ); ?>><?php esc_html_e( 'Last 90 days', 'syntekpro-slider' ); ?></option>
                </select>
            </label>
            <button type="submit" class="button"><?php esc_html_e( 'Apply', 'syntekpro-slider' ); ?></button>
            <?php if ( $slider_id ) : ?>
            <a href="<?php echo esc_url( $export_url ); ?>" class="button"><?php esc_html_e( '&#11123; Export CSV', 'syntekpro-slider' ); ?></a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ( $stats ) : ?>

    <!-- KPI row -->
    <div class="spslider-analytics-kpis spslider-section-anchor" id="sp-overview">
        <div class="spslider-kpi">
            <span class="spslider-kpi-value"><?php echo esc_html( number_format( $stats['total_views'] ) ); ?></span>
            <span class="spslider-kpi-label"><?php esc_html_e( 'Total Views', 'syntekpro-slider' ); ?></span>
        </div>
        <div class="spslider-kpi">
            <span class="spslider-kpi-value"><?php echo esc_html( number_format( $stats['unique_sessions'] ) ); ?></span>
            <span class="spslider-kpi-label"><?php esc_html_e( 'Unique Sessions', 'syntekpro-slider' ); ?></span>
        </div>
        <div class="spslider-kpi">
            <span class="spslider-kpi-value"><?php echo esc_html( number_format( $stats['nav_swipe'] ) ); ?></span>
            <span class="spslider-kpi-label"><?php esc_html_e( 'Swipe Navigations', 'syntekpro-slider' ); ?></span>
        </div>
        <div class="spslider-kpi">
            <span class="spslider-kpi-value"><?php echo esc_html( number_format( $stats['nav_click'] ) ); ?></span>
            <span class="spslider-kpi-label"><?php esc_html_e( 'Click Navigations', 'syntekpro-slider' ); ?></span>
        </div>
    </div>

    <!-- Daily chart -->
    <div class="spslider-card spslider-section-anchor" id="sp-daily">
        <h2><?php esc_html_e( 'Daily Views', 'syntekpro-slider' ); ?></h2>
        <canvas id="spslider-chart-daily" height="80"
            data-chart="<?php echo esc_attr( wp_json_encode( $stats['daily'] ) ); ?>"
        ></canvas>
    </div>

    <div class="spslider-analytics-grid spslider-section-anchor" id="sp-stats">
        <!-- Per-slide views -->
        <div class="spslider-card">
            <h2><?php esc_html_e( 'Slide Views', 'syntekpro-slider' ); ?></h2>
            <?php if ( empty( $stats['slide_views'] ) ) : ?>
                <p><?php esc_html_e( 'No data yet.', 'syntekpro-slider' ); ?></p>
            <?php else : ?>
            <table class="widefat">
                <thead><tr>
                    <th><?php esc_html_e( 'Slide ID', 'syntekpro-slider' ); ?></th>
                    <th><?php esc_html_e( 'Views', 'syntekpro-slider' ); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ( $stats['slide_views'] as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row->slide_id ); ?></td>
                    <td><?php echo esc_html( number_format( $row->views ) ); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Per-layer clicks -->
        <div class="spslider-card">
            <h2><?php esc_html_e( 'Layer Click-Through', 'syntekpro-slider' ); ?></h2>
            <?php if ( empty( $stats['layer_clicks'] ) ) : ?>
                <p><?php esc_html_e( 'No layer clicks recorded yet.', 'syntekpro-slider' ); ?></p>
            <?php else : ?>
            <table class="widefat">
                <thead><tr>
                    <th><?php esc_html_e( 'Layer ID', 'syntekpro-slider' ); ?></th>
                    <th><?php esc_html_e( 'Clicks', 'syntekpro-slider' ); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ( $stats['layer_clicks'] as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row->layer_id ); ?></td>
                    <td><?php echo esc_html( number_format( $row->clicks ) ); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function() {
        function renderChart() {
            var canvas = document.getElementById('spslider-chart-daily');
            if (!canvas || canvas.dataset.rendered) return;
            var parent = canvas.parentElement;
            if (!parent || parent.clientWidth === 0) return;
            canvas.dataset.rendered = '1';

            var data   = JSON.parse(canvas.getAttribute('data-chart') || '[]');
            if (!data.length) { parent.querySelector('h2').insertAdjacentHTML('afterend','<p><?php echo esc_js(__('No data for this period.','syntekpro-slider')); ?></p>'); canvas.remove(); return; }
            var ctx    = canvas.getContext('2d');
            var labels = data.map(function(r){ return r.day; });
            var vals   = data.map(function(r){ return parseInt(r.views,10); });
            var maxV   = Math.max.apply(null, vals) || 1;
            var w = canvas.width = parent.clientWidth;
            var h = canvas.height = 120;
            var padL = 40, padR = 10, padT = 10, padB = 30;
            var cw = w - padL - padR, ch = h - padT - padB;
            ctx.clearRect(0,0,w,h);
            ctx.strokeStyle='#e5e7eb'; ctx.lineWidth=1;
            [0,0.25,0.5,0.75,1].forEach(function(t){
                var y = padT + ch*(1-t);
                ctx.beginPath(); ctx.moveTo(padL,y); ctx.lineTo(padL+cw,y); ctx.stroke();
                ctx.fillStyle='#9ca3af'; ctx.font='10px sans-serif';
                ctx.fillText(Math.round(maxV*t), 2, y+4);
            });
            ctx.strokeStyle='#6366f1'; ctx.lineWidth=2; ctx.beginPath();
            vals.forEach(function(v,i){
                var x = padL + cw*i/(vals.length-1||1);
                var y = padT + ch*(1 - v/maxV);
                i===0 ? ctx.moveTo(x,y) : ctx.lineTo(x,y);
            });
            ctx.stroke();
            ctx.fillStyle='rgba(99,102,241,0.12)';
            ctx.lineTo(padL+cw, padT+ch); ctx.lineTo(padL, padT+ch); ctx.closePath(); ctx.fill();
            ctx.fillStyle='#555'; ctx.font='10px sans-serif';
            labels.forEach(function(l,i){
                if(i%(Math.ceil(labels.length/6))===0){
                    var x=padL+cw*i/(labels.length-1||1);
                    ctx.fillText(l.slice(5),x-12,h-5);
                }
            });
        }
        // Try now (visible if Daily Views is the active tab)
        renderChart();
        // Also render when tab becomes visible
        var el = document.getElementById('sp-daily');
        if (el) el.addEventListener('spslider:tab-shown', renderChart);
    })();
    </script>

    <?php else : ?>
        <div class="spslider-card">
            <p><?php esc_html_e( 'Select a slider above to view its analytics.', 'syntekpro-slider' ); ?></p>
        </div>
    <?php endif; ?>
    </div><!-- /page-content -->
    </div><!-- /page-layout -->
</div>
