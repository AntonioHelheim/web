<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
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
class WETA_Testimonial_Slider extends Widget_Base {

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
		return 'weta_testimonial_slider';
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
		return __( 'Testimonial', 'weta-core' );
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
            'weta_layout',
            [
                'label' => esc_html__( 'Layout Style', 'weta-core' ),
            ]
        );

        $this->add_control(
            'weta_design_style',
            [
                'label' => esc_html__( 'Style', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'layout-1',
                'options' => [
                    'layout-1' => esc_html__( 'Layout 1', 'weta-core' ),
                    'layout-2'  => esc_html__( 'Layout 2', 'weta-core' ),
                    'layout-3'  => esc_html__( 'Layout 3', 'weta-core' ),
                ],
            ]
        );

        $this->add_control(
            'testimonial_section_bg',
            [
                'label' => esc_html__( 'Choose Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'weta_design_style' => ['layout-3'],
                ],
            ]
        );
        
        $this->end_controls_section();

        // weta_section_title
        $this->start_controls_section(
            'weta_section_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-3'],
                ],

            ]
        );

        $this->add_control(
            'weta_subheading_num',
            [
                'label' => esc_html__('Sub Title Before', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('#1', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-3'],
                ],
            ]
        );

        $this->add_control(
            'weta_subheading',
            [
                'label' => esc_html__('Sub Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Our services', 'weta-core'),
                'placeholder' => esc_html__('Type Sub Heading Text', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_subheading_icon_switcher',
            [
                'label' => esc_html__( 'Subheading Icon Show', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'weta_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Trust us with your repair needs Repairing with care', 'weta-core'),
                'placeholder' => esc_html__('Type Section Title Here', 'weta-core'),
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
                'default' => 'h2',
                'toggle' => false,
            ]
        );

        $this->end_controls_section();


        // Testimonial List
        $this->start_controls_section(
            'testimonial_list_content',
            [
                'label' => esc_html__( 'Testimonial List', 'weta-core' ),
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
                ],
                'default' => 'style_1',
                'frontend_available' => true,
                'style_transfer' => true,
            ]
        );

        $repeater->add_control(
            'quote_switcher',
            [
                'label' => esc_html__( 'Quote Show/Hide', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $repeater->add_control(
            'testimonial_image',
            [
                'label' => esc_html__( 'Author Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'repeater_condition' => ['style_1', 'style_2']
                ],  
            ]
        );

        $repeater->add_control(
            'testimonial_image_big',
            [
                'label' => esc_html__( 'Author Image Big size', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'repeater_condition' => ['style_2']
                ],  
            ]
        );

        $repeater->add_control(
            'testimonial_name', [
                'label' => esc_html__( 'Name', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Cameron Williamson' , 'weta-core' ),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'testimonial_designation', [
                'label' => esc_html__( 'Designation', 'weta-core' ),
                'description' => esc_html__( 'This field only for used layout 2 and 3', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__( 'Nafiz Ahmed' , 'weta-core' ),
                'label_block' => true,
            ]
        );

        // rating
        $repeater->add_control(
            'rr_testi_rating',
            [
                'label' => esc_html__('Select Rating Count', 'rr-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => esc_html__('Single Star', 'rr-core'),
                    '2' => esc_html__('2 Star', 'rr-core'),
                    '3' => esc_html__('3 Star', 'rr-core'),
                    '4' => esc_html__('4 Star', 'rr-core'),
                    '5' => esc_html__('5 Star', 'rr-core'),
                ],
                'default' => '5',
                'condition' => [
                    'repeater_condition' => ['style_1']
                ],  
            ]
        );

        $repeater->add_control(
            'testimonial_content',
            [
                'label' => esc_html__( 'Content', 'weta-core' ),
                'type' => Controls_Manager::TEXTAREA,
                'rows' => 10,
                'default' => esc_html__( 'Medical is a field that deals with the study, diagnosis treatment diseases and injuries. It encompasses various branches such as cardiolog,the a  neurology orthopedics, and more professionals work diligently', 'weta-core' ),
            ]
        );

        $this->add_control(
            'testimonial_list',
            [
                'label' => esc_html__( 'Testimonial List', 'weta-core' ),
                'type' => Controls_Manager::REPEATER,
                'fields' =>  $repeater->get_controls(),
                'default' => [ 
                    [
                        'testimonial_name' => esc_html__( 'Cameron Williamson', 'weta-core' ),
                        'testimonial_description' => esc_html__( 'Medical is a field that deals with the study, diagnosis treatment diseases and injuries. It encompasses various branches such as cardiolog,the a  neurology orthopedics, and more professionals work diligently', 'weta-core' ),
                    ],
                    [
                        'testimonial_name' => esc_html__( 'Cameron Williamson', 'weta-core' ),
                        'testimonial_description' => esc_html__( 'Medical is a field that deals with the study, diagnosis treatment diseases and injuries. It encompasses various branches such as cardiolog,the a  neurology orthopedics, and more professionals work diligently', 'weta-core' ),
                    ],
                    [
                        'testimonial_name' => esc_html__( 'Cameron Williamson', 'weta-core' ),
                        'testimonial_description' => esc_html__( 'Medical is a field that deals with the study, diagnosis treatment diseases and injuries. It encompasses various branches such as cardiolog,the a  neurology orthopedics, and more professionals work diligently', 'weta-core' ),
                    ],
                    [
                        'testimonial_name' => esc_html__( 'Cameron Williamson', 'weta-core' ),
                        'testimonial_description' => esc_html__( 'Medical is a field that deals with the study, diagnosis treatment diseases and injuries. It encompasses various branches such as cardiolog,the a  neurology orthopedics, and more professionals work diligently', 'weta-core' ),
                    ],
                    [
                        'testimonial_name' => esc_html__( 'Cameron Williamson', 'weta-core' ),
                        'testimonial_description' => esc_html__( 'Medical is a field that deals with the study, diagnosis treatment diseases and injuries. It encompasses various branches such as cardiolog,the a  neurology orthopedics, and more professionals work diligently', 'weta-core' ),
                    ],

                ],
                'title_field' => '{{{ testimonial_name }}}',
            ]
        );

        $this->end_controls_section();

        // TAB_STYLE
		$this->start_controls_section(
			'weta_testimonial_layout_style',
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
                    '{{WRAPPER}} .our-testimonial.section-space' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .customer-feedback__active' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .client-testimonial.section-space' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-testimonial.section-space' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .customer-feedback__active' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .client-testimonial__background' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            '_heading_arrow',
            [
                'label' => esc_html__( 'Arrow/Dots', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2'],
                ],
            ]
        );

        $this->start_controls_tabs( '_tabs_arrow',[
            'condition' => [
                'weta_design_style' => ['layout-1', 'layout-2'],
            ],
        ] );
        
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
                    '{{WRAPPER}} .rr-btn .btn-wrap svg path' => 'stroke: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );
        
        $this->add_control(
            'arrow_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .feedback__item__button-prev' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .feedback__item__button-next' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .customer-feedback__dot .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}} !important',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_border',
            [
                'label' => esc_html__( 'Border', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .feedback__item__button-prev' => 'border-color: {{VALUE}}',
                    '{{WRAPPER}} .feedback__item__button-next' => 'border-color: {{VALUE}}',
                    '{{WRAPPER}} .customer-feedback__dot .swiper-pagination-bullet' => 'border-color: {{VALUE}} !important',

                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'arrow_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );
        
        $this->add_control(
            'arrow_color_hover',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .testi-2 .testi-item-wrapper .testi-carousel-wrap .swiper-arrow .swiper-nav:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_background_hover',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn:before' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_border_hover',
            [
                'label' => esc_html__( 'Border', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-btn:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

		$this->end_controls_section();


        // style tab here
        $this->start_controls_section(
            '_section_style_content',
            [
                'label' => __( 'Title / Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-3'],
                ],
            ]
        );

        // sub Title
        $this->add_control(
            '_heading_sub_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Sub Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'sub_title_spacing',
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
            'sub_title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-2__subtitle' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_typography',
                'selector' => '{{WRAPPER}} .section__subtitle, .section-2__subtitle, .section-3__subtitle',
            ]
        );

        $this->add_control(
            'sub_title_number_color',
            [
                'label' => __( 'Number Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle span' => 'color: {{VALUE}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_num_typography',
                'selector' => '{{WRAPPER}} .section__subtitle span, .section-3__subtitle span',
            ]
        );

        $this->add_control(
            'sub_title_number_bg_color',
            [
                'label' => __( 'BG Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .section-2__subtitle' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle span' => 'background-color: {{VALUE}}',
                ],
                
            ]
        );

        $this->add_responsive_control(
            'subheading_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .section-2__subtitle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .section-3__subtitle span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
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

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
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
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .section__title, .section-2__title, h2.lg',
            ]
        );

        $this->end_controls_section();


        // Testimonial List
        $this->start_controls_section(
			'weta_testimonial_list_style',
			[
				'label' => __( 'Testimonial List', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            '_heading_name',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Name', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'name_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .feedback__item-info h4' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .client-testimonial__slider-content__item-header .designation h4' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .feedback__item-info h4' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .client-testimonial__slider-content__item-header .designation h4' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'selector' => '{{WRAPPER}} .feedback__item-info h4, .client-testimonial__slider-content__item-header .designation h4',
            ]
        );

        $this->add_control(
            '_heading_designation',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Designation', 'weta-core' ),
            ]
        );

        $this->add_control(
            'designation_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .feedback__item-info span' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .client-testimonial__slider-content__item-header .designation span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'designation_typography',
                'selector' => '{{WRAPPER}} .feedback__item-info span, .client-testimonial__slider-content__item-header .designation span',
            ]
        );

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
                    '{{WRAPPER}} .feedback__item-content p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .feedback__item-content p' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .client-testimonial__slider-content__item-body p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .feedback__item-content p, .client-testimonial__slider-content__item-body p',
            ]
        );

        $this->add_control(
            '_heading_review',
            [
                'label' => esc_html__( 'Review', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2'],
                ],
            ]
        );

        $this->add_control( 
            'review_icon_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .feedback__item-qoute i' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2'],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'review_typography_icon',
                'selector' => '{{WRAPPER}} .feedback__item-qoute i',
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2'],
                ],
            ]
        );

        $this->add_control( 
            'author_overlay_background',
            [
                'label' => __( 'Author Overlay', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .panel' => 'background-color: {{VALUE}}!important',
                    '{{WRAPPER}} .client-testimonial__slider-nav__item.slick-active.slick-current' => 'border-color: {{VALUE}}!important',
                ],
            ]
        );

        $this->add_control(
            '_heading_layout',
            [
                'label' => esc_html__( 'Layout', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'layout_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .feedback__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .client-testimonial__slider-content__item-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control( 
            'layout_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .feedback__item' => 'background-color: {{VALUE}}!important',
                    '{{WRAPPER}} .client-testimonial__slider-content__item-body' => 'background-color: {{VALUE}}!important',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'layout_border',
                'selector' => '{{WRAPPER}} .feedback__item',
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2'],
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

        <?php if ( $settings['weta_design_style']  == 'layout-1' ) : 
            $this->add_render_attribute('title_args', 'class', 'section__title text-uppercase wow fadeIn animated' );
            $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
        ?>

        <section class="our-testimonial section-space theme-bg-1 parallax-element">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="our-testimonial__content-wrapper mb-60 mb-sm-50 mb-xs-40 d-flex justify-content-start justify-content-sm-between align-items-sm-end align-items-start flex-column flex-sm-row">
                            <div class="section__title-wrapper">
                                <?php if ( !empty($settings['weta_subheading']) ) : ?>
                                    <span class="section__subtitle mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s"><?php if(!empty($settings['weta_subheading_num'])): ?><span class="layer" data-depth="0.009"><?php echo rrdevs_kses($settings['weta_subheading_num']); ?></span><?php endif; ?> <?php echo rrdevs_kses( $settings['weta_subheading'] ); ?>
                                        <?php if($settings['weta_subheading_icon_switcher']): ?>
                                            <img class="rightLeft" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icons/arrow-right.svg" alt="arrow not found">
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
                            </div>

                            <div class="feedback__item__button-slider d-flex align-items-center wow fadeIn animated" data-wow-delay=".7s">
                                <div class="feedback__item__button-prev rr-btn">
                                    <span class="btn-wrap">
                                        <span class="text-one">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11 6L1 6" stroke="#1D1D1D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6 1L1 6L6 11" stroke="#1D1D1D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-two">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11 6L1 6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6 1L1 6L6 11" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </span>
                                </div>
                                <div class="feedback__item__button-next rr-btn">
                                    <span class="btn-wrap">
                                        <span class="text-one">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 6L11 6" stroke="#1D1D1D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6 1L11 6L6 11" stroke="#1D1D1D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="text-two">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 6L11 6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M6 1L11 6L6 11" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="swiper feedback__active">
                <div class="swiper-wrapper">
                    <?php foreach ($settings['testimonial_list'] as $index => $item) : 
                        if ( !empty($item['testimonial_image']['url']) ) {
                            $testimonial_image = !empty($item['testimonial_image']['id']) ? wp_get_attachment_image_url( $item['testimonial_image']['id']) : $item['testimonial_image']['url'];
                            $testimonial_image_alt = get_post_meta($item["testimonial_image"]["id"], "_wp_attachment_image_alt", true);
                        }
                    ?>
                        <div class="swiper-slide">
                            <div class="feedback__item wow fadeIn animated" data-wow-delay=".5s">
                                <div class="feedback__item-qoute mb-30 mb-xs-20">
                                    <?php
                                        $rr_rating = $item['rr_testi_rating'];
                                            for($i=1; $i<=$rr_rating; $i++) :
                                    ?>
                                        <i class="fa-solid fa-star"></i>
                                    <?php endfor; ?>
                                </div>

                                <div class="feedback__item-content mb-40 mb-sm-30 mb-xs-25">
                                    <?php if ( !empty($item['testimonial_content']) ) : ?>
                                        <p><?php echo rrdevs_kses($item['testimonial_content']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="feedback__item-author d-flex align-items-center justify-content-between">
                                    <div class="feedback__item-author__left d-flex align-items-center">
                                        <div class="feedback__item-thumb overflow-hidden position-relative">
                                            <?php if ( !empty($testimonial_image) ) : ?>
                                                <img src="<?php echo esc_url($testimonial_image); ?>" class="img-fluid" alt="<?php echo esc_attr($testimonial_image_alt); ?>">
                                            <?php endif; ?>
                                            <div class="panel wow"></div>
                                        </div>
                                        <div class="feedback__item-info">
                                            <?php if ( !empty($item['testimonial_name']) ) : ?>
                                                <h4 class="text-uppercase"><?php echo rrdevs_kses($item['testimonial_name']); ?></h4>
                                            <?php endif; ?>
                                            <span><?php echo rrdevs_kses($item['testimonial_designation']); ?></span>
                                        </div>
                                    </div>

                                    <div class="feedback__item-author__right">
                                        <?php if ( !empty($item['quote_switcher']) ) : ?> 
                                            <div class="icon">
                                                <svg width="27" height="22" viewBox="0 0 27 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M14.6226 20.6297L15.209 21.4327C18.5213 19.5153 21.1171 17.1283 22.981 14.2673C24.8408 11.4123 25.7856 8.49411 25.7856 5.51977C25.7856 3.93556 25.3875 2.65568 24.5036 1.77562C23.6346 0.910439 22.4762 0.5 21.0867 0.5C19.5223 0.5 18.1833 1.03837 17.1024 2.11461L17.0959 2.12114L17.0895 2.12791C16.0155 3.27956 15.4799 4.68381 15.4799 6.31073C15.4799 7.49233 15.9076 8.48997 16.7615 9.26435C17.5393 10.1146 18.5403 10.5395 19.7249 10.5395C19.8798 10.5395 20.0279 10.5355 20.1677 10.5264C19.6085 14.4125 17.7679 17.7763 14.6226 20.6297ZM0.663986 20.6297L1.25043 21.4327C4.56266 19.5153 7.15848 17.1283 9.02234 14.2673C10.8822 11.4123 11.827 8.49411 11.827 5.51977C11.827 3.93556 11.4289 2.65568 10.545 1.77563C9.67603 0.910439 8.51758 0.5 7.12811 0.5C5.56367 0.5 4.22472 1.03837 3.14381 2.11461L3.13725 2.12114L3.13094 2.12791C2.0569 3.27956 1.5213 4.68381 1.5213 6.31073C1.5213 7.49233 1.94903 8.48997 2.80292 9.26435C3.58067 10.1146 4.58165 10.5395 5.76629 10.5395C5.92123 10.5395 6.06929 10.5355 6.2091 10.5264C5.64985 14.4125 3.80928 17.7763 0.663986 20.6297Z" stroke="#F3B351"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php elseif ( $settings['weta_design_style']  == 'layout-2' ): ?>

        <div class="swiper customer-feedback__active z-0">
            <div class="swiper-wrapper">
                <?php foreach ($settings['testimonial_list'] as $index => $item) : 
                    if ( !empty($item['testimonial_image']['url']) ) {
                        $testimonial_image = !empty($item['testimonial_image']['id']) ? wp_get_attachment_image_url( $item['testimonial_image']['id']) : $item['testimonial_image']['url'];
                        $testimonial_image_alt = get_post_meta($item["testimonial_image"]["id"], "_wp_attachment_image_alt", true);
                    }
                ?>
                    <div class="swiper-slide">
                        <div class="feedback__item feedback__item-customer feedback__item-customer-animation">
                            <div class="feedback__item-qoute mb-30 mb-xs-20">
                                <?php
                                    $rr_rating = $item['rr_testi_rating'];
                                        for($i=1; $i<=$rr_rating; $i++) :
                                ?>
                                    <i class="fa-solid fa-star"></i>
                                <?php endfor; ?>
                            </div>

                            <div class="feedback__item-content mb-40 mb-sm-30 mb-xs-25">
                                <?php if ( !empty($item['testimonial_content']) ) : ?>
                                    <p><?php echo rrdevs_kses($item['testimonial_content']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="feedback__item-author d-flex align-items-center justify-content-between">
                                <div class="feedback__item-author__left d-flex align-items-center">
                                    <div class="feedback__item-thumb overflow-hidden position-relative">
                                        <?php if ( !empty($testimonial_image) ) : ?>
                                            <img src="<?php echo esc_url($testimonial_image); ?>" class="img-fluid" alt="<?php echo esc_attr($testimonial_image_alt); ?>">
                                        <?php endif; ?>
                                        <div class="panel wow"></div>
                                    </div>
                                    <div class="feedback__item-info">
                                        <?php if ( !empty($item['testimonial_name']) ) : ?>
                                            <h4 class="text-uppercase"><?php echo rrdevs_kses($item['testimonial_name']); ?></h4>
                                        <?php endif; ?>
                                        <span><?php echo rrdevs_kses($item['testimonial_designation']); ?></span>
                                    </div>
                                </div>

                                <div class="feedback__item-author__right">
                                    <?php if ( !empty($item['quote_switcher']) ) : ?> 
                                        <div class="icon">
                                            <svg width="27" height="22" viewBox="0 0 27 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14.6226 20.6297L15.209 21.4327C18.5213 19.5153 21.1171 17.1283 22.981 14.2673C24.8408 11.4123 25.7856 8.49411 25.7856 5.51977C25.7856 3.93556 25.3875 2.65568 24.5036 1.77562C23.6346 0.910439 22.4762 0.5 21.0867 0.5C19.5223 0.5 18.1833 1.03837 17.1024 2.11461L17.0959 2.12114L17.0895 2.12791C16.0155 3.27956 15.4799 4.68381 15.4799 6.31073C15.4799 7.49233 15.9076 8.48997 16.7615 9.26435C17.5393 10.1146 18.5403 10.5395 19.7249 10.5395C19.8798 10.5395 20.0279 10.5355 20.1677 10.5264C19.6085 14.4125 17.7679 17.7763 14.6226 20.6297ZM0.663986 20.6297L1.25043 21.4327C4.56266 19.5153 7.15848 17.1283 9.02234 14.2673C10.8822 11.4123 11.827 8.49411 11.827 5.51977C11.827 3.93556 11.4289 2.65568 10.545 1.77563C9.67603 0.910439 8.51758 0.5 7.12811 0.5C5.56367 0.5 4.22472 1.03837 3.14381 2.11461L3.13725 2.12114L3.13094 2.12791C2.0569 3.27956 1.5213 4.68381 1.5213 6.31073C1.5213 7.49233 1.94903 8.48997 2.80292 9.26435C3.58067 10.1146 4.58165 10.5395 5.76629 10.5395C5.92123 10.5395 6.06929 10.5355 6.2091 10.5264C5.64985 14.4125 3.80928 17.7763 0.663986 20.6297Z" stroke="#F3B351"/>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="customer-feedback__dot"></div>
        </div>

    <?php elseif ( $settings['weta_design_style']  == 'layout-3' ): 
            $this->add_render_attribute('title_args', 'class', 'section-3__title lg wow fadeIn animated' );
            $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
        ?>
        
        <section class="client-testimonial client-testimonial__background section-space overflow-hidden parallax-element" data-background="<?php if(!empty($settings['testimonial_section_bg']['url'])){echo esc_url($settings['testimonial_section_bg']['url']);} ?>">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-3__title-wrapper client-testimonial__content text-center mb-60 mb-sm-50 mb-xs-40">
                            
                            <?php if ( !empty($settings['weta_subheading']) ) : ?>
                                
                                <span class="section-3__subtitle justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s"><?php if(!empty($settings['weta_subheading_num'])): ?><span class="layer" data-depth="0.009"><?php echo rrdevs_kses($settings['weta_subheading_num']); ?></span><?php endif; ?> <?php echo rrdevs_kses( $settings['weta_subheading'] ); ?>
                                    <?php if($settings['weta_subheading_icon_switcher']): ?>
                                        <img class="rightLeft" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icons/arrow-right.svg" alt="arrow not found">
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
                        </div>
                    </div>
                </div>

                <div class="row client-testimonial__slider-wrapper align-items-center">
                    <div class="col-lg-4">
                        <div class="client-testimonial__slider-thubnail wow fadeIn animated" data-wow-delay=".4s">
                            <?php foreach ($settings['testimonial_list'] as $index => $item) : 
                                if ( !empty($item['testimonial_image_big']['url']) ) {
                                    $testimonial_image_big = !empty($item['testimonial_image_big']['id']) ? wp_get_attachment_image_url( $item['testimonial_image_big']['id'], 'full') : $item['testimonial_image_big']['url'];
                                    $testimonial_image_big_alt = get_post_meta($item["testimonial_image_big"]["id"], "_wp_attachment_image_alt", true);
                                }
                            ?>
                                <div class="client-testimonial__slider-thubnail__item">
                                    <?php if ( !empty($testimonial_image_big) ) : ?>
                                        <img src="<?php echo esc_url($testimonial_image_big); ?>" class="img-fluid" alt="<?php echo esc_attr($testimonial_image_big_alt); ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-1">
                        <div class="client-testimonial__slider-nav wow fadeIn animated" data-wow-delay=".5s">
                            <?php foreach ($settings['testimonial_list'] as $index => $item) : 
                                if ( !empty($item['testimonial_image']['url']) ) {
                                    $testimonial_image = !empty($item['testimonial_image']['id']) ? wp_get_attachment_image_url( $item['testimonial_image']['id']) : $item['testimonial_image']['url'];
                                    $testimonial_image_alt = get_post_meta($item["testimonial_image"]["id"], "_wp_attachment_image_alt", true);
                                }
                            ?>
                                <div class="client-testimonial__slider-nav__item">
                                    <?php if ( !empty($testimonial_image) ) : ?>
                                        <img src="<?php echo esc_url($testimonial_image); ?>" class="img-fluid" alt="<?php echo esc_attr($testimonial_image_alt); ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="client-testimonial__slider-content wow fadeIn animated" data-wow-delay=".7s">
                            <?php foreach ($settings['testimonial_list'] as $index => $item) : 
                                if ( !empty($item['testimonial_image']['url']) ) {
                                    $testimonial_image = !empty($item['testimonial_image']['id']) ? wp_get_attachment_image_url( $item['testimonial_image']['id']) : $item['testimonial_image']['url'];
                                    $testimonial_image_alt = get_post_meta($item["testimonial_image"]["id"], "_wp_attachment_image_alt", true);
                                }
                            ?>
                                <div class="client-testimonial__slider-content__item">
                                    <div class="client-testimonial__slider-content__item-header d-flex flex-wrap mb-35 mb-sm-30 mb-xs-25">
                                        <?php if ( !empty($item['quote_switcher']) ) : ?>
                                            <div class="icon">
                                                <img class="img-fluid" src="<?php echo get_template_directory_uri() ?>/assets/imgs/client-testimonial/quote.svg" alt="image not found">
                                            </div>
                                        <?php endif; ?>
                                        <div class="designation d-flex justify-content-start flex-column">
                                            <?php if ( !empty($item['testimonial_name']) ) : ?>
                                                <h4 class="rr-fw-medium"><?php echo rrdevs_kses($item['testimonial_name']); ?></h4>
                                            <?php endif; ?>
                                            <span><?php echo rrdevs_kses($item['testimonial_designation']); ?></span>
                                        </div>
                                    </div>
                                    <div class="client-testimonial__slider-content__item-body">
                                        <?php if ( !empty($item['testimonial_content']) ) : ?>
                                            <p><?php echo rrdevs_kses($item['testimonial_content']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <?php endif; ?>

    <?php 
	}
}

$widgets_manager->register( new WETA_Testimonial_Slider() );