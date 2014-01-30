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
						<h1>Location</h1>
						<div class="address">
							Annie Williams Park, Bracebridge
						</div>
						<div class="map">
							<iframe width="641" height="439" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=annie+williams+park+bracebridge&amp;aq=&amp;sll=37.0625,-95.677068&amp;sspn=48.956293,93.076172&amp;ie=UTF8&amp;hq=annie+williams+park&amp;hnear=Bracebridge,+Muskoka+District+Municipality,+Ontario,+Canada&amp;t=m&amp;cid=17576312538836443078&amp;ll=45.03702,-79.318457&amp;spn=0.026626,0.054932&amp;z=14&amp;iwloc=A&amp;output=embed"></iframe><br /><small><a href="https://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=annie+williams+park+bracebridge&amp;aq=&amp;sll=37.0625,-95.677068&amp;sspn=48.956293,93.076172&amp;ie=UTF8&amp;hq=annie+williams+park&amp;hnear=Bracebridge,+Muskoka+District+Municipality,+Ontario,+Canada&amp;t=m&amp;cid=17576312538836443078&amp;ll=45.03702,-79.318457&amp;spn=0.026626,0.054932&amp;z=14&amp;iwloc=A" style="color:#0000FF;text-align:left"></a></small>
						</div>
						<div class="details">
							There is plenty of free parking available but we recommend carpooling and taxiing from your accomodations.
						</div>
						<div class="seperator"></div>
						<div class="keep-in-mind">
							Please keep in mind this is 19+ event and you will definitely need photo I.D. to enter and pets are NOT allowed.
						</div>
					</div><!-- .entry-content -->
				
				</article><!-- #post-<? the_ID(); ?> -->

			<? endwhile; // end of the loop. ?>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>