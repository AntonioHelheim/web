<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

function weta_fontawesome_six_tab( $fontawesome_six_icons_included = array() ) {

	$fontawesome_icons = array(
		'fa-solid fa-angle-right',
		'fa-regular fa-angle-right',
		'fa-light fa-angle-right',
		'fa-duotone fa-angle-right',
		'fa-thin fa-angle-right',
		'fas fa-arrow-right',
		'fa-regular fa-arrow-right',
		'fa-light fa-arrow-right',
		'fa-thin fa-arrow-right',
	);

    $fontawesome_six_icons_included['weta-fontawesome-six-icons'] = array(
		'name'          => 'weta-fontawesome-six-icons',
		'label'         => esc_html__( 'WETA - Fontawesome 6', 'weta-core' ),
		'labelIcon'     => 'weta-icon',
        'prefix'        => '',
        'displayPrefix' => '',
        'url'           => WETA_CORE_ELEMENTS_ASSETS . 'css/fontawesome-6.0.0.min.css',
		'icons'         => $fontawesome_icons,
		'ver'           => '6.0.0',
    );

	return $fontawesome_six_icons_included;
}
        
// Register Icons
add_filter( 'elementor/icons_manager/additional_tabs', 'weta_fontawesome_six_tab' );