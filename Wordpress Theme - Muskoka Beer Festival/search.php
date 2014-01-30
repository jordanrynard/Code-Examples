<?php
/**
 * The template for displaying Search Results pages.
 *
 */

get_header(); ?>

	<section id="primary">
		<h1 class="page-title"><?=get_search_query()?></h1>

		<div id="content" role="main">
			<? if (have_posts()): ?>
				<? while (have_posts()): the_post(); ?>

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
			<? else: ?>
	
				Could not find what you were looking for.
	
			<? endif; ?>
		</div><!-- #content -->
	</section><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>