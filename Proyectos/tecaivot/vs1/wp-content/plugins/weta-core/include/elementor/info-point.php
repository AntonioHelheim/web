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
class WETA_Info_Point extends Widget_Base {

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
		return 'weta_info_point';
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
		return __( 'Info Point', 'weta-core' );
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
            'weta_info_point',
            [
                'label' => esc_html__('Info Point', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
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
                ],
                'default' => 'layout-1',
            ]
        );

        $repeater = new Repeater();

        if (weta_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'weta_info_point_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'fa-fa-check',
                ]
            );
        } else {
            $repeater->add_control(
                'weta_info_point_selected_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'fab fa-check',
                        'library' => 'solid',
                    ],
                ]
            );
        }

        $repeater->add_control(
            'weta_info_point_title', [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );
     
        $this->add_control(
            'weta_info_point_list',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'weta_info_point_title' => esc_html__('It is a long established fact', 'weta-core'),
                    ],
                    [
                        'weta_info_point_title' => esc_html__('There are many variations of passages', 'weta-core'),
                    ],
                ],
                'title_field' => '{{{ weta_info_point_title }}}',
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            '_section_info_point_style',
            [
                'label' => __( 'Info Point', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_icon',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Icon', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .projects-detalis__content-text2 ul li i:before' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .about-two__content-list ul li p::before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __( 'Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .projects-detalis__content-text2 ul li i:before' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_right_spacing',
            [
                'label' => __( 'Right Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .projects-detalis__content-text2 ul li i:before' => 'padding-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .about-two__content-list ul li p' => 'padding-left: {{SIZE}}{{UNIT}};',
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

        $this->add_responsive_control(
            'title_bottom_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .projects-detalis__content-text2 ul li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .about-two__content-list ul li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .projects-detalis__content-text2 ul li' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .about-two__content-list ul li p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .projects-detalis__content-text2 ul li, .about-two__content-list ul li p',
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

        <?php if ( $settings['weta_design_style']  == 'layout-1' ): ?>
            <div class="projects-detalis__content-text2">
                <ul>
                    <?php foreach ($settings['weta_info_point_list'] as $item) : ?>
                        <li>
                            <?php if (!empty($item['weta_info_point_icon']) || !empty($item['weta_info_point_selected_icon']['value'])) : ?>
                                <?php weta_render_icon($item, 'weta_info_point_icon', 'weta_info_point_selected_icon'); ?>
                            <?php endif; ?>
                            <?php if ( !empty($item['weta_info_point_title']) ) : ?>
                                <?php echo rrdevs_kses($item['weta_info_point_title' ]); ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ): ?>

            <div class="about-two__content-list">
                <ul>
                    <?php foreach ($settings['weta_info_point_list'] as $item) : ?>
                        <?php if ( !empty($item['weta_info_point_title']) ) : ?>
                            <li>
                                <p><?php echo rrdevs_kses($item['weta_info_point_title' ]); ?></p>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>

        <?php 
	}
}

$widgets_manager->register( new WETA_Info_Point() );