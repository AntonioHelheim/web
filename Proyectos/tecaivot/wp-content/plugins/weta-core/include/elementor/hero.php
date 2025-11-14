<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
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
class WETA_Hero extends Widget_Base {

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
		return 'weta_hero';
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
		return __( 'Hero', 'weta-core' );
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


        // Title & Content
        $this->start_controls_section(
            '_content_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'weta_subtitle_number',
            [
                'label' => esc_html__('Subtitle Number', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('01', 'weta-core'),
                'label_block' => true,
            ]
        );       

        $this->add_control(
            'weta_subtitle',
            [
                'label' => esc_html__('Subtitle', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Remarkable Support Team', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_subtitle_icon',
            [
                'label' => esc_html__( 'Subtitle Icon', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'weta_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Finest Mobile Device Governance Software.', 'weta-core'),
                'label_block' => true,
            ]
        ); 

        $this->add_control(
            'weta_description',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Ut imperdiet leo in arcu luctus, non tempor nunc mollis. Suspendisse malesuada dolor ut leo aliquam, quis imperdiet sapien lobortis. Etiam vulputate sit amet eros sed facilisis.', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_title_tag',
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
                'default' => 'h1',
                'toggle' => false,
            ]
        );

        $this->end_controls_section();


        // Hero Style 02 
        $this->start_controls_section(
            '_content_title_hero_02',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'weta_title_text_01',
            [
                'label' => esc_html__('Title Text 01', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('We Are', 'weta-core'),
                'label_block' => true,
            ]
        );   

        $this->add_control(
            'weta_title_img_01',
            [
                'label' => esc_html__( 'Title Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );     

        $this->add_control(
            'weta_title_text_02',
            [
                'label' => esc_html__('Title Text 02', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Est.2012', 'weta-core'),
                'label_block' => true,
            ]
        );     

        $this->add_control(
            'weta_title_text_03',
            [
                'label' => esc_html__('Title Text 03', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Design Branding', 'weta-core'),
                'label_block' => true,
            ]
        );        

        $this->add_control(
            'weta_title_text_04',
            [
                'label' => esc_html__('Title Text 04', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Digital Agency', 'weta-core'),
                'label_block' => true,
            ]
        ); 

        $this->end_controls_section();



        // Hero Style 03 Title & Description
        $this->start_controls_section(
            '_content_hero_02_title_description',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'weta_title_03',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Passionate Think Basins Building Future Connections.', 'weta-core'),
                'label_block' => true,
            ]
        );        

        $this->add_control(
            'weta_desc_03',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Donec fermentum dapibus iaculis ullamcorper. Maecenas eros nisl, faucibus id elementum sit amet, hendrerit at torto Sed congue ipsum nibh, aliquet elementum.', 'weta-core'),
                'label_block' => true,
            ]
        );   

        $this->end_controls_section();



        // Image
        $this->start_controls_section(
            'weta_section_image',
            [
                'label' => esc_html__('Image', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'hero_image',
            [
                'label' => esc_html__( 'Hero Main Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'hero_circle_image',
            [
                'label' => esc_html__( 'Hero Circle Image', 'weta-core' ),
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
            'hero_shape_switch',
            [
                'label' => esc_html__( 'Hero Shape Switch', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();


        // button
        $this->start_controls_section(
            '_content_button',
            [
                'label' => esc_html__('Button', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
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
                'default' => esc_html__('Download iOS', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_btn_button_show' => 'yes'
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
                    'weta_btn_button_show' => 'yes'
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
                    'weta_btn_button_show' => 'yes'
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
                    'weta_btn_button_show' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'weta_btn_link_icon',
            [
                'label' => esc_html__( 'Button Icon', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->end_controls_section();


        // button 02
        $this->start_controls_section(
            '_content_button2',
            [
                'label' => esc_html__('Button 02', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'weta_btn_button2_show',
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
            'weta_btn2_text',
            [
                'label' => esc_html__('Button 02 Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Download Android', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_btn_button_show' => 'yes'
                ],
            ]
        );
        $this->add_control(
            'weta_btn2_link_type',
            [
                'label' => esc_html__('Button 02 Link Type', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'label_block' => true,
                'condition' => [
                    'weta_btn_button2_show' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'weta_btn2_link',
            [
                'label' => esc_html__('Button 02 link', 'weta-core'),
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
                    'weta_btn2_link_type' => '1',
                    'weta_btn_button2_show' => 'yes'
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_btn2_page_link',
            [
                'label' => esc_html__('Select Button 02 Page', 'weta-core'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_btn2_link_type' => '2',
                    'weta_btn_button2_show' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'weta_btn2_link_icon',
            [
                'label' => esc_html__( 'Button 02 Icon', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->end_controls_section();


        // Happy Customer
        $this->start_controls_section(
            '_content_happy_customer',
            [
                'label' => esc_html__('Happy Customer Info', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'happy_customer_image01',
            [
                'label' => esc_html__( 'Happy Customer Image 01', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );        

        $this->add_control(
            'happy_customer_image02',
            [
                'label' => esc_html__( 'Happy Customer Image 02', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );        

        $this->add_control(
            'happy_customer_image03',
            [
                'label' => esc_html__( 'Happy Customer Image 03', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'happy_customer_number',
            [
                'label' => esc_html__('Happy Customer Number', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('2,658', 'weta-core'),
                'label_block' => true,
            ]
        );        

        $this->add_control(
            'happy_customer_text',
            [
                'label' => esc_html__('Happy Customer Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Happy Customers', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();


        // Hero 02 Author Thumb
        $this->start_controls_section(
            '_content_hero_02_author_thumb',
            [
                'label' => esc_html__('Hero Author Thumb', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'author_thumb_01',
            [
                'label' => esc_html__( 'Author Thumb 01', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );        

        $this->add_control(
            'author_thumb_02',
            [
                'label' => esc_html__( 'Author Thumb 02', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );        

        $this->add_control(
            'author_thumb_03',
            [
                'label' => esc_html__( 'Author Thumb 03', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'author_thumb_text',
            [
                'label' => esc_html__('Author Thumb Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Design Branding', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();



        // Hero Raing 
        $this->start_controls_section(
            '_content_customer_rating',
            [
                'label' => esc_html__('Customer Rating', 'weta-core'),
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'customer_rating_number',
            [
                'label' => esc_html__('Customer Rating Number', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('4.7/5', 'weta-core'),
                'label_block' => true,
            ]
        );        

        $this->add_control(
            'customer_rating_text',
            [
                'label' => esc_html__('Customer Rating Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Happy Customers', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'customer_rating_switch',
            [
                'label' => esc_html__( 'Rating Switch', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();


        // Style Control
        $this->start_controls_section(
            'weta_layout_style',
            [
                'label' => __( 'Design Layout', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .banner__space' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner__space' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Title & Content
		$this->start_controls_section(
            '_main_slider_style',
            [
                'label' => __( 'Title & Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        // Subtitle Number
        $this->add_control(
            '_heading_subtitle_number',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subtitle Number', 'weta-core' ),
            ]
        );

        $this->add_control(
            'subtitle_number_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner__sub-title span' => 'color: {{VALUE}}',
                ],
            ]
        );         

        $this->add_control(
            'subtitle_number_bg_color',
            [
                'label' => __( 'BG Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner__sub-title span' => 'background-color: {{VALUE}}',
                ],
            ]
        );        


        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_number_typography',
                'selector' => '{{WRAPPER}} .banner__sub-title span',
            ]
        );


        // Subtitle
        $this->add_control(
            '_heading_subtitle',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subtitle', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'subtitle_spacing',
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
            'subtitle_color',
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
                'name' => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .weta-el-subtitle',
            ]
        );

        // Title
        $this->add_control(
            '_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
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
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .weta-el-title',
            ]
        );

        // Description
        $this->add_control(
            '_content_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'description_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-desc' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-desc' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .weta-el-desc',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Button Style 01 
        $this->start_controls_section(
			'weta_button_style',
			[
				'label' => __( 'Button', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
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
                    '{{WRAPPER}} .rr-primary-btn'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn__theme .btn-wrap .text-one'    => 'color: {{VALUE}}',
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
                    '{{WRAPPER}} .rr-btn::before' => 'background-color: {{VALUE}}',
                ],
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
                    '{{WRAPPER}} .rr-btn__theme:hover .btn-wrap .text-two' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-primary-btn:hover' => 'background-color: {{VALUE}}',
                ],
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


        // Button Style 02
        $this->start_controls_section(
            'weta_button2_style',
            [
                'label' => __( 'Button 02', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-1', 'layout-3' ],
                ],
            ]
        );

        $this->start_controls_tabs( 'tabs_button2_style' );

        $this->start_controls_tab(
            'button2_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );

        $this->add_control(
            'button2_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn-sec'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn .btn-wrap .text-one'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button2_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn-sec' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn::before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button2_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );

        $this->add_control(
            'button2_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn-sec:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .rr-btn__theme:hover .btn-wrap .text-two' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button2_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn-sec:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button2_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn-sec' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button2_border',
                'selector' => '{{WRAPPER}} .rr-btn-sec',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'button2_typography',
                'selector' => '{{WRAPPER}} .rr-btn-sec',
            ]
        );

        $this->add_control(
            'button2_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .rr-btn-sec' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button2_margin',
            [
                'label'      => esc_html__( 'Margin', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .rr-btn-sec' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // Happy Customer Style
        $this->start_controls_section(
            'weta_happy_customer_style',
            [
                'label' => __( 'Happy Customer', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        // Happy Customer Number
        $this->add_control(
            'weta_happy_customer_number_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Happy Customer Number', 'weta-core' ),
            ]
        );

        $this->add_control(
            'weta_happy_customer_number_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner__review__item .odometer' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'weta_happy_customer_number_typography',
                'selector' => '{{WRAPPER}} .banner__review__item .odometer',
            ]
        );

        // Happy Customer Text
        $this->add_control(
            'weta_happy_customer_text',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Happy Customer Text', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'weta_happy_customer_text_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .happy-customer-el-text' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'weta_happy_customer_text_typography',
                'selector' => '{{WRAPPER}} .happy-customer-el-text',
            ]
        );
        
        $this->end_controls_section();  



        // Happy Customer Rating
        $this->start_controls_section(
            'weta_customer_rating_style',
            [
                'label' => __( 'Customer Rating', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-1' ],
                ],
            ]
        );

        // Happy Customer Rating
        $this->add_control(
            'weta_customer_avg_rating__style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Customer Avarage Rating', 'weta-core' ),
            ]
        );

        $this->add_control(
            'weta_customer_avg_rating_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner__review__item h2 span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'weta_customer_avg_rating_typography',
                'selector' => '{{WRAPPER}} .banner__review__item h2 span.rating',
            ]
        );


        // Customer Rating  Icon
        $this->add_control(
            'weta_customer_avg_rating_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Avagrage Rating Icon', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'weta_customer_avg_rating_icon_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rating-wrapper .rate svg path' => 'fill: {{VALUE}}',
                ],
            ]
        );


        // Customer Rating  Text
        $this->add_control(
            'weta_customer_avg_rating_text',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Customer Avagrage Rating Text', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'weta_customer_avg_rating_text_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rating-wrapper p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'weta_customer_avg_rating_text_typography',
                'selector' => '{{WRAPPER}} .rating-wrapper p',
            ]
        );
        
        $this->end_controls_section();



        // Hero Style 02 Background Color
        $this->start_controls_section(
            'weta_hero_style_02_bg_color_style',
            [
                'label' => __( 'Hero Style 02 Background Color', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'weta_hero_style_02_bg_color_point_01',
            [
                'label' => __( 'Background Color Point 01', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner-2__shape-1' => 'background-color: {{VALUE}}',
                ],
            ]
        );        

        $this->add_control(
            'weta_hero_style_02_bg_color_point_02',
            [
                'label' => __( 'Background Color Point 02', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner-2__shape-2' => 'background-color: {{VALUE}}',
                ],
            ]
        );       

        $this->add_control(
            'weta_hero_style_02_bg_color_point_03',
            [
                'label' => __( 'Background Color Point 03', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner-2__shape-3' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'weta_hero_style_02_bg_color_point_04',
            [
                'label' => __( 'Background Color Point 04', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner-2__shape-4' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'weta_hero_style_02_bg_color_point_05',
            [
                'label' => __( 'Background Color Point 05', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner-2__shape-5' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();



        // Hero Style 02 Title
        $this->start_controls_section(
            'weta_hero_style_02_style',
            [
                'label' => __( 'Title Style', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );


        $this->add_control(
            'weta_hero_style_02_style_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner-2__content h2' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'weta_hero_style_02_style_typography',
                'selector' => '{{WRAPPER}} .banner-2__content h2',
            ]
        );

        $this->end_controls_section();


        // Hero Style 02 author Thumb Text
        $this->start_controls_section(
            'weta_hero_style_02_author_thumb_style',
            [
                'label' => __( 'Author Thumb Text Style', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'hero_style_02_author_text_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .banner-2__title-4' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'hero_style_02_author_text_typography',
                'selector' => '{{WRAPPER}} .banner-2__title-4',
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
        $this->add_render_attribute('title_args', 'class', 'banner__title mb-15 mb-xs-5 wow fadeIn animated weta-el-title');
        $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');

        if ( !empty($settings['weta_subtitle_icon']['url']) ) {
            $weta_subtitle_icon = !empty($settings['weta_subtitle_icon']['id']) ? wp_get_attachment_image_url( $settings['weta_subtitle_icon']['id'], 'full') : $settings['weta_subtitle_icon']['url'];
            $weta_subtitle_icon_alt = get_post_meta($settings["weta_subtitle_icon"]["id"], "_wp_attachment_image_alt", true);
        }        

        if ( !empty($settings['hero_image']['url']) ) {
            $hero_image = !empty($settings['hero_image']['id']) ? wp_get_attachment_image_url( $settings['hero_image']['id'], 'full') : $settings['hero_image']['url'];
            $weta_image_alt = get_post_meta($settings["hero_image"]["id"], "_wp_attachment_image_alt", true);
        }        

        if ( !empty($settings['hero_circle_image']['url']) ) {
            $hero_circle_image = !empty($settings['hero_circle_image']['id']) ? wp_get_attachment_image_url( $settings['hero_circle_image']['id'], 'full') : $settings['hero_circle_image']['url'];
            $hero_circle_image_alt = get_post_meta($settings["hero_circle_image"]["id"], "_wp_attachment_image_alt", true);
        }         

        // Customer Icon 01
        if ( !empty($settings['happy_customer_image01']['url']) ) {
            $happy_customer_image01 = !empty($settings['happy_customer_image01']['id']) ? wp_get_attachment_image_url( $settings['happy_customer_image01']['id'], 'full') : $settings['happy_customer_image01']['url'];
            $happy_customer_image01_alt = get_post_meta($settings["happy_customer_image01"]["id"], "_wp_attachment_image_alt", true);
        }       

        // Customer Icon 02
        if ( !empty($settings['happy_customer_image02']['url']) ) {
            $happy_customer_image02 = !empty($settings['happy_customer_image02']['id']) ? wp_get_attachment_image_url( $settings['happy_customer_image02']['id'], 'full') : $settings['happy_customer_image02']['url'];
            $happy_customer_image02_alt = get_post_meta($settings["happy_customer_image02"]["id"], "_wp_attachment_image_alt", true);
        }               

        // Customer Icon 03
        if ( !empty($settings['happy_customer_image03']['url']) ) {
            $happy_customer_image03 = !empty($settings['happy_customer_image03']['id']) ? wp_get_attachment_image_url( $settings['happy_customer_image03']['id'], 'full') : $settings['happy_customer_image03']['url'];
            $happy_customer_image03_alt = get_post_meta($settings["happy_customer_image03"]["id"], "_wp_attachment_image_alt", true);
        }        


        // Weta Button Icon 
        if ( !empty($settings['weta_btn_link_icon']['url']) ) {
            $weta_btn_link_icon = !empty($settings['weta_btn_link_icon']['id']) ? wp_get_attachment_image_url( $settings['weta_btn_link_icon']['id'], 'full') : $settings['weta_btn_link_icon']['url'];
            $weta_btn_link_icon_alt = get_post_meta($settings["weta_btn_link_icon"]["id"], "_wp_attachment_image_alt", true);
        }    

        // Link
        if ('2' == $settings['weta_btn_link_type']) {
            $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_btn_page_link']));
            $this->add_render_attribute('weta-button-arg', 'target', '_self');
            $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
            $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme wow fadeIn animated rr-primary-btn');
            $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.6s');
        } else {
            if ( ! empty( $settings['weta_btn_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button-arg', $settings['weta_btn_link'] );
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme wow fadeIn animated rr-primary-btn');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.6s');
            }
        }


        // Weta Button Icon 
        if ( !empty($settings['weta_btn2_link_icon']['url']) ) {
            $weta_btn2_link_icon = !empty($settings['weta_btn2_link_icon']['id']) ? wp_get_attachment_image_url( $settings['weta_btn2_link_icon']['id'], 'full') : $settings['weta_btn2_link_icon']['url'];
            $weta_btn2_link_icon_alt = get_post_meta($settings["weta_btn2_link_icon"]["id"], "_wp_attachment_image_alt", true);
        }  

        // Link 02
        if ('2' == $settings['weta_btn2_link_type']) {
            $this->add_render_attribute('weta-button2-arg', 'href', get_permalink($settings['weta_btn2_page_link']));
            $this->add_render_attribute('weta-button2-arg', 'target', '_self');
            $this->add_render_attribute('weta-button2-arg', 'rel', 'nofollow');
            $this->add_render_attribute('weta-button2-arg', 'class', 'rr-btn wow fadeIn animated rr-btn-sec');
            $this->add_render_attribute('weta-button2-arg', 'data-wow-delay', '.7s');
        } else {
            if ( ! empty( $settings['weta_btn2_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button2-arg', $settings['weta_btn2_link'] );
                $this->add_render_attribute('weta-button2-arg', 'class', 'rr-btn wow fadeIn animated rr-btn-sec');
                $this->add_render_attribute('weta-button2-arg', 'data-wow-delay', '.7s');
            }
        }
    ?>


    <!-- Banner area start -->
    <section class="banner banner__space overflow-hidden theme-bg-1 parallax-element">
        <div class="container container-xxl">
            <div class="banner__shape">
                <?php if(!empty($settings['hero_shape_switch'])) : ?>
                <div class="banner__shape-container">
                    <svg width="1920" height="870" viewBox="0 0 1920 870" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <mask id="mask0_486_59" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="1920" height="870">
                            <rect width="1920" height="870" fill="#D9D9D9"/>
                        </mask>
                        <g mask="url(#mask0_486_59)">
                            <g class="layer" data-depth="0.09">
                            <circle cx="1737" cy="69" r="6" fill="#FBD95E"/>
                            </g>
                            <g class="layer" data-depth="0.03">
                            <circle cx="1781" cy="95" r="4" fill="#5489FA"/>
                            </g>
                            <g class="layer" data-depth="0.03">
                            <circle cx="1749" cy="114" r="2" fill="#F461A6"/>
                            </g>

                            <g class="layer" data-depth="0.03">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M910.895 539.321C909.574 540.245 908.38 541.336 907.343 542.565C911.58 542.172 916.962 542.814 923.21 545.938C929.883 549.275 935.256 549.387 938.995 548.666C938.656 547.639 938.229 546.652 937.723 545.713C933.442 546.164 927.954 545.567 921.559 542.369C917.498 540.339 913.918 539.502 910.895 539.321ZM935.222 542.154C931.842 538.377 926.929 536 921.461 536C919.856 536 918.298 536.205 916.813 536.59C918.824 537.139 920.961 537.942 923.21 539.067C927.893 541.408 931.935 542.162 935.222 542.154ZM939.795 552.272C935.277 553.164 929.039 552.981 921.559 549.241C914.566 545.744 909 545.789 905.247 546.623C905.053 546.666 904.864 546.711 904.679 546.758C904.188 547.827 903.795 548.951 903.514 550.118C903.815 550.038 904.125 549.961 904.446 549.89C909 548.878 915.434 548.922 923.21 552.81C930.203 556.307 935.769 556.263 939.523 555.428C939.651 555.4 939.778 555.37 939.903 555.34C939.916 555.049 939.923 554.756 939.923 554.461C939.923 553.721 939.879 552.99 939.795 552.272ZM939.3 559.236C934.826 560.018 928.769 559.718 921.559 556.113C914.566 552.616 909 552.66 905.247 553.494C904.413 553.68 903.664 553.905 903.003 554.143C903.001 554.249 903 554.355 903 554.461C903 564.658 911.266 572.923 921.461 572.923C930.006 572.923 937.196 567.118 939.3 559.236Z" fill="#6640FF"/>
                            </g>
                            <path d="M1202 83.8821C1195.78 88.9099 1189.86 88.2414 1185.53 87.7578C1181.65 87.324 1179.83 87.2102 1177.37 89.2014C1174.9 91.1926 1174.61 93.006 1174.19 96.9244C1173.71 101.284 1173.06 107.257 1166.84 112.285C1160.62 117.313 1154.7 116.644 1150.38 116.161C1146.5 115.727 1144.68 115.613 1142.21 117.604C1139.75 119.595 1139.45 121.409 1139.03 125.32C1138.56 129.679 1137.91 135.653 1131.69 140.681C1125.47 145.708 1119.54 145.04 1115.22 144.556C1111.34 144.123 1109.52 144.009 1107.05 146L1100 137.118C1106.22 132.09 1112.14 132.759 1116.47 133.242C1120.35 133.676 1122.17 133.79 1124.63 131.799C1127.1 129.807 1127.39 127.994 1127.81 124.083C1128.29 119.723 1128.94 113.75 1135.16 108.722C1141.38 103.694 1147.3 104.363 1151.62 104.846C1155.5 105.28 1157.32 105.394 1159.79 103.403C1162.26 101.412 1162.55 99.5983 1162.97 95.6799C1163.44 91.3206 1164.09 85.3471 1170.31 80.3193C1176.53 75.2916 1182.46 75.96 1186.78 76.4436C1190.66 76.8774 1192.48 76.9912 1194.95 75L1202 83.8821Z" fill="#EFECFF"/>
                                <g class="layer" data-depth="0.01">
                            <path d="M1194.45 75.3538C1196.41 77.782 1196.04 81.4621 1193.15 82.6205C1188.76 84.3778 1184.71 83.9287 1181.53 83.5781C1177.65 83.1504 1175.83 83.0383 1173.37 85.0014C1170.9 86.9645 1170.61 88.7524 1170.19 92.6156C1169.71 96.9135 1169.06 102.803 1162.84 107.76C1156.62 112.717 1150.7 112.058 1146.38 111.581C1142.5 111.153 1140.68 111.041 1138.21 113.004C1135.75 114.967 1135.45 116.755 1135.03 120.611C1134.56 124.909 1133.91 130.799 1127.69 135.756C1121.47 140.713 1115.54 140.053 1111.22 139.577C1110.07 139.45 1109.1 139.351 1108.24 139.325C1105.15 139.234 1101.49 139.055 1099.55 136.646V136.646C1097.59 134.218 1097.96 130.538 1100.85 129.38C1105.24 127.622 1109.29 128.071 1112.47 128.422C1116.35 128.85 1118.17 128.962 1120.63 126.999C1123.1 125.035 1123.39 123.248 1123.81 119.391C1124.29 115.094 1124.94 109.204 1131.16 104.247C1137.38 99.2903 1143.3 99.9493 1147.62 100.426C1151.5 100.854 1153.32 100.966 1155.79 99.0028C1158.26 97.0397 1158.55 95.2518 1158.97 91.3886C1159.44 87.0907 1160.09 81.2013 1166.31 76.2444C1172.53 71.2875 1178.46 71.9465 1182.78 72.4233C1183.93 72.5502 1184.9 72.6493 1185.76 72.6747C1188.85 72.7659 1192.51 72.945 1194.45 75.3538V75.3538Z" fill="url(#paint0_linear_486_59)" fill-opacity="0.5"/>
                            </g>
                        </g>
                        <defs>
                            <linearGradient id="paint0_linear_486_59" x1="1147" y1="71" x2="1147" y2="141" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#9888F4" offset="0%"/>
                                <stop offset="1" stop-color="#907EF3"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <?php endif; ?>

                <?php if(!empty($hero_circle_image)) : ?>
                <div class="banner__shape-ball">
                    <div class="zooming">
                        <div class="layer" data-depth="0.02"><img class="wow fadeIn animated" data-wow-delay=".7s" src="<?php echo esc_url($hero_circle_image); ?>" alt="<?php print esc_attr($hero_circle_image_alt); ?>">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if(!empty($hero_image)) : ?> 
            <div class="banner__image wow fadeIn animated" data-wow-delay=".5s">
                <div class="rightLeft">
                    <img src="<?php echo esc_url($hero_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="banner__content">
                        <?php if ( !empty($settings['weta_subtitle']) ) : ?>
                        <span class="banner__sub-title mb-20 mb-xs-10 wow fadeIn animated weta-el-subtitle" data-wow-delay=".1s">
                            <?php if ( !empty($settings['weta_subtitle_number']) ) : ?>
                            <span class="layer" data-depth="0.009"><?php echo rrdevs_kses( $settings['weta_subtitle_number'] ); ?></span> 
                            <?php endif; ?>

                            <?php echo rrdevs_kses( $settings['weta_subtitle'] ); ?>

                            <?php if(!empty($weta_subtitle_icon)) : ?>
                            <img class="rightLeft" src="<?php echo esc_url($weta_subtitle_icon); ?>" alt="arrow not found">
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>

                        <?php
                            if ( !empty($settings['weta_title' ]) ) :
                                printf( '<%1$s %2$s>%3$s</%1$s>',
                                    tag_escape( $settings['weta_title_tag'] ),
                                    $this->get_render_attribute_string( 'title_args' ),
                                    rrdevs_kses( $settings['weta_title' ] )
                                    );
                            endif;
                        ?>

                        <?php if ( !empty($settings['weta_description']) ) : ?>
                        <p class="mb-40 mb-xs-30 wow fadeIn animated weta-el-desc" data-wow-delay=".5s"><?php echo rrdevs_kses( $settings['weta_description'] ); ?></p>
                        <?php endif; ?>

                        <div class="rr-btn__wrapper d-flex flex-wrap align-items-center mb-65 mb-sm-50 mb-xs-45">
                            <?php if(!empty( $settings['weta_btn_text'] )) : ?>
                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                <span class="btn-wrap">
                                    <?php if(!empty($settings['weta_btn_text'])) : ?>
                                    <span class="text-one"> 
                                        <img src="<?php echo esc_url($weta_btn_link_icon); ?>" alt="<?php print esc_attr($weta_btn_link_icon_alt); ?>"><?php echo $settings['weta_btn_text']; ?>
                                    </span>
                                    <?php endif; ?>

                                    <?php if(!empty($settings['weta_btn_text'])) : ?>
                                    <span class="text-two">
                                        <img src="<?php echo esc_url($weta_btn_link_icon); ?>" alt="<?php print esc_attr($weta_btn_link_icon_alt); ?>"><?php echo $settings['weta_btn_text']; ?>
                                    </span>
                                    <?php endif; ?>
                                </span>
                            </a>
                            <?php endif; ?>

                            <?php if(!empty( $settings['weta_btn2_text'] )) : ?>
                            <a <?php echo $this->get_render_attribute_string( 'weta-button2-arg' ); ?>>
                                <span class="btn-wrap">
                                    <span class="text-one">
                                        <img src="<?php echo esc_url($weta_btn2_link_icon); ?>" alt="<?php print esc_attr($weta_btn2_link_icon_alt); ?>"><?php echo $settings['weta_btn2_text']; ?>
                                    </span>

                                    <span class="text-two">
                                        <img src="<?php echo esc_url($weta_btn2_link_icon); ?>" alt="<?php print esc_attr($weta_btn2_link_icon_alt); ?>"><?php echo $settings['weta_btn2_text']; ?>
                                    </span>
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>

                        <div class="banner__review d-flex flex-column flex-sm-row wow fadeIn animated" data-wow-delay=".9s">

                            <div class="banner__review__img-items">
                                <?php if(!empty($happy_customer_image01)) : ?>
                                <img src="<?php print esc_url($happy_customer_image01); ?>" alt="<?php print esc_attr($happy_customer_image01_alt); ?>">
                                <?php endif; ?>                           

                                <?php if(!empty($happy_customer_image02)) : ?>
                                <img src="<?php print esc_url($happy_customer_image02); ?>" alt="<?php print esc_attr($happy_customer_image02_alt); ?>">
                                <?php endif; ?>                            

                                <?php if(!empty($happy_customer_image03)) : ?>
                                <img src="<?php print esc_url($happy_customer_image03); ?>" alt="<?php print esc_attr($happy_customer_image03_alt); ?>">
                                <?php endif; ?>
                            </div>

                            <div class="banner__review__item-wrapper d-flex align-items-center">
                                <div class="banner__review__item">
                                    <?php if ( !empty($settings['happy_customer_number']) ) : ?>
                                    <h2><span class="odometer" data-count="<?php echo esc_attr($settings['happy_customer_number']);?>">0</span></h2>
                                    <?php endif; ?>

                                    <?php if ( !empty($settings['happy_customer_text']) ) : ?>
                                    <p class="happy-customer-el-text"><?php echo rrdevs_kses( $settings['happy_customer_text'] ); ?></p>
                                    <?php endif; ?>
                                </div>

                                <span class="bar"></span>

                                <div class="banner__review__item d-flex flex-column">
                                    <?php if ( !empty($settings['customer_rating_number']) ) : ?>
                                    <h2><span class="rating"><?php echo rrdevs_kses( $settings['customer_rating_number'] ); ?></span></h2>
                                    <?php endif; ?>

                                    <div class="rating-wrapper d-flex flex-row">
                                        <?php if(!empty($settings['customer_rating_switch'])) : ?>
                                        <div class="rate">
                                            <span>
                                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12.9681 4.70793C12.8825 4.44465 12.649 4.25825 12.3738 4.23335L8.62048 3.8926L7.13714 0.419814C7.02762 0.164672 6.77843 0 6.50107 0C6.22371 0 5.97442 0.164672 5.8656 0.419814L4.38226 3.8926L0.62834 4.23335C0.353159 4.25875 0.120139 4.44515 0.0340334 4.70793C-0.0515761 4.9712 0.0274862 5.25997 0.235608 5.4425L3.07282 7.93034L2.23627 11.6148C2.17506 11.8857 2.28022 12.1659 2.505 12.3284C2.62583 12.4162 2.76778 12.46 2.91023 12.46C3.03265 12.46 3.15516 12.4275 3.26458 12.362L6.50107 10.4268L9.73697 12.362C9.97436 12.5038 10.2728 12.4909 10.4971 12.3284C10.7219 12.1659 10.8271 11.8857 10.7659 11.6148L9.92932 7.93034L12.7665 5.4425C12.9746 5.25997 13.0537 4.9718 12.9681 4.70793Z" fill="#3C66FA"/>
                                                </svg>
                                            </span>
                                            <span>
                                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12.9681 4.70793C12.8825 4.44465 12.649 4.25825 12.3738 4.23335L8.62048 3.8926L7.13714 0.419814C7.02762 0.164672 6.77843 0 6.50107 0C6.22371 0 5.97442 0.164672 5.8656 0.419814L4.38226 3.8926L0.62834 4.23335C0.353159 4.25875 0.120139 4.44515 0.0340334 4.70793C-0.0515761 4.9712 0.0274862 5.25997 0.235608 5.4425L3.07282 7.93034L2.23627 11.6148C2.17506 11.8857 2.28022 12.1659 2.505 12.3284C2.62583 12.4162 2.76778 12.46 2.91023 12.46C3.03265 12.46 3.15516 12.4275 3.26458 12.362L6.50107 10.4268L9.73697 12.362C9.97436 12.5038 10.2728 12.4909 10.4971 12.3284C10.7219 12.1659 10.8271 11.8857 10.7659 11.6148L9.92932 7.93034L12.7665 5.4425C12.9746 5.25997 13.0537 4.9718 12.9681 4.70793Z" fill="#3C66FA"/>
                                                </svg>
                                            </span>
                                            <span>
                                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12.9681 4.70793C12.8825 4.44465 12.649 4.25825 12.3738 4.23335L8.62048 3.8926L7.13714 0.419814C7.02762 0.164672 6.77843 0 6.50107 0C6.22371 0 5.97442 0.164672 5.8656 0.419814L4.38226 3.8926L0.62834 4.23335C0.353159 4.25875 0.120139 4.44515 0.0340334 4.70793C-0.0515761 4.9712 0.0274862 5.25997 0.235608 5.4425L3.07282 7.93034L2.23627 11.6148C2.17506 11.8857 2.28022 12.1659 2.505 12.3284C2.62583 12.4162 2.76778 12.46 2.91023 12.46C3.03265 12.46 3.15516 12.4275 3.26458 12.362L6.50107 10.4268L9.73697 12.362C9.97436 12.5038 10.2728 12.4909 10.4971 12.3284C10.7219 12.1659 10.8271 11.8857 10.7659 11.6148L9.92932 7.93034L12.7665 5.4425C12.9746 5.25997 13.0537 4.9718 12.9681 4.70793Z" fill="#3C66FA"/>
                                                </svg>
                                            </span>
                                            <span>
                                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12.9681 4.70793C12.8825 4.44465 12.649 4.25825 12.3738 4.23335L8.62048 3.8926L7.13714 0.419814C7.02762 0.164672 6.77843 0 6.50107 0C6.22371 0 5.97442 0.164672 5.8656 0.419814L4.38226 3.8926L0.62834 4.23335C0.353159 4.25875 0.120139 4.44515 0.0340334 4.70793C-0.0515761 4.9712 0.0274862 5.25997 0.235608 5.4425L3.07282 7.93034L2.23627 11.6148C2.17506 11.8857 2.28022 12.1659 2.505 12.3284C2.62583 12.4162 2.76778 12.46 2.91023 12.46C3.03265 12.46 3.15516 12.4275 3.26458 12.362L6.50107 10.4268L9.73697 12.362C9.97436 12.5038 10.2728 12.4909 10.4971 12.3284C10.7219 12.1659 10.8271 11.8857 10.7659 11.6148L9.92932 7.93034L12.7665 5.4425C12.9746 5.25997 13.0537 4.9718 12.9681 4.70793Z" fill="#3C66FA"/>
                                                </svg>
                                            </span>
                                            <span>
                                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path opacity="0.3" d="M12.9759 4.70793C12.8903 4.44465 12.6568 4.25825 12.3816 4.23335L8.62829 3.8926L7.14495 0.419814C7.03544 0.164672 6.78625 0 6.50888 0C6.23152 0 5.98223 0.164672 5.87341 0.419814L4.39007 3.8926L0.636152 4.23335C0.360972 4.25875 0.127951 4.44515 0.0418459 4.70793C-0.0437636 4.9712 0.0352987 5.25997 0.24342 5.4425L3.08064 7.93034L2.24408 11.6148C2.18288 11.8857 2.28803 12.1659 2.51282 12.3284C2.63364 12.4162 2.7756 12.46 2.91805 12.46C3.04046 12.46 3.16297 12.4275 3.27239 12.362L6.50888 10.4268L9.74478 12.362C9.98217 12.5038 10.2807 12.4909 10.505 12.3284C10.7297 12.1659 10.8349 11.8857 10.7737 11.6148L9.93713 7.93034L12.7743 5.4425C12.9824 5.25997 13.0615 4.9718 12.9759 4.70793Z" fill="#5B7486"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ( !empty($settings['customer_rating_text']) ) : ?>
                                        <p><?php echo rrdevs_kses( $settings['customer_rating_text'] ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Banner area end -->

    <?php elseif ( $settings['design_style']  == 'layout-2' ) : 
        if ( !empty($settings['weta_title_img_01']['url']) ) {
            $weta_title_img_01 = !empty($settings['weta_title_img_01']['id']) ? wp_get_attachment_image_url( $settings['weta_title_img_01']['id'], 'full') : $settings['weta_title_img_01']['url'];
            $weta_title_img_01_alt = get_post_meta($settings["weta_title_img_01"]["id"], "_wp_attachment_image_alt", true);
        }

        // Author Thumb 
        if ( !empty($settings['author_thumb_01']['url']) ) {
            $author_thumb_01 = !empty($settings['author_thumb_01']['id']) ? wp_get_attachment_image_url( $settings['author_thumb_01']['id'], 'full') : $settings['author_thumb_01']['url'];
            $author_thumb_01_alt = get_post_meta($settings["author_thumb_01"]["id"], "_wp_attachment_image_alt", true);
        }        

        if ( !empty($settings['author_thumb_02']['url']) ) {
            $author_thumb_02 = !empty($settings['author_thumb_02']['id']) ? wp_get_attachment_image_url( $settings['author_thumb_02']['id'], 'full') : $settings['author_thumb_02']['url'];
            $author_thumb_02_alt = get_post_meta($settings["author_thumb_02"]["id"], "_wp_attachment_image_alt", true);
        }        

        if ( !empty($settings['author_thumb_03']['url']) ) {
            $author_thumb_03 = !empty($settings['author_thumb_03']['id']) ? wp_get_attachment_image_url( $settings['author_thumb_03']['id'], 'full') : $settings['author_thumb_03']['url'];
            $author_thumb_03_alt = get_post_meta($settings["author_thumb_03"]["id"], "_wp_attachment_image_alt", true);
        }
    ?>

    <!-- Banner area start -->
    <section class="banner-2 banner-2__space overflow-hidden parallax-element">
        <div class="container container-xxl">
            <div class="banner-2__shape">
                <div class="banner-2__shape-1"></div>
                <div class="banner-2__shape-2"></div>
                <div class="banner-2__shape-3"></div>
                <div class="banner-2__shape-4"></div>
                <div class="banner-2__shape-5"></div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="banner-2__content">

                        <div class="banner-2__content-1">
                            <h2 class="banner-2__title">
                                <?php if ( !empty($settings['weta_title_text_01']) ) : ?>
                                    <?php echo rrdevs_kses($settings['weta_title_text_01' ]); ?> 
                                <?php endif; ?>

                                <?php if(!empty($weta_title_img_01)) : ?>
                                <span class="custom-img banner-2__image">
                                    <img src="<?php echo esc_url($weta_title_img_01); ?>" alt="<?php print esc_attr($weta_title_img_01_alt); ?>">
                                </span> 
                                <?php endif; ?>

                                <?php if ( !empty($settings['weta_title_text_02']) ) : ?>
                                <span class="opacity-10"><?php echo rrdevs_kses($settings['weta_title_text_02' ]); ?></span>
                                <?php endif; ?>
                            </h2>
                        </div>

                        <?php if ( !empty($settings['weta_title_text_03']) ) : ?>
                        <div class="banner-2__content-2">
                            <h2 class="banner-2__title-2"><?php echo rrdevs_kses($settings['weta_title_text_03' ]); ?></h2>
                        </div>
                        <?php endif; ?>

                        <div class="banner-2__content-3 d-flex flex-column-reverse flex-md-row align-items-md-center">
                            <div class="banner-2__content-profile d-flex align-items-center">
                                <div class="profile-img__wrapper d-flex">
                                    <?php if(!empty($author_thumb_01)) : ?>
                                    <div class="profile-img banner-2__image-1">
                                        <img src="<?php echo esc_url($author_thumb_01); ?>" alt="<?php print esc_attr($author_thumb_01_alt); ?>">
                                    </div>
                                    <?php endif; ?>

                                    <?php if(!empty($author_thumb_02)) : ?>
                                    <div class="profile-img banner-2__image-2">
                                        <img src="<?php echo esc_url($author_thumb_02); ?>" alt="<?php print esc_attr($author_thumb_02_alt); ?>">
                                    </div>
                                    <?php endif; ?>

                                    <?php if(!empty($author_thumb_03)) : ?>
                                    <div class="profile-img banner-2__image-3">
                                        <img src="<?php echo esc_url($author_thumb_03); ?>" alt="<?php print esc_attr($author_thumb_03_alt); ?>">
                                    </div>
                                    <?php endif; ?>

                                    <div class="profile-img-custom banner-2__image-4"></div>
                                </div>

                                <?php if ( !empty($settings['author_thumb_text']) ) : ?>
                                <span class="banner-2__title-4"><?php echo rrdevs_kses($settings['author_thumb_text' ]); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ( !empty($settings['weta_title_text_04']) ) : ?>
                            <h2 class="banner-2__title-3"><?php echo rrdevs_kses($settings['weta_title_text_04' ]); ?></h2>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Banner area end -->


    <?php elseif ( $settings['design_style']  == 'layout-3' ) : 
        if ( !empty($settings['hero_image']['url']) ) {
            $hero_image = !empty($settings['hero_image']['id']) ? wp_get_attachment_image_url( $settings['hero_image']['id'], 'full') : $settings['hero_image']['url'];
            $weta_image_alt = get_post_meta($settings["hero_image"]["id"], "_wp_attachment_image_alt", true);
        }        

        if ( !empty($settings['hero_circle_image']['url']) ) {
            $hero_circle_image = !empty($settings['hero_circle_image']['id']) ? wp_get_attachment_image_url( $settings['hero_circle_image']['id'], 'full') : $settings['hero_circle_image']['url'];
            $hero_circle_image_alt = get_post_meta($settings["hero_circle_image"]["id"], "_wp_attachment_image_alt", true);
        } 


        // Link
        if ('2' == $settings['weta_btn_link_type']) {
            $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_btn_page_link']));
            $this->add_render_attribute('weta-button-arg', 'target', '_self');
            $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
            $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-4 rr-primary-btn');
            $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.6s');
        } else {
            if ( ! empty( $settings['weta_btn_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button-arg', $settings['weta_btn_link'] );
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-4 rr-primary-btn');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '.6s');
            }
        }



        // Link 02
        if ('2' == $settings['weta_btn2_link_type']) {
            $this->add_render_attribute('weta-button2-arg', 'href', get_permalink($settings['weta_btn2_page_link']));
            $this->add_render_attribute('weta-button2-arg', 'target', '_self');
            $this->add_render_attribute('weta-button2-arg', 'rel', 'nofollow');
            $this->add_render_attribute('weta-button2-arg', 'class', 'schedule rr-btn-sec');
            $this->add_render_attribute('weta-button2-arg', 'data-wow-delay', '.7s');
        } else {
            if ( ! empty( $settings['weta_btn2_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button2-arg', $settings['weta_btn2_link'] );
                $this->add_render_attribute('weta-button2-arg', 'class', 'schedule rr-btn-sec');
                $this->add_render_attribute('weta-button2-arg', 'data-wow-delay', '.7s');
            }
        }
    ?>

    <!-- Banner area start -->
    <section class="banner-3 banner-3__space overflow-hidden theme-banner-3 parallax-element">
        <?php if(!empty($settings['hero_shape_switch'])) : ?>
        <div class="container container-xxl line-container">
            <div class="line-1"><span></span></div>
            <div class="line-2"><span></span></div>
        </div>
        <?php endif; ?>

        <div class="container container-xxl">
            <?php if(!empty($hero_circle_image)) : ?>
            <div class="banner-3__shape wow fadeIn animated" data-wow-delay=".2s">
                <img src="<?php echo esc_url($hero_circle_image); ?>" alt="<?php print esc_attr($hero_circle_image_alt); ?>">
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-7">
                    <div class="banner-3__content">
                        <?php if(!empty( $settings['weta_title_03'] )) : ?>
                        <h1 class="banner-3__title mb-10 wow fadeIn animated weta-el-title" data-wow-delay=".1s"><?php echo rrdevs_kses( $settings['weta_title_03'] ); ?></h1>
                        <?php endif; ?>

                        <?php if(!empty( $settings['weta_desc_03'] )) : ?>
                        <p class="mb-20 mb-xs-15 wow fadeIn animated weta-el-desc" data-wow-delay=".3s"><?php echo rrdevs_kses( $settings['weta_desc_03'] ); ?></p>
                        <?php endif; ?>

                        <div class="rr-btn__wrapper d-flex flex-wrap align-items-center mt-40 mt-sm-35 mt-xs-30 wow fadeIn animated" data-wow-delay=".5s">
                            <?php if(!empty( $settings['weta_btn_text'] )) : ?>
                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                <span class="btn-wrap">
                                    <span class="text-one"><?php echo $settings['weta_btn_text']; ?></span>
                                    <span class="text-two"><?php echo $settings['weta_btn_text']; ?></span>
                                </span>
                            </a>
                            <?php endif; ?>

                            <?php if(!empty( $settings['weta_btn2_text'] )) : ?>
                            <a <?php echo $this->get_render_attribute_string( 'weta-button2-arg' ); ?>>
                                <?php echo $settings['weta_btn2_text']; ?> <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 7H15" stroke="#010915" stroke-opacity="0.24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 1L15 7L9 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if(!empty($hero_image)) : ?> 
                <div class="col-lg-5">
                    <div class="banner-3__media wow fadeIn animated" data-wow-delay=".3s">
                        <img src="<?php echo esc_url($hero_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- Banner area end -->   


    <?php endif; ?>
		<?php
	}
}

$widgets_manager->register( new WETA_Hero() );