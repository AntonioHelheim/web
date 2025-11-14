<?php
namespace WETACore\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Gallery_Post extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'weta_gallery_post';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Gallery Post', 'weta-core' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'weta-icon';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'weta_core' ];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function register_controls() {

        $this->start_controls_section(
			'content',
			[
				'label' 			=> esc_html__( 'Portfolio', 'weta-core' ),
				'tab'   			=> Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'important_note',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'Add Portfolio from Portfolios Custom Post. And Drag and Drop this widget where you see Portfolio. For the color change in this widget style follow.', 'weta-core' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
			]
		);

		$this->add_control(
            'image_popup_icon',
            [
                'label'   => esc_html__( 'Image Popup Icon', 'weta-core' ),
                'type'    => Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-plus',
                    'library' => 'solid',
                ],
            ]
        );

		$this->add_control(
            'link_icon',
            [
                'label'   => esc_html__( 'Link Icon', 'weta-core' ),
                'type'    => Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-link',
                    'library' => 'solid',
                ],
            ]
        );

        $this->end_controls_section();
        
        $this->start_controls_section(
			'stylesheet',
			[
				'label' 			=> esc_html__( 'Portfolio', 'weta-core' ),
				'tab'   			=> Controls_Manager::TAB_STYLE,
			]
        );

		$this->add_control(
			'filter_options',
			[
				'label' => esc_html__( 'Filter Options', 'weta-core' ),
				'type' => Controls_Manager::HEADING,
			]
		);

        $this->add_control(
			'alignment',
			[
				'label'             => esc_html__( 'Alignment', 'weta-core' ),
				'type'         => Controls_Manager::CHOOSE,
				'default'           => 'center',
                'options'      => [
                    'left'   => [
                        'title' => esc_html__( 'Left', 'weta-core' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'weta-core' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'  => [
                        'title' => esc_html__( 'Right', 'weta-core' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
				'selectors' => [
					'{{WRAPPER}} .portfolio-menu' 	=> 'text-align: {{VALUE}};',
				],
			]
        );

		$this->start_controls_tabs( 'tabs_filter' );

		$this->start_controls_tab(
            'filter_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'weta-core' ),
            ]
        );

        $this->add_control(
            'filter_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-menu button'    => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'filter_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-menu button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'label' 			=> esc_html__( 'Border', 'weta-core' ),
				'name' 				=> 'filter_border',
				'selector' 			=> '{{WRAPPER}} .portfolio-menu button',
			]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'filter_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'weta-core' ),
            ]
        );

        $this->add_control(
            'filter_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-menu button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'filter_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-menu button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'label' 			=> esc_html__( 'Border', 'weta-core' ),
				'name' 				=> 'filter_border_hover',
				'selector' 			=> '{{WRAPPER}} .portfolio-menu button:hover',
			]
        );

		$this->end_controls_tab();

        $this->start_controls_tab(
            'filter_active_tab',
            [
                'label' => esc_html__( 'Active', 'weta-core' ),
            ]
        );

        $this->add_control(
            'filter_color_active',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-menu button.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'filter_background_active',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-menu button.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'label' 			=> esc_html__( 'Border', 'weta-core' ),
				'name' 				=> 'filter_border_active',
				'selector' 			=> '{{WRAPPER}} .portfolio-menu button.active',
			]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

		$this->add_control(
			'filter_border_radius',
			[
				'label' 			=> esc_html__( 'Border Radius', 'weta-core' ),
				'type' 				=> Controls_Manager::DIMENSIONS,
				'size_units' 		=> [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .portfolio-menu button' 	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'filter_margin_bottom',
			[
				'label'             => esc_html__( 'Margin Bottom', 'weta-core' ),
				'type'              => Controls_Manager::SLIDER,
				'size_units'        => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min'       => 0,
						'max'       => 250,
						'step'      => 5,
					],
				],
				'default' => [
					'unit'          => 'px',
					'size'          => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .portfolio-menu' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'parent_options',
			[
				'label' => esc_html__( 'Parent Options', 'weta-core' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'subheading_color',
			[
				'label' 			=> esc_html__( 'Subheading Color', 'weta-core' ),
				'type' 				=> Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .portfolio-content-wrapper span' 	=> 'color: {{VALUE}}',
				],
			]
		);
        
        $this->add_control(
			'heading_color',
			[
				'label' 			=> esc_html__( 'Heading Color', 'weta-core' ),
				'type' 				=> Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .portfolio-content-wrapper h4' 	=> 'color: {{VALUE}}',
				],
			]
		);
        
        $this->add_control(
			'opacity',
			[
				'label' 			=> esc_html__( 'Opacity', 'weta-core' ),
				'type' 				=> Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .portfolio-content-wrapper:after' 	=> 'background-color: {{VALUE}}',
				],
			]
        );

		$this->add_control(
			'opacity_percent',
			[
				'label'             => esc_html__( 'Opacity Percent', 'weta-core' ),
				'type'              => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'       => .1,
						'max'       => 1,
						'step'      => .1,
					],
				],
				'default' => [
					'size'          => .7,
				],
				'selectors' => [
					'{{WRAPPER}} .portfolio-content-wrapper:after' => 'opacity: {{SIZE}};',
				],
			]
		);

        $this->add_control(
			'border_radius',
			[
				'label' 			=> esc_html__( 'Border Radius', 'weta-core' ),
				'type' 				=> Controls_Manager::DIMENSIONS,
				'size_units' 		=> [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .single-portfolio' 	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$tags = get_terms([
			'hide_empty'=> true,
			'taxonomy'=>'gallery-cat'
		]);

		$portfolios = new \WP_Query([
			'posts_per_page'=> -1,
			'post_type'=> 'gallery',
			'post_status'=> 'publish',

		]);



        ?>

<div class="row">
			<div class="col-lg-12">
				<div class="portfolio-menu">
					<button class="active" data-filter="*">All</button>
						<?php 
						foreach($tags as $tag){
							printf('<button data-filter=".%s">%s</button>', esc_attr($tag->slug),esc_html($tag->name));
						}
					?>
				</div>
			</div>
		</div>

		<div class="row portfolio-active">
				<?php while($portfolios->have_posts()){
					$portfolios->the_post();
					$portfolio_tags = $this->get_portfolio_tags(get_the_ID());
					$image_url = get_the_post_thumbnail_url(get_the_ID(), 'large')
					?>
						<div class="col-lg-4 col-md-6 col-sm-12 <?php echo esc_attr($portfolio_tags) ; ?>">
							<div class="single-portfolio" style="background-image: url(<?php the_post_thumbnail_url('weta-page-thumbnail-large'); ?>);">
								<div class="portfolio-content-wrapper">
									<div class="portfolio-content">
										<span><?php echo esc_attr($portfolio_tags); ?></span>
										<h4><?php the_title(); ?></h4>
										<a href="<?php echo esc_url($image_url) ;?>" class="popup-image">
											<?php Icons_Manager::render_icon( $settings['image_popup_icon'], ['aria-hidden' => 'true'] );?>
										</a>
										<a href="<?php the_permalink(); ?>" class="portfolio-right-icon">
											<?php Icons_Manager::render_icon( $settings['link_icon'], ['aria-hidden' => 'true'] );?>
										</a>
									</div>
								</div>
							</div>
						</div>
					<?php
            	} ?>
			</div>
		</div>
       
	   <?php wp_reset_query(); ?>

       <?php
	}

    
    function get_portfolio_tags($post_id){
        $tags   = get_the_terms($post_id, 'gallery-cat');
        $_tags  = [];
        
        if ( ! empty ( $tags ) ) {

            foreach($tags as $tag) {
                $_tags[$tag->term_id] = $tag->slug;
            }
        }

        return join (' ', $_tags);
    }

}

$widgets_manager->register( new WETA_Gallery_Post() );