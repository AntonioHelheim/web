<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Repeater;
use \Elementor\Utils;
use \Elementor\Control_Media;

use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Text_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Features extends Widget_Base {

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
		return 'weta_features';
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
		return __( 'Features', 'weta-core' );
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
                'label' => esc_html__( 'Design Layout', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
			'design_style',
			[
				'label' => esc_html__( 'Design Style', 'weta-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'layout-1',
				'options' => [
					'layout-1' => esc_html__( 'Layout 1', 'weta-core' ),
					'layout-2' => esc_html__( 'Layout 2', 'weta-core' ),
					'layout-3' => esc_html__( 'Layout 3', 'weta-core' ),
				],
			]
		);

        $this->add_control(
            'shape_switch',
            [
                'label' => esc_html__( 'Shape ON/OFF', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'subheading_before', [
                'label' => esc_html__( 'Subheading Before', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __( 'Services', 'weta-core' ),
            ]
        );

        $this->add_control(
            'subheading_icon',
            [
                'label' => esc_html__( 'Subheading Icon', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'subheading', [
                'label' => esc_html__( 'Subheading', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __( 'OUR FEATURES', 'weta-core' ),
            ]
        );

        $this->add_control(
            'title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __( 'Fixing a Repair flickering', 'weta-core' ),
            ]
        );

        $this->add_control(
            'description', [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'default' => __( 'Customer sion is crucial for amohlodi business as it leads to customr Customer is satisfaction is crucial for amohlodi business', 'weta-core' ),
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'image',
            [
                'label' => esc_html__( 'Choose Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'video_text',
            [
                'label' => esc_html__( 'Video Text', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'How it Works', 'weta-core' ),
                'label_block' => true,
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

        $this->add_control(
            'video_url',
            [
                'label' => esc_html__( 'Video URL', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'https://www.youtube.com/watch?v=3WrZMzqpFTc', 'weta-core' ),
                'label_block' => true,
                'condition' => [
                    'design_style' => 'layout-3',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_feature_list',
            [
                'label' => esc_html__('Features List', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'feature_icon_type',
            [
                'label' => esc_html__( 'Image Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'icon' => esc_html__( 'Icon', 'weta-core' ),
                    'image' => esc_html__( 'Image', 'weta-core' ),
                ],
            ]
        );
        
        $repeater->add_control(
            'feature_image_icon',
            [
                'label' => esc_html__( 'Upload Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'feature_icon_type' => 'image',
                ],
            ]
        );
        
        $repeater->add_control(
            'feature_icon',
            [
                'label' => esc_html__( 'Icon', 'weta-core' ),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'label_block' => true,
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'solid',
                ],
                'condition' => [
                    'feature_icon_type' => 'icon',
                ],
            ]
        );

        $repeater->add_control(
            'feature_title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );        

        $repeater->add_control(
            'feature_description', [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
            ]
        );
        
        $repeater->add_control(
            'feature_link_text',
            [
                'label' => esc_html__( 'Button Text', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );
        
        $repeater->add_control(
            'weta_link_type',
            [
                'label' => esc_html__( 'Button Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'label_block' => true,
            ]
        );
        
        $repeater->add_control(
            'weta_link',
            [
                'label' => esc_html__( 'Button link', 'weta-core' ),
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
                    'weta_link_type' => '1',
                ],
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'weta_page_link',
            [
                'label' => esc_html__( 'Select Button Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_link_type' => '2',
                ]
            ]
        );

        $this->add_control(
            'features_list',
            [
                'label' => esc_html__('Features - List', 'weta-core'),
                'description' => esc_html__( 'Not more than 4 item it will break the design', 'weta-core' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'feature_title' => esc_html__( 'User Analytics', 'weta-core' ),
                        'feature_description' => esc_html__( 'Suspends emend Suspnds herderite lectors tempus null clamorers quits.', 'weta-core' ),
                        'feature_link_text' => esc_html__( 'More Details', 'weta-core' ),
                    ],
                    [
                        'feature_title' => esc_html__( 'Creative Design', 'weta-core' ),
                        'feature_description' => esc_html__( 'Suspends emend Suspnds herderite lectors tempus null clamorers quits.', 'weta-core' ),
                        'feature_link_text' => esc_html__( 'More Details', 'weta-core' ),
                    ],
                    [
                        'feature_title' => esc_html__( 'Smart Coding', 'weta-core' ),
                        'feature_description' => esc_html__( 'Suspends emend Suspnds herderite lectors tempus null clamorers quits.', 'weta-core' ),
                        'feature_link_text' => esc_html__( 'More Details', 'weta-core' ),
                    ],
                    [
                        'feature_title' => esc_html__( 'Online Support', 'weta-core' ),
                        'feature_description' => esc_html__( 'Suspends emend Suspnds herderite lectors tempus null clamorers quits.', 'weta-core' ),
                        'feature_link_text' => esc_html__( 'More Details', 'weta-core' ),
                    ],
                ],
                'title_field' => '{{{ feature_title }}}',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_feature_list_two',
            [
                'label' => esc_html__('Features List', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'feature_title_two', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'features_list_two',
            [
                'label' => esc_html__('Features - List', 'weta-core'),
                'description' => esc_html__( 'Not more than 4 item it will break the design', 'weta-core' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'feature_title_two' => esc_html__( 'Keep Your All Data Protected', 'weta-core' ),
                    ],
                    [
                        'feature_title_two' => esc_html__( 'Multi Device Supported', 'weta-core' ),
                    ],
                    [
                        'feature_title_two' => esc_html__( 'Easily Data Managable', 'weta-core' ),
                    ],
                    [
                        'feature_title_two' => esc_html__( 'Excellent Team Operation', 'weta-core' ),
                    ],
                ],
                'title_field' => '{{{ feature_title_two }}}',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_button',
            [
                'label' => esc_html__( 'Button', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );
        
        $this->add_control(
            'weta_feature_button_text',
            [
                'label' => esc_html__( 'Button Text', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'All Features Show', 'weta-core' ),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'weta_feature_button_link_type',
            [
                'label' => esc_html__( 'Button Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'weta_feature_button_link',
            [
                'label' => esc_html__( 'Button link', 'weta-core' ),
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
                    'weta_feature_button_link_type' => '1',
                ],
                'label_block' => true,
            ]
        );
        $this->add_control(
            'weta_feature_button_page_link',
            [
                'label' => esc_html__( 'Select Button Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_feature_button_link_type' => '2',
                ]
            ]
        );
        
        $this->end_controls_section();

        $this->start_controls_section(
            '_style_design_layout',
            [
                'label' => esc_html__( 'Design Layout', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'design_layout_margin',
            [
                'label' => esc_html__( 'Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .our-features-1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .customers-solutions__wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'design_layout_padding',
            [
                'label' => esc_html__( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .our-features-1' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .customers-solutions__wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'design_layout_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .our-features-1' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .customers-solutions__wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'design_layout_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .our-features-1, .customers-solutions__wrapper',
            ]
        );

        $this->add_control(
            '_heading_style_section_subheading_before',
            [
                'label' => esc_html__( 'Subheading Before', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'section_subheading_before_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section-3__subtitle span' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'section_subheading_before_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle span' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'section_subheading_before_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
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
                'name' => 'section_subheading_before_typography',
                'selector' => '{{WRAPPER}} .section-3__subtitle span',
            ]
        );

        $this->add_control(
            '_heading_style_section_subheading',
            [
                'label' => esc_html__( 'Subheading', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'section_subheading_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-subheading' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'section_subheading_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-subheading' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_subheading_typography',
                'selector' => '{{WRAPPER}} .weta-el-section-subheading',
            ]
        );

        $this->add_control(
            '_heading_style_section_title',
            [
                'label' => esc_html__( 'Title', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'section_title_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'section_title_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-title' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'selector' => '{{WRAPPER}} .weta-el-section-title',
            ]
        );

        $this->add_control(
            '_heading_style_section_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Description', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );
        
        $this->add_responsive_control(
            'section_description_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-description' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );
        
        $this->add_control(
            'section_description_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-description' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_description_typography',
                'selector' => '{{WRAPPER}} .weta-el-section-description',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_style_features',
            [
                'label' => esc_html__( 'Features List', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_feature_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Title', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'feature_title_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .our-features__item-header h4' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .our-features-1__list li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .customers-solutions__content ul li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'feature_title_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item-header h4' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .our-features-1__list li' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .customers-solutions__content ul li' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'feature_title_typography',
                'selector' => '{{WRAPPER}} .our-features__item-header h4, .our-features-1__list li, .customers-solutions__content ul li',
            ]
        );

        $this->add_control(
            '_heading_feature_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Icon', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'feature_icon_size',
            [
                'label' => esc_html__( 'Font Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .our-features__item-header .icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_control(
            'feature_icon_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item-header .icon i' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_control(
            '_style_feature_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Description', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'feature_description_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .our-features__item p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'feature_description_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item p' => 'color: {{VALUE}} !important',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'feature_description_typography',
                'selector' => '{{WRAPPER}} .our-features__item p',
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            '_style_button',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Button', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->start_controls_tabs( 'feature_button_tabs' );
        
        $this->start_controls_tab(
            'feature_button_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'feature_button_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item .readmore' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .our-features__item .readmore svg path' => 'stroke: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            '_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'feature_button_color_hover',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item .readmore:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .our-features__item .readmore:hover svg path' => 'stroke: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'feature_button_typography',
                'selector' => '{{WRAPPER}} .our-features__item .readmore',
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            '_heading_style_feature_list_box',
            [
                'label' => esc_html__( 'Box Layout', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( 'feature_list_tabs' );
        
        $this->start_controls_tab(
            'feature_list_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'feature_list_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'feature_list_border_color',
            [
                'label' => esc_html__( 'Border Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'feature_list_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'feature_list_background_hover',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'feature_list_border_color_hover',
            [
                'label' => esc_html__( 'Border Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features__item:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            '_style_weta_button',
            [
                'label' => esc_html__( 'Button', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );
        
        $this->start_controls_tabs( 'tabs_style__button' );
        
        $this->start_controls_tab(
            'weta_button_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'weta_button_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn__theme-4 .btn-wrap .text-one'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn__theme-4 .btn-wrap .text-two'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn__theme .btn-wrap .text-one'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn__theme .btn-wrap .text-two'    => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'weta_button_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn__theme-4' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'weta_button_border',
                'selector' => '{{WRAPPER}} .rr-btn__theme-4, .rr-btn',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'weta_button_box_shadow',
                'selector' => '{{WRAPPER}} .rr-btn__theme-4, .rr-btn',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'weta_button_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'weta_button_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn__theme-4:hover .btn-wrap .text-one' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .rr-btn__theme-4:hover .btn-wrap .text-two' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .rr-btn:hover .btn-wrap .text-one' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .rr-btn:hover .btn-wrap .text-two' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'weta_button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn__theme-4:before' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'weta_button_border_hover',
                'selector' => '{{WRAPPER}} .rr-btn__theme-4:hover, .rr-btn:hover',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'weta_button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rr-btn__theme-4:hover, .rr-btn:hover',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_control(
            'weta_button_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn__theme-4' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .rr-btn__theme-4:before' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .rr-btn' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .rr-btn:before' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'weta_button_typography',
                'selector' => '{{WRAPPER}} .rr-btn__theme-4, .rr-btn',
            ]
        );
        
        $this->add_control(
            'weta_button_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .rr-btn__theme-4' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .rr-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'weta_button_margin',
            [
                'label'      => esc_html__( 'Margin', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .rr-btn__theme-4' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        if ( !empty($settings['image']['url']) ) {
            $weta_image = !empty($settings['image']['id']) ? wp_get_attachment_image_url( $settings['image']['id'], 'full') : $settings['image']['url'];
            $weta_image_alt = get_post_meta($settings["image"]["id"], "_wp_attachment_image_alt", true);
        }

        if ( !empty($settings['subheading_icon']['url']) ) {
            $subheading_icon = !empty($settings['subheading_icon']['id']) ? wp_get_attachment_image_url( $settings['subheading_icon']['id'], 'full') : $settings['subheading_icon']['url'];
            $subheading_icon_alt = get_post_meta($settings["subheading_icon"]["id"], "_wp_attachment_image_alt", true);
        }

        ?>

        <?php if ( $settings['design_style']  == 'layout-1' ): ?>

            <section class="our-features section-space__features overflow-hidden parallax-element">
                <div class="container">
                    <?php if ( !empty ( $settings['shape_switch'] ) ) : ?>
                        <div class="section-shape">
                            <div class="bar-shape" data-parallax='{"y": -200, "x": 300, "smoothness": 15}'>
                                <svg width="1110" height="1309" viewBox="0 0 1110 1309" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <rect opacity="0.1" x="925.229" y="472" width="205.367" height="977.546" rx="102.683" transform="rotate(45 925.229 472)" fill="#403CFA"/>
                                    </g>
                                    <g>
                                        <rect opacity="0.1" x="964.229" y="78" width="205.367" height="977.546" rx="102.683" transform="rotate(45 964.229 78)" fill="#403CFA"/>
                                    </g>
                                    <g data-parallax='{"y": -100, "x": 100, "smoothness": 15}'>
                                        <rect opacity="0.1" x="691.229" width="205.367" height="977.546" rx="102.683" transform="rotate(45 691.229 0)" fill="#403CFA"/>
                                    </g>
                                </svg>
                            </div>

                            <div class="small-ball-shape" data-parallax='{"y": -100, "x": -100, "smoothness": 15}'>
                                <svg width="1920" height="897" viewBox="0 0 1920 897" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g data-parallax='{"x": 200, "smoothness": 15}'>
                                        <g class="leftRight">
                                        <path d="M1769 713.884C1776.74 720.044 1784.12 719.225 1789.5 718.633C1794.33 718.101 1796.6 717.962 1799.67 720.402C1802.74 722.842 1803.11 725.064 1803.63 729.865C1804.22 735.207 1805.03 742.526 1812.77 748.687C1820.52 754.848 1827.89 754.029 1833.28 753.436C1838.11 752.905 1840.37 752.765 1843.45 755.205C1846.51 757.645 1846.88 759.867 1847.41 764.66C1847.99 770.002 1848.8 777.321 1856.55 783.482C1864.29 789.643 1871.67 788.824 1877.05 788.231C1881.88 787.7 1884.15 787.56 1887.22 790L1896 779.116C1888.26 772.956 1880.88 773.775 1875.5 774.367C1870.67 774.899 1868.4 775.038 1865.33 772.598C1862.26 770.158 1861.89 767.936 1861.37 763.144C1860.78 757.802 1859.97 750.482 1852.23 744.322C1844.48 738.161 1837.11 738.98 1831.72 739.572C1826.89 740.104 1824.63 740.243 1821.55 737.803C1818.48 735.364 1818.12 733.142 1817.59 728.34C1817.01 722.999 1816.2 715.679 1808.45 709.518C1800.71 703.357 1793.33 704.176 1787.95 704.769C1783.12 705.3 1780.85 705.44 1777.78 703L1769 713.884Z" fill="#FEEAA6" fill-opacity="0.4"/>
                                        </g>
                                    </g>
                                    <g class="rightLeft">
                                    <path d="M1778.35 702.444C1775.95 705.449 1776.4 709.99 1779.97 711.433C1785.4 713.628 1790.41 713.069 1794.34 712.633C1799.13 712.101 1801.38 711.962 1804.43 714.402C1807.47 716.842 1807.84 719.064 1808.36 723.865C1808.94 729.207 1809.74 736.526 1817.43 742.687C1825.11 748.848 1832.43 748.029 1837.77 747.436C1842.56 746.905 1844.81 746.765 1847.86 749.205C1850.9 751.645 1851.27 753.867 1851.79 758.66C1852.37 764.002 1853.17 771.321 1860.86 777.482C1868.54 783.643 1875.86 782.824 1881.2 782.231C1882.59 782.076 1883.77 781.955 1884.82 781.921C1888.67 781.794 1893.24 781.563 1895.65 778.556C1898.05 775.551 1897.6 771.01 1894.03 769.567C1888.6 767.372 1883.59 767.931 1879.66 768.367C1874.87 768.899 1872.62 769.038 1869.57 766.598C1866.53 764.158 1866.16 761.936 1865.64 757.144C1865.06 751.802 1864.26 744.482 1856.57 738.322C1848.89 732.161 1841.57 732.98 1836.23 733.572C1831.44 734.104 1829.19 734.243 1826.14 731.803C1823.09 729.364 1822.73 727.142 1822.21 722.34C1821.63 716.999 1820.83 709.679 1813.14 703.518C1805.46 697.357 1798.14 698.176 1792.8 698.769C1791.41 698.924 1790.23 699.045 1789.18 699.079C1785.33 699.206 1780.76 699.437 1778.35 702.444Z" fill="url(#paint0_linear_431_74)" fill-opacity="0.5"/>
                                    </g>
                                    <g data-parallax='{"x": 250, "smoothness": 15}'>
                                        <g class="leftRight">
                                        <circle cx="1364" cy="96" r="6" fill="#6640FF"/>
                                    </g>
                                    </g>

                                    <g data-parallax='{"x": 150, "smoothness": 15}'>
                                        <g class="rightLeft">
                                            <circle cx="1810" cy="207" r="8" fill="#6640FF"/>
                                        </g>
                                    </g>

                                    <g data-parallax='{"x": 200, "smoothness": 15}'>
                                    <g class="rightLeft">
                                    <path d="M402.9 98.0701C394.08 105.14 385.68 104.2 379.55 103.52C374.05 102.91 371.47 102.75 367.97 105.55C364.48 108.35 364.06 110.9 363.46 116.41C362.79 122.54 361.87 130.94 353.05 138.01C344.23 145.08 335.83 144.14 329.7 143.46C324.2 142.85 321.62 142.69 318.12 145.49C314.63 148.29 314.21 150.84 313.61 156.34C312.94 162.47 312.02 170.87 303.2 177.94C294.38 185.01 285.98 184.07 279.85 183.39C274.35 182.78 271.77 182.62 268.27 185.42L258.27 172.93C267.09 165.86 275.49 166.8 281.62 167.48C287.12 168.09 289.7 168.25 293.2 165.45C296.69 162.65 297.11 160.1 297.71 154.6C298.38 148.47 299.3 140.07 308.12 133C316.94 125.93 325.34 126.87 331.47 127.55C336.97 128.16 339.55 128.32 343.05 125.52C346.55 122.72 346.96 120.17 347.56 114.66C348.23 108.53 349.15 100.13 357.97 93.0601C366.79 85.9901 375.19 86.9301 381.32 87.6101C386.82 88.2201 389.4 88.3801 392.9 85.5801L402.9 98.0701Z" fill="#EFECFF"/>
                                    </g>
                                    <g class="leftRight">
                                    <path d="M392.63 85.2456C395.392 88.6949 394.873 93.9082 390.776 95.5631C384.539 98.0824 378.794 97.4407 374.28 96.94C368.78 96.33 366.2 96.17 362.7 98.97C359.21 101.77 358.79 104.32 358.19 109.83C357.52 115.96 356.6 124.36 347.78 131.43C338.96 138.5 330.56 137.56 324.43 136.88C318.93 136.27 316.35 136.11 312.85 138.91C309.36 141.71 308.94 144.26 308.34 149.76C307.67 155.89 306.75 164.29 297.93 171.36C289.11 178.43 280.71 177.49 274.58 176.81C272.978 176.632 271.624 176.493 270.422 176.454C266.006 176.309 260.762 176.044 258 172.594C255.238 169.145 255.757 163.932 259.854 162.277C266.091 159.758 271.836 160.399 276.35 160.9C281.85 161.51 284.43 161.67 287.93 158.87C291.42 156.07 291.84 153.52 292.44 148.02C293.11 141.89 294.03 133.49 302.85 126.42C311.67 119.35 320.07 120.29 326.2 120.97C331.7 121.58 334.28 121.74 337.78 118.94C341.28 116.14 341.69 113.59 342.29 108.08C342.96 101.95 343.88 93.55 352.7 86.48C361.52 79.41 369.92 80.35 376.05 81.03C377.652 81.2077 379.006 81.3472 380.208 81.3865C384.624 81.5309 389.868 81.7958 392.63 85.2456Z" fill="url(#paint1_linear_431_74)" fill-opacity="0.5"/>
                                    </g>
                                    </g>
                                    <defs>
                                        <linearGradient id="paint0_linear_431_74" x1="1837" y1="697" x2="1837" y2="784" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#FFE176" offset="0%"/>
                                            <stop offset="1" stop-color="#FFD646"/>
                                        </linearGradient>
                                        <linearGradient id="paint1_linear_431_74" x1="325.315" y1="79" x2="325.315" y2="178.84" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#9888F4" offset="0%"/>
                                            <stop offset="1" stop-color="#907EF3"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row flex-column-reverse flex-xl-row">
                        <div class="col-xl-5">
                            <div class="our-features__media wow fadeIn animated" data-wow-delay=".3s">
                                <img src="<?php print esc_url($weta_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
                            </div>
                        </div>
                        <div class="col-xl-7">
                            <div class="section__title-wrapper mb-60 mb-sm-50 mb-xs-40">
                                <?php if ( !empty ( $settings['subheading'] ) ) : ?>
                                    <span class="weta-el-section-subheading section__subtitle mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                        <?php if ( !empty ( $settings['subheading_before'] ) ) : ?>
                                            <span class="layer" data-depth="0.009">
                                                <?php print esc_html($settings['subheading_before']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php print esc_html($settings['subheading']); ?>
                                        <?php if ( !empty ( $settings['subheading_icon']['url'] ) ) : ?>
                                            <img class="rightLeft" src="<?php print esc_url($subheading_icon); ?>" alt="<?php print esc_attr($subheading_icon_alt); ?>">
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( !empty ( $settings['title'] ) ) : ?>
                                    <h2 class="weta-el-section-title section__title mb-5 text-uppercase wow fadeIn animated" data-wow-delay=".3s">
                                        <?php print rrdevs_kses($settings['title']); ?>
                                    </h2>
                                <?php endif; ?>
                                <?php if ( !empty ( $settings['description'] ) ) : ?>
                                    <p class="weta-el-section-description mb-0 wow fadeIn animated" data-wow-delay=".5s">
                                        <?php print rrdevs_kses($settings['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="our-features__item-wrapper d-grid">
                                <?php foreach( $settings['features_list'] as $key => $item ): 
                                    
                                    if ('2' == $item['weta_link_type']) {
                                        $link = get_permalink($item['weta_page_link']);
                                        $target = '_self';
                                        $rel = 'nofollow';
                                    } else {
                                        $link = !empty($item['weta_link']['url']) ? $item['weta_link']['url'] : '';
                                        $target = !empty($item['weta_link']['is_external']) ? '_blank' : '';
                                        $rel = !empty($item['weta_link']['nofollow']) ? 'nofollow' : '';
                                    }
                                    
                                ?>
                                <div class="our-features__item wow fadeIn animated" data-wow-delay=".7s">
                                    <div class="our-features__item-header d-flex align-items-center mb-15 mb-xs-10">
                                        <?php if ( $item['feature_icon_type']  == 'image' ): 
                                            if ( !empty($item['feature_image_icon']['url']) ) {
                                                $feature_image_icon = !empty($item['feature_image_icon']['id']) ? wp_get_attachment_image_url( $item['feature_image_icon']['id'], 'full') : $item['feature_image_icon']['url'];
                                                $feature_image_icon_alt = get_post_meta($item["feature_image_icon"]["id"], "_wp_attachment_image_alt", true);
                                            }
                                            ?>
                                            <div class="icon">
                                                <img src="<?php echo esc_url($feature_image_icon); ?>" alt="<?php echo esc_attr($feature_image_icon_alt); ?>">
                                            </div>
                                        <?php elseif ( $item['feature_icon_type']  == 'icon' ): ?>
                                            <div class="icon">
                                                <?php weta_render_icon($item, 'feature_icon' ); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ( !empty ( $item['feature_title'] ) ) : ?>
                                            <h4><?php print rrdevs_kses( $item['feature_title' ] ); ?></h4>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ( !empty ( $item['feature_description'] ) ) : ?>
                                        <p class="body mb-20">
                                            <?php print rrdevs_kses( $item['feature_description'] ); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ( !empty ( $item['feature_link_text'] ) ) : ?>
                                        <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>" class="readmore">
                                            <?php print rrdevs_kses($item['feature_link_text']); ?>
                                            <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M1 1L5 5L1 9" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif ( $settings['design_style']  == 'layout-2' ): 
            if ('2' == $settings['weta_feature_button_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_feature_button_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-sm mt-35 mt-sm-30 mt-xs-25 wow fadeIn animated');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.7s');
            } else {
                if ( ! empty( $settings['weta_feature_button_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_feature_button_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-sm mt-35 mt-sm-30 mt-xs-25 wow fadeIn animated');
                    $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.7s');
                }
            }
            
            ?>

            <section class="our-features-1 our-features-1__section-space theme-bg-1 overflow-hidden parallax-element">
                <div class="container">
                    <?php if ( !empty ( $settings['shape_switch'] ) ) : ?>
                        <div class="our-features-1__shape">
                            <div class="our-features-1__shape-1" data-parallax='{"y": 50, "scale": 1.2, "smoothness": 15}'><div class="layer" data-depth="0.02"><div class="zooming"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/our-feature-1/shape-1.svg" alt="shape not found"></div></div></div>
                            <div class="our-features-1__shape-container">
                                <svg width="1919" height="670" viewBox="0 0 1919 670" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <mask id="mask0_1301_88" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="1919" height="670">
                                        <rect width="1919" height="670" fill="#D9D9D9"/>
                                    </mask>
                                    <g mask="url(#mask0_1301_88)">
                                        <g data-parallax='{"y": 50, "scale": 1.5, "smoothness": 15}'><g class="layer" data-depth="0.02"><g class="leftRight">
                                            <path d="M127 236C127 239.314 124.314 242 121 242C117.686 242 115 239.314 115 236C115 232.686 117.686 230 121 230C124.314 230 127 232.686 127 236Z" fill="#FBD95E"/>
                                        </g></g></g>
                                        <g data-parallax='{"y": 50, "scale": 1.5, "smoothness": 15}'><g class="layer" data-depth="0.002"><g class="leftRight">
                                        <path d="M169 262C169 264.209 167.209 266 165 266C162.791 266 161 264.209 161 262C161 259.791 162.791 258 165 258C167.209 258 169 259.791 169 262Z" fill="#5489FA"/>
                                        </g></g></g>

                                        <g data-parallax='{"y": 50, "scale": 1.5, "smoothness": 15}'><g class="layer" data-depth="0.002"><g class="leftRight">
                                        <path d="M135 281C135 282.105 134.105 283 133 283C131.895 283 131 282.105 131 281C131 279.895 131.895 279 133 279C134.105 279 135 279.895 135 281Z" fill="#F461A6"/>
                                        </g></g></g>
                                        <g class="layer" data-depth="0.02"><g class="rightLeft">
                                        <path d="M1690 92C1690 95.3137 1687.31 98 1684 98C1680.69 98 1678 95.3137 1678 92C1678 88.6863 1680.69 86 1684 86C1687.31 86 1690 88.6863 1690 92Z" fill="#6640FF"/>
                                        </g></g>
                                        <g class="layer" data-depth="0.002"><g class="leftRight">
                                        <path d="M1297 587C1297 591.418 1293.42 595 1289 595C1284.58 595 1281 591.418 1281 587C1281 582.582 1284.58 579 1289 579C1293.42 579 1297 582.582 1297 587Z" fill="#6640FF"/>
                                        </g></g>
                                        <g ><g class="layer" data-depth="0.02"><g class="leftRight">
                                        <path d="M1786 451.759C1778.32 457.849 1771 457.039 1765.66 456.453C1760.87 455.928 1758.62 455.79 1755.57 458.202C1752.53 460.614 1752.16 462.81 1751.64 467.556C1751.06 472.837 1750.26 480.072 1742.57 486.162C1734.89 492.252 1727.57 491.442 1722.23 490.857C1717.44 490.331 1715.19 490.193 1712.14 492.605C1709.1 495.017 1708.73 497.214 1708.21 501.951C1707.63 507.231 1706.83 514.467 1699.14 520.557C1691.46 526.647 1684.14 525.837 1678.8 525.251C1674.01 524.726 1671.76 524.588 1668.71 527L1660 516.241C1667.68 510.151 1675 510.961 1680.34 511.547C1685.13 512.072 1687.38 512.21 1690.43 509.798C1693.47 507.386 1693.84 505.19 1694.36 500.452C1694.94 495.172 1695.74 487.937 1703.43 481.847C1711.11 475.757 1718.43 476.566 1723.77 477.152C1728.56 477.677 1730.81 477.815 1733.86 475.403C1736.91 472.992 1737.27 470.795 1737.79 466.049C1738.37 460.769 1739.17 453.533 1746.86 447.443C1754.54 441.353 1761.86 442.163 1767.2 442.749C1771.99 443.274 1774.24 443.412 1777.29 441L1786 451.759Z" fill="#EFECFF"/>
                                        </g></g></g>
                                        <g class="layer" data-depth="0.02"><g class="rightLeft">
                                        <path d="M1776.71 440.486C1779.09 443.475 1778.64 447.974 1775.11 449.419C1769.71 451.631 1764.73 451.07 1760.82 450.633C1756.07 450.101 1753.84 449.962 1750.81 452.402C1747.79 454.842 1747.43 457.064 1746.91 461.865C1746.33 467.207 1745.54 474.527 1737.92 480.687C1730.29 486.848 1723.03 486.029 1717.74 485.436C1712.98 484.905 1710.75 484.765 1707.73 487.205C1704.71 489.645 1704.35 491.867 1703.83 496.66C1703.25 502.002 1702.45 509.321 1694.83 515.482C1687.21 521.643 1679.95 520.824 1674.65 520.231C1673.3 520.08 1672.16 519.961 1671.14 519.923C1667.28 519.78 1662.69 519.537 1660.29 516.514C1657.91 513.526 1658.36 509.026 1661.89 507.581C1667.29 505.369 1672.27 505.93 1676.18 506.367C1680.93 506.899 1683.16 507.038 1686.19 504.598C1689.21 502.158 1689.57 499.936 1690.09 495.144C1690.67 489.802 1691.46 482.482 1699.08 476.322C1706.71 470.161 1713.97 470.98 1719.26 471.572C1724.02 472.104 1726.25 472.243 1729.27 469.804C1732.3 467.364 1732.65 465.142 1733.17 460.34C1733.75 454.999 1734.55 447.679 1742.17 441.518C1749.79 435.357 1757.05 436.176 1762.35 436.769C1763.7 436.92 1764.84 437.039 1765.86 437.077C1769.72 437.22 1774.31 437.463 1776.71 440.486Z" fill="url(#paint0_linear_1301_88)" fill-opacity="0.5"/>
                                        </g></g>
                                    </g>
                                    <defs>
                                        <linearGradient id="paint0_linear_1301_88" x1="1718.5" y1="435" x2="1718.5" y2="522" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#9888F4" offset="0%"/>
                                            <stop offset="1" stop-color="#907EF3"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row flex-column-reverse flex-xl-row">
                        <div class="col-xxl-6 col-xl-5">
                            <div class="our-features-1__media wow fadeIn animated" data-wow-delay=".5s">
                                <div class="leftRight">
                                    <img src="<?php print esc_url($weta_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-xl-7">
                            <div class="section__title-wrapper mb-30 mb-sm-25 mb-xs-20 our-features-1__content">
                                <?php if ( !empty ( $settings['subheading'] ) ) : ?>
                                    <span class="weta-el-section-subheading section__subtitle mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                        <?php if ( !empty ( $settings['subheading_before'] ) ) : ?>
                                            <span class="layer" data-depth="0.009">
                                                <?php print esc_html($settings['subheading_before']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php print esc_html($settings['subheading']); ?>
                                        <?php if ( !empty ( $settings['subheading_icon']['url'] ) ) : ?>
                                            <img class="rightLeft" src="<?php print esc_url($subheading_icon); ?>" alt="<?php print esc_attr($subheading_icon_alt); ?>">
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( !empty ( $settings['title'] ) ) : ?>
                                    <h2 class="weta-el-section-title section__title text-uppercase wow fadeIn animated" data-wow-delay=".3s">
                                        <?php print rrdevs_kses($settings['title']); ?>
                                    </h2>
                                <?php endif; ?>
                            </div>

                            <div class="our-features-1">
                                <ul class="our-features-1__list wow fadeIn animated" data-wow-delay=".5s">
                                    <?php foreach( $settings['features_list_two'] as $key => $item ): ?>
                                        <?php if ( !empty ( $item['feature_title_two'] ) ) : ?>
                                            <li><?php print rrdevs_kses( $item['feature_title_two' ] ); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>

                                <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                    <span class="btn-wrap">
                                        <span class="text-one">Show all Features <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.12295 8.87947C0.97392 8.73337 0.960372 8.50475 1.08231 8.34364L1.12295 8.29749L4.48671 5L1.12295 1.70251C0.97392 1.55641 0.960372 1.32779 1.08231 1.16669L1.12295 1.12053C1.27198 0.974432 1.50519 0.961151 1.66952 1.08069L1.7166 1.12053L5.37705 4.70901C5.52608 4.85511 5.53963 5.08373 5.4177 5.24483L5.37705 5.29099L1.7166 8.87947C1.55267 9.04018 1.28688 9.04018 1.12295 8.87947Z" fill="white" stroke="white" stroke-width="0.5"/>
                                        </svg></span>
                                        <span class="text-two">Show all Features <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.12295 8.87947C0.97392 8.73337 0.960372 8.50475 1.08231 8.34364L1.12295 8.29749L4.48671 5L1.12295 1.70251C0.97392 1.55641 0.960372 1.32779 1.08231 1.16669L1.12295 1.12053C1.27198 0.974432 1.50519 0.961151 1.66952 1.08069L1.7166 1.12053L5.37705 4.70901C5.52608 4.85511 5.53963 5.08373 5.4177 5.24483L5.37705 5.29099L1.7166 8.87947C1.55267 9.04018 1.28688 9.04018 1.12295 8.87947Z" fill="white" stroke="white" stroke-width="0.5"/>
                                        </svg></span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
        <?php elseif ( $settings['design_style']  == 'layout-3' ): 

            if ('2' == $settings['weta_feature_button_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_feature_button_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-4');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.7s');
            } else {
                if ( ! empty( $settings['weta_feature_button_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_feature_button_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-4');
                    $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.7s');
                }
            }
            
            ?>

            <section class="customers-solutions overflow-hidden parallax-element">
                <div class="container container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="customers-solutions__wrapper d-flex flex-column flex-lg-row justify-content-between align-items-center">
                                <?php if ( !empty ( $settings['shape_switch'] ) ) : ?>
                                    <div class="customers-solutions__shape wow fadeIn animated" data-wow-delay=".3s">
                                        <img src="<?php print get_template_directory_uri(); ?>/assets/imgs/customers-solutions/background-shape.svg" alt="<?php print esc_attr( 'shape', 'weta-core' ); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="customers-solutions__content">
                                    <div class="section-3__title-wrapper">
                                        <?php if ( !empty ( $settings['subheading'] ) ) : ?>
                                            <span class="weta-el-section-subheading section-3__subtitle flex-wrap justify-content-start mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                                <?php if ( !empty ( $settings['subheading_before'] ) ) : ?>
                                                    <span class="layer" data-depth="0.009">
                                                        <?php print esc_html($settings['subheading_before']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php print esc_html($settings['subheading']); ?>
                                                <?php if ( !empty ( $settings['subheading_icon']['url'] ) ) : ?>
                                                    <img class="rightLeft" src="<?php print esc_url($subheading_icon); ?>" alt="<?php print esc_attr($subheading_icon_alt); ?>">
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ( !empty ( $settings['title'] ) ) : ?>
                                            <h2 class="weta-el-section-title section-3__title lg mb-15 wow fadeIn animated" data-wow-delay=".3s">
                                                <?php print rrdevs_kses($settings['title']); ?>
                                            </h2>
                                        <?php endif; ?>
                                        <?php if ( !empty ( $settings['description'] ) ) : ?>
                                            <p class="weta-el-section-description mb-30 wow fadeIn animated" data-wow-delay=".5s">
                                                <?php print rrdevs_kses($settings['description']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <ul class="wow fadeIn animated" data-wow-delay=".6s">
                                            <?php foreach( $settings['features_list_two'] as $key => $item ): ?>
                                                <?php if ( !empty ( $item['feature_title_two'] ) ) : ?>
                                                    <li><?php print rrdevs_kses( $item['feature_title_two' ] ); ?></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>

                                        <div class="rr-btn__wrapper d-flex flex-wrap align-items-center mt-40 mt-sm-35 mt-xs-30 wow fadeIn animated" data-wow-delay=".7s">
                                            <?php if ( !empty ($settings['weta_feature_button_text']) ) : ?>
                                                <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                                    <span class="btn-wrap">
                                                        <span class="text-one">
                                                            <?php print esc_html($settings['weta_feature_button_text']); ?>
                                                        </span>
                                                        <span class="text-two">
                                                            <?php print esc_html($settings['weta_feature_button_text']); ?>
                                                        </span>
                                                    </span>
                                                </a>
                                            <?php endif; ?>

                                            <a href="<?php print esc_url($settings['video_url']); ?>" class="popup-video" data-effect="mfp-move-from-top vertical-middle">
                                                <svg class="zooming" width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13 7L0.875644 14L0.875644 0L13 7Z" fill="#010915"/>
                                                </svg>
                                                <?php print esc_html($settings['video_text']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="customers-solutions__media rightLeft wow fadeIn animated" data-wow-delay=".3s">
                                    <img src="<?php print esc_url($weta_image); ?>" alt="<?php print esc_url($weta_image_alt); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php endif; ?>

        <?php 
	}
}

$widgets_manager->register( new WETA_Features() );