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
							<?php if( $cat ): ?>
							<?php $catdata=get_category( $cat ); ?>
							<?php if ( $catdata->parent ): ?>
							<?php echo get_category_parents( $catdata->parent, true, '</li><li>' ); ?>
							<?php endif; ?>
							<?php endif; ?>
							<a><?php single_term_title(); ?></a>
						</li>
					</ol>
				</div>
			</div>
			<div class="contents works-area">
				<h2>WORKS</h2>
				<div class="works-inner">
					<?php if(have_posts()): while(have_posts()): the_post(); ?>
					<div class="works-list">
						<div class="works-image">
							<a href="<?php the_permalink(); ?>"><img src="<?php echo mythumb( 'medium' ); ?>" alt=""></a>
						</div>
						<div class="works-text">
							<div class="works-text-inner">
								<p class="works-date"><a href="<?php the_permalink(); ?>">2015.12.31</a></p>
								<div class="works-cat"><?php the_category(); ?></div>
							</div>
							<p class="works-title"><a href="<?php the_permalink(); ?>"><?php echo wp_trim_words( get_the_title(), 30, '...' ); ?></a></p>
						</div>
					</div><?php endwhile; else: ?>
					<p><?php echo "お探しの記事、ページは見つかりませんでした。"; ?></p>
					<?php endif; ?>
				</div>
				<div class="pagination pagination-index">
					<?php echo paginate_links( array( 
						'type' => 'list',
						'prev_text' => '&laquo',
						'next_text' => '&raquo'
					) ); ?>
				</div>	
			</div>
	</article>
	
	<?php get_footer(); ?>