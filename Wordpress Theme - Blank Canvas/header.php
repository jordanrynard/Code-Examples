<?php
/**
 * Header
 */
?>
<!DOCTYPE html>
<html <? language_attributes(); ?>>
<head>
<meta charset="<? bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width" />
<title><? wp_title('|', false, 'right'); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="icon" type="image/png" href="<?=get_bloginfo('template_url')?>/favicon.png" />
<link rel="stylesheet" type="text/css" media="all" href="<? bloginfo('stylesheet_url'); ?>" />
<!--<link rel="stylesheet" type="text/css" media="all" href="<? bloginfo('template_url'); ?>/fonts/stylesheet.css" />-->
<? if (is_singular() && get_option('thread_comments')) wp_enqueue_script('comment-reply'); ?>
<link rel="pingback" href="<? bloginfo('pingback_url'); ?>" /><!--   -->
<!--[if lt IE 9]>
<script src="<?=get_template_directory_uri()?>/scripts/html5shiv/html5.js" type="text/javascript"></script>
<![endif]-->
<? wp_enqueue_script('jquery'); ?>
<? wp_head(); ?>
</head>

<body <? body_class(); ?>>
<div id="page" class="hfeed">
	<header id="branding" role="banner">
		<h1 id="logo">
			<a href="<?=home_url('/')?>" title="<?=esc_attr(get_bloginfo('name', 'display'))?>" rel="home"><? bloginfo('name'); ?></a>
		</h1>
		<nav id="access" role="navigation">
			<? wp_nav_menu(array('menu'=>'Main Menu', 'container'=>false, 'menu_id'=>'main-menu')); ?>
		</nav><!-- #access -->
	</header><!-- #branding -->

	<div id="main">