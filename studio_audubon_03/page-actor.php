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
				<div class="actors-inner">
			        <ul>
			        	<?php   
				          $args = array(
				            'post_type' => 'actor',
				            'posts_per_page'=>24,
				            'orderby' => 'menu_order',
				            'order' => 'ASC',
				          );
				          $films = new WP_Query( $args );  
				            $postnum = 0; // Set counter to 0 outside the loop
				            if ( $films->have_posts() ) :
				            while ( $films->have_posts() ) : $films->the_post(); 
				            $postnum++;
				        ?>
				        <li>
				            <div class="thumbnail">
				            	<a href="<?php the_permalink() ?>">
				            		<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
				            		<img width="299" height="350" src="<?php echo $image[0]; ?>" class="attachment-actor_thumbnail size-actor_thumbnail wp-post-image" alt="" loading="lazy" srcset="<?php echo $image[0]; ?>">
				            	</a>
				            </div>
				            <div class="title">
				            	<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
				            </div>
				        </li>
				        <?php 
				          endwhile; 
				          endif;
				          wp_reset_query() 
				        ?>
					</ul>
			    </div>
			</div>
		</article>
	</div>
	<?php get_footer(); ?>