<?php
/**
 * weta customizer
 *
 * @package weta
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Added Panels & Sections
 */
function weta_customizer_panels_sections( $wp_customize ) {

    //Add panel
    $wp_customize->add_panel( 'weta_customizer', [
        'priority' => 10,
        'title'    => esc_html__( 'Weta Customizer', 'weta' ),
    ] );

    /**
     * Customizer Section
     */
    $wp_customize->add_section( 'header_top_setting', [
        'title'       => esc_html__( 'Header Info Setting', 'weta' ),
        'description' => '',
        'priority'    => 10,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'header_social', [
        'title'       => esc_html__( 'Header Social', 'weta' ),
        'description' => '',
        'priority'    => 11,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'section_header_logo', [
        'title'       => esc_html__( 'Header Setting', 'weta' ),
        'description' => '',
        'priority'    => 12,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'blog_setting', [
        'title'       => esc_html__( 'Blog Setting', 'weta' ),
        'description' => '',
        'priority'    => 13,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'header_side_setting', [
        'title'       => esc_html__( 'Side Info', 'weta' ),
        'description' => '',
        'priority'    => 14,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'breadcrumb_setting', [
        'title'       => esc_html__( 'Breadcrumb Setting', 'weta' ),
        'description' => '',
        'priority'    => 15,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'blog_setting', [
        'title'       => esc_html__( 'Blog Setting', 'weta' ),
        'description' => '',
        'priority'    => 16,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'footer_setting', [
        'title'       => esc_html__( 'Footer Settings', 'weta' ),
        'description' => '',
        'priority'    => 17,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'footer_social', [
        'title'       => esc_html__( 'Footer Social', 'weta' ),
        'description' => '',
        'priority'    => 18,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'color_setting', [
        'title'       => esc_html__( 'Color Setting', 'weta' ),
        'description' => '',
        'priority'    => 19,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( '404_page', [
        'title'       => esc_html__( '404 Page', 'weta' ),
        'description' => '',
        'priority'    => 20,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );

    $wp_customize->add_section( 'typo_setting', [
        'title'       => esc_html__( 'Typography Setting', 'weta' ),
        'description' => '',
        'priority'    => 21,
        'capability'  => 'edit_theme_options',
        'panel'       => 'weta_customizer',
    ] );
}

add_action( 'customize_register', 'weta_customizer_panels_sections' );

function _header_top_fields( $fields ) {

    // preloader 
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_topbar_switch',
        'label'    => esc_html__( 'Topbar Swicher', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_header_right',
        'label'    => esc_html__( 'Header Right On/Off', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];


    // Backtotop
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_backtotop',
        'label'    => esc_html__('Back To Top On/Off', 'weta'),
        'section'  => 'header_top_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__('Enable', 'weta'),
            'off' => esc_html__('Disable', 'weta'),
        ],
    ];

    // preloader 
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_preloader',
        'label'    => esc_html__( 'Preloader On/Off', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];


    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_search',
        'label'    => esc_html__( 'Serach On/Off', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];


    // contact button
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_button_text',
        'label'    => esc_html__( 'Button Text', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( 'Get Started', 'weta' ),
        'priority' => 10,
        'active_callback' => [
            [
                'setting'  => 'weta_header_right',
                'operator' => '==',
                'value'    => true,
            ],
        ],
    ];

    $fields[] = [
        'type'     => 'link',
        'settings' => 'weta_button_link',
        'label'    => esc_html__( 'Button URL', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
        'active_callback' => [
            [
                'setting'  => 'weta_header_right',
                'operator' => '==',
                'value'    => true,
            ],
        ],
    ];

    // Login Button 
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_login_button_text',
        'label'    => esc_html__( 'Login Button Text', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( 'Login', 'weta' ),
        'priority' => 10,
        'active_callback' => [
            [
                'setting'  => 'weta_header_right',
                'operator' => '==',
                'value'    => true,
            ],
        ],
    ];

    $fields[] = [
        'type'     => 'link',
        'settings' => 'weta_login_button_link',
        'label'    => esc_html__( 'Login Button URL', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
        'active_callback' => [
            [
                'setting'  => 'weta_header_right',
                'operator' => '==',
                'value'    => true,
            ],
        ],
    ];


    // Header 03 Button
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_header_03_button_text',
        'label'    => esc_html__( 'Header 03 Button Text', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( 'GET A FREE QUOTE', 'weta' ),
        'priority' => 10,
        'active_callback' => [
            [
                'setting'  => 'weta_header_right',
                'operator' => '==',
                'value'    => true,
            ],
        ],
    ];

    $fields[] = [
        'type'     => 'link',
        'settings' => 'weta_header_03_button_link',
        'label'    => esc_html__( 'Header 03 Button URL', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
        'active_callback' => [
            [
                'setting'  => 'weta_header_right',
                'operator' => '==',
                'value'    => true,
            ],
        ],
    ];


    // default header topbar info
    // Topbar Text
    $fields[] = [
        'type'     => 'textarea',
        'settings' => 'weta_topbar_text_01',
        'label'    => esc_html__( 'Topbar Slide Text 01', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( 'Create an account to avail a 50% bonus discount at checkout.', 'weta' ),
        'priority' => 10,
    ];

    // Topbar Slider Button
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_topbar_slide_button_text',
        'label'    => esc_html__( 'Topbar Slider Button Text 01', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( 'Learn More', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'link',
        'settings' => 'weta_topbar_slide_button_link',
        'label'    => esc_html__( 'Topbar Slider Button URL 01', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];


    $fields[] = [
        'type'     => 'textarea',
        'settings' => 'weta_topbar_text_02',
        'label'    => esc_html__( 'Topbar Slide Text 02', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( 'Create an account to avail a 50% bonus discount at checkout.', 'weta' ),
        'priority' => 10,
    ];


    // Topbar Slider Button 02
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_topbar_slide_button_text_02',
        'label'    => esc_html__( 'Topbar Slider Button Text 02', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( 'Learn More', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'link',
        'settings' => 'weta_topbar_slide_button_link_02',
        'label'    => esc_html__( 'Topbar Slider Button URL 02', 'weta' ),
        'section'  => 'header_top_setting',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    return $fields;
}
add_filter( 'kirki/fields', '_header_top_fields' );



/*
Header Social
 */
function _header_social_fields( $fields ) {
    // header section social
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_topbar_facebook_url',
        'label'    => esc_html__( 'Facebook Url', 'weta' ),
        'section'  => 'header_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_topbar_linkedin_url',
        'label'    => esc_html__( 'Linkedin Url', 'weta' ),
        'section'  => 'header_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_topbar_instagram_url',
        'label'    => esc_html__( 'Instagram Url', 'weta' ),
        'section'  => 'header_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_topbar_youtube_url',
        'label'    => esc_html__( 'Youtube Url', 'weta' ),
        'section'  => 'header_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];    

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_topbar_pinterest_url',
        'label'    => esc_html__( 'Pinterest Url', 'weta' ),
        'section'  => 'header_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    return $fields;
}
add_filter( 'kirki/fields', '_header_social_fields' );



/*
Header Settings
 */
function _header_header_fields( $fields ) {
    $fields[] = [
        'type'        => 'radio-image',
        'settings'    => 'choose_default_header',
        'label'       => esc_html__( 'Select Header Style', 'weta' ),
        'section'     => 'section_header_logo',
        'placeholder' => esc_html__( 'Select an option...', 'weta' ),
        'priority'    => 10,
        'multiple'    => 1,
        'choices'     => [
            'header-style-1'   => get_template_directory_uri() . '/inc/img/header/header-1.png',
            'header-style-2'   => get_template_directory_uri() . '/inc/img/header/header-2.png',
            'header-style-3'   => get_template_directory_uri() . '/inc/img/header/header-3.png',
        ],
        'default'     => 'header-style-1',
    ];

    $fields[] = [
        'type'        => 'image',
        'settings'    => 'logo',
        'label'       => esc_html__( 'Header Logo', 'weta' ),
        'description' => esc_html__( 'Upload Your Logo.', 'weta' ),
        'section'     => 'section_header_logo',
        'default'     => get_template_directory_uri() . '/assets/imgs/logo/logo-5.svg',
    ];

    $fields[] = [
        'type'        => 'image',
        'settings'    => 'seconday_logo',
        'label'       => esc_html__( 'Header Secondary Logo', 'weta' ),
        'description' => esc_html__( 'Header Logo Black', 'weta' ),
        'section'     => 'section_header_logo',
        'default'     => get_template_directory_uri() . '/assets/imgs/logo/logo-white.svg',
    ];      


    // Header Style 02 Logo 
    $fields[] = [
        'type'        => 'image',
        'settings'    => 'header_style_02_logo',
        'label'       => esc_html__( 'Header Style 02 Logo', 'weta' ),
        'description' => esc_html__( 'Header 02 Logo', 'weta' ),
        'section'     => 'section_header_logo',
        'default'     => get_template_directory_uri() . '/assets/imgs/logo/logo.svg',
    ];     

    // Header Style 03 Logo 
    $fields[] = [
        'type'        => 'image',
        'settings'    => 'header_style_03_logo',
        'label'       => esc_html__( 'Header Style 03 Logo', 'weta' ),
        'description' => esc_html__( 'Header 03 Logo', 'weta' ),
        'section'     => 'section_header_logo',
        'default'     => get_template_directory_uri() . '/assets/imgs/logo/logo-2.svg',
    ];    

    return $fields;
}
add_filter( 'kirki/fields', '_header_header_fields' );

/*
Header Side Info
 */
function _header_side_fields( $fields ) {
    // side info settings
    $fields[] = [
        'type'        => 'image',
        'settings'    => 'weta_side_logo',
        'label'       => esc_html__( 'Logo Side', 'weta' ),
        'description' => esc_html__( 'Logo Side', 'weta' ),
        'section'     => 'header_side_setting',
        'default'     => get_template_directory_uri() . '/assets/imgs/logo/logo-white.svg',
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_side_social_title',
        'label'    => esc_html__( 'Side Social Title', 'weta' ),
        'section'  => 'header_side_setting',
        'default'  => esc_html__( 'Subscribe & Follow', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_side_social_hide',
        'label'    => esc_html__( 'Side Info Social On/Off', 'weta' ),
        'section'  => 'header_side_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    return $fields;
}
add_filter( 'kirki/fields', '_header_side_fields' );

/*
_header_page_title_fields
 */
function _header_page_title_fields( $fields ) {
    // Breadcrumb Setting
    $fields[] = [
        'type'        => 'image',
        'settings'    => 'breadcrumb_bg_img',
        'label'       => esc_html__( 'Breadcrumb Background Image', 'weta' ),
        'description' => esc_html__( 'Breadcrumb Background Image', 'weta' ),
        'section'     => 'breadcrumb_setting',
        'default'     => get_template_directory_uri() . '/assets/img/bg-img/page-header.jpg"',
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_breadcrumb_bg_color',
        'label'       => __( 'Breadcrumb BG Color', 'weta' ),
        'description' => esc_html__( 'This is a Breadcrumb bg color control.', 'weta' ),
        'section'     => 'breadcrumb_setting',
        'default'     => '#222',
        'priority'    => 10,
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'breadcrumb_shape_switch',
        'label'    => esc_html__( 'Breadcrumb Shape Hide', 'weta' ),
        'section'  => 'breadcrumb_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'breadcrumb_switch',
        'label'    => esc_html__( 'Breadcrumb Hide', 'weta' ),
        'section'  => 'breadcrumb_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'breadcrumb_info_switch',
        'label'    => esc_html__( 'Breadcrumb Info switch', 'weta' ),
        'section'  => 'breadcrumb_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    return $fields;
}
add_filter( 'kirki/fields', '_header_page_title_fields' );

/*
Header Social
 */
function _header_blog_fields( $fields ) {
// Blog Setting
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_blog_btn_switch',
        'label'    => esc_html__( 'Blog BTN On/Off', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_blog_page_sidebar_hide',
        'label'    => esc_html__( 'Blog Page Sidebar On/Off', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_blog_cat',
        'label'    => esc_html__( 'Blog Category Meta On/Off', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_blog_author',
        'label'    => esc_html__( 'Blog Author Meta On/Off', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_blog_date',
        'label'    => esc_html__( 'Blog Date Meta On/Off', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_blog_comments',
        'label'    => esc_html__( 'Blog Comments Meta On/Off', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => '1',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_blog_btn',
        'label'    => esc_html__( 'Blog Button text', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => esc_html__( 'Read More', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'breadcrumb_blog_title',
        'label'    => esc_html__( 'Blog Title', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => esc_html__( 'Blog', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'breadcrumb_blog_title_details',
        'label'    => esc_html__( 'Blog Details Title', 'weta' ),
        'section'  => 'blog_setting',
        'default'  => esc_html__( 'Blog Details', 'weta' ),
        'priority' => 10,
    ];
    return $fields;
}
add_filter( 'kirki/fields', '_header_blog_fields' );


/*
Footer
 */
function _header_footer_fields( $fields ) {
    // Footer Setting
    $fields[] = [
        'type'        => 'radio-image',
        'settings'    => 'choose_default_footer',
        'label'       => esc_html__( 'Choose Footer Style', 'weta' ),
        'section'     => 'footer_setting',
        'default'     => '5',
        'placeholder' => esc_html__( 'Select an option...', 'weta' ),
        'priority'    => 10,
        'multiple'    => 1,
        'choices'     => [
            'footer-style-1'   => get_template_directory_uri() . '/inc/img/footer/footer-1.png',
            'footer-style-2'   => get_template_directory_uri() . '/inc/img/footer/footer-2.png',
            'footer-style-3'   => get_template_directory_uri() . '/inc/img/footer/footer-3.png',
        ],
        'default'     => 'footer-style-1',
    ];

    $fields[] = [
        'type'        => 'select',
        'settings'    => 'footer_widget_number',
        'label'       => esc_html__( 'Widget Number', 'weta' ),
        'section'     => 'footer_setting',
        'default'     => '4',
        'placeholder' => esc_html__( 'Select an option...', 'weta' ),
        'priority'    => 10,
        'multiple'    => 1,
        'choices'     => [
            '4' => esc_html__( 'Widget Number 4', 'weta' ),
            '3' => esc_html__( 'Widget Number 3', 'weta' ),
            '2' => esc_html__( 'Widget Number 2', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'        => 'text',
        'settings'    => 'weta_footer_top_space',
        'label'       => __( 'Footer Top Space', 'weta' ),
        'description' => esc_html__( 'This is Footer Top Space Control', 'weta' ),
        'section'     => 'footer_setting',
        'default'     => '',
        'priority'    => 10,
    ];    

    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_footer_bg_color',
        'label'       => __( 'Default Footer BG Color', 'weta' ),
        'description' => esc_html__( 'This is a Footer bg color control.', 'weta' ),
        'section'     => 'footer_setting',
        'default'     => '#010915',
        'priority'    => 10,
    ];

    $fields[] = [
        'type'        => 'image',
        'settings'    => 'weta_footer_logo',
        'label'       => esc_html__( 'Footer Logo', 'weta' ),
        'description' => esc_html__( 'Upload Your Logo.', 'weta' ),
        'section'     => 'footer_setting',
        'default'     => get_template_directory_uri() . '/assets/imgs/logo/logo-4.svg',
    ];

    // Default Footer top switch
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_default_footer_top_switch',
        'label'    => esc_html__( 'Default Footer Top On/Off', 'weta' ),
        'section'  => 'footer_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    // Footer Bottom Menu Swich 
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'weta_footer_bottom_menu_switch',
        'label'    => esc_html__( 'Footer Bottom Menu On/Off', 'weta' ),
        'section'  => 'footer_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];


    // Footer Style 02 
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'footer_style_2_switch',
        'label'    => esc_html__( 'Footer Style 2 On/Off', 'weta' ),
        'section'  => 'footer_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];


    // Footer Style 03 
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'footer_style_3_switch',
        'label'    => esc_html__( 'Footer Style 03 On/Off', 'weta' ),
        'section'  => 'footer_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    $fields[] = [
        'type'        => 'image',
        'settings'    => 'weta_footer_style_03_logo',
        'label'       => esc_html__( 'Footer Style 03 Logo', 'weta' ),
        'description' => esc_html__( 'Upload Your Logo.', 'weta' ),
        'section'     => 'footer_setting',
        'default'     => get_template_directory_uri() . '/assets/imgs/logo/logo-3.svg',
    ];

    // Footer Style 04 
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'footer_style_4_switch',
        'label'    => esc_html__( 'Footer Style 04 On/Off', 'weta' ),
        'section'  => 'footer_setting',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];

    // Footer Copyright Text 
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_copyright',
        'label'    => esc_html__( 'Copyright', 'weta' ),
        'section'  => 'footer_setting',
        'default'  => esc_html__( 'Copyright © 2024 Weta All Rights Reserved.', 'weta' ),
        'priority' => 10,
    ];

    return $fields;
}
add_filter( 'kirki/fields', '_header_footer_fields' );


/*
Footer Social
 */
function _footer_social_fields( $fields ) {
    // Footer section social
    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_footer_fb_url',
        'label'    => esc_html__( 'Facebook Url', 'weta' ),
        'section'  => 'footer_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_footer_twitter_url',
        'label'    => esc_html__( 'Twitter Url', 'weta' ),
        'section'  => 'footer_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_footer_linkedin_url',
        'label'    => esc_html__( 'Linkedin Url', 'weta' ),
        'section'  => 'footer_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_footer_instagram_url',
        'label'    => esc_html__( 'Instagram Url', 'weta' ),
        'section'  => 'footer_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_footer_youtube_url',
        'label'    => esc_html__( 'Youtube Url', 'weta' ),
        'section'  => 'footer_social',
        'default'  => esc_html__( '#', 'weta' ),
        'priority' => 10,
    ];


    return $fields;
}
add_filter( 'kirki/fields', '_footer_social_fields' );


// color
function weta_color_fields( $fields ) {
    // Color Settings
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_prim',
        'label'       => __( 'Primary Color', 'weta' ),
        'description' => esc_html__( 'This is a Primary color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#3C66FA',
        'priority'    => 10,

    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_prim1',
        'label'       => __( 'Primary 1 Color', 'weta' ),
        'description' => esc_html__( 'This is a Primary 1 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#F3B351',
        'priority'    => 10,

    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_prim1',
        'label'       => __( 'Primary 1 Color', 'weta' ),
        'description' => esc_html__( 'This is a Primary 1 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#F3B351',
        'priority'    => 10,

    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_prim2',
        'label'       => __( 'Primary 2 Color', 'weta' ),
        'description' => esc_html__( 'This is a Primary 2 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#7341F1',
        'priority'    => 10,

    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_heading_primary',
        'label'       => __( 'Heading Primary Color', 'weta' ),
        'description' => esc_html__( 'This is a Heading Primary color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#010915',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_heading_secondary',
        'label'       => __( 'Heading Secondary Color', 'weta' ),
        'description' => esc_html__( 'This is a Heading Secondary color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#0B1728',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_text_prim',
        'label'       => __( 'Text Primary Color', 'weta' ),
        'description' => esc_html__( 'This is a Text Primary color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#010915',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_text_prim1',
        'label'       => __( 'Text Primary 1 Color', 'weta' ),
        'description' => esc_html__( 'This is a Text Primary 1 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#23232B',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_text_prim2',
        'label'       => __( 'Text Primary 2 Color', 'weta' ),
        'description' => esc_html__( 'This is a Text Primary 2 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#818181',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_text_prim3',
        'label'       => __( 'Text Primary 3 Color', 'weta' ),
        'description' => esc_html__( 'This is a Text Primary 3 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#84858D',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_placeholder',
        'label'       => __( 'Placeholder Color', 'weta' ),
        'description' => esc_html__( 'This is a Placeholder color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#4A5764',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_selection',
        'label'       => __( 'Selection Color', 'weta' ),
        'description' => esc_html__( 'This is a Selection color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#3C66FA',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_body_prim',
        'label'       => __( 'Body Color', 'weta' ),
        'description' => esc_html__( 'This is a Selection color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#ffffff',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_section1',
        'label'       => __( 'Section 1 Color', 'weta' ),
        'description' => esc_html__( 'This is a Section 1 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#E9EBF7',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_section2',
        'label'       => __( 'Section 2 Color', 'weta' ),
        'description' => esc_html__( 'This is a Section 2 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#F5F5F5',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_section3',
        'label'       => __( 'Section 3 Color', 'weta' ),
        'description' => esc_html__( 'This is a Section 3 color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#F14141',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_menu_prim',
        'label'       => __( 'Menu Primary Color', 'weta' ),
        'description' => esc_html__( 'This is a Menu Primary color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#888899',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_body',
        'label'       => __( 'Body Text Color', 'weta' ),
        'description' => esc_html__( 'This is a body text color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => 'rgba(1, 9, 21, 0.6)',
        'priority'    => 10,
        'choices'     => [
			'alpha' => true,
		],
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_white',
        'label'       => __( 'White Color', 'weta' ),
        'description' => esc_html__( 'This is a white color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#FFFFFF',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_black',
        'label'       => __( 'Black Color', 'weta' ),
        'description' => esc_html__( 'This is a black color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => '#000000',
        'priority'    => 10,
    ];
    $fields[] = [
        'type'        => 'color',
        'settings'    => 'weta_color_option_border_prim',
        'label'       => __( 'Border Primary Color', 'weta' ),
        'description' => esc_html__( 'This is a Border Primary color control.', 'weta' ),
        'section'     => 'color_setting',
        'default'     => 'rgba(1, 9, 21, 0.14)',
        'priority'    => 10,
        'choices'     => [
			'alpha' => true,
		],
    ];
    return $fields;
}
add_filter( 'kirki/fields', 'weta_color_fields' );

// 404
function weta_404_fields( $fields ) {

    // 404 settings
    $fields[] = [
        'type'        => 'image',
        'settings'    => 'weta_404_img',
        'label'       => esc_html__('404 Image.', 'weta'),
        'description' => esc_html__('404 Image.', 'weta'),
        'section'     => '404_page',
        'default'     => get_template_directory_uri() . '/assets/imgs/404/404.svg',
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_404_text',
        'label'    => esc_html__('404 Text', 'weta'),
        'section'  => '404_page',
        'default'  => esc_html__('404', 'weta'),
        'priority' => 10,
    ];    

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_404_title',
        'label'    => esc_html__('404 Page Title', 'weta'),
        'section'  => '404_page',
        'default'  => esc_html__('Oops! Page Can’t be Found.', 'weta'),
        'priority' => 10,
    ];    

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_404_message',
        'label'    => esc_html__('404 Message', 'weta'),
        'section'  => '404_page',
        'default'  => esc_html__('Oops! it could be you or us, there is no page here. It might have been moved or deleted.', 'weta'),
        'priority' => 10,
    ];

    $fields[] = [
        'type'     => 'text',
        'settings' => 'weta_404_btn_text',
        'label'    => esc_html__('404 Button Text', 'weta'),
        'section'  => '404_page',
        'default'  => esc_html__('Go Back Home', 'weta'),
        'priority' => 10,
    ];

    // 404 page shape switch
    $fields[] = [
        'type'     => 'switch',
        'settings' => 'shape_switch_404',
        'label'    => esc_html__( 'Shape On/Off', 'weta' ),
        'section'  => '404_page',
        'default'  => '0',
        'priority' => 10,
        'choices'  => [
            'on'  => esc_html__( 'Enable', 'weta' ),
            'off' => esc_html__( 'Disable', 'weta' ),
        ],
    ];


    return $fields;
}
add_filter( 'kirki/fields', 'weta_404_fields' );

/**
 * Added Fields
 */
function weta_typo_fields( $fields ) {
    // typography settings
    $fields[] = [
        'type'        => 'typography',
        'settings'    => 'typography_body_setting',
        'label'       => esc_html__( 'Body Font', 'weta' ),
        'section'     => 'typo_setting',
        'default'     => [
            'font-family'    => '',
            'variant'        => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '0',
            'color'          => '',
        ],
        'priority'    => 10,
        'transport'   => 'auto',
        'output'      => [
            [
                'element' => 'body',
            ],
        ],
    ];

    $fields[] = [
        'type'        => 'typography',
        'settings'    => 'typography_h_setting',
        'label'       => esc_html__( 'Heading h1 Fonts', 'weta' ),
        'section'     => 'typo_setting',
        'default'     => [
            'font-family'    => '',
            'variant'        => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '0',
            'color'          => '',
        ],
        'priority'    => 10,
        'transport'   => 'auto',
        'output'      => [
            [
                'element' => 'h1',
            ],
        ],
    ];

    $fields[] = [
        'type'        => 'typography',
        'settings'    => 'typography_h2_setting',
        'label'       => esc_html__( 'Heading h2 Fonts', 'weta' ),
        'section'     => 'typo_setting',
        'default'     => [
            'font-family'    => '',
            'variant'        => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '0',
            'color'          => '',
        ],
        'priority'    => 10,
        'transport'   => 'auto',
        'output'      => [
            [
                'element' => 'h2',
            ],
        ],
    ];

    $fields[] = [
        'type'        => 'typography',
        'settings'    => 'typography_h3_setting',
        'label'       => esc_html__( 'Heading h3 Fonts', 'weta' ),
        'section'     => 'typo_setting',
        'default'     => [
            'font-family'    => '',
            'variant'        => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '0',
            'color'          => '',
        ],
        'priority'    => 10,
        'transport'   => 'auto',
        'output'      => [
            [
                'element' => 'h3',
            ],
        ],
    ];

    $fields[] = [
        'type'        => 'typography',
        'settings'    => 'typography_h4_setting',
        'label'       => esc_html__( 'Heading h4 Fonts', 'weta' ),
        'section'     => 'typo_setting',
        'default'     => [
            'font-family'    => '',
            'variant'        => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '0',
            'color'          => '',
        ],
        'priority'    => 10,
        'transport'   => 'auto',
        'output'      => [
            [
                'element' => 'h4',
            ],
        ],
    ];

    $fields[] = [
        'type'        => 'typography',
        'settings'    => 'typography_h5_setting',
        'label'       => esc_html__( 'Heading h5 Fonts', 'weta' ),
        'section'     => 'typo_setting',
        'default'     => [
            'font-family'    => '',
            'variant'        => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '0',
            'color'          => '',
        ],
        'priority'    => 10,
        'transport'   => 'auto',
        'output'      => [
            [
                'element' => 'h5',
            ],
        ],
    ];

    $fields[] = [
        'type'        => 'typography',
        'settings'    => 'typography_h6_setting',
        'label'       => esc_html__( 'Heading h6 Fonts', 'weta' ),
        'section'     => 'typo_setting',
        'default'     => [
            'font-family'    => '',
            'variant'        => '',
            'font-size'      => '',
            'line-height'    => '',
            'letter-spacing' => '0',
            'color'          => '',
        ],
        'priority'    => 10,
        'transport'   => 'auto',
        'output'      => [
            [
                'element' => 'h6',
            ],
        ],
    ];
    return $fields;
}

add_filter( 'kirki/fields', 'weta_typo_fields' );

/**
 * This is a short hand function for getting setting value from customizer
 *
 * @param string $name
 *
 * @return bool|string
 */
function weta_THEME_option( $name ) {
    $value = '';
    if ( class_exists( 'weta' ) ) {
        $value = Kirki::get_option( weta_get_theme(), $name );
    }

    return apply_filters( 'weta_THEME_option', $value, $name );
}

/**
 * Get config ID
 *
 * @return string
 */
function weta_get_theme() {
    return 'weta';
}