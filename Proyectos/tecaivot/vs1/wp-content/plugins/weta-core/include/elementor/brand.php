<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Brand extends Widget_Base {

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
		return 'weta_brand';
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
		return __( 'Brand', 'weta-core' );
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
            'weta_brand_content',
            [
                'label' => esc_html__('Brand', 'weta-core'),
            ]
        );

        $this->add_control(
            'weta_image',
            [
                'label' => esc_html__( 'Choose Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'weta_btn_link_type',
            [
                'label' => esc_html__('Button Link Type', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => 'Custom Link',
                    '2' => 'Internal Page',
                ],
                'default' => '1',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_btn_link',
            [
                'label' => esc_html__('Button link', 'weta-core'),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__('https://your-link.com', 'weta-core'),
                'show_external' => false,
                'default' => [
                    'url' => '#',
                    'is_external' => true,
                    'nofollow' => true,
                    'custom_attributes' => '',
                ],
                'condition' => [
                    'weta_btn_link_type' => '1',
                ],
                'label_block' => true,
            ]
        );
        $this->add_control(
            'weta_btn_page_link',
            [
                'label' => esc_html__('Select Button Page', 'weta-core'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_btn_link_type' => '2',
                ]
            ]
        );
        
        $this->end_controls_section();

		$this->start_controls_section(
			'weta_brand_style',
			[
				'label' => __( 'Brand', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'brand_bottom_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .client-page__single' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'brand_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .client-page__single' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'brand_background_hover',
            [
                'label' => esc_html__( 'Background (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .client-page__single::before' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'brand_border_hover',
            [
                'label' => esc_html__( 'Border (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .client-page__single::after' => 'background-color: {{VALUE}}',
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

        if ( !empty($settings['weta_image']['url']) ) {
            $weta_image_url = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'], 'full') : $settings['weta_image']['url'];
            $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
        }  

        // Link
        if ('2' == $settings['weta_btn_link_type']) {
            $this->add_render_attribute('weta-button-arg', 'href', get_permalink($settings['weta_btn_page_link']));
            $this->add_render_attribute('weta-button-arg', 'target', '_self');
            $this->add_render_attribute('weta-button-arg', 'rel', 'nofollow');
        } else {
            if ( ! empty( $settings['weta_btn_link']['url'] ) ) {
                $this->add_link_attributes( 'weta-button-arg', $settings['weta_btn_link'] );
            }
        }

		?>
            <div class="sponsor-section pt-120">
                <div class="container">
                    <div class="sponsor-carousel swiper">
                        <div class="swiper-wrapper swiper-container">
                            <div class="swiper-slide">
                                <div class="sponsor-item">
                                    <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/sponsor/sponsor-1.png" alt="sponsor"></a>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="sponsor-item">
                                    <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/sponsor/sponsor-2.png" alt="sponsor"></a>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="sponsor-item">
                                    <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/sponsor/sponsor-3.png" alt="sponsor"></a>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="sponsor-item">
                                    <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/sponsor/sponsor-4.png" alt="sponsor"></a>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="sponsor-item">
                                    <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/sponsor/sponsor-5.png" alt="sponsor"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./ sponsor-section -->
            <div class="client-page__single text-center d-none">
                <a <?php echo $this->get_render_attribute_string( 'weta-button-arg' ); ?>>
                    <img src="<?php echo esc_url($weta_image_url); ?>" alt="<?php echo esc_attr($weta_image_alt); ?>" />
                </a>
            </div>

        <?php 
	}
}

$widgets_manager->register( new WETA_Brand() );