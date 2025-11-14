<?php

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function weta_widgets_init() {

    $footer_style_2_switch = get_theme_mod( 'footer_style_2_switch', true );
    $footer_style_3_switch = get_theme_mod( 'footer_style_3_switch', true );
    $footer_style_4_switch = get_theme_mod( 'footer_style_4_switch', true );

    /**
     * Blog sidebar
     */
    register_sidebar( [
        'name'          => esc_html__( 'Blog Sidebar', 'weta' ),
        'id'            => 'blog-sidebar',
        'description'          => esc_html__( 'Set Your Blog Widget', 'weta' ),
        'before_widget' => '<div id="%1$s" class="sidebar__widget sidebar-widget sidebar__single %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="sidebar__widget-head"><h4 class="sidebar__widget-title">',
        'after_title'   => '</h4></div>',
    ] );


    /**
     * Service sidebar
     */
    register_sidebar( [
        'name'          => esc_html__( 'Service Sidebar', 'weta' ),
        'id'            => 'services-sidebar',
        'description'          => esc_html__( 'Set Your Service Widget', 'weta' ),
        'before_widget' => '<div id="%1$s" class="sidebar__single service__sidebar %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="sidebar__widget-head mb-35"><h6 class="widget title">',
        'after_title'   => '</h6></div>',
    ] );



    $footer_widgets = get_theme_mod( 'footer_widget_number', 5 );

    // footer default
    for ( $num = 1; $num <= $footer_widgets; $num++ ) {
        register_sidebar( [
            'name'          => sprintf( esc_html__( 'Footer %1$s', 'weta' ), $num ),
            'id'            => 'footer-' . $num,
            'description'   => sprintf( esc_html__( 'Footer column %1$s', 'weta' ), $num ),
            'before_widget' => '<div id="%1$s" class="footer-widget mb-50 footer-col-'.$num.' %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="footer-3__widget-title"><h4>',
            'after_title'   => '</h4></div>',
        ] );
    }


    $footer_02_widgets = get_theme_mod( 'footer_widget_number', 5 );
    // footer 2
    if ( $footer_style_2_switch ) {
        for ( $num = 1; $num <= $footer_02_widgets; $num++ ) {

            register_sidebar( [
                'name'          => sprintf( esc_html__( 'Footer Style 2 : %1$s', 'weta' ), $num ),
                'id'            => 'footer-2-' . $num,
                'description'   => sprintf( esc_html__( 'Footer Style 2 : %1$s', 'weta' ), $num ),
                'before_widget' => '<div id="%1$s" class="footer-widget footer__widget footer__widget-title footer__widget-2 footer-col-2- footer__widget-item-'.$num.' %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4>',
                'after_title'   => '</h4>',
            ] );
        }
    }

    $footer_03_widgets = get_theme_mod( 'footer_widget_number', 5 );

    // footer 3
    if ( $footer_style_3_switch ) {
        for ( $num = 1; $num <= $footer_03_widgets; $num++ ) {

            register_sidebar( [
                'name'          => sprintf( esc_html__( 'Footer Style 3 : %1$s', 'weta' ), $num ),
                'id'            => 'footer-3-' . $num,
                'description'   => sprintf( esc_html__( 'Footer Style 3 : %1$s', 'weta' ), $num ),
                'before_widget' => '<div id="%1$s" class="footer-widget footer-2__widget-title footer__widget-3 footer-col-3-'.$num.' %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4>',
                'after_title'   => '</h4>',
            ] );
        }
    }

    // footer 4
    $footer_04_widgets = get_theme_mod( 'footer_widget_number', 5 );
    if ( $footer_style_4_switch ) {
        for ( $num = 1; $num <= $footer_04_widgets; $num++ ) {
            register_sidebar( [
                'name'          => sprintf( esc_html__( 'Footer Style 4 : %1$s', 'weta' ), $num ),
                'id'            => 'footer-4-' . $num,
                'description'   => sprintf( esc_html__( 'Footer Style 4 : %1$s', 'weta' ), $num ),
                'before_widget' => '<div id="%1$s" class="footer-widget footer__widget footer__widget-title footer__widget-2 footer-col-2- footer__widget-item-'.$num.' %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4>',
                'after_title'   => '</h4>',
            ] );
        }
    }
}

add_action( 'widgets_init', 'weta_widgets_init' );