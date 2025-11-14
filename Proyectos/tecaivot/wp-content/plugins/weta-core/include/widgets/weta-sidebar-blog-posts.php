<?php

/**
 * Weta Sidebar Blog Posts
 *
 * @author      RRdevs
 * @category    Widgets
 * @package     Weta/Widgets
 * @version     1.0.0
 * @extends     WP_Widget

 */

add_action( 'widgets_init', function () {

    register_widget( 'Weta_sidebar_blog_posts' );

} );

Class Weta_sidebar_blog_posts extends WP_Widget {

    public function __construct() {

        parent::__construct( 'Weta_sidebar_blog_posts', esc_html__( 'Weta Sidebar Blog Posts', 'weta-core' ), array(
            'description' => esc_html__( 'Show Latest Post by Weta', 'weta-core' ),
        ) );

    }

    public function widget( $args, $instance ) {

        extract( $args );

        extract( $instance );

        ?>



			<?php echo $before_widget;

        if ( $instance['title'] ):

            echo $before_title; ?>

						<?php echo apply_filters( 'widget_title', $instance['title'] );

            echo $after_title; ?>

				<?php endif;?>



			<div class="sidebar-post__wrapper">

				<?php

        $q = new WP_Query( array(
            'post_type'      => 'post',
			'post__not_in' => get_option( 'sticky_posts' ),
            'posts_per_page' => ( $instance['count'] ) ? $instance['count'] : '3',
            'order'          => ( $instance['posts_order'] ) ? $instance['posts_order'] : 'DESC',
            'orderby'        => 'date',
        ) );

        if ( $q->have_posts() ):

            while ( $q->have_posts() ): $q->the_post();

                ?>
                    <div class="sidebar-post">
						<?php if ( has_post_thumbnail() ): ?>
							<a href="<?php the_permalink();?>"><img src="<?php print esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) );?>" alt=""></a>
						<?php endif;?>

                        <div class="sidebar-post_content">
                            <ul class="post-meta">
                                <li><?php the_time( get_option('date_format') ); ?></li>
                            </ul>

                            <a href="<?php the_permalink();?>">
                            	<h3 class="title rr-fw-medium">
                            		<?php print wp_trim_words(get_the_title(), 7, ''); ?>
                            	</h3>
                            </a>
                        </div>
                    </div>
					<?php endwhile;endif;?>
				</div>
		<?php echo $after_widget; ?>

		<?php
    }

    public function form( $instance ) {

        $title = !empty( $instance['title'] ) ? $instance['title'] : '';

        $count = !empty( $instance['count'] ) ? $instance['count'] : esc_html__( '3', 'weta-core' );

        $posts_order = !empty( $instance['posts_order'] ) ? $instance['posts_order'] : esc_html__( 'DESC', 'weta-core' );

        $choose_style = !empty( $instance['choose_style'] ) ? $instance['choose_style'] : esc_html__( 'style_1', 'weta-core' );

        ?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title</label>

				<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat">
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'count' ); ?>">How many posts you want to show ?</label>

				<input type="number" name="<?php echo $this->get_field_name( 'count' ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" value="<?php echo esc_attr( $count ); ?>" class="widefat">
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'posts_order' ); ?>">Posts Order</label>

				<select name="<?php echo $this->get_field_name( 'posts_order' ); ?>" id="<?php echo $this->get_field_id( 'posts_order' ); ?>" class="widefat">

					<option value="" disabled="disabled">Select Post Order</option>

					<option value="ASC" <?php if ( $posts_order === 'ASC' ) {echo 'selected="selected"';}?>>ASC</option>

					<option value="DESC" <?php if ( $posts_order === 'DESC' ) {echo 'selected="selected"';}?>>DESC</option>

				</select>

			</p>
		<?php

    }

}