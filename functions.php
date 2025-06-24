<?php

/* LOAD BOILERPLATE ASSETS */

function boilerplate_load_assets() {
	wp_enqueue_script('ourmainjs', get_theme_file_uri('/build/index.js'), ['wp-element', 'react-jsx-runtime'], '1.0', true);
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
			[
				'header-menu' => __( 'Desktop - Header Menu' ),
				'sidebar-menu' => __( 'Mobile - Sidebar Menu' ),
				'footer-candidates' => __( 'Footer - Candidates Menu' ),
				'footer-clients' => __( 'Footer - Clients Menu' ),
				'footer-resources' => __( 'Footer - Resources Menu' ),
				'footer-company' => __( 'Footer - Company Menu' ),
				'footer-legal' => __( 'Footer - Legal Menu' )
			]
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



/**
 * Prioritize a page over an archive when they share the same URL slug.
 */
function prioritize_page_over_archive() {
		// Only run on the front-end and when the query is for an archive
		if ( is_admin() || ! is_archive() ) {
				return;
		}

		// Get the current URL path
		$request_uri = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

		// Check if a page exists with the same slug
		$page = get_page_by_path( $request_uri );

		if ( $page ) {
				// If a page exists, modify the query to load the page
				global $wp_query;
				$wp_query = new WP_Query( [
						'page_id' => $page->ID,
				] );

				// Ensure WordPress treats this as a page
				$wp_query->is_archive = false;
				$wp_query->is_page = true;
				$wp_query->is_singular = true;
		}
}
add_action( 'template_redirect', 'prioritize_page_over_archive' );



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



/* CLEAN PAGINATION LINKS */

function origin_clean_pagination_links($link) {
	$link = str_replace('/page/1/', '/', $link);
	return $link;
}
add_filter('paginate_links', 'origin_clean_pagination_links');



/* MODIFY BREADCRUMBS FOR CUSTOM TAXONOMIES */

add_filter( 'rank_math/frontend/breadcrumb/items', function( $crumbs, $class ) {
	// Get all registered taxonomies
	$taxonomies = get_taxonomies( [ '_builtin' => false ], 'objects' );

	// Check if the current page is a taxonomy term page
	foreach ( $taxonomies as $taxonomy ) {
		if ( is_tax( $taxonomy->name ) ) {
			// Find the taxonomy item in the crumbs (usually index 1)
			if ( isset( $crumbs[1] ) ) {
				// Verify the breadcrumb item matches the taxonomy label
				if ( $crumbs[1][0] === $taxonomy->labels->name ) {
					// Get the taxonomy archive link
					$taxonomy_link = get_taxonomy_link( $taxonomy->name );
					if ( $taxonomy_link ) {
						// Add the link to the taxonomy breadcrumb item
						$crumbs[1][1] = $taxonomy_link;
					}
				}
			}
			break; // Exit loop once the matching taxonomy is found
		}
	}
	return $crumbs;
}, 10, 2 );

// Helper function to get taxonomy archive link
function get_taxonomy_link( $taxonomy ) {
	$tax = get_taxonomy( $taxonomy );
	if ( $tax && ! empty( $tax->rewrite['slug'] ) ) {
		return home_url( '/' . $tax->rewrite['slug'] . '/' );
	}
	return false;
}
