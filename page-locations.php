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

<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 pb-single fade-up">
	<?php the_content(); ?>
</div>

<footer class="post-single__footer px-half lg:px-0">
	<section class="post-single__tags pb-double lg:pb-single 2xl:pb-half fade-up">
		<h6 class="mb-5 font-sans">We have recruited positions in the following territories:</h6>
		<ul class="tag-list">
			<li><a href="/locations/united-kingdom/" class="tag-list__tag">United Kingdom</a></li>
			<li><a href="/locations/ireland/" class="tag-list__tag">Ireland</a></li>
			<li><a href="/locations/netherlands/" class="tag-list__tag">Netherlands</a></li>
			<li><a href="/locations/belgium/" class="tag-list__tag">Belgium</a></li>
			<li><a href="/locations/germany/" class="tag-list__tag">Germany</a></li>
			<li><a href="/locations/france/" class="tag-list__tag">France</a></li>
			<li><a href="/locations/italy/" class="tag-list__tag">Italy</a></li>
			<li><a href="/locations/spain/" class="tag-list__tag">Spain</a></li>
			<li><a href="/locations/portugal/" class="tag-list__tag">Portugal</a></li>
			<li><a href="/locations/austria/" class="tag-list__tag">Austria</a></li>
			<li><a href="/locations/switzerland/" class="tag-list__tag">Switzerland</a></li>
			<li><a href="/locations/poland/" class="tag-list__tag">Poland</a></li>
			<li><a href="/locations/czech-republic/" class="tag-list__tag">Czech Republic</a></li>
			<li><a href="/locations/bulgaria/" class="tag-list__tag">Bulgaria</a></li>
			<li><a href="/locations/romania/" class="tag-list__tag">Romania</a></li>
			<li><a href="/locations/hungary/" class="tag-list__tag">Hungary</a></li>
			<li><a href="/locations/slovak-republic/" class="tag-list__tag">Slovak Republic</a></li>
			<li><a href="/locations/sweden/" class="tag-list__tag">Sweden</a></li>
			<li><a href="/locations/denmark/" class="tag-list__tag">Denmark</a></li>
			<li><a href="/locations/norway/" class="tag-list__tag">Norway</a></li>
			<li><a href="/locations/united-arab-emirates/" class="tag-list__tag">United Arab Emirates</a></li>
			<li><a href="/locations/united-states/" class="tag-list__tag">United States</a></li>
			<li><a href="/locations/canada/" class="tag-list__tag">Canada</a></li>
			<li><a href="/locations/egypt/" class="tag-list__tag">Egypt</a></li>
		</ul>
	</section>
</footer>

<?php include('marquee.php'); ?>
<?php include('countup.php'); ?>
<?php include('cta-bar.php'); ?>

<?php get_footer(); ?>
