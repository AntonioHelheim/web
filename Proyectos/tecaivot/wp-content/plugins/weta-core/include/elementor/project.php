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
class WETA_Project extends Widget_Base {

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
		return 'weta_project';
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
		return __( 'Project', 'weta-core' );
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
            '_content_project',
            [
                'label' => esc_html__('Project Slider', 'weta-core'),
            ]
        );

        $this->add_control(
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

        $this->add_control(
            'link_type',
            [
                'label' => esc_html__( 'Service Link Type', 'weta-core' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
            ]
        );

        $this->add_control(
            'link',
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
                ]
            ]
        );

        $this->add_control(
            'page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'link_type' => '2',
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
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'design_layout_margin',
            [
                'label' => __( 'Content Margin', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'design_layout_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item' => 'background-color: {{VALUE}}',
                ],
            ]
        );

		$this->end_controls_section();

        $this->start_controls_section(
			'_style_project_slider',
			[
				'label' => esc_html__( 'Project Slider', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            '_heading_style_button',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Button', 'weta-core' ),
            ]
        );

        $this->start_controls_tabs( 'button_tabs' );
        
        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'button_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item a svg path' => 'stroke: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item a' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            '_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'button_color_hover',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item a:hover svg path' => 'stroke: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_background_hover',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item a:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_control(
            '_heading_style_inner_layout',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Layout', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'project_slider_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-1__item:after' => 'background-color: {{VALUE}}',
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
            $project_image = !empty($settings['image']['id']) ? wp_get_attachment_image_url( $settings['image']['id'], 'full') : $settings['image']['url'];
            $project_image_alt = get_post_meta($settings["image"]["id"], "_wp_attachment_image_alt", true);
        }
        
        if ('2' == $settings['link_type']) {
            $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['page_link']));
            $this->add_render_attribute('weta-button-arg', 'target', '_self');
            $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
            $this->add_render_attribute('weta-button-arg', 'class', 'text-domain-el-button');
        } else {
            if ( ! empty( $settings['link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button-arg', $settings['link'] );
                $this->add_render_attribute('weta-button-arg', 'class', 'text-domain-el-button');
            }
        }
        

		?>

            <?php if ( $settings['design_style']  == 'layout-1' ): ?>

                <div class="portfolio-1__item mb-30">
                    <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                        <svg width="37" height="38" viewBox="0 0 37 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.19287 28.1929L27.5777 9.80809" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M9.19287 9.80713L27.5776 9.80713L27.5776 28.1919" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                    <img src="<?php print esc_url($project_image); ?>" alt="<?php print esc_url($project_image_alt); ?>">
                </div>

            <?php elseif ( $settings['design_style']  == 'layout-2' ): ?>

            <?php endif; ?>

		<?php
	}
}

$widgets_manager->register( new WETA_Project() );