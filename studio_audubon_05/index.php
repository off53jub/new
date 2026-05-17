<?php
/**
*Template Name: homepage
**/
?>
<?php get_header();?>
<div id="container" class="toppage">

  <?php
  // TOPICSスライダー（slides CPT）
  if ( function_exists( 'audubon_render_slides_banner' ) ) {
      audubon_render_slides_banner();
  }
  ?>

  <hr>

  <section id="news">
    <div class="contents news-area">
      <h2 class="section-title">Information</h2>
      <div class="news-list-inner">
        <?php
$args = array(
    'posts_per_page' => 10, // 表示件数の指定（Information ホームページ表示）
    'post_type'      => 'news_list',
    'orderby'        => 'date',
    'order'          => 'DESC', // 最新の投稿が上
);
$posts = get_posts($args);
foreach ($posts as $post): // ループの開始
    setup_postdata($post); // 記事データの取得
    ?>
        <div class="news-list">
          <?php
          $audubon_news_date = function_exists( 'audubon_get_news_display_date' )
              ? audubon_get_news_display_date()
              : '';
          if ( $audubon_news_date !== '' ) :
          ?>
          <p class="news-date"><span><?php echo esc_html( $audubon_news_date ); ?></span></p>
          <?php endif; ?>
          <p class="news-title">
            <a href="<?php the_permalink();?>"><?php the_title(); ?></a>
          </p>
        </div>
        <?php
endforeach; // ループの終了
wp_reset_postdata(); // 直前のクエリを復元する
?>
      </div>
      <div class="works-button">
        <a href="<?php echo home_url('/news'); ?>">INFORMATION一覧はこちら</a>
      </div>
    </div>
  </section>

  <hr>

  <section id="pickup">
    <div class="contents">
      <h2 class="section-title">Pick up</h2>
      <?php
      // 動的Pickup（CPT: audubon_pickup）が登録されていればそれを表示。
      // 0件 or 関数未定義の場合は旧ACFフィールドのフォールバックで表示。
      $rendered_dynamic = false;
      if ( function_exists( 'audubon_render_pickup_section' ) ) {
          $rendered_dynamic = audubon_render_pickup_section();
      }
      if ( ! $rendered_dynamic ) :
      ?>
      <div class="flex">
        <figure class="image-wrapper">
          <a href="https://studio-audubon.jp/actor/shiraishi_kayoko#hyakumonogatari" aria-label="白石加代子「百物語」">
            <img src="<?php echo get_template_directory_uri(); ?>/images/pickup_thumnail_01.jpg" alt="白石加代子の「百物語」" loading="lazy">
          </a>
        </figure>
        <figure class="image-wrapper">
			<?php the_field('pickup_youtube_code'); ?>
        </figure>
      </div>
    </div>
	<div class="contents">
      <div class="flex">
        <figure class="image-wrapper">
        	<a href="<?php the_field('first_banner_link'); ?>"><img src="<?php the_field('first_banner'); ?>" alt="" loading="lazy"></a>
        </figure>
        <figure class="image-wrapper">
			<a href="<?php the_field('second_banner_link'); ?>"><img src="<?php the_field('second_banner'); ?>" alt="" loading="lazy"></a>
        </figure>
      </div>
      <?php endif; ?>
    </div>
  </section>
    

  <hr>



  <article>
    <div class="contents works-area">
      <h2 class="section-title">Works</h2>
      <div class="works-inner">

        <?php
$args = array(
    'paged' => $paged,
    'post_type' => 'post',
    'category_name' => 'works',
    'order' => 'DESC',
    'posts_per_page' => 12, // 表示件数の指定
);
$the_query = new WP_Query($args);
if ($the_query->have_posts()): while ($the_query->have_posts()): $the_query->the_post();
        ?>
        <div class="works-list">
          <div class="works-image">
            <?php if (has_post_thumbnail()): ?>
            <a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>">
              <?php the_post_thumbnail('small_thumbnail');?>
            </a>
            <?php else: ?>
            <!--アイキャッチ画像がない場合は、デフォルトの画像を表示-->
            <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.png" alt="デフォルト画像" loading="lazy" />
            <?php endif;?>

          </div>
          <div class="works-text">
            <div class="works-text-inner">
              <p class="works-date"><time
                  datetime="<?php echo get_the_date('y-m-d'); ?>"><?php echo get_the_date(); ?></time></p>
              <div class="works-cat"><?php the_category();?></div>
            </div>
            <p class="works-title"><a
                href="<?php the_permalink();?>"><?php the_title(); ?></a></p>
          </div>
        </div>
        <?php endwhile;endif;?>
      </div>

      <div class="works-button">
        <a href="<?php echo home_url('/works'); ?>">WORKS一覧はこちら</a>
      </div>
    </div>
  </article>
</div>
<?php get_footer();?>