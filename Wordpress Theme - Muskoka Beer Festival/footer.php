<?php
/**
 * Footer
 */
?>
	<div style="clear:both;"></div>
	</div><!-- #main -->

	<footer id="colophon" role="contentinfo">
		<div class="links">
			<h4>Follow Us Yo!</h4>
			<a href="<?=get_field('facebook','options');?>" title="Facebook" target="_blank" class="facebook">Facebook</a><?
			?><a href="<?=get_field('twitter','options');?>" title="Twitter" target="_blank" class="twitter">Twitter</a><?
			?><a href="<?=get_field('youtube','options');?>" title="Youtube" target="_blank" class="youtube">Youtube</a>
		</div>
		<div class="copyright">
			<?=get_bloginfo('name')?> - copyright <?=date('Y')?>  /  <?=get_field('email', 'options')?>  /  <?=get_field('phone', 'options')?>  
		</div>
	</footer><!-- #colophon -->

</div><!-- #page -->

<? wp_footer(); ?>

</body>
</html>