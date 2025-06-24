<?php get_header(); ?>

<?php 
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) { 
		echo "<div class='breadcrumbs'>";
		rank_math_the_breadcrumbs();
		echo "</div>";
	}
?>

<?php
// GET DEFAULTS

if ( is_tax() || is_category() || is_tag() ) {
	$term = get_queried_object();
	$taxonomy = $term->taxonomy;
	$term_id = $term->term_id;
	$slug = $term->slug;

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

<?php if ( is_tax() ): ?>

	<?php
		$args_tax = [
			'post_type' => 'job',
			'posts_per_page' => -1,
			'tax_query' => [
				'relation' => 'AND',
				[
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $slug,
				],
			],
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
		$query_tax = new WP_Query($args_tax);
	?>

	<?php if ($query_tax->have_posts()): ?>
		<section class="layout py-0 fade-up">
			<div class="flex flex-col md:flex-row w-full md:justify-between">
				<h2 class="pb-half"><?php echo single_term_title("",false) ?> Job Openings</h2>
				<div>
					<a href="/jobs/?<?php echo $taxonomy . "=" . $slug ?>" class="button button--outline">View All</a>
				</div>
			</div>
		</section>
		<section class="layout pt-half fade-up">
			<div data-hs-carousel='{
			"loadingClasses": "opacity-0",
			"isAutoPlay": true,
			"speed": 5000,
			"isAutoHeight": true,
			"dotsItemClasses": "hs-carousel-active:bg-gold-light hs-carousel-active:border-gold-light size-3 border border-gray-400 rounded-full cursor-pointer dark:border-neutral-600 dark:hs-carousel-active:bg-gold-light dark:hs-carousel-active:border-gold-light"
			}' class="relative">
				<div class="hs-carousel relative overflow-hidden w-full">
					<div class="hs-carousel-body flex flex-nowrap overflow-hidden transition-[height,transform] duration-700 opacity-0">
						<?php while ($query_tax->have_posts()): $query_tax->the_post(); ?>
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
		</section>
	<?php endif; ?>
<?php endif; ?>



<?php
$args_post = [
	'post_type' => 'post',
	'posts_per_page' => 6,
	'tax_query' => [
		'relation' => 'AND',
		[
			'taxonomy' => $taxonomy,
			'field' => 'slug',
			'terms' => $slug,
		],
	],
];

// Prepare the WP Query
$query_post = new WP_Query($args_post);
?>

<?php if ($query_post->have_posts()): ?>
<section>

	<header class="layout pt-0 pb-single fade-up">
		<h2><?php echo single_term_title("",false) ?> Insights</h2>
	</header>

	<div class="layout post-archive pt-0 ">
	
		<?php while ($query_post->have_posts()): $query_post->the_post(); ?>

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
								$arr_cats = [];
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

		<?php endwhile; ?>

		<?php
			$pagination = paginate_links( [
				'total' => $query_post->max_num_pages,
				'current' => (get_query_var('page')) ? get_query_var('page') : 1,
				'format' => '?page=%#%',
				'show_all' => false,
				'prev_next' => true,
				'next_text' => '>',
				'prev_text' => '<',
			]);

			if ($pagination) {
				echo '<nav class="pagination" aria-label="Pagination">' . $pagination . '</nav>'; 
			}
		?>

	</div>



</section>

<?php endif; ?>

<?php include('marquee.php'); ?>
<?php include('countup.php'); ?>
<?php include('cta-bar.php'); ?>

<?php get_footer();