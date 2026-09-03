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
 * Tp Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Faq extends Widget_Base {

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
		return 'weta_faq';
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
		return __( 'FAQ', 'weta-core' );
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
            'weta_design_style',
            [
                'label' => esc_html__('Select Layout', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__('Layout 1', 'weta-core'),
                    // 'layout-2' => esc_html__('Layout 2', 'weta-core'),
                    // 'layout-3' => esc_html__('Layout 3', 'weta-core'),
                    // 'layout-4' => esc_html__('Layout 4', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->add_control(
            'weta_shape_switch',
            [
                'label' => esc_html__( 'Show/Hide Shape', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );

        $this->end_controls_section();

        // Title and description
        $this->start_controls_section(
            'weta_faq',
            [
                'label' => esc_html__('Title & Description', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'weta_design_style' => ['layout-10'],
                ],
            ]
        );

        $this->add_control(
            'weta_subtitle',
            [
                'label' => esc_html__('Subtitle', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Frequently Ask Questions', 'weta-core'),
                'placeholder' => esc_html__('Type Subtitle Text', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2', 'layout-3'],
                ],
            ]
        );  

        $this->add_control(
            'weta_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('What Mostly People Ask Us About', 'weta-core'),
                'placeholder' => esc_html__('Type Heading Text', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2', 'layout-3'],
                ],
            ]
        );

        $this->add_control(
            'weta_description',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Praesent mollis tortor augue, lacinia vestibulum sem cursus sed. Suspendisse quis sapien sed odio dictum fringilla eget eget ligula Duis varius ornare quam, interdum libero elementum bibendum arcu efficitur vehicula.', 'weta-core'),
                'placeholder' => esc_html__('Type section description here', 'weta-core'),
                'condition' => [
                    'weta_design_style' => ['layout-10'],
                ],
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


        // Image
        $this->start_controls_section(
            'weta_image_content',
            [
                'label' => esc_html__('Image', 'weta-core'),
                'description' => esc_html__( 'Seoq Image', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'weta_image',
            [
                'type' => Controls_Manager::MEDIA,
                'label' => __( 'Image', 'weta-core' ),
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );

        $this->end_controls_section();


        // weta_faq_list
        $this->start_controls_section(
            '_weta_faq_list',
            [
                'label' => esc_html__('FAQ List', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'weta_faq_active',
            [
                'label' => esc_html__( 'Open', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'active',
                'default' => '',
            ]
        );

        $repeater->add_control(
            'weta_faq_title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'weta_faq_description', [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'weta_faq_image',
            [
                'type' => Controls_Manager::MEDIA,
                'label' => __( 'Image', 'weta-core' ),
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'weta_design_style' => ['layout-10'],
                ],
            ]
        );

        $this->add_control(
            'weta_faq_list',
            [
                'label' => esc_html__('Faq List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'weta_faq_open' => 'active',
                        'weta_faq_title' => esc_html__('Can I specify a delivery date when ordering?', 'weta-core'),
                        'weta_faq_description' => esc_html__('There are many variations of passages of is psum the majority have suffered alteration in some we by injected humour', 'weta-core'),
                        'weta_faq_image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'weta_faq_title' => esc_html__('Why are the cleaning rate will be lowest price?', 'weta-core'),
                        'weta_faq_description' => esc_html__('We denounce with righteous indignation and dislike men demoralized by the charms of pleasure of the moment, they cannot foresee the pain and trouble.', 'weta-core'),
                        'weta_faq_image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'weta_faq_title' => esc_html__('Your pricing table will be possible?', 'weta-core'),
                        'weta_faq_description' => esc_html__('We denounce with righteous indignation and dislike men demoralized by the charms of pleasure of the moment, they cannot foresee the pain and trouble.', 'weta-core'),
                        'weta_faq_image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'weta_faq_title' => esc_html__('Why do I need to remove toys from the pool?', 'weta-core'),
                        'weta_faq_description' => esc_html__('We denounce with righteous indignation and dislike men demoralized by the charms of pleasure of the moment, they cannot foresee the pain and trouble.', 'weta-core'),
                        'weta_faq_image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'weta_faq_title' => esc_html__('Do I have to cover my pool when it rains?', 'weta-core'),
                        'weta_faq_description' => esc_html__('We denounce with righteous indignation and dislike men demoralized by the charms of pleasure of the moment, they cannot foresee the pain and trouble.', 'weta-core'),
                        'weta_faq_image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                ],
                'title_field' => '{{{ weta_faq_title }}}',
            ]
        );
        $this->end_controls_section();


        // Design Style Control
        $this->start_controls_section(
            '_section_layout_style',
            [
                'label' => __( 'Design Layout', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .our-features' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background_color',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .our-features' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_title_style',
            [
                'label' => __( 'Title', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'weta_design_style' => ['layout-10'],
                ],
            ]
        );

        // Subheading
        $this->add_control(
            '_subheading_options',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subheading', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'subheading_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section-title__tagline' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subheading_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-title__tagline' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subheading_typography',
                'selector' => '{{WRAPPER}} .section-title__tagline',
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
                    '{{WRAPPER}} .section-title__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-title__title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .section-title__title',
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
                    '{{WRAPPER}} .section-title__text' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .section-title__style2 .text-box p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-title__text' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-title__style2 .text-box p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .section-title__text, .section-title__style2 .text-box p',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_faq_list_style',
            [
                'label' => __( 'Faq List', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Icon', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'faq_icon_size',
            [
                'label' => __( 'Font Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .faq-one-accrodion .accrodion-title-inner .icon span::before' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            '_heading_faq_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'faq_title_typography',
                'selector' => '{{WRAPPER}} .faq-one-accrodion .accrodion-title h4',
            ]
        );

        $this->start_controls_tabs( 'faq_list_tabs' );

        $this->start_controls_tab(
            'faq_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );

        $this->add_control(
            'faq_title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rs__faq .accordion-button' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'faq_title_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rs__faq .accordion-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'faq_active_tab',
            [
                'label' => esc_html__( 'Active', 'weta-core' ),
            ]
        );

        $this->add_control(
            'faq_title_color_active',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rs__faq .accordion-item:has(.collapse.show)' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'faq_title_background_active',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rs__faq .accordion-item:has(.collapse.show)' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            '_heading_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'faq_description_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rs__faq .accordion-body > p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'faq_description_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rs__faq .accordion-body' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'faq_description_typography',
                'selector' => '{{WRAPPER}} .rs__faq .accordion-body > p',
            ]
        );

        $this->add_responsive_control(
            'faq_description_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .rs__faq .accordion-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        <?php if ( $settings['weta_design_style']  == 'layout-1' ):
            $this->add_render_attribute('title_args', 'class', 'section-title wow fade-in-bottom rrdevs-el-title');
            $this->add_render_attribute('title_args', 'data-wow-delay', '600ms');

            if ( !empty($settings['weta_image']['url']) ) {
                $weta_image_url = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'],'full') : $settings['weta_image']['url'];
                $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
            }
        ?>

        <!--our-features start-->
        <section class="our-features section-space__faq faq overflow-hidden">
            <div class="container">
                <?php if(!empty($settings['weta_shape_switch'])) : ?>
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
                    <?php if(!empty($weta_image_url)) : ?>
                    <div class="col-xl-5">
                        <div class="our-features__media  wow fadeIn animated" data-wow-delay=".3s">
                            <img src="<?php echo esc_url($weta_image_url); ?>" alt="image not found">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="col-xl-7">
                        <div class="rs__faq">
                            <div class="accordion" id="accordionExample">
                                <?php foreach ($settings['weta_faq_list'] as $id => $item) :
                                    // active class
                                    $collapsed_tab = ($id == 0) ? '' : 'collapsed';
                                    $area_expanded = ($id == 0) ? 'true' : 'false';
                                    $active_show_tab = ($id == 0) ? 'collapse show' : 'collapse';
                                ?>
                                <div class="accordion-item  wow fadeIn animated" data-wow-delay=".3s">
                                    <h5 class="accordion-header" id="headingOne">
                                        <button class="accordion-button <?php echo esc_attr($collapsed_tab); ?>" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapseOne-<?php echo esc_attr($id); ?>" 
                                        aria-expanded="<?php echo esc_attr($area_expanded); ?>" 
                                        aria-controls="collapseOne-<?php echo esc_attr($id); ?>">
                                            <?php echo rrdevs_kses($item['weta_faq_title' ]); ?>
                                        </button>
                                    </h5>

                                    <div id="collapseOne-<?php echo esc_attr($id); ?>" 
                                        class="accordion-collapse collapse <?php echo esc_attr($active_show_tab); ?>" 
                                        aria-labelledby="headingOne" 
                                        data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <p><?php echo rrdevs_kses($item['weta_faq_description' ]); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--our-features end-->

        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ):

            $this->add_render_attribute('title_args', 'class', 'section-title__one-title weta-el-title');

            if ( !empty($settings['weta_image']['url']) ) {
                $weta_image_url = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'],'full') : $settings['weta_image']['url'];
                $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
            }

            if ( !empty($settings['weta_image_shape_01']['url']) ) {
                $weta_image_shape_01_url = !empty($settings['weta_image_shape_01']['id']) ? wp_get_attachment_image_url( $settings['weta_image_shape_01']['id'],'full') : $settings['weta_image_shape_01']['url'];
                $weta_image_shape_01_alt = get_post_meta($settings["weta_image_shape_01"]["id"], "_wp_attachment_image_alt", true);
            }

            if ( !empty($settings['weta_image_shape_02']['url']) ) {
                $weta_image_shape_02_url = !empty($settings['weta_image_shape_02']['id']) ? wp_get_attachment_image_url( $settings['weta_image_shape_02']['id'],'full') : $settings['weta_image_shape_01']['url'];
                $weta_image_shape_02_alt = get_post_meta($settings["weta_image_shape_02"]["id"], "_wp_attachment_image_alt", true);
            }
        ?>

        <!-- faq two start -->
        <div class="faq-two">
            <div class="row g-0 align-items-center">
                <div class="col-xxl-6 col-xl-6 col-lg-12 col-md-12">
                    <div class="faq-two-content__wrapper">
                        <?php if(!empty($settings['weta_shape_switch'])) : ?>
                            <div class="faq-two__animation1">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/arrow-snack.svg" alt="shape">
                            </div>
                            <div class="faq-two__animation2">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/6.svg" alt="shape">
                            </div>
                            <div class="faq-two__animation3">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/5.svg" alt="shape">
                            </div>
                            <div class="faq-two__animation4">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/11.svg" alt="shape">
                            </div>
                            <div class="faq-two__animation5">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/arrow-circle-line-2.png" alt="shape">
                            </div>
                        <?php endif; ?>

                        <div class="fac-two__content">
                            <?php
                                if ( !empty($settings['weta_title' ]) ) :
                                    printf( '<%1$s %2$s>%3$s</%1$s>',
                                        tag_escape( $settings['weta_title_tag'] ),
                                        $this->get_render_attribute_string( 'title_args' ),
                                        rrdevs_kses( $settings['weta_title' ] )
                                        );
                                endif;
                            ?>

                            <div class="faq-two__faq">
                                <div class="accordion" id="accordionExample">
                                    <?php foreach ($settings['weta_faq_list'] as $id => $item) :
                                        // active class
                                        $collapsed_tab = ($id == 0) ? '' : 'collapsed';
                                        $area_expanded = ($id == 0) ? 'true' : 'false';
                                        $active_show_tab = ($id == 0) ? 'collapse show' : 'collapse';
                                    ?>
                                    <div class="accordion-items wow fadeInUp" data-wow-delay=".3s">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-buttons <?php echo esc_attr($collapsed_tab); ?>"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#faqtwocollapseOne-<?php echo esc_attr($id); ?>"
                                            aria-expanded="<?php echo esc_attr($area_expanded); ?>"
                                            aria-controls="faqtwocollapseOne-<?php echo esc_attr($id); ?>">
                                                <?php echo rrdevs_kses($item['weta_faq_title' ]); ?>
                                            </button>
                                        </h2>

                                        <div id="faqtwocollapseOne-<?php echo esc_attr($id); ?>"
                                        class="accordion-collapse collapse <?php echo esc_attr($active_show_tab); ?>"
                                        aria-labelledby="headingOne-<?php echo esc_attr($id); ?>"
                                        data-bs-parent="#accordionExample">
                                            <?php echo \Elementor\Plugin::instance()->frontend->get_builder_content($item['template'], true); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-xl-6 col-lg-12 col-md-12">
                    <div class="faq-two__right wow fadeInUp" data-wow-delay=".3s">
                        <div class="faq-two__right-img">
                            <?php if(!empty($weta_image_url)) : ?>
                            <img src="<?php echo esc_url($weta_image_url); ?>" alt="faq img">
                            <?php endif; ?>

                            <?php if(!empty($weta_image_shape_01_url)) : ?>
                            <div class="faq-two__right-img1">
                                <img src="<?php echo esc_url($weta_image_shape_01_url); ?>" alt="faq img">
                            </div>
                            <?php endif; ?>

                            <?php if(!empty($weta_image_shape_02_url)) : ?>
                            <div class="faq-two__right-img2">
                                <img src="<?php echo esc_url($weta_image_shape_02_url); ?>" alt="faq img">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- faq two end -->


        <?php elseif ( $settings['weta_design_style']  == 'layout-3' ):

            $this->add_render_attribute('title_args', 'class', 'section-title__two-title mb-10 weta-el-title');

            if ( !empty($settings['weta_image']['url']) ) {
                $weta_image_url = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'],'full') : $settings['weta_image']['url'];
                $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
            }
        ?>

        <section></section>

        <?php elseif ( $settings['weta_design_style']  == 'layout-4' ): ?>

        <div class="faq-one">
            <div class="faq-one__content">
                <div class="faq-one__faq">
                    <div class="accordion" id="accordionExample">
                        <?php foreach ($settings['weta_faq_list'] as $id => $item) :
                            // active class
                            $collapsed_tab = ($id == 0) ? '' : 'collapsed';
                            $area_expanded = ($id == 0) ? 'true' : 'false';
                            $active_show_tab = ($id == 0) ? 'collapse show' : 'collapse';
                        ?>
                        <div class="accordion-items">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-buttons <?php echo esc_attr($collapsed_tab); ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseOne-<?php echo esc_attr($id); ?>"
                                aria-expanded="<?php echo esc_attr($area_expanded); ?>"
                                aria-controls="collapseOne-<?php echo esc_attr($id); ?>">
                                    <?php echo rrdevs_kses($item['weta_faq_title' ]); ?>
                                </button>
                            </h2>

                            <div id="collapseOne-<?php echo esc_attr($id); ?>"
                            class="accordion-collapse collapse <?php echo esc_attr($active_show_tab); ?>"
                            aria-labelledby="headingOne-<?php echo esc_attr($id); ?>"
                            data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <?php echo rrdevs_kses($item['weta_faq_description' ]); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <?php
	}
}

$widgets_manager->register( new weta_Faq() );