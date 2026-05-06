<?php get_header(); ?>
	<div id="container">
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
			
			<div class="contents single-area">
				<div class="subpage-area">
					<?php if(have_posts()): while(have_posts()): the_post(); ?>
					<article <?php post_class( 'single-post' ); ?>>
						<h5><?php the_title(); ?></h5>

						<div class="single-date">
							<time datetime="<?php echo get_the_date( 'y-m-d' ); ?>">
								<p><?php echo get_the_date(); ?></p>
							</time>
						</div>
						<div class="single-cat">
							<?php the_category(); ?>
						</div>	

						<div class="single-text"><?php the_content(); ?></div>

						<div class="previous-next">
							<div class="page-previous">
								<p><?php previous_post_link('%link', '&lt;&nbsp;前の記事'); ?></p>
							</div>
							<div class="page-next">
								<p><?php next_post_link('%link', '次の記事&nbsp;&gt;'); ?></p>
							</div>
						</div>
					</article>
					<?php endwhile; endif; ?>
				</div>

				
			</div>
		</article>
	</div>
<?php get_footer(); ?>