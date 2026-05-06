<?php
/**
 * テンプレートタグ（テーマからも呼び出せるヘルパー関数）。
 *
 * 対象CPT:
 *   - actor       : アクター
 *   - news_list   : ニュース
 *   - slides      : 既存スライド
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * アクターのプロフィールPDFのURL。
 */
function audubon_get_actor_profile_pdf_url( $actor_id = null ) {
    $actor_id = $actor_id ?: get_the_ID();
    $pdf_id   = (int) get_post_meta( $actor_id, '_audubon_profile_pdf_id', true );
    if ( ! $pdf_id ) {
        return '';
    }
    return wp_get_attachment_url( $pdf_id );
}

/**
 * アクターの最新の出演（news_list投稿）。
 * 1) 手動指定された投稿が最優先
 * 2) 自動取得がONなら、_audubon_related_actors にこのアクターIDを含むnews_listの最新（並び替え用日付→投稿日）
 */
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

    if ( $query->have_posts() ) {
        return $query->posts[0];
    }
    return null;
}

/**
 * ニュース投稿の表示用「放映日時」テキスト。フリーテキスト優先、なければ投稿日。
 */
function audubon_get_news_display_date( $post_id = null, $format = '' ) {
    $post_id = $post_id ?: get_the_ID();
    $text    = get_post_meta( $post_id, '_audubon_broadcast_text', true );
    if ( $text !== '' ) {
        return $text;
    }
    $format = $format ?: get_option( 'date_format' );
    return get_the_date( $format, $post_id );
}

/**
 * 後方互換: 旧API名。
 */
function audubon_get_information_display_date( $post_id = null, $format = '' ) {
    return audubon_get_news_display_date( $post_id, $format );
}

/**
 * 既存 `slides` CPTの一覧（menu_order昇順、なければ作成日降順）。
 */
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
