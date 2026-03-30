(function() {
    'use strict';

    function initSlider(root) {
        var track = root.querySelector('.sp-slider-track');
        if (!track) {
            return;
        }

        var slides = Array.prototype.slice.call(track.children);
        if (!slides.length) {
            return;
        }

        var autoplay = root.getAttribute('data-autoplay') === 'true';
        var autoplayDelay = parseInt(root.getAttribute('data-autoplay-delay'), 10) || 5000;
        var autoplayPauseHover = root.getAttribute('data-autoplay-pause-hover') === 'true';
        var pauseOnInteraction = root.getAttribute('data-pause-on-interaction') === 'true';
        var loop = root.getAttribute('data-loop') === 'true';
        var withDots = root.getAttribute('data-pagination') === 'true';
        var withNav = root.getAttribute('data-navigation') === 'true';
        var withKeyboard = root.getAttribute('data-keyboard') === 'true';
        var withSwipe = root.getAttribute('data-swipe') === 'true';
        var withProgress = root.getAttribute('data-progress') === 'true';
        var withThumbs = root.getAttribute('data-thumbnails') === 'true';
        var withCounter = root.getAttribute('data-counter') === 'true';
        var withLazy = root.getAttribute('data-lazy') === 'true';
        var transition = root.getAttribute('data-transition') || 'slide';
        var speed = parseInt(root.getAttribute('data-speed'), 10) || 600;
        var index = 0;
        var timer = null;

        var progressFill = withProgress ? root.querySelector('.sp-slider-progress-fill') : null;
        var counter = withCounter ? root.querySelector('.sp-slider-counter') : null;
        var thumbs = withThumbs ? Array.prototype.slice.call(root.querySelectorAll('.sp-slider-thumb')) : [];

        function ensureLazyBackground(i) {
            if (!withLazy || !slides[i]) {
                return;
            }
            var bg = slides[i].getAttribute('data-bg');
            if (bg && !slides[i].style.backgroundImage) {
                slides[i].style.backgroundImage = 'url(' + bg + ')';
            }
        }

        function clamp(i) {
            if (loop) {
                if (i < 0) return slides.length - 1;
                if (i >= slides.length) return 0;
            }
            if (i < 0) return 0;
            if (i >= slides.length) return slides.length - 1;
            return i;
        }

        function renderDots() {
            if (!withDots) return;
            var dotsWrap = root.querySelector('.sp-slider-dots');
            if (!dotsWrap) return;
            dotsWrap.innerHTML = '';
            slides.forEach(function(_, i) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'sp-slider-dot' + (i === index ? ' is-active' : '');
                btn.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                btn.addEventListener('click', function() {
                    goTo(i);
                });
                dotsWrap.appendChild(btn);
            });
        }

        function renderThumbs() {
            if (!thumbs.length) return;
            thumbs.forEach(function(thumb, i) {
                if (i === index) {
                    thumb.classList.add('is-active');
                } else {
                    thumb.classList.remove('is-active');
                }
            });
        }

        function renderCounter() {
            if (!counter) return;
            counter.textContent = (index + 1) + ' / ' + slides.length;
        }

        function kickProgress() {
            if (!progressFill) return;
            progressFill.style.transition = 'none';
            progressFill.style.transform = 'scaleX(0)';
            if (!autoplay || slides.length <= 1) {
                return;
            }
            requestAnimationFrame(function() {
                progressFill.style.transition = 'transform ' + autoplayDelay + 'ms linear';
                progressFill.style.transform = 'scaleX(1)';
            });
        }

        function update() {
            if (transition === 'fade' || transition === 'zoom') {
                slides.forEach(function(slide, i) {
                    if (i === index) {
                        slide.classList.add('is-active');
                    } else {
                        slide.classList.remove('is-active');
                    }
                });
            } else {
                track.style.transform = 'translateX(' + (-index * 100) + '%)';
            }

            ensureLazyBackground(index);
            ensureLazyBackground(clamp(index + 1));
            renderDots();
            renderThumbs();
            renderCounter();
            kickProgress();
        }

        function goTo(i) {
            index = clamp(i);
            update();
        }

        function next() {
            if (!loop && index >= slides.length - 1) {
                stopAuto();
                return;
            }
            goTo(index + 1);
        }

        function onManualInteraction() {
            if (pauseOnInteraction) {
                stopAuto();
                if (progressFill) {
                    progressFill.style.transition = 'none';
                    progressFill.style.transform = 'scaleX(0)';
                }
            } else {
                startAuto();
            }
        }

        function prev() {
            goTo(index - 1);
        }

        function stopAuto() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function startAuto() {
            if (!autoplay || slides.length <= 1) {
                return;
            }
            stopAuto();
            timer = setInterval(next, autoplayDelay);
        }

        if (withNav) {
            var prevBtn = root.querySelector('.sp-slider-prev');
            var nextBtn = root.querySelector('.sp-slider-next');
            if (prevBtn) prevBtn.addEventListener('click', function() { prev(); onManualInteraction(); });
            if (nextBtn) nextBtn.addEventListener('click', function() { next(); onManualInteraction(); });
        }

        if (thumbs.length) {
            thumbs.forEach(function(thumb) {
                thumb.addEventListener('click', function() {
                    var i = parseInt(thumb.getAttribute('data-index'), 10);
                    if (!isNaN(i)) {
                        goTo(i);
                        onManualInteraction();
                    }
                });
            });
        }

        if (withKeyboard) {
            root.addEventListener('keydown', function(event) {
                if (event.key === 'ArrowRight') {
                    next();
                    onManualInteraction();
                }
                if (event.key === 'ArrowLeft') {
                    prev();
                    onManualInteraction();
                }
            });
        }

        if (withSwipe) {
            var touchStartX = 0;
            var touchCurrentX = 0;

            root.addEventListener('touchstart', function(event) {
                if (!event.touches || !event.touches.length) return;
                touchStartX = event.touches[0].clientX;
                touchCurrentX = touchStartX;
            }, { passive: true });

            root.addEventListener('touchmove', function(event) {
                if (!event.touches || !event.touches.length) return;
                touchCurrentX = event.touches[0].clientX;
            }, { passive: true });

            root.addEventListener('touchend', function() {
                var delta = touchCurrentX - touchStartX;
                if (Math.abs(delta) < 35) return;
                if (delta < 0) {
                    next();
                } else {
                    prev();
                }
                onManualInteraction();
            });
        }

        if (autoplayPauseHover) {
            root.addEventListener('mouseenter', stopAuto);
            root.addEventListener('mouseleave', startAuto);
        }

        ensureLazyBackground(0);
        ensureLazyBackground(clamp(1));

        track.style.setProperty('--sp-slider-speed', speed + 'ms');

        update();
        startAuto();
    }

    function initAll() {
        document.querySelectorAll('.sp-slider-runtime').forEach(initSlider);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
