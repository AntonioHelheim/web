<?php 

/**
 * Template part for displaying post meta
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package weta
 */

$categories = get_the_terms( $post->ID, 'category' );
$weta_blog_author = get_theme_mod( 'weta_blog_author', true );
$weta_blog_date = get_theme_mod( 'weta_blog_date', true );
$weta_blog_cat = get_theme_mod( 'weta_blog_cat', false );
$weta_blog_comments = get_theme_mod( 'weta_blog_comments', true );

?>


<ul class="blog-details__meta list-unstyled">
    <?php if ( !empty($weta_blog_author) ): ?>
        <li><a href="<?php print esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>"><i class="fa-light fa-user"></i> <?php print get_the_author();?></a></li>
    <?php endif;?>

    <?php if ( !empty($weta_blog_date) ): ?>
        <?php 
            if (has_post_thumbnail()) {

            } else {
                ?>
                    <li><i class="fa-light fa-calendar-days"></i> <?php the_time( get_option('date_format') ); ?></li>
                <?php
            }
        ?>
    <?php endif;?>

    <?php if ( !empty($weta_blog_cat) ): ?>
    <?php if ( !empty( $categories[0]->name ) ): ?>  
        <li><i class="fas fa-tag"></i> <a href="<?php print esc_url(get_category_link($categories[0]->term_id)); ?>"><?php echo esc_html($categories[0]->name); ?></a></li>
    <?php endif;?>
    <?php endif;?>

    <?php if ( !empty($weta_blog_comments) ): ?>
        <li><a href="<?php comments_link();?>"><i class="fa-light fa-comment"></i><span><?php comments_number(); ?></span></a></li>
    <?php endif;?>
</ul>