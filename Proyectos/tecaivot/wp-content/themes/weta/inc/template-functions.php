<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package weta
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function weta_body_classes( $classes ) {
    // Adds a class of hfeed to non-singular pages.
    if ( !is_singular() ) {
        $classes[] = 'hfeed';
    }
    // Adds a class of no-sidebar when there is no sidebar present.
    if ( !is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'no-sidebar';
    }
    if (  function_exists('tutor') ) {
        $user_name = sanitize_text_field(get_query_var('tutor_student_username'));
        $get_user = tutor_utils()->get_user_by_login($user_name);
    }

    if ( !empty($get_user) ) {
        $classes[] = 'profile-breadcrumb';
    }

    return $classes;
}
add_filter( 'body_class', 'weta_body_classes' );

/**
 * Get tags.
 */
function weta_get_tag() {
    $html = '';
    if ( has_tag() ) {
        $html .= '<span class="title">' . esc_html__( 'Tags:', 'weta' ) . '</span>';
        $html .= get_the_tag_list( '', '', '' );
    }
    return $html;
}

/**
 * Get categories.
 */
function weta_get_category() {

    $categories = get_the_category( get_the_ID() );
    $x = 0;
    foreach ( $categories as $category ) {

        if ( $x == 2 ) {
            break;
        }
        $x++;
        print '<a class="news-tag" href="' . get_category_link( $category->term_id ) . '">' . $category->cat_name . '</a>';

    }
}

// Add Span In category number
function weta_cat_count_span($links) {
    $links = str_replace('</a> (', '</a> <span>(', $links);
    $links = str_replace(')', ')</span>', $links);
    return $links;
}

add_filter('wp_list_categories', 'weta_cat_count_span');

// Add Span In archive number
function weta_the_archive_count($links) {
    $links = str_replace('</a>'.esc_attr__('&nbsp;','weta').'(', '</a> <span>(', $links);
    $links = str_replace(')', ')</span>', $links);
    return $links;
}

add_filter('get_archives_link', 'weta_the_archive_count');

/** img alt-text **/
function weta_img_alt_text( $img_er_id = null ) {
    $image_id = $img_er_id;
    $image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', false );
    $image_title = get_the_title( $image_id );

    if ( !empty( $image_id ) ) {
        if ( $image_alt ) {
            $alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', false );
        } else {
            $alt_text = get_the_title( $image_id );
        }
    } else {
        $alt_text = esc_html__( 'Image Alt Text', 'weta' );
    }

    return $alt_text;
}

// weta_service_sidebar
function weta_service_sidebar_func() {
    if ( is_active_sidebar( 'services-sidebar' ) ) {

        dynamic_sidebar( 'services-sidebar' );
    }
}
add_action( 'weta_service_sidebar', 'weta_service_sidebar_func', 20 );

/**
 * Post Navigation
 */

 if ( !function_exists('weta_post_navigation') ) {
	function weta_post_navigation(){
		$weta_next_post = get_next_post();
		$weta_prev_post = get_previous_post();
		
		if ( $weta_next_post || $weta_prev_post ) : ?>

			<div class="blog-post-revew">
                <?php if ( ! empty( $weta_prev_post ) ) : ?>
                    <div class="revew">
                        <a href="<?php echo get_permalink( $weta_prev_post ); ?>"><?php echo esc_html( 'PREV POST', 'weta' ); ?></a>
                        <h6 class="title"><?php echo get_the_title( $weta_prev_post ); ?></h6>
                    </div>
                <?php endif; ?>
                <?php if ( ! empty( $weta_next_post ) ) : ?>
                    <div class="next">
                        <a href="<?php echo get_permalink( $weta_next_post ); ?>">NEXT POST</a>
                        <h6 class="title"><?php echo get_the_title( $weta_next_post ); ?></h6>
                    </div>
                <?php endif; ?>
            </div>
		
		<?php endif;
	}
}