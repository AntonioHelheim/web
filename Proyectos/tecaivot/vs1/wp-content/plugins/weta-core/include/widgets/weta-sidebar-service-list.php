<?php

/**
 * Weta Sidebar Service List
 * 
 * @author      RRdevs
 * @category    Widgets
 * @package     Weta/Widgets
 * @version     1.0.0
 * @extends     WP_Widget
 */

add_action( 'widgets_init', function () {

    register_widget( 'Weta_sidebar_service_list' );

} );

class Weta_sidebar_service_list extends WP_Widget {

    public function __construct() {

        parent::__construct( 'Weta_sidebar_service_list', esc_html__( 'Weta Sidebar Service List', 'weta-core' ), array(

            'description' => esc_html__( 'Weta Sidebar Service List by Weta', 'weta-core' ),

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
			<ul>
				<li><a href="<?php print esc_url( $button_one_url ); ?>"><?php print esc_html( $button_one_text ); ?></a> <span><i class="fa-solid fa-angle-right"></i></span></li>
				<li><a href="<?php print esc_url( $button_two_url ); ?>"><?php print esc_html( $button_two_text ); ?></a> <span><i class="fa-solid fa-angle-right"></i></span></li>
				<li><a href="<?php print esc_url( $button_three_url ); ?>"><?php print esc_html( $button_three_text ); ?></a> <span><i class="fa-solid fa-angle-right"></i></span></li>
				<li><a href="<?php print esc_url( $button_four_url ); ?>"><?php print esc_html( $button_four_text ); ?></a> <span><i class="fa-solid fa-angle-right"></i></span></li>
				<li><a href="<?php print esc_url( $button_five_url ); ?>"><?php print esc_html( $button_five_text ); ?></a> <span><i class="fa-solid fa-angle-right"></i></span></li>
				<li><a href="<?php print esc_url( $button_six_url ); ?>"><?php print esc_html( $button_six_text ); ?></a> <span><i class="fa-solid fa-angle-right"></i></span></li>
			</ul>

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
        $button_one_text = isset( $instance['button_one_text'] ) ? $instance['button_one_text'] : '';
        $button_two_text = isset( $instance['button_two_text'] ) ? $instance['button_two_text'] : '';
        $button_three_text = isset( $instance['button_three_text'] ) ? $instance['button_three_text'] : '';
        $button_four_text = isset( $instance['button_four_text'] ) ? $instance['button_four_text'] : '';
        $button_five_text = isset( $instance['button_five_text'] ) ? $instance['button_five_text'] : '';
        $button_six_text = isset( $instance['button_six_text'] ) ? $instance['button_six_text'] : '';
        $button_one_url = isset( $instance['button_one_url'] ) ? $instance['button_one_url'] : '';
        $button_two_url = isset( $instance['button_two_url'] ) ? $instance['button_two_url'] : '';
        $button_three_url = isset( $instance['button_three_url'] ) ? $instance['button_three_url'] : '';
        $button_four_url = isset( $instance['button_four_url'] ) ? $instance['button_four_url'] : '';
        $button_five_url = isset( $instance['button_five_url'] ) ? $instance['button_five_url'] : '';
        $button_six_url = isset( $instance['button_six_url'] ) ? $instance['button_six_url'] : '';

        ?>

		<p>
			<label for="title"><?php esc_html_e( 'Title:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'title' ) );?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) );?>" class="widefat" value="<?php print esc_attr( $title );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button One Text:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_one_text' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_one_text' ) );?>" class="widefat" value="<?php print esc_attr( $button_one_text );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button One URL:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_one_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_one_url' ) );?>" class="widefat" value="<?php print esc_attr( $button_one_url );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Two Text:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_two_text' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_two_text' ) );?>" class="widefat" value="<?php print esc_attr( $button_two_text );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Two URL:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_two_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_two_url' ) );?>" class="widefat" value="<?php print esc_attr( $button_two_url );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Three Text:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_three_text' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_three_text' ) );?>" class="widefat" value="<?php print esc_attr( $button_three_text );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Three URL:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_three_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_three_url' ) );?>" class="widefat" value="<?php print esc_attr( $button_three_url );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Four Text:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_four_text' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_four_text' ) );?>" class="widefat" value="<?php print esc_attr( $button_four_text );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Four URL:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_four_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_four_url' ) );?>" class="widefat" value="<?php print esc_attr( $button_four_url );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Five Text:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_five_text' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_five_text' ) );?>" class="widefat" value="<?php print esc_attr( $button_five_text );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Five URL:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_five_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_five_url' ) );?>" class="widefat" value="<?php print esc_attr( $button_five_url );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Six Text:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_six_text' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_six_text' ) );?>" class="widefat" value="<?php print esc_attr( $button_six_text );?>">
		</p>

		<p>
			<label for="title"><?php esc_html_e( 'Button Six URL:', 'weta-core' );?></label>
			<input type="text" id="<?php print esc_attr( $this->get_field_id( 'button_six_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'button_six_url' ) );?>" class="widefat" value="<?php print esc_attr( $button_six_url );?>">
		</p>

		<?php

    }

    public function update( $new_instance, $old_instance ) {

        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['banner'] = ( !empty( $new_instance['banner'] ) ) ? strip_tags( $new_instance['banner'] ) : '';
        $instance['button_one_text'] = ( !empty( $new_instance['button_one_text'] ) ) ? strip_tags( $new_instance['button_one_text'] ) : '';
        $instance['button_two_text'] = ( !empty( $new_instance['button_two_text'] ) ) ? strip_tags( $new_instance['button_two_text'] ) : '';
        $instance['button_three_text'] = ( !empty( $new_instance['button_three_text'] ) ) ? strip_tags( $new_instance['button_three_text'] ) : '';
        $instance['button_four_text'] = ( !empty( $new_instance['button_four_text'] ) ) ? strip_tags( $new_instance['button_four_text'] ) : '';
        $instance['button_five_text'] = ( !empty( $new_instance['button_five_text'] ) ) ? strip_tags( $new_instance['button_five_text'] ) : '';
        $instance['button_six_text'] = ( !empty( $new_instance['button_six_text'] ) ) ? strip_tags( $new_instance['button_six_text'] ) : '';
        $instance['button_one_url'] = ( !empty( $new_instance['button_one_url'] ) ) ? strip_tags( $new_instance['button_one_url'] ) : '';
        $instance['button_two_url'] = ( !empty( $new_instance['button_two_url'] ) ) ? strip_tags( $new_instance['button_two_url'] ) : '';
        $instance['button_three_url'] = ( !empty( $new_instance['button_three_url'] ) ) ? strip_tags( $new_instance['button_three_url'] ) : '';
        $instance['button_four_url'] = ( !empty( $new_instance['button_four_url'] ) ) ? strip_tags( $new_instance['button_four_url'] ) : '';
        $instance['button_five_url'] = ( !empty( $new_instance['button_five_url'] ) ) ? strip_tags( $new_instance['button_five_url'] ) : '';
        $instance['button_six_url'] = ( !empty( $new_instance['button_six_url'] ) ) ? strip_tags( $new_instance['button_six_url'] ) : '';

        return $instance;

    }

}