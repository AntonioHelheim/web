<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eduker
 */

?>
<article id="post-<?php the_ID();?>" <?php post_class( 'blog-grid__single postbox__item mb-30 format-quote' );?>>
    <div class="blog__details-content">
        <blockquote>
            <div class="right">
                <p><?php the_excerpt();?></p>
                <span><?php print get_the_author(); ?></span>
            </div>
        </blockquote>
    </div>
</article>