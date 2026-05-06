<?php
/**
 * Studio Audubon テーマの functions.php
 *
 * - テーマサポート（タイトルタグ、アイキャッチ、HTML5など）
 * - メニュー登録
 * - audubon-customizations.php の読み込み（メタボックス・ショートコード）
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'AUDUBON_THEME_VERSION' ) ) {
    define( 'AUDUBON_THEME_VERSION', '1.0.0' );
}

/* テーマサポート */
add_action( 'after_setup_theme', 'audubon_theme_setup' );
function audubon_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    register_nav_menus( array(
        'primary' => 'メインメニュー',
        'footer'  => 'フッターメニュー',
    ) );
}

/* メイン機能ファイル（メタボックス・ショートコード等）を読み込み */
require_once get_stylesheet_directory() . '/audubon-customizations.php';
