<?php
/**
 * Template Name: Custom Registration
 * Path: wp-content/themes/origin/page-register.php
 */
ob_start();
error_log( 'Custom Registration: Page loaded' );
if ( is_user_logged_in() ) {
	error_log( 'Custom Registration: User already logged in, redirecting to profile' );
	wp_redirect( home_url( '/profile/' ) );
	exit;
}
$field_groups = acf_get_field_groups( [ 'user_form' => 'all' ] );
$acf_fields_by_group = [];
$excluded_fields = [ 'candidate_id', 'id', 'resume_url', 'cover_letter_url' ];
foreach ( $field_groups as $group ) {
	$acf_fields_by_group[$group['key']] = [
		'title' => $group['title'],
		'fields' => acf_get_fields( $group['key'] ),
	];
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
		$username = sanitize_user( $user_email, true );
		$resume = $_FILES['resume'] ?? null;
		$cover_letter = $_FILES['cover_letter'] ?? null;
		// Validation
		if ( empty( $first_name ) ) $errors[] = 'First name is required.';
		if ( empty( $last_name ) ) $errors[] = 'Last name is required.';
		if ( empty( $user_email ) ) $errors[] = 'Email is required.';
		elseif ( ! is_email( $user_email ) ) $errors[] = 'Invalid email format.';
		elseif ( email_exists( $user_email ) ) $errors[] = 'Email is already registered.';
		if ( empty( $user_password ) ) $errors[] = 'Password is required.';
		elseif ( strlen( $user_password ) < 8 ) $errors[] = 'Password must be at least 8 characters.';
		if ( username_exists( $username ) ) $errors[] = 'Username is already taken.';
		if ( ! $resume || $resume['error'] === UPLOAD_ERR_NO_FILE ) {
			$errors[] = 'Resume is required.';
		} elseif ( $resume['type'] !== 'application/pdf' ) {
			$errors[] = 'Resume must be a PDF file.';
		} elseif ( $resume['size'] > 5 * 1024 * 1024 ) {
			$errors[] = 'Resume file size must be less than 5MB.';
		}
		if ( $cover_letter && $cover_letter['error'] !== UPLOAD_ERR_NO_FILE ) {
			if ( $cover_letter['type'] !== 'application/pdf' ) {
				$errors[] = 'Cover letter must be a PDF file.';
			} elseif ( $cover_letter['size'] > 5 * 1024 * 1024 ) {
				$errors[] = 'Cover letter file size must be less than 5MB.';
			}
		}
		$acf_values = [];
		foreach ( $acf_fields_by_group as $group_key => $group_data ) {
			foreach ( $group_data['fields'] as $field ) {
				if ( in_array( $field['name'], $excluded_fields ) ) continue;
				$field_name = $field['name'];
				$field_label = $field['label'];
				$field_type = $field['type'];
				$value = isset( $_POST[$field_name] ) ? $_POST[$field_name] : '';
				if ( $field_type === 'text' || $field_type === 'url' || $field_type === 'number' ) {
					$value = sanitize_text_field( $value );
				} elseif ( $field_type === 'textarea' ) {
					$value = sanitize_textarea_field( $value );
				} elseif ( $field_type === 'select' ) {
					$value = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
				} elseif ( $field_type === 'taxonomy' ) {
					$value = is_array( $value ) ? array_map( 'intval', $value ) : intval( $value );
				}
				$acf_values[$field_name] = $value;
				if ( $field['required'] && ( empty( $value ) || ( is_array( $value ) && count( $value ) === 0 ) ) ) {
					$errors[] = "$field_label is required.";
				}
			}
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
				foreach ( $acf_values as $field_name => $value ) {
					update_field( $field_name, $value, 'user_' . $user_id );
					error_log( 'Custom Registration: Saved ACF field ' . $field_name . ' for user ID ' . $user_id . ': Value=' . var_export( $value, true ) );
				}
				$upload_dir = wp_upload_dir()['basedir'] . '/temp/';
				if ( ! file_exists( $upload_dir ) ) wp_mkdir_p( $upload_dir );
				if ( $resume && $resume['error'] !== UPLOAD_ERR_NO_FILE ) {
					$resume_path = $upload_dir . uniqid( 'resume_' ) . '.pdf';
					if ( move_uploaded_file( $resume['tmp_name'], $resume_path ) ) {
						update_user_meta( $user_id, '_temp_resume_path', $resume_path );
						error_log( 'Custom Registration: Resume uploaded to temp path: ' . $resume_path );
					} else {
						$errors[] = 'Failed to upload resume.';
						error_log( 'Custom Registration: Failed to upload resume: ' . print_r( $resume, true ) );
					}
				}
				if ( $cover_letter && $cover_letter['error'] !== UPLOAD_ERR_NO_FILE ) {
					$cover_letter_path = $upload_dir . uniqid( 'cover_letter_' ) . '.pdf';
					if ( move_uploaded_file( $cover_letter['tmp_name'], $cover_letter_path ) ) {
						update_user_meta( $user_id, '_temp_cover_letter_path', $cover_letter_path );
						error_log( 'Custom Registration: Cover letter uploaded to temp path: ' . $cover_letter_path );
					} else {
						$errors[] = 'Failed to upload cover letter.';
						error_log( 'Custom Registration: Failed to upload cover letter: ' . print_r( $cover_letter, true ) );
					}
				}
				if ( empty( $errors ) ) {
					do_action( 'oru_profile_updated', $user_id );
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
						wp_redirect( home_url( '/profile/?register=success' ) );
						exit;
					} else {
						$errors[] = 'Auto-login failed: ' . $user->get_error_message();
						error_log( 'Custom Registration Error: Auto-login failed for User ID ' . $user_id . ': ' . $user->get_error_message() );
						wp_delete_user( $user_id );
					}
				} else {
					wp_delete_user( $user_id );
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

<style>
.button:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}
</style>

<div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 fade-up">
	<?php if ( ! empty( $errors ) ) : ?>
		<?php foreach ( $errors as $error ) : ?>
			<div class="alert-solid alert-solid--danger">
 jaun				<?php echo esc_html( $error ); ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( isset( $_GET['register'] ) && $_GET['register'] === 'success' ) : ?>
		<div class="alert-solid alert-solid--success">
			Registration successful! Redirecting to your profile...
		</div>
	<?php endif; ?>
	<form method="post" action="" class="form form--register space-y-4" enctype="multipart/form-data">
		<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
			<div class="form__field-wrapper form__field-wrapper--first_name">
				<label class="form__label form__label--first_name" for="register_first_name">First Name <span class="text-red-500">*</span></label>
				<input class="form__field form__field--first_name" type="text" name="first_name" id="register_first_name" placeholder="e.g. John" required value="<?php echo isset( $_POST['first_name'] ) ? esc_attr( $_POST['first_name'] ) : ''; ?>" />
			</div>
			<div class="form__field-wrapper form__field-wrapper--last_name">
				<label class="form__label form__label--last_name" for="register_last_name">Last Name <span class="text-red-500">*</span></label>
				<input class="form__field form__field--last_name" type="text" name="last_name" id="register_last_name" placeholder="e.g. Smith" required value="<?php echo isset( $_POST['last_name'] ) ? esc_attr( $_POST['last_name'] ) : ''; ?>" />
			</div>
			<div class="form__field-wrapper form__field-wrapper--user_email">
				<label class="form__label form__label--user_email" for="register_user_email">Email Address <span class="text-red-500">*</span></label>
				<input class="form__field form__field--user_email" type="email" name="user_email" id="register_user_email" placeholder="e.g. john@smith.com" required value="<?php echo isset( $_POST['user_email'] ) ? esc_attr( $_POST['user_email'] ) : ''; ?>" />
			</div>
			<div class="form__field-wrapper form__field-wrapper--user_password">
				<label class="form__label form__label--user_password" for="register_user_password">Password <span class="text-red-500">*</span></label>
				<input class="form__field form__field--user_password" type="password" name="user_password" id="register_user_password" placeholder="Enter New Password" required />
			</div>
		</fieldset>
		<?php
		foreach ( $acf_fields_by_group as $group_key => $group_data ) {
			?>
			<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
				<?php
				foreach ( $group_data['fields'] as $field ) {
					if ( in_array( $field['name'], $excluded_fields ) ) continue;
					$field_name = $field['name'];
					$field_label = $field['label'];
					$field_type = $field['type'];
					$required = $field['required'] ? 'required' : '';
					$required_star = $field['required'] ? '<span class="text-red-500">*</span>' : '';
					$placeholder = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : 'Enter ' . esc_attr( $field_label );
					$value = isset( $_POST[$field_name] ) ? ( is_array( $_POST[$field_name] ) ? array_map( 'esc_attr', $_POST[$field_name] ) : esc_attr( $_POST[$field_name] ) ) : '';
					?>
					<div class="form__field-wrapper form__field-wrapper--<?php echo esc_attr( $field_name ); ?>">
						<label class="form__label form__label--<?php echo esc_attr( $field_name ); ?>" for="<?php echo 'register_' . esc_attr( $field_name ); ?>">
							<?php echo esc_html( $field_label ); ?>
							<?php echo $required_star; ?>
						</label>
						<?php
						switch ( $field_type ) {
							case 'text':
							case 'url':
							case 'number':
								?>
								<?php if ( in_array( $field_name, [ 'phone', 'mobile' ] ) ) : ?>
									<div class="relative">
										<input type="tel" 
											   class="form__field form__field--<?php echo esc_attr( $field_name ); ?> !ps-11" 
											   name="<?php echo esc_attr( $field_name ); ?>" 
											   id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
											   placeholder="<?php echo $placeholder; ?>" 
											   <?php echo $required; ?> 
											   value="<?php echo is_array( $value ) ? '' : $value; ?>" />
										<div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
											<span class="text-gray-dim dark:text-white-dim text-sm"><i class="fa-solid fa-phone"></i></span>
										</div>
									</div>
								<?php elseif ( $field_name === 'linkedin__s' ) : ?>
									<div class="relative">
										<input type="url" 
											   class="form__field form__field--<?php echo esc_attr( $field_name ); ?> !ps-11" 
											   name="<?php echo esc_attr( $field_name ); ?>" 
											   id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
											   placeholder="<?php echo $placeholder; ?>" 
											   <?php echo $required; ?> 
											   value="<?php echo is_array( $value ) ? '' : $value; ?>" />
										<div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
											<span class="text-gray-dim dark:text-white-dim text-sm"><i class="fa-brands fa-linkedin"></i></span>
										</div>
									</div>
								<?php elseif ( in_array( $field_name, [ 'current_salary', 'expected_salary' ] ) ) : ?>
									<div class="relative">
										<input type="number" 
											   class="form__field form__field--<?php echo esc_attr( $field_name ); ?> !ps-11" 
											   name="<?php echo esc_attr( $field_name ); ?>" 
											   id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
											   placeholder="<?php echo $placeholder; ?>" 
											   <?php echo $required; ?> 
											   value="<?php echo is_array( $value ) ? '' : $value; ?>" />
										<div class="absolute inset-y-0 start-0 flex items-center pointer-events-none z-20 ps-4">
											<span class="text-gray-dim dark:text-white-dim text-sm"><i class="fa-solid fa-sterling-sign"></i></span>
										</div>
									</div>
								<?php else : ?>
									<input type="<?php echo esc_attr( $field_type ); ?>" 
										   class="form__field form__field--<?php echo esc_attr( $field_name ); ?>" 
										   name="<?php echo esc_attr( $field_name ); ?>" 
										   id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
										   placeholder="<?php echo $placeholder; ?>" 
										   <?php echo $required; ?> 
										   value="<?php echo is_array( $value ) ? '' : $value; ?>" />
								<?php endif; ?>
								<?php
								break;
							case 'textarea':
								?>
								<textarea name="<?php echo esc_attr( $field_name ); ?>" 
										  class="form__field form__field--<?php echo esc_attr( $field_name ); ?>" 
										  id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
										  placeholder="<?php echo $placeholder; ?>" 
										  <?php echo $required; ?>><?php echo is_array( $value ) ? '' : esc_textarea( $value ); ?></textarea>
								<?php
								break;
							case 'select':
								$multiple = ! empty( $field['multiple'] ) ? 'multiple="multiple"' : '';
								$name = ! empty( $field['multiple'] ) ? $field_name . '[]' : $field_name;
								?>
								<?php if ( $multiple ) : ?>
									<select id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
											name="<?php echo esc_attr( $name ); ?>" 
											multiple 
											data-hs-select='{
												"placeholder": "<?php echo esc_attr( $field['instructions'] ?: 'Check all that apply' ); ?>",
												"dropdownClasses": "advanced-select__dropdown",
												"optionClasses": "advanced-select__option",
												"mode": "tags",
												"hasSearch": true,
												"searchClasses": "advanced-select__search",
												"searchWrapperClasses": "advanced-select__search-wrapper",
												"wrapperClasses": "advanced-select__wrapper",
												"tagsItemTemplate": "<div class=\"advanced-select__tag-item\"><div class=\"advanced-select__tag-item-icon\" data-icon></div><div class=\"advanced-select__tag-item-title\" data-title></div><div class=\"advanced-select__tag-item-remove\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
												"tagsInputId": "<?php echo 'register_' . esc_attr( $field_name ); ?>",
												"tagsInputClasses": "advanced-select__tags-input",
												"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200\" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500\" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
												"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
											}' 
											class="form__field form__field--<?php echo esc_attr( $field_name ); ?>">
										<?php foreach ( $field['choices'] as $choice_value => $choice_label ) : ?>
											<option value="<?php echo esc_attr( $choice_value ); ?>" <?php echo is_array( $value ) && in_array( $choice_value, $value ) ? 'selected' : ''; ?>>
												<?php echo esc_html( $choice_label ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<select id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
											name="<?php echo esc_attr( $name ); ?>" 
											data-hs-select='{
												"placeholder": "<?php echo esc_attr( $field['instructions'] ?: 'Choose' ); ?>",
												"toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
												"toggleClasses": "advanced-select__toggle",
												"dropdownClasses": "advanced-select__dropdown",
												"optionClasses": "advanced-select__option",
												"hasSearch": true,
												"searchPlaceholder": "Search...",
												"searchClasses": "advanced-select__search",
												"searchWrapperClasses": "advanced-select__search-wrapper",
												"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200\" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500\" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
												"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
											}' 
											class="form__field form__field--<?php echo esc_attr( $field_name ); ?>">
										<option value=""><?php echo esc_html( $field['instructions'] ?: 'Choose' ); ?></option>
										<?php foreach ( $field['choices'] as $choice_value => $choice_label ) : ?>
											<option value="<?php echo esc_attr( $choice_value ); ?>" <?php selected( $value, $choice_value ); ?>>
												<?php echo esc_html( $choice_label ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
								<?php
								break;
							case 'taxonomy':
								$taxonomy = $field['taxonomy'];
								$terms = get_terms( [
									'taxonomy' => $taxonomy,
									'hide_empty' => false,
								] );
								if ( is_wp_error( $terms ) || empty( $terms ) ) {
									error_log( 'Custom Registration: No terms found for taxonomy ' . $taxonomy );
									?>
									<p>No options available for <?php echo esc_html( $field_label ); ?>.</p>
									<?php
									break;
								}
								if ( $field['field_type'] === 'select' ) :
									?>
									<select id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
											name="<?php echo esc_attr( $field_name ); ?>" 
											data-hs-select='{
												"placeholder": "<?php echo esc_attr( $field['instructions'] ?: 'Choose' ); ?>",
												"toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
												"toggleClasses": "advanced-select__toggle",
												"dropdownClasses": "advanced-select__dropdown",
												"optionClasses": "advanced-select__option",
												"hasSearch": true,
												"searchPlaceholder": "Search...",
												"searchClasses": "advanced-select__search",
												"searchWrapperClasses": "advanced-select__search-wrapper",
												"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200\" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500\" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
												"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
											}' 
											class="form__field form__field--<?php echo esc_attr( $field_name ); ?>">
										<option value=""><?php echo esc_html( $field['instructions'] ?: 'Choose' ); ?></option>
										<?php foreach ( $terms as $term ) : ?>
											<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $value, $term->term_id ); ?>>
												<?php echo esc_html( $term->name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php elseif ( $field['field_type'] === 'multi_select' ) : ?>
									<select id="<?php echo 'register_' . esc_attr( $field_name ); ?>" 
											name="<?php echo esc_attr( $field_name ); ?>[]" 
											multiple 
											data-hs-select='{
												"placeholder": "<?php echo esc_attr( $field['instructions'] ?: 'Check all that apply' ); ?>",
												"dropdownClasses": "advanced-select__dropdown",
												"optionClasses": "advanced-select__option",
												"mode": "tags",
												"hasSearch": true,
												"searchClasses": "advanced-select__search",
												"searchWrapperClasses": "advanced-select__search-wrapper",
												"wrapperClasses": "advanced-select__wrapper",
												"tagsItemTemplate": "<div class=\"advanced-select__tag-item\"><div class=\"advanced-select__tag-item-icon\" data-icon></div><div class=\"advanced-select__tag-item-title\" data-title></div><div class=\"advanced-select__tag-item-remove\" data-remove><svg class=\"shrink-0 size-3\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg></div></div>",
												"tagsInputId": "<?php echo 'register_' . esc_attr( $field_name ); ?>",
												"tagsInputClasses": "advanced-select__tags-input",
												"optionTemplate": "<div class=\"flex items-center\"><div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-gray-800 dark:text-neutral-200\" data-title></div><div class=\"text-xs text-gray-500 dark:text-neutral-500\" data-description></div></div><div class=\"ms-auto\"><span class=\"hidden hs-selected:block\"><svg class=\"shrink-0 size-4 text-gold\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z\"/></svg></span></div></div>",
												"extraMarkup": "<div class=\"absolute top-1/2 end-3 -translate-y-1/2\"><svg class=\"shrink-0 size-3.5 text-gray-500 dark:text-neutral-500\" xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"m7 15 5 5 5-5\"/><path d=\"m7 9 5-5 5 5\"/></svg></div>"
											}' 
											class="form__field form__field--<?php echo esc_attr( $field_name ); ?>">
										<?php foreach ( $terms as $term ) : ?>
											<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo is_array( $value ) && in_array( $term->term_id, $value ) ? 'selected' : ''; ?>>
												<?php echo esc_html( $term->name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
								<?php
								break;
						}
						?>
					</div>
					<?php
				}
				?>
			</fieldset>
			<?php
		}
		?>
		<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
			<div class="form__field-wrapper form__field-wrapper--resume">
				<label class="form__label form__label--resume" for="register_resume">Resume (PDF) <span class="text-red-500">*</span></label>
				<p class="text-sm text-red-500 mt-0 mb-2">No resume uploaded. Please upload a PDF resume.</p>
				<input class="form__field form__field--resume form__field--upload" type="file" name="resume" id="register_resume" accept="application/pdf" required />
			</div>
		</fieldset>
		<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
			<div class="form__field-wrapper form__field-wrapper--cover_letter">
				<label class="form__label form__label--cover_letter" for="register_cover_letter">Cover Letter (PDF, Optional)</label>
				<p class="text-sm text-gray-500 dark:text-neutral-400 mt-0 mb-2">No cover letter uploaded. Optionally upload a PDF cover letter.</p>
				<input class="form__field form__field--cover_letter form__field--upload" type="file" name="cover_letter" id="register_cover_letter" accept="application/pdf" />
			</div>
		</fieldset>
		<?php wp_nonce_field( 'custom_register', '_wpnonce' ); ?>
		<input type="hidden" name="register_submit" value="1" />
		<div class="text-center space-y-2">
			<input type="submit" value="Create Your Account" class="button w-full" id="register_submit" />
			<div class="text-sm mt-2">Already have an account? <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="no-underline">Log in</a></div>
		</div>
	</form>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		const form = document.querySelector('.form.form--register');
		const submitButton = document.getElementById('register_submit');
		const emailInput = document.getElementById('register_user_email');
		const resumeInput = document.getElementById('register_resume');
		const coverLetterInput = document.getElementById('register_cover_letter');
		let isSubmitting = false;
		if (!form || !submitButton || !emailInput || !resumeInput) {
			console.error('Register form elements missing');
			return;
		}
		form.addEventListener('submit', function (event) {
			if (isSubmitting) {
				event.preventDefault();
				console.warn('Form submission blocked: already submitting');
				return;
			}
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
			if (!document.getElementById('register_first_name').value) {
				errors.push('First name is required');
			}
			if (!document.getElementById('register_last_name').value) {
				errors.push('Last name is required');
			}
			if (!resumeInput.files.length) {
				errors.push('Resume is required');
			} else if (resumeInput.files[0].type !== 'application/pdf') {
				errors.push('Resume must be a PDF file');
			} else if (resumeInput.files[0].size > 5 * 1024 * 1024) {
				errors.push('Resume file size must be less than 5MB');
			}
			if (coverLetterInput && coverLetterInput.files.length) {
				if (coverLetterInput.files[0].type !== 'application/pdf') {
					errors.push('Cover letter must be a PDF file');
				} else if (coverLetterInput.files[0].size > 5 * 1024 * 1024) {
					errors.push('Cover letter file size must be less than 5MB');
				}
			}
			<?php
			foreach ( $acf_fields_by_group as $group_key => $group_data ) {
				foreach ( $group_data['fields'] as $field ) {
					if ( in_array( $field['name'], $excluded_fields ) ) continue;
					$field_name = $field['name'];
					$field_label = $field['label'];
					$required = $field['required'] ? 'true' : 'false';
					?>
					const <?php echo esc_js( $field_name ); ?>Input = document.getElementById('<?php echo 'register_' . esc_js( $field_name ); ?>');
					if (<?php echo $required; ?> && !<?php echo esc_js( $field_name ); ?>Input.value) {
						errors.push('<?php echo esc_js( $field_label ); ?> is required');
					}
					<?php
				}
			}
			?>
			if (errors.length > 0) {
				event.preventDefault();
				console.error('Client-side validation errors: ', errors);
				alert('Please fix the following errors:\n' + errors.join('\n'));
			} else {
				isSubmitting = true;
				console.log('Form submission triggered with email: ' + emailInput.value);
				console.log('Form data: ', new FormData(form));
				submitButton.disabled = true;
				submitButton.value = 'Submitting...';
				submitButton.classList.add('opacity-50', 'cursor-not-allowed');
			}
		});
		<?php if ( isset( $_GET['register'] ) && $_GET['register'] === 'success' ) : ?>
		setTimeout(function() {
			window.location.href = '<?php echo esc_url( home_url( '/profile/' ) ); ?>';
		}, 1000);
		<?php endif; ?>
	});
	</script>
</div>

<?php get_footer(); ?>
