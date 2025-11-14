<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package weta
 */

/**
 *
 * weta header
 */

function weta_check_header() {
    $weta_header_style = function_exists( 'get_field' ) ? get_field( 'header_style' ) : NULL;
    $weta_default_header_style = get_theme_mod( 'choose_default_header', 'header-style-1' );

    if ( $weta_header_style == 'header-style-1' && empty($_GET['s']) ) {
        get_template_part( 'template-parts/header/header-1' );
    }
    elseif ( $weta_header_style == 'header-style-2' && empty($_GET['s']) ) {
        get_template_part( 'template-parts/header/header-2' );
    }
    elseif ( $weta_header_style == 'header-style-3' && empty($_GET['s']) ) {
        get_template_part( 'template-parts/header/header-3' );
    }
    elseif ( $weta_header_style == 'header-style-4' && empty($_GET['s']) ) {
        get_template_part( 'template-parts/header/header-4' );
    } else {

        /** default header style **/
        if ( $weta_default_header_style == 'header-style-2' ) {
            get_template_part( 'template-parts/header/header-2' );
        }
        elseif ( $weta_default_header_style == 'header-style-3' ) {
            get_template_part( 'template-parts/header/header-3' );
        }
        elseif ( $weta_default_header_style == 'header-style-4' ) {
            get_template_part( 'template-parts/header/header-4' );
        } else {
            get_template_part( 'template-parts/header/header-1' );
        }
    }

}
add_action( 'weta_header_style', 'weta_check_header', 10 );


/**
 * [weta_header_lang description]
 * @return [type] [description]
 */
function weta_header_lang_default() {
    $weta_header_lang = get_theme_mod( 'weta_header_lang', false );
    if ( $weta_header_lang ): ?>

    <ul>
        <li><a href="javascript:void(0)"><?php print esc_html__( 'English', 'weta' );?> <i class="fas fa-angle-down"></i></a>
        <?php do_action( 'weta_language' );?>
        </li>
    </ul>

    <?php endif;?>
<?php
}

/**
 * [weta_language_list description]
 * @return [type] [description]
 */
function _weta_language( $mar ) {
    return $mar;
}
function weta_language_list() {

    $mar = '';
    $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
    if ( !empty( $languages ) ) {
        $mar = '<ul>';
        foreach ( $languages as $lan ) {
            $active = $lan['active'] == 1 ? 'active' : '';
            $mar .= '<li class="' . $active . '"><a href="' . $lan['url'] . '">' . $lan['translated_name'] . '</a></li>';
        }
        $mar .= '</ul>';
    } else {
        //remove this code when send themeforest reviewer team
        $mar .= '<ul>';
        $mar .= '<li><a href="#">' . esc_html__( 'English', 'weta' ) . '</a></li>';
        $mar .= '<li><a href="#">' . esc_html__( 'Dutch', 'weta' ) . '</a></li>';
        $mar .= '<li><a href="#">' . esc_html__( 'French', 'weta' ) . '</a></li>';
        $mar .= '<li><a href="#">' . esc_html__( 'Hindi', 'weta' ) . '</a></li>';
        $mar .= ' </ul>';
    }
    print _weta_language( $mar );
}
add_action( 'weta_language', 'weta_language_list' );


// header logo
function weta_header_logo() { ?>
        <?php
        $weta_logo_on = function_exists( 'get_field' ) ? get_field( 'is_enable_sec_logo' ) : NULL;
        $weta_logo = get_template_directory_uri() . '/assets/imgs/logo/logo-5.svg';
        $weta_logo_black = get_template_directory_uri() . '/assets/imgs/logo/logo-white.svg';

        $weta_site_logo = get_theme_mod( 'logo', $weta_logo );
        $weta_secondary_logo = get_theme_mod( 'seconday_logo', $weta_logo_black );
        ?>

        <?php if ( !empty( $weta_logo_on ) ) : ?>
            <a href="<?php print esc_url( home_url( '/' ) );?>">
                <img src="<?php print esc_url( $weta_secondary_logo );?>" alt="<?php print esc_attr__( 'logo', 'weta' );?>">
            </a>
        <?php else : ?>
            <a href="<?php print esc_url( home_url( '/' ) );?>">
                <img src="<?php print esc_url( $weta_site_logo );?>" alt="<?php print esc_attr__( 'logo', 'weta' );?>">
            </a>
        <?php endif; ?>
   <?php
}


// Header Style 02 Logo
function weta_header_02_logo() {?>
    <?php
        $weta_02_heaer_logo = get_template_directory_uri() . '/assets/imgs/logo/logo.svg';
        $weta_header_style_02_logo = get_theme_mod( 'header_style_02_logo', $weta_02_heaer_logo );
    ?>
        <a href="<?php print esc_url( home_url( '/' ) );?>">
            <img src="<?php print esc_url( $weta_header_style_02_logo );?>" alt="<?php print esc_attr__( 'logo', 'weta' );?>">
        </a>
    <?php
}


// Header Style 04 Logo
function weta_header_03_logo() {?>
    <?php
        $weta_03_heaer_logo = get_template_directory_uri() . '/assets/imgs/logo/logo-2.svg';
        $weta_header_style_03_logo = get_theme_mod( 'header_style_03_logo', $weta_03_heaer_logo );
    ?>
        <a href="<?php print esc_url( home_url( '/' ) );?>">
            <img src="<?php print esc_url( $weta_header_style_03_logo );?>" alt="<?php print esc_attr__( 'logo', 'weta' );?>">
        </a>
    <?php
}


// Weta Mobile Logo 
function weta_mobile_logo() {
    // side info
    $weta_mobile_logo_hide = get_theme_mod( 'weta_mobile_logo_hide', false );

    $weta_site_logo = get_theme_mod( 'logo', get_template_directory_uri() . '/assets/imgs/logo/logo-white.svg' );

    ?>

    <a href="<?php print esc_url( home_url( '/' ) );?>">
        <img src="<?php print esc_url( $weta_site_logo );?>" alt="<?php print esc_attr__( 'logo', 'weta' );?>" />
    </a>
<?php }


/**
 * [weta_header_social_profiles description]
 * @return [type] [description]
 */
function weta_header_social_profiles() {
    $weta_topbar_facebook_url = get_theme_mod( 'weta_topbar_facebook_url', __( '#', 'weta' ) );
    $weta_topbar_youtube_url = get_theme_mod( 'weta_topbar_youtube_url', __( '#', 'weta' ) );
    $weta_topbar_linkedin_url = get_theme_mod( 'weta_topbar_linkedin_url', __( '#', 'weta' ) );
    $weta_topbar_instagram_url = get_theme_mod( 'weta_topbar_instagram_url', __( '#', 'weta' ) );
    $weta_topbar_pinterest_url = get_theme_mod( 'weta_topbar_pinterest_url', __( '#', 'weta' ) );
    ?>
        <?php if ( !empty( $weta_topbar_facebook_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_topbar_facebook_url );?>"><i class="fa-brands fa-facebook-f"></i></a></li>
        <?php endif;?>

        <?php if ( !empty( $weta_topbar_linkedin_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_topbar_linkedin_url );?>"><i class="fa-brands fa-linkedin"></i></a></li>
        <?php endif;?>        

        <?php if ( !empty( $weta_topbar_instagram_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_topbar_instagram_url );?>"><i class="fa-brands fa-instagram"></i></a></li>
        <?php endif;?>

        <?php if ( !empty( $weta_topbar_youtube_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_topbar_youtube_url );?>"><i class="fab fa-youtube"></i></a></li>
        <?php endif;?>     
           
        <?php if ( !empty( $weta_topbar_pinterest_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_topbar_pinterest_url );?>"><i class="fa-brands fa-pinterest-p"></i></a></li>
        <?php endif;?>
    <?php
}

/**
 * [weta_header_menu description]
 * @return [type] [description]
 */
function weta_header_menu() {
    ?>
    <?php
        wp_nav_menu( [
            'theme_location' => 'main-menu',
            'menu_class'     => 'main-menu__list',
            'container'      => '',
            'fallback_cb'    => 'Weta_Navwalker_Class::fallback',
            'walker'         => new Weta_Navwalker_Class,
        ] );
    ?>
    <?php
}


/** 
 * [weta_header_onepage_nav_menu description]
 * @return [type] [description]
 */
function weta_onepage_nav_menu()
{ ?>
    <?php
    wp_nav_menu(array(
        'theme_location'    => 'onepage-nav-menu',
        'menu_class'        => 'mobile_one_page',
        'container'         => '',
        'fallback_cb'       => 'Navwalker_Class::fallback',
        'walker'            => new Weta_Navwalker_Class
    ));
    ?>
<?php
}


/**
 * [weta_header_menu description]
 * @return [type] [description]
 */
function weta_mobile_menu() {
    ?>
    <?php
        $weta_menu = wp_nav_menu( [
            'theme_location' => 'main-menu',
            'menu_class'     => '',
            'container'      => '',
            'menu_id'        => 'mobile-menu-active',
            'echo'           => false,
        ] );

    $weta_menu = str_replace( "menu-item-has-children", "menu-item-has-children has-children", $weta_menu );
        echo wp_kses_post( $weta_menu );
    ?>
    <?php
}


/**
 * [weta_footer_bottom_menu description]
 * @return [type] [description]
 */
function weta_footer_bottom_menu() {
    ?>
    <?php
        wp_nav_menu( [
            'theme_location' => 'footer-bottom-menu',
            'menu_class'     => 'footer-3__top-menu',
            'container'      => '',
            'fallback_cb'    => 'Navwalker_Class::fallback',
            'walker'         => new Weta_Navwalker_Class,
        ] );
    ?>
    <?php
}

/**
 * [weta_footer_bottom_menu_02 description]
 * @return [type] [description]
 */
function weta_footer_copyright_menu() {
    ?>
    <?php
        wp_nav_menu( [
            'theme_location' => 'footer-copyright-menu',
            'menu_class'     => 'footer__copyright-menu',
            'container'      => '',
            'fallback_cb'    => 'Navwalker_Class::fallback',
            'walker'         => new Weta_Navwalker_Class,
        ] );
    ?>
    <?php
}



/**
 *
 * weta footer
 */
add_action( 'weta_footer_style', 'weta_check_footer', 10 );

function weta_check_footer() {
    $weta_footer_style = function_exists( 'get_field' ) ? get_field( 'footer_style' ) : NULL;
    $weta_default_footer_style = get_theme_mod( 'choose_default_footer', 'footer-style-1' );

    if ( $weta_footer_style == 'footer-style-1' ) {
        get_template_part( 'template-parts/footer/footer-1' );
    }
    elseif ( $weta_footer_style == 'footer-style-2' ) {
        get_template_part( 'template-parts/footer/footer-2' );
    }
    elseif ( $weta_footer_style == 'footer-style-3' ) {
        get_template_part( 'template-parts/footer/footer-3' );
    } 
    elseif ( $weta_footer_style == 'footer-style-4' ) {
        get_template_part( 'template-parts/footer/footer-4' );
    } else {

        /** default footer style **/
        if ( $weta_default_footer_style == 'footer-style-2' ) {
            get_template_part( 'template-parts/footer/footer-2' );
        }
        elseif ( $weta_default_footer_style == 'footer-style-3' ) {
            get_template_part( 'template-parts/footer/footer-3' );
        } 
        elseif ( $weta_default_footer_style == 'footer-style-4' ) {
            get_template_part( 'template-parts/footer/footer-4' );
        } else {
            get_template_part( 'template-parts/footer/footer-1' );
        }
    }
}


// weta_copyright_text
function weta_copyright_text() {
   print get_theme_mod( 'weta_copyright', esc_html__( 'Copyright © 2024 Weta All Rights Reserved.', 'weta' ) );
}


/**
 * [weta_footer_social_profiles description]
 * @return [type] [description]
 */
function weta_footer_social_profiles() {
    $weta_footer_fb_url = get_theme_mod( 'weta_footer_fb_url', __( '#', 'weta' ) );
    $weta_footer_twitter_url = get_theme_mod( 'weta_footer_twitter_url', __( '#', 'weta' ) );
    $weta_footer_youtube_url = get_theme_mod( 'weta_footer_youtube_url', __( '#', 'weta' ) );
    $weta_footer_linkedin_url = get_theme_mod( 'weta_footer_linkedin_url', __( '#', 'weta' ) );
    $weta_footer_instagram_url = get_theme_mod( 'weta_footer_instagram_url', __( '#', 'weta' ) );
    ?>
        <?php if ( !empty( $weta_footer_fb_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_footer_fb_url );?>"><i class="fa-brands fa-facebook-f"></i></a></li>
        <?php endif;?>

        <?php if ( !empty( $weta_footer_twitter_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_footer_twitter_url );?>"><i class="fa-brands fa-twitter"></i></a></li>
        <?php endif;?>

        <?php if ( !empty( $weta_footer_linkedin_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_footer_linkedin_url );?>"><i class="fa-brands fa-linkedin"></i></a></li>
        <?php endif;?>        

        <?php if ( !empty( $weta_footer_instagram_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_footer_instagram_url );?>"><i class="fa-brands fa-instagram"></i></a></li>
        <?php endif;?>

        <?php if ( !empty( $weta_footer_youtube_url ) ): ?>
            <li><a href="<?php print esc_url( $weta_footer_youtube_url );?>"><i class="fab fa-youtube"></i></a></li>
        <?php endif;?>
    <?php
}


/**
 *
 * pagination
 */
if ( !function_exists( 'weta_pagination' ) ) {

    function _weta_pagi_callback( $pagination ) {
        return $pagination;
    }

    //page navegation
    function weta_pagination( $prev, $next, $pages, $args ) {
        global $wp_query, $wp_rewrite;
        $menu = '';
        $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

        if ( $pages == '' ) {
            global $wp_query;
            $pages = $wp_query->max_num_pages;

            if ( !$pages ) {
                $pages = 1;
            }

        }

        $pagination = [
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'total'     => $pages,
            'current'   => $current,
            'prev_text' => $prev,
            'next_text' => $next,
            'type'      => 'array',
        ];

        //rewrite permalinks
        if ( $wp_rewrite->using_permalinks() ) {
            $pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
        }

        if ( !empty( $wp_query->query_vars['s'] ) ) {
            $pagination['add_args'] = ['s' => get_query_var( 's' )];
        }

        $pagi = '';
        if ( paginate_links( $pagination ) != '' ) {
            $paginations = paginate_links( $pagination );
            $pagi .= '<ul>';
            foreach ( $paginations as $key => $pg ) {
                $pagi .= '<li>' . $pg . '</li>';
            }
            $pagi .= '</ul>';
        }

        print _weta_pagi_callback( $pagi );
    }
}

// theme color
function weta_custom_color() {
    $weta_color_option_prim = get_theme_mod( 'weta_color_option_prim', '#3C66FA' );
    $weta_color_option_prim1 = get_theme_mod( 'weta_color_option_prim1', '#F3B351' );
    $weta_color_option_prim2 = get_theme_mod( 'weta_color_option_prim2', '#7341F1' );
    $weta_color_option_heading_primary = get_theme_mod( 'weta_color_option_heading_primary', '#010915' );
    $weta_color_option_heading_secondary = get_theme_mod( 'weta_color_option_heading_secondary', '#0B1728' );
    $weta_color_option_text_prim = get_theme_mod( 'weta_color_option_text_prim', '#010915' );
    $weta_color_option_text_prim1 = get_theme_mod( 'weta_color_option_text_prim1', '#23232B' );
    $weta_color_option_text_prim2 = get_theme_mod( 'weta_color_option_text_prim2', '#818181' );
    $weta_color_option_text_prim3 = get_theme_mod( 'weta_color_option_text_prim3', '#84858D' );
    $weta_color_option_placeholder = get_theme_mod( 'weta_color_option_placeholder', '#4A5764' );
    $weta_color_option_selection = get_theme_mod( 'weta_color_option_selection', '#3C66FA' );
    $weta_color_option_body_prim = get_theme_mod( 'weta_color_option_body_prim', '#ffffff' );
    $weta_color_option_section1 = get_theme_mod( 'weta_color_option_section1', '#E9EBF7' );
    $weta_color_option_section2 = get_theme_mod( 'weta_color_option_section2', '#F5F5F5' );
    $weta_color_option_section3 = get_theme_mod( 'weta_color_option_section3', '#F14141' );
    $weta_color_option_menu_prim = get_theme_mod( 'weta_color_option_menu_prim', '#888899' );
    $weta_color_option_body = get_theme_mod( 'weta_color_option_body', 'rgba(1, 9, 21, 0.6)' );
    $weta_color_option_black = get_theme_mod( 'weta_color_option_black', '#000000' );
    $weta_color_option_white = get_theme_mod( 'weta_color_option_white', '#FFFFFF' );
    $weta_color_option_border_prim = get_theme_mod( 'weta_color_option_border_prim', 'rgba(1, 9, 21, 0.14)' );

    wp_enqueue_style( 'weta-custom', WETA_THEME_CSS_DIR . 'weta-custom.css', [] );

    if ( !empty($weta_color_option_prim) || !empty($weta_color_option_prim1) || !empty($weta_color_option_prim2) || !empty($weta_color_option_heading_primary) || !empty($weta_color_option_heading_secondary) || !empty($weta_color_option_text_prim) || !empty($weta_color_option_text_prim1) || !empty($weta_color_option_text_prim2) || !empty($weta_color_option_text_prim3) || !empty($weta_color_option_placeholder) || !empty($weta_color_option_selection) || !empty($weta_color_option_body_prim) || !empty($weta_color_option_section1) || !empty($weta_color_option_section2) || !empty($weta_color_option_section3) || !empty($weta_color_option_menu_prim) || !empty($weta_color_option_body) || !empty($weta_color_option_black) || !empty($weta_color_option_white) || !empty($weta_color_option_border_prim)) {
        $custom_css = '';

        $custom_css .= "html:root{
          --rr-theme-primary: " . $weta_color_option_prim . ";
          --rr-theme-primary1: " . $weta_color_option_prim1 . ";
          --rr-theme-primary2: " . $weta_color_option_prim2 . ";
          --rr-heading-primary: " . $weta_color_option_heading_primary . ";
          --rr-heading-secondary: " . $weta_color_option_heading_secondary . ";
          --rr-text-primary: " . $weta_color_option_text_prim . ";
          --rr-text-primary1: " . $weta_color_option_text_prim1 . ";
          --rr-text-primary2: " . $weta_color_option_text_prim2 . ";
          --rr-text-primary3: " . $weta_color_option_text_prim3 . ";
          --rr-common-placeholder: " . $weta_color_option_placeholder . ";
          --rr-common-selection: " . $weta_color_option_selection . ";
          --rr-body-primary: " . $weta_color_option_body_prim . ";
          --rr-section-1: " . $weta_color_option_section1 . ";
          --rr-section-2: " . $weta_color_option_section2 . ";
          --rr-section-3: " . $weta_color_option_section3 . ";
          --rr-menu-primary: " . $weta_color_option_menu_prim . ";
          --rr-text-body: " . $weta_color_option_body . ";
          --rr-common-black: " . $weta_color_option_black . ";
          --rr-common-white: " . $weta_color_option_white . ";
          --rr-border-primary: 1px solid " . $weta_color_option_border_prim . ";
        }";

        $custom_css .= "body .footer__widget .title { color: " . $weta_color_option_black . "!important}";


        wp_add_inline_style( 'weta-custom', $custom_css );
    }
}
add_action( 'wp_enqueue_scripts', 'weta_custom_color' );

// weta_kses_intermediate
function weta_kses_intermediate( $string = '' ) {
    return wp_kses( $string, weta_get_allowed_html_tags( 'intermediate' ) );
}

function weta_get_allowed_html_tags( $level = 'basic' ) {
    $allowed_html = [
        'b'      => [],
        'i'      => [],
        'u'      => [],
        'em'     => [],
        'br'     => [],
        'abbr'   => [
            'title' => [],
        ],
        'span'   => [
            'class' => [],
        ],
        'strong' => [],
        'a'      => [
            'href'  => [],
            'title' => [],
            'class' => [],
            'id'    => [],
        ],
    ];

    if ($level === 'intermediate') {
        $allowed_html['a'] = [
            'href' => [],
            'title' => [],
            'class' => [],
            'id' => [],
        ];
        $allowed_html['div'] = [
            'class' => [],
            'id' => [],
        ];
        $allowed_html['img'] = [
            'src' => [],
            'class' => [],
            'alt' => [],
        ];
        $allowed_html['del'] = [
            'class' => [],
        ];
        $allowed_html['ins'] = [
            'class' => [],
        ];
        $allowed_html['bdi'] = [
            'class' => [],
        ];
        $allowed_html['i'] = [
            'class' => [],
            'data-rating-value' => [],
        ];
    }

    return $allowed_html;
}


// WP kses allowed tags
// ----------------------------------------------------------------------------------------
function weta_kses($raw){

   $allowed_tags = array(
      'a'                         => array(
         'class'   => array(),
         'href'    => array(),
         'rel'  => array(),
         'title'   => array(),
         'target' => array(),
      ),
      'abbr'                      => array(
         'title' => array(),
      ),
      'b'                         => array(),
      'blockquote'                => array(
         'cite' => array(),
      ),
      'cite'                      => array(
         'title' => array(),
      ),
      'code'                      => array(),
      'del'                    => array(
         'datetime'   => array(),
         'title'      => array(),
      ),
      'dd'                     => array(),
      'div'                    => array(
         'class'   => array(),
         'title'   => array(),
         'style'   => array(),
      ),
      'dl'                     => array(),
      'dt'                     => array(),
      'em'                     => array(),
      'h1'                     => array(),
      'h2'                     => array(),
      'h3'                     => array(),
      'h4'                     => array(),
      'h5'                     => array(),
      'h6'                     => array(),
      'i'                         => array(
         'class' => array(),
      ),
      'img'                    => array(
         'alt'  => array(),
         'class'   => array(),
         'height' => array(),
         'src'  => array(),
         'width'   => array(),
      ),
      'li'                     => array(
         'class' => array(),
      ),
      'ol'                     => array(
         'class' => array(),
      ),
      'p'                         => array(
         'class' => array(),
      ),
      'q'                         => array(
         'cite'    => array(),
         'title'   => array(),
      ),
      'span'                      => array(
         'class'   => array(),
         'title'   => array(),
         'style'   => array(),
      ),
      'iframe'                 => array(
         'width'         => array(),
         'height'     => array(),
         'scrolling'     => array(),
         'frameborder'   => array(),
         'allow'         => array(),
         'src'        => array(),
      ),
      'strike'                 => array(),
      'br'                     => array(),
      'strong'                 => array(),
      'data-wow-duration'            => array(),
      'data-wow-delay'            => array(),
      'data-wallpaper-options'       => array(),
      'data-stellar-background-ratio'   => array(),
      'ul'                     => array(
         'class' => array(),
      ),
      'svg' => array(
           'class' => true,
           'aria-hidden' => true,
           'aria-labelledby' => true,
           'role' => true,
           'xmlns' => true,
           'width' => true,
           'height' => true,
           'viewbox' => true, // <= Must be lower case!
       ),
       'g'     => array( 'fill' => true ),
       'title' => array( 'title' => true ),
       'path'  => array( 'd' => true, 'fill' => true,  ),
      );

   if (function_exists('wp_kses')) { // WP is here
      $allowed = wp_kses($raw, $allowed_tags);
   } else {
      $allowed = $raw;
   }

   return $allowed;
}