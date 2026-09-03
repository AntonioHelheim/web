<?php

	/**
	 * Template part for displaying header layout two
	 *
	 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
	 *
	 * @package weta
	*/

    $weta_search = get_theme_mod( 'weta_search', false );
    $weta_header_right = get_theme_mod( 'weta_header_right', false );

    // Sibde info Content 
    $weta_side_hide = get_theme_mod( 'weta_side_hide', false );
    $weta_side_social_hide = get_theme_mod( 'weta_side_social_hide', false );
    $weta_side_logo = get_theme_mod( 'weta_side_logo', get_template_directory_uri() . '/assets/img/logo/logo.png' );

    // Contact Title 
    $weta_extra_contact_title = get_theme_mod( 'weta_extra_contact_title', __( 'Contact Us', 'weta' ) );

    // Contact Info 
    $weta_extra_email = get_theme_mod( 'weta_extra_email', __( 'info@example.com', 'weta' ) );
    $weta_extra_email_link = get_theme_mod( 'weta_extra_email_link', __( 'info@example.com', 'weta' ) );

    $weta_extra_phone = get_theme_mod( 'weta_extra_phone', __( '+01569896654', 'weta' ) );
    $weta_extra_phone_url = get_theme_mod( 'weta_extra_phone_url', __( '+01569896654', 'weta' ) );

    $weta_extra_address = get_theme_mod( 'weta_extra_address', __( 'Amsterdam, 109-74', 'weta' ) );

    // About Info 
    $weta_extra_about_title = get_theme_mod( 'weta_extra_about_title', __( 'About Us', 'weta' ) );
    $weta_extra_about_description = get_theme_mod( 'weta_extra_about_description', __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud nisi ut aliquip ex ea commodo consequat.', 'weta' ) );
    $weta_extra_about_button_text = get_theme_mod( 'weta_extra_about_button_text', __( 'CONTACT US', 'weta' ) );
    $weta_extra_about_button_url = get_theme_mod( 'weta_extra_about_button_url', __( '#', 'weta' ) );


    // Enable One Page Nav 
    $enable_onepage_nav_menu = function_exists('get_field') ? get_field('enable_onepage_nav_menu') : NULL;
?>

    <header class="header sticky-active">
        <div class="primary-header">
            <div class="container">
                <div class="primary-header-inner">
                    <div class="header-logo d-lg-block">
                        <?php weta_header_logo();?>
                    </div>
                    <div class="header-menu-wrap">
                        <div class="mobile-menu-items sub-menu">
                            <?php
                                if ($enable_onepage_nav_menu) {
                                    weta_onepage_nav_menu();
                                } else {
                                    weta_header_menu();
                                }
                            ?>
                        </div>
                    </div>
                    <!-- /.header-menu-wrap -->

                    <div class="header-right">
                        <?php if ( !empty( $weta_search ) ): ?>
                        <div class="search-icon dl-search-icon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <?php endif; ?>

                        <div class="sidebar-icon">
                            <button class="sidebar-trigger open">
                                <span></span>
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                        <div class="header-logo d-none d-lg-none">
                            <?php weta_header_logo();?>
                        </div>
                        <div class="header-right-item">
                            <a href="javascript:void(0)" class="mobile-side-menu-toggle d-lg-none">
                                <i class="fa-sharp fa-solid fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    <!-- /.header-right -->
                </div>
                <!-- /.primary-header-inner -->
            </div>
        </div>
    </header>

    <div id="popup-search-box">
        <div class="box-inner-wrap d-flex align-items-center">
            <form id="form" action="<?php print esc_url( home_url( '/' ) );?>" method="get" role="search">
                <input id="popup-search" type="text" name="s" value="<?php print esc_attr( get_search_query() )?>" placeholder="<?php print esc_attr__( 'Type keywords here...', 'weta' );?>">
            </form>
            <div class="search-close"><i class="fa-sharp fa-regular fa-xmark"></i></div>
        </div>
    </div>

    <div class="mobile-side-menu">
        <div class="side-menu-content">
            <div class="side-menu-head">
                <?php weta_mobile_logo();?>
                <button class="mobile-side-menu-close"><i class="fa-regular fa-xmark"></i></button>
            </div>
            <div class="side-menu-wrap"></div>
         <?php if ( !empty( $weta_side_hide ) ): ?>
         <ul class="side-menu-list">
            <?php if ( !empty( $weta_extra_address ) ): ?>
            <li><i class="fa-light fa-location-dot"></i><?php print esc_html( 'Address :', 'weta' ); ?> <span><?php echo weta_kses($weta_extra_address); ?></span></li>
            <?php endif;?>

            <?php if ( !empty( $weta_extra_phone ) ): ?>
            <li><i class="fa-light fa-phone"></i><?php print esc_html( 'Phone :', 'weta' ); ?> <a href="<?php echo esc_attr($weta_extra_phone); ?>"><?php echo weta_kses($weta_extra_phone); ?></a></li>
            <?php endif;?>

            <?php if ( !empty( $weta_extra_email ) ): ?>
            <li><i class="fa-light fa-envelope"></i><?php print esc_html( 'Email :', 'weta' ); ?> <a href="<?php echo esc_attr($weta_extra_email); ?>"><?php echo weta_kses($weta_extra_email); ?></a></li>
            <?php endif;?>
         </ul>
         <?php endif;?>
        </div>
    </div>
  <!-- /.mobile-side-menu -->
  <div class="mobile-side-menu-overlay"></div>

  <div id="sidebar-area" class="sidebar-area">
      <button class="sidebar-trigger close">
          <svg
              class="sidebar-close"
              xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink"
              x="0px"
              y="0px"
              width="16px"
              height="12.7px"
              viewBox="0 0 16 12.7"
              style="enable-background: new 0 0 16 12.7"
              xml:space="preserve"
          >
              <g>
                  <rect
                      x="0"
                      y="5.4"
                      transform="matrix(0.7071 -0.7071 0.7071 0.7071 -2.1569 7.5208)"
                      width="16"
                      height="2"
                  ></rect>
                  <rect
                      x="0"
                      y="5.4"
                      transform="matrix(0.7071 0.7071 -0.7071 0.7071 6.8431 -3.7929)"
                      width="16"
                      height="2"
                  ></rect>
              </g>
          </svg>
      </button>
      <div class="side-menu-content">
          <div class="side-menu-logo">
              <?php weta_mobile_logo();?>
          </div>
          
          <div class="side-menu-about">
                <?php if ( !empty( $weta_extra_about_title ) ): ?>
                    <div class="side-menu-header">
                        <h3><?php print esc_html($weta_extra_about_title); ?></h3>
                    </div>
                <?php endif;?>

                <?php if ( !empty( $weta_extra_about_description ) ): ?>
                    <p><?php print esc_html($weta_extra_about_description); ?></p>
                <?php endif;?>

                <?php if ( !empty( $weta_extra_about_button_text ) ): ?>
                    <a href="<?php print esc_url($weta_extra_about_button_url); ?>" class="rr-primary-btn"><?php print esc_html($weta_extra_about_button_text); ?></a>
                <?php endif;?>
          </div>

          <div class="side-menu-contact">
              <div class="side-menu-header">
                  <h3><?php echo weta_kses($weta_extra_contact_title); ?></h3>
              </div>
              <ul class="side-menu-list">
                  <?php if ( !empty( $weta_extra_address ) ): ?>
                  <li>
                      <i class="fas fa-map-marker-alt"></i>
                      <p><?php echo weta_kses($weta_extra_address); ?></p>
                  </li>
                  <?php endif;?>
    
                  <?php if ( !empty( $weta_extra_phone ) ): ?>
                  <li>
                      <i class="fas fa-phone"></i>
                      <a href="tel:<?php echo esc_attr($weta_extra_phone_url); ?>"><?php echo weta_kses($weta_extra_phone); ?></a>
                  </li>
                  <?php endif;?>

                  <?php if ( !empty( $weta_extra_email ) ): ?>
                  <li>
                      <i class="fas fa-envelope-open-text"></i>
                      <a href="<?php echo esc_attr($weta_extra_email_link); ?>"><?php echo weta_kses($weta_extra_email); ?></a>
                  </li>
                  <?php endif;?>
              </ul>
          </div>

          <?php if ( !empty( $weta_side_social_hide ) ): ?>
          <ul class="side-menu-social">
            <?php weta_header_social_profiles() ?>
          </ul>
          <?php endif;?>
      </div>
  </div>