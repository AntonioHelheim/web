<?php

namespace WETACore\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Border;
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
class WETA_Services extends Widget_Base {

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
        return 'weta_services';
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
        return __( 'Services', 'weta-core' );
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
        return [ 'weta-services' ];
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
            'weta_design_style',
            [
                'label' => esc_html__('Select Layout', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__('Layout 1', 'weta-core'),
                    'layout-2' => esc_html__('Layout 2', 'weta-core'),
                    'layout-3' => esc_html__('Layout 3', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->add_control(
            'weta_shape_switcher',
            [
                'label' => esc_html__( 'Shape Show', 'weta-core' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'weta-core' ),
                'label_off' => esc_html__( 'Hide', 'weta-core' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // weta_section_title
        $this->start_controls_section(
            'weta_section_title',
            [
                'label' => esc_html__('Title & Content', 'weta-core'),
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2'],
                ],

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
                    'weta_design_style' => ['layout-1', 'layout-3'],
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


        // Service lists for layout-1
        $this->start_controls_section(
            'weta_services_1',
            [
                'label' => esc_html__('Services List', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'weta_design_style' => ['layout-1'],
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'active_tab',
            [
                'label' => __('Is Active Tab?', 'runok-core'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'runok-core'),
                'label_off' => __('No', 'runok-core'),
                'return_value' => 'active',
                'default' => '',
            ]
        );

        $repeater->add_control(
            'template',
            [
                'label' => __('Section Template', 'tpcore'),
                'placeholder' => __('Select a section template for as tab content', 'tpcore'),
  
                'type' => Controls_Manager::SELECT2,
                'options' => get_elementor_templates()
            ]
        );

        $repeater->add_control(
			'weta_service_image_icon_switcher',
			[
				'label' => esc_html__( 'Image Type', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'image' => esc_html__( 'Image', 'weta-core' ),
					'icon' => esc_html__( 'Icon', 'weta-core' ),
                    'svg' => esc_html__( 'SVG Code', 'weta-core' ),
				],
			]
		);

        $repeater->add_control(
            'weta_service_icon_image',
            [
                'label' => esc_html__('Upload Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'weta_service_image_icon_switcher' => 'image',
                ],
            ]
        );

        $repeater->add_control(
            'weta_service_icon_svg',
            [
                'show_label' => false,
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'placeholder' => esc_html__('SVG Code Here', 'weta-core'),
                'condition' => [
                    'weta_service_image_icon_switcher' => 'svg'
                ]
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'weta_service_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'eicon-user-circle-o',
                    'condition' => [
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        } else {
            $repeater->add_control(
                'weta_service_selected_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'eicon-user-circle-o',
                        'library' => 'solid',
                    ],
                    'condition' => [
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        }

		$repeater->add_control(
			'weta_services_list_title',
			[
				'label' => esc_html__( 'Title', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'List Title' , 'weta-core' ),
				'label_block' => true,
			]
		);    
        
        $repeater->add_control(
            'service_title_heading',
            [
                'label' => esc_html__( 'Template', 'weta-core' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'after',
            ]
        );

        $this->add_control(
			'weta_service_lists',
			[
				'label' => esc_html__( 'Repeater List', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'weta_services_list_title' => esc_html__( 'Rapid Repair Garage', 'weta-core' ),
						'list_content' => esc_html__( 'Customer satisfaction is crucial for amohlodi business as it leads to customer', 'weta-core' ),
						'active_tab' => 'active',
					],
					[
						'weta_services_list_title' => esc_html__( 'Auto Tech Services', 'weta-core' ),
						'list_content' => esc_html__( 'Customer satisfaction is crucial for amohlodi business as it leads to customer', 'weta-core' ),
					],
					[
						'weta_services_list_title' => esc_html__( 'Roadside Auto Repair', 'weta-core' ),
						'list_content' => esc_html__( 'Customer satisfaction is crucial for amohlodi business as it leads to customer', 'weta-core' ),
					],
				],
				'title_field' => '{{{ weta_services_list_title }}}',
			]
		);

        $this->end_controls_section();

        // Service lists for layout-2
        $this->start_controls_section(
            'weta_services_2',
            [
                'label' => esc_html__('Services List', 'weta-core'),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'weta_design_style' => ['layout-2'],
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
			'weta_service_image_icon_switcher',
			[
				'label' => esc_html__( 'Image Type', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => esc_html__( 'Image', 'weta-core' ),
					'icon' => esc_html__( 'Icon', 'weta-core' ),
                    'svg' => esc_html__( 'SVG Code', 'weta-core' ),
				],
			]
		);

        $repeater->add_control(
            'weta_service_icon_image',
            [
                'label' => esc_html__('Upload Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'weta_service_image_icon_switcher' => 'image',
                ],
            ]
        );

        $repeater->add_control(
            'weta_service_icon_svg',
            [
                'show_label' => false,
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'placeholder' => esc_html__('SVG Code Here', 'weta-core'),
                'condition' => [
                    'weta_service_image_icon_switcher' => 'svg'
                ]
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $repeater->add_control(
                'weta_service_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'eicon-user-circle-o',
                    'condition' => [
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        } else {
            $repeater->add_control(
                'weta_service_selected_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'eicon-user-circle-o',
                        'library' => 'solid',
                    ],
                    'condition' => [
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        }

		$repeater->add_control(
			'weta_services_list_title',
			[
				'label' => esc_html__( 'Title', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'List Title' , 'weta-core' ),
				'label_block' => true,
			]
		);        

        $repeater->add_control(
            'weta_services_list_desc',
            [
                'label' => esc_html__( 'Description', 'weta-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Customer satisfaction is crucial for amohlodi business as it leads to customer' , 'weta-core' ),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
			'weta_button',
			[
				'label' => esc_html__( 'Button', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $repeater->add_control(
            'weta_services_btn_text',
            [
                'label' => esc_html__('Button Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Read More', 'weta-core'),
                'title' => esc_html__('Enter button text', 'weta-core'),
            ]
        );

        $repeater->add_control(
            'weta_services_link_type',
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

        $repeater->add_control(
            'weta_services_link',
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
                    'weta_services_link_type' => '1',
                ]
            ]
        );

        $repeater->add_control(
            'weta_services_page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_services_link_type' => '2',
                ]
            ]
        );

        $this->add_control(
			'weta_service_lists_2',
			[
				'label' => esc_html__( 'Repeater List', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'weta_services_list_title' => esc_html__( 'Digital Marketing', 'weta-core' ),
						'list_content' => esc_html__( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core' ),
					],
					[
						'weta_services_list_title' => esc_html__( 'Graphics Design', 'weta-core' ),
						'list_content' => esc_html__( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core' ),
					],
					[
						'weta_services_list_title' => esc_html__( 'Web Development', 'weta-core' ),
						'list_content' => esc_html__( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core' ),
					],
					[
						'weta_services_list_title' => esc_html__( 'Software Develop', 'weta-core' ),
						'list_content' => esc_html__( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core' ),
					],
					[
						'weta_services_list_title' => esc_html__( 'Social Media', 'weta-core' ),
						'list_content' => esc_html__( 'Ut imperdiet leo in arcu luctus non nunc moles suspends malasada dolon leo quits imperdiet sapien lobortis.', 'weta-core' ),
					],
				],
				'title_field' => '{{{ weta_services_list_title }}}',
			]
		);

        $this->end_controls_section();

        $this->start_controls_section(
            'service_list_3_content',
            [
                'label' => esc_html__( 'Service list',  'weta-core'  ),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'weta_design_style' => ['layout-3'],
                ],
            ]
        );
        
        $this->add_control(
			'weta_service_image_icon_switcher',
			[
				'label' => esc_html__( 'Image Type', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => esc_html__( 'Image', 'weta-core' ),
					'icon' => esc_html__( 'Icon', 'weta-core' ),
                    'svg' => esc_html__( 'SVG Code', 'weta-core' ),
				],
			]
		);

        $this->add_control(
            'weta_service_icon_image',
            [
                'label' => esc_html__('Upload Image', 'weta-core'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'weta_service_image_icon_switcher' => 'image',
                ],
            ]
        );

        $this->add_control(
            'weta_service_icon_svg',
            [
                'show_label' => false,
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'placeholder' => esc_html__('SVG Code Here', 'weta-core'),
                'condition' => [
                    'weta_service_image_icon_switcher' => 'svg'
                ]
            ]
        );

        if (weta_is_elementor_version('<', '2.6.0')) {
            $this->add_control(
                'weta_service_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICON,
                    'label_block' => true,
                    'default' => 'eicon-user-circle-o',
                    'condition' => [
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        } else {
            $this->add_control(
                'weta_service_selected_icon',
                [
                    'label' => esc_html__('Icon', 'weta-core'),
                    'type' => Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                    'label_block' => true,
                    'default' => [
                        'value' => 'eicon-user-circle-o',
                        'library' => 'solid',
                    ],
                    'condition' => [
                        'weta_service_image_icon_switcher' => 'icon',
                    ],
                ]
            );
        }

		$this->add_control(
			'weta_services_list_title',
			[
				'label' => esc_html__( 'Title', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'List Title' , 'weta-core' ),
				'label_block' => true,
			]
		);        

        $this->add_control(
            'weta_services_list_desc',
            [
                'label' => esc_html__( 'Description', 'weta-core' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Customer satisfaction is crucial for amohlodi business as it leads to customer' , 'weta-core' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
			'weta_button',
			[
				'label' => esc_html__( 'Button', 'weta-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_control(
            'weta_services_btn_text',
            [
                'label' => esc_html__('Button Text', 'weta-core'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Read More', 'weta-core'),
                'title' => esc_html__('Enter button text', 'weta-core'),
            ]
        );

        $this->add_control(
            'weta_services_link_type',
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
            'weta_services_link',
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
                    'weta_services_link_type' => '1',
                ]
            ]
        );

        $this->add_control(
            'weta_services_page_link',
            [
                'label' => esc_html__( 'Select Service Link Page', 'weta-core' ),
                'type' => Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => weta_get_all_pages(),
                'condition' => [
                    'weta_services_link_type' => '2',
                ]
            ]
        );
        
        $this->end_controls_section();
    

        // Design Style Control 
        $this->start_controls_section(
			'weta_layout_style',
			[
				'label' => __( 'Design Layout', 'weta-core' ),
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
                    '{{WRAPPER}} .services-provided.section-space' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .what-we-do.section-space-top' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .service-2__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'service_content_background',
            [
                'label' => __( 'Background Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .services-provided.section-space' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .what-we-do.section-space-top' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .service-2__item' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'service_content_background_hover',
                'types' => [ 'classic', 'gradient' ],
                'selectors' => [
                    '{{WRAPPER}} .service-2__item-circle:after' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-3'],
                ],
            ]
        );

        $this->add_responsive_control(
            'service_content_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'text-domain' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .service-2__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => ['layout-3'],
                ],
            ]
        );

        $this->end_controls_section();

        // style tab here
        $this->start_controls_section(
            '_section_style_content',
            [
                'label' => __( 'Title / Content', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'weta_design_style' => ['layout-1', 'layout-2'],
                ],
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
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sub_title_num_typography',
                'selector' => '{{WRAPPER}} .section__subtitle span, .section-3__subtitle span',
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
                    'weta_design_style' => ['layout-2'],
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

        $this->end_controls_section();

        $this->start_controls_section(
            'arrow_style',
            [
                'label' => esc_html__( 'Arrow',  'weta-core'  ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'weta_design_style' => [ 'layout-2' ],
                ],
            ]
        );
        
        $this->start_controls_tabs( 'arrow_tabs' );
        
        $this->start_controls_tab(
            'arrow_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'arrow_normal_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__slider-control button svg path' => 'stroke: {{VALUE}}',
                    '{{WRAPPER}} .what-we-do__scrollbar .swiper-scrollbar-drag' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'arrow_bg_normal_color',
            [
                'label' => esc_html__( 'Background Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__slider-control button' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'arrow_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'arrow_hover_color',
            [
                'label' => esc_html__( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__slider-control button:hover svg path' => 'stroke: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'arrow_bg_hover_color',
            [
                'label' => esc_html__( 'Background Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__slider-control button:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->end_controls_section();


        // TAB_STYLE
        $this->start_controls_section(
            'weta_services_list_style',
            [
                'label' => __( 'Services List', 'weta-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'service_box_content_padding',
            [
                'label' => __( 'Service Box Padding', 'weta-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            'service_box_content_background',
            [
                'label' => __( 'Service Box Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2' ],
                ],
            ]
        );

        $this->add_control(
            '_icon_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Icon', 'weta-core' ),
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __( 'Icon Size', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .nav-link span' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .nav-link img' => 'width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .service-2__item-icon .service-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .service-2__item-icon img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( 'service_icon_tabs' );
        
        $this->start_controls_tab(
            'service_icon_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );
        
        $this->add_control(
            'service_icon_icon_color_normal',
            [
                'label' => __( 'Icon Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .services-provided__tab li button span' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .services-provided__tab li button span svg path[stroke="#292D32"]' => 'stroke: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1', 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            'service_icon_bg_color_normal',
            [
                'label' => __( 'Icon BG Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .services-provided__tab li button span' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'service_icon_hover_tab',
            [
                'label' => esc_html__( 'Active', 'weta-core' ),
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );
        
        $this->add_control(
            'service_icon_icon_color_hover',
            [
                'label' => __( 'Icon Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .services-provided__tab li button.active span' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .services-provided__tab li button.active span svg path' => 'stroke: {{VALUE}} !important',
                ],
            ]
        );

        $this->add_control(
            'service_icon_bg_color_hover',
            [
                'label' => __( 'Icon BG Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .services-provided__tab li button.active span' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
					'unit' => 'px',
					'size' => 60,
				],
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .service-2__item-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            '_service_list_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            '_service_list_title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item h4' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .service-2__item h4' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->start_controls_tabs(
            '_service_list_style_tabs'
        );

        $this->start_controls_tab(
            '_service_list_style_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );

        $this->add_control(
            '_service_list_title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nav-tabs .nav-link' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .what-we-do__item h4' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .service-2__item h4' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            '_service_list_style_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
                'condition' => [
                    'weta_design_style' => [ 'layout-1' ],
                ],
            ]
        );

        $this->add_control(
            '_service_list_title_color_hover',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .nav-link:focus' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .nav-link:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => '_service_list_title_typography',
                'selector' => '{{WRAPPER}} .nav-tabs .nav-link, .what-we-do__item h4, .service-2__item h4',
            ]
        );


        // Service Description Style 
        $this->add_control(
            '_service_list_heading_desc',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Description', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            '_service_list_desc_color',
            [
                'label' => __( 'Description Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item p' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .service-2__item p' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => '_service_list_desc_typography',
                'selector' => '{{WRAPPER}} .what-we-do__item p, .service-2__item p',
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );


        // Service Button Style 
        $this->add_control(
            '_service_list_heading_btn',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Button', 'weta-core' ),
                'separator' => 'before',
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_responsive_control(
            '_service_list_btn_spacing',
            [
                'label' => __( 'Top Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item a' => 'margin-top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .service-2__item a' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_control(
            '_service_list_btn_color',
            [
                'label' => __( 'Button Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item a' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .what-we-do__item a svg path' => 'fill: {{VALUE}} !important',
                    '{{WRAPPER}} .service-2__item a' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .service-2__item a svg path' => 'stroke: {{VALUE}} !important',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );        

        $this->add_control(
            '_service_list_btn_hover_color',
            [
                'label' => __( 'Button Hover Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .what-we-do__item a:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .what-we-do__item a:hover svg path' => 'fill: {{VALUE}}',
                    '{{WRAPPER}} .service-2__item a:hover' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .service-2__item a:hover svg path' => 'stroke: {{VALUE}} !important',
                ],
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => '_service_list_btn_typography',
                'selector' => '{{WRAPPER}} .what-we-do__item a, .service-2__item a',
                'condition' => [
                    'weta_design_style' => [ 'layout-2', 'layout-3' ],
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
        $this->add_render_attribute('title_args', 'class', 'title section-title__title');

        ?>

        <?php if ( $settings['weta_design_style']  == 'layout-1' ): 
                $this->add_render_attribute('title_args', 'class', 'section__title text-uppercase wow fadeIn animated' );
                $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
            ?>

            <section class="services-provided section-space overflow-hidden parallax-element">
                <div class="container">
                    <?php if($settings['weta_shape_switcher']): ?>
                        <div class="services-provided__shape">
                            <div class="services-provided__shape-right" data-parallax='{"y": 50, "scale": 1.2, "smoothness": 15}'><div class="layer" data-depth="0.02"><div class="zooming-8"><img src="<?php echo get_template_directory_uri() ?>/assets/imgs/services-provided/shape.svg" alt="image not found"></div></div></div>
                            <div class="services-provided__shape-container">
                                <svg width="1920" height="1003" viewBox="0 0 1920 1003" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <mask id="mask0_1335_53" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="1920" height="1003">
                                        <rect width="1920" height="1003" fill="#D9D9D9"/>
                                    </mask>
                                    <g mask="url(#mask0_1335_53)">
                                        <g data-parallax='{"y": -100, "x":100 "scale": 1.5, "smoothness": 15}'><g class="layer" data-depth="-0.025"><g class="rightLeft">
                                        <g opacity="0.3">
                                            <path d="M810 758C810 759.657 808.657 761 807 761C805.343 761 804 759.657 804 758C804 756.343 805.343 755 807 755C808.657 755 810 756.343 810 758Z" fill="#7562E0"/>
                                            <path d="M828 758C828 759.657 826.657 761 825 761C823.343 761 822 759.657 822 758C822 756.343 823.343 755 825 755C826.657 755 828 756.343 828 758Z" fill="#7562E0"/>
                                            <path d="M846 758C846 759.657 844.657 761 843 761C841.343 761 840 759.657 840 758C840 756.343 841.343 755 843 755C844.657 755 846 756.343 846 758Z" fill="#7562E0"/>
                                            <path d="M864 758C864 759.657 862.657 761 861 761C859.343 761 858 759.657 858 758C858 756.343 859.343 755 861 755C862.657 755 864 756.343 864 758Z" fill="#7562E0"/>
                                            <path d="M882 758C882 759.657 880.657 761 879 761C877.343 761 876 759.657 876 758C876 756.343 877.343 755 879 755C880.657 755 882 756.343 882 758Z" fill="#7562E0"/>
                                            <path d="M900 758C900 759.657 898.657 761 897 761C895.343 761 894 759.657 894 758C894 756.343 895.343 755 897 755C898.657 755 900 756.343 900 758Z" fill="#7562E0"/>
                                            <path d="M918 758C918 759.657 916.657 761 915 761C913.343 761 912 759.657 912 758C912 756.343 913.343 755 915 755C916.657 755 918 756.343 918 758Z" fill="#7562E0"/>
                                            <path d="M936 758C936 759.657 934.657 761 933 761C931.343 761 930 759.657 930 758C930 756.343 931.343 755 933 755C934.657 755 936 756.343 936 758Z" fill="#7562E0"/>
                                            <path d="M954 758C954 759.657 952.657 761 951 761C949.343 761 948 759.657 948 758C948 756.343 949.343 755 951 755C952.657 755 954 756.343 954 758Z" fill="#7562E0"/>
                                            <path d="M972 758C972 759.657 970.657 761 969 761C967.343 761 966 759.657 966 758C966 756.343 967.343 755 969 755C970.657 755 972 756.343 972 758Z" fill="#7562E0"/>
                                            <path d="M990 758C990 759.657 988.657 761 987 761C985.343 761 984 759.657 984 758C984 756.343 985.343 755 987 755C988.657 755 990 756.343 990 758Z" fill="#7562E0"/>
                                            <path d="M1008 758C1008 759.657 1006.66 761 1005 761C1003.34 761 1002 759.657 1002 758C1002 756.343 1003.34 755 1005 755C1006.66 755 1008 756.343 1008 758Z" fill="#7562E0"/>
                                            <path d="M810 776C810 777.657 808.657 779 807 779C805.343 779 804 777.657 804 776C804 774.343 805.343 773 807 773C808.657 773 810 774.343 810 776Z" fill="#7562E0"/>
                                            <path d="M828 776C828 777.657 826.657 779 825 779C823.343 779 822 777.657 822 776C822 774.343 823.343 773 825 773C826.657 773 828 774.343 828 776Z" fill="#7562E0"/>
                                            <path d="M846 776C846 777.657 844.657 779 843 779C841.343 779 840 777.657 840 776C840 774.343 841.343 773 843 773C844.657 773 846 774.343 846 776Z" fill="#7562E0"/>
                                            <path d="M864 776C864 777.657 862.657 779 861 779C859.343 779 858 777.657 858 776C858 774.343 859.343 773 861 773C862.657 773 864 774.343 864 776Z" fill="#7562E0"/>
                                            <path d="M882 776C882 777.657 880.657 779 879 779C877.343 779 876 777.657 876 776C876 774.343 877.343 773 879 773C880.657 773 882 774.343 882 776Z" fill="#7562E0"/>
                                            <path d="M900 776C900 777.657 898.657 779 897 779C895.343 779 894 777.657 894 776C894 774.343 895.343 773 897 773C898.657 773 900 774.343 900 776Z" fill="#7562E0"/>
                                            <path d="M918 776C918 777.657 916.657 779 915 779C913.343 779 912 777.657 912 776C912 774.343 913.343 773 915 773C916.657 773 918 774.343 918 776Z" fill="#7562E0"/>
                                            <path d="M936 776C936 777.657 934.657 779 933 779C931.343 779 930 777.657 930 776C930 774.343 931.343 773 933 773C934.657 773 936 774.343 936 776Z" fill="#7562E0"/>
                                            <path d="M954 776C954 777.657 952.657 779 951 779C949.343 779 948 777.657 948 776C948 774.343 949.343 773 951 773C952.657 773 954 774.343 954 776Z" fill="#7562E0"/>
                                            <path d="M972 776C972 777.657 970.657 779 969 779C967.343 779 966 777.657 966 776C966 774.343 967.343 773 969 773C970.657 773 972 774.343 972 776Z" fill="#7562E0"/>
                                            <path d="M990 776C990 777.657 988.657 779 987 779C985.343 779 984 777.657 984 776C984 774.343 985.343 773 987 773C988.657 773 990 774.343 990 776Z" fill="#7562E0"/>
                                            <path d="M1008 776C1008 777.657 1006.66 779 1005 779C1003.34 779 1002 777.657 1002 776C1002 774.343 1003.34 773 1005 773C1006.66 773 1008 774.343 1008 776Z" fill="#7562E0"/>
                                            <path d="M810 794C810 795.657 808.657 797 807 797C805.343 797 804 795.657 804 794C804 792.343 805.343 791 807 791C808.657 791 810 792.343 810 794Z" fill="#7562E0"/>
                                            <path d="M828 794C828 795.657 826.657 797 825 797C823.343 797 822 795.657 822 794C822 792.343 823.343 791 825 791C826.657 791 828 792.343 828 794Z" fill="#7562E0"/>
                                            <path d="M846 794C846 795.657 844.657 797 843 797C841.343 797 840 795.657 840 794C840 792.343 841.343 791 843 791C844.657 791 846 792.343 846 794Z" fill="#7562E0"/>
                                            <path d="M864 794C864 795.657 862.657 797 861 797C859.343 797 858 795.657 858 794C858 792.343 859.343 791 861 791C862.657 791 864 792.343 864 794Z" fill="#7562E0"/>
                                            <path d="M882 794C882 795.657 880.657 797 879 797C877.343 797 876 795.657 876 794C876 792.343 877.343 791 879 791C880.657 791 882 792.343 882 794Z" fill="#7562E0"/>
                                            <path d="M900 794C900 795.657 898.657 797 897 797C895.343 797 894 795.657 894 794C894 792.343 895.343 791 897 791C898.657 791 900 792.343 900 794Z" fill="#7562E0"/>
                                            <path d="M918 794C918 795.657 916.657 797 915 797C913.343 797 912 795.657 912 794C912 792.343 913.343 791 915 791C916.657 791 918 792.343 918 794Z" fill="#7562E0"/>
                                            <path d="M936 794C936 795.657 934.657 797 933 797C931.343 797 930 795.657 930 794C930 792.343 931.343 791 933 791C934.657 791 936 792.343 936 794Z" fill="#7562E0"/>
                                            <path d="M954 794C954 795.657 952.657 797 951 797C949.343 797 948 795.657 948 794C948 792.343 949.343 791 951 791C952.657 791 954 792.343 954 794Z" fill="#7562E0"/>
                                            <path d="M972 794C972 795.657 970.657 797 969 797C967.343 797 966 795.657 966 794C966 792.343 967.343 791 969 791C970.657 791 972 792.343 972 794Z" fill="#7562E0"/>
                                            <path d="M990 794C990 795.657 988.657 797 987 797C985.343 797 984 795.657 984 794C984 792.343 985.343 791 987 791C988.657 791 990 792.343 990 794Z" fill="#7562E0"/>
                                            <path d="M1008 794C1008 795.657 1006.66 797 1005 797C1003.34 797 1002 795.657 1002 794C1002 792.343 1003.34 791 1005 791C1006.66 791 1008 792.343 1008 794Z" fill="#7562E0"/>
                                            <path d="M810 812C810 813.657 808.657 815 807 815C805.343 815 804 813.657 804 812C804 810.343 805.343 809 807 809C808.657 809 810 810.343 810 812Z" fill="#7562E0"/>
                                            <path d="M828 812C828 813.657 826.657 815 825 815C823.343 815 822 813.657 822 812C822 810.343 823.343 809 825 809C826.657 809 828 810.343 828 812Z" fill="#7562E0"/>
                                            <path d="M846 812C846 813.657 844.657 815 843 815C841.343 815 840 813.657 840 812C840 810.343 841.343 809 843 809C844.657 809 846 810.343 846 812Z" fill="#7562E0"/>
                                            <path d="M864 812C864 813.657 862.657 815 861 815C859.343 815 858 813.657 858 812C858 810.343 859.343 809 861 809C862.657 809 864 810.343 864 812Z" fill="#7562E0"/>
                                            <path d="M882 812C882 813.657 880.657 815 879 815C877.343 815 876 813.657 876 812C876 810.343 877.343 809 879 809C880.657 809 882 810.343 882 812Z" fill="#7562E0"/>
                                            <path d="M900 812C900 813.657 898.657 815 897 815C895.343 815 894 813.657 894 812C894 810.343 895.343 809 897 809C898.657 809 900 810.343 900 812Z" fill="#7562E0"/>
                                            <path d="M918 812C918 813.657 916.657 815 915 815C913.343 815 912 813.657 912 812C912 810.343 913.343 809 915 809C916.657 809 918 810.343 918 812Z" fill="#7562E0"/>
                                            <path d="M936 812C936 813.657 934.657 815 933 815C931.343 815 930 813.657 930 812C930 810.343 931.343 809 933 809C934.657 809 936 810.343 936 812Z" fill="#7562E0"/>
                                            <path d="M954 812C954 813.657 952.657 815 951 815C949.343 815 948 813.657 948 812C948 810.343 949.343 809 951 809C952.657 809 954 810.343 954 812Z" fill="#7562E0"/>
                                            <path d="M972 812C972 813.657 970.657 815 969 815C967.343 815 966 813.657 966 812C966 810.343 967.343 809 969 809C970.657 809 972 810.343 972 812Z" fill="#7562E0"/>
                                            <path d="M990 812C990 813.657 988.657 815 987 815C985.343 815 984 813.657 984 812C984 810.343 985.343 809 987 809C988.657 809 990 810.343 990 812Z" fill="#7562E0"/>
                                            <path d="M1008 812C1008 813.657 1006.66 815 1005 815C1003.34 815 1002 813.657 1002 812C1002 810.343 1003.34 809 1005 809C1006.66 809 1008 810.343 1008 812Z" fill="#7562E0"/>
                                            <path d="M810 830C810 831.657 808.657 833 807 833C805.343 833 804 831.657 804 830C804 828.343 805.343 827 807 827C808.657 827 810 828.343 810 830Z" fill="#7562E0"/>
                                            <path d="M828 830C828 831.657 826.657 833 825 833C823.343 833 822 831.657 822 830C822 828.343 823.343 827 825 827C826.657 827 828 828.343 828 830Z" fill="#7562E0"/>
                                            <path d="M846 830C846 831.657 844.657 833 843 833C841.343 833 840 831.657 840 830C840 828.343 841.343 827 843 827C844.657 827 846 828.343 846 830Z" fill="#7562E0"/>
                                            <path d="M864 830C864 831.657 862.657 833 861 833C859.343 833 858 831.657 858 830C858 828.343 859.343 827 861 827C862.657 827 864 828.343 864 830Z" fill="#7562E0"/>
                                            <path d="M882 830C882 831.657 880.657 833 879 833C877.343 833 876 831.657 876 830C876 828.343 877.343 827 879 827C880.657 827 882 828.343 882 830Z" fill="#7562E0"/>
                                            <path d="M900 830C900 831.657 898.657 833 897 833C895.343 833 894 831.657 894 830C894 828.343 895.343 827 897 827C898.657 827 900 828.343 900 830Z" fill="#7562E0"/>
                                            <path d="M918 830C918 831.657 916.657 833 915 833C913.343 833 912 831.657 912 830C912 828.343 913.343 827 915 827C916.657 827 918 828.343 918 830Z" fill="#7562E0"/>
                                            <path d="M936 830C936 831.657 934.657 833 933 833C931.343 833 930 831.657 930 830C930 828.343 931.343 827 933 827C934.657 827 936 828.343 936 830Z" fill="#7562E0"/>
                                            <path d="M954 830C954 831.657 952.657 833 951 833C949.343 833 948 831.657 948 830C948 828.343 949.343 827 951 827C952.657 827 954 828.343 954 830Z" fill="#7562E0"/>
                                            <path d="M972 830C972 831.657 970.657 833 969 833C967.343 833 966 831.657 966 830C966 828.343 967.343 827 969 827C970.657 827 972 828.343 972 830Z" fill="#7562E0"/>
                                            <path d="M990 830C990 831.657 988.657 833 987 833C985.343 833 984 831.657 984 830C984 828.343 985.343 827 987 827C988.657 827 990 828.343 990 830Z" fill="#7562E0"/>
                                            <path d="M1008 830C1008 831.657 1006.66 833 1005 833C1003.34 833 1002 831.657 1002 830C1002 828.343 1003.34 827 1005 827C1006.66 827 1008 828.343 1008 830Z" fill="#7562E0"/>
                                            <path d="M810 848C810 849.657 808.657 851 807 851C805.343 851 804 849.657 804 848C804 846.343 805.343 845 807 845C808.657 845 810 846.343 810 848Z" fill="#7562E0"/>
                                            <path d="M828 848C828 849.657 826.657 851 825 851C823.343 851 822 849.657 822 848C822 846.343 823.343 845 825 845C826.657 845 828 846.343 828 848Z" fill="#7562E0"/>
                                            <path d="M846 848C846 849.657 844.657 851 843 851C841.343 851 840 849.657 840 848C840 846.343 841.343 845 843 845C844.657 845 846 846.343 846 848Z" fill="#7562E0"/>
                                            <path d="M864 848C864 849.657 862.657 851 861 851C859.343 851 858 849.657 858 848C858 846.343 859.343 845 861 845C862.657 845 864 846.343 864 848Z" fill="#7562E0"/>
                                            <path d="M882 848C882 849.657 880.657 851 879 851C877.343 851 876 849.657 876 848C876 846.343 877.343 845 879 845C880.657 845 882 846.343 882 848Z" fill="#7562E0"/>
                                            <path d="M900 848C900 849.657 898.657 851 897 851C895.343 851 894 849.657 894 848C894 846.343 895.343 845 897 845C898.657 845 900 846.343 900 848Z" fill="#7562E0"/>
                                            <path d="M918 848C918 849.657 916.657 851 915 851C913.343 851 912 849.657 912 848C912 846.343 913.343 845 915 845C916.657 845 918 846.343 918 848Z" fill="#7562E0"/>
                                            <path d="M936 848C936 849.657 934.657 851 933 851C931.343 851 930 849.657 930 848C930 846.343 931.343 845 933 845C934.657 845 936 846.343 936 848Z" fill="#7562E0"/>
                                            <path d="M954 848C954 849.657 952.657 851 951 851C949.343 851 948 849.657 948 848C948 846.343 949.343 845 951 845C952.657 845 954 846.343 954 848Z" fill="#7562E0"/>
                                            <path d="M972 848C972 849.657 970.657 851 969 851C967.343 851 966 849.657 966 848C966 846.343 967.343 845 969 845C970.657 845 972 846.343 972 848Z" fill="#7562E0"/>
                                            <path d="M990 848C990 849.657 988.657 851 987 851C985.343 851 984 849.657 984 848C984 846.343 985.343 845 987 845C988.657 845 990 846.343 990 848Z" fill="#7562E0"/>
                                            <path d="M1008 848C1008 849.657 1006.66 851 1005 851C1003.34 851 1002 849.657 1002 848C1002 846.343 1003.34 845 1005 845C1006.66 845 1008 846.343 1008 848Z" fill="#7562E0"/>
                                            <path d="M810 866C810 867.657 808.657 869 807 869C805.343 869 804 867.657 804 866C804 864.343 805.343 863 807 863C808.657 863 810 864.343 810 866Z" fill="#7562E0"/>
                                            <path d="M828 866C828 867.657 826.657 869 825 869C823.343 869 822 867.657 822 866C822 864.343 823.343 863 825 863C826.657 863 828 864.343 828 866Z" fill="#7562E0"/>
                                            <path d="M846 866C846 867.657 844.657 869 843 869C841.343 869 840 867.657 840 866C840 864.343 841.343 863 843 863C844.657 863 846 864.343 846 866Z" fill="#7562E0"/>
                                            <path d="M864 866C864 867.657 862.657 869 861 869C859.343 869 858 867.657 858 866C858 864.343 859.343 863 861 863C862.657 863 864 864.343 864 866Z" fill="#7562E0"/>
                                            <path d="M882 866C882 867.657 880.657 869 879 869C877.343 869 876 867.657 876 866C876 864.343 877.343 863 879 863C880.657 863 882 864.343 882 866Z" fill="#7562E0"/>
                                            <path d="M900 866C900 867.657 898.657 869 897 869C895.343 869 894 867.657 894 866C894 864.343 895.343 863 897 863C898.657 863 900 864.343 900 866Z" fill="#7562E0"/>
                                            <path d="M918 866C918 867.657 916.657 869 915 869C913.343 869 912 867.657 912 866C912 864.343 913.343 863 915 863C916.657 863 918 864.343 918 866Z" fill="#7562E0"/>
                                            <path d="M936 866C936 867.657 934.657 869 933 869C931.343 869 930 867.657 930 866C930 864.343 931.343 863 933 863C934.657 863 936 864.343 936 866Z" fill="#7562E0"/>
                                            <path d="M954 866C954 867.657 952.657 869 951 869C949.343 869 948 867.657 948 866C948 864.343 949.343 863 951 863C952.657 863 954 864.343 954 866Z" fill="#7562E0"/>
                                            <path d="M972 866C972 867.657 970.657 869 969 869C967.343 869 966 867.657 966 866C966 864.343 967.343 863 969 863C970.657 863 972 864.343 972 866Z" fill="#7562E0"/>
                                            <path d="M990 866C990 867.657 988.657 869 987 869C985.343 869 984 867.657 984 866C984 864.343 985.343 863 987 863C988.657 863 990 864.343 990 866Z" fill="#7562E0"/>
                                            <path d="M1008 866C1008 867.657 1006.66 869 1005 869C1003.34 869 1002 867.657 1002 866C1002 864.343 1003.34 863 1005 863C1006.66 863 1008 864.343 1008 866Z" fill="#7562E0"/>
                                            <path d="M810 884C810 885.657 808.657 887 807 887C805.343 887 804 885.657 804 884C804 882.343 805.343 881 807 881C808.657 881 810 882.343 810 884Z" fill="#7562E0"/>
                                            <path d="M828 884C828 885.657 826.657 887 825 887C823.343 887 822 885.657 822 884C822 882.343 823.343 881 825 881C826.657 881 828 882.343 828 884Z" fill="#7562E0"/>
                                            <path d="M846 884C846 885.657 844.657 887 843 887C841.343 887 840 885.657 840 884C840 882.343 841.343 881 843 881C844.657 881 846 882.343 846 884Z" fill="#7562E0"/>
                                            <path d="M864 884C864 885.657 862.657 887 861 887C859.343 887 858 885.657 858 884C858 882.343 859.343 881 861 881C862.657 881 864 882.343 864 884Z" fill="#7562E0"/>
                                            <path d="M882 884C882 885.657 880.657 887 879 887C877.343 887 876 885.657 876 884C876 882.343 877.343 881 879 881C880.657 881 882 882.343 882 884Z" fill="#7562E0"/>
                                            <path d="M900 884C900 885.657 898.657 887 897 887C895.343 887 894 885.657 894 884C894 882.343 895.343 881 897 881C898.657 881 900 882.343 900 884Z" fill="#7562E0"/>
                                            <path d="M918 884C918 885.657 916.657 887 915 887C913.343 887 912 885.657 912 884C912 882.343 913.343 881 915 881C916.657 881 918 882.343 918 884Z" fill="#7562E0"/>
                                            <path d="M936 884C936 885.657 934.657 887 933 887C931.343 887 930 885.657 930 884C930 882.343 931.343 881 933 881C934.657 881 936 882.343 936 884Z" fill="#7562E0"/>
                                            <path d="M954 884C954 885.657 952.657 887 951 887C949.343 887 948 885.657 948 884C948 882.343 949.343 881 951 881C952.657 881 954 882.343 954 884Z" fill="#7562E0"/>
                                            <path d="M972 884C972 885.657 970.657 887 969 887C967.343 887 966 885.657 966 884C966 882.343 967.343 881 969 881C970.657 881 972 882.343 972 884Z" fill="#7562E0"/>
                                            <path d="M990 884C990 885.657 988.657 887 987 887C985.343 887 984 885.657 984 884C984 882.343 985.343 881 987 881C988.657 881 990 882.343 990 884Z" fill="#7562E0"/>
                                            <path d="M1008 884C1008 885.657 1006.66 887 1005 887C1003.34 887 1002 885.657 1002 884C1002 882.343 1003.34 881 1005 881C1006.66 881 1008 882.343 1008 884Z" fill="#7562E0"/>
                                        </g>
                                        </g></g></g>
                                        <g class="layer" data-depth="0.009">
                                        <g class="leftRight">
                                        <path d="M1568 764.884C1575.74 771.044 1583.12 770.225 1588.5 769.633C1593.33 769.101 1595.6 768.962 1598.67 771.402C1601.74 773.842 1602.11 776.064 1602.63 780.865C1603.22 786.207 1604.03 793.526 1611.77 799.687C1619.52 805.848 1626.89 805.029 1632.28 804.436C1637.11 803.905 1639.37 803.765 1642.45 806.205C1645.51 808.645 1645.88 810.867 1646.41 815.66C1646.99 821.002 1647.8 828.321 1655.55 834.482C1663.29 840.643 1670.67 839.824 1676.05 839.231C1680.88 838.7 1683.15 838.56 1686.22 841L1695 830.116C1687.26 823.956 1679.88 824.775 1674.5 825.367C1669.67 825.899 1667.4 826.038 1664.33 823.598C1661.26 821.158 1660.89 818.936 1660.37 814.144C1659.78 808.802 1658.97 801.482 1651.23 795.322C1643.48 789.161 1636.11 789.98 1630.72 790.572C1625.89 791.104 1623.63 791.243 1620.55 788.803C1617.48 786.364 1617.12 784.142 1616.59 779.34C1616.01 773.999 1615.2 766.679 1607.45 760.518C1599.71 754.357 1592.33 755.176 1586.95 755.769C1582.12 756.3 1579.85 756.44 1576.78 754L1568 764.884Z" fill="#FEEAA6" fill-opacity="0.4"/>
                                        </g>
                                        <g class="rightLeft">
                                            <path d="M1577.35 753.444C1574.95 756.449 1575.4 760.99 1578.97 762.433C1584.4 764.628 1589.41 764.069 1593.34 763.633C1598.13 763.101 1600.38 762.962 1603.43 765.402C1606.47 767.842 1606.84 770.064 1607.36 774.865C1607.94 780.207 1608.74 787.527 1616.43 793.687C1624.11 799.848 1631.43 799.029 1636.77 798.436C1641.56 797.905 1643.81 797.765 1646.86 800.205C1649.9 802.645 1650.27 804.867 1650.79 809.66C1651.37 815.002 1652.17 822.321 1659.86 828.482C1667.54 834.643 1674.86 833.824 1680.2 833.231C1681.59 833.076 1682.77 832.955 1683.82 832.921C1687.67 832.794 1692.24 832.563 1694.65 829.556C1697.05 826.551 1696.6 822.01 1693.03 820.567C1687.6 818.372 1682.59 818.931 1678.66 819.367C1673.87 819.899 1671.62 820.038 1668.57 817.598C1665.53 815.158 1665.16 812.936 1664.64 808.144C1664.06 802.802 1663.26 795.482 1655.57 789.322C1647.89 783.161 1640.57 783.98 1635.23 784.572C1630.44 785.104 1628.19 785.243 1625.14 782.804C1622.09 780.364 1621.73 778.142 1621.21 773.34C1620.63 767.999 1619.83 760.679 1612.14 754.518C1604.46 748.357 1597.14 749.176 1591.8 749.769C1590.41 749.924 1589.23 750.045 1588.18 750.08C1584.33 750.206 1579.76 750.437 1577.35 753.444Z" fill="url(#paint0_linear_1335_53)" fill-opacity="0.5"/>
                                        </g>
                                        </g>
                                        <g data-parallax='{"y": -100, "x":100 "scale": 1.5, "smoothness": 15}'><g class="layer" data-depth="0.02"><g class="leftRight">
                                        <path d="M831 428C831 431.314 828.314 434 825 434C821.686 434 819 431.314 819 428C819 424.686 821.686 422 825 422C828.314 422 831 424.686 831 428Z" fill="#FBD95E"/>
                                        </g></g></g>
                                        <g data-parallax='{"y": -100, "x":100 "scale": 1.5, "smoothness": 15}'><g class="layer" data-depth="0.002"><g class="leftRight">
                                        <path d="M873 454C873 456.209 871.209 458 869 458C866.791 458 865 456.209 865 454C865 451.791 866.791 450 869 450C871.209 450 873 451.791 873 454Z" fill="#5489FA"/>
                                        </g></g></g>
                                        <g data-parallax='{"y": -100, "x":100 "scale": 1.5, "smoothness": 15}'><g class="layer" data-depth="0.002"><g class="leftRight">
                                        <path d="M839 473C839 474.105 838.105 475 837 475C835.895 475 835 474.105 835 473C835 471.895 835.895 471 837 471C838.105 471 839 471.895 839 473Z" fill="#F461A6"/>
                                        </g></g></g>

                                        <g class="layer" data-depth="0.009">
                                            <g class="leftRight">
                                            <path d="M219.9 261.07C211.08 268.14 202.68 267.2 196.55 266.52C191.05 265.91 188.47 265.75 184.97 268.55C181.48 271.35 181.06 273.9 180.46 279.41C179.79 285.54 178.87 293.94 170.05 301.01C161.23 308.08 152.83 307.14 146.7 306.46C141.2 305.85 138.62 305.69 135.12 308.49C131.63 311.29 131.21 313.84 130.61 319.34C129.94 325.47 129.02 333.87 120.2 340.94C111.38 348.01 102.98 347.07 96.85 346.39C91.35 345.78 88.77 345.62 85.27 348.42L75.27 335.93C84.09 328.86 92.49 329.8 98.62 330.48C104.12 331.09 106.7 331.25 110.2 328.45C113.69 325.65 114.11 323.1 114.71 317.6C115.38 311.47 116.3 303.07 125.12 296C133.94 288.93 142.34 289.87 148.47 290.55C153.97 291.16 156.55 291.32 160.05 288.52C163.55 285.72 163.96 283.17 164.56 277.66C165.23 271.53 166.15 263.13 174.97 256.06C183.79 248.99 192.19 249.93 198.32 250.61C203.82 251.22 206.4 251.38 209.9 248.58L219.9 261.07Z" fill="#EFECFF"/>
                                            </g>
                                            <g class="rightLeft">
                                            <path d="M209.63 248.246C212.392 251.695 211.873 256.908 207.776 258.563C201.539 261.082 195.794 260.441 191.28 259.94C185.78 259.33 183.2 259.17 179.7 261.97C176.21 264.77 175.79 267.32 175.19 272.83C174.52 278.96 173.6 287.36 164.78 294.43C155.96 301.5 147.56 300.56 141.43 299.88C135.93 299.27 133.35 299.11 129.85 301.91C126.36 304.71 125.94 307.26 125.34 312.76C124.67 318.89 123.75 327.29 114.93 334.36C106.11 341.43 97.71 340.49 91.58 339.81C89.9781 339.632 88.6238 339.493 87.4224 339.454C83.0056 339.309 77.7615 339.044 74.9995 335.594C72.2379 332.145 72.7574 326.932 76.8544 325.277C83.0914 322.758 88.8361 323.399 93.35 323.9C98.85 324.51 101.43 324.67 104.93 321.87C108.42 319.07 108.84 316.52 109.44 311.02C110.11 304.89 111.03 296.49 119.85 289.42C128.67 282.35 137.07 283.29 143.2 283.97C148.7 284.58 151.28 284.74 154.78 281.94C158.28 279.14 158.69 276.59 159.29 271.08C159.96 264.95 160.88 256.55 169.7 249.48C178.52 242.41 186.92 243.35 193.05 244.03C194.652 244.208 196.006 244.347 197.208 244.387C201.624 244.531 206.868 244.796 209.63 248.246Z" fill="url(#paint1_linear_1335_53)" fill-opacity="0.5"/>
                                            </g>
                                        </g>
                                    </g>
                                    <defs>
                                        <linearGradient id="paint0_linear_1335_53" x1="1636" y1="748" x2="1636" y2="835" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#FFE176" offset="0%"/>
                                            <stop offset="1" stop-color="#FFD646"/>
                                        </linearGradient>
                                        <linearGradient id="paint1_linear_1335_53" x1="142.315" y1="242" x2="142.315" y2="341.84" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#9888F4" offset="0%"/>
                                            <stop offset="1" stop-color="#907EF3"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="section__title-wrapper services-provided__content text-center mb-60 mb-sm-50 mb-xs-40 parallax-element">
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
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-lg-4">
                            <ul class="nav nav-tabs services-provided__tab wow fadeIn animated" data-wow-delay=".5s" id="myTab" role="tablist">
                                <?php 
                                    if($settings['weta_service_lists']):
                                        foreach ($settings['weta_service_lists'] as $key => $tab):
                                            $active = $tab['active_tab'] ? 'active' : NULL ; 
                                ?>
                                    <li class="nav-item" role="presentation">
                                        <button 
                                            class="nav-link <?php echo esc_attr($active); ?>" 
                                            id="home-<?php echo esc_attr($key); ?>-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#home-<?php echo esc_attr($key); ?>" 
                                            type="button" role="tab" 
                                            aria-controls="home-<?php echo esc_attr($key); ?>"
                                            aria-selected="false" >
                                                <!-- Service Icon Start -->
                                                <?php if (!empty($tab['weta_service_icon_image']['url'])): ?>
                                                        <span><img src="<?php echo $tab['weta_service_icon_image']['url']; ?>" alt="<?php echo get_post_meta(attachment_url_to_postid($tab['weta_service_icon_image']['url']), '_wp_attachment_image_alt', true); ?>"></span>
                                                <?php endif; ?>
                                                <!-- Service Icon  -->
                                                <?php if (!empty($tab['weta_service_icon']) || !empty($tab['weta_service_selected_icon']['value'])) : ?>
                                                        <span><?php weta_render_icon($tab, 'weta_service_icon', 'weta_service_selected_icon'); ?></span>
                                                <?php endif; ?>
                                                <!-- Service SVG  -->
                                                <?php if (!empty($tab['weta_service_icon_svg'])): ?>
                                                        <span><?php echo $tab['weta_service_icon_svg']; ?></span>
                                                <?php endif; ?>
                                                <!-- Service Icon End -->
                                                
                                            <?php if($tab['weta_services_list_title']): ?>
                                                <?php echo esc_html($tab['weta_services_list_title']); ?>
                                            <?php endif; ?>
                                            </button>
                                    </li>
                                <?php endforeach; endif; ?>
                            </ul>
                        </div>
                        <div class="col-lg-8">
                            <div class="tab-content wow fadeIn animated" data-wow-delay=".7s" id="myTabContent">
                                <?php 
                                    if($settings['weta_service_lists']):
                                        foreach ($settings['weta_service_lists'] as $key => $tab):
                                            $active = $tab['active_tab'] ? 'show active' : '';
                                ?>
                                    <div class="tab-pane fade <?php echo esc_attr($active); ?>" id="home-<?php echo esc_attr($key); ?>" role="tabpanel" aria-labelledby="home-<?php echo esc_attr($key); ?>-tab">
                                        <div class="services-provided__tab-content">
                                            <div class="services-provided__tab-content-media">
                                                <?php echo \Elementor\Plugin::instance()->frontend->get_builder_content($tab['template'], true); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                <?php endforeach; endif; ?>

                                
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif ( $settings['weta_design_style']  == 'layout-2' ): 
            $this->add_render_attribute('title_args', 'class', 'section-2__title xl text-uppercase section-title-2-animation' );
            $this->add_render_attribute('title_args', 'data-wow-delay', '.3s');
            ?>

        <section class="what-we-do section-space-top overflow-hidden">
            <div class="container container-xxl">
                <div class="row">
                    <div class="col-12">
                        <div class="what-we-do__content d-flex flex-column justify-content-sm-between align-items-sm-end flex-sm-row  mb-40 mb-sm-30 mb-xs-50">
                            <div class="section-2__title-wrapper">
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

                            <div class="what-we-do__slider-control d-flex align-items-center">
                                <button class="what-we-do__slider-arrow-prev section-subTile-2-animation">
                                    <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.07007 1L1.00007 7.07L7.07007 13.14" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16 7.07031L1 7.07031" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button class="what-we-do__slider-arrow-next section-subTile-2-animation">
                                    <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.92993 1L15.9999 7.07L9.92993 13.14" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M1 7.07031L16 7.07031" stroke="#010915" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="swiper what-we-do__active">
                            <div class="swiper-wrapper">
                                <?php 
                                    if($settings['weta_service_lists_2']):
                                        foreach ($settings['weta_service_lists_2'] as $key => $tab):

                                        // Link
                                        if ('2' == $tab['weta_services_link_type']) {
                                            $link = get_permalink($tab['weta_services_page_link']);
                                            $target = '_self';
                                            $rel = 'nofollow';
                                        } else {
                                            $link = !empty($tab['weta_services_link']['url']) ? $tab['weta_services_link']['url'] : '';
                                            $target = !empty($tab['weta_services_link']['is_external']) ? '_blank' : '';
                                            $rel = !empty($tab['weta_services_link']['nofollow']) ? 'nofollow' : '';
                                        }
                                ?>
                                    <div class="swiper-slide">
                                        <div class="what-we-do__item theme-bg-2 what-we-do-image-animation">
                                            <div class="what-we-do__item-icon mb-45 mb-sm-40 mb-xs-35">
                                                <!-- Service Icon Start -->
                                                <?php if (!empty($tab['weta_service_icon_image']['url'])): ?>
                                                    <div class="service-icon">
                                                        <img src="<?php echo $tab['weta_service_icon_image']['url']; ?>" alt="<?php echo get_post_meta(attachment_url_to_postid($tab['weta_service_icon_image']['url']), '_wp_attachment_image_alt', true); ?>">
                                                    </div>
                                                <?php endif; ?>
                                                <!-- Service Icon  -->
                                                <?php if (!empty($tab['weta_service_icon']) || !empty($tab['weta_service_selected_icon']['value'])) : ?>
                                                    <div class="service-icon">
                                                        <?php weta_render_icon($tab, 'weta_service_icon', 'weta_service_selected_icon'); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <!-- Service SVG  -->
                                                <?php if (!empty($tab['weta_service_icon_svg'])): ?>
                                                    <div class="service-icon">
                                                        <?php echo $tab['weta_service_icon_svg']; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <!-- Service Icon End -->
                                            </div>
                                            <?php if($tab['weta_services_list_title']): ?>
                                                <h4 class="mb-10"><?php echo esc_html($tab['weta_services_list_title']); ?></h4>
                                            <?php endif; ?>
                                            <?php if($tab['weta_services_list_desc']): ?>
                                                <p class="mb-0"><?php echo esc_html($tab['weta_services_list_desc']); ?></p>
                                            <?php endif; ?>

                                            <?php if(!empty($tab['weta_services_btn_text'])) : ?>
                                                <a href="<?php echo esc_url($link); ?>" class="mt-60 mt-sm-50 mt-xs-45 d-inline-block"><?php echo rrdevs_kses($tab['weta_services_btn_text']); ?> <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.648661 10.3807C0.487945 10.22 0.393974 10.0057 0.387425 9.78495C0.380877 9.56421 0.462285 9.35512 0.61374 9.20366L7.23005 2.58735L2.17704 2.43861C2.0676 2.43537 1.9586 2.4106 1.85625 2.36572C1.7539 2.32084 1.66021 2.25673 1.58053 2.17705C1.50086 2.09737 1.43674 2.00368 1.39187 1.90133C1.34699 1.79898 1.32222 1.68998 1.31897 1.58054C1.31572 1.4711 1.33406 1.36338 1.37294 1.26351C1.41182 1.16365 1.47048 1.0736 1.54557 0.998518C1.62066 0.92343 1.7107 0.864771 1.81057 0.825891C1.91043 0.787011 2.01815 0.768672 2.12759 0.771919L9.18985 0.981446C9.29932 0.984556 9.40839 1.00923 9.5108 1.05407C9.61321 1.0989 9.70695 1.163 9.78666 1.24271C9.86636 1.32242 9.93047 1.41616 9.9753 1.51857C10.0201 1.62098 10.0448 1.73005 10.0479 1.83952L10.2574 8.90178C10.264 9.12279 10.1825 9.33215 10.0308 9.4838C9.8792 9.63544 9.66984 9.71695 9.44883 9.7104C9.22781 9.70384 9.01324 9.60975 8.85232 9.44883C8.6914 9.28791 8.59731 9.07334 8.59076 8.85233L8.44202 3.79932L1.8257 10.4156C1.67425 10.5671 1.46515 10.6485 1.24441 10.6419C1.02368 10.6354 0.809378 10.5414 0.648661 10.3807Z" fill="#010915"/>
                                                </svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                        <div class="what-we-do__scrollbar"></div>
                    </div>
                </div>
            </div>
        </section>

        <?php elseif ( $settings['weta_design_style']  == 'layout-3' ): ?>

            <div class="service-2__item wow fadeIn animated" data-wow-delay=".5s">
                <div class="service-2__item-circle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="service-2__item-icon mb-35 mb-sm-30 mb-xs-25">
                    <!-- Service Icon Start -->
                    <?php if (!empty($settings['weta_service_icon_image']['url'])): ?>
                        <div class="service-icon">
                            <img src="<?php echo $settings['weta_service_icon_image']['url']; ?>" alt="<?php echo get_post_meta(attachment_url_to_postid($settings['weta_service_icon_image']['url']), '_wp_attachment_image_alt', true); ?>">
                        </div>
                    <?php endif; ?>
                    <!-- Service Icon  -->
                    <?php if (!empty($settings['weta_service_icon']) || !empty($settings['weta_service_selected_icon']['value'])) : ?>
                        <div class="service-icon">
                            <?php weta_render_icon($settings, 'weta_service_icon', 'weta_service_selected_icon'); ?>
                        </div>
                    <?php endif; ?>
                    <!-- Service SVG  -->
                    <?php if (!empty($settings['weta_service_icon_svg'])): ?>
                        <div class="service-icon">
                            <?php echo $settings['weta_service_icon_svg']; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Service Icon End -->
                </div>
                <?php if($settings['weta_services_list_title']): ?>
                    <h4 class="mb-10 mb-xs-5"><?php echo esc_html($settings['weta_services_list_title']); ?></h4>
                <?php endif; ?>
                <?php if($settings['weta_services_list_desc']): ?>
                    <p class="mb-30"><?php echo esc_html($settings['weta_services_list_desc']); ?></p>
                <?php endif; ?>

                <?php
                    // Link
                    if ('2' == $settings['weta_services_link_type']) {
                        $link = get_permalink($settings['weta_services_page_link']);
                        $target = '_self';
                        $rel = 'nofollow';
                    } else {
                        $link = !empty($settings['weta_services_link']['url']) ? $settings['weta_services_link']['url'] : '';
                        $target = !empty($settings['weta_services_link']['is_external']) ? '_blank' : '';
                        $rel = !empty($settings['weta_services_link']['nofollow']) ? 'nofollow' : '';
                    }
                if(!empty($settings['weta_services_btn_text'])) : ?>
                    <a href="<?php echo esc_url($link); ?>"><?php echo rrdevs_kses($settings['weta_services_btn_text']); ?> <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 7H15" stroke="#010915" stroke-opacity="0.24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 1L15 7L9 13" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    </a>
                <?php endif; ?>
            </div>

        <?php endif; ?>

        <?php 
    }
}

$widgets_manager->register( new WETA_Services() );