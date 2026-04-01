/* ==========================================================================
   SyntekPro Slider — Full Drag-and-Drop Editor
   Requires: jQuery, jQuery UI (sortable, draggable, resizable)
   ========================================================================== */
/* global SPSLIDER_EDITOR, jQuery */
(function ($) {
    'use strict';

    var cfg = window.SPSLIDER_EDITOR || {};

    /* ──────────────────────────────────────────────────────────────────────
     * STATE
     * ─────────────────────────────────────────────────────────────────────*/
    var state = {
        slider:           { id: 0, name: 'Untitled Slider', settings: {} },
        slides:           [],          // [{id, title, settings, layers:[{…}]}]
        activeSlideIdx:   0,
        selectedLayerId:  null,
        selectedLayerIds: [],          // [id1, id2, ...]
        history:          [],
        historyIndex:     -1,
        globals: {
            colors: ['#0d6efd', '#212529', '#ffc107', '#dc3545', '#198754', '#ffffff'],
            fonts:  ['"Open Sans", sans-serif', '"Montserrat", sans-serif', '"Roboto", sans-serif']
        },
        breakpoint:       'desktop',   // desktop|tablet|mobile
        isDirty:          false,
        isSaving:         false,
        zoom:             1,
        copiedLayer:      null,
        copiedStyles:     null,
    };

    /* ──────────────────────────────────────────────────────────────────────
     * EDITOR CORE ACTIONS
     * ─────────────────────────────────────────────────────────────────────*/
    var Editor = {
        applyLoaded: function (data) {
            if (!data) return;
            state.slider = data.slider || { id: 0, name: 'Untitled Slider', settings: {} };
            state.slides = data.slides || [];
            state.activeSlideIdx = 0;
            state.selectedLayerId = null;
            state.history = [History.snapshot()];
            state.historyIndex = 0;
            state.isDirty = false;
            Canvas.render();
            Canvas.updateSize();
            LayersPanel.render();
            SlideManager.render();
            Toolbar.updateUndoRedo();
            $('#spe-slider-name').text(state.slider.name);
        },

        addLayer: function (type) {
            var slide = Utils.activeSlide();
            if (!slide) { Toolbar.notify('Add a slide first', 'error'); return; }
            var lyr = Defaults.layer(type);
            
            if (type === 'image') {
                StockLibrary.open(function(url) {
                    lyr.content = url;
                    slide.layers.unshift(lyr);
                    state.selectedLayerId = lyr.id;
                    History.push();
                    Canvas.render();
                    LayersPanel.render();
                    PropertiesPanel.render(lyr);
                });
                return;
            }

            slide.layers.unshift(lyr);
            state.selectedLayerId = lyr.id;
            History.push();
            Canvas.render();
            LayersPanel.render();
            PropertiesPanel.render(lyr);
            Toolbar.notify('Added ' + type + ' layer', 'success');
        },

        duplicateLayer: function (id) {
            var lyr = Utils.layerById(id);
            if (!lyr) return;
            var slide = Utils.activeSlide();
            var copy  = Utils.deepClone(lyr);
            copy.id   = Utils.uid();
            copy.name = (lyr.name || lyr.type) + ' (copy)';
            copy.x   += 20;
            copy.y   += 20;
            slide.layers.unshift(copy);
            state.selectedLayerId = copy.id;
            History.push();
            Canvas.render();
            LayersPanel.render();
            PropertiesPanel.render(copy);
        },

        deleteLayer: function (id) {
            var slide = Utils.activeSlide();
            if (!slide) return;
            var idx = Utils.layerIndexById(id);
            if (idx === -1) return;
            slide.layers.splice(idx, 1);
            if (String(state.selectedLayerId) === String(id)) {
                state.selectedLayerId = null;
                PropertiesPanel.clear();
            }
            History.push();
            Canvas.render();
            LayersPanel.render();
        },

        copyStyles: function (id) {
            var lyr = Utils.layerById(id);
            if (!lyr) return;
            /* Filter out position/identity, keep visuals */
            var exclude = ['id', 'x', 'y', 'name', 'z_index', 'locked', 'visible'];
            var styles = Utils.deepClone(lyr);
            exclude.forEach(function (k) { delete styles[k]; });
            state.copiedStyles = { type: lyr.type, styles: styles };
            Toolbar.notify('Styles copied', 'success');
        },

        pasteStyles: function (id) {
            if (!state.copiedStyles) return;
            var lyr = Utils.layerById(id);
            if (!lyr) return;
            if (lyr.type !== state.copiedStyles.type) {
                Toolbar.notify('Cannot paste ' + state.copiedStyles.type + ' styles to ' + lyr.type, 'error');
                return;
            }
            $.extend(true, lyr, state.copiedStyles.styles);
            History.push();
            Canvas.render();
            PropertiesPanel.render(lyr);
            Toolbar.notify('Styles pasted', 'success');
        },

        addSlide: function () {
            var s = Defaults.slide();
            state.slides.push(s);
            state.activeSlideIdx = state.slides.length - 1;
            state.selectedLayerId = null;
            History.push();
            SlideManager.render();
            Canvas.render();
            LayersPanel.render();
            PropertiesPanel.clear();
        },

        duplicateSlide: function (idx) {
            var s = state.slides[idx];
            if (!s) return;
            var copy = Utils.deepClone(s);
            copy.id = null;
            state.slides.splice(idx + 1, 0, copy);
            state.activeSlideIdx = idx + 1;
            History.push();
            SlideManager.render();
            Canvas.render();
            LayersPanel.render();
        },

        deleteSlide: function (idx) {
            if (state.slides.length <= 1) { Toolbar.notify('Must have at least one slide', 'error'); return; }
            state.slides.splice(idx, 1);
            if (state.activeSlideIdx >= state.slides.length) state.activeSlideIdx = state.slides.length - 1;
            state.selectedLayerId = null;
            History.push();
            SlideManager.render();
            Canvas.render();
            LayersPanel.render();
            PropertiesPanel.clear();
        }
    };

    /* ──────────────────────────────────────────────────────────────────────
     * UTILITIES
     * ─────────────────────────────────────────────────────────────────────*/
    var Utils = {
        uid: function () {
            return 'tmp_' + Math.random().toString(36).slice(2, 9);
        },
        deepClone: function (o) {
            return JSON.parse(JSON.stringify(o));
        },
        activeSlide: function () {
            return state.slides[state.activeSlideIdx] || null;
        },
        layerById: function (id) {
            var slide = Utils.activeSlide();
            if (!slide) return null;
            for (var i = 0; i < slide.layers.length; i++) {
                if (String(slide.layers[i].id) === String(id)) return slide.layers[i];
            }
            return null;
        },
        getChildLayers: function (parentId) {
            var slide = Utils.activeSlide();
            if (!slide || !slide.layers) return [];
            return slide.layers.filter(function (lyr) { return String(lyr.parent_id) === String(parentId); });
        },
        isDescendant: function (childId, ancestorId) {
            var lyr = Utils.layerById(childId);
            if (!lyr || !lyr.parent_id) return false;
            if (String(lyr.parent_id) === String(ancestorId)) return true;
            return Utils.isDescendant(lyr.parent_id, ancestorId);
        },
        layerIndexById: function (id) {
            var slide = Utils.activeSlide();
            if (!slide) return -1;
            for (var i = 0; i < slide.layers.length; i++) {
                if (String(slide.layers[i].id) === String(id)) return i;
            }
            return -1;
        },
        esc: function (str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },
        px: function (v) { return parseFloat(v) || 0; },
        clamp: function (v, min, max) { return Math.min(max, Math.max(min, v)); },
        /* Convert desktop coords to canvas-display coords at current zoom */
        toDisplay: function (val) { return val * state.zoom; },
        toLogical: function (val) { return val / state.zoom; },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * HISTORY (Undo / Redo)
     * ─────────────────────────────────────────────────────────────────────*/
    var History = {
        MAX: 50,

        snapshot: function () {
            return Utils.deepClone({ slides: state.slides, slider: state.slider });
        },

        push: function () {
            /* discard redo tail */
            state.history = state.history.slice(0, state.historyIndex + 1);
            state.history.push(History.snapshot());
            if (state.history.length > History.MAX) state.history.shift();
            state.historyIndex = state.history.length - 1;
            state.isDirty = true;
            Toolbar.updateUndoRedo();
        },

        undo: function () {
            if (state.historyIndex <= 0) return;
            state.historyIndex--;
            History.restore(state.history[state.historyIndex]);
        },

        redo: function () {
            if (state.historyIndex >= state.history.length - 1) return;
            state.historyIndex++;
            History.restore(state.history[state.historyIndex]);
        },

        restore: function (snap) {
            state.slides = Utils.deepClone(snap.slides);
            state.slider = Utils.deepClone(snap.slider);
            state.selectedLayerId = null;
            Canvas.render();
            LayersPanel.render();
            PropertiesPanel.clear();
            SlideManager.render();
            Toolbar.updateUndoRedo();
            state.isDirty = true;
        },

        getLabel: function(snap) {
            /* Basic logic to label the step */
            if (!snap) return 'Original State';
            return 'Edit Slider (' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}) + ')';
        }
    };

    /* ──────────────────────────────────────────────────────────────────────
     * HISTORY PANEL
     * ─────────────────────────────────────────────────────────────────────*/
    var HistoryPanel = {
        open: function() {
            var html = '<div class="spe-history-list">';
            state.history.forEach(function(snap, i) {
                var active = (i === state.historyIndex) ? 'is-active' : '';
                html += '<div class="spe-history-item ' + active + '" data-idx="' + i + '">' +
                        '<span class="spe-hi-icon"></span>' +
                        '<span class="spe-hi-label">' + History.getLabel(snap) + '</span>' +
                        '</div>';
            });
            html += '</div>';
            Modals.open('Action History', html, null, false);
            HistoryPanel.bind();
        },
        bind: function() {
            $('.spe-history-item').on('click', function() {
                var idx = parseInt($(this).data('idx'));
                state.historyIndex = idx;
                History.restore(state.history[idx]);
                Modals.close();
            });
        }
    };

    /* ──────────────────────────────────────────────────────────────────────
     * THEME MANAGER (Global Styles)
     * ─────────────────────────────────────────────────────────────────────*/
    var ThemeManager = {
        open: function() {
            var html = '<div class="spe-theme-wrap">' +
                '<h3>Global Color Palette</h3>' +
                '<div class="spe-theme-colors">';
            state.globals.colors.forEach(function(c, i) {
                html += '<div class="spe-theme-color-item">' +
                        '<input type="color" value="' + c + '" data-idx="' + i + '">' +
                        '<button class="spe-theme-apply-color" data-color="' + c + '">Apply to Selected</button>' +
                        '</div>';
            });
            html += '</div><hr>' +
                '<h3>Global Font Stack</h3>' +
                '<div class="spe-theme-fonts">';
            state.globals.fonts.forEach(function(f, i) {
                html += '<div class="spe-theme-font-item">' +
                        '<code class="spe-font-preview" style=\'font-family:' + f + '\'>' + f + '</code>' +
                        '<button class="spe-theme-apply-font" data-font=\'' + f + '\'>Apply</button>' +
                        '</div>';
            });
            html += '</div></div>';
            Modals.open('Theme Settings', html, null, false);
            ThemeManager.bind();
        },
        bind: function() {
            /* Update color palette */
            $('.spe-theme-color-item input').on('change', function() {
                var idx = $(this).data('idx');
                state.globals.colors[idx] = $(this).val();
            });
            /* Apply color to selection */
            $('.spe-theme-apply-color').on('click', function() {
                var color = $(this).siblings('input').val();
                var ids = state.selectedLayerIds.length ? state.selectedLayerIds : (state.selectedLayerId ? [state.selectedLayerId] : []);
                ids.forEach(function(id) {
                    var lyr = Utils.layerById(id);
                    if (lyr) lyr.color = color;
                });
                Canvas.render();
                History.push();
            });
            /* Apply font stack */
            $('.spe-theme-apply-font').on('click', function() {
                var font = $(this).data('font');
                var ids = state.selectedLayerIds.length ? state.selectedLayerIds : (state.selectedLayerId ? [state.selectedLayerId] : []);
                ids.forEach(function(id) {
                    var lyr = Utils.layerById(id);
                    if (lyr && lyr.type === 'text') lyr.fontFamily = font;
                });
                Canvas.render();
                History.push();
            });
        }
    };

    /* ──────────────────────────────────────────────────────────────────────
     * CONTEXT MENU
     * ─────────────────────────────────────────────────────────────────────*/
    var ContextMenu = {
        $el: null,

        init: function () {
            this.$el = $('<div id="spe-context-menu" class="spe-context-menu"></div>').appendTo('body');
            this.bindEvents();
        },

        open: function (x, y, context) {
            var lyr = Utils.layerById(state.selectedLayerId);
            var items = [];

            if (context === 'layer' && lyr) {
                items = [
                    { label: 'Duplicate', icon: LayersPanel.ctrlIcons.dup, action: function () { Editor.duplicateLayer(lyr.id); } },
                    { label: 'Copy Styles', icon: '', action: function () { Editor.copyStyles(lyr.id); } },
                    { label: 'Paste Styles', icon: '', action: function () { Editor.pasteStyles(lyr.id); }, disabled: !state.copiedStyles || state.copiedStyles.type !== lyr.type },
                    { sep: true },
                ];

                /* Parenting Submenu (or simple items) */
                var slide = Utils.activeSlide();
                var potentialParents = (slide ? slide.layers : []).filter(function(p) { 
                    return String(p.id) !== String(lyr.id) && !Utils.isDescendant(p.id, lyr.id); 
                });

                if (lyr.parent_id) {
                    items.push({ label: 'Un-parent (Make Root)', action: function() { lyr.parent_id = null; History.push(); Canvas.render(); LayersPanel.render(); } });
                }

                if (potentialParents.length > 0) {
                    /* For simplicity in this UI, we just show "Parent to..." and a list if it's small, 
                       or we can just show a modal. Let's do a simple prompt for now to choose by name. */
                    items.push({ label: 'Parent to...', action: function() {
                        var optHtml = '<div class="spe-sfield"><label>Choose Parent Layer</label><select id="spe-parent-select"><option value="">— Dynamic List —</option>';
                        potentialParents.forEach(function(p) {
                            optHtml += '<option value="' + p.id + '">' + Utils.esc(p.name || p.type) + '</option>';
                        });
                        optHtml += '</select></div>';
                        Modals.open('Set Parent Layer', optHtml, function() {
                            var pid = $('#spe-parent-select').val();
                            lyr.parent_id = pid || null;
                            History.push();
                            Canvas.render();
                            LayersPanel.render();
                            Modals.close();
                        });
                    }});
                }

                items.push(
                    { sep: true },
                    { label: lyr.locked ? 'Unlock' : 'Lock', icon: lyr.locked ? LayersPanel.ctrlIcons.locked : LayersPanel.ctrlIcons.unlocked, action: function () { lyr.locked = !lyr.locked; History.push(); Canvas.render(); LayersPanel.render(); } },
                    { label: 'Delete', icon: LayersPanel.ctrlIcons.del, action: function () { Editor.deleteLayer(lyr.id); }, danger: true },
                );
            } else {
                items = [
                    { label: 'Add Text', action: function () { Editor.addLayer('text'); } },
                    { label: 'Add Image', action: function () { Editor.addLayer('image'); } },
                    { label: 'Add Button', action: function () { Editor.addLayer('button'); } },
                    { sep: true },
                    { label: 'Paste Styles', action: function () { Editor.pasteStyles(state.selectedLayerId); }, disabled: !state.copiedStyles || !state.selectedLayerId }
                ];
            }

            var html = '';
            items.forEach(function (it) {
                if (it.sep) { html += '<div class="spe-cm-sep"></div>'; return; }
                html += '<div class="spe-cm-item' + (it.danger ? ' is-danger' : '') + (it.disabled ? ' is-disabled' : '') + '" data-idx="' + items.indexOf(it) + '">' +
                        (it.icon || '') + '<span>' + it.label + '</span></div>';
            });

            this.$el.html(html).css({ left: x, top: y }).addClass('is-open');

            this.$el.off('click').on('click', '.spe-cm-item', function () {
                var it = items[$(this).data('idx')];
                if (it && it.action && !it.disabled) {
                    it.action();
                    ContextMenu.close();
                }
            });
        },

        close: function () {
            this.$el.removeClass('is-open');
        },

        bindEvents: function () {
            $(document).on('mousedown', function (e) {
                if (!$(e.target).closest('#spe-context-menu').length) ContextMenu.close();
            });
        }
    };

    /* ──────────────────────────────────────────────────────────────────────
     * API (AJAX)
     * ─────────────────────────────────────────────────────────────────────*/
    var API = {
        post: function (action, extra, done, fail) {
            return $.post(cfg.ajax_url, $.extend({
                action: action,
                nonce:  cfg.nonce,
            }, extra)).done(function (res) {
                if (res && res.success) {
                    done && done(res.data);
                } else {
                    Toolbar.notify((res && res.data) || cfg.i18n.error_generic, 'error');
                    fail && fail(res);
                }
            }).fail(function () {
                Toolbar.notify(cfg.i18n.error_generic, 'error');
                fail && fail();
            });
        },

        load: function (sliderId, done) {
            API.post('spslider_load', { slider_id: sliderId }, done);
        },

        save: function (done) {
            if (state.isSaving) return;
            state.isSaving = true;
            Toolbar.setSaving(true);

            var payload = {
                slider_id: state.slider.id,
                name:      state.slider.name,
                settings:  state.slider.settings,
                slides:    state.slides,
            };

            API.post('spslider_save', {
                slider_id: state.slider.id,
                payload: JSON.stringify(payload),
            }, function (data) {
                state.isSaving = false;
                state.isDirty  = false;
                Toolbar.setSaving(false);
                Toolbar.notify(cfg.i18n.saved, 'success');
                /* On first save the DB may have assigned real IDs — reload */
                if (data && data.slider_id) {
                    API.load(data.slider_id, function (d) { Editor.applyLoaded(d); });
                }
                done && done(data);
            }, function () {
                state.isSaving = false;
                Toolbar.setSaving(false);
            });
        },

        getTemplates: function (done) {
            API.post('spslider_get_templates', {}, done);
        },

        importTemplate: function (templateId, done) {
            API.post('spslider_import_template', {
                slider_id:   state.slider.id,
                template_id: templateId,
            }, done);
        },

        getDynamicSources: function (done) {
            API.post('spslider_dynamic_sources', {}, done);
        },

        importDynamic: function (config, done) {
            API.post('spslider_dynamic_import', {
                slider_id: state.slider.id,
                config: JSON.stringify(config),
            }, done);
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * DEFAULT LAYER SETTINGS (mirrored from PHP)
     * ─────────────────────────────────────────────────────────────────────*/
    var Defaults = {
        layer: function (type) {
            var base = {
                id: Utils.uid(), type: type,
                x: 60, y: 60, width: 300, height: 100,
                z_index: 10, visible: true, locked: false,
                opacity: 1, rotation: 0, parallax_depth: 0,
                alt: '',
                animation_in:  { effect: 'fade', delay: 0,   duration: 600, easing: 'ease-out', distance: 30 },
                animation_out: { effect: 'fade', delay: 0,   duration: 400, easing: 'ease-in',  distance: 30 },
                breakpoints:   { tablet: { visible: true, font_size_scale: 0.85 }, mobile: { visible: true, font_size_scale: 0.7 } },
                hover: { effect: 'none', scale: 1.05, glow: false },
                click: { action: 'none', url: '', target: '_self', slide: 0 },
                style: {},
            };
            var specific = {
                text:   { content: 'Click to edit text', font_size: 24, font_weight: '400', font_family: 'inherit', color: '#333333', text_align: 'left', line_height: 1.5, letter_spacing: 0 },
                image:  { src: '', object_fit: 'cover', border_radius: 0 },
                button: { label: 'Click Me', url: '#', target: '_self', bg_color: '#6366f1', text_color: '#ffffff', border_radius: 6, padding: '12px 28px', font_size: 16, font_weight: '600', hover_bg: '#4f46e5', border: 'none', width: 180, height: 52 },
                video:  { src: '', type: 'mp4', autoplay: false, loop: false, muted: true, controls: true, poster: '' },
                shape:  { shape: 'rectangle', fill: '#6366f1', stroke: '', stroke_width: 0, border_radius: 0 },
                countdown: { countdown_target: '', countdown_label: '', countdown_expired: 'Expired!', font_size: 28, font_weight: '700', color: '#ffffff', width: 400, height: 80 },
                icon: { icon_class: 'dashicons dashicons-star-filled', icon_size: 48, color: '#ffffff', width: 60, height: 60 },
                lottie: { lottie_src: '', lottie_autoplay: true, lottie_loop: true, width: 300, height: 300 },
                html: { html_content: '<div>Custom HTML</div>', width: 400, height: 200 },
            };
            return $.extend(true, {}, base, specific[type] || {});
        },
        slide: function () {
            return {
                id: null, title: 'Slide',
                settings: {
                    bg_color: '#1a1a2e', bg_image: '', bg_size: 'cover',
                    bg_position: 'center center', bg_repeat: 'no-repeat',
                    bg_video: '', bg_overlay: '', bg_overlay_opacity: 0,
                    link: '', link_target: '_self', thumbnail: '',
                    ken_burns: false, ken_burns_zoom: 120, ken_burns_direction: 'in',
                },
                layers: [],
            };
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * CANVAS
     * ─────────────────────────────────────────────────────────────────────*/
    var Canvas = {
        $el:    null,
        $inner: null,
        drag:   null,   /* { layerId, startX, startY, origX, origY } */
        resize: null,   /* { layerId, handle, startX, startY, origX, origY, origW, origH } */
        guides: { h: [], v: [] },

        init: function () {
            Canvas.$el    = $('#spe-canvas');
            Canvas.$inner = $('#spe-canvas-inner');
            Canvas.bindEvents();
        },

        /* Width of the slide in designer units (matches slider settings) */
        designWidth: function () {
            return Utils.px(state.slider.settings.width)  || 1200;
        },
        designHeight: function () {
            return Utils.px(state.slider.settings.height) || 600;
        },

        updateSize: function () {
            var avail  = Canvas.$el.width() - 32;
            var dw     = Canvas.designWidth();
            var dh     = Canvas.designHeight();
            var zoom   = Math.min(1, avail / dw);
            state.zoom = zoom;
            Canvas.$inner.css({
                width:  dw * zoom,
                height: dh * zoom,
            });
            /* Re-scale all layer elements */
            Canvas.$inner.find('.spe-layer').each(function () {
                var id  = $(this).data('layer-id');
                var lyr = Utils.layerById(id);
                if (lyr) Canvas.positionLayerEl($(this), lyr);
            });
            $('#spe-zoom-label').text(Math.round(state.zoom * 100) + '%');
        },

        setZoom: function (z) {
            state.zoom = Utils.clamp(z, 0.1, 3);
            Canvas.updateSize();
            Canvas.render();
            $('#spe-zoom-label').text(Math.round(state.zoom * 100) + '%');
        },

        /* ── Breakpoint Helpers ── */
        getBP: function () {
            return state.breakpoint || 'desktop';
        },

        isBP: function (bp) {
            return Canvas.getBP() === bp;
        },

        getVal: function (layer, prop, def) {
            var bp = Canvas.getBP();
            if (bp === 'desktop') return layer[prop] !== undefined ? layer[prop] : def;
            
            /* Inheritance check: Tablet checks tablet then desktop. Mobile checks mobile, tablet, then desktop. */
            var bps = layer.breakpoints || {};
            if (bp === 'tablet') {
                if (bps.tablet && bps.tablet[prop] !== undefined) return bps.tablet[prop];
                return layer[prop] !== undefined ? layer[prop] : def;
            }
            if (bp === 'mobile') {
                if (bps.mobile && bps.mobile[prop] !== undefined) return bps.mobile[prop];
                if (bps.tablet && bps.tablet[prop] !== undefined) return bps.tablet[prop];
                return layer[prop] !== undefined ? layer[prop] : def;
            }
            return layer[prop] !== undefined ? layer[prop] : def;
        },

        setVal: function (layer, prop, val) {
            var bp = Canvas.getBP();
            if (bp === 'desktop') {
                layer[prop] = val;
            } else {
                if (!layer.breakpoints) layer.breakpoints = {};
                if (!layer.breakpoints[bp]) layer.breakpoints[bp] = {};
                layer.breakpoints[bp][prop] = val;
            }
        },

        render: function () {
            var slide = Utils.activeSlide();
            var $inner = Canvas.$inner;
            $inner.empty();

            if (!slide) {
                $inner.append('<div class="spe-canvas-empty">No slides yet.<br>Add a slide below.</div>');
                return;
            }

            /* Background */
            var s = slide.settings;
            var bgStyle = 'background-color:' + (s.bg_color || '#fff') + ';';
            if (s.bg_image) bgStyle += 'background-image:url(' + s.bg_image + ');background-size:' + (s.bg_size || 'cover') + ';background-position:' + (s.bg_position || 'center') + ';';
            $inner.attr('style', 'width:' + Canvas.designWidth() * state.zoom + 'px;height:' + Canvas.designHeight() * state.zoom + 'px;' + bgStyle);

            /* Overlay */
            if (s.bg_overlay) {
                var op = parseFloat(s.bg_overlay_opacity) || 0;
                $('<div class="spe-canvas-overlay">').css({ background: s.bg_overlay, opacity: op }).appendTo($inner);
            }

            /* Alignment guides */
            $('<div class="spe-guide spe-guide-h" id="spe-guide-h">').hide().appendTo($inner);
            $('<div class="spe-guide spe-guide-v" id="spe-guide-v">').hide().appendTo($inner);

            /* Safety: show breakpoint guide label */
            $('<div class="spe-canvas-bp-label">').text(state.breakpoint.toUpperCase()).appendTo($inner);

            /* Render layers */
            var layers = slide.layers || [];
            for (var i = layers.length - 1; i >= 0; i--) {
                Canvas.renderLayer(layers[i]);
            }

            Canvas.updateSelectionBox();
        },

        renderLayer: function (layer) {
            if (!layer) return;

            /* Breakpoint visibility */
            if (state.breakpoint !== 'desktop') {
                var bp = (layer.breakpoints || {})[state.breakpoint] || {};
                if (bp.visible === false) return;
            }

            var $el = $('<div class="spe-layer">')
                .attr('data-layer-id', layer.id)
                .css({
                    zIndex:   layer.z_index || 10,
                    opacity:  parseFloat(layer.opacity) || 1,
                });

            if (layer.locked)  $el.addClass('is-locked');
            if (!layer.visible) $el.addClass('is-hidden');
            if (String(layer.id) === String(state.selectedLayerId)) $el.addClass('is-selected');

            Canvas.positionLayerEl($el, layer);
            Canvas.renderLayerContent($el, layer);
            Canvas.addResizeHandles($el);

            /* Tooltip label */
            $('<span class="spe-layer-label">').text(layer.name || layer.type).appendTo($el);

            Canvas.$inner.append($el);
        },

        positionLayerEl: function ($el, layer) {
            var x = Canvas.getVal(layer, 'x', 0);
            var y = Canvas.getVal(layer, 'y', 0);
            var w = Canvas.getVal(layer, 'width', 100);
            var h = Canvas.getVal(layer, 'height', 100);

            $el.css({
                left:   Utils.toDisplay(x) + 'px',
                top:    Utils.toDisplay(y) + 'px',
                width:  Utils.toDisplay(w) + 'px',
                height: Utils.toDisplay(h) + 'px',
            });
        },

        renderLayerContent: function ($el, layer) {
            var $c = $('<div class="spe-layer-content">');
            switch (layer.type) {
                case 'text':
                    $c.css({ fontSize: (layer.font_size || 16) * state.zoom + 'px', color: layer.color || '#333', fontWeight: layer.font_weight || '400', fontFamily: layer.font_family || 'inherit', textAlign: layer.text_align || 'left', lineHeight: layer.line_height || 1.5 })
                      .html(layer.content || 'Text');
                    
                    /* Double click to edit */
                    $el.on('dblclick', function(e) {
                         e.stopPropagation();
                         var $this = $(this).find('.spe-layer-content');
                         $this.attr('contenteditable', 'true').focus();
                         /* Select all text */
                         document.execCommand('selectAll', false, null);
                         
                         $this.on('blur.textedit', function() {
                             var newText = $(this).html();
                             layer.content = newText;
                             $(this).attr('contenteditable', 'false').off('blur.textedit');
                             PropertiesPanel.render(layer);
                             History.push();
                         });
                         
                         $this.on('keydown.textedit', function(e) {
                             if (e.key === 'Enter' && !e.shiftKey) {
                                 e.preventDefault();
                                 $(this).blur();
                             }
                         });
                    });
                    break;
                case 'image':
                    if (layer.src) {
                        $('<img>').attr({ src: layer.src, alt: layer.alt || '' }).css({ width: '100%', height: '100%', objectFit: layer.object_fit || 'cover', borderRadius: (layer.border_radius || 0) + 'px' }).appendTo($c);
                    } else {
                        $c.addClass('spe-layer-placeholder').html('<svg viewBox="0 0 24 24" width="32" height="32"><rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><polyline points="21 15 16 10 5 21" fill="none" stroke="currentColor" stroke-width="1.5"/></svg><span>Image</span>');
                    }
                    break;
                case 'button':
                    $c.addClass('spe-layer-btn-preview').css({ background: layer.bg_color || '#6366f1', color: layer.text_color || '#fff', borderRadius: (layer.border_radius || 6) + 'px', fontSize: (layer.font_size || 16) * state.zoom + 'px', padding: layer.padding || '12px 28px', display:'flex', alignItems:'center', justifyContent:'center', height:'100%' }).text(layer.label || 'Button');
                    break;
                case 'video':
                    $c.addClass('spe-layer-placeholder').html('<svg viewBox="0 0 24 24" width="32" height="32"><polygon points="5 3 19 12 5 21 5 3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg><span>Video</span>');
                    break;
                case 'shape':
                    $c.css({ background: layer.fill || '#6366f1', borderRadius: (layer.shape === 'circle' ? '50%' : (layer.border_radius || 0)) + 'px', width: '100%', height: '100%', border: layer.stroke ? (layer.stroke_width || 1) + 'px solid ' + layer.stroke : 'none' });
                    break;
                case 'countdown':
                    $c.addClass('spe-layer-placeholder').css({
                        color: layer.color || '#ffffff',
                        fontSize: (layer.font_size || 28) * state.zoom + 'px',
                        fontWeight: layer.font_weight || '700',
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        gap: '6px'
                    }).html('<strong>12d 08h 43m 21s</strong>' + ((layer.countdown_label || '') ? '<span style="font-size:0.5em;opacity:.75;">' + Utils.esc(layer.countdown_label) + '</span>' : ''));
                    break;
                case 'icon':
                    $('<span>').attr('class', layer.icon_class || 'dashicons dashicons-star-filled').css({
                        color: layer.color || '#ffffff',
                        fontSize: (layer.icon_size || 48) * state.zoom + 'px',
                        width: '100%',
                        height: '100%',
                        display: 'inline-flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                    }).appendTo($c);
                    break;
                case 'lottie':
                    $c.addClass('spe-layer-placeholder').html('<svg viewBox="0 0 24 24" width="32" height="32"><rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M7 14c1.4-2.4 3.1-3.6 5-3.6 1.8 0 3.1.9 5 2.9" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span>' + Utils.esc(layer.lottie_src ? 'Lottie Animation' : 'Lottie JSON') + '</span>');
                    break;
                case 'html':
                    $c.css({ width: '100%', height: '100%', overflow: 'hidden' }).html(layer.html_content || '<div>Custom HTML</div>');
                    break;
            }
            $el.append($c);
        },

        addResizeHandles: function ($el) {
            var handles = ['nw','n','ne','e','se','s','sw','w'];
            handles.forEach(function (h) {
                $('<div class="spe-rh spe-rh-' + h + '">').attr('data-handle', h).appendTo($el);
            });
        },

        updateSelectionBox: function () {
            Canvas.$inner.find('.spe-layer').each(function () {
                var idStr = String($(this).data('layer-id'));
                var isSelected = state.selectedLayerIds.map(String).indexOf(idStr) > -1;
                $(this).toggleClass('is-selected', isSelected);
                $(this).toggleClass('is-active-selection', idStr === String(state.selectedLayerId));
            });
        },

        calcGuides: function (excludeId) {
            var g = { h: [], v: [] };
            var dw = Canvas.designWidth();
            var dh = Canvas.designHeight();

            /* Slide edges & center */
            g.v.push(0, dw / 2, dw);
            g.h.push(0, dh / 2, dh);

            /* Other layers */
            var slide = Utils.activeSlide();
            if (slide) {
                slide.layers.forEach(function (l) {
                    if (String(l.id) === String(excludeId) || !l.visible) return;
                    g.v.push(l.x, l.x + (l.width / 2), l.x + l.width);
                    g.h.push(l.y, l.y + (l.height / 2), l.y + l.height);
                });
            }
            Canvas.guides = g;
        },

        checkSnap: function (val, dir) {
            var snapThreshold = 5;
            var points = dir === 'v' ? Canvas.guides.v : Canvas.guides.h;
            for (var i = 0; i < points.length; i++) {
                if (Math.abs(val - points[i]) < snapThreshold) return points[i];
            }
            return null;
        },

        showGuide: function (val, dir) {
            var $g = dir === 'v' ? $('#spe-guide-v') : $('#spe-guide-h');
            if (val === null) { $g.hide(); return; }
            $g.show().css(dir === 'v' ? 'left' : 'top', Utils.toDisplay(val));
        },

        bindEvents: function () {
            /* Mousedown on canvas — select or deselect */
            Canvas.$inner.on('mousedown', function (e) {
                if (e.which === 3) return;
                if ($(e.target).is('.spe-rh')) return; 
                if (!$(e.target).closest('.spe-layer').length) {
                    /* Start Lasso Selection */
                    var cx = e.clientX, cy = e.clientY;
                    var $lasso = $('<div id="spe-lasso">').appendTo('body');
                    
                    $(document).on('mousemove.lasso', function(me) {
                        var x1 = Math.min(cx, me.clientX), y1 = Math.min(cy, me.clientY);
                        var x2 = Math.max(cx, me.clientX), y2 = Math.max(cy, me.clientY);
                        $lasso.css({ left: x1, top: y1, width: x2-x1, height: y2-y1, display: 'block' });
                    });

                    $(document).one('mouseup.lasso', function(ue) {
                        $(document).off('mousemove.lasso');
                        var rect = $lasso[0].getBoundingClientRect();
                        $lasso.remove();
                        
                        var newSelection = [];
                        Canvas.$inner.find('.spe-layer').each(function() {
                            var lRect = this.getBoundingClientRect();
                            if (lRect.left >= rect.left && lRect.right <= rect.right &&
                                lRect.top >= rect.top && lRect.bottom <= rect.bottom) {
                                newSelection.push($(this).data('layer-id'));
                            }
                        });
                        
                        if (newSelection.length > 0) {
                            state.selectedLayerIds = newSelection;
                            state.selectedLayerId = newSelection.length === 1 ? newSelection[0] : null;
                            Canvas.updateSelectionBox();
                            LayersPanel.updateSelection();
                            if (newSelection.length === 1) {
                                PropertiesPanel.render(Utils.layerById(newSelection[0]));
                            } else {
                                PropertiesPanel.renderMulti(newSelection);
                            }
                        } else {
                            /* click on blank canvas — deselect */
                            Canvas.selectLayer(null);
                        }
                    });
                }
            });

            /* Context Menu */
            Canvas.$inner.on('contextmenu', function (e) {
                e.preventDefault();
                var $lyrGroup = $(e.target).closest('.spe-layer');
                if ($lyrGroup.length) {
                    Canvas.selectLayer($lyrGroup.data('layer-id'));
                    ContextMenu.open(e.clientX, e.clientY, 'layer');
                } else {
                    ContextMenu.open(e.clientX, e.clientY, 'canvas');
                }
            });

            /* Mousedown on layer — start drag */
            Canvas.$inner.on('mousedown', '.spe-layer', function (e) {
                if (e.which === 3) return; /* Right click */
                if ($(e.target).is('.spe-rh')) return;
                e.stopPropagation();
                if (!$(e.target).is('[contenteditable]')) e.preventDefault();
                var id  = $(this).data('layer-id');
                var lyr = Utils.layerById(id);
                if (!lyr || lyr.locked) return;
                Canvas.selectLayer(id);
                Canvas.calcGuides(id);

                Canvas.drag = {
                    layerId: id,
                    $el:     $(this),
                    startX:  e.clientX,
                    startY:  e.clientY,
                    origX:   Canvas.getVal(lyr, 'x', 0),
                    origY:   Canvas.getVal(lyr, 'y', 0),
                    lastNx:  Canvas.getVal(lyr, 'x', 0),
                    lastNy:  Canvas.getVal(lyr, 'y', 0),
                };
            });

            /* Mousedown on resize handle */
            Canvas.$inner.on('mousedown', '.spe-rh', function (e) {
                e.stopPropagation();
                e.preventDefault();
                var $layer = $(this).closest('.spe-layer');
                var id     = $layer.data('layer-id');
                var lyr    = Utils.layerById(id);
                if (!lyr || lyr.locked) return;
                Canvas.calcGuides(id);

                Canvas.resize = {
                    layerId: id,
                    $el:     $layer,
                    handle:  $(this).data('handle'),
                    startX:  e.clientX,
                    startY:  e.clientY,
                    origX:   Canvas.getVal(lyr, 'x', 0), 
                    origY:   Canvas.getVal(lyr, 'y', 0),
                    origW:   Canvas.getVal(lyr, 'width', 100), 
                    origH:   Canvas.getVal(lyr, 'height', 100),
                };
            });

            /* Global mousemove */
            $(document).on('mousemove.speditor', function (e) {
                Canvas.onMouseMove(e);
            });

            /* Global mouseup */
            $(document).on('mouseup.speditor', function () {
                if (Canvas.drag || Canvas.resize) {
                    History.push();
                }
                Canvas.drag   = null;
                Canvas.resize = null;
                Canvas.showGuide(null, 'h');
                Canvas.showGuide(null, 'v');
            });

            /* Zoom Buttons */
            $('#spe-zoom-in').on('click',  function () { Canvas.setZoom(state.zoom + 0.1); });
            $('#spe-zoom-out').on('click', function () { Canvas.setZoom(state.zoom - 0.1); });
            $('#spe-zoom-fit').on('click', function () { Canvas.updateSize(); Canvas.render(); });
        },

        onMouseMove: function (e) {
            if (Canvas.drag) {
                var d = Canvas.drag;
                var dx = Utils.toLogical(e.clientX - d.startX);
                var dy = Utils.toLogical(e.clientY - d.startY);
                var lyr = Utils.layerById(d.layerId);
                if (!lyr) return;

                var nx = d.origX + dx;
                var ny = d.origY + dy;

                /* Snapping */
                var snapX = Canvas.checkSnap(nx, 'v') || Canvas.checkSnap(nx + lyr.width / 2, 'v') || Canvas.checkSnap(nx + lyr.width, 'v');
                var snapY = Canvas.checkSnap(ny, 'h') || Canvas.checkSnap(ny + lyr.height / 2, 'h') || Canvas.checkSnap(ny + lyr.height, 'h');

                if (snapX !== null) {
                    if (Math.abs(nx - snapX) < 5) nx = snapX;
                    else if (Math.abs(nx + lyr.width/2 - snapX) < 5) nx = snapX - lyr.width/2;
                    else if (Math.abs(nx + lyr.width - snapX) < 5) nx = snapX - lyr.width;
                    Canvas.showGuide(snapX, 'v');
                } else { Canvas.showGuide(null, 'v'); }

                if (snapY !== null) {
                    if (Math.abs(ny - snapY) < 5) ny = snapY;
                    else if (Math.abs(ny + lyr.height/2 - snapY) < 5) ny = snapY - lyr.height/2;
                    else if (Math.abs(ny + lyr.height - snapY) < 5) ny = snapY - lyr.height;
                    Canvas.showGuide(snapY, 'h');
                } else { Canvas.showGuide(null, 'h'); }

                Canvas.setVal(lyr, 'x', Math.round(nx));
                Canvas.setVal(lyr, 'y', Math.round(ny));

                Canvas.positionLayerEl(d.$el, lyr);
                PropertiesPanel.updateCoords(lyr);

                /* Drag children */
                var children = Utils.getChildLayers(lyr.id);
                children.forEach(function (child) {
                    var cx = Canvas.getVal(child, 'x', 0);
                    var cy = Canvas.getVal(child, 'y', 0);
                    Canvas.setVal(child, 'x', cx + (nx - d.lastNx || 0));
                    Canvas.setVal(child, 'y', cy + (ny - d.lastNy || 0));
                    var $childEl = Canvas.$inner.find('.spe-layer[data-layer-id="' + child.id + '"]');
                    if ($childEl.length) Canvas.positionLayerEl($childEl, child);
                });
                d.lastNx = nx;
                d.lastNy = ny;
            }

            if (Canvas.resize) {
                var r  = Canvas.resize;
                var rdx = Utils.toLogical(e.clientX - r.startX);
                var rdy = Utils.toLogical(e.clientY - r.startY);
                var lyr = Utils.layerById(r.layerId);
                if (!lyr) return;

                var x = r.origX, y = r.origY, w = r.origW, h = r.origH;
                var h2 = r.handle;
                if (h2.includes('e')) w = Math.max(20, r.origW + rdx);
                if (h2.includes('s')) h = Math.max(20, r.origH + rdy);
                if (h2.includes('w')) { x = r.origX + rdx; w = Math.max(20, r.origW - rdx); }
                if (h2.includes('n')) { y = r.origY + rdy; h = Math.max(20, r.origH - rdy); }

                Canvas.setVal(lyr, 'x', Math.round(x));
                Canvas.setVal(lyr, 'y', Math.round(y));
                Canvas.setVal(lyr, 'width', Math.round(w));
                Canvas.setVal(lyr, 'height', Math.round(h));
                
                Canvas.positionLayerEl(r.$el, lyr);
                PropertiesPanel.updateCoords(lyr);
            }
        },

        selectLayer: function (id, multi) {
            if (multi) {
                var idx = state.selectedLayerIds.indexOf(id);
                if (idx > -1) {
                    state.selectedLayerIds.splice(idx, 1);
                } else {
                    state.selectedLayerIds.push(id);
                }
                state.selectedLayerId = state.selectedLayerIds.length === 1 ? state.selectedLayerIds[0] : null;
            } else {
                state.selectedLayerId = id;
                state.selectedLayerIds = id ? [id] : [];
            }
            
            Canvas.updateSelectionBox();
            LayersPanel.updateSelection();
            
            if (state.selectedLayerIds.length === 1) {
                var lyr = Utils.layerById(state.selectedLayerId);
                if (lyr) PropertiesPanel.render(lyr);
            } else if (state.selectedLayerIds.length > 1) {
                PropertiesPanel.renderMulti(state.selectedLayerIds);
            } else {
                PropertiesPanel.clear();
            }
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * LAYERS PANEL
     * ─────────────────────────────────────────────────────────────────────*/
    var LayersPanel = {
        $el: null,

        init: function () {
            LayersPanel.$el = $('#spe-layers-list');
        },

        /* SVG icon definitions for layer types */
        svgIcons: {
            text:   '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 3h10M7 3v9M4.5 3V2h5v1" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            image:  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/><circle cx="4.5" cy="5.5" r="1.2" fill="currentColor"/><path d="M1 10l3-3 2.5 2.5L9 7l4 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            button: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="4" width="12" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 7h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>',
            video:  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M5.5 5v4l3.5-2-3.5-2z" fill="currentColor"/></svg>',
            shape:  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.3"/></svg>',
            countdown: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.3"/><path d="M7 4.2v3l2 1.2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            icon: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 2l1.5 3.1L12 5.6 9.5 8l.6 3.4L7 9.9l-3.1 1.5.6-3.4L2 5.6l3.5-.5L7 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>',
            lottie: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="2" y="2" width="10" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2"/><path d="M4.5 8c.8-1.3 1.7-2 2.7-2 .9 0 1.5.5 2.3 1.6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            html: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M4.5 4L2 7l2.5 3M9.5 4L12 7l-2.5 3M8 3l-2 8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        },

        /* SVG icons for control buttons */
        ctrlIcons: {
            visible:  '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1 6s2-3.5 5-3.5S11 6 11 6s-2 3.5-5 3.5S1 6 1 6z" stroke="currentColor" stroke-width="1.2"/><circle cx="6" cy="6" r="1.5" stroke="currentColor" stroke-width="1.2"/></svg>',
            hidden:   '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1.5 1.5l9 9M4 5.2A2 2 0 006.8 8M8.5 7.3c.8-.5 1.5-1.3 1.5-1.3s-2-3.5-5-3.5a4.6 4.6 0 00-1.5.3" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/></svg>',
            locked:   '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="2.5" y="5.5" width="7" height="5" rx="1" stroke="currentColor" stroke-width="1.1"/><path d="M4 5.5V4a2 2 0 014 0v1.5" stroke="currentColor" stroke-width="1.1"/></svg>',
            unlocked: '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="2.5" y="5.5" width="7" height="5" rx="1" stroke="currentColor" stroke-width="1.1"/><path d="M4 5.5V4a2 2 0 014 0" stroke="currentColor" stroke-width="1.1" stroke-linecap="round"/></svg>',
            dup:      '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.1"/><path d="M9 3V2a1 1 0 00-1-1H2a1 1 0 00-1 1v6a1 1 0 001 1h1" stroke="currentColor" stroke-width="1.1"/></svg>',
            del:      '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M4.5 3V2h3v1M3 3v7a1 1 0 001 1h4a1 1 0 001-1V3" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            drag:     '<svg width="10" height="10" viewBox="0 0 10 10" fill="none"><circle cx="3.5" cy="2" r=".8" fill="currentColor"/><circle cx="6.5" cy="2" r=".8" fill="currentColor"/><circle cx="3.5" cy="5" r=".8" fill="currentColor"/><circle cx="6.5" cy="5" r=".8" fill="currentColor"/><circle cx="3.5" cy="8" r=".8" fill="currentColor"/><circle cx="6.5" cy="8" r=".8" fill="currentColor"/></svg>',
        },

        render: function () {
            var slide  = Utils.activeSlide();
            var layers = slide ? (slide.layers || []) : [];
            var html   = '';

            if (!layers.length) {
                html = '<div class="spe-layers-empty"><svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="4" y="6" width="24" height="20" rx="3" stroke="currentColor" stroke-width="1.5" opacity=".3"/><path d="M16 13v6M13 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".5"/></svg><span>No layers yet</span><small>Click the + button above to add layers</small></div>';
            } else {
                /* Grouping: Parent layers first, and children indented under them */
                var roots = layers.filter(function(l) { return !l.parent_id; }).sort(function (a, b) { return (b.z_index || 0) - (a.z_index || 0); });
                
                var renderItem = function(lyr, depth) {
                    var isSelected = String(lyr.id) === String(state.selectedLayerId);
                    var icon = LayersPanel.svgIcons[lyr.type] || LayersPanel.svgIcons.shape;
                    var children = layers.filter(function(l) { return String(l.parent_id) === String(lyr.id); }).sort(function (a, b) { return (b.z_index || 0) - (a.z_index || 0); });
                    var hasChildren = children.length > 0;

                    var itemHtml = '<div class="spe-layer-item' + (isSelected ? ' is-selected' : '') + (lyr.locked ? ' is-locked' : '') + (!lyr.visible ? ' is-hidden' : '') + (lyr.parent_id ? ' is-child' : '') + '"' +
                            ' data-layer-id="' + Utils.esc(lyr.id) + '" style="padding-left:' + (10 + depth * 20) + 'px">' +
                            '<span class="spe-li-drag" title="Drag to reorder">' + LayersPanel.ctrlIcons.drag + '</span>' +
                            '<span class="spe-li-icon spe-type-' + lyr.type + '">' + icon + '</span>' +
                            '<span class="spe-li-name">' + Utils.esc(lyr.name || lyr.type) + '</span>' +
                            '<span class="spe-li-actions">' +
                              '<button class="spe-li-ctrl spe-li-vis" title="Toggle visibility" data-id="' + Utils.esc(lyr.id) + '">' + (lyr.visible ? LayersPanel.ctrlIcons.visible : LayersPanel.ctrlIcons.hidden) + '</button>' +
                              '<button class="spe-li-ctrl spe-li-lock" title="Toggle lock" data-id="' + Utils.esc(lyr.id) + '">' + (lyr.locked ? LayersPanel.ctrlIcons.locked : LayersPanel.ctrlIcons.unlocked) + '</button>' +
                              '<button class="spe-li-ctrl spe-li-dup" title="Duplicate" data-id="' + Utils.esc(lyr.id) + '">' + LayersPanel.ctrlIcons.dup + '</button>' +
                              '<button class="spe-li-ctrl spe-li-del" title="Delete" data-id="' + Utils.esc(lyr.id) + '">' + LayersPanel.ctrlIcons.del + '</button>' +
                            '</span>' +
                            '</div>';
                    
                    children.forEach(function(c) {
                        itemHtml += renderItem(c, depth + 1);
                    });
                    return itemHtml;
                };

                roots.forEach(function(r) {
                    html += renderItem(r, 0);
                });
            }
            LayersPanel.$el.html(html);

            /* Sortable */
            LayersPanel.$el.sortable({
                handle: '.spe-li-drag',
                items: '.spe-layer-item',
                placeholder: 'spe-li-placeholder',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                stop: function (e, ui) {
                    LayersPanel.updateStructureFromDOM();
                    History.push();
                    Canvas.render();
                },
            });
        },

        updateStructureFromDOM: function () {
            var slide = Utils.activeSlide();
            if (!slide) return;
            
            var newLayers = [];
            var total = slide.layers.length;
            
            /* We determine parenting by visual order in the list (if we wanted to allow drag-to-parent) 
               but for now let's just update Z-index based on visual flat order to keep it simple, 
               and rely on the Context Menu for parenting. */
            
            LayersPanel.$el.find('.spe-layer-item').each(function (i) {
                var id  = $(this).data('layer-id');
                var lyr = Utils.layerById(id);
                if (lyr) {
                    lyr.z_index = (total - i) * 10;
                    newLayers.push(lyr);
                }
            });
            slide.layers = newLayers;
        },

        updateZFromDOM: function () {
            LayersPanel.updateStructureFromDOM();
        },

        typeIcon: function (type) {
            return LayersPanel.svgIcons[type] || LayersPanel.svgIcons.shape;
        },

        updateSelection: function () {
            LayersPanel.$el.find('.spe-layer-item').each(function () {
                var id = String($(this).data('layer-id'));
                var isSelected = state.selectedLayerIds.map(String).indexOf(id) > -1;
                $(this).toggleClass('is-selected', isSelected);
            });
        },

        bindEvents: function () {
            /* Click item → select layer */
            LayersPanel.$el.on('click', '.spe-layer-item', function (e) {
                if ($(e.target).closest('button').length) return;
                var id = $(this).data('layer-id');
                Canvas.selectLayer(id, e.ctrlKey || e.metaKey || e.shiftKey);
            });
            });

            /* Visibility toggle */
            LayersPanel.$el.on('click', '.spe-li-vis', function (e) {
                e.stopPropagation();
                var lyr = Utils.layerById($(this).data('id'));
                if (!lyr) return;
                lyr.visible = !lyr.visible;
                History.push();
                LayersPanel.render();
                Canvas.render();
            });

            /* Lock toggle */
            LayersPanel.$el.on('click', '.spe-li-lock', function (e) {
                e.stopPropagation();
                var lyr = Utils.layerById($(this).data('id'));
                if (!lyr) return;
                lyr.locked = !lyr.locked;
                History.push();
                LayersPanel.render();
                Canvas.render();
            });

            /* Duplicate */
            LayersPanel.$el.on('click', '.spe-li-dup', function (e) {
                e.stopPropagation();
                Editor.duplicateLayer($(this).data('id'));
            });

            /* Delete */
            LayersPanel.$el.on('click', '.spe-li-del', function (e) {
                e.stopPropagation();
                Editor.deleteLayer($(this).data('id'));
            });

            /* Toggle add-layer drawer */
            $('#spe-add-layer-toggle').on('click', function () {
                var $drawer = $('#spe-add-layer-drawer');
                $drawer.slideToggle(150);
                $(this).toggleClass('is-open');
            });

            /* Add layer buttons (inside drawer) */
            $('#spe-add-layer-btns').on('click', '.spe-add-layer-btn', function () {
                var type = $(this).data('type');
                Editor.addLayer(type);
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * PROPERTIES PANEL
     * ─────────────────────────────────────────────────────────────────────*/
    var PropertiesPanel = {
        $el:    null,
        $inner: null,

        init: function () {
            PropertiesPanel.$el    = $('#spe-props');
            PropertiesPanel.$inner = $('#spe-props-inner');
        },

        clear: function () {
            PropertiesPanel.$inner.html(
                '<div class="spe-props-empty">' +
                '<svg width="40" height="40" viewBox="0 0 40 40" fill="none"><rect x="6" y="8" width="28" height="24" rx="3" stroke="currentColor" stroke-width="1.5" opacity=".25"/><path d="M14 16h12M14 21h8M14 26h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".35"/></svg>' +
                '<span>Select a layer to edit</span>' +
                '</div>'
            );
        },

        render: function (lyr) {
            if (!lyr) { PropertiesPanel.clear(); return; }

            var sections = PropertiesPanel.buildSections(lyr);
            var typeLabel = lyr.type.charAt(0).toUpperCase() + lyr.type.slice(1);
            var icon = LayersPanel.svgIcons[lyr.type] || '';
            var html = '<div class="spe-props-header">' +
                '<span class="spe-props-type-icon spe-type-' + lyr.type + '">' + icon + '</span>' +
                '<span class="spe-props-type-label">' + Utils.esc(typeLabel) + ' Layer</span>' +
                '</div>';

            sections.forEach(function (sec, idx) {
                var open = idx < 2; /* first two sections open by default */
                html += '<div class="spe-props-section' + (open ? ' is-open' : '') + '">' +
                    '<div class="spe-section-title" data-toggle="section">' +
                      '<span>' + sec.title + '</span>' +
                      '<svg class="spe-section-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                    '</div>';
                html += '<div class="spe-section-body"' + (!open ? ' style="display:none"' : '') + '>';
                sec.fields.forEach(function (f) {
                    html += PropertiesPanel.fieldHTML(f, lyr);
                });
                html += '</div></div>';
            });

            /* Animation button */
            html += '<div class="spe-props-anim-row">' +
                '<button id="spe-open-anim" class="spe-props-anim-btn">' +
                '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1v2M7 11v2M1 7h2M11 7h2M2.8 2.8l1.4 1.4M9.8 9.8l1.4 1.4M2.8 11.2l1.4-1.4M9.8 4.2l1.4-1.4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>' +
                ' Animations' +
                '</button>' +
                '</div>';

            PropertiesPanel.$inner.html(html);
            PropertiesPanel.bindFields(lyr);
        },

        renderMulti: function (ids) {
            var html = '<div class="spe-props-header">' +
                '<span class="spe-props-type-icon">' +
                '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="2" y="2" width="6" height="6" stroke="currentColor"/><rect x="6" y="6" width="6" height="6" stroke="#fff" fill="var(--spe-primary)"/></svg>' +
                '</span>' +
                '<span class="spe-props-type-label">' + ids.length + ' Layers Selected</span>' +
                '</div>';

            html += '<div class="spe-props-section is-open">' +
                    '<div class="spe-section-title"><span>Bulk Actions</span></div>' +
                    '<div class="spe-section-body">' +
                      '<div class="spe-multi-actions">' +
                        '<button class="spe-btn primary block" id="spe-multi-group">Group Selected</button>' +
                        '<button class="spe-btn danger block" id="spe-multi-delete">Delete Selected</button>' +
                      '</div>' +
                    '</div></div>';
            
            PropertiesPanel.$inner.html(html);
            
            $('#spe-multi-delete').on('click', function() {
                ids.forEach(function(id) { Editor.deleteLayer(id); });
                Canvas.selectLayer(null);
            });
            
            $('#spe-multi-group').on('click', function() {
                var parentId = ids[0];
                for(var i=1; i<ids.length; i++) {
                    var l = Utils.layerById(ids[i]);
                    if (l) l.parent_id = parentId;
                }
                History.push();
                LayersPanel.render();
                Toolbar.notify('Grouped ' + (ids.length-1) + ' layers under ' + Utils.layerById(parentId).name, 'success');
            });
        },

        buildSections: function (lyr) {
            var sections = [];

            /* Position & Size */
            sections.push({
                title: 'Position & Size',
                fields: [
                    { key: 'x',      label: 'X',       type: 'number', min: -9999 },
                    { key: 'y',      label: 'Y',       type: 'number', min: -9999 },
                    { key: 'width',  label: 'Width',   type: 'number', min: 1 },
                    { key: 'height', label: 'Height',  type: 'number', min: 1 },
                    { key: 'z_index', label: 'Z-Index', type: 'number', min: 0 },
                    { key: 'rotation', label: 'Rotation', type: 'number', min: -360, max: 360 },
                    { key: 'opacity',  label: 'Opacity',  type: 'number', min: 0, max: 1, step: 0.05 },
                ],
            });

            /* Layer name */
            sections.push({ title: 'Identity', fields: [{ key: 'name', label: 'Layer Name', type: 'text' }, { key: 'alt', label: 'Accessibility Label', type: 'text' }] });

            /* Parallax */
            sections.push({ title: 'Effects', fields: [{ key: 'parallax_depth', label: 'Parallax Depth', type: 'number', min: -1, max: 1, step: 0.05 }] });

            /* Hover */
            sections.push({ title: 'Hover Effect', fields: [
                { key: 'hover.effect', label: 'Effect', type: 'select', options: [ { v:'none',l:'None' },{ v:'scale',l:'Scale' },{ v:'glow',l:'Glow' },{ v:'reveal',l:'Reveal' } ] },
                { key: 'hover.scale', label: 'Scale Amount', type: 'number', min: 1, max: 1.5, step: 0.01, show_if: { key:'hover.effect', val:'scale' } },
                { key: 'hover.glow', label: 'Glow Highlight', type: 'checkbox', show_if: { key:'hover.effect', val:'glow' } }
            ] });

            /* Click */
            sections.push({ title: 'Click Action', fields: [
                { key: 'click.action', label: 'Action', type: 'select', options: [ { v:'none',l:'None' },{ v:'url',l:'Open URL' },{ v:'slide',l:'Go to Slide' },{ v:'video',l:'Play Video' } ] },
                { key: 'click.url',    label: 'URL',   type: 'text',   show_if: { key:'click.action', val:'url' } },
                { key: 'click.target', label: 'Target', type: 'select', options: [ { v:'_self',l:'Same tab' },{ v:'_blank',l:'New tab' } ], show_if: { key:'click.action', val:'url' } },
                { key: 'click.slide',  label: 'Slide #', type: 'number', min: 1, show_if: { key:'click.action', val:'slide' } },
            ] });

            /* Type-specific */
            switch (lyr.type) {
                case 'text':
                    sections.push({ title: 'Text', fields: [
                        { key: 'content',        label: 'Content',      type: 'textarea' },
                        { key: 'font_size',      label: 'Font Size',    type: 'number', min: 1 },
                        { key: 'font_weight',    label: 'Weight',       type: 'select', options: [ { v:'400',l:'Normal' },{ v:'700',l:'Bold' },{ v:'300',l:'Light' },{ v:'900',l:'Black' } ] },
                        { key: 'color',          label: 'Color',        type: 'color' },
                        { key: 'text_align',     label: 'Align',        type: 'select', options: [ { v:'left',l:'Left' },{ v:'center',l:'Center' },{ v:'right',l:'Right' } ] },
                        { key: 'line_height',    label: 'Line Height',  type: 'number', min: 0.5, max: 5, step: 0.1 },
                        { key: 'letter_spacing', label: 'Letter Spacing', type: 'number', min: -5, max: 20, step: 0.5 },
                    ] });
                    break;
                case 'image':
                    sections.push({ title: 'Image', fields: [
                        { key: 'src',           label: 'Image URL',   type: 'media', btn: 'Choose Image' },
                        { key: 'alt',           label: 'Alt Text',    type: 'text' },
                        { key: 'object_fit',    label: 'Object Fit',  type: 'select', options: [ { v:'cover',l:'Cover' },{ v:'contain',l:'Contain' },{ v:'fill',l:'Fill' } ] },
                        { key: 'border_radius', label: 'Border Radius', type: 'number', min: 0 },
                    ] });
                    break;
                case 'button':
                    sections.push({ title: 'Button', fields: [
                        { key: 'label',       label: 'Label',       type: 'text' },
                        { key: 'url',         label: 'URL',         type: 'text' },
                        { key: 'target',      label: 'Target',      type: 'select', options: [ { v:'_self',l:'Same tab' },{ v:'_blank',l:'New tab' } ] },
                        { key: 'bg_color',    label: 'BG Color',    type: 'color' },
                        { key: 'text_color',  label: 'Text Color',  type: 'color' },
                        { key: 'hover_bg',    label: 'Hover BG',    type: 'color' },
                        { key: 'border_radius', label: 'Radius',    type: 'number', min: 0 },
                        { key: 'font_size',   label: 'Font Size',   type: 'number', min: 8 },
                    ] });
                    break;
                case 'video':
                    sections.push({ title: 'Video', fields: [
                        { key: 'src',      label: 'Video URL',  type: 'text' },
                        { key: 'autoplay', label: 'Autoplay',  type: 'checkbox' },
                        { key: 'loop',     label: 'Loop',      type: 'checkbox' },
                        { key: 'muted',    label: 'Muted',     type: 'checkbox' },
                        { key: 'controls', label: 'Controls',  type: 'checkbox' },
                    ] });
                    break;
                case 'shape':
                    sections.push({ title: 'Shape', fields: [
                        { key: 'shape',         label: 'Shape',   type: 'select', options: [ { v:'rectangle',l:'Rectangle' },{ v:'circle',l:'Circle' },{ v:'triangle',l:'Triangle' } ] },
                        { key: 'fill',          label: 'Fill',    type: 'color' },
                        { key: 'stroke',        label: 'Stroke',  type: 'color' },
                        { key: 'stroke_width',  label: 'Stroke W', type: 'number', min: 0 },
                        { key: 'border_radius', label: 'Radius',  type: 'number', min: 0 },
                    ] });
                    break;
                case 'countdown':
                    sections.push({ title: 'Countdown', fields: [
                        { key: 'countdown_target', label: 'Target Date', type: 'datetime-local' },
                        { key: 'countdown_label', label: 'Label', type: 'text' },
                        { key: 'countdown_expired', label: 'Expired Text', type: 'text' },
                        { key: 'font_size', label: 'Font Size', type: 'number', min: 8 },
                        { key: 'font_weight', label: 'Weight', type: 'select', options: [ { v:'400',l:'Normal' },{ v:'700',l:'Bold' },{ v:'900',l:'Black' } ] },
                        { key: 'color', label: 'Color', type: 'color' },
                    ] });
                    break;
                case 'icon':
                    sections.push({ title: 'Icon', fields: [
                        { key: 'icon_class', label: 'Dashicon Class', type: 'text' },
                        { key: 'icon_size', label: 'Size', type: 'number', min: 8 },
                        { key: 'color', label: 'Color', type: 'color' },
                    ] });
                    break;
                case 'lottie':
                    sections.push({ title: 'Lottie', fields: [
                        { key: 'lottie_src', label: 'JSON URL', type: 'text' },
                        { key: 'lottie_autoplay', label: 'Autoplay', type: 'checkbox' },
                        { key: 'lottie_loop', label: 'Loop', type: 'checkbox' },
                    ] });
                    break;
                case 'html':
                    sections.push({ title: 'HTML', fields: [
                        { key: 'html_content', label: 'Markup', type: 'textarea' },
                    ] });
                    break;
            }

            /* Breakpoint overrides */
            sections.push({ title: 'Breakpoints', fields: [
                { key: 'breakpoints.tablet.visible', label: 'Visible on Tablet', type: 'checkbox' },
                { key: 'breakpoints.mobile.visible', label: 'Visible on Mobile', type: 'checkbox' },
                { key: 'breakpoints.tablet.font_size_scale', label: 'Tablet Font Scale', type: 'number', min: 0.1, max: 2, step: 0.05 },
                { key: 'breakpoints.mobile.font_size_scale', label: 'Mobile Font Scale', type: 'number', min: 0.1, max: 2, step: 0.05 },
            ] });

            return sections;
        },

        fieldHTML: function (f, lyr) {
            var val = PropertiesPanel.getNestedVal(lyr, f.key);
            var id  = 'spf-' + f.key.replace(/\./g, '-');

            /* show_if logic — hide if condition not met */
            var hidden = '';
            if (f.show_if) {
                var dep = PropertiesPanel.getNestedVal(lyr, f.show_if.key);
                if (dep !== f.show_if.val) hidden = ' style="display:none"';
            }

            var html = '<div class="spe-field"' + hidden + ' data-key="' + Utils.esc(f.key) + '">';
            html += '<label class="spe-field-label" for="' + id + '">' + Utils.esc(f.label) + '</label>';

            switch (f.type) {
                case 'text':
                    html += '<input type="text" id="' + id + '" class="spe-field-inp" data-key="' + f.key + '" value="' + Utils.esc(val !== undefined ? val : '') + '">';
                    break;
                case 'number':
                    html += '<input type="number" id="' + id + '" class="spe-field-inp" data-key="' + f.key + '" value="' + (val !== undefined ? val : '') + '"' +
                            (f.min !== undefined ? ' min="' + f.min + '"' : '') +
                            (f.max !== undefined ? ' max="' + f.max + '"' : '') +
                            (f.step !== undefined ? ' step="' + f.step + '"' : '') + '>';
                    break;
                case 'datetime-local':
                    html += '<input type="datetime-local" id="' + id + '" class="spe-field-inp" data-key="' + f.key + '" value="' + Utils.esc(val !== undefined ? val : '') + '">';
                    break;
                case 'color':
                    html += '<input type="color" id="' + id + '" class="spe-field-inp" data-key="' + f.key + '" value="' + Utils.esc(val || '#000000') + '">';
                    break;
                case 'checkbox':
                    html += '<input type="checkbox" id="' + id + '" class="spe-field-inp spe-checkbox" data-key="' + f.key + '"' + (val ? ' checked' : '') + '>';
                    break;
                case 'textarea':
                    html += '<textarea id="' + id + '" class="spe-field-inp" data-key="' + f.key + '" rows="4">' + Utils.esc(val || '') + '</textarea>';
                    break;
                case 'select':
                    html += '<select id="' + id + '" class="spe-field-inp" data-key="' + f.key + '">';
                    (f.options || []).forEach(function (opt) {
                        html += '<option value="' + Utils.esc(opt.v) + '"' + (String(val) === String(opt.v) ? ' selected' : '') + '>' + Utils.esc(opt.l) + '</option>';
                    });
                    html += '</select>';
                    break;
                case 'media':
                    html += '<div class="spe-media-field">' +
                            '<input type="text" id="' + id + '" class="spe-field-inp" data-key="' + f.key + '" value="' + Utils.esc(val || '') + '">' +
                            '<button class="spe-btn spe-btn-xs spe-media-choose" data-target="' + id + '">' + (f.btn || 'Choose') + '</button>' +
                            '</div>';
                    break;
            }

            html += '</div>';
            return html;
        },

        getNestedVal: function (obj, path) {
            if (['x', 'y', 'width', 'height'].includes(path)) {
                return Canvas.getVal(obj, path, 0);
            }
            return path.split('.').reduce(function (o, k) { return (o && o[k] !== undefined) ? o[k] : undefined; }, obj);
        },

        setNestedVal: function (obj, path, val) {
            var parts = path.split('.');
            var last  = parts.pop();
            var ref   = obj;
            parts.forEach(function (k) { if (ref[k] === undefined) ref[k] = {}; ref = ref[k]; });
            ref[last] = val;
        },

        updateCoords: function (lyr) {
            PropertiesPanel.$inner.find('[data-key="x"]').val(Canvas.getVal(lyr, 'x', 0));
            PropertiesPanel.$inner.find('[data-key="y"]').val(Canvas.getVal(lyr, 'y', 0));
            PropertiesPanel.$inner.find('[data-key="width"]').val(Canvas.getVal(lyr, 'width', 100));
            PropertiesPanel.$inner.find('[data-key="height"]').val(Canvas.getVal(lyr, 'height', 100));
        },

        bindFields: function (lyr) {
            /* Collapsible sections */
            PropertiesPanel.$inner.on('click', '[data-toggle="section"]', function () {
                var $section = $(this).closest('.spe-props-section');
                $section.toggleClass('is-open');
                $section.find('.spe-section-body').slideToggle(150);
            });

            /* Input / change */
            PropertiesPanel.$inner.on('input change', '.spe-field-inp', function () {
                var key = $(this).data('key');
                var val;
                if ($(this).is('[type=checkbox]')) {
                    val = $(this).prop('checked');
                } else if ($(this).is('[type=number]')) {
                    val = parseFloat($(this).val());
                    if (isNaN(val)) return;
                } else {
                    val = $(this).val();
                }

                if (['x', 'y', 'width', 'height'].includes(key)) {
                    Canvas.setVal(lyr, key, val);
                } else {
                    PropertiesPanel.setNestedVal(lyr, key, val);
                }
                
                state.isDirty = true;
                Canvas.render();
            });

            /* Debounced history push on change */
            var histTimer;
            PropertiesPanel.$inner.on('change', '.spe-field-inp', function () {
                clearTimeout(histTimer);
                histTimer = setTimeout(function () { History.push(); }, 400);
            });

            /* Media chooser */
            PropertiesPanel.$inner.on('click', '.spe-media-choose', function () {
                var targetId = $(this).data('target');
                var frame = wp.media({ title: 'Choose Image', button: { text: 'Use Image' }, multiple: false });
                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    $('#' + targetId).val(att.url).trigger('input');
                });
                frame.open();
            });

            /* Animation button */
            PropertiesPanel.$inner.on('click', '#spe-open-anim', function () {
                AnimationEditor.open(lyr);
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * SLIDE MANAGER
     * ─────────────────────────────────────────────────────────────────────*/
    var SlideManager = {
        $el:   null,
        $list: null,

        init: function () {
            SlideManager.$el   = $('#spe-slide-manager');
            SlideManager.$list = $('#spe-slides-strip');
            SlideManager.bindEvents();
        },

        render: function () {
            var html = '';
            state.slides.forEach(function (slide, idx) {
                var isActive = idx === state.activeSlideIdx;
                var thumb = (slide.settings && slide.settings.bg_image) ? 'background-image:url(' + slide.settings.bg_image + ');background-size:cover;background-position:center;' : 'background:' + ((slide.settings && slide.settings.bg_color) || '#1a1a2e') + ';';
                var layerCount = (slide.layers || []).length;
                html += '<div class="spe-slide-thumb' + (isActive ? ' is-active' : '') + '" data-idx="' + idx + '">' +
                        '<div class="spe-thumb-preview" style="' + thumb + '">' +
                          '<div class="spe-thumb-actions">' +
                            '<button class="spe-thumb-ctrl spe-thumb-dup" title="Duplicate slide" data-idx="' + idx + '">' +
                              '<svg width="10" height="10" viewBox="0 0 12 12" fill="none"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.2"/><path d="M9 3V2a1 1 0 00-1-1H2a1 1 0 00-1 1v6a1 1 0 001 1h1" stroke="currentColor" stroke-width="1.2"/></svg>' +
                            '</button>' +
                            '<button class="spe-thumb-ctrl spe-thumb-del" title="Delete slide" data-idx="' + idx + '">' +
                              '<svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M4.5 3V2h3v1M3 3v7a1 1 0 001 1h4a1 1 0 001-1V3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>' +
                            '</button>' +
                          '</div>' +
                        '</div>' +
                        '<div class="spe-thumb-info">' +
                          '<span class="spe-thumb-num">' + (idx + 1) + '</span>' +
                          '<span class="spe-thumb-meta">' + layerCount + ' layer' + (layerCount !== 1 ? 's' : '') + '</span>' +
                        '</div>' +
                        '</div>';
            });

            html += '<div class="spe-add-slide-wrap">' +
                      '<button id="spe-add-slide-btn" title="Add Slide" class="spe-thumb-add">' +
                        '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>' +
                        '<span>Add Slide</span>' +
                      '</button>' +
                      '<div id="spe-add-slide-menu" class="spe-add-slide-menu" style="display:none;">' +
                        '<button class="spe-add-menu-item" data-action="blank">' +
                          '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="2" y="1" width="10" height="12" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="M5 5h4M5 7.5h4M5 10h2" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity=".5"/></svg>' +
                          ' Blank Slide' +
                        '</button>' +
                        '<button class="spe-add-menu-item" data-action="template">' +
                          '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="8" y="1" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="1" y="8" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="8" y="8" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.2"/></svg>' +
                          ' From Template' +
                        '</button>' +
                      '</div>' +
                    '</div>';
            SlideManager.$list.html(html);

            /* Sortable */
            SlideManager.$list.sortable({
                items: '.spe-slide-thumb',
                stop: function () {
                    var newOrder = [];
                    SlideManager.$list.find('.spe-slide-thumb').each(function () {
                        newOrder.push(state.slides[parseInt($(this).data('idx'))]);
                    });
                    state.slides = newOrder;
                    state.activeSlideIdx = 0;
                    History.push();
                    SlideManager.render();
                    Canvas.render();
                    LayersPanel.render();
                },
            });
        },

        bindEvents: function () {
            /* Click thumbnail → switch slide */
            SlideManager.$list.on('click', '.spe-slide-thumb', function (e) {
                if ($(e.target).is('button')) return;
                state.activeSlideIdx  = parseInt($(this).data('idx'));
                state.selectedLayerId = null;
                SlideManager.render();
                Canvas.render();
                LayersPanel.render();
                PropertiesPanel.clear();
            });

            /* Add slide — toggle dropdown menu */
            SlideManager.$list.on('click', '#spe-add-slide-btn', function (e) {
                e.stopPropagation();
                var $menu = $('#spe-add-slide-menu');
                $menu.toggle();
            });

            /* Add slide menu: blank slide */
            SlideManager.$list.on('click', '.spe-add-menu-item[data-action="blank"]', function (e) {
                e.stopPropagation();
                $('#spe-add-slide-menu').hide();
                Editor.addSlide();
            });

            /* Add slide menu: from template */
            SlideManager.$list.on('click', '.spe-add-menu-item[data-action="template"]', function (e) {
                e.stopPropagation();
                $('#spe-add-slide-menu').hide();
                TemplatesModal.openForSlide();
            });

            /* Static HTML Add Slide button (outside strip) */
            $('#spe-add-slide').on('click', function (e) {
                e.stopPropagation();
                var $menu = $('#spe-add-slide-menu');
                $menu.toggle();
            });

            /* Close add-slide menu on outside click */
            $(document).on('click', function () {
                $('#spe-add-slide-menu').hide();
            });

            /* Duplicate slide */
            SlideManager.$list.on('click', '.spe-thumb-dup', function (e) {
                e.stopPropagation();
                Editor.duplicateSlide(parseInt($(this).data('idx')));
            });

            /* Delete slide */
            SlideManager.$list.on('click', '.spe-thumb-del', function (e) {
                e.stopPropagation();
                Editor.deleteSlide(parseInt($(this).data('idx')));
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * TOOLBAR
     * ─────────────────────────────────────────────────────────────────────*/
    var Toolbar = {
        init: function () {
            /* Undo / Redo */
            $('#spe-undo').on('click', function () { History.undo(); });
            $('#spe-redo').on('click', function () { History.redo(); });

            /* Save */
            $('#spe-save-btn').on('click', function () { API.save(); });

            /* Preview */
            $('#spe-preview-btn').on('click', function () { PreviewModal.open(); });

            /* Settings */
            $('#spe-settings-btn').on('click', function () { SettingsModal.open(); });

            /* Templates */
            $('#spe-templates-btn').on('click', function () { TemplatesModal.open(); });

            /* Dynamic Content */
            $('#spe-dynamic-btn').on('click', function () { DynamicModal.open(); });

            /* Timeline */
            $('#spe-timeline-btn').on('click', function () { TimelineModal.open(); });

            /* Global Styles (Theme) */
            $('#spe-theme-btn').on('click', function () { ThemeManager.open(); });

            /* History Panel */
            $('#spe-history-btn').on('click', function () { HistoryPanel.open(); });

            /* Breakpoint switcher */
            $('.spe-bp-btn').on('click', function () {
                state.breakpoint = $(this).data('bp');
                $('.spe-bp-btn').removeClass('active');
                $(this).addClass('active');
                Canvas.updateSize();
                Canvas.render();
                LayersPanel.render();
            });

            /* Slider name edit */
            $('#spe-slider-name').on('input', function () {
                state.slider.name = $(this).text();
                state.isDirty = true;
            });

            /* Keyboard shortcuts */
            $(document).on('keydown', function (e) {
                var tag = (e.target.tagName || '').toLowerCase();
                if (tag === 'input' || tag === 'textarea' || tag === 'select' || $(e.target).is('[contenteditable]')) return;

                if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); History.undo(); }
                if ((e.ctrlKey || e.metaKey) && (e.shiftKey && e.key === 'z' || e.key === 'y')) { e.preventDefault(); History.redo(); }
                if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); API.save(); }
                if (e.key === 'Delete' || e.key === 'Backspace') {
                    if (state.selectedLayerId && !$(document.activeElement).is('input,textarea,select')) {
                        Editor.deleteLayer(state.selectedLayerId);
                    }
                }
                if (e.key === 'Escape') { state.selectedLayerId = null; Canvas.updateSelectionBox(); PropertiesPanel.clear(); LayersPanel.updateSelection(); }

                /* Arrow nudge */
                var nudgeAmt = e.shiftKey ? 10 : 1;
                if (state.selectedLayerId) {
                    var lyr = Utils.layerById(state.selectedLayerId);
                    if (lyr && !lyr.locked) {
                        var moved = true;
                        if (e.key === 'ArrowLeft')  { lyr.x -= nudgeAmt; }
                        else if (e.key === 'ArrowRight') { lyr.x += nudgeAmt; }
                        else if (e.key === 'ArrowUp')    { lyr.y -= nudgeAmt; }
                        else if (e.key === 'ArrowDown')  { lyr.y += nudgeAmt; }
                        else moved = false;
                        if (moved) { e.preventDefault(); Canvas.render(); PropertiesPanel.updateCoords(lyr); }
                    }
                }
            });
        },

        updateUndoRedo: function () {
            $('#spe-undo').prop('disabled', state.historyIndex <= 0);
            $('#spe-redo').prop('disabled', state.historyIndex >= state.history.length - 1);
        },

        setSaving: function (saving) {
            var $btn = $('#spe-save-btn');
            $btn.prop('disabled', saving);
            if (saving) {
                $btn.addClass('is-saving').find('svg').hide();
                $btn.contents().filter(function () { return this.nodeType === 3; }).first().replaceWith(' Saving…');
            } else {
                $btn.removeClass('is-saving').find('svg').show();
                $btn.contents().filter(function () { return this.nodeType === 3; }).first().replaceWith(' Save');
            }
        },

        notify: function (msg, type) {
            var $n = $('#spe-notify');
            if (!$n.length) {
                $n = $('<div id="spe-notify" class="spe-toast"></div>').appendTo('#spslider-editor-app');
            }
            var icon = type === 'success'
                ? '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 8l3 3 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                : '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3.5M8 10.5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
            $n.html(icon + '<span>' + Utils.esc(msg) + '</span>')
              .attr('data-type', type || 'success')
              .addClass('show');
            clearTimeout(Toolbar._notifyTimer);
            Toolbar._notifyTimer = setTimeout(function () { $n.removeClass('show'); }, 2800);
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * SETTINGS MODAL
     * ─────────────────────────────────────────────────────────────────────*/
    var SettingsModal = {
        open: function () {
            var s = state.slider.settings;
            var transitions = cfg.transitions || {};
            var easings     = cfg.easings     || {};

            var tOpts = '<option value="">— Choose —</option>';
            var eOpts = '<option value="">— Choose —</option>';
            $.each(transitions, function (k, v) { tOpts += '<option value="' + Utils.esc(k) + '"' + (s.transition === k ? ' selected' : '') + '>' + Utils.esc(v) + '</option>'; });
            $.each(easings,     function (k, v) { eOpts += '<option value="' + Utils.esc(k) + '"' + (s.easing     === k ? ' selected' : '') + '>' + Utils.esc(v) + '</option>'; });

            var tabs = [
                { id: 'general',    label: 'General',    icon: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 9a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.3"/><path d="M11.5 5.5l-.8-.3a4 4 0 00-.4-.7l.2-.8a.4.4 0 00-.1-.4l-.5-.5a.4.4 0 00-.4-.1l-.8.2a4 4 0 00-.7-.4L7.7 2a.4.4 0 00-.4-.3h-.6a.4.4 0 00-.4.3l-.3.8a4 4 0 00-.7.4l-.8-.2a.4.4 0 00-.4.1l-.5.5a.4.4 0 00-.1.4l.2.8a4 4 0 00-.4.7L3 5.7a.4.4 0 00-.3.4v.6a.4.4 0 00.3.4l.8.3c.1.3.2.5.4.7l-.2.8a.4.4 0 00.1.4l.5.5a.4.4 0 00.4.1l.8-.2c.2.2.5.3.7.4l.3.8a.4.4 0 00.4.3h.6a.4.4 0 00.4-.3l.3-.8c.3-.1.5-.2.7-.4l.8.2a.4.4 0 00.4-.1l.5-.5a.4.4 0 00.1-.4l-.2-.8c.2-.2.3-.5.4-.7l.8-.3a.4.4 0 00.3-.4v-.6a.4.4 0 00-.3-.4z" stroke="currentColor" stroke-width="1"/></svg>' },
                { id: 'autoplay',   label: 'Autoplay',   icon: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M4 2.5v9l7-4.5-7-4.5z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>' },
                { id: 'navigation', label: 'Navigation', icon: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 7h8M9 4l3 3-3 3M5 4L2 7l3 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>' },
                { id: 'transition', label: 'Transition', icon: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="5" height="8" rx="1" stroke="currentColor" stroke-width="1.2"/><rect x="8" y="3" width="5" height="8" rx="1" stroke="currentColor" stroke-width="1.2" stroke-dasharray="2 1.5"/><path d="M6 7h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>' },
                { id: 'scaling',    label: 'Scaling',    icon: '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 5V2a1 1 0 011-1h3M9 1h3a1 1 0 011 1v3M13 9v3a1 1 0 01-1 1h-3M5 13H2a1 1 0 01-1-1V9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>' },
            ];

            var tabNav = '<div class="spe-stabs">';
            tabs.forEach(function (t, i) {
                tabNav += '<button class="spe-stab' + (i === 0 ? ' active' : '') + '" data-tab="' + t.id + '">' + t.icon + ' <span>' + t.label + '</span></button>';
            });
            tabNav += '</div>';

            var panels = '';

            /* General */
            panels += '<div class="spe-stab-panel active" data-panel="general">' +
                '<div class="spe-settings-grid">' +
                  '<div class="spe-sfield"><label>Width (px)</label><input type="number" data-key="width" value="' + (s.width || 1200) + '" min="100"></div>' +
                  '<div class="spe-sfield"><label>Height (px)</label><input type="number" data-key="height" value="' + (s.height || 600) + '" min="50"></div>' +
                '</div>' +
                '</div>';

            /* Autoplay */
            panels += '<div class="spe-stab-panel" data-panel="autoplay">' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Enable Autoplay</label><label class="spe-toggle"><input type="checkbox" data-key="autoplay" ' + (s.autoplay ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield"><label>Speed (ms)</label><input type="number" data-key="autoplay_speed" value="' + (s.autoplay_speed || 5000) + '" min="500" step="250"></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Pause on Hover</label><label class="spe-toggle"><input type="checkbox" data-key="pause_on_hover" ' + (s.pause_on_hover ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Loop</label><label class="spe-toggle"><input type="checkbox" data-key="loop" ' + (s.loop ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '</div>';

            /* Navigation */
            panels += '<div class="spe-stab-panel" data-panel="navigation">' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Show Arrows</label><label class="spe-toggle"><input type="checkbox" data-key="arrows" ' + (s.arrows ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Show Dots</label><label class="spe-toggle"><input type="checkbox" data-key="dots" ' + (s.dots ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Touch / Swipe</label><label class="spe-toggle"><input type="checkbox" data-key="touch" ' + (s.touch ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Keyboard Navigation</label><label class="spe-toggle"><input type="checkbox" data-key="keyboard_nav" ' + (s.keyboard_nav ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Global Parallax</label><label class="spe-toggle"><input type="checkbox" data-key="parallax" ' + (s.parallax ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Lazy Load</label><label class="spe-toggle"><input type="checkbox" data-key="lazy_load" ' + (s.lazy_load ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '<div class="spe-sfield spe-sfield-toggle"><label>Preload Next Slide</label><label class="spe-toggle"><input type="checkbox" data-key="preload_next" ' + (s.preload_next ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                '</div>';

            /* Transition */
            panels += '<div class="spe-stab-panel" data-panel="transition">' +
                '<div class="spe-sfield"><label>Effect</label><select data-key="transition">' + tOpts + '</select></div>' +
                '<div class="spe-sfield"><label>Speed (ms)</label><input type="number" data-key="speed" value="' + (s.speed || 700) + '" min="100" step="50"></div>' +
                '<div class="spe-sfield"><label>Easing</label><select data-key="easing">' + eOpts + '</select></div>' +
                '</div>';

            /* Scaling */
            panels += '<div class="spe-stab-panel" data-panel="scaling">' +
                '<div class="spe-sfield"><label>Mode</label><select data-key="scaling_mode">' +
                  '<option value="auto"' + (s.scaling_mode === 'auto' ? ' selected' : '') + '>Auto</option>' +
                  '<option value="fixed"' + (s.scaling_mode === 'fixed' ? ' selected' : '') + '>Fixed</option>' +
                  '<option value="fullwidth"' + (s.scaling_mode === 'fullwidth' ? ' selected' : '') + '>Full Width</option>' +
                  '<option value="fullscreen"' + (s.scaling_mode === 'fullscreen' ? ' selected' : '') + '>Full Screen</option>' +
                '</select></div>' +
                '</div>';

            var html = tabNav + '<div class="spe-stab-panels">' + panels + '</div>';

            Modals.open('Slider Settings', html, function () {
                /* Apply changes */
                $('#spe-modal-body [data-key]').each(function () {
                    var key = $(this).data('key');
                    var val;
                    if ($(this).is('[type=checkbox]')) val = $(this).prop('checked');
                    else if ($(this).is('[type=number]')) val = parseFloat($(this).val()) || 0;
                    else val = $(this).val();
                    state.slider.settings[key] = val;
                });
                Canvas.updateSize();
                Canvas.render();
                History.push();
                Modals.close();
            });

            /* Tab switching */
            $('#spe-modal-body').on('click', '.spe-stab', function () {
                var t = $(this).data('tab');
                $('#spe-modal-body .spe-stab').removeClass('active');
                $(this).addClass('active');
                $('#spe-modal-body .spe-stab-panel').removeClass('active');
                $('#spe-modal-body .spe-stab-panel[data-panel="' + t + '"]').addClass('active');
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * ANIMATION EDITOR MODAL
     * ─────────────────────────────────────────────────────────────────────*/
    var AnimationEditor = {
        open: function (lyr) {
            var anims = cfg.animations || {};
            var easings = cfg.easings  || {};
            var build = function (dir) {
                var a    = lyr['animation_' + dir] || {};
                var eff  = a.effect || 'fade';
                var eOpts = '', aOpts = '';
                $.each(anims,   function (k, v) { aOpts += '<option value="' + k + '"' + (eff === k ? ' selected' : '') + '>' + v + '</option>'; });
                $.each(easings, function (k, v) { eOpts += '<option value="' + k + '"' + (a.easing === k ? ' selected' : '') + '>' + v + '</option>'; });
                return '<div class="spe-anim-group" data-dir="' + dir + '">' +
                    '<div class="spe-sfield"><label>Effect</label><select data-akey="effect">' + aOpts + '</select></div>' +
                    '<div class="spe-settings-grid">' +
                      '<div class="spe-sfield"><label>Delay (ms)</label><input type="number" data-akey="delay" value="' + (a.delay || 0) + '" min="0" max="5000"></div>' +
                      '<div class="spe-sfield"><label>Duration (ms)</label><input type="number" data-akey="duration" value="' + (a.duration || 600) + '" min="50" max="5000"></div>' +
                    '</div>' +
                    '<div class="spe-sfield"><label>Easing</label><select data-akey="easing">' + eOpts + '</select></div>' +
                    '<div class="spe-sfield"><label>Distance (px)</label><input type="number" data-akey="distance" value="' + (a.distance || 30) + '" min="0" max="500"></div>' +
                    '</div>';
            };

            var html = '<div class="spe-stabs spe-stabs-anim">' +
                '<button class="spe-stab active" data-tab="in">' +
                  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7h8M7 4l3 3-3 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                  ' <span>Enter (IN)</span>' +
                '</button>' +
                '<button class="spe-stab" data-tab="out">' +
                  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M12 7H4M7 4L4 7l3 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                  ' <span>Exit (OUT)</span>' +
                '</button>' +
                '<button class="spe-stab" data-tab="presets">' +
                  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 7h12M7 1v12M4 4l6 6M10 4L4 10" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>' +
                  ' <span>Presets</span>' +
                '</button>' +
                '</div>' +
                '<div class="spe-stab-panels">' +
                  '<div class="spe-stab-panel active" data-panel="in">' + build('in') + '</div>' +
                  '<div class="spe-stab-panel" data-panel="out">' + build('out') + '</div>' +
                  '<div class="spe-stab-panel" data-panel="presets">' +
                    '<div class="spe-anim-presets-grid">' +
                      '<div class="spe-anim-preset" data-preset="smooth-fade"><span>Smooth Fade</span><small>Subtle entry</small></div>' +
                      '<div class="spe-anim-preset" data-preset="bounce-in"><span>Bounce In</span><small>Playful & energetic</small></div>' +
                      '<div class="spe-anim-preset" data-preset="slide-up"><span>Slide Up</span><small>Clean vertical rise</small></div>' +
                      '<div class="spe-anim-preset" data-preset="zoom-in"><span>Zoom In</span><small>Pop from center</small></div>' +
                      '<div class="spe-anim-preset" data-preset="elastic"><span>Elastic</span><small>Overshoot effect</small></div>' +
                      '<div class="spe-anim-preset" data-preset="flip"><span>3D Flip</span><small>Professional tilt</small></div>' +
                    '</div>' +
                  '</div>' +
                '</div>';

            Modals.open('Layer Animations — ' + (lyr.name || lyr.type), html, function () {
                var $activePanel = $('#spe-modal-body .spe-stab-panel.active');
                if ($activePanel.data('panel') === 'presets') {
                    /* If closed on presets, just close. User must click a preset to apply. */
                    Modals.close();
                    return;
                }
                $('#spe-modal-body .spe-anim-group').each(function () {
                    var dir = $(this).data('dir');
                    $(this).find('[data-akey]').each(function () {
                        var akey = $(this).data('akey');
                        var val  = $(this).is('[type=number]') ? (parseFloat($(this).val()) || 0) : $(this).val();
                        if (!lyr['animation_' + dir]) lyr['animation_' + dir] = {};
                        lyr['animation_' + dir][akey] = val;
                    });
                });
                History.push();
                Modals.close();
            });

            /* Tab switching */
            $('#spe-modal-body').on('click', '.spe-stab', function () {
                var t = $(this).data('tab');
                $('#spe-modal-body .spe-stab').removeClass('active');
                $(this).addClass('active');
                $('#spe-modal-body .spe-stab-panel').removeClass('active');
                $('#spe-modal-body .spe-stab-panel[data-panel="' + t + '"]').addClass('active');
            });

            /* Preset selection */
            $('#spe-modal-body').on('click', '.spe-anim-preset', function() {
                var p = $(this).data('preset');
                var config = {
                    'smooth-fade': { in: { effect: 'fade', delay: 0, duration: 800, easing: 'ease-out' }, out: { effect: 'fade', delay: 0, duration: 400, easing: 'ease-in' } },
                    'bounce-in':   { in: { effect: 'backOut', delay: 0, duration: 1000, easing: 'ease-out', distance: 100 }, out: { effect: 'fade', delay: 0, duration: 400, easing: 'ease-in' } },
                    'slide-up':    { in: { effect: 'fade', delay: 0, duration: 800, easing: 'ease-out', distance: 50 }, out: { effect: 'fade', delay: 0, duration: 400, easing: 'ease-in' } },
                    'zoom-in':     { in: { effect: 'fade', delay: 0, duration: 600, easing: 'circOut' }, out: { effect: 'fade', delay: 0, duration: 300, easing: 'ease-in' } },
                    'elastic':     { in: { effect: 'elasticOut', delay: 0, duration: 1500, easing: 'ease-out' }, out: { effect: 'fade', delay: 0, duration: 400 } },
                    'flip':        { in: { effect: 'flip', delay: 0, duration: 1000, easing: 'ease-out' }, out: { effect: 'fade', delay: 0, duration: 400 } },
                }[p];

                if (config) {
                    lyr.animation_in = config.in;
                    lyr.animation_out = config.out;
                    Toolbar.notify('Applied ' + $(this).find('span').text() + ' preset', 'success');
                    History.push();
                    Modals.close();
                }
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * TIMELINE MODAL
     * ─────────────────────────────────────────────────────────────────────*/
    var TimelineModal = {
        TOTAL: 8000, /* 8 seconds timeline */
        dragging: null,

        open: function () {
            var slide = Utils.activeSlide();
            if (!slide) { Toolbar.notify('No active slide', 'error'); return; }
            var html = TimelineModal.build(slide);
            Modals.open('Animation Timeline', html, function () { Modals.close(); }, false);
            TimelineModal.bindEvents();
        },

        build: function (slide) {
            var tl = TimelineModal.TOTAL;
            var layers = slide.layers || [];
            var rows = '';
            layers.forEach(function (lyr) {
                var ain  = lyr.animation_in  || {};
                var aout = lyr.animation_out || {};
                var inDelay = ain.delay || 0;
                var inDur   = ain.duration || 600;
                var outDelay= aout.delay || 0;
                var outDur  = aout.duration || 400;

                /* Convert to % of timeline */
                var inL  = (inDelay / tl) * 100;
                var inW  = (inDur   / tl) * 100;
                var outL = (outDelay / tl) * 100;
                var outW = (outDur   / tl) * 100;

                rows += '<div class="spe-tl-row">' +
                    '<div class="spe-tl-label">' + Utils.esc(lyr.name || lyr.type) + '</div>' +
                    '<div class="spe-tl-track" data-layer-id="' + lyr.id + '">' +
                      '<div class="spe-tl-bar spe-tl-in"  style="left:' + inL.toFixed(1)  + '%;width:' + inW.toFixed(1)  + '%" title="IN: delay ' + inDelay  + 'ms / dur ' + inDur  + 'ms" data-dir="in">' +
                        '<div class="spe-tl-handle handle-l"></div><div class="spe-tl-handle handle-r"></div>' +
                      '</div>' +
                      '<div class="spe-tl-bar spe-tl-out" style="left:' + outL.toFixed(1) + '%;width:' + outW.toFixed(1) + '%" title="OUT: delay ' + outDelay + 'ms / dur ' + outDur + 'ms" data-dir="out">' +
                        '<div class="spe-tl-handle handle-l"></div><div class="spe-tl-handle handle-r"></div>' +
                      '</div>' +
                    '</div>' +
                    '</div>';
            });

            var ruler = '';
            for (var ms = 0; ms <= tl; ms += 1000) {
                ruler += '<span style="left:' + ((ms / tl) * 100) + '%">' + (ms / 1000) + 's</span>';
            }

            return '<div class="spe-tl-ruler">' + ruler + '</div>' +
                   '<div class="spe-tl-rows">' + rows + '</div>' +
                   '<p class="spe-tl-hint">Drag bars to adjust delay, drag handles to adjust duration.</p>';
        },

        bindEvents: function () {
            var $body = $('#spe-modal-body');

            $body.on('mousedown', '.spe-tl-bar', function (e) {
                if ($(e.target).is('.spe-tl-handle')) return;
                var $bar = $(this);
                var $track = $bar.closest('.spe-tl-track');
                TimelineModal.dragging = {
                    type: 'move',
                    $bar: $bar,
                    $track: $track,
                    lyrId: $track.data('layer-id'),
                    dir: $bar.data('dir'),
                    startX: e.clientX,
                    origLeft: parseFloat($bar[0].style.left)
                };
                e.preventDefault();
            });

            $body.on('mousedown', '.spe-tl-handle', function (e) {
                var $handle = $(this);
                var $bar = $handle.closest('.spe-tl-bar');
                var $track = $bar.closest('.spe-tl-track');
                TimelineModal.dragging = {
                    type: 'resize',
                    side: $handle.hasClass('handle-l') ? 'l' : 'r',
                    $bar: $bar,
                    $track: $track,
                    lyrId: $track.data('layer-id'),
                    dir: $bar.data('dir'),
                    startX: e.clientX,
                    origLeft: parseFloat($bar[0].style.left),
                    origWidth: parseFloat($bar[0].style.width)
                };
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('mousemove.sptl', function (e) {
                if (!TimelineModal.dragging) return;
                var d = TimelineModal.dragging;
                var trackW = d.$track.width();
                var dxPerc = ((e.clientX - d.startX) / trackW) * 100;
                var lyr = Utils.layerById(d.lyrId);
                if (!lyr) return;
                var anim = lyr['animation_' + d.dir] || {};

                if (d.type === 'move') {
                    var newL = Utils.clamp(d.origLeft + dxPerc, 0, 100 - parseFloat(d.$bar[0].style.width));
                    d.$bar.css('left', newL + '%');
                    anim.delay = Math.round((newL / 100) * TimelineModal.TOTAL);
                } else {
                    if (d.side === 'l') {
                        var newL = Utils.clamp(d.origLeft + dxPerc, 0, d.origLeft + d.origWidth - 1);
                        var newW = d.origLeft + d.origWidth - newL;
                        d.$bar.css({ left: newL + '%', width: newW + '%' });
                        anim.delay = Math.round((newL / 100) * TimelineModal.TOTAL);
                        anim.duration = Math.round((newW / 100) * TimelineModal.TOTAL);
                    } else {
                        var newW = Utils.clamp(d.origWidth + dxPerc, 1, 100 - d.origLeft);
                        d.$bar.css('width', newW + '%');
                        anim.duration = Math.round((newW / 100) * TimelineModal.TOTAL);
                    }
                }
                lyr['animation_' + d.dir] = anim;
            });

            $(document).on('mouseup.sptl', function () {
                if (TimelineModal.dragging) {
                    History.push();
                    TimelineModal.dragging = null;
                }
            });
        }
    };

    /* ──────────────────────────────────────────────────────────────────────
     * TEMPLATES MODAL
     * ─────────────────────────────────────────────────────────────────────*/
    var TemplatesModal = {
        _flattenTemplates: function (data) {
            /* data may be {category: [templates]} or a flat array */
            if ($.isArray(data)) return data;
            var flat = [];
            $.each(data, function (cat, items) {
                if (!$.isArray(items)) return;
                items.forEach(function (t) {
                    t.category = t.category || cat;
                    flat.push(t);
                });
            });
            return flat;
        },

        _buildGrid: function (data) {
            var templates = data || [];
            var cats = {};
            templates.forEach(function (t) { if (!cats[t.category]) cats[t.category] = []; cats[t.category].push(t); });

            var html = '<div class="spe-template-cats">';
            html += '<button class="spe-tcat-btn active" data-cat="all">All</button>';
            $.each(cats, function (cat) { html += '<button class="spe-tcat-btn" data-cat="' + Utils.esc(cat) + '">' + Utils.esc(cat) + '</button>'; });
            html += '</div><div class="spe-template-grid">';
            templates.forEach(function (t) {
                var thumbUrl = t.thumbnail || t.thumb || '';
                html += '<div class="spe-template-card" data-cat="' + Utils.esc(t.category) + '" data-id="' + Utils.esc(t.id) + '">' +
                    (thumbUrl ? '<img src="' + Utils.esc(thumbUrl) + '" alt="' + Utils.esc(t.name) + '">' : '<div class="spe-tpl-no-thumb">' + Utils.esc(t.name.charAt(0)) + '</div>') +
                    '<div class="spe-tpl-name">' + Utils.esc(t.name) + '</div>' +
                    '<div class="spe-tpl-cat">' + Utils.esc(t.category) + '</div>' +
                    '</div>';
            });
            html += '</div>';
            return html;
        },

        _bindCategoryFilter: function () {
            $(document).on('click.tmplcat', '.spe-tcat-btn', function () {
                var cat = $(this).data('cat');
                $('.spe-tcat-btn').removeClass('active');
                $(this).addClass('active');
                var all = cat === 'all';
                $('.spe-template-card').toggle(all).filter('[data-cat="' + cat + '"]').show();
            });
        },

        /* Full template import — replaces all slides */
        open: function () {
            Modals.open('Starter Templates', '<div class="spe-loading">Loading templates…</div>', null, false);
            API.getTemplates(function (data) {
                $('#spe-modal-body').html(TemplatesModal._buildGrid(TemplatesModal._flattenTemplates(data)));
            });

            TemplatesModal._bindCategoryFilter();

            $(document).on('click.tmplimport', '.spe-template-card', function () {
                var id = $(this).data('id');
                if (!id) return;
                API.importTemplate(id, function (data) {
                    Modals.close();
                    if (data && data.slides) {
                        state.slides = data.slides;
                        state.activeSlideIdx = 0;
                        History.push();
                        SlideManager.render();
                        Canvas.render();
                        LayersPanel.render();
                    } else {
                        API.load(state.slider.id, function (d) { Editor.applyLoaded(d); });
                    }
                });
            });
        },

        /* Add a single slide from a template (appends, does not replace) */
        openForSlide: function () {
            Modals.open('Add Slide from Template', '<div class="spe-loading">Loading templates…</div>', null, false);
            API.getTemplates(function (data) {
                $('#spe-modal-body').html(TemplatesModal._buildGrid(TemplatesModal._flattenTemplates(data)));
            });

            TemplatesModal._bindCategoryFilter();

            $(document).on('click.tmplimport', '.spe-template-card', function () {
                var id = $(this).data('id');
                if (!id) return;
                $(this).css('opacity', '0.5');
                API.importTemplate(id, function () {
                    Modals.close();
                    /* Reload full slider from DB to pick up the new slide */
                    API.load(state.slider.id, function (d) {
                        Editor.applyLoaded(d);
                        /* Jump to the last slide (the newly added one) */
                        state.activeSlideIdx = state.slides.length - 1;
                        SlideManager.render();
                        Canvas.render();
                        LayersPanel.render();
                        PropertiesPanel.clear();
                        Toolbar.notify('Slide added from template', 'success');
                    });
                });
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * DYNAMIC CONTENT MODAL
     * ─────────────────────────────────────────────────────────────────────*/
    var DynamicModal = {
        open: function () {
            API.getDynamicSources(function (sources) {
                var ptOpts = '';
                var pts    = (sources && sources.post_types) || {};
                $.each(pts, function (k, v) { ptOpts += '<option value="' + Utils.esc(k) + '">' + Utils.esc(v) + '</option>'; });

                var html = '<div class="spe-modal-section"><h3>Source</h3>' +
                    '<label>Source Type <select id="spe-dyn-source"><option value="posts">WordPress Posts</option><option value="woocommerce">WooCommerce Products</option><option value="acf">ACF Repeater</option></select></label>' +
                    '<label>Post Type <select id="spe-dyn-pt">' + ptOpts + '</select></label>' +
                    '<label>Posts per page <input type="number" id="spe-dyn-count" value="5" min="1" max="50"></label>' +
                    '<label>Order By <select id="spe-dyn-orderby"><option value="date">Date</option><option value="title">Title</option><option value="menu_order">Menu Order</option><option value="rand">Random</option></select></label>' +
                    '</div>';

                Modals.open('Dynamic Content', html, function () {
                    var config = {
                        source:         $('#spe-dyn-source').val(),
                        post_type:      $('#spe-dyn-pt').val(),
                        posts_per_page: parseInt($('#spe-dyn-count').val()) || 5,
                        orderby:        $('#spe-dyn-orderby').val(),
                    };
                    API.importDynamic(config, function (data) {
                        Modals.close();
                        if (data && data.slides) {
                            data.slides.forEach(function (s) { state.slides.push(s); });
                            History.push();
                            SlideManager.render();
                            Canvas.render();
                            LayersPanel.render();
                        }
                    });
                });
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * PREVIEW MODAL
     * ─────────────────────────────────────────────────────────────────────*/
    var PreviewModal = {
        open: function () {
            var url = cfg.preview_url + '&slider_id=' + state.slider.id + '&preview=1';
            var html = '<div class="spe-preview-frame-wrap"><iframe src="' + url + '" class="spe-preview-frame" allow="autoplay"></iframe></div>';
            Modals.open('Preview', html, function () { Modals.close(); }, false);
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * GENERIC MODAL HELPER
     * ─────────────────────────────────────────────────────────────────────*/
    var Modals = {
        open: function (title, body, onApply, showApply) {
            $(document).off('.animtab .tmplcat .tmplimport');
            var $m = $('#spe-modal-overlay');
            $m.find('.spe-modal-title').text(title);
            $('#spe-modal-body').html(body);

            var $apply = $('#spe-modal-apply');
            if (showApply === false) {
                $apply.hide();
            } else {
                $apply.show().off('click.modal').on('click.modal', function () {
                    if (onApply) onApply();
                });
            }
            $m.addClass('visible');
        },

        close: function () {
            $('#spe-modal-overlay').removeClass('visible');
            $(document).off('.animtab .tmplcat .tmplimport');
        },

        init: function () {
            $('#spe-modal-close, #spe-modal-cancel').on('click', Modals.close);
            $('#spe-modal-overlay').on('click', function (e) {
                if ($(e.target).is('#spe-modal-overlay')) Modals.close();
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * STOCK LIBRARY (Unsplash)
     * ─────────────────────────────────────────────────────────────────────*/
    var StockLibrary = {
        API_URL: 'https://api.unsplash.com/search/photos',
        CLIENT_ID: '7681600b3e558837e2996d93b3f33013d115fa01ec01c6c06ed1d9e2eb4b2383', /* Demo ID */

        search: function (query, page, done) {
            $.get(StockLibrary.API_URL, {
                query: query || 'background',
                per_page: 24,
                page: page || 1,
                client_id: StockLibrary.CLIENT_ID
            }).done(done);
        },

        open: function (onSelected) {
            var html = '<div class="spe-stock-wrap">' +
                '<div class="spe-stock-search-bar">' +
                  '<input type="text" id="spe-stock-input" placeholder="Search Unsplash (e.g. nature, technology)…">' +
                  '<button id="spe-stock-search-btn" class="spe-btn primary">Search</button>' +
                '</div>' +
                '<div id="spe-stock-results" class="spe-stock-grid">' +
                   '<div class="spe-stock-loading">Type to search thousands of free images…</div>' +
                '</div>' +
                '</div>';

            Modals.open('Stock Image Library', html, null, false);

            $('#spe-stock-search-btn').on('click', function() {
                var q = $('#spe-stock-input').val();
                $('#spe-stock-results').html('<div class="spe-stock-loading">Searching Unsplash…</div>');
                StockLibrary.search(q, 1, function(res) {
                    var gridHtml = '';
                    (res.results || []).forEach(function(img) {
                        gridHtml += '<div class="spe-stock-item" data-url="' + img.urls.regular + '">' +
                            '<img src="' + img.urls.thumb + '" alt="' + (img.alt_description || '') + '">' +
                            '<div class="spe-stock-info">' +
                              '<div class="spe-stock-author">by ' + img.user.name + '</div>' +
                            '</div>' +
                            '</div>';
                    });
                    if (!res.results || !res.results.length) gridHtml = '<div class="spe-stock-empty">No results found for "' + Utils.esc(q) + '".</div>';
                    $('#spe-stock-results').html(gridHtml);
                });
            });

            $('#spe-modal-body').on('click', '.spe-stock-item', function() {
                var url = $(this).data('url');
                onSelected && onSelected(url);
                Modals.close();
            });
            
            /* Enter key search */
            $('#spe-modal-body').on('keypress', '#spe-stock-input', function(e) {
                if (e.which === 13) $('#spe-stock-search-btn').click();
            });
        }
    };

    /* ──────────────────────────────────────────────────────────────────────
     * SLIDE BACKGROUND panel (right-click on canvas bg / slide settings)
     * ─────────────────────────────────────────────────────────────────────*/
    var SlideBgPanel = {
        bindEvents: function () {
            /* Double-click on canvas background = open slide settings */
            Canvas.$inner.on('dblclick', function (e) {
                if (!$(e.target).closest('.spe-layer').length) {
                    SlideBgPanel.open();
                }
            });
        },

        open: function () {
            var slide = Utils.activeSlide();
            if (!slide) return;
            var s = slide.settings || {};

            var html = '<div class="spe-stabs spe-stabs-anim">' +
                '<button class="spe-stab active" data-tab="bg">' +
                  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><circle cx="5" cy="5" r="1.5" fill="currentColor"/><path d="M1 11l3-4 2.5 3L9 7l4 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
                  ' <span>Background</span>' +
                '</button>' +
                '<button class="spe-stab" data-tab="fx">' +
                  '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1v2M7 11v2M1 7h2M11 7h2M2.8 2.8l1.4 1.4M9.8 9.8l1.4 1.4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>' +
                  ' <span>Effects</span>' +
                '</button>' +
                                '<button class="spe-stab" data-tab="meta">' +
                                    '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 2.5h8A1.5 1.5 0 0112.5 4v6A1.5 1.5 0 0111 11.5H3A1.5 1.5 0 011.5 10V4A1.5 1.5 0 013 2.5z" stroke="currentColor" stroke-width="1.2"/><path d="M4 5.5h6M4 8h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>' +
                                    ' <span>Content</span>' +
                                '</button>' +
                '</div>' +
                '<div class="spe-stab-panels">' +
                  '<div class="spe-stab-panel active" data-panel="bg">' +
                    '<div class="spe-sfield"><label>Background Color</label><input type="color" data-skey="bg_color" value="' + Utils.esc(s.bg_color || '#1a1a2e') + '"></div>' +
                                        '<div class="spe-sfield"><label>Background Image</label><div class="spe-media-row"><input type="text" data-skey="bg_image" id="spe-skey-bg_image" value="' + Utils.esc(s.bg_image || '') + '" placeholder="Image URL…"><button class="spe-btn-media spe-media-slide-img" data-target="#spe-skey-bg_image" title="WP Media Library">' +
                      '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="1" y="1" width="10" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2"/><circle cx="4" cy="4.5" r="1" fill="currentColor"/><path d="M1 9l2.5-3 2 2 1.5-1.5L11 9" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>' +
                    '</button>' +
                    '<button class="spe-btn-media spe-stock-slide-img" title="Search Unsplash">' +
                      '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M8.5 11V3h7v8h-7zM24 11h-8v8H8v-8H0V3h24v8z"/></svg>' +
                    '</button></div></div>' +
                    '<div class="spe-settings-grid">' +
                      '<div class="spe-sfield"><label>Size</label><select data-skey="bg_size"><option value="cover"' + (s.bg_size === 'cover' ? ' selected' : '') + '>Cover</option><option value="contain"' + (s.bg_size === 'contain' ? ' selected' : '') + '>Contain</option><option value="auto"' + (s.bg_size === 'auto' ? ' selected' : '') + '>Auto</option></select></div>' +
                      '<div class="spe-sfield"><label>Position</label><input type="text" data-skey="bg_position" value="' + Utils.esc(s.bg_position || 'center center') + '"></div>' +
                    '</div>' +
                  '</div>' +
                  '<div class="spe-stab-panel" data-panel="fx">' +
                    '<div class="spe-sfield spe-sfield-toggle"><label>Ken Burns Effect</label><label class="spe-toggle"><input type="checkbox" data-skey="ken_burns" ' + (s.ken_burns ? 'checked' : '') + '><span class="spe-toggle-track"></span></label></div>' +
                    '<div class="spe-sfield"><label>Ken Burns Zoom (%)</label><input type="number" data-skey="ken_burns_zoom" value="' + (s.ken_burns_zoom || 120) + '" min="100" max="200"></div>' +
                                        '<div class="spe-sfield"><label>Ken Burns Direction</label><select data-skey="ken_burns_direction"><option value="in"' + (s.ken_burns_direction === 'in' ? ' selected' : '') + '>Zoom In</option><option value="out"' + (s.ken_burns_direction === 'out' ? ' selected' : '') + '>Zoom Out</option></select></div>' +
                    '<div class="spe-sfield"><label>Overlay Color</label><input type="color" data-skey="bg_overlay" value="' + Utils.esc(s.bg_overlay || '#000000') + '"></div>' +
                    '<div class="spe-sfield"><label>Overlay Opacity</label><input type="number" data-skey="bg_overlay_opacity" value="' + (s.bg_overlay_opacity || 0) + '" min="0" max="1" step="0.05"></div>' +
                                    '</div>' +
                                    '<div class="spe-stab-panel" data-panel="meta">' +
                                        '<div class="spe-sfield"><label>Slide Title</label><input type="text" data-slide-key="title" value="' + Utils.esc(slide.title || 'Slide') + '"></div>' +
                                        '<div class="spe-sfield"><label>Slide Link</label><input type="text" data-skey="link" value="' + Utils.esc(s.link || '') + '" placeholder="https://example.com"></div>' +
                                        '<div class="spe-sfield"><label>Link Target</label><select data-skey="link_target"><option value="_self"' + (s.link_target === '_blank' ? '' : ' selected') + '>Same tab</option><option value="_blank"' + (s.link_target === '_blank' ? ' selected' : '') + '>New tab</option></select></div>' +
                                        '<div class="spe-sfield"><label>Thumbnail</label><div class="spe-media-row"><input type="text" data-skey="thumbnail" id="spe-skey-thumbnail" value="' + Utils.esc(s.thumbnail || '') + '" placeholder="Thumbnail URL…"><button class="spe-btn-media spe-media-slide-img" data-target="#spe-skey-thumbnail" title="WP Media Library">' +
                                            '<svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="1" y="1" width="10" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2"/><circle cx="4" cy="4.5" r="1" fill="currentColor"/><path d="M1 9l2.5-3 2 2 1.5-1.5L11 9" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>' +
                                        '</button></div></div>' +
                  '</div>' +
                '</div>';

            Modals.open('Slide Settings', html, function () {
                var slide2 = Utils.activeSlide();
                if (!slide2) return;
                $('#spe-modal-body [data-skey]').each(function () {
                    var k = $(this).data('skey');
                    var v = $(this).is('[type=checkbox]') ? $(this).prop('checked') : ($(this).is('[type=number]') ? parseFloat($(this).val()) : $(this).val());
                    slide2.settings[k] = v;
                });
                $('#spe-modal-body [data-slide-key]').each(function () {
                    slide2[$(this).data('slide-key')] = $(this).val();
                });
                Canvas.render();
                SlideManager.render();
                History.push();
                Modals.close();
            });

            /* Tab switching */
            $('#spe-modal-body').on('click', '.spe-stab', function () {
                var t = $(this).data('tab');
                $('#spe-modal-body .spe-stab').removeClass('active');
                $(this).addClass('active');
                $('#spe-modal-body .spe-stab-panel').removeClass('active');
                $('#spe-modal-body .spe-stab-panel[data-panel="' + t + '"]').addClass('active');
            });

            /* Media chooser for slide assets */
            $(document).off('click.slide-media').on('click.slide-media', '.spe-media-slide-img', function () {
                var target = $(this).data('target');
                var frame = wp.media({ title: 'Choose Media', button: { text: 'Use Media' }, multiple: false });
                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    $(target).val(att.url);
                });
                frame.open();
            });

            /* Stock image button for slide bg */
            $(document).on('click.stock-bg', '.spe-stock-slide-img', function () {
                StockLibrary.open(function(url) {
                    $('[data-skey="bg_image"]').val(url);
                });
            });
        },
    };

    /* ──────────────────────────────────────────────────────────────────────
     * EDITOR — main controller
     * ─────────────────────────────────────────────────────────────────────*/
    var Editor = {
        init: function () {
            Canvas.init();
            LayersPanel.init();
            PropertiesPanel.init();
            SlideManager.init();
            Toolbar.init();
            Modals.init();
            ContextMenu.init();
            LayersPanel.bindEvents();
            SlideBgPanel.bindEvents();
            /* Add click handlers for the stock buttons in slide settings */
            $(document).on('click', '.spe-stock-slide-img', function() {
                var $target = $(this).siblings('input[data-skey="bg_image"]');
                StockLibrary.open(function(url) {
                    $target.val(url).trigger('change');
                    var slide = Utils.activeSlide();
                    if (slide) {
                        slide.settings.bg_image = url;
                        Canvas.render();
                    }
                });
            });
            Canvas.updateSize();
            $(window).on('resize.speditor', function () { Canvas.updateSize(); Canvas.render(); });

            /* Load slider data */
            var sliderId = parseInt(cfg.slider_id) || 0;
            if (sliderId) {
                API.load(sliderId, function (data) { Editor.applyLoaded(data); });
            } else {
                /* Brand new slider — start with one blank slide */
                state.slider = { id: 0, name: cfg.i18n.untitled || 'Untitled Slider', settings: {} };
                $('#spe-slider-name').text(state.slider.name);
                Editor.addSlide();
                History.push();
            }

            /* Warn before leaving with unsaved changes */
            $(window).on('beforeunload', function () {
                if (state.isDirty) return cfg.i18n.unsaved_changes;
            });
        },

        applyLoaded: function (data) {
            if (!data) return;
            state.slider.id       = data.slider_id  || state.slider.id;
            state.slider.name     = data.name        || 'Untitled Slider';
            state.slider.settings = data.settings    || {};
            state.slides          = data.slides      || [];
            state.activeSlideIdx  = 0;
            state.selectedLayerId = null;
            state.isDirty         = false;

            /* Ensure each layer/slide has a client-side id */
            state.slides.forEach(function (sl) {
                if (!sl.id) sl.id = Utils.uid();
                (sl.layers || []).forEach(function (ly) { if (!ly.id) ly.id = Utils.uid(); });
            });

            $('#spe-slider-name').text(state.slider.name);
            Canvas.updateSize();
            Canvas.render();
            LayersPanel.render();
            SlideManager.render();
            PropertiesPanel.clear();
            History.push();
        },

        addSlide: function () {
            var slide = Defaults.slide();
            slide.title = 'Slide ' + (state.slides.length + 1);
            state.slides.push(slide);
            state.activeSlideIdx = state.slides.length - 1;
            state.selectedLayerId = null;
            History.push();
            SlideManager.render();
            Canvas.render();
            LayersPanel.render();
            PropertiesPanel.clear();
        },

        duplicateSlide: function (idx) {
            var src  = state.slides[idx];
            if (!src) return;
            var copy = Utils.deepClone(src);
            copy.id  = null;
            copy.title = (copy.title || 'Slide') + ' (copy)';
            /* Reset layer ids */
            (copy.layers || []).forEach(function (l) { l.id = Utils.uid(); });
            state.slides.splice(idx + 1, 0, copy);
            state.activeSlideIdx = idx + 1;
            History.push();
            SlideManager.render();
            Canvas.render();
            LayersPanel.render();
        },

        deleteSlide: function (idx) {
            if (state.slides.length <= 1) { Toolbar.notify('Cannot delete the last slide', 'error'); return; }
            if (!window.confirm('Delete this slide and all its layers?')) return;
            state.slides.splice(idx, 1);
            state.activeSlideIdx = Math.min(state.activeSlideIdx, state.slides.length - 1);
            state.selectedLayerId = null;
            History.push();
            SlideManager.render();
            Canvas.render();
            LayersPanel.render();
            PropertiesPanel.clear();
        },

        addLayer: function (type) {
            var slide = Utils.activeSlide();
            if (!slide) { Toolbar.notify('Add a slide first', 'error'); return; }
            var lyr = Defaults.layer(type);
            /* Auto-name */
            var count = (slide.layers || []).filter(function (l) { return l.type === type; }).length;
            lyr.name  = type.charAt(0).toUpperCase() + type.slice(1) + ' ' + (count + 1);
            if (!slide.layers) slide.layers = [];
            slide.layers.push(lyr);
            state.selectedLayerId = lyr.id;
            History.push();
            Canvas.render();
            LayersPanel.render();
            Canvas.selectLayer(lyr.id);
        },

        duplicateLayer: function (id) {
            var slide = Utils.activeSlide();
            if (!slide) return;
            var idx  = Utils.layerIndexById(id);
            if (idx === -1) return;
            var copy = Utils.deepClone(slide.layers[idx]);
            copy.id  = Utils.uid();
            copy.x  += 20; copy.y += 20;
            copy.name = (copy.name || copy.type) + ' (copy)';
            slide.layers.splice(idx + 1, 0, copy);
            state.selectedLayerId = copy.id;
            History.push();
            Canvas.render();
            LayersPanel.render();
            Canvas.selectLayer(copy.id);
        },

        deleteLayer: function (id) {
            var slide = Utils.activeSlide();
            if (!slide) return;
            var idx = Utils.layerIndexById(id);
            if (idx === -1) return;
            slide.layers.splice(idx, 1);
            if (String(state.selectedLayerId) === String(id)) {
                state.selectedLayerId = null;
                PropertiesPanel.clear();
            }
            History.push();
            Canvas.render();
            LayersPanel.render();
        },
    };

    /* ── Boot ─────────────────────────────────────────────────────────────*/
    $(document).ready(function () { Editor.init(); });

})(jQuery);
