<?php
/**
 * アクター詳細ページのフォールバックテンプレート。
 * テーマ側に single-audubon_actor.php がある場合はそちらが優先されます。
 */
get_header();
?>
<main class="audubon-single-actor" style="max-width:920px;margin:0 auto;padding:24px;">
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header>
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="audubon-actor-thumbnail" style="margin:16px 0;">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
            <?php endif; ?>

            <?php echo do_shortcode( '[audubon_actor_links]' ); ?>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
