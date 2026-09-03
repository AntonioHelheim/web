<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Control_Media;
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
class WETA_Quote extends Widget_Base {

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
		return 'weta_quote';
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
		return esc_html__( 'Quote', 'weta-core' );
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
            '_content_quote',
            [
                'label' => esc_html__( 'Quote', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'quote_description', [
                'label' => esc_html__( 'Description', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Purus non enim praesent elementum facilisis leo. Quis lectus nulla volutpat. Nunc scelerisque viverra mauris in aliquam sem fringilla Nam lectus urna duis convallis convallis tellus.', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'quote_name', [
                'label' => esc_html__( 'Name', 'weta-core' ),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Dominic L. Ement', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_style_design_layout',
            [
                'label' => esc_html__( 'Design Layout', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'quote_layout_margin',
            [
                'label' => esc_html__( 'Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one blockquote' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'quote_layout_padding',
            [
                'label' => esc_html__( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one blockquote' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_layout_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one blockquote' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_style_quote',
            [
                'label' => esc_html__( 'Quote', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_quote_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Icon', 'weta-core' ),
            ]
        );

        $this->add_control(
            'quote_icon_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one .left' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            '_heading_quote_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Description', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'quote_description_bottom_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one .right p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_description_color',
            [
                'label' => esc_html__( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one .right p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'quote_description_typography',
                'selector' => '{{WRAPPER}} .service-quote__one .right p',
            ]
        );

        $this->add_control(
            '_style_heading_quote_name',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Name', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'quote_name_shape_background',
            [
                'label' => esc_html__( 'Shape', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one .right span:after' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'quote_name_color',
            [
                'label' => esc_html__( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .service-quote__one .right span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'quote_name_typography',
                'selector' => '{{WRAPPER}} .service-quote__one .right span',
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

            <div class="service-quote__one">
                <blockquote>
                    <div class="left">
                        <img src="<?php print get_template_directory_uri(); ?>/assets/imgs/service-details/qoute.png" alt="icon not found">
                    </div>
                    <div class="right">
                        <?php if ( !empty ( $settings['quote_description' ] ) ) : ?>
                            <p><?php echo rrdevs_kses($settings['quote_description' ]); ?></p>
                        <?php endif; ?>
                        <span><?php echo rrdevs_kses($settings['quote_name' ]); ?></span>
                    </div>
                </blockquote>
            </div>

        <?php 
	}
}

$widgets_manager->register( new WETA_Quote() );