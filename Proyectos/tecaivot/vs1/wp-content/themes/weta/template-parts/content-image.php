<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package eduker
 */

if ( is_single() ) : ?>

    <article id="post-<?php the_ID();?>" <?php post_class( 'postbox__item format-image' );?>>
        <?php if ( has_post_thumbnail() ): ?>
            <div class="blog-details__img">
                <?php the_post_thumbnail( 'full', ['class' => 'img-responsive'] );?>
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
        </div>
        <div class="blog-details__bottom tagcloud">
            <?php print weta_get_tag();?>
        </div>
    </article>

<?php else: ?>

    <article id="post-<?php the_ID();?>" <?php post_class( 'blog-grid__single postbox__item mb-60 format-image' );?>>
        <div class="blog-grid__single blog-page">
            <?php if ( has_post_thumbnail() ): ?>
                <div class="blog-grid__single-img">
                    <a href="<?php the_permalink();?>">
                        <?php the_post_thumbnail( 'full', ['class' => 'img-responsive'] );?>
                    </a>
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

<?php endif;?>