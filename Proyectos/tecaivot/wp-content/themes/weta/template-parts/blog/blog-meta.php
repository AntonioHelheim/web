<?php

/**
 * Template part for displaying post meta
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package weta
 */

 $categories = get_the_terms( $post->ID, 'category' );
 $weta_blog_author = get_theme_mod( 'weta_blog_author', true );
 $weta_blog_date = get_theme_mod( 'weta_blog_date', true );
 $weta_blog_cat = get_theme_mod( 'weta_blog_cat', false );
 $weta_blog_comments = get_theme_mod( 'weta_blog_comments', true );

?>

<?php if (has_post_thumbnail()) {
    ?>
        <ul class="post-meta blog__details-meta mb-20">
            <?php if ( !empty($weta_blog_author) ): ?>
            <li>
                <a href="<?php print esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>">
                    <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.4444 15V13.4444C13.4444 12.6193 13.1167 11.828 12.5332 11.2445C11.9498 10.6611 11.1585 10.3333 10.3333 10.3333H4.11111C3.28599 10.3333 2.49467 10.6611 1.91122 11.2445C1.32778 11.828 1 12.6193 1 13.4444V15" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.22244 7.22222C8.94066 7.22222 10.3336 5.82933 10.3336 4.11111C10.3336 2.39289 8.94066 1 7.22244 1C5.50422 1 4.11133 2.39289 4.11133 4.11111C4.11133 5.82933 5.50422 7.22222 7.22244 7.22222Z" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php print get_the_author();?>
                </a>
            </li>
            <?php endif;?>

            <?php if ( !empty($weta_blog_date) ): ?>
            <li>
                <a href="">
                    <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.5 1.00018V3.10029" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10.0996 1.00018V3.10029" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.6006 5.54955V11.4999C13.6006 13.6 12.5506 15 10.1005 15H4.50018C2.05005 15 1 13.6 1 11.4999V5.54955C1 3.44945 2.05005 2.04938 4.50018 2.04938H10.1005C12.5506 2.04938 13.6006 3.44945 13.6006 5.54955Z" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.5 7.2995H10.1003" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.5 10.7996H7.30014" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    <?php the_time(get_option('date_format')); ?>
                </a>
            </li>
            <?php endif;?>

            <?php if ( !empty($weta_blog_comments) ): ?>
            <li>
                <a href="<?php comments_link();?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.9542 5.18276V8.67567C14.9542 8.90271 14.9454 9.12101 14.9192 9.33059C14.7184 11.6883 13.33 12.8584 10.7714 12.8584H10.4221C10.2038 12.8584 9.99423 12.9632 9.86324 13.1378L8.81539 14.535C8.35258 15.155 7.60159 15.155 7.13878 14.535L6.0909 13.1378C5.97738 12.9894 5.72416 12.8584 5.53205 12.8584H5.18276C2.39717 12.8584 1 12.1686 1 8.67567V5.18276C1 2.62421 2.17886 1.23578 4.52784 1.03494C4.73742 1.00874 4.95572 1 5.18276 1H10.7714C13.557 1 14.9542 2.39717 14.9542 5.18276Z" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.0382 7.3309H11.046" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.98346 7.3309H7.99132" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.92487 7.3309H4.93273" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    <?php comments_number(); ?>
                </a>
            </li>
            <?php endif;?>
        </ul>
    <?php
} else {
    ?>
        <div class="meta-post if-no-thumbnail">
            <ul class="post-meta blog__details-meta mb-20">
                <?php if ( !empty($weta_blog_author) ): ?>
                <li>
                    <a href="<?php print esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );?>">
                        <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.4444 15V13.4444C13.4444 12.6193 13.1167 11.828 12.5332 11.2445C11.9498 10.6611 11.1585 10.3333 10.3333 10.3333H4.11111C3.28599 10.3333 2.49467 10.6611 1.91122 11.2445C1.32778 11.828 1 12.6193 1 13.4444V15" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7.22244 7.22222C8.94066 7.22222 10.3336 5.82933 10.3336 4.11111C10.3336 2.39289 8.94066 1 7.22244 1C5.50422 1 4.11133 2.39289 4.11133 4.11111C4.11133 5.82933 5.50422 7.22222 7.22244 7.22222Z" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php print get_the_author();?>
                    </a>
                </li>
                <?php endif;?>

                <?php if ( !empty($weta_blog_date) ): ?>
                <li>
                    <a href="">
                        <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.5 1.00018V3.10029" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.0996 1.00018V3.10029" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.6006 5.54955V11.4999C13.6006 13.6 12.5506 15 10.1005 15H4.50018C2.05005 15 1 13.6 1 11.4999V5.54955C1 3.44945 2.05005 2.04938 4.50018 2.04938H10.1005C12.5506 2.04938 13.6006 3.44945 13.6006 5.54955Z" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.5 7.2995H10.1003" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.5 10.7996H7.30014" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                        <?php the_time(get_option('date_format')); ?>
                    </a>
                </li>
                <?php endif;?>

                <?php if ( !empty($weta_blog_comments) ): ?>
                <li>
                    <a href="<?php comments_link();?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.9542 5.18276V8.67567C14.9542 8.90271 14.9454 9.12101 14.9192 9.33059C14.7184 11.6883 13.33 12.8584 10.7714 12.8584H10.4221C10.2038 12.8584 9.99423 12.9632 9.86324 13.1378L8.81539 14.535C8.35258 15.155 7.60159 15.155 7.13878 14.535L6.0909 13.1378C5.97738 12.9894 5.72416 12.8584 5.53205 12.8584H5.18276C2.39717 12.8584 1 12.1686 1 8.67567V5.18276C1 2.62421 2.17886 1.23578 4.52784 1.03494C4.73742 1.00874 4.95572 1 5.18276 1H10.7714C13.557 1 14.9542 2.39717 14.9542 5.18276Z" stroke="#7341F1" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M11.0382 7.3309H11.046" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7.98346 7.3309H7.99132" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4.92487 7.3309H4.93273" stroke="#7341F1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                        <?php comments_number(); ?>
                    </a>
                </li>
                <?php endif;?>
            </ul>
        </div>
    <?php
}