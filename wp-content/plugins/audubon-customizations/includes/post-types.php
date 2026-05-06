<?php
/**
 * Custom Post Types registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'audubon_register_post_types' );
function audubon_register_post_types() {

    // アクター（Actor）
    register_post_type( 'audubon_actor', array(
        'labels' => array(
            'name'               => 'アクター',
            'singular_name'      => 'アクター',
            'menu_name'          => 'アクター',
            'add_new'            => '新規追加',
            'add_new_item'       => '新規アクターを追加',
            'edit_item'          => 'アクターを編集',
            'new_item'           => '新規アクター',
            'view_item'          => 'アクターを表示',
            'search_items'       => 'アクターを検索',
            'not_found'          => 'アクターが見つかりません',
            'not_found_in_trash' => 'ゴミ箱にアクターはありません',
        ),
        'public'        => true,
        'has_archive'   => 'actors',
        'rewrite'       => array( 'slug' => 'actor' ),
        'menu_icon'     => 'dashicons-admin-users',
        'menu_position' => 5,
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'show_in_rest'  => true,
    ) );

    // Information（旧News）
    register_post_type( 'audubon_information', array(
        'labels' => array(
            'name'               => 'Information',
            'singular_name'      => 'Information',
            'menu_name'          => 'Information',
            'add_new'            => '新規追加',
            'add_new_item'       => '新規Informationを追加',
            'edit_item'          => 'Informationを編集',
            'new_item'           => '新規Information',
            'view_item'          => 'Informationを表示',
            'search_items'       => 'Informationを検索',
            'not_found'          => 'Informationが見つかりません',
            'not_found_in_trash' => 'ゴミ箱にInformationはありません',
        ),
        'public'        => true,
        'has_archive'   => 'information',
        'rewrite'       => array( 'slug' => 'information' ),
        'menu_icon'     => 'dashicons-megaphone',
        'menu_position' => 6,
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'show_in_rest'  => true,
    ) );

    // 既存の `slides` CPT は本プラグインでは登録しません（テーマまたは別プラグイン側で登録済みのため）。
    // includes/meta-boxes.php で `slides` にキャプション・リンク用メタボックスを追加し、
    // includes/shortcodes.php の [audubon_slides] で3分割A4ポスターの自動スライダーとして表示します。

    // Works（作品）
    register_post_type( 'audubon_work', array(
        'labels' => array(
            'name'               => 'Works',
            'singular_name'      => 'Work',
            'menu_name'          => 'Works',
            'add_new'            => '新規追加',
            'add_new_item'       => '新規作品を追加',
            'edit_item'          => '作品を編集',
            'new_item'           => '新規作品',
            'view_item'          => '作品を表示',
            'search_items'       => '作品を検索',
            'not_found'          => '作品が見つかりません',
            'not_found_in_trash' => 'ゴミ箱に作品はありません',
        ),
        'public'        => true,
        'has_archive'   => 'works',
        'rewrite'       => array( 'slug' => 'works' ),
        'menu_icon'     => 'dashicons-format-gallery',
        'menu_position' => 8,
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
        'show_in_rest'  => true,
    ) );
}

/**
 * Information投稿でアーカイブを放映日時で並べ替え。
 */
add_action( 'pre_get_posts', 'audubon_order_information_by_broadcast_date' );
function audubon_order_information_by_broadcast_date( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->is_post_type_archive( 'audubon_information' ) ) {
        $query->set( 'meta_key', '_audubon_broadcast_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'DESC' );
    }
}
