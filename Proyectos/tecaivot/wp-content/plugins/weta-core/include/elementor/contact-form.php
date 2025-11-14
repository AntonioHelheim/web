<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Border;
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
class WETA_Contact_Form extends Widget_Base {

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
		return 'weta_contact_form';
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
		return __( 'Contact Form', 'weta-core' );
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


    public function get_weta_contact_form(){
        if ( ! class_exists( 'WPCF7' ) ) {
            return;
        }
        $weta_cfa         = array();
        $weta_cf_args     = array( 'posts_per_page' => -1, 'post_type'=> 'wpcf7_contact_form' );
        $weta_forms       = get_posts( $weta_cf_args );
        $weta_cfa         = ['0' => esc_html__( 'Select Form', 'weta-core' ) ];
        if( $weta_forms ){
            foreach ( $weta_forms as $weta_form ){
                $weta_cfa[$weta_form->ID] = $weta_form->post_title;
            }
        }else{
            $weta_cfa[ esc_html__( 'No contact form found', 'weta-core' ) ] = 0;
        }
        return $weta_cfa;
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
                ],
                'default' => 'layout-1',
            ]
        );

        $this->add_control(
            'bg_image',
            [
                'label' => esc_html__( 'Choose Image', 'text-domain' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->end_controls_section();

        // Title & Content
		$this->start_controls_section(
            'weta_section_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
            ]
        );

        $this->add_control(
            'weta_subtitle',
            [
                'label' => esc_html__('Sub Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'connect with us', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ],
        );        

        $this->add_control(
            'weta_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Get in touch', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Contact Form 
        $this->start_controls_section(
            'weta_contact',
            [
                'label' => esc_html__('Contact Form', 'weta-core'),
            ]
        );
        $this->add_control(
            'weta_select_contact_form',
            [
                'label'   => esc_html__( 'Select Form', 'weta-core' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '0',
                'options' => $this->get_weta_contact_form(),
            ]
        );

        $this->end_controls_section();

        // Contact Info 
        $this->start_controls_section(
            'weta_contact_info_list_content',
            [
                'label' => esc_html__('Contact Info List', 'weta-core'),
            ]
        );

        $this->add_control(
            'contact_info_title',
            [
                'label' => esc_html__( 'Title', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Get In Touch', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'contact_info_description',
            [
                'label' => esc_html__( 'Description', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'have questions about implementation need guidance specific aspect feel free share more can provide relevant information.', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'weta_contact_info_type',
            [
                'label' => esc_html__('Info Type', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'phone' => esc_html__( 'Phone', 'weta-core' ),
                    'email' => esc_html__( 'Email', 'weta-core' ),
                    'address' => esc_html__( 'Address', 'weta-core' ),
                ],
                'default' => 'address',
            ]
        );

        

        $repeater->add_control(
            'weta_contact_info_switch',
            [
                'label' => esc_html__( 'Image Type', 'weta-core' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'image' => esc_html__( 'Image', 'weta-core' ),
                    'icon' => esc_html__( 'Icon', 'weta-core' ),
                ],
            ]
        );

        $repeater->add_control(
            'weta_contact_info_image_icon',
            [
                'label' => esc_html__('Upload Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'weta_contact_info_switch' => 'image',
                ],
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'weta_contact_info_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'eicon-user-circle-o',
                    'condition' => [
                        'weta_contact_info_switch' => 'icon',
                    ],
                ]
            );
        } else {
            $repeater->add_control(
                'weta_contact_info_selected_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'eicon-user-circle-o',
                        'library' => 'solid',
                    ],
                    'condition' => [
                        'weta_contact_info_switch' => 'icon',
                    ],
                ]
            );
        }

        $repeater->add_control(
            'contact_info_phone_label', [
                'label' => esc_html__('Phone Label', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'phone' ],
                ],
            ]
        );

        $repeater->add_control(
            'contact_info_phone', [
                'label' => esc_html__('Phone', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'phone' ],
                ],
            ]
        );

        $repeater->add_control(
            'contact_info_phone_url', [
                'label' => esc_html__('Phone URL', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'phone' ],
                ],
            ]
        );

        $repeater->add_control(
            'contact_info_email_label', [
                'label' => esc_html__('Email Label', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'email' ],
                ],
            ]
        );

        $repeater->add_control(
            'contact_info_email', [
                'label' => esc_html__('Email', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'email' ],
                ],
            ]
        );

        $repeater->add_control(
            'contact_info_address_label', [
                'label' => esc_html__('Address Label', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'address' ],
                ],
            ]
        );

        $repeater->add_control(
            'contact_info_address', [
                'label' => esc_html__('Address', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'address' ],
                ],
            ]
        );

        $repeater->add_control(
            'contact_info_address_url', [
                'label' => esc_html__('Address URL', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_contact_info_type' => [ 'address' ],
                ],
            ]
        );
     
        $this->add_control(
            'contact_info_list',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'weta_contact_info_type' => 'phone',
                        'contact_info_phone_label' => esc_html__('CALL US AT', 'weta-core'),
                        'contact_info_phone' => esc_html__('+(888) 364 985 35', 'weta-core'),
                        'contact_info_phone_url' => '+(88836498535',
                    ],
                    [
                        'weta_contact_info_type' => 'email',
                        'contact_info_email_label' => esc_html__('EMAIL US ON', 'weta-core'),
                        'contact_info_email' => 'example@gmail.com',
                    ],                    
                    [
                        'weta_contact_info_type' => 'address',
                        'contact_info_address_label' => esc_html__('FIND US', 'weta-core'),
                        'contact_info_address' => esc_html__('GV25+G6 London, UK', 'weta-core'),
                        'contact_info_address_url' => 'https://maps.app.goo.gl/z8EPw8zWqqzP3R1x8',
                    ],
                ],
            ]
        );


        $this->end_controls_section();



        // Style Control 
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
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            'design_layout_background_two',
            [
                'label' => __( 'Background Two', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            '_heading_style_inner_layout',
            [
                'label' => esc_html__( 'Inner Layout', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'design_inner_layout_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-us__box' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'design_inner_layout_box_shadow',
                'selector' => '{{WRAPPER}} .contact-us__box',
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
            '_section_style_content',
            [
                'label' => __( 'Title / Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

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
                    '{{WRAPPER}} .section-2__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',             
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-2__title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .section-2__title',
            ]
        );

        $this->add_control(
            '_heading_subtitle',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subtitle', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_responsive_control(
            'subtitle_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section-2__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',               
                ],
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-2__subtitle' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_control(
            'subtitle_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-2__subtitle' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .section-2__subtitle',
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ]
        );

        $this->end_controls_section();

        // Contact Form 
		$this->start_controls_section(
			'weta_contact_style',
			[
				'label' => __( 'Contact Form', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            'input_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .contact-form .form-control' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .subscribe-wrap .subscribe-content .subscribe-form .form-control' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        // Input
        $this->add_control(
            '_content_input',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Input', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->start_controls_tabs( '_form_input_tabs' );
        
        $this->start_controls_tab(
            'form_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'input_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-us__form input::placeholder' => 'color: {{VALUE}} !important',
                    '{{WRAPPER}} .contact-us__form textarea::placeholder' => 'color: {{VALUE}} !important',
                    '{{WRAPPER}} .contact-us__form input' => 'color: {{VALUE}} !important',
                    '{{WRAPPER}} .contact-us__form textarea' => 'color: {{VALUE}} !important',
                ],
            ]
        );

		$this->add_control(
            'input_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-us__form input' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .contact-us__form textarea' => 'background-color: {{VALUE}}',
                ],
            ]
        );

		$this->add_control(
            'input_border_color',
            [
                'label'     => esc_html__( 'Border', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-us__form input' => 'border-color: {{VALUE}}',
                    '{{WRAPPER}} .contact-us__form textarea' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'form_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );

        $this->add_control(
            'input_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-us__form input:hover::placeholder' => 'color: {{VALUE}} !important',
                    '{{WRAPPER}} .contact-us__form textarea:hover::placeholder' => 'color: {{VALUE}} !important',
                ],
            ]
        );


		$this->add_control(
            'input_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-us__form input:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .contact-us__form textarea:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

		$this->add_control(
            'input_border_color_hover',
            [
                'label'     => esc_html__( 'Border', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-us__form input:hover' => 'border-color: {{VALUE}}',
                    '{{WRAPPER}} .contact-us__form textarea:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();
        
        $this->end_controls_tabs();

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
                    '{{WRAPPER}} .weta-el-button .btn-wrap .text-one' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .weta-el-button .btn-wrap .text-two' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .weta-el-button .btn-wrap .text-one svg path' => 'fill: {{VALUE}}',
                    '{{WRAPPER}} .weta-el-button .btn-wrap .text-two svg path' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .weta-el-button',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .weta-el-button',
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
                    '{{WRAPPER}} .weta-el-button:hover .btn-wrap .text-one' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .weta-el-button:hover .btn-wrap .text-one svg path' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .weta-el-button:hover .btn-wrap .text-two' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .weta-el-button:hover .btn-wrap .text-two svg path' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .weta-el-button:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border_hover',
                'selector' => '{{WRAPPER}} .weta-el-button:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .weta-el-button:hover',
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
                    '{{WRAPPER}} .weta-el-button' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .weta-el-button',
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .weta-el-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .weta-el-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Contact Info List
        $this->start_controls_section(
            'weta_contact_list_content_style',
            [
                'label' => __( 'Contact Info List Style', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'content_padding_contact_info',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .contact-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'contact_info_content_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-box' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'contact_info_list_bottom_spacing',
            [
                'label' => __( 'Contact List Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .contact-box:not(:last-of-type)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        // Contact Info icon
        $this->add_control(
            '_contact_info_icon_text_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Contact Info Icon', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'contact_info_icon_text_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-box .icon' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'contact_info_icon_content_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-box .icon' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'contact_info_icon_text_typography',
                'selector' => '{{WRAPPER}} .contact-box .icon',
            ]
        );


        // Contact Info 
        $this->add_control(
            '_contact_info_text_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Contact Info Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'contact_info_text_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .contact-box .content .title' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'contact_info_text_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-box .content .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'contact_info_text_typography',
                'selector' => '{{WRAPPER}} .contact-box .content .title',
            ]
        );


        // Contact Info Label
        $this->add_control(
            '_contact_info_text_label_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Contact Info Text Label', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'contact_info_text_label_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .contact-box .content .contact-info span' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'contact_info_label_text_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-box .content .contact-info span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'contact_info_label_text_typography',
                'selector' => '{{WRAPPER}} .contact-box .content .contact-info span',
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

    <?php if ( $settings['weta_design_style']  == 'layout-1' ) : 
         
        if ( !empty($settings['bg_image']['url']) ) {
            $bg_image = !empty($settings['bg_image']['id']) ? wp_get_attachment_image_url( $settings['bg_image']['id'], 'full') : $settings['bg_image']['url'];
            $bg_image_alt = get_post_meta($settings["bg_image"]["id"], "_wp_attachment_image_alt", true);
        } 
    ?>

    <section class="weta-el-section contact-us contact-us__background section-space__contact section-space-bottom position-relative z-0 overflow-hidden">
        <div class="container container-xxl">
            <div class="row">
                <div class="col-12">
                    <div class="contact-us__box d-flex flex-xl-row flex-column align-items-center justify-content-between">
                        <div class="contact-us__form-content">
                            <div class="section-2__title-wrapper contact-us__content mb-45 mb-sm-30 mb-xs-20">
                                <?php if(!empty($settings['weta_subtitle'])): ?>
                                    <span class="section-2__subtitle justify-content-start mb-15 mb-xs-5 section-subTile-2-animation">
                                        <?php echo $settings['weta_subtitle']; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if(!empty($settings['weta_title'])): ?>
                                    <h2 class="section-2__title xl text-uppercase section-title-2-animation">
                                        <?php echo $settings['weta_title']; ?>
                                    </h2>
                                <?php endif; ?>
                            </div>

                            <?php if( !empty($settings['weta_select_contact_form']) ) : ?>
                                <?php echo do_shortcode( '[contact-form-7 id="'.$settings['weta_select_contact_form'].'"]' ); ?>
                            <?php else : ?>
                                <?php echo '<div class="alert alert-info"><p class="m-0">' . __('Please Select contact form.', 'weta-core' ). '</p></div>'; ?>
                            <?php endif; ?>

                        </div>
                        <?php if(!empty($settings['bg_image'])) : ?>
                            <div class="contact-us__media contact-us__media-animation">
                                <img src="<?php print esc_url($bg_image); ?>" alt="<?php print esc_attr($weta_author_image_alt); ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>  

    <?php elseif ( $settings['weta_design_style']  == 'layout-2' ) : ?>

        <section class="contact-us-1 section-space overflow-hidden">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="contact-us-1__form-wrapper d-flex flex-column flex-lg-row justify-content-between">
                            <div class="contact-us-1__form-left">
                                <h3 class="contact-us-1__form-title mb-35 mb-xs-30"><?php print $settings['contact_info_title'] ?></h3>

                                <div class="contact-us-1__form-item-list d-flex flex-column">
                                    <?php foreach ($settings['contact_info_list'] as $item) : ?>
                                        <div class="contact-us-1__form-item d-flex align-items-center">
                                            <?php if ( $item['weta_contact_info_switch']  == 'image' ): ?>
                                                <div class="icon">
                                                    <img src="<?php echo $item['weta_contact_info_image_icon']['url']; ?>" alt="<?php echo get_post_meta(attachment_url_to_postid($item['weta_contact_info_image_icon']['url']), '_wp_attachment_image_alt', true); ?>">
                                                </div>
                                            <?php elseif ( $item['weta_contact_info_switch']  == 'icon' ): ?>
                                                <?php if (!empty($item['weta_contact_info_icon']) || !empty($item['weta_contact_info_selected_icon']['value'])) : ?>
                                                    <div class="icon">
                                                        <?php weta_render_icon($item, 'weta_contact_info_icon', 'weta_contact_info_selected_icon'); ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ( $item['weta_contact_info_type']  == 'phone' ): ?>
                                                <div class="text">
                                                    <span><?php echo weta_kses($item['contact_info_phone_label' ]); ?></span>
                                                    <a href="tel:<?php echo esc_attr($item['contact_info_phone_url' ]); ?>">
                                                        <?php echo weta_kses($item['contact_info_phone' ]); ?>
                                                    </a>
                                                </div>
                                            <?php elseif ( $item['weta_contact_info_type']  == 'email' ): ?>
                                                <div class="text">
                                                    <span><?php echo weta_kses($item['contact_info_email_label' ]); ?></span>
                                                    <a href="mailto:<?php echo esc_attr($item['contact_info_email' ]); ?>">
                                                        <?php echo weta_kses($item['contact_info_email' ]); ?>
                                                    </a>
                                                </div>
                                            <?php elseif ( $item['weta_contact_info_type']  == 'address' ): ?>
                                                <div class="text">
                                                    <span><?php echo weta_kses($item['contact_info_address_label' ]); ?></span>
                                                    <a href="https://maps.app.goo.gl/z8EPw8zWqqzP3R1x8">
                                                        <?php echo weta_kses($item['contact_info_address' ]); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ( !empty ($settings['contact_info_description']) ) : ?>
                                    <p class="mb-0 mt-35 mt-xs-30">
                                        <?php print esc_html($settings['contact_info_description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="contact-us-1__form-right">
                                <?php if(!empty($settings['weta_title'])): ?>
                                    <h3 class="contact-us-1__form-title mb-35 mb-xs-30">
                                        <?php echo rrdevs_kses($settings['weta_title']); ?>
                                    </h3>
                                <?php endif; ?>

                                <?php if( !empty($settings['weta_select_contact_form']) ) : ?>
                                    <?php echo do_shortcode( '[contact-form-7 id="'.$settings['weta_select_contact_form'].'"]' ); ?>
                                <?php else : ?>
                                    <?php echo '<div class="alert alert-info"><p class="m-0">' . __('Please Select contact form.', 'weta-core' ). '</p></div>'; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <?php endif;

	}
}

$widgets_manager->register( new WETA_Contact_Form() ); 