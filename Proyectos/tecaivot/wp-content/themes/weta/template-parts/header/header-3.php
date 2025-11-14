<?php

	/**
	 * Template part for displaying header layout two
	 *
	 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
	 *
	 * @package weta
	*/

    // Header Button 
    $weta_header_right = get_theme_mod( 'weta_header_right', false );
    $weta_header_03_button_text = get_theme_mod( 'weta_header_03_button_text', __( 'GET A FREE QUOTE', 'weta' ) );
    $weta_header_03_button_link = get_theme_mod( 'weta_header_03_button_link', __( '#', 'weta' ) );

    // Enable One Page Nav 
    $enable_onepage_nav_menu = function_exists('get_field') ? get_field('enable_onepage_nav_menu') : NULL;
?>

<!-- Header area start -->
<header>
    <div id="header-sticky" class="header__area header-2">
        <div class="container">
            <div class="mega__menu-wrapper p-relative">
                <div class="header__main">
                    <div class="header__logo">
                        <?php weta_header_03_logo();?>
                    </div>

                    <div class="mean__menu-wrapper d-none d-lg-block">
                        <div class="main-menu main-menu-2" id="mobile-menu">
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
                    <div class="header__right">
                        <div class="header__action d-flex align-items-center">
                            <div class="header__btn-wrap d-none d-sm-inline-flex">
                                <a href="<?php echo esc_url($weta_header_03_button_link); ?>" class="rr-btn rr-btn__theme-1 rr-btn__theme-1-sm">
                                    <span class="btn-wrap">
                                        <span class="text-one"><?php echo esc_html($weta_header_03_button_text); ?>
                                        <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.38575 8.93578H4.03817L4.27661 9.18868L8.61139 13.7864C8.61139 13.7864 8.6114 13.7864 8.61141 13.7864C8.69179 13.8717 8.75598 13.9735 8.79993 14.086C8.84388 14.1986 8.8666 14.3196 8.8666 14.442C8.8666 14.5643 8.84388 14.6853 8.79993 14.7979C8.75598 14.9105 8.69177 15.0122 8.61139 15.0975C8.53102 15.1828 8.43609 15.2499 8.33227 15.2956C8.22848 15.3412 8.11758 15.3645 8.00582 15.3645C7.89406 15.3645 7.78316 15.3412 7.67937 15.2956C7.57556 15.2499 7.48063 15.1828 7.40026 15.0975L1.34324 8.67011L1.34311 8.66997C1.26262 8.58476 1.19831 8.48307 1.15429 8.37049C1.11026 8.2579 1.0875 8.13692 1.0875 8.01455C1.0875 7.89217 1.11026 7.77119 1.15429 7.6586C1.19831 7.54603 1.26262 7.44433 1.34311 7.35912L1.34324 7.35898L7.40026 0.931574C7.56247 0.759446 7.7805 0.664545 8.00582 0.664545C8.23115 0.664545 8.44918 0.759446 8.61139 0.931574C8.77388 1.104 8.8666 1.33967 8.8666 1.58713C8.8666 1.83459 8.77389 2.07024 8.61141 2.24267C8.6114 2.24268 8.61139 2.24269 8.61139 2.2427L4.27661 6.84041L4.03817 7.09331H4.38575L16.0818 7.09331C16.3068 7.09331 16.5245 7.18807 16.6865 7.35994C16.8488 7.53212 16.9413 7.76744 16.9413 8.01455C16.9413 8.26165 16.8488 8.49698 16.6865 8.66915C16.5245 8.84102 16.3068 8.93578 16.0818 8.93578H4.38575Z" fill="#010915" stroke="#F3B351" stroke-width="0.3"/>
                                        </svg></span>

                                        <span class="text-two"><?php echo esc_html($weta_header_03_button_text); ?> <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.38575 8.93578H4.03817L4.27661 9.18868L8.61139 13.7864C8.61139 13.7864 8.6114 13.7864 8.61141 13.7864C8.69179 13.8717 8.75598 13.9735 8.79993 14.086C8.84388 14.1986 8.8666 14.3196 8.8666 14.442C8.8666 14.5643 8.84388 14.6853 8.79993 14.7979C8.75598 14.9105 8.69177 15.0122 8.61139 15.0975C8.53102 15.1828 8.43609 15.2499 8.33227 15.2956C8.22848 15.3412 8.11758 15.3645 8.00582 15.3645C7.89406 15.3645 7.78316 15.3412 7.67937 15.2956C7.57556 15.2499 7.48063 15.1828 7.40026 15.0975L1.34324 8.67011L1.34311 8.66997C1.26262 8.58476 1.19831 8.48307 1.15429 8.37049C1.11026 8.2579 1.0875 8.13692 1.0875 8.01455C1.0875 7.89217 1.11026 7.77119 1.15429 7.6586C1.19831 7.54603 1.26262 7.44433 1.34311 7.35912L1.34324 7.35898L7.40026 0.931574C7.56247 0.759446 7.7805 0.664545 8.00582 0.664545C8.23115 0.664545 8.44918 0.759446 8.61139 0.931574C8.77388 1.104 8.8666 1.33967 8.8666 1.58713C8.8666 1.83459 8.77389 2.07024 8.61141 2.24267C8.6114 2.24268 8.61139 2.24269 8.61139 2.2427L4.27661 6.84041L4.03817 7.09331H4.38575L16.0818 7.09331C16.3068 7.09331 16.5245 7.18807 16.6865 7.35994C16.8488 7.53212 16.9413 7.76744 16.9413 8.01455C16.9413 8.26165 16.8488 8.49698 16.6865 8.66915C16.5245 8.84102 16.3068 8.93578 16.0818 8.93578H4.38575Z" fill="#010915" stroke="#F3B351" stroke-width="0.3"/>
                                        </svg></span>
                                    </span>
                                </a>
                            </div>

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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Header area end -->


<?php get_template_part( 'template-parts/header/header-side-info' ); ?>