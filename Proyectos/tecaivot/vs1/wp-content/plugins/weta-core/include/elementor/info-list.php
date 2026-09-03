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
class WETA_Info_List extends Widget_Base {

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
		return 'weta_info_list';
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
		return __( 'Info List', 'weta-core' );
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
                    'layout-2' => esc_html__('Layout 2', 'weta-core'),
                    // 'layout-3' => esc_html__('Layout 3', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->end_controls_section();

        // Info List
        $this->start_controls_section(
            'weta_info',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'weta_info_title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'weta_info_url',
            [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'show_label' => true,
                'label' => __( 'Info URL', 'weta-core' ),
                'default' => __( '#', 'weta-core' ),
                'placeholder' => __( 'url here', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $this->add_control(
            'weta_info_list',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'weta_info_title' => esc_html__('Cohabiter elemental simple', 'weta-core'),
                    ],
                    [
                        'weta_info_title' => esc_html__('Phaseolus nulls ellipt', 'weta-core'),
                    ],
                ],
                'title_field' => '{{{ weta_info_title }}}',
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
                    '{{WRAPPER}} .list-unstyled.all-project__list li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .list-unstyled.all-project__list li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_border_color',
            [
                'label' => __( 'Border', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .list-unstyled.all-project__list li' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'content_border_color_hover',
            [
                'label' => __( 'Border (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .list-unstyled.all-project__list li:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_info_list_style',
            [
                'label' => __( 'Info List', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_label',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Label', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .all-project__name' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .all-project__name',
            ]
        );

        $this->add_responsive_control(
            'label_width',
            [
                'label' => __( 'Width', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
				],
                'selectors' => [
                    '{{WRAPPER}} .all-project__name-box' => 'max-width: {{SIZE}}{{UNIT}};',
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
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .all-project__content' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .all-project__content',
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
        <?php if ( $settings['weta_design_style']  == 'layout-1' ) : ?>

        <div class="accordion-body">
            <div class="accrodion-content">
                <div class="text">
                    <div class="list">
                        <ul>
                            <?php foreach ($settings['weta_info_list'] as $item) : ?>
                            <li><a href="<?php echo esc_url($item['weta_info_url']);?>"><?php echo rrdevs_kses($item['weta_info_title' ]); ?> <i class="fa-regular fa-arrow-up-right"></i></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ): ?>
            <div class="accordion-body">
                <div class="accrodion-content">
                    <div class="text">
                        <div class="pricing-list">
                            <ul>
                                <?php foreach ($settings['weta_info_list'] as $item) : ?>
                                <li><i class="fa-light fa-check"></i><?php echo rrdevs_kses($item['weta_info_title' ]); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="pricing-footer d__column-two">
                            <h5><?php echo rrdevs_kses($settings['weta_price' ]); ?></h5>

                            <a href="<?php echo esc_url($settings['weta_price_btn_url']); ?>"><?php echo rrdevs_kses( $settings['weta_price_btn'] ); ?> <i class="fa-light fa-arrow-right"></i></a>

                        </div>

                    </div>
                </div>
            </div>

            <?php endif; ?>
        <?php
	}
}

$widgets_manager->register( new WETA_Info_List() );