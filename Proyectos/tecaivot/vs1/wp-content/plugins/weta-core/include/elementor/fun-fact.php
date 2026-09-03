<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Image_Size;
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
class WETA_Fun_Fact extends Widget_Base {

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
		return 'weta_fun_fact';
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
		return __( 'Fun Fact', 'weta-core' );
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
            'section_title',
            [
                'label' => esc_html__('Desigin Layout', 'weta-core'),
            ]
        );

        $this->add_control(
            'design_style',
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
            'fun_fact_icon_type',
            [
                'label' => esc_html__('Icon Type', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'icon' => esc_html__('Icon', 'weta-core'),
                    'image' => esc_html__('Image', 'weta-core'),
                ],
                'default' => 'image',
                'condition' => [
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $this->add_control(
                'fun_fact_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'fa fa-check-circle',
                    'condition'   => [
                        'fun_fact_icon_type' => 'icon',
                        'design_style' => [ 'layout-3' ],
                    ],
                ]
            );
        } else {
            $this->add_control(
                'fun_fact_selected_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'far fa-check-circle',
                        'library' => 'solid',
                    ],
                    'condition'   => [
                        'fun_fact_icon_type' => 'icon',
                        'design_style' => [ 'layout-3' ],
                    ],
                ]
            );
        }

        $this->add_control(
            'fun_fact_image_icon',
            [
                'label'     => esc_html__( 'Choose Image', 'weta-core' ),
                'type'      => Controls_Manager::MEDIA,
                'default'   => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'fun_fact_icon_type'   => ['image'],
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'rr_fact',
            [
                'label' => esc_html__('Fact List', 'weta-core'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

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

        //icon image svg

        $repeater->add_control(
            'rr_box_icon_type',
            [
                'label' => esc_html__('Select Icon Type', 'weta-core'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'image' => esc_html__('Image', 'weta-core'),
                    'svg' => esc_html__('SVG', 'weta-core'),
                ],
            ]
        );
        $repeater->add_control(
            'rr_box_icon_svg',
            [
                'show_label' => false,
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'placeholder' => esc_html__('SVG Code Here', 'weta-core'),
                'condition' => [
                    'rr_box_icon_type' => 'svg',
                ]
            ]
        );

        $repeater->add_control(
            'rr_box_icon_image',
            [
                'label' => esc_html__('Upload Icon Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'rr_box_icon_type' => 'image',
                ]
            ]
        );

        $repeater->add_control(
            'rr_fact_number', [
                'label' => esc_html__('Number', 'weta-core'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('200', 'weta-core'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'rr_fact_number_unit', [
                'label' => esc_html__('Number Unit', 'weta-core'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('+', 'weta-core'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'rr_fact_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Happy Clients', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'rr_fact_list',
            [
                'label' => esc_html__('Fact - List', 'weta-core'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'rr_fact_number' => esc_html__('200', 'weta-core'),
                        'rr_fact_title' => esc_html__('Happy Clients', 'weta-core'),
                    ],
                    [
                        'rr_fact_number' => esc_html__('20', 'weta-core'),
                        'rr_fact_title' => esc_html__('Winning award', 'weta-core')
                    ],
                    [
                        'rr_fact_number' => esc_html__('10', 'weta-core'),
                        'rr_fact_title' => esc_html__('Complete project', 'weta-core')
                    ],                   
                    [
                        'rr_fact_number' => esc_html__('900', 'weta-core'),
                        'rr_fact_title' => esc_html__('Client review', 'weta-core')
                    ]
                ],
                'title_field' => '{{{ rr_fact_title }}}',
            ]
        );

        $this->end_controls_section();


        // Fact Style Control 
        $this->start_controls_section(
            '_section_fun_fact_style',
            [
                'label' => __( 'Fun Fact', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_layout',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Section', 'weta-core' ),
            ]
        );


        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .counter-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .counter-section.counter-1' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .counter-item-wrap .counter-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin',
            [
                'label'      => esc_html__( 'Margin', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .counter-section' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .counter-section.counter-1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .counter-item-wrap .counter-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label' => __( 'Content Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .counter-section' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .counter-section.counter-1' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .counter-item-wrap .counter-item' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            '_style_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Icon', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .counter-item-wrap .center-icon' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'icon_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .counter-item-wrap .center-icon' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => [ 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            '_content_count',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Count', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'count_margin_spacing',
            [
                'label' => __( 'Margin Bottom', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .counter-item .counter-content .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .counter-item-wrap .counter-item .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'count_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .counter-item .counter-content .title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .counter-item-wrap .counter-item .title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .counter-item-wrap .counter-item .title' => '-webkit-text-stroke-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'count_typography',
                'selector' => '{{WRAPPER}} .counter-item .counter-content .title, .counter-item-wrap .counter-item .title',
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

        $this->add_responsive_control(
            'title_top_spacing',
            [
                'label' => __( 'Top Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .counter-item .counter-content p' => 'margin-top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .counter-item-wrap .counter-item p' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .counter-item .counter-content p' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .counter-item-wrap .counter-item p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .counter-item .counter-content p, .counter-item-wrap .counter-item p',
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

        <section class="counter-section counter-1 bg-dark-1">
            <div class="container">
                <div class="row gy-lg-0 gy-4">
                    <?php foreach ($settings['rr_fact_list'] as $key => $item) : ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="counter-item text-center wow fade-in-bottom" data-wow-delay="200ms">
                            <div class="counter-icon">
                                <?php if( $item['rr_box_icon_type'] == 'image' ) : ?>
                                    <?php if (!empty($item['rr_box_icon_image']['url'])): ?>
                                    <img src="<?php echo $item['rr_box_icon_image']['url']; ?>"
                                        alt="<?php echo get_post_meta(attachment_url_to_postid($item['rr_box_icon_image']['url']), '_wp_attachment_image_alt', true); ?>">
                                    <?php endif; ?>
                                    <?php else : ?>
                                    <?php if (!empty($item['rr_box_icon_svg'])): ?>
                                    <?php echo $item['rr_box_icon_svg']; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="counter-content">
                                <h3 class="title">
                                    <span class="odometer" data-count="<?php echo esc_attr($item['rr_fact_number' ]); ?>">0</span>
                                    <?php echo esc_attr($item['rr_fact_number_unit' ]); ?>
                                </h3>

                                <?php if(!empty($item['rr_fact_title'])) : ?>
                                <p><?php echo esc_html($item['rr_fact_title']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php elseif ( $settings['design_style']  == 'layout-2' ): ?>

        <section class="counter-section counter-1 bright-color pt-120 pb-120">
            <div class="container">
                <div class="row gy-lg-0 gy-4">
                    <?php foreach ($settings['rr_fact_list'] as $key => $item) : ?>
                    <div class="col-xl-3 col-lg-3 col-md-6">
                        <div class="counter-item text-center">
                            <div class="counter-content">
                                <h3 class="title">
                                    <span class="odometer" data-count="<?php echo esc_attr($item['rr_fact_number' ]); ?>">0</span>
                                    <?php echo esc_attr($item['rr_fact_number_unit' ]); ?>
                                </h3>
                                <div class="counter-icon">
                                    <?php if( $item['rr_box_icon_type'] == 'image' ) : ?>
                                        <?php if (!empty($item['rr_box_icon_image']['url'])): ?>
                                        <img src="<?php echo $item['rr_box_icon_image']['url']; ?>"
                                            alt="<?php echo get_post_meta(attachment_url_to_postid($item['rr_box_icon_image']['url']), '_wp_attachment_image_alt', true); ?>">
                                        <?php endif; ?>
                                        <?php else : ?>
                                        <?php if (!empty($item['rr_box_icon_svg'])): ?>
                                        <?php echo $item['rr_box_icon_svg']; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if(!empty($item['rr_fact_title'])) : ?>
                                <p><?php echo esc_html($item['rr_fact_title']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php elseif ( $settings['design_style']  == 'layout-3' ): 
        
        if ( !empty($settings['fun_fact_image_icon']['url']) ) {
            $fun_fact_image_icon = !empty($settings['fun_fact_image_icon']['id']) ? wp_get_attachment_image_url( $settings['fun_fact_image_icon']['id'], 'full') : $settings['fun_fact_image_icon']['url'];
            $fun_fact_image_icon_alt = get_post_meta($settings["fun_fact_image_icon"]["id"], "_wp_attachment_image_alt", true);
        }
        
        ?>

        <div class="counter-item-wrap">
            <div class="center-icon">
                <?php if ( 'icon' === $settings['fun_fact_icon_type'] ): ?>
                    <?php weta_render_icon($settings, 'fun_fact_us_icon', 'fun_fact_selected_icon'); ?>

                <?php elseif ( 'image' === $settings['fun_fact_icon_type'] ): ?>
                    <img src="<?php echo esc_url( $fun_fact_image_icon ); ?>" alt="<?php echo esc_attr( $fun_fact_image_icon_alt ); ?>">
                <?php endif;?>
                <div class="ripple"></div>
            </div>
            <?php foreach ($settings['rr_fact_list'] as $key => $item) : ?>
                <div class="counter-item">
                    <h3 class="title">
                        <span class="odometer" data-count="<?php echo esc_attr($item['rr_fact_number' ]); ?>">0</span><?php echo esc_attr($item['rr_fact_number_unit' ]); ?>
                    </h3>
                    <?php if(!empty($item['rr_fact_title'])) : ?>
                        <p><?php echo esc_html($item['rr_fact_title']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
        <?php 
	}
}

$widgets_manager->register( new WETA_Fun_Fact() );