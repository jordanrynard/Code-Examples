<?php
/**
 * The Sidebar containing the main widget areas.
 *
 */
?>
	<div id="secondary" class="widget-area" role="complementary">
		<? if (!dynamic_sidebar('sidebar')): ?>

			<aside id="search" class="widget widget_search">
				<? get_search_form(); ?>
			</aside>

			<aside id="archives" class="widget">
				<h1 class="widget-title">Archives</h1>
				<ul>
					<? wp_get_archives(array('type'=>'monthly')); ?>
				</ul>
			</aside>

			<aside id="meta" class="widget">
				<h1 class="widget-title">Meta</h1>
				<ul>
					<? wp_register(); ?>
					<li><? wp_loginout(); ?></li>
					<? wp_meta(); ?>
				</ul>
			</aside>

		<? endif; // end sidebar widget area ?>
	</div><!-- #secondary .widget-area -->

	<? if (is_active_sidebar('sidebar-2')): ?>
		<div id="tertiary" class="widget-area" role="complementary">
			<? dynamic_sidebar('sidebar-2'); ?>
		</div><!-- #tertiary .widget-area -->
	<? endif; ?>