<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package weta
 */

get_header();

$class_main = 'col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12';
$weta_blog_page_sidebar_hide = get_theme_mod( 'weta_blog_page_sidebar_hide', true );
if( !empty($weta_blog_page_sidebar_hide) ){
	if(is_active_sidebar('blog-sidebar')){
		$class_main = 'col-xl-8 col-lg-8 col-12';
	}
}

?>

<section class="rr-blog-area blog-girde content-area">
	<div class="container">
        <div class="row">
			<div class="<?php echo esc_attr($class_main) ?> blog-post-items blog-padding">
				<div class="row">
					<div class="col-12">
						<?php
							if ( have_posts() ):
							if ( is_home() && !is_front_page() ):
						?>

							<header>
								<h1 class="page-title screen-reader-text"><?php single_post_title();?></h1>
							</header>
							<?php
								endif;?>
							<?php
								/* Start the Loop */
								while ( have_posts() ): the_post(); ?>
									<div class="<?php echo esc_attr($classes_post_grid) ?>">
										<?php
											/*
											* Include the Post-Type-specific template for the content.
											* If you want to override this in a child theme, then include a file
											* called content-___.php (where ___ is the Post Type name) and that will be used instead.
											*/
											get_template_part( 'template-parts/content', get_post_format() );
										?>

									</div>
								<?php
									endwhile;
								?>
									<div class="pagination">
										<?php weta_pagination( '<i class="fas fa-arrow-left"></i> Prew', 'Next <i class="fas fa-arrow-right"></i>', '', ['class' => ''] );?>
									</div>
								<?php
								else:
									get_template_part( 'template-parts/content', 'none' );
								endif;
							?>


						</div>
					</div>
				</div>

			<?php if ( !empty( $weta_blog_page_sidebar_hide ) ): ?>
				<?php if ( is_active_sidebar( 'blog-sidebar' ) ): ?>
					<div class="col-xl-4 col-lg-4 col-12">
						<div class="sidebar">
							<?php get_sidebar();?>
						</div>
					</div>
				<?php endif;?>
			<?php endif;?>
        </div>
    </div>
</section>

<?php
get_footer();
