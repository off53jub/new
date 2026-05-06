<?php
/**
 * single-news_list.php
 * 既存CPT `news_list` の単体ページのフォールバックテンプレート。
 *
 * 投稿の作成日ではなく、編集画面で入力したフリーテキストの「放映日時」を表示します。
 * 後で新テーマを作る際は、このファイルをそのまま新テーマの直下にコピーしてください。
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
                    <?php echo esc_html( audubon_get_news_display_date() ); ?>
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
