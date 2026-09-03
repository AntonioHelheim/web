<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Control_Media;
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
class Pixfix_image extends Widget_Base {

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
		return 'weta_image';
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
		return __( 'Image', 'weta-core' );
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
            '_content_image',
            [
                'label' => esc_html__('Image', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'image',
            [
                'label' => esc_html__( 'Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );



        $this->add_control(
            'image_2',
            [
                'label' => esc_html__( 'Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'image_3',
            [
                'label' => esc_html__( 'Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'image_4',
            [
                'label' => esc_html__( 'Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
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
            <?php
                for ($i = 1; $i <= 30; $i++) {
                    $svg_id_[$i] = uniqid();
                }
            ?>

            <svg width="806" height="643" viewBox="0 0 806 643" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.01"><g class="rightLeft">
                <g filter="url(#my_<?php echo $svg_id_['1'] ?>)">
                    <rect x="469" y="394" width="227" height="175" rx="24" fill="url(#my_<?php echo $svg_id_['4'] ?>)" shape-rendering="crispEdges"/>
                    <rect x="469.5" y="394.5" width="226" height="174" rx="23.5" stroke="url(#my_<?php echo $svg_id_['8'] ?>)" shape-rendering="crispEdges"/>
                </g>
                </g></g></g>
                <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.0001"><g class="leftRight">
                <rect x="469.5" y="260.5" width="336" height="93" rx="18.5" fill="url(#my_<?php echo $svg_id_['5'] ?>)" stroke="url(#my_<?php echo $svg_id_['9'] ?>)"/>
                </g></g></g>

                <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="0.01"><g class="rightLeft">
                <g filter="url(#my_<?php echo $svg_id_['2'] ?>)">
                    <rect x="60" y="268" width="359" height="279" rx="23" fill="url(#my_<?php echo $svg_id_['6'] ?>)" shape-rendering="crispEdges"/>
                    <rect x="60.5" y="268.5" width="358" height="278" rx="22.5" stroke="url(#my_<?php echo $svg_id_['10'] ?>)" shape-rendering="crispEdges"/>
                </g>
                </g></g></g>
                <g data-parallax='{"y": -5, "x":5 "scale": 1.1, "smoothness": 15}'><g class="layer" data-depth="-0.01"><g class="rightLeft">
                    <g filter="url(#my_<?php echo $svg_id_['3'] ?>)">
                        <rect x="318" y="40" width="258" height="196" rx="21" fill="url(#my_<?php echo $svg_id_['7'] ?>)" shape-rendering="crispEdges"/>
                        <rect x="318.5" y="40.5" width="257" height="195" rx="20.5" stroke="url(#my_<?php echo $svg_id_['11'] ?>)" shape-rendering="crispEdges"/>
                    </g>
                </g></g></g>
                <defs>
                    <filter id="my_<?php echo $svg_id_['1'] ?>" x="429" y="378" width="307" height="255" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dy="24"/>
                        <feGaussianBlur stdDeviation="20"/>
                        <feComposite in2="hardAlpha" operator="out"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_187_4823"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_187_4823" result="shape"/>
                    </filter>
                    <pattern id="my_<?php echo $svg_id_['4'] ?>" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#my_<?php echo $svg_id_['12'] ?>" transform="matrix(0.00384615 0 0 0.00497512 -0.0230769 -0.0149254)"/>
                    </pattern>
                    <pattern id="my_<?php echo $svg_id_['5'] ?>" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#my_<?php echo $svg_id_['13'] ?>" transform="matrix(0.00293255 0 0 0.0104167 -0.0293255 -0.114583)"/>
                    </pattern>
                    <filter id="my_<?php echo $svg_id_['2'] ?>" x="0" y="244" width="479" height="399" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dy="36"/>
                        <feGaussianBlur stdDeviation="30"/>
                        <feComposite in2="hardAlpha" operator="out"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0.027451 0 0 0 0 0.027451 0 0 0 0 0.027451 0 0 0 0.05 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_187_4823"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_187_4823" result="shape"/>
                    </filter>
                    <pattern id="my_<?php echo $svg_id_['6'] ?>" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#my_<?php echo $svg_id_['14'] ?>" transform="matrix(0.00275482 0 0 0.00353357 -0.0413223 -0.0318021)"/>
                    </pattern>
                    <filter id="my_<?php echo $svg_id_['3'] ?>" x="248" y="0" width="378" height="316" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dx="-10" dy="20"/>
                        <feGaussianBlur stdDeviation="30"/>
                        <feComposite in2="hardAlpha" operator="out"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.05 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_187_4823"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_187_4823" result="shape"/>
                    </filter>
                    <pattern id="my_<?php echo $svg_id_['7'] ?>" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#my_<?php echo $svg_id_['15'] ?>" transform="matrix(0.00384615 0 0 0.00502513 -0.0538461 -0.0753769)"/>
                    </pattern>
                    <linearGradient id="my_<?php echo $svg_id_['8'] ?>" x1="689.5" y1="569.001" x2="469" y2="394.001" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#FF6937" offset="0%"/>
                        <stop offset="1" stop-color="#FF6937" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="my_<?php echo $svg_id_['9'] ?>" x1="637.5" y1="260" x2="637.5" y2="354" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#49A847" offset="0%"/>
                        <stop offset="1" stop-color="#49A847" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="my_<?php echo $svg_id_['10'] ?>" x1="77.0411" y1="288.982" x2="393.576" y2="547.527" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#216FED" stop-opacity="0.43" offset="0%"/>
                        <stop offset="1" stop-color="#216FED" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="my_<?php echo $svg_id_['11'] ?>" x1="318" y1="44.5007" x2="576" y2="226.501" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F2383A" offset="0%"/>
                        <stop offset="1" stop-color="#F2383A" stop-opacity="0"/>
                    </linearGradient>

                    <image id="my_<?php echo $svg_id_['12'] ?>" width="272" height="210" xlink:href="<?php echo $settings['image']['url']; ?>"/>
                    <image id="my_<?php echo $svg_id_['13'] ?>" width="360" height="115" xlink:href="<?php echo $settings['image_2']['url']; ?>"/>
                    <image id="my_<?php echo $svg_id_['14'] ?>" width="391" height="302" xlink:href="<?php echo $settings['image_3']['url']; ?>"/>
                    <image id="my_<?php echo $svg_id_['15'] ?>" width="287" height="228" xlink:href="<?php echo $settings['image_4']['url']; ?>"/>
                </defs>
            </svg>

        <?php 
	}
}

$widgets_manager->register( new Pixfix_image() );