<?php

/**
 * weta_scripts description
 * @return [type] [description]
 */
function weta_scripts() {

    /**
     * all css files
    */

    wp_enqueue_style( 'weta-fonts', weta_fonts_url(), array(), '1.0.0');
    if( is_rtl() ){
        wp_enqueue_style( 'bootstrap-rtl', WETA_THEME_ASSETS .'css/bootstrap.rtl.min.css', array() );
    } else{
        wp_enqueue_style( 'bootstrap', WETA_THEME_ASSETS . 'css/bootstrap.min.css', array() );
    }
    wp_enqueue_style( 'animate', WETA_THEME_ASSETS . 'css/animate.min.css', [] );
    wp_enqueue_style( 'fontawesome-pro', WETA_THEME_ASSETS . 'css/fontawesome-pro.css', [] );
    wp_enqueue_style( 'magnific-popup', WETA_THEME_ASSETS . 'css/magnific-popup.css', [] );
    wp_enqueue_style( 'nice-select', WETA_THEME_ASSETS . 'css/nice-select.css', [] );
    wp_enqueue_style( 'odometer-theme-default', WETA_THEME_ASSETS . 'css/odometer-theme-default.css', [] );
    wp_enqueue_style( 'slick', WETA_THEME_ASSETS . 'css/slick.min.css', [] );
    wp_enqueue_style( 'spacing', WETA_THEME_ASSETS . 'css/spacing.css', [] );
    wp_enqueue_style( 'swiper', WETA_THEME_ASSETS . 'css/swiper.min.css', [] );
    wp_enqueue_style( 'weta-custom', WETA_THEME_ASSETS . 'css/weta-custom.css', [] );
    wp_enqueue_style( 'weta-updater', WETA_THEME_ASSETS . 'css/weta-updater.css', [], time() );
    wp_enqueue_style( 'weta-unit', WETA_THEME_ASSETS . 'css/weta-unit.css', [], time() );
    wp_enqueue_style( 'weta-responsive', WETA_THEME_ASSETS . 'css/weta-responsive.css', [], time() );
    wp_enqueue_style( 'weta-main', WETA_THEME_ASSETS . 'css/main.css', [], time() );
    wp_enqueue_style( 'weta-style', get_stylesheet_uri() );


    // all js
    wp_enqueue_script( 'bootstrap-bundle', WETA_THEME_ASSETS . 'js/bootstrap.bundle.min.js', [ 'jquery' ], '', true );
    wp_enqueue_script( 'counterup', WETA_THEME_ASSETS . 'js/counterup.js', [ 'jquery' ], '', true );
    wp_enqueue_script( 'gsap', WETA_THEME_ASSETS . 'js/gsap.min.js', [ 'jquery' ], '', true );
    wp_enqueue_script( 'isotope', WETA_THEME_ASSETS . 'js/isotope-pkgd.js', [ 'imagesloaded' ], '5.4.5', true );
    wp_enqueue_script( 'appear', WETA_THEME_ASSETS . 'js/jquery.appear.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'knob', WETA_THEME_ASSETS . 'js/jquery-knob.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'magnific-popup', WETA_THEME_ASSETS . 'js/magnific-popup.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'meanmenu', WETA_THEME_ASSETS . 'js/meanmenu.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'nice-select', WETA_THEME_ASSETS . 'js/nice-select.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'odometer', WETA_THEME_ASSETS . 'js/odometer.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'parallax', WETA_THEME_ASSETS . 'js/parallax.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'purecounter', WETA_THEME_ASSETS . 'js/purecounter.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'ScrollTrigger', WETA_THEME_ASSETS . 'js/ScrollTrigger.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'slick', WETA_THEME_ASSETS . 'js/slick.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'SplitText', WETA_THEME_ASSETS . 'js/SplitText.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'swiper', WETA_THEME_ASSETS . 'js/swiper.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'type', WETA_THEME_ASSETS . 'js/type.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'waypoints', WETA_THEME_ASSETS . 'js/waypoints.min.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'wow', WETA_THEME_ASSETS . 'js/wow.js', [ 'jquery' ], false, true );
    wp_enqueue_script( 'weta-main', WETA_THEME_ASSETS . 'js/main.js', [ 'jquery' ], time(), true );
    wp_enqueue_script( 'weta-elementor', WETA_THEME_ASSETS . 'js/weta-elementor.js', [ 'jquery' ], time(), true );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'weta_scripts' );


/*
Register Fonts
*/

function weta_fonts_url() {
    $fonts_url = '';

    /* Translators: If there are characters in your language that are not
    * supported by DM Sans, translate this to 'off'. Do not translate
    * into your own language.
    */
    $dm_sans = _x( 'on', 'DM Sans font: on or off', 'weta' );

    /* Translators: If there are characters in your language that are not
    * supported by Roboto, translate this to 'off'. Do not translate
    * into your own language.
    */
    $roboto = _x( 'on', 'Roboto font: on or off', 'weta' );

    if ( 'off' !== $dm_sans || 'off' !== $roboto ) {
        $font_families = array();

        if ( 'off' !== $dm_sans ) {
            $font_families[] = 'DM+Sans:400,500,600,700,800,900';
        }

        if ( 'off' !== $roboto ) {
            $font_families[] = 'Roboto:400,500,600,700,900';
        }

        $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),
            'subset' => urlencode( 'latin,latin-ext' ),
        );

        $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
    }

    return esc_url_raw( $fonts_url );
}