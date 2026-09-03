<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Repeater;
use \Elementor\Utils;
use \Elementor\Control_Media;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Service_List extends Widget_Base {

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
		return 'weta_service_list';
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
		return __( 'Service List', 'weta-core' );
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
            '_content_service_list',
            [
                'label' => esc_html__( 'Service List', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'service_list_title', [
                'label' => esc_html__('Text', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );
        
        $repeater->add_control(
            'link_switcher',
            [
                'label' => esc_html__( 'Add Services link', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'link_type',
            [
                'label' => esc_html__( 'Service Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'condition' => [
                    'link_switcher' => 'yes'
                ]
            ]
        );

        $repeater->add_control(
            'service_link',
            [
                'label' => esc_html__( 'Service Link link', 'weta-core' ),
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
                    'link_type' => '1',
                    'link_switcher' => 'yes',
                ]
            ]
        );

        $repeater->add_control(
            'service_page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'link_type' => '2',
                    'link_switcher' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'service_list_list',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'service_list_title' => esc_html__( 'Restoring functionality, one repair', 'weta-core' ),
                    ],
                    [
                        'service_list_title' => esc_html__( 'Repairing your problems restoring', 'weta-core' ),
                    ],
                    [
                        'service_list_title' => esc_html__( 'Quality repair services you can', 'weta-core' ),
                    ],
                    [
                        'service_list_title' => esc_html__( 'Repairing with care exceeding expectations', 'weta-core' ),
                    ],
                    [
                        'service_list_title' => esc_html__( 'Reliable Repair', 'weta-core' ),
                    ],
                    [
                        'service_list_title' => esc_html__( 'Perfect Restore', 'weta-core' ),
                    ],
                ],
                'title_field' => '{{{ service_list_title }}}',
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            '_style_design_layout',
            [
                'label' => __( 'Design Layout', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'design_layout_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'design_layout_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_style_service_list',
            [
                'label' => __( 'Service List', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
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

        $this->start_controls_tabs( '_tabs_title' );
        
        $this->start_controls_tab(
            'title_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list li a' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'title_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list li a' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'title_border',
                'selector' => '{{WRAPPER}} .sidebar-list li a',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'title_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'title_color_hover',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list li a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'title_background_hover',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list li a:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'label'    => esc_html__( 'Border', 'weta-core' ),
                'name'     => 'title_border_hover',
                'selector' => '{{WRAPPER}} .sidebar-list li a:hover',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .sidebar-list li a',
            ]
        );

        $this->add_control(
            '_heading_service_list_layout',
            [
                'label' => esc_html__( 'Layout', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'service_list_layout_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'service_list_layout_padding',
            [
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .sidebar-list li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

            <ul class="sidebar-list">
                <?php foreach ($settings['service_list_list'] as $item) : 
                    // Link
                    if ('2' == $item['link_type']) {
                        $link = get_permalink($item['service_page_link']);
                        $target = '_self';
                        $rel = 'nofollow';
                    } else {
                        $link = !empty($item['service_link']['url']) ? $item['service_link']['url'] : '';
                        $target = !empty($item['service_link']['is_external']) ? '_blank' : '';
                        $rel = !empty($item['service_link']['nofollow']) ? 'nofollow' : '';
                    }
                ?>
                <li>
                    <a target="<?php echo esc_attr($target); ?>" rel="<?php echo esc_attr($rel); ?>" href="<?php echo esc_url($link); ?>">
                        <?php echo rrdevs_kses($item['service_list_title' ]); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

        <?php 
	}
}

$widgets_manager->register( new WETA_Service_List() );