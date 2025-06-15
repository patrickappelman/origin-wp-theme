<?php get_header(); ?>

<?php 
$thumbnail = get_the_post_thumbnail_url();

if($thumbnail) {
  echo "<div class='hero hero--has-image'><div class='hero__background'>";
  echo get_the_post_thumbnail();
  echo "</div>";
} else {
  echo "<div class='hero hero--small'>";
}

?>
    <div class="hero__container fade-up">
        <h1 class="reverse text-display-5 w-full"><?php the_title(); ?></h1>
    </div>
</div>

<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0">
  <?php the_content(); ?>
</div>

<?php include('cta-bar.php'); ?>

<?php get_footer();