<?php
/**
 * archive-news_list.php
 * ニュース（CPT: news_list）の一覧ページ。
 * 投稿の作成日ではなく、フリーテキストの「放映日時」を表示します。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<main class="audubon-archive-news" style="max-width:920px;margin:0 auto;padding:24px;">
    <header>
        <h1>News</h1>
    </header>

    <?php if ( have_posts() ) : ?>
        <ul class="audubon-news-list" style="list-style:none;padding:0;margin:0;">
            <?php while ( have_posts() ) : the_post(); ?>
                <li style="border-bottom:1px solid #eee;padding:16px 0;">
                    <div style="display:flex;gap:16px;align-items:baseline;flex-wrap:wrap;">
                        <span class="audubon-news-list__date" style="color:#666;">
                            <?php
                            if ( function_exists( 'audubon_get_news_display_date' ) ) {
                                echo esc_html( audubon_get_news_display_date() );
                            } else {
                                the_date();
                            }
                            ?>
                        </span>
                        <a href="<?php the_permalink(); ?>" style="font-weight:600;color:inherit;text-decoration:none;">
                            <?php the_title(); ?>
                        </a>
                    </div>
                    <?php if ( has_excerpt() ) : ?>
                        <p style="margin:8px 0 0;color:#444;"><?php echo esc_html( get_the_excerpt() ); ?></p>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <nav class="audubon-pagination" style="margin-top:24px;">
            <?php the_posts_pagination(); ?>
        </nav>
    <?php else : ?>
        <p>記事がまだありません。</p>
    <?php endif; ?>
</main>
<?php
get_footer();
