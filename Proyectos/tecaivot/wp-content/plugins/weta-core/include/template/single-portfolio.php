<?php 
/** 
 * The main template file
 *
 * @package  WordPress
 * @subpackage  WETA Core
 */
get_header(); 

?>

<section class="portfolio-details pt-120 pb-120">
   <div class="container">
      <?php the_content(); ?>
   </div>
</section>

<?php get_footer();  ?>