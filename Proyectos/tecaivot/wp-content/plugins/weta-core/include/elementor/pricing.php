<?php
namespace WETACore\Widgets;

use Elementor\Widget_Base;
use \Elementor\Repeater;
use \Elementor\Control_Media;
use \Elementor\Utils;
Use \Elementor\Core\Schemes\Typography;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Image_Size;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Pricing extends Widget_Base {

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
		return 'weta_pricing';
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
		return __( 'Pricing', 'weta-core' );
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

        // Header
        $this->start_controls_section(
            '_section_header',
            [
                'label' => __('Header', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __('Starter Plan', 'weta-core'),
                'dynamic' => [
                    'active' => true
                ]
            ]
        );

        $this->end_controls_section();

        // Pricing Image
        $this->start_controls_section(
            '_content_pricing_icon',
            [
                'label' => esc_html__('Pricing Image', 'weta-core'),
            ]
        );

        $this->add_control(
            'price_bg_image',
            [
                'label' => esc_html__( 'Gradient BG Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );


        $this->add_control(
            'price_image',
            [
                'label' => esc_html__( 'Price Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->end_controls_section();


        // Pricing 
        $this->start_controls_section(
            '_section_pricing',
            [
                'label' => __('Pricing', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'currency',
            [
                'label' => __('Currency', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'label_block' => false,
                'options' => [
                    '' => __('None', 'weta-core'),
                    'baht' => '&#3647; ' . _x('Baht', 'Currency Symbol', 'weta-core'),
                    'bdt' => '&#2547; ' . _x('BD Taka', 'Currency Symbol', 'weta-core'),
                    'dollar' => '&#36; ' . _x('Dollar', 'Currency Symbol', 'weta-core'),
                    'euro' => '&#128; ' . _x('Euro', 'Currency Symbol', 'weta-core'),
                    'franc' => '&#8355; ' . _x('Franc', 'Currency Symbol', 'weta-core'),
                    'guilder' => '&fnof; ' . _x('Guilder', 'Currency Symbol', 'weta-core'),
                    'krona' => 'kr ' . _x('Krona', 'Currency Symbol', 'weta-core'),
                    'lira' => '&#8356; ' . _x('Lira', 'Currency Symbol', 'weta-core'),
                    'peseta' => '&#8359 ' . _x('Peseta', 'Currency Symbol', 'weta-core'),
                    'peso' => '&#8369; ' . _x('Peso', 'Currency Symbol', 'weta-core'),
                    'pound' => '&#163; ' . _x('Pound Sterling', 'Currency Symbol', 'weta-core'),
                    'real' => 'R$ ' . _x('Real', 'Currency Symbol', 'weta-core'),
                    'ruble' => '&#8381; ' . _x('Ruble', 'Currency Symbol', 'weta-core'),
                    'rupee' => '&#8360; ' . _x('Rupee', 'Currency Symbol', 'weta-core'),
                    'indian_rupee' => '&#8377; ' . _x('Rupee (Indian)', 'Currency Symbol', 'weta-core'),
                    'shekel' => '&#8362; ' . _x('Shekel', 'Currency Symbol', 'weta-core'),
                    'won' => '&#8361; ' . _x('Won', 'Currency Symbol', 'weta-core'),
                    'yen' => '&#165; ' . _x('Yen/Yuan', 'Currency Symbol', 'weta-core'),
                    'custom' => __('Custom', 'weta-core'),
                ],
                'default' => 'dollar',
            ]
        );

        $this->add_control(
            'currency_custom',
            [
                'label' => __('Custom Symbol', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'currency' => 'custom',
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $this->add_control(
            'price',
            [
                'label' => __('Price', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '24',
                'dynamic' => [
                    'active' => true
                ]
            ]
        );

        $this->add_control(
            'period',
            [
                'label' => __('Period', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('/mo', 'weta-core'),
                'dynamic' => [
                    'active' => true
                ]
            ]
        );

        $this->end_controls_section();


        // Description
        $this->start_controls_section(
            '_section_description',
            [
                'label' => __('Description', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'description',
            [
                'label' => __('Description', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __('Perfect Plan For Basic.', 'weta-core'),
                'dynamic' => [
                    'active' => true
                ]
            ]
        );

        $this->end_controls_section();

        // Features
        $this->start_controls_section(
            '_section_features',
            [
                'label' => __('Features', 'weta-core'),
            ]
        );

        $this->add_control(
            'includes',
            [
                'label' => __('Includes Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __('Includes:', 'weta-core'),
                'dynamic' => [
                    'active' => true
                ]
            ]
        );

        $repeater = new Repeater();


        $repeater->add_control(
            'single_features_icon_image',
            [
                'label' => esc_html__('Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'text',
            [
                'label' => __('Text', 'weta-core'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Exciting Feature', 'weta-core'),
                'dynamic' => [
                    'active' => true
                ]
            ]
        );

        $this->add_control(
            'features_list',
            [
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'show_label' => false,
                'default' => [
                    [
                        'text' => __('Training Intake / Requests', 'weta-core'),
                    ],
                    [
                        'text' => __('Planning Board', 'weta-core'),
                    ],
                    [
                        'text' => __('Content Creation, exceeding', 'weta-core'),
                    ],
                    [
                        'text' => __('Team Management', 'weta-core'),
                    ],        
                    [
                        'text' => __('Unlimited Collaborators', 'weta-core'),
                    ],
                ],
                'title_field' => '<# print((text)); #>',
            ]
        );

        $this->end_controls_section();


		// weta_btn_button_group
        $this->start_controls_section(
            'weta_btn_button_group',
            [
                'label' => esc_html__('Button', 'weta-core'),
            ]
        );

        $this->add_control(
            'weta_btn_text',
            [
                'label' => esc_html__('Button Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Purchase Plan', 'weta-core'),
                'title' => esc_html__('Enter button text', 'weta-core'),
                'label_block' => true,
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
                ]
            ]
        );
        
        $this->end_controls_section();


        // Style Control 
        $this->start_controls_section(
			'weta_layout_style',
			[
				'label' => __( 'Design Layout', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'content_margin',
            [
                'label' => __( 'Content Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .pricing-plan__item-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background_color',
            [
                'label' => __( 'Content Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-body' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'content_border',
                'selector' => '{{WRAPPER}} .pricing-plan__item-body',
            ]
        );

        $this->end_controls_section();


        // Header 
        $this->start_controls_section(
            'weta_header_style',
            [
                'label' => esc_html__('Header', 'weta-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'weta_header_title_content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'header_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header-label'    => 'color: {{VALUE}}',
                ],
            ]
        );        

        $this->add_control(
            'header_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header-label'    => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'header_typography',
                'selector' => '{{WRAPPER}} .pricing-plan__item-header-label',
            ]
        );

        $this->add_responsive_control(
            'header_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header-label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // Pricing 
        $this->start_controls_section(
            'weta_pricing_style',
            [
                'label' => esc_html__('Pricing', 'weta-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'pricing_options',
            [
                'label' => esc_html__( 'Pricing Options', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
            ]
        );
        
        $this->add_control(
            'pricing_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header h2'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'pricing_typography',
                'selector' => '{{WRAPPER}} .pricing-plan__item-header h2',
            ]
        );

        $this->add_responsive_control(
            'pricing_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header h2' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'pricing_duration_options',
            [
                'label' => esc_html__( 'Duration Options', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pricing_duration_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header h2 span'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'pricing_duration_typography',
                'selector' => '{{WRAPPER}} .pricing-plan__item-header h2 span',
            ]
        );

        $this->add_responsive_control(
            'pricing_duration_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header h2 span' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // Pricing Description 
        $this->start_controls_section(
            'weta_description_style',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-header p'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'description_typography',
                'selector' => '{{WRAPPER}} .pricing-plan__item-header p',
            ]
        );

        $this->end_controls_section();


        // Feature List 
        $this->start_controls_section(
			'weta_features_list_style',
			[
				'label' => __( 'Features List', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            'weta_features_list_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-body ul li'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'features_list_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .pricing-plan__item-body ul' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'features_list_typography',
                'selector' => '{{WRAPPER}} .pricing-plan__item-body ul li',
            ]
        );

		$this->end_controls_section();

        
        // Button Style 
        $this->start_controls_section(
			'weta_button_style',
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
                'label'     => esc_html__( 'Text', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rrdevs-el-btn'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rrdevs-el-btn'    => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .rrdevs-el-btn',
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
                'label'     => esc_html__( 'Text', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rrdevs-el-btn:hover' => 'color: {{VALUE}}!important;',
                ],
            ]
        );

        $this->add_control(
            'button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rrdevs-el-btn:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border_hover',
                'selector' => '{{WRAPPER}} .rrdevs-el-btn:hover',
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
                    '{{WRAPPER}} .rrdevs-el-btn' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .rrdevs-el-btn',
            ]
        );

		$this->end_controls_section();

	}

    private static function get_currency_symbol($symbol_name)
    {
        $symbols = [
            'baht' => '&#3647;',
            'bdt' => '&#2547;',
            'dollar' => '&#36;',
            'euro' => '&#128;',
            'franc' => '&#8355;',
            'guilder' => '&fnof;',
            'indian_rupee' => '&#8377;',
            'pound' => '&#163;',
            'peso' => '&#8369;',
            'peseta' => '&#8359',
            'lira' => '&#8356;',
            'ruble' => '&#8381;',
            'shekel' => '&#8362;',
            'rupee' => '&#8360;',
            'real' => 'R$',
            'krona' => 'kr',
            'won' => '&#8361;',
            'yen' => '&#165;',
        ];

        return isset($symbols[$symbol_name]) ? $symbols[$symbol_name] : '';
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

            if ( !empty($settings['price_bg_image']['url']) ) {
                $price_bg_image = !empty($settings['price_bg_image']['id']) ? wp_get_attachment_image_url( $settings['price_bg_image']['id'], 'full') : $settings['price_bg_image']['url'];
                $price_bg_image_alt = get_post_meta($settings["price_bg_image"]["id"], "_wp_attachment_image_alt", true);
            }             

            if ( !empty($settings['price_image']['url']) ) {
                $price_image = !empty($settings['price_image']['id']) ? wp_get_attachment_image_url( $settings['price_image']['id'], 'full') : $settings['price_image']['url'];
                $price_image_alt = get_post_meta($settings["price_image"]["id"], "_wp_attachment_image_alt", true);
            } 

            // Link
            if ('2' == $settings['weta_btn_link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_btn_page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-3 mt-50 mt-sm-40 mt-xs-35 rrdevs-el-btn');
            } else {
                if ( ! empty( $settings['weta_btn_link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['weta_btn_link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'rr-btn rr-btn__theme-3 mt-50 mt-sm-40 mt-xs-35 rrdevs-el-btn');
                }
            }

	        if ($settings['currency'] === 'custom') {
	            $currency = $settings['currency_custom'];
	        } else {
	            $currency = self::get_currency_symbol($settings['currency']);
	        }
		?>

            <div class="pricing-plan__item mb-60 wow fadeIn animated" data-wow-delay=".5s">
                <div class="pricing-plan__item-header d-flex flex-column" data-background="<?php print esc_url($price_bg_image); ?>">
                     <?php if ( !empty( $settings['title'] )) : ?>
                    <span class="pricing-plan__item-header-label"><?php echo rrdevs_kses($settings['title']); ?></span>
                    <?php endif; ?>

                    <?php if (!empty($price_image)) : ?>
                    <div class="pricing-plan__item-header-icon mb-30">
                        <img src="<?php print esc_url($price_image); ?>" alt="<?php print esc_attr($price_image_alt); ?>">
                    </div>
                    <?php endif; ?>

                    <h2 class="mb-10"><?php echo esc_html($currency); ?><?php echo rrdevs_kses($settings['price']); ?> <span><?php echo rrdevs_kses($settings['period']); ?></span></h2>

                    <?php if(!empty($settings['description'])) : ?>
                    <p class="mb-0"><?php echo rrdevs_kses($settings['description']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="pricing-plan__item-body">
                    <?php if(!empty($settings['includes'])) : ?>
                    <p class="mb-20"><?php echo rrdevs_kses($settings['includes']); ?></p>
                    <?php endif; ?>

                    <ul>
                        <?php foreach ($settings['features_list'] as $index => $item) : ?>
                        <li>
                            <img src="<?php echo $item['single_features_icon_image']['url']; ?>" alt="image not found">
                            <?php echo rrdevs_kses($item['text']); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if(!empty( $settings['weta_btn_text'] )) : ?>
                    <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                        <span class="btn-wrap">
                            <span class="text-one"><?php echo $settings['weta_btn_text']; ?> <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 7H15" stroke="#010915" stroke-opacity="0.24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 1L15 7L9 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="text-two"><?php echo $settings['weta_btn_text']; ?> <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 7H15" stroke="#010915" stroke-opacity="0.24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 1L15 7L9 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php
	}

}

$widgets_manager->register( new WETA_Pricing() );