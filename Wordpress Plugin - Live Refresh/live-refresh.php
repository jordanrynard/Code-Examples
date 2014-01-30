<?
/*
Plugin Name: Live Refresh
Description: Refreshes a webpage on file changes and wordpress backend post updates. Updates CSS without a page refresh. Processes SCSS files into CSS with debugging info.
Author: Jordan Rynard
Version: 1.0.0
Repository: cws
Includes: Pusher API; the SassParser from PHPSASS
Notes: 1) For now you must change the theme location in the class definitions in live-refresh.php and file-alteration-monitor.php 2) View the JS Console for real-time data 3) Dashboard Saves are detected by touching a file on save;
*/


class liveRefresh {

	// For now we'll define this manually, leave the autodetect for v2
	private $theme_location = '/../wp-content/themes/twentyfourteen/';

	function __construct(){
		add_action('init', array($this,'devToolsInit'), 100);
	}

	function devToolsInit(){
		wp_enqueue_script('jquery');

		// Admin Menu Bar
		if (!is_admin()){
			add_action('wp_head', array($this,'admin_menu_bar_head_stuff'), 100);
		} else {
			add_action('admin_head', array($this,'admin_menu_bar_head_stuff'), 100);
		}
		if (current_user_can('administrator')){
			add_action('admin_bar_menu', array($this,'add_admin_bar_link'), 900);
			add_action('wp_ajax_livephp-settings', array($this,'ajaxHandler'), 100);
		}

		// Dev tool stuff
		$status = get_option('live-refresh_status');
		if ($status == 'dev' && !is_admin() && current_user_can('administrator')){
			wp_enqueue_script('pusher', plugins_url('libraries/pusher/pusher.min.js', __FILE__));
			wp_enqueue_script('live-refresh', plugins_url('live-refresh.js', __FILE__));
		}	

		// WP Save Trigger
	    if ($status == 'dev' && is_admin() && current_user_can('administrator')){
	        add_action('save_post', array($this,'devToolsTouchContentCheckFile'), 100);
	    }

	}

	function devToolsTouchContentCheckFile(){
	    $contentCheckFile = getcwd().$this->theme_location.'/wp-contentcheck.txt';
	    touch($contentCheckFile);
	}

	function add_admin_bar_link(){
		global $wp_admin_bar;
		$status = get_option('live-refresh_status');
		if (empty($status)) $status = 'live';
		$alternative_status = $status=='dev'?'Live':'Refresh Monitoring';
		$status_message = $status=='dev'?'Monitoring for Changes':'Refresh Monitoring Disabled';
		$wp_admin_bar->add_menu(
			array(
				'id' => 'live-refresh',
				'title' => '<span class="live-refresh live-refresh-icon '.$status.'">'.$status_message.'</span>',
				'href' => '#',
				'meta'  => array(
					'title' => __('Switch site to '.$alternative_status.' Mode'),
				)
			)
		);
	}

	function ajaxHandler() {
		check_ajax_referer( 'wp-livephp-top-secret', 'security' );
		if (isset($_POST['option']) && isset($_POST['state'])){
			if ($_POST['state']=='dev'){
				update_option('live-refresh_status','dev');
			}
			if ($_POST['state']=='live'){
				update_option('live-refresh_status','live');
			}
		}
		die();
	}

	function admin_menu_bar_head_stuff(){
		$path = plugins_url( '/images/', __FILE__);
		$ajax_nonce = wp_create_nonce("wp-livephp-top-secret");
		$key = 'status';
		?>
		<script>
		if(typeof(jQuery)!="undefined") {
			<?php if (!is_admin()) : ?>
			if (typeof(ajaxurl) == "undefined") {
				var ajaxurl = "<?php echo admin_url('admin-ajax.php') ?>";
			}
			<?php endif ?>
			function live_option_switch(opt, state, reload) {
				reload = typeof(reload) == "undefined" ? false : reload;
				var data = {
					action: "livephp-settings",
					security: '<?php echo $ajax_nonce; ?>',
					option: opt,
					state:  state
				};
				jQuery.post(ajaxurl, data, function(response) {
					if ('content' == opt && state) {
						if (response != '') {
							// error updating the option
							jQuery('#enable_content').click();
						}
						jQuery('#wp-livephp_contenterror').html(response);
					}
					if (reload) {
						location.reload();
					}
				});
			}
			jQuery(document).ready(function(){
				jQuery('#wp-admin-bar-live-refresh a').click(function(){
					return false;
				});
				jQuery('#wp-admin-bar-live-refresh a .live-refresh').click(function(){
					var h = jQuery(this).hasClass('live') ? 'dev' : 'live';
					live_option_switch('<?php echo $key ?>', h, true);
					jQuery(this).blur();
				});
			});
		}
		</script>
		<style>
			#wpadminbar .live-refresh-icon {
				padding-left:20px;
				background:url('<?=plugins_url('img/red_circle.png', __FILE__)?>');
				background-size:17px 17px;
				background-repeat:no-repeat;
				background-position:left top;			
			}
			#wpadminbar .live-refresh-icon.live {
				background-image:url('<?=plugins_url('img/green_circle.png', __FILE__)?>');
			}
		</style>
		<?
	}

} // end liveRefresh



new liveRefresh();

