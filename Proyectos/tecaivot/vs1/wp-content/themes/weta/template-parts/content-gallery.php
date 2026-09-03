<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eduker
 */

$gallery_images = function_exists('get_field') ? get_field('post_gallery') : '';
if ( is_single() ): ?>

    <article id="post-<?php the_ID();?>" <?php post_class( 'postbox__item format-gallery mb-60' );?>>
        <?php if ( !empty( $gallery_images ) ): ?>
            <div class="postbox__thumb postbox__slider blog_gallery_active swiper-container w-img p-relative">
                <div class="swiper-wrapper">
                    <?php foreach ( $gallery_images as $key => $image ): ?>
                    <div class="postbox__slider-item swiper-slide">
                        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                    </div>
                    <?php endforeach;?>
                </div>
                <div class="postbox-nav">
                    <button class="postbox-slider-button-next blog-gallery-arrow"><i class="fa-light fa-angle-right"></i></button>
                    <button class="postbox-slider-button-prev blog-gallery-arrow"><i class="fa-light fa-angle-left"></i></button>
                </div>
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

    <article id="post-<?php the_ID();?>" <?php post_class( 'blog-grid__single postbox__item mb-60 format-gallery' );?>>
        <div class="blog-grid__single blog-page">
            <?php if ( !empty( $gallery_images ) ): ?>
                <div class="postbox__thumb postbox__slider blog_gallery_active swiper-container">
                    <div class="swiper-wrapper">
                        <?php foreach ( $gallery_images as $key => $image ): ?>
                        <div class="postbox__slider-item swiper-slide">
                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
                        </div>
                        <?php endforeach;?>
                    </div>
                    <div class="postbox-nav">
                        <button class="postbox-slider-button-next blog-gallery-arrow"><i class="fa-light fa-angle-right"></i></button>
                        <button class="postbox-slider-button-prev blog-gallery-arrow"><i class="fa-light fa-angle-left"></i></button>
                    </div>
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