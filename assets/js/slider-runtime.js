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
        var loop = root.getAttribute('data-loop') === 'true';
        var withDots = root.getAttribute('data-pagination') === 'true';
        var withNav = root.getAttribute('data-navigation') === 'true';
        var index = 0;
        var timer = null;

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

        function update() {
            track.style.transform = 'translateX(' + (-index * 100) + '%)';
            renderDots();
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
            if (prevBtn) prevBtn.addEventListener('click', prev);
            if (nextBtn) nextBtn.addEventListener('click', next);
        }

        root.addEventListener('mouseenter', stopAuto);
        root.addEventListener('mouseleave', startAuto);

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
