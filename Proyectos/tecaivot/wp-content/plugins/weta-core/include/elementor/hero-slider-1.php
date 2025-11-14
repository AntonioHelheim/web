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
class WETA_Hero_Slider_One extends Widget_Base {

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
		return 'weta_hero_slider_one';
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
		return __( 'Hero Slider One', 'weta-core' );
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
                'default' => __( 'Empowering', 'weta-core' ),
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
                'default' => __( '<span>Fixing</span> business', 'weta-core' ),
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
                'default' => __( 'success', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'slider_list_content',
            [
                'label' => esc_html__( 'Slider List Content', 'textdomain' ),
                'type' => \Elementor\Controls_Manager::CODE,
                'language' => 'html',
                'rows' => 20,
            ]
        );

        $repeater->add_control(
            'video_url',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Video URL', 'weta-core' ),
                'default' => 'https://youtu.be/Hh3MjLaDNG8?feature=shared',
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'video_text',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Video Text', 'weta-core' ),
                'default' => __( 'See How It Works', 'weta-core' ),
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
                'default' => __( 'Explore More', 'weta-core' ),
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
                        'subtitle' => __( 'Innovation at Work', 'weta-core' ),
                        'title_one' => __( 'Empowering Fixing', 'weta-core' ),
                        'title_two' => __( 'business', 'weta-core' ),
                        'title_three' => __( 'success', 'weta-core' ),
                    ],
                ]
            ]
        );

        $this->end_controls_section();


        // Design Style Control 
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
                'label' => __( 'Content Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .slider-content-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-slider' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            '_heading_arrow_dots',
            [
                'label' => esc_html__( 'Arrow', 'weta-core' ),
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
                'label' => esc_html__( 'Active', 'weta-core' ),
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
                'separator' => 'before'
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


        // Slider Info List
        $this->add_control(
            '_slider_info_list',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Info List', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'info_list_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-list li:not(:last-of-type)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'info_list_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-list li' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'info_list_typography',
                'selector' => '{{WRAPPER}} .slider-item .slider-content-wrap .slider-list li',
            ]
        );

        // Slider Info List Icon
        $this->add_control(
            '_slider_info_list_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Info List Icon', 'weta-core' ),
                'separator' => 'before',
            ]
        );


        $this->add_control(
            'info_list_color_icon',
            [
                'label' => __( 'Icon Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-list li i' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'info_list_icon_typography',
                'selector' => '{{WRAPPER}} .slider-item .slider-content-wrap .slider-list li i',
            ]
        );


         // Slider Video Text
        $this->add_control(
            '_slider_video_text',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Video Text', 'weta-core' ),
                'separator' => 'before',
            ]
        );


        $this->add_control(
            'slider_video_text',
            [
                'label' => __( 'Video Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-btn-wrap .video-btn span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'video_text_typography',
                'selector' => '{{WRAPPER}} .slider-item .slider-content-wrap .slider-btn-wrap .video-btn span',
            ]
        );



        $this->add_control(
            '_slider_video_button',
            [
                'label' => esc_html__( 'Video Button', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( '_tabs_video_btn' );
        
        $this->start_controls_tab(
            'video_button_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'video_btn_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-btn-wrap .video-btn a .play-btn' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'video_btn_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-btn-wrap .video-btn a .play-btn' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'video_btn_typography',
                'selector' => '{{WRAPPER}} .slider-item .slider-content-wrap .slider-btn-wrap .video-btn a .play-btn',
            ]
        );

        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'video_btn_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );

        $this->add_control(
            'video_btn_hover_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-btn-wrap .video-btn a .play-btn:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'video_btn_hover_bg',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-item .slider-content-wrap .slider-btn-wrap .video-btn a .play-btn:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();


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
                    '{{WRAPPER}} .rr-primary-btn i' => 'padding-left: {{SIZE}}{{UNIT}}!important;',
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
                    '{{WRAPPER}} .rr-primary-btn i' => 'font-size: {{SIZE}}{{UNIT}}!important;',
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

        <div class="slider-section">
            <div class="weta-slider swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach ( $settings['hero_slider_list'] as $item ) :
                        if ( !empty($item['image']['url']) ) {
                            $hero_image_url = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                            $hero_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                        }                        

                        if ( !empty($item['shape_image']['url']) ) {
                            $shape_image_url = !empty($item['shape_image']['id']) ? wp_get_attachment_image_url( $item['shape_image']['id'], 'full') : $item['shape_image']['url'];
                            $shape_image_alt = get_post_meta($item["shape_image"]["id"], "_wp_attachment_image_alt", true);
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
                        <div class="slider-item">
                            <div class="container">
                                <div class="row align-items-center">
                                    <div class="col-xl-5 col-lg-12 col-md-12">
                                        <div class="slider-content-wrap">
                                            <div class="slider-content-inner">
                                                <div class="slider-content">
                                                    <?php if ( !empty($item['shape_switch']) ) : ?>
                                                    <div class="shapes">
                                                        <div class="shape shape-1"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/shapes/hero-shape-1.png" alt="shape"></div>
                                                        <div class="shape shape-2"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/shapes/hero-shape-2.png" alt="shape"></div>
                                                    </div>
                                                    <?php endif; ?>

                                                    <?php if(!empty($item['subtitle'])) : ?>
                                                    <div class="weta-caption sub-heading">
                                                        <div class="inner-layer">
                                                            <div class="weta-small-cap" data-animation="weta-fadeInUp" data-delay="1000ms" data-duration="1200ms">
                                                                <?php echo rrdevs_kses( $item['subtitle'] ); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>

                                                    <div class="weta-caption heading">
                                                        <?php if(!empty( $item['title_one'] )) : ?>
                                                        <div class="inner-layer">
                                                            <div class="weta-cap" data-animation="weta-fadeInUp" data-delay="1200ms" data-duration="1400ms" >
                                                                <span><?php echo rrdevs_kses( $item['title_one'] ); ?></span> 
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>

                                                        <?php if(!empty( $item['title_two'] )) : ?>
                                                        <div class="inner-layer layer-2">
                                                            <div class="weta-cap" data-animation="weta-fadeInUp" data-delay="1400ms" data-duration="1600ms">
                                                                <?php echo rrdevs_kses( $item['title_two'] ); ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>

                                                        <?php if(!empty( $item['title_three'] )) : ?>
                                                        <div class="inner-layer layer-2">
                                                            <div class="weta-cap" data-animation="weta-fadeInUp" data-delay="1600ms" data-duration="1800ms">
                                                                <?php echo rrdevs_kses( $item['title_three'] ); ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if(!empty( $item['slider_list_content'] )) : ?>
                                                    <ul class="slider-list" data-animation="weta-fadeInUp" data-delay="1800ms" data-duration="2000ms">
                                                        <?php echo rrdevs_kses( $item['slider_list_content'] ); ?>
                                                    </ul>
                                                    <?php endif; ?>

                                                    <div class="slider-btn-wrap" data-animation="weta-fadeInUp" data-delay="2000ms" data-duration="2200ms">
                                                        <a href="<?php echo esc_url($link); ?>" class="rr-primary-btn">
                                                            <?php echo rrdevs_kses( $item['button_text'] ); ?>
                                                            <?php if (!empty($item['icon']) || !empty($item['selected_icon']['value'])) : ?>
                                                                <?php weta_render_icon($item, 'icon', 'selected_icon'); ?>
                                                            <?php endif; ?>
                                                        </a>

                                                        <div class="video-btn"> 
                                                            <a
                                                                class="video-popup"
                                                                data-autoplay="true"
                                                                data-vbtype="video"
                                                                href="<?php echo rrdevs_kses( $item['video_url'] ); ?>">
                                                                <div class="play-btn">
                                                                    <i class="fa-sharp fa-solid fa-play"></i>
                                                                    <div class="ripple"></div>
                                                                </div>

                                                                <?php if(!empty( $item['video_text'] )) : ?>
                                                                <span><?php echo rrdevs_kses( $item['video_text'] ); ?></span>
                                                                <?php endif; ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-7 col-lg-12 col-md-12">
                                        <div class="slider-img">
                                            <?php if(!empty($shape_image_url)) : ?>
                                            <img class="bg-img" data-animation="weta-zoomIn" data-delay="1500ms" data-duration="2000ms" src="<?php echo esc_url($shape_image_url); ?>" alt="<?php echo esc_attr($shape_image_alt); ?>">
                                            <?php endif; ?>

                                            <?php if(!empty($hero_image_url)) : ?>
                                            <div class="slider-men" data-animation="weta-fadeInUp" data-delay="1000ms" data-duration="2000ms">
                                                <img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($hero_image_alt); ?>">
                                            </div>
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

$widgets_manager->register( new WETA_Hero_Slider_One() );