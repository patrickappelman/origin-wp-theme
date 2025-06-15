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

<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 pb-single fade-up">
  <?php the_content(); ?>
</div>
<footer class="post-single__footer px-half lg:px-0">
  <section class="post-single__tags pb-double lg:pb-single 2xl:pb-half fade-up">
    <h6 class="mb-5 font-sans">We recruit multilingual professionals and experts for all industries and sectors, including:</h6>
    <ul class="tag-list">
      <li><a href="/industries/administration/" class="tag-list__tag">Administration</a></li>
      <li><a href="/industries/banking/" class="tag-list__tag">Banking</a></li>
      <li><a href="/industries/financial-services/" class="tag-list__tag">Financial Services</a></li>
      <li><a href="/industries/accounting/" class="tag-list__tag">Accounting</a></li>
      <li><a href="/industries/logistics/" class="tag-list__tag">Logistics</a></li>
      <li><a href="/industries/customer-service/" class="tag-list__tag">Customer Service</a></li>
      <li><a href="/industries/sales-marketing/" class="tag-list__tag">Sales – Marketing</a></li>
      <li><a href="/industries/human-resources/" class="tag-list__tag">Human Resources</a></li>
      <li><a href="/industries/recruitment-employment-firm/" class="tag-list__tag">Recruitment/Employment Firm</a></li>
      <li><a href="/industries/it-services/" class="tag-list__tag">IT Services</a></li>
      <li><a href="/industries/technology/" class="tag-list__tag">Technology</a></li>
      <li><a href="/industries/airline-aviation/" class="tag-list__tag">Airline – Aviation</a></li>
      <li><a href="/industries/oil-and-gas/" class="tag-list__tag">Oil & Gas</a></li>
      <li><a href="/industries/research-and-development/" class="tag-list__tag">Research & Development</a></li>
      <li><a href="/industries/consulting/" class="tag-list__tag">Consulting</a></li>
      <li><a href="/industries/marketing/" class="tag-list__tag">Marketing</a></li>
      <li><a href="/industries/public-relations/" class="tag-list__tag">Public Relations</a></li>
      <li><a href="/industries/communications/" class="tag-list__tag">Communications</a></li>
    </ul>
  </section>
</footer>

<?php include('marquee.php'); ?>
<?php include('countup.php'); ?>
<?php include('cta-bar.php'); ?>

<?php get_footer();