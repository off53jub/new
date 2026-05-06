<?php
/**
 * 既存CPTに追加するメタボックス。
 *
 * - actor       : プロフィールPDF / 最新の出演 (news_list)
 * - news_list   : 放映日時（フリーテキスト）/ 並び替え用日付 / 出演アクター
 * - slides      : ポスター下に表示するテキスト / リンク先
 *
 * メタキーは全て `_audubon_*` で名前空間化し、既存フィールドと衝突しません。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'add_meta_boxes', 'audubon_register_meta_boxes' );
function audubon_register_meta_boxes() {
    add_meta_box(
        'audubon_actor_meta',
        'アクター追加情報（プロフィールPDF / 最新の出演）',
        'audubon_render_actor_meta_box',
        'actor',
        'normal',
        'high'
    );

    add_meta_box(
        'audubon_news_meta',
        '放映日時・出演アクター',
        'audubon_render_news_meta_box',
        'news_list',
        'side',
        'high'
    );

    add_meta_box(
        'audubon_slide_meta',
        'スライド設定（出演テキスト・リンク）',
        'audubon_render_slide_meta_box',
        'slides',
        'normal',
        'high'
    );
}

/**
 * 添付PDFのIDから安全に表示用テキストを生成。
 */
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
        <span class="description">フリーテキストとは別に、一覧での並び順を制御するための日付。空欄なら投稿日で並びます。</span>
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
        <span class="description">[audubon_slides] ショートコードで3分割A4ポスターとして表示する際に使用します。既存テーマのスライド表示には影響しません。</span>
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
        <br><span class="description">空の場合、既存スライドのリンク（テーマ依存）が優先されます。</span>
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

    // Actor (既存CPT)
    if ( $post->post_type === 'actor'
        && isset( $_POST['audubon_actor_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_actor_meta_nonce'], 'audubon_actor_meta' ) ) {

        $pdf_id = isset( $_POST['audubon_profile_pdf_id'] ) ? absint( $_POST['audubon_profile_pdf_id'] ) : 0;
        update_post_meta( $post_id, '_audubon_profile_pdf_id', $pdf_id );

        $latest_info_id = isset( $_POST['audubon_latest_information_id'] ) ? absint( $_POST['audubon_latest_information_id'] ) : 0;
        update_post_meta( $post_id, '_audubon_latest_information_id', $latest_info_id );

        $auto = ! empty( $_POST['audubon_auto_latest_information'] ) ? '1' : '0';
        update_post_meta( $post_id, '_audubon_auto_latest_information', $auto );
    }

    // News (既存CPT news_list)
    if ( $post->post_type === 'news_list'
        && isset( $_POST['audubon_news_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_news_meta_nonce'], 'audubon_news_meta' ) ) {

        $text = isset( $_POST['audubon_broadcast_text'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_broadcast_text'] ) ) : '';
        update_post_meta( $post_id, '_audubon_broadcast_text', $text );

        $sort = isset( $_POST['audubon_broadcast_sort'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_broadcast_sort'] ) ) : '';
        update_post_meta( $post_id, '_audubon_broadcast_sort', $sort );

        $actors = isset( $_POST['audubon_related_actors'] ) && is_array( $_POST['audubon_related_actors'] )
            ? array_map( 'absint', $_POST['audubon_related_actors'] )
            : array();
        update_post_meta( $post_id, '_audubon_related_actors', $actors );
    }

    // Slides (既存CPT)
    if ( $post->post_type === 'slides'
        && isset( $_POST['audubon_slide_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_slide_meta_nonce'], 'audubon_slide_meta' ) ) {

        $caption = isset( $_POST['audubon_slide_caption'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_slide_caption'] ) ) : '';
        update_post_meta( $post_id, '_audubon_slide_caption', $caption );

        $link = isset( $_POST['audubon_slide_link'] ) ? esc_url_raw( wp_unslash( $_POST['audubon_slide_link'] ) ) : '';
        update_post_meta( $post_id, '_audubon_slide_link', $link );
    }
}

/**
 * メディアアップローダーをアクター編集画面で読み込む。
 */
add_action( 'admin_enqueue_scripts', 'audubon_admin_enqueue' );
function audubon_admin_enqueue( $hook ) {
    global $post;
    if ( ( $hook === 'post.php' || $hook === 'post-new.php' )
        && $post && $post->post_type === 'actor' ) {
        wp_enqueue_media();
    }
}
