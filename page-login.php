<?php
/**
 * Template Name: Custom Login
 * Path: wp-content/themes/origin/page-login.php
 */

// Buffer output to prevent headers issues
ob_start();

error_log( 'Custom Login: Page loaded' );

// Handle redirect_to parameter
$redirect_url = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/profile/' );
if ( parse_url( $redirect_url, PHP_URL_HOST ) !== parse_url( home_url(), PHP_URL_HOST ) ) {
	$redirect_url = home_url( '/profile/' );
	$url_suffix = '';
} else {
	$url_suffix = isset( $_GET['redirect_to'] ) ? '?redirect_to=' . urlencode( $redirect_url ) : '';
}
error_log( 'Custom Login: redirect_to=' . ( $redirect_url ?: 'not set' ) . ', url_suffix=' . ( $url_suffix ?: 'empty' ) );

if ( is_user_logged_in() ) {
	error_log( 'Custom Login: User already logged in, redirecting to ' . $redirect_url );
	wp_safe_redirect( $redirect_url );
	exit;
}

$errors = [];
$success = '';

if ( isset( $_POST['login_submit'] ) ) {
	error_log( 'Custom Login: Form submission detected with POST: ' . print_r( $_POST, true ) );
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'custom_login' ) ) {
		$errors[] = 'Invalid or expired form submission.';
		error_log( 'Custom Login: Invalid nonce' );
	} else {
		$user_email = sanitize_email( $_POST['user_email'] ?? '' );
		$user_password = $_POST['user_password'] ?? '';
		$remember = isset( $_POST['remember'] ) && $_POST['remember'] === '1';

		// Validation
		if ( empty( $user_email ) ) {
			$errors[] = 'Email is required.';
		} elseif ( ! is_email( $user_email ) ) {
			$errors[] = 'Invalid email format.';
		}
		if ( empty( $user_password ) ) {
			$errors[] = 'Password is required.';
		}

		if ( empty( $errors ) ) {
			$creds = [
				'user_login' => $user_email, // Uses email as username
				'user_password' => $user_password,
				'remember' => $remember,
			];
			$user = wp_signon( $creds, is_ssl() );
			if ( ! is_wp_error( $user ) ) {
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID, $remember, is_ssl() );
				error_log( 'Custom Login: User ID ' . $user->ID . ' logged in successfully, redirecting to ' . $redirect_url );
				$success = 'Login successful! Redirecting...';
				wp_safe_redirect( $redirect_url );
				exit;
			} else {
				$errors[] = str_replace( '/wp-login.php?action=lostpassword', '/forgot-password/' . $url_suffix, $user->get_error_message() );
				error_log( 'Custom Login Error: ' . $user->get_error_message() );
			}
		}
	}
} elseif ( isset( $_GET['password_reset'] ) && $_GET['password_reset'] === 'success' ) {
	$success = 'Password successfully reset. Please log in again.';
	error_log( 'Custom Login: Password reset success message displayed' );
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
	<form method="post" action="" class="form form--login space-y-4">
		<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
			<div class="form__field-wrapper form__field-wrapper--user_email">
				<label class="form__label form__label--user_email" for="login_user_email">Email Address <span class="text-red-500">*</span></label>
				<input class="form__field form__field--user_email" type="email" name="user_email" id="login_user_email" placeholder="e.g. john@smith.com" required value="<?php echo isset( $_POST['user_email'] ) ? esc_attr( $_POST['user_email'] ) : ''; ?>" />
			</div>
			<div class="form__field-wrapper form__field-wrapper--user_password">
				<label class="form__label form__label--user_password" for="login_user_password">Password <span class="text-red-500">*</span></label>
				<input class="form__field form__field--user_password" type="password" name="user_password" id="login_user_password" placeholder="Enter Password" required />
			</div>
			<div class="form__field-wrapper form__field-wrapper--remember">
				<div class="flex">
					<input type="checkbox" name="remember" id="login_remember" value="1" class="form__checkbox" />
					<label for="login_remember" class="form__checkbox-label">Remember Me</label>
				</div>
			</div>
		</fieldset>
		
		<?php wp_nonce_field( 'custom_login', '_wpnonce' ); ?>
		<input type="hidden" name="login_submit" value="1" />
		<div class="text-center space-y-2">
			<input type="submit" value="Log In" class="button w-full" id="login_submit" />
			<div class="text-sm mt-2">Don't have an account? <a href="<?php echo esc_url( home_url( '/register/' ) . $url_suffix ); ?>" class="no-underline">Sign up now!</a></div>
			<div class="text-sm mt-2"><a href="<?php echo esc_url( home_url( '/forgot-password/' ) . $url_suffix ); ?>" class="no-underline">Forgot Password?</a></div>
		</div>
	</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('.form.form--login');
	const submitButton = document.getElementById('login_submit');
	const emailInput = document.getElementById('login_user_email');
	
	if (form && submitButton) {
		form.addEventListener('submit', function (event) {
			let errors = [];
			if (!emailInput.value) {
				errors.push('Email is required');
			} else if (!/\S+@\S+\.\S+/.test(emailInput.value)) {
				errors.push('Email is invalid');
			}
			if (!document.getElementById('login_user_password').value) {
				errors.push('Password is required');
			}
			if (errors.length > 0) {
				event.preventDefault();
				console.error('Client-side validation errors: ', errors);
				alert('Please fix the following errors:\n' + errors.join('\n'));
			} else {
				console.log('Login submission attempted with email: ' + emailInput.value);
				console.log('Form data: ', new FormData(form));
				submitButton.disabled = true;
				submitButton.value = 'Submitting...';
			}
		});
	}

	<?php if ( $success && str_contains( $success, 'Redirecting' ) ) : ?>
		setTimeout(function() {
			window.location.href = '<?php echo esc_url( $redirect_url ); ?>';
		}, 1000);
	<?php endif; ?>
});
</script>

<?php get_footer(); ?>
