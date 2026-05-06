<?php
/**
 * header.php
 * すべてのページの上部HTMLを出力します。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="audubon-site">
    <header class="audubon-site__header" role="banner">
        <div class="audubon-site__header-inner">
            <h1 class="audubon-site__title">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
            </h1>
            <nav class="audubon-site__nav" role="navigation" aria-label="メインメニュー">
                <?php
                if ( has_nav_menu( 'primary' ) ) {
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'depth'          => 2,
                        'fallback_cb'    => false,
                    ) );
                }
                ?>
            </nav>
        </div>
    </header>

    <div class="audubon-site__main" id="content">
