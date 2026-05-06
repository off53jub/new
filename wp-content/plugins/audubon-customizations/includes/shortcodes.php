<?php
/**
 * ショートコード。
 *
 * - [audubon_slides]                            既存`slides` CPTを使った3分割A4自動スライダー
 * - [audubon_works post_type="works"]           Works一覧（A4ポスターグリッド、静的）
 * - [audubon_actor_links id=""]                 アクターページ用「最新の出演 / プロフィールPDF」リンク
 * - [audubon_news_date]                         ニュースの放映日時（フリーテキスト優先）
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
                        <?php if ( $link ) : ?>
                            <a href="<?php echo esc_url( $link ); ?>" class="audubon-banner__link">
                        <?php endif; ?>
                            <div class="audubon-banner__poster">
                                <?php echo $thumb; ?>
                            </div>
                            <?php if ( $caption ) : ?>
                                <p class="audubon-banner__caption"><?php echo esc_html( $caption ); ?></p>
                            <?php endif; ?>
                        <?php if ( $link ) : ?>
                            </a>
                        <?php endif; ?>
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
        'post_type' => 'works',   // 既存CPTがある場合に応じて指定。例: post_type="post" category="works"
        'category'  => '',        // 通常投稿で運用している場合のカテゴリスラッグ
        'limit'     => -1,
        'columns'   => 3,
    ), $atts, 'audubon_works' );

    $args = array(
        'post_type'      => sanitize_key( $atts['post_type'] ),
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
                    <div class="audubon-works__poster">
                        <?php echo $thumb; ?>
                    </div>
                    <p class="audubon-works__title">
                        <?php echo esc_html( get_the_title( $work ) ); ?>
                    </p>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

add_shortcode( 'audubon_actor_links', 'audubon_shortcode_actor_links' );
function audubon_shortcode_actor_links( $atts ) {
    $atts = shortcode_atts( array(
        'id' => 0,
    ), $atts, 'audubon_actor_links' );

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
                <?php if ( $date ) : ?>
                    <span class="audubon-actor-links__date"><?php echo esc_html( $date ); ?></span>
                <?php endif; ?>
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
    $atts = shortcode_atts( array(
        'id'     => 0,
        'format' => '',
    ), $atts, 'audubon_news_date' );
    $id = (int) $atts['id'] ?: get_the_ID();
    return esc_html( audubon_get_news_display_date( $id, $atts['format'] ) );
}

// 旧名の後方互換
add_shortcode( 'audubon_information_date', 'audubon_shortcode_news_date' );
