<?php 

   /**
    * Template part for displaying header side information
    *
    * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
    *
    * @package weta
   */

   $weta_side_social_hide = get_theme_mod( 'weta_side_social_hide', false );
   $weta_side_logo = get_theme_mod( 'weta_side_logo', get_template_directory_uri() . '/assets/imgs/logo/logo-white.svg' );
   $weta_side_social_title = get_theme_mod( 'weta_side_social_title', __( 'Subscribe & Follow', 'weta' ) );
?>


<!-- Offcanvas area start -->
<div class="fix">
    <div class="offcanvas__area">
        <div class="offcanvas__wrapper">
            <div class="offcanvas__content">
                <div class="offcanvas__top d-flex justify-content-between align-items-center">
                    <div class="offcanvas__logo">
                        <?php weta_mobile_logo();?>
                    </div>
                    <div class="offcanvas__close">
                        <button class="offcanvas-close-icon animation--flip">
                                <span class="offcanvas-m-lines">
                              <span class="offcanvas-m-line line--1"></span><span class="offcanvas-m-line line--2"></span><span class="offcanvas-m-line line--3"></span>
                                </span>
                        </button>
                    </div>
                </div>
                <div class="mobile-menu fix"></div>

                <?php if ( !empty( $weta_side_social_hide ) ): ?>
                <div class="offcanvas__social">
                    <?php if ( !empty( $weta_side_social_title ) ): ?>
                    <h4 class="offcanvas__title mb-20"><?php echo weta_kses($weta_side_social_title); ?></h4>
                    <?php endif;?>
                    
                    <ul class="header-top-socail-menu d-flex">
                        <?php weta_header_social_profiles() ?>
                    </ul>
                </div>
                <?php endif;?>
            </div>
        </div>
    </div>
</div>
<div class="offcanvas__overlay"></div>
<div class="offcanvas__overlay-white"></div>
<!-- Offcanvas area start -->