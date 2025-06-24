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
		<h6 class="mb-5 font-sans">We have recruited positions using the following language skills:</h6>
		<ul class="tag-list">
			<li><a href="/languages/german/" class="tag-list__tag">German</a></li>
			<li><a href="/languages/italian/" class="tag-list__tag">Italian</a></li>
			<li><a href="/languages/finnish/" class="tag-list__tag">Finnish</a></li>
			<li><a href="/languages/spanish/" class="tag-list__tag">Spanish</a></li>
			<li><a href="/languages/french/" class="tag-list__tag">French</a></li>
			<li><a href="/languages/dutch/" class="tag-list__tag">Dutch</a></li>
			<li><a href="/languages/portuguese/" class="tag-list__tag">Portuguese</a></li>
			<li><a href="/languages/russian/" class="tag-list__tag">Russian</a></li>
			<li><a href="/languages/hebrew/" class="tag-list__tag">Hebrew</a></li>
			<li><a href="/languages/swedish/" class="tag-list__tag">Swedish</a></li>
			<li><a href="/languages/turkish/" class="tag-list__tag">Turkish</a></li>
			<li><a href="/languages/norwegian/" class="tag-list__tag">Norwegian</a></li>
			<li><a href="/languages/polish/" class="tag-list__tag">Polish</a></li>
			<li><a href="/languages/arabic/" class="tag-list__tag">Arabic</a></li>
			<li><a href="/languages/mandarin/" class="tag-list__tag">Mandarin</a></li>
			<li><a href="/languages/icelandic/" class="tag-list__tag">Icelandic</a></li>
			<li><a href="/languages/danish/" class="tag-list__tag">Danish</a></li>
			<li><a href="/languages/slovakian/" class="tag-list__tag">Slovakian</a></li>
			<li><a href="/languages/flemish/" class="tag-list__tag">Flemish</a></li>
			<li><a href="/languages/ukrainian/" class="tag-list__tag">Ukrainian</a></li>
			<li><a href="/languages/greek/" class="tag-list__tag">Greek</a></li>
			<li><a href="/languages/croatian/" class="tag-list__tag">Croatian</a></li>
			<li><a href="/languages/slovenian/" class="tag-list__tag">Slovenian</a></li>
			<li><a href="/languages/serbian/" class="tag-list__tag">Serbian</a></li>
			<li><a href="/languages/bulgarian/" class="tag-list__tag">Bulgarian</a></li>
			<li><a href="/languages/estonian/" class="tag-list__tag">Estonian</a></li>
			<li><a href="/languages/indonesian/" class="tag-list__tag">Indonesian</a></li>
			<li><a href="/languages/hungarian/" class="tag-list__tag">Hungarian</a></li>
			<li><a href="/languages/romanian/" class="tag-list__tag">Romanian</a></li>
			<li><a href="/languages/japanese/" class="tag-list__tag">Japanese</a></li>
			<li><a href="/languages/korean/" class="tag-list__tag">Korean</a></li>
			<li><a href="/languages/czech/" class="tag-list__tag">Czech</a></li>
		</ul>
	</section>
</footer>

<?php include('marquee.php'); ?>
<?php include('countup.php'); ?>
<?php include('cta-bar.php'); ?>

<?php get_footer(); ?>
