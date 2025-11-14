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
class WETA_Quote_Slider extends Widget_Base {

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
		return 'weta_quote_slider';
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
		return __( 'Quote Slider', 'weta-core' );
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
            '_content_design_layout',
            [
                'label' => esc_html__('Design Layout', 'weta-core'),
            ]
        );

        $this->add_control(
            'design_style',
            [
                'label' => esc_html__('Select Layout', 'weta-core'),
                'type' => Controls_Manager::HIDDEN,
                'options' => [
                    'layout-1' => esc_html__('Layout 1', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $repeater = new Repeater();

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
			'heading',
			[
				'label' => esc_html__( 'Title', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
				'type' => Controls_Manager::TEXT,
                'label_block' => true,
			]
		);

        // REPEATER
        $this->add_control(
            'project_list',
            [
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'heading' => __( 'Carve Out Your Tomorrow through Strategic Decision-making.', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'heading' => __( 'Carve Out Your Tomorrow through Strategic Decision-making.', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'heading' => __( 'Carve Out Your Tomorrow through Strategic Decision-making.', 'weta-core' ),
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                        'heading' => __( 'Carve Out Your Tomorrow through Strategic Decision-making.', 'weta-core' ),
                    ],
                ]
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
			'_style_design_layout',
			[
				'label' => __( 'Design Layout', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'design_layout_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .decision-making__item-space' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'design_layout_margin',
            [
                'label' => __( 'Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .decision-making__item-space' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'design_layout_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .decision-making__item-overlay:after',
            ]
        );

        $this->add_control(
            '_heading_arrow_dots',
            [
                'label' => esc_html__( 'Arrow/Dots', 'weta-core' ),
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
                    '{{WRAPPER}} .decision-making__slider-prev svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .decision-making__slider-next svg path' => 'stroke: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .decision-making__slider-prev' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .decision-making__slider-next' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'arrow_active_tab',
            [
                'label' => esc_html__( 'Active', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'arrow_color_hover',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .decision-making__slider-prev:hover svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .decision-making__slider-next:hover svg path' => 'stroke: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_background_hover',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .decision-making__slider-prev:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .decision-making__slider-next:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

		$this->end_controls_section();

        $this->start_controls_section(
			'_style_quote_slider',
			[
				'label' => esc_html__( 'Quote Slider', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            '_heading_style_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Title', 'weta-core' ),
            ]
        );
        
        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .decision-making__item-content h2' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .decision-making__item-content h2' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .decision-making__item-content h2',
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

            <?php if ( $settings['design_style']  == 'layout-1' ): ?>

                <section class="decision-making overflow-hidden">
                    <div class="swiper decision-making__active">
                        <div class="swiper-wrapper">
                            <?php foreach ( $settings['project_list'] as $item ) :

                                if ( !empty($item['image']['url']) ) {
                                    $project_image = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                                    $project_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                                }

                            ?>
                            <div class="swiper-slide">
                                <div class="decision-making__item decision-making__item-space d-flex align-items-end overflow-hidden decision-making__item-overlay" data-background="<?php print esc_url($project_image); ?>">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="decision-making__item-content">
                                                    <?php if ( !empty ( $item['heading'] ) ): ?>
                                                        <h2 class="text-uppercase color-white">
                                                            <?php print rrdevs_kses( $item['heading'] ); ?>
                                                        </h2>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="container  position-relative z-1">
                            <div class="decision-making__slider">
                                <button class="decision-making__slider-prev">
                                    <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.07031 1L1.00031 7.07L7.07031 13.14" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16 7.07007L1 7.07007" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>

                                <button class="decision-making__slider-next">
                                    <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.92969 1L15.9997 7.07L9.92969 13.14" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M1 7.07007L16 7.07007" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

            <?php elseif ( $settings['design_style']  == 'layout-2' ): ?>

            <?php endif; ?>

		<?php
	}
}

$widgets_manager->register( new WETA_Quote_Slider() );