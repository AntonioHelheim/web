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
class WETA_Team extends Widget_Base {

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
		return 'weta_team';
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
		return __( 'Team', 'weta-core' );
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
            'weta_teams',
            [
                'label' => esc_html__('Members', 'weta-core'),
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
            'title',
            [
                'label' => __( 'Name', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => __( 'Ahmad Dev', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $this->add_control(
            'designation',
            [
                'label' => __( 'Designation', 'weta-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'show_label' => true,
                'default' => __( 'Founder', 'weta-core' ),
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );

        $this->add_control(
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

        $this->add_control(
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

        $this->add_control(
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

        $this->add_control(
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
                    '{{WRAPPER}} .team-1__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .team-1__item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'design_layout_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-1__item' => 'background-color: {{VALUE}}',
                ],
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
                    '{{WRAPPER}} .team-1__item-content a' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'member_name_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-1__item-content a' => 'color: {{VALUE}}',
                ],
            ]
        );

		$this->add_control(
            'member_name_color_hover',
            [
                'label' => __( 'Color (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-1__item-content a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'member_name_typography',
                'selector' => '{{WRAPPER}} .team-1__item-content a',
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
                    '{{WRAPPER}} .team-1__item-content span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'member_designation_typography',
                'selector' => '{{WRAPPER}} .team-1__item-content span',
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
                    '{{WRAPPER}} .team-1__item-content' => 'background-color: {{VALUE}}',
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
            $weta_team_image_url = !empty($settings['image']['id']) ? wp_get_attachment_image_url( $settings['image']['id'], 'full') : $settings['image']['url'];
            $weta_team_image_alt = get_post_meta($settings["image"]["id"], "_wp_attachment_image_alt", true);
        }

        if ('2' == $settings['weta_link_type']) {
            $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_page_link']));
            $this->add_render_attribute('weta-button-arg', 'target', '_self');
            $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
        } else {
            if ( ! empty( $settings['weta_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button-arg', $settings['weta_link'] );
            }
        }
        
		?>

            <?php if ( $settings['design_style']  == 'layout-1' ): ?>

                <div class="team-1__item mb-40">
                    <div class="team-1__item-content">
                        <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                            <?php echo weta_kses( $settings['title'] ); ?>
                        </a>
                        <?php if( !empty($settings['designation']) ) : ?>
                            <span><?php echo weta_kses( $settings['designation'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <img src="<?php echo esc_url($weta_team_image_url); ?>" alt="<?php echo esc_attr($weta_team_image_alt); ?>">
                </div>

            <?php elseif ( $settings['design_style']  == 'layout-2' ): ?>

            <?php endif; ?>

		<?php
	}
}

$widgets_manager->register( new WETA_Team() );