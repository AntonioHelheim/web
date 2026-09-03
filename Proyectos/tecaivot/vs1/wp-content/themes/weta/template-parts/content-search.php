<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package weta
 */

if ( is_single() ) : ?>
    <article id="post-<?php the_ID();?>" <?php post_class( 'postbox__item format-search' );?>>
        <?php get_template_part( 'template-parts/blog/blog-meta' ); ?>
        <h3 class="blog-post-title"><?php the_title();?></h3>

        <div class="postbox__text blog-info-paragraph">
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

        <div class="bottom-tag-line">
            <div class="d-flex flex-wrap">
                <?php print weta_get_tag();?>

            </div>
        </div>
    </article>
<?php else: ?>

    <article id="post-<?php the_ID();?>" <?php post_class( 'blog-one__single postbox__item format-search' );?>>
        <?php if ( has_post_thumbnail() ): ?>
            <div class="blog-post-media">
                <a href="<?php the_permalink();?>">
                    <?php the_post_thumbnail( 'full', ['class' => 'img-fluid'] );?>
                </a>
            </div>
        <?php endif;?>
        <?php get_template_part( 'template-parts/blog/blog-meta' ); ?>
        <div class="blog-info-paragraph">
            <h2><a href="<?php the_permalink();?>" class="title"><?php the_title();?></a></h2>
            <p><?php the_excerpt();?></p>
        </div>
        <?php get_template_part( 'template-parts/blog/blog-btn' ); ?>
    </article>

<?php endif;?>