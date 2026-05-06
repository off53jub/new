<?php
/**
 * テンプレートタグ（テーマからも呼び出せるヘルパー関数）。
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
 * アクターの最新の出演作品（Information投稿）。
 * 1) 手動指定された投稿が最優先
 * 2) 自動取得がONなら、_audubon_related_actors にこのアクターIDを含むInformationの最新（放映日時優先）
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
        'post_type'      => 'audubon_information',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => '_audubon_related_actors',
                'value'   => sprintf( ':"%d";', $actor_id ),
                'compare' => 'LIKE',
            ),
        ),
        'meta_key'       => '_audubon_broadcast_date',
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
 * Information投稿の表示日付。放映日時が入っていればそれ、無ければ投稿日。
 */
function audubon_get_information_display_date( $post_id = null, $format = '' ) {
    $post_id = $post_id ?: get_the_ID();
    $format  = $format ?: get_option( 'date_format' );
    $broadcast = get_post_meta( $post_id, '_audubon_broadcast_date', true );
    if ( $broadcast ) {
        $ts = strtotime( $broadcast );
        if ( $ts ) {
            return date_i18n( $format, $ts );
        }
    }
    return get_the_date( $format, $post_id );
}

/**
 * Information投稿の表示ラベル（例: 初回放送 / 公開日）。
 */
function audubon_get_information_label( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    return get_post_meta( $post_id, '_audubon_broadcast_label', true );
}

/**
 * 既存 `slides` CPTのスライド一覧（menu_order昇順、なければ作成日降順）。
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

/**
 * Works一覧（menu_order昇順、なければ公開年降順）。
 */
function audubon_get_works( $limit = -1 ) {
    return get_posts( array(
        'post_type'      => 'audubon_work',
        'posts_per_page' => $limit,
        'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
        'post_status'    => 'publish',
    ) );
}
