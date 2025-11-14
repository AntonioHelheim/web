<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
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
class WETA_Hero_Slider_Two extends Widget_Base {

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
		return 'weta_hero_slider_two';
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
		return __( 'Hero Slider Two', 'weta-core' );
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
            '_content_hero_slider',
            [
                'label' => esc_html__('Hero Slider', 'weta-core'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'shape_switch',
            [
                'label' => esc_html__( 'Shape Show/Hide', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

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
            'shape_image',
            [
                'type' => Controls_Manager::MEDIA,
                'label' => __( 'Shape Image', 'weta-core' ),
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );       

        $repeater->add_control(
            'subtitle',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'show_label' => true,
                'label' => __( 'Subtitle', 'weta-core' ),
                'default' => __( 'Innovation at Work', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );               

        $repeater->add_control(
            'title_one',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Title One', 'weta-core' ),
                'default' => __( 'Stay cool with our', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'title_two',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Title Two', 'weta-core' ),
                'default' => __( 'top notch AC', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'title_three',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Title Three', 'weta-core' ),
                'default' => __( 'Repair Service', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'description',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Description', 'weta-core' ),
                'default' => __( 'Vero elitr justo clita lorem. Ipsum dolor at sed stet sit diam no.  Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );


        $repeater->add_control(
            'call_image',
            [
                'type' => Controls_Manager::MEDIA,
                'label' => __( 'Call Image', 'weta-core' ),
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );       

        $repeater->add_control(
            'call_label',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'show_label' => true,
                'label' => __( 'Call Label', 'weta-core' ),
                'default' => __( 'Need help?', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        ); 

        $repeater->add_control(
            'call_text',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'show_label' => true,
                'label' => __( 'Call Text', 'weta-core' ),
                'default' => __( '(307) 555-0133', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'call_url',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Call URL', 'weta-core' ),
                'default' => 'mailto:3075550133',
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
                'label' => esc_html__( 'Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'condition' => [
                    'link_switcher' => 'yes'
                ],
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label' => esc_html__( 'Link', 'weta-core' ),
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
                'label' => esc_html__( 'Select Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'link_type' => '2',
                    'link_switcher' => 'yes',
                ]
            ]
        );

        $repeater->add_control(
            'button_text',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Title', 'weta-core' ),
                'default' => __( 'Read More', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'link_switcher' => 'yes'
                ],
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'fa fa-long-arrow-alt-right',
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
                        'value' => 'fas fa-long-arrow-alt-right',
                        'library' => 'solid',
                    ],
                ]
            );
        }

        // REPEATER
        $this->add_control(
            'hero_slider_list',
            [
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '<# print(title_one || "Carousel Item"); #>',
                'default' => [
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'subtitle' => __( 'Welcome To Logistics', 'weta-core' ),
                        'title_one' => __( 'Your Gateway to', 'weta-core' ),
                        'title_two' => __( 'Any Destination', 'weta-core' ),
                        'title_three' => __( 'In The World.', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'subtitle' => __( 'Welcome To Logistics', 'weta-core' ),
                        'title_one' => __( 'One step solution', 'weta-core' ),
                        'title_two' => __( 'for WorldWide', 'weta-core' ),
                        'title_three' => __( 'Transportation.', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'subtitle' => __( 'Welcome To Logistics', 'weta-core' ),
                        'title_one' => __( 'Doing it right', 'weta-core' ),
                        'title_two' => __( 'Costs less in', 'weta-core' ),
                        'title_three' => __( 'the end', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'subtitle' => __( 'Welcome To Logistics', 'weta-core' ),
                        'title_one' => __( 'Your Gateway to', 'weta-core' ),
                        'title_two' => __( 'Any Destination', 'weta-core' ),
                        'title_three' => __( 'In The World.', 'weta-core' ),
                    ],
                ]
            ]
        );

        $this->end_controls_section();

        // Style Control 
		$this->start_controls_section(
			'design_layout_style',
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
                    '{{WRAPPER}} .slider-content-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .weta-slider',
			]
		);

        $this->add_control(
            '_heading_arrow_dots',
            [
                'label' => esc_html__( 'Arrow', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( '_tabs_arrow' );
        
        $this->start_controls_tab(
            'arrow_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'text-domain' ),
            ]
        );
        
        $this->add_control(
            'arrow_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-swiper-pagination .swiper-pagination-bullet:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-swiper-pagination .swiper-pagination-bullet::before' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_border',
            [
                'label' => esc_html__( 'Border', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-swiper-pagination .swiper-pagination-bullet::before' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'arrow_hover_tab',
            [
                'label' => esc_html__( 'Active', 'text-domain' ),
            ]
        );
        
       $this->add_control(
            'arrow_background_hover',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_border_hover',
            [
                'label' => esc_html__( 'Border', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

		$this->end_controls_section();

        $this->start_controls_section(
			'hero_slider_style',
			[
				'label' => __( 'Hero Slider', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        // Subheading
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
                    '{{WRAPPER}} .slider-item .slider-content-wrap .weta-small-cap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'subtitle_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .weta-small-cap' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .slider-item .slider-content-wrap .weta-small-cap',
            ]
        );

        // Title
        $this->add_control(
            '_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .weta-caption.heading' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .weta-cap' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .slider-item .slider-content-wrap .weta-cap',
            ]
        );


        // Title Cap
        $this->add_control(
            '_heading_title_cap',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title Cap', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'title_cap_fill_color',
            [
                'label' => __( 'Fill Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .weta-cap span' => '-webkit-text-fill-color: {{VALUE}}',
                ],
            ]
        );     

        $this->add_control(
            'title_cap_stroke_width_color',
            [
                'label' => __( 'Stroke Width', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .weta-cap span' => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_cap_stroke_color',
            [
                'label' => __( 'Stroke Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .weta-cap span' => '-webkit-text-stroke-color: {{VALUE}}',
                ],
            ]
        );        


        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_cap_typography',
                'selector' => '{{WRAPPER}} .slider-item .slider-content-wrap .weta-cap span',
            ]
        );


        // Description
        $this->add_control(
            '_heading_description',
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
                    '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-desc' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'description_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-desc' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-desc',
            ]
        );


        // Contact Info
        $this->add_control(
            '_contact_info_label',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Contact Info Label', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'contact_info_label_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-btn-wrap .header-contact .contact span' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'contact_info_label_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-btn-wrap .header-contact .contact span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'contact_info_label_typography',
                'selector' => '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-btn-wrap .header-contact .contact span',
            ]
        );


        // Contact Info Text
        $this->add_control(
            '_contact_info_text',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Contact Info Text', 'weta-core' ),
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
                    '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-btn-wrap .header-contact .contact a' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'contact_info_text_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-btn-wrap .header-contact .contact a' => 'color: {{VALUE}}',
                ],
            ]
        );        

        $this->add_control(
            'contact_info_text_hover_color',
            [
                'label' => __( 'Hover Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-btn-wrap .header-contact .contact a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'contact_info_text_typography',
                'selector' => '{{WRAPPER}} .slider-item.item-2 .slider-content-wrap .slider-content .slider-btn-wrap .header-contact .contact a',
            ]
        );

		$this->end_controls_section();


        // Button 
        $this->start_controls_section(
			'weta_button_style',
			[
				'label' => __( 'Button', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            'button_icon_spacing',
            [
                'label'     => esc_html__( 'Icon Spacing', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default'   => [
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rr-primary-btn i' => 'padding-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_icon_size',
            [
                'label'     => esc_html__( 'Icon Size', 'weta-core' ),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default'   => [
                    'size' => 14,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rr-primary-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .rr-primary-btn:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border_hover',
                'selector' => '{{WRAPPER}} .slider-2 .slider-item .slider-content-wrap .slider-content .slider-btn-wrap .slider-btn .lt-primary-btn:hover',
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

        <div class="slider-section">
            <div class="weta-slider swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach ( $settings['hero_slider_list'] as $item ) :
                        if ( !empty($item['image']['url']) ) {
                            $hero_image_url = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                            $hero_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                        }                    

                        if ( !empty($item['shape_image']['url']) ) {
                            $shape_image = !empty($item['shape_image']['id']) ? wp_get_attachment_image_url( $item['shape_image']['id'], 'full') : $item['shape_image']['url'];
                            $shape_image_alt = get_post_meta($item["shape_image"]["id"], "_wp_attachment_image_alt", true);
                        }                    

                        if ( !empty($item['call_image']['url']) ) {
                            $call_image = !empty($item['call_image']['id']) ? wp_get_attachment_image_url( $item['call_image']['id'], 'full') : $item['call_image']['url'];
                            $call_image_alt = get_post_meta($item["call_image"]["id"], "_wp_attachment_image_alt", true);
                        }

                        // Link
                        if ('2' == $item['link_type']) {
                            $link = get_permalink($item['page_link']);
                            $target = '_self';
                            $rel = 'nofollow';
                        } else {
                            $link = !empty($item['link']['url']) ? $item['link']['url'] : '';
                            $target = !empty($item['link']['is_external']) ? '_blank' : '';
                            $rel = !empty($item['link']['nofollow']) ? 'nofollow' : '';
                        }
                    ?>
                    <div class="swiper-slide">
                        <div class="slider-item item-2">
                            <div class="container">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="slider-content-wrap">
                                            <div class="slider-content-inner">
                                                <div class="slider-content">
                                                    <?php if ( !empty($item['shape_switch']) ) : ?>
                                                    <div class="shapes">
                                                        <div class="shape shape-1"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/shapes/hero-shape-1.png" alt="shape"></div>
                                                        <div class="shape shape-2"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/shapes/hero-shape-2.png" alt="shape"></div>
                                                    </div>
                                                    <?php endif; ?>

                                                    <?php if ( !empty( $item['subtitle'] ) ) : ?>
                                                    <div class="weta-caption sub-heading">
                                                        <div class="inner-layer">
                                                            <div class="weta-small-cap" data-animation="weta-fadeInUp" data-delay="1000ms" data-duration="1200ms">
                                                            <?php echo rrdevs_kses( $item['subtitle'] ); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>

                                                    <div class="weta-caption heading">
                                                        <div class="inner-layer">
                                                            <div class="weta-cap" data-animation="weta-fadeInUp" data-delay="1200ms" data-duration="1400ms" >
                                                                <?php echo rrdevs_kses( $item['title_one'] ); ?>
                                                            </div>
                                                        </div>

                                                        <div class="inner-layer layer-2">
                                                            <div class="weta-cap" data-animation="weta-fadeInUp" data-delay="1400ms" data-duration="1600ms">
                                                                <span><?php echo rrdevs_kses( $item['title_two'] ); ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="inner-layer layer-2">
                                                            <div class="weta-cap" data-animation="weta-fadeInUp" data-delay="1600ms" data-duration="1800ms">
                                                                <?php echo rrdevs_kses( $item['title_three'] ); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <?php if ( !empty( $item['description'] ) ) : ?>
                                                    <div class="slider-desc" data-animation="weta-fadeInUp" data-delay="1800ms" data-duration="2000ms">
                                                        <?php echo rrdevs_kses( $item['description'] ); ?>
                                                    </div>
                                                    <?php endif; ?>

                                                    <div class="slider-btn-wrap" data-animation="weta-fadeInUp" data-delay="2000ms" data-duration="2200ms">
                                                        <?php if ( !empty($item['link_switcher']) ) : ?>
                                                        <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>" class="rr-primary-btn">
                                                            <?php echo rrdevs_kses( $item['button_text'] ); ?>
                                                            <?php if (!empty($item['icon']) || !empty($item['selected_icon']['value'])) : ?>
                                                                <?php weta_render_icon($item, 'icon', 'selected_icon'); ?>
                                                            <?php endif; ?>
                                                        </a>
                                                        <?php endif; ?>

                                                        <div class="header-contact">
                                                            <?php if(!empty( $call_image )) : ?>
                                                            <img src="<?php echo esc_url($call_image); ?>" alt="<?php echo esc_attr($call_image_alt); ?>">
                                                            <?php endif; ?>

                                                            <h4 class="contact">
                                                                <?php if ( !empty( $item['call_label'] ) ) : ?>
                                                                <span><?php echo rrdevs_kses( $item['call_label'] ); ?></span>
                                                                <?php endif; ?>

                                                                <?php if ( !empty( $item['call_text'] ) ) : ?>
                                                                <a href="<?php echo esc_url( $item['call_url'] ); ?>"><?php echo rrdevs_kses( $item['call_text'] ); ?></a>
                                                                <?php endif; ?>
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="slider-img-box" data-animation="weta-img-effect" data-delay="300ms" data-duration="1000ms">
                                            <?php if( !empty($shape_image) ) : ?>
                                            <div class="shape" data-background="<?php echo esc_attr($shape_image); ?>"></div>
                                            <?php endif; ?>

                                            <?php if( !empty($hero_image_url) ) : ?>
                                            <img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($hero_image_alt); ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="weta-swiper-pagination"></div>
            </div>
        </div>

		<?php
	}
}

$widgets_manager->register( new WETA_Hero_Slider_Two() );