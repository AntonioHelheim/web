<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Post_List extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'weta_post_list';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Blog Post', 'weta-core' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'weta-icon';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'weta_core' ];
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [ 'weta-core' ];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function register_controls() {

        // layout Panel
        $this->start_controls_section(
            'weta_layout',
            [
                'label' => esc_html__('Design Layout', 'weta-core'),
            ]
        );

        $this->add_control(
            'weta_design_style',
            [
                'label' => esc_html__('Select Layout', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__('Layout 1', 'weta-core'),
                    'layout-2' => esc_html__('Layout 2', 'weta-core'),
                    'layout-3' => esc_html__('Layout 3', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->end_controls_section();


        // weta_section_title
        $this->start_controls_section(
            'weta_section_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
            ]
        );
        
        $this->add_control(
            'weta_subtitle_before',
            [
                'label' => esc_html__('Subtitle Before', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('#6', 'weta-core'),
                'label_block' => true,
            ]
        );       
        
        $this->add_control(
            'weta_subtitle',
            [
                'label' => esc_html__('Subtitle', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Blog & News', 'weta-core'),
                'label_block' => true,
            ]
        );       
        
        $this->add_control(
            'title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Our Latest Insights', 'weta-core'),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'title_tag',
            [
                'label' => esc_html__('Title HTML Tag', 'weta-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'h1' => [
                        'title' => esc_html__('H1', 'weta-core'),
                        'icon' => 'eicon-editor-h1'
                    ],
                    'h2' => [
                        'title' => esc_html__('H2', 'weta-core'),
                        'icon' => 'eicon-editor-h2'
                    ],
                    'h3' => [
                        'title' => esc_html__('H3', 'weta-core'),
                        'icon' => 'eicon-editor-h3'
                    ],
                    'h4' => [
                        'title' => esc_html__('H4', 'weta-core'),
                        'icon' => 'eicon-editor-h4'
                    ],
                    'h5' => [
                        'title' => esc_html__('H5', 'weta-core'),
                        'icon' => 'eicon-editor-h5'
                    ],
                    'h6' => [
                        'title' => esc_html__('H6', 'weta-core'),
                        'icon' => 'eicon-editor-h6'
                    ]
                ],
                'default' => 'h2',
                'toggle' => false,
            ]
        );

        $this->end_controls_section();


        // Blog Query
		$this->start_controls_section(
            'weta_post_query',
            [
                'label' => esc_html__('Blog Query', 'weta-core'),
            ]
        );

        $post_type = 'post';
        $taxonomy = 'category';

        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__('Posts Per Page', 'weta-core'),
                'description' => esc_html__('Leave blank or enter -1 for all.', 'weta-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => '3',
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => esc_html__('Include Categories', 'weta-core'),
                'description' => esc_html__('Select a category to include or leave blank for all.', 'weta-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => weta_get_categories($taxonomy),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'exclude_category',
            [
                'label' => esc_html__('Exclude Categories', 'weta-core'),
                'description' => esc_html__('Select a category to exclude', 'weta-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => weta_get_categories($taxonomy),
                'label_block' => true
            ]
        );

        $this->add_control(
            'post__not_in',
            [
                'label' => esc_html__('Exclude Item', 'weta-core'),
                'type' => Controls_Manager::SELECT2,
                'options' => weta_get_all_types_post($post_type),
                'multiple' => true,
                'label_block' => true
            ]
        );

        $this->add_control(
            'offset',
            [
                'label' => esc_html__('Offset', 'weta-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => '0',
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => esc_html__('Order By', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => array(
			        'ID' => 'Post ID',
			        'author' => 'Post Author',
			        'title' => 'Title',
			        'date' => 'Date',
			        'modified' => 'Last Modified Date',
			        'parent' => 'Parent Id',
			        'rand' => 'Random',
			        'comment_count' => 'Comment Count',
			        'menu_order' => 'Menu Order',
			    ),
                'default' => 'date',
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__('Order', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'asc' 	=> esc_html__( 'Ascending', 'weta-core' ),
                    'desc' 	=> esc_html__( 'Descending', 'weta-core' )
                ],
                'default' => 'desc',

            ]
        );
        $this->add_control(
            'ignore_sticky_posts',
            [
                'label' => esc_html__( 'Ignore Sticky Posts', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'weta_blog_title_word',
            [
                'label' => esc_html__('Title Word Count', 'weta-core'),
                'description' => esc_html__('Set how many word you want to displa!', 'weta-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => '6',
            ]
        );

        $this->add_control(
            'weta_post_content',
            [
                'label' => __('Content', 'weta-core'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'weta-core'),
                'label_off' => __('Hide', 'weta-core'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'weta_post_content_limit',
            [
                'label' => __('Content Limit', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => '14',
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'weta_post_content' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'weta_btn_button_show',
            [
                'label' => esc_html__( 'Show Button', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'weta_btn_text',
            [
                'label' => esc_html__('Button Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Read More', 'weta-core'),
                'title' => esc_html__('Enter button text here', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_btn_button_show' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'weta_author_switch',
            [
                'label' => esc_html__( 'Author Show/Hide', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'weta_design_style' => ['layout-10'],
                ],
            ]
        );

        $this->end_controls_section();


        // button
        $this->start_controls_section(
            '_content_button',
            [
                'label' => esc_html__('Button', 'weta-core'),
                'condition' => [
                    'weta_design_style' => ['layout-3'],
                ],
            ]
        );

        $this->add_control(
            'weta_btn_button_show_02',
            [
                'label' => esc_html__( 'Show Button', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'weta_btn_text_02',
            [
                'label' => esc_html__('Button Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('More Details', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_btn_button_show_02' => 'yes'
                ],
            ]
        );
        $this->add_control(
            'weta_btn_link_type',
            [
                'label' => esc_html__('Button Link Type', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'label_block' => true,
                'condition' => [
                    'weta_btn_button_show_02' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'weta_btn_link',
            [
                'label' => esc_html__('Button link', 'weta-core'),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('https://your-link.com', 'weta-core'),
                'show_external' => false,
                'default' => [
                    'url' => '#',
                    'is_external' => true,
                    'nofollow' => true,
                    'custom_attributes' => '',
                ],
                'condition' => [
                    'weta_btn_link_type' => '1',
                    'weta_btn_button_show_02' => 'yes'
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_btn_page_link',
            [
                'label' => esc_html__('Select Button Page', 'weta-core'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_btn_link_type' => '2',
                    'weta_btn_button_show_02' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();


        // Style Control Start
        $this->start_controls_section(
			'_style_design_layout',
			[
				'label' => __( 'Design Layout', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'design_layout_margin',
            [
                'label' => __( 'Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'design_layout_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'design_layout_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .weta-el-section',
            ]
        );

        $this->end_controls_section();


        // Title & Content
        $this->start_controls_section(
            'weta_title_style',
            [
                'label' => __( 'Title & Content', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_subheading_before_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subtitle Before', 'weta-core' ),
            ]
        );

        $this->add_control(
            'subheading_before_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'subheading_before_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle span' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subheading_before_typography',
                'selector' => '{{WRAPPER}} .section__subtitle span, .section-3__subtitle span',
            ]
        );

        // Title
        $this->add_control(
            '_subheading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subtitle', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'subheading_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subheading_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-subtitle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subheading_typography',
                'selector' => '{{WRAPPER}} .weta-el-subtitle',
            ]
        );

        // Title
        $this->add_control(
            '_heading_title_02',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'title_spacing_02',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-title' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color_02',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_02_typography',
                'selector' => '{{WRAPPER}} .weta-el-title',
            ]
        );

        $this->end_controls_section();


        // Blog Query Style Control
		$this->start_controls_section(
			'weta_post_query_style',
			[
				'label' => __( 'Blog Query', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'post_content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .blog-item__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',                    
                    '{{WRAPPER}} .blog-3__item__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',                    
                    '{{WRAPPER}} .blog-2__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'post_content_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item__text' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .blog-3__item__text' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .blog-2__item' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'post_item_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .blog-item' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .blog-3__item' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .blog-2__item' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'post_item_border',
                'selector' => '{{WRAPPER}} .blog-item, .blog-3__item, .blog-2__item',
            ]
        );

		// Title
        $this->add_control(
            '_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .blog-el-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-el-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label' => __( 'Color (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-el-title:hover' => 'color: {{VALUE}}!important',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .blog-el-title',
            ]
        );

        // Category Meta
        $this->add_control(
            '_heading_author',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Author Meta', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'weta_design_style' => 'layout-2',
                ],
            ]
        );

        $this->start_controls_tabs( 'author_meta_tabs' );
        
        $this->start_controls_tab(
            'author_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'text-domain' ),
            ]
        );
        
        $this->add_control(
            'author_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-3__item__meta a span' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => 'layout-2',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'author_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'text-domain' ),
            ]
        );
        
        $this->add_control(
            'author_color_hover',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-3__item__meta a:hover span' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => 'layout-2',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'author_typography',
                'selector' => '{{WRAPPER}} .blog-3__item__meta a span',
                'condition' => [
                    'weta_design_style' => 'layout-2',
                ],
            ]
        );

        // Category Meta
        $this->add_control(
            '_heading_category',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Category Meta', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'category_content_padding',
            [
                'label' => __( 'Category Text Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .blog-item__meta .meta-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',                    
                    '{{WRAPPER}} .weta-blog-el-category' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'category_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item__meta .meta-btn' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .weta-blog-el-category' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );        

        $this->add_control(
            'category_bg_color',
            [
                'label' => __( 'Background Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item__meta .meta-btn' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .weta-blog-el-category' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'category_typography',
                'selector' => '
                {{WRAPPER}} .blog-item__meta .meta-btn, .weta-blog-el-category',
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'category_border',
                'selector' => '{{WRAPPER}} .blog-item__meta .meta-btn, .weta-blog-el-category',
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'category_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .blog-item__meta .meta-btn' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .weta-blog-el-category' => 'border-radius: {{SIZE}}px;',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );


		// Meta
        $this->add_control(
            '_heading_meta',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Date Meta', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .meta-date' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .blog-2__item_tag-date .date' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'selector' => '{{WRAPPER}} .meta-date, .blog-2__item_tag-date .date',
            ]
        );

		$this->end_controls_section();


        // Button 
        $this->start_controls_section(
            '_style_button',
            [
                'label' => __( 'Button', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'tabs_button_style' );

        $this->start_controls_tab(
            'button_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item .readmore' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .blog-3__item .readmore' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn__theme-1 .btn-wrap .text-one' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_color_icon',
            [
                'label'     => esc_html__( 'Icon Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item .readmore svg path[stroke="#0A2540"]' => 'stroke: {{VALUE}};',
                    '{{WRAPPER}} .blog-3__item .readmore svg path[stroke="#0A2540"]' => 'stroke: {{VALUE}};',
                    '{{WRAPPER}} .rr-btn__theme-1 .btn-wrap .text-one svg path[stroke="#0A2540"]' => 'stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item .readmore' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .blog-3__item .readmore' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn__theme-1:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .blog-item .readmore, .blog-3__item .readmore, .rr-btn',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .blog-item .readmore, .blog-3__item .readmore, .rr-btn',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );

        $this->add_control(
            'button_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item .readmore:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .blog-3__item .readmore:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .rr-btn__theme-1 .btn-wrap .text-two' => 'color: {{VALUE}};',
                ],
            ]
        );        

        $this->add_control(
            'button_color_icon_hover',
            [
                'label'     => esc_html__( 'Icon Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item .readmore:hover svg path[stroke="#0A2540"]' => 'stroke: {{VALUE}};',
                    '{{WRAPPER}} .blog-3__item .readmore:hover svg path[stroke="#0A2540"]' => 'stroke: {{VALUE}};',
                    '{{WRAPPER}} .rr-btn:hover svg path[stroke="#0A2540"]' => 'stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blog-item .readmore:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .blog-3__item .readmore:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border_hover',
                'selector' => '{{WRAPPER}} .blog-item .readmore:hover, .blog-3__item .readmore:hover, .rr-btn:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .blog-item .readmore' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .blog-3__item .readmore' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .rr-btn' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .blog-item .readmore, .blog-3__item .readmore, .rr-btn',
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .blog-item .readmore' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .blog-3__item .readmore' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .rr-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_margin',
            [
                'label'      => esc_html__( 'Margin', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .blog-item .readmore' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .blog-3__item .readmore' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .rr-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

	}


	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } else if (get_query_var('page')) {
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }

        // include_categories
        $category_list = '';
        if (!empty($settings['category'])) {
            $category_list = implode(", ", $settings['category']);
        }
        $category_list_value = explode(" ", $category_list);

        // exclude_categories
        $exclude_categories = '';
        if(!empty($settings['exclude_category'])){
            $exclude_categories = implode(", ", $settings['exclude_category']);
        }
        $exclude_category_list_value = explode(" ", $exclude_categories);

        $post__not_in = '';
        if (!empty($settings['post__not_in'])) {
            $post__not_in = $settings['post__not_in'];
            $args['post__not_in'] = $post__not_in;
        }
        $posts_per_page = (!empty($settings['posts_per_page'])) ? $settings['posts_per_page'] : '-1';
        $orderby = (!empty($settings['orderby'])) ? $settings['orderby'] : 'post_date';
        $order = (!empty($settings['order'])) ? $settings['order'] : 'desc';
        $offset_value = (!empty($settings['offset'])) ? $settings['offset'] : '0';
        $ignore_sticky_posts = (! empty( $settings['ignore_sticky_posts'] ) && 'yes' == $settings['ignore_sticky_posts']) ? true : false ;


        // number
        $off = (!empty($offset_value)) ? $offset_value : 0;
        $offset = $off + (($paged - 1) * $posts_per_page);
        $p_ids = array();

        // build up the array
        if (!empty($settings['post__not_in'])) {
            foreach ($settings['post__not_in'] as $p_idsn) {
                $p_ids[] = $p_idsn;
            }
        }

        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => $orderby,
            'order' => $order,
            'offset' => $offset,
            'paged' => $paged,
            'post__not_in' => $p_ids,
            'ignore_sticky_posts' => $ignore_sticky_posts
        );

        // exclude_categories
        if ( !empty($settings['exclude_category'])) {

            // Exclude the correct cats from tax_query
            $args['tax_query'] = array(
                array(
                    'taxonomy'	=> 'category',
                    'field'	 	=> 'slug',
                    'terms'		=> $exclude_category_list_value,
                    'operator'	=> 'NOT IN'
                )
            );

            // Include the correct cats in tax_query
            if ( !empty($settings['category'])) {
                $args['tax_query']['relation'] = 'AND';
                $args['tax_query'][] = array(
                    'taxonomy'	=> 'category',
                    'field'		=> 'slug',
                    'terms'		=> $category_list_value,
                    'operator'	=> 'IN'
                );
            }

        } else {
            // Include the cats from $cat_slugs in tax_query
            if (!empty($settings['category'])) {
                $args['tax_query'][] = [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $category_list_value,
                ];
            }
        }

        $filter_list = $settings['category'];

        // The Query
        $query = new \WP_Query($args);

        ?>

        <?php if ( $settings['weta_design_style']  == 'layout-1' ): 
            
            $this->add_render_attribute( 'title_args', 'class', 'weta-el-title section__title text-uppercase wow fadeIn animated' );
            $this->add_render_attribute( 'title_args', 'data-wow-delay', '.3s' );
            
            ?>

            <section class="weta-el-section weta-blog-style-01 blog section-space overflow-hidden parallax-element">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="section__title-wrapper text-center mb-60 mb-sm-50 mb-xs-40">
                                <?php if ( !empty ( $settings['weta_subtitle'] ) ) : ?>
                                    <span class="weta-el-subtitle section__subtitle justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                        <span class="layer" data-depth="0.009"><?php echo $settings['weta_subtitle_before']; ?></span> <?php echo $settings['weta_subtitle']; ?>
                                        <img class="rightLeft" src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/arrow-right.svg" alt="arrow not found">
                                    </span>
                                <?php endif; ?>
                                <?php
                                    if ( !empty($settings['title' ]) ) :
                                        printf( '<%1$s %2$s>%3$s</%1$s>',
                                            tag_escape( $settings['title_tag'] ),
                                            $this->get_render_attribute_string( 'title_args' ),
                                            rrdevs_kses( $settings['title' ] )
                                            );
                                    endif;
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-minus-30">
                        <?php if ($query->have_posts()) : ?>
                            <?php while ($query->have_posts()) :
                            $query->the_post();
                            global $post;
                            $categories = get_the_category($post->ID);
                            $weta_post_content_limit = $settings['weta_post_content_limit'];
                        ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="blog-item mb-30 wow fadeIn animated" data-wow-delay=".5s">
                                    <a class="blog-item__media overflow-hidden position-relative" href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( $post->ID );?>
                                        <div class="panel wow"></div>
                                    </a>

                                    <div class="blog-item__text">
                                        <div class="blog-item__meta d-flex flex-row mb-15">
                                            <?php  if ( ! empty( $categories ) ) {
                                                echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '" class="meta-btn">' . esc_html( $categories[0]->name ) . '</a>';
                                                }
                                            ?>
                                            <div class="meta-date">
                                                <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5 1V3.40009" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M11.4023 1V3.40009" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M1.39844 6.67188H14.9989" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M15.4006 6.19937V12.9996C15.4006 15.3997 14.2006 16.9998 11.4005 16.9998H5.00027C2.20017 16.9998 1.00012 15.3997 1.00012 12.9996V6.19937C1.00012 3.79928 2.20017 2.19922 5.00027 2.19922H11.4005C14.2006 2.19922 15.4006 3.79928 15.4006 6.19937Z" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M11.1534 10.36H11.1606" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M11.1534 12.7604H11.1606" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8.1964 10.36H8.20359" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8.1964 12.7604H8.20359" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M5.23546 10.36H5.24265" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M5.23546 12.7604H5.24265" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <?php echo get_the_date("M d, Y"); ?>
                                            </div>
                                        </div>
                                        <a href="<?php the_permalink(); ?>" class="mb-20 d-block mb-xs-15">
                                            <h3 class="blog-el-title"><?php echo wp_trim_words(get_the_title(), $settings['weta_blog_title_word'], ''); ?></h3>
                                        </a>

                                        <a href="<?php the_permalink(); ?>" class="readmore">
                                            <?php print esc_html($settings['weta_btn_text']);?>
                                            <svg width="7" height="11" viewBox="0 0 7 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 1L5.5 5.5L1 10" stroke="#0A2540" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; wp_reset_query(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ) :
            $this->add_render_attribute( 'title_args', 'class', 'weta-el-title section-3__title lg wow fadeIn animated' );
            $this->add_render_attribute( 'title_args', 'data-wow-delay', '.3s' );
            ?>

            <section class="weta-el-section weta-blog-style-02 blog-3 section-space overflow-hidden parallax-element">
                <div class="container">
                    <div data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><div class="zooming">
                        <div class="blog-3__shape"></div>
                    </div></div>
                    <div data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><div class="zooming">
                        <div class="blog-3__shape-1"></div>
                    </div></div>

                    <div class="row">
                        <div class="col-12">
                            <div class="section-3__title-wrapper text-center mb-60 mb-sm-50 mb-xs-40">
                                <?php if ( !empty ( $settings['weta_subtitle'] ) ) : ?>
                                    <span class="weta-el-subtitle section-3__subtitle justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                        <span class="layer" data-depth="0.009"><?php echo $settings['weta_subtitle_before']; ?></span> <?php echo $settings['weta_subtitle']; ?>
                                        <img class="rightLeft" src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/arrow-right.svg" alt="arrow not found">
                                    </span>
                                <?php endif; ?>
                                <?php
                                    if ( !empty($settings['title' ]) ) :
                                        printf( '<%1$s %2$s>%3$s</%1$s>',
                                            tag_escape( $settings['title_tag'] ),
                                            $this->get_render_attribute_string( 'title_args' ),
                                            rrdevs_kses( $settings['title' ] )
                                            );
                                    endif;
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-minus-30">
                        <?php if ($query->have_posts()) : ?>
                            <?php while ($query->have_posts()) :
                            $query->the_post();
                            global $post;
                            $categories = get_the_category($post->ID);
                            $weta_post_content_limit = $settings['weta_post_content_limit'];
                        ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="blog-3__item mb-30 wow fadeIn animated" data-wow-delay=".5s">
                                    <a class="blog-3__item__media overflow-hidden position-relative" href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( $post->ID );?>
                                        <div class="panel wow"></div>
                                    </a>

                                    <div class="blog-3__item__text">
                                        <div class="blog-3__item__meta d-flex flex-row mb-15">
                                            <div class="meta-date">
                                                <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5 1V3.40009" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M11.4023 1V3.40009" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M1.39844 6.67188H14.9989" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M15.4006 6.19937V12.9996C15.4006 15.3997 14.2006 16.9998 11.4005 16.9998H5.00027C2.20017 16.9998 1.00012 15.3997 1.00012 12.9996V6.19937C1.00012 3.79928 2.20017 2.19922 5.00027 2.19922H11.4005C14.2006 2.19922 15.4006 3.79928 15.4006 6.19937Z" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M11.1534 10.36H11.1606" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M11.1534 12.7604H11.1606" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8.1964 10.36H8.20359" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8.1964 12.7604H8.20359" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M5.23546 10.36H5.24265" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M5.23546 12.7604H5.24265" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <?php echo get_the_date("M d, Y"); ?>
                                            </div>
                                            <a href="<?php echo get_the_author(); ?>"><svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8.28125 9C10.4904 9 12.2812 7.20914 12.2812 5C12.2812 2.79086 10.4904 1 8.28125 1C6.07211 1 4.28125 2.79086 4.28125 5C4.28125 7.20914 6.07211 9 8.28125 9Z" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M15.1542 17.0004C15.1542 13.9044 12.0742 11.4004 8.28215 11.4004C4.49015 11.4004 1.41016 13.9044 1.41016 17.0004" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                                <span>
                                                <?php print esc_html( 'By', 'weta-core' ); ?> <?php echo get_the_author(); ?>
                                            </span>
                                            </a>
                                        </div>
                                        <a href="<?php the_permalink(); ?>" class="mb-20 d-block mb-xs-15">
                                            <h4 class="blog-el-title"><?php echo wp_trim_words(get_the_title(), $settings['weta_blog_title_word'], ''); ?></h4>
                                        </a>

                                        <?php if (!empty($settings['weta_btn_button_show'])): ?>
                                            <a href="<?php the_permalink(); ?>" class="readmore">
                                                <?php print esc_html($settings['weta_btn_text']);?>
                                                <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M1 7H15" stroke="#010915" stroke-opacity="0.24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M9 1L15 7L9 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; wp_reset_query(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        <?php elseif ( $settings['weta_design_style']  == 'layout-3' ) : 
            $this->add_render_attribute('title_args', 'class', 'weta-el-title section-2__title xl text-uppercase section-title-2-animation');

            // Link
            if ('2' == $settings['weta_btn_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_btn_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-1 section-button-animation mb-md-50 mb-sm-45 mb-xs-40 weta-el-btn');
            } else {
                if ( ! empty( $settings['weta_btn_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_btn_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-1 section-button-animation mb-md-50 mb-sm-45 mb-xs-40 weta-el-btn');
                }
            }
        ?>

        <!--blog-2 start-->
        <section class="weta-el-section blog-2 post-content section-space overflow-hidden parallax-element white-bg z-1 position-relative">
            <div class="container container-xxl">
                <div class="blog-2__shape">
                    <div class="zooming"><div class="layer" data-depth="0.02"><div class="blog-2__shape-1"></div></div></div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-4">
                        <div class="blog-2__content">
                            <div class="section-2__title-wrapper mb-45 mb-md-10 mb-sm-10 mb-xs-10">
                                <?php if(!empty($settings['weta_subtitle'])): ?>
                                <span class="section-2__subtitle justify-content-start mb-15 mb-xs-5 section-subTile-2-animation weta-el-subtitle">
                                    <?php echo $settings['weta_subtitle']; ?>   
                                </span>
                                <?php endif; ?>

                                <?php
                                    if ( !empty($settings['title' ]) ) :
                                        printf( '<%1$s %2$s>%3$s</%1$s>',
                                            tag_escape( $settings['title_tag'] ),
                                            $this->get_render_attribute_string( 'title_args' ),
                                            rrdevs_kses( $settings['title' ] )
                                            );
                                    endif;
                                ?>
                            </div>

                            <?php if(!empty($settings['weta_btn_text_02'])) : ?>
                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                <span class="btn-wrap">
                                    <span class="text-one"><?php echo $settings['weta_btn_text_02']; ?>
                                    <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.38575 8.93578H4.03817L4.27661 9.18868L8.61139 13.7864C8.61139 13.7864 8.6114 13.7864 8.61141 13.7864C8.69179 13.8717 8.75598 13.9735 8.79993 14.086C8.84388 14.1986 8.8666 14.3196 8.8666 14.442C8.8666 14.5643 8.84388 14.6853 8.79993 14.7979C8.75598 14.9105 8.69177 15.0122 8.61139 15.0975C8.53102 15.1828 8.43609 15.2499 8.33227 15.2956C8.22848 15.3412 8.11758 15.3645 8.00582 15.3645C7.89406 15.3645 7.78316 15.3412 7.67937 15.2956C7.57556 15.2499 7.48063 15.1828 7.40026 15.0975L1.34324 8.67011L1.34311 8.66997C1.26262 8.58476 1.19831 8.48307 1.15429 8.37049C1.11026 8.2579 1.0875 8.13692 1.0875 8.01455C1.0875 7.89217 1.11026 7.77119 1.15429 7.6586C1.19831 7.54603 1.26262 7.44433 1.34311 7.35912L1.34324 7.35898L7.40026 0.931574C7.56247 0.759446 7.7805 0.664545 8.00582 0.664545C8.23115 0.664545 8.44918 0.759446 8.61139 0.931574C8.77388 1.104 8.8666 1.33967 8.8666 1.58713C8.8666 1.83459 8.77389 2.07024 8.61141 2.24267C8.6114 2.24268 8.61139 2.24269 8.61139 2.2427L4.27661 6.84041L4.03817 7.09331H4.38575L16.0818 7.09331C16.3068 7.09331 16.5245 7.18807 16.6865 7.35994C16.8488 7.53212 16.9413 7.76744 16.9413 8.01455C16.9413 8.26165 16.8488 8.49698 16.6865 8.66915C16.5245 8.84102 16.3068 8.93578 16.0818 8.93578H4.38575Z" fill="#010915" stroke="#F3B351" stroke-width="0.3"/>
                                    </svg></span>

                                    <span class="text-two"><?php echo $settings['weta_btn_text_02']; ?> <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.38575 8.93578H4.03817L4.27661 9.18868L8.61139 13.7864C8.61139 13.7864 8.6114 13.7864 8.61141 13.7864C8.69179 13.8717 8.75598 13.9735 8.79993 14.086C8.84388 14.1986 8.8666 14.3196 8.8666 14.442C8.8666 14.5643 8.84388 14.6853 8.79993 14.7979C8.75598 14.9105 8.69177 15.0122 8.61139 15.0975C8.53102 15.1828 8.43609 15.2499 8.33227 15.2956C8.22848 15.3412 8.11758 15.3645 8.00582 15.3645C7.89406 15.3645 7.78316 15.3412 7.67937 15.2956C7.57556 15.2499 7.48063 15.1828 7.40026 15.0975L1.34324 8.67011L1.34311 8.66997C1.26262 8.58476 1.19831 8.48307 1.15429 8.37049C1.11026 8.2579 1.0875 8.13692 1.0875 8.01455C1.0875 7.89217 1.11026 7.77119 1.15429 7.6586C1.19831 7.54603 1.26262 7.44433 1.34311 7.35912L1.34324 7.35898L7.40026 0.931574C7.56247 0.759446 7.7805 0.664545 8.00582 0.664545C8.23115 0.664545 8.44918 0.759446 8.61139 0.931574C8.77388 1.104 8.8666 1.33967 8.8666 1.58713C8.8666 1.83459 8.77389 2.07024 8.61141 2.24267C8.6114 2.24268 8.61139 2.24269 8.61139 2.2427L4.27661 6.84041L4.03817 7.09331H4.38575L16.0818 7.09331C16.3068 7.09331 16.5245 7.18807 16.6865 7.35994C16.8488 7.53212 16.9413 7.76744 16.9413 8.01455C16.9413 8.26165 16.8488 8.49698 16.6865 8.66915C16.5245 8.84102 16.3068 8.93578 16.0818 8.93578H4.38575Z" fill="#010915" stroke="#F3B351" stroke-width="0.3"/>
                                    </svg></span>
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="swiper blog-2__active">
                            <div class="swiper-wrapper">
                                <?php if ($query->have_posts()) : ?>
                                    <?php while ($query->have_posts()) :
                                    $query->the_post();
                                    global $post;
                                    $categories = get_the_category($post->ID);
                                    $weta_post_content_limit = $settings['weta_post_content_limit'];
                                ?>
                                <div class="swiper-slide">
                                    <div class="blog-2__item">

                                        <a href="<?php the_permalink(); ?>" class="blog-2__item-thumbnail d-block mb-20 mb-sm-15 mb-xs-10">
                                            <?php the_post_thumbnail( $post->ID );?>
                                        </a>

                                        <div class="blog-2__item_tag-date mb-20 mb-sm-25 mb-xs-20 d-flex align-items-center">
                                            <?php  if ( ! empty( $categories ) ) {
                                                echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '" class="tags section-2__subtitle weta-blog-el-category justify-content-start">' . esc_html( $categories[0]->name ) . '</a>';
                                                }
                                            ?>
                                            <span class="date"><?php echo get_the_date("M.d.Y"); ?></span>
                                        </div>

                                        <h4 class="blog-el-title">
                                            <a href="<?php the_permalink(); ?>"><?php echo wp_trim_words(get_the_title(), $settings['weta_blog_title_word'], ''); ?></a>
                                        </h4>
                                    </div>
                                </div>
                                <?php endwhile; wp_reset_query(); ?>
                                <?php endif; ?>
                            </div>
                            <div class="blog-2__dot"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--blog-2 end-->

        <?php endif; ?>
       <?php
	}

}

$widgets_manager->register( new WETA_Post_List() );