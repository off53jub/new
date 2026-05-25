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

    /**
     * スプラッシュ（ローディング画面）の制御。
     * - ホームページにのみ DOM が出力されている
     * - 画像のロードが終わってから最低 1.6s 表示してフェードアウト
     * - ホームページを開くたびに毎回表示します
     */
    function initSplash() {
        var splash = document.getElementById('audubon-splash');
        if (!splash) return;

        document.body.classList.add('audubon-splash-active');

        var img = splash.querySelector('.audubon-splash__image');
        var minDisplayMs = 1600;
        var fadeMs = 700;
        var startedAt = Date.now();

        function hide() {
            var elapsed = Date.now() - startedAt;
            var wait = Math.max(0, minDisplayMs - elapsed);
            window.setTimeout(function () {
                splash.classList.add('audubon-splash--hidden');
                document.body.classList.remove('audubon-splash-active');
                window.setTimeout(function () {
                    if (splash.parentNode) splash.parentNode.removeChild(splash);
                }, fadeMs);
            }, wait);
        }

        if (img && !img.complete) {
            img.addEventListener('load', hide);
            img.addEventListener('error', hide);
            // 念のため7秒後にフォールバックで強制非表示
            window.setTimeout(hide, 7000);
        } else {
            hide();
        }
    }

    ready(function () {
        initSplash();

        var banners = document.querySelectorAll('.audubon-banner');
        Array.prototype.forEach.call(banners, initBanner);

        // 「プロフィールをPDFで保存」ボタン → ブラウザの印刷機能を呼び出す
        // ユーザは印刷ダイアログから「PDFとして保存」を選んでダウンロードします
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest && e.target.closest('[data-audubon-print="1"]');
            if (trigger) {
                e.preventDefault();
                window.print();
            }
        });
    });
})();
