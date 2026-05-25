<?php get_header();?>
<div id="container" class="toppage">
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
    <div class="contents works-area">
      <h2 class="section-title">Works</h2>
      <div class="works-inner">
        <div class="image-wrapper">
          <img src="http://studio-audubon.gridrocks.com/wp/wp-content/uploads/2020/11/actor-work-thumbnail-01.jpg"
            alt="白石加代子の「百物語」バナー画像">
        </div>
        <?php
$args = array(
    'paged' => $paged,
    'post_type' => 'post',
    'category_name' => 'works',
    'posts_per_page' => 12, // 表示件数の指定
    'orderby' => 'date',
    'order'   => 'DESC', // 最新の作品を上に表示
);
$the_query = new WP_Query($args);
if ($the_query->have_posts()): while ($the_query->have_posts()): $the_query->the_post();
        ?>
        <div class="works-list">
          <div class="works-image">
            <a href="<?php the_permalink();?>">
              <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
              <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.png" alt="" loading="lazy">
              <?php endif; ?>
            </a>
          </div>
          <div class="works-text">
            <div class="works-text-inner">
              <p class="works-date"><time
                  datetime="<?php echo get_the_date('y-m-d'); ?>"><?php echo get_the_date(); ?></time></p>
              <div class="works-cat"><?php the_category();?></div>
            </div>
            <p class="works-title"><a
                href="<?php the_permalink();?>"><?php echo wp_trim_words(get_the_title(), 30, '...'); ?></a></p>
          </div>
        </div><?php endwhile;else: ?>
        <p><?php echo "お探しの記事、ページは見つかりませんでした。"; ?></p>
        <?php endif;?>
      </div>
      <div class="pagination pagination-index">
        <?php
if ($the_query->max_num_pages > 1) {
    echo paginate_links(array(
        'base' => get_pagenum_link(1) . '%_%',
        'format' => 'page/%#%/',
        'current' => max(1, $paged),
        'total' => $the_query->max_num_pages,
        'type' => 'list',
        'prev_text' => '&laquo',
        'next_text' => '&raquo',
    ));
}
wp_reset_postdata();
?>
      </div>
    </div>
  </article>
  <?php get_footer();?>