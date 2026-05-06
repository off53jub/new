<?php
/* Template Name Posts: single-actor
Template Post Type: actor
 */
?>
<?php get_header();?>
<style type="text/css">
  .carousel-item img.big-img{
    height: 450px;
    object-fit: cover;
    object-position: center;
  }
  .thumb img{
    width: 67px;
    height: 67px;
    object-fit: cover;
    object-position: center;
  }
  .player-name .sub {
    font-size: 14px;
    display: block;
  }
  .modal-body {
      position: relative;
      -ms-flex: 1 1 auto;
      flex: 1 1 auto;
      padding: 5px !important;
  }
</style>
<div id="container">
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

      <div class="contents">
        <div class="subpage-area">
          <?php if (have_posts()): while (have_posts()): the_post();?>
            <article <?php post_class('single-post');?>>
              <!-- <div class="single-date">
                <time datetime="<?php echo get_the_date('y-m-d'); ?>">
                  <p><?php echo get_the_date(); ?></p>
                </time>
              </div> -->
              <div class="single-cat">
                <?php the_category();?>
              </div>

              <!--<div class="image-wrapper">
        <?php //if (has_post_thumbnail()): ?>
        <a href="<?php //the_permalink();?>" title="<?php //the_title_attribute();?>">
        <?php //the_post_thumbnail();?></a>
        </div> -->
        <div class="row" id="actor">
          <div class="col-xl-5">
            <?php if( have_rows('profile') ): while( have_rows('profile') ): the_row(); ?>
            <?php 
              /* FRONT-PAGE SLIDER WITH ACF GALLERY AND BOOTSTRAP 4 CAROUSEL */
              $images = get_sub_field('actor_gallery');
              $count=0;
              $count1=0;
              if($images) : 
            ?>
            <div class="carousel-container">
              <!-- Sorry! Lightbox doesn't work - yet. -->
              <div id="myCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                  <?php foreach( $images as $image ): ?>
                    <div class="carousel-item <?php if($count1==0) : echo ' active'; endif; ?>" data-slide-number="<?php echo $count; ?>">
                      <img src="<?php echo $image['url']; ?>" class="d-block w-100 img-fluid big-img" alt="..." data-remote="<?php echo $image['url']; ?>" data-type="image" data-toggle="lightbox" data-gallery="example-gallery">
                    </div>
                    <?php $count1++; endforeach; ?>  
                </div>
              </div>
              <!-- Carousel Navigation -->
              <div id="carousel-thumbs" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                  <div class="carousel-item active">
                    <div class="row mx-0">
                      <?php foreach( $images as $image ): ?>
                      <div id="carousel-selector-<?php echo $count; ?>" class="thumb col-2 col-sm-2 px-1 py-2 <?php if($count1==0) : echo 'selected'; endif; ?>" data-target="#myCarousel" data-slide-to="<?php echo $count; ?>">
                        <img src="<?php echo $image['url']; ?>" class="img-fluid" alt="...">
                      </div>
                       <?php $count++; endforeach; ?>
                    </div>
                  </div>
                </div>
                <a class="carousel-control-prev" href="#carousel-thumbs" role="button" data-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carousel-thumbs" role="button" data-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="sr-only">Next</span>
                </a>
              </div>
            </div>
            <?php endif;  endwhile; endif;?>
          </div>
          <div class="col-xl-7">
            <div class="player-name">
              <h5 class="d-block"><?php the_title();?></h5>
              <span class="sub"><?php the_field('name') ?></span>
              <?php if( have_rows('profile') ): while( have_rows('profile') ): the_row(); ?>
                  <h4 class="actor-work-heading my-3">Profile</h4>
                  <?php echo get_sub_field('profile_description') ?>
              <?php endwhile; endif; ?>
            </div>
          </div>
          <?php if( have_rows('more_details') ): while( have_rows('more_details') ): the_row(); ?>
          <div class="col-xl-12 mt-3 mb-3">
              <h4 class="actor-work-heading mt-3 mb-4"><?php echo get_sub_field('group_title')?></h4>
              <ul class="list-group">
              <?php if( have_rows('group_details') ): while( have_rows('group_details') ): the_row(); ?>
                <li class="list-group-item"><?php echo get_sub_field('content'); ?></li>
              <?php endwhile; endif; ?>
              </ul>
          </div>
          <?php endwhile; endif; ?>

          <?php if ( function_exists( 'audubon_render_actor_links' ) ) : ?>
          <div class="col-xl-12 mt-3 mb-3">
              <?php audubon_render_actor_links(); ?>
          </div>
          <?php endif; ?>
        </div>
          
          <div class="previous-next">
            <div class="page-previous">
              <p><?php previous_post_link('%link', '&lt;&nbsp;前の記事');?></p>
            </div>
            <div class="page-next">
              <p><?php next_post_link('%link', '次の記事&nbsp;&gt;');?></p>
            </div>
          </div>
        </article>
        <?php endwhile; endif;?>
      </div>
    </div>
  </article>
</div>
<?php get_footer();?>
<script>
  $(document).ready(function() {
    $('#myCarousel').carousel({
      interval: false
    });
    $('#carousel-thumbs').carousel({
      interval: false
    });

    // handles the carousel thumbnails
    // https://stackoverflow.com/questions/25752187/bootstrap-carousel-with-thumbnails-multiple-carousel
    $('[id^=carousel-selector-]').click(function() {
      var id_selector = $(this).attr('id');
      var id = parseInt( id_selector.substr(id_selector.lastIndexOf('-') + 1) );
      $('#myCarousel').carousel(id);
    });
    // Only display 3 items in nav on mobile.
    if ($(window).width() < 575) {
      $('#carousel-thumbs .row div:nth-child(4)').each(function() {
      var rowBoundary = $(this);
      $('<div class="row mx-0">').insertAfter(rowBoundary.parent()).append(rowBoundary.nextAll().addBack());
      });
      $('#carousel-thumbs .carousel-item .row:nth-child(even)').each(function() {
      var boundary = $(this);
      $('<div class="carousel-item">').insertAfter(boundary.parent()).append(boundary.nextAll().addBack());
      });
    }
    // Hide slide arrows if too few items.
    if ($('#carousel-thumbs .carousel-item').length < 2) {
      $('#carousel-thumbs [class^=carousel-control-]').remove();
      $('.machine-carousel-container #carousel-thumbs').css('padding','0 5px');
    }
    // when the carousel slides, auto update
    $('#myCarousel').on('slide.bs.carousel', function(e) {
      var id = parseInt( $(e.relatedTarget).attr('data-slide-number') );
      $('[id^=carousel-selector-]').removeClass('selected');
      $('[id=carousel-selector-'+id+']').addClass('selected');
    });
    $('#myCarousel .carousel-item img').on('click', function(e) {
      var src = $(e.target).attr('data-remote');
      if (src) $(this).ekkoLightbox();
    });
    // when user swipes, go next or previous
    // $('#myCarousel').swipe({
    //   fallbackToMouseEvents: true,
    //   swipeLeft: function(e) {
    //   $('#myCarousel').carousel('next');
    //   },
    //   swipeRight: function(e) {
    //   $('#myCarousel').carousel('prev');
    //   },
    //   allowPageScroll: 'vertical',
    //   preventDefaultEvents: false,
    //   threshold: 75
    // });
    /*
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox();
    });
    */
    
  });

</script>