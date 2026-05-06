<?php
/**
 * 管理画面まわりのカスタマイズ。
 * 既存CPT (`actor` / `news_list` / `slides`) の一覧に列を追加します。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// news_list 一覧に「放映日時（フリーテキスト）」列
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

// slides 一覧に「出演テキスト」列
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

// actor 一覧に「PDF / 最新の出演」列
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
            printf(
                '<a href="%s">%s</a>',
                esc_url( get_edit_post_link( $info->ID ) ),
                esc_html( get_the_title( $info ) )
            );
        } else {
            echo '—';
        }
    }
}, 10, 2 );
