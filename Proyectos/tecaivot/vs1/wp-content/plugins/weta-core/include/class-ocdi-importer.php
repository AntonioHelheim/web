<?php
/**
 * 
 * Demo Imports
 */

function rr_ocdi_import_files() {
    
    return array(
      array(
        'import_file_name'           => 'Home 01',
        'local_import_file'             => trailingslashit( get_template_directory() ) .'sample-data/contents-demo.xml',
        'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'sample-data/widget-settings.json',
        'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'sample-data/customizer-data.dat',
        'import_preview_image_url' => plugins_url( 'assets/img/demo/home1.jpg', dirname(__FILE__) ),
        'preview_url'                => 'https://wp.rrdevs.net/weta/',
      ),
      array(
        'import_file_name'           => 'Home 02',
        'local_import_file'             => trailingslashit( get_template_directory() ) .'sample-data/contents-demo.xml',
        'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'sample-data/widget-settings.json',
        'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'sample-data/customizer-data.dat',
        'import_preview_image_url' => plugins_url( 'assets/img/demo/home2.jpg', dirname(__FILE__) ),
        'preview_url'                => 'https://wp.rrdevs.net/weta/home-02/',
      ),
      array(
        'import_file_name'           => 'Home 03',
        'local_import_file'             => trailingslashit( get_template_directory() ) .'sample-data/contents-demo.xml',
        'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'sample-data/widget-settings.json',
        'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'sample-data/customizer-data.dat',
        'import_preview_image_url' => plugins_url( 'assets/img/demo/home3.jpg', dirname(__FILE__) ),
        'preview_url'                => 'https://wp.rrdevs.net/weta/home-03/',
      ),
      array(
        'import_file_name'           => 'Home 01 (One Page Nav)',
        'local_import_file'             => trailingslashit( get_template_directory() ) .'sample-data/contents-demo.xml',
        'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'sample-data/widget-settings.json',
        'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'sample-data/customizer-data.dat',
        'import_preview_image_url' => plugins_url( 'assets/img/demo/home1.jpg', dirname(__FILE__) ),
        'preview_url'                => 'https://wp.rrdevs.net/weta/home-01-one-page-nav/',
      ),
      array(
        'import_file_name'           => 'Home 02 (One Page Nav)',
        'local_import_file'             => trailingslashit( get_template_directory() ) .'sample-data/contents-demo.xml',
        'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'sample-data/widget-settings.json',
        'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'sample-data/customizer-data.dat',
        'import_preview_image_url' => plugins_url( 'assets/img/demo/home2.jpg', dirname(__FILE__) ),
        'preview_url'                => 'https://wp.rrdevs.net/weta/home-02-one-page-nav/',
      ),      
      array(
        'import_file_name'           => 'Home 03 (One Page Nav)',
        'local_import_file'             => trailingslashit( get_template_directory() ) .'sample-data/contents-demo.xml',
        'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'sample-data/widget-settings.json',
        'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'sample-data/customizer-data.dat',
        'import_preview_image_url' => plugins_url( 'assets/img/demo/home3.jpg', dirname(__FILE__) ),
        'preview_url'                => 'https://wp.rrdevs.net/weta/home-03-one-page-nav/',
      ),
    );
}
add_filter( 'ocdi/import_files', 'rr_ocdi_import_files' );


// after demo imports
function rr_ocdi_after_import_setup( $demo ) {
    $front_page_id = "";
    $blog_page_id = "";
    if( "Home 01" == $demo['import_file_name'] ){
        // Assign front page and posts page (blog page).
        $front_page_id = get_page_by_title( 'Home' );
        $blog_page_id  = get_page_by_title( 'Blog' );
    }else if( "Home 02" == $demo['import_file_name'] ){
        // Assign front page and posts page (blog page).
        $front_page_id = get_page_by_title( 'Home 02' );
        $blog_page_id  = get_page_by_title( 'Blog' );
    }
    else if( "Home 03" == $demo['import_file_name'] ){
        // Assign front page and posts page (blog page).
        $front_page_id = get_page_by_title( 'Home 03' );
        $blog_page_id  = get_page_by_title( 'Blog' );
    }
    else if( "Home 01 (One Page Nav)" == $demo['import_file_name'] ){
        // Assign front page and posts page (blog page).
        $front_page_id = get_page_by_title( 'Home 01 (One Page Nav)' );
        $blog_page_id  = get_page_by_title( 'Blog' );
    }
    else if( "Home 02 (One Page Nav)" == $demo['import_file_name'] ){
        // Assign front page and posts page (blog page).
        $front_page_id = get_page_by_title( 'Home 02 (One Page Nav)' );
        $blog_page_id  = get_page_by_title( 'Blog' );
    }    
    else if( "Home 03 (One Page Nav)" == $demo['import_file_name'] ){
        // Assign front page and posts page (blog page).
        $front_page_id = get_page_by_title( 'Home 03 (One Page Nav)' );
        $blog_page_id  = get_page_by_title( 'Blog' );
    }

    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $front_page_id->ID );
    update_option( 'page_for_posts', $blog_page_id->ID );


    // Assign menus to their locations.
    $main_menu = get_term_by( 'name', 'Main Menu', 'nav_menu' );
 
    set_theme_mod( 'nav_menu_locations', [
            'main-menu' => $main_menu->term_id, // replace 'main-menu' here with the menu location identifier from register_nav_menu() function in your theme.
        ]
    );
}
add_action( 'ocdi/after_import', 'rr_ocdi_after_import_setup' );



function rr_ocdi_plugin_page_setup( $default_settings ) {
    $default_settings['parent_slug'] = 'themes.php';
    $default_settings['page_title']  = esc_html__( 'One Click Demo Import' , 'one-click-demo-import' );
    $default_settings['menu_title']  = esc_html__( 'Import Theme Demos' , 'one-click-demo-import' );
    $default_settings['capability']  = 'import';
    $default_settings['menu_slug']   = 'one-click-demo-import';
 
    return $default_settings;
}
add_filter( 'ocdi/plugin_page_setup', 'rr_ocdi_plugin_page_setup' );