<?php
/**
 * Studio Audubon - 機能拡張ファイル
 *
 * 既存テーマ studio_audubon_02 に対する追加機能。
 * functions.php から require_once で読み込まれます。
 *
 * 対象CPT:
 *   - actor      （アクター）
 *   - news_list  （ニュース）※既存のCPT
 *   - slides     （スライド）
 *   - post       （Worksは通常投稿、category=works）
 *
 * メタキーは全て `_audubon_*` で名前空間化。既存ACFフィールドとは衝突しません。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'AUDUBON_FEATURES_VERSION' ) ) {
    define( 'AUDUBON_FEATURES_VERSION', '1.0.0' );
}

/* =============================================================
 * 1. CSS / JS
 * ============================================================= */

/**
 * 既存の slides CPT に page-attributes（標準の "Order" 入力欄）サポートを後付け。
 * これでスライド編集画面の右サイドバーに "Page Attributes" メタボックスが出ます。
 * ※メインの表示順入力欄はカスタムメタボックス側にも設けています（より目立つ位置）。
 */
add_action( 'init', function () {
    if ( post_type_exists( 'slides' ) ) {
        add_post_type_support( 'slides', 'page-attributes' );
    }
}, 20 );

add_action( 'wp_enqueue_scripts', 'audubon_features_enqueue', 20 );
function audubon_features_enqueue() {
    $base = get_template_directory_uri();
    wp_enqueue_style(
        'audubon-features',
        $base . '/assets/css/audubon-features.css',
        array(),
        AUDUBON_FEATURES_VERSION
    );
    wp_enqueue_script(
        'audubon-features',
        $base . '/assets/js/audubon-features.js',
        array(),
        AUDUBON_FEATURES_VERSION,
        true
    );
}

/* =============================================================
 * 2. メタボックス（追加フィールド）
 * ============================================================= */

add_action( 'add_meta_boxes', 'audubon_register_meta_boxes' );
function audubon_register_meta_boxes() {
    add_meta_box( 'audubon_actor_meta', 'アクター追加情報（プロフィールPDF / 最新の出演）',
        'audubon_render_actor_meta_box', 'actor', 'normal', 'high' );

    add_meta_box( 'audubon_news_meta', '表示日時（フリーテキスト）・出演アクター',
        'audubon_render_news_meta_box', 'news_list', 'normal', 'high' );

    add_meta_box( 'audubon_slide_meta', 'TOPICSスライダーの内容（ヘッダー / 出演者・タイトル / 本文 / リンク）',
        'audubon_render_slide_meta_box', 'slides', 'normal', 'high' );
}

function audubon_render_actor_meta_box( $post ) {
    wp_nonce_field( 'audubon_actor_meta', 'audubon_actor_meta_nonce' );

    $latest_info_id = get_post_meta( $post->ID, '_audubon_latest_information_id', true );
    $auto_latest    = get_post_meta( $post->ID, '_audubon_auto_latest_information', true );
    if ( $auto_latest === '' ) {
        $auto_latest = '1';
    }

    $news_items = get_posts( array(
        'post_type'      => 'news_list',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
    ?>
    <p>
        <label>
            <input type="checkbox" name="audubon_auto_latest_information" value="1" <?php checked( $auto_latest, '1' ); ?>>
            <strong>最新の出演（ニュース記事）を自動的にリンクする</strong>
        </label>
    </p>
    <p>
        <label><strong>手動で出演ニュース記事を指定する場合</strong></label><br>
        <select name="audubon_latest_information_id" style="width:100%;max-width:480px;">
            <option value="">— 選択しない（自動）—</option>
            <?php foreach ( $news_items as $news ) : ?>
                <option value="<?php echo esc_attr( $news->ID ); ?>" <?php selected( $latest_info_id, $news->ID ); ?>>
                    <?php echo esc_html( $news->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><span class="description">「自動」がONの場合は、このアクターが紐付けられたニュース記事のうち最新のものが表示されます。手動指定があればそちらが優先されます。</span>
    </p>
    <hr>
    <p style="color:#666;">
        <strong>プロフィールPDF:</strong> アクターページに表示される「プロフィールをPDFで保存」ボタンは、ブラウザの印刷機能を使ってこのプロフィールページの内容をそのままPDF化するものです。<br>
        手動でPDFを用意・アップロードする必要はありません。プロフィール本文を編集すれば、PDFの内容も自動で更新されます。
    </p>
    <?php
}

function audubon_render_news_meta_box( $post ) {
    wp_nonce_field( 'audubon_news_meta', 'audubon_news_meta_nonce' );
    $broadcast_text = get_post_meta( $post->ID, '_audubon_broadcast_text', true );
    $broadcast_sort = get_post_meta( $post->ID, '_audubon_broadcast_sort', true );

    $related_actors = get_post_meta( $post->ID, '_audubon_related_actors', true );
    if ( ! is_array( $related_actors ) ) {
        $related_actors = array();
    }
    $actors = get_posts( array(
        'post_type'      => 'actor',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );
    ?>
    <p style="margin:0 0 6px;">
        <label for="audubon_broadcast_text" style="font-size:14px;">
            <strong>表示日時（フリーテキスト）</strong>
        </label>
    </p>
    <p style="margin:0 0 6px;">
        <input type="text" id="audubon_broadcast_text" name="audubon_broadcast_text"
               value="<?php echo esc_attr( $broadcast_text ); ?>"
               style="width:100%;font-size:18px;padding:8px 10px;line-height:1.4;"
               placeholder="例: 2025年8月13日(水)21:00〜 / 毎週土曜 / 公開中 など">
    </p>
    <p style="margin:0 0 14px;color:#666;">
        この投稿の一覧・詳細で表示される日時テキストです。<strong>ここに入力した内容がそのまま表示されます</strong>。<br>空欄の場合は WordPress の投稿日にフォールバックします。
    </p>
    <p>
        <label for="audubon_broadcast_sort"><strong>並び替え用の日付（任意）</strong></label><br>
        <input type="date" id="audubon_broadcast_sort" name="audubon_broadcast_sort"
               value="<?php echo esc_attr( $broadcast_sort ); ?>" style="max-width:240px;">
        <span class="description">一覧での並び順を制御するための日付。空欄なら投稿日順で並びます。</span>
    </p>
    <hr>
    <p>
        <label><strong>出演アクター</strong></label><br>
        <select name="audubon_related_actors[]" multiple style="width:100%;height:140px;">
            <?php foreach ( $actors as $actor ) : ?>
                <option value="<?php echo esc_attr( $actor->ID ); ?>" <?php echo in_array( $actor->ID, $related_actors, true ) ? 'selected' : ''; ?>>
                    <?php echo esc_html( $actor->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="description">アクターページ「最新の出演」リンクの自動取得に使用されます。Ctrl/Cmd+クリックで複数選択。</span>
    </p>
    <?php
}

function audubon_render_slide_meta_box( $post ) {
    wp_nonce_field( 'audubon_slide_meta', 'audubon_slide_meta_nonce' );
    $heading     = get_post_meta( $post->ID, '_audubon_slide_heading', true );
    $actor_name  = get_post_meta( $post->ID, '_audubon_slide_actor_name', true );
    $description = get_post_meta( $post->ID, '_audubon_slide_description', true );
    $order       = (int) $post->menu_order; // 表示順は wp_posts.menu_order に保存
    $link        = get_post_meta( $post->ID, '_audubon_slide_link', true );
    // 旧テーマ（03）の caption が入っている場合は actor_name のフォールバックとして使う
    $legacy_caption = get_post_meta( $post->ID, '_audubon_slide_caption', true );
    if ( $actor_name === '' && $legacy_caption !== '' ) {
        $actor_name = $legacy_caption;
    }

    if ( $heading === '' ) {
        $heading = '出演情報';
    }
    ?>
    <p style="margin:0 0 18px;color:#666;">
        トップページの <strong>TOPICSスライダー</strong> に表示される情報です。<br>
        アイキャッチ画像（左側）+ ヘッダー / 出演者名 / 本文 / リンクボタン で1枚のカードとして表示されます。
    </p>

    <p style="margin:0 0 6px;">
        <label for="audubon_slide_order"><strong>表示順</strong>（数字が小さいほど先に表示されます）</label>
    </p>
    <p style="margin:0 0 14px;">
        <input type="number" id="audubon_slide_order" name="audubon_slide_order"
               value="<?php echo esc_attr( $order ); ?>" min="0" step="1"
               style="width:120px;font-size:16px;padding:7px 10px;text-align:right;">
        <span class="description" style="margin-left:8px;">
            例: 1, 2, 3...（同じ数字の場合は新しい投稿が先）。空欄/0 だと末尾に並びます。
        </span>
    </p>

    <hr style="margin:14px 0;">

    <p style="margin:0 0 6px;">
        <label for="audubon_slide_heading"><strong>カードヘッダー</strong>（例: 出演情報 / お知らせ / リリース 等）</label>
    </p>
    <p style="margin:0 0 14px;">
        <input type="text" id="audubon_slide_heading" name="audubon_slide_heading"
               value="<?php echo esc_attr( $heading ); ?>"
               style="width:100%;font-size:16px;padding:7px 10px;"
               placeholder="例: 出演情報">
    </p>

    <p style="margin:0 0 6px;">
        <label for="audubon_slide_actor_name"><strong>出演者・タイトルなど（カッコ内に表示）</strong></label>
    </p>
    <p style="margin:0 0 14px;">
        <input type="text" id="audubon_slide_actor_name" name="audubon_slide_actor_name"
               value="<?php echo esc_attr( $actor_name ); ?>"
               style="width:100%;font-size:16px;padding:7px 10px;"
               placeholder="例: 田村健太郎">
        <span class="description">「【 〜 】」の形で自動的に囲まれます。</span>
    </p>

    <p style="margin:0 0 6px;">
        <label for="audubon_slide_description"><strong>本文</strong></label>
    </p>
    <p style="margin:0 0 14px;">
        <textarea id="audubon_slide_description" name="audubon_slide_description"
                  rows="3" style="width:100%;font-size:14px;padding:7px 10px;line-height:1.6;"
                  placeholder="例: Netflixシリーズ「地獄に堕ちるわよ」に出演いたします。4月27日（月）より世界独占配信です。"><?php echo esc_textarea( $description ); ?></textarea>
    </p>

    <p style="margin:0 0 6px;">
    <p style="margin:0 0 6px;">
        <label for="audubon_slide_link"><strong>「詳細はコチラ」ボタンのリンク先URL</strong></label>
    </p>
    <p style="margin:0;">
        <input type="url" id="audubon_slide_link" name="audubon_slide_link"
               value="<?php echo esc_attr( $link ); ?>" style="width:100%;" placeholder="https://studio-audubon.jp/news_list/xxxxx">
        <span class="description">
            カードの「詳細はコチラ」ボタンを押したときに開くURLです。<br>
            例: 該当のニュース記事ページのURL（<code>https://studio-audubon.jp/news_list/...</code>）<br>
            空欄の場合は、このスライド投稿自身のページに自動でリンクします。
        </span>
    </p>
    <?php
    $link_blank = get_post_meta( $post->ID, '_audubon_slide_link_blank', true );
    ?>
    <p style="margin:8px 0 0;">
        <label>
            <input type="checkbox" name="audubon_slide_link_blank" value="1"
                   <?php checked( $link_blank, '1' ); ?>>
            別ウィンドウ（新しいタブ）で開く
        </label>
        <span class="description" style="display:block;margin-left:24px;color:#666;">
            外部サイトへのリンクなど、現在のページを離れずに開きたい場合にチェック。
        </span>
    </p>
    <?php
}

add_action( 'save_post', 'audubon_save_meta_boxes', 10, 2 );
function audubon_save_meta_boxes( $post_id, $post ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( $post->post_type === 'actor'
        && isset( $_POST['audubon_actor_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_actor_meta_nonce'], 'audubon_actor_meta' ) ) {

        update_post_meta( $post_id, '_audubon_latest_information_id',
            isset( $_POST['audubon_latest_information_id'] ) ? absint( $_POST['audubon_latest_information_id'] ) : 0 );
        update_post_meta( $post_id, '_audubon_auto_latest_information',
            ! empty( $_POST['audubon_auto_latest_information'] ) ? '1' : '0' );
    }

    if ( $post->post_type === 'news_list'
        && isset( $_POST['audubon_news_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_news_meta_nonce'], 'audubon_news_meta' ) ) {

        update_post_meta( $post_id, '_audubon_broadcast_text',
            isset( $_POST['audubon_broadcast_text'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_broadcast_text'] ) ) : '' );
        update_post_meta( $post_id, '_audubon_broadcast_sort',
            isset( $_POST['audubon_broadcast_sort'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_broadcast_sort'] ) ) : '' );

        $actors = isset( $_POST['audubon_related_actors'] ) && is_array( $_POST['audubon_related_actors'] )
            ? array_map( 'absint', $_POST['audubon_related_actors'] )
            : array();
        update_post_meta( $post_id, '_audubon_related_actors', $actors );
    }

    if ( $post->post_type === 'slides'
        && isset( $_POST['audubon_slide_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_slide_meta_nonce'], 'audubon_slide_meta' ) ) {

        update_post_meta( $post_id, '_audubon_slide_heading',
            isset( $_POST['audubon_slide_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_slide_heading'] ) ) : '' );
        update_post_meta( $post_id, '_audubon_slide_actor_name',
            isset( $_POST['audubon_slide_actor_name'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_slide_actor_name'] ) ) : '' );
        update_post_meta( $post_id, '_audubon_slide_description',
            isset( $_POST['audubon_slide_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['audubon_slide_description'] ) ) : '' );
        update_post_meta( $post_id, '_audubon_slide_link',
            isset( $_POST['audubon_slide_link'] ) ? esc_url_raw( wp_unslash( $_POST['audubon_slide_link'] ) ) : '' );
        update_post_meta( $post_id, '_audubon_slide_link_blank',
            ! empty( $_POST['audubon_slide_link_blank'] ) ? '1' : '0' );

        // 表示順は wp_posts.menu_order に保存。save_post の中で wp_update_post を
        // 呼ぶと再帰するので、フックを一度外してから戻す。
        if ( isset( $_POST['audubon_slide_order'] ) ) {
            $order = absint( wp_unslash( $_POST['audubon_slide_order'] ) );
            if ( (int) $post->menu_order !== $order ) {
                remove_action( 'save_post', 'audubon_save_meta_boxes', 10 );
                wp_update_post( array(
                    'ID'         => $post_id,
                    'menu_order' => $order,
                ) );
                add_action( 'save_post', 'audubon_save_meta_boxes', 10, 2 );
            }
        }
    }
}

/* =============================================================
 * 3. テンプレートタグ
 * ============================================================= */

function audubon_get_actor_latest_information( $actor_id = null ) {
    $actor_id = $actor_id ?: get_the_ID();

    $manual_id = (int) get_post_meta( $actor_id, '_audubon_latest_information_id', true );
    if ( $manual_id ) {
        $info = get_post( $manual_id );
        if ( $info && $info->post_status === 'publish' ) {
            return $info;
        }
    }
    $auto = get_post_meta( $actor_id, '_audubon_auto_latest_information', true );
    if ( $auto === '0' ) {
        return null;
    }

    // 関連アクターメタを持つnews_listを対象。broadcast_sortがあるものは優先、無ければ投稿日順。
    $query = new WP_Query( array(
        'post_type'      => 'news_list',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => '_audubon_related_actors',
                'value'   => sprintf( ':"%d";', $actor_id ),
                'compare' => 'LIKE',
            ),
            array(
                'relation'     => 'OR',
                'with_sort'    => array( 'key' => '_audubon_broadcast_sort', 'compare' => 'EXISTS' ),
                'without_sort' => array( 'key' => '_audubon_broadcast_sort', 'compare' => 'NOT EXISTS' ),
            ),
        ),
        'orderby'        => array(
            'with_sort' => 'DESC',
            'date'      => 'DESC',
        ),
    ) );
    return $query->have_posts() ? $query->posts[0] : null;
}

/**
 * ニュース投稿の表示日時。
 * 編集画面のフリーテキスト（_audubon_broadcast_text）が入っていればそれを使い、
 * 未入力の場合は投稿の作成日にフォールバックします。
 *
 * 第2引数 $format はフォールバック時の投稿日のフォーマット（省略時はWP設定の date_format）。
 * フリーテキスト側にはフォーマットは適用しません（入力そのままを返します）。
 */
function audubon_get_news_display_date( $post_id = null, $format = '' ) {
    $post_id = $post_id ?: get_the_ID();
    $text    = (string) get_post_meta( $post_id, '_audubon_broadcast_text', true );
    if ( $text !== '' ) {
        return $text;
    }
    $format = $format ?: get_option( 'date_format' );
    return get_the_date( $format, $post_id );
}

/**
 * アクター用「最新の出演」リンク + 「プロフィールをPDFで保存」ボタンのHTML出力。
 *
 * PDFはブラウザの印刷機能でこのプロフィールページの内容をそのままPDF化する形式です。
 * （手動でPDFをアップロードする必要はありません）
 *
 * single-actor.php から呼び出します。
 */
function audubon_render_actor_links( $actor_id = null ) {
    $actor_id = $actor_id ?: get_the_ID();
    if ( ! $actor_id ) {
        return;
    }

    $info = audubon_get_actor_latest_information( $actor_id );
    ?>
    <div class="audubon-actor-links">
        <?php if ( $info ) :
            $date = audubon_get_news_display_date( $info->ID );
            ?>
            <a class="audubon-actor-links__information" href="<?php echo esc_url( get_permalink( $info ) ); ?>">
                <span class="audubon-actor-links__label">最新の出演</span>
                <span class="audubon-actor-links__title"><?php echo esc_html( get_the_title( $info ) ); ?></span>
                <?php if ( $date ) : ?><span class="audubon-actor-links__date"><?php echo esc_html( $date ); ?></span><?php endif; ?>
            </a>
        <?php endif; ?>

        <?php /* PDF保存ボタンは廃止 */ ?>
    </div>
    <?php
}

/**
 * トップページ用 TOPICSスライダー（slides CPT）。
 * 2列のカード構成（A4ポスター + ヘッダー / 出演者名 / 本文 / 詳細リンク）。
 * index.php から呼び出します。
 *
 * 関数名は studio_audubon_03 互換のため audubon_render_slides_banner のまま。
 */
function audubon_render_slides_banner() {
    $slides = get_posts( array(
        'post_type'      => 'slides',
        'posts_per_page' => -1,
        'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
        'post_status'    => 'publish',
    ) );
    if ( empty( $slides ) ) {
        return;
    }
    ?>
    <section id="topics" class="audubon-topics" data-visible="2" data-interval="2000">
        <div class="contents audubon-topics__inner">
            <h2 class="section-title">Topics</h2>

            <div class="audubon-topics__viewport-wrap">
                <button class="audubon-topics__nav audubon-topics__nav--prev" aria-label="前へ">
                    <span aria-hidden="true">‹</span>
                </button>

                <div class="audubon-topics__viewport">
                    <ul class="audubon-topics__track">
                        <?php foreach ( $slides as $slide ) :
                            $heading     = get_post_meta( $slide->ID, '_audubon_slide_heading', true );
                            $actor_name  = get_post_meta( $slide->ID, '_audubon_slide_actor_name', true );
                            $description = get_post_meta( $slide->ID, '_audubon_slide_description', true );
                            $link        = get_post_meta( $slide->ID, '_audubon_slide_link', true );
                            // 旧フィールド（caption）からのフォールバック
                            if ( $actor_name === '' ) {
                                $legacy = get_post_meta( $slide->ID, '_audubon_slide_caption', true );
                                if ( $legacy !== '' ) {
                                    $actor_name = $legacy;
                                }
                            }
                            if ( $heading === '' ) {
                                $heading = '出演情報';
                            }
                            $thumb = get_the_post_thumbnail( $slide->ID, 'large',
                                array( 'class' => 'audubon-topics__image' ) );
                            if ( ! $thumb ) {
                                continue;
                            }
                            ?>
                            <li class="audubon-topics__item">
                                <article class="audubon-topics__card">
                                    <div class="audubon-topics__media">
                                        <?php echo $thumb; ?>
                                    </div>
                                    <div class="audubon-topics__body">
                                        <p class="audubon-topics__heading"><?php echo esc_html( $heading ); ?></p>
                                        <?php if ( $actor_name ) : ?>
                                            <p class="audubon-topics__actor">【 <?php echo esc_html( $actor_name ); ?> 】</p>
                                        <?php endif; ?>
                                        <?php if ( $description ) : ?>
                                            <p class="audubon-topics__description"><?php echo nl2br( esc_html( $description ) ); ?></p>
                                        <?php endif; ?>
                                        <?php
                                        // 「詳細はコチラ」ボタンは常時表示。
                                        // 編集画面でリンク先URLが設定されていればそれを使い、
                                        // 空欄ならスライド投稿自身のパーマリンクにフォールバック。
                                        $cta_url   = $link ?: get_permalink( $slide );
                                        $cta_blank = get_post_meta( $slide->ID, '_audubon_slide_link_blank', true ) === '1';
                                        ?>
                                        <?php if ( $cta_url ) : ?>
                                            <p class="audubon-topics__cta-wrap">
                                                <a class="audubon-topics__cta" href="<?php echo esc_url( $cta_url ); ?>"
                                                   <?php if ( $cta_blank ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                                                    <span class="audubon-topics__cta-arrow" aria-hidden="true">›</span>
                                                    詳細はコチラ
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <button class="audubon-topics__nav audubon-topics__nav--next" aria-label="次へ">
                    <span aria-hidden="true">›</span>
                </button>
            </div>

            <ol class="audubon-topics__dots" aria-label="ページ送り"></ol>
        </div>
    </section>
    <?php
}

/* =============================================================
 * 4. 管理画面の一覧列
 * ============================================================= */

add_filter( 'manage_news_list_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['audubon_broadcast_text'] = '放映日時';
            $new['audubon_broadcast_sort'] = '並び替え用日付';
        }
    }
    return $new;
} );
add_action( 'manage_news_list_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'audubon_broadcast_text' ) {
        echo esc_html( get_post_meta( $post_id, '_audubon_broadcast_text', true ) );
    }
    if ( $column === 'audubon_broadcast_sort' ) {
        echo esc_html( get_post_meta( $post_id, '_audubon_broadcast_sort', true ) );
    }
}, 10, 2 );

add_filter( 'manage_slides_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['audubon_slide_order']   = '表示順';
            $new['audubon_slide_heading'] = 'ヘッダー';
            $new['audubon_slide_actor']   = '出演者・タイトル';
        }
    }
    return $new;
} );

// 表示順カラムをソート可能に
add_filter( 'manage_edit-slides_sortable_columns', function ( $columns ) {
    $columns['audubon_slide_order'] = 'menu_order';
    return $columns;
} );

// 一覧画面のデフォルト並びを表示順に
add_action( 'pre_get_posts', function ( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) return;
    if ( $query->get( 'post_type' ) === 'slides' && ! $query->get( 'orderby' ) ) {
        $query->set( 'orderby', 'menu_order' );
        $query->set( 'order', 'ASC' );
    }
} );

add_action( 'manage_slides_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'audubon_slide_order' ) {
        $p = get_post( $post_id );
        echo esc_html( (int) ( $p ? $p->menu_order : 0 ) );
    }
    if ( $column === 'audubon_slide_heading' ) {
        echo esc_html( get_post_meta( $post_id, '_audubon_slide_heading', true ) );
    }
    if ( $column === 'audubon_slide_actor' ) {
        $actor = get_post_meta( $post_id, '_audubon_slide_actor_name', true );
        if ( $actor === '' ) {
            $actor = get_post_meta( $post_id, '_audubon_slide_caption', true ); // 旧データのフォールバック
        }
        echo esc_html( $actor );
    }
}, 10, 2 );

add_filter( 'manage_actor_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['audubon_actor_news'] = '最新の出演';
        }
    }
    return $new;
} );
add_action( 'manage_actor_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'audubon_actor_news' ) {
        $info = audubon_get_actor_latest_information( $post_id );
        if ( $info ) {
            printf( '<a href="%s">%s</a>',
                esc_url( get_edit_post_link( $info->ID ) ),
                esc_html( get_the_title( $info ) ) );
        } else {
            echo '—';
        }
    }
}, 10, 2 );

/* =============================================================
 * 05新機能
 * ============================================================= */

/* -------------------------------------------------------------
 * （旧 A-1/B-6 アクター属性タクソノミー（性別 / 年代 / ジャンル /
 *  得意分野）の登録は削除しました。アクター編集画面の右サイドバーに
 *  あった分類用ボックスは表示されなくなります。
 * ------------------------------------------------------------- */

/* -------------------------------------------------------------
 * （旧 A-1 アクター絞り込みUI / pre_get_posts は削除しました。
 *   タクソノミー定義は残しているため、管理画面でのアクター分類は
 *   引き続き使用可能です。フロントの絞り込みフォームは出力されません）
 * ------------------------------------------------------------- */

/* -------------------------------------------------------------
 * A-3: 出演実績の構造化メタボックス
 *  - _audubon_credits[] = [ media, title, role, director, year, link ]
 * ------------------------------------------------------------- */
add_action( 'add_meta_boxes', 'audubon_register_credits_meta_box' );
function audubon_register_credits_meta_box() {
    add_meta_box(
        'audubon_credits_meta',
        '出演実績（構造化）',
        'audubon_render_credits_meta_box',
        'actor',
        'normal',
        'default'
    );
}
function audubon_render_credits_meta_box( $post ) {
    wp_nonce_field( 'audubon_credits_meta', 'audubon_credits_meta_nonce' );
    $credits = get_post_meta( $post->ID, '_audubon_credits', true );
    if ( ! is_array( $credits ) ) {
        $credits = array();
    }
    if ( empty( $credits ) ) {
        $credits = array( array() ); // 空の行を1つ表示
    }
    $media_options = array( '舞台', '映画', 'テレビ', 'CM', '配信', 'ラジオ', 'その他' );
    ?>
    <p style="color:#666;margin:0 0 10px;">
        媒体・作品名・役名・演出/監督・公開年・関連リンクを項目別に登録できます。<br>
        絞り込み・出演ジャンル別表示に利用されます。
    </p>
    <table class="widefat audubon-credits" id="audubon-credits-table">
        <thead>
            <tr>
                <th style="width:90px;">媒体</th>
                <th>作品名</th>
                <th style="width:140px;">役名</th>
                <th style="width:140px;">演出/監督</th>
                <th style="width:80px;">公開年</th>
                <th>関連リンク</th>
                <th style="width:36px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $credits as $i => $c ) :
                $c = wp_parse_args( $c, array(
                    'media' => '', 'title' => '', 'role' => '', 'director' => '', 'year' => '', 'link' => '',
                ) );
                ?>
                <tr class="audubon-credits__row">
                    <td>
                        <select name="audubon_credits[<?php echo $i; ?>][media]">
                            <option value="">—</option>
                            <?php foreach ( $media_options as $m ) : ?>
                                <option value="<?php echo esc_attr( $m ); ?>" <?php selected( $c['media'], $m ); ?>><?php echo esc_html( $m ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="audubon_credits[<?php echo $i; ?>][title]"    value="<?php echo esc_attr( $c['title'] ); ?>" style="width:100%;"></td>
                    <td><input type="text" name="audubon_credits[<?php echo $i; ?>][role]"     value="<?php echo esc_attr( $c['role'] ); ?>" style="width:100%;"></td>
                    <td><input type="text" name="audubon_credits[<?php echo $i; ?>][director]" value="<?php echo esc_attr( $c['director'] ); ?>" style="width:100%;"></td>
                    <td><input type="text" name="audubon_credits[<?php echo $i; ?>][year]"     value="<?php echo esc_attr( $c['year'] ); ?>" style="width:100%;" placeholder="2025"></td>
                    <td><input type="url"  name="audubon_credits[<?php echo $i; ?>][link]"     value="<?php echo esc_attr( $c['link'] ); ?>" style="width:100%;" placeholder="https://..."></td>
                    <td><button type="button" class="button audubon-credits__remove" aria-label="行を削除">×</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><button type="button" class="button button-secondary" id="audubon-credits-add">＋行を追加</button></p>
    <script>
    (function(){
        var table = document.getElementById('audubon-credits-table');
        var addBtn = document.getElementById('audubon-credits-add');
        if (!table || !addBtn) return;
        addBtn.addEventListener('click', function(){
            var tbody = table.querySelector('tbody');
            var newIndex = tbody.children.length;
            var firstRow = tbody.children[0];
            if (!firstRow) return;
            var clone = firstRow.cloneNode(true);
            clone.querySelectorAll('input, select').forEach(function(el){
                el.value = '';
                if (el.name) el.name = el.name.replace(/\[\d+\]/, '['+newIndex+']');
            });
            tbody.appendChild(clone);
        });
        table.addEventListener('click', function(e){
            var btn = e.target.closest('.audubon-credits__remove');
            if (!btn) return;
            var row = btn.closest('tr');
            if (table.querySelectorAll('tbody tr').length <= 1) {
                row.querySelectorAll('input, select').forEach(function(el){ el.value=''; });
            } else {
                row.parentNode.removeChild(row);
            }
        });
    })();
    </script>
    <?php
}

add_action( 'save_post_actor', 'audubon_save_credits_meta', 10, 1 );
function audubon_save_credits_meta( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( ! isset( $_POST['audubon_credits_meta_nonce'] )
        || ! wp_verify_nonce( $_POST['audubon_credits_meta_nonce'], 'audubon_credits_meta' ) ) {
        return;
    }
    $rows = isset( $_POST['audubon_credits'] ) && is_array( $_POST['audubon_credits'] )
        ? $_POST['audubon_credits'] : array();
    $cleaned = array();
    foreach ( $rows as $row ) {
        $row = wp_parse_args( $row, array(
            'media' => '', 'title' => '', 'role' => '', 'director' => '', 'year' => '', 'link' => '',
        ) );
        // 全部空ならスキップ
        if ( implode( '', array_map( 'trim', $row ) ) === '' ) {
            continue;
        }
        $cleaned[] = array(
            'media'    => sanitize_text_field( wp_unslash( $row['media'] ) ),
            'title'    => sanitize_text_field( wp_unslash( $row['title'] ) ),
            'role'     => sanitize_text_field( wp_unslash( $row['role'] ) ),
            'director' => sanitize_text_field( wp_unslash( $row['director'] ) ),
            'year'     => sanitize_text_field( wp_unslash( $row['year'] ) ),
            'link'     => esc_url_raw( wp_unslash( $row['link'] ) ),
        );
    }
    update_post_meta( $post_id, '_audubon_credits', $cleaned );
}

/**
 * 構造化された出演実績テーブルを表示（single-actor.php から呼び出し）
 */
function audubon_render_actor_credits( $actor_id = null ) {
    $actor_id = $actor_id ?: get_the_ID();
    $credits  = get_post_meta( $actor_id, '_audubon_credits', true );
    if ( ! is_array( $credits ) || empty( $credits ) ) {
        return;
    }
    // 媒体ごとにグループ化
    $by_media = array();
    foreach ( $credits as $c ) {
        $m = $c['media'] ?: 'その他';
        $by_media[ $m ][] = $c;
    }
    ?>
    <div class="audubon-credits-display">
        <h4 class="actor-work-heading mt-3 mb-3">出演実績</h4>
        <?php foreach ( $by_media as $media => $items ) : ?>
            <div class="audubon-credits-display__group">
                <h5 class="audubon-credits-display__media"><?php echo esc_html( $media ); ?></h5>
                <ul class="audubon-credits-display__list">
                    <?php foreach ( $items as $c ) : ?>
                        <li class="audubon-credits-display__item">
                            <?php if ( $c['year'] ) : ?>
                                <span class="audubon-credits-display__year"><?php echo esc_html( $c['year'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $c['link'] ) : ?>
                                <a href="<?php echo esc_url( $c['link'] ); ?>" target="_blank" rel="noopener">
                            <?php endif; ?>
                                <span class="audubon-credits-display__title"><?php echo esc_html( $c['title'] ); ?></span>
                            <?php if ( $c['link'] ) : ?></a><?php endif; ?>
                            <?php if ( $c['role'] ) : ?>
                                <span class="audubon-credits-display__role">役 / <?php echo esc_html( $c['role'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $c['director'] ) : ?>
                                <span class="audubon-credits-display__director">演出・監督 / <?php echo esc_html( $c['director'] ); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/* -------------------------------------------------------------
 * （旧 A-4 アクター詳細「お問い合わせ」CTA は削除しました）
 * ------------------------------------------------------------- */

/* -------------------------------------------------------------
 * B-2: Pickup を動的化（CPT: audubon_pickup）
 * ------------------------------------------------------------- */
add_action( 'init', 'audubon_register_pickup_cpt' );
function audubon_register_pickup_cpt() {
    register_post_type( 'audubon_pickup', array(
        'labels' => array(
            'name'          => 'Pickup',
            'singular_name' => 'Pickup',
            'menu_name'     => 'Pickup',
            'add_new'       => '新規追加',
            'add_new_item'  => '新規Pickupを追加',
            'edit_item'     => 'Pickupを編集',
        ),
        // 独立した一覧ページ /pickup/ を持たせる（archive-audubon_pickup.php が使われる）
        'public'              => true,
        'has_archive'         => 'pickup',
        'rewrite'             => array( 'slug' => 'pickup', 'with_front' => false ),
        'exclude_from_search' => true, // サイト内検索の結果には出さない（販促枠のため）
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-star-filled',
        'menu_position'       => 7,
        'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
    ) );
}

/**
 * /pickup/ アーカイブの並び順：管理画面の「表示順」(menu_order) の小さい順、
 * 同じなら新しい順。全件表示。
 */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) return;
    if ( $q->is_post_type_archive( 'audubon_pickup' ) ) {
        $q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
        $q->set( 'posts_per_page', -1 );
    }
} );

/**
 * /pickup/ を有効にするためのリライトルール再生成（初回アクセス時に一度だけ）。
 * 手動で「設定 > パーマリンク > 変更を保存」しても同じ効果があります。
 */
add_action( 'init', function () {
    if ( get_option( 'audubon_pickup_rewrite_v' ) !== '2' ) {
        flush_rewrite_rules();
        update_option( 'audubon_pickup_rewrite_v', '2' );
    }
}, 99 );

add_action( 'add_meta_boxes', function () {
    add_meta_box( 'audubon_pickup_meta', 'Pickup設定（YouTube埋込・リンク）',
        'audubon_render_pickup_meta_box', 'audubon_pickup', 'normal', 'high' );
} );

function audubon_render_pickup_meta_box( $post ) {
    wp_nonce_field( 'audubon_pickup_meta', 'audubon_pickup_meta_nonce' );
    $youtube  = get_post_meta( $post->ID, '_audubon_pickup_youtube', true );
    $link     = get_post_meta( $post->ID, '_audubon_pickup_link', true );
    ?>
    <p style="color:#666;margin:0 0 10px;">
        <strong>画像で表示する場合</strong>: 右側の「アイキャッチ画像」を設定してください（<strong>縦・横・正方形どの比率でもそのまま表示</strong>されます。切り取りはされません）。<br>
        <strong>クリックで別ページに飛ばす場合</strong>: 下の「リンク先URL」を入力すると、画像タップでそのURLへ移動します。<br>
        <strong>YouTube動画を表示する場合</strong>: 下のフィールドに iframe コードまたは YouTube URL を入力してください（画像が無くてもOK）。
    </p>
    <p>
        <label for="audubon_pickup_link"><strong>クリック時のリンク先URL（任意）</strong></label><br>
        <input type="url" id="audubon_pickup_link" name="audubon_pickup_link"
               value="<?php echo esc_attr( $link ); ?>" style="width:100%;" placeholder="https://...">
    </p>
    <p>
        <label for="audubon_pickup_youtube"><strong>YouTube埋込コード または URL（任意）</strong></label><br>
        <textarea id="audubon_pickup_youtube" name="audubon_pickup_youtube" rows="3" style="width:100%;font-family:monospace;"
                  placeholder="https://www.youtube.com/watch?v=... または iframeコード"><?php echo esc_textarea( $youtube ); ?></textarea>
    </p>
    <?php
}

add_action( 'save_post_audubon_pickup', function ( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( ! isset( $_POST['audubon_pickup_meta_nonce'] )
        || ! wp_verify_nonce( $_POST['audubon_pickup_meta_nonce'], 'audubon_pickup_meta' ) ) {
        return;
    }
    update_post_meta( $post_id, '_audubon_pickup_youtube',
        isset( $_POST['audubon_pickup_youtube'] ) ? wp_kses( wp_unslash( $_POST['audubon_pickup_youtube'] ),
            array( 'iframe' => array( 'src' => true, 'width' => true, 'height' => true, 'frameborder' => true,
                'allow' => true, 'allowfullscreen' => true, 'title' => true ) ) ) : '' );
    update_post_meta( $post_id, '_audubon_pickup_link',
        isset( $_POST['audubon_pickup_link'] ) ? esc_url_raw( wp_unslash( $_POST['audubon_pickup_link'] ) ) : '' );
} );

/**
 * Pickupセクションの動的レンダリング（index.php から呼び出し）
 * 既存ACFフィールドが残っていればフォールバックとして表示するため、index.php側は段階的に切替可能。
 */
function audubon_render_pickup_section() {
    $items = get_posts( array(
        'post_type'      => 'audubon_pickup',
        'posts_per_page' => -1,
        'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
        'post_status'    => 'publish',
    ) );
    if ( empty( $items ) ) {
        return false; // 0件 → 呼び出し側で旧ACFのフォールバック表示
    }
    ?>
    <div class="audubon-pickup">
        <?php foreach ( $items as $item ) :
            $youtube = get_post_meta( $item->ID, '_audubon_pickup_youtube', true );
            $link    = get_post_meta( $item->ID, '_audubon_pickup_link', true );
            $thumb   = get_the_post_thumbnail( $item->ID, 'large', array( 'loading' => 'lazy' ) );
            ?>
            <figure class="audubon-pickup__item">
                <?php if ( $youtube ) : ?>
                    <?php
                    // URLだけならiframeに変換
                    if ( preg_match( '#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/))([\w-]+)#', $youtube, $m ) ) {
                        $vid = esc_attr( $m[1] );
                        echo '<iframe src="https://www.youtube.com/embed/' . $vid . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
                    } else {
                        // すでにiframe等の場合はそのまま
                        echo wp_kses_post( $youtube );
                    }
                    ?>
                <?php elseif ( $thumb ) :
                    if ( $link ) echo '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">';
                    echo $thumb;
                    if ( $link ) echo '</a>';
                endif; ?>
                <?php if ( get_the_title( $item ) ) : ?>
                    <figcaption class="audubon-pickup__caption"><?php echo esc_html( get_the_title( $item ) ); ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endforeach; ?>
    </div>
    <?php
    return true;
}

/* -------------------------------------------------------------
 * F-2: すべての <img> に loading="lazy" を強制適用
 *  WP 5.5+ ではデフォルトで content imageに付与されますが、
 *  一部テンプレート出力には付かないため、フィルタで補完。
 * ------------------------------------------------------------- */
add_filter( 'wp_lazy_loading_enabled', '__return_true' );
add_filter( 'the_content', function ( $html ) {
    if ( is_admin() ) return $html;
    return preg_replace_callback( '/<img(?![^>]*\bloading=)([^>]*)>/i', function ( $m ) {
        return '<img loading="lazy"' . $m[1] . '>';
    }, $html );
}, 99 );

/**
 * ニュース記事に紐付くアクター名リンクを 1行で出力。
 *  _audubon_related_actors（出演アクターのID配列）を参照。
 *  Information の 3行構成（日付・タイトル・出演者）の3行目に使用。
 */
function audubon_render_news_actors_links( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    $actor_ids = get_post_meta( $post_id, '_audubon_related_actors', true );
    if ( ! is_array( $actor_ids ) || empty( $actor_ids ) ) {
        return;
    }
    $links = array();
    foreach ( $actor_ids as $actor_id ) {
        $actor = get_post( (int) $actor_id );
        if ( ! $actor || $actor->post_status !== 'publish' ) continue;
        $links[] = sprintf(
            '<a href="%s" class="news-actor-link">%s</a>',
            esc_url( get_permalink( $actor ) ),
            esc_html( get_the_title( $actor ) )
        );
    }
    if ( empty( $links ) ) return;
    echo '<p class="news-actors">' . implode( ' / ', $links ) . '</p>';
}


/**
 * ヘッダー（およびフッター）ナビゲーションの "News" メニュー項目を
 * "Information" に置き換え。
 * 管理画面 > 外観 > メニュー の「Navigation Label」を直接書き換えなくても、
 * テーマ側で表示文字を上書きします。
 */
add_filter( 'nav_menu_item_title', 'audubon_rename_news_to_info', 10, 2 );
function audubon_rename_news_to_info( $title, $item ) {
    if ( strcasecmp( trim( $title ), 'News' ) === 0 ) {
        return 'Information';
    }
    return $title;
}

/**
 * About ページの会社概要テーブルに「代表取締役 谷渕 寿江」行を自動挿入。
 * - 「◎会社概要」or `company-info-heading` がページ本文に含まれていれば About と判定
 * - 既に「代表取締役」が入っていれば何もしない（多重挿入回避）
 * - 行を入れる位置: 既存の「本店」行の直前
 */
add_filter( 'the_content', function ( $content ) {
    if ( is_admin() || ! is_singular() ) {
        return $content;
    }
    if ( strpos( $content, '◎会社概要' ) === false && strpos( $content, 'company-info-heading' ) === false ) {
        return $content;
    }
    if ( strpos( $content, '代表取締役' ) !== false ) {
        return $content; // 既に手動で入れている場合は触らない
    }
    $new_row = "<tr><th>代表取締役</th><td>谷渕 寿江</td></tr>\n";
    $content = preg_replace(
        '/(<tr>\s*<th>\s*本店\s*<\/th>)/u',
        $new_row . '$1',
        $content,
        1
    );
    return $content;
}, 20 );
