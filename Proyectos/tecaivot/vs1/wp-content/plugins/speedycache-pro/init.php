<?php

if(!defined('ABSPATH')){
	die('Hacking Attempt!');
}

if(!class_exists('SpeedyCache')){
#[\AllowDynamicProperties]
class SpeedyCache{
	public $options = array();
	public $brand_name = 'SpeedyCache';
	public $logs;
	public $settings;
	public $license;
	public $image;
	public $mobile_cache;
	public $columnjs;
	public $js;
	public $css_util;
	public $render_blocking;
	public $enhanced;
	public $object;
	public $bloat;
}
}

// Prevent update of speedycache free
// This also work for auto update
add_filter('site_transient_update_plugins', 'speedycache_pro_disable_manual_update_for_plugin');
add_filter('pre_site_transient_update_plugins', 'speedycache_pro_disable_manual_update_for_plugin');

// Auto update free version after update pro version
add_action('upgrader_process_complete', 'speedycache_pro_update_free_after_pro', 10, 2);

add_action('plugins_loaded', 'speedycache_pro_load_plugin');
register_activation_hook( __FILE__, 'speedycache_pro_activate');

function speedycache_pro_load_plugin(){
	global $speedycache;
	
	if(empty($speedycache)){
		$speedycache = new \SpeedyCache();
	}
	
	speedycache_pro_load_license();

	// Actions to handle WP Cron schedules
	add_action('speedycache_auto_optm', '\SpeedyCache\Image::auto_optimize', 10, 1);
	add_action('speedycache_img_delete', '\SpeedyCache\Image::scheduled_delete', 10, 1);
	add_action('speedycache_generate_ccss', '\SpeedyCache\CriticalCss::generate', 10, 1);
	add_action('speedycache_unused_css', '\SpeedyCache\UnusedCss::generate', 10, 1);
	
	speedycache_pro_update_check();
	
	// Check for updates
	include_once(SPEEDYCACHE_PRO_DIR . '/main/plugin-update-checker.php');
	$speedycache_updater = SpeedyCache_PucFactory::buildUpdateChecker(SPEEDYCACHE_API.'/updates.php?version='.SPEEDYCACHE_PRO_VERSION, SPEEDYCACHE_PRO_FILE);

	// Add the license key to query arguments
	$speedycache_updater->addQueryArgFilter('speedycache_pro_updater_filter_args');
		
	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-speedycache-pro', 'speedycache_pro_updater_check_link', 10, 1);	
	
	if(!is_admin() && !current_user_can('activate_plugins')){
		return;
	}

	include_once SPEEDYCACHE_PRO_DIR . '/main/admin.php';
	add_action('admin_notices', 'speedycachepro_free_version_nag');
}
	
// Nag when plugins dont have same version.
function speedycachepro_free_version_nag(){
	if(!defined('SPEEDYCACHE_VERSION')){
		return;
	}

	if(version_compare(SPEEDYCACHE_VERSION, SPEEDYCACHE_PRO_VERSION) > 0){
		echo '<div class="notice notice-error">
		<p style="font-size:16px;">You are using an Older version of SpeedyCache Pro. We suggest you upgrade SpeedyCache Pro to be able to use SpeedyCache without any issue.</p>
	</div>';
	}elseif(version_compare(SPEEDYCACHE_VERSION, SPEEDYCACHE_PRO_VERSION) < 0){
		echo '<div class="notice notice-error">
		<p style="font-size:16px;">You are using an Older version of the Free version of SpeedyCache. We suggest you update the free version of SpeedyCache to be able to use SpeedyCache without any issue.</p>
	</div>';
	}
}
//register_deactivation_hook( __FILE__, '\SpeedyCache\Install::deactivate');

// Load WP CLI command(s) on demand.
if(defined('WP_CLI') && !empty(WP_CLI) && defined('SPEEDYCACHE_PRO')){
	include_once SPEEDYCACHE_PRO_DIR.'/main/cli.php';
}