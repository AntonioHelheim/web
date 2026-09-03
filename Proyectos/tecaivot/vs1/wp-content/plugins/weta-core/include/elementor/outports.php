<?php
namespace WetaCore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Control_Media;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Css_Filter;
use \Elementor\Repeater;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Utils;
use \Elementor\Group_Control_Box_Shadow;
use WetaCore\Elementor\Controls\Group_Control_WetaBGGradient;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * weta Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class Weta_outports extends Widget_Base {

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
		return 'weta_outports_bar';
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
		return __( 'Outports', 'weta-core' );
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
    
   
          // outports group
          $this->start_controls_section(
            'weta_outports',
            [
                'label' => esc_html__('outports List', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'weta_process_hide_show',
            [
                'label' => esc_html__( 'Show Items', 'weta-core' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'rr_outports_icon_type',
            [
                'label' => esc_html__('Select Icon Type', 'weta-core'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'icon',
                'options' => [
                    'image' => esc_html__('Image', 'weta-core'),
                    'icon' => esc_html__('Icon', 'weta-core'),
                    'svg' => esc_html__('SVG', 'weta-core'),
                ],
            ]
        );

        $repeater->add_control(
            'rr_outports_image',
            [
                'label' => esc_html__('Upload Icon Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'rr_outports_icon_type' => 'image',
                ]

            ]
        );

        $repeater->add_control(
            'rr_outports_icon_svg',
            [
                    'show_label' => false,
                    'type' => Controls_Manager::TEXTAREA,
                    'label_block' => true,
                    'placeholder' => esc_html__('SVG Code Here', 'weta-core'),
                    'condition' => [
                        'rr_outports_icon_type' => 'svg'
                    ]
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'rr_outports_icon',
                [
                    'show_label' => false, 
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'fa fa-star',
                    'condition' => [
                        'rr_outports_icon_type' => 'icon'
                    ]
                ]
            );
        } else {
            $repeater->add_control(
                'rr_outports_selected_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'fas fa-star',
                        'library' => 'solid',
                    ],
                    'condition' => [
                        'rr_outports_icon_type' => 'icon'
                    ]
                ]
            );
        }

        $repeater->add_control(
            'rr_outports_title', [
                'label' => esc_html__('Title', 'weta-core'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Service Title', 'weta-core'),
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'rr_outports_title_url', [
                'label' => esc_html__('Title Url', 'weta-core'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('#', 'weta-core'),
                'label_block' => true,
            ]
        );
        $repeater->add_control(
            'rr_outports_description',
            [
                'label' => esc_html__('Description', 'weta-core'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered.',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'rr_outports_list',
            [
                'label' => esc_html__('Services - List', 'weta-core'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'rr_outports_title' => esc_html__('Discover', 'weta-core'),
                    ],
                    [
                        'rr_outports_title' => esc_html__('Define', 'weta-core')
                    ],
                    [
                        'rr_outports_title' => esc_html__('Develop', 'weta-core')
                    ]
                ],
                'title_field' => '{{{ rr_outports_title }}}',
            ]
        );
        $this->end_controls_section(); 
    
              // TAB_STYLE
              $this->start_controls_section(
                'design_layout_style',
                [
                    'label' => __( 'outports Box Style', 'weta-core' ),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
            );
            $this->add_responsive_control(
                'content_padding_box',
                [
                    'label' => __( 'Content Padding', 'weta-core' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .outports-items' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
        
            $this->add_responsive_control(
                'content_margin_box',
                [
                    'label' => __( 'Content Margin', 'weta-core' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .outports-items' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            $this->add_control(
                'box_border_radius',
                [
                    'label'     => esc_html__( 'Border Radius', 'weta-core' ),
                    'type'      => Controls_Manager::SLIDER,
                    'selectors' => [
                        '{{WRAPPER}} .outports-item' => 'border-radius: {{SIZE}}px;',
                    ],
                ] 
            );
            $this->add_control(
                'box_border_radius_color',
                [
                    'label'     => esc_html__( 'Border Color', 'weta-core' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .outports-item' => 'border-color: {{VALUE}}',
                    ],
                ]
            );
            $this->add_control(
                'content_box_bg',
                [
                    'label'     => esc_html__( 'Background', 'weta-core' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .outports-item' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
    
            $this->add_control(
                '__titile_heading',
                [
                    'label' => esc_html__( 'Title', 'text-domain' ),
                    'type' => Controls_Manager::HEADING,
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
                        '{{WRAPPER}} .outports-items .outports-item .outports-info .title' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                    ],
                ]
            );
    
            $this->add_control(
                'title_color',
                [
                    'label' => esc_html__( 'Color', 'text-domain' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .outports-items .outports-item .outports-info .title' => 'color: {{VALUE}}',
                    ],
                ]
            );
    
            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'title_typography_title',
                    'selector' => '{{WRAPPER}} .outports-items .outports-item .outports-info .title',
                ]
            );
    
            $this->add_control(
                '_description_heading',
                [
                    'label' => esc_html__( 'Description', 'text-domain' ),
                    'type' => Controls_Manager::HEADING,
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
                        '{{WRAPPER}} .outports-items .outports-item .outports-info  p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
    
            $this->add_control(
                'description_color',
                [
                    'label' => esc_html__( 'Color', 'text-domain' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .outports-items .outports-item .outports-info p' => 'color: {{VALUE}}',
                    ],
                ]
            );
    
            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'description_typography',
                    'selector' => '{{WRAPPER}} .outports-items .outports-item .outports-info p',
                ]
            );
    
            $this->end_controls_section();  
            $this->start_controls_section(
                'design_icon_style',
                [
                    'label' => __( 'Icon Style', 'weta-core' ),
                    'tab' => Controls_Manager::TAB_STYLE,
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

        $this->add_responsive_control(
            'icon_bottom_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} ..outports-items .outports-item .outports-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __( 'Icon Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ..outports-items .outports-item .outports-icon::before' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __( 'Icon Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} ..outports-items .outports-item .outports-icon::before' => 'font-size: {{SIZE}}{{UNIT}};',
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

        $this->add_render_attribute('title_args', 'class', 'section__title wow fadeIn  mb-0');
        $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');

        if ( !empty($settings['weta_image']['url']) ) {
            $weta_image = !empty($settings['weta_image']['id']) ? wp_get_attachment_image_url( $settings['weta_image']['id'], 'full' ) : $settings['weta_image']['url'];
            $weta_image_alt = get_post_meta($settings["weta_image"]["id"], "_wp_attachment_image_alt", true);
        }
		?>
<div class="outports-items">
    <?php foreach ($settings['rr_outports_list'] as $key => $item) : ?>
    <div class="outports-item wow fade-in-right" data-wow-delay="300ms">
        <div class="outports-icon">
            <?php if($item['rr_outports_icon_type'] == 'icon') : ?>
            <?php if (!empty($item['rr_outports_icon']) || !empty($item['rr_outports_selected_icon']['value'])) : ?>
                <?php weta_render_icon($item, 'rr_outports_icon', 'rr_outports_selected_icon'); ?>
            <?php endif; ?>
            <?php elseif( $item['rr_outports_icon_type'] == 'image' ) : ?>
            <?php if (!empty($item['rr_outports_image']['url'])): ?>
                <img src="<?php echo $item['rr_outports_image']['url']; ?>"
                    alt="<?php echo get_post_meta(attachment_url_to_postid($item['rr_outports_image']['url']), '_wp_attachment_image_alt', true); ?>">
            <?php endif; ?>
            <?php else : ?>
            <?php if (!empty($item['rr_outports_icon_svg'])): ?>
                <?php echo $item['rr_outports_icon_svg']; ?>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="outports-info">
        <?php if (!empty($item['rr_outports_title'])): ?>
            <h3 class="title"><a href="https://rrdevs.net/demos/wp/weta/service/"><?php echo weta_kses($item['rr_outports_title' ]); ?></a>    <?php if (!empty($settings['weta_process_hide_show'])): ?>
                            <span>0<?php echo ($key+1); ;?></span>
                        <?php endif; ?>
            </h3>
            <?php endif; ?>
            <?php if (!empty($item['rr_outports_description' ])): ?>
            <p><?php echo weta_kses($item['rr_outports_description']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
	}

}

$widgets_manager->register( new weta_outports() );