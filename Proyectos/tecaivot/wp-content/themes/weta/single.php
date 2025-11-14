<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package weta
 */

get_header();

$class_main = 'col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12';
if(is_active_sidebar('blog-sidebar')){
  	$class_main = 'col-xl-8 col-lg-8 col-12';
}

?>
<div class="rr-blog-area blog-details pt-120 pb-100">
    <div class="container">
        <div class="row">
			<div class="<?php echo esc_attr($class_main) ?>">
				<div class="postbox__wrapper postbox__details blog-details__left">
					<?php
						while ( have_posts() ):
						the_post();

						get_template_part( 'template-parts/content', get_post_format() );

    				?>

					<?php

						get_template_part( 'template-parts/biography' );

						// If comments are open or we have at least one comment, load up the comment template.
						if ( comments_open() || get_comments_number() ):
							comments_template();
						endif;

						endwhile; // End of the loop.
					?>
				</div>
			</div>
			<?php if ( is_active_sidebar( 'blog-sidebar' ) ): ?>
				<div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
					<div class="sidebar">
						<?php get_sidebar();?>
					</div>
				</div>
			<?php endif;?>
		</div>
	</div>
</div>

<?php
get_footer();
