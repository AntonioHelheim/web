<?php 
class WETAPortfolioPost 
{
	function __construct() {
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		add_action( 'init', array( $this, 'create_cat' ) );
		add_filter( 'template_include', array( $this, 'portfolio_template_include' ) );
	}
	
	public function portfolio_template_include( $template ) {
		if ( is_singular( 'portfolio' ) ) {
			return $this->get_template( 'single-portfolio.php');
		}
		return $template;
	}
	
	public function get_template( $template ) {
		if ( $theme_file = locate_template( array( $template ) ) ) {
			$file = $theme_file;
		} 
		else {
			$file = WETA_CORE_ADDONS_DIR . '/include/template/'. $template;
		}
		return apply_filters( __FUNCTION__, $file, $template );
	}
	
	
	public function register_custom_post_type() {
		// $medidove_mem_slug = get_theme_mod('medidove_mem_slug','member'); 
		$labels = array(
			'name'                  => esc_html_x( 'Portfolios', 'Post Type General Name', 'weta-core' ),
			'singular_name'         => esc_html_x( 'Portfolio', 'Post Type Singular Name', 'weta-core' ),
			'menu_name'             => esc_html__( 'Portfolios', 'weta-core' ),
			'name_admin_bar'        => esc_html__( 'Portfolio', 'weta-core' ),
			'archives'              => esc_html__( 'Item Archives', 'weta-core' ),
			'parent_item_colon'     => esc_html__( 'Parent Item:', 'weta-core' ),
			'all_items'             => esc_html__( 'All Items', 'weta-core' ),
			'add_new_item'          => esc_html__( 'Add New Portfolio', 'weta-core' ),
			'add_new'               => esc_html__( 'Add New', 'weta-core' ),
			'new_item'              => esc_html__( 'New Item', 'weta-core' ),
			'edit_item'             => esc_html__( 'Edit Item', 'weta-core' ),
			'update_item'           => esc_html__( 'Update Item', 'weta-core' ),
			'view_item'             => esc_html__( 'View Item', 'weta-core' ),
			'search_items'          => esc_html__( 'Search Item', 'weta-core' ),
			'not_found'             => esc_html__( 'Not found', 'weta-core' ),
			'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'weta-core' ),
			'featured_image'        => esc_html__( 'Featured Image', 'weta-core' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'weta-core' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'weta-core' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'weta-core' ),
			'inserbt_into_item'     => esc_html__( 'Insert into item', 'weta-core' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'weta-core' ),
			'items_list'            => esc_html__( 'Items list', 'weta-core' ),
			'items_list_navigation' => esc_html__( 'Items list navigation', 'weta-core' ),
			'filter_items_list'     => esc_html__( 'Filter items list', 'weta-core' ),
		);

		$args   = array(
			'label'                 => esc_html__( 'Portfolio', 'weta-core' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail'),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'   			=> 'dashicons-index-card',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,		
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);

		register_post_type( 'portfolio', $args );
	}
	
	public function create_cat() {
		$labels = array(
			'name'                       => esc_html_x( 'Portfolio Categories', 'Taxonomy General Name', 'weta-core' ),
			'singular_name'              => esc_html_x( 'Portfolio Categories', 'Taxonomy Singular Name', 'weta-core' ),
			'menu_name'                  => esc_html__( 'Portfolio Categories', 'weta-core' ),
			'all_items'                  => esc_html__( 'All Portfolio Category', 'weta-core' ),
			'parent_item'                => esc_html__( 'Parent Item', 'weta-core' ),
			'parent_item_colon'          => esc_html__( 'Parent Item:', 'weta-core' ),
			'new_item_name'              => esc_html__( 'New Portfolio Category Name', 'weta-core' ),
			'add_new_item'               => esc_html__( 'Add New Portfolio Category', 'weta-core' ),
			'edit_item'                  => esc_html__( 'Edit Portfolio Category', 'weta-core' ),
			'update_item'                => esc_html__( 'Update Portfolio Category', 'weta-core' ),
			'view_item'                  => esc_html__( 'View Portfolio Category', 'weta-core' ),
			'separate_items_with_commas' => esc_html__( 'Separate items with commas', 'weta-core' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove items', 'weta-core' ),
			'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'weta-core' ),
			'popular_items'              => esc_html__( 'Popular Portfolio Category', 'weta-core' ),
			'search_items'               => esc_html__( 'Search Portfolio Category', 'weta-core' ),
			'not_found'                  => esc_html__( 'Not Found', 'weta-core' ),
			'no_terms'                   => esc_html__( 'No Portfolio Category', 'weta-core' ),
			'items_list'                 => esc_html__( 'Portfolio Category list', 'weta-core' ),
			'items_list_navigation'      => esc_html__( 'Portfolio Category list navigation', 'weta-core' ),
		);

		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
		);

		register_taxonomy('portfolio-cat','portfolio', $args );
	}

}

new WETAPortfolioPost();