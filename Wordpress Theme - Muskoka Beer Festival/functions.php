<?php
/*
Muskoka Beer Festival 
by Jordan Rynard
*/
/*=========================================================================================================*/
/*=========================================== Image Sizes =================================================*/
/*=========================================================================================================*/
add_theme_support('post-thumbnails');
set_post_thumbnail_size(258, 0, false);

add_image_size('lightbox', 1024, 768, false);
add_image_size('collaborator', 320, 200, false);
add_image_size('gallery', 236, 155, true);
add_image_size('slider', 758, 521, false);
add_image_size('entertainment', 331, 0, false);
add_image_size('entertainment-secondary', 514, 0, false);
add_image_size('collab-pop', 260, 234, false);
add_image_size('sponsor', 480, 250, false);
add_image_size('call-to-action', 430, 322, true);

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
define('CWS_URL', get_template_directory_uri());
define('CWS_TEMPLATE_URL', get_template_directory_uri()); // http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
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
function print_arc($array){
	echo "<xmp>";
	print_r($array);
	echo "</xmp>";
}
function print_jr($array){
	echo "<pre>";
	print_r($array);
	echo "</pre>";
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
/*======================================================= DEPRECATED ======================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================*/
/*========================================= ACF Fields - WPSC save fix ====================================*/
/*=========================================================================================================*/
// Fix for ACF Fields being overwritten by WPSC on re-save of posts (ACF < 3.3.8)
// add_action('admin_menu', 'wpsc_metabox_fix_patch',99);
function wpsc_metabox_fix_patch() {
	global $acf;
	if (has_action('save_post', array($acf->field_group, 'save_post'))>0){
		remove_action('save_post', array($acf->field_group, 'save_post'));
		add_action('save_post', array($acf->field_group, 'save_post'),99);
		if (has_action('save_post', array($acf->input, 'save_post'))>0){
			remove_action('save_post', array($acf->input, 'save_post'));
			add_action('save_post', array($acf->input, 'save_post'),99);
		} else {
			trigger_error('ACF WPSC-E-Commerce Patch is now invalid.');
		}
	} else {
		trigger_error('ACF WPSC-E-Commerce Patch is now invalid.');
	}
	/* Debug Order // The code below is useful for debugging the ACTUAL save_post order */
	// global $wp_filter;
	// print_r($wp_filter['save_post']);
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
	wp_add_dashboard_widget('jr_dashboard_widget', 'Canada Web Services', 'jr_dashboard_widget');
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
// Check
add_filter('nav_menu_css_class', 'jr_current_menu_item_class', 10, 2 );
function jr_current_menu_item_class($classes, $item) {
	global $post, $wp_query;
	// ADD current-menu-item to a Nav Link
	if ($item->post_name == "page-slug" && get_post_type() == "post_type"){
		$classes[] = 'current-menu-item';
	}
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
add_filter('acf_settings', 'jr_acf_options_page_settings');
function jr_acf_options_page_settings($options){
    $options['options_page']['title'] = 'Global Options';
    $options['options_page']['pages'] = array('Site Settings');
    return $options;
}
/*=========================================================================================================*/
/*======================== Taxonomy Drag n Drop Sorting (Gecka Terms Ordering) ============================*/
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
 




/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*==================================================== TICKET SYSTEM ======================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/





function PPHttpPost($methodName_, $nvpStr_) {
	global $environment;
 
	// Set up API credentials, PayPal end point, and API version.
	$API_UserName = urlencode('CREDENTIALS REMOVED');
	$API_Password = urlencode('CREDENTIALS REMOVED');
	$API_Signature = urlencode('CREDENTIALS REMOVED');
	$API_Endpoint = "https://api-3t.paypal.com/nvp";
	if("sandbox" === $environment || "beta-sandbox" === $environment) {
		$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
	}
	$version = urlencode('51.0');
 
	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
 
	// Turn off the server and peer verification (TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
 
	// Set the API operation, version, and API signature in the request.
	$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
 
	// Set the request as a POST FIELD for curl.
	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
 
	// Get response from the server.
	$httpResponse = curl_exec($ch);
 
	if(!$httpResponse) {
		exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
	}
 
	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);
 
	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if(sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = urldecode($tmpAr[1]);
		}
	}
 
	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	}
 
	return $httpParsedResponseAr;
}
function doResults(){
	global $httpParsedResponseAr, $all_results, $nvpStr, $total_count, $environment;
	$httpParsedResponseAr = PPHttpPost('TransactionSearch', $nvpStr);
	// echo "<div>".$nvpStr."</div>";
	if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
		// echo 'TransactionSearch Completed Successfully<br/><br/>';
		$all_results[] = $httpParsedResponseAr;
		// Do Count
		$_i=0; while (!empty($httpParsedResponseAr['L_TIMESTAMP'.$_i])){
			$total_count++;
			$lasttimestamp = urldecode($httpParsedResponseAr['L_TIMESTAMP'.$_i]);
			$_i++;
		}
		// Check if there's more queries (caps at 100)
		$i=0; while (!empty($httpParsedResponseAr['L_ERRORCODE'.$i])){
			$error = $httpParsedResponseAr['L_ERRORCODE'.$i];
			if ($error == '11002'){
				parse_str($nvpStr, $query_string);
				if(!empty($lasttimestamp)){
					$query_string['ENDDATE'] = $lasttimestamp; // date("m/d/Y",strtotime(end($timestamp)));
					$nvpStr = "&".urldecode(http_build_query($query_string));
					doResults();
				}
			}
			$i++;
		}
		// print_r($httpParsedResponseAr, true);
	} else  {
		echo 'TransactionSearch failed<br/><br/>';
		print_r($httpParsedResponseAr);
	}
}

add_action('admin_menu', 'paypal_customers_menu_page');

function paypal_customers_menu_page() {
   add_menu_page('Paypal Customers', 'Paypal Customers', 'edit_pages', 'jr-paypal-customers', 'do_jr_paypal_customers', '', 3);
   // add_submenu_page('jr-paypal-customers', 'Download Contact List', 'Download Contact List', 'edit_pages', 'jr-paypal-customers_download', 'do_jr_paypal_customers_download');
}
function do_jr_paypal_customers_download(){
	header("Content-Type:application/csv"); 
	header("Content-Disposition:attachment;filename=customers.csv"); 
	do_jr_paypal_customers(true);
	die();
}	 
function do_jr_paypal_customers($download=false){	 
	global $httpParsedResponseAr, $all_results, $nvpStr, $total_count, $environment;
	if (!$download){
		echo '<script>alert("This will take a moment to load - please be patient.\nThe more customers there are the longer this takes.");</script>';
	}
	/** TransactionSearch NVP example; last modified 08MAY23.
	 *
	 *  Search your account history for transactions that meet the criteria you specify. 
	*/
	 
	$environment = 'live';	// or 'beta-sandbox' or 'live'
	 
	/**
	 * Send HTTP POST Request
	 *
	 * @param	string	The API method name
	 * @param	string	The POST Message fields in &name=value pair format
	 * @return	array	Parsed HTTP Response body
	 */
	 
	// Set request-specific fields.
	// $transactionID = urlencode('example_transaction_id');
	 
	// Add request-specific fields to the request string.
	$nvpStr = "&TRANSACTIONID=$transactionID";
	 
	// Set additional request-specific fields and add them to the request string.
	$startDateStr = "06/14/2013";			// in 'mm/dd/ccyy' format
	$endDateStr = date("m/d/Y",time()+43200);			// in 'mm/dd/ccyy' format
	if(isset($startDateStr)) {
	   $start_time = strtotime($startDateStr);
	   $iso_start = date('Y-m-d\T00:00:00\Z',  $start_time);
	   $nvpStr .= "&STARTDATE=$iso_start";
	  }
	 
	if(isset($endDateStr)&&$endDateStr!='') {
	   $end_time = strtotime($endDateStr);
	   $iso_end = date('Y-m-d\T24:00:00\Z', $end_time);
	   $nvpStr .= "&ENDDATE=$iso_end";
	}
	$nvpStr .= "&TransactionClass=Received";
	 
	// Execute the API operation; see the PPHttpPost function above.
	// $httpParsedResponseAr = PPHttpPost('TransactionSearch', $nvpStr);
	$all_results = array();
	$total_count = 0;
	//echo "<pre>";
	doResults();
	$customers = array('_empty'=>array());
	$array_check = array();
	$new_purchases = true;
	foreach ($all_results as $array){
		$i=0; while ($array['L_TRANSACTIONID'.$i]){
			if (empty($array['L_EMAIL'.$i])){
				$customers['_empty'][] = $array['L_NAME'.$i];
			} else {
				$key = preg_replace('/[^A-Za-z0-9-]/', '', urldecode($array['L_EMAIL'.$i]));
				$transaction_id = $array['L_TRANSACTIONID'.$i];
				$result = PPHttpPost('GetTransactionDetails', '&TRANSACTIONID='.$transaction_id);
				if ($result['L_NAME0'] == 'Muskoka Beer Festival 2013 Tickets'){
					$customers[$key."_".$i] = array(
						'name'=>urldecode($array['L_NAME'.$i]),
						'email'=>urldecode($array['L_EMAIL'.$i]),
						'address'=>$result['SHIPTONAME']."<br/>".$result['SHIPTOSTREET']."<br/>".(!empty($result['SHIPTOSTREET2'])?$result['SHIPTOSTREET2']."<br/>":'').$result['SHIPTOCITY']."<br/>".$result['SHIPTOSTATE']."<br/>".$result['SHIPTOCOUNTRYNAME']."<br/>".$result['SHIPTOZIP'],
						'qty'=>$result['L_QTY0'],
						'when'=>$result['L_OPTIONSVALUE0'],
						'time'=>$result['ORDERTIME'],
					);
					if (!empty($array_check[$key])){
						$customers[$key."_".$i]['duplicate']=true;
					}
					if (!empty($result['SHIPTOSTREET2'])){
						$customers[$key."_".$i]['address2']=true;
					}
					$array_check[$key]=true;
				}
			}
			$i++;
		}
	}
	if ($download){
		$output = fopen("php://output",'w') or die("Can't open php://output");
		// header("Content-Type:application/csv"); 
		// header("Content-Disposition:attachment;filename=customers.csv"); 
		fputcsv($output, array('name','email','address','qty','when','time'));
		foreach($customers as $key => $customer) {
		    if ($key == '_empty' || empty($customer['email'])){
		    	continue;
		    }
		    // $customer = array($customer['name'], $customer['email']);
		    $customer['address'] = str_replace("<br>","\n\r",$customer['address']);
		    $customer['address'] = str_replace("<br/>","\n\r",$customer['address']);
		    $customer['address'] = str_replace("<br />","\n\r",$customer['address']);
		    $customer['time'] = date('M d, Y',strtotime($customer['time']));
		    
		    fputcsv($output, $customer);
		}
		fclose($output) or die("Can't close php://output");
	} else {
		echo "<h1>Paypal Customers</h1>";
		echo "<a href='".get_bloginfo('home')."/wp-admin/admin-ajax.php?action=jr_download_paypal_customers'>Download as Contact List (csv)</a> &nbsp; <i><small>(Imports into Excel, Email Address Books, etc)</small></i><br/>";
		echo "<br/>";
		echo "<div><span style='color:rgba(255,0,0,.4);'>Additional Address Info</span> <span style='color:rgba(0,255,0,.4);'>User made more than one purchase</span> <span style='color:rgba(255,255,0,.4);'>Both of the aforementioned</span></div>";
		echo "<style>table.customer-table tr:nth-child(even){background:rgba(0,0,0,.05);}</style>";
		echo "<table class='customer-table' cellspacing='0'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th style='text-align:left;padding:10px 10px 10px 10px;'>Name</th>";
		echo "<th style='text-align:left;padding:10px 10px 10px 10px;'>Email</th>";
		echo "<th style='text-align:left;padding:10px 10px 10px 10px;'>Mail To</th>";
		echo "<th style='text-align:center;padding:10px 10px 10px 10px;'>Tickets</th>";
		echo "<th style='text-align:left;padding:10px 10px 10px 10px;'>When</th>";
		echo "<th style='text-align:left;padding:10px 10px 10px 10px;'>Date</th>";
		echo "<th style='text-align:left;padding:10px 10px 10px 10px; display:none;'>Debug</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($customers as $key => $customer){
		    if ($key == '_empty' || empty($customer['email']) || $customer['email']=='jordanrynard@arcadiasites.com'){
		    	continue;
		    }
			$this_class = '';
			if ($customer['email'] == 'greg.drew@gmail.com'){
				$new_purchases = false;
			}
			if (!$new_purchases){
				if (!empty($customer['duplicate'])){
					$changed_duplicate = true;
					$this_class .= 'duplicate';
				}
				if (!empty($customer['address2'])){
					$changed_address = true;
					$this_class .= ' address2';
				}
			}
			echo "<tr class='".$this_class."'>";
			echo "<td style='vertical-align:top;padding:10px 10px 10px 10px;'>".$customer['name']."</td>";
			echo "<td style='vertical-align:top;padding:10px 10px 10px 10px;'>".$customer['email']."</td>";
			echo "<td style='vertical-align:top;padding:10px 10px 10px 10px;'>".$customer['address']."</td>";
			echo "<td style='vertical-align:top;padding:10px 10px 10px 10px;text-align:center'>".$customer['qty']."</td>";
			echo "<td style='vertical-align:top;padding:10px 10px 10px 10px;'>".$customer['when']."</td>";
			echo "<td style='vertical-align:top;padding:10px 10px 10px 10px;'>".date('M d, Y',strtotime($customer['time']))."</td>";
			echo "</tr>";
		}
		echo "</tbody>";
		echo "</table>";
		echo "<style>table.customer-table tr.duplicate{background:rgba(0,255,0,.4)!important;} table.customer-table tr.address2{background:rgba(255,0,0,.4);!important} table.customer-table tr.address2.duplicate{background:rgba(255,255,0,.4)!important;}</style>";
	}
}
add_action('wp_ajax_jr_download_paypal_customers', 'do_jr_paypal_customers_download');


add_action('wp_ajax_nopriv_save_session_survey', 'save_session_survey');
function save_session_survey(){
	$survey_count = get_option('survey_count');
	if (empty($survey_count)){
		$survey_count = 0;
	}
	$survey_count++;
	error_log(json_encode($_POST));
	$results = array(
		'where_did_you_hear' => preg_replace('/[^ \w]+/', "", $_POST['where_did_you_hear']),
		'is_first_session' => preg_replace('/[^ \w]+/', "", $_POST['is_first_session']),
		'heard_from_other' => preg_replace('/[^ \w]+/', "", $_POST['heard_from_other'])
	);
	update_option('survey_'.$survey_count, json_encode($results));
	update_option('survey_count', $survey_count);
	die();
}


/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*======================================================= SURVEY ==========================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/
/*=========================================================================================================================*/


// Function that outputs the contents of the dashboard widget
function dashboard_widget_session_survey() {
	$survey_results = array('where_did_you_hear'=>array(), 'is_first_session'=>array());
	$survey_count = get_option('survey_count');
	$i=0; while ($i <= $survey_count){ $i++;
		$result = get_option('survey_'.$i);
		if (!empty($result)){
			$result = json_decode($result);
			$survey_results['where_did_you_hear'][$result->{where_did_you_hear}]++;
			$survey_results['heard_from_other'][strtolower(str_replace(" ","", preg_replace("/[^ \w]+/", "", $result->{heard_from_other})))] = $result->{heard_from_other};
			$survey_results['is_first_session'][$result->{is_first_session}]++;
		}
	}
	?>
	Where did you hear about us?
	<div id="pie1" style="height:200px;width:300px; "></div>
	Is this your first Session?
	<div id="pie2" style="height:200px;width:300px; "></div>
	<div>Other places heard from:</div>
	<? foreach ($survey_results['heard_from_other'] as $heard_key => $heard_other): ?>
		<? if (empty($heard_key)) continue; ?>
		<i style="display:inline-block;padding-left:10px"><?=$heard_other?></i><br/>
	<? endforeach; ?>
	<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?=get_bloginfo('template_url')?>/scripts/jqplot/excanvas.js"></script><![endif]-->
	<script language="javascript" type="text/javascript" src="<?=get_bloginfo('template_url')?>/scripts/jqplot/jquery.jqplot.min.js"></script>
	<script type="text/javascript" src="<?=get_bloginfo('template_url')?>/scripts/jqplot/plugins/jqplot.pieRenderer.min.js"></script>
	<script type="text/javascript" src="<?=get_bloginfo('template_url')?>/scripts/jqplot/plugins/jqplot.donutRenderer.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?=get_bloginfo('template_url')?>/scripts/jqplot/jquery.jqplot.css" />	
	<script>
		jQuery(document).ready(function($){
		  var pieData = [
			<? $i=0; foreach ($survey_results['where_did_you_hear'] as $key => $value): $i++; ?>
				<? if ($i!=1): ?>,<? endif;?>
				['<?=ucwords($key)?> (<?=$value?>)', <?=$value?>]
			<? endforeach; ?>
		  ];
		  var plot1 = jQuery.jqplot ('pie1', [pieData], 
		    { 
		      seriesDefaults: {
		        // Make this a pie chart.
		        renderer: jQuery.jqplot.PieRenderer, 
		        rendererOptions: {
		          // Put data labels on the pie slices.
		          // By default, labels show the percentage of the slice.
		          showDataLabels: true
		        }
		      }, 
		      legend: { show:true, location: 'e' }
		    }
		  );
		});		
		jQuery(document).ready(function($){
		  var pieData2 = [
			<? $i=0; foreach ($survey_results['is_first_session'] as $key => $value): $i++; ?>
				<? if ($i!=1): ?>,<? endif;?>
				['<?=ucwords($key)?> (<?=$value?>)', <?=$value?>]
			<? endforeach; ?>
		  ];
		  var plot1 = jQuery.jqplot ('pie2', [pieData2], 
		    { 
		      seriesDefaults: {
		        // Make this a pie chart.
		        renderer: jQuery.jqplot.PieRenderer, 
		        rendererOptions: {
		          // Put data labels on the pie slices.
		          // By default, labels show the percentage of the slice.
		          showDataLabels: true
		        }
		      }, 
		      legend: { show:true, location: 'e' }
		    }
		  );
		});		
	</script>
	<?
}
// Function used in the action hook
function add_dashboard_session_survey_widget() {
	wp_add_dashboard_widget('dashboard_widget', 'Session Survey Results', 'dashboard_widget_session_survey');
}
// Register the new dashboard widget with the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'add_dashboard_session_survey_widget');

add_filter( 'query_vars', 'addnew_query_vars', 10, 1 );
function addnew_query_vars($vars)
{   
    $vars[] = 'year'; // var1 is the name of variable you want to add       
    return $vars;
}
