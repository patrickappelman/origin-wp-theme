<?php get_header(); ?>

<?php
// GET DEFAULTS

if ( is_tax() || is_category() || is_tag() ) {
    $term = get_queried_object();
    $taxonomy = $term->taxonomy;
    $term_id = $term->term_id;

    // Get Rank Math taxonomy meta for term-specific SEO title
    $rank_math_meta = get_option( 'rank_math_taxonomy_meta' );
    $seo_title = isset( $rank_math_meta[$taxonomy][$term_id]['title'] ) ? $rank_math_meta[$taxonomy][$term_id]['title'] : '';

    // If no term-specific title, check the taxonomy's title template
    if ( empty( $seo_title ) ) {
        $rank_math_options = get_option( 'rank-math-options-titles' );

        $title_template = isset( $rank_math_options['tax_' . $taxonomy . '_title'] ) ? $rank_math_options['tax_' . $taxonomy . '_title'] : '';
        
        if ( ! empty( $title_template ) ) {

            $seo_title = $title_template;
        }
    }

    // Process Rank Math variables (e.g., %term%, %sep%)
    if ( ! empty( $seo_title ) ) {

        $seo_title = str_replace( "%sitename%", "", $seo_title );
        $seo_title = str_replace( "%sep%", "", $seo_title );
        $seo_title = str_replace( "%page%", "", $seo_title );
        $seo_title = trim($seo_title);
        
        if ( class_exists( 'RankMath\Helper' ) ) {
            // Check if Rank Math Helper class
            $seo_title = RankMath\Helper::replace_vars( $seo_title, $term );
        } else {
            // Fallback if Rank Math Helper class is unavailable
            $seo_title = str_replace( '%term%', $term->name, $seo_title );
        }
    }

    // Final fallback to default term title
    if ( !empty( $seo_title ) ) {
        $page_title = $seo_title;
    } else {
        $page_title  = get_the_archive_title();
    }

}

?>


<div class="hero hero--small">
    <div class="hero__container fade-up">
        <div class="hero__meta hero__meta--top">
            <?php if ( is_category() ) { ?>
                <a class="button button--outline button--light" href="<?php echo get_the_permalink(get_option('page_for_posts')) ?>"><?php echo get_the_title(get_option('page_for_posts')); ?></a>
            <?php } else if ( is_tax("language") ) { ?>
                <a class="button button--outline button--light" href="<?php echo get_the_permalink(12) ?>"><?php echo get_the_title(12); ?></a>
            <?php } else if ( is_tax("country") ) { ?>
                <a class="button button--outline button--light" href="<?php echo get_the_permalink(16) ?>"><?php echo get_the_title(16); ?></a>
            <?php } else if ( is_tax("industry") ) { ?>
                <a class="button button--outline button--light" href="<?php echo get_the_permalink(14) ?>"><?php echo get_the_title(14); ?></a>
            <?php } ?>
        </div>
        <h1 class="reverse text-display-5 w-full"><?php echo $page_title; ?></h1>
    </div>
</div>

<?php 
    if ( is_category() ) {
?>
    <section class="layout">
        <div class="post-single__body prose py-single 2xl:py-half px-half mx-auto lg:px-0 fade-up">
            <?php if ( !empty( category_description() ) ) {
                echo category_description();
            } else {
                echo "<p>Stay ahead with the latest on " . single_term_title("",false) . " from Origin Recruitment.</p>";
            }
            echo "<p class='mb-0'><a href='" . get_the_permalink(get_option('page_for_posts')) . "'>View more insights →</a></p>";
            ?>
        </div>
    </section>
<?php
    } else if ( is_tax("language") ) {
?>
    <section class="layout">
        <div class="post-single__body prose 2xl:py-half px-half mx-auto lg:px-0 fade-up">
            <?php if ( !empty( category_description() ) ) {
                echo category_description();
            } else {
                echo "<p>Multilingual and bilingual jobs, trends, insights, and more for " . single_term_title("",false) . "-speaking job seekers and employers.</p>";
                echo "<p>" . get_the_excerpt(12) . "</p>";
            }
            echo "<p class='mb-0'><a href='" . get_the_permalink(12) . "'>View more language solutions →</a></p>";
            ?>
        </div>
    </section>
<?php
    } else if ( is_tax("country") ) {
?>
    <section class="layout">
        <div class="post-single__body prose 2xl:py-half px-half mx-auto lg:px-0 fade-up">
            <?php if ( !empty( category_description() ) ) {
                echo category_description();
            } else {
                echo "<p class='mb-0'>Multilingual and bilingual jobs, trends, insights, and more in " . single_term_title("",false) . " for job seekers and employers.</p>";
                echo "<p>" . get_the_excerpt(16) . "</p>";
            } 
            echo "<p class='mb-0'><a href='" . get_the_permalink(16) . "'>View our global reach →</a></p>";
            ?>
        </div>
    </section>
<?php
    } else if ( is_tax("industry") ) {
?>
    <section class="layout">
        <div class="post-single__body prose 2xl:py-half px-half mx-auto lg:px-0 fade-up">
            <?php if ( !empty( category_description() ) ) {
                echo category_description();
            } else {
                echo "<p class='mb-0'>Multilingual and bilingual jobs, trends, insights, and more in the " . strtolower( single_term_title("",false) ) . " industry for job seekers and employers.</p>";
                echo "<p>" . get_the_excerpt(14) . "</p>";
            } 
            echo "<p class='mb-0'><a href='" . get_the_permalink(14) . "'>View more industries →</a></p>";
            ?>
        </div>
    </section>
<?php
    }
?>

<?php if (have_posts()) { ?>
    <section class="layout pt-0 fade-up">
        <h2><?php echo single_term_title("",false) ?> Insights</h2>
    </section>
<section class="layout post-archive pt-0">

        <?php
        while(have_posts()) {
        the_post(); ?>
        <article class="post-archive__article fade-up">
            <a href="<?php the_permalink() ?>" class="post-archive__article-image-wrapper">
                <?php echo get_the_post_thumbnail(); ?>
            </a>
            <header>
                <div class="post-archive__article-meta-row">
                    <span><?php echo get_the_date("F j, Y"); ?></span><span>•</span>
                    <span><?php 
                        $categories = get_categories();
                        if( $categories ) {
                            $arr_cats = array();
                            foreach($categories AS $cat) {
                                $arr_cats[] .= "<a class='post-archive__article-category hover:text-gold' href='" . get_category_link($cat) . "'>" . $cat->name . "</a>";
                            }
                            echo implode(", ", $arr_cats);
                        }
                    ?></span>
                </div>
                <h4 class="post-archive__article-title"><?php the_title(); ?></h4>
            </header>
            <div class="post-archive__article-excerpt"><?php the_excerpt(); ?></div>
            <p><a href="<?php the_permalink() ?>" class="button">Read More →</a></p>
        </article>
        <?php }
?>
</section>
<?php } ?>

<?php include('marquee.php'); ?>
<?php include('countup.php'); ?>
<?php include('cta-bar.php'); ?>

<?php get_footer();