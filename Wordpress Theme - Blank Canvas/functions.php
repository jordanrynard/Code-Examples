<?php
/*
Blank Canvas
by Jordan Rynard

NOTES:
If File Gallery buggers up it's because there's no WYSIWYG editor
Avoid Image Sizes 400 and 290/291 in width (This messes images up for some reason)
Query vs get_posts (http://wordpress.stackexchange.com/questions/1753/when-should-you-use-wp-query-vs-query-posts-vs-get-posts)
(posts_per_page is not the same as numberposts (one is for pagination, one is for total # of posts to get)
get_queried_object() = your friend
Override the values of user options with the following: add_filter('get_user_option_$option', 'function_name');
ACF: If calling get_field_object in function, need global $wpdb

http://codex.wordpress.org/Determining_Plugin_and_Content_Directories

USEFUL:
$pagenow 
$current_page

tb_show()
tb_close() // javascript manual thickbox calls

get_intermediate_image_sizes() // gets size names
global $_wp_additional_image_sizes // array containing dimensions

*/
/*=========================================================================================================*/
/*=========================================== Image Sizes =================================================*/
/*=========================================================================================================*/
add_theme_support('post-thumbnails');
set_post_thumbnail_size(258, 0, false);

add_image_size('lightbox', 1024, 768, false);

/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=================================================== INITIALIZATION ======================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/

/*=========================================================================================================*/
/*============================================= Set Constants =============================================*/
/*=========================================================================================================*/
// Useful Predefined Constants: TEMPLATEPATH
define('jr_URL', get_template_directory_uri());
define('jr_TEMPLATE_URL', get_template_directory_uri()); // http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
// define('WP_MEMORY_LIMIT', '64M'); // Increase Memory Limit // http://betterwp.net/282-wordpress-constants/
// define('WP_DEBUG_DISPLAY', true);
// define('WP_DEBUG_LOG', true);
// define('WP_POST_REVISIONS', 2)

/*=========================================================================================================*/
/*================================= Load Javascript / Set Javascript Variables ============================*/
/*=========================================================================================================*/
add_action('wp_enqueue_scripts', 'jr_scripts');
function jr_scripts() {
	wp_enqueue_script('jr_scripts', get_template_directory_uri().'/scripts.js', array('jquery'));
	wp_localize_script('jr_scripts', 'jr', array('template_url' => get_template_directory_uri()));
}
add_action('admin_enqueue_scripts', 'jr_admin_scripts');
function jr_admin_scripts($hook) {
    wp_enqueue_script('jr_admin_scripts', get_bloginfo('template_url').'/admin.js', array('jquery'));
}

/*=========================================================================================================*/
/*=============================================== Load Stylsheets =========================================*/
/*=========================================================================================================*/
// CHECK ME
add_action('admin_enqueue_scripts', 'jr_admin_styles');
function jr_admin_styles() {
	wp_enqueue_style('jr_admin_styles', get_template_directory_uri().'/admin.css');
}

/*=========================================================================================================*/
/*========================================== Set Up Widget Locations ======================================*/
/*=========================================================================================================*/
add_action('widgets_init', 'jr_widgets_init');
function jr_widgets_init(){
	register_sidebar(array(
		'name' => 'Sidebar',
		'id' => 'sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	));
}

/*=========================================================================================================*/
/*============================================= Admin Body Class ==========================================*/
/*=========================================================================================================*/
// Check
add_filter('admin_body_class', 'jr_admin_body_class');
function jr_admin_body_class($classes) {
	global $post;
	$mode = 'admin';
	$uri = $_SERVER["REQUEST_URI"];
	if (strstr($uri, 'edit.php')) {
		$post_type = empty($_GET['post_type']) ? 'post' : $_GET['post_type'];
		$mode .= ' edit '.$post_type;
	} elseif (strstr($uri, 'post.php')) {
		$mode .= ' post '.get_post_type($post->ID);
	} elseif (strstr($uri, 'edit-tags.php')) {
		if ($_GET['action'] == 'edit'){
			$mode .= ' edit-tag '.$_GET['taxonomy'];;
		} else {
			$mode .= ' edit-tags '.$_GET['taxonomy'];;
		}
	}
	$classes .= $mode;
	if (!empty($post->post_name)){
		$classes .= ' pageadmin-'.$post->post_name.' ';
	}
	return $classes;
}

/*=========================================================================================================*/
/*===================================== Taxonomy Drag n Drop Sorting ======================================*/
/*======================================== (Gecka Terms Ordering) =========================================*/
/*=========================================================================================================*/
add_action('admin_init', 'jr_taxonomy_sorting');
function jr_taxonomy_sorting(){
	if(function_exists('add_term_ordering_support')){
		$taxonomies=get_taxonomies('','objects'); 
		foreach ($taxonomies as $taxonomy){
			add_term_ordering_support($taxonomy->name);
		}
	}
}

/*=========================================================================================================*/
/*============================================= Better Titles =============================================*/
/*=========================================================================================================*/
add_filter('wp_title', 'jr_title_tag', 10, 3);
function jr_title_tag($title, $sep, $sep_location){
	$page_num = '';
	if(get_query_var('paged')) {
		$page_num = ' Page '.get_query_var('paged').' '.$sep.' ';
	} elseif(get_query_var('page')) {
		$page_num = ' Page '.get_query_var('page').' '.$sep.' ';
	}
	$sitename = $page_num.get_bloginfo('name');
	if (is_category()) {
		single_cat_title(); echo 'Category Archive for &quot;'; echo $sitename;
	} elseif (is_tag()) {
		echo 'Tag Archive for &quot;'; single_tag_title(); echo '&quot; | '; echo $sitename;
	} elseif (is_tax()){
		// $term_obj = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy')); 
		$taxonomy_obj = get_taxonomy(get_query_var('taxonomy')); echo $title; echo $taxonomy_obj->label; echo ' | '; echo $sitename;
	} elseif (is_archive()) {
		echo $title; echo $sitename;
	} elseif (is_search()) {
		echo 'Search results for &quot;'.esc_html($_GET['s']).'&quot; | '; echo $sitename;
	} elseif (is_home() || is_front_page()) {
		echo $sitename;
	} elseif (is_404()) {
		echo 'Error 404 Not Found | '; echo $sitename;
	} elseif (is_page()){
		echo $title; echo $sitename;
	} elseif (is_single()) {
		$post_type_obj = get_post_type_object(get_post_type()); echo $title; echo $post_type_obj->label; echo ' | '; echo $sitename;
	} else {
		echo $title;echo $sitename;
	} 
}

/*=========================================================================================================*/
/*============================================ Hide Admin Bar =============================================*/
/*=========================================================================================================*/
add_filter('get_user_option_show_admin_bar_front', 'jr_show_admin_bar_front');
function jr_show_admin_bar_front(){
	return 'true';
}

/*=========================================================================================================*/
/*========================================== Customize Admin Bar ==========================================*/
/*=========================================================================================================*/
add_action('admin_bar_menu', 'jr_branding_remove_wp_logo', 999);
function jr_branding_remove_wp_logo($wp_admin_bar){
    $wp_admin_bar->remove_node('wp-logo');
	$wp_admin_bar->remove_menu('comments');
}

/*=========================================================================================================*/
/*=============================================== Remove Help =============================================*/
/*=========================================================================================================*/
add_filter('contextual_help', 'jr_remove_help_tabs', 999, 3);
function jr_remove_help_tabs($old_help, $screen_id, $screen){
    $screen->remove_help_tabs();
    return $old_help;
}

/*=========================================================================================================*/
/*============================================== Replace Howdy ============================================*/
/*=========================================================================================================*/
add_filter('admin_bar_menu', 'jr_replace_howdy', 25);
function jr_replace_howdy($wp_admin_bar){
    $my_account = $wp_admin_bar->get_node('my-account');
    $newtitle = str_replace('Howdy,', 'Logged in as', $my_account->title);            
    @$wp_admin_bar->add_node(array(
        'id' => 'my-account',
        'title' => $newtitle,
    ));
}

/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*======================================================= FUNCTIONS =======================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/

/*=========================================================================================================*/
/*=========================================== Better Array Display ========================================*/
/*=========================================================================================================*/
if (!function_exists('print_arc')){
	function print_arc($array){
		echo "<xmp>";
		print_r($array);
		echo "</xmp>";
	}
}
if (!function_exists('print_jr')){
	function print_jr($array){
		echo "<pre>";
		print_r($array);
		echo "</pre>";
	}
}

/*=========================================================================================================*/
/*=============================================== Pagination ==============================================*/
/*=========================================================================================================*/
function jr_pagination($args=array()){
	global $wp_query;
	$big = 999999999; // This needs to be an unlikely integer
	// http://codex.wordpress.org/Function_Reference/paginate_links
	$defaults = array(
		'base' => str_replace($big, '%#%', get_pagenum_link($big)),
		'current' => max(1, get_query_var('paged')),
		'total' => $wp_query->max_num_pages,
		'mid_size' => 5
	);
	$args = $defaults + $args;
	$paginate_links = paginate_links($args);
	// Display the pagination if more than one page is found
	if ($paginate_links){
		$pagination = '<div class="pagination">';
		$pagination .= $paginate_links;
		$pagination .= '</div>';
	}
	return $pagination;
}

/*=========================================================================================================*/
/*======================================== Contact Form Submission ========================================*/
/*=========================================================================================================*/
function jr_contact_form_submit($submission_type){
	if (empty($_POST['contact'])){
		return false;
	} else {
		$values = array();
		$message = "<b style='text-decoration:underline'>".$submission_type."</b><br/><br/>";
		foreach ($_POST['contact'] as $key => $value){
			$label = str_replace("_", " ", $key);
			$label = ucwords($label);
			$values[] = array('label'=>$label, 'value'=>$value);
		}
		foreach ($values as $key => $value){
			$message .= "<b><i>".$value['label']."</i></b>: ".$value['value']."<br/>";
		}
		$message .= "<br/><br/><span style='font-size:11px; font-style:italic;'>This was an automatically generated email, please do not reply to it</span>";
		if (function_exists(get_field)){
			$admin_email = get_field('email','option');
		} else {
			$admin_email = 'noreply@wordpress.canada-web-services.com';
		}
		$admin_email = 'noreply@wordpress.canada-web-services.com';
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: '.get_bloginfo('name').' <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
		mail($admin_email, $submission_type, $message, $headers);
		return true;
	}
}

/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*===================================================== CUSTOMIZATION =====================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/

/*=========================================================================================================*/
/*============================================== Tiny MCE =================================================*/
/*=========================================================================================================*/
add_filter("mce_buttons", "jr_add_mce_buttons"); // http://sumtips.com/2011/02/customize-wordpress-tinymce-buttons.html
function jr_add_mce_buttons($buttons){
	array_push($buttons, "fontsizeselect");
	// array_push($buttons, "fontselect");
	return $buttons;
}
add_filter('tiny_mce_before_init', 'jr_custom_format_tiny_mce' );
function jr_custom_format_tiny_mce($init) {
	// Add block format elements you want to show in dropdown
	// $init['theme_advanced_text_colors'] = '010101,f89920';
	$init['theme_advanced_font_sizes'] = '10px,12px,14px,16px,18px,20px,22px,24px,26px,30px,36px,46px,60px';	
	// $init['theme_advanced_fonts'] = 'League Gothic=league_gothicregular;Ashbury Light=ashbury-lightregular;';
	// Add elements not included in standard tinyMCE doropdown p,h1,h2,h3,h4,h5,h6
	//$init['extended_valid_elements'] = 'code[*]';
	return $init;
}

/*=========================================================================================================*/
/*============================================ Admin Menu =================================================*/
/*=========================================================================================================*/
// Make the restrictions by setting User Role defaults (if we need a page accessible we need role permissions)
// Then we remove the (unimportant/unrisky) pages that stuck around with this function
// Then we use Admin Custom Menu to add any pages we want accessible (like menus, that this function removed)
//
// Note: If a page is hidden as the result of User Role Editor, Menu Editor Pro isn't going to help view it
// We've added the nav-menus.php to the Global Settings page with the permissions 'publish_page', so if the
// user can't create new pages, they can't edit the menu -- makes sense, right? (using Menu Editor Pro)
// We've also sorted in Menu Editor Pro (that's all)
if (is_admin() && !current_user_can('administrator')){
	add_action( 'admin_menu', 'jr_remove_admin_menu_pages' );
	function jr_remove_admin_menu_pages() {
		remove_menu_page('tools.php'); // Press This sticks around
		remove_menu_page('edit-comments.php'); // Comments stick around
		remove_menu_page('upload.php'); // Media page sticks around (cause we still need ability to upload)
		remove_menu_page('themes.php'); // Themes page sticks around (themes aren't switchable / viewable)
	}
}

/*=========================================================================================================*/
/*============================================ Dashboard ==================================================*/
/*=========================================================================================================*/
// Widgets
add_action('admin_menu', 'jr_remove_dashboard_widgets');
function jr_remove_dashboard_widgets() {
	// Get names from the ID of the div in the dashboard
	remove_meta_box('welcome-panel', 'dashboard', 'core');
	remove_meta_box('dashboard_right_now', 'dashboard', 'core');
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
	// remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
	remove_meta_box('dashboard_plugins', 'dashboard', 'core');
	remove_meta_box('dashboard_quick_press', 'dashboard', 'core');
	// remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');
	remove_meta_box('dashboard_primary', 'dashboard', 'core');
	remove_meta_box('dashboard_secondary', 'dashboard', 'core');
}
// Custom Widgets
/*
add_action('wp_dashboard_setup', 'jr_add_dashboard_widget');
function jr_add_dashboard_widget() {
	wp_add_dashboard_widget('jr_dashboard_widget', 'Jordan Rynard's Widget', 'jr_dashboard_widget');
}
function jr_dashboard_widget() {
	echo "<p>Dearest Client, <br/>Here's how to do that thing I told you about yesterday...</p>";
}
*/
// Filters
add_filter('get_user_option_screen_layout_dashboard', 'jr_screen_layout_dashboard');
function jr_screen_layout_dashboard($value){
	if (empty($value)){
		return '3';
	}
	return $value;
}
add_filter('get_user_option_meta-box-order_dashboard', 'jr_meta_box_order_dashboard');
function jr_meta_box_order_dashboard($value){
	if (empty($value)){
		$value = 'a:4:{s:6:"normal";s:20:"jr_dashboard_widget";s:4:"side";s:24:"dashboard_incoming_links";s:7:"column3";s:23:"dashboard_recent_drafts";s:7:"column4";s:0:"";}';
		return unserialize($value);
	}
	return $value;
}
add_filter('get_user_option_show_welcome_panel', 'jr_show_welcome_panel');
function jr_show_welcome_panel(){
	return '0';
}
if (is_admin() && !current_user_can('administrator')){
	add_filter('screen_options_show_screen', 'remove_screen_options');
	function remove_screen_options(){ 
		return false;
	}
}

/*=========================================================================================================*/
/*============================================ POST EDIT LAYOUT ===========================================*/
/*=========================================================================================================*/
// Match the admin layout in post edit screens 
// !! (See if I can clean this up at some point) !!
add_action('admin_init', 'jr_all_users_use_admin_layouts');
$jr_admin_layouts=array();
function jr_all_users_use_admin_layouts(){
	if (!current_user_can('administrator')){
		// For security reasons we change the admin ID, so should try not to make an assumption unless we have to
		$admin_user = get_user_by('login', 'super-admin');
		if (!$admin_user) {
			$admin_user = get_user_by('login', 'admin');
		}
		$admin_id = $admin_user->ID ? $admin_user->ID : 1;		
		//
		global $jr_admin_layouts;
		$post_types = get_post_types('','names');
		foreach ($post_types as $post_type){
			add_filter('get_user_option_metaboxhidden_'.$post_type, 'jr_metaboxhidden');
			add_filter('get_user_option_meta-box-order_'.$post_type, 'jr_metaboxorder');
			add_filter('get_user_option_screen_layout_'.$post_type, 'jr_screenlayout');
			add_filter('get_user_option_manageedit-'.$post_type.'columnshidden', 'jr_columnshidden');
			$jr_admin_layouts['metaboxhidden'][$post_type] = get_user_option('metaboxhidden_'.$post_type, $admin_id);
			$jr_admin_layouts['metaboxorder'][$post_type] = get_user_option('meta-box-order_'.$post_type, $admin_id);
			$jr_admin_layouts['screenlayout'][$post_type] = get_user_option('screen_layout_'.$post_type, $admin_id);
			$jr_admin_layouts['columnshidden'][$post_type] = get_user_option('manageedit-'.$post_type.'columnshidden', $admin_id);	
		}
	}
}
function jr_metaboxhidden($value){
	global $jr_admin_layouts;
	$post_type = get_post_type();
	if (!empty($post_type)){
		return $jr_admin_layouts['metaboxhidden'][$post_type];
	} else {
		return $value;
	}
}
function jr_metaboxorder($value){
	global $jr_admin_layouts;
	$post_type = get_post_type();
	if (!empty($post_type)){
		return $jr_admin_layouts['metaboxorder'][$post_type];
	} else {
		return $value;
	}
}
function jr_screenlayout($value){
	global $jr_admin_layouts;
	$post_type = get_post_type();
	if (!empty($post_type)){
		return $jr_admin_layouts['screenlayout'][$post_type];
	} else {
		return $value;
	}
}
function jr_columnshidden($value){
	global $jr_admin_layouts;
	$post_type = get_post_type();
	if (!empty($post_type)){
		return $jr_admin_layouts['columnshidden'][$post_type];
	} else {
		return $value;
	}
}

/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*==================================================== SITE SPECIFIC ======================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/

/*=========================================================================================================*/
/*====================================== WP_Query Modifications ===========================================*/
/*======================================       (Front End)       ==========================================*/
/*=========================================================================================================*/
add_filter('pre_get_posts', 'jr_query_defaults' ); // http://codex.wordpress.org/Plugin_API/Action_Reference/pre_get_posts
function jr_query_defaults($query){
	if (is_admin() || !$query->is_main_query()){
        return;
	}
	// http://codex.wordpress.org/WordPress_Query_Vars  // http://codex.wordpress.org/Class_Reference/WP_Query
	if (is_post_type_archive('post')){
		$query->set('posts_per_page', 10);
	}
	if (!is_post_type_archive('post')){
		$query->set('orderby', 'menu_order title');
	}
	/*
	if (is_post_type_archive('events')){
		$query->set('meta_key', 'events-start-date');
		$query->set('orderby', 'meta_value date');
		$query->set('order', 'DESC');
	}
	*/
	return;
}

/*=========================================================================================================*/
/*====================================== WP_Query Modifications ===========================================*/
/*======================================       (Back End)       ===========================================*/
/*=========================================================================================================*/
add_filter('pre_get_posts', 'jr_admin_query_defaults');
function jr_admin_query_defaults($query) {
	if (!is_admin() || !empty($_GET['orderby'])) {
		return;
	}
	// Sort everything by menu_order except posts, which should be sorted by date
	if (!is_post_type_archive('post')){
		$query->set('orderby', 'menu_order title');
	}
	// Set the default visible amount of posts on a page
	if (empty($_GET['posts_per_page'])){
		$query->set('posts_per_page', 50);
	}
	// Sort by a custom value for Events
	/*
	if (is_post_type_archive('events')){
		$query->set('meta_key', 'events-start-date');
		$query->set('orderby', 'meta_value date');
		$query->set('order', 'DESC');
	}
	*/
	return;
}

/*=========================================================================================================*/
/*======================================= current-menu-item fix ===========================================*/
/*=========================================================================================================*/
add_filter('nav_menu_css_class', 'jr_current_menu_item_class', 10, 2 );
function jr_current_menu_item_class($classes, $item) {
	global $post, $wp_query;
	// ADD Page Slug to Menu Item Class
	$id = get_post_meta($item->ID, '_menu_item_object_id', true);
	$slug = basename(get_permalink($id));
	$classes[] = 'menu-item-'.$slug;
	// ADD current-menu-item classes to a menu-item whose slug matches the current post_type
	if ($slug == get_post_type()){
		$classes[] = 'current_page_item';
	}
	// ADD current-menu-item classes to a CUSTOM match
	/*
	if ($slug == 'poge_slug' && get_post_type() == 'holla'){
		$classes[] = 'current_page_item';
	}
	if ($item->title == "Healthy Tips" && get_post_type() == "healthy-tips"){
		$classes[] = 'current_page_item';
	}
	if ($item->post_name == "Healthy Tips" && get_post_type() == "healthy-tips"){
		$classes[] = 'current_page_item';
	}
	*/
	// REMOVE current-menu-item from Blog)
	if ($item->type == "post_type" && get_post_type() != "post"){
		foreach ($classes as $key => $class){
			if ($class=='current-menu-item') unset($classes[$key]);
			if ($class=='current_page_parent') unset($classes[$key]);
		}
	}
	return $classes;
}

/*=========================================================================================================*/
/*========================================= edit.php - Columns ============================================*/
/*=========================================================================================================*/
add_filter('manage_pages_columns', 'jr_manage_pages_columns');
function jr_manage_pages_columns($columns){
	unset($columns['date']);
	unset($columns['author']);
	unset($columns['comments']);
	return $columns;
}
add_filter('manage_posts_columns', 'jr_manage_posts_columns');
function jr_manage_posts_columns($columns){
	unset($columns['author']);
	return $columns;
}
add_filter('manage_post-type_posts_columns', 'jr_manage_post_type_posts_columns');
function jr_manage_post_type_posts_columns($columns){
	unset($columns['date']);
	return $columns;
}

/*=========================================================================================================*/
/*=========================================== Options Pages ===============================================*/
/*=========================================================================================================*/
/*
// OLD WAY
add_filter('acf_settings', 'jr_acf_options_page_settings');
function jr_acf_options_page_settings($options){
    $options['options_page']['title'] = 'Global Options';
    $options['options_page']['pages'] = array('Contact Info', 'Site Settings');
    return $options;
}
*/
if (function_exists("register_options_page")){
    register_options_page('Site Settings');
    // register_options_page('Footer');
}


/*=========================================================================================================*/
/*=================== Taxonomy Drag n Drop Sorting (Gecka Terms Ordering dependency) =======================*/
/*=========================================================================================================*/
add_action('admin_init', 'jr_do_tax_sort');
function jr_do_tax_sort(){
	if(function_exists('add_term_ordering_support')){
		$taxonomies=get_taxonomies('','objects'); 
		foreach ($taxonomies as $taxonomy){
			add_term_ordering_support($taxonomy->name);
		}
	}
}
