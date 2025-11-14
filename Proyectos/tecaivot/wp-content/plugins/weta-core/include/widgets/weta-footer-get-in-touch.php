<?php

/**
 * WETA Footer Get In Touch Widget
 * @author     RRdevs
 * @category   Widgets
 * @package    WETA/Widgets
 * @version    1.0.0
 * @extends    WP_Widget
 */

add_action( 'widgets_init', function () {
    register_widget( 'WETA_footer_get_in_touch' );
} );

class WETA_footer_get_in_touch extends WP_Widget {

    public function __construct() {
        parent::__construct( 'WETA_footer_get_in_touch', esc_html__( 'WETA Footer Get In Touch', 'weta-core' ), array(
            'description' => esc_html__( 'Show Footer Get In Touch Widget By WETA', 'weta-core' ),
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

        <div class="footer__widget footer__widget-item-5">
            <div class="footer__widget-button">
                <?php if ( !empty( $download_ios ) ): ?>
                <a href="<?php print esc_url( $download_ios_url );?>" class="rr-btn rr-btn__theme">
                    <span class="btn-wrap">
                        <span class="text-one">
                            <?php if ( !empty( $download_ios_image ) ): ?>
                            <img src="<?php print $download_ios_image;?>" alt="ios_image">
                            <?php endif; ?>

                            <?php print $download_ios;?></span>
                        <span class="text-two">
                            <?php if ( !empty( $download_ios_image ) ): ?>
                            <img src="<?php print $download_ios_image;?>" alt="ios_image">
                            <?php endif; ?>
                            <?php print $download_ios;?>
                        </span>
                    </span>
                </a>
                <?php endif; ?>

                <?php if ( !empty( $download_android ) ): ?>
                <a href="<?php print esc_url( $download_android_url );?>" class="rr-btn"> 
                    <span class="btn-wrap">
                        <span class="text-one">
                            <?php if ( !empty( $download_android_image ) ): ?>
                            <img src="<?php print $download_android_image;?>" alt="android_image">
                            <?php endif; ?>
                            <?php print $download_android;?>
                        </span>

                        <span class="text-two">
                            <?php if ( !empty( $download_android_image ) ): ?>
                            <img src="<?php print $download_android_image;?>" alt="android_image">
                            <?php endif; ?>
                            <?php print $download_android;?>
                        </span>
                    </span>
                </a>
                <?php endif; ?>
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

        $title = isset( $instance['title'] ) ? $instance['title'] : '';

        // ios button 
        $download_ios = isset( $instance['download_ios'] ) ? $instance['download_ios'] : '';
        $ios_icon = isset( $instance['download_ios_image'] ) ? $instance['download_ios_image'] : '';
        $download_ios_url = isset( $instance['download_ios_url'] ) ? $instance['download_ios_url'] : '';


        // android button 
        $download_android = isset( $instance['download_android'] ) ? $instance['download_android'] : '';
        $download_android_image = isset( $instance['download_android_image'] ) ? $instance['download_android_image'] : '';
        $download_android_url = isset( $instance['download_android_url'] ) ? $instance['download_android_url'] : '';

        ?>

		<p>
			<label for="title"><?php esc_html_e( 'Title:', 'weta-core' );?></label>
			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'title' ) );?>" value="<?php print esc_attr( $title );?>">
		</p>

        <!-- IOS  -->
		<p>
			<label for="title"><?php esc_html_e( 'Download iOS Button Text:', 'weta-core' );?></label>
			<textarea class="widefat" rows="7" cols="15" id="<?php print esc_attr( $this->get_field_id( 'download_ios' ) );?>" value="<?php print esc_attr( $download_ios );?>" name="<?php print esc_attr( $this->get_field_name( 'download_ios' ) );?>"><?php print esc_attr( $download_ios );?></textarea>
		</p>


        <p>
            <button type="submit" class="button button-secondary" id="author_info_image">Upload Media</button>
            <input type="hidden" name="<?php print esc_attr( $this->get_field_name( 'download_ios_image' ) );?>" class="image_er_link" value="<?php print $ios_icon;?>">
            <div class="author-image-show">
                <img src="<?php print $ios_icon;?>" alt="" width="150" height="auto">
            </div>
        </p>


		<p>
			<label for="title"><?php esc_html_e( 'Download iOS Button URL:', 'weta-core' );?></label>
			<input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'download_ios_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'download_ios_url' ) );?>" value="<?php print esc_attr( $download_ios_url );?>">
		</p>


        <!-- Android  -->
        <p>
            <label for="title"><?php esc_html_e( 'Download Android Button Text:', 'weta-core' );?></label>
            <textarea class="widefat" rows="7" cols="15" id="<?php print esc_attr( $this->get_field_id( 'download_android' ) );?>" value="<?php print esc_attr( $download_android );?>" name="<?php print esc_attr( $this->get_field_name( 'download_android' ) );?>"><?php print esc_attr( $download_android );?></textarea>
        </p>


        <p>
            <button type="submit" class="button button-secondary" id="author_info_image">Upload Media</button>
            <input type="hidden" name="<?php print esc_attr( $this->get_field_name( 'download_android_image' ) );?>" class="image_er_link" value="<?php print $download_android_image;?>">
            <div class="author-image-show">
                <img src="<?php print $download_android_image;?>" alt="" width="150" height="auto">
            </div>
        </p>


        <p>
            <label for="title"><?php esc_html_e( 'Download Android Button URL:', 'weta-core' );?></label>
            <input type="text" class="widefat" id="<?php print esc_attr( $this->get_field_id( 'download_android_url' ) );?>"  name="<?php print esc_attr( $this->get_field_name( 'download_android_url' ) );?>" value="<?php print esc_attr( $download_android_url );?>">
        </p>

		<?php

    }

    public function update( $new_instance, $old_instance ) {

        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        // IOS 
        $instance['download_ios'] = ( !empty( $new_instance['download_ios'] ) ) ? strip_tags( $new_instance['download_ios'] ) : '';
        $instance['download_ios_image'] = ( !empty( $new_instance['download_ios_image'] ) ) ? strip_tags( $new_instance['download_ios_image'] ) : '';
        $instance['download_ios_url'] = ( !empty( $new_instance['download_ios_url'] ) ) ? strip_tags( $new_instance['download_ios_url'] ) : '';

        // Android 
        $instance['download_android'] = ( !empty( $new_instance['download_android'] ) ) ? strip_tags( $new_instance['download_android'] ) : '';
        $instance['download_android_image'] = ( !empty( $new_instance['download_android_image'] ) ) ? strip_tags( $new_instance['download_android_image'] ) : '';
        $instance['download_android_url'] = ( !empty( $new_instance['download_android_url'] ) ) ? strip_tags( $new_instance['download_android_url'] ) : '';

        return $instance;

    }

}