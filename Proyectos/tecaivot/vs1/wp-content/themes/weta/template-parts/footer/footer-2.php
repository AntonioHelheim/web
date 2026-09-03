<?php

/**
 * Template part for displaying footer layout two
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package weta
*/

$footer_style_2_switch = get_theme_mod( 'footer_style_2_switch', true );

$weta_footer_02_logo = get_theme_mod( 'weta_footer_02_logo', get_template_directory_uri() . '/assets/img/logo/logo-secondary.png' );

$footer_style_2_shape_switch = get_theme_mod( 'footer_style_2_shape_switch', true );
$weta_footer_02_shape = get_theme_mod( 'weta_footer_02_shape', get_template_directory_uri() . '/assets/img/shapes/footer-shape.png' );

$weta_footer_bg_url_from_page = function_exists( 'get_field' ) ? get_field( 'weta_footer_bg' ) : '';
$weta_footer_bg_color_from_page = function_exists( 'get_field' ) ? get_field( 'weta_footer_bg_color' ) : '';
$footer_bg_color = get_theme_mod( 'weta_footer_bg_color' );


// bg color
$bg_color = !empty( $weta_footer_bg_color_from_page ) ? $weta_footer_bg_color_from_page : $footer_bg_color;

$footer_columns = 0;
$footer_widgets = get_theme_mod( 'footer_widget_number', 5 );

for ( $num = 1; $num <= $footer_widgets; $num++ ) {
    if ( is_active_sidebar( 'footer-2-' . $num ) ) {
        $footer_columns++;
    }
}

switch ( $footer_columns ) {
case '1':
    $footer_class[1] = 'col-lg-12';
    break;
case '2':
    $footer_class[1] = 'col-lg-6 col-md-6 wow fadeInUp';
    $footer_class[2] = 'col-lg-6 col-md-6 wow fadeInUp';
    break;
case '3':
    $footer_class[1] = 'col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12 d-flex';
    $footer_class[2] = 'col-xl-6 col-lg-6 col-md-4 col-sm-6 col-12 d-flex justify-content-center';
    $footer_class[3] = 'col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12 d-flex';
    break;
case '4':
    $footer_class[1] = 'col-xl-3 col-lg-3 col-md-6 col-12';
    $footer_class[2] = 'col-xl-3 col-lg-3 col-md-6 col-12';
    $footer_class[3] = 'col-xl-3 col-lg-3 col-md-6 col-12';
    $footer_class[4] = 'col-xl-3 col-lg-3 col-md-6 col-12';
    break;
case '5':
    $footer_class[1] = 'col-xl-3 col-lg-3';
    $footer_class[2] = 'col-lg-2 col-6';
    $footer_class[3] = 'col-lg-2 col-6';
    $footer_class[4] = 'col-lg-2 col-6';
    $footer_class[5] = 'col-lg-3 col-6';
    break;
default:
    $footer_class = 'col-xl-3 col-lg-3 col-md-6';
    break;
}

?>


<!-- Footer area start -->
<footer>
    <section class="footer__area-common weta-footer-style-02 theme-footer-bg-1 overflow-hidden">
        <div class="container">
            <?php if ( $footer_style_2_switch ) { if ( is_active_sidebar( 'footer-2-1' ) OR is_active_sidebar( 'footer-2-2' ) OR is_active_sidebar( 'footer-2-3' ) OR is_active_sidebar( 'footer-2-4' ) OR is_active_sidebar( 'footer-2-5' ) ): ?>
            <div class="row mb-minus-40">
                <?php if ( $footer_columns > 5 ) {
                    print '<div class="col-xl-3 col-lg-3">';
                    dynamic_sidebar( 'footer-2-1' );
                    print '</div>';

                    print '<div class="col-lg-2 col-6">';
                    dynamic_sidebar( 'footer-2-2' );
                    print '</div>';

                    print '<div class="col-lg-2 col-6">';
                    dynamic_sidebar( 'footer-2-3' );
                    print '</div>';

                    print '<div class="col-lg-2 col-6">';
                    dynamic_sidebar( 'footer-2-4' );
                    print '</div>';                

                    print '<div class="col-lg-3 col-6">';
                    dynamic_sidebar( 'footer-2-5' );
                    print '</div>';

                    } else {
                        for ( $num = 1; $num <= $footer_columns; $num++ ) {
                            if ( !is_active_sidebar( 'footer-2-' . $num ) ) {
                                continue;
                            }
                            print '<div class="' . esc_attr( $footer_class[$num] ) . '">';
                            dynamic_sidebar( 'footer-2-' . $num );
                            print '</div>';
                        }
                    }
                ?>
            </div>
            <?php endif;
                }
            ?>
        </div>

        <div class="footer__bottom-wrapper footer__bottom-1">
            <div class="container">
                <div class="footer__bottom">
                    <div class="footer__copyright">
                        <p><?php print weta_copyright_text(); ?></p>
                    </div>

                    <div class="footer__copyright-menu">
                        <?php weta_footer_copyright_menu(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</footer>
<!-- Footer area end -->