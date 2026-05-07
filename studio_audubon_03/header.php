<!doctype html>
<html lang="ja">

<head>
  <meta charset="utf-8" />
  <meta name="keywords" content="俳優マネジメント, 文化人マネジメント, 舞台公演企画, イベント制作, 渋谷区, スタジオオーデュボン">
  <meta name="description" content="東京都渋谷区に拠点を置くスタジオオーデュボンは、俳優・文化人のマネジメントおよび舞台・イベントの企画制作を手がけています。クリエイター支援や舞台公演、朗読劇まで幅広くプロデュース。">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="">

  <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>?ver=<?php echo date('U'); ?>">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/reset-min.css">
  <link crossorigin="anonymous" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css"
    integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" rel="stylesheet" />
	
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css"/>
	
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/blueimp-gallery/3.3.0/css/blueimp-gallery.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/blueimp-gallery/3.3.0/css/blueimp-gallery-indicator.min.css">
  <title><?php wp_title('|', true, 'right'); ?>
    <?php bloginfo('name'); ?>
  </title>

  <?php wp_deregister_script('jquery'); ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/js/bootstrap.min.js"></script> 
<!--   <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.js"></script>
  

	

  <script type="text/javascript">
  $(function() {
    var navBox = $("#fixed-header");
    navBox.hide();
    var TargetPos = 300;
    $(window).scroll(function() {
      var ScrollPos = $(window).scrollTop();
      if (ScrollPos > TargetPos) {
        navBox.fadeIn();
      } else {
        navBox.fadeOut();
      }
    });
  });
	  
  </script>

  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/js/slick/slick.css">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/js/slick/slick-theme.css">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/luminous-basic.min.css">
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/slick/slick.js"></script>

  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/Luminous.min.js"></script>


  <script>
  $(function() {
    $('#slider').slick({
      autoplay: true,
      autoplaySpeed: 4500,
      speed: 600,
      dots: false,
      arrows: false,
      pauseOnHover: false,
      fade: true,
      infinite: true,
      cssEase: 'linear'
    });
  });
  </script>



  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/iscroll.js"></script>
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/drawer.min.css">
  <script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/drawer.min.js"></script>
	
  <script>
  $(document).ready(function() {
    $(".drawer").drawer();
  });
  </script>

  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php if ( is_front_page() || is_home() ) : ?>
  <!-- ホームページ用スプラッシュ（ローディング画面） -->
  <div class="audubon-splash" id="audubon-splash" role="presentation" aria-hidden="true">
    <img src="<?php echo get_template_directory_uri(); ?>/images/audubon_top_keyvisual.jpg"
         alt="" class="audubon-splash__image">
  </div>
<?php endif; ?>
  <!-- <?php
global $template;
$description = '[適用テンプレートファイル名]';
$template_name = basename($template, '.php');
echo '', $description, $template_name, '.php', '</p>';
?> -->

  <div id="wrap" class="drawer drawer--right">

    <header>
      <div class="header-inner">
        <div class="header-image">
          <a href="<?php echo esc_url(home_url('/')); ?>"><img
              src="<?php echo get_template_directory_uri(); ?>/images/header_logo.png"></a>
        </div>
        <nav class="global-navi header-navi">
          <ul id="global-navi">
            <?php wp_nav_menu([]); ?>
          </ul>
        </nav>
      </div>

      <div id="fixed-inner">
        <nav id="fixed-header">
          <div class="fixed-header-image">
            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><img
                src="<?php echo get_template_directory_uri(); ?>/images/header_logo.png"></a>
          </div>
          <nav class="global-navi header-navi">
            <ul>
              <?php wp_nav_menu([]); ?>
            </ul>
          </nav>
        </nav>
      </div>

      <div id="smart-head">
        <div class="smart-head-image">
          <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><img
              src="<?php echo get_template_directory_uri(); ?>/images/header_logo.png"></a>
        </div>
        <button type="button" class="drawer-toggle drawer-hamburger">
          <span class="sr-only">toggle navigation</span>
          <span class="drawer-hamburger-icon"></span>
        </button>

        <nav class="drawer-nav">
          <ul class="drawer-menu">
            <?php wp_nav_menu([]); ?>
          </ul>
        </nav>
      </div>

    </header>