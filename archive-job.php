<?php get_header(); ?>

<?php 
	if (function_exists('rank_math_the_breadcrumbs')) { 
		echo "<div class='breadcrumbs'>";
		rank_math_the_breadcrumbs();
		echo "</div>";
	}
?>

<?php
// Function to get terms associated with 'job' post type
function get_job_related_terms($taxonomy) {
	$args = array(
		'post_type' => 'job',
		'posts_per_page' => -1,
		'fields' => 'ids',
		'tax_query' => array(
			array(
				'taxonomy' => $taxonomy,
				'operator' => 'EXISTS',
			),
		),
	);
	$query = new WP_Query($args);
	$term_ids = array();

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$terms = wp_get_post_terms(get_the_ID(), $taxonomy, array('fields' => 'ids'));
			$term_ids = array_merge($term_ids, $terms);
		}
	}
	wp_reset_postdata();

	$term_ids = array_unique($term_ids);
	if (!empty($term_ids)) {
		return get_terms(array(
			'taxonomy' => $taxonomy,
			'include' => $term_ids,
			'hide_empty' => false,
		));
	}
	return array();
}

// Get available terms for taxonomies
$languages = get_job_related_terms('language');
$countries = get_job_related_terms('country');
$industries = get_job_related_terms('industry');
$sectors = get_job_related_terms('sector');

// Get query parameters
$language_query = isset($_GET['language']) ? explode(',', sanitize_text_field($_GET['language'])) : [];
$country_query = isset($_GET['country']) ? explode(',', sanitize_text_field($_GET['country'])) : [];
$industry_query = isset($_GET['industry']) ? explode(',', sanitize_text_field($_GET['industry'])) : [];
$sector_query = isset($_GET['sector']) ? explode(',', sanitize_text_field($_GET['sector'])) : [];
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$status_query = isset($_GET['job_opening_status']) ? sanitize_text_field($_GET['job_opening_status']) : 'in-progress';
$paged = (get_query_var('page')) ? get_query_var('page') : 1;

if ($status_query == 'all') {
	$status_query = explode(',', 'in-progress,filled,cancelled,declined,inactive,submitted-by-client');
}

$args = array(
	'post_type' => 'job',
	'posts_per_page' => 10,
	'paged' => $paged,
	'meta_query' => array(
		'relation' => 'AND',
		array(
			'key' => 'job_opening_status',
			'value' => $status_query,
			'compare' => 'IN',
		),
	),
	'tax_query' => array(
		'relation' => 'AND',
	),
	's' => $search_query,
);

if (!empty($language_query)) {
	$args['tax_query'][] = array(
		'taxonomy' => 'language',
		'field' => 'slug',
		'terms' => $language_query,
	);
}

if (!empty($country_query)) {
	$args['tax_query'][] = array(
		'taxonomy' => 'country',
		'field' => 'slug',
		'terms' => $country_query,
	);
}

if (!empty($industry_query)) {
	$args['tax_query'][] = array(
		'taxonomy' => 'industry',
		'field' => 'slug',
		'terms' => $industry_query,
	);
}

if (!empty($sector_query)) {
	$args['tax_query'][] = array(
		'taxonomy' => 'sector',
		'field' => 'slug',
		'terms' => $sector_query,
	);
}

$query = new WP_Query($args);

$pagination_args = array(
	'language' => implode(',', $language_query),
	'country' => implode(',', $country_query),
	'industry' => implode(',', $industry_query),
	'sector' => implode(',', $sector_query),
	'search' => $search_query,
	'job_opening_status' => is_array($status_query) ? implode(',', $status_query) : $status_query,
);

$pagination_args = array_filter($pagination_args, function($value) { return $value !== '' && $value !== false && $value !== null; });
?>

<?php
$arr_job_status = array(
	'In-progress' => array(
		'alert-type' => 'success',
		'can-apply' => true,
		'msg' => 'This job is now accepting applications.'
	),
	'Filled' => array(
		'alert-type' => 'danger',
		'can-apply' => false,
		'msg' => 'This job has been filled.'
	),
	'Cancelled' => array(
		'alert-type' => 'danger',
		'can-apply' => false,
		'msg' => 'This job is no longer available.'
	),
	'Declined' => array(
		'alert-type' => 'danger',
		'can-apply' => false,
		'msg' => 'This job is no longer available.'
	),
	'Inactive' => array(
		'alert-type' => 'danger',
		'can-apply' => false,
		'msg' => 'This job is no longer available.'
	),
	'Submitted by client' => array(
		'alert-type' => 'warning',
		'can-apply' => false,
		'msg' => 'This job is no longer available.'
	),
);
?>

<div class="hero hero--small">
	<div class="hero__container">
		<h1 class="reverse text-display-5 fade-up w-full md:w-3/4 lg:w-1/2">Explore Jobs</h1>
	</div>
</div>

<section class="layout gap-double xl:gap-double xl:pb-double pb-double 2xl:pb-single pt-half flex w-full flex-col md:pt-0 lg:flex-row-reverse">
	<aside class="w-full lg:w-1/3 sticky__sidebar md:py-triple xl:py-double 2xl:py-single relative py-0 !pb-0">
		<div class="job-single__details sticky__sidebar-details p-single text-gray-default dark:text-white-default bg-[#f5f5f5] dark:bg-[#222222]">
			<form id="jobs-filter-form" class="jobs-filter-form">
				<div class="pb-5">
					<label for="search-filter" class="sr-only">Search all jobs</label>
					<input id="search-filter" name="search" type="text" class="py-2.5 text-sm sm:py-3 px-4 block w-full border-gray-200 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 placeholder-gray-dim dark:placeholder-white-dim dark:focus:ring-neutral-600" placeholder="Search all jobs" value="<?php echo esc_attr($search_query); ?>">
				</div>
				<div class="pb-5">
					<label class="text-sm sr-only" for="status-filter">Filter by Job Status</label>
					<select id="status-filter" name="job_opening_status[]" multiple="" data-hs-select='{
						"placeholder": "Filter by Status",
						"dropdownClasses": "advanced-select__dropdown",
						"optionClasses": "advanced-select__option",
						"mode": "tags",
						"wrapperClasses": "advanced-select__wrapper",
						"tagsItemTemplate": "<div class=\"advanced-select__tag-item\"><div class=\"advanced-select__tag-item-icon\" data-icon></div><div class=\"advanced-select__tag-item-title\" data-title></div><div class=\"advanced-select__tag-item-remove\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
						"tagsInputId": "status-filter",
						"tagsInputClasses": "advanced-select__tags-input",
						"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500 \" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
						"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
						}' class="hidden">
						<option value="in-progress" selected>Now Hiring</option>
					</select>
				</div>
				<div class="pb-5">
					<label class="text-sm sr-only" for="language-filter">Filter by Language</label>
					<select id="language-filter" name="language[]" multiple="" data-hs-select='{
						"placeholder": "Filter by Language",
						"dropdownClasses": "advanced-select__dropdown",
						"optionClasses": "advanced-select__option",
						"mode": "tags",
						"hasSearch": true,
						"searchClasses": "advanced-select__search",
						"searchWrapperClasses": "advanced-select__search-wrapper",
						"wrapperClasses": "advanced-select__wrapper",
						"tagsItemTemplate": "<div class=\"advanced-select__tag-item\"><div class=\"advanced-select__tag-item-icon\" data-icon></div><div class=\"advanced-select__tag-item-title\" data-title></div><div class=\"advanced-select__tag-item-remove\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
						"tagsInputId": "language-filter",
						"tagsInputClasses": "advanced-select__tags-input",
						"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500 \" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
						"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
						}' class="hidden">
						<?php foreach ($languages as $language): ?>
							<option value="<?php echo esc_attr($language->slug); ?>" 
							<?php echo in_array($language->slug, $language_query) ? 'selected' : ''; ?>>
							<?php echo esc_html($language->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="pb-5">
					<label class="text-sm sr-only" for="location-filter">Filter by Location</label>
					<select id="location-filter" name="country[]" multiple="" data-hs-select='{
						"placeholder": "Filter by Location",
						"dropdownClasses": "advanced-select__dropdown",
						"optionClasses": "advanced-select__option",
						"mode": "tags",
						"hasSearch": true,
						"searchClasses": "advanced-select__search",
						"searchWrapperClasses": "advanced-select__search-wrapper",
						"wrapperClasses": "advanced-select__wrapper",
						"tagsItemTemplate": "<div class=\"advanced-select__tag-item\"><div class=\"advanced-select__tag-item-icon\" data-icon></div><div class=\"advanced-select__tag-item-title\" data-title></div><div class=\"advanced-select__tag-item-remove\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
						"tagsInputId": "location-filter",
						"tagsInputClasses": "advanced-select__tags-input",
						"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500 \" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
						"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
						}' class="hidden">
						<?php foreach ($countries as $country): ?>
							<option value="<?php echo esc_attr($country->slug); ?>" 
							<?php echo in_array($country->slug, $country_query) ? 'selected' : ''; ?>>
							<?php echo esc_html($country->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="pb-5">
					<label class="text-sm sr-only" for="industry-filter">Filter by Industry</label>
					<select id="industry-filter" name="industry[]" multiple="" data-hs-select='{
						"placeholder": "Filter by Industry",
						"dropdownClasses": "advanced-select__dropdown",
						"optionClasses": "advanced-select__option",
						"mode": "tags",
						"hasSearch": true,
						"searchClasses": "advanced-select__search",
						"searchWrapperClasses": "advanced-select__search-wrapper",
						"wrapperClasses": "advanced-select__wrapper",
						"tagsItemTemplate": "<div class=\"advanced-select__tag-item\"><div class=\"advanced-select__tag-item-icon\" data-icon></div><div class=\"advanced-select__tag-item-title\" data-title></div><div class=\"advanced-select__tag-item-remove\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
						"tagsInputId": "industry-filter",
						"tagsInputClasses": "advanced-select__tags-input",
						"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500 \" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
						"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
						}' class="hidden">
						<?php foreach ($industries as $industry): ?>
							<option value="<?php echo esc_attr($industry->slug); ?>" 
							<?php echo in_array($industry->slug, $industry_query) ? 'selected' : ''; ?>>
							<?php echo esc_html($industry->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="pb-5">
					<label class="text-sm sr-only" for="sector-filter">Filter by Sector</label>
					<select id="sector-filter" name="sector[]" multiple="" data-hs-select='{
						"placeholder": "Filter by Sector",
						"dropdownClasses": "advanced-select__dropdown",
						"optionClasses": "advanced-select__option",
						"mode": "tags",
						"hasSearch": true,
						"searchClasses": "advanced-select__search",
						"searchWrapperClasses": "advanced-select__search-wrapper",
						"wrapperClasses": "advanced-select__wrapper",
						"tagsItemTemplate": "<div class=\"advanced-select__tag-item\"><div class=\"advanced-select__tag-item-icon\" data-icon></div><div class=\"advanced-select__tag-item-title\" data-title></div><div class=\"advanced-select__tag-item-remove\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
						"tagsInputId": "sector-filter",
						"tagsInputClasses": "advanced-select__tags-input",
						"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500 \" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
						"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
						}' class="hidden">
						<?php foreach ($sectors as $sector): ?>
							<option value="<?php echo esc_attr($sector->slug); ?>" 
							<?php echo in_array($sector->slug, $sector_query) ? 'selected' : ''; ?>>
							<?php echo esc_html($sector->name); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</form>
		</div>
	</aside>

	<section class="w-full lg:w-2/3 sticky__content md:py-triple xl:py-double 2xl:py-single relative py-0 !pb-0">
		<div id="jobs-results">
			<?php if ($query->have_posts()): ?>
			<?php while ($query->have_posts()): $query->the_post(); ?>
			<?php $job_status = get_field("job_opening_status"); ?>
			<article class="job-listing p-single text-gray-default dark:text-white-default bg-[#f5f5f5] dark:bg-[#222222]">
				<?php if (!$arr_job_status[$job_status]['can-apply']): ?>
					<div class="alert-soft <?php echo "alert-soft--" . $arr_job_status[$job_status]['alert-type'] ?> mb-half" role="alert" tabindex="-1" aria-labelledby="job-status-label">
						<span id="job-status-label" class="font-bold">Job Status:</span> <?php echo $arr_job_status[$job_status]['msg'] ?>
					</div>
				<?php endif; ?>
				<div class="job-listing__meta-row"><time datetime="<?php echo get_the_date("Y-m-d"); ?>">Posted on <?php echo get_the_date("F j, Y"); ?></time></div>
				<h3 class="job-listing__title mb-single 2xl:mb-half"><?php the_title(); ?></h3>
				<ul class="job-listing__details-list gap-6 mb-single 2xl:mb-half grid grid-cols-2">
					<li>
						<i class="fa-solid fa-fw fa-globe" alt="Languages"></i>
						<?php
							$arr_language_terms = get_the_terms(get_the_ID(), 'language');
							$arr_languages = [];
							$languages = "";
							if (!empty($arr_language_terms)) {
								foreach ($arr_language_terms as $language_term) {
									$arr_languages[] = $language_term->name;
								}
								$languages = implode(", ", $arr_languages);
							}
							echo "<div>" . $languages . "</div>";
						?>
					</li>
					<li>
						<i class="fa-solid fa-fw fa-location-dot" alt="Location"></i>
						<?php
							$arr_country_terms = get_the_terms(get_the_ID(), 'country');
							$arr_location = [];
							$location = "";
							if (!empty(get_field("city"))) {
								$arr_location["city"] = get_field("city");
							}
							if (!empty($arr_country_terms)) {
								$arr_location["country"] = $arr_country_terms[0]->name;
							}
							if (!empty($arr_location)) {
								$location = implode(", ", $arr_location);
							}
							if (get_field("remote_job")) {
								$location = "Remote";
							}
							echo "<div>" . $location . "</div>";
						?>
					</li>
					<li>
						<i class="fa-solid fa-fw fa-building" alt="Industry"></i>
						<?php
							$arr_industry_terms = get_the_terms(get_the_ID(), 'industry');
							$arr_industries = [];
							$industries = "";
							if (!empty($arr_industry_terms)) {
								foreach ($arr_industry_terms as $industry_term) {
									$arr_industries[] = $industry_term->name;
								}
								$industries = implode(", ", $arr_industries);
							}
							echo "<div>" . $industries . "</div>";
						?>
					</li>
					<li>
						<i class="fa-solid fa-fw fa-tags" alt="Sector"></i>
						<?php
							$arr_sector_terms = get_the_terms(get_the_ID(), 'sector');
							$arr_sectors = [];
							$sectors = "";
							if (!empty($arr_sector_terms)) {
								foreach ($arr_sector_terms as $sector_term) {
									$arr_sectors[] = $sector_term->name;
								}
								$sectors = implode(", ", $arr_sectors);
							}
							echo "<div>" . $sectors . "</div>";
						?>
					</li>
					<li>
						<i class="fa-solid fa-fw fa-briefcase" alt="Employment Type"></i>
						<?php echo get_field("job_type"); ?>
					</li>
					<li class="col-span-2">
						<i class="fa-solid fa-fw fa-money-bills" alt="Salary"></i>
						<?php echo get_field("salary"); ?>
					</li>
				</ul>
				<div>
					<?php if ($arr_job_status[$job_status]['can-apply']): ?>
					<a href="<?php echo get_the_permalink(); ?>#Apply" class="button mr-2.5"><i class="fa-solid fa-pen-to-square"></i> Apply Now</a>
					<?php endif; ?>
					<a href="<?php echo get_the_permalink(); ?>" class="button button--outline">View full listing</a>
				</div>
			</article>
			<?php endwhile; ?>
			<?php
				$pagination = paginate_links(array(
					'total' => $query->max_num_pages,
					'current' => $paged,
					'format' => '?page=%#%',
					'show_all' => false,
					'prev_next' => true,
					'add_args' => $pagination_args,
					'next_text' => '>',
					'prev_text' => '<',
				));
				if ($pagination) {
					echo '<nav class="pagination" aria-label="Pagination">' . $pagination . '</nav>';
				}
			?>
			<?php else: ?>
			<p>No jobs found matching your criteria.</p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</section>
</section>

<?php include('contact.php'); ?>

<?php get_footer(); ?>
