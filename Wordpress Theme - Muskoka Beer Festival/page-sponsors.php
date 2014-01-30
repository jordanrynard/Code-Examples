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
					
					<h1 class="entry-title"><? the_title(); ?></h1>
				
					<div class="entry-content">
						<!--<img class="temp" src="<?=get_bloginfo('template_url')?>/images/sponsors_temp.png" />-->
						<div style="text-align:center;">
							<?
								$sponsors = get_field('sponsors');
								foreach ($sponsors as $sponsor):
							?>
									<a href="<?=$sponsor['link']?>" style="margin:15px; display:inline-block;" target="_blank"><img src="<?=reset(wp_get_attachment_image_src($sponsor['logo'],'sponsor'))?>" /></a>
							<? 	endforeach; ?>
						</div>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>