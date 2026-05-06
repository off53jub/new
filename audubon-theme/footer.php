<?php
/**
 * footer.php
 * すべてのページの下部HTMLを出力します。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
    </div><!-- .audubon-site__main -->

    <footer class="audubon-site__footer" role="contentinfo">
        <?php
        if ( has_nav_menu( 'footer' ) ) {
            wp_nav_menu( array(
                'theme_location' => 'footer',
                'container'      => false,
                'depth'          => 1,
                'fallback_cb'    => false,
            ) );
        }
        ?>
        <p>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
    </footer>
</div><!-- .audubon-site -->
<?php wp_footer(); ?>
</body>
</html>
