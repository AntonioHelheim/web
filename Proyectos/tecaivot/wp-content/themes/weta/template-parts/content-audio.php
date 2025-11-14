<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package seoq
 */

$seoq_audio_url = function_exists( 'get_field' ) ? get_field( 'format_style' ) : NULL;
if ( is_single() ):
?>

    <article id="post-<?php the_ID();?>" <?php post_class( 'postbox__item format-audio' );?>>
        <?php if ( !empty( $seoq_audio_url ) ): ?>
            <div class="postbox__thumb postbox__audio mb-25 w-img p-relative">
                <?php echo wp_oembed_get( $seoq_audio_url ); ?>
            </div>
        <?php endif;?>
        <div class="blog-details__content post-content-wrap">
            <?php get_template_part( 'template-parts/blog/blog-meta' ); ?>
            <div class="postbox__text">
                <?php the_content();?>
                <?php
                    wp_link_pages( [
                        'before'      => '<div class="page-links">' . esc_html__( 'Pages:', 'weta' ),
                        'after'       => '</div>',
                        'link_before' => '<span class="page-number">',
                        'link_after'  => '</span>',
                    ] );
                ?>
            </div>

            <div class="blog-details__bottom tagcloud">
                <?php print weta_get_tag();?>
            </div>
        </div>

    </article>

<?php else: ?>

    <article id="post-<?php the_ID();?>" <?php post_class( 'blog-grid__single postbox__item mb-30 format-image' );?>>
        <div class="blog-grid__single blog-page">
            <?php if ( !empty( $seoq_audio_url ) ): ?>
                <div class="postbox__thumb postbox__audio w-img p-relative">
                    <?php echo wp_oembed_get( $seoq_audio_url ); ?>
                </div>
            <?php endif;?>
            <div class="blog-grid__single-content post-content-wrap">
                <?php get_template_part( 'template-parts/blog/blog-meta' ); ?>
                <h2 class="rr-blog-title"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                <div class="postbox__text">
                    <?php the_excerpt();?>
                </div>
                <?php get_template_part( 'template-parts/blog/blog-btn' ); ?>
            </div>
        </div>
    </article>

<?php
endif;?>