<?php
/**
 * Template Name: User Profile
 * Path: wp-content/themes/origin/page-user-profile.php
 */

// Buffer output to prevent headers issues
ob_start();

error_log( 'User Profile: Page loaded' );

if ( ! is_user_logged_in() ) {
	error_log( 'User Profile: User not logged in, redirecting to login' );
	wp_redirect( home_url( '/login/' ) );
	exit;
}

$current_user = wp_get_current_user();
$field_groups = acf_get_field_groups( [ 'user_form' => 'all' ] );
$acf_fields_by_group = [];
$excluded_fields = [ 'candidate_id', 'id' ]; // Fields to exclude from rendering

foreach ( $field_groups as $group ) {
	$acf_fields_by_group[$group['key']] = [
		'title' => $group['title'],
		'fields' => acf_get_fields( $group['key'] ),
	];
}

$errors = [];
$success = '';

if ( isset( $_POST['profile_submit'] ) ) {
	error_log( 'User Profile: Form submission detected with POST: ' . print_r( $_POST, true ) );
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'custom_profile' ) ) {
		$errors[] = 'Invalid or expired form submission.';
		error_log( 'User Profile: Invalid nonce' );
	} else {
		$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
		$last_name = sanitize_text_field( $_POST['last_name'] ?? '' );
		$user_email = sanitize_email( $_POST['user_email'] ?? '' );

		// Validation for core fields
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
		} elseif ( $user_email !== $current_user->user_email && email_exists( $user_email ) ) {
			$errors[] = 'Email is already registered.';
		}

		// Dynamic ACF field validation
		$acf_values = [];
		foreach ( $acf_fields_by_group as $group_key => $group_data ) {
			foreach ( $group_data['fields'] as $field ) {
				if ( in_array( $field['name'], $excluded_fields ) ) {
					continue;
				}
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

				// Validate required fields
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
				// Save ACF fields to user meta
				foreach ( $acf_values as $field_name => $value ) {
					update_user_meta( $current_user->ID, $field_name, $value );
				}
				error_log( 'User Profile: User ID ' . $current_user->ID . ' updated, saved meta: ' . print_r( $acf_values, true ) );
				$success = 'Profile updated successfully!';
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
			<?php echo $success; ?>
		</div>
	<?php endif; ?>
	<form method="post" action="" class="form form--profile space-y-4">
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
				<input class="form__field form__field--user_email" type="email" name="user_email" id="profile_user_email" placeholder="e.g. john@smith.com" required value="<?php echo esc_attr( $current_user->user_email ); ?>" />
			</div>
		</fieldset>
		<?php
		// Render ACF fields by group
		foreach ( $acf_fields_by_group as $group_key => $group_data ) {
			?>
			<fieldset class="form__fieldset bg-[#f5f5f5] dark:bg-[#222222] p-single mb-half">
				<?php
				foreach ( $group_data['fields'] as $field ) {
					if ( in_array( $field['name'], $excluded_fields ) ) {
						continue; // Skip excluded fields
					}
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
		
		<?php wp_nonce_field( 'custom_profile', '_wpnonce' ); ?>
		<input type="hidden" name="profile_submit" value="1" />
		<div class="text-center space-y-2">
			<input type="submit" value="Update Profile" class="button w-full" id="profile_submit" />
			<div class="text-sm mt-2"><a href="<?php echo esc_url( wp_logout_url( home_url( '/login/' ) ) ); ?>" class="no-underline">Log out</a></div>
		</div>
	</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('.form.form--profile');
	const submitButton = document.getElementById('profile_submit');
	const emailInput = document.getElementById('profile_user_email');
	
	if (form && submitButton) {
		form.addEventListener('submit', function (event) {
			let errors = [];
			if (!emailInput.value) {
				errors.push('Email is required');
			} else if (!/\S+@\S+\.\S+/.test(emailInput.value)) {
				errors.push('Email is invalid');
			}
			if (!document.getElementById('profile_first_name').value) {
				errors.push('First name is required');
			}
			if (!document.getElementById('profile_last_name').value) {
				errors.push('Last name is required');
			}
			<?php
			// Dynamic client-side validation for ACF fields
			foreach ( $acf_fields_by_group as $group_key => $group_data ) {
				foreach ( $group_data['fields'] as $field ) {
					if ( in_array( $field['name'], $excluded_fields ) ) {
						continue;
					}
					$field_name = $field['name'];
					$field_label = $field['label'];
					$field_type = $field['type'];
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
				console.log('Form submission attempted with email: ' + emailInput.value);
				console.log('Form data: ', new FormData(form));
				submitButton.disabled = true;
				submitButton.value = 'Submitting...';
			}
		});
	}
});
</script>

<?php get_footer(); ?>
