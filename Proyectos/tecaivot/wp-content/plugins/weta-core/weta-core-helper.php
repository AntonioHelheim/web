<?php
/**
	* Plugin Name: WETA Core
	* Description: RRdevs elementor core plugin.
	* Plugin URI:  https://rrdevs.net
	* Version:     1.0.1
	* Author:      RRDevs
	* Author URI:  https://themeforest.net/user/rrdevs
	* Text Domain: weta-core
	* Elementor tested up to: 3.21.4
	* Elementor Pro tested up to: 3.21.4
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Controls_Manager;

/**
 * Define
*/
define('WETA_CORE_ADDONS_URL', plugins_url('/', __FILE__));
define('WETA_CORE_ADDONS_DIR', dirname(__FILE__));
define('WETA_CORE_ADDONS_PATH', plugin_dir_path(__FILE__));
define('WETA_CORE_ELEMENTS_PATH', WETA_CORE_ADDONS_DIR . '/include/elementor');
define('WETA_CORE_WIDGET_PATH', WETA_CORE_ADDONS_DIR . '/include/widgets');
define('WETA_CORE_INCLUDE_PATH', WETA_CORE_ADDONS_DIR . '/include');
define('WETA_CORE_ELEMENTS_ASSETS', trailingslashit( WETA_CORE_ADDONS_URL . 'assets/' ) );

// $GLOBAL['weta_core_icons'] = 
/**
 * Include all files
*/
include_once(WETA_CORE_ADDONS_DIR . '/include/icons/fontawesome-6.php');
include_once(WETA_CORE_ADDONS_DIR . '/include/custom-post-portfolio.php');
include_once(WETA_CORE_ADDONS_DIR . '/include/common-functions.php');
include_once(WETA_CORE_ADDONS_DIR . '/include/class-ocdi-importer.php');
include_once(WETA_CORE_ADDONS_DIR . '/include/allow-svg.php');


/**
 * WETA Core Custom Widget
*/
include_once(WETA_CORE_WIDGET_PATH . '/weta-footer-info.php');
include_once(WETA_CORE_WIDGET_PATH . '/weta-footer-contact-info.php');
include_once(WETA_CORE_WIDGET_PATH . '/weta-sidebar-blog-posts.php');
include_once(WETA_CORE_WIDGET_PATH . '/weta-footer-info-two.php');
include_once(WETA_CORE_WIDGET_PATH . '/weta-footer-get-in-touch.php');



/**
 * Main WETA Core Class
 *
 * The init class that runs the Hello World plugin.
 * Intended To make sure that the plugin's minimum requirements are met.
 *
 * You should only modify the constants to match your plugin's needs.
 *
 * Any custom code should go inside Plugin Class in the plugin.php file.
 * @since 1.2.0
 */
final class WETA_Core {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.2.0
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.2.0
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		// Init Plugin
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin
	 *
	 * Validates that Elementor is already loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed include the plugin class.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function init() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
			return;
		}

	
		// Once we get here, We have passed all validation checks so we can safely include our plugin
		require_once( 'plugin.php' );
	}


	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'weta-core' ),
			'<strong>' . esc_html__( 'WETA Core', 'weta-core' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'weta-core' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'weta-core' ),
			'<strong>' . esc_html__( 'WETA Core', 'weta-core' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'weta-core' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'weta-core' ),
			'<strong>' . esc_html__( 'WETA Core', 'weta-core' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'weta-core' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}
}

// Instantiate WETA_Core.
new WETA_Core();