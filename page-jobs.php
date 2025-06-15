<?php get_header(); ?>

<div class="hero hero--small">
    <div class="hero__container">
        <h1 class="reverse text-display-5 fade-up w-full md:w-3/4 lg:w-1/2"><?php the_title(); ?></h1>
    </div>
</div>
<section class="layout gap-double lg:gap-single flex w-full flex-col lg:flex-row-reverse">

    <aside class="w-full lg:w-1/3">
        <div class="job-single__details p-single text-gray-default dark:text-white-default bg-[#eee] dark:bg-[#222222]">
            <div class="pb-half">
                <label for="search" class="hidden">Search all jobs</label>
                <input type="text" class="py-2.5 text-sm sm:py-3 px-4 block w-full border-gray-200 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 placeholder-gray-dim dark:placeholder-white-dim dark:focus:ring-neutral-600" placeholder="Search all jobs">
            </div>
            <div class="pb-half">
                <label class="text-sm" for="">Languages</label>
                <select multiple="" data-hs-select='{
                "placeholder": "Filter by Language",
                "dropdownClasses": "mt-2 z-50 w-full max-h-72 p-1 space-y-0.5 bg-white border border-gray-200 overflow-hidden overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 dark:bg-neutral-900 dark:border-neutral-700",
                "optionClasses": "py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 dark:bg-neutral-900 dark:hover:bg-neutral-800 dark:text-neutral-200 dark:focus:bg-neutral-800",
                "mode": "tags",
                "wrapperClasses": "relative bg-white dark:bg-black ps-0.5 pe-9 min-h-11.5 flex items-center flex-wrap text-nowrap w-full border border-gray-200 text-start text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400",
                "tagsItemTemplate": "<div class=\"flex flex-nowrap items-center relative z-10 bg-white border border-gray-200 rounded-full p-1 pl-2 m-1 dark:bg-neutral-900 dark:border-neutral-700 \"><div class=\"size-6 me-1\" data-icon></div><div class=\"whitespace-nowrap text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"inline-flex shrink-0 justify-center items-center size-5 ms-2 rounded-full text-gray-800 bg-gray-200 hover:bg-gray-300 focus:outline-hidden focus:ring-2 focus:ring-gray-400 text-sm dark:bg-neutral-700/50 dark:hover:bg-neutral-700 dark:text-neutral-400 cursor-pointer\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
                "tagsInputId": "filter-languages",
                "tagsInputClasses": "py-2.5 sm:py-3 px-2 min-w-20 order-1 border-transparent focus:ring-0 sm:text-sm outline-hidden dark:bg-neutral-900 placeholder-gray-dim dark:placeholder-white-dim dark:text-neutral-400",
                "optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500 \" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
                "extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
                }' class="hidden">
                    <option value="">Choose</option>
                    <option value="german">German</option>
                    <option value="japanese">Japanese</option>
                    <option selected value="korean">Korean</option>
                </select>
            </div>
            <div>
                <label class="text-sm" for="">Locations</label>
                <select multiple="" data-hs-select='{
                "placeholder": "Filter by Location",
                "dropdownClasses": "mt-2 z-50 w-full max-h-72 p-1 space-y-0.5 bg-white border border-gray-200 overflow-hidden overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 dark:bg-neutral-900 dark:border-neutral-700",
                "optionClasses": "py-2 px-4 w-full text-sm text-gray-800 cursor-pointer hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 hs-select-disabled:pointer-events-none hs-select-disabled:opacity-50 dark:bg-neutral-900 dark:hover:bg-neutral-800 dark:text-neutral-200 dark:focus:bg-neutral-800",
                "mode": "tags",
                "wrapperClasses": "relative bg-white dark:bg-black ps-0.5 pe-9 min-h-11.5 flex items-center flex-wrap text-nowrap w-full border border-gray-200 text-start text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400",
                "tagsItemTemplate": "<div class=\"flex flex-nowrap items-center relative z-10 bg-white border border-gray-200 rounded-full p-1 pl-2 m-1 dark:bg-neutral-900 dark:border-neutral-700 \"><div class=\"size-6 me-1\" data-icon></div><div class=\"whitespace-nowrap text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"inline-flex shrink-0 justify-center items-center size-5 ms-2 rounded-full text-gray-800 bg-gray-200 hover:bg-gray-300 focus:outline-hidden focus:ring-2 focus:ring-gray-400 text-sm dark:bg-neutral-700/50 dark:hover:bg-neutral-700 dark:text-neutral-400 cursor-pointer\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
                "tagsInputId": "filter-locations",
                "tagsInputClasses": "py-2.5 sm:py-3 px-2 min-w-20 order-1 border-transparent focus:ring-0 sm:text-sm outline-hidden dark:bg-neutral-900 placeholder-gray-dim dark:placeholder-white-dim dark:text-neutral-400",
                "optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200 \" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500 \" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
                "extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500 \" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
                }' class="hidden">
                    <option value="">Choose</option>
                    <option value="france">France</option>
                    <option value="germany">Germany</option>
                    <option selected value="united-kingdom">United Kingdom</option>
                </select>
            </div>
        </div>
    </aside>

    <section class="w-full lg:w-2/3">

<?php
// construct the query arguments
$args = array(
    'post_type' => 'job'
);

$loop = new WP_Query( $args );

while ( $loop->have_posts() ) {

    $loop->the_post();

?>

<article class="job-listing p-single text-gray-default dark:text-white-default bg-[#eee] dark:bg-[#222222]">
    <h3 class="job-listing__title mb-single 2xl:mb-half"><?php the_title(); ?></h3>
    <ul class="job-listing__details-list gap-single xl:gap-half mb-single 2xl:mb-half grid grid-cols-2">
        <li>
            <i class="fa-solid fa-fw fa-globe" alt="Languages"></i>
            Korean
        </li>
        <li>
            <i class="fa-solid fa-fw fa-location-dot" alt="Location"></i>
            London, United Kingdom
        </li>
        <li>
            <i class="fa-solid fa-fw fa-building" alt="Industry"></i>
            Sales - Marketing
        </li>
        <li>
            <i class="fa-solid fa-fw fa-briefcase" alt="Employment Type"></i>
            Full Time
        </li>
        <li>
            <i class="fa-solid fa-fw fa-money-bills" alt="Salary"></i>
            £42,000+
        </li>
    </ul>
    <div>
        <a href="<?php echo get_the_permalink(); ?>#Apply" class="button mr-2.5"><i class="fa-solid fa-pen-to-square"></i> Apply Now</a>
        <a href="<?php echo get_the_permalink(); ?>" class="button button--outline">View full listing</a>
    </div>
</article>

<?php

}
?>      
    </section>
</section>

<?php include('contact.php'); ?>

<?php get_footer();