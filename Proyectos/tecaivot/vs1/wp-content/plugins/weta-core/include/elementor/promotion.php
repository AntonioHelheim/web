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
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Promotion extends Widget_Base {

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
		return 'weta_promotion';
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
		return __( 'Promotion', 'weta-core' );
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
            '_content_promotion',
            [
                'label' => esc_html__( 'Promotion', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'weta_image',
            [
                'label' => esc_html__( 'Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'weta_title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __( 'Have any Qustion? <br>Contact Us Now', 'weta-core' ),
            ]
        );

        $this->add_control(
            'phone_label', [
                'label' => esc_html__( 'Phone Label', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __( 'Call :', 'weta-core' ),
            ]
        );

        $this->add_control(
            'phone', [
                'label' => esc_html__( 'Phone', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => '012548325',
            ]
        );

        $this->add_control(
            'email', [
                'label' => esc_html__( 'Email', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => 'nafiz123@gmail.com',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_button',
            [
                'label' => esc_html__( 'Button', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'weta_btn_text',
            [
                'label' => esc_html__('Button Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('DOWNLOAD PDF', 'weta-core'),
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

        if (weta_is_elementor_version('<', '2.6.0')) {
            $this->add_control(
                'button_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'fa-sharp fa-regular fa-arrow-right',
                ]
            );
        } else {
            $this->add_control(
                'button_selected_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'fa-sharp fa-regular fa-arrow-right',
                        'library' => 'solid',
                    ],
                ]
            );
        }

        $this->add_control(
            'button_icon_alignment',
            [
                'label'   => esc_html__( 'Icon Position', 'weta-core' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'before-icon',
                'options' => [
                    'before-icon' => esc_html__( 'Before', 'weta-core' ),
                    'after-icon'  => esc_html__( 'After', 'weta-core' ),
                ],
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
                    '{{WRAPPER}} .before-icon i' => 'padding-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .after-icon i'  => 'padding-left: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .before-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .after-icon i'  => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_style_promotion',
            [
                'label' => __( 'Promotion', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_content_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-box .content .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .sidebar-box .content .title',
            ]
        );

        $this->add_control(
            '_heading_phone',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Phone', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'phone_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-box .content .number' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'phone_typography',
                'selector' => '{{WRAPPER}} .sidebar-box .content .number',
            ]
        );

        $this->add_control(
            '_heading_email',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Email', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'email_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-box .content .mail' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'email_typography',
                'selector' => '{{WRAPPER}} .sidebar-box .content .mail',
            ]
        );

        $this->add_control(
            '_heading_layout',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Layout', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'layout_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-box .content' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'layout_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .sidebar-box .content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

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
                'label'     => esc_html__( 'Text', 'weta-core' ),
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

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
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
                'label'     => esc_html__( 'Text', 'weta-core' ),
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
                    '{{WRAPPER}} .rr-primary-btn.transparent::before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border_hover',
                'selector' => '{{WRAPPER}} .rr-primary-btn:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rr-primary-btn:hover',
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

        if ( !empty($settings['weta_image']['url']) ) {
            $weta_image = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'], 'large') : $settings['weta_image']['url'];
            $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
        }

        // Link
        if ('2' == $settings['weta_btn_link_type']) {
            $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_btn_page_link']));
            $this->add_render_attribute('weta-button-arg', 'target', '_self');
            $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
            $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn');
        } else {
            if ( ! empty( $settings['weta_btn_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button-arg', $settings['weta_btn_link'] );
                $this->add_render_attribute('weta-button-arg', 'class', 'rr-primary-btn');
            }
        }

		?>
            <div class="sidebar-box-wrap sticky-widget">
                <div class="sidebar-box">
                    <img src="<?php echo esc_url($weta_image); ?>" alt="<?php echo esc_url($weta_image_alt); ?>" />
                    <div class="content text-center white-content">
                        <?php if ( !empty($settings['weta_title']) ) : ?>
                            <h4 class="title">
                                <?php echo rrdevs_kses($settings['weta_title' ]); ?>
                            </h4>
                        <?php endif; ?>
                        <?php if ( !empty($settings['phone']) ) : ?>
                            <a class="number" href="tel:<?php print esc_attr($settings['phone']); ?>">
                                <?php print esc_html($settings['phone_label']); ?><?php print esc_html($settings['phone']); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ( !empty($settings['email']) ) : ?>
                            <a class="mail" href="mailto:<?php print esc_attr($settings['email']); ?>">
                                <?php print esc_html($settings['email']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($settings['weta_btn_text'])) : ?>
                    <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                        <?php echo $settings['weta_btn_text']; ?>
                        <?php if (!empty($settings['button_icon']) || !empty($settings['button_selected_icon']['value'])) : ?>
                            <span class="<?php echo esc_attr( $settings['button_icon_alignment'] ); ?>">
                                <?php weta_render_icon($settings, 'button_icon', 'button_selected_icon'); ?></span>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>

        <?php 
	}
}

$widgets_manager->register( new WETA_Promotion() );