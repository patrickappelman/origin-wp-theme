<?php 
	get_header();
	global $post;
?>

<?php 
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) { 
		echo "<div class='breadcrumbs'>";
		rank_math_the_breadcrumbs();
		echo "</div>";
	}
?>

<article class="post-single">

	<header class="post-single__header hero hero--post">

		<div class="hero__background">
			<?php echo get_the_post_thumbnail(); ?>
		</div>

		<div class="hero__container fade-up">

			<?php
				// Get the categories
				$categories = get_the_category();

				// If categories exist
				if( $categories ) {
					echo '<div class="hero__meta hero__meta--top">';
					$arr_cats = [];
					foreach( $categories AS $cat ) {
							echo "<a class='button button--outline button--light' href='" . get_category_link($cat) . "'>" . $cat->name . "</a>";
					}
					echo '</div>';
				}
			?>
			<h1 class="post-single__title reverse text-display-5 w-full"><?php the_title(); ?></h1>
			<div class="hero__meta hero__meta--bottom">
				<div>
					<i class="fa-solid fa-user" alt="Author"></i> <?php echo get_the_author_meta('display_name', get_post_field('post_author', $post->ID)); ?>
				</div>
				<time datetime="<?php echo get_the_date("Y-m-d"); ?>">
					<i class="fa-solid fa-calendar-days" alt="Published On"></i>
					<?php echo get_the_date("F j, Y"); ?>
				</time>
			</div>
		</div>
	</header>

	<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 fade-up">
		<?php the_content(); ?>
	</div>

	<footer class="post-single__footer px-half lg:px-0">
		
		<?php 

			// Set terms array
			$arr_terms = [];

			// Add categories to terms array
			// Note: We already got the $categories variable at the beginning of this file.
			foreach( $categories AS $cat ) {
				$arr_terms[] = [
					'name' => $cat->name,
					'link' => get_category_link($cat)
				];
			}

			// Get the languages
			$languages = get_the_terms( $post->ID , 'language' );
			$arr_language_slugs = [];

			// Check for errors first, as 'language' is a custom taxonomy
			if ( empty( $languages->errors ) ) {

				// Add languages to terms array
				foreach( $languages as $language ) {
					$arr_terms[] = [
						'name' => $language->name,
						'link' => get_term_link( intval( $language->term_id ) , 'language' ),
					];
					$arr_language_slugs = $language->slug;
				}
				
			}
			
			// Get the countries
			$countries = get_the_terms( $post->ID , 'country' );
			$arr_country_slugs = [];

			// Check for errors first, as 'country' is a custom taxonomy
			if( empty( $countries->errors ) ) {

				// Add countries to terms array
				foreach( $countries as $country ) {
					$arr_terms[] = [
						'name' => $country->name,
						'link' => get_term_link( intval( $country->term_id ) , 'country' ),
					];
					$arr_country_slugs = $country->slug;
				}

			}

			// Get the industries
			$industries = get_the_terms( $post->ID , 'industry' );
			$arr_industry_slugs = [];

			// Check for errors first, as 'industry' is a custom taxonomy
			if( empty( $industries->errors ) ) {

				// Add industries to terms array
				foreach( $industries as $industry ) {
					$arr_terms[] = [
						'name' => $industry->name,
						'link' => get_term_link( intval( $industry->term_id ) , 'industry' ),
					];
					$arr_industry_slugs = $industry->slug;
				}

			}

			// Get the sectors
			$sectors = get_the_terms( $post->ID , 'industry' );
			$arr_sector_slugs = [];

			// Check for errors first, as 'sector' is a custom taxonomy
			if( empty( $sectors->errors ) ) {

				// Add sectors to terms array
				foreach( $sectors as $sector ) {
					// $arr_terms[] = [
					// 	'name' => $sector->name,
					// 	'link' => get_term_link( intval($sector->term_id),'sector')
					// ];
					$arr_sector_slugs = $sector->slug;
				}

			}

			if( $arr_terms ) {

				echo '<section class="post-single__tags pb-double lg:pb-single 2xl:pb-half fade-up"><h6 class="mb-5 font-sans">Tags:</h6><ul class="tag-list">';

				foreach( $arr_terms as $term ) {
					echo '<li><a href="' . $term['link'] . '" class="tag-list__tag">' . $term['name'] . '</a></li>';
				}

				echo '</ul></section>';

			}
		?>

		<section class="post-single__share pb-double fade-up">
			<h6 class="mb-5 font-sans">Share This Article:</h6>
			<?php 
				global $wp;
				$share_url = urlencode( home_url( $wp->request ) );
				$share_title = urlencode( get_the_title() );
			?>
			<ul class="share-list">
				<li>
					<a rel="nofollow" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" class="share-list__platform">
						<i class="fa-brands fa-facebook" alt="Facebook Logo"></i>
						<span class="hidden">Share on Facebook</span>
					</a>
				</li>
				<li>
					<a rel="nofollow" href="https://x.com/intent/post?text=<?php echo $share_title; ?>&url=<?php echo $share_url; ?>" class="share-list__platform">
						<i class="fa-brands fa-x-twitter" alt="X Logo"></i>
						<span class="hidden">Share on X</span>
					</a>
				</li>
				<li>
					<a rel="nofollow" href="https://www.linkedin.com/shareArticle?url=<?php echo $share_url; ?>" class="share-list__platform">
						<i class="fa-brands fa-linkedin" alt="LinkedIn Logo"></i>
						<span class="hidden">Share on LinkedIn</span>
					</a>
				</li>
				<li>
					<a rel="nofollow" href="mailto:?subject=<?php echo $share_title . urlencode(" - Origin Recruitment"); ?>&body=<?php echo $share_url; ?>" class="share-list__platform">
						<i class="fa-solid fa-envelope" alt="Email Icon"></i>
						<span class="hidden">Share via email</span>
					</a>
				</li>
			</ul>
		</section>
	</footer>

	<?php
		$args_tax = [
			'post_type' => 'job',
			'posts_per_page' => 10,
			'tax_query' => [
				'relation' => 'OR',
				[
					'taxonomy' => 'language',
					'field' => 'slug',
					'terms' => $arr_language_slugs,
				],
				// [
				// 	'taxonomy' => 'country',
				// 	'field' => 'slug',
				// 	'terms' => $arr_country_slugs,
				// ],
				// [
				// 	'taxonomy' => 'industry',
				// 	'field' => 'slug',
				// 	'terms' => $arr_industry_slugs,
				// ],
				// [
				// 	'taxonomy' => 'sector',
				// 	'field' => 'slug',
				// 	'terms' => $arr_sector_slugs,
				// ],
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
		$query_tax = new WP_Query( $args_tax );
	?>

	<?php if ( $query_tax->have_posts() ): ?>
		
		<section>

			<header class="layout py-0 fade-up">
				<div class="flex flex-col md:flex-row w-full md:justify-between">
					<h2 class="pb-half"><?php echo single_term_title("",false) ?> Related Job Openings</h2>
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
													$arr_industry_terms = get_the_terms( get_the_ID() , 'industry' );
													$arr_industries = [];
													$industries = "";

													if ( !empty( $arr_industry_terms ) ) {
														foreach ($arr_industry_terms as $industry_term) {
															$arr_industries[] = $industry_term->name;
														}
													}

													if ( !empty( $arr_industry_terms ) ) {
														$industries = implode(", " , $arr_industries);
													}

													echo "<div>" . $industries . "</div>";
												?>
											</li>
											<li>
												<i class="fa-solid fa-fw fa-tags" alt="Sector"></i>
												<?php
													$arr_sector_terms = get_the_terms( get_the_ID() , 'sector' );
													$arr_sectors = [];
													$sectors = "";

													if ( !empty( $arr_sector_terms ) ) {
														foreach ( $arr_sector_terms as $sector_term ) {
															$arr_sectors[] = $sector_term->name;
														}
													}

													if ( !empty( $arr_sector_terms ) ) {
														$sectors = implode( ", " , $arr_sectors );
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

	<?php

		//get the categories a post belongs to
		$cats = get_the_category($post->ID);

		$cat_array = [];

		if ( $cats ) {
			foreach( $cats as $key1 => $cat ) {
				$cat_array[$key1] = $cat->slug;
			}
		}

		//get the tags a post belongs to
		$tags = get_the_tags( $post->ID );

		$tag_array = [];

		if ( $tags ) {
			foreach( $tags as $key2 => $tag ) {
				$tag_array[$key2] = $tag->slug;
			}
		}

		// construct the query arguments
		$args = [
			'post_type' => 'post',
			'posts_per_page' => 3,
			'orderby' => 'rand',
			'post__not_in' => [ $post->ID ],
			'tax_query' => [
				'relation' => 'OR',
				[
					'taxonomy' => 'category',
					'field' => 'slug',
					'terms' => $cat_array,
					'include_children' => false,
				],
				[
					'taxonomy' => 'post_tag',
					'field' => 'slug',
					'terms' => $tag_array,
				],
			],
		];

		$the_query = new WP_Query( $args );

	?>

	<?php if ( $the_query->have_posts() ) : ?>

		<aside class="post-single__posts-archive pb-single fade-up">

			<header class="px-single text-center">
				<h3>More Insights from Origin</h3>
			</header>

			<section class="layout post-archive pt-double lg:pt-single">

				<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>

					<article class="post-archive__article">
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
											$arr_cats = [];
											foreach($categories AS $cat) {
												$arr_cats[] .= "<a class='post-archive__article-category hover:text-gold' href='" . get_category_link($cat) . "'>" . $cat->name . "</a>";
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

				<?php endwhile; ?>

			</section>
		</aside>

	<?php endif; ?>
	<?php wp_reset_postdata(); ?>

</article>

<?php include('cta-bar.php'); ?>

<?php get_footer(); ?>