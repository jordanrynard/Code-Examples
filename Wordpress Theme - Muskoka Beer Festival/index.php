<?php
/**
 * Index
 *
 */

get_header(); ?>

	<div id="primary">
		<h1 class="page-title">Blog</h1>

		<div id="content" role="main">
			<? while ( have_posts() ) : the_post(); ?>
				
				<article id="post-<? the_ID(); ?>" <? post_class(); ?>>
					
					<h1 class="entry-title">
						<a href="<? the_permalink(); ?>" title="<?=the_title_attribute('echo=0')?>" rel="bookmark"><? the_title(); ?></a>
					</h1>
				
					<div class="entry-content">
						<? if (has_post_thumbnail()): ?>
							<div class="post-thumbnail-container">
								<? the_post_thumbnail(); ?>	
							</div>
						<? endif; ?>
						<? the_content(); ?>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->
			
			<? endwhile; ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>