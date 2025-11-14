<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Repeater;
use \Elementor\Utils;
use \Elementor\Control_Media;
use \Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class Pixfix_Project_Info extends Widget_Base {

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
		return 'project_info';
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
		return __( 'Project Info', 'weta-core' );
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
            '_content_portfolio_info',
            [
                'label' => esc_html__('Portfolio Info', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'image',
            [
                'label' => esc_html__( 'Testimonial Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $this->add_control(
            'date', [
                'label' => esc_html__( 'Date', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'default' => esc_html__( 'October 19, 2024', 'weta-core' ),
                'label_block' => true,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'weta_portfolio_info_label', [
                'label' => esc_html__('Label', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'weta_portfolio_info_title', [
                'label' => esc_html__('Text', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_portfolio_info_list',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'weta_portfolio_info_label' => esc_html__('Client:', 'weta-core'),
                        'weta_portfolio_info_title' => esc_html__('Nafizul islam bhuiyan', 'weta-core'),
                    ],
                    [
                        'weta_portfolio_info_label' => esc_html__('Project:', 'weta-core'),
                        'weta_portfolio_info_title' => esc_html__('Plumber', 'weta-core'),
                    ],
                    [
                        'weta_portfolio_info_label' => esc_html__('Category:', 'weta-core'),
                        'weta_portfolio_info_title' => esc_html__('RepairTechs', 'weta-core'),
                    ],
                ],
                'title_field' => '{{{ weta_portfolio_info_label }}}',
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            '_style_info_list',
            [
                'label' => __( 'Info List', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Heading', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'heading_icon_size',
            [
                'label' => esc_html__( 'Icon Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .project-details-img .details-list li i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-details-img .details-list li i' => 'color: {{VALUE}}',
                ],
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
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-details-img .details-list li .name' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .project-details-img .details-list li .name',
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
                'label' => esc_html__( 'Top Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .project-details-img .details-list li span' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-details-img .details-list li span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .project-details-img .details-list li span',
            ]
        );

        $this->add_control(
            '_heading_layout',
            [
                'label' => esc_html__( 'Layout', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'layout_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-details-img .details-list' => 'background-color: {{VALUE}}',
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
                    '{{WRAPPER}} .project-details-img .details-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        if ( !empty($settings['image']['url']) ) {
            $image = !empty($settings['image']['id']) ? wp_get_attachment_image_url( $settings['image']['id'], 'full') : $settings['image']['url'];
            $image_alt = get_post_meta($settings["image"]["id"], "_wp_attachment_image_alt", true);
        }

		?>

            <div class="project-details-img">
                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_url($image_alt); ?>">
                <ul class="details-list">
                    <?php if ( !empty($settings['date']) ) : ?>
                        <li>
                            <i class="fa-light fa-calendar-days"></i>
                            <span><?php print esc_html($settings['date']); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php foreach ($settings['weta_portfolio_info_list'] as $item) : ?>
                        <li>
                            <?php if ( !empty($item['weta_portfolio_info_label']) ) : ?>
                                <span class="name">
                                    <?php echo rrdevs_kses($item['weta_portfolio_info_label' ]); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ( !empty($item['weta_portfolio_info_title']) ) : ?>
                                <span><?php echo rrdevs_kses($item['weta_portfolio_info_title' ]); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php 
	}
}

$widgets_manager->register( new Pixfix_Project_Info() );