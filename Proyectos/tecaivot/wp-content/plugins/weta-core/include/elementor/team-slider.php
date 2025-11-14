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
class WETA_Team_Slider extends Widget_Base {

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
		return 'weta_team_slider';
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
		return __( 'Team Slider', 'weta-core' );
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
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__('Layout 1', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_title',
            [
                'label'       => esc_html__( 'Title & Content', 'weta-core' ),
                'tab'         => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'sub_title',
            [
                'label' => esc_html__('Subtitle', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => __('Our Team Member', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__( 'Title', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Meet The Team Member Meeting', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'weta_teams',
            [
                'label' => esc_html__('Members', 'weta-core'),
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
            'title',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'label' => __( 'Name', 'weta-core' ),
                'default' => __( 'Courtney Henry', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'designation',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'show_label' => true,
                'label' => __( 'Designation', 'weta-core' ),
                'default' => __( 'Software Tester', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $repeater->add_control(
            'weta_link_switcher',
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
            'weta_link_type',
            [
                'label' => esc_html__( 'Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'condition' => [
                    'weta_link_switcher' => 'yes'
                ]
            ]
        );

        $repeater->add_control(
            'weta_link',
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
                    'weta_link_type' => '1',
                    'weta_link_switcher' => 'yes',
                ]
            ]
        );

        $repeater->add_control(
            'weta_page_link',
            [
                'label' => esc_html__( 'Select Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_link_type' => '2',
                    'weta_link_switcher' => 'yes',
                ]
            ]
        );

        // REPEATER
        $this->add_control(
            'teams',
            [
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '<# print(title || "Carousel Item"); #>',
                'default' => [
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ],
                    [
                        'image' => [
                            'url' => Utils::get_placeholder_image_src(),
                        ],
                    ]
                ]
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
			'design_layout_style',
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
                    '{{WRAPPER}} .weta-el-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .weta-el-section' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'design_layout_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            '_heading_arrow',
            [
                'label' => esc_html__( 'Arrow', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->start_controls_tabs( '_tabs_arrow' );
        
        $this->start_controls_tab(
            '_arrow_normal_tab',
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
                    '{{WRAPPER}} .specialists__slider-arrow-next svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-prev svg path' => 'stroke: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__slider-arrow-next' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-prev' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'drag_background',
            [
                'label' => esc_html__( 'Drag Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__scrollbar' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            '_arrow_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'arrow_color_active',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__slider-arrow-next:hover svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-next:focus svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-prev:hover svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-prev:focus svg path' => 'stroke: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'arrow_background_active',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__slider-arrow-next:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-next:focus' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-prev:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .specialists__slider-arrow-prev:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'drag_background_active',
            [
                'label' => esc_html__( 'Drag Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__scrollbar .swiper-scrollbar-drag' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

		$this->end_controls_section();

        $this->start_controls_section(
            '_style_title',
            [
                'label' => __( 'Title & Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        // Title
        $this->add_control(
            '_heading_subheading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subheading', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'subheading_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-subheading' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subheading_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-subheading' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'subheading_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-subheading' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subheading_typography',
                'selector' => '{{WRAPPER}} .weta-el-section-subheading',
            ]
        );

        $this->add_responsive_control(
            'subheading_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-subheading' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'subheading_border_radius',
            [
                'label' => __( 'Border Radius', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-subheading' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title
        $this->add_control(
            '_section_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'section_title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-el-section-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'selector' => '{{WRAPPER}} .weta-el-section-title',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
			'team_member_style',
			[
				'label' => __( 'Members', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        // Name
        $this->add_control(
            '_heading_member_name',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Name', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'member_name_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .specialists__item-content a' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'member_name_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__item-content a' => 'color: {{VALUE}}',
                ],
            ]
        );

		$this->add_control(
            'member_name_color_hover',
            [
                'label' => __( 'Color (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__item-content a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'member_name_typography',
                'selector' => '{{WRAPPER}} .specialists__item-content a',
            ]
        );

        // Name
        $this->add_control(
            '_heading_member_designation',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Designation', 'weta-core' ),
                'separator' => 'before'
            ]
        );

		$this->add_control(
            'member_designation_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__item-content span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'member_designation_typography',
                'selector' => '{{WRAPPER}} .specialists__item-content span',
            ]
        );

        $this->add_control(
            '_heading_name_layout',
            [
                'label' => esc_html__( 'Layout', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

		$this->add_control(
            'member_name_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .specialists__item-content' => 'background-color: {{VALUE}}',
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

            <?php if ( $settings['design_style']  == 'layout-1' ): ?>

                <section class="specialists weta-el-section section-space overflow-hidden theme-bg-2">
                    <div class="container container-xxl">
                        <div class="row">
                            <div class="col-12">
                                <div class="specialists__content d-flex flex-column justify-content-sm-between align-items-sm-end flex-sm-row  mb-40 mb-sm-30 mb-xs-50">
                                    <div class="section-2__title-wrapper">
                                        <?php if(!empty($settings['sub_title'])): ?>
                                            <span class="section-2__subtitle weta-el-section-subheading justify-content-start mb-15 mb-xs-5 section-subTile-2-animation">
                                                <?php echo rrdevs_kses($settings['sub_title']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(!empty($settings['title'])): ?>
                                            <h2 class="section-2__title xl weta-el-section-title text-uppercase section-title-2-animation">
                                                <?php echo rrdevs_kses($settings['title']); ?>
                                            </h2>
                                        <?php endif; ?>
                                    </div>

                                    <div class="specialists__slider-control d-flex align-items-center">
                                        <button class="specialists__slider-arrow-prev section-subTile-2-animation">
                                            <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M7.07007 1L1.00007 7.07L7.07007 13.14" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M16 7.07031L1 7.07031" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button class="specialists__slider-arrow-next section-subTile-2-animation">
                                            <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.92993 1L15.9999 7.07L9.92993 13.14" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M1 7.07031L16 7.07031" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="swiper specialists__active">
                                    <div class="swiper-wrapper">
                                        <?php foreach ( $settings['teams'] as $item ) :
                                            $name = weta_kses( $item['title' ] );

                                            if ( !empty($item['image']['url']) ) {
                                                $weta_team_image_url = !empty($item['image']['id']) ? wp_get_attachment_image_url( $item['image']['id'], 'full') : $item['image']['url'];
                                                $weta_team_image_alt = get_post_meta($item["image"]["id"], "_wp_attachment_image_alt", true);
                                            }
                                            
                                            // Link
                                            if ('2' == $item['weta_link_type']) {
                                                $link = get_permalink($item['weta_page_link']);
                                                $target = '_self';
                                                $rel = 'nofollow';
                                            } else {
                                                $link = !empty($item['weta_link']['url']) ? $item['weta_link']['url'] : '';
                                                $target = !empty($item['weta_link']['is_external']) ? '_blank' : '';
                                                $rel = !empty($item['weta_link']['nofollow']) ? 'nofollow' : '';
                                            }

                                        ?>
                                        <div class="swiper-slide">
                                            <div class="specialists__item specialists-image-animation">
                                                <div class="specialists__item-content">
                                                    <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>">
                                                        <?php echo weta_kses( $item['title'] ); ?>
                                                    </a>
                                                    <?php if( !empty($item['designation']) ) : ?>
                                                        <span><?php echo weta_kses( $item['designation'] ); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="specialists__thumb">
                                                    <img class="img-fluid" src="<?php echo esc_url($weta_team_image_url); ?>" alt="<?php echo esc_attr($weta_team_image_alt); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="specialists__scrollbar"></div>
                            </div>
                        </div>
                    </div>
                </section>

            <?php elseif ( $settings['design_style']  == 'layout-2' ): ?>

            <?php endif; ?>

		<?php
	}
}

$widgets_manager->register( new WETA_Team_Slider() );