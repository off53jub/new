<?php
/**
*Template Name: page-actor
**/
?>		
	<?php get_header(); ?>	
	<div id="container" class="toppage">
		<article>
			<div class="contents">
				<div class="bread">
					<ol>
						<li>
							<a href="<?php echo home_url(); ?>">ホーム</a>
						</li>
						<li>
							<?php if( has_category() ): ?>
							<?php $postcat=get_the_category(); ?>
							<?php echo get_category_parents ( $postcat[0],true, '</li><li>' ); ?>
							<?php endif; ?>
							<a><?php the_title(); ?></a>
						</li>
					</ol>
				</div>
			</div>
		</article>
		<article>
			<div class="contents actors-area">
				<?php
				// 絞り込みUI
				if ( function_exists( 'audubon_actor_filter_shortcode' ) ) {
					echo audubon_actor_filter_shortcode();
				}
				?>
				<div class="actors-inner">
			        <ul>
			        	<?php
				          $args = array(
				            'post_type' => 'actor',
				            'posts_per_page'=>24,
				            'orderby' => 'menu_order',
				            'order' => 'ASC',
				          );
				          // 絞り込み条件をクエリに反映
				          $tax_query = array();
				          foreach ( array( 'audubon_actor_gender', 'audubon_actor_age', 'audubon_actor_genre', 'audubon_actor_specialty' ) as $tax ) {
				              if ( ! empty( $_GET[ $tax ] ) ) {
				                  $tax_query[] = array(
				                      'taxonomy' => $tax,
				                      'field'    => 'slug',
				                      'terms'    => sanitize_text_field( wp_unslash( $_GET[ $tax ] ) ),
				                  );
				              }
				          }
				          if ( count( $tax_query ) > 1 ) {
				              $tax_query['relation'] = 'AND';
				          }
				          if ( ! empty( $tax_query ) ) {
				              $args['tax_query'] = $tax_query;
				          }
				          $films = new WP_Query( $args );
				            $postnum = 0;
				            if ( $films->have_posts() ) :
				            while ( $films->have_posts() ) : $films->the_post();
				            $postnum++;
				        ?>
				        <li>
				            <div class="thumbnail">
				            	<a href="<?php the_permalink() ?>">
				            		<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
				            		<img width="299" height="350" src="<?php echo $image[0]; ?>" class="attachment-actor_thumbnail size-actor_thumbnail wp-post-image" alt="<?php the_title_attribute(); ?>" loading="lazy">
				            	</a>
				            </div>
				            <div class="title">
				            	<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
				            </div>
				        </li>
				        <?php
				          endwhile;
				          else: ?>
				          <li class="audubon-actor-list__empty" style="grid-column:1/-1;text-align:center;padding:40px 0;color:#666;">
				              該当するアクターが見つかりませんでした。
				          </li>
				        <?php endif;
				          wp_reset_query()
				        ?>
					</ul>
			    </div>
			</div>
		</article>
	</div>
	<?php get_footer(); ?>