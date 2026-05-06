<?php
/**
 * index.php
 * 最も汎用的なテンプレート。他に該当するテンプレートが無い場合のフォールバックです。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<?php if ( have_posts() ) : ?>
    <?php if ( is_home() && ! is_front_page() ) : ?>
        <header>
            <h1><?php single_post_title(); ?></h1>
        </header>
    <?php endif; ?>

    <ul class="audubon-entry-list">
        <?php while ( have_posts() ) : the_post(); ?>
            <li class="audubon-entry-list__item">
                <h2 class="audubon-entry-list__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <?php if ( has_excerpt() ) : ?>
                    <p><?php echo esc_html( get_the_excerpt() ); ?></p>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>

    <nav class="audubon-pagination">
        <?php the_posts_pagination(); ?>
    </nav>
<?php else : ?>
    <p>記事がまだありません。</p>
<?php endif; ?>
<?php
get_footer();
