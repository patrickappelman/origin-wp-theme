<?php get_header(); ?>

<section class="hero hero--large bg-[#222222] dark:bg-[#444444]">
	<div class="hero__background">
		<img src="https://images.unsplash.com/photo-1652707227172-aae5272f28c5?q=80&w=3652&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="" />
	</div>
	<div class="hero__container">
		<h1 class="reverse text-display-5 fade-up w-full md:w-3/4 lg:w-1/2">Connecting Global Talent, Empowering Business Success</h1>
	</div>
</section>

<section class="layout layout--2-col layout--reverse">
	<div class="my-auto w-full lg:w-1/2">
		<div class="gap-single xl:gap-half fade-up flex flex-col align-middle">
			<h2>More than 50 years of Multilingual Recruitment</h2>
			<p class="leading-loose"><?php echo get_the_excerpt(10); ?></p>
			<div>
				<a href="/contact-us/" class="button">Get In Touch</a>
			</div>
		</div>
	</div>
	<div class="fade-up w-full lg:w-1/2">
		<div class="inline-block aspect-3/2 w-full bg-[#E5E5E5] align-top dark:bg-[#444444] overflow-hidden [&>img]:object-cover [&>img]:w-full [&>img]:h-full  [&>img]:transform [&>img]:transition-transform [&>img]:duration-500 [&>img]:hover:scale-105">
			<?php echo get_the_post_thumbnail(10); ?>
		</div>
	</div>
</section>

<?php include('countup.php'); ?>

<section>
	<nav class="bg-gold flex flex-row justify-center">
		<ul class="max-w-container py-double lg:py-half gap-single md:gap-half mx-auto grid grid-cols-1 text-center md:grid-cols-2 md:flex-row lg:flex">
			<li class="block">
				<a class="md:py-half px-button-x inline-block font-bold text-white hover:underline lg:py-0" href="#Language-Expertise">Language Expertise</a>
			</li>
			<li class="block">
				<a class="md:py-half px-button-x inline-block font-bold text-white hover:underline lg:py-0" href="#Global-Reach">Global Reach</a>
			</li>
			<li class="block">
				<a class="md:py-half px-button-x inline-block font-bold text-white hover:underline lg:py-0" href="#Industry-Knowledge">Industry Knowledge</a>
			</li>
			<li class="block">
				<a class="md:py-half px-button-x inline-block font-bold text-white hover:underline lg:py-0" href="#Executive-Search">Executive Search</a>
			</li>
		</ul>
	</nav>

	<a name="Language-Expertise" id="Language-Expertise"></a>
	<article class="layout layout--2-col layout--reverse pb-0">
		<div class="fade-up w-full lg:w-1/2">
			<a href="<?php echo get_the_permalink(12); ?>" class="inline-block aspect-3/2 w-full bg-[#E5E5E5] align-top dark:bg-[#444444] overflow-hidden [&>img]:object-cover [&>img]:w-full [&>img]:h-full  [&>img]:transform [&>img]:transition-transform [&>img]:duration-500 [&>img]:hover:scale-105">
				<?php echo get_the_post_thumbnail(12); ?>
			</a>
		</div>
		<div class="my-auto w-full lg:w-1/2">
			<div class="gap-single xl:gap-half fade-up flex flex-col align-middle">
				<h2>Global Language Talent: Fluent in Your Success</h2>
				<p class="leading-loose"><?php echo get_the_excerpt(12); ?></p>
				<div>
					<a href="<?php echo get_the_permalink(12); ?>" class="button">View Language Solutions</a>
				</div>
			</div>
		</div>
	</article>

	<a name="Global-Reach" id="Global-Reach"></a>
	<article class="layout layout--2-col pb-0">
		<div class="fade-up w-full lg:w-1/2">
			<a href="<?php echo get_the_permalink(16); ?>" class="inline-block aspect-3/2 w-full bg-[#E5E5E5] align-top dark:bg-[#444444] overflow-hidden [&>img]:object-cover [&>img]:w-full [&>img]:h-full  [&>img]:transform [&>img]:transition-transform [&>img]:duration-500 [&>img]:hover:scale-105">
				<?php echo get_the_post_thumbnail(16); ?>
			</a>
		</div>
		<div class="my-auto w-full lg:w-1/2">
			<div class="gap-single xl:gap-half fade-up flex flex-col align-middle">
				<h2>Connecting Talent Across 25+ Countries</h2>
				<p class="leading-loose"><?php echo get_the_excerpt(16); ?></p>
				<div>
					<a href="<?php echo get_the_permalink(16); ?>" class="button">See Our Global Impact</a>
				</div>
			</div>
		</div>
	</article>

	<a name="Industry-Knowledge" id="Industry-Knowledge"></a>
	<article class="layout layout--2-col layout--reverse pb-0">
		<div class="fade-up w-full lg:w-1/2">
			<a href="<?php echo get_the_permalink(14); ?>" class="inline-block aspect-3/2 w-full bg-[#E5E5E5] align-top dark:bg-[#444444] overflow-hidden [&>img]:object-cover [&>img]:w-full [&>img]:h-full  [&>img]:transform [&>img]:transition-transform [&>img]:duration-500 [&>img]:hover:scale-105">
				<?php echo get_the_post_thumbnail(14); ?>
			</a>
		</div>
		<div class="my-auto w-full lg:w-1/2">
			<div class="gap-single xl:gap-half fade-up flex flex-col align-middle">
				<h2>Specialized Recruitment Across Diverse Industries</h2>
				<p class="leading-loose"><?php echo get_the_excerpt(14); ?></p>
				<div>
					<a href="<?php echo get_the_permalink(14); ?>" class="button">View Industry Expertise</a>
				</div>
			</div>
		</div>
	</article>

	<a name="Executive-Search" id="Executive-Search"></a>
	<article class="layout layout--2-col pb-0">
		<div class="fade-up w-full lg:w-1/2">
			<a href="<?php echo get_the_permalink(18); ?>" class="inline-block aspect-3/2 w-full bg-[#E5E5E5] align-top dark:bg-[#444444] overflow-hidden [&>img]:object-cover [&>img]:w-full [&>img]:h-full  [&>img]:transform [&>img]:transition-transform [&>img]:duration-500 [&>img]:hover:scale-105">
				<?php echo get_the_post_thumbnail(18); ?>
			</a>
		</div>
		<div class="my-auto w-full lg:w-1/2">
			<div class="gap-single xl:gap-half fade-up flex flex-col align-middle">
				<h2>Executive Recruitment for Senior Roles</h2>
				<p class="leading-loose"><?php echo get_the_excerpt(18); ?></p>
				<div>
					<a href="<?php echo get_the_permalink(18); ?>" class="button">Explore Leadership Solutions</a>
				</div>
			</div>
		</div>
	</article>
</section>

<?php include('marquee.php'); ?>
<?php include('cta-bar.php'); ?>

<?php get_footer(); ?>
