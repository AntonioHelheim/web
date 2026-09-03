<?php

/**
 * Template part for displaying post btn
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package weta
 */

$weta_blog_btn = get_theme_mod( 'weta_blog_btn', 'Read More' );
$weta_blog_btn_switch = get_theme_mod( 'weta_blog_btn_switch', true );

?>

<?php if ( !empty( $weta_blog_btn_switch ) ): ?>
<a href="<?php the_permalink();?>" class="rr-btn rr-btn__theme-4">
    <span class="btn-wrap">
        <span class="text-one"><?php print esc_html( $weta_blog_btn );?></span>
        <span class="text-two"><?php print esc_html( $weta_blog_btn );?></span>
    </span>
</a>
<?php endif;?>