<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package weta
 */

get_header();
?>

<section class="error__area  section-space">
   <div class="container container-xxl">
      <?php 

         $weta_404_text = get_theme_mod('weta_404_text', __('404', 'weta'));
         $weta_404_title = get_theme_mod('weta_404_title', __('Oops! Page Can’t be Found.', 'weta'));
         $weta_404_message = get_theme_mod('weta_404_message', __('Oops! it could be you or us, there is no page here. It might have been moved or deleted.', 'weta'));
         $weta_404_img = get_theme_mod('weta_404_img', get_template_directory_uri() . '/assets/imgs/404/404.svg');
         $weta_404_btn_text = get_theme_mod('weta_404_btn_text', __('Go Back Home', 'weta'));         
         $shape_switch_404 = get_theme_mod('shape_switch_404', false);
      ?>

      <?php if(!empty($shape_switch_404)) : ?>
      <div class="error__element-shape">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/imgs/404/elements.svg" alt="imag not found">
      </div>
      <?php endif; ?>

      <div class="row">
          <div class="col-12">
              <div class="error__content text-center">
                  <?php if(!empty($weta_404_text)) : ?>
                  <h1><?php print esc_html($weta_404_text); ?></h1>
                  <?php endif; ?>

                  <?php if( !empty( $weta_404_img ) ) : ?>
                  <div class="error__content-media mb-65 mb-sm-50 mb-xs-45">
                      <img src="<?php echo esc_url($weta_404_img); ?>" alt="<?php print esc_attr__('Error', 'weta'); ?>">
                  </div>
                  <?php endif; ?>

                  <?php if(!empty($weta_404_title)) : ?>
                  <h3 class="mb-15 mb-xs-10"><?php print esc_html($weta_404_title); ?></h3>
                  <?php endif; ?>

                  <?php if(!empty($weta_404_message)) : ?>
                  <p class="mb-0"><?php print esc_html($weta_404_message); ?></p>
                  <?php endif; ?>

                  <?php if(!empty($weta_404_btn_text)) : ?>
                  <a href="<?php print esc_url(home_url('/')); ?>" class="rr-btn rr-btn__theme-4 mt-35 mt-sm-30 mt-xs-25">
                      <span class="btn-wrap">
                          <span class="text-one"><?php print esc_html($weta_404_btn_text); ?></span>
                          <span class="text-two"><?php print esc_html($weta_404_btn_text); ?></span>
                      </span>
                  </a>
                  <?php endif; ?>
              </div>
          </div>
      </div>
    </div>
</section>

<?php
get_footer();