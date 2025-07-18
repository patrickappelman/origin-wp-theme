<?php

get_header(); ?>

<?php 
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) { 
		echo "<div class='breadcrumbs'>";
		rank_math_the_breadcrumbs();
		echo "</div>";
	}
?>

<div class="max-w-4xl mx-auto px-4">

  <!-- example react component -->
  <div id="render-react-example-here"></div>
  <!-- end example react component -->

  <div class="prose max-w-full">
    <?php if (have_posts()) {
      while(have_posts()) {
        the_post(); ?>
        <div>
          <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
          <?php the_content(); ?>
        </div>
      <?php }
    } ?>
  </div>
</div>

<?php include('cta-bar.php'); ?>

<?php get_footer();