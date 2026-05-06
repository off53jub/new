<?php
/**
 * Information一覧ページのフォールバックテンプレート。
 * 投稿日ではなく「放映日時」を表示する点が円企画スタイルです。
 */
get_header();
?>
<main class="audubon-archive-information" style="max-width:920px;margin:0 auto;padding:24px;">
    <header>
        <h1>Information</h1>
    </header>

    <?php if ( have_posts() ) : ?>
        <ul class="audubon-information-list" style="list-style:none;padding:0;margin:0;">
            <?php while ( have_posts() ) : the_post(); ?>
                <li style="border-bottom:1px solid #eee;padding:16px 0;">
                    <div style="display:flex;gap:16px;align-items:baseline;flex-wrap:wrap;">
                        <time style="color:#666;font-variant-numeric:tabular-nums;">
                            <?php
                            $label = audubon_get_information_label( get_the_ID() );
                            if ( $label ) {
                                echo esc_html( $label ) . '：';
                            }
                            echo esc_html( audubon_get_information_display_date() );
                            ?>
                        </time>
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
