<?php get_header(); ?>

	<?php 
		if ( function_exists( 'rank_math_the_breadcrumbs' ) ) { 
			echo "<div class='breadcrumbs'>";
			rank_math_the_breadcrumbs();
			echo "</div>";
		} 
	?>

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

<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 pb-0 fade-up">
  <?php the_content(); ?>
</div>

<?php include('marquee.php'); ?>
<?php include('countup.php'); ?>

<?php
	$args = [
		'post_type' => 'job',
		'posts_per_page' => 6,
		'meta_query' => [
			'relation' => 'AND',
			[
				'key' => 'job_opening_status',
				'value' => 'in-progress',
				'compare' => 'IN',
			],
		],
	];

	// Prepare the WP Query
	$query = new WP_Query($args);
?>

<?php if ($query->have_posts()): ?>
	
	<section>

		<header class="layout pb-0 fade-up">
			<div class="flex flex-col md:flex-row w-full md:justify-between">
				<h2 class="pb-half">Recent Job Openings</h2>
				<div>
					<a href="/jobs/" class="button button--outline">View All</a>
				</div>
			</div>
		</header>

		<div class="layout pt-half fade-up">
			<div data-hs-carousel='{
			"loadingClasses": "opacity-0",
			"isAutoPlay": true,
			"speed": 5000,
			"isAutoHeight": true,
			"dotsItemClasses": "hs-carousel-active:bg-gold-light hs-carousel-active:border-gold-light size-3 border border-gray-400 rounded-full cursor-pointer dark:border-neutral-600 dark:hs-carousel-active:bg-gold-light dark:hs-carousel-active:border-gold-light"
			}' class="relative">
				<div class="hs-carousel relative overflow-hidden w-full">
					<div class="hs-carousel-body flex flex-nowrap overflow-hidden transition-[height,transform] duration-700 opacity-0">
						<?php while ($query->have_posts()): $query->the_post(); ?>
						<div class="hs-carousel-slide">
						<article class="job-listing p-single text-gray-default dark:text-white-default bg-[#f5f5f5] dark:bg-[#222222]">
							<div class="job-listing__meta-row"><time datetime="<?php echo get_the_date("Y-m-d"); ?>">Posted on <?php echo get_the_date("F j, Y"); ?></time></div>
							<h3 class="job-listing__title mb-single 2xl:mb-half"><?php the_title(); ?></h3>
							<ul class="job-listing__details-list gap-6 mb-single 2xl:mb-half grid grid-cols-2">
								<li>
									<i class="fa-solid fa-fw fa-globe" alt="Languages"></i>
									<?php
										$arr_language_terms = get_the_terms( get_the_ID(), 'language' );
										$arr_languages = [];
										$languages = "";

										if ( !empty( $arr_language_terms ) ) {
											foreach ($arr_language_terms as $language_term) {
												$arr_languages[] = $language_term->name;
											}
										}

										if ( !empty( $arr_language_terms ) ) {
											$languages = implode(", ", $arr_languages);
										}

										echo "<div>" . $languages . "</div>";
									?>
								</li>
								<li>
									<i class="fa-solid fa-fw fa-location-dot" alt="Location"></i>
									<?php
										$arr_country_terms = get_the_terms( get_the_ID(), 'country' );
										$arr_location = [];
										$location = "";
										
										if ( !empty( get_field( "city" ) ) ) {
											$arr_location["city"] = get_field( "city" );
										}

										// if ( !empty( get_field( "state" ) ) ) {
										//   $arr_location["state"] = get_field( "state" );
										// }

										if ( !empty( $arr_country_terms ) ) {
											// $arr_location["country"] = $arr_country_terms[0]->name;
											$arr_location["country"] = $arr_country_terms[0]->name;
										}

										if ( !empty( $arr_location ) ) {
											$location = implode(", ", $arr_location);
										}

										if ( get_field( "remote_job" ) ) {
											$location = "Remote";
										}

										echo "<div>" . $location . "</div>";
									?>
								</li>
								<li>
									<i class="fa-solid fa-fw fa-building" alt="Industry"></i>
									<?php
										$arr_industry_terms = get_the_terms( get_the_ID(), 'industry' );
										$arr_industries = [];
										$industries = "";

										if ( !empty( $arr_industry_terms ) ) {
											foreach ($arr_industry_terms as $industry_term) {
												$arr_industries[] = $industry_term->name;
											}
										}

										if ( !empty( $arr_industry_terms ) ) {
											$industries = implode(", ", $arr_industries);
										}

										echo "<div>" . $industries . "</div>";
									?>
								</li>
								<li>
									<i class="fa-solid fa-fw fa-tags" alt="Sector"></i>
									<?php
										$arr_sector_terms = get_the_terms( get_the_ID(), 'sector' );
										$arr_sectors = [];
										$sectors = "";

										if ( !empty( $arr_sector_terms ) ) {
											foreach ($arr_sector_terms as $sector_term) {
												$arr_sectors[] = $sector_term->name;
											}
										}

										if ( !empty( $arr_sector_terms ) ) {
											$sectors = implode(", ", $arr_sectors);
										}

										echo "<div>" . $sectors . "</div>";
									?>
								</li>
								<li>
									<i class="fa-solid fa-fw fa-briefcase" alt="Employment Type"></i>
									<?php echo get_field( "job_type" ); ?>
								</li>
								<li class="col-span-2">
									<i class="fa-solid fa-fw fa-money-bills" alt="Salary"></i>
									<?php echo get_field( "salary" ); ?>
								</li>
							</ul>
							<div>
								<a href="<?php echo get_the_permalink(); ?>#Apply" class="button mr-2.5"><i class="fa-solid fa-pen-to-square"></i> Apply Now</a>
								<a href="<?php echo get_the_permalink(); ?>" class="button button--outline">View full listing</a>
							</div>
						</article>
						</div>
						<?php endwhile; ?>
					</div>
				</div>
				<div class="hs-carousel-pagination flex justify-center absolute bottom-3 start-0 end-0 gap-x-2"></div>
			</div>
		</div>

	</section>
	
<?php endif; ?>

<?php include('cta-bar.php'); ?>

<?php get_footer(); ?>
