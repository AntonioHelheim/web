<?php
namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Typography;
use \Elementor\Control_Media;
use \Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Video_Popup extends Widget_Base {

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
		return 'weta_video_popup';
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
		return __( 'Video', 'weta-core' );
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

        // weta_layout
        $this->start_controls_section(
            'weta_layout',
            [
                'label' => esc_html__('Design Layout', 'weta-core'),
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


        $this->add_control(
            'video_shape_switch',
            [
                'label' => esc_html__( 'Video Shape Switch', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'weta-core' ),
                'label_off' => esc_html__( 'No', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'weta_design_style' => 'layout-2',
                ],
            ]
        );

        $this->end_controls_section();

        // weta_section_title
        $this->start_controls_section(
            'weta_section_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ]
        );

        $this->add_control(
            'weta_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Video Box Title', 'weta-core'),
                'placeholder' => esc_html__('Type Video Box Title Here', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_description',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Description Text', 'weta-core'),
                'placeholder' => esc_html__('Type Description Text Here', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_title_tag',
            [
                'label' => esc_html__('Title HTML Tag', 'weta-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'h1' => [
                        'title' => esc_html__('H1', 'weta-core'),
                        'icon' => 'eicon-editor-h1'
                    ],
                    'h2' => [
                        'title' => esc_html__('H2', 'weta-core'),
                        'icon' => 'eicon-editor-h2'
                    ],
                    'h3' => [
                        'title' => esc_html__('H3', 'weta-core'),
                        'icon' => 'eicon-editor-h3'
                    ],
                    'h4' => [
                        'title' => esc_html__('H4', 'weta-core'),
                        'icon' => 'eicon-editor-h4'
                    ],
                    'h5' => [
                        'title' => esc_html__('H5', 'weta-core'),
                        'icon' => 'eicon-editor-h5'
                    ],
                    'h6' => [
                        'title' => esc_html__('H6', 'weta-core'),
                        'icon' => 'eicon-editor-h6'
                    ]
                ],
                'default' => 'h2',
                'toggle' => false,
            ]
        );

        $this->end_controls_section();


        // _weta_image
        $this->start_controls_section(
            '_weta_image_section',
            [
                'label' => esc_html__('Image', 'weta-core'),
            ]
        );
        $this->add_control(
            'weta_image',
            [
                'label' => esc_html__( 'Background Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'weta_image_size',
                'default' => 'full',
                'exclude' => [
                    'custom'
                ]
            ]
        );

        $this->add_control(
            'weta_video_url',
            [
                'label' => esc_html__('Video', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => 'https://www.youtube.com/watch?v=3WrZMzqpFTc',
                'title' => esc_html__('Video url', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_video_text',
            [
                'label' => esc_html__('Video Text', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Play', 'weta-core'),
                'label_block' => true,
            ]
        ); 

        $this->end_controls_section();


		// TAB_STYLE
		$this->start_controls_section(
			'content_layout_style',
			[
				'label' => __( 'Layout', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __( 'Content Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .operationl-procedures, .play-video' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_background_color',
            [
                'label' => esc_html__( 'Content Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .operationl-procedures, .play-video' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // style title
        $this->start_controls_section(
            '_section_style_content',
            [
                'label' => __( 'Title & Description', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'weta_design_style' => 'layout-1',
                ],
            ]
        );

        // Title
        $this->add_control(
            '_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta_el_title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta_el_title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .weta_el_title',
            ]
        );



        // Description
        $this->add_control(
            '_heading_description_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'description_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .weta_el_desc' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta_el_desc' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .weta_el_desc',
            ]
        );

        $this->end_controls_section();


        // Video Style 
		$this->start_controls_section(
			'video_style',
			[
				'label' => __( 'Video Button', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            'video_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .operationl-procedures__video-btn .popup-video' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .play-video__content .popup-video' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'video_background_hover',
            [
                'label'     => esc_html__( 'Background (Hover)', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .operationl-procedures__video-btn .popup-video:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .play-video__content .popup-video:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
			'play_button_size',
			[
				'label' => esc_html__( 'Button Size', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .operationl-procedures__video-btn .popup-video' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .play-video__content .popup-video' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				],
			]
		);

        $this->add_control(
			'play_button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .operationl-procedures__video-btn .popup-video' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .play-video__content .popup-video' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


        // Video Text
        $this->add_control(
            '_heading_video_text_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Video Text', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'video_text_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .operationl-procedures__video-btn .popup-video' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .play-video__content .popup-video' => 'color: {{VALUE}}',
                ],
            ]
        );        

        $this->add_control(
            'video_text_hover_color',
            [
                'label' => __( 'Hover Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .operationl-procedures__video-btn .popup-video:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .play-video__content .popup-video:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'video_text_typography',
                'selector' => '{{WRAPPER}} .operationl-procedures__video-btn .popup-video, .play-video__content .popup-video',
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
            $weta_image = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'], $settings['weta_image_size_size']) : $settings['weta_image']['url'];
            $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
        }

        if ( !empty($settings['contact_image']['url']) ) {
            $contact_image = !empty($settings['contact_image']['id']) ? wp_get_attachment_image_url( $settings['contact_image']['id'], $settings['weta_image_size_size']) : $settings['contact_image']['url'];
            $contact_image_alt = get_post_meta($settings["contact_image"]["id"], "_wp_attachment_image_alt", true);
        }

		?>

        <?php if ( $settings['weta_design_style']  == 'layout-1' ):
            $this->add_render_attribute('title_args', 'class', 'mb-10 color-white text-uppercase wow fadeIn animated weta_el_title');         
            $this->add_render_attribute('title_args', 'data-wow-delay', '.1s');    
        ?>

    <!-- operationl-procedures start-->
    <section class="operationl-procedures operationl-procedures__overlay overflow-hidden operationl-procedures__section-space overflow-hidden" data-background="<?php echo esc_url($weta_image); ?>">
        <div class="container">
            <div class="row">
                <div class="col-xxl-7">
                    <div class="operationl-procedures__content">
                        <?php
                            if ( !empty($settings['weta_title' ]) ) :
                                printf( '<%1$s %2$s>%3$s</%1$s>',
                                    tag_escape( $settings['weta_title_tag'] ),
                                    $this->get_render_attribute_string( 'title_args' ),
                                    rrdevs_kses( $settings['weta_title' ] )
                                    );
                            endif;
                        ?>

                        <?php if ( !empty($settings['weta_description']) ) : ?>
                        <p class="color-white mb-0 wow fadeIn animated weta_el_desc" data-wow-delay=".3s"><?php echo rrdevs_kses( $settings['weta_description'] ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ( !empty($settings['weta_video_url']) ) : ?>
                <div class="col-xxl-5">
                    <div class="operationl-procedures__video-btn wow fadeIn animated" data-wow-delay=".6s">
                        <a href="<?php echo esc_url($settings["weta_video_url"]); ?>" class="popup-video zooming" data-effect="mfp-move-from-top vertical-middle">
                            <?php if ( !empty($settings['weta_video_text']) ) : ?>
                                <?php echo rrdevs_kses( $settings['weta_video_text'] ); ?>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- operationl-procedures end-->


        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ): ?>

        <!-- play-video start -->
        <div class="play-video section-space overflow-hidden">
            <div class="container container-xxl position-relative z-1">
                <?php if(!empty($settings['video_shape_switch'])) : ?>
                <div class="play-video__horizental-bar">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="play-video__content">
                            <?php if ($settings['weta_image']['url'] || $settings['weta_image']['id']) : ?>
                            <div class="play-video__media play-video-animation">
                                <img src="<?php echo esc_url($weta_image); ?>" alt="<?php print $weta_image_alt; ?>" class="img-fluid">
                            </div>
                            <?php endif; ?>

                            <?php if ( !empty($settings['weta_video_url']) ) : ?>
                            <div class="popup-video__wrapper">
                                <div class="popup-video-button_animation">
                                    <a href="<?php echo esc_url($settings["weta_video_url"]); ?>" class="popup-video zooming" data-effect="mfp-move-from-top vertical-middle">
                                        <?php if ( !empty($settings['weta_video_text']) ) : ?>
                                            <?php echo rrdevs_kses( $settings['weta_video_text'] ); ?>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- play-video end -->

        <?php endif; ?>

        <?php
		
	}

}

$widgets_manager->register( new WETA_Video_Popup() );