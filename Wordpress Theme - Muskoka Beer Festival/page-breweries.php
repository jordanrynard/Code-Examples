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
									
					<div class="entry-content">
						<div class="breweries breweries">
							<h1 class="entry-title">Breweries</h1>
							<ul class="content">
								<? $breweries = get_field('breweries'); ?>
								<? if (empty($breweries)): ?>
									<li class="empty">
										Please check back soon for a listing <br/>
										of our participating breweries.<br/>
										<span class="seperator"></span><br/>
										We will be updating frequently.
									</li>
								<? else: ?>
									<? $i=0; foreach ($breweries as $brewery): $i++; ?>
										<li><span class="number"><?=$i?>. </span><?=$brewery['name']?></li>
										<? $sub_vendors = $brewery['sub_vendors']; ?>
										<? if (!empty($sub_vendors)): ?>
											<li class="sub-vendor"><?=$sub_vendors?></li>
										<? endif; ?>
									<? endforeach; ?>
								<? endif; ?>
							</ul>
						</div>
						<div class="wineries breweries">
							<h1 class="entry-title">Wine & Spirits</h1>
							<ul class="content">
								<? $breweries = get_field('wine_and_spirits'); ?>
								<? if (empty($breweries)): ?>
									<li class="empty">
										Please check back soon for a listing <br/>
										of our participating wineries.<br/>
										<span class="seperator"></span><br/>
										We will be updating frequently.
									</li>
								<? else: ?>
									<? $i=0; foreach ($breweries as $brewery): $i++; ?>
										<li><span class="number"><?=$i?>. </span><?=$brewery['name']?></li>
									<? endforeach; ?>
								<? endif; ?>
							</ul>
						</div>
						<div class="more">... more to come!</div>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>