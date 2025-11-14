<?php
namespace WETACore;

use WETACore\PageSettings\Page_Settings;
use Elementor\Controls_Manager;


/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.2.0
 */
class WETA_Core_Plugin {

	/**
	 * Instance
	 *
	 * @since 1.2.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Add Category
	 */

    public function weta_core_elementor_category($manager)
    {
        $manager->add_category(
            'weta_core',
            array(
                'title' => esc_html__('WETA Core Addons', 'weta-core'),
                'icon' => 'eicon-banner',
            )
        );
    }

	/**
	 * widget_styles
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function widget_styles() {
		wp_enqueue_style('weta-core', WETA_CORE_ADDONS_URL . 'assets/css/weta-core.css', null, '1.0');
	}

	/**
	 * widget_scripts
	 *
	 * Load required plugin core files.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function widget_scripts() {
		wp_enqueue_script( 'weta-core', plugins_url( '/assets/js/hello-world.js', __FILE__ ), [ 'jquery' ], false, true );
	}

	/**
	 * Editor scripts
	 *
	 * Enqueue plugin javascripts integrations for Elementor editor.
	 *
	 * @since 1.2.1
	 * @access public
	 */
	public function editor_scripts() {
		add_filter( 'script_loader_tag', [ $this, 'editor_scripts_as_a_module' ], 10, 2 );

		wp_enqueue_script(
			'weta_core-editor',
			plugins_url( '/assets/js/editor/editor.js', __FILE__ ),
			[
				'elementor-editor',
			],
			'1.2.1',
			true
		);
	}


	/**
	 * weta_enqueue_editor_scripts
	 */
    function weta_enqueue_editor_scripts()
    {
        wp_enqueue_style('weta-element-addons-editor', WETA_CORE_ADDONS_URL . 'assets/css/editor.css', null, '1.0');
    }

	/**
	 * Force load editor script as a module
	 *
	 * @since 1.2.1
	 *
	 * @param string $tag
	 * @param string $handle
	 *
	 * @return string
	 */
	public function editor_scripts_as_a_module( $tag, $handle ) {
		if ( 'weta-core-editor' === $handle ) {
			$tag = str_replace( '<script', '<script type="module"', $tag );
		}

		return $tag;
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		// Its is now safe to include Widgets files
		foreach($this->weta_core_widget_list() as $widget_file_name){
			require_once( WETA_CORE_ELEMENTS_PATH . "/{$widget_file_name}.php" );
		}
	}

	public function weta_core_widget_list() {
		return [
			'heading',
			'testimonial-slider',
			'weta-btn',
			'hero',
			// 'hero-slider-1',
			// 'hero-slider-2',
			// 'about',
			'services',
			// 'service-list',
			// 'services-box',
			// 'info-list',
			'project',
			'project-slider',
			'quote-slider',
			// 'project-info',
			// 'promotion',
			'team',
			'team-slider',
			'team-details',
			'pricing',
			'features',
			'post-list',
			'faq',
			// 'fun-fact',
			'video-popup',
			'brand-slider',
			'image',
			'cta',
			'quote',
			// 'contact-info',
			// 'contact-info-box',
			'contact-form',
			// 'info-point',
			'work-process',
			// 'how-it-work',
			// 'progressbar',
		];
	}

	/**
	 * Add page settings controls
	 *
	 * Register new settings for a document page settings.
	 *
	 * @since 1.2.1
	 * @access private
	 */
	private function add_page_settings_controls() {
		require_once( __DIR__ . '/page-settings/manager.php' );
		new Page_Settings();
	}

	/**
	 * Register controls
	 *
	 * @param Controls_Manager $controls_Manager
	 */

    public function register_controls(Controls_Manager $controls_Manager)
    {
        include_once(WETA_CORE_ADDONS_DIR . '/controls/wetagradient.php');
        $wetagradient = 'WETACore\Elementor\Controls\Group_Control_WETAGradient';
        $controls_Manager->add_group_control($wetagradient::get_type(), new $wetagradient());

        include_once(WETA_CORE_ADDONS_DIR . '/controls/wetabggradient.php');
        $wetabggradient = 'WETACore\Elementor\Controls\Group_Control_WETABGGradient';
        $controls_Manager->add_group_control($wetabggradient::get_type(), new $wetabggradient());
    }

	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function __construct() {

        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'widget_scripts' ] );

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_scripts' ] );

		add_action('elementor/elements/categories_registered', [$this, 'weta_core_elementor_category']);

	    add_action('elementor/controls/controls_registered', [$this, 'register_controls']);

	    add_action('elementor/editor/after_enqueue_scripts', [$this, 'weta_enqueue_editor_scripts'] );

		$this->add_page_settings_controls();

	}
}

// Instantiate Plugin Class
WETA_Core_Plugin::instance();