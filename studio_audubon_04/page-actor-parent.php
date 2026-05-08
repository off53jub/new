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
    <div class="contents actors-area">
      <h2 class="section-title">Actor</h2>
      <div class="actors-inner">
        <ul>
          <?php
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$parent_id = get_the_ID();
$args = array(
    'posts_per_page' => 18,
    'post_type' => 'page',
//     'orderby' => 'menu_order',
//     'order' => 'DESC',
    'post_parent' => $parent_id,
    'paged' => $paged,
);

$common_pages = new WP_Query($args);
if ($common_pages->have_posts()):
    while ($common_pages->have_posts()): $common_pages->the_post();
        ?>

          <li>
            <div class="thumbnail"><a href="<?php the_permalink();?>"><?php the_post_thumbnail('actor_thumbnail');?></a>
            </div>
            <div class="title"><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a>
            </div>
          </li>

          <?php
    endwhile;
    wp_reset_postdata();
endif;
?>
        </ul>
      </div>
    </div>
</div>

<?php if (function_exists('wp_pagenavi')) {wp_pagenavi(array('query' => $common_pages));}?>
</article>
<?php get_footer();?>