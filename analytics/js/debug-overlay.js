/**
 * Syntekpro Animations - Debug Overlay
 * Opt-in runtime inspector for animation engine and trigger states.
 */
(function() {
    'use strict';

    const CSS_PRESETS = {
        fadeIn: true,
        fadeInUp: true,
        fadeInDown: true,
        fadeInLeft: true,
        fadeInRight: true,
        slideLeft: true,
        slideRight: true,
        slideUp: true,
        slideDown: true,
        zoomIn: true,
        zoomInUp: true,
        zoomInDown: true,
        zoomInLeft: true,
        zoomInRight: true,
        scaleUp: true,
        scaleDown: true,
        scaleX: true,
        scaleY: true,
        rotateIn: true,
        pulse: true,
        revealLeft: true,
        revealRight: true,
        revealUp: true,
        revealDown: true
    };

    const STATE = {
        overlay: null,
        toggle: null,
        list: null,
        badges: null,
        summary: {},
        markersLayer: null,
        visible: false,
        markersOnly: false,
        data: {
            items: [],
            globalEngine: 'auto',
            hasGsap: false
        }
    };

    const DEBUG_QUERY_KEY = 'syntekpro_debug';
    const PERSIST_ROLE = !!(window.syntekproAnim && window.syntekproAnim.debugOverlayPersistRole);
    const CURRENT_ROLE = (window.syntekproAnim && window.syntekproAnim.debugOverlayRole) || 'guest';

    if (!shouldEnableOverlay()) {
        return;
    }

    document.addEventListener('DOMContentLoaded', function() {
        buildOverlay();
        attachListeners();
        refreshFromDom();
    });

    function shouldEnableOverlay() {
        const params = new URLSearchParams(window.location.search);
        const queryOptIn = params.has(DEBUG_QUERY_KEY);
        const storedOptIn = localStorage.getItem(storageKey()) === '1';
        const developerMode = !!(window.syntekproAnim && window.syntekproAnim.developerMode);
        const localizedOptIn = !!(window.syntekproAnim && window.syntekproAnim.debugOverlay);
        return developerMode || queryOptIn || storedOptIn || localizedOptIn;
    }

    function storageKey() {
        if (PERSIST_ROLE && CURRENT_ROLE) {
            return DEBUG_QUERY_KEY + '_' + CURRENT_ROLE;
        }
        return DEBUG_QUERY_KEY;
    }

    function attachListeners() {
        window.addEventListener('syntekpro:animations-ready', function(event) {
            if (!event || !event.detail) {
                return;
            }
            renderOverlay(event.detail);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key && event.key.toLowerCase() === 'd' && event.shiftKey) {
                event.preventDefault();
                toggleOverlay();
            }
        });

        window.addEventListener('resize', scheduleMarkersUpdate);
        window.addEventListener('scroll', scheduleMarkersUpdate, { passive: true });
    }

    function buildOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'syntekpro-debug-overlay';
        overlay.setAttribute('role', 'region');
        overlay.setAttribute('aria-label', 'Syntekpro Animations debug overlay');

        overlay.innerHTML = '' +
            '<div class="syntekpro-debug-header">' +
                '<div class="syntekpro-debug-title">' +
                    '<span class="syntekpro-debug-dot"></span>' +
                    '<span>Syntekpro Debug</span>' +
                '</div>' +
                '<button class="syntekpro-debug-close" type="button" aria-label="Close debug overlay">&#10005;</button>' +
            '</div>' +
            '<div class="syntekpro-debug-section">' +
                '<div class="syntekpro-debug-section-title">Runtime</div>' +
                '<div class="syntekpro-debug-kv">' +
                    '<div class="syntekpro-debug-kv-label">Global Engine</div>' +
                    '<div class="syntekpro-debug-kv-value" data-debug-global>auto</div>' +
                    '<div class="syntekpro-debug-kv-label">Animations</div>' +
                    '<div class="syntekpro-debug-kv-value" data-debug-total>0</div>' +
                    '<div class="syntekpro-debug-kv-label">CSS</div>' +
                    '<div class="syntekpro-debug-kv-value" data-debug-css>0</div>' +
                    '<div class="syntekpro-debug-kv-label">GSAP</div>' +
                    '<div class="syntekpro-debug-kv-value" data-debug-gsap>0</div>' +
                '</div>' +
            '</div>' +
            '<div class="syntekpro-debug-section">' +
                '<div class="syntekpro-debug-section-title">State</div>' +
                '<div class="syntekpro-debug-badge-row" data-debug-badges></div>' +
            '</div>' +
            '<div class="syntekpro-debug-section">' +
                '<div class="syntekpro-debug-section-title">Animations</div>' +
                '<div class="syntekpro-debug-list" data-debug-list></div>' +
            '</div>' +
            '<div class="syntekpro-debug-footer">' +
                '<span>Shift+D toggles overlay</span>' +
                '<button class="syntekpro-debug-toggle" type="button" data-debug-hide>Hide</button>' +
            '</div>';

        document.body.appendChild(overlay);
        STATE.overlay = overlay;
        STATE.list = overlay.querySelector('[data-debug-list]');
        STATE.badges = overlay.querySelector('[data-debug-badges]');
        STATE.summary = {
            global: overlay.querySelector('[data-debug-global]'),
            total: overlay.querySelector('[data-debug-total]'),
            css: overlay.querySelector('[data-debug-css]'),
            gsap: overlay.querySelector('[data-debug-gsap]')
        };

        const closeBtn = overlay.querySelector('.syntekpro-debug-close');
        const hideBtn = overlay.querySelector('[data-debug-hide]');
        closeBtn.addEventListener('click', hideOverlay);
        hideBtn.addEventListener('click', hideOverlay);

        const markersOnlyBtn = document.createElement('button');
        markersOnlyBtn.className = 'syntekpro-debug-toggle';
        markersOnlyBtn.type = 'button';
        markersOnlyBtn.textContent = 'Markers only';
        markersOnlyBtn.setAttribute('aria-pressed', 'false');
        markersOnlyBtn.addEventListener('click', function() {
            STATE.markersOnly = !STATE.markersOnly;
            updateMarkersOnlyState();
            markersOnlyBtn.setAttribute('aria-pressed', STATE.markersOnly ? 'true' : 'false');
            markersOnlyBtn.textContent = STATE.markersOnly ? 'Markers on' : 'Markers only';
        });

        const toggle = document.createElement('button');
        toggle.className = 'syntekpro-debug-toggle';
        toggle.type = 'button';
        toggle.textContent = 'Show debug overlay';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.addEventListener('click', function() {
            toggleOverlay();
        });

        document.body.appendChild(toggle);
        STATE.toggle = toggle;
        showOverlay();

        // Footer marker-only button placement
        const footer = overlay.querySelector('.syntekpro-debug-footer');
        if (footer) {
            footer.insertBefore(markersOnlyBtn, footer.firstChild);
        }
    }

    function refreshFromDom() {
        renderOverlay(collectFromDom());
    }

    function collectFromDom() {
        const nodes = document.querySelectorAll('.sp-animate');
        const globalEngine = (window.syntekproAnim && window.syntekproAnim.engine) ? window.syntekproAnim.engine : 'auto';
        const hasGsap = typeof gsap !== 'undefined';
        const items = [];

        nodes.forEach(function(node, index) {
            const animationType = node.getAttribute('data-animation') || 'fadeIn';
            const trigger = node.getAttribute('data-trigger') || 'scroll';
            const engineRequested = node.getAttribute('data-engine') || 'auto';
            const cssCapable = !!CSS_PRESETS[animationType];
            const resolvedEngine = node.dataset.spEngineResolved || resolveEngine(engineRequested, globalEngine, cssCapable, hasGsap);
            const duration = parseFloat(node.getAttribute('data-duration')) || 1;
            const delay = parseFloat(node.getAttribute('data-delay')) || 0;
            const markers = node.getAttribute('data-markers') === 'true' || node.dataset.spMarkers === 'true';
            const onceOnly = node.getAttribute('data-once') !== 'false' && node.dataset.spOnce !== 'false';
            const name = node.id ? '#' + node.id : (node.className ? '.' + node.className.split(' ').join('.') : node.tagName.toLowerCase() + '-' + (index + 1));

            items.push({
                name: name,
                animation: animationType,
                trigger: trigger,
                engineRequested: engineRequested,
                engineResolved: resolvedEngine,
                cssCapable: cssCapable,
                duration: duration,
                delay: delay,
                markers: markers,
                onceOnly: onceOnly
            });
        });

        return {
            items: items,
            globalEngine: globalEngine,
            hasGsap: hasGsap
        };
    }

    function resolveEngine(engineRequested, globalEngine, cssCapable, hasGsap) {
        const effective = engineRequested !== 'auto' ? engineRequested : globalEngine;
        if (effective === 'css' || (effective === 'auto' && cssCapable)) {
            return 'css';
        }
        if (hasGsap) {
            return 'gsap';
        }
        return 'none';
    }

    function renderOverlay(payload) {
        if (!payload) {
            return;
        }

        STATE.data = payload;
        const items = Array.isArray(payload.items) ? payload.items : [];
        const cssCount = items.filter(function(item) { return item.engineResolved === 'css'; }).length;
        const gsapCount = items.filter(function(item) { return item.engineResolved === 'gsap'; }).length;

        setText(STATE.summary.global, payload.globalEngine || 'auto');
        setText(STATE.summary.total, items.length);
        setText(STATE.summary.css, cssCount);
        setText(STATE.summary.gsap, gsapCount);

        renderBadges(payload);
        renderList(items);
        updateMarkersFromDom();
    }

    function renderBadges(payload) {
        if (!STATE.badges) return;
        STATE.badges.innerHTML = '';

        const badges = [];
        const developerMode = !!(window.syntekproAnim && window.syntekproAnim.developerMode);
        const queryOptIn = new URLSearchParams(window.location.search).has(DEBUG_QUERY_KEY);

        if (developerMode) {
            badges.push({ label: 'Developer mode', variant: 'is-success' });
        }

        if (payload.hasGsap) {
            badges.push({ label: 'GSAP loaded', variant: '' });
        } else {
            badges.push({ label: 'GSAP missing', variant: 'is-danger' });
        }

        if (queryOptIn) {
            badges.push({ label: 'Query opt-in', variant: '' });
        }

        badges.push({ label: 'Shortcut Shift+D', variant: '' });

        badges.forEach(function(badge) {
            const el = document.createElement('span');
            el.className = 'syntekpro-debug-badge' + (badge.variant ? ' ' + badge.variant : '');
            el.textContent = badge.label;
            STATE.badges.appendChild(el);
        });
    }

    function renderList(items) {
        if (!STATE.list) return;
        STATE.list.innerHTML = '';

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'syntekpro-debug-item';
            empty.textContent = 'No animations detected on this page.';
            STATE.list.appendChild(empty);
            return;
        }

        items.forEach(function(item) {
            const row = document.createElement('div');
            row.className = 'syntekpro-debug-item';

            const textWrap = document.createElement('div');
            const name = document.createElement('div');
            name.className = 'syntekpro-debug-item-name';
            name.textContent = item.name;

            const meta = document.createElement('div');
            meta.className = 'syntekpro-debug-item-meta';
            const metaParts = [item.animation, item.trigger];
            if (typeof item.duration === 'number') {
                metaParts.push(item.duration + 's');
            }
            if (item.delay) {
                metaParts.push('delay ' + item.delay + 's');
            }
            if (item.onceOnly === false) {
                metaParts.push('loopable');
            }
            if (item.markers) {
                metaParts.push('markers');
            }
            meta.textContent = metaParts.join(' • ');

            textWrap.appendChild(name);
            textWrap.appendChild(meta);

            const pills = document.createElement('div');
            const enginePill = document.createElement('span');
            enginePill.className = 'syntekpro-debug-pill ' + (item.engineResolved === 'css' ? 'is-engine-css' : 'is-engine-gsap');
            enginePill.textContent = item.engineResolved === 'css' ? 'CSS engine' : 'GSAP engine';

            const triggerPill = document.createElement('span');
            triggerPill.className = 'syntekpro-debug-pill is-trigger';
            triggerPill.textContent = item.trigger;

            pills.appendChild(enginePill);
            pills.appendChild(triggerPill);

            row.appendChild(textWrap);
            row.appendChild(pills);

            STATE.list.appendChild(row);
        });
    }

    function setText(node, value) {
        if (!node) return;
        node.textContent = value;
    }

    function showOverlay() {
        if (!STATE.overlay) return;
        STATE.overlay.classList.add('is-active');
        STATE.visible = true;
        updateToggleLabel();
        document.body.classList.add('syntekpro-debug-overlay-active');
        updateMarkersFromDom();
        try {
            localStorage.setItem(storageKey(), '1');
        } catch (storageError) {
            console.warn('Unable to persist debug overlay preference', storageError);
        }
    }

    function hideOverlay() {
        if (!STATE.overlay) return;
        STATE.overlay.classList.remove('is-active');
        STATE.visible = false;
        updateToggleLabel();
        document.body.classList.remove('syntekpro-debug-overlay-active');
        if (!STATE.markersOnly) {
            document.body.classList.remove('syntekpro-debug-markers-only');
            clearMarkers();
        } else {
            document.body.classList.add('syntekpro-debug-markers-only');
            updateMarkersFromDom();
        }
    }

    function toggleOverlay() {
        if (STATE.visible) {
            hideOverlay();
        } else {
            showOverlay();
        }
    }

    function updateToggleLabel() {
        if (!STATE.toggle) return;
        STATE.toggle.textContent = STATE.visible ? 'Hide debug overlay' : 'Show debug overlay';
        STATE.toggle.setAttribute('aria-expanded', STATE.visible ? 'true' : 'false');
    }

    function updateMarkersOnlyState() {
        if (STATE.markersOnly) {
            document.body.classList.add('syntekpro-debug-markers-only');
            updateMarkersFromDom();
        } else {
            document.body.classList.remove('syntekpro-debug-markers-only');
            if (!STATE.visible) {
                clearMarkers();
            }
        }
    }

    function ensureMarkerLayer() {
        if (STATE.markersLayer && document.body.contains(STATE.markersLayer)) {
            return STATE.markersLayer;
        }
        const layer = document.createElement('div');
        layer.className = 'syntekpro-debug-marker-layer';
        document.body.appendChild(layer);
        STATE.markersLayer = layer;
        return layer;
    }

    function updateMarkersFromDom() {
        if (!STATE.visible && !STATE.markersOnly) return;
        const layer = ensureMarkerLayer();
        layer.innerHTML = '';

        const nodes = document.querySelectorAll('.sp-animate');
        nodes.forEach(function(node) {
            const rect = node.getBoundingClientRect();
            const marker = document.createElement('div');
            marker.className = 'syntekpro-debug-marker';
            marker.style.top = Math.max(0, rect.top + window.scrollY) + 'px';
            marker.style.left = Math.max(0, rect.left + window.scrollX) + 'px';
            marker.style.width = rect.width + 'px';
            marker.style.height = rect.height + 'px';

            const label = document.createElement('div');
            label.className = 'syntekpro-debug-marker-label';
            const anim = node.getAttribute('data-animation') || 'fadeIn';
            const trig = node.getAttribute('data-trigger') || 'scroll';
            const engine = node.dataset.spEngineResolved || node.getAttribute('data-engine') || 'auto';
            label.textContent = anim + ' • ' + trig + ' • ' + engine;
            marker.appendChild(label);

            layer.appendChild(marker);
        });
    }

    let markersRaf = null;
    function scheduleMarkersUpdate() {
        if (!STATE.visible && !STATE.markersOnly) return;
        if (markersRaf) return;
        markersRaf = window.requestAnimationFrame(function() {
            markersRaf = null;
            updateMarkersFromDom();
        });
    }

    function clearMarkers() {
        if (STATE.markersLayer) {
            STATE.markersLayer.innerHTML = '';
        }
    }

    // Expose minimal API for console use
    window.syntekproDebugOverlay = {
        show: showOverlay,
        hide: hideOverlay,
        toggle: toggleOverlay,
        refresh: refreshFromDom
    };
})();
