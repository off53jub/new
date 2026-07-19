/**
 * Studio Audubon - 機能拡張用JS
 * - トップページのスプラッシュ（ローディング画面）
 * - トップページの TOPICS スライダー（2列カード + ドット送り + 自動送り）
 * - 「プロフィールをPDFで保存」ボタン
 */
(function () {
    'use strict';

    /**
     * TOPICS スライダー
     */
    function initTopics(root) {
        var track = root.querySelector('.audubon-topics__track');
        var dotsList = root.querySelector('.audubon-topics__dots');
        if (!track) return;

        var items = Array.prototype.slice.call(track.children);
        if (items.length === 0) return;

        var visible = parseInt(root.dataset.visible, 10) || 2;
        var interval = parseInt(root.dataset.interval, 10) || 5500;
        var index = 0;
        var timer = null;
        var paused = false; // カーソルが乗っている／フォーカス中は自動送りを止めるフラグ
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // モバイル時に visible が動的に1になるかチェック（CSSで flex: 0 0 100%）。
        // ただ JS 側では index = 「leftmost のアイテム位置」として常に1ずつ進めればOK
        // ループ用に先頭のアイテムを末尾に複製
        if (items.length > visible) {
            for (var i = 0; i < visible; i++) {
                var clone = items[i].cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                track.appendChild(clone);
            }
        }

        // ドット生成（オリジナルのアイテム数分）
        var dots = [];
        if (dotsList) {
            dotsList.innerHTML = '';
            for (var d = 0; d < items.length; d++) {
                var li = document.createElement('li');
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'audubon-topics__dot';
                btn.setAttribute('aria-label', (d + 1) + '番目に移動');
                (function (target) {
                    btn.addEventListener('click', function () {
                        stop();
                        index = target;
                        update(true);
                        renderDots();
                        start();
                    });
                })(d);
                li.appendChild(btn);
                dotsList.appendChild(li);
                dots.push(btn);
            }
        }

        function getStep() {
            var first = track.children[0];
            return first ? first.getBoundingClientRect().width : 0;
        }

        function update(animate) {
            track.style.transition = animate ? '' : 'none';
            track.style.transform = 'translateX(' + (-index * getStep()) + 'px)';
            renderDots();
        }

        function renderDots() {
            if (!dots.length) return;
            // 表示中のアクティブ ドット = (index mod items.length)
            var active = ((index % items.length) + items.length) % items.length;
            for (var i = 0; i < dots.length; i++) {
                if (i === active) {
                    dots[i].classList.add('audubon-topics__dot--active');
                } else {
                    dots[i].classList.remove('audubon-topics__dot--active');
                }
            }
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
            // タイマー自体は動かし続け、paused の間だけ送りをスキップする。
            // これで「カーソルを乗せる→止まる」が2回目以降も毎回効く。
            timer = window.setInterval(function () {
                if (paused) return;
                next();
            }, interval);
        }

        function stop() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        var prevBtn = root.querySelector('.audubon-topics__nav--prev');
        var nextBtn = root.querySelector('.audubon-topics__nav--next');
        if (prevBtn) prevBtn.addEventListener('click', function () { stop(); prev(); start(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { stop(); next(); start(); });

        // ---- 一時停止の制御 ----
        // ホバーできる端末（PC）: カーソルを乗せている間だけ止める（離れたら再開）。
        // ホバーできない端末（スマホ等）: スライダーをタップすると停止、もう一度タップで再開。
        //   → PC のホバー停止と同じ「止めたら止まる」体験をタッチでも実現する。
        var canHover = !(window.matchMedia && window.matchMedia('(hover: none)').matches);

        if (canHover) {
            // フラグ方式：タイマーは動かし続け paused の間だけ送りをスキップするので毎回効く。
            root.addEventListener('mouseenter', function () { paused = true; });
            root.addEventListener('mouseleave', function () { paused = false; });
            // キーボード操作でも止まる
            root.addEventListener('focusin',  function () { paused = true; });
            root.addEventListener('focusout', function () { paused = false; });
        } else {
            // タッチ端末：指で押さえている間だけ止める（離すと再開）。
            // PC のホバー停止と同じく「触れている間は止まる」挙動をタッチで再現。
            var holdPause  = function () { paused = true;  root.classList.add('audubon-topics--paused'); };
            var holdResume = function () { paused = false; root.classList.remove('audubon-topics--paused'); };
            root.addEventListener('touchstart',  holdPause,  { passive: true });
            root.addEventListener('touchend',     holdResume);
            root.addEventListener('touchcancel',  holdResume);
        }

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

    /**
     * Pickup の石積み（masonry）
     * 縦横バラバラの画像でも、背の低い列へ順に詰めて大きなすき間をなくす。
     * 各列は上端から始まるので左右が揃う。JSが無効なら1列で縦積み（CSS既定）。
     */
    function initPickupMasonry(grid) {
        var items = Array.prototype.slice.call(grid.querySelectorAll('.audubon-pickup__item'));
        if (items.length < 2) return;
        var builtCols = 0;

        function ratioOf(it) {
            var img = it.querySelector('img');
            if (img && img.naturalWidth) return img.naturalHeight / img.naturalWidth;
            if (it.querySelector('iframe')) return 9 / 16; // YouTube等は16:9
            return 1;
        }
        function layout() {
            var cols = grid.clientWidth < 600 ? 1 : 2;
            if (cols === builtCols) return;
            builtCols = cols;
            grid.innerHTML = '';
            if (cols === 1) {
                grid.classList.remove('is-masonry');
                items.forEach(function (it) { grid.appendChild(it); });
                return;
            }
            grid.classList.add('is-masonry');
            var colEls = [], colH = [];
            for (var c = 0; c < cols; c++) {
                var d = document.createElement('div');
                d.className = 'audubon-pickup__col';
                grid.appendChild(d); colEls.push(d); colH.push(0);
            }
            items.forEach(function (it) {
                var mi = 0;
                for (var c = 1; c < cols; c++) { if (colH[c] < colH[mi]) mi = c; }
                colEls[mi].appendChild(it); colH[mi] += ratioOf(it);
            });
        }
        // 画像サイズが確定したら組み直す
        items.forEach(function (it) {
            var img = it.querySelector('img');
            if (img && !img.complete) { img.addEventListener('load', function () { builtCols = 0; layout(); }); }
        });
        layout();
        var t; window.addEventListener('resize', function () { window.clearTimeout(t); t = window.setTimeout(layout, 150); });
    }

    ready(function () {
        initSplash();

        var topics = document.querySelectorAll('.audubon-topics');
        Array.prototype.forEach.call(topics, initTopics);

        var pickups = document.querySelectorAll('.audubon-pickup');
        Array.prototype.forEach.call(pickups, initPickupMasonry);

        // 「プロフィールをPDFで保存」ボタン → ブラウザの印刷機能を呼び出す
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest && e.target.closest('[data-audubon-print="1"]');
            if (trigger) {
                e.preventDefault();
                window.print();
            }
        });
    });
})();
