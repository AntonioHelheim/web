<?php

/**

 * Weta Social Profile

 * @author     RRdevs
 * @category   Widgets
 * @package    Weta/Widgets
 * @version    1.0.0
 * @extends    WP_Widget

 */

add_action( 'widgets_init', function () {

    register_widget( 'Weta_social_profile' );

} );

class Weta_social_profile extends WP_Widget {

    public function __construct() {

        parent::__construct( 'Weta_social_profile', esc_html__( 'Weta Social Profile', 'weta-core' ), array(

            'description' => esc_html__( 'Show Footer Info Widget By Weta', 'weta-core' ),

        ) );

    }

    public function widget( $args, $instance ) {

        extract( $args );

        extract( $instance );

        print $before_widget;

        if ( !empty( $title ) ) {

            print $before_title . apply_filters( 'widget_title', $title ) . $after_title;
        }

        ?>
			<div class="social-hvr">
				<?php if ( !empty( $twitter ) ): ?>
					<a href="<?php print esc_url( $twitter );?>"><i class="fa-brands fa-twitter"></i></a>
				<?php endif;?>
				<?php if ( !empty( $facebook ) ): ?>
					<a href="<?php print esc_url( $facebook );?>"><i class="fa-brands fa-facebook-f"></i></a>
				<?php endif;?>
				<?php if ( !empty( $linkedin ) ): ?>
					<a href="<?php print esc_url( $linkedin );?>"><i class="fa-brands fa-linkedin-in"></i></a>
				<?php endif;?>
				<?php if ( !empty( $pinterest ) ): ?>
					<a href="<?php print esc_url( $pinterest );?>"><i class="fa-brands fa-pinterest-p"></i></a>
				<?php endif;?>
				<?php if ( !empty( $youtube ) ): ?>
					<a href="<?php print esc_url( $youtube );?>"><i class="fa-brands fa-youtube"></i></a>
				<?php endif;?>
				<?php if ( !empty( $instagram ) ): ?>
					<a href="<?php print esc_url( $instagram );?>"><i class="fa-brands fa-instagram"></i></a>
				<?php endif;?>
				<?php if ( !empty( $whatsapp ) ): ?>
					<a href="<?php print esc_url( $whatsapp );?>"><i class="fa-brands fa-whatsapp"></i></a>
				<?php endif;?>
			</div>

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

        $description = isset( $instance['description'] ) ? $instance['description'] : '';

        $author_img = isset( $instance['image_box_image'] ) ? $instance['image_box_image'] : '';

        $facebook = isset( $instance['facebook'] ) ? $instance['facebook'] : '';

        $twitter = isset( $instance['twitter'] ) ? $instance['twitter'] : '';

        $instagram = isset( $instance['instagram'] ) ? $instance['instagram'] : '';

        $pinterest = isset( $instance['pinterest'] ) ? $instance['pinterest'] : '';

        $youtube = isset( $instance['youtube'] ) ? $instance['youtube'] : '';

        $linkedin = isset( $instance['linkedin'] ) ? $instance['linkedin'] : '';

        $whatsapp = isset( $instance['whatsapp'] ) ? $instance['whatsapp'] : '';

        ?>



		<p>

			<label for="title"><?php esc_html_e( 'Title:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'title' ) );?>" value="<?php print esc_attr( $title );?>">

		</p>



		<p>

			<label for="title"><?php esc_html_e( 'Short Description:', 'weta-core' );?></label>

			<textarea class="widefat" rows="7" cols="15" id="<?php print esc_attr( $this->get_field_id( 'description' ) );?>" value="<?php print esc_attr( $description );?>" name="<?php print esc_attr( $this->get_field_name( 'description' ) );?>"><?php print esc_attr( $description );?></textarea>

		</p>



		<p>

			<button type="submit" class="button button-secondary" id="author_info_image">Upload Media</button>

			<input type="hidden" name="<?php print esc_attr( $this->get_field_name( 'image_box_image' ) );?>" class="image_er_link" value="<?php print $author_img;?>">

			<div class="author-image-show">

				<img src="<?php print $author_img;?>" alt="" width="150" height="auto">

			</div>

		</p>



		<p>

			<label for="title"><?php esc_html_e( 'Facebook:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'facebook' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'facebook' ) );?>" value="<?php print esc_attr( $facebook );?>">

		</p>



		<p>

			<label for="title"><?php esc_html_e( 'Twitter:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'twitter' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'twitter' ) );?>" value="<?php print esc_attr( $twitter );?>">

		</p>



		<p>

			<label for="title"><?php esc_html_e( 'Instagram:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'instagram' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'instagram' ) );?>" value="<?php print esc_attr( $instagram );?>">

		</p>

		<p>

			<label for="title"><?php esc_html_e( 'Pinterest:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'pinterest' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'pinterest' ) );?>" value="<?php print esc_attr( $pinterest );?>">

		</p>



		<p>

			<label for="title"><?php esc_html_e( 'Youtube:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'youtube' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'youtube' ) );?>" value="<?php print esc_attr( $youtube );?>">

		</p>



		<p>

			<label for="title"><?php esc_html_e( 'Linkedin:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'linkedin' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'linkedin' ) );?>" value="<?php print esc_attr( $linkedin );?>">

		</p>


		<p>

			<label for="title"><?php esc_html_e( 'Whatsapp:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'whatsapp' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'whatsapp' ) );?>" value="<?php print esc_attr( $whatsapp );?>">

		</p>



		<?php

    }

    public function update( $new_instance, $old_instance ) {

        $instance = array();

        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        $instance['description'] = ( !empty( $new_instance['description'] ) ) ? strip_tags( $new_instance['description'] ) : '';

        $instance['facebook'] = ( !empty( $new_instance['facebook'] ) ) ? strip_tags( $new_instance['facebook'] ) : '';

        $instance['twitter'] = ( !empty( $new_instance['twitter'] ) ) ? strip_tags( $new_instance['twitter'] ) : '';

        $instance['instagram'] = ( !empty( $new_instance['instagram'] ) ) ? strip_tags( $new_instance['instagram'] ) : '';

        $instance['pinterest'] = ( !empty( $new_instance['pinterest'] ) ) ? strip_tags( $new_instance['pinterest'] ) : '';

        $instance['youtube'] = ( !empty( $new_instance['youtube'] ) ) ? strip_tags( $new_instance['youtube'] ) : '';

        $instance['linkedin'] = ( !empty( $new_instance['linkedin'] ) ) ? strip_tags( $new_instance['linkedin'] ) : '';

        $instance['whatsapp'] = ( !empty( $new_instance['whatsapp'] ) ) ? strip_tags( $new_instance['whatsapp'] ) : '';

        $instance['image_box_image'] = ( !empty( $new_instance['image_box_image'] ) ) ? strip_tags( $new_instance['image_box_image'] ) : '';

        return $instance;

    }

}