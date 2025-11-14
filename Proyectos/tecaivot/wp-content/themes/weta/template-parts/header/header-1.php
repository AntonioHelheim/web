<?php

    /**
     * Template part for displaying header layout one
     *
     * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
     *
     * @package weta
    */
    $weta_topbar_switch = get_theme_mod('weta_topbar_switch', false);

    // Header Topbar Slide Text 
    $weta_topbar_text_01 = get_theme_mod( 'weta_topbar_text_01', __( 'Create an account to avail a 50% bonus discount at checkout.', 'weta' ) );
    $weta_topbar_text_02 = get_theme_mod( 'weta_topbar_text_02', __( 'Create an account to avail a 50% bonus discount at checkout.', 'weta' ) );

    // Topbar Slider Button 
    $weta_topbar_slide_button_text = get_theme_mod( 'weta_topbar_slide_button_text', __( 'Learn More', 'weta' ) );
    $weta_topbar_slide_button_link = get_theme_mod( 'weta_topbar_slide_button_link', __( '#', 'weta' ) );

    // Topbar Slider Button 02
    $weta_topbar_slide_button_text_02 = get_theme_mod( 'weta_topbar_slide_button_text_02', __( 'Learn More', 'weta' ) );
    $weta_topbar_slide_button_link_02 = get_theme_mod( 'weta_topbar_slide_button_link_02', __( '#', 'weta' ) );

    // Header Button 
    $weta_header_right = get_theme_mod( 'weta_header_right', false );
    $weta_button_text = get_theme_mod( 'weta_button_text', __( 'Get Started', 'weta' ) );
    $weta_button_link = get_theme_mod( 'weta_button_link', __( '#', 'weta' ) );

    // Login Button 
    $weta_login_button_text = get_theme_mod( 'weta_login_button_text', __( 'Login', 'weta' ) );
    $weta_login_button_link = get_theme_mod( 'weta_login_button_link', __( '#', 'weta' ) );


    // Enable One Page Nav 
    $enable_onepage_nav_menu = function_exists('get_field') ? get_field('enable_onepage_nav_menu') : NULL;
?>

    <!-- Header area start -->
    <header>
        <?php if ( !empty( $weta_topbar_switch ) ): ?>
        <div class="header-3_top d-none d-sm-block">
            <div class="container">
                <div class="swiper header-3_top-slider">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="header-3_top-slider__item">
                                <?php if ( !empty( $weta_topbar_text_01 ) ): ?>
                                <p>
                                    <?php echo esc_html($weta_topbar_text_01); ?> 

                                    <?php if ( !empty( $weta_topbar_slide_button_text ) ): ?>
                                    <a href="<?php echo esc_url($weta_topbar_slide_button_link); ?>"><?php echo esc_html($weta_topbar_slide_button_text); ?> 
                                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L5 5L1 9" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="header-3_top-slider__item">
                                <?php if ( !empty( $weta_topbar_text_02 ) ): ?>
                                <p>
                                    <?php echo esc_html($weta_topbar_text_02); ?> 

                                    <?php if ( !empty( $weta_topbar_slide_button_text_02 ) ): ?>
                                    <a href="<?php echo esc_url($weta_topbar_slide_button_link_02); ?>"><?php echo esc_html($weta_topbar_slide_button_text_02); ?> 
                                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 1L5 5L1 9" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="header-3_top-slider__controller d-flex">
                        <button class="header-3_top-slider__arrow-prev">
                            <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 9L1.70707 5.70707C1.31818 5.31818 1.31818 4.68182 1.70707 4.29293L5 1" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button class="header-3_top-slider__arrow-next">
                            <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 9L4.29293 5.70707C4.68182 5.31818 4.68182 4.68182 4.29293 4.29293L1 1" stroke="white" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div id="header-sticky" class="header__area header-3">
            <div class="container">
                <div class="mega__menu-wrapper p-relative">
                    <div class="header-3__main">
                        <div class="header__logo">
                            <?php weta_header_logo();?>
                        </div>

                        <div class="mean__menu-wrapper d-none d-lg-block">
                            <div class="main-menu main-menu-3" id="mobile-menu">
                                <?php
                                    if ($enable_onepage_nav_menu) {
                                        weta_onepage_nav_menu();
                                    } else {
                                        weta_header_menu();
                                    }
                                ?>
                            </div>
                        </div>

                        <?php if ( !empty( $weta_header_right ) ): ?>
                        <div class="header__right d-none d-lg-block">
                            <div class="header__action d-flex align-items-center">

                                <div class="header__btn-wrap d-none d-sm-inline-flex">
                                    <?php if ( !empty( $weta_login_button_text ) ): ?>
                                    <a href="<?php echo esc_url($weta_login_button_link); ?>" class="rr-btn__login">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="15" viewBox="0 0 13 15" fill="none">
                                            <path d="M6.15454 7C7.8114 7 9.15454 5.65685 9.15454 4C9.15454 2.34315 7.8114 1 6.15454 1C4.49769 1 3.15454 2.34315 3.15454 4C3.15454 5.65685 4.49769 7 6.15454 7Z" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.308 14.2C11.308 11.878 8.998 10 6.154 10C3.31 10 1 11.878 1 14.2" stroke="#010915" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg> <?php echo esc_html($weta_login_button_text); ?>
                                    </a>
                                    <?php endif; ?>

                                    <?php if ( !empty( $weta_button_text ) ): ?>
                                    <a href="<?php echo esc_url($weta_button_link); ?>" class="rr-btn rr-btn__theme-4">
                                        <span class="btn-wrap">
                                            <span class="text-one"><?php echo esc_html($weta_button_text); ?></span>
                                            <span class="text-two"><?php echo esc_html($weta_button_text); ?></span>
                                        </span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="header__hamburger ml-20 d-lg-none">
                            <div class="sidebar__toggle">
                                <a class="bar-icon" href="avascript:void(0)">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Header area end -->
    
<?php get_template_part( 'template-parts/header/header-side-info' ); ?>