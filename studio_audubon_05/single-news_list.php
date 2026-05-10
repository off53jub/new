<?php
/**
 * single-news_list.php
 * ニュース（CPT: news_list）の単体ページ。
 * 投稿の作成日ではなく、編集画面で入力した「表示日時」フリーテキストを表示します。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<div id="container">
  <article>
    <div class="contents">
      <div class="bread">
        <ol>
          <li>
            <a href="<?php echo home_url(); ?>">ホーム</a>
          </li>
          <li>
            <?php if (has_category()): ?>
            <?php $postcat = get_the_category();?>
            <?php echo get_category_parents($postcat[0], true, '</li><li>'); ?>
            <?php endif;?>
            <a><?php the_title();?></a>
          </li>
        </ol>
      </div>
    </div>

    <div class="contents single-area">
      <div class="subpage-area">
        <?php if (have_posts()): while (have_posts()): the_post();?>
        <article <?php post_class('single-post');?>>
          <h5><?php the_title();?></h5>

          <?php
          $audubon_news_date = function_exists( 'audubon_get_news_display_date' )
              ? audubon_get_news_display_date()
              : '';
          if ( $audubon_news_date !== '' ) :
          ?>
          <div class="single-date">
            <p><?php echo esc_html( $audubon_news_date ); ?></p>
          </div>
          <?php endif; ?>

          <div class="single-cat">
            <?php the_category();?>
          </div>

          <div class="single-text">
            <?php
            // 本文が入っていればそのまま表示（既存運用は維持）。
            // 空の場合のみ、表示日時 + 出演アクターから最低限の内容を自動生成。
            $audubon_post_content = trim( strip_tags( get_the_content() ) );
            if ( $audubon_post_content !== '' ) :
                the_content();
            else :
                $audubon_actor_ids = get_post_meta( get_the_ID(), '_audubon_related_actors', true );
                $audubon_actor_links = array();
                if ( is_array( $audubon_actor_ids ) ) {
                    foreach ( $audubon_actor_ids as $aid ) {
                        $a = get_post( (int) $aid );
                        if ( ! $a || $a->post_status !== 'publish' ) continue;
                        $audubon_actor_links[] = sprintf(
                            '<a href="%s">%s</a>',
                            esc_url( get_permalink( $a ) ),
                            esc_html( get_the_title( $a ) )
                        );
                    }
                }
                ?>
                <p>
                    <?php if ( $audubon_news_date !== '' ) : ?>
                        <strong>日時：</strong><?php echo esc_html( $audubon_news_date ); ?><br>
                    <?php endif; ?>
                    <?php if ( ! empty( $audubon_actor_links ) ) : ?>
                        <strong>出演：</strong><?php echo implode( ' / ', $audubon_actor_links ); ?>
                    <?php endif; ?>
                </p>
                <p style="color:#777;">詳細は近日公開予定です。</p>
                <?php
            endif;
            ?>
          </div>

          <div class="previous-next">
            <div class="page-previous">
              <p><?php previous_post_link('%link', '&lt;&nbsp;前の記事');?></p>
            </div>
            <div class="page-next">
              <p><?php next_post_link('%link', '次の記事&nbsp;&gt;');?></p>
            </div>
          </div>
        </article>
        <?php endwhile;endif;?>
      </div>
    </div>
  </article>
</div>
<?php get_footer();?>
