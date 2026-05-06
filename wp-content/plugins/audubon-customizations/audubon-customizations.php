<?php
/**
 * Plugin Name: Studio Audubon Customizations
 * Plugin URI: https://studio-audubon.jp/
 * Description: Studio Audubonサイト用のカスタマイズ。アクターページ、Information（旧News）、A4ポスターバナー、Works一覧を管理画面から編集可能にします。
 * Version: 1.0.0
 * Author: Studio Audubon
 * Text Domain: audubon
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AUDUBON_PLUGIN_FILE', __FILE__ );
define( 'AUDUBON_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUDUBON_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AUDUBON_VERSION', '1.0.0' );

require_once AUDUBON_PLUGIN_DIR . 'includes/post-types.php';
require_once AUDUBON_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once AUDUBON_PLUGIN_DIR . 'includes/template-tags.php';
require_once AUDUBON_PLUGIN_DIR . 'includes/shortcodes.php';
require_once AUDUBON_PLUGIN_DIR . 'includes/admin.php';

add_action( 'wp_enqueue_scripts', 'audubon_enqueue_assets' );
function audubon_enqueue_assets() {
    wp_enqueue_style(
        'audubon-banner',
        AUDUBON_PLUGIN_URL . 'assets/css/banner.css',
        array(),
        AUDUBON_VERSION
    );
    wp_enqueue_style(
        'audubon-works',
        AUDUBON_PLUGIN_URL . 'assets/css/works.css',
        array(),
        AUDUBON_VERSION
    );
    wp_enqueue_style(
        'audubon-actor',
        AUDUBON_PLUGIN_URL . 'assets/css/actor.css',
        array(),
        AUDUBON_VERSION
    );
    wp_enqueue_script(
        'audubon-banner',
        AUDUBON_PLUGIN_URL . 'assets/js/banner.js',
        array(),
        AUDUBON_VERSION,
        true
    );
}

register_activation_hook( __FILE__, 'audubon_activate' );
function audubon_activate() {
    audubon_register_post_types();
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'audubon_deactivate' );
function audubon_deactivate() {
    flush_rewrite_rules();
}

add_filter( 'single_template', 'audubon_single_template' );
function audubon_single_template( $single ) {
    global $post;
    if ( $post->post_type === 'audubon_actor' ) {
        $template = AUDUBON_PLUGIN_DIR . 'templates/single-actor.php';
        if ( file_exists( $template ) ) {
            return $template;
        }
    }
    return $single;
}

add_filter( 'archive_template', 'audubon_archive_template' );
function audubon_archive_template( $archive ) {
    if ( is_post_type_archive( 'audubon_information' ) ) {
        $template = AUDUBON_PLUGIN_DIR . 'templates/archive-information.php';
        if ( file_exists( $template ) ) {
            return $template;
        }
    }
    return $archive;
}
