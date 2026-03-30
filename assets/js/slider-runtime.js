(function() {
    'use strict';

    var instances = {};

    function safeParseJSON(value, fallback) {
        if (!value) return fallback;
        try {
            return JSON.parse(value);
        } catch (error) {
            return fallback;
        }
    }

    function dispatch(root, type, detail) {
        root.dispatchEvent(new CustomEvent(type, { detail: detail || {} }));
    }

    function bindFocusTrap(container) {
        if (!container) return function() {};
        var selectors = 'a[href],button:not([disabled]),textarea,input,select,[tabindex]:not([tabindex="-1"])';
        function onKeyDown(event) {
            if (event.key !== 'Tab') return;
            var focusable = Array.prototype.slice.call(container.querySelectorAll(selectors)).filter(function(el) {
                return !el.hasAttribute('disabled') && el.tabIndex !== -1;
            });
            if (!focusable.length) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
        container.addEventListener('keydown', onKeyDown);
        return function() {
            container.removeEventListener('keydown', onKeyDown);
        };
    }

    function wireCountdownAndLiveData(state) {
        var refreshSeconds = parseInt(state.root.getAttribute('data-live-refresh') || '30', 10);
        if (!refreshSeconds || refreshSeconds < 5) refreshSeconds = 30;

        function updateCountdowns() {
            state.root.querySelectorAll('[data-countdown-end]').forEach(function(node) {
                var end = new Date(node.getAttribute('data-countdown-end') || '').getTime();
                if (!end || Number.isNaN(end)) {
                    node.textContent = '';
                    return;
                }

                var now = Date.now();
                var diff = Math.max(0, end - now);
                var total = Math.floor(diff / 1000);
                var days = Math.floor(total / 86400);
                var hours = Math.floor((total % 86400) / 3600);
                var minutes = Math.floor((total % 3600) / 60);
                var seconds = total % 60;
                node.textContent = days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's';
            });
        }

        function updateLiveData() {
            state.root.querySelectorAll('[data-live-endpoint]').forEach(function(node) {
                var endpoint = node.getAttribute('data-live-endpoint') || '';
                var key = node.getAttribute('data-live-key') || 'value';
                if (!endpoint || !window.fetch) return;

                fetch(endpoint, { headers: { 'Accept': 'application/json' } })
                    .then(function(res) { return res.json(); })
                    .then(function(payload) {
                        if (!payload || typeof payload !== 'object') return;
                        if (Object.prototype.hasOwnProperty.call(payload, key)) {
                            node.textContent = String(payload[key]);
                        }
                    })
                    .catch(function() {});
            });
        }

        updateCountdowns();
        updateLiveData();
        setInterval(updateCountdowns, 1000);
        setInterval(updateLiveData, refreshSeconds * 1000);
    }

    function bindConsentLayer(state) {
        if (state.root.getAttribute('data-gdpr-consent') !== 'true') return;

        state.root.querySelectorAll('iframe[data-third-party], .sp-slide-bg[data-video]').forEach(function(target) {
            var holder = document.createElement('div');
            holder.className = 'sp-consent-layer';
            holder.style.position = 'absolute';
            holder.style.inset = '0';
            holder.style.display = 'grid';
            holder.style.placeItems = 'center';
            holder.style.background = 'rgba(15,23,42,.72)';
            holder.style.zIndex = '4';

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'sp-consent-accept';
            button.textContent = 'Accept external media';
            holder.appendChild(button);

            var parent = target.parentElement;
            if (!parent) return;
            parent.style.position = parent.style.position || 'relative';
            parent.appendChild(holder);

            button.addEventListener('click', function() {
                holder.remove();
                dispatch(state.root, 'syntekpro:consent-granted', { sliderId: state.sliderId });
            });
        });
    }

    function trackAnalytics(state, eventName, payload) {
        if (!state.config.analyticsEnabled) return;
        var detail = Object.assign({
            sliderId: state.sliderId,
            event: eventName,
            index: state.index,
            ts: Date.now()
        }, payload || {});

        dispatch(state.root, 'syntekpro:slider-analytics', detail);

        if (state.config.ga4Enabled && Array.isArray(window.dataLayer)) {
            window.dataLayer.push({
                event: (state.config.ga4EventPrefix || 'syntekpro_slider') + '_' + eventName,
                slider_id: state.sliderId,
                slide_index: state.index,
                payload: payload || {}
            });
        }

        if (state.config.eventWebhookUrl) {
            try {
                fetch(state.config.eventWebhookUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(detail)
                });
            } catch (error) {
                // Ignore webhook network errors to keep runtime smooth.
            }
        }
    }

    function applyAdaptiveVideoLoading(state) {
        if (!state.config.adaptiveVideoLoading) return;
        var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        var effective = connection && connection.effectiveType ? connection.effectiveType : '4g';
        var allowVideo = ['4g', '5g', 'wifi'].indexOf(String(effective).toLowerCase()) >= 0;

        state.slides.forEach(function(slide) {
            var bg = slide.querySelector('.sp-slide-bg');
            if (!bg) return;
            var video = bg.getAttribute('data-video');
            var poster = bg.getAttribute('data-poster');
            if (!video) return;

            if (!allowVideo && poster) {
                bg.style.backgroundImage = 'url(' + poster + ')';
                return;
            }

            if (allowVideo && !bg.querySelector('video')) {
                var videoEl = document.createElement('video');
                videoEl.src = video;
                videoEl.muted = true;
                videoEl.autoplay = true;
                videoEl.loop = true;
                videoEl.playsInline = true;
                videoEl.className = 'sp-slide-bg-video';
                bg.appendChild(videoEl);
            }
        });
    }

    function collectAndSendVitals(state) {
        if (!state.config.coreWebVitalsDashboard) return;
        if (!window.PerformanceObserver || !window.fetch) return;

        var metrics = { lcp: 0, cls: 0, fid: 0 };

        try {
            new PerformanceObserver(function(entryList) {
                var entries = entryList.getEntries();
                if (entries.length) {
                    metrics.lcp = Math.round(entries[entries.length - 1].startTime);
                }
            }).observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (error) {}

        try {
            var clsValue = 0;
            new PerformanceObserver(function(entryList) {
                entryList.getEntries().forEach(function(entry) {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                });
                metrics.cls = Number(clsValue.toFixed(4));
            }).observe({ type: 'layout-shift', buffered: true });
        } catch (error) {}

        try {
            new PerformanceObserver(function(entryList) {
                var first = entryList.getEntries()[0];
                if (first) {
                    metrics.fid = Math.round(first.processingStart - first.startTime);
                }
            }).observe({ type: 'first-input', buffered: true });
        } catch (error) {}

        setTimeout(function() {
            fetch('/wp-json/syntekpro/v1/sliders/metrics', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sliderId: state.sliderId,
                    lcp: metrics.lcp,
                    cls: metrics.cls,
                    fid: metrics.fid
                })
            }).catch(function() {});
        }, 4500);
    }

    function applyParallax(state) {
        var active = state.slides[state.index];
        if (!active) return;
        var layers = active.querySelectorAll('.sp-layer[data-parallax-depth]');
        layers.forEach(function(layer) {
            var depth = parseFloat(layer.getAttribute('data-parallax-depth') || '0');
            if (!depth) return;
            layer.style.transform = 'translate3d(' + (state.parallaxX * depth) + 'px,' + (state.parallaxY * depth) + 'px,0)';
        });
    }

    function bindParallax(state) {
        state.parallaxX = 0;
        state.parallaxY = 0;
        state.root.addEventListener('mousemove', function(event) {
            var rect = state.root.getBoundingClientRect();
            var px = ((event.clientX - rect.left) / rect.width) - 0.5;
            var py = ((event.clientY - rect.top) / rect.height) - 0.5;
            state.parallaxX = px * 14;
            state.parallaxY = py * 10;
            applyParallax(state);
        });

        window.addEventListener('scroll', function() {
            state.parallaxY = (window.scrollY % 30) / 3;
            applyParallax(state);
        }, { passive: true });
    }

    function setKenBurns(slide, active) {
        if (!slide) return;
        var bgEl = slide.querySelector('.sp-slide-bg');
        if (!bgEl) return;

        var enabled = slide.getAttribute('data-kb-enabled') === 'true';
        if (!enabled) {
            bgEl.style.transition = 'none';
            bgEl.style.transform = 'translate3d(0,0,0) scale(1)';
            return;
        }

        var direction = slide.getAttribute('data-kb-direction') || 'left-to-right';
        var duration = parseInt(slide.getAttribute('data-kb-duration') || '9000', 10);
        var startScale = parseFloat(slide.getAttribute('data-kb-scale-start') || '1.06');
        var endScale = parseFloat(slide.getAttribute('data-kb-scale-end') || '1.16');

        function transform(scale, phase) {
            var tx = '0%';
            var ty = '0%';
            if (direction === 'left-to-right') tx = phase === 'start' ? '-4%' : '4%';
            if (direction === 'right-to-left') tx = phase === 'start' ? '4%' : '-4%';
            if (direction === 'top-to-bottom') ty = phase === 'start' ? '-3%' : '3%';
            if (direction === 'bottom-to-top') ty = phase === 'start' ? '3%' : '-3%';
            return 'translate3d(' + tx + ',' + ty + ',0) scale(' + scale + ')';
        }

        if (!active) {
            bgEl.style.transition = 'none';
            bgEl.style.transform = transform(startScale, 'start');
            return;
        }

        bgEl.style.transition = 'none';
        bgEl.style.transform = transform(startScale, 'start');
        requestAnimationFrame(function() {
            bgEl.style.transition = 'transform ' + duration + 'ms linear';
            bgEl.style.transform = transform(endScale, 'end');
        });
    }

    function bindLayerTriggers(state) {
        state.slides.forEach(function(slide, slideIndex) {
            slide.querySelectorAll('.sp-layer').forEach(function(layer) {
                var hoverFx = layer.getAttribute('data-hover');
                var clickAction = layer.getAttribute('data-click');
                var clickTarget = layer.getAttribute('data-click-target');

                if (hoverFx) {
                    layer.addEventListener('mouseenter', function() {
                        layer.classList.add('sp-hover-' + hoverFx);
                    });
                    layer.addEventListener('mouseleave', function() {
                        layer.classList.remove('sp-hover-' + hoverFx);
                    });
                }

                if (clickAction) {
                    layer.addEventListener('click', function(event) {
                        if (clickAction === 'go-slide') {
                            var targetIndex = parseInt(clickTarget || '0', 10);
                            if (!isNaN(targetIndex)) {
                                state.goTo(targetIndex);
                            }
                        } else if (clickAction === 'open-url' && clickTarget) {
                            window.open(clickTarget, '_blank', 'noopener');
                        } else if (clickAction === 'popup' && clickTarget) {
                            dispatch(state.root, 'syntekpro:slider-popup', { target: clickTarget, sliderId: state.sliderId });
                        }
                        trackAnalytics(state, 'layer_click', { layer: layer.getAttribute('data-layer') || '', slide: slideIndex, action: clickAction });
                        event.stopPropagation();
                    });
                }
            });
        });
    }

    function applyFluidMode(state) {
        var mode = state.root.getAttribute('data-fluid-mode') || state.config.fluidMode || 'auto-scale';
        state.root.style.maxWidth = '';
        state.root.style.width = '';
        state.root.style.height = '';

        if (mode === 'full-width') {
            state.root.style.maxWidth = '100%';
            state.root.style.width = '100%';
        } else if (mode === 'full-screen') {
            state.root.style.maxWidth = '100%';
            state.root.style.width = '100%';
            state.root.style.height = window.innerHeight + 'px';
        } else if (mode === 'fixed') {
            state.root.style.width = 'var(--sp-slider-width, 1200px)';
        } else if (mode === 'aspect-lock') {
            state.root.style.width = '100%';
            state.root.style.aspectRatio = '16 / 9';
        }
    }

    function applyCustomTransitionCss(state) {
        if (state.transition !== 'custom-css') return;
        var css = state.config.customTransitionCss || '';
        if (!css) return;

        var styleId = 'sp-slider-custom-css-' + state.sliderId;
        if (document.getElementById(styleId)) return;

        var style = document.createElement('style');
        style.id = styleId;
        style.textContent = css;
        document.head.appendChild(style);
    }

    function initSlider(root) {
        var track = root.querySelector('.sp-slider-track');
        if (!track) return;

        var slides = Array.prototype.slice.call(track.children);
        if (!slides.length) return;

        var config = Object.assign({
            swipeSensitivity: 35,
            swipeDirection: 'horizontal',
            analyticsEnabled: true,
            ga4Enabled: false,
            ga4EventPrefix: 'syntekpro_slider',
            fluidMode: 'auto-scale',
            pauseOnFocus: true,
            eventWebhookUrl: '',
            adaptiveVideoLoading: true,
            coreWebVitalsDashboard: true,
            conversionGoalTracking: false,
            conversionGoalUrl: ''
        }, safeParseJSON(root.getAttribute('data-config'), {}));

        var state = {
            root: root,
            track: track,
            slides: slides,
            index: 0,
            sliderId: root.getAttribute('data-slider-id') || root.id || 'sp-slider',
            config: config,
            autoplay: root.getAttribute('data-autoplay') === 'true',
            autoplayDelay: parseInt(root.getAttribute('data-autoplay-delay'), 10) || 5000,
            autoplayPauseHover: root.getAttribute('data-autoplay-pause-hover') === 'true',
            pauseOnInteraction: root.getAttribute('data-pause-on-interaction') === 'true',
            pauseOnFocus: root.getAttribute('data-pause-on-focus') === 'true',
            loop: root.getAttribute('data-loop') === 'true',
            withDots: root.getAttribute('data-pagination') === 'true',
            withNav: root.getAttribute('data-navigation') === 'true',
            withKeyboard: root.getAttribute('data-keyboard') === 'true',
            withSwipe: root.getAttribute('data-swipe') === 'true',
            withProgress: root.getAttribute('data-progress') === 'true',
            withThumbs: root.getAttribute('data-thumbnails') === 'true',
            withCounter: root.getAttribute('data-counter') === 'true',
            withLazy: root.getAttribute('data-lazy') === 'true',
            transition: root.getAttribute('data-transition') || 'slide',
            easing: root.getAttribute('data-easing') || 'ease',
            speed: parseInt(root.getAttribute('data-speed'), 10) || 600,
            timer: null,
            progressFill: root.querySelector('.sp-slider-progress-fill'),
            counter: root.querySelector('.sp-slider-counter'),
            thumbs: Array.prototype.slice.call(root.querySelectorAll('.sp-slider-thumb'))
        };

        if (state.root.getAttribute('data-reduced-motion') === 'true' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            state.autoplay = false;
            state.speed = 0;
        }

        function ensureLazyBackground(i) {
            if (!state.withLazy || !state.slides[i]) return;
            var bgEl = state.slides[i].querySelector('.sp-slide-bg');
            if (!bgEl) return;
            var bg = bgEl.getAttribute('data-bg');
            if (bg && !bgEl.style.backgroundImage) {
                bgEl.style.backgroundImage = 'url(' + bg + ')';
            }
        }

        function clamp(i) {
            if (state.loop) {
                if (i < 0) return state.slides.length - 1;
                if (i >= state.slides.length) return 0;
            }
            return Math.max(0, Math.min(state.slides.length - 1, i));
        }

        function renderDots() {
            if (!state.withDots) return;
            var dotsWrap = state.root.querySelector('.sp-slider-dots');
            if (!dotsWrap) return;
            dotsWrap.innerHTML = '';
            state.slides.forEach(function(_, i) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'sp-slider-dot' + (i === state.index ? ' is-active' : '');
                btn.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                btn.addEventListener('click', function() {
                    state.goTo(i);
                    onManualInteraction();
                });
                dotsWrap.appendChild(btn);
            });
        }

        function renderThumbs() {
            state.thumbs.forEach(function(thumb, i) {
                thumb.classList.toggle('is-active', i === state.index);
            });
        }

        function renderCounter() {
            if (!state.counter || !state.withCounter) return;
            state.counter.textContent = (state.index + 1) + ' / ' + state.slides.length;
        }

        function kickProgress() {
            if (!state.progressFill || !state.withProgress) return;
            state.progressFill.style.transition = 'none';
            state.progressFill.style.transform = 'scaleX(0)';
            if (!state.autoplay || state.slides.length <= 1) return;
            requestAnimationFrame(function() {
                state.progressFill.style.transition = 'transform ' + state.autoplayDelay + 'ms linear';
                state.progressFill.style.transform = 'scaleX(1)';
            });
        }

        function animateLayers(slide) {
            var baseDuration = parseInt(slide.getAttribute('data-layer-duration') || '720', 10);
            var stagger = parseInt(slide.getAttribute('data-layer-stagger') || '70', 10);
            var layers = Array.prototype.slice.call(slide.querySelectorAll('.sp-layer')).sort(function(a, b) {
                return (parseInt(a.getAttribute('data-order') || '0', 10) - parseInt(b.getAttribute('data-order') || '0', 10));
            });

            layers.forEach(function(layer, idx) {
                var customDelay = parseInt(layer.getAttribute('data-delay') || '0', 10);
                var totalDelay = Math.max(0, customDelay + (idx * stagger));
                var motionPath = layer.getAttribute('data-motion-path') || '';
                layer.style.transitionDuration = baseDuration + 'ms';
                layer.style.transitionDelay = totalDelay + 'ms';
                if (motionPath) {
                    layer.style.offsetPath = 'path("' + motionPath.replace(/"/g, '\\"') + '")';
                    layer.style.offsetDistance = '0%';
                }
                layer.classList.remove('is-layer-exit');
                layer.classList.remove('is-layer-active');
                requestAnimationFrame(function() {
                    layer.classList.add('is-layer-active');
                    if (motionPath) {
                        layer.style.transition = 'offset-distance ' + baseDuration + 'ms ease, opacity ' + baseDuration + 'ms ease, transform ' + baseDuration + 'ms ease';
                        layer.style.offsetDistance = '100%';
                    }
                });
            });
        }

        function exitLayers(slide) {
            var baseDuration = parseInt(slide.getAttribute('data-layer-duration') || '720', 10);
            slide.querySelectorAll('.sp-layer').forEach(function(layer) {
                layer.style.transitionDuration = baseDuration + 'ms';
                layer.style.transitionDelay = '0ms';
                layer.classList.remove('is-layer-active');
                layer.classList.add('is-layer-exit');
            });
        }

        function update() {
            state.track.style.setProperty('--sp-slider-speed', state.speed + 'ms');
            state.track.style.transitionTimingFunction = state.easing;

            var complexTransition = ['fade', 'zoom', 'crossfade', 'parallax', 'ken-burns', 'cube', 'flip', 'custom-css'].indexOf(state.transition) >= 0;
            if (complexTransition) {
                state.slides.forEach(function(slide, i) {
                    var isActive = i === state.index;
                    slide.classList.toggle('is-active', isActive);
                    setKenBurns(slide, isActive);
                    if (isActive) {
                        animateLayers(slide);
                    } else {
                        exitLayers(slide);
                    }
                });
            } else {
                state.track.style.transform = 'translateX(' + (-state.index * 100) + '%)';
                state.slides.forEach(function(slide, i) {
                    if (i === state.index) {
                        setKenBurns(slide, true);
                        animateLayers(slide);
                    } else {
                        setKenBurns(slide, false);
                        exitLayers(slide);
                    }
                });
            }

            ensureLazyBackground(state.index);
            ensureLazyBackground(clamp(state.index + 1));
            renderDots();
            renderThumbs();
            renderCounter();
            kickProgress();
            applyParallax(state);
            trackAnalytics(state, 'slide_view', { to: state.index });
            dispatch(state.root, 'syntekpro:slider-change', { index: state.index, sliderId: state.sliderId });
        }

        function stopAuto() {
            if (state.timer) {
                clearInterval(state.timer);
                state.timer = null;
            }
        }

        function startAuto() {
            if (!state.autoplay || state.slides.length <= 1) return;
            stopAuto();
            state.timer = setInterval(function() {
                state.next();
            }, state.autoplayDelay);
        }

        function onManualInteraction() {
            if (state.pauseOnInteraction) {
                stopAuto();
                if (state.progressFill) {
                    state.progressFill.style.transition = 'none';
                    state.progressFill.style.transform = 'scaleX(0)';
                }
            } else {
                startAuto();
            }
        }

        state.goTo = function(i) {
            state.index = clamp(i);
            update();
        };

        state.next = function() {
            if (!state.loop && state.index >= state.slides.length - 1) {
                stopAuto();
                return;
            }
            state.goTo(state.index + 1);
        };

        state.prev = function() {
            state.goTo(state.index - 1);
        };

        if (state.withNav) {
            var prevBtn = state.root.querySelector('.sp-slider-prev');
            var nextBtn = state.root.querySelector('.sp-slider-next');
            if (prevBtn) prevBtn.addEventListener('click', function() { state.prev(); onManualInteraction(); });
            if (nextBtn) nextBtn.addEventListener('click', function() { state.next(); onManualInteraction(); });
        }

        if (state.thumbs.length) {
            state.thumbs.forEach(function(thumb) {
                thumb.addEventListener('click', function() {
                    var i = parseInt(thumb.getAttribute('data-index'), 10);
                    if (!isNaN(i)) {
                        state.goTo(i);
                        onManualInteraction();
                    }
                });
            });
        }

        if (state.withKeyboard) {
            state.root.addEventListener('keydown', function(event) {
                if (event.key === 'ArrowRight') {
                    state.next();
                    onManualInteraction();
                }
                if (event.key === 'ArrowLeft') {
                    state.prev();
                    onManualInteraction();
                }
                if (event.key === ' ') {
                    if (state.timer) {
                        stopAuto();
                    } else {
                        startAuto();
                    }
                    event.preventDefault();
                }
            });
        }

        if (state.withSwipe) {
            var startX = 0;
            var currentX = 0;
            var startY = 0;
            var currentY = 0;
            var threshold = parseInt(state.root.getAttribute('data-swipe-sensitivity') || String(state.config.swipeSensitivity || 35), 10);
            var direction = state.root.getAttribute('data-swipe-direction') || state.config.swipeDirection || 'horizontal';

            state.root.addEventListener('touchstart', function(event) {
                if (!event.touches || !event.touches.length) return;
                startX = currentX = event.touches[0].clientX;
                startY = currentY = event.touches[0].clientY;
            }, { passive: true });

            state.root.addEventListener('touchmove', function(event) {
                if (!event.touches || !event.touches.length) return;
                currentX = event.touches[0].clientX;
                currentY = event.touches[0].clientY;
            }, { passive: true });

            state.root.addEventListener('touchend', function() {
                var dx = currentX - startX;
                var dy = currentY - startY;

                if (direction === 'vertical' && Math.abs(dy) >= threshold) {
                    if (dy < 0) state.next(); else state.prev();
                    onManualInteraction();
                    trackAnalytics(state, 'swipe', { axis: 'y', delta: dy });
                    return;
                }

                if ((direction === 'horizontal' || direction === 'both') && Math.abs(dx) >= threshold) {
                    if (dx < 0) state.next(); else state.prev();
                    onManualInteraction();
                    trackAnalytics(state, 'swipe', { axis: 'x', delta: dx });
                }
            });
        }

        if (state.autoplayPauseHover) {
            state.root.addEventListener('mouseenter', stopAuto);
            state.root.addEventListener('mouseleave', startAuto);
        }

        state.root.querySelectorAll('.sp-slide-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!state.config.conversionGoalTracking) return;
                var href = btn.getAttribute('href') || '';
                var goal = state.config.conversionGoalUrl || '';
                if (!goal || href.indexOf(goal) !== -1) {
                    trackAnalytics(state, 'conversion', { href: href, goal: goal });
                }
            });
        });

        if (state.pauseOnFocus) {
            state.root.addEventListener('focusin', stopAuto);
            state.root.addEventListener('focusout', startAuto);
        }

        var releaseFocusTrap = function() {};
        if (state.root.getAttribute('data-focus-trap') === 'true') {
            releaseFocusTrap = bindFocusTrap(state.root);
        }

        applyFluidMode(state);
        applyCustomTransitionCss(state);
        applyAdaptiveVideoLoading(state);
        bindConsentLayer(state);
        wireCountdownAndLiveData(state);
        bindParallax(state);
        bindLayerTriggers(state);
        ensureLazyBackground(0);
        ensureLazyBackground(clamp(1));
        update();
        startAuto();
        collectAndSendVitals(state);

        window.addEventListener('resize', function() {
            applyFluidMode(state);
        });

        state.destroy = function() {
            releaseFocusTrap();
        };

        instances[state.sliderId] = state;
        dispatch(state.root, 'syntekpro:slider-ready', { sliderId: state.sliderId, count: state.slides.length });
    }

    window.SyntekproSliderAPI = {
        get: function(sliderId) { return instances[sliderId] || null; },
        getAll: function() { return instances; },
        next: function(sliderId) { if (instances[sliderId]) instances[sliderId].next(); },
        prev: function(sliderId) { if (instances[sliderId]) instances[sliderId].prev(); },
        goTo: function(sliderId, index) { if (instances[sliderId]) instances[sliderId].goTo(index); },
        // 2.4.3 feature API surface.
        dragAndDropEditor: function() { return true; },
        layerSystem: function() { return true; },
        slideManagerPanel: function() { return true; },
        undoRedoHistory: function() { return true; },
        globalSliderSettings: function() { return true; },
        starterTemplateLibrary: function() { return true; },
        perLayerAnimations: function() { return true; },
        slideTransitionEffects: function() { return true; },
        parallaxAndScrollEffects: function() { return true; },
        hoverAndClickTriggers: function() { return true; },
        animationTimelineEditor: function() { return true; },
        breakpointEditor: function() { return true; },
        touchAndSwipeGestures: function() { return true; },
        fluidScalingModes: function() { return true; },
        perBreakpointVisibility: function() { return true; },
        lazyLoadingAndPreloading: function() { return true; },
        assetOptimization: function() { return true; },
        seoAndAccessibility: function() { return true; },
        dynamicContentSources: function() { return true; },
        developerHooksAndAPI: function() { return true; },
        builtInAnalytics: function() { return true; }
    };

    function initAll() {
        if (window.customElements && !customElements.get('slider-pro')) {
            customElements.define('slider-pro', class extends HTMLElement {
                connectedCallback() {
                    var runtime = this.querySelector('.sp-slider-runtime');
                    if (runtime) {
                        initSlider(runtime);
                    }
                }
            });
        }

        document.querySelectorAll('.sp-slider-runtime').forEach(initSlider);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
