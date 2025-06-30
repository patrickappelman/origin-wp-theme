<?php get_header(); 
global $post;
?>

<?php 
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) { 
		echo "<div class='breadcrumbs'>";
		rank_math_the_breadcrumbs();
		echo "</div>";
	}
?>

<article class="job-single">
	<header class="job-single__header hero hero--job">
		<div class="hero__container fade-up">
				<div class="hero__meta hero__meta--top"></div>
			<h1 class="job-single__title reverse text-display-5 w-full"><?php the_title(); ?></h1>
			<div class="hero__meta hero__meta--bottom">
				<time datetime="<?php echo get_the_date('Y-m-d'); ?>">
					<i class="fa-solid fa-calendar-days" alt="Published On"></i>
					Posted on <?php echo get_the_date('F j, Y'); ?>
				</time>
			</div>
		</div>
	</header>
	<?php
		$arr_job_status = [
			'In-progress' => [
				'alert-type' => 'success',
				'can-apply' => true,
				'msg' => 'This job is now accepting applications.'
			],
			'Filled' => [
				'alert-type' => 'danger',
				'can-apply' => false,
				'msg' => 'This job has been filled.'
			],
			'Cancelled' => [
				'alert-type' => 'danger',
				'can-apply' => false,
				'msg' => 'This job is no longer available.'
			],
			'Declined' => [
				'alert-type' => 'danger',
				'can-apply' => false,
				'msg' => 'This job is no longer available.'
			],
			'Inactive' => [
				'alert-type' => 'danger',
				'can-apply' => false,
				'msg' => 'This job is no longer available.'
			],
			'Submitted by client' => [
				'alert-type' => 'warning',
				'can-apply' => false,
				'msg' => 'This job is no longer available.'
			],
		];
		
		$job_status = get_field( 'job_opening_status' );
	?>
	<div class="layout gap-double xl:gap-double xl:pb-double pb-double 2xl:pb-single pt-half flex w-full flex-col md:pt-0 lg:flex-row-reverse">
		<aside class="job-single__sidebar sticky__sidebar md:py-triple xl:py-double 2xl:py-single relative w-full py-0 !pb-0 lg:w-1/2">
			<div class="job-single__details sticky__sidebar-details p-single text-gray-default dark:text-white-default bg-[#f5f5f5] dark:bg-[#222222]">
				<?php if ( !$arr_job_status[$job_status]['can-apply'] ) { ?>
				<div class="job-single__status mb-half">
					<div class="alert-soft <?php echo 'alert-soft--' . $arr_job_status[$job_status]['alert-type'] ?>" role="alert" tabindex="-1" aria-labelledby="job-status-label">
						<span id="job-status-label" class="font-bold">Job Status:</span> <?php echo $arr_job_status[$job_status]['msg'] ?>
					</div>
				</div>
				<?php } ?>
				<h2 class="job-single__details-title mb-single 2xl:mb-half">
					Job Details
				</h2>
				<dl class="job-single__details-list gap-6 mb-single 2xl:mb-half grid grid-cols-2">
					<div class="col-span-2">
						<dt>Job Title</dt>
						<dd><?php the_title(); ?></dd>
					</div>
					<div>
						<dt>Languages</dt>
						<dd>
							<i class="fa-solid fa-fw fa-globe" alt="Languages"></i>
							<?php
								$arr_language_terms = get_the_terms( get_the_ID(), 'language' );
								$arr_languages = [];
								$languages = "";

								if ( !empty( $arr_language_terms ) ) {
									foreach ($arr_language_terms as $language_term) {
										$arr_languages[] = "<a class='hover:text-gold' href='/jobs/?language=" . $language_term->slug . "'>" . $language_term->name . "</a>";
									}
								}

								if ( !empty( $arr_language_terms ) ) {
									$languages = implode(", ", $arr_languages);
								}

								echo "<div>" . $languages . "</div>";
							?>
						</dd>
					</div>
					<div>
						<dt>Location</dt>
						<dd>
							<i class="fa-solid fa-fw fa-location-dot" alt="Location"></i>
							<?php
								$arr_country_terms = get_the_terms( get_the_ID(), 'country' );
								$arr_location = [];
								$location = "";
								
								if ( !empty( get_field( 'city' ) ) ) {
									$arr_location["city"] = get_field( 'city' );
								}

								if ( !empty( $arr_country_terms ) ) {
									$arr_location["country"] = "<a class='hover:text-gold' href='/jobs/?country=" . $arr_country_terms[0]->slug . "'>" . $arr_country_terms[0]->name . "</a>";
								}

								if ( !empty( $arr_location ) ) {
									$location = implode(", ", $arr_location);
								}

								if ( get_field( 'remote_job' ) ) {
									$location = "Remote";
								}

								echo "<div>" . $location . "</div>";
							?>
						</dd>
					</div>
					<div>
						<dt>Industry / Sectors</dt>
						<dd>
							<i class="fa-solid fa-fw fa-building" alt="Industry"></i>
							<?php
								$arr_industry_terms = get_the_terms( get_the_ID(), 'industry' );
								$arr_industries = [];
								$industries = "";

								if ( !empty( $arr_industry_terms ) ) {
									foreach ($arr_industry_terms as $industry_term) {
										$arr_industries[] = "<a class='hover:text-gold' href='/jobs/?industry=" . $industry_term->slug . "'>" . $industry_term->name . "</a>";
									}
									$industries = implode(", ", $arr_industries);
								}

								echo "<div>" . $industries . "</div>";
							?>
						</dd>
						<dd>
							<i class="fa-solid fa-fw fa-tags" alt="Sector"></i>
							<?php
								$arr_sector_terms = get_the_terms( get_the_ID(), 'sector' );
								$arr_sectors = [];
								$sectors = "";

								if ( !empty( $arr_sector_terms ) ) {
									foreach ($arr_sector_terms as $sector_term) {
										$arr_sectors[] = "<a class='hover:text-gold' href='/jobs/?sector=" . $sector_term->slug . "'>" . $sector_term->name . "</a>";
									}
									$sectors = implode(", ", $arr_sectors);
								}

								echo "<div>" . $sectors . "</div>";
							?>
						</dd>
					</div>
					<div class="col-span-1">
						<dt>Employment Type</dt>
						<dd>
							<i class="fa-solid fa-fw fa-briefcase" alt="Employment Type"></i>
							<?php echo get_field( 'job_type' ); ?>
						</dd>
					</div>
					<div class="col-span-2">
						<dt>Salary</dt>
						<dd>
							<i class="fa-solid fa-fw fa-money-bills" alt="Salary"></i>
							<?php echo "<div>" . get_field( 'salary' ) . "</div>"; ?>
						</dd>
					</div>
				</dl>
				<div>
					<?php if ( $arr_job_status[$job_status]['can-apply'] ) : ?>
						<a href="#Apply" class="button mr-2.5 mb-2.5">Apply Now</a>
					<?php endif; ?>
					<a href="/jobs/" class="button button--outline">View more jobs</a>
				</div>
			</div>
		</aside>

		<section class="job-single__description sticky__content md:py-triple xl:py-double 2xl:py-single relative w-full py-0 !pb-0 lg:w-1/2">
			<h2 class="mb-single 2xl:mb-half">Job Description</h2>
			<div class="prose pb-single">
				<?php the_content(); ?>
				<a id="Apply" name="Apply"></a>
			</div>
			<?php if ( $arr_job_status[$job_status]['can-apply'] ) { ?>
			<div class="not-prose job-single__application p-single text-gray-default dark:text-white-default bg-[#f5f5f5] dark:bg-[#222222]">
				<h2 class="job-single__details-title mb-single 2xl:mb-half">Apply Now</h2>
			</div>
			<?php } ?>
		</section>
	</div>
</article>

<?php include('cta-bar.php'); ?>

<?php get_footer(); ?>
