<?php
/**
 * 管理画面まわりのカスタマイズ。
 * - Information / Works / Bannerの一覧に必要な列を追加
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Information一覧に「放映日時」列
add_filter( 'manage_audubon_information_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['broadcast_date']  = '放映日時';
            $new['broadcast_label'] = 'ラベル';
        }
    }
    return $new;
} );

add_action( 'manage_audubon_information_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'broadcast_date' ) {
        echo esc_html( audubon_get_information_display_date( $post_id ) );
    }
    if ( $column === 'broadcast_label' ) {
        echo esc_html( audubon_get_information_label( $post_id ) );
    }
}, 10, 2 );

add_filter( 'manage_edit-audubon_information_sortable_columns', function ( $columns ) {
    $columns['broadcast_date'] = 'broadcast_date';
    return $columns;
} );

add_action( 'pre_get_posts', function ( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->get( 'orderby' ) === 'broadcast_date' ) {
        $query->set( 'meta_key', '_audubon_broadcast_date' );
        $query->set( 'orderby', 'meta_value' );
    }
} );

// 既存 slides 一覧に「出演テキスト」列を追加
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

// Works一覧にポスター・公開年
add_filter( 'manage_audubon_work_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['work_thumb'] = 'ポスター';
            $new['work_year']  = '公開年';
        }
    }
    return $new;
} );

add_action( 'manage_audubon_work_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'work_thumb' ) {
        echo get_the_post_thumbnail( $post_id, array( 60, 85 ) );
    }
    if ( $column === 'work_year' ) {
        echo esc_html( get_post_meta( $post_id, '_audubon_work_year', true ) );
    }
}, 10, 2 );

// Actor一覧にPDF・最新出演
add_filter( 'manage_audubon_actor_posts_columns', function ( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( $key === 'title' ) {
            $new['actor_pdf']  = 'プロフィールPDF';
            $new['actor_info'] = '最新の出演';
        }
    }
    return $new;
} );

add_action( 'manage_audubon_actor_posts_custom_column', function ( $column, $post_id ) {
    if ( $column === 'actor_pdf' ) {
        $url = audubon_get_actor_profile_pdf_url( $post_id );
        echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank">PDF</a>' : '—';
    }
    if ( $column === 'actor_info' ) {
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
