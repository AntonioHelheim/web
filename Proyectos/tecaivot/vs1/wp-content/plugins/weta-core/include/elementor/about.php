<?php
namespace WETACore\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Border;
Use \Elementor\Core\Schemes\Typography;
use \Elementor\Repeater;
use \Elementor\Utils;
use \Elementor\Control_Media;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_About extends Widget_Base {

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
		return 'about';
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
		return __( 'About', 'weta-core' );
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

        $this->end_controls_section();

        // Image
        $this->start_controls_section(
            'weta_section_image',
            [
                'label' => esc_html__('Image', 'weta-core'),
            ]
        );

        $this->add_control(
            'weta_goal_shape',
            [
                'label' => esc_html__( 'Goal Shape', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );

        

        $this->add_control(
            'weta_img_01',
            [
                'label' => esc_html__( 'Image 01', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'weta_img_02',
            [
                'label' => esc_html__( 'Image 02', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'weta_img_03',
            [
                'label' => esc_html__( 'Image 03', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );
        
        $this->add_control(
            'video_url',
            [
                'label' => esc_html__('Video URL', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => 'https://youtu.be/iyd7qUH3oH0',
                'placeholder' => 'https://youtu.be/iyd7qUH3oH0',
                'label_block' => true,
                'condition' => [
                    'design_style' => [ 'layout-10' ],
                ],
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
            'subheading',
            [
                'label' => esc_html__('Subheading', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('About Us', 'weta-core'),
                'label_block' => true,
            ]
        );       
        
        $this->add_control(
            'title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Digital Trusted Transport Logistic Company', 'weta-core'),
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

        $this->add_control(
            'description',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => esc_html__('There are many variations of passages of but the majority have in some form, by injected humou or words which don’t look even slightly believable of but the majority have suffered', 'weta-core'),
            ]
        );

        $this->end_controls_section();

        // Button 
        $this->start_controls_section(
            '_content_button',
            [
                'label' => esc_html__('Button', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'link_switcher',
            [
                'label' => esc_html__( 'Show Link', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'link_text',
            [
                'label' => esc_html__( 'Link Text', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__( 'Read More', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
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
                    'link_switcher' => 'yes',
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
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
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ]
            ]
        );

        $this->add_control(
            'page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'link_type' => '2',
                    'link_switcher' => 'yes',
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ]
            ]
        );

        $this->end_controls_section();


        // About Contact Info
        $this->start_controls_section(
            '_content_about_contact_info',
            [
                'label' => esc_html__('About Contact', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => esc_html__( 'Icon', 'weta-core' ),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fa-sharp fa-solid fa-phone',
                    'library' => 'fa-solid',
                ],
                'recommended' => [
                    'fa-solid' => [
                        'circle',
                        'dot-circle',
                        'square-full',
                    ],
                    'fa-regular' => [
                        'circle',
                        'dot-circle',
                        'square-full',
                    ],
                ],
            ]
        );

        $this->add_control(
            'contact_label',
            [
                'label' => esc_html__('Contact Label', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Help Desk 24/7', 'weta-core'),
                'label_block' => true,
            ]
        );         

        $this->add_control(
            'about_contact_text',
            [
                'label' => esc_html__('Contact Text', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('000 2324 39493', 'weta-core'),
                'label_block' => true,
            ]
        ); 

        $this->add_control(
            'about_contact_url',
            [
                'label' => esc_html__('Contact URL', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '#',
                'title' => esc_html__('Video url', 'weta-core'),
                'label_block' => true,
            ]
        );  

        $this->end_controls_section();


        // Author Signature
        $this->start_controls_section(
            '_content_about_info',
            [
                'label' => esc_html__('About Info', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'about_info_image',
            [
                'label' => esc_html__( 'About Info Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'about_info_title',
            [
                'label' => esc_html__('About Info Title', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Best Awarded Company', 'weta-core'),
                'label_block' => true,
            ]
        );        

        $this->add_control(
            'about_info_desc',
            [
                'label' => esc_html__('About Info Description', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Lorem Ipsum is simply dummy text of the printing and typesetting', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();


        // Features group
        $this->start_controls_section(
            'weta_features',
            [
                'label' => esc_html__('Features List', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();


        $repeater->add_control(
            'repeater_condition',
            [
                'label' => __( 'Field condition', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'style_1' => __( 'Style 1', 'weta-core' ),
                    'style_2' => __( 'Style 2', 'weta-core' ),
                    'style_3' => __( 'Style 3', 'weta-core' ),
                ],
                'default' => 'style_1',
                'frontend_available' => true,
                'style_transfer' => true,
            ]
        );

        $repeater->add_control(
			'icon_type',
			[
				'label' => esc_html__( 'Image Type', 'weta-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => esc_html__( 'Image', 'weta-core' ),
					'icon' => esc_html__( 'Icon', 'weta-core' ),
                    'svg' => esc_html__( 'SVG Code', 'weta-core' ),
				],
                'condition' => [
                    'repeater_condition' => ['style_1', 'style_2']
                ]  
			]
		);

        $repeater->add_control(
            'image_icon',
            [
                'label' => esc_html__('Upload Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'icon_type' => 'image',
                    'repeater_condition' => ['style_1', 'style_2']
                ],
            ]
        );

        $repeater->add_control(
            'weta_about_icon_svg',
            [
                'show_label' => false,
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'placeholder' => esc_html__('SVG Code Here', 'weta-core'),
                'condition' => [
                    'icon_type' => 'svg',
                    'repeater_condition' => ['style_1', 'style_2']
                ]
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'icon-house',
                    'condition' => [
                        'icon_type' => 'icon',
                        'repeater_condition' => ['style_1', 'style_2']
                    ],
                ]
            );
        } else {
            $repeater->add_control(
                'selected_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'icon-house',
                        'library' => 'solid',
                    ],
                    'condition' => [
                        'icon_type' => 'icon',
                        'repeater_condition' => ['style_1', 'style_2']
                    ],
                ]
            );
        }

        $repeater->add_control(
            'check_icon',
            [
                'label' => esc_html__( 'Check Icon', 'weta-core' ),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fa-sharp fa-solid fa-circle-check',
                    'library' => 'fa-solid',
                ],
                'recommended' => [
                    'fa-solid' => [
                        'circle',
                        'dot-circle',
                        'square-full',
                    ],
                    'fa-regular' => [
                        'circle',
                        'dot-circle',
                        'square-full',
                    ],
                ],
                'condition' => [
                    'repeater_condition' => ['style_3']
                ]  
            ]
        );

        $repeater->add_control(
            'weta_features_title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Global Service', 'weta-core'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'weta_features_description', [
                'label' => esc_html__('Description', 'weta-core'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => esc_html__('Local Service', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'repeater_condition' => ['style_1', 'style_2']
                ] 
            ]
        );
     
        $this->add_control(
            'weta_features_list',
            [
                'label' => esc_html__('Services - List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'weta_features_title' => esc_html__('Repair Genius Fix Master', 'weta-core'),
                    ],
                    [
                        'weta_features_title' => esc_html__('Restore Pro Repair Techs', 'weta-core')
                    ]
                ],
                'title_field' => '{{{ weta_features_title }}}',
            ]
        );

        $this->end_controls_section();

        // Style tab
		$this->start_controls_section(
			'weta_layout_style',
			[
				'label' => __( 'Design Layout', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .about-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .goal-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-section' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .goal-section' => 'background-color: {{VALUE}}',
                ],
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

        // Title
        $this->add_control(
            '_subheading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subheading', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'subheading_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section-heading .sub-heading' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subheading_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-heading .sub-heading' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subheading_typography',
                'selector' => '{{WRAPPER}} .section-heading .sub-heading',
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
                    '{{WRAPPER}} .section-heading.heading-2 .section-title' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-heading.heading-2 .section-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .section-heading.heading-2 .section-title',
            ]
        );

        // Description
        $this->add_control(
            '_content_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'description_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section-heading.heading-2 p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-heading.heading-2 p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .section-heading.heading-2 p',
            ]
        );

        $this->end_controls_section();


        // Button 
		$this->start_controls_section(
			'_style_button',
			[
				'label' => __( 'Button', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ],
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
                    '{{WRAPPER}} .rr-primary-btn' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-primary-btn' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .rr-primary-btn',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .rr-primary-btn',
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
                    '{{WRAPPER}} .rr-primary-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-primary-btn:after, .rr-primary-btn:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border_hover',
                'selector' => '{{WRAPPER}} .rr-primary-btn:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rr-primary-btn:hover',
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
                    '{{WRAPPER}} .rr-primary-btn' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .rr-primary-btn:before' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .rr-primary-btn',
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .rr-primary-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .rr-primary-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

		$this->end_controls_section();


        // Features List
		$this->start_controls_section(
			'weta_features_list_style',
			[
				'label' => __( 'Features List', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'feature_box_padding',
            [
                'label' => __( 'Box Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .about-two .about-two__info-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',                    
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
			'features_list_icon',
			[
				'label' => esc_html__( 'Features Icon', 'weta-core' ),
				'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
			]
		);

        $this->add_control(
            'features_list_icon_color',
            [
                'label' => esc_html__( 'Color', 'text-domain' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item .about-icon' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-icon svg' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'features_list_icon_background',
            [
                'label' => esc_html__( 'Background', 'text-domain' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item .about-icon' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-icon' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'features_list_icon_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item .about-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'features_list_icon_size',
            [
                'label' => __( 'Font Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item .about-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-icon svg' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2'],
                ],
            ]
        );

        $this->add_control(
			'features_list_title',
			[
				'label' => esc_html__( 'Feature Title', 'weta-core' ),
				'type' => Controls_Manager::HEADING,
                'separator' => 'before',
			]
		);

        $this->add_responsive_control(
            'features_list_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-info .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .about-2 .about-content .about-list li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'features_list_title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item .title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-info .title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .about-2 .about-content .about-list li' => 'color: {{VALUE}}',
                ],
            ]
        );        

        $this->add_control(
            'features_list_check_icon_color',
            [
                'label' => __( 'Icon Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-content .about-list li i' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'features_list_title_typography',
                'selector' => '{{WRAPPER}} .about-item-wrap .about-item .title, .goal-content .goal-item-wrap .goal-item .goal-info .title, .about-2 .about-content .about-list li',
            ]
        );

        $this->add_control(
			'features_list_description',
			[
				'label' => esc_html__( 'Description', 'weta-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
			]
		);

        $this->add_responsive_control(
            'features_list_description_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-info p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'features_list_description_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item p' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item .goal-info p' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'features_list_description_typography',
                'selector' => '{{WRAPPER}} .about-item-wrap .about-item p, .goal-content .goal-item-wrap .goal-item .goal-info p',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
			'features_layout',
			[
				'label' => esc_html__( 'Layout', 'weta-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-2' ],
                ],
			]
		);

        $this->add_control(
            'features_layout_background',
            [
                'label' => esc_html__( 'Background', 'text-domain' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-content .about-item' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .goal-content .goal-item-wrap .goal-item' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-10', 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'features_layout_border',
            [
                'label' => esc_html__( 'Left Border', 'text-domain' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-item-wrap .about-item' => 'border-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-1'],
                ],
            ]
        );

		$this->end_controls_section();


        // About Contact Info
        $this->start_controls_section(
            'weta_about_contact_style',
            [
                'label' => __( 'About Contact', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-2'],
                ],
            ]
        );

        // Contact Label
        $this->add_control(
            '_about_contact_label',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Contact Label', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'about_contact_label_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .goal-content .goal-btn-wrap .goal-contact .number span' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'about_contact_label_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .goal-content .goal-btn-wrap .goal-contact .number span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'about_contact_label_typography',
                'selector' => '{{WRAPPER}} .goal-content .goal-btn-wrap .goal-contact .number span',
            ]
        );

        // Contact Phone
        $this->add_control(
            '_contact_phone_number',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Phone Number', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'phone_number_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .goal-contact .number a' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'phone_number_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .goal-contact .number a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'phone_number_typography',
                'selector' => '{{WRAPPER}} .goal-contact .number a',
            ]
        );

        $this->end_controls_section();


        // About Contact Info
        $this->start_controls_section(
            'weta_about_info_style',
            [
                'label' => __( 'About Info', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-3'],
                ],
            ]
        );

        $this->add_responsive_control(
            'about_info_content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-img .img-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'about_info_content_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-img .img-content' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'about_info_round_border_color',
            [
                'label' => __( 'Round Border Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-img::before' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        // About Info
        $this->add_control(
            '_about_info_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'About Info Title', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'about_info_title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-img .img-content .content-right .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'about_info_title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-img .img-content .content-right .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'about_info_title_typography',
                'selector' => '{{WRAPPER}} .about-2 .about-img .img-content .content-right .title',
            ]
        );

        // About Info Description
        $this->add_control(
            '_about_info_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'About Info Description', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'about_info_desc_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-img .img-content .content-right p' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'about_info_desc_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .about-2 .about-img .img-content .content-right p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'about_info_desc_typography',
                'selector' => '{{WRAPPER}} .about-2 .about-img .img-content .content-right p',
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

		?>

        <?php if ( $settings['design_style']  == 'layout-1' ) :
            $this->add_render_attribute('title_args', 'class', 'section-title wow fade-in-right');
            $this->add_render_attribute('title_args', 'data-wow-delay', '300ms');

            if ( !empty($settings['weta_img_01']['url']) ) {
                $weta_img_01 = !empty($settings['weta_img_01']['id']) ? wp_get_attachment_image_url( $settings['weta_img_01']['id'], 'full') : $settings['weta_img_01']['url'];
                $weta_img_01_alt = get_post_meta($settings["weta_img_01"]["id"], "_wp_attachment_image_alt", true);
            }            

            if ( !empty($settings['weta_img_02']['url']) ) {
                $weta_img_02 = !empty($settings['weta_img_02']['id']) ? wp_get_attachment_image_url( $settings['weta_img_02']['id'], 'full') : $settings['weta_img_02']['url'];
                $weta_img_02_alt = get_post_meta($settings["weta_img_02"]["id"], "_wp_attachment_image_alt", true);
            }         

            if ( !empty($settings['weta_img_03']['url']) ) {
                $weta_img_03 = !empty($settings['weta_img_03']['id']) ? wp_get_attachment_image_url( $settings['weta_img_03']['id'], 'full') : $settings['weta_img_03']['url'];
                $weta_img_03_alt = get_post_meta($settings["weta_img_03"]["id"], "_wp_attachment_image_alt", true);
            } 


            // Link
            if ('2' == $settings['link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn wow fade-in-right');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '600ms');
            } else {
                if ( ! empty( $settings['link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn wow fade-in-right');
                }
            }

        ?>

        <section class="about-section">
            <div class="container">
                <div class="row gy-lg-0 gy-4 align-items-center">
                    <div class="col-lg-6">
                        <div class="about-img-wrap wow fade-in-left" data-wow-delay="400ms">
                            <div class="left-img">
                                <?php if(!empty($weta_img_01)) : ?>
                                <div class="img-item">
                                    <img class="img-1" src="<?php print esc_url($weta_img_01); ?>" alt="<?php print esc_attr($weta_img_01_alt); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if(!empty($weta_img_02)) : ?>
                                <div class="img-item">
                                    <img class="img-2" src="<?php print esc_url($weta_img_02); ?>" alt="<?php print esc_attr($weta_img_02_alt); ?>">
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($weta_img_03)) : ?>
                            <div class="right-img">
                                <div class="img-item">
                                    <img class="img-3" src="<?php print esc_url($weta_img_03); ?>" alt="about">
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-content">
                            <div class="section-heading heading-2">
                                <?php if(!empty($settings['subheading'])): ?>
                                <h4 class="sub-heading wow fade-in-right" data-wow-delay="200ms">
                                    <?php echo $settings['subheading']; ?>    
                                </h4>
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

                                <?php if(!empty($settings['description'])): ?>
                                <p class="wow fade-in-right" data-wow-delay="400ms"><?php echo $settings['description']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="about-item-wrap wow fade-in-right" data-wow-delay="500ms">
                                <?php if($settings['weta_features_list']):
                                    foreach ($settings['weta_features_list'] as $index => $item) :
                                ?>
                                <div class="about-item">
                                    <?php if (!empty($item['image_icon']['url'])): ?>
                                        <div class="about-icon">
                                            <img src="<?php print esc_url($item['image_icon']['url']); ?>" alt="<?php echo get_post_meta(attachment_url_to_postid($item['image_icon']['url']), '_wp_attachment_image_alt', true); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['icon']) || !empty($item['selected_icon']['value'])) : ?>
                                        <div class="about-icon">
                                            <?php weta_render_icon($item, 'icon', 'selected_icon'); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($item['weta_features_title'])): ?>
                                    <h3 class="title"><?php echo rrdevs_kses($item['weta_features_title']); ?></h3>
                                    <?php endif; ?>

                                    <?php if(!empty($item['weta_features_description'])): ?>
                                    <p><?php echo rrdevs_kses($item['weta_features_description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>

                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                <?php print esc_html($settings['link_text']); ?> <i class="fa-sharp fa-regular fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <?php elseif ( $settings['design_style']  == 'layout-2' ) : 
            $this->add_render_attribute('title_args', 'class', 'section-title');
            $this->add_render_attribute('title_args', 'data-wow-delay', '300ms');

            if ( !empty($settings['weta_img_01']['url']) ) {
                $weta_img_01 = !empty($settings['weta_img_01']['id']) ? wp_get_attachment_image_url( $settings['weta_img_01']['id'], 'full') : $settings['weta_img_01']['url'];
                $weta_img_01_alt = get_post_meta($settings["weta_img_01"]["id"], "_wp_attachment_image_alt", true);
            }          

            if ( !empty($settings['weta_goal_shape']['url']) ) {
                $weta_goal_shape = !empty($settings['weta_goal_shape']['id']) ? wp_get_attachment_image_url( $settings['weta_goal_shape']['id'], 'full') : $settings['weta_goal_shape']['url'];
                $weta_goal_shape_alt = get_post_meta($settings["weta_goal_shape"]["id"], "_wp_attachment_image_alt", true);
            } 

            // Link
            if ('2' == $settings['link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '600ms');
            } else {
                if ( ! empty( $settings['link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn');
                }
            }
        ?>

        <section class="goal-section bg-dark-1">
            <div class="container">
                <div class="row goal-wrap align-items-center">
                    <div class="col-lg-6">
                        <div class="goal-content pt-120 pb-120">
                            <div class="section-heading heading-2 white-content wow fade-in-bottom" data-wow-delay="400ms">
                                <?php if(!empty($settings['subheading'])): ?>
                                <h4 class="sub-heading"><?php echo $settings['subheading']; ?></h4>
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

                                <?php if(!empty($settings['description'])): ?>
                                <p><?php echo $settings['description']; ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="goal-item-wrap wow fade-in-bottom" data-wow-delay="600ms">
                                <?php if($settings['weta_features_list']):
                                    foreach ($settings['weta_features_list'] as $index => $item) :
                                ?>
                                <div class="goal-item">
                                    <?php if (!empty($item['image_icon']['url'])): ?>
                                        <div class="goal-icon">
                                            <img src="<?php print esc_url($item['image_icon']['url']); ?>" alt="<?php echo get_post_meta(attachment_url_to_postid($item['image_icon']['url']), '_wp_attachment_image_alt', true); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['icon']) || !empty($item['selected_icon']['value'])) : ?>
                                        <div class="goal-icon">
                                            <?php weta_render_icon($item, 'icon', 'selected_icon'); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($item['weta_about_icon_svg'])): ?>
                                        <div class="goal-icon">
                                            <?php echo $item['weta_about_icon_svg']; ?>
                                        </div>
                                    <?php endif; ?>


                                    <div class="goal-info">
                                        <?php if(!empty($item['weta_features_title'])): ?>
                                        <h3 class="title"><?php echo rrdevs_kses($item['weta_features_title']); ?></h3>
                                        <?php endif; ?>

                                        <?php if(!empty($item['weta_features_description'])): ?>
                                        <p><?php echo rrdevs_kses($item['weta_features_description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>

                            <div class="goal-btn-wrap wow fade-in-bottom" data-wow-delay="800ms">
                                <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>><?php print esc_html($settings['link_text']); ?> <i class="fa-sharp fa-regular fa-arrow-right"></i></a>

                                <div class="goal-contact">
                                    <div class="contact-icon">
                                        <?php \Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                    </div>

                                    <h4 class="number">
                                        <?php if(!empty($settings['contact_label'])): ?>
                                        <span><?php echo $settings['contact_label']; ?></span>
                                        <?php endif; ?>

                                        <?php if(!empty($settings['about_contact_text'])): ?>
                                        <a href="<?php echo esc_url($settings["about_contact_url"]); ?>"><?php echo $settings['about_contact_text']; ?></a>
                                        <?php endif; ?>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="goal-img">
                            <?php if(!empty($weta_img_01)) : ?>
                            <div class="bg-shape">
                                <img src="<?php print esc_url($weta_img_01); ?>" alt="<?php print esc_attr($weta_img_01_alt); ?>">
                            </div>
                            <?php endif; ?>

                            <?php if(!empty($weta_goal_shape)) : ?>
                            <img src="<?php print esc_url($weta_goal_shape); ?>" alt="<?php print esc_attr($weta_goal_shape_alt); ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <?php elseif ( $settings['design_style']  == 'layout-3' ) : 
            $this->add_render_attribute('title_args', 'class', 'section-title');

            if ( !empty($settings['weta_img_01']['url']) ) {
                $weta_img_01 = !empty($settings['weta_img_01']['id']) ? wp_get_attachment_image_url( $settings['weta_img_01']['id'], 'full') : $settings['weta_img_01']['url'];
                $weta_img_01_alt = get_post_meta($settings["weta_img_01"]["id"], "_wp_attachment_image_alt", true);
            }            

            if ( !empty($settings['about_info_image']['url']) ) {
                $about_info_image = !empty($settings['about_info_image']['id']) ? wp_get_attachment_image_url( $settings['about_info_image']['id'], 'full') : $settings['about_info_image']['url'];
                $about_info_image_alt = get_post_meta($settings["about_info_image"]["id"], "_wp_attachment_image_alt", true);
            }          

            // Link
            if ('2' == $settings['link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn wow fade-in-right');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '700ms');
            } else {
                if ( ! empty( $settings['link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn wow fade-in-right');
                }
            }
        ?>

        <section class="about-section about-2 pt-120 pb-120 overflow-hidden">
            <div class="container">
                <div class="row gy-lg-0 gy-4 align-items-center">
                    <div class="col-lg-6">
                        <div class="about-img wow fade-in-left" data-wow-delay="400ms">
                            <?php if(!empty($weta_img_01)) : ?>
                            <img src="<?php print esc_url($weta_img_01); ?>" alt="<?php print esc_attr($weta_img_01_alt); ?>">
                            <?php endif; ?>

                            <div class="img-content">
                                <?php if(!empty($about_info_image)) : ?>
                                <img src="<?php print esc_url($about_info_image); ?>" alt="<?php print esc_attr($about_info_image_alt); ?>">
                                <?php endif; ?>

                                <div class="content-right">
                                    <?php if(!empty($settings['about_info_title'])): ?>
                                    <h3 class="title"><?php echo $settings['about_info_title']; ?> </h3>
                                    <?php endif; ?>

                                    <?php if(!empty($settings['about_info_desc'])): ?>
                                    <p><?php echo $settings['about_info_desc']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-content">
                            <div class="section-heading heading-2 wow fade-in-right" data-wow-delay="300ms">
                                <?php if(!empty($settings['subheading'])): ?>
                                <h4 class="sub-heading"><?php echo $settings['subheading']; ?></h4>
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
                                <?php if(!empty($settings['description'])): ?>
                                <p><?php echo $settings['description']; ?></p>
                                <?php endif; ?>
                            </div>

                            <ul class="about-list wow fade-in-right" data-wow-delay="500ms">
                                <?php if($settings['weta_features_list']):
                                    foreach ($settings['weta_features_list'] as $index => $item) :
                                ?>
                                    <?php if(!empty($item['weta_features_title'])): ?>
                                    <li>
                                        <?php \Elementor\Icons_Manager::render_icon( $item['check_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                        <?php echo rrdevs_kses($item['weta_features_title']); ?> 
                                    </li>
                                    <?php endif; ?>
                                <?php endforeach; endif; ?>
                            </ul>
                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>><?php print esc_html($settings['link_text']); ?> <i class="fa-sharp fa-regular fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php 
    endif;
	}
}

$widgets_manager->register( new weta_About() );