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
	      <?php
	      // クエリ文字列 ?actor=ID でアクター絞り込み（v06）
	      $audubon_filter_actor_id   = isset( $_GET['news_actor'] ) ? absint( $_GET['news_actor'] ) : 0;
	      $audubon_filter_actor_name = '';
	      if ( $audubon_filter_actor_id ) {
	          $a = get_post( $audubon_filter_actor_id );
	          if ( $a && $a->post_type === 'actor' && $a->post_status === 'publish' ) {
	              $audubon_filter_actor_name = get_the_title( $a );
	          } else {
	              $audubon_filter_actor_id = 0; // 無効なIDなら無視
	          }
	      }
	      ?>
	      <h2 class="section-title">
	          <?php if ( $audubon_filter_actor_name ) : ?>
	              <?php echo esc_html( $audubon_filter_actor_name ); ?> の Information
	          <?php else : ?>
	              Information
	          <?php endif; ?>
	      </h2>
	      <?php if ( $audubon_filter_actor_name ) : ?>
	          <p class="audubon-news-filter-back">
	              <a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">← すべての Information を見る</a>
	          </p>
	      <?php endif; ?>
	      <div class="news-list-inner news-page">
	        <?php
$args = array(
    'paged' => $paged,
    'post_type' => 'news_list',
    'posts_per_page' => 10, // 表示件数の指定
    // 並び替え用日付（_audubon_broadcast_sort）が設定されていればそれを優先、無ければ投稿日。
    'meta_query' => array(
        'relation'     => 'AND',
        'sort_group'   => array(
            'relation'     => 'OR',
            'with_sort'    => array( 'key' => '_audubon_broadcast_sort', 'compare' => 'EXISTS' ),
            'without_sort' => array( 'key' => '_audubon_broadcast_sort', 'compare' => 'NOT EXISTS' ),
        ),
    ),
    'orderby' => array(
        'with_sort' => 'DESC',
        'date'      => 'DESC',
    ),
);
// アクター絞り込み: _audubon_related_actors に該当IDが含まれる記事だけに。
// 保存形式が integer の場合は ;i:12;、string の場合は :"12"; とシリアライズされるため、
// 両方を OR でカバー。
if ( $audubon_filter_actor_id ) {
    $args['meta_query'][] = array(
        'relation' => 'OR',
        array(
            'key'     => '_audubon_related_actors',
            'value'   => sprintf( ';i:%d;', $audubon_filter_actor_id ),
            'compare' => 'LIKE',
        ),
        array(
            'key'     => '_audubon_related_actors',
            'value'   => sprintf( ':"%d";', $audubon_filter_actor_id ),
            'compare' => 'LIKE',
        ),
    );
}
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
	      <p><?php
	        if ( $audubon_filter_actor_name ) {
	            echo esc_html( $audubon_filter_actor_name ) . ' に関する Information はまだありません。';
	        } else {
	            echo 'お探しの記事、ページは見つかりませんでした。';
	        }
	      ?></p>
	      <?php endif;?>
	    </div>
	    <div class="pagination pagination-index">
	      <?php
if ($the_query->max_num_pages > 1) {
    $pagination_args = array(
        'base' => get_pagenum_link(1) . '%_%',
        'format' => 'page/%#%/',
        'current' => max(1, $paged),
        'total' => $the_query->max_num_pages,
        'type' => 'list',
        'prev_text' => '&laquo',
        'next_text' => '&raquo',
    );
    // アクター絞り込み中はクエリ文字列を維持
    if ( $audubon_filter_actor_id ) {
        $pagination_args['add_args'] = array( 'news_actor' => $audubon_filter_actor_id );
    }
    echo paginate_links( $pagination_args );
}
wp_reset_postdata();
?>
	    </div>
	  </article>
	</div>
	<?php get_footer();?>