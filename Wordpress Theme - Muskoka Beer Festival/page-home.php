<?php
/**
 * Home Page
 */

get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<? while ( have_posts() ) : the_post(); ?>

					<article id="post-<? the_ID(); ?>" <? post_class(); ?>>
											
						<div class="entry-content">
							<div id="arrow_right_button" style="display:none;"></div>
							<div class="slider">
								<ul class="slider-ul">
									<?
										$images = get_field('slider');
										$i=0; foreach ($images as $image){ $i++;
									?>
											<li class="<?=$i==1?'active':''?>" data-id="<?=$image['id']?>">
												<?=wp_get_attachment_image( $image['id'], 'slider');?>
											</li>
									<?
										}
									?>
								</ul>
								<ul class="slider-nav">
									<?
										$images = get_field('slider');
										$i=0; foreach ($images as $image){ $i++;
									?>
											<li class="<?=$i==1?'active':''?>" data-id="<?=$image['id']?>">
												<a href="#"></a>
											</li>
									<?
										}
									?>
								</ul>
							</div><!--.slider-->
							<div class="ctas">
								<h2>Lots of fun stuff to do</h2>
								<a href="<?=get_field('cta_left_link')?>" class="left"><?=wp_get_attachment_image(get_field('cta_left_image'), 'call-to-action_1'); ?></a>
								<a href="<?=get_field('cta_right_link')?>" class="right"><?=wp_get_attachment_image(get_field('cta_right_image'), 'call-to-action_2'); ?></a>
							</div>					
						</div><!-- .entry-content -->
					
					</article><!-- #post-<? the_ID(); ?> -->

				<? endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>