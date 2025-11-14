<?php

namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;
use \Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Services_Box extends Widget_Base {

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
        return 'weta_services_box';
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
        return __( 'Services Box', 'weta-core' );
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
        return [ 'weta-services' ];
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
                ],
                'default' => 'layout-1',
            ]
        );

        $this->end_controls_section();


        // Services Box
        $this->start_controls_section(
            'weta_services_box',
            [
                'label' => esc_html__('Services Box', 'weta-core'),
                'description' => esc_html__( 'Service Box', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
			'weta_service_image_icon_switcher',
			[
				'label' => esc_html__( 'Image Type', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => esc_html__( 'Image', 'weta-core' ),
					'icon' => esc_html__( 'Icon', 'weta-core' ),
                    'svg' => esc_html__( 'SVG Code', 'weta-core' ),
				],
			]
		);

        $this->add_control(
            'weta_service_icon_image',
            [
                'label' => esc_html__('Upload Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'weta_service_image_icon_switcher' => 'image',
                ],
            ]
        );

        $this->add_control(
            'weta_service_icon_svg',
            [
                'show_label' => false,
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'placeholder' => esc_html__('SVG Code Here', 'weta-core'),
                'condition' => [
                    'weta_service_image_icon_switcher' => 'svg'
                ]
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $this->add_control(
                'weta_service_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'eicon-user-circle-o',
                    'condition' => [
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        } else {
            $this->add_control(
                'weta_service_selected_icon',
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
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        }

		$this->add_control(
			'weta_services_list_title',
			[
				'label' => esc_html__( 'Title', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Service Box Title' , 'weta-core' ),
				'label_block' => true,
			]
		);    

        $this->add_control(
            'weta_services_list_title_url',
            [
                'label' => esc_html__('URL', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '#',
                'title' => esc_html__('Title URL', 'weta-core'),
                'label_block' => true,
            ]
        );    

        $this->add_control(
            'weta_services_list_desc',
            [
                'label' => esc_html__( 'Description', 'weta-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Customer satisfaction is crucial for amohlodi business as it leads to customer' , 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();


        // Button 
        $this->start_controls_section(
            '_content_button',
            [
                'label' => esc_html__('Button', 'weta-core'),
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'link_switcher',
            [
                'label' => esc_html__( 'Show Link', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'link_text',
            [
                'label' => esc_html__( 'Link Text', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__( 'Read More', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'link_type',
            [
                'label' => esc_html__( 'Service Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'condition' => [
                    'link_switcher' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => esc_html__( 'Service Link link', 'weta-core' ),
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

        $this->add_control(
            'page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'link_type' => '2',
                    'link_switcher' => 'yes',
                ]
            ]
        );

        $this->end_controls_section();
    

        // Service Box Style
        $this->start_controls_section(
            'weta_services_box_style',
            [
                'label' => __( 'Services Box', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'service_box_content_padding',
            [
                'label' => __( 'Service Box Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .service-3 .service-item .service-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'service_box_content_background',
            [
                'label' => __( 'Service Box Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-3 .service-item .service-content' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'service_box_content_before_background',
            [
                'label' => __( 'Service Box Top Border Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-3 .service-item::before' => 'background-color: {{VALUE}}',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Service Box Border', 'weta-core' ),
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .service-3 .service-item .service-content',
            ]
        );

        $this->add_control(
            '_icon_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Icon', 'weta-core' ),
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __( 'Icon Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
					'unit' => 'px',
					'size' => 60,
				],
                'selectors' => [
                    '{{WRAPPER}} .service-3 .service-item .service-content .service-icon svg' => 'width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .service-3 .service-item .service-content .service-icon' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __( 'Icon Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-3 .service-item .service-content .service-icon svg' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .service-3 .service-item .service-content .service-icon' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            'service_icon_bg_color',
            [
                'label' => __( 'Icon BG Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-3 .service-item .service-content .service-icon' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            '_service_list_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            '_service_list_title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs(
            '_service_list_style_tabs'
        );

        $this->start_controls_tab(
            '_service_list_style_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );

        $this->add_control(
            '_service_list_title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            '_service_list_style_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );

        $this->add_control(
            '_service_list_title_color_hover',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content .title a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => '_service_list_title_typography',
                'selector' => '{{WRAPPER}} .service-item .service-content .title a, .service-box .service-content .title',
            ]
        );


        // Service Description Style 
        $this->add_control(
            '_service_list_heading_desc',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            '_service_list_desc_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            '_service_list_desc_color',
            [
                'label' => __( 'Description Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => '_service_list_desc_typography',
                'selector' => '{{WRAPPER}} .service-item .service-content p, .service-box .service-content p',
            ]
        );


        // Service Button Style 
        $this->add_control(
            '_service_list_heading_btn',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Button', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            '_service_list_btn_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content .read-more' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            '_service_list_btn_color',
            [
                'label' => __( 'Button Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content .read-more' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .service-3 .service-item .service-content .read-more i' => 'color: {{VALUE}}',
                ],
            ]
        );        

        $this->add_control(
            '_service_list_btn_hover_color',
            [
                'label' => __( 'Button Hover Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-item .service-content .read-more:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .service-3 .service-item .service-content .read-more:hover i' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => '_service_list_btn_typography',
                'selector' => '{{WRAPPER}} .service-item .service-content .read-more',
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
        $this->add_render_attribute('title_args', 'class', 'title section-title__title');

        ?>

        <?php if ( $settings['weta_design_style']  == 'layout-1' ): 
            if ( !empty($settings['weta_service_icon_image']['url']) ) {
                $weta_service_icon_image = !empty($settings['weta_service_icon_image']['id']) ? wp_get_attachment_image_url( $settings['weta_service_icon_image']['id'], 'full') : $settings['weta_service_icon_image']['url'];
                $weta_service_icon_image_alt = get_post_meta($settings["weta_service_icon_image"]["id"], "_wp_attachment_image_alt", true);
            } 

            // Link
            if ('2' == $settings['link_type']) {
                $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['page_link']));
                $this->add_render_attribute('weta-button-arg', 'target', '_self');
                $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
                $this->add_render_attribute('weta-button-arg', 'class', 'read-more');
                $this->add_render_attribute('weta-button-arg', 'data-wow-delay', '600ms');
            } else {
                if ( ! empty( $settings['link']['url'] ) ) {
                    $this->add_link_attributes( 'weta-button-arg', $settings['link'] );
                    $this->add_render_attribute('weta-button-arg', 'class', 'read-more');
                }
            }
        ?>

        <div class="service-3">
            <div class="service-item wow fade-in-bottom" data-wow-delay="400ms">
                <div class="service-content">
                    <!-- Service Icon Start -->
                    <?php if (!empty($settings['weta_service_icon_image']['url'])): ?>
                        <div class="service-icon">
                            <img src="<?php echo $settings['weta_service_icon_image']['url']; ?>" alt="<?php echo get_post_meta(attachment_url_to_postid($settings['weta_service_icon_image']['url']), '_wp_attachment_image_alt', true); ?>">
                        </div>
                    <?php endif; ?>
                    <!-- Service Icon  -->
                    <?php if (!empty($settings['weta_service_icon']) || !empty($settings['weta_service_selected_icon']['value'])) : ?>
                        <div class="service-icon">
                            <?php weta_render_icon($settings, 'weta_service_icon', 'weta_service_selected_icon'); ?>
                        </div>
                    <?php endif; ?>
                    <!-- Service SVG  -->
                    <?php if (!empty($settings['weta_service_icon_svg'])): ?>
                        <div class="service-icon">
                            <?php echo $settings['weta_service_icon_svg']; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Service Icon End -->

                    <?php if($settings['weta_services_list_title']): ?> 
                    <h3 class="title">
                        <a href="<?php echo esc_url($settings["weta_services_list_title_url"]); ?>"><?php echo esc_html($settings['weta_services_list_title']); ?></a>
                    </h3>
                    <?php endif; ?>

                    <?php if($settings['weta_services_list_desc']): ?>
                    <p><?php echo esc_html($settings['weta_services_list_desc']); ?></p>
                    <?php endif; ?>

                    <?php if(!empty($settings['link_text'])) : ?>
                    <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>><?php print esc_html($settings['link_text']); ?> <i class="fa-sharp fa-regular fa-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ): ?>

        <?php elseif ( $settings['weta_design_style']  == 'layout-3' ): ?>

        <?php endif; ?>

        <?php 
    }
}

$widgets_manager->register( new WETA_Services_Box() );