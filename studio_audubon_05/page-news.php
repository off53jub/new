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
	    <div class="contents news-area-column">
	      <h2 class="section-title">Information</h2>
	      <div class="news-list-inner news-page">
	        <?php
$args = array(
    'paged' => $paged,
    'post_type' => 'news_list',
    'posts_per_page' => 10, // 表示件数の指定
    // 並び替え用日付（_audubon_broadcast_sort）が設定されていればそれを優先、無ければ投稿日。
    // OR + EXISTS / NOT EXISTS にしないと「メタが無い既存投稿」が除外されてしまうので注意。
    'meta_query' => array(
        'relation'     => 'OR',
        'with_sort'    => array( 'key' => '_audubon_broadcast_sort', 'compare' => 'EXISTS' ),
        'without_sort' => array( 'key' => '_audubon_broadcast_sort', 'compare' => 'NOT EXISTS' ),
    ),
    'orderby' => array(
        'with_sort' => 'DESC',
        'date'      => 'DESC',
    ),
);
$the_query = new WP_Query($args);
if ($the_query->have_posts()): while ($the_query->have_posts()): $the_query->the_post();
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
	            <a href="<?php the_permalink();?>"><?php echo wp_trim_words(get_the_title(), 40, '...'); ?></a>
	          </p>
	        </div>
	        <?php endwhile;else: ?>
	      </div>
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
	  </article>
	</div>
	<?php get_footer();?>