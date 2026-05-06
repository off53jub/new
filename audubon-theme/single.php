<?php
/**
 * single.php
 * 通常投稿（Works含む）の単一ページ表示。
 * CPT固有のテンプレート（single-actor.php / single-news_list.php）は別ファイルで提供。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<?php while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header>
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <p style="color:#777;font-size:0.9rem;margin:0 0 16px;">
                <?php echo esc_html( get_the_date() ); ?>
            </p>
        </header>
        <?php if ( has_post_thumbnail() ) : ?>
            <div class="entry-thumbnail" style="margin:16px 0;">
                <?php the_post_thumbnail( 'large' ); ?>
            </div>
        <?php endif; ?>
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
    </article>

    <?php
    if ( comments_open() || get_comments_number() ) {
        comments_template();
    }
    ?>
<?php endwhile; ?>
<?php
get_footer();
