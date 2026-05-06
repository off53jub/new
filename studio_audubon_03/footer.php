	<?php get_footer(); ?>
	<footer>
	  <div class="footer-top">
	    <div class="footer-inner flex">
	      <div class="footer-logo">
	        <a href="<?php echo esc_url(home_url('/')); ?>"><img
	            src="<?php echo get_template_directory_uri(); ?>/images/footer_logo.png"></a>

	        <div class="footer-sns">
	          <a href="#"><i class="fab fa-instagram"></i></a>
	          <a href="#"><i class="fab fa-twitter"></i></a>
	          <a href=""><i class="fab fa-facebook-f"></i></a>
	        </div>
 	        <p>俳優及び文化人マネジメント・舞台公演・イベント企画制作<span class="spacer"></span>〒150-0011 東京都渋谷区東2-22-3 イーストプラス006<span class="spacer"></span>TEL:03-3473-7373
	        </p>
	      </div>
	    </div>
	  </div>
	  <div class="footer-bottom">
	    <div class="footer-inner align-center">
	      <p id="privacy-policy" class="pr-30"><a href="http://studio-audubon.jp/privacy-policy">Privacy Policy</a></p>
	      <p id="copyright">&copy;&nbsp;2018&nbsp;studio&nbsp;audubon.&nbsp;All&nbsp;Rights&nbsp;Reserved.</p>
	    </div>
	  </div>
	</footer>
	</div>
	<?php wp_footer(); ?>
	<script>
var luminousTrigger = document.querySelectorAll('.luminous');
if (luminousTrigger !== null) {
  new LuminousGallery(luminousTrigger);
}
	</script>
	</body>

	</html>