<?php
/**
 * Template Name: Custom Registration
 * Path: wp-content/themes/origin/page-register.php
 */

// Buffer output to prevent headers issues
ob_start();

error_log( 'Custom Registration: Page loaded' );

if ( is_user_logged_in() ) {
	error_log( 'Custom Registration: User already logged in, redirecting to home' );
	wp_redirect( home_url( '/profile' ) );
	exit;
}

$errors = [];
$success = '';

if ( isset( $_POST['register_submit'] ) ) {
	error_log( 'Custom Registration: Form submission detected with POST: ' . print_r( $_POST, true ) );
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'custom_register' ) ) {
		$errors[] = 'Invalid or expired form submission.';
		error_log( 'Custom Registration: Invalid nonce' );
	} else {
		$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
		$last_name = sanitize_text_field( $_POST['last_name'] ?? '' );
		$user_email = sanitize_email( $_POST['user_email'] ?? '' );
		$user_password = $_POST['user_password'] ?? '';
		$username = sanitize_user( $user_email, true ); // Set username to email

		// Validation
		if ( empty( $first_name ) ) {
			$errors[] = 'First name is required.';
		}
		if ( empty( $last_name ) ) {
			$errors[] = 'Last name is required.';
		}
		if ( empty( $user_email ) ) {
			$errors[] = 'Email is required.';
		} elseif ( ! is_email( $user_email ) ) {
			$errors[] = 'Invalid email format.';
		} elseif ( email_exists( $user_email ) ) {
			$errors[] = 'Email is already registered.';
		}
		if ( empty( $user_password ) ) {
			$errors[] = 'Password is required.';
		} elseif ( strlen( $user_password ) < 8 ) {
			$errors[] = 'Password must be at least 8 characters.';
		}
		if ( username_exists( $username ) ) {
			$errors[] = 'Username is already taken.';
		}

		if ( empty( $errors ) ) {
			$user_data = [
				'user_login' => $username,
				'user_email' => $user_email,
				'user_pass' => $user_password,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'role' => 'subscriber',
			];
			$user_id = wp_insert_user( $user_data );

			if ( ! is_wp_error( $user_id ) ) {
				error_log( 'Custom Registration: User created with ID ' . $user_id );
				// Auto-login
				wp_clear_auth_cookie();
				$creds = [
					'user_login' => $username,
					'user_password' => $user_password,
					'remember' => true,
				];
				$user = wp_signon( $creds, is_ssl() );
				if ( ! is_wp_error( $user ) ) {
					wp_set_current_user( $user_id );
					wp_set_auth_cookie( $user_id, true, is_ssl() );
					error_log( 'Custom Registration: User ID ' . $user_id . ' logged in successfully' );
					$success = 'Registration successful! Redirecting to home...';
					wp_redirect( home_url( '/' ) );
					exit;
				} else {
					$errors[] = 'Auto-login failed: ' . $user->get_error_message();
					error_log( 'Custom Registration Error: Auto-login failed for User ID ' . $user_id . ': ' . $user->get_error_message() );
				}
			} else {
				$errors[] = $user_id->get_error_message();
				error_log( 'Custom Registration Error: ' . $user_id->get_error_message() );
			}
		}
	}
}

ob_end_flush();

get_header();

?>

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

<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 fade-up">
	<?php if ( ! empty( $errors ) ) : ?>
		<?php foreach ( $errors as $error ) : ?>
			<div class="alert-solid alert-solid--danger">
				<?php echo $error; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( $success ) : ?>
		<div class="alert-solid alert-solid--success">
			<?php echo $success; ?></p>
		</div>
	<?php endif; ?>
	<form method="post" action="" class="register-form space-y-4">
		<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
			<div class="form__field-wrapper first_name">
				<label class="form__label" for="first_name">First Name <span class="text-red-500">*</span></label>
				<input type="text" name="first_name" id="register_first_name" placeholder="e.g. John" class="form__field" required value="<?php echo isset( $_POST['first_name'] ) ? esc_attr( $_POST['first_name'] ) : ''; ?>" />
			</div>
			<div class="form__field-wrapper last_name">
				<label class="form__label" for="last_name">Last Name <span class="text-red-500">*</span></label>
				<input type="text" name="last_name" id="register_last_name" placeholder="e.g. Smith" class="form__field" required value="<?php echo isset( $_POST['last_name'] ) ? esc_attr( $_POST['last_name'] ) : ''; ?>" />
			</div>
			<div class="form__field-wrapper user_email">
				<label class="form__label" for="user_email">Email Address <span class="text-red-500">*</span></label>
				<input type="email" name="user_email" id="register_user_email" placeholder="e.g. john@smith.com" class="form__field" required value="<?php echo isset( $_POST['user_email'] ) ? esc_attr( $_POST['user_email'] ) : ''; ?>" />
			</div>
			<div class="form__field-wrapper user_password">
				<label class="form__label" for="user_password">Password <span class="text-red-500">*</span></label>
				<input type="password" name="user_password" id="register_user_password" placeholder="Enter New Password" class="form__field" required />
			</div>
		</fieldset>
		
		<?php wp_nonce_field( 'custom_register', '_wpnonce' ); ?>
		<input type="hidden" name="register_submit" value="1" />
		<div class="text-center">
			<input type="submit" value="Create Your Account" class="button w-full" id="register_submit" />
			<div class="text-sm mt-2">Already have an account? <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="no-underline">Log in</a></div>
		</div>
	</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('.register-form');
	const submitButton = document.getElementById('register_submit');
	const emailInput = document.getElementById('register_user_email');
	
	// Client-side validation and prevent double submission
	if (form && submitButton) {
		form.addEventListener('submit', function (event) {
			let errors = [];
			if (!emailInput.value) {
				errors.push('Email is required');
			} else if (!/\S+@\S+\.\S+/.test(emailInput.value)) {
				errors.push('Email is invalid');
			}
			if (!document.getElementById('register_user_password').value) {
				errors.push('Password is required');
			} else if (document.getElementById('register_user_password').value.length < 8) {
				errors.push('Password must be at least 8 characters');
			}
			if (errors.length > 0) {
				event.preventDefault();
				console.error('Client-side validation errors: ', errors);
				alert('Please fix the following errors:\n' + errors.join('\n'));
			} else {
				console.log('Form submission attempted with email: ' + emailInput.value);
				console.log('Form data: ', new FormData(form));
				submitButton.disabled = true; // Prevent double submission
				submitButton.value = 'Submitting...';
			}
		});
	}

	// Fallback redirect
	<?php if ( $success ) : ?>
		setTimeout(function() {
			window.location.href = '<?php echo esc_url( home_url( '/' ) ); ?>';
		}, 1000);
	<?php endif; ?>
});
</script>

<?php get_footer(); ?>
