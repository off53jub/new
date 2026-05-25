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
    <article>
      <div class="contents works-area">
        <h2 class="section-title">Works</h2>
        <div class="works-flex-wrapper">
          <?php
$categories = get_categories('parent=0');
foreach ($categories as $category):
?>

          <div class="works-inner">
            <?php query_posts('category_name=actor');
if (have_posts()): while (have_posts()): the_post();
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
            </div>
            <?php endwhile;endif;?>
            <?php endforeach;?>
          </div>
        </div>

        <div class="works-button">
          <a href="<?php echo home_url('/works'); ?>">WORKS一覧はこちら</a>
        </div>
      </div>
    </article>
    <?php get_footer();?>