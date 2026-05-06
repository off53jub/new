<?php
/**
 * ショートコード。
 *
 * - [audubon_banner]                 トップページ用 3分割A4バナー（自動スライド）
 * - [audubon_works limit="-1"]       Works一覧（A4ポスターグリッド、静的）
 * - [audubon_actor_links id=""]      アクターページ用「最新の出演作品 / プロフィールPDF」リンク
 * - [audubon_information_date]       Information本文/ループ内で放映日時を表示
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'audubon_banner', 'audubon_shortcode_banner' );
function audubon_shortcode_banner( $atts ) {
    $atts = shortcode_atts( array(
        'visible'  => 3,      // 一度に見せる枚数
        'interval' => 4000,   // 自動スライド間隔（ms）
    ), $atts, 'audubon_banner' );

    $banners = audubon_get_banner_posters();
    if ( empty( $banners ) ) {
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
                <?php foreach ( $banners as $banner ) :
                    $caption = get_post_meta( $banner->ID, '_audubon_banner_caption', true );
                    $link    = get_post_meta( $banner->ID, '_audubon_banner_link', true );
                    $thumb   = get_the_post_thumbnail( $banner->ID, 'large', array( 'class' => 'audubon-banner__image' ) );
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
        'limit'   => -1,
        'columns' => 3,
    ), $atts, 'audubon_works' );

    $works = audubon_get_works( (int) $atts['limit'] );
    if ( empty( $works ) ) {
        return '';
    }

    ob_start();
    ?>
    <ul class="audubon-works audubon-works--cols-<?php echo (int) $atts['columns']; ?>">
        <?php foreach ( $works as $work ) :
            $year     = get_post_meta( $work->ID, '_audubon_work_year', true );
            $ext_link = get_post_meta( $work->ID, '_audubon_work_link', true );
            $url      = $ext_link ?: get_permalink( $work );
            $thumb    = get_the_post_thumbnail( $work->ID, 'large', array( 'class' => 'audubon-works__image' ) );
            ?>
            <li class="audubon-works__item">
                <a href="<?php echo esc_url( $url ); ?>" class="audubon-works__link">
                    <div class="audubon-works__poster">
                        <?php echo $thumb; ?>
                    </div>
                    <p class="audubon-works__title">
                        <?php echo esc_html( get_the_title( $work ) ); ?>
                        <?php if ( $year ) : ?>
                            <span class="audubon-works__year">（<?php echo esc_html( $year ); ?>）</span>
                        <?php endif; ?>
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
            $label = audubon_get_information_label( $info->ID );
            $date  = audubon_get_information_display_date( $info->ID );
            ?>
            <a class="audubon-actor-links__information" href="<?php echo esc_url( get_permalink( $info ) ); ?>">
                <span class="audubon-actor-links__label">最新の出演作品</span>
                <span class="audubon-actor-links__title"><?php echo esc_html( get_the_title( $info ) ); ?></span>
                <?php if ( $date ) : ?>
                    <span class="audubon-actor-links__date">
                        <?php echo $label ? esc_html( $label ) . '：' : ''; ?><?php echo esc_html( $date ); ?>
                    </span>
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

add_shortcode( 'audubon_information_date', 'audubon_shortcode_information_date' );
function audubon_shortcode_information_date( $atts ) {
    $atts = shortcode_atts( array(
        'id'     => 0,
        'format' => '',
    ), $atts, 'audubon_information_date' );
    $id = (int) $atts['id'] ?: get_the_ID();
    return esc_html( audubon_get_information_display_date( $id, $atts['format'] ) );
}
