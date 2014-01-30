<?php
/**
 * The template for displaying all pages.
 *
 */

get_header(); ?>

	<div id="primary">
		<div id="content" role="main">
			<? if (empty($_GET['y'])): ?>
				<? $gallery = get_posts(array('numberposts'=>1,'post_type'=>'photo-gallery', 'order'=>'ASC')); ?>
			<? else: ?>
				<? $gallery = get_posts(array('numberposts'=>1,'post_type'=>'photo-gallery', 'meta_key'=>'year', 'meta_value'=>$_GET['y'])); ?>
			<? endif; ?>

			<? foreach ($gallery as $photo_gallery): ?>

				<article id="post-<? the_ID(); ?>" <? post_class(); ?>>
					
					<h1 class="entry-title">Photo Gallery</h1>
				
					<div class="entry-content">
						<ul class="gallery">
							<? $images = get_field('gallery', $photo_gallery->ID)?>
							<? $i=0; foreach ($images as $image): $i++; ?>
								<li class="<?=($i-1)%3==0?'first':''?>">
									<a href="<?=reset(wp_get_attachment_image_src( $image['id'],'lightbox'))?>" class="lightbox" rel="lightbox[session]">
										<?=wp_get_attachment_image( $image['id'],'gallery')?>
									</a>
								</li>
							<? endforeach; ?>
						</ul>
					</div><!-- .entry-content -->
				</article><!-- #post-<? the_ID(); ?> -->

			<? endforeach; // end of the loop. ?>
			
			<? $all_galleries = get_posts(array('numberposts'=>-1, 'post_type'=>'photo-gallery', 'order'=>'ASC'));?>
			
			<ul class="all-galleries">
				<? foreach ($all_galleries as $_gallery): ?>
					<li class="<?=get_field('year',$_gallery->ID)==get_field('year',$photo_gallery->ID)?'active':''?>">
						<a href="<?=get_bloginfo('home')?>/photo-gallery/?y=<?=get_field('year',$_gallery->ID)?>"><?=get_field('year',$_gallery->ID)?></a>
					</li>
				<? endforeach;?>
			</ul>
		</div><!-- #content -->
	</div><!-- #primary -->

<? get_sidebar(); ?>
<? get_footer(); ?>