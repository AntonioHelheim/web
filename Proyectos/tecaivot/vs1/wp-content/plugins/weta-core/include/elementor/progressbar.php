<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Control_Media;
use \Elementor\Group_Control_Background;
use \Elementor\Repeater;
use \Elementor\Controls_Manager;
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
class WETA_Progressbar extends Widget_Base {

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
		return 'progress_bar';
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
		return __( 'Progress Bar', 'weta-core' );
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
            '_content_progressbar',
            [
                'label' => esc_html__('Progress Bar', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'skill_percent',
            [
                'label' => esc_html__('Skill Percent', 'weta-core'),
                'description' => esc_html__('Please avoid %, px etc. Only type digits', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '90',
            ]
        );

        $this->add_control(
            'skill_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__('Power outlet', 'weta-core'),
            ]
        );
        
        $this->end_controls_section();

        $this->start_controls_section(
            '_style_progressbar',
            [
                'label' => __( 'Progress Bar', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_text',
            [
                'label' => esc_html__( 'Text', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'skill_text_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skills-item .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'skill_text_typography',
                'selector' => '{{WRAPPER}} .skills-item .title',
            ]
        );

        $this->add_control(
            '_heading_percent',
            [
                'label' => esc_html__( 'Percent', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'percent_top',
            [
                'label' => __( 'Height', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .skills-item .progress .progress-bar span' => 'top: -{{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'skill_percent_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skills-item .progress .progress-bar span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'skill_percent_typography',
                'selector' => '{{WRAPPER}} .skills-item .progress .progress-bar span',
            ]
        );

        $this->add_control(
            '_heading_bar',
            [
                'label' => esc_html__( 'Bar', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'skill_bar_fill',
            [
                'label' => esc_html__( 'Bar Fill', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skills-item .progress .progress-bar' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'skill_bar_background',
            [
                'label' => esc_html__( 'Bar Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skills-item .progress' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'skill_bar_height',
            [
                'label' => __( 'Height', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .skills-item .progress .progress-bar' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .skills-item .progress' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            '_heading_layout',
            [
                'label' => esc_html__( 'Layout', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'layout_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .skills-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'layout_margin',
            [
                'label' => __( 'Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .skills-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'layout_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skills-item' => 'background-color: {{VALUE}}',
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

            <?php if (!empty($settings['skill_percent'])): ?>
                <div class="skills-item">
                    <h4 class="title">
                        <?php print esc_html(($settings['skill_title'])); ?>
                    </h4>
                    <div class="progress">
                        <div class="progress-bar wow slideInLeft" data-wow-delay="0ms" data-wow-duration="2000ms" role="progressbar" style="width: <?php print esc_attr(($settings['skill_percent'])); ?>%; visibility: visible; animation-duration: 2000ms; animation-delay: 0ms; animation-name: slideInLeft;">
                            <span><?php print esc_attr(($settings['skill_percent'])); ?><?php print esc_html( '%', 'weta-core' ); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php
	}

}

$widgets_manager->register( new WETA_Progressbar() );