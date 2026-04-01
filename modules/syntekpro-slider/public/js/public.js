/* ==========================================================================
   SyntekPro Slider — Frontend Slider Engine  v1.0.0
   Vanilla JS — No dependencies required
   ========================================================================== */
/* global SPSLIDER_PUBLIC */
(function () {
    'use strict';

    /* ── Globals ─────────────────────────────────────────────────────────── */
    var cfg = (typeof SPSLIDER_PUBLIC !== 'undefined') ? SPSLIDER_PUBLIC : {};

    /* ══════════════════════════════════════════════════════════════════════
     * SPSliderInstance
     * ════════════════════════════════════════════════════════════════════*/
    function SPSliderInstance(container, config) {
        this.container    = container;
        this.config       = config;
        this.slides       = [];
        this.currentIdx   = 0;
        this.isPlaying    = false;
        this.isAnimating  = false;
        this.autoTimer    = null;
        this.progressTimer= null;
        this.touchData    = null;
        this._events      = {};
        this.lazyObserver = null;
        this.prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    SPSliderInstance.prototype = {

        /* ── Init ─────────────────────────────────────────────────────────*/
        init: function () {
            var self = this;
            var c    = this.container;

            /* Gather slides */
            this.slides = Array.prototype.slice.call(c.querySelectorAll('.spslider-slide'));

            if (!this.slides.length) return;

            /* Speed / easing CSS vars */
            var speed  = this.config.speed  || 700;
            var easing = this.config.easing || 'ease-in-out';
            c.style.setProperty('--sp-speed',  speed + 'ms');
            c.style.setProperty('--sp-easing', easing);

            /* Apply transition class */
            var trans = this.config.transition || 'slide';
            c.classList.add('spslider-trans-' + trans);

            /* Apply scaling class */
            var sm = this.config.scaling_mode || 'auto';
            var wrapper = c.closest('.spslider-wrapper');
            if (wrapper) wrapper.classList.add('spslider-scale-' + sm);

            /* Apply aspect ratio height */
            if (this.config.height) {
                var h = parseInt(this.config.height);
                if (h) c.style.height = h + 'px';
            }

            /* Mark first slide active */
            this.slides.forEach(function (s, i) {
                s.classList.toggle('active', i === 0);
            });

            /* Build nav */
            this.buildArrows();
            this.buildDots();
            this.buildProgress();

            /* Features */
            if (this.config.lazy_load !== false) this.initLazyLoad();
            if (this.config.touch     !== false) this.initTouch();
            if (this.config.keyboard_nav)         this.initKeyboard();
            if (this.config.parallax)             this.initParallax();
            if (this.config.mousewheel_nav)       this.initMousewheel();
            this.initHoverEffects();
            this.initClickHandlers();
            this.initCountdowns();
            this.initLiveRegion();
            this.initFocusPause();
            this.initVideoAutoPause();
            this.initScrollTrigger();
            this.animateLayersIn(this.slides[0]);

            /* Autoplay */
            if (this.config.autoplay) {
                this.startAutoplay();
                if (this.config.pause_on_hover) this.initPauseOnHover();
            }

            /* Signal init */
            this.emit('init');
            c.classList.remove('loading');
        },

        /* ── Navigation ───────────────────────────────────────────────────*/
        goTo: function (idx, dir) {
            if (this.isAnimating) return;
            if (!this.config.loop) {
                if (idx < 0 || idx >= this.slides.length) return;
            } else {
                idx = ((idx % this.slides.length) + this.slides.length) % this.slides.length;
            }
            if (idx === this.currentIdx) return;

            var from = this.slides[this.currentIdx];
            var to   = this.slides[idx];
            var direction = dir || (idx > this.currentIdx ? 'left' : 'right');

            this.isAnimating = true;
            this.emit('beforeChange', { from: this.currentIdx, to: idx });
            this.track('slide_view', to.dataset.slideId);

            this.animateLayersOut(from);
            this.applyTransition(from, to, direction, idx);
        },

        next: function () { this.goTo(this.currentIdx + 1, 'left');  },
        prev: function () { this.goTo(this.currentIdx - 1, 'right'); },

        /* ── Transition engine ────────────────────────────────────────────*/
        applyTransition: function (from, to, dir, toIdx) {
            var self   = this;
            var trans  = this.config.transition || 'slide';
            var speed  = this.prefersReduced ? 0 : (this.config.speed || 700);

            if (this.prefersReduced) {
                self._commitChange(from, to, toIdx);
                return;
            }

            switch (trans) {
                case 'fade':
                    self._transitionFade(from, to, toIdx, speed);
                    break;
                case 'zoom':
                    self._transitionZoom(from, to, dir, toIdx, speed);
                    break;
                case 'crossfade':
                    self._transitionCrossfade(from, to, toIdx, speed);
                    break;
                case 'parallax':
                    self._transitionParallaxSlide(from, to, dir, toIdx, speed);
                    break;
                case 'cube3d':
                    self._transitionCube(from, to, dir, toIdx, speed);
                    break;
                case 'flip':
                    self._transitionFlip(from, to, dir, toIdx, speed);
                    break;
                case 'kenburns':
                    self._transitionFade(from, to, toIdx, speed);
                    to.classList.add('kenburns-active');
                    break;
                case 'custom':
                    to.classList.add('spslider-entering');
                    setTimeout(function () {
                        self._commitChange(from, to, toIdx);
                        to.classList.remove('spslider-entering');
                    }, speed + 50);
                    break;
                default: /* slide */
                    self._transitionSlide(from, to, dir, toIdx, speed);
                    break;
            }
        },

        _transitionFade: function (from, to, toIdx, speed) {
            var self = this;
            to.style.visibility = 'visible';
            to.style.opacity    = '0';
            to.classList.add('active');
            setTimeout(function () {
                to.style.transition = 'opacity ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                to.style.opacity    = '1';
                from.style.transition = 'opacity ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                from.style.opacity    = '0';
                setTimeout(function () {
                    self._commitChange(from, to, toIdx);
                    from.style.opacity = '';
                    to.style.opacity   = '';
                    to.style.transition = '';
                    from.style.transition = '';
                }, speed);
            }, 10);
        },

        _transitionSlide: function (from, to, dir, toIdx, speed) {
            var self = this;
            var enterFrom = dir === 'left' ? '100%' : '-100%';
            var leaveTo   = dir === 'left' ? '-100%' : '100%';
            to.style.visibility = 'visible';
            to.style.transform  = 'translateX(' + enterFrom + ')';
            to.classList.add('active');
            setTimeout(function () {
                var trans = 'transform ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                to.style.transition   = trans;
                from.style.transition = trans;
                to.style.transform    = 'translateX(0)';
                from.style.transform  = 'translateX(' + leaveTo + ')';
                setTimeout(function () {
                    self._commitChange(from, to, toIdx);
                    from.style.transform = '';
                    to.style.transform   = '';
                    to.style.transition  = '';
                    from.style.transition= '';
                }, speed);
            }, 10);
        },

        _transitionZoom: function (from, to, dir, toIdx, speed) {
            var self = this;
            to.style.visibility   = 'visible';
            to.style.transform    = 'scale(0.8)';
            to.style.opacity      = '0';
            to.classList.add('active');
            setTimeout(function () {
                var t = 'transform ' + speed + 'ms ' + (self.config.easing || 'ease-in-out') + ', opacity ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                to.style.transition   = t;
                from.style.transition = t;
                to.style.transform    = 'scale(1)';
                to.style.opacity      = '1';
                from.style.transform  = 'scale(1.1)';
                from.style.opacity    = '0';
                setTimeout(function () {
                    self._commitChange(from, to, toIdx);
                    [from, to].forEach(function (s) { s.style.transform = ''; s.style.opacity = ''; s.style.transition = ''; });
                }, speed);
            }, 10);
        },

        _transitionCrossfade: function (from, to, toIdx, speed) {
            var self = this;
            to.style.visibility = 'visible';
            to.style.opacity    = '0';
            to.classList.add('active');
            setTimeout(function () {
                var t = 'opacity ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                to.style.transition   = t;
                from.style.transition = t;
                to.style.opacity      = '1';
                from.style.opacity    = '0';
                setTimeout(function () {
                    self._commitChange(from, to, toIdx);
                    from.style.opacity = '';
                    to.style.opacity   = '';
                }, speed);
            }, 10);
        },

        _transitionParallaxSlide: function (from, to, dir, toIdx, speed) {
            var self = this;
            var enterFrom = dir === 'left' ? '100%' : '-100%';
            var fromMoveTo = dir === 'left' ? '-30%' : '30%';
            to.style.visibility = 'visible';
            to.style.transform  = 'translateX(' + enterFrom + ')';
            to.classList.add('active');
            setTimeout(function () {
                var t = 'transform ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                to.style.transition   = t;
                from.style.transition = t;
                to.style.transform    = 'translateX(0)';
                from.style.transform  = 'translateX(' + fromMoveTo + ')';
                setTimeout(function () {
                    self._commitChange(from, to, toIdx);
                    from.style.transform = '';
                    to.style.transform   = '';
                }, speed);
            }, 10);
        },

        _transitionCube: function (from, to, dir, toIdx, speed) {
            var self      = this;
            var enterAngle = dir === 'left' ? -90 : 90;
            var leaveAngle = dir === 'left' ?  90 : -90;
            to.style.visibility = 'visible';
            to.style.transform  = 'rotateY(' + enterAngle + 'deg)';
            to.classList.add('active');
            setTimeout(function () {
                var t = 'transform ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                to.style.transition   = t;
                from.style.transition = t;
                to.style.transform    = 'rotateY(0)';
                from.style.transform  = 'rotateY(' + leaveAngle + 'deg)';
                setTimeout(function () {
                    self._commitChange(from, to, toIdx);
                    from.style.transform = '';
                    to.style.transform   = '';
                }, speed);
            }, 10);
        },

        _transitionFlip: function (from, to, dir, toIdx, speed) {
            var self = this;
            to.style.visibility = 'visible';
            to.style.transform  = 'rotateY(180deg)';
            to.classList.add('active');
            setTimeout(function () {
                var t = 'transform ' + speed + 'ms ' + (self.config.easing || 'ease-in-out');
                to.style.transition   = t;
                from.style.transition = t;
                to.style.transform    = 'rotateY(0)';
                from.style.transform  = 'rotateY(-180deg)';
                setTimeout(function () {
                    self._commitChange(from, to, toIdx);
                    from.style.transform = '';
                    to.style.transform   = '';
                }, speed);
            }, 10);
        },

        _commitChange: function (from, to, toIdx) {
            from.classList.remove('active');
            from.style.visibility = '';
            this.currentIdx = toIdx;
            this.isAnimating = false;
            this.updateDots();
            this.animateLayersIn(to);
            this.lazyLoadSlide(to);
            /* Preload next */
            if (this.config.preload_next) {
                var nextIdx = (toIdx + 1) % this.slides.length;
                this.lazyLoadSlide(this.slides[nextIdx]);
            }
            /* ARIA live announce */
            this.announce('Slide ' + (toIdx + 1) + ' of ' + this.slides.length);
            this.emit('afterChange', { idx: toIdx });
            if (this.autoTimer) this.restartAutoplay();
        },

        /* ── Layer Animations ─────────────────────────────────────────────*/
        animateLayersIn: function (slide) {
            if (!slide) return;
            this._animateLayers(slide, 'in');
        },

        animateLayersOut: function (slide) {
            if (!slide) return;
            this._animateLayers(slide, 'out');
        },

        _animateLayers: function (slide, dir) {
            var self    = this;
            var layers  = slide.querySelectorAll('.spslider-layer');
            var reduced = this.prefersReduced;

            layers.forEach(function (el) {
                var configStr = el.dataset.layerConfig;
                if (!configStr) return;
                var lyrcfg;
                try { lyrcfg = JSON.parse(configStr); } catch (e) { return; }

                /* Breakpoint visibility */
                var bp = self._currentBreakpoint();
                if (bp !== 'desktop' && lyrcfg.breakpoints) {
                    var bpData = lyrcfg.breakpoints[bp] || {};
                    if (bpData.visible === false) { el.style.display = 'none'; return; }
                    el.style.display = '';
                }

                var anim = lyrcfg['animation_' + dir] || {};
                var effect   = anim.effect   || 'fade';
                var delay    = reduced ? 0 : (anim.delay    || 0);
                var duration = reduced ? 0 : (anim.duration || 600);
                var easing   = anim.easing   || 'ease-out';
                var distance = (anim.distance || 30) + 'px';

                /* Reset previous */
                el.style.animation = '';
                el.style.opacity   = '';
                el.style.transform = '';
                el.classList.remove('sp-anim-hidden', 'sp-anim-visible');

                var animName = 'sp-anim-' + effect + '-' + dir;
                el.style.cssText += [
                    '--sp-dist:' + distance,
                    'animation:' + animName + ' ' + duration + 'ms ' + easing + ' ' + delay + 'ms both',
                ].join(';');

                el.classList.add(dir === 'in' ? 'sp-anim-visible' : 'sp-anim-hidden');
            });
        },

        _currentBreakpoint: function () {
            var w = window.innerWidth;
            if (w <= 480) return 'mobile';
            if (w <= 768) return 'tablet';
            return 'desktop';
        },

        /* ── Arrows ───────────────────────────────────────────────────────*/
        buildArrows: function () {
            var c    = this.container;
            var self = this;
            if (!this.config.arrows) return;

            var svgLeft  = '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>';
            var svgRight = '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>';

            var $prev = this._createElement('button', { class: 'spslider-arrow spslider-prev', 'aria-label': 'Previous slide', type: 'button' });
            $prev.innerHTML = svgLeft;

            var $next = this._createElement('button', { class: 'spslider-arrow spslider-next', 'aria-label': 'Next slide', type: 'button' });
            $next.innerHTML = svgRight;

            $prev.addEventListener('click', function () { self.prev(); self.track('nav_click'); });
            $next.addEventListener('click', function () { self.next(); self.track('nav_click'); });

            c.appendChild($prev);
            c.appendChild($next);
        },

        /* ── Dots ─────────────────────────────────────────────────────────*/
        buildDots: function () {
            var c    = this.container;
            var self = this;
            if (!this.config.dots) return;

            var $wrap = this._createElement('div', { class: 'spslider-dots', role: 'tablist', 'aria-label': 'Slides' });

            this.slides.forEach(function (s, i) {
                var $dot = self._createElement('button', {
                    class: 'spslider-dot' + (i === 0 ? ' active' : ''),
                    type:  'button',
                    role:  'tab',
                    'aria-selected': i === 0 ? 'true' : 'false',
                    'aria-label': 'Slide ' + (i + 1),
                });
                $dot.addEventListener('click', function () {
                    self.goTo(i, i > self.currentIdx ? 'left' : 'right');
                    self.track('nav_click');
                });
                $wrap.appendChild($dot);
            });

            c.appendChild($wrap);
            this._dots = $wrap.querySelectorAll('.spslider-dot');
        },

        updateDots: function () {
            if (!this._dots) return;
            var self = this;
            this._dots.forEach(function (dot, i) {
                var active = i === self.currentIdx;
                dot.classList.toggle('active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        },

        /* ── Progress bar ─────────────────────────────────────────────────*/
        buildProgress: function () {
            if (!this.config.autoplay) return;
            this._progressBar = this._createElement('div', { class: 'spslider-progress-bar' });
            this.container.appendChild(this._progressBar);
        },

        _updateProgress: function (elapsed, total) {
            if (!this._progressBar) return;
            var pct = Math.min(100, (elapsed / total) * 100);
            this._progressBar.style.width = pct + '%';
        },

        /* ── Autoplay ─────────────────────────────────────────────────────*/
        startAutoplay: function () {
            var self  = this;
            var speed = this.config.autoplay_speed || 5000;
            this.isPlaying = true;
            this.container.classList.remove('is-paused');

            var startTime = Date.now();
            this._progressTick = setInterval(function () {
                self._updateProgress(Date.now() - startTime, speed);
            }, 50);

            this.autoTimer = setTimeout(function () {
                clearInterval(self._progressTick);
                self.next();
            }, speed);
        },

        stopAutoplay: function () {
            clearTimeout(this.autoTimer);
            clearInterval(this._progressTick);
            this.autoTimer = null;
            this.isPlaying = false;
            this.container.classList.add('is-paused');
            if (this._progressBar) this._progressBar.style.width = '0%';
        },

        restartAutoplay: function () {
            this.stopAutoplay();
            this.startAutoplay();
        },

        initPauseOnHover: function () {
            var self = this;
            this.container.addEventListener('mouseenter', function () { self.pause(); });
            this.container.addEventListener('mouseleave', function () { self.play(); });
        },

        play: function () {
            if (!this.isPlaying) this.startAutoplay();
        },

        pause: function () {
            this.stopAutoplay();
        },

        /* ── Touch / Swipe ────────────────────────────────────────────────*/
        initTouch: function () {
            var self      = this;
            var threshold = this.config.swipe_sensitivity || 50;
            var c         = this.container;

            c.addEventListener('touchstart', function (e) {
                self.touchData = { x: e.touches[0].clientX, y: e.touches[0].clientY, startTime: Date.now() };
                if (self.isPlaying) self.stopAutoplay();
            }, { passive: true });

            c.addEventListener('touchmove', function (e) {
                if (!self.touchData) return;
                var dx = e.touches[0].clientX - self.touchData.x;
                if (Math.abs(dx) > 5) e.preventDefault();
            }, { passive: false });

            c.addEventListener('touchend', function (e) {
                if (!self.touchData) return;
                var dx   = e.changedTouches[0].clientX - self.touchData.x;
                var dy   = e.changedTouches[0].clientY - self.touchData.y;
                var time = Date.now() - self.touchData.startTime;
                self.touchData = null;

                if (Math.abs(dx) > threshold && Math.abs(dx) > Math.abs(dy)) {
                    dx < 0 ? self.next() : self.prev();
                    self.track('nav_swipe');
                }
                if (self.config.autoplay) self.startAutoplay();
            });
        },

        /* ── Keyboard navigation ──────────────────────────────────────────*/
        initKeyboard: function () {
            var self = this;
            this.container.setAttribute('tabindex', '0');
            this.container.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft')  { self.prev(); e.preventDefault(); }
                if (e.key === 'ArrowRight') { self.next(); e.preventDefault(); }
            });
        },

        /* ── Lazy Loading ─────────────────────────────────────────────────*/
        initLazyLoad: function () {
            if (!window.IntersectionObserver) return;
            var self = this;
            this.lazyObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        self.lazyLoadSlide(entry.target);
                        self.lazyObserver.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '200px' });

            this.slides.forEach(function (slide, i) {
                if (i > 0) {
                    self.lazyObserver.observe(slide);
                } else {
                    self.lazyLoadSlide(slide);
                }
            });
        },

        lazyLoadSlide: function (slide) {
            if (!slide) return;
            /* Background image */
            var bgLazy = slide.dataset.bgLazy;
            if (bgLazy) {
                slide.style.backgroundImage = 'url(' + bgLazy + ')';
                delete slide.dataset.bgLazy;
            }
            /* Lazy images inside */
            slide.querySelectorAll('img[data-src]').forEach(function (img) {
                img.src = img.dataset.src;
                delete img.dataset.src;
            });
        },

        /* ── Parallax ─────────────────────────────────────────────────────*/
        initParallax: function () {
            var self    = this;
            var c       = this.container;
            var rafPending = false;

            function onPointer(e) {
                if (rafPending) return;
                rafPending = true;
                requestAnimationFrame(function () {
                    rafPending = false;
                    var rect = c.getBoundingClientRect();
                    var cx   = e.clientX - rect.left - rect.width  / 2;
                    var cy   = e.clientY - rect.top  - rect.height / 2;
                    var slide = self.slides[self.currentIdx];
                    if (!slide) return;
                    slide.querySelectorAll('.spslider-layer[data-parallax]').forEach(function (el) {
                        var depth = parseFloat(el.dataset.parallax) || 0;
                        if (!depth) return;
                        var tx = cx * depth * 0.04;
                        var ty = cy * depth * 0.04;
                        el.style.transform = 'translate(' + tx + 'px,' + ty + 'px)';
                    });
                });
            }

            function onScroll() {
                if (rafPending) return;
                rafPending = true;
                requestAnimationFrame(function () {
                    rafPending = false;
                    var scrollY = window.pageYOffset;
                    var rect    = c.getBoundingClientRect();
                    var relY    = rect.top + rect.height / 2;
                    var slide   = self.slides[self.currentIdx];
                    if (!slide) return;
                    slide.querySelectorAll('.spslider-layer[data-parallax]').forEach(function (el) {
                        var depth = parseFloat(el.dataset.parallax) || 0;
                        if (!depth) return;
                        var ty = (relY - window.innerHeight / 2) * depth * 0.1;
                        el.style.transform = 'translateY(' + ty + 'px)';
                    });
                });
            }

            c.addEventListener('mousemove', onPointer, { passive: true });
            window.addEventListener('scroll', onScroll, { passive: true });
        },

        /* ── Hover effects ────────────────────────────────────────────────*/
        initHoverEffects: function () {
            this.slides.forEach(function (slide) {
                slide.querySelectorAll('.spslider-layer').forEach(function (el) {
                    var hoverBg = el.dataset.hoverBg;
                    if (hoverBg) {
                        var origBg = el.style.background || el.style.backgroundColor;
                        el.addEventListener('mouseenter', function () { el.style.background = hoverBg; });
                        el.addEventListener('mouseleave', function () { el.style.background = origBg;  });
                    }
                });
            });
        },

        /* ── Click handlers ───────────────────────────────────────────────*/
        initClickHandlers: function () {
            var self = this;
            this.slides.forEach(function (slide) {
                slide.querySelectorAll('.spslider-layer').forEach(function (el) {
                    var action = el.dataset.clickAction;
                    if (!action || action === 'none') return;

                    el.style.cursor = 'pointer';
                    el.addEventListener('click', function () {
                        switch (action) {
                            case 'url':
                                var url    = el.dataset.clickUrl;
                                var target = el.dataset.clickTarget || '_self';
                                if (url) window.open(url, target);
                                break;
                            case 'slide':
                                var idx = parseInt(el.dataset.clickSlide) - 1;
                                if (!isNaN(idx)) self.goTo(idx);
                                break;
                            case 'video':
                                var vid = slide.querySelector('video');
                                if (vid) vid.paused ? vid.play() : vid.pause();
                                break;
                        }
                        self.track('layer_click', null, el.dataset.layerId);
                    });
                });
            });
        },

        /* ── Analytics ────────────────────────────────────────────────────*/
        track: function (eventType, slideId, layerId) {
            if (!cfg.analytics) return;
            var sliderId = this.container.dataset.sliderId;
            var payload  = {
                action:     'spslider_track',
                nonce:      cfg.nonce,
                slider_id:  sliderId,
                event_type: eventType,
            };
            if (slideId) payload.slide_id = slideId;
            if (layerId) payload.layer_id = layerId;

            /* Beacon if available (non-blocking) */
            if (navigator.sendBeacon && cfg.ajax_url) {
                var fd = new FormData();
                Object.keys(payload).forEach(function (k) { fd.append(k, payload[k]); });
                navigator.sendBeacon(cfg.ajax_url, fd);
            } else if (cfg.ajax_url) {
                fetch(cfg.ajax_url, { method: 'POST', body: new URLSearchParams(payload) }).catch(function () {});
            }
        },

        /* ── Event emitter ────────────────────────────────────────────────*/
        on: function (event, cb) {
            if (!this._events[event]) this._events[event] = [];
            this._events[event].push(cb);
            return this;
        },

        off: function (event, cb) {
            if (!this._events[event]) return;
            this._events[event] = this._events[event].filter(function (fn) { return fn !== cb; });
        },

        emit: function (event, data) {
            var handlers = this._events[event] || [];
            handlers.forEach(function (fn) { try { fn(data); } catch (e) {} });
            /* Also dispatch DOM CustomEvent */
            var ce = new CustomEvent('spslider:' + event, { detail: data, bubbles: true });
            this.container.dispatchEvent(ce);
        },

        /* ── Destroy ──────────────────────────────────────────────────────*/
        destroy: function () {
            this.stopAutoplay();
            if (this.lazyObserver) this.lazyObserver.disconnect();
            if (this._countdownInterval) clearInterval(this._countdownInterval);
            this._events = {};
            this.container.classList.remove('loading');
            this.emit('destroyed');
        },

        /* ── Helper ───────────────────────────────────────────────────────*/
        _createElement: function (tag, attrs) {
            var el = document.createElement(tag);
            Object.keys(attrs).forEach(function (key) { el.setAttribute(key, attrs[key]); });
            return el;
        },

        /* ── Countdown Timers ─────────────────────────────────────────────*/
        initCountdowns: function () {
            var self = this;
            var countdowns = this.container.querySelectorAll('.spslider-countdown-layer');
            if (!countdowns.length) return;

            function pad(n) { return n < 10 ? '0' + n : '' + n; }

            function tick() {
                countdowns.forEach(function (el) {
                    var target = new Date(el.dataset.target).getTime();
                    var now    = Date.now();
                    var diff   = target - now;

                    if (diff <= 0) {
                        el.textContent = el.dataset.expired || 'Expired!';
                        return;
                    }

                    var d = Math.floor(diff / 86400000);
                    var h = Math.floor((diff % 86400000) / 3600000);
                    var m = Math.floor((diff % 3600000) / 60000);
                    var s = Math.floor((diff % 60000) / 1000);

                    var dEl = el.querySelector('.sp-cd-days');
                    var hEl = el.querySelector('.sp-cd-hours');
                    var mEl = el.querySelector('.sp-cd-mins');
                    var sEl = el.querySelector('.sp-cd-secs');
                    if (dEl) dEl.textContent = pad(d);
                    if (hEl) hEl.textContent = pad(h);
                    if (mEl) mEl.textContent = pad(m);
                    if (sEl) sEl.textContent = pad(s);
                });
            }

            tick();
            this._countdownInterval = setInterval(tick, 1000);
        },

        /* ── Live Announcer (ARIA) ────────────────────────────────────────*/
        initLiveRegion: function () {
            this._liveRegion = this._createElement('div', {
                'class': 'spslider-sr-only',
                'aria-live': 'polite',
                'aria-atomic': 'true',
                'role': 'status'
            });
            this._liveRegion.style.cssText = 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;';
            this.container.appendChild(this._liveRegion);
        },

        announce: function (text) {
            if (this._liveRegion) this._liveRegion.textContent = text;
        },

        /* ── Focus management (pause on focus) ────────────────────────────*/
        initFocusPause: function () {
            var self = this;
            this.container.addEventListener('focusin', function () {
                if (self.isPlaying) self.pause();
            });
            this.container.addEventListener('focusout', function (e) {
                if (!self.container.contains(e.relatedTarget) && self.config.autoplay) {
                    self.play();
                }
            });
        },

        /* ── Video auto-pause (when slide leaves) ─────────────────────────*/
        initVideoAutoPause: function () {
            var self = this;
            this.on('beforeChange', function (data) {
                var from = self.slides[data.from];
                if (!from) return;
                from.querySelectorAll('video').forEach(function (v) {
                    if (!v.paused) v.pause();
                });
            });
            this.on('afterChange', function (data) {
                var to = self.slides[data.idx];
                if (!to) return;
                to.querySelectorAll('video[autoplay]').forEach(function (v) {
                    v.play().catch(function(){});
                });
            });
        },

        /* ── Mousewheel navigation ────────────────────────────────────────*/
        initMousewheel: function () {
            var self = this;
            var lastWheel = 0;
            this.container.addEventListener('wheel', function (e) {
                var now = Date.now();
                if (now - lastWheel < 800) return; // debounce
                lastWheel = now;
                if (e.deltaY > 0) self.next();
                else if (e.deltaY < 0) self.prev();
                e.preventDefault();
            }, { passive: false });
        },

        /* ── Scroll-triggered autoplay ────────────────────────────────────*/
        initScrollTrigger: function () {
            var self = this;
            if (!window.IntersectionObserver) return;
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting && !self.isPlaying && self.config.autoplay) {
                        self.startAutoplay();
                    } else if (!entry.isIntersecting && self.isPlaying) {
                        self.stopAutoplay();
                    }
                });
            }, { threshold: 0.3 });
            observer.observe(this.container);
        },
    };

    /* ══════════════════════════════════════════════════════════════════════
     * Auto-init all .spslider-container elements on DOM ready
     * ════════════════════════════════════════════════════════════════════*/
    var instances = [];

    function initAll() {
        var containers = document.querySelectorAll('.spslider-container');
        containers.forEach(function (el) {
            if (el._spslider) return; /* already initialised */
            var configStr = el.dataset.config || '{}';
            var config;
            try { config = JSON.parse(configStr); } catch (e) { config = {}; }
            el.classList.add('loading');
            var inst = new SPSliderInstance(el, config);
            inst.init();
            el._spslider = inst;
            instances.push(inst);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    /* ══════════════════════════════════════════════════════════════════════
     * Public API  window.SPSlider
     * ════════════════════════════════════════════════════════════════════*/
    window.SPSlider = {
        instances: instances,

        /** Get the SPSliderInstance for a slider by its numeric ID */
        get: function (sliderId) {
            var el = document.querySelector('.spslider-container[data-slider-id="' + sliderId + '"]');
            return el ? el._spslider : null;
        },

        /** Manually initialise a container element (e.g. in a modal / after AJAX) */
        init: function (el, config) {
            if (!el) return null;
            var inst = new SPSliderInstance(el, config || {});
            inst.init();
            el._spslider = inst;
            instances.push(inst);
            return inst;
        },

        /** Reinitialise all containers that were added after page load */
        reinit: function () { initAll(); },
    };

})();
