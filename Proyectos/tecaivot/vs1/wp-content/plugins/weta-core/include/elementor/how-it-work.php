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
class WETA_How_It_Work extends Widget_Base {

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
		return 'weta_how_it_work';
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
		return __( 'How It Work', 'weta-core' );
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

        // Work Process List
        $this->start_controls_section(
            '_content_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'shape_img_switch',
            [
                'label' => esc_html__( 'Shape ON/OFF', 'seoq-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'seoq-core' ),
                'label_off' => esc_html__( 'No', 'seoq-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'subheading',
            [
                'label' => esc_html__('Sub Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('How It Works', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
			'heading',
			[
				'label' => esc_html__( 'Title', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
				'type' => Controls_Manager::TEXT,
                'default' => esc_html__('How We Deliver Your Parcel', 'weta-core'),
                'label_block' => true,
			]
		);

        $this->end_controls_section();

        // Work Process List
        $this->start_controls_section(
            '_content_how_it_work',
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
            'description', [
                'label' => esc_html__('No.', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
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
            'link_text', [
                'label' => esc_html__('Button Text', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Read More', 'weta-core'),
                'condition' => [
                    'link_switcher' => 'yes'
                ],
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
                'label' => esc_html__( 'Link link', 'weta-core' ),
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

        $this->add_control(
            'weta_work_process_list',
            [
                'label' => esc_html__('Work Process List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'title' => esc_html__('Parcel Register', 'weta-core'),
                        'description' => __('One of the key components of best logistics industy', 'weta-core'),
                    ],
                    [
                        'title' => __('Parcel Loading', 'weta-core'),
                        'description' => __('One of the key components of best logistics industy', 'weta-core'),
                    ],
                    [
                        'title' => __('Parcel In-transit', 'weta-core'),
                        'description' => __('One of the key components of best logistics industy', 'weta-core'),
                    ],
                    [
                        'title' => __('Parcel Delivery', 'weta-core'),
                        'description' => __('One of the key components of best logistics industy', 'weta-core'),
                    ],
                ],
                'title_field' => '{{{ title }}}',
            ]
        );

        $this->end_controls_section();


        // Price Content
        $this->start_controls_section(
            'weta_faq_price_content',
            [
                'label' => esc_html__('Price Info', 'weta-core'),
                'description' => esc_html__( 'WETA Price Info', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            'weta_price',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Price', 'weta-core'),
                'placeholder' => esc_html__('Type Heading Text', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            'weta_price_btn',
            [
                'label' => esc_html__('Button Text', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Free Try', 'weta-core'),
                'placeholder' => esc_html__('Type Button Text', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            'weta_price_btn_url',
            [
                'label' => esc_html__('URL', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '#',
                'title' => esc_html__('BTN url', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
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
                    '{{WRAPPER}} .process-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_margin',
            [
                'label' => __( 'Content Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .process-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_style_title',
            [
                'label' => __( 'Title & Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_subheading',
            [
                'label' => esc_html__( 'Subheading', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
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
                'label' => __( 'Color', 'weta-core' ),
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

        $this->add_control(
            '_heading',
            [
                'label' => esc_html__( 'Heading', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'heading_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section-heading .section-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'heading_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section-heading .section-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .section-heading .section-title',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_info_list_style',
            [
                'label' => __( 'Work Process List', 'weta-core' ),
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
                    '{{WRAPPER}} .service-box .service-content .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-box .service-content .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label' => __( 'Color (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-box .service-content .title a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .service-box .service-content .title',
            ]
        );

        $this->add_control(
            '_content_subtitle',
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
                    '{{WRAPPER}} .service-box .service-content p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-box .service-content p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .service-box .service-content p',
            ]
        );

        $this->add_control(
            '_content_link',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Link', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'link_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-box .service-content .read-more' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'link_color_hover',
            [
                'label' => __( 'Color (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-box .service-content .read-more:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'link_typography',
                'selector' => '{{WRAPPER}} .service-box .service-content .read-more',
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

            <section class="service-section service-2 bg-grey pt-120 pb-120" data-background="<?php echo get_template_directory_uri(); ?>/assets/img/bg-img/map-bg.png">
                <div class="service-map"></div>
                <?php if ( !empty($settings['shape_img_switch']) ) : ?>
                    <div class="truck"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/shapes/truck.png" alt="truck"></div>
                <?php endif; ?>
                <div class="container">
                    <div class="section-heading text-center">
                        <?php if ( !empty($settings['subheading']) ) : ?>
                            <h4 class="sub-heading wow fade-in-bottom" data-wow-delay="400ms">
                                <?php print rrdevs_kses( $settings['subheading'] ); ?>
                            </h4>
                        <?php endif; ?>
                        <?php if ( !empty($settings['heading']) ) : ?>
                            <h2 class="section-title wow fade-in-bottom" data-wow-delay="600ms">
                                <?php print rrdevs_kses( $settings['heading'] ); ?>
                            </h2>
                        <?php endif; ?>
                    </div>
                    <div class="row gy-lg-0 gy-4 service-items">
                        <?php foreach ($settings['weta_work_process_list'] as $item) : 
                        
                            if ( !empty($item['image']['url']) ) {
                                $weta_image_url = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                                $weta_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
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
                        <div class="col-lg-3 col-md-6">
                            <div class="service-box text-center">
                                <?php if($item['icon_type'] !== 'image') : ?>
                                    <?php if (!empty($item['icon']) || !empty($item['selected_icon']['value'])) : ?>
                                        <div class="service-icon how-it-work-icon">
                                            <?php weta_render_icon($item, 'icon', 'selected_icon'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <?php if (!empty($item['image']['url'])): ?>
                                        <div class="service-icon how-it-work-icon">
                                            <img src="<?php print $weta_image_url; ?>" alt="<?php print $weta_image_alt; ?>">
                                        </div>
                                    <?php endif; ?>  
                                <?php endif; ?>
                                
                                <div class="service-content">
                                    <h3 class="title">
                                        <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>">
                                            <?php echo rrdevs_kses($item['title' ]); ?>
                                        </a>
                                    </h3>
                                    <p><?php echo rrdevs_kses($item['description' ]); ?></p>
                                    <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>" class="read-more">
                                        <?php echo rrdevs_kses($item['link_text' ]); ?> <i class="fa-regular fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        <?php
	}
}

$widgets_manager->register( new WETA_How_It_Work() );