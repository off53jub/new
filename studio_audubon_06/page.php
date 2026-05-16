<?php
/**
*Template Name: 固定ページのテンプレート
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
			<div class="contents subpage-area">
				<?php if(have_posts()): while(have_posts()): the_post(); ?>

					<h2 class="template-title"><?php the_title(); ?></h1>

					<div class="single-text"><?php the_content(); ?></div>
					<div class="previous-next">

					</div>
				<?php endwhile; endif; ?>
			</div>
		</article>
	</div>
	<?php get_footer(); ?>