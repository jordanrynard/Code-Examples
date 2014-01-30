<?php
/**
 * The template for displaying all pages.
 *
 */

get_header(); ?>

	<div id="primary">
		<div id="content" role="main">
			<? while ( have_posts() ) : the_post(); ?>

				<article id="post-<? the_ID(); ?>" <? post_class(); ?>>
					
					<h1 class="entry-title">Food</h1>
				
					<div class="entry-content">
						Food brought to you by:<br/>
						<a href="http://savourmuskoka.ca"><img class="savour-muskoka" src="<?=get_bloginfo('template_url')?>/images/savour-muskoka.jpg" /></a>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>