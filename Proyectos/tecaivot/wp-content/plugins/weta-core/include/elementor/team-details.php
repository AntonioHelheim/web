<?php
namespace WETACore\Widgets;

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
use WETACore\Elementor\Controls\Group_Control_WETABGGradient;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Team_Details extends Widget_Base {

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
		return 'weta_team_details';
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
		return __( 'Team Details', 'weta-core' );
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

    protected static function get_profile_names()
    {
        return [
            '500px' => esc_html__('500px', 'weta-core'),
            'apple' => esc_html__('Apple', 'weta-core'),
            'behance' => esc_html__('Behance', 'weta-core'),
            'bitbucket' => esc_html__('BitBucket', 'weta-core'),
            'codepen' => esc_html__('CodePen', 'weta-core'),
            'delicious' => esc_html__('Delicious', 'weta-core'),
            'deviantart' => esc_html__('DeviantArt', 'weta-core'),
            'digg' => esc_html__('Digg', 'weta-core'),
            'dribbble' => esc_html__('Dribbble', 'weta-core'),
            'email' => esc_html__('Email', 'weta-core'),
            'facebook' => esc_html__('Facebook', 'weta-core'),
            'flickr' => esc_html__('Flicker', 'weta-core'),
            'foursquare' => esc_html__('FourSquare', 'weta-core'),
            'github' => esc_html__('Github', 'weta-core'),
            'houzz' => esc_html__('Houzz', 'weta-core'),
            'instagram' => esc_html__('Instagram', 'weta-core'),
            'jsfiddle' => esc_html__('JS Fiddle', 'weta-core'),
            'linkedin' => esc_html__('LinkedIn', 'weta-core'),
            'medium' => esc_html__('Medium', 'weta-core'),
            'pinterest' => esc_html__('Pinterest', 'weta-core'),
            'product-hunt' => esc_html__('Product Hunt', 'weta-core'),
            'reddit' => esc_html__('Reddit', 'weta-core'),
            'slideshare' => esc_html__('Slide Share', 'weta-core'),
            'snapchat' => esc_html__('Snapchat', 'weta-core'),
            'soundcloud' => esc_html__('SoundCloud', 'weta-core'),
            'spotify' => esc_html__('Spotify', 'weta-core'),
            'stack-overflow' => esc_html__('StackOverflow', 'weta-core'),
            'tripadvisor' => esc_html__('TripAdvisor', 'weta-core'),
            'tumblr' => esc_html__('Tumblr', 'weta-core'),
            'twitch' => esc_html__('Twitch', 'weta-core'),
            'twitter' => esc_html__('Twitter', 'weta-core'),
            'vimeo' => esc_html__('Vimeo', 'weta-core'),
            'vk' => esc_html__('VK', 'weta-core'),
            'website' => esc_html__('Website', 'weta-core'),
            'whatsapp' => esc_html__('WhatsApp', 'weta-core'),
            'wordpress' => esc_html__('WordPress', 'weta-core'),
            'xing' => esc_html__('Xing', 'weta-core'),
            'yelp' => esc_html__('Yelp', 'weta-core'),
            'youtube' => esc_html__('YouTube', 'weta-core'),
        ];
    }


	protected function register_controls() {

        $this->start_controls_section(
            'weta_section_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
            ]
        );

        $this->add_control(
            'image',
            [
                'label' => esc_html__( 'Choose Image', 'weta-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );     

        $this->add_control(
            'name',
            [
                'label' => esc_html__('Name', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Ravi OLeigh', 'weta-core'),
                'label_block' => true,
            ]
        );       

        $this->add_control(
            'position',
            [
                'label' => esc_html__('Position', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('FOUNDER & CEO', 'weta-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'about',
            [
                'label' => esc_html__('About', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => esc_html__('Etiam ullamcorper malasada elementum. In molestie pharetra lacus sit abet protium elit facilices acilisis Quisque sit amet lobortis diam Pelletize elementum nibh quis ex sempe eu dipygus Donec nisi purus mollis scelerisque tellus vel, auctor aliquet quam.', 'weta-core'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'label',
            [
                'label' => esc_html__('Label', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control(
            'field_type',
            [
                'label' => esc_html__('Type', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'phone' => esc_html__( 'Phone', 'weta-core' ),
                    'email' => esc_html__( 'Email', 'weta-core' ),
                    'website' => esc_html__( 'Website', 'weta-core' ),
                    'text' => esc_html__( 'Text', 'weta-core' ),
                ],
                'default' => 'phone',
            ]
        );

        $repeater->add_control(
            'phone',
            [
                'label' => esc_html__('Phone', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'field_type' => [ 'phone' ],
                ],
            ]
        );

        $repeater->add_control(
            'email',
            [
                'label' => esc_html__('Email', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'field_type' => [ 'email' ],
                ],
            ]
        );

        $repeater->add_control(
            'website',
            [
                'label' => esc_html__('Website', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'field_type' => [ 'website' ],
                ],
            ]
        );

        $repeater->add_control(
            'text',
            [
                'label' => esc_html__('Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'field_type' => [ 'text' ],
                ],
            ]
        );

        $this->add_control(
            'info_list',
            [
                'label' => esc_html__('Info List', 'weta-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'label' => esc_html__( 'PHONE:', 'weta-core' ),
                        'field_type' => 'phone',
                        'phone' => '+857 6458 547 65',
                    ],
                    [
                        'label' => esc_html__( 'EMAIL:', 'weta-core' ),
                        'field_type' => 'email',
                        'email' => 'example@gmail.com',
                    ],
                    [
                        'label' => esc_html__( 'SPECIALIST:', 'weta-core' ),
                        'field_type' => 'text',
                        'text' => esc_html__( 'ui/ux Design', 'weta-core' ),
                    ],
                    [
                        'label' => esc_html__( 'EXPERIENCE:', 'weta-core' ),
                        'field_type' => 'text',
                        'text' => esc_html__( '5+ years', 'weta-core' ),
                    ],
                ],
                'title_field' => '{{{ label }}}',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_section_social',
            [
                'label' => esc_html__('Social Profiles', 'weta-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_profiles',
            [
                'label' => esc_html__('Show Profiles', 'weta-core'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'weta-core'),
                'label_off' => esc_html__('Hide', 'weta-core'),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
                'style_transfer' => true,
            ]
        );

        $this->add_control(
            'weta_profile_label',
            [
                'label' => esc_html__('Social Label', 'weta-core'),
                'description' => weta_get_allowed_html_desc( 'intermediate' ),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Follow us:', 'weta-core'),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'name',
            [
                'label' => esc_html__('Profile Name', 'weta-core'),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'select2options' => [
                    'allowClear' => false,
                ],
                'options' => self::get_profile_names()
            ]
        );

        $repeater->add_control(
            'link', [
                'label' => esc_html__('Profile Link', 'weta-core'),
                'placeholder' => esc_html__('Add your profile link', 'weta-core'),
                'type' => Controls_Manager::URL,
                'label_block' => true,
                'autocomplete' => false,
                'show_external' => false,
                'condition' => [
                    'name!' => 'email'
                ],
                'dynamic' => [
                    'active' => true,
                ]
            ]
        );
        $this->add_control(
            'profiles',
            [
                'show_label' => false,
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '<# print(name.slice(0,1).toUpperCase() + name.slice(1)) #>',
                'default' => [
                    [
                        'link' => ['url' => 'https://facebook.com/'],
                        'name' => 'facebook'
                    ],
                    [
                        'link' => ['url' => 'https://linkedin.com/'],
                        'name' => 'linkedin'
                    ],
                    [
                        'link' => ['url' => 'https://twitter.com/'],
                        'name' => 'twitter'
                    ]
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            '_content_progressbar',
            [
                'label' => esc_html__('Progress Bar', 'runok-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'skill_title',
            [
                'label' => esc_html__('Title', 'runok-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'skill_percent',
            [
                'label' => esc_html__('Skill Percent', 'runok-core'),
                'description' => esc_html__('Please avoid %, px etc. Only type digits', 'runok-core'),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'runok_skill_list',
            [
                'label' => esc_html__( 'Skill - List', 'runok-core' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'skill_title' => __( 'PRODUCT DESIGN', 'runok-core' ),
                        'skill_percent' => '80',
                    ],
                    [
                        'skill_title' => __( 'SOFTWARE DEVELOPER', 'runok-core' ),
                        'skill_percent' => '70',
                    ],
                    [
                        'skill_title' => __( 'DATA INTEGRATION', 'runok-core' ),
                        'skill_percent' => '90',
                    ],
                    [
                        'skill_title' => __( 'BACKEND DEVELOPMENT', 'runok-core' ),
                        'skill_percent' => '90',
                    ],
                ],
                'title_field' => '{{{ skill_title }}}',
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
                'label' => __( 'Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .weta-team-details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
            'design_layout_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .weta-team-details' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
			'_style_title',
			[
				'label' => __( 'Title & Content', 'weta-core' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
            '_heading_name',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Name', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'name_bottom_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .team-details__content .name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__content .name' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'selector' => '{{WRAPPER}} .team-details__content .name',
            ]
        );

        $this->add_control(
            '_heading_position',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Position', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'position_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__content span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'position_typography',
                'selector' => '{{WRAPPER}} .team-details__content span',
            ]
        );

        $this->add_control(
            '_heading_about',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'About', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'about_bottom_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .team-details__content p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'about_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__content p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'about_typography',
                'selector' => '{{WRAPPER}} .team-details__content p',
            ]
        );

        $this->add_control(
            '_heading_info_label',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Info Label', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'info_label_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-list li span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'info_label_typography',
                'selector' => '{{WRAPPER}} .team-details__info-list li span',
            ]
        );

        $this->add_control(
            '_heading_info_text',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Info Text', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'info_text_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-list li' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .team-details__info-list li a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'info_text_color_hover',
            [
                'label' => __( 'Color (Hover)', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-list li a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'info_text_typography',
                'selector' => '{{WRAPPER}} .team-details__info-list li, .team-details__info-list li a',
            ]
        );

        $this->add_control(
            '_heading_follow',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Follow', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'follow_icon_size',
            [
                'label' => __( 'Icon Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-social a' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( '_tabs_follow' );
        
        $this->start_controls_tab(
            'follow_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'follow_icon_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-social a' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'follow_icon_background',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-social a' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'follow_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'follow_icon_color_hover',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-social a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'follow_icon_background_hover',
            [
                'label' => __( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info-social a:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_control(
            '_heading_style_author_layout',
            [
                'label' => esc_html__( 'Layout', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'author_layout_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'author_layout_border_layout',
            [
                'label' => esc_html__( 'Border Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details__info' => 'border-color: {{VALUE}}',
                ],
            ]
        );

		$this->end_controls_section();

        $this->start_controls_section(
            '_style_progressbar',
            [
                'label' => __( 'Progress Bar', 'runok-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            '_heading_style_progressbar_text',
            [
                'label' => esc_html__( 'Text', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'progressbar_bottom_spacing',
            [
                'label' => esc_html__( 'Bottom Spacing', 'text-domain' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .team-details-skill__progress-item-content' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'progressbar_text_color',
            [
                'label' => esc_html__( 'Color', 'runok-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details-skill__progress-item .span-title' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .team-details-skill__progress-item .left-side' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'progressbar_text_typography',
                'selector' => '{{WRAPPER}} .team-details-skill__progress-item .span-title, .team-details-skill__progress-item .left-side',
            ]
        );

        $this->add_control(
            '_heading_style_progressbar',
            [
                'label' => esc_html__( 'Bar', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'progressbar_fill',
            [
                'label' => esc_html__( 'Bar Fill', 'text-domain' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details-skill__progress-item .progress .progress-bar' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'progressbar_background',
            [
                'label' => esc_html__( 'Bar Background', 'text-domain' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .team-details-skill__progress-item .progress' => 'background-color: {{VALUE}}',
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
                $image = !empty($settings['image']['id']) ? wp_get_attachment_image_url( $settings['image']['id'], 'full' ) : $settings['image']['url'];
                $image_alt = get_post_meta($settings["image"]["id"], "_wp_attachment_image_alt", true);
            }

            if ( !empty($settings['weta_signature_image']['url']) ) {
                $weta_signature_image = !empty($settings['weta_signature_image']['id']) ? wp_get_attachment_image_url( $settings['weta_signature_image']['id'], 'full' ) : $settings['weta_signature_image']['url'];
                $weta_signature_image_alt = get_post_meta($settings["weta_signature_image"]["id"], "_wp_attachment_image_alt", true);
            }

		?>

            <section class="weta-team-details team-details section-space">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-5">
                            <div class="team-details__info mb-sm-30 mb-xs-25">
                                <div class="team-details__info-media">
                                    <?php if ( !empty ( $settings['image']['url'] ) ) : ?>  
                                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($image_alt); ?>" />
                                    <?php endif; ?>

                                    <?php if ($settings['show_profiles'] && is_array($settings['profiles'])) : ?>
                                        <div class="team-details__info-social">
                                            <?php
                                            foreach ($settings['profiles'] as $profile) :
                                                $icon = $profile['name'];
                                                $url = esc_url($profile['link']['url']);

                                                printf('<a target="_blank" rel="noopener" href="%s" class="elementor-repeater-item-%s"><i class="fab fa-%s" aria-hidden="true"></i></a>',
                                                    $url,
                                                    esc_attr($profile['_id']),
                                                    esc_attr($icon)
                                                );
                                            endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <ul class="team-details__info-list">
                                    <?php foreach ($settings['info_list'] as $item) : ?>
                                        <li>
                                            <span><?php print esc_html($item['label']); ?></span>
                                            <?php if ( $item['field_type']  == 'phone' ) : ?>
                                                <a href="tel:<?php print esc_url($item['phone']); ?>">
                                                    <?php print esc_html($item['phone']); ?>
                                                </a>
                                            <?php elseif ( $item['field_type']  == 'email' ) : ?>
                                                <a href="mailto:<?php print esc_attr($item['email']); ?>">
                                                    <?php print esc_html($item['email']); ?>
                                                </a>
                                            <?php elseif ( $item['field_type']  == 'website' ) : ?>
                                                <a href="<?php print esc_url($item['website']); ?>">
                                                    <?php print esc_html($item['website']); ?>
                                                </a>
                                            <?php elseif ( $item['field_type']  == 'text' ) : ?>
                                                <?php print esc_html($item['text']); ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-7">
                            <div class="team-details__content">
                                <?php if ( !empty($settings['name' ]) ) : ?>
                                    <h2 class="name">
                                        <?php echo rrdevs_kses( $settings['name'] ); ?>
                                    </h2>
                                <?php endif; ?>
                                <?php if ( !empty($settings['position' ]) ) : ?>
                                    <span class="d-block mb-15 mb-xs-10">
                                        <?php echo rrdevs_kses( $settings['position'] ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ( !empty($settings['about']) ) : ?>
                                    <p class="mb-30">
                                        <?php echo rrdevs_kses( $settings['about'] ); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="team-details-skill__progress-item__wrapper mb-minus-30">
                                    <?php foreach ($settings['runok_skill_list'] as $key => $item) : ?>
                                        <div class="team-details-skill__progress-item fix wow fadeIn animated mb-30">
                                            <div class="team-details-skill__progress-item-content d-flex justify-content-between mb-15">
                                                <?php if (!empty($item['skill_title'])): ?>
                                                    <span class="span-title">
                                                        <?php print esc_html(($item['skill_title'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="left-side">
                                                    <?php print esc_attr(($item['skill_percent'])); ?>%
                                                </span>
                                            </div>

                                            <div class="progress d-flex">
                                                <div class="progress-bar wow slideInLeft"
                                                    data-wow-delay="0s"
                                                    data-wow-duration=".8s"
                                                    role="progressbar"
                                                    data-width="<?php print esc_attr(($item['skill_percent'])); ?>%"
                                                    aria-valuenow="<?php print esc_attr(($item['skill_percent'])); ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php
	}

}

$widgets_manager->register( new WETA_Team_Details() );