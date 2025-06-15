<?php


/* LOAD BOILERPLATE ASSETS */

function boilerplate_load_assets() {
  wp_enqueue_script('ourmainjs', get_theme_file_uri('/build/index.js'), array('wp-element', 'react-jsx-runtime'), '1.0', true);
  wp_enqueue_style('ourmaincss', get_theme_file_uri('/build/index.css'));
}

add_action('wp_enqueue_scripts', 'boilerplate_load_assets');





/* ADD THEME SUPPORT */

function boilerplate_add_support() {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
}

add_action('after_setup_theme', 'boilerplate_add_support');





/* REGISTER THEME MENUS */

function origin_register_menus() {
  register_nav_menus(
      array(
      'header-menu' => __( 'Desktop - Header Menu' ),
      'sidebar-menu' => __( 'Mobile - Sidebar Menu' ),
      'footer-candidates' => __( 'Footer - Candidates Menu' ),
      'footer-clients' => __( 'Footer - Clients Menu' ),
      'footer-resources' => __( 'Footer - Resources Menu' ),
      'footer-company' => __( 'Footer - Company Menu' ),
      'footer-legal' => __( 'Footer - Legal Menu' )
      )
  );
}
add_action( 'init', 'origin_register_menus' );





/**
 * Font Awesome Kit Setup
 *
 * This will add your Font Awesome Kit to the front-end, the admin back-end,
 * and the login screen area.
 */
if (! function_exists('fa_custom_setup_kit') ) {
  function fa_custom_setup_kit($kit_url = '') {
    foreach ( [ 'wp_enqueue_scripts', 'admin_enqueue_scripts', 'login_enqueue_scripts' ] as $action ) {
      add_action(
        $action,
        function () use ( $kit_url ) {
          wp_enqueue_script( 'font-awesome-kit', $kit_url, [], null );
        }
      );
    }
  }
}

fa_custom_setup_kit('https://kit.fontawesome.com/8bea96acec.js');





/* OVERRIDE ARCHIVE TITLES */

function change_archive_page_title( $title ) {
  if ( is_home() ) {
    // If is the primary Archives Page (Blog)
    $title = get_the_title( get_option( 'page_for_posts' ) );
    // $title = "Origin Insights";
  } else if( is_category() ) {
    $title = single_cat_title();
  } else if ( is_tax('language') ) {
    $title = single_term_title('',false) . " Multilingual Jobs";
  } else if ( is_tax('country') ) {
    $title = "Multilingual Jobs in " . single_term_title('',false);
  } else if ( is_tax('industry') ) {
    $title = single_term_title('',false) . " Multilingual Jobs";
  }
  return $title;
}

add_filter( 'get_the_archive_title', 'change_archive_page_title' );





/* ENABLE EXCERPT SUPPORT FOR PAGES */

add_post_type_support( 'page', 'excerpt' );





// Test
/*
function wporg_add_custom_box() {
	$screens = [ 'jobs', 'wporg_cpt' ];
	foreach ( $screens as $screen ) {
		add_meta_box(
			'wporg_box_id',           // Unique ID
			'Custom Meta Box Title',  // Box title
			'wporg_custom_box_html',  // Content callback, must be of type callable
			$screen                   // Post type
		);
	}
}

add_action( 'add_meta_boxes', 'wporg_add_custom_box' );

function wporg_custom_box_html( $post ) {
	?>
	<label for="wporg_field">Description for this field</label>
	<select name="wporg_field" id="wporg_field" class="postbox">
		<option value="">Select something...</option>
		<option value="something">Something</option>
		<option value="else">Else</option>
	</select>
	<?php
}
*/