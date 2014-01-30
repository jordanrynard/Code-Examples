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
						
						<h2 class="banner">
							Griffin Session Toronto brings you The Collaboration Nation. We are teaming up 20 of the best craft brewers with 20 chosen artists to bring you the most unique beer experience yet! 
						</h2>
					
						<span class="seperator"></span>
						<div class="collaborators">
							<h1 class="entry-title">Collaborators</h2>
							<ul>
								<? 
									$collaborators = get_posts(array(
										'numberposts'=>-1,
										'post_type'=>'collaborators',
										'orderby'=>'menu_order',
										'order'=>'asc'
									));
								?>
								<? foreach ($collaborators as $collaborator): ?>
									<li>
										<script>
											jQuery(document).ready(function($){
												$(".more-info-collaborator").click(function(){
													$(".info-block").hide();
													$(this).next(".info-block").show();
													return false;
												});
												$(".close-info-block").click(function(){
													$(".info-block").hide();
													return false;
												});
												$(".info-block").find("a").click(function(){
													if ($(this).attr("href")==""){
														return false;
													} else {
														return true;
													}
													return false;
												});
											});
										</script>
										<a href="#" class="more-info-collaborator" style="display:block;">
											<div class="image">
												<?=wp_get_attachment_image(get_field('the_image',$collaborator->ID), $size = 'collaborator')?>
											</div>
											<div class="name">
												<?=get_field('artist_name',$collaborator->ID)?>
												 & 
												<?=get_field('brewery_name',$collaborator->ID)?>
											</div>
										</a>
										<div class="info-block" style="padding:20px 0px 20px 20px;display:none; z-index:999; position:fixed; top:75px; left:50%; margin-left:-400px; height:600px; width:800px; background:#fff; border:10px solid #000; border-radius:25px;">
											<a href="#" class="close-info-block" style="position:absolute;top:-25px;right:-25px;"><img src="http://muhammadimasjid.org/wp-content/themes/muhammadi-masjid/images/popup-closeButton.png" width="43px"/></a>
											<div style="overflow:auto; height:540px; padding-right:20px;">
												<div style="overflow:hidden;text-align:left;">
													<div style="float:left;margin-right:10px;"><a href="<?=get_field('artist_link',$collaborator->ID)?>"><?=wp_get_attachment_image(get_field('artist_image',$collaborator->ID), $size = 'collaborator')?></a></div>
													<div style="float:right;margin-left:10px;"><a href="<?=get_field('brewery_link',$collaborator->ID)?>"><?=wp_get_attachment_image(get_field('brewery_image',$collaborator->ID), $size = 'collaborator')?></a></div>
													<h2 style="text-transform:uppercase; font-weight:bold; padding-bottom:20px; padding-top:10px;">
														<?=get_field('artist_name',$collaborator->ID)?>
													 	& 
														<?=get_field('brewery_name',$collaborator->ID)?>
													</h2>
													<div style="padding-bottom:10px; font-size:18px;">Brewing Date: <i><?=get_field('brewing_date',$collaborator->ID)?></i></div>
													<div style="padding-bottom:10px; font-size:18px;">Beer Style: <i><?=get_field('beer_style',$collaborator->ID)?></i></div>
													<div style="padding-bottom:10px; font-size:18px;">Beer Name: <i><?=get_field('beer_name',$collaborator->ID)?></i></div>
													<div style="clear:both; text-align:left; font-size:12px; padding-top:15px;">
														<b style="font-size:15px;">The Artist</b><br/>
														<br/>
														<p>
															<?=get_field('artist_description',$collaborator->ID)?>
														</p>
														<br/>
														<br/>
														<b style="font-size:15px;">The Brewery</b><br/>
														<p>
															<?=get_field('brewery_description',$collaborator->ID)?>
														</p>
													</div>
												</div>
											</div>
										</div>

									</li>
								<? endforeach; ?>
							</ul>
						</ul>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>