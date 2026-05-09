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
        この投稿の一覧・詳細で表示される日時テキストです。<strong>ここに入力した内容がそのまま表示されます</strong>（投稿の作成日時は使われません）。空欄の場合は日時表示なし。
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
        <label for="audubon_slide_link"><strong>「詳しくはこちらから」のリンク先URL</strong></label>
    </p>
    <p style="margin:0;">
        <input type="url" id="audubon_slide_link" name="audubon_slide_link"
               value="<?php echo esc_attr( $link ); ?>" style="width:100%;" placeholder="https://...">
        <span class="description">空欄の場合は「詳しくはこちらから」ボタンを表示しません。</span>
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
 * フリーテキストで入力された値のみを返し、未入力なら空文字を返します。
 * （投稿の作成日へのフォールバックは行いません）
 *
 * 第2引数 $format は後方互換のために受け付けますが、フリーテキストに対しては適用されません。
 */
function audubon_get_news_display_date( $post_id = null, $format = '' ) {
    $post_id = $post_id ?: get_the_ID();
    return (string) get_post_meta( $post_id, '_audubon_broadcast_text', true );
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

        <button type="button" class="audubon-actor-links__pdf" data-audubon-print="1">
            <span class="audubon-actor-links__icon" aria-hidden="true">⬇</span>
            プロフィールをPDFで保存
        </button>
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
    <section id="topics" class="audubon-topics" data-visible="2" data-interval="5500">
        <div class="contents audubon-topics__inner">
            <h2 class="section-title">TOPICS</h2>

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
                                        <?php if ( $link ) : ?>
                                            <p class="audubon-topics__cta-wrap">
                                                <a class="audubon-topics__cta" href="<?php echo esc_url( $link ); ?>">
                                                    <span class="audubon-topics__cta-arrow" aria-hidden="true">›</span>
                                                    詳しくはこちらから
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
            $new['audubon_slide_heading'] = 'ヘッダー';
            $new['audubon_slide_actor']   = '出演者・タイトル';
        }
    }
    return $new;
} );
add_action( 'manage_slides_posts_custom_column', function ( $column, $post_id ) {
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
 * A-1 / B-6: アクター絞り込み用タクソノミー
 *  - audubon_actor_gender   : 性別（男性/女性/その他）
 *  - audubon_actor_age      : 年代（10代-60代以上）
 *  - audubon_actor_genre    : ジャンル（俳優/声優/モデル/文化人 等）
 *  - audubon_actor_specialty: 得意分野（舞台/映画/TV/CM/朗読 等）
 * ------------------------------------------------------------- */
add_action( 'init', 'audubon_register_actor_taxonomies' );
function audubon_register_actor_taxonomies() {
    if ( ! post_type_exists( 'actor' ) ) {
        return;
    }

    $base = array(
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical'      => true,
        'show_in_menu'      => true,
        'show_ui'           => true,
    );

    register_taxonomy( 'audubon_actor_gender', 'actor', array_merge( $base, array(
        'label'        => '性別',
        'rewrite'      => array( 'slug' => 'actor-gender' ),
    ) ) );
    register_taxonomy( 'audubon_actor_age', 'actor', array_merge( $base, array(
        'label'   => '年代',
        'rewrite' => array( 'slug' => 'actor-age' ),
    ) ) );
    register_taxonomy( 'audubon_actor_genre', 'actor', array_merge( $base, array(
        'label'   => 'ジャンル',
        'rewrite' => array( 'slug' => 'actor-genre' ),
    ) ) );
    register_taxonomy( 'audubon_actor_specialty', 'actor', array_merge( $base, array(
        'label'   => '得意分野',
        'rewrite' => array( 'slug' => 'actor-specialty' ),
    ) ) );

    // 初期ターム（存在しない場合のみ追加）
    audubon_seed_terms( 'audubon_actor_gender',    array( '男性', '女性', 'その他' ) );
    audubon_seed_terms( 'audubon_actor_age',       array( '10代', '20代', '30代', '40代', '50代', '60代以上' ) );
    audubon_seed_terms( 'audubon_actor_genre',     array( '俳優', '声優', 'モデル', '文化人', 'タレント', 'その他' ) );
    audubon_seed_terms( 'audubon_actor_specialty', array( '舞台', '映画', 'テレビ', 'CM', '朗読', '吹替', 'ナレーション' ) );
}

function audubon_seed_terms( $taxonomy, $names ) {
    if ( get_option( 'audubon_seeded_' . $taxonomy ) ) {
        return;
    }
    foreach ( $names as $name ) {
        if ( ! term_exists( $name, $taxonomy ) ) {
            wp_insert_term( $name, $taxonomy );
        }
    }
    update_option( 'audubon_seeded_' . $taxonomy, '1' );
}

/**
 * アクター絞り込み フィルターUIをショートコードで提供
 *   [audubon_actor_filter]
 */
add_shortcode( 'audubon_actor_filter', 'audubon_actor_filter_shortcode' );
function audubon_actor_filter_shortcode() {
    $taxonomies = array(
        'audubon_actor_gender'    => '性別',
        'audubon_actor_age'       => '年代',
        'audubon_actor_genre'     => 'ジャンル',
        'audubon_actor_specialty' => '得意分野',
    );

    ob_start();
    ?>
    <form class="audubon-actor-filter" method="get">
        <?php foreach ( $taxonomies as $tax => $label ) :
            $terms = get_terms( array(
                'taxonomy'   => $tax,
                'hide_empty' => false,
            ) );
            if ( empty( $terms ) || is_wp_error( $terms ) ) continue;
            $current = isset( $_GET[ $tax ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ) : '';
            ?>
            <label class="audubon-actor-filter__field">
                <span class="audubon-actor-filter__label"><?php echo esc_html( $label ); ?></span>
                <select name="<?php echo esc_attr( $tax ); ?>">
                    <option value="">すべて</option>
                    <?php foreach ( $terms as $term ) : ?>
                        <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current, $term->slug ); ?>>
                            <?php echo esc_html( $term->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endforeach; ?>
        <button type="submit" class="audubon-actor-filter__submit">絞り込む</button>
        <a class="audubon-actor-filter__reset" href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'] ?? '/', '?' ) ); ?>">リセット</a>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * 絞り込みクエリ変数を main query に反映（page-actor.php / page-actor-parent.php 用）
 */
add_action( 'pre_get_posts', 'audubon_apply_actor_filter_query' );
function audubon_apply_actor_filter_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->get( 'post_type' ) !== 'actor' ) {
        return;
    }
    $tax_query = array();
    foreach ( array( 'audubon_actor_gender', 'audubon_actor_age', 'audubon_actor_genre', 'audubon_actor_specialty' ) as $tax ) {
        if ( ! empty( $_GET[ $tax ] ) ) {
            $tax_query[] = array(
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ),
            );
        }
    }
    if ( count( $tax_query ) > 1 ) {
        $tax_query['relation'] = 'AND';
    }
    if ( ! empty( $tax_query ) ) {
        $query->set( 'tax_query', $tax_query );
    }
}

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
 * A-4: アクター詳細「お問い合わせ」CTA
 * audubon_render_actor_links() を拡張 — Contact ページへリンク
 * ------------------------------------------------------------- */
function audubon_render_actor_inquiry_button( $actor_id = null ) {
    $actor_id   = $actor_id ?: get_the_ID();
    $actor_name = get_the_title( $actor_id );
    $contact_url = home_url( '/contact/' );
    $url = add_query_arg( 'actor', rawurlencode( $actor_name ), $contact_url );
    ?>
    <a class="audubon-actor-links__inquiry" href="<?php echo esc_url( $url ); ?>">
        <span class="audubon-actor-links__label">CASTING</span>
        <span class="audubon-actor-links__title"><?php echo esc_html( $actor_name ); ?>についてお問い合わせ</span>
    </a>
    <?php
}

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
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-star-filled',
        'menu_position' => 7,
        'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
    ) );
}

add_action( 'add_meta_boxes', function () {
    add_meta_box( 'audubon_pickup_meta', 'Pickup設定（YouTube埋込・リンク）',
        'audubon_render_pickup_meta_box', 'audubon_pickup', 'normal', 'high' );
} );

function audubon_render_pickup_meta_box( $post ) {
    wp_nonce_field( 'audubon_pickup_meta', 'audubon_pickup_meta_nonce' );
    $youtube = get_post_meta( $post->ID, '_audubon_pickup_youtube', true );
    $link    = get_post_meta( $post->ID, '_audubon_pickup_link', true );
    ?>
    <p style="color:#666;margin:0 0 10px;">
        <strong>画像で表示する場合</strong>: アイキャッチ画像を設定してください。<br>
        <strong>YouTube動画を表示する場合</strong>: 下のフィールドに iframe コードまたは YouTube URL を入力してください（画像が無くてもOK）。
    </p>
    <p>
        <label for="audubon_pickup_youtube"><strong>YouTube埋込コード または URL（任意）</strong></label><br>
        <textarea id="audubon_pickup_youtube" name="audubon_pickup_youtube" rows="3" style="width:100%;font-family:monospace;"
                  placeholder="https://www.youtube.com/watch?v=... または iframeコード"><?php echo esc_textarea( $youtube ); ?></textarea>
    </p>
    <p>
        <label for="audubon_pickup_link"><strong>クリック時のリンク先URL（任意）</strong></label><br>
        <input type="url" id="audubon_pickup_link" name="audubon_pickup_link"
               value="<?php echo esc_attr( $link ); ?>" style="width:100%;" placeholder="https://...">
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
            <figure class="audubon-pickup__item image-wrapper">
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
                    if ( $link ) echo '<a href="' . esc_url( $link ) . '">';
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
