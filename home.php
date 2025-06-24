<?php get_header(); ?>

<?php 
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) { 
		echo "<div class='breadcrumbs'>";
		rank_math_the_breadcrumbs();
		echo "</div>";
	}
?>

<?php 
$thumbnail = get_the_post_thumbnail(96);

if($thumbnail) {
  echo "<div class='hero hero--has-image'><div class='hero__background'>";
  echo $thumbnail;
  echo "</div>";
} else {
  echo "<div class='hero hero--small'>";
}

?>
        <div class="hero__container fade-up">
        <h1 class="reverse text-display-5 w-full md:w-3/4 lg:w-1/2"><?php echo get_the_archive_title(); ?></h1>
    </div>
</div>


 <?php $terms = get_terms( array(
        'taxonomy'   => 'category',
        'hide_empty' => true,
    ));

    if ($terms) {
        echo '<div class="layout pb-0 fade-up"><h6 class="mb-5 font-sans">Browse by Category</h6><ul class="tag-list">';
        foreach($terms AS $term) {
            echo '<li><a href="' . get_category_link($term) . '" class="tag-list__tag">' . $term->name . '</a></li>';
        }
        echo "</ul></div>";
    }
?>

<section class="layout post-archive">

<?php  if (have_posts()) {
        while(have_posts()) {
            the_post();    
?>
        <article class="post-archive__article fade-up">
            <a href="<?php the_permalink() ?>" class="post-archive__article-image-wrapper">
                <?php echo get_the_post_thumbnail(); ?>
            </a>
            <header>
                <div class="post-archive__article-meta-row">
                    <time datetime="<?php echo get_the_date("Y-m-d"); ?>"><?php echo get_the_date("F j, Y"); ?></time>
                    <span>•</span>
                    <span>

<?php $categories = get_categories();
            if( $categories ) {
                $arr_cats = array();
                foreach($categories AS $cat) {
                    $arr_cats[] .= "<a class='post-archive__article-category hover:text-gold' href='" . esc_url(get_category_link($cat)) . "'>" . $cat->name . "</a>";
                }
                echo implode(", ", $arr_cats);
            }
?>

                    </span>
                </div>
                <h4 class="post-archive__article-title"><?php the_title(); ?></h4>
            </header>
            <div class="post-archive__article-excerpt"><?php the_excerpt(); ?></div>
            <p><a href="<?php the_permalink() ?>" class="button">Read More →</a></p>
        </article>

<?php 
        }
    }
?>

</section>

<?php include('cta-bar.php'); ?>

<?php get_footer();