<?php
namespace WETACore\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Utils;
use \Elementor\Control_Media;

use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Text_Shadow;
use \Elementor\Group_Control_Typography;
Use \Elementor\Core\Schemes\Typography;
use \Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Heading extends Widget_Base {

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
		return 'weta_heading';
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
		return __( 'Heading', 'weta-core' );
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

        // layout Panel
        $this->start_controls_section(
            'weta_layout',
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
                    'layout-2' => esc_html__('Layout 2', 'weta-core'),
                    'layout-3' => esc_html__('Layout 3', 'weta-core'),
                    'layout-4' => esc_html__('Layout 4', 'weta-core'),
                    'layout-5' => esc_html__('Layout 5', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->end_controls_section();


        // weta_section_title
        $this->start_controls_section(
            'weta_section_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
            ]
        );

        $this->add_control(
            'weta_subheading_num',
            [
                'label' => esc_html__('Sub Title Before', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('#1', 'weta-core'),
                'label_block' => true,
                'condition' => [
                    'design_style' => ['layout-1', 'layout-2', 'layout-4', 'layout-5'],
                ],
            ]
        );

        $this->add_control(
            'weta_subheading',
            [
                'label' => esc_html__('Sub Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Our services', 'weta-core'),
                'placeholder' => esc_html__('Type Sub Heading Text', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_subheading_icon_switcher',
            [
                'label' => esc_html__( 'Subheading Icon Show', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'design_style' => ['layout-1', 'layout-2', 'layout-4', 'layout-5'],
                ],
            ]
        );

        $this->add_control(
            'weta_title',
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Trust us with your repair needs Repairing with care', 'weta-core'),
                'placeholder' => esc_html__('Type Section Title Here', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'weta_description',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Weta Heading Description', 'weta-core'),
                'placeholder' => esc_html__('Type section description here', 'weta-core'),
                'condition' => [
                    'design_style' => ['layout-2'],
                ],
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

        // style tab here
        $this->start_controls_section(
            '_section_style_content',
            [
                'label' => __( 'Title / Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        // sub Title
        $this->add_control(
            '_heading_sub_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Sub Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'sub_title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .section-2__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .section-3__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'sub_title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-2__subtitle' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_typography',
                'selector' => '{{WRAPPER}} .section__subtitle, .section-2__subtitle, .section-3__subtitle',
            ]
        );

        $this->add_control(
            'sub_title_number_color',
            [
                'label' => __( 'Number Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle span' => 'color: {{VALUE}}',
                ],
                'separator' => 'before',
                'condition' => [
                    'design_style' => ['layout-1', 'layout-2', 'layout-4', 'layout-5'],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_num_typography',
                'selector' => '{{WRAPPER}} .section__subtitle span, .section-3__subtitle span',
                'condition' => [
                    'design_style' => ['layout-1', 'layout-2', 'layout-4', 'layout-5'],
                ],
            ]
        );

        $this->add_control(
            'sub_title_number_bg_color',
            [
                'label' => __( 'BG Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .section-2__subtitle' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__subtitle span' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => ['layout-1', 'layout-2', 'layout-3', 'layout-4', 'layout-5'],
                ],
                
            ]
        );

        $this->add_responsive_control(
            'subheading_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .section__subtitle span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .section-2__subtitle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .section-3__subtitle span' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title
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
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .section__title' => 'margin-bottom: {{SIZE}}{{UNIT}} !important',
                    '{{WRAPPER}} .section-2__title' => 'margin-bottom: {{SIZE}}{{UNIT}} !important',
                ],
                'condition' => [
                    'design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-2__title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .section-3__title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .section__title, .section-2__title, .section-3__title.lg',
            ]
        );

        // Description
        $this->add_control(
            '_content_description',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .section__title-wrapper p' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'design_style' => ['layout-2'],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .section__title-wrapper p',
                'condition' => [
                    'design_style' => ['layout-2'],
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

        <?php if ( $settings['design_style']  == 'layout-1' ) : 
            $this->add_render_attribute('title_args', 'class', 'section__title text-uppercase wow fadeIn animated');
            $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
        ?>

            <div class="section__title-wrapper text-center">
                <?php if ( !empty($settings['weta_subheading']) ) : ?>
                    <span class="section__subtitle justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s"><?php if(!empty($settings['weta_subheading_num'])): ?><span class="layer" data-depth="0.009"><?php echo rrdevs_kses($settings['weta_subheading_num']); ?></span><?php endif; ?> <?php echo rrdevs_kses( $settings['weta_subheading'] ); ?>
                    <?php if($settings['weta_subheading_icon_switcher']): ?>
                        <img class="rightLeft" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icons/arrow-right.svg" alt="arrow not found">
                    <?php endif; ?>
                    </span>
                <?php endif; ?>
                <?php
                    if ( !empty($settings['weta_title' ]) ) :
                        printf( '<%1$s %2$s>%3$s</%1$s>',
                            tag_escape( $settings['weta_title_tag'] ),
                            $this->get_render_attribute_string( 'title_args' ),
                            rrdevs_kses( $settings['weta_title' ] )
                            );
                    endif;
                ?>
            </div>

        <?php elseif ( $settings['design_style']  == 'layout-2' ) : 
            $this->add_render_attribute('title_args', 'class', 'section__title mb-5 text-uppercase wow fadeIn animated');
            $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
        ?>

            <div class="section__title-wrapper">
                <?php if ( !empty($settings['weta_subheading']) ) : ?>
                    <span class="section__subtitle mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s"><?php if(!empty($settings['weta_subheading_num'])): ?><span class="layer" data-depth="0.009"><?php echo rrdevs_kses($settings['weta_subheading_num']); ?></span><?php endif; ?> <?php echo rrdevs_kses( $settings['weta_subheading'] ); ?>
                        <?php if($settings['weta_subheading_icon_switcher']): ?>
                            <img class="rightLeft" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icons/arrow-right.svg" alt="arrow not found">
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                <?php
                    if ( !empty($settings['weta_title' ]) ) :
                        printf( '<%1$s %2$s>%3$s</%1$s>',
                            tag_escape( $settings['weta_title_tag'] ),
                            $this->get_render_attribute_string( 'title_args' ),
                            rrdevs_kses( $settings['weta_title' ] )
                            );
                    endif;
                ?>
                <?php if(!empty( $settings['weta_description'] )) : ?>
                    <p class="mb-0 wow fadeIn animated" data-wow-delay="500ms"><?php echo rrdevs_kses( $settings['weta_description'] ); ?></p>
                <?php endif; ?>
            </div>

        <?php elseif ( $settings['design_style']  == 'layout-3' ) : 
            $this->add_render_attribute('title_args', 'class', 'section-2__title xl text-uppercase section-title-2-animation');
        ?>

            <div class="section-2__title-wrapper case-studies__content">
                <?php if ( !empty($settings['weta_subheading']) ) : ?>
                    <span class="section-2__subtitle justify-content-start mb-15 mb-xs-5 section-subTile-2-animation"><?php echo rrdevs_kses( $settings['weta_subheading'] ); ?></span>
                <?php endif; ?>
                <?php
                    if ( !empty($settings['weta_title' ]) ) :
                        printf( '<%1$s %2$s>%3$s</%1$s>',
                            tag_escape( $settings['weta_title_tag'] ),
                            $this->get_render_attribute_string( 'title_args' ),
                            rrdevs_kses( $settings['weta_title' ] )
                            );
                    endif;
                ?>
            </div>

        <?php elseif ( $settings['design_style']  == 'layout-4' ) : 
            $this->add_render_attribute('title_args', 'class', 'section-3__title lg wow fadeIn animated');
            $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
        ?>

            <div class="section-3__title-wrapper service-2__content hed-st-04 text-center">
                <?php if ( !empty($settings['weta_subheading']) ) : ?>
                    <span class="section-3__subtitle justify-content-center mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                    <?php if(!empty($settings['weta_subheading_num'])): ?><span class="layer" data-depth="0.009"><?php echo rrdevs_kses($settings['weta_subheading_num']); ?></span><?php endif; ?>                        
                        <?php echo rrdevs_kses( $settings['weta_subheading'] ); ?>
                    <?php if($settings['weta_subheading_icon_switcher']): ?>
                        <img class="rightLeft" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icons/mad-emoji.svg" alt="arrow not found">
                    <?php endif; ?>
                </span>
                <?php endif; ?>
                
                <?php
                    if ( !empty($settings['weta_title' ]) ) :
                        printf( '<%1$s %2$s>%3$s</%1$s>',
                            tag_escape( $settings['weta_title_tag'] ),
                            $this->get_render_attribute_string( 'title_args' ),
                            rrdevs_kses( $settings['weta_title' ] )
                            );
                    endif;
                ?>
            </div>

        <?php elseif ( $settings['design_style']  == 'layout-5' ) : 
            $this->add_render_attribute('title_args', 'class', 'section-3__title lg mb-15 wow fadeIn animated');
            $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
        ?>

            <div class="section-3__title-wrapper">
                <?php if ( !empty($settings['weta_subheading']) ) : ?>
                    <span class="section-3__subtitle flex-wrap justify-content-start mb-10 mb-xs-5 wow fadeIn animated" data-wow-delay=".1s">
                        <?php if(!empty($settings['weta_subheading_num'])): ?><span class="layer" data-depth="0.009"><?php echo rrdevs_kses($settings['weta_subheading_num']); ?></span><?php endif; ?>                        
                            <?php echo rrdevs_kses( $settings['weta_subheading'] ); ?>
                        <?php if($settings['weta_subheading_icon_switcher']): ?>
                            <img class="rightLeft" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icons/mad-emoji.svg" alt="arrow not found">
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                
                
                <?php
                    if ( !empty($settings['weta_title' ]) ) :
                        printf( '<%1$s %2$s>%3$s</%1$s>',
                            tag_escape( $settings['weta_title_tag'] ),
                            $this->get_render_attribute_string( 'title_args' ),
                            rrdevs_kses( $settings['weta_title' ] )
                            );
                    endif;
                ?>

                <?php if(!empty( $settings['weta_description'] )) : ?>
                    <p class="mb-0 wow fadeIn animated" data-wow-delay="500ms"><?php echo rrdevs_kses( $settings['weta_description'] ); ?></p>
                <?php endif; ?>
            </div>

        <?php endif; ?>

        <?php
	}
}

$widgets_manager->register( new WETA_Heading() );