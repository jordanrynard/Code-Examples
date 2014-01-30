<?php
/**
 * Home Page
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<? while ( have_posts() ) : the_post(); ?>

					<article id="post-<? the_ID(); ?>" <? post_class(); ?>>
						
						<h1 class="entry-title"><? the_title(); ?></h1>
					
						<div class="entry-content">
							<? the_content(); ?>
						</div><!-- .entry-content -->
					
					</article><!-- #post-<? the_ID(); ?> -->

				<? endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>