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
					
					<h1 class="entry-title">Entertainment</h1>
				
					<div class="entry-content">
						<? $entertainers = get_posts(array('numberposts'=>-1, 'post_type'=>'entertainment', 'order'=>'ASC', 'orderby'=>'menu_order')); ?>
						<ul>
							<? if (empty($entertainers)): ?>
								<li class="empty">
									Please check back soon for a listing<br/>
									of our wicked entertainers!<br/>
									<span class="seperator"></span><br/>
									We will be updating frequently.
								</li>
							<? else: ?>
								<? foreach ($entertainers as $entertainer): ?>
									<li class="entertainer">
                                        <div class="left">
                                            <?=wp_get_attachment_image(get_field('side_image',$entertainer->ID),'entertainment')?>
                                        </div>
                                        <div class="right">
                                            <h2><?=$entertainer->post_title?></h2>
                                        	<div class="description">
                                            	<?=get_field('description',$entertainer->ID)?>
                                            </div>
											<? $sec_url = get_field('secondary_image_link',$entertainer->ID); ?>
                                            <div class="secondary_image">
                                            	<? if (!empty($sec_url)): ?>
                                                	<a href="<?=$sec_url?>" target="_blank">
                                                <? endif; ?>
													<?=wp_get_attachment_image(get_field('secondary_image',$entertainer->ID),'entertainment-secondary')?>
                                            	<? if (!empty($sec_url)): ?>
                                                	</a>
                                                <? endif; ?>
                                            </div>
                                            <a href="<?=get_field('website',$entertainer->ID)?>" target="_blank"><?=str_replace("http://","",get_field('website',$entertainer->ID))?></a>
                                        </div>
                                        <hr/>
									</li>
								<? endforeach; ?>
							<? endif; ?>
						</ul>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>