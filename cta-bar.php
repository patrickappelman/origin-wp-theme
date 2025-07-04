<section class="flex flex-col lg:flex-row">
    <div class="bg-gold-dark px-half md:px-double md:py-double lg:px-double lg:py-double 2xl:py-single py-double grid w-full justify-items-end lg:w-1/2">
        <div class="gap-single xl:gap-half max-w-half-container inline-flex flex-col pl-0">
            <h2 class="leading-tight text-[var(--color-white-bold)]">Looking for jobs?</h2>
            <p class="leading-relaxed text-[var(--color-white-default)]">We are committed to providing the service we offer to the highest standards. Candidates who are looking for a multilingual job with languages trust us to listen to them, understand their expectations and help them to find their ideal job that can utilise their language skills.</p>
            <div>
                <?php if ( ! is_user_logged_in() ) : ?>
                <a href="<?php echo home_url( "/register/" ); ?>" class="button mr-1 mb-2">Get Started</a>
                <a href="<?php echo home_url( "/jobs/" ); ?>" class="button button--outline button--light mb-2">Explore Jobs</a>
                <?php else : ?>
                <a href="<?php echo home_url( "/jobs/" ); ?>" class="button mr-1 mb-2">Explore Jobs</a>
                <a href="<?php echo home_url( "/profile/" ); ?>" class="button button--outline button--light mb-2">Update Profile</a>
                <?php endif; ?>
            </div>
            <p><a href="/candidates/" class="text-white-bold hover:underline">How our recruitment process works →</a></p>
        </div>
    </div>
    <div class="bg-gold-darkest px-half md:px-double md:py-double lg:px-double lg:py-double 2xl:py-single py-double grid justify-items-start lg:w-1/2">
        <div class="gap-single xl:gap-half max-w-half-container inline-flex flex-col pl-0">
            <h2 class="leading-tight text-[var(--color-white-bold)]">Looking for Talent?</h2>
            <p class="leading-relaxed text-[var(--color-white-default)]">For more than 50 years, Origin Recruitment has been a trusted partner to companies requiring multilingual recruitment. Whether you are looking to fill a single vacancy or recruit an entire multilingual or bilingual team, our professional recruitment service is tailored to your requirements.</p>
            <div>
                <a href="<?php echo home_url( "/clients/" ) ?>#Vacancy" class="button mr-1 mb-2">Post a Vacancy</a>
                <a href="<?php echo home_url( "/clients/" ) ?>" class="button button--outline button--light  mb-2">Get Tailored Solutions</a>
            </div>
            <p><a href="<?php echo home_url( "/clients/" ) ?>" class="text-white-bold hover:underline">Our candidate selection guarantee →</a></p>
        </div>
    </div>
</section>

<?php include('contact.php'); ?>
