<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Cta extends Widget_Base {

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
		return 'weta_cta';
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
		return __( 'CTA', 'weta-core' );
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
            'weta_cta_content',
            [
                'label' => esc_html__('CTA', 'weta-core'),
            ]
        );
        
        $this->add_control(
            'design_style',
            [
                'label' => esc_html__( 'Select Layout', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__( 'Layout 1', 'weta-core' ),
                    'layout-2' => esc_html__( 'Layout 2', 'weta-core' ),
                    'layout-3' => esc_html__( 'Layout 3', 'weta-core' ),
                    'layout-4' => esc_html__( 'Layout 4', 'weta-core' ),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->add_control(
            'weta_subheading',
            [
                'label' => esc_html__( 'Subheading', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Have Query?', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'DO YOU HAVE ANY QUESTIONS?', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_image',
            [
                'label' => esc_html__( 'Choose Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'weta_switch',
            [
                'label' => esc_html__( 'Shape ON/OFF', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3', 'layout-4' ],
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_button',
            [
                'label' => esc_html__( 'Button',  'weta-core'  ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'weta_button_text',
            [
                'label' => esc_html__( 'Button Text', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Button Text', 'weta-core' ),
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'weta_button_link_type',
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
            'weta_button_link',
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
                    'weta_button_link_type' => '1',
                ],
                'label_block' => true,
            ]
        );
        $this->add_control(
            'weta_button_page_link',
            [
                'label' => esc_html__( 'Select Button Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_button_link_type' => '2',
                ]
            ]
        );

        $this->add_control(
            '_heading_content_button_two',
            [
                'label' => esc_html__( 'Button Two', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3', 'layout-4' ],
                ],
            ]
        );
        
        $this->add_control(
            'weta_button_text_two',
            [
                'label' => esc_html__( 'Button Text', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Button Text', 'weta-core' ),
                'label_block' => true,
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3', 'layout-4' ],
                ],
            ]
        );
        
        $this->add_control(
            'weta_button_link_type_two',
            [
                'label' => esc_html__( 'Button Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'label_block' => true,
                'condition' => [
                    'design_style' => [ 'layout-2', 'layout-3', 'layout-4' ],
                ],
            ]
        );
        
        $this->add_control(
            'weta_button_link_two',
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
                    'design_style' => [ 'layout-2', 'layout-3', 'layout-4' ],
                    'weta_button_link_type_two' => '1',
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_button_page_link_two',
            [
                'label' => esc_html__( 'Select Button Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_button_link_type_two' => '2',
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
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .cta__wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cta-2__wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

		$this->add_responsive_control(
            'design_layout_margin',
            [
                'label' => __( 'Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .cta__wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cta-2__wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'design_layout_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .cta__wrapper, .cta-2__background',
            ]
        );

		$this->end_controls_section();

		$this->start_controls_section(
			'weta_cta_style',
			[
				'label' => __( 'CTA', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            '_heading_subheading',
            [
                'label' => esc_html__( 'Subheading', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'subheading_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-subheading' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subheading_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-subheading' => 'color: {{VALUE}}',
                ],
            ]
        ); 

        $this->add_control(
            'subheading_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-subheading' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'subheading_padding',
            [
                'label' => esc_html__( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-subheading' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'subheading_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-subheading' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'subheading_typography',
                'selector' => '{{WRAPPER}} .weta-el-subheading',
            ]
        );

        $this->add_control(
            '_heading_title',
            [
                'label' => esc_html__( 'Title', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
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
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-title' => 'color: {{VALUE}}',
                ],
            ]
        ); 

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .weta-el-title',
            ]
        );

		$this->end_controls_section();

        $this->start_controls_section(
            '_style_button',
            [
                'label' => esc_html__( 'Button', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_style_primary',
            [
                'label' => esc_html__( 'Primary Button', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
            ]
        );
        
        $this->start_controls_tabs( 'tabs_style_primary_button' );
        
        $this->start_controls_tab(
            'primary_button_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'primary_button_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button .text-one'    => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'primary_button_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'primary_button_border',
                'selector' => '{{WRAPPER}} .weta-el-button',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'primary_button_box_shadow',
                'selector' => '{{WRAPPER}} .weta-el-button',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'primary_button_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'primary_button_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button:hover .text-two' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'primary_button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button:after, .weta-el-button:before' => 'background-color: {{VALUE}}!important',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'primary_button_border_hover',
                'selector' => '{{WRAPPER}} .weta-el-button:hover',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'primary_button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .weta-el-button:hover',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_control(
            'primary_button_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .weta-el-button:before' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'primary_button_typography',
                'selector' => '{{WRAPPER}} .weta-el-button',
            ]
        );
        
        $this->add_control(
            'primary_button_padding',
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
            'primary_button_margin',
            [
                'label'      => esc_html__( 'Margin', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .weta-el-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            '_heading_style_secondary',
            [
                'label' => esc_html__( 'Secondary Button', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->start_controls_tabs( 'tabs_style_secondary_button' );
        
        $this->start_controls_tab(
            'secondary_button_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'secondary_button_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button-two .text-one'    => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'secondary_button_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button-two' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'secondary_button_border',
                'selector' => '{{WRAPPER}} .weta-el-button-two',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'secondary_button_box_shadow',
                'selector' => '{{WRAPPER}} .weta-el-button-two',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'secondary_button_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'secondary_button_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button-two:hover .text-two' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'secondary_button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button-two:after, .weta-el-button-two:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'secondary_button_border_hover',
                'selector' => '{{WRAPPER}} .weta-el-button-two:hover',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'secondary_button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .weta-el-button-two:hover',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_control(
            'secondary_button_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-button-two' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .weta-el-button-two:before' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'secondary_button_typography',
                'selector' => '{{WRAPPER}} .weta-el-button-two',
            ]
        );
        
        $this->add_control(
            'secondary_button_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .weta-el-button-two' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'secondary_button_margin',
            [
                'label'      => esc_html__( 'Margin', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .weta-el-button-two' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
      
        if ( !empty($settings['weta_image']['url']) ) {
            $weta_image = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'], 'full') : $settings['weta_image']['url'];
            $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
        }

		?>

        <?php if ( $settings['design_style']  == 'layout-1' ): 
            
            if ('2' == $settings['weta_button_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_button_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-1 section-button-animation');
            } else {
                if ( ! empty( $settings['weta_button_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_button_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-1 section-button-animation');
                }
            }
            
            ?>
        
            <section class="cta__area cta__footer-up position-relative z-2">
                <div class="container container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="cta__wrapper theme-bg-3">
                                <div class="cta__content mb-md-40 mb-sm-35 mb-xs-30">
                                    <div class="section-2__title-wrapper mb-45 mb-md-10 mb-sm-10 mb-xs-10">
                                        <?php if(!empty($settings['weta_subheading'])) : ?>
                                            <span class="weta-el-subheading section-2__subtitle justify-content-start mb-15 mb-xs-5 section-subTile-2-animation">
                                                <?php echo rrdevs_kses($settings['weta_subheading']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(!empty($settings['weta_title'])) : ?>
                                            <h2 class="weta-el-title section-2__title text-uppercase section-title-2-animation">
                                                <?php echo rrdevs_kses($settings['weta_title']); ?>
                                            </h2>
                                        <?php endif; ?>
                                    </div>

                                    <?php if(!empty($settings['weta_button_text'])) : ?>
                                        <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                            <span class="btn-wrap">
                                                <span class="text-one">
                                                    <?php echo $settings['weta_button_text']; ?>
                                                    <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M4.38575 8.93578H4.03817L4.27661 9.18868L8.61139 13.7864C8.61139 13.7864 8.6114 13.7864 8.61141 13.7864C8.69179 13.8717 8.75598 13.9735 8.79993 14.086C8.84388 14.1986 8.8666 14.3196 8.8666 14.442C8.8666 14.5643 8.84388 14.6853 8.79993 14.7979C8.75598 14.9105 8.69177 15.0122 8.61139 15.0975C8.53102 15.1828 8.43609 15.2499 8.33227 15.2956C8.22848 15.3412 8.11758 15.3645 8.00582 15.3645C7.89406 15.3645 7.78316 15.3412 7.67937 15.2956C7.57556 15.2499 7.48063 15.1828 7.40026 15.0975L1.34324 8.67011L1.34311 8.66997C1.26262 8.58476 1.19831 8.48307 1.15429 8.37049C1.11026 8.2579 1.0875 8.13692 1.0875 8.01455C1.0875 7.89217 1.11026 7.77119 1.15429 7.6586C1.19831 7.54603 1.26262 7.44433 1.34311 7.35912L1.34324 7.35898L7.40026 0.931574C7.56247 0.759446 7.7805 0.664545 8.00582 0.664545C8.23115 0.664545 8.44918 0.759446 8.61139 0.931574C8.77388 1.104 8.8666 1.33967 8.8666 1.58713C8.8666 1.83459 8.77389 2.07024 8.61141 2.24267C8.6114 2.24268 8.61139 2.24269 8.61139 2.2427L4.27661 6.84041L4.03817 7.09331H4.38575L16.0818 7.09331C16.3068 7.09331 16.5245 7.18807 16.6865 7.35994C16.8488 7.53212 16.9413 7.76744 16.9413 8.01455C16.9413 8.26165 16.8488 8.49698 16.6865 8.66915C16.5245 8.84102 16.3068 8.93578 16.0818 8.93578H4.38575Z" fill="#010915" stroke="#F3B351" stroke-width="0.3"/>
                                                    </svg>
                                                </span>

                                                <span class="text-two">
                                                    <?php echo $settings['weta_button_text']; ?>
                                                    <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M4.38575 8.93578H4.03817L4.27661 9.18868L8.61139 13.7864C8.61139 13.7864 8.6114 13.7864 8.61141 13.7864C8.69179 13.8717 8.75598 13.9735 8.79993 14.086C8.84388 14.1986 8.8666 14.3196 8.8666 14.442C8.8666 14.5643 8.84388 14.6853 8.79993 14.7979C8.75598 14.9105 8.69177 15.0122 8.61139 15.0975C8.53102 15.1828 8.43609 15.2499 8.33227 15.2956C8.22848 15.3412 8.11758 15.3645 8.00582 15.3645C7.89406 15.3645 7.78316 15.3412 7.67937 15.2956C7.57556 15.2499 7.48063 15.1828 7.40026 15.0975L1.34324 8.67011L1.34311 8.66997C1.26262 8.58476 1.19831 8.48307 1.15429 8.37049C1.11026 8.2579 1.0875 8.13692 1.0875 8.01455C1.0875 7.89217 1.11026 7.77119 1.15429 7.6586C1.19831 7.54603 1.26262 7.44433 1.34311 7.35912L1.34324 7.35898L7.40026 0.931574C7.56247 0.759446 7.7805 0.664545 8.00582 0.664545C8.23115 0.664545 8.44918 0.759446 8.61139 0.931574C8.77388 1.104 8.8666 1.33967 8.8666 1.58713C8.8666 1.83459 8.77389 2.07024 8.61141 2.24267C8.6114 2.24268 8.61139 2.24269 8.61139 2.2427L4.27661 6.84041L4.03817 7.09331H4.38575L16.0818 7.09331C16.3068 7.09331 16.5245 7.18807 16.6865 7.35994C16.8488 7.53212 16.9413 7.76744 16.9413 8.01455C16.9413 8.26165 16.8488 8.49698 16.6865 8.66915C16.5245 8.84102 16.3068 8.93578 16.0818 8.93578H4.38575Z" fill="#010915" stroke="#F3B351" stroke-width="0.3"/>
                                                    </svg>
                                                </span>
                                            </span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="cta__media cta__media-animation">
                                    <img src="<?php print esc_url($weta_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        
        <?php elseif ( $settings['design_style']  == 'layout-2' ): 
            
            if ('2' == $settings['weta_button_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_button_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-2');
            } else {
                if ( ! empty( $settings['weta_button_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_button_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-2');
                }
            }

            if ('2' == $settings['weta_button_link_type_two']) {
                $this->add_render_attribute('weta-button-arg-two', 'href', get_permalink($settings['weta_button_page_link_two']));
                $this->add_render_attribute('weta-button-arg-two', 'target', '_self');
                $this->add_render_attribute('weta-button-arg-two', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg-two', 'class', 'weta-el-button-two rr-btn rr-btn__theme-2 rr-btn__theme-2_black');
            } else {
                if ( ! empty( $settings['weta_button_link_two']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg-two', $settings['weta_button_link_two'] );
                    $this->add_render_attribute('weta-button-arg-two', 'class', 'weta-el-button-two rr-btn rr-btn__theme-2 rr-btn__theme-2_black');
                }
            }
            
            ?>

            <section class="cta-2__wrapper footer-up">
                <div class="container container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="cta-2__wrapper d-flex flex-xl-row flex-column align-items-center cta-2__background parallax-element">
                                <div class="cta-2__shape">
                                    <?php if ( !empty ($settings['weta_switch']) ) :  ?>
                                        <svg width="259" height="329" viewBox="0 0 259 329" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.01"><g class="leftRight">
                                            <path d="M259 283.505C254.365 287.188 249.951 286.698 246.73 286.344C243.84 286.026 242.484 285.943 240.645 287.401C238.811 288.859 238.59 290.188 238.275 293.057C237.923 296.25 237.44 300.625 232.805 304.307C228.17 307.99 223.756 307.5 220.535 307.146C217.645 306.828 216.289 306.745 214.45 308.203C212.616 309.661 212.395 310.99 212.08 313.854C211.728 317.047 211.244 321.422 206.61 325.104C201.975 328.786 197.561 328.297 194.34 327.943C191.45 327.625 190.094 327.542 188.255 329L183 322.495C187.635 318.813 192.049 319.302 195.27 319.656C198.16 319.974 199.516 320.057 201.355 318.599C203.189 317.141 203.41 315.813 203.725 312.948C204.077 309.755 204.56 305.38 209.195 301.698C213.83 298.016 218.244 298.505 221.465 298.859C224.355 299.177 225.711 299.26 227.55 297.802C229.389 296.344 229.605 295.016 229.92 292.146C230.272 288.953 230.756 284.578 235.39 280.896C240.025 277.214 244.439 277.703 247.66 278.057C250.55 278.375 251.906 278.458 253.745 277L259 283.505Z" fill="#EFECFF"/>
                                            <path d="M253.467 276.391C254.875 278.192 254.612 280.884 252.503 281.769C249.247 283.134 246.246 282.791 243.892 282.523C241.039 282.2 239.702 282.115 237.887 283.601C236.077 285.087 235.859 286.441 235.548 289.366C235.2 292.62 234.723 297.079 230.15 300.832C225.576 304.586 221.22 304.087 218.041 303.726C215.189 303.402 213.851 303.317 212.036 304.803C210.226 306.29 210.009 307.643 209.697 310.563C209.35 313.817 208.873 318.276 204.299 322.029C199.725 325.782 195.369 325.283 192.191 324.922C191.423 324.835 190.765 324.765 190.177 324.739C187.818 324.632 184.988 324.469 183.533 322.609C182.125 320.808 182.388 318.116 184.497 317.231C187.753 315.866 190.754 316.209 193.109 316.477C195.961 316.8 197.298 316.885 199.113 315.399C200.923 313.913 201.141 312.559 201.452 309.639C201.8 306.385 202.277 301.926 206.85 298.173C211.424 294.42 215.78 294.919 218.959 295.28C221.811 295.604 223.149 295.689 224.964 294.202C226.779 292.716 226.992 291.362 227.303 288.437C227.65 285.183 228.127 280.724 232.701 276.971C237.275 273.218 241.631 273.717 244.809 274.078C245.577 274.165 246.235 274.235 246.823 274.261C249.182 274.368 252.012 274.531 253.467 276.391Z" fill="url(#paint0_linear_187_3327)" fill-opacity="0.5"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                            <circle cx="6" cy="6" r="6" fill="#FBD95E"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                            <circle cx="50" cy="32" r="4" fill="#7FFA54"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                            <circle cx="18" cy="51" r="2" fill="white"/>
                                            </g></g></g>
                                            <defs>
                                                <linearGradient id="paint0_linear_187_3327" x1="218.5" y1="273" x2="218.5" y2="326" gradientUnits="userSpaceOnUse">
                                                    <stop stop-color="#9888F4" offset="0%"/>
                                                    <stop offset="1" stop-color="#907EF3"/>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="cta-2__content">
                                    <?php if(!empty($settings['weta_subheading'])) : ?>
                                        <span class="weta-el-subheading subtitle mb-15 mb-xs-10 wow fadeIn animated" data-wow-delay=".1s">
                                            <?php echo rrdevs_kses($settings['weta_subheading']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(!empty($settings['weta_title'])) : ?>
                                        <h2 class="weta-el-title color-white mb-30 mb-xs-20 wow fadeIn animated" data-wow-delay=".3s">
                                            <?php echo rrdevs_kses($settings['weta_title']); ?>
                                        </h2>
                                    <?php endif; ?>

                                    <div class="rr-btn__wrapper d-flex flex-wrap align-items-center wow fadeIn animated" data-wow-delay=".5s">
                                        <?php if(!empty($settings['weta_button_text'])) : ?>
                                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                                <span class="btn-wrap">
                                                    <span class="text-one"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/apple-black.png" alt="">
                                                        <?php echo $settings['weta_button_text']; ?>
                                                    </span>
                                                    <span class="text-two"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/apple-black.png" alt="">
                                                        <?php echo $settings['weta_button_text']; ?>
                                                    </span>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if(!empty($settings['weta_button_text_two'])) : ?>
                                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg-two' ); ?>>
                                                <span class="btn-wrap">
                                                    <span class="text-one"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/android.png" alt="">
                                                        <?php echo $settings['weta_button_text_two']; ?>
                                                    </span>
                                                    <span class="text-two"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/android.png" alt="">
                                                        <?php echo $settings['weta_button_text_two']; ?>
                                                    </span>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="cta-2__media wow fadeIn animated leftRight" data-wow-delay=".7s">
                                    <img src="<?php print esc_url($weta_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        
        <?php elseif ( $settings['design_style']  == 'layout-3' ): 
            
            if ('2' == $settings['weta_button_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_button_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-2');
            } else {
                if ( ! empty( $settings['weta_button_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_button_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-2');
                }
            }

            if ('2' == $settings['weta_button_link_type_two']) {
                $this->add_render_attribute('weta-button-arg-two', 'href', get_permalink($settings['weta_button_page_link_two']));
                $this->add_render_attribute('weta-button-arg-two', 'target', '_self');
                $this->add_render_attribute('weta-button-arg-two', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg-two', 'class', 'weta-el-button-two rr-btn rr-btn__theme-2 rr-btn__theme-2_black');
            } else {
                if ( ! empty( $settings['weta_button_link_two']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg-two', $settings['weta_button_link_two'] );
                    $this->add_render_attribute('weta-button-arg-two', 'class', 'weta-el-button-two rr-btn rr-btn__theme-2 rr-btn__theme-2_black');
                }
            }
            
            ?>

            <section class="cta-2__area footer-up">
                <div class="container container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="cta-2__wrapper d-flex flex-xl-row flex-column align-items-center cta-2__background parallax-element">
                                <?php if ( !empty ($settings['weta_switch']) ) :  ?>
                                    <div class="cta-2__shape">
                                        <svg width="259" height="329" viewBox="0 0 259 329" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.01"><g class="leftRight">
                                                <path d="M259 283.505C254.365 287.188 249.951 286.698 246.73 286.344C243.84 286.026 242.484 285.943 240.645 287.401C238.811 288.859 238.59 290.188 238.275 293.057C237.923 296.25 237.44 300.625 232.805 304.307C228.17 307.99 223.756 307.5 220.535 307.146C217.645 306.828 216.289 306.745 214.45 308.203C212.616 309.661 212.395 310.99 212.08 313.854C211.728 317.047 211.244 321.422 206.61 325.104C201.975 328.786 197.561 328.297 194.34 327.943C191.45 327.625 190.094 327.542 188.255 329L183 322.495C187.635 318.813 192.049 319.302 195.27 319.656C198.16 319.974 199.516 320.057 201.355 318.599C203.189 317.141 203.41 315.813 203.725 312.948C204.077 309.755 204.56 305.38 209.195 301.698C213.83 298.016 218.244 298.505 221.465 298.859C224.355 299.177 225.711 299.26 227.55 297.802C229.389 296.344 229.605 295.016 229.92 292.146C230.272 288.953 230.756 284.578 235.39 280.896C240.025 277.214 244.439 277.703 247.66 278.057C250.55 278.375 251.906 278.458 253.745 277L259 283.505Z" fill="#EFECFF"/>
                                                <path d="M253.467 276.391C254.875 278.192 254.612 280.884 252.503 281.769C249.247 283.134 246.246 282.791 243.892 282.523C241.039 282.2 239.702 282.115 237.887 283.601C236.077 285.087 235.859 286.441 235.548 289.366C235.2 292.62 234.723 297.079 230.15 300.832C225.576 304.586 221.22 304.087 218.041 303.726C215.189 303.402 213.851 303.317 212.036 304.803C210.226 306.29 210.009 307.643 209.697 310.563C209.35 313.817 208.873 318.276 204.299 322.029C199.725 325.782 195.369 325.283 192.191 324.922C191.423 324.835 190.765 324.765 190.177 324.739C187.818 324.632 184.988 324.469 183.533 322.609C182.125 320.808 182.388 318.116 184.497 317.231C187.753 315.866 190.754 316.209 193.109 316.477C195.961 316.8 197.298 316.885 199.113 315.399C200.923 313.913 201.141 312.559 201.452 309.639C201.8 306.385 202.277 301.926 206.85 298.173C211.424 294.42 215.78 294.919 218.959 295.28C221.811 295.604 223.149 295.689 224.964 294.202C226.779 292.716 226.992 291.362 227.303 288.437C227.65 285.183 228.127 280.724 232.701 276.971C237.275 273.218 241.631 273.717 244.809 274.078C245.577 274.165 246.235 274.235 246.823 274.261C249.182 274.368 252.012 274.531 253.467 276.391Z" fill="url(#paint0_linear_187_3327)" fill-opacity="0.5"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                                <circle cx="6" cy="6" r="6" fill="#FBD95E"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                                <circle cx="50" cy="32" r="4" fill="#7FFA54"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                                <circle cx="18" cy="51" r="2" fill="white"/>
                                            </g></g></g>
                                            <defs>
                                                <linearGradient id="paint0_linear_187_3327" x1="218.5" y1="273" x2="218.5" y2="326" gradientUnits="userSpaceOnUse">
                                                    <stop stop-color="#9888F4" offset="0%"/>
                                                    <stop offset="1" stop-color="#907EF3"/>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <div class="cta-2__content">
                                    <?php if(!empty($settings['weta_subheading'])) : ?>
                                        <span class="weta-el-subheading subtitle mb-15 mb-xs-10 wow fadeIn animated" data-wow-delay=".1s">
                                            <?php echo rrdevs_kses($settings['weta_subheading']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(!empty($settings['weta_title'])) : ?>
                                        <h2 class="weta-el-title color-white mb-30 mb-xs-20 wow fadeIn animated" data-wow-delay=".3s">
                                            <?php echo rrdevs_kses($settings['weta_title']); ?>
                                        </h2>
                                    <?php endif; ?>
                                    <div class="rr-btn__wrapper d-flex flex-wrap align-items-center wow fadeIn animated" data-wow-delay=".5s">
                                        <?php if(!empty($settings['weta_button_text'])) : ?>
                                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                                <span class="btn-wrap">
                                                    <span class="text-one"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/apple-black.png" alt="">
                                                        <?php echo $settings['weta_button_text']; ?>
                                                    </span>
                                                    <span class="text-two"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/apple-black.png" alt="">
                                                        <?php echo $settings['weta_button_text']; ?>
                                                    </span>
                                                </span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if(!empty($settings['weta_button_text_two'])) : ?>
                                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg-two' ); ?>>
                                                <span class="btn-wrap">
                                                    <span class="text-one"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/android.png" alt="">
                                                        <?php echo $settings['weta_button_text_two']; ?>
                                                    </span>
                                                    <span class="text-two"><img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/android.png" alt="">
                                                        <?php echo $settings['weta_button_text_two']; ?>
                                                    </span>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="cta-2__media wow fadeIn animated leftRight" data-wow-delay=".7s">
                                    <img src="<?php print esc_url($weta_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif ( $settings['design_style']  == 'layout-4' ): 
            
            if ('2' == $settings['weta_button_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_button_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-2');
            } else {
                if ( ! empty( $settings['weta_button_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_button_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'weta-el-button rr-btn rr-btn__theme-2');
                }
            }

            if ('2' == $settings['weta_button_link_type_two']) {
                $this->add_render_attribute('weta-button-arg-two', 'href', get_permalink($settings['weta_button_page_link_two']));
                $this->add_render_attribute('weta-button-arg-two', 'target', '_self');
                $this->add_render_attribute('weta-button-arg-two', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg-two', 'class', 'weta-el-button-two rr-btn rr-btn__theme-2 rr-btn__theme-2_black');
            } else {
                if ( ! empty( $settings['weta_button_link_two']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg-two', $settings['weta_button_link_two'] );
                    $this->add_render_attribute('weta-button-arg-two', 'class', 'weta-el-button-two rr-btn rr-btn__theme-2 rr-btn__theme-2_black');
                }
            }
            
            ?>

            <section class="cta-2__area footer-up cta-2__background">
                <div class="container container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="cta-2__wrapper d-flex flex-xl-row flex-column align-items-center parallax-element">
                                <?php if ( !empty ($settings['weta_switch']) ) :  ?>
                                    <div class="cta-2__shape">
                                        <svg width="259" height="329" viewBox="0 0 259 329" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.01"><g class="leftRight">
                                                <path d="M259 283.505C254.365 287.188 249.951 286.698 246.73 286.344C243.84 286.026 242.484 285.943 240.645 287.401C238.811 288.859 238.59 290.188 238.275 293.057C237.923 296.25 237.44 300.625 232.805 304.307C228.17 307.99 223.756 307.5 220.535 307.146C217.645 306.828 216.289 306.745 214.45 308.203C212.616 309.661 212.395 310.99 212.08 313.854C211.728 317.047 211.244 321.422 206.61 325.104C201.975 328.786 197.561 328.297 194.34 327.943C191.45 327.625 190.094 327.542 188.255 329L183 322.495C187.635 318.813 192.049 319.302 195.27 319.656C198.16 319.974 199.516 320.057 201.355 318.599C203.189 317.141 203.41 315.813 203.725 312.948C204.077 309.755 204.56 305.38 209.195 301.698C213.83 298.016 218.244 298.505 221.465 298.859C224.355 299.177 225.711 299.26 227.55 297.802C229.389 296.344 229.605 295.016 229.92 292.146C230.272 288.953 230.756 284.578 235.39 280.896C240.025 277.214 244.439 277.703 247.66 278.057C250.55 278.375 251.906 278.458 253.745 277L259 283.505Z" fill="#EFECFF"/>
                                                <path d="M253.467 276.391C254.875 278.192 254.612 280.884 252.503 281.769C249.247 283.134 246.246 282.791 243.892 282.523C241.039 282.2 239.702 282.115 237.887 283.601C236.077 285.087 235.859 286.441 235.548 289.366C235.2 292.62 234.723 297.079 230.15 300.832C225.576 304.586 221.22 304.087 218.041 303.726C215.189 303.402 213.851 303.317 212.036 304.803C210.226 306.29 210.009 307.643 209.697 310.563C209.35 313.817 208.873 318.276 204.299 322.029C199.725 325.782 195.369 325.283 192.191 324.922C191.423 324.835 190.765 324.765 190.177 324.739C187.818 324.632 184.988 324.469 183.533 322.609C182.125 320.808 182.388 318.116 184.497 317.231C187.753 315.866 190.754 316.209 193.109 316.477C195.961 316.8 197.298 316.885 199.113 315.399C200.923 313.913 201.141 312.559 201.452 309.639C201.8 306.385 202.277 301.926 206.85 298.173C211.424 294.42 215.78 294.919 218.959 295.28C221.811 295.604 223.149 295.689 224.964 294.202C226.779 292.716 226.992 291.362 227.303 288.437C227.65 285.183 228.127 280.724 232.701 276.971C237.275 273.218 241.631 273.717 244.809 274.078C245.577 274.165 246.235 274.235 246.823 274.261C249.182 274.368 252.012 274.531 253.467 276.391Z" fill="url(#paint0_linear_187_3327)" fill-opacity="0.5"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                                <circle cx="6" cy="6" r="6" fill="#FBD95E"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                                <circle cx="50" cy="32" r="4" fill="#7FFA54"/>
                                            </g></g></g>
                                            <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="rightLeft">
                                                <circle cx="18" cy="51" r="2" fill="white"/>
                                            </g></g></g>
                                            <defs>
                                                <linearGradient id="paint0_linear_187_3327" x1="218.5" y1="273" x2="218.5" y2="326" gradientUnits="userSpaceOnUse">
                                                    <stop stop-color="#9888F4" offset="0%"/>
                                                    <stop offset="1" stop-color="#907EF3"/>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <div class="cta-2__content">
                                    <?php if(!empty($settings['weta_subheading'])) : ?>
                                        <span class="weta-el-subheading subtitle mb-15 mb-xs-10 wow fadeIn animated" data-wow-delay=".1s">
                                            <?php echo rrdevs_kses($settings['weta_subheading']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(!empty($settings['weta_title'])) : ?>
                                        <h2 class="weta-el-title color-white mb-30 mb-xs-20 wow fadeIn animated" data-wow-delay=".3s">
                                            <?php echo rrdevs_kses($settings['weta_title']); ?>
                                        </h2>
                                    <?php endif; ?>

                                    <div class="rr-btn__wrapper d-flex flex-wrap align-items-center wow fadeIn animated" data-wow-delay=".5s">
                                        <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                                            <span class="btn-wrap">
                                                <span class="text-one">
                                                    <img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/apple-black.png" alt="">
                                                    <?php echo $settings['weta_button_text']; ?>
                                                </span>
                                                <span class="text-two">
                                                    <img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/apple-black.png" alt="">
                                                    <?php echo $settings['weta_button_text']; ?>
                                                </span>
                                            </span>
                                        </a>

                                        <?php if(!empty($settings['weta_button_text_two'])) : ?>
                                            <a <?php echo $this->get_render_attribute_string( 'weta-button-arg-two' ); ?>>
                                                <span class="btn-wrap">
                                                    <span class="text-one">
                                                        <img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/android.png" alt="">
                                                        <?php echo $settings['weta_button_text_two']; ?>
                                                    </span>
                                                    <span class="text-two">
                                                        <img src="<?php print get_template_directory_uri(); ?>/assets/imgs/icons/android.png" alt="">
                                                        <?php echo $settings['weta_button_text_two']; ?>
                                                    </span>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="cta-2__media wow fadeIn animated leftRight" data-wow-delay=".7s">
                                    <img src="<?php print esc_url($weta_image); ?>" alt="<?php print esc_attr($weta_image_alt); ?>">
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

$widgets_manager->register( new weta_Cta() );