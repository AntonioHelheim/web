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
class WETA_Contact_Info extends Widget_Base {

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
		return 'weta_contact_info';
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
		return __( 'Contact Info', 'weta-core' );
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
            'weta_contact_info',
            [
                'label' => esc_html__('Contact Info', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $this->add_control(
                'weta_contact_info_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'fa fa-map-marker',
                ]
            );
        } else {
            $this->add_control(
                'weta_contact_info_selected_icon',
                [
                    'show_label' => false,
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'fa fa-map-marker',
                        'library' => 'solid',
                    ],
                ]
            );
        }

        $this->add_control(
            'weta_contact_info_title', 
            [
                'label' => esc_html__('Title', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => esc_html__( 'Our Location', 'weta-core' ),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'weta_info_type',
            [
                'label' => esc_html__('Info Type', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'phone' => esc_html__('Phone', 'weta-core'),
                    'email' => esc_html__('Email', 'weta-core'),
                    'url' => esc_html__('URL', 'weta-core'),
                ],
                'default' => 'phone',
            ]
        );

        $repeater->add_control(
            'weta_contact_info_text', [
                'label' => esc_html__('Text', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'weta_contact_info_url', [
                'label' => esc_html__('URL', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'basic' ),
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'condition' => [
                    'weta_info_type' => [ 'phone', 'email', 'url' ],
                ],
            ]
        );
     
        $this->add_control(
            'weta_contact_info_list',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'weta_contact_info_text' => esc_html__('354 Oakridge, Camden NJ 08102 - USA', 'weta-core'),
                    ],
                    [
                        'weta_contact_info_text' => esc_html__('354 Oakridge, Camden NJ 08102 - USA', 'weta-core'),
                    ],
                ],
                'title_field' => '{{{ weta_contact_info_text }}}',
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            '_section_contact_info_style',
            [
                'label' => __( 'Contact Info', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_layout',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Layout', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-item' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'content_border_color',
            [
                'label' => esc_html__( 'Border Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-item.item-2 .contact-list li' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'content_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .contact-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_margin',
            [
                'label'      => esc_html__( 'Margin', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .contact-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'main_content_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Section Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'main_title_bottom_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .lg-contact-info-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'main_title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lg-contact-info-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'main_title_typography',
                'selector' => '{{WRAPPER}} .lg-contact-info-title',
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
                    '{{WRAPPER}} .contact-item.item-2 .contact-list li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-item.item-2 .contact-list li' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .contact-item.item-2 .contact-list li',
            ]
        );

        $this->add_control(
            '_heading_info',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Info', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'info_bottom_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .contact-item.item-2 .contact-list li a' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'info_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .contact-item.item-2 .contact-list li a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'info_typography',
                'selector' => '{{WRAPPER}} .contact-box__single-text p a, .contact-box__single-text p',
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


<div class="contact-item item-2">
<?php if ( !empty($settings['weta_contact_info_title']) ) : ?>
    <h3 class="lg-contact-info-title"><?php echo rrdevs_kses($settings['weta_contact_info_title' ]); ?></h3>
<?php endif; ?>
    <ul class="contact-list">
    <?php foreach ($settings['weta_contact_info_list'] as $item) : ?>
        <?php if ( $item['weta_info_type']  == 'url' ): ?>
        <li>Address : <a href="<?php echo esc_url($item['weta_contact_info_url' ]); ?>"><?php echo rrdevs_kses($item['weta_contact_info_text' ]); ?></a></li>
        <?php elseif ( $item['weta_info_type']  == 'phone' ): ?>
        <li>Phone : <a href="tel:<?php echo esc_attr($item['weta_contact_info_url' ]); ?>"><?php echo rrdevs_kses($item['weta_contact_info_text' ]); ?></a></li>
        <?php elseif ( $item['weta_info_type']  == 'email' ): ?>
        <li>Email : <a href="mailto:<?php echo esc_attr($item['weta_contact_info_url' ]); ?>"><?php echo rrdevs_kses($item['weta_contact_info_text' ]); ?></a></li>
        <?php endif; ?>
        <?php endforeach; ?>      
    </ul>
</div>

<div class="contact-box__single text-center d-none">
    <?php if (!empty($settings['weta_contact_info_icon']) || !empty($settings['weta_contact_info_selected_icon']['value'])) : ?>
    <div class="contact-box__single-icon">
        <?php weta_render_icon($settings, 'weta_contact_info_icon', 'weta_contact_info_selected_icon'); ?>
    </div>
    <?php endif; ?>

    <div class="contact-box__single-text">
        <?php if ( !empty($settings['weta_contact_info_title']) ) : ?>
        <h2><a href="#"><?php echo rrdevs_kses($settings['weta_contact_info_title' ]); ?></a></h2>
        <?php endif; ?>
        <?php foreach ($settings['weta_contact_info_list'] as $item) : ?>
        <?php if ( $item['weta_info_type']  == 'text' ): ?>
        <p><?php echo rrdevs_kses($item['weta_contact_info_text' ]); ?></p>
        <?php elseif ( $item['weta_info_type']  == 'phone' ): ?>
        <p><a
                href="tel:<?php echo esc_attr($item['weta_contact_info_url' ]); ?>"><?php echo rrdevs_kses($item['weta_contact_info_text' ]); ?></a>
        </p>
        <?php elseif ( $item['weta_info_type']  == 'email' ): ?>
        <p><a
                href="mailto:<?php echo esc_attr($item['weta_contact_info_url' ]); ?>"><?php echo rrdevs_kses($item['weta_contact_info_text' ]); ?></a>
        </p>
        <?php elseif ( $item['weta_info_type']  == 'url' ): ?>
        <p><a
                href="<?php echo esc_url($item['weta_contact_info_url' ]); ?>"><?php echo rrdevs_kses($item['weta_contact_info_text' ]); ?></a>
        </p>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php 
	}
}
$widgets_manager->register( new WETA_Contact_Info() );