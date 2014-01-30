<?php
/**
 * Header
 */
?>
<!DOCTYPE html>
<html <? language_attributes(); ?>>
<head>
<meta charset="<? bloginfo('charset'); ?>" />
<meta name="viewport" content="width=1614" />
<title><? wp_title('|', false, 'right'); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="icon" type="image/png" href="<?=get_bloginfo('template_url')?>/favicon.png" />
<link rel="stylesheet" type="text/css" media="all" href="<? bloginfo('stylesheet_url'); ?>" />
<link rel="stylesheet" type="text/css" media="all" href="<? bloginfo('template_url'); ?>/fonts/stylesheet.css" />
<link rel="stylesheet" type="text/css" media="all" href="<? bloginfo('template_url'); ?>/fonts/heroic/stylesheet.css" />
<? if (is_singular() && get_option('thread_comments')) wp_enqueue_script('comment-reply'); ?>
<link rel="pingback" href="<? bloginfo('pingback_url'); ?>" /><!--   -->
<!--[if lt IE 9]>
<script src="<?=get_template_directory_uri()?>/scripts/html5shiv/html5.js" type="text/javascript"></script>
<![endif]-->
<? wp_enqueue_script('jquery'); ?>
<? wp_head(); ?>
<link rel="stylesheet" type="text/css" media="all" href="<? bloginfo('template_url'); ?>/scripts/lightbox/css/lightbox.css" />
<script src="<? bloginfo('template_url'); ?>/scripts/lightbox/jquery.lightbox.js"></script>
<script>
jQuery(document).ready(function($){
	$(".lightbox").lightbox({
		fileLoadingImage : '<? bloginfo('template_url'); ?>/scripts/lightbox/images/loading.gif',
		fileBottomNavCloseImage : '<? bloginfo('template_url'); ?>/scripts/lightbox/images/closelabel.gif',
		fitToScreen: true,		// resize images if they are bigger than window
        disableNavbarLinks: true,

	});
<? if (!empty($_GET['purchase'])): ?>
	$(document).scrollTop( 500 );  
<? endif; ?>
});
</script>
</head>

<body <? body_class(); ?>>
<div id="page" class="hfeed">
	<header id="branding" role="banner">
		<h1 id="logo">
			<a href="<?=home_url('/')?>" title="<?=esc_attr(get_bloginfo('name', 'display'))?>" rel="home"><? bloginfo('name'); ?></a>
		</h1>
		<h2 id="tagline" title="Saturday / August 3rd / 1-7pm - Annie Williams Park, Bracebridge">
			Saturday / August 3rd / 1-7pm - Annie Williams Park, Bracebridge
		</h2>
	</header><!-- #branding -->

	<div id="main">
		<nav id="access" role="navigation">
			<? wp_nav_menu(array('menu'=>'Main Menu', 'container'=>false, 'menu_id'=>'main-menu')); ?>
			<div id="twitter-feed">
				<div class="top">
					<img class="bird" src="<?=get_bloginfo('template_url')?>/images/icon-twitter-slanted.png" />
					<a href="<?=get_field('twitter','options');?>" class="link" target="_blank"># griffingastro</a>
				</div>
				<ul class="tweets">
					<?
					echo cws_twitter_feed(array(
						'num'=>3, 
						'user'=>'griffingastro',
						'date'=>"M d @ ga",
						'interval'=>1.5, // Number of hours to wait before updating cache
						'tag'=>'li' // Default Tag

					));
					?>
				</ul>
			</div>
		</nav><!-- #access -->
