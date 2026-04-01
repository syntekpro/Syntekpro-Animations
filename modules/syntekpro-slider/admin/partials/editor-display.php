<?php
defined( 'ABSPATH' ) || exit;
$slider_id = isset( $_GET['slider_id'] ) ? (int) $_GET['slider_id'] : 0;
$slider    = $slider_id ? SPSLIDER_Database::get_slider( $slider_id ) : null;
if ( ! $slider ) wp_die( esc_html__( 'Slider not found.', 'syntekpro-slider' ) );
require_once SPSLIDER_DIR . 'admin/partials/page-header.php';
?>
<div id="spslider-editor-app" data-slider-id="<?php echo esc_attr( $slider_id ); ?>">

    <!-- ── Top Toolbar ──────────────────────────────────────────────────── -->
    <header class="spe-toolbar">
        <div class="spe-toolbar-left">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=syntekpro-slider' ) ); ?>" class="spe-back-btn" title="<?php esc_attr_e( 'Back to sliders', 'syntekpro-slider' ); ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <span class="spe-slider-name" id="spe-slider-name" contenteditable="true"><?php echo esc_html( $slider->name ); ?></span>
        </div>
        <div class="spe-toolbar-center">
            <div class="spe-toolbar-group">
                <button id="spe-undo" class="spe-tool-btn" title="<?php esc_attr_e( 'Undo (Ctrl+Z)', 'syntekpro-slider' ); ?>" disabled>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 6h7a3 3 0 110 6H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 3L3 6l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button id="spe-redo" class="spe-tool-btn" title="<?php esc_attr_e( 'Redo (Ctrl+Y)', 'syntekpro-slider' ); ?>" disabled>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 6H6a3 3 0 100 6h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 3l3 3-3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <span class="spe-toolbar-sep"></span>
            <div class="spe-breakpoint-switcher">
                <button class="spe-bp-btn active" data-bp="desktop" title="<?php esc_attr_e( 'Desktop (1200px)', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="10" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 14h6M8 12v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
                <button class="spe-bp-btn" data-bp="tablet" title="<?php esc_attr_e( 'Tablet (768px)', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="3" y="1" width="10" height="14" rx="1.5" stroke="currentColor" stroke-width="1.5"/><circle cx="8" cy="13" r=".75" fill="currentColor"/></svg>
                </button>
                <button class="spe-bp-btn" data-bp="mobile" title="<?php esc_attr_e( 'Mobile (375px)', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="4" y="1" width="8" height="14" rx="1.5" stroke="currentColor" stroke-width="1.5"/><circle cx="8" cy="13" r=".75" fill="currentColor"/></svg>
                </button>
            </div>
            <span class="spe-toolbar-sep"></span>
            <div class="spe-toolbar-group">
                <button id="spe-theme-btn" class="spe-tool-btn" title="<?php esc_attr_e( 'Global Styles', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 15A7 7 0 108 1a7 7 0 000 14z" stroke="currentColor" stroke-width="1.5"/><path d="M8 11V8M8 5h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button id="spe-history-btn" class="spe-tool-btn" title="<?php esc_attr_e( 'Action History', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 8a7 7 0 111.4 4.2L1 15l2.8-1.4A7 7 0 011 8z" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3l2 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <span class="spe-toolbar-sep"></span>
            <div class="spe-toolbar-group">
                <button id="spe-timeline-btn" class="spe-tool-btn" title="<?php esc_attr_e( 'Animation Timeline', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="3" width="5" height="2" rx="1" fill="currentColor"/><rect x="3" y="7" width="8" height="2" rx="1" fill="currentColor"/><rect x="2" y="11" width="6" height="2" rx="1" fill="currentColor"/></svg>
                </button>
                <button id="spe-templates-btn" class="spe-tool-btn" title="<?php esc_attr_e( 'Template Library', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="9" y="1" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="1" y="9" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="9" y="9" width="6" height="6" rx="1" stroke="currentColor" stroke-width="1.5"/></svg>
                </button>
                <button id="spe-dynamic-btn" class="spe-tool-btn" title="<?php esc_attr_e( 'Dynamic Content', 'syntekpro-slider' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M5 2a3 3 0 00-3 3v6a3 3 0 003 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M11 2a3 3 0 013 3v6a3 3 0 01-3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M6 6l4 2-4 2V6z" fill="currentColor"/></svg>
                </button>
            </div>
        </div>
        <div class="spe-toolbar-right">
            <button id="spe-settings-btn" class="spe-tool-btn" title="<?php esc_attr_e( 'Slider Settings', 'syntekpro-slider' ); ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 10a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.5"/><path d="M13.5 6.5l-1-.4a4.5 4.5 0 00-.5-.8l.3-1a.5.5 0 00-.1-.5l-.7-.7a.5.5 0 00-.5-.1l-1 .3a4.5 4.5 0 00-.8-.5l-.4-1a.5.5 0 00-.5-.3h-1a.5.5 0 00-.5.3l-.4 1a4.5 4.5 0 00-.8.5l-1-.3a.5.5 0 00-.5.1l-.7.7a.5.5 0 00-.1.5l.3 1a4.5 4.5 0 00-.5.8l-1 .4a.5.5 0 00-.3.5v1a.5.5 0 00.3.5l1 .4c.1.3.3.6.5.8l-.3 1a.5.5 0 00.1.5l.7.7a.5.5 0 00.5.1l1-.3c.3.2.5.4.8.5l.4 1a.5.5 0 00.5.3h1a.5.5 0 00.5-.3l.4-1c.3-.1.6-.3.8-.5l1 .3a.5.5 0 00.5-.1l.7-.7a.5.5 0 00.1-.5l-.3-1c.2-.3.4-.5.5-.8l1-.4a.5.5 0 00.3-.5v-1a.5.5 0 00-.3-.5z" stroke="currentColor" stroke-width="1.2"/></svg>
            </button>
            <button id="spe-preview-btn" class="spe-tool-btn" title="<?php esc_attr_e( 'Preview', 'syntekpro-slider' ); ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1.5 8s2.5-5 6.5-5 6.5 5 6.5 5-2.5 5-6.5 5S1.5 8 1.5 8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/></svg>
            </button>
            <span class="spe-toolbar-sep"></span>
            <button id="spe-save-btn" class="spe-btn-save">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M12.5 14.5h-9a1 1 0 01-1-1v-11a1 1 0 011-1h7l3 3v9a1 1 0 01-1 1z" stroke="currentColor" stroke-width="1.4"/><path d="M5.5 1.5v3h4v-3M5.5 14.5v-4h5v4" stroke="currentColor" stroke-width="1.4"/></svg>
                <?php esc_html_e( 'Save', 'syntekpro-slider' ); ?>
            </button>
            <span id="spe-save-status" class="spe-save-status"></span>
        </div>
    </header>

    <!-- ── Main Body ────────────────────────────────────────────────────── -->
    <div class="spe-body">

        <!-- Layer Panel (left) -->
        <aside class="spe-panel spe-panel-layers">
            <div class="spe-panel-header">
                <span><?php esc_html_e( 'Layers', 'syntekpro-slider' ); ?></span>
                <button id="spe-add-layer-toggle" class="spe-add-layer-toggle" title="<?php esc_attr_e( 'Add Layer', 'syntekpro-slider' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div id="spe-add-layer-drawer" class="spe-add-layer-drawer" style="display:none;">
                <div id="spe-add-layer-btns" class="spe-add-layer-grid">
                    <button class="spe-add-layer-btn" data-type="text" title="<?php esc_attr_e( 'Text Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M3 4h12M9 4v11M6 4V3h6v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'Text', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="image" title="<?php esc_attr_e( 'Image Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="2" y="3" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><circle cx="6.5" cy="7.5" r="1.5" fill="currentColor"/><path d="M2 13l4-4 3 3 2-2 5 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'Image', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="button" title="<?php esc_attr_e( 'Button Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="2" y="5" width="14" height="8" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6 9h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <span><?php esc_html_e( 'Button', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="video" title="<?php esc_attr_e( 'Video Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="2" y="3" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M7 7v4l4-2-4-2z" fill="currentColor"/></svg>
                        <span><?php esc_html_e( 'Video', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="shape" title="<?php esc_attr_e( 'Shape Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="5" stroke="currentColor" stroke-width="1.5"/><rect x="5" y="5" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1" opacity=".4"/></svg>
                        <span><?php esc_html_e( 'Shape', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="countdown" title="<?php esc_attr_e( 'Countdown Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="6" stroke="currentColor" stroke-width="1.5"/><path d="M9 5.5v3.8l2.5 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'Countdown', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="icon" title="<?php esc_attr_e( 'Icon Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 2.5l1.9 3.9 4.3.6-3.1 3 0.8 4.3L9 12.4l-3.9 1.9 0.8-4.3-3.1-3 4.3-.6L9 2.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'Icon', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="lottie" title="<?php esc_attr_e( 'Lottie Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="3" y="3" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M6 11c1.2-2 2.4-3 3.5-3 1.1 0 1.9 0.7 2.5 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'Lottie', 'syntekpro-slider' ); ?></span>
                    </button>
                    <button class="spe-add-layer-btn" data-type="html" title="<?php esc_attr_e( 'HTML Layer', 'syntekpro-slider' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M6 5L3 9l3 4M12 5l3 4-3 4M10 4l-2 10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'HTML', 'syntekpro-slider' ); ?></span>
                    </button>
                </div>
            </div>
            <div id="spe-layers-list" class="spe-layers-list">
                <p class="spe-layers-empty"><?php esc_html_e( 'No layers yet. Click + to add one.', 'syntekpro-slider' ); ?></p>
            </div>
        </aside>

        <!-- Canvas (center) -->
        <main class="spe-canvas-area">
            <div class="spe-canvas-ruler-h"></div>
            <div class="spe-canvas-ruler-v"></div>
            <div class="spe-canvas-scroll">
                <div id="spe-canvas" class="spe-canvas">
                    <div id="spe-canvas-inner" class="spe-canvas-inner">
                        <!-- Slide background -->
                        <div id="spe-slide-bg" class="spe-slide-bg"></div>
                        <!-- Layers container -->
                        <div id="spe-layers-canvas" class="spe-layers-canvas"></div>
                        <!-- Alignment guides -->
                        <div class="spe-guide spe-guide-h" id="spe-guide-h"></div>
                        <div class="spe-guide spe-guide-v" id="spe-guide-v"></div>
                        <!-- Selection box -->
                        <div id="spe-selection-box" class="spe-selection-box"></div>
                    </div>
                </div>
            </div>
            <div class="spe-canvas-zoom-controls">
                <button id="spe-zoom-out" title="<?php esc_attr_e( 'Zoom Out', 'syntekpro-slider' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 7h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
                <span id="spe-zoom-label">100%</span>
                <button id="spe-zoom-in" title="<?php esc_attr_e( 'Zoom In', 'syntekpro-slider' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 3v8M3 7h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
                <button id="spe-zoom-fit" title="<?php esc_attr_e( 'Fit to View', 'syntekpro-slider' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 5V2a1 1 0 011-1h3M9 1h3a1 1 0 011 1v3M13 9v3a1 1 0 01-1 1h-3M5 13H2a1 1 0 01-1-1V9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </main>

        <!-- Properties Panel (right) -->
        <aside id="spe-props" class="spe-panel spe-panel-props">
            <div id="spe-props-inner">
                <p class="spe-select-layer-hint"><?php esc_html_e( 'Select a layer to edit its properties.', 'syntekpro-slider' ); ?></p>
            </div>
        </aside>

    </div>

    <!-- ── Slide Manager (bottom strip) ─────────────────────────────────── -->
    <footer id="spe-slide-manager" class="spe-slide-manager">
        <div class="spe-slide-manager-label">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="5" height="4" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="8" y="3" width="5" height="4" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="4.5" y="9" width="5" height="4" rx="1" stroke="currentColor" stroke-width="1.2"/></svg>
            <span><?php esc_html_e( 'Slides', 'syntekpro-slider' ); ?></span>
        </div>
        <div class="spe-slide-manager-inner">
            <div id="spe-slides-strip" class="spe-slide-strip">
                <!-- Slides rendered by JS -->
            </div>
            <button id="spe-add-slide" class="spe-add-slide-btn" title="<?php esc_attr_e( 'Add Slide', 'syntekpro-slider' ); ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <?php esc_html_e( 'Add Slide', 'syntekpro-slider' ); ?>
            </button>
        </div>
    </footer>

    <!-- ── Generic Reusable Modal (used by Modals helper in editor.js) ── -->
    <div id="spe-modal-overlay" class="spe-modal-overlay">
        <div class="spe-modal-box">
            <div class="spe-modal-header">
                <h2 class="spe-modal-title"></h2>
                <button id="spe-modal-close" class="spe-modal-x">&times;</button>
            </div>
            <div id="spe-modal-body" class="spe-modal-body"></div>
            <div class="spe-modal-footer">
                <button id="spe-modal-apply" class="button button-primary"><?php esc_html_e( 'Apply', 'syntekpro-slider' ); ?></button>
                <button id="spe-modal-cancel" class="button"><?php esc_html_e( 'Cancel', 'syntekpro-slider' ); ?></button>
            </div>
        </div>
    </div>

</div><!-- /#spslider-editor-app -->
