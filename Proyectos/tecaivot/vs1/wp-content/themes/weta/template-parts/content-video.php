<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package seoq
 */

$weta_video_url = function_exists( 'get_field' ) ? get_field( 'format_style' ) : NULL;

if ( is_single() ):
?>

    <article id="post-<?php the_ID();?>" <?php post_class( 'postbox__item format-video' );?>>
        <?php if ( has_post_thumbnail() ): ?>
        <div class="postbox__thumb postbox__video w-img p-relative">
            <?php the_post_thumbnail( 'full', ['class' => 'img-responsive'] );?>
            <?php if(!empty($weta_video_url)) : ?>
            <a href="<?php print esc_url( $weta_video_url );?>" class="play-btn pulse-btn popup-video video-popup video-popup" data-vbtype="video" data-autoplay="true"><i class="fas fa-play"></i></a>
            <?php endif; ?>
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

    <article id="post-<?php the_ID();?>" <?php post_class( 'blog-grid__single postbox__item mb-60 format-video' );?>>
        <div class="blog-grid__single blog-page">
            <?php if ( has_post_thumbnail() ): ?>
                <div class="postbox__video">
                    <div class="blog-grid__single-img">
                        <a href="<?php the_permalink();?>">
                            <?php the_post_thumbnail( 'full', ['class' => 'img-responsive'] );?>
                        </a>
                    </div>

                    <a href="<?php print esc_url( $weta_video_url );?>" class="play-btn pulse-btn popup-video video-popup" data-vbtype="video" data-autoplay="true"><i class="fas fa-play"></i></a>

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

<?php endif; ?>