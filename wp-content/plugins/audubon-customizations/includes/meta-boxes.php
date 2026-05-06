<?php
/**
 * カスタムフィールド（Meta Boxes）。
 * - アクター: プロフィールPDF / 最新の出演情報（Information投稿の選択）
 * - Information: 放映日時（任意の日時を入力可能）
 * - バナー: ポスター画像 / キャプション（○○出演など）/ リンク先
 * - Works: ポスター画像 / 公開年 / リンク先
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'add_meta_boxes', 'audubon_register_meta_boxes' );
function audubon_register_meta_boxes() {
    add_meta_box(
        'audubon_actor_meta',
        'アクター情報',
        'audubon_render_actor_meta_box',
        'audubon_actor',
        'normal',
        'high'
    );

    add_meta_box(
        'audubon_information_meta',
        '放映・公開日時',
        'audubon_render_information_meta_box',
        'audubon_information',
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

    add_meta_box(
        'audubon_work_meta',
        'Work設定',
        'audubon_render_work_meta_box',
        'audubon_work',
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

    $profile_pdf_id   = get_post_meta( $post->ID, '_audubon_profile_pdf_id', true );
    $latest_info_id   = get_post_meta( $post->ID, '_audubon_latest_information_id', true );
    $auto_latest      = get_post_meta( $post->ID, '_audubon_auto_latest_information', true );
    if ( $auto_latest === '' ) {
        $auto_latest = '1';
    }

    $informations = get_posts( array(
        'post_type'      => 'audubon_information',
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
            <strong>最新の出演作品（Information）を自動的にリンクする</strong>
        </label>
    </p>
    <p>
        <label><strong>手動で出演作品を指定する場合</strong></label><br>
        <select name="audubon_latest_information_id" style="width:100%;max-width:480px;">
            <option value="">— 選択しない（自動）—</option>
            <?php foreach ( $informations as $info ) : ?>
                <option value="<?php echo esc_attr( $info->ID ); ?>" <?php selected( $latest_info_id, $info->ID ); ?>>
                    <?php echo esc_html( $info->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><span class="description">「自動的にリンクする」がONの場合、最新の関連Information記事（このアクターを紐付けたもの、または下記から手動で選択したもの）が優先されます。</span>
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

function audubon_render_information_meta_box( $post ) {
    wp_nonce_field( 'audubon_information_meta', 'audubon_information_meta_nonce' );
    $broadcast_date = get_post_meta( $post->ID, '_audubon_broadcast_date', true );
    $broadcast_label = get_post_meta( $post->ID, '_audubon_broadcast_label', true );

    $related_actors = get_post_meta( $post->ID, '_audubon_related_actors', true );
    if ( ! is_array( $related_actors ) ) {
        $related_actors = array();
    }
    $actors = get_posts( array(
        'post_type'      => 'audubon_actor',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );
    ?>
    <p>
        <label for="audubon_broadcast_date"><strong>放映日時 / 公開日時</strong></label><br>
        <input type="datetime-local" id="audubon_broadcast_date" name="audubon_broadcast_date"
               value="<?php echo esc_attr( $broadcast_date ); ?>" style="width:100%;">
        <span class="description">投稿の作成日時とは別に、任意の日時を表示用に入力できます（円企画スタイル）。</span>
    </p>
    <p>
        <label for="audubon_broadcast_label"><strong>表示ラベル（任意）</strong></label><br>
        <input type="text" id="audubon_broadcast_label" name="audubon_broadcast_label"
               value="<?php echo esc_attr( $broadcast_label ); ?>" placeholder="例: 初回放送 / 公開日 / 上演" style="width:100%;">
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
        <span class="description">アクターページの「最新の出演作品」リンクに使用されます。Ctrl/Cmd+クリックで複数選択。</span>
    </p>
    <?php
}

function audubon_render_slide_meta_box( $post ) {
    wp_nonce_field( 'audubon_slide_meta', 'audubon_slide_meta_nonce' );
    $caption = get_post_meta( $post->ID, '_audubon_slide_caption', true );
    $link    = get_post_meta( $post->ID, '_audubon_slide_link', true );
    ?>
    <p>
        <span class="description">[audubon_slides] ショートコードで3分割A4ポスターとして表示するときに使用される情報です。既存テーマのスライド表示には影響しません。</span>
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
        <br><span class="description">空の場合、既存の slides CPT に設定されたリンクを優先します（テーマ依存）。</span>
    </p>
    <?php
}

function audubon_render_work_meta_box( $post ) {
    wp_nonce_field( 'audubon_work_meta', 'audubon_work_meta_nonce' );
    $year = get_post_meta( $post->ID, '_audubon_work_year', true );
    $link = get_post_meta( $post->ID, '_audubon_work_link', true );
    ?>
    <p>
        <span class="description">アイキャッチ画像にA4比率（210:297）のポスター画像を設定してください。</span>
    </p>
    <p>
        <label for="audubon_work_year"><strong>公開年</strong></label><br>
        <input type="text" id="audubon_work_year" name="audubon_work_year"
               value="<?php echo esc_attr( $year ); ?>" style="width:120px;" placeholder="2025">
    </p>
    <p>
        <label for="audubon_work_link"><strong>外部リンク（任意）</strong></label><br>
        <input type="url" id="audubon_work_link" name="audubon_work_link"
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

    // Actor
    if ( $post->post_type === 'audubon_actor'
        && isset( $_POST['audubon_actor_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_actor_meta_nonce'], 'audubon_actor_meta' ) ) {

        $pdf_id = isset( $_POST['audubon_profile_pdf_id'] ) ? absint( $_POST['audubon_profile_pdf_id'] ) : 0;
        update_post_meta( $post_id, '_audubon_profile_pdf_id', $pdf_id );

        $latest_info_id = isset( $_POST['audubon_latest_information_id'] ) ? absint( $_POST['audubon_latest_information_id'] ) : 0;
        update_post_meta( $post_id, '_audubon_latest_information_id', $latest_info_id );

        $auto = ! empty( $_POST['audubon_auto_latest_information'] ) ? '1' : '0';
        update_post_meta( $post_id, '_audubon_auto_latest_information', $auto );
    }

    // Information
    if ( $post->post_type === 'audubon_information'
        && isset( $_POST['audubon_information_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_information_meta_nonce'], 'audubon_information_meta' ) ) {

        $broadcast_date = isset( $_POST['audubon_broadcast_date'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_broadcast_date'] ) ) : '';
        update_post_meta( $post_id, '_audubon_broadcast_date', $broadcast_date );

        $broadcast_label = isset( $_POST['audubon_broadcast_label'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_broadcast_label'] ) ) : '';
        update_post_meta( $post_id, '_audubon_broadcast_label', $broadcast_label );

        $actors = isset( $_POST['audubon_related_actors'] ) && is_array( $_POST['audubon_related_actors'] )
            ? array_map( 'absint', $_POST['audubon_related_actors'] )
            : array();
        update_post_meta( $post_id, '_audubon_related_actors', $actors );
    }

    // Slide (既存slides CPTへの追加メタ)
    if ( $post->post_type === 'slides'
        && isset( $_POST['audubon_slide_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_slide_meta_nonce'], 'audubon_slide_meta' ) ) {

        $caption = isset( $_POST['audubon_slide_caption'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_slide_caption'] ) ) : '';
        update_post_meta( $post_id, '_audubon_slide_caption', $caption );

        $link = isset( $_POST['audubon_slide_link'] ) ? esc_url_raw( wp_unslash( $_POST['audubon_slide_link'] ) ) : '';
        update_post_meta( $post_id, '_audubon_slide_link', $link );
    }

    // Work
    if ( $post->post_type === 'audubon_work'
        && isset( $_POST['audubon_work_meta_nonce'] )
        && wp_verify_nonce( $_POST['audubon_work_meta_nonce'], 'audubon_work_meta' ) ) {

        $year = isset( $_POST['audubon_work_year'] ) ? sanitize_text_field( wp_unslash( $_POST['audubon_work_year'] ) ) : '';
        update_post_meta( $post_id, '_audubon_work_year', $year );

        $link = isset( $_POST['audubon_work_link'] ) ? esc_url_raw( wp_unslash( $_POST['audubon_work_link'] ) ) : '';
        update_post_meta( $post_id, '_audubon_work_link', $link );
    }
}

/**
 * メディアアップローダーを管理画面で読み込む。
 */
add_action( 'admin_enqueue_scripts', 'audubon_admin_enqueue' );
function audubon_admin_enqueue( $hook ) {
    global $post;
    if ( ( $hook === 'post.php' || $hook === 'post-new.php' )
        && $post && $post->post_type === 'audubon_actor' ) {
        wp_enqueue_media();
    }
}
