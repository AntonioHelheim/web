<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
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
class WETA_Work_Process extends Widget_Base {

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
		return 'weta_work_process';
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
		return __( 'Work Process', 'weta-core' );
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
                ],
            ]
        );

        $this->add_control(
			'bg_shape_show',
			[
				'label' => esc_html__( 'Show Shape', 'weta-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => esc_html__( 'Yes', 'weta-core' ),
					'no' => esc_html__( 'No', 'weta-core' ),
				],
                'condition' => [
                    'weta_design_style' => ['layout-2'],
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
                    'weta_design_style' => ['layout-2'],
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

        // Work Process List
        $this->start_controls_section(
            '_content_work_process',
            [
                'label' => esc_html__('Work Process List', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
			'icon_type',
			[
				'label' => esc_html__( 'Image Type', 'weta-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => esc_html__( 'Image', 'weta-core' ),
					'icon' => esc_html__( 'Icon', 'weta-core' ),
				],
			]
		);

        $repeater->add_control(
            'image',
            [
                'label' => esc_html__('Upload Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'icon_type' => 'image',
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
                    'default' => 'icon-house',
                    'condition' => [
                        'icon_type' => 'icon',
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
                    ],
                ]
            );
        }

        $repeater->add_control(
            'title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'steps', [
                'label' => esc_html__('Step Number', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('wLorem ipsum dolor sit amet designersi consectetur adipiscing here', 'weta-core'),
                'placeholder' => esc_html__('Type section description here', 'weta-core'),
            ]
        );

        $repeater->add_control(
			'shape_show',
			[
				'label' => esc_html__( 'Show Shape', 'weta-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => esc_html__( 'Yes', 'weta-core' ),
					'no' => esc_html__( 'No', 'weta-core' ),
				],
			]
		);

        $this->add_control(
            'weta_work_process_list',
            [
                'label' => esc_html__('Work Process List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'title' => esc_html__('Download app', 'weta-core'),
                        'description' => esc_html__('Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core'),
                        'steps' => esc_html__('Step 1', 'weta-core'),
                        'shape_show' => 'yes',
                    ],
                    [
                        'title' => esc_html__('Setup your profile', 'weta-core'),
                        'description' => esc_html__('Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core'),
                        'steps' => esc_html__('Step 2', 'weta-core'),
                        'shape_show' => 'yes',
                    ],
                    [
                        'title' => esc_html__('Enjoy the features!', 'weta-core'),
                        'description' => esc_html__('Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core'),
                        'steps' => esc_html__('Step 3', 'weta-core'),
                        'shape_show' => 'no',
                    ],
                    
                ],
                'title_field' => '{{{ title }}}',
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
                    '{{WRAPPER}} .work-step__item-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .why-choose-us.section-space' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_bg_color',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .work-step__item-wrapper' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .why-choose-us.section-space' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'content_box_shadow',
            [
                'label' => esc_html__( 'Box Shadow', 'weta-core' ),
                'type' => Controls_Manager::BOX_SHADOW,
                'selectors' => [
                    '{{SELECTOR}} .work-step__item-wrapper' => 'box-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{SPREAD}}px {{COLOR}};',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );

        $this->add_responsive_control(
            'content_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .work-step__item-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );

        $this->end_controls_section();


        // style tab here
        $this->start_controls_section(
            '_section_style_content',
            [
                'label' => __( 'Title / Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'weta_design_style' => ['layout-2'],
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
                    '{{WRAPPER}} .section-3__subtitle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_typography',
                'selector' => '{{WRAPPER}} .section-3__subtitle',
            ]
        );

        $this->add_control(
            'sub_title_number_color',
            [
                'label' => __( 'Box Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-3__subtitle span' => 'color: {{VALUE}}',
                ],
                'separator' => 'before',

            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_num_typography',
                'selector' => '{{WRAPPER}} .section-3__subtitle span',
            ]
        );

        $this->add_control(
            'sub_title_number_bg_color',
            [
                'label' => __( 'BG Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
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
                    '{{WRAPPER}} .section-3__title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .section-3__title.lg',
            ]
        );

        $this->end_controls_section();


        // List style
        $this->start_controls_section(
            '_section_info_list_style',
            [
                'label' => __( 'Work Process List', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Icon', 'weta-core' ),
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            'list_icon_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .process-item .process-icon' => 'fill: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            'layout_border_color',
            [
                'label' => __( 'Border Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .process-item .process-icon' => 'border-color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            '_content_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'list_title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} h4.work-step__item-title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .why-choose-us__item__content h4' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'list_title_typography',
                'selector' => '{{WRAPPER}} h4.work-step__item-title, .why-choose-us__item__content h4',
            ]
        );

        $this->add_control(
            '_content_des',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'list_des_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .work-step__item__body p' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .why-choose-us__item__content p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'list_des_typography',
                'selector' => '{{WRAPPER}} .work-step__item__body p, .why-choose-us__item__content p',
            ]
        );

        $this->add_control(
            '_content_step',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Step', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'list_step_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .work-step__item-step' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .why-choose-us__item__content .step' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'list_step_bg_color',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .work-step__item-step' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .why-choose-us__item__content .step' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'list_step_typography',
                'selector' => '{{WRAPPER}} .work-step__item-step, .why-choose-us__item__content .step',
            ]
        );

        $this->add_control(
            '_content_shape',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Shape', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'list_shape_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .work-step__item-right .readmore svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .why-choose-us__item-icon svg' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'list_shape_bg_color',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .work-step__item-right .readmore' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .why-choose-us__item-icon' => 'background-color: {{VALUE}}',
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
            $this->add_render_attribute('title_args', [
                'class' => 'section-title wow fade-in-bottom',
                'data-wow-delay' => '500ms',
            ] );
	    ?>
            <div class="work-step__item-wrapper d-flex flex-column flex-xl-row justify-content-between">
                <?php foreach ($settings['weta_work_process_list'] as $key => $item) :     
                    if ( !empty($item['image']['url']) ) {
                        $weta_image_url = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                        $weta_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                    } 
                
                ?>
                    <div class="work-step__item d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between wow fadeIn animated" data-wow-delay=".5s">
                        <div class="work-step__item-left">
                            <div class="work-step__item__header mb-25 d-flex align-items-center justify-content-between">
                                <div class="work-step__item-icon">
                                    <?php if($item['icon_type'] == 'icon') : ?>
                                        <?php if (!empty($item['icon']) || !empty($item['selected_icon']['value'])) : ?>
                                            <?php weta_render_icon($item, 'icon', 'selected_icon'); ?></span>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <?php if (!empty($item['image']['url'])): ?>
                                            <img src="<?php print $weta_image_url; ?>" alt="<?php print $weta_image_alt; ?>">
                                        <?php endif; ?>  
                                    <?php endif; ?>
                                </div>
                                <?php if(!empty($item['steps' ])): ?>
                                    <button class="work-step__item-step"><?php echo rrdevs_kses($item['steps' ]); ?></button>
                                <?php endif; ?>
                            </div>

                            <div class="work-step__item__body">
                            <?php if(!empty($item['title' ])): ?>
                                <h4 class="work-step__item-title mb-15"><?php echo rrdevs_kses($item['title' ]); ?></h4>
                            <?php endif; ?>
                            <?php if(!empty($item['description' ])): ?>
                                <p><?php echo rrdevs_kses($item['description' ]); ?></p>
                            <?php endif; ?>
                            
                        </div>
                        </div>
                        
                        <?php if($item['shape_show'] == 'yes'): ?>
                            <div class="work-step__item-right">
                                <span class="readmore">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 7H13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 1L13 7L7 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ): 
                $this->add_render_attribute('title_args', 'class', 'section-3__title lg wow fadeIn animated');
                $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
            ?>
            
            <section class="why-choose-us section-space overflow-hidden parallax-element position-relative z-1">
                <div class="container position-relative z-1">
                    <?php if($settings['bg_shape_show'] == 'yes'): ?>
                        <div class="why-choose-us__background rightLeft"><img src="<?php echo get_template_directory_uri() ?>/assets/imgs/why-choose-us/background-svg.svg" alt=""></div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="section-3__title-wrapper text-center mb-60 mb-sm-50 mb-xs-40">
                                <?php if ( !empty($settings['weta_subheading']) ) : ?>
                                    <span class="section-3__subtitle section-3__subtitle-2 flex-wrap justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                                        <?php if($settings['weta_subheading_icon_switcher']): ?>
                                            <img class="leftRight" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icons/mad-emoji.svg" alt="arrow not found">
                                        <?php endif; ?> 
                                         <?php echo rrdevs_kses( $settings['weta_subheading'] ); ?> <?php if(!empty($settings['weta_subheading_num'])): ?><span class="layer" data-depth="0.009"><?php echo rrdevs_kses($settings['weta_subheading_num']); ?><?php endif; ?> </span>
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

                    <div class="row mb-minus-60 position-relative z-1">
                        <div class="why-choose-us__item-bar"></div>
                        <?php foreach ($settings['weta_work_process_list'] as $key => $item) :     
                            if ( !empty($item['image']['url']) ) {
                                $weta_image_url = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                                $weta_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                            } 
                        ?>
                            <div class="col-xl-4 col-lg-6">
                                <div class="why-choose-us__item text-center mb-60 wow fadeIn animated" data-wow-delay=".5s">
                                    <div class="why-choose-us__item-icon">
                                        <?php if($item['icon_type'] == 'icon') : ?>
                                            <?php if (!empty($item['icon']) || !empty($item['selected_icon']['value'])) : ?>
                                                <?php weta_render_icon($item, 'icon', 'selected_icon'); ?></span>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <?php if (!empty($item['image']['url'])): ?>
                                                <img src="<?php print $weta_image_url; ?>" alt="<?php print $weta_image_alt; ?>">
                                            <?php endif; ?>  
                                        <?php endif; ?>
                                    </div>
                                    <div class="why-choose-us__item-arrow-bottom">
                                        <img src="<?php echo get_template_directory_uri() ?>/assets/imgs/why-choose-us/why-choose-us__item-bottom-arrow.png" alt="image not found">
                                    </div>

                                    <div class="why-choose-us__item__content mt-25">
                                        <?php if(!empty($item['steps' ])): ?>
                                            <span class="step mb-25 d-inline-block"><?php echo rrdevs_kses($item['steps' ]); ?></span>
                                        <?php endif; ?>
                                        <?php if(!empty($item['title' ])): ?>
                                            <h4 class="work-step__item-title mb-15"><?php echo rrdevs_kses($item['title' ]); ?></h4>
                                        <?php endif; ?>
                                        <?php if(!empty($item['description' ])): ?>
                                            <p><?php echo rrdevs_kses($item['description' ]); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </section>

        <?php endif; ?>
        <?php
	}
}

$widgets_manager->register( new WETA_Work_Process() );