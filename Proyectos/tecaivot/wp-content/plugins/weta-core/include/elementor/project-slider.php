<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;
use \Elementor\Repeater;
use \Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Project_Slider extends Widget_Base {

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
		return 'weta_project_slider';
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
		return __( 'Project Slider', 'weta-core' );
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
		
        $this->start_controls_section(
            '_content_design_layout',
            [
                'label' => esc_html__('Design Layout', 'weta-core'),
            ]
        );

        $this->add_control(
            'design_style',
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

        $this->add_control(
            'secondary_subheading',
            [
                'label' => esc_html__( 'Secondary Subheading', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( '#6', 'weta-core' ),
                'label_block' => true,
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'subheading',
            [
                'label' => esc_html__('Subheading', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => __('CASE STUDIES', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'subheading_shape_switch',
            [
                'label' => esc_html__( 'Subheading Shape ON/OFF', 'text-domain' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'text-domain' ),
                'label_off' => esc_html__( 'Hide', 'text-domain' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
			'heading',
			[
				'label' => esc_html__( 'Title', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
				'type' => Controls_Manager::TEXT,
                'default' => __( 'Shape Your Tomorrow with a Strategic Approach.', 'weta-core' ),
                'label_block' => true,
			]
		);

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_project',
            [
                'label' => esc_html__('Project Slider', 'weta-core'),
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'image',
            [
                'type' => Controls_Manager::MEDIA,
                'label' => __( 'Image', 'weta-core' ),
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'link_type',
            [
                'label' => esc_html__( 'Service Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label' => esc_html__( 'Service Link link', 'weta-core' ),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__( 'https://your-link.com', 'weta-core' ),
                'show_external' => true,
                'default' => [
                    'url' => '#',
                    'is_external' => false,
                    'nofollow' => false,
                ],
                'condition' => [
                    'link_type' => '1',
                ]
            ]
        );

        $repeater->add_control(
            'page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'link_type' => '2',
                ]
            ]
        );

        // REPEATER
        $this->add_control(
            'project_list',
            [
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_project_two',
            [
                'label' => esc_html__('Project Slider', 'weta-core'),
                'condition' => [
                    'design_style' => 'layout-2',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'image',
            [
                'type' => Controls_Manager::MEDIA,
                'label' => __( 'Image', 'weta-core' ),
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        // REPEATER
        $this->add_control(
            'project_list_two',
            [
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_project_three',
            [
                'label' => esc_html__('Project Slider', 'weta-core'),
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'image',
            [
                'type' => Controls_Manager::MEDIA,
                'label' => __( 'Image', 'weta-core' ),
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );                      

        $repeater->add_control(
            'title',
            [
                'label' => __( 'Title', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => __( 'Description', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'show_label' => true,
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'link_switcher',
            [
                'label' => esc_html__( 'Show Link', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'link_type',
            [
                'label' => esc_html__( 'Service Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'condition' => [
                    'link_switcher' => 'yes'
                ]
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label' => esc_html__( 'Service Link link', 'weta-core' ),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__( 'https://your-link.com', 'weta-core' ),
                'show_external' => true,
                'default' => [
                    'url' => '#',
                    'is_external' => false,
                    'nofollow' => false,
                ],
                'condition' => [
                    'link_type' => '1',
                    'link_switcher' => 'yes',
                ]
            ]
        );

        $repeater->add_control(
            'page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'link_type' => '2',
                    'link_switcher' => 'yes',
                ]
            ]
        );

        // REPEATER
        $this->add_control(
            'project_list_three',
            [
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '<# print(title || "Carousel Item"); #>',
                'default' => [
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Seo Marketing', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Digital Marketing', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Software Dev', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Seo Marketing', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Digital Marketing', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Software Dev', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Seo Marketing', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Digital Marketing', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'title' => __( 'Software Dev', 'weta-core' ),
                        'description' => __( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada', 'weta-core' ),
                    ],
                ]
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
			'_style_design_layout',
			[
				'label' => __( 'Design Layout', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'design_layout_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'design_layout_margin',
            [
                'label' => __( 'Content Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'design_layout_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            '_heading_arrow_dots',
            [
                'label' => esc_html__( 'Arrow/Dots', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( '_tabs_arrow' );
        
        $this->start_controls_tab(
            'arrow_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'arrow_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .case-studies__scrollbar' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .cool-amazing__slider-dot .swiper-pagination-bullet:after' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .recent-works__scrollbar .swiper-scrollbar-drag' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'arrow_active_tab',
            [
                'label' => esc_html__( 'Active', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'arrow_background_hover',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .case-studies__scrollbar .swiper-scrollbar-drag' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .cool-amazing__slider-dot .swiper-pagination-bullet-active:after' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .recent-works__scrollbar' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_border_hover',
            [
                'label' => esc_html__( 'Border', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cool-amazing__slider-dot .swiper-pagination-bullet-active' => 'border-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-2',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_control(
            '_heading_style_secondary_subheading',
            [
                'label' => esc_html__( 'Secondary Subheading', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'secondary_subheading_color',
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
            'secondary_subheading_background',
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
                'name' => 'secondary_subheading_typography',
                'selector' => '{{WRAPPER}} .section__subtitle span, .section-3__subtitle span',
            ]
        );

        $this->add_control(
            '_heading_style_subheading',
            [
                'label' => esc_html__( 'Subheading', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
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
                    '{{WRAPPER}} .section__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .section-2__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .section-3__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'subheading_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-2__subtitle' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'subheading_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-2__subtitle' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-2',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subheading_typography',
                'selector' => '{{WRAPPER}} .section__subtitle, .section-2__subtitle, .section-3__subtitle',
            ]
        );

        $this->add_control(
            '_heading_style_title',
            [
                'label' => esc_html__( 'Heading', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'heading_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .section-2__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .section-3__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'heading_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-2__title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .section__title, .section-2__title, .section-3__title',
            ]
        );

		$this->end_controls_section();

        $this->start_controls_section(
			'_style_project_slider',
			[
				'label' => esc_html__( 'Project Slider', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            '_heading_style_button',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Button', 'weta-core' ),
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );

        $this->start_controls_tabs( 'button_tabs' );
        
        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );
        
        $this->add_control(
            'button_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .case-studies__item a svg path' => 'stroke: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );
        
        $this->add_control(
            'button_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .case-studies__item a' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            '_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );
        
        $this->add_control(
            'button_color_hover',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .case-studies__item a:hover svg path' => 'stroke: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );
        
        $this->add_control(
            'button_background_hover',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .case-studies__item a:hover' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_control(
            '_heading_style_inner_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Title', 'weta-core' ),
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .recent-works__item-content a' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

		$this->start_controls_tabs( 'title_tabs' );
        
        $this->start_controls_tab(
            'title_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'text-domain' ),
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .recent-works__item-content a' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'title_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'text-domain' ),
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );
        
        $this->add_control(
            'title_color_hover',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .recent-works__item-content a:hover' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .recent-works__item-content a',
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

        $this->add_control(
            '_heading_style_inner_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Description', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'description_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .recent-works__item-content span' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

		$this->add_control(
            'description_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .recent-works__item-content span' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .recent-works__item-content span',
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

        $this->add_control(
            '_heading_style_inner_layout',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Layout', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'project_slider_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .case-studies__item:after' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .recent-works__item-content' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
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
        $this->add_render_attribute('title_args', 'class', 'section-title__title');
        

		?>

            <?php if ( $settings['design_style']  == 'layout-1' ): ?>

                <section class="case-studies weta-el-section section-space overflow-hidden">
                    <div class="container container-xxl">
                        <div class="row">
                            <div class="col-12">
                                <div class="section-2__title-wrapper case-studies__content mb-40 mb-sm-30 mb-xs-20">
                                    <?php if ( !empty ( $settings['subheading'] ) ): ?>
                                        <span class="section-2__subtitle justify-content-start mb-15 mb-xs-5 section-subTile-2-animation">
                                            <?php print rrdevs_kses( $settings['subheading'] ); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( !empty ( $settings['heading'] ) ): ?>
                                        <h2 class="section-2__title xl text-uppercase section-title-2-animation">
                                            <?php print rrdevs_kses( $settings['heading'] ); ?>
                                        </h2>
                                    <?php endif; ?>
                                </div>

                                <div class="swiper case-studies__active">
                                    <div class="swiper-wrapper">
                                        <?php foreach ( $settings['project_list'] as $item ) :

                                            if ( !empty($item['image']['url']) ) {
                                                $project_image = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                                                $project_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                                            }

                                            // Link
                                            if ('2' == $item['link_type']) {
                                                $link = get_permalink($item['page_link']);
                                                $target = '_self';
                                                $rel = 'nofollow';
                                            } else {
                                                $link = $item['link']['url'];
                                                $target = $item['link']['is_external'];
                                                $rel = $item['link']['nofollow'];
                                            }

                                        ?>
                                        <div class="swiper-slide">
                                            <div class="case-studies__item case-studies-image-animation">
                                                <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>">
                                                    <svg width="37" height="38" viewBox="0 0 37 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M9.19287 28.1929L27.5777 9.80809" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M9.19287 9.80713L27.5776 9.80713L27.5776 28.1919" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                                <div class="case-studies__thumb">
                                                    <img src="<?php print esc_url($project_image); ?>" alt="<?php print esc_url($project_image_alt); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="case-studies__scrollbar"></div>
                            </div>
                        </div>
                    </div>
                </section>

            <?php elseif ( $settings['design_style']  == 'layout-2' ): ?>

                <section class="cool-amazing weta-el-section section-space overflow-hidden parallax-element">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="section__title-wrapper text-center mb-60 mb-sm-50 mb-xs-40">
                                    <?php if ( !empty ( $settings['subheading'] ) ): ?>
                                        <span class="section__subtitle justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                            <span class="layer" data-depth="0.009"><?php print esc_html($settings['secondary_subheading']); ?></span> <?php print rrdevs_kses($settings['subheading']); ?>
                                            <?php if ( !empty ( $settings['subheading_shape_switch'] ) ) : ?>
                                                <img class="rightLeft" src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/arrow-right.svg" alt="<?php print esc_attr( 'arrow not found', 'weta-core' ); ?>">
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( !empty ( $settings['heading'] ) ): ?>
                                        <h2 class="section__title text-uppercase wow fadeIn animated" data-wow-delay=".3s">
                                            <?php print rrdevs_kses( $settings['heading'] ); ?>
                                        </h2>
                                    <?php endif; ?>
                                </div>

                                <div class="swiper cool-amazing__slider">
                                    <div class="swiper-wrapper">
                                        <?php foreach ( $settings['project_list_two'] as $item ) :

                                            if ( !empty($item['image']['url']) ) {
                                                $project_image = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                                                $project_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                                            }

                                        ?>
                                        <div class="swiper-slide">
                                            <div class="cool-amazing__item wow fadeIn animated" data-wow-delay=".5s">
                                                <div class="cool-amazing__item-media">
                                                    <img src="<?php print esc_url($project_image); ?>" alt="<?php print esc_url($project_image_alt); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="cool-amazing__slider-dot mt-60 mt-sm-45 mt-xs-40"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            <?php elseif ( $settings['design_style']  == 'layout-3' ): ?>

                <section class="recent-works weta-el-section overflow-hidden parallax-element position-relative z-1">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="section-3__title-wrapper mb-60 mb-sm-50 mb-xs-40">
                                    <?php if ( !empty ( $settings['subheading'] ) ): ?>
                                        <span class="section-3__subtitle justify-content-start mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                            <span class="layer" data-depth="0.009"><?php print esc_html($settings['secondary_subheading']); ?></span> <?php print rrdevs_kses($settings['subheading']); ?>
                                            <?php if ( !empty ( $settings['subheading_shape_switch'] ) ) : ?>
                                                <img class="rightLeft" src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/mad-emoji.svg" alt="<?php print esc_attr( 'shape', 'weta-core' ); ?>">
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( !empty ( $settings['heading'] ) ): ?>
                                        <h2 class="section-3__title lg wow fadeIn animated" data-wow-delay=".3s">
                                            <?php print rrdevs_kses( $settings['heading'] ); ?>
                                        </h2>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="swiper recent-works__active">
                        <div class="swiper-wrapper">
                            <?php foreach ( $settings['project_list_three'] as $item ) :

                                if ( !empty($item['image']['url']) ) {
                                    $project_image = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                                    $project_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                                }

                                // Link
                                if ('2' == $item['link_type']) {
                                    $link = get_permalink($item['page_link']);
                                    $target = '_self';
                                    $rel = 'nofollow';
                                } else {
                                    $link = $item['link']['url'];
                                    $target = $item['link']['is_external'];
                                    $rel = $item['link']['nofollow'];
                                }

                            ?>
                            <div class="swiper-slide">
                                <div class="recent-works__item wow fadeIn animated" data-wow-delay=".5s">
                                    <div class="recent-works__item-content">
                                        <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>">
                                            <?php print rrdevs_kses($item['title']); ?>
                                        </a>
                                        <?php if ( !empty ($item['description']) ) : ?>
                                            <span><?php print rrdevs_kses($item['description']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="recent-works__thumb">
                                        <img src="<?php print esc_url($project_image); ?>" alt="<?php print esc_url($project_image_alt); ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="recent-works__scrollbar mt-70 mt-lg-55 mt-md-50 mt-sm-40 mt-xs-30"></div>
                    </div>
                </section>

            <?php endif; ?>

		<?php
	}
}

$widgets_manager->register( new WETA_Project_Slider() );