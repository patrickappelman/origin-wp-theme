<?php
/**
 * Template Name: User Profile
 * Path: wp-content/themes/origin/page-profile.php
 */
ob_start();
error_log( 'User Profile: Page loaded' );

// Handle redirect_to parameter for unauthenticated users
$redirect_url = isset( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/profile/?profile_update=success' );
if ( parse_url( $redirect_url, PHP_URL_HOST ) !== parse_url( home_url(), PHP_URL_HOST ) ) {
	$redirect_url = home_url( '/profile/' );
	$url_suffix = '';
} else {
	$url_suffix = isset( $_GET['redirect_to'] ) ? '?redirect_to=' . urlencode( $redirect_url ) : '';
}
error_log( 'User Profile: redirect_to=' . ( $redirect_url ?: 'not set' ) . ', url_suffix=' . ( $url_suffix ?: 'empty' ) );

if ( ! is_user_logged_in() ) {
	$login_redirect_url = home_url( '/login/' ) . '?redirect_to=' . urlencode( home_url( '/profile/' ) );
	error_log( 'User Profile: User not logged in, redirecting to ' . $login_redirect_url );
	wp_safe_redirect( $login_redirect_url );
	exit;
}

$current_user = wp_get_current_user();
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
if ( isset( $_POST['profile_submit'] ) ) {
	error_log( 'User Profile: Form submission detected with POST: ' . print_r( $_POST, true ) );
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'custom_profile' ) ) {
		$errors[] = 'Invalid or expired form submission.';
		error_log( 'User Profile: Invalid nonce' );
	} else {
		$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
		$last_name = sanitize_text_field( $_POST['last_name'] ?? '' );
		$user_email = sanitize_email( $_POST['user_email'] ?? '' );
		$resume = $_FILES['resume'] ?? null;
		$cover_letter = $_FILES['cover_letter'] ?? null;
		$resume_url = get_user_meta( $current_user->ID, 'resume_url', true );
		if ( empty( $first_name ) ) $errors[] = 'First name is required.';
		if ( empty( $last_name ) ) $errors[] = 'Last name is required.';
		if ( $user_email !== $current_user->user_email ) $errors[] = 'Email cannot be changed.';
		if ( empty( $resume_url ) && ( ! $resume || $resume['error'] === UPLOAD_ERR_NO_FILE ) ) {
			$errors[] = 'Resume is required when no resume is uploaded.';
		}
		if ( $resume && $resume['error'] !== UPLOAD_ERR_NO_FILE ) {
			if ( $resume['type'] !== 'application/pdf' ) {
				$errors[] = 'Resume must be a PDF file.';
			} elseif ( $resume['size'] > 5 * 1024 * 1024 ) {
				$errors[] = 'Resume file size must be less than 5MB.';
			}
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
				'ID' => $current_user->ID,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'user_email' => $user_email,
			];
			$user_id = wp_update_user( $user_data );
			if ( ! is_wp_error( $user_id ) ) {
				foreach ( $acf_values as $field_name => $value ) {
					update_user_meta( $current_user->ID, $field_name, $value );
					error_log( 'User Profile: Saved ACF field ' . $field_name . ' for user ID ' . $current_user->ID . ': Result=' . var_export( $value, true ) );
				}
				$upload_dir = wp_upload_dir()['basedir'] . '/temp/';
				if ( ! file_exists( $upload_dir ) ) wp_mkdir_p( $upload_dir );
				if ( $resume && $resume['error'] !== UPLOAD_ERR_NO_FILE ) {
					$resume_path = $upload_dir . uniqid( 'resume_' ) . '.pdf';
					if ( move_uploaded_file( $resume['tmp_name'], $resume_path ) ) {
						update_user_meta( $current_user->ID, '_temp_resume_path', $resume_path );
						error_log( 'User Profile: Resume uploaded to temp path: ' . $resume_path );
					} else {
						$errors[] = 'Failed to upload resume.';
						error_log( 'User Profile: Failed to upload resume: ' . print_r( $resume, true ) );
					}
				}
				if ( $cover_letter && $cover_letter['error'] !== UPLOAD_ERR_NO_FILE ) {
					$cover_letter_path = $upload_dir . uniqid( 'cover_letter_' ) . '.pdf';
					if ( move_uploaded_file( $cover_letter['tmp_name'], $cover_letter_path ) ) {
						update_user_meta( $current_user->ID, '_temp_cover_letter_path', $cover_letter_path );
						error_log( 'User Profile: Cover letter uploaded to temp path: ' . $cover_letter_path );
					} else {
						$errors[] = 'Failed to upload cover letter.';
						error_log( 'User Profile: Failed to upload cover letter: ' . print_r( $cover_letter, true ) );
					}
				}
				if ( empty( $errors ) ) {
					do_action( 'oru_profile_updated', $current_user->ID );
					wp_safe_redirect( $redirect_url );
					exit;
				}
			} else {
				$errors[] = $user_id->get_error_message();
				error_log( 'User Profile Error: ' . $user_id->get_error_message() );
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
				<?php echo esc_html( $error ); ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( isset( $_GET['profile_update'] ) && $_GET['profile_update'] === 'success' ) : ?>
		<div class="alert-solid alert-solid--success">
			Profile updated successfully!
		</div>
	<?php endif; ?>
	<form method="post" action="" class="form form--profile space-y-4" enctype="multipart/form-data">
		<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
			<div class="form__field-wrapper form__field-wrapper--first_name">
				<label class="form__label form__label--first_name" for="profile_first_name">First Name <span class="text-red-500">*</span></label>
				<input class="form__field form__field--first_name" type="text" name="first_name" id="profile_first_name" placeholder="e.g. John" required value="<?php echo esc_attr( $current_user->first_name ); ?>" />
			</div>
			<div class="form__field-wrapper form__field-wrapper--last_name">
				<label class="form__label form__label--last_name" for="profile_last_name">Last Name <span class="text-red-500">*</span></label>
				<input class="form__field form__field--last_name" type="text" name="last_name" id="profile_last_name" placeholder="e.g. Smith" required value="<?php echo esc_attr( $current_user->last_name ); ?>" />
			</div>
			<div class="form__field-wrapper form__field-wrapper--user_email">
				<label class="form__label form__label--user_email" for="profile_user_email">Email Address <span class="text-red-500">*</span></label>
				<input class="form__field form__field--user_email" type="email" name="user_email" id="profile_user_email" placeholder="e.g. john@smith.com" readonly value="<?php echo esc_attr( $current_user->user_email ); ?>" />
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
					$value = get_user_meta( $current_user->ID, $field_name, true );
					$value = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( $value );
					?>
					<div class="form__field-wrapper form__field-wrapper--<?php echo esc_attr( $field_name ); ?>">
						<label class="form__label form__label--<?php echo esc_attr( $field_name ); ?>" for="<?php echo 'profile_' . esc_attr( $field_name ); ?>">
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
											   id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
											   id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
											   id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
										   id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
										  id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
										  placeholder="<?php echo $placeholder; ?>" 
										  <?php echo $required; ?>><?php echo is_array( $value ) ? '' : esc_textarea( $value ); ?></textarea>
								<?php
								break;
							case 'select':
								$multiple = ! empty( $field['multiple'] ) ? 'multiple="multiple"' : '';
								$name = ! empty( $field['multiple'] ) ? $field_name . '[]' : $field_name;
								?>
								<?php if ( $multiple ) : ?>
									<select id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
												"tagsInputId": "<?php echo 'profile_' . esc_attr( $field_name ); ?>",
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
									<select id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
									error_log( 'No terms found for taxonomy: ' . $taxonomy );
									?>
									<p>No options available for <?php echo esc_html( $field_label ); ?>.</p>
									<?php
									break;
								}
								if ( $field['field_type'] === 'select' ) :
									?>
									<select id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
									<select id="<?php echo 'profile_' . esc_attr( $field_name ); ?>" 
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
												"tagsInputId": "<?php echo 'profile_' . esc_attr( $field_name ); ?>",
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
				<label class="form__label form__label--resume">Resume (PDF) <span class="text-red-500">*</span></label>
				<?php $resume_url = get_user_meta( $current_user->ID, 'resume_url', true ); ?>
				<?php if ( $resume_url ) : ?>
					<?php
					$parts = explode( '/', rtrim( $resume_url, '/' ) );
					$attachment_id = end( $parts );
					$candidate_id = count( $parts ) >= 3 ? $parts[count( $parts ) - 3] : '';
					if ( ctype_digit( $candidate_id ) && ctype_digit( $attachment_id ) ) {
						$download_url = wp_nonce_url( add_query_arg( [ 'action' => 'download_resume' ], home_url() ), 'download_resume' );
						?>
						<p class="text-sm not-prose mt-5"><a href="<?php echo esc_url( $download_url ); ?>" class="button download-resume text-sm mb-2">Download Your Resume</a></p>
						<button type="button" class="hs-collapse-toggle button button--outline inline-flex items-center gap-x-2 text-sm" id="hs-basic-collapse-resume" aria-expanded="false" aria-controls="profile_upload_new_resume" data-hs-collapse="#profile_upload_new_resume">
							Upload New Resume
							<svg class="hs-collapse-open:rotate-180 shrink-0 size-4 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="m6 9 6 6 6-6"></path>
							</svg>
						</button>
						<div id="profile_upload_new_resume" class="hs-collapse hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="hs-basic-collapse-resume">
							<div class="mt-5">
								<p class="text-gray-500 dark:text-neutral-400">
									<input class="form__field form__field--resume form__field--upload" type="file" name="resume" id="profile_resume" accept="application/pdf" <?php echo empty( $resume_url ) ? 'required' : ''; ?> />
								</p>
							</div>
						</div>
					<?php
					} else {
						error_log( 'User Profile: Invalid resume_url format for user ID ' . $current_user->ID . ': ' . $resume_url );
					}
					?>
				<?php else : ?>
					<p class="text-sm text-red-500 mt-0 mb-2">No resume uploaded. Please upload a PDF resume.</p>
					<input class="form__field form__field--resume form__field--upload" type="file" name="resume" id="profile_resume" accept="application/pdf" required />
				<?php endif; ?>
			</div>
		</fieldset>
		<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
			<div class="form__field-wrapper form__field-wrapper--cover_letter">
				<label class="form__label form__label--cover_letter">Cover Letter (PDF, Optional)</label>
				<?php $cover_letter_url = get_user_meta( $current_user->ID, 'cover_letter_url', true ); ?>
				<?php if ( $cover_letter_url ) : ?>
					<?php
					$parts = explode( '/', rtrim( $cover_letter_url, '/' ) );
					$attachment_id = end( $parts );
					$candidate_id = count( $parts ) >= 3 ? $parts[count( $parts ) - 3] : '';
					if ( ctype_digit( $candidate_id ) && ctype_digit( $attachment_id ) ) {
						$download_url = wp_nonce_url( add_query_arg( [ 'action' => 'download_cover_letter' ], home_url() ), 'download_cover_letter' );
						?>
						<p class="text-sm not-prose mt-5"><a href="<?php echo esc_url( $download_url ); ?>" class="button download-cover-letter text-sm mb-2">Download Your Cover Letter</a></p>
						<button type="button" class="hs-collapse-toggle button button--outline inline-flex items-center gap-x-2 text-sm" id="hs-basic-collapse-cover-letter" aria-expanded="false" aria-controls="profile_upload_new_cover_letter" data-hs-collapse="#profile_upload_new_cover_letter">
							Upload New Cover Letter
							<svg class="hs-collapse-open:rotate-180 shrink-0 size-4 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="m6 9 6 6 6-6"></path>
							</svg>
						</button>
						<div id="profile_upload_new_cover_letter" class="hs-collapse hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="hs-basic-collapse-cover-letter">
							<div class="mt-5">
								<p class="text-gray-500 dark:text-neutral-400">
									<input class="form__field form__field--cover_letter form__field--upload" type="file" name="cover_letter" id="profile_cover_letter" accept="application/pdf" />
								</p>
							</div>
						</div>
					<?php
					} else {
						error_log( 'User Profile: Invalid cover_letter_url format for user ID ' . $current_user->ID . ': ' . $cover_letter_url );
					}
					?>
				<?php else : ?>
					<p class="text-sm text-gray-500 dark:text-neutral-400 mt-0 mb-2">No cover letter uploaded. Optionally upload a PDF cover letter.</p>
					<input class="form__field form__field--cover_letter form__field--upload" type="file" name="cover_letter" id="profile_cover_letter" accept="application/pdf" />
				<?php endif; ?>
			</div>
		</fieldset>
		<?php wp_nonce_field( 'custom_profile', '_wpnonce' ); ?>
		<input type="hidden" name="profile_submit" value="1" />
		<div class="text-center space-y-2">
			<input type="submit" value="Update Profile" class="button w-full" id="profile_submit" />
			<div class="text-sm mt-2"><a href="<?php echo esc_url( wp_logout_url( home_url( '/login/' ) . $url_suffix ) ); ?>" class="no-underline">Log out</a></div>
		</div>
	</form>
	<script>
	try {
		const form = document.querySelector('.form.form--profile');
		const submitButton = document.getElementById('profile_submit');
		const resumeInput = document.getElementById('profile_resume');
		const coverLetterInput = document.getElementById('profile_cover_letter');
		const downloadButton = document.querySelector('.download-resume');
		const downloadCoverLetterButton = document.querySelector('.download-cover-letter');
		let isSubmitting = false;
		if (!form || !submitButton || !resumeInput) {
			console.error('Profile form elements missing');
			throw new Error('Form elements missing');
		}
		form.addEventListener('submit', function (event) {
			if (isSubmitting) {
				event.preventDefault();
				console.warn('Form submission blocked: already submitting');
				return;
			}
			let errors = [];
			if (!document.getElementById('profile_first_name').value) {
				errors.push('First name is required');
			}
			if (!document.getElementById('profile_last_name').value) {
				errors.push('Last name is required');
			}
			if (resumeInput.hasAttribute('required') && !resumeInput.files.length) {
				errors.push('Resume is required when no resume is uploaded');
			}
			if (resumeInput.files.length) {
				if (resumeInput.files[0].type !== 'application/pdf') {
					errors.push('Resume must be a PDF file');
				} else if (resumeInput.files[0].size > 5 * 1024 * 1024) {
					errors.push('Resume file size must be less than 5MB');
				}
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
					const <?php echo esc_js( $field_name ); ?>Input = document.getElementById('<?php echo 'profile_' . esc_js( $field_name ); ?>');
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
				console.log('Profile form submission triggered');
				console.log('Form data: ', new FormData(form));
				submitButton.disabled = true;
				submitButton.value = 'Submitting...';
				submitButton.classList.add('opacity-50', 'cursor-not-allowed');
			}
		});
	} catch (error) {
		console.error('Profile form script error: ', error);
	}
	</script>
</div>

<?php get_footer(); ?>
