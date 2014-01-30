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
					
					<h1 class="entry-title">Contact Us</h1>
				
					<div class="entry-content">
						<div class="name"><?=get_field('name')?></div>
						<div class="email"><?=get_field('email')?></div>
						<div class="phone"><?=get_field('phone')?></div>
						<div class="seperator"></div>
						<div class="address"><?=get_field('address')?></div>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>