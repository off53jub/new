<?php

remove_action('template_redirect', 'redirect_canonical');

add_filter('show_admin_bar', '__return_false');

add_theme_support('post-thumbnails');

function mythumb($size)
{
    global $post;

    if (has_post_thumbnail()) {
        $postthumb = wp_get_attachment_image_src(get_post_thumbnail_id(), $size);
        $url = $postthumb[0];
    } else {
        $url = get_template_directory_uri() . '/noimage.jpg';
    }

    return $url;
}

/* アイキャッチ画像 サイズ指定追加 */
function my_theme_setup()
{
    add_theme_support('post-thumbnails');
    add_image_size('small_thumbnail', 300, 424, true); // A4比率（210:297）
    add_image_size('actor_thumbnail', 230, 325, true); // A4比率（210:297）
    add_image_size('audubon_a4_large', 600, 849, true); // A4比率（大）
    add_action('after_setup_theme', 'my_theme_setup');
    
}

/* 管理画面にメニューを表示する */
add_action('after_setup_theme', 'register_menu');
function register_menu()
{
    register_nav_menu('primary', __('Primary Menu', 'studio_audubon'));
}

//表示件数制御
add_action('pre_get_posts', 'my_pre_get_posts');
function my_pre_get_posts($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_category()) {
        $query->set('posts_per_page', 12);
    }
    if ($query->is_post_type_archive('news-list')) {
        $query->set('posts_per_page', 10);
        $query->set('order', 'ASC');
        $query->set('orderby', 'date');
    }
}

/* 固定ページのみ自動整形を無効 */
add_filter('the_content', 'wpautop_filter', 9);
function wpautop_filter($content)
{
    global $post;
    $remove_filter = false;

    $arr_types = array('page');
    $post_type = get_post_type($post->ID);
    if (in_array($post_type, $arr_types)) {
        $remove_filter = true;
    }

    if ($remove_filter) {
        remove_filter('the_content', 'wpautop');
        remove_filter('the_excerpt', 'wpautop');
    }

    return $content;
}

/* ページネーション */

/*Actor order*/
function download_sort_order($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->is_tax('download')) {
        $query->set('orderby', 'menu_order');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'download_sort_order');

/* ---- Audubon機能拡張（メタボックス・スライダー・PDFダウンロード等） ---- */
require_once get_template_directory() . '/audubon-customizations.php';
