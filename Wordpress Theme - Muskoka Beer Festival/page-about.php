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
					
					<h1 class="entry-title">About The Festival</h1>
				
					<div class="entry-content">
						<div class="main">
							<? the_content(); ?>
							<div class="seperator"></div>
							<div class="bottom">
								<?=get_field('about_continued')?>
							</div>
						</div>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>