<?php
/**
 * Template Name: Works (A4ポスター一覧)
 *
 * Worksは通常投稿で運用されているため、このテンプレートを固定ページに割り当てると
 * その固定ページに通常投稿一覧（A4ポスター比率）が表示されます。
 *
 * 使い方:
 *   1. 管理画面 > 固定ページ > 新規追加 で「Works」というページを作成
 *   2. 右サイドバーの「テンプレート」で "Works (A4ポスター一覧)" を選択
 *   3. 公開
 *
 * もしくは固定ページの本文に [audubon_works] ショートコードを貼るだけでもOKです。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$query = new WP_Query( array(
    'post_type'      => 'post',
    'posts_per_page' => 12,
    'post_status'    => 'publish',
    'paged'          => $paged,
    'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
) );
?>
<main class="audubon-page-works" style="max-width:1080px;margin:0 auto;padding:24px;">
    <?php while ( have_posts() ) : the_post(); ?>
        <header>
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        <?php if ( get_the_content() ) : ?>
            <div class="entry-content"><?php the_content(); ?></div>
        <?php endif; ?>
    <?php endwhile; ?>

    <?php if ( $query->have_posts() ) : ?>
        <ul class="audubon-works audubon-works--cols-3">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <li class="audubon-works__item">
                    <a href="<?php the_permalink(); ?>" class="audubon-works__link">
                        <div class="audubon-works__poster">
                            <?php
                            if ( has_post_thumbnail() ) {
                                the_post_thumbnail( 'large', array( 'class' => 'audubon-works__image' ) );
                            }
                            ?>
                        </div>
                        <p class="audubon-works__title"><?php the_title(); ?></p>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>

        <nav class="audubon-pagination" style="margin-top:24px;">
            <?php
            echo paginate_links( array(
                'total'   => $query->max_num_pages,
                'current' => $paged,
            ) );
            ?>
        </nav>
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <p>作品がまだありません。</p>
    <?php endif; ?>
</main>
<?php
get_footer();
