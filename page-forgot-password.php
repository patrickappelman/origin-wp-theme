<?php
/**
 * Template Name: Forgot Password
 * Path: wp-content/themes/origin/page-forgot-password.php
 */
ob_start();
error_log( 'Forgot Password: Page loaded' );

// Handle redirect_to parameter
$redirect_url = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/login/' );
if ( parse_url( $redirect_url, PHP_URL_HOST ) !== parse_url( home_url(), PHP_URL_HOST ) ) {
	$redirect_url = home_url( '/login/' );
	$url_suffix = '';
} else {
	$url_suffix = isset( $_GET['redirect_to'] ) ? '?redirect_to=' . urlencode( $redirect_url ) : '';
}
error_log( 'Forgot Password: redirect_to=' . ( $redirect_url ?: 'not set' ) . ', url_suffix=' . ( $url_suffix ?: 'empty' ) );

if ( is_user_logged_in() ) {
	error_log( 'Forgot Password: User already logged in, redirecting to login' );
	wp_safe_redirect( home_url( '/login/' ) . $url_suffix );
	exit;
}

$errors = [];
$success = '';

if ( isset( $_POST['forgot_password_submit'] ) ) {
	error_log( 'Forgot Password: Form submission detected with POST: ' . print_r( $_POST, true ) );
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'custom_forgot_password' ) ) {
		$errors[] = 'Invalid or expired form submission.';
		error_log( 'Forgot Password: Invalid nonce' );
	} else {
		$user_email = sanitize_email( $_POST['user_email'] ?? '' );
		if ( empty( $user_email ) ) {
			$errors[] = 'Email is required.';
		} elseif ( ! is_email( $user_email ) ) {
			$errors[] = 'Invalid email format.';
		} elseif ( ! email_exists( $user_email ) ) {
			$errors[] = 'No account found with that email.';
		}
		if ( empty( $errors ) ) {
			$user = get_user_by( 'email', $user_email );
			$reset_key = get_password_reset_key( $user );
			if ( ! is_wp_error( $reset_key ) ) {
				$reset_url = add_query_arg(
					[
						'key' => $reset_key,
						'login' => rawurlencode( $user->user_login ),
					],
					home_url( '/forgot-password/' )
				);
				$subject = 'Password Reset Request';
				$message = "Hi {$user->first_name},\n\n";
				$message .= "You requested a password reset for your Origin Recruitment account.\n";
				$message .= "Click the link below to reset your password:\n";
				$message .= "$reset_url\n\n";
				$message .= "If you did not request this, please ignore this email.\n";
				$message .= "Thank you,\nOrigin Recruitment Team";
				$headers = ['Content-Type: text/plain; charset=UTF-8'];
				error_log( 'Forgot Password: Attempting to send email to ' . $user_email . ' with reset URL: ' . $reset_url );
				if ( wp_mail( $user_email, $subject, $message, $headers ) ) {
					error_log( 'Forgot Password: Reset email sent to ' . $user_email );
					wp_safe_redirect( home_url( '/login/' ) . $url_suffix . ( $url_suffix ? '&' : '?' ) . 'reset=success' );
					exit;
				} else {
					$errors[] = 'Failed to send reset email. Please try again.';
					error_log( 'Forgot Password Error: Failed to send reset email to ' . $user_email );
					error_log( 'Forgot Password: Email content - Subject: ' . $subject . ', Message: ' . $message );
				}
			} else {
				$errors[] = 'Error generating reset link. Please try again.';
				error_log( 'Forgot Password Error: ' . $reset_key->get_error_message() );
			}
		}
	}
}

// Handle password reset form
if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
	$reset_key = sanitize_text_field( $_GET['key'] );
	$user_login = sanitize_text_field( $_GET['login'] );
	$user = check_password_reset_key( $reset_key, $user_login );

	if ( is_wp_error( $user ) ) {
		$errors[] = 'Invalid or expired reset link.';
		error_log( 'Forgot Password Error: Invalid reset key for ' . $user_login );
	} elseif ( isset( $_POST['reset_password_submit'] ) ) {
		error_log( 'Forgot Password: Reset submission detected with POST: ' . print_r( $_POST, true ) );
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'custom_reset_password' ) ) {
			$errors[] = 'Invalid or expired form submission.';
			error_log( 'Forgot Password: Invalid reset nonce' );
		} else {
			$new_password = $_POST['new_password'] ?? '';
			if ( empty( $new_password ) ) {
				$errors[] = 'New password is required.';
			} elseif ( strlen( $new_password ) < 8 ) {
				$errors[] = 'Password must be at least 8 characters.';
			}
			if ( empty( $errors ) ) {
				reset_password( $user, $new_password );
				error_log( 'Forgot Password: Password reset for User ID ' . $user->ID );
				$success = 'Password reset successfully! Redirecting to login...';
				wp_safe_redirect( home_url( '/login/' ) . $url_suffix . ( $url_suffix ? '&' : '?' ) . 'password_reset=success' );
				exit;
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
				<?php echo esc_html( $error ); ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( $success ) : ?>
		<div class="alert-solid alert-solid--success">
			<?php echo esc_html( $success ); ?>
		</div>
	<?php endif; ?>
	<?php if ( isset( $_GET['reset'] ) && $_GET['reset'] === 'success' ) : ?>
		<div class="alert-solid alert-solid--success">
			Password reset email sent. Please check your inbox.
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) && ! is_wp_error( $user ) ) : ?>
		<form method="post" action="" class="form form--reset-password space-y-4">
			<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
				<div class="form__field-wrapper form__field-wrapper--new_password">
					<label class="form__label form__label--new_password" for="reset_new_password">New Password <span class="text-red-500">*</span></label>
					<input class="form__field form__field--new_password" type="password" name="new_password" id="reset_new_password" placeholder="Enter New Password" required />
				</div>
			</fieldset>
			<?php wp_nonce_field( 'custom_reset_password', '_wpnonce' ); ?>
			<input type="hidden" name="reset_password_submit" value="1" />
			<div class="text-center space-y-2">
				<input type="submit" value="Reset Password" class="button w-full" id="reset_password_submit" />
				<div class="text-sm mt-2">
					<a href="<?php echo esc_url( home_url( '/login/' ) . $url_suffix ); ?>" class="no-underline">Back to login</a> | 
					<a href="<?php echo esc_url( home_url( '/register/' ) . $url_suffix ); ?>" class="no-underline">Sign up</a>
				</div>
			</div>
		</form>
	<?php else : ?>
		<form method="post" action="" class="form form--forgot-password space-y-4">
			<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
				<div class="form__field-wrapper form__field-wrapper--user_email">
					<label class="form__label form__label--user_email" for="forgot_user_email">Email Address <span class="text-red-500">*</span></label>
					<input class="form__field form__field--user_email" type="email" name="user_email" id="forgot_user_email" placeholder="e.g. john@smith.com" required value="<?php echo isset( $_POST['user_email'] ) ? esc_attr( $_POST['user_email'] ) : ''; ?>" />
				</div>
			</fieldset>
			<?php wp_nonce_field( 'custom_forgot_password', '_wpnonce' ); ?>
			<input type="hidden" name="forgot_password_submit" value="1" />
			<div class="text-center space-y-2">
				<input type="submit" value="Send Reset Link" class="button w-full" id="forgot_password_submit" />
				<div class="text-sm mt-2">
					<a href="<?php echo esc_url( home_url( '/login/' ) . $url_suffix ); ?>" class="no-underline">Back to login</a> | 
					<a href="<?php echo esc_url( home_url( '/register/' ) . $url_suffix ); ?>" class="no-underline">Sign up</a>
				</div>
			</div>
		</form>
	<?php endif; ?>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		const form = document.querySelector('.form.form--forgot-password') || document.querySelector('.form.form--reset-password');
		const submitButton = document.getElementById('forgot_password_submit') || document.getElementById('reset_password_submit');
		const emailInput = document.getElementById('forgot_user_email');
		const passwordInput = document.getElementById('reset_new_password');
		let isSubmitting = false;
		if (!form || !submitButton) {
			console.error('Form elements missing');
			return;
		}
		form.addEventListener('submit', function (event) {
			if (isSubmitting) {
				event.preventDefault();
				console.warn('Form submission blocked: already submitting');
				return;
			}
			let errors = [];
			if (emailInput && !emailInput.value) {
				errors.push('Email is required');
			} else if (emailInput && !/\S+@\S+\.\S+/.test(emailInput.value)) {
				errors.push('Email is invalid');
			}
			if (passwordInput && !passwordInput.value) {
				errors.push('New password is required');
			} else if (passwordInput && passwordInput.value.length < 8) {
				errors.push('New password must be at least 8 characters');
			}
			if (errors.length > 0) {
				event.preventDefault();
				console.error('Client-side validation errors: ', errors);
				alert('Please fix the following errors:\n' + errors.join('\n'));
			} else {
				isSubmitting = true;
				console.log('Form submission triggered: ' + (emailInput ? 'Forgot password' : 'Reset password'));
				console.log('Form data: ', new FormData(form));
				submitButton.disabled = true;
				submitButton.value = 'Submitting...';
				submitButton.classList.add('opacity-50', 'cursor-not-allowed');
			}
		});
		<?php if ( isset( $_GET['reset'] ) && $_GET['reset'] === 'success' ) : ?>
		setTimeout(function() {
			window.location.href = '<?php echo esc_url( home_url( '/login/' ) . $url_suffix ); ?>';
		}, 3000);
		<?php endif; ?>
		<?php if ( $success && str_contains( $success, 'Redirecting' ) ) : ?>
		setTimeout(function() {
			window.location.href = '<?php echo esc_url( home_url( '/login/' ) . $url_suffix . ( $url_suffix ? '&' : '?' ) . 'password_reset=success' ); ?>';
		}, 3000);
		<?php endif; ?>
	});
	</script>
</div>

<?php get_footer(); ?>
