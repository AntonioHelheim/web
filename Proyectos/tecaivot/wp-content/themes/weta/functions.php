<?php

/**
 * weta functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package weta
 */

if ( !function_exists( 'weta_setup' ) ):
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function weta_setup() {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on weta, use a find and replace
         * to change 'weta' to the name of your theme in all the template files.
         */
        load_theme_textdomain( 'weta', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support( 'title-tag' );

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support( 'post-thumbnails' );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( [
            'main-menu' => esc_html__( 'Main Menu', 'weta' ),
            'onepage-nav-menu' => esc_html__('Onepage Nav Menu', 'weta'),
            'footer-bottom-menu' => esc_html__( 'Footer Bottom Menu', 'weta' ),
            'footer-copyright-menu' => esc_html__( 'Footer Copyright Menu', 'weta' ),
        ] );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support( 'html5', [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ] );

        // Set up the WordPress core custom background feature.
        add_theme_support( 'custom-background', apply_filters( 'weta_custom_background_args', [
            'default-color' => 'ffffff',
            'default-image' => '',
        ] ) );

        // Add theme support for selective refresh for widgets.
        add_theme_support( 'customize-selective-refresh-widgets' );

        //Enable custom header
        add_theme_support( 'custom-header' );

        /**
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        add_theme_support( 'custom-logo', [
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        ] );

        /**
         * Enable suporrt for Post Formats
         *
         * see: https://codex.wordpress.org/Post_Formats
         */
        add_theme_support('post-formats', [
            'image',
            'audio',
            'video',
            'gallery',
            'quote',
        ]);


        // Add support for Block Styles.
        add_theme_support( 'wp-block-styles' );

        // Add support for full and wide align images.
        add_theme_support( 'align-wide' );

        // Add support for editor styles.
        add_theme_support( 'editor-styles' );

        // Add support for responsive embedded content.
        add_theme_support( 'responsive-embeds' );

        remove_theme_support( 'widgets-block-editor' );

        add_image_size( 'weta-case-details', 1170, 600, [ 'center', 'center' ] );
    }
endif;
add_action( 'after_setup_theme', 'weta_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function weta_content_width() {
    // This variable is intended to be overruled from themes.
    // Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $GLOBALS['content_width'] = apply_filters( 'weta_content_width', 640 );
}
add_action( 'after_setup_theme', 'weta_content_width', 0 );



/**
 * Enqueue scripts and styles.
 */

define( 'WETA_THEME_DIR', get_template_directory() );
define( 'WETA_THEME_URI', get_template_directory_uri() );
define( 'WETA_THEME_ASSETS', WETA_THEME_URI . '/assets/' );
define( 'WETA_THEME_CSS_DIR', WETA_THEME_URI . '/assets/css/' );
define( 'WETA_THEME_JS_DIR', WETA_THEME_URI . '/assets/js/' );
define( 'WETA_THEME_INC', WETA_THEME_DIR . '/inc/' );



// wp_body_open
if ( !function_exists( 'wp_body_open' ) ) {
    function wp_body_open() {
        do_action( 'wp_body_open' );
    }
}

/**
 * Implement the Custom Header feature.
 */
require WETA_THEME_INC . 'custom-header.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require WETA_THEME_INC . 'template-functions.php';

/**
 * Custom template helper function for this theme.
 */
require WETA_THEME_INC . 'template-helper.php';

/**
 * initialize kirki customizer class.
 */
include_once WETA_THEME_INC . 'kirki-customizer.php';
include_once WETA_THEME_INC . 'class-weta-kirki.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
    require WETA_THEME_INC . 'jetpack.php';
}

/**
 * include weta functions file
 */
require_once WETA_THEME_INC . 'class-navwalker.php';
require_once WETA_THEME_INC . 'class-tgm-plugin-activation.php';
require_once WETA_THEME_INC . 'add_plugin.php';
require_once WETA_THEME_INC . '/common/weta-breadcrumb.php';
require_once WETA_THEME_INC . '/common/weta-scripts.php';
require_once WETA_THEME_INC . '/common/weta-widgets.php';


/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function weta_pingback_header() {
    if ( is_singular() && pings_open() ) {
        printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
    }
}
add_action( 'wp_head', 'weta_pingback_header' );

// change textarea position in comment form
// ----------------------------------------------------------------------------------------
function weta_move_comment_textarea_to_bottom( $fields ) {
    $comment_field       = $fields[ 'comment' ];
    unset( $fields[ 'comment' ] );
    $fields[ 'comment' ] = $comment_field;
    return $fields;
}
add_filter( 'comment_form_fields', 'weta_move_comment_textarea_to_bottom' );


// weta_comment
if ( !function_exists( 'weta_comment' ) ) {
    function weta_comment( $comment, $args, $depth ) {
        $GLOBAL['comment'] = $comment;
        extract( $args, EXTR_SKIP );
        $args['reply_text'] = 'Reply';
        $replayClass = 'comment-depth-' . esc_attr( $depth );
        ?>
            <li id="comment-<?php comment_ID();?>">
                <div class="author-img">
                    <figure class="media">
                        <?php print get_avatar( $comment, 60, null, null, [ 'class' => [] ] );?>
                    </figure>
                </div>

                <div class="author-info">
                    <h3 class="name"><?php print get_comment_author_link();?></h3>
                    <p class="date"><?php comment_time( get_option( 'date_format' ) );?></p>
                    <div class="text">
                        <p><?php comment_text();?></p>
                        <?php comment_reply_link( array_merge( $args, [ 'depth' => $depth, 'max_depth' => $args['max_depth'] ] ) );?>
                    </div>
                </div>
            </li>
        <?php
    }
    function weta_comment_reply_link_class( $class ) {
        $class = str_replace( "class='comment-reply-link", "class='reply", $class );
        return $class;
    }

    add_filter( 'comment_reply_link', 'weta_comment_reply_link_class' );
}


/**
 * shortcode supports for removing extra p, spance etc
 *
 */
add_filter( 'the_content', 'weta_shortcode_extra_content_remove' );
/**
 * Filters the content to remove any extra paragraph or break tags
 * caused by shortcodes.
 *
 * @since 1.0.0
 *
 * @param string $content  String of HTML content.
 * @return string $content Amended string of HTML content.
 */
function weta_shortcode_extra_content_remove( $content ) {

    $array = [
        '<p>['    => '[',
        ']</p>'   => ']',
        ']<br />' => ']',
    ];
    return strtr( $content, $array );

}

// weta_search_filter_form
if ( !function_exists( 'weta_search_filter_form' ) ) {
    function weta_search_filter_form( $form ) {

        $form = sprintf(
            '<div class="sidebar__search"><form class="sidebar__search-form" action="%s" method="get">
      	<input type="search" value="%s" required name="s" placeholder="%s">
      	<button type="submit"> <i class="fal fa-search"></i>  </button>
		</form></div>',
            esc_url( home_url( '/' ) ),
            esc_attr( get_search_query() ),
            esc_html__( 'Search here', 'weta' )
        );

        return $form;
    }
    add_filter( 'get_search_form', 'weta_search_filter_form' );
}

add_action( 'admin_enqueue_scripts', 'weta_admin_custom_scripts' );

function weta_admin_custom_scripts() {
    wp_enqueue_media();
    wp_enqueue_style( 'customizer-style', get_template_directory_uri() . '/inc/css/customizer-style.css',array());
    wp_enqueue_script( 'weta-admin-custom', get_template_directory_uri() . '/inc/js/admin_custom.js', [ 'jquery' ], '', true );
    wp_enqueue_script( 'weta-admin-custom' );
}