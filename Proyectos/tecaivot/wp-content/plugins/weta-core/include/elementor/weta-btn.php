<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Btn extends Widget_Base {

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
		return 'weta_btn';
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
		return __( 'Weta Button', 'weta-core' );
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
                'default' => esc_html__('View More', 'weta-core'),
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

        $this->add_responsive_control(
            'weta_align',
            [
                'label' => esc_html__('Alignment', 'weta-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'weta-core'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'weta-core'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'weta-core'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'toggle' => false,
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};'
                ]
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
                    '{{WRAPPER}} .rr-el-btn'    => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rr-btn__theme-4 .btn-wrap .text-one'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-el-btn' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .rr-el-btn',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .rr-el-btn',
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
                    '{{WRAPPER}} .rr-btn__theme-4:hover .text-two' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rr-el-btn::before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'button_border_hover',
                'selector' => '{{WRAPPER}} .rr-el-btn:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rr-el-btn:hover',
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
                    '{{WRAPPER}} .rr-el-btn' => 'border-radius: {{SIZE}}px;',
                    '{{WRAPPER}} .rr-el-btn:before' => 'border-radius: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label'    => esc_html__( 'Typography', 'weta-core' ),
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .rr-el-btn',
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .rr-el-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .rr-el-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        // Link
        if ('2' == $settings['weta_btn_link_type']) {
            $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_btn_page_link']));
            $this->add_render_attribute('weta-button-arg', 'target', '_self');
            $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
            $this->add_render_attribute('weta-button-arg', 'class', 'd-flex rr-btn rr-btn__theme-4 rr-el-btn');
        } else {
            if ( ! empty( $settings['weta_btn_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button-arg', $settings['weta_btn_link'] );
                $this->add_render_attribute('weta-button-arg', 'class', 'd-flex rr-btn rr-btn__theme-4 rr-el-btn');
            }
        }

		?>
            <?php if (!empty($settings['weta_btn_text'])) : ?>
            <div class="back-to-portfolio">
                <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                    <span class="btn-wrap">
                        <span class="text-one"><?php echo $settings['weta_btn_text']; ?></span>
                        <span class="text-two"><?php echo $settings['weta_btn_text']; ?></span>
                    </span>
                    <?php if (!empty($settings['icon']) || !empty($settings['selected_icon']['value'])) : ?>
                        <span class="<?php echo esc_attr( $settings['button_icon_alignment'] ); ?>">
                            <?php weta_render_icon($settings, 'icon', 'selected_icon'); ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>
        <?php
	}
}

$widgets_manager->register( new WETA_Btn() );