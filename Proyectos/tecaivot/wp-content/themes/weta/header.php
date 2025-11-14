<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package weta
 */
?>

<!doctype html>
<html <?php language_attributes();?>>
<head>
	<meta charset="<?php bloginfo( 'charset' );?>">
    <?php if ( is_singular() && pings_open( get_queried_object() ) ): ?>
    <?php endif;?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head();?>
</head>

<body <?php body_class();?>>

    <?php wp_body_open();?>


    <?php
        $weta_preloader = get_theme_mod( 'weta_preloader', false );
		$weta_backtotop = get_theme_mod('weta_backtotop', true);
    ?>

    <?php if ( !empty( $weta_preloader ) ): ?>
        <!-- preloader start -->
        <div id="preloader">
            <div class="preloader-close is-primary2">x</div>
            <div class="sk-three-bounce is-primary2">
                <div class="sk-child sk-bounce1"></div>
                <div class="sk-child sk-bounce2"></div>
                <div class="sk-child sk-bounce3"></div>
            </div>
        </div>
        <!-- preloader end -->
    <?php endif;?>


    <?php if ( !empty( $weta_backtotop ) ): ?>
    <!-- Backtotop start -->
    <div class="backtotop-wrap cursor-pointer">
        <svg class="backtotop-wrap d-none">
            <path class="btn-wrap" d="M0 0 L10 10" />
        </svg>
        <span class="btn-wrap">
            <span class="text-one"><i class="fa-solid fa-angle-up"></i></span>
            <span class="text-two"><i class="fa-solid fa-angle-up"></i></span>
        </span>
    </div>
    <!-- Backtotop end -->
    <?php endif;?>


    <!-- header start -->
    <?php do_action( 'weta_header_style' );?>
    <!-- header end -->

    <!-- wrapper-box start -->
    <?php do_action( 'weta_before_main_content' );?>