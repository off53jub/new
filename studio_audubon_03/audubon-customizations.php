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

add_action( 'admin_enqueue_scripts', 'audubon_features_admin_enqueue' );
function audubon_features_admin_enqueue( $hook ) {
    global $post;
    if ( ( $hook === 'post.php' || $hook === 'post-new.php' )
        && $post && $post->post_type === 'actor' ) {
        wp_enqueue_media();
    }
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

    add_meta_box( 'audubon_slide_meta', 'スライド下のテキスト・リンク',
        'audubon_render_slide_meta_box', 'slides', 'normal', 'high' );
}

function audubon_get_pdf_display( $attachment_id ) {
    if ( ! $attachment_id ) {
        return '';
    }
    $url   = wp_get_attachment_url( $attachment_id );
    $title = get_the_title( $attachment_id );
    if ( ! $url ) {
        return '';
    }
    return sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( $title ) );
}

function audubon_render_actor_meta_box( $post ) {
    wp_nonce_field( 'audubon_actor_meta', 'audubon_actor_meta_nonce' );

    $profile_pdf_id = get_post_meta( $post->ID, '_audubon_profile_pdf_id', true );
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
        <label><strong>プロフィールPDF</strong></label><br>
        <input type="hidden" name="audubon_profile_pdf_id" id="audubon_profile_pdf_id" value="<?php echo esc_attr( $profile_pdf_id ); ?>">
        <button type="button" class="button" id="audubon_profile_pdf_select">PDFを選択</button>
        <button type="button" class="button" id="audubon_profile_pdf_remove">削除</button>
        <span id="audubon_profile_pdf_preview" style="margin-left:8px;">
            <?php echo audubon_get_pdf_display( $profile_pdf_id ); ?>
        </span>
    </p>

    <hr>

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

    <script>
    (function($){
        var customUploader;
        $('#audubon_profile_pdf_select').on('click', function(e){
            e.preventDefault();
            if (customUploader) { customUploader.open(); return; }
            customUploader = wp.media({
                title: 'プロフィールPDFを選択',
                library: { type: 'application/pdf' },
                button: { text: 'この PDF を使用' },
                multiple: false
            });
            customUploader.on('select', function(){
                var attachment = customUploader.state().get('selection').first().toJSON();
                $('#audubon_profile_pdf_id').val(attachment.id);
                $('#audubon_profile_pdf_preview').html('<a href="'+attachment.url+'" target="_blank">'+attachment.filename+'</a>');
            });
            customUploader.open();
        });
        $('#audubon_profile_pdf_remove').on('click', function(e){
            e.preventDefault();
            $('#audubon_profile_pdf_id').val('');
            $('#audubon_profile_pdf_preview').html('');
        });
    })(jQuery);
    </script>
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
    $caption = get_post_meta( $post->ID, '_audubon_slide_caption', true );
    $link    = get_post_meta( $post->ID, '_audubon_slide_link', true );
    ?>
    <p style="margin:0 0 6px;">
        <label for="audubon_slide_caption" style="font-size:14px;">
            <strong>スライド下に表示するテキスト（1行）</strong>
        </label>
    </p>
    <p style="margin:0 0 6px;">
        <input type="text" id="audubon_slide_caption" name="audubon_slide_caption"
               value="<?php echo esc_attr( $caption ); ?>"
               style="width:100%;font-size:18px;padding:8px 10px;line-height:1.4;"
               placeholder="例: 山田太郎 出演">
    </p>
    <p style="margin:0 0 18px;color:#666;">
        トップページのスライダーで、A4ポスター画像の真下に1行で表示されます。空欄の場合は何も表示されません。
    </p>
    <p style="margin:0 0 6px;">
        <label for="audubon_slide_link"><strong>リンク先URL（任意）</strong></label>
    </p>
    <p style="margin:0;">
        <input type="url" id="audubon_slide_link" name="audubon_slide_link"
               value="<?php echo esc_attr( $link ); ?>" style="width:100%;" placeholder="https://...">
        <span class="description">スライドをクリックしたときの遷移先。空欄ならクリックしても遷移しません。</span>
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

        update_post_meta( $post_id, '_audubon_profile_pdf_id',
            isset( $_POST['audubon_profile_pdf_id'] ) ? absint( $_POST['audubon_profile_pdf_id'] ) : 0 );
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

        update_post_meta( $post_id, '_audubon_slide_caption',
            isset( $_POST['audubon_slide_caption'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_slide_caption'] ) ) : '' );
        update_post_meta( $post_id, '_audubon_slide_link',
            isset( $_POST['audubon_slide_link'] ) ? esc_url_raw( wp_unslash( $_POST['audubon_slide_link'] ) ) : '' );
    }
}

/* =============================================================
 * 3. テンプレートタグ
 * ============================================================= */

function audubon_get_actor_profile_pdf_url( $actor_id = null ) {
    $actor_id = $actor_id ?: get_the_ID();
    $pdf_id   = (int) get_post_meta( $actor_id, '_audubon_profile_pdf_id', true );
    return $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
}

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
 * アクター用「最新の出演」+「PDFダウンロード」のHTML出力。
 * single-actor.php から呼び出します。
 */
function audubon_render_actor_links( $actor_id = null ) {
    $actor_id = $actor_id ?: get_the_ID();
    if ( ! $actor_id ) {
        return;
    }

    $info    = audubon_get_actor_latest_information( $actor_id );
    $pdf_url = audubon_get_actor_profile_pdf_url( $actor_id );

    if ( ! $info && ! $pdf_url ) {
        return;
    }
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

        <?php if ( $pdf_url ) : ?>
            <a class="audubon-actor-links__pdf" href="<?php echo esc_url( $pdf_url ); ?>" download target="_blank" rel="noopener">
                <span class="audubon-actor-links__icon" aria-hidden="true">⬇</span>
                プロフィールPDFをダウンロード
            </a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * トップページ用 3分割A4スライダー（slides CPT）。
 * index.php から呼び出します。
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
    <div class="audubon-banner" data-visible="3" data-interval="4000">
        <button class="audubon-banner__nav audubon-banner__nav--prev" aria-label="前へ">‹</button>
        <div class="audubon-banner__viewport">
            <ul class="audubon-banner__track">
                <?php foreach ( $slides as $slide ) :
                    $caption = get_post_meta( $slide->ID, '_audubon_slide_caption', true );
                    $link    = get_post_meta( $slide->ID, '_audubon_slide_link', true );
                    $thumb   = get_the_post_thumbnail( $slide->ID, 'large', array( 'class' => 'audubon-banner__image' ) );
                    if ( ! $thumb ) {
                        continue;
                    }
                    ?>
                    <li class="audubon-banner__item">
                        <?php if ( $link ) : ?><a href="<?php echo esc_url( $link ); ?>" class="audubon-banner__link"><?php endif; ?>
                            <div class="audubon-banner__poster"><?php echo $thumb; ?></div>
                            <?php if ( $caption ) : ?>
                                <p class="audubon-banner__caption"><?php echo esc_html( $caption ); ?></p>
                            <?php endif; ?>
                        <?php if ( $link ) : ?></a><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <button class="audubon-banner__nav audubon-banner__nav--next" aria-label="次へ">›</button>
    </div>
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
            $new['audubon_slide_caption'] = '出演テキスト';
        }
    }
    return $new;
} );
add_action( 'manage_slides_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'audubon_slide_caption' ) {
        echo esc_html( get_post_meta( $post_id, '_audubon_slide_caption', true ) );
    }
}, 10, 2 );

add_filter( 'manage_actor_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['audubon_actor_pdf']  = 'プロフィールPDF';
            $new['audubon_actor_news'] = '最新の出演';
        }
    }
    return $new;
} );
add_action( 'manage_actor_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'audubon_actor_pdf' ) {
        $url = audubon_get_actor_profile_pdf_url( $post_id );
        echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank">PDF</a>' : '—';
    }
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
