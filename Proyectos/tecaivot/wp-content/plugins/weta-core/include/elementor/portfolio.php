<?php

namespace WETACore\Widgets;

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Widget_Base;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

/**
 * WETA Core
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class WETA_Portfolio extends Widget_Base {

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
        return 'weta_portfolio';
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
        return __( 'Portfolio', 'weta-core' );
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
        return ['weta_core'];
    }

    /**
     * Retrieve the list of scripts the widget depended on.
     *
     * Used to set scripts dependencies required to run the widget.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return array Widget scripts dependencies.
     */
    public function get_script_depends() {
        return ['weta-portfolio'];
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

        // Project group
        $this->start_controls_section(
            'weta_portfolio',
            [
                'label'       => esc_html__( 'Portfolio', 'weta-core' ),
                'description' => esc_html__( 'Control all the style settings from Style tab', 'weta-core' ),
                'tab'         => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'important_note',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'Add photos from Dashboard Portfolio.', 'weta-core' ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
            ]
        );

        $this->add_control(
            'design_style',
            [
                'label' => esc_html__('Select Layout', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'layout-1' => esc_html__('Layout 1', 'weta-core'),
                    'layout-2' => esc_html__('Layout 2', 'weta-core'),
                ],
                'default' => 'layout-1',
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__('Posts Per Page', 'weta-core'),
                'description' => esc_html__('Leave blank or enter -1 for all.', 'weta-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => '6',
            ]
        ); 

        $this->add_control(
            'order',
            [
                'label' => esc_html__('Order', 'weta-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'asc' 	=> esc_html__( 'Ascending', 'weta-core' ),
                    'desc' 	=> esc_html__( 'Descending', 'weta-core' )
                ],
                'default' => 'desc',

            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'content_layout_stylesheet',
            [
                'label' => esc_html__( 'Design LAyout', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_layout_background',
            [
                'label' => esc_html__( 'Background', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .portfolio-section' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'content_layout_padding',
            [
                'label'      => esc_html__( 'Padding', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .portfolio-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'portfolio_stylesheet',
            [
                'label' => esc_html__( 'Portfolio', 'weta-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        // Title
        $this->add_control(
            '_heading_title',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Title', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .project-item .project-content .title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Text Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-item .project-content .title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .project-item .project-content .title',
            ]
        );

        // Subtitle
        $this->add_control(
            '_heading_subtitle',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Subtitle', 'weta-core' ),
                'separator' => 'before'
            ]
        );

        $this->add_responsive_control(
            'subtitle_spacing',
            [
                'label' => __( 'Bottom Spacing', 'weta-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .project-item .project-content span' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => __( 'Color', 'weta-core' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .project-item .project-content span p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .project-item .project-content span',
            ]
        );

        $this->add_control(
            '_heading_pagination',
            [
                'label'     => esc_html__( 'Pagination', 'weta-core' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( '_tabs_pagination' );
        
        $this->start_controls_tab(
            'pagination_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'text-domain' ),
            ]
        );
        
        $this->add_control(
            'pagination_color',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pagination-wrap .pagination-list li a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'pagination_background',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pagination-wrap .pagination-list li a' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'pagination_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'text-domain' ),
            ]
        );
        
        $this->add_control(
            'pagination_color_hover',
            [
                'label'     => esc_html__( 'Color', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pagination-wrap .pagination-list li.active a' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .pagination-wrap .pagination-list li a:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'pagination_background_hover',
            [
                'label'     => esc_html__( 'Background', 'weta-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pagination-wrap .pagination-list li.active a' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .pagination-wrap .pagination-list li a:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->add_control(
            'pagination_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'weta-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .pagination-wrap .pagination-list li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        $posts_per_page = (!empty($settings['posts_per_page'])) ? $settings['posts_per_page'] : '5';
        $order = (!empty($settings['order'])) ? $settings['order'] : 'desc';

        $paged = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;

        $portfolios = new \WP_Query( [
            'post_type'      => 'portfolio',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'order'          => $order,
            'paged' => $paged, 
        ] );

        ?>

        <?php if ( $settings['design_style']  == 'layout-1' ): ?>

            <section class="portfolio-section pt-120 pb-120">
                <div class="container">
                    <div class="row gy-4 portfolio-wrap">
                        <?php while ( $portfolios->have_posts() ) {
                            $portfolios->the_post();
                            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' )
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="project-item">
                                <div class="project-img">
                                    <img src="<?php the_post_thumbnail_url( 'large' );?>" alt="project">
                                </div>
                                <div class="project-content">
                                    <h3 class="title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title() ; ?></a>
                                    </h3>
                                    <span><?php echo the_excerpt(); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                        }?>
                    </div>
                    <div class="pagination-wrap">
                        <?php
                            $big = 999999999;
                            echo paginate_links( array(
                                'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
                                'format' => '?paged=%#%',
                                'current' => max( 1, get_query_var('paged') ),
                                'total' => $portfolios->max_num_pages,
                                'prev_text' => '<i class="fa-solid fa-chevrons-left"></i>',
                                'next_text' => '<i class="fa-solid fa-chevrons-right"></i>'
                            ) );
                        ?>
                    </div>
                </div>
            </section>
        
        <?php elseif ( $settings['design_style']  == 'layout-2' ): ?>

            <section class="portfolio-section pt-120 pb-120">
                <div class="container">
                    <div class="row gy-4 portfolio-wrap">
                        <?php while ( $portfolios->have_posts() ) {
                            $portfolios->the_post();
                            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' )
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="project-item">
                                <div class="project-img">
                                    <img src="<?php the_post_thumbnail_url( 'large' );?>" alt="project">
                                </div>
                                <div class="project-content">
                                    <h3 class="title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title() ; ?></a>
                                    </h3>
                                    <span><?php echo the_excerpt(); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                        }?>
                    </div>
                </div>
            </section>
        
        <?php endif; ?>

	    <?php wp_reset_query();?>

        <?php
    }
}

$widgets_manager->register( new WETA_Portfolio() );