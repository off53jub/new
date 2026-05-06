<?php
/**
*Template Name: homepage
**/
?>		
<?php get_header();?>
<style>

 .carousel {
	 width: 100%;
	 height: 58vh;
	 border-radius: 3px;
	 overflow: hidden;
	 position: relative;
	 box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}
 .carousel:hover .controls {
	 opacity: 1;
}
 .carousel .controls {
	 opacity: 0;
	 display: flex;
	 position: absolute;
	 top: 50%;
	 left: 0;
	 justify-content: space-between;
	 width: 100%;
	 z-index: 99999;
	 transition: all ease 0.5s;
}
 .carousel .controls .control {
	 margin: 0 5px;
	 display: flex;
	 align-items: center;
	 justify-content: center;
	 height: 40px;
	 width: 40px;
	 border-radius: 50%;
	 background-color: rgba(255, 255, 255, 0.7);
	 opacity: 0.5;
	 transition: ease 0.3s;
	 cursor: pointer;
}
 .carousel .controls .control:hover {
	 opacity: 1;
}
 .carousel .slides {
	 position: absolute;
	 top: 50%;
	 left: 0;
	 transform: translateY(-50%);
	 display: flex;
	 width: 100%;
	 transition: 1s ease-in-out all;
}
 .carousel .slides .slide {
    min-width: 100%;
    height: auto;
    object-fit: cover;
    object-position: center;
}
 
</style>
<div id="container" class="toppage">

  <div id="main-image">
	  <div class="slideshow">
		   <div class="carousel">
			  <div class="slides">
				  <?php   
					  $args = array(
						  'post_type' => 'slides',
						  'posts_per_page'=>4,
						  'orderby' => 'date',
						  'order' => 'ASC',
					  );
					  $films = new WP_Query( $args );  
					  $postnum = 0; // Set counter to 0 outside the loop
					  if ( $films->have_posts() ) :
					  while ( $films->have_posts() ) : $films->the_post(); 
					  $postnum++;
				  ?>
				  <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); ?>
					<img src="<?php echo $image[0]; ?>" alt="slide image" class="slide">
				<?php 
				  endwhile; 
				  endif;
				  wp_reset_query() 
				?>
			  </div>
			  <div class="controls">
				<div class="control prev-slide">&#9668;</div>
				<div class="control next-slide">&#9658;</div>
			  </div>
			</div>
	  </div>
  </div>

  <section id="news">
    <div class="contents news-area">
      <h2 class="section-title">News</h2>
      <div class="news-list-inner">
        <?php
$args = array(
    'posts_per_page' => 5, // 表示件数の指定
    'post_type' => 'news_list',
);
$posts = get_posts($args);
foreach ($posts as $post): // ループの開始
    setup_postdata($post); // 記事データの取得
    ?>
        <div class="news-list">
          <a href="<?php the_permalink();?>">
            <p class="news-date"><time
                datetime="<?php echo get_the_date('y-m-d'); ?>"><?php echo get_the_date(); ?></time></p>
            <p class="news-title"><?php echo wp_trim_words(get_the_title(), 40, '...'); ?></p>
          </a>
        </div>
        <?php
endforeach; // ループの終了
wp_reset_postdata(); // 直前のクエリを復元する
?>
      </div>
    </div>
  </section>

  <hr>

  <section id="pickup">
    <div class="contents">
      <h2 class="section-title">Pick up</h2>
      <div class="flex">
        <figure class="image-wrapper">
          <img src="<?php echo get_template_directory_uri(); ?>/images/pickup_thumnail_01.jpg" alt="pickupアイキャッチ画像その1">
        </figure>
        <figure class="image-wrapper">
			<?php the_field('pickup_youtube_code'); ?>
<!--           <figcaption>白石加代子「百物語」シリーズ無料配信スタート！</figcaption> -->
        </figure>
      </div>
    </div>
	<div class="contents">
      <div class="flex">
        <figure class="image-wrapper">
        	<a href="<?php the_field('first_banner_link'); ?>"><img src="<?php the_field('first_banner'); ?>" alt=""></a> 
        </figure>
        <figure class="image-wrapper">
			<a href="<?php the_field('second_banner_link'); ?>"><img src="<?php the_field('second_banner'); ?>" alt=""></a>
        </figure>
      </div>
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
            <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.png" alt="デフォルト画像" />
            <?php endif;?>

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
<script>
const delay = 3000; //ms

const slides = document.querySelector(".slides");
const slidesCount = slides.childElementCount;
const maxLeft = (slidesCount - 1) * 100 * -1;

let current = 0;

function changeSlide(next = true) {
  if (next) {
    current += current > maxLeft ? -100 : current * -1;
  } else {
    current = current < 0 ? current + 100 : maxLeft;
  }

  slides.style.left = current + "%";
}

let autoChange = setInterval(changeSlide, delay);
const restart = function() {
  clearInterval(autoChange);
  autoChange = setInterval(changeSlide, delay);
};

// Controls
document.querySelector(".next-slide").addEventListener("click", function() {
  changeSlide();
  restart();
});

document.querySelector(".prev-slide").addEventListener("click", function() {
  changeSlide(false);
  restart();
});

</script>