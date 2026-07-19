<?php
/**
 * Pickup 一覧ページ（独立ページ /pickup/）
 * - 「Pickup」CPT の項目を 16:9 のグリッドで一覧表示
 * - 画像タップで「リンク先URL」へ移動（設定時）
 * - 「ダウンロード用ファイル」または表示中の画像をダウンロードできるボタンを表示
 */
get_header(); ?>
<div id="container" class="toppage">
  <article>
    <div class="contents">
      <div class="bread">
        <ol>
          <li><a href="<?php echo home_url(); ?>">ホーム</a></li>
          <li><a>Pickup</a></li>
        </ol>
      </div>
    </div>

    <div class="contents pickup-area">
      <h2 class="section-title">Pickup</h2>

      <?php if ( have_posts() ) : ?>
      <div class="audubon-pickup audubon-pickup--archive">
        <?php while ( have_posts() ) : the_post();
            $link  = get_post_meta( get_the_ID(), '_audubon_pickup_link', true );
            $thumb = has_post_thumbnail()
                ? get_the_post_thumbnail( get_the_ID(), 'large', array(
                      'loading' => 'lazy',
                      'alt'     => esc_attr( get_the_title() ),
                  ) )
                : '';
            ?>
        <figure class="audubon-pickup__item">
          <?php if ( $thumb ) : ?>
              <?php if ( $link ) : ?>
              <a href="<?php echo esc_url( $link ); ?>"><?php echo $thumb; ?></a>
              <?php else : ?>
              <?php echo $thumb; ?>
              <?php endif; ?>
          <?php endif; ?>

          <?php if ( get_the_title() ) : ?>
          <figcaption class="audubon-pickup__caption"><?php echo esc_html( get_the_title() ); ?></figcaption>
          <?php endif; ?>
        </figure>
        <?php endwhile; ?>
      </div>
      <?php else : ?>
      <p style="text-align:center;color:#666;">現在Pickupはありません。</p>
      <?php endif; wp_reset_postdata(); ?>
    </div>
  </article>
</div>
<?php get_footer(); ?>
