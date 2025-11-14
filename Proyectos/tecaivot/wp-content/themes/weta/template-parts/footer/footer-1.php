<?php

/**
 * Template part for displaying footer layout one
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package weta
*/

$weta_footer_logo = get_theme_mod( 'weta_footer_logo' );
$weta_footer_logo = get_theme_mod( 'weta_footer_logo', get_template_directory_uri() . '/assets/imgs/logo/logo-4.svg' );
$weta_default_footer_top_switch = get_theme_mod('weta_default_footer_top_switch', false);
$weta_footer_bottom_menu_switch = get_theme_mod('weta_footer_bottom_menu_switch', false);
$footer_top_space = get_theme_mod( 'weta_footer_top_space' );
$footer_bg_color = get_theme_mod( 'weta_footer_bg_color' );

$footer_newsletter_heading = get_theme_mod( 'footer_newsletter_heading', esc_html__( 'Newsletter Subscribe', 'weta' ) );
$footer_newsletter_subheading = get_theme_mod( 'footer_newsletter_heading', esc_html__( 'Subscribe Our Newsletter For Get More Update', 'weta' ) );
$footer_newsletter_shortcode = get_theme_mod( 'footer_newsletter_shortcode' );


// footer_columns
$footer_columns = 0;
$footer_widgets = get_theme_mod( 'footer_widget_number', 5 );

for ( $num = 1; $num <= $footer_widgets; $num++ ) {
    if ( is_active_sidebar( 'footer-' . $num ) ) {
        $footer_columns++;
    }
}

switch ( $footer_columns ) {
case '1':
    $footer_class[1] = 'col-lg-12';
    break;
case '2':
    $footer_class[1] = 'col-xl-6 col-lg-6 col-md-6';
    $footer_class[2] = 'col-xl-6 col-lg-6 col-md-6';
    break;
case '3':
    $footer_class[1] = 'col-xl-4 col-lg-6 col-md-6';
    $footer_class[2] = 'col-xl-4 col-lg-6 col-md-6';
    $footer_class[3] = 'col-xl-4 col-lg-6 col-md-6';
    break;
case '4':
    $footer_class[1] = 'col-xl-3 col-lg-3 col-md-6';
    $footer_class[2] = 'col-xl-3 col-lg-3 col-md-6';
    $footer_class[3] = 'col-xl-3 col-lg-3 col-md-6';
    $footer_class[4] = 'col-xl-3 col-lg-3 col-md-6';
    break;
case '5':
    $footer_class[1] = 'col-xl-4 col-lg-8 col-md-8 col-12';
    $footer_class[2] = 'col-xl-2 col-lg-4 col-md-4 col-12';
    $footer_class[3] = 'col-xl-2 col-lg-4 col-md-4 col-12';
    $footer_class[4] = 'col-xl-2 col-lg-4 col-md-4 col-12';
    $footer_class[5] = 'col-xl-2 col-lg-4 col-md-4 col-12';
    break;
default:
    $footer_class = 'col-xl-3 col-lg-3 col-md-6';
    break;
}

?>


<!-- Footer area start -->
<footer>
    <section class="footer-3__area-common theme-footer-bg-2 overflow-hidden" data-bg-color="<?php print esc_attr( $footer_bg_color );?>">
        <div class="container container-xxl">
            <?php if(!empty($weta_default_footer_top_switch)) : ?>
            <div class="row pt-80">
                <div class="col-12">
                    <div class="footer-3__top-wrapper d-flex flex-xl-row flex-column align-items-center justify-content-between theme-footer-bg-3 ">

                        <div class="footer-3__logo">
                            <a href="<?php print esc_url( home_url( '/' ) );?>">
                                <img src="<?php echo esc_url($weta_footer_logo); ?>" alt="<?php print esc_attr__('Footer Logo', 'weta'); ?>">
                            </a>
                        </div>

                        <?php if(!empty($weta_footer_bottom_menu_switch)) : ?>
                        <div class="footer-3__top-menu">
                            <?php weta_footer_bottom_menu(); ?>
                        </div>
                        <?php endif; ?>

                        <div class="footer-3__subscribe d-flex">
                            <input type="text" placeholder="Enter e-mail">
                            <button type="submit" class="rr-btn">
                                <span class="btn-wrap">
                                    <span class="text-one">Send <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.34302 13.2422L12.8283 4.75691" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4.34302 4.75684H12.8283V13.2421" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    </span>
                                    <span class="text-two">Send <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.34302 13.2422L12.8283 4.75691" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4.34302 4.75684H12.8283V13.2421" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar('footer-1') OR is_active_sidebar('footer-2') OR is_active_sidebar('footer-3') OR is_active_sidebar('footer-4') OR is_active_sidebar('footer-5') ): ?>
            <div class="row mb-20 mt-75 mt-sm-65 mt-xs-50">
                <?php
                    if ( $footer_columns > 5 ) {
                    print '<div class="col-xl-4 col-lg-8 col-md-8 col-12">';
                    dynamic_sidebar( 'footer-1' );
                    print '</div>';

                    print '<div class="col-xl-2 col-lg-4 col-md-4 col-12">';
                    dynamic_sidebar( 'footer-2' );
                    print '</div>';

                    print '<div class="col-xl-2 col-lg-4 col-md-4 col-12">';
                    dynamic_sidebar( 'footer-3' );
                    print '</div>';

                    print '<div class="col-xl-2 col-lg-4 col-md-4 col-12">';
                    dynamic_sidebar( 'footer-4' );
                    print '</div>';                    

                    print '<div class="col-xl-2 col-lg-4 col-md-4 col-12">';
                    dynamic_sidebar( 'footer-5' );
                    print '</div>';

                    ?>
                    </div></div>
                    <?php
                    } else {
                        for ( $num = 1; $num <= $footer_columns; $num++ ) {
                            if ( !is_active_sidebar( 'footer-' . $num ) ) {
                                continue;
                            }
                            print '<div class="' . esc_attr( $footer_class[$num] ) . '">';
                            dynamic_sidebar( 'footer-' . $num );
                            print '</div>';
                        }
                    }
                ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="wt-copyright-area coyright-bg">
            <div class="copyright-content">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <p><?php print weta_copyright_text(); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</footer>
<!-- Footer area end -->