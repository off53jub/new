<?php
/**
 * Studio Audubon Customizations
 *
 * テーマ用の機能拡張ファイル。テーマの functions.php から1行 require するだけで動きます。
 *
 *   require_once get_stylesheet_directory() . '/audubon-customizations.php';
 *
 * 対象CPT:
 *   - actor      （アクター）
 *   - news_list  （ニュース）
 *   - slides     （スライド）
 *   - post       （Worksは通常投稿）
 *
 * メタキーは全て `_audubon_*` で名前空間化しているため、既存フィールドとは衝突しません。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'AUDUBON_THEME_VERSION' ) ) {
    define( 'AUDUBON_THEME_VERSION', '1.0.0' );
}

/* =============================================================
 * 1. CSS / JS のエンキュー
 * ============================================================= */

add_action( 'wp_enqueue_scripts', 'audubon_enqueue_assets' );
function audubon_enqueue_assets() {
    $base = get_stylesheet_directory_uri();
    wp_enqueue_style(
        'audubon-customizations',
        $base . '/assets/css/audubon.css',
        array(),
        AUDUBON_THEME_VERSION
    );
    wp_enqueue_script(
        'audubon-banner',
        $base . '/assets/js/audubon-banner.js',
        array(),
        AUDUBON_THEME_VERSION,
        true
    );
}

add_action( 'admin_enqueue_scripts', 'audubon_admin_enqueue' );
function audubon_admin_enqueue( $hook ) {
    global $post;
    if ( ( $hook === 'post.php' || $hook === 'post-new.php' )
        && $post && $post->post_type === 'actor' ) {
        wp_enqueue_media();
    }
}

/* =============================================================
 * 2. ニュース（news_list）アーカイブ並び順
 * ============================================================= */

add_action( 'pre_get_posts', 'audubon_order_news_list' );
function audubon_order_news_list( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->is_post_type_archive( 'news_list' ) ) {
        $query->set( 'meta_key', '_audubon_broadcast_sort' );
        $query->set( 'orderby', array(
            'meta_value' => 'DESC',
            'date'       => 'DESC',
        ) );
    }
}

/* =============================================================
 * 3. メタボックス
 * ============================================================= */

add_action( 'add_meta_boxes', 'audubon_register_meta_boxes' );
function audubon_register_meta_boxes() {
    add_meta_box( 'audubon_actor_meta', 'アクター追加情報（プロフィールPDF / 最新の出演）',
        'audubon_render_actor_meta_box', 'actor', 'normal', 'high' );

    add_meta_box( 'audubon_news_meta', '放映日時・出演アクター',
        'audubon_render_news_meta_box', 'news_list', 'side', 'high' );

    add_meta_box( 'audubon_slide_meta', 'スライド設定（出演テキスト・リンク）',
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
    <p>
        <label for="audubon_broadcast_text"><strong>放映日時（フリーテキスト）</strong></label><br>
        <input type="text" id="audubon_broadcast_text" name="audubon_broadcast_text"
               value="<?php echo esc_attr( $broadcast_text ); ?>" style="width:100%;"
               placeholder="例: 2025年8月13日(水)21:00〜 / 毎週土曜 / 公開中 など">
        <span class="description">投稿の作成日時とは別に、表示用のテキストを自由に入力できます。</span>
    </p>
    <p>
        <label for="audubon_broadcast_sort"><strong>並び替え用の日付（任意）</strong></label><br>
        <input type="date" id="audubon_broadcast_sort" name="audubon_broadcast_sort"
               value="<?php echo esc_attr( $broadcast_sort ); ?>" style="width:100%;">
        <span class="description">フリーテキストとは別に、一覧での並び順制御に使う日付。空欄なら投稿日で並びます。</span>
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
    <p>
        <span class="description">[audubon_slides] ショートコードでA4ポスター3分割スライダーとして表示する際に使用します。既存テーマのスライド表示には影響しません。</span>
    </p>
    <p>
        <label for="audubon_slide_caption"><strong>ポスター下に表示するテキスト（例: 山田太郎 出演）</strong></label><br>
        <input type="text" id="audubon_slide_caption" name="audubon_slide_caption"
               value="<?php echo esc_attr( $caption ); ?>" style="width:100%;">
    </p>
    <p>
        <label for="audubon_slide_link"><strong>リンク先URL（任意）</strong></label><br>
        <input type="url" id="audubon_slide_link" name="audubon_slide_link"
               value="<?php echo esc_attr( $link ); ?>" style="width:100%;" placeholder="https://...">
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
 * 4. テンプレートタグ（テーマから直接呼び出し可）
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

    $query = new WP_Query( array(
        'post_type'      => 'news_list',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => '_audubon_related_actors',
                'value'   => sprintf( ':"%d";', $actor_id ),
                'compare' => 'LIKE',
            ),
        ),
        'meta_key'       => '_audubon_broadcast_sort',
        'orderby'        => array(
            'meta_value' => 'DESC',
            'date'       => 'DESC',
        ),
    ) );
    return $query->have_posts() ? $query->posts[0] : null;
}

function audubon_get_news_display_date( $post_id = null, $format = '' ) {
    $post_id = $post_id ?: get_the_ID();
    $text    = get_post_meta( $post_id, '_audubon_broadcast_text', true );
    if ( $text !== '' ) {
        return $text;
    }
    $format = $format ?: get_option( 'date_format' );
    return get_the_date( $format, $post_id );
}

function audubon_get_slides( $limit = -1 ) {
    if ( ! post_type_exists( 'slides' ) ) {
        return array();
    }
    return get_posts( array(
        'post_type'      => 'slides',
        'posts_per_page' => $limit,
        'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
        'post_status'    => 'publish',
    ) );
}

/* =============================================================
 * 5. ショートコード
 * ============================================================= */

add_shortcode( 'audubon_slides', 'audubon_shortcode_slides' );
function audubon_shortcode_slides( $atts ) {
    $atts = shortcode_atts( array(
        'visible'  => 3,
        'interval' => 4000,
    ), $atts, 'audubon_slides' );

    $slides = audubon_get_slides();
    if ( empty( $slides ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="audubon-banner"
         data-visible="<?php echo (int) $atts['visible']; ?>"
         data-interval="<?php echo (int) $atts['interval']; ?>">
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
    return ob_get_clean();
}

add_shortcode( 'audubon_works', 'audubon_shortcode_works' );
function audubon_shortcode_works( $atts ) {
    $atts = shortcode_atts( array(
        'category' => '',          // カテゴリで絞り込みたい場合は category="works"
        'limit'    => -1,
        'columns'  => 3,
    ), $atts, 'audubon_works' );

    $args = array(
        'post_type'      => 'post',  // Worksは通常投稿
        'posts_per_page' => (int) $atts['limit'],
        'post_status'    => 'publish',
        'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
    );
    if ( $atts['category'] ) {
        $args['category_name'] = sanitize_title( $atts['category'] );
    }

    $works = get_posts( $args );
    if ( empty( $works ) ) {
        return '';
    }

    ob_start();
    ?>
    <ul class="audubon-works audubon-works--cols-<?php echo (int) $atts['columns']; ?>">
        <?php foreach ( $works as $work ) :
            $thumb = get_the_post_thumbnail( $work->ID, 'large', array( 'class' => 'audubon-works__image' ) );
            ?>
            <li class="audubon-works__item">
                <a href="<?php echo esc_url( get_permalink( $work ) ); ?>" class="audubon-works__link">
                    <div class="audubon-works__poster"><?php echo $thumb; ?></div>
                    <p class="audubon-works__title"><?php echo esc_html( get_the_title( $work ) ); ?></p>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

add_shortcode( 'audubon_actor_links', 'audubon_shortcode_actor_links' );
function audubon_shortcode_actor_links( $atts ) {
    $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'audubon_actor_links' );
    $actor_id = (int) $atts['id'] ?: get_the_ID();
    if ( ! $actor_id ) {
        return '';
    }

    $info    = audubon_get_actor_latest_information( $actor_id );
    $pdf_url = audubon_get_actor_profile_pdf_url( $actor_id );
    if ( ! $info && ! $pdf_url ) {
        return '';
    }

    ob_start();
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
    return ob_get_clean();
}

add_shortcode( 'audubon_news_date', 'audubon_shortcode_news_date' );
function audubon_shortcode_news_date( $atts ) {
    $atts = shortcode_atts( array( 'id' => 0, 'format' => '' ), $atts, 'audubon_news_date' );
    $id = (int) $atts['id'] ?: get_the_ID();
    return esc_html( audubon_get_news_display_date( $id, $atts['format'] ) );
}

/* =============================================================
 * 6. 管理画面の一覧列
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
add_filter( 'manage_edit-news_list_sortable_columns', function ( $columns ) {
    $columns['audubon_broadcast_sort'] = 'audubon_broadcast_sort';
    return $columns;
} );
add_action( 'pre_get_posts', function ( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->get( 'orderby' ) === 'audubon_broadcast_sort' ) {
        $query->set( 'meta_key', '_audubon_broadcast_sort' );
        $query->set( 'orderby', 'meta_value' );
    }
} );

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
