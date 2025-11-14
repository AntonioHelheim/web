<?php

/**
 * Weta Sidebar Support Query
 * 
 * @author      RRdevs
 * @category    Widgets
 * @package     Weta/Widgets
 * @version     1.0.0
 * @extends     WP_Widget
 */

add_action( 'widgets_init', function () {

    register_widget( 'Weta_sidebar_support_query' );

} );

class Weta_sidebar_support_query extends WP_Widget {

    public function __construct() {

        parent::__construct( 'Weta_sidebar_support_query', esc_html__( 'Weta Sidebar Support Query', 'weta-core' ), array(

            'description' => esc_html__( 'Weta Sidebar Support Query by Weta', 'weta-core' ),

        ) );

    }

    public function widget( $args, $instance ) {

        extract( $args );

        extract( $instance );

        print $before_widget;

        ?>
			<div class="contact-banner__img1">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/5.svg" alt="">
			</div>
			<div class="contact-banner__img2">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/6.svg" alt="">
			</div>
			<div class="contact-banner__img3">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/img/shape/7.svg" alt="">
			</div>
			<div class="widget_contact_banner__img">
				<img src="<?php print $banner;?>" alt="">
			</div>
			<span class="contact_banner__subheading">
				<?php
					if ( !empty( $title ) ) {
						print apply_filters( 'widget_title', $title );
					}
				?>
			</span>
			<?php if ( $description ): ?>
				<h4 class="contact_banner__title"><?php print esc_html( $description );?></h4>
			<?php endif;?>
			<a href="<?php print esc_url( $button_url );?>"><?php print esc_html( $button_text );?> <i class="fa-solid fa-arrow-right"></i></a>

			<?php print $after_widget;?>

		<?php

    }

    /**
     * widget function.
     *
     * @see WP_Widget
     * @access public
     * @param array $instance
     * @return void
     */

    public function form( $instance ) {

        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        $banner_img = isset( $instance['banner'] ) ? $instance['banner'] : '';
        $description = isset( $instance['description'] ) ? $instance['description'] : '';
        $button_text = isset( $instance['button_text'] ) ? $instance['button_text'] : '';
        $button_url = isset( $instance['button_url'] ) ? $instance['button_url'] : '';

        ?>

		<p>
			<label for="title"><?php esc_html_e( 'Title:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'title' ) );?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) );?>" class="widefat" value="<?php print esc_attr( $title );?>">
		</p>

		<p>
			<button type="submit" class="button button-secondary" id="author_info_image">Upload Media</button>
			<input type="hidden" name="<?php print esc_attr( $this->get_field_name( 'banner' ) );?>" class="image_er_link" value="<?php print $banner;?>">
			<div class="author-image-show">

				<img src="<?php print $banner_img;?>" alt="" width="150" height="auto">

			</div>
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Description:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'description' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'description' ) );?>" class="widefat" value="<?php print esc_attr( $description );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Text:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_text' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_text' ) );?>" class="widefat" value="<?php print esc_attr( $button_text );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button URL:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_url' ) );?>" class="widefat" value="<?php print esc_attr( $button_url );?>">
		</p>

		<?php

    }

    public function update( $new_instance, $old_instance ) {

        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['banner'] = ( !empty( $new_instance['banner'] ) ) ? strip_tags( $new_instance['banner'] ) : '';
        $instance['description'] = ( !empty( $new_instance['description'] ) ) ? strip_tags( $new_instance['description'] ) : '';
        $instance['button_text'] = ( !empty( $new_instance['button_text'] ) ) ? strip_tags( $new_instance['button_text'] ) : '';
        $instance['button_url'] = ( !empty( $new_instance['button_url'] ) ) ? strip_tags( $new_instance['button_url'] ) : '';

        return $instance;

    }

}