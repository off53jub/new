/**
 * Studio Audubon - 機能拡張用JS
 * トップページの A4 3分割スライダー用。
 */
(function () {
    'use strict';

    function initBanner(banner) {
        var track = banner.querySelector('.audubon-banner__track');
        if (!track) return;
        var items = Array.prototype.slice.call(track.children);
        if (items.length === 0) return;

        var visible = parseInt(banner.dataset.visible, 10) || 3;
        var interval = parseInt(banner.dataset.interval, 10) || 4000;
        var index = 0;
        var timer = null;
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (items.length > visible) {
            for (var i = 0; i < visible; i++) {
                var clone = items[i].cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                track.appendChild(clone);
            }
        }

        function getStep() {
            var first = track.children[0];
            return first ? first.getBoundingClientRect().width : 0;
        }
        function update(animate) {
            track.style.transition = animate ? '' : 'none';
            track.style.transform = 'translateX(' + (-index * getStep()) + 'px)';
        }
        function next() {
            index++;
            update(true);
            if (index >= items.length) {
                window.setTimeout(function () {
                    index = 0;
                    update(false);
                }, 600);
            }
        }
        function prev() {
            if (index <= 0) {
                index = items.length;
                update(false);
                window.setTimeout(function () {
                    index--;
                    update(true);
                }, 20);
                return;
            }
            index--;
            update(true);
        }
        function start() {
            if (reduced || items.length <= visible) return;
            stop();
            timer = window.setInterval(next, interval);
        }
        function stop() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        var prevBtn = banner.querySelector('.audubon-banner__nav--prev');
        var nextBtn = banner.querySelector('.audubon-banner__nav--next');
        if (prevBtn) prevBtn.addEventListener('click', function () { stop(); prev(); start(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { stop(); next(); start(); });

        banner.addEventListener('mouseenter', stop);
        banner.addEventListener('mouseleave', start);
        window.addEventListener('resize', function () { update(false); });

        update(false);
        start();
    }

    function ready(fn) {
        if (document.readyState !== 'loading') { fn(); return; }
        document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        var banners = document.querySelectorAll('.audubon-banner');
        Array.prototype.forEach.call(banners, initBanner);
    });
})();
