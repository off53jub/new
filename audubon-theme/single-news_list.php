<?php
/**
 * single-news_list.php
 * ニュース（CPT: news_list）の単体ページ。
 * 投稿の作成日ではなく、編集画面で入力した「放映日時」を表示します。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main class="audubon-single-news" style="max-width:920px;margin:0 auto;padding:24px;">
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header>
                <p class="audubon-news-meta" style="color:#666;margin:0 0 8px;">
                    <?php
                    if ( function_exists( 'audubon_get_news_display_date' ) ) {
                        echo esc_html( audubon_get_news_display_date() );
                    } else {
                        the_date();
                    }
                    ?>
                </p>
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="audubon-news-thumbnail" style="margin:16px 0;">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
            <?php endif; ?>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
