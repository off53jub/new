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
