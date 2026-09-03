<?php

/**
 * Weta Footer Contact Info
 *
 * @author      RRdevs
 * @category    Widgets
 * @package     Weta/Widgets
 * @version     1.0.0
 * @extends     WP_Widget
 */

add_action( 'widgets_init', function () {
    register_widget( 'Weta_footer_contact_info' );
} );

class Weta_footer_contact_info extends WP_Widget {

    public function __construct() {

        parent::__construct( 'Weta_footer_contact_info', esc_html__( 'Weta Footer Contact Info', 'weta-core' ), array(

            'description' => esc_html__( 'Weta Footer Contact Info by Weta', 'weta-core' ),
        ) );
    }

    public function widget( $args, $instance ) {

        extract( $args );
        extract( $instance );
        print $before_widget;

        ?>

        <div class="footer-3__widget footer-3__widget-item-1">
            <div class="footer-3__content">
            	<?php if (!empty($footer_info_title)) : ?>
                <h2 class="section-2__title mb-15 mb-xs-10 xl text-uppercase"><?php print esc_html( $footer_info_title );?></h2>
                <?php endif; ?>

                <?php if (!empty($description)) : ?>
                <p class="mb-40 mb-xs-30"><?php print esc_html( $description );?></p>
                <?php endif; ?>

                <?php if (!empty($email)) : ?>
                <a href="mailto:<?php print esc_attr( $email );?>"><?php print esc_html( $email );?></a>
            	<?php endif; ?>
            </div>

            <div class="footer-3__social mt-35 mt-xs-25">
   				<?php if ( !empty( $facebook ) ): ?>
                	<a href="<?php print esc_url( $facebook );?>"><i class="fab fa-facebook-f"></i></a>
                <?php endif;?>

	            <?php if ( !empty( $pinterest ) ): ?>
	                <a href="<?php print esc_url( $pinterest );?>"><i class="fab fa-pinterest"></i></a>
	            <?php endif;?>

				<?php if ( !empty( $instagram ) ): ?>
	                <a href="<?php print esc_url( $instagram );?>"><i class="fab fa-instagram"></i></a>
	            <?php endif;?>

	            <?php if ( !empty( $linkedin ) ): ?>
	                <a href="<?php print esc_url( $linkedin );?>"><i class="fa-brands fa-linkedin-in"></i></a>
	            <?php endif;?>	            

	            <?php if ( !empty( $youtube ) ): ?>
	                <a href="<?php print esc_url( $youtube );?>"><i class="fa-brands fa-youtube"></i></a>
	            <?php endif;?>	            

	            <?php if ( !empty( $whatsapp ) ): ?>
	                <a href="<?php print esc_url( $whatsapp );?>"><i class="fa-brands fa-whatsapp"></i></a>
	            <?php endif;?>
            </div>
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

        $footer_info_title = isset( $instance['footer_info_title'] ) ? $instance['footer_info_title'] : '';
        $description = isset( $instance['description'] ) ? $instance['description'] : '';
        $email = isset( $instance['email'] ) ? $instance['email'] : '';
   
        // For Social 
        $facebook = isset( $instance['facebook'] ) ? $instance['facebook'] : '';
        $instagram = isset( $instance['instagram'] ) ? $instance['instagram'] : '';
        $pinterest = isset( $instance['pinterest'] ) ? $instance['pinterest'] : '';
        $youtube = isset( $instance['youtube'] ) ? $instance['youtube'] : '';
        $linkedin = isset( $instance['linkedin'] ) ? $instance['linkedin'] : '';
        $whatsapp = isset( $instance['whatsapp'] ) ? $instance['whatsapp'] : '';
        ?>

		<p>
			<label for="title"><?php esc_html_e( 'Title:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'footer_info_title' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'footer_info_title' ) );?>" class="widefat" value="<?php print esc_attr( $footer_info_title );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Description:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'description' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'description' ) );?>" class="widefat" value="<?php print esc_attr( $description );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Email Address:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'email' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'email' ) );?>" class="widefat" value="<?php print esc_attr( $email );?>">
		</p>


		<!-- For Social  -->
		<p>
			<label for="title"><?php esc_html_e( 'Facebook:', 'weta-core' );?></label>

			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'facebook' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'facebook' ) );?>" value="<?php print esc_attr( $facebook );?>">
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
        $instance['footer_info_title'] = ( !empty( $new_instance['footer_info_title'] ) ) ? strip_tags( $new_instance['footer_info_title'] ) : '';
        $instance['description'] = ( !empty( $new_instance['description'] ) ) ? strip_tags( $new_instance['description'] ) : '';
        $instance['email'] = ( !empty( $new_instance['email'] ) ) ? strip_tags( $new_instance['email'] ) : '';


        // for Social 

        $instance['facebook'] = ( !empty( $new_instance['facebook'] ) ) ? strip_tags( $new_instance['facebook'] ) : '';

        $instance['instagram'] = ( !empty( $new_instance['instagram'] ) ) ? strip_tags( $new_instance['instagram'] ) : '';

        $instance['pinterest'] = ( !empty( $new_instance['pinterest'] ) ) ? strip_tags( $new_instance['pinterest'] ) : '';

        $instance['youtube'] = ( !empty( $new_instance['youtube'] ) ) ? strip_tags( $new_instance['youtube'] ) : '';

        $instance['linkedin'] = ( !empty( $new_instance['linkedin'] ) ) ? strip_tags( $new_instance['linkedin'] ) : '';

        $instance['whatsapp'] = ( !empty( $new_instance['whatsapp'] ) ) ? strip_tags( $new_instance['whatsapp'] ) : '';

        $instance['image_box_image'] = ( !empty( $new_instance['image_box_image'] ) ) ? strip_tags( $new_instance['image_box_image'] ) : '';
        
        return $instance;	
    }
}