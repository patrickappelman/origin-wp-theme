<?php get_header(); ?>

	<?php 
		if ( function_exists( 'rank_math_the_breadcrumbs' ) ) { 
			echo "<div class='breadcrumbs'>";
			rank_math_the_breadcrumbs();
			echo "</div>";
		}
	?>

	<?php 
		if ( get_the_post_thumbnail_url() ) {
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

<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 fade-up">
	<?php the_content(); ?>
</div>

<?php $form_shortcode = get_field( 'form_shortcode' ); if ( $form_shortcode ) : ?>
	<section id="Vacancy" class="flex items-center px-[15px] md:px-single">
		<div class="prose mx-auto">
			<h2 class="leading-tight" style="margin-top:0!important;">Submit Vacancy</h2>
			<p class="leading-relaxed mb-single">Use the form below to provide details about your vacancy.</p>
			<?php echo do_shortcode( $form_shortcode ); ?>
		</div>
	</section>
<?php endif; ?>

<?php include('marquee.php'); ?>
<?php include('countup.php'); ?>
<?php include('cta-bar.php'); ?>

<?php get_footer(); ?>
