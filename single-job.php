<?php get_header(); 
global $post;
?>

<article class="job-single">
  <header class="job-single__header hero hero--job">
    <div class="hero__container fade-up">
        <div class="hero__meta hero__meta--top"></div>
      <h1 class="job-single__title reverse text-display-5 w-full"><?php the_title(); ?></h1>
      <div class="hero__meta hero__meta--bottom">
        <time datetime="<?php echo get_the_date("Y-m-d"); ?>">
          <i class="fa-solid fa-calendar-days" alt="Published On"></i>
          Posted on <?php echo get_the_date("F j, Y"); ?>
        </time>
      </div>
    </div>
  </header>

  <div class="layout gap-double xl:gap-double xl:pb-double pb-double 2xl:pb-single pt-half flex w-full flex-col md:pt-0 lg:flex-row-reverse">

    <aside class="job-single__sidebar md:py-triple xl:py-double 2xl:py-single relative w-full py-0 !pb-0 lg:w-1/2">
      <div class="job-single__details p-single text-gray-default dark:text-white-default bg-[#e5e5e5] dark:bg-[#222222]">
        <h2 class="job-single__details-title mb-single 2xl:mb-half">
          Job Details
        </h2>
        <dl class="job-single__details-list gap-single xl:gap-half mb-single 2xl:mb-half grid grid-cols-2">
          <div class="col-span-2">
            <dt>Job Title</dt>
            <dd><?php the_title(); ?></dd>
          </div>
          <div>
            <dt>Languages</dt>
            <dd>
              <i class="fa-solid fa-fw fa-globe" alt="Languages"></i>
              Korean
            </dd>
          </div>
          <div>
            <dt>Location</dt>
            <dd>
              <i class="fa-solid fa-fw fa-location-dot" alt="Location"></i>
              London, United Kingdom
            </dd>
          </div>
          <div>
            <dt>Industry</dt>
            <dd>
              <i class="fa-solid fa-fw fa-building" alt="Industry"></i>
              Sales - Marketing
            </dd>
          </div>
          <div>
            <dt>Employment Type</dt>
            <dd>
              <i class="fa-solid fa-fw fa-briefcase" alt="Employment Type"></i>
              Full Time
            </dd>
          </div>
          <div>
            <dt>Salary</dt>
            <dd>
              <i class="fa-solid fa-fw fa-money-bills" alt="Salary"></i>
              £42,000+
            </dd>
          </div>
        </dl>
        <div>
          <a href="#Apply" class="button mr-2.5"><i class="fa-solid fa-pen-to-square"></i> Apply Now</a>
          <a href="/jobs/" class="button button--outline">View more jobs</a>
        </div>
      </div>
    </aside>

    <section class="job-single__description md:py-triple xl:py-double 2xl:py-single relative w-full py-0 !pb-0 lg:w-1/2">
      <h2 class="mb-single 2xl:mb-half">Job Description</h2>
      <div class="prose pb-single">
          <?php the_content(); ?>
          <a id="Apply" name="Apply"></a>
      </div>
      <div class="not-prose job-single__application p-single text-gray-default dark:text-white-default bg-[#e5e5e5] dark:bg-[#222222]">
        <h2 class="job-single__details-title mb-single 2xl:mb-half">Apply Now</h2>
      </div>
    </section>

  </div>
</article>

<?php include('cta-bar.php'); ?>

<?php get_footer();