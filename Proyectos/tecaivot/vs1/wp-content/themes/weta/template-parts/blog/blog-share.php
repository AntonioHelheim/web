<?php 

/**
 * Template part for displaying post share
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package weta
 */

$share_summary = get_the_excerpt();
$share_url = wp_get_shortlink();
$share_title = html_entity_decode(get_the_title(), ENT_COMPAT, 'UTF-8');
$share_media = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'large' );

?>

<div class="blog-details__social-list">
    <a href="//twitter.com/share?text=<?php echo urlencode($share_title); ?>&url=<?php echo esc_url($share_url); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
    <a href="//www.facebook.com/sharer/sharer.php?u=<?php echo esc_url($share_url); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
    <a href="//pinterest.com/pin/create/link/?url=<?php echo esc_url($share_url); ?>&media=<?php echo esc_url($share_media); ?>&description=<?php echo urlencode($share_title); ?>" target="_blank""><i class="fab fa-pinterest-p"></i></a>
</div>