<?php
/**
 * Ack Labs 2026 — Theme Functions
 *
 * @package acklabs
 * @author  Elliot Toman
 */

// ─────────────────────────────────────────────────────────────────────────────
// Theme Setup
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_setup() {
	load_theme_textdomain( 'acklabs', get_template_directory() . '/languages' );

	// WordPress manages <title> tag via wp_head()
	add_theme_support( 'title-tag' );

	// Featured images (used for OG image)
	add_theme_support( 'post-thumbnails' );

	// Site icon / favicon
	add_theme_support( 'site-icon' );

	// HTML5 markup
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );

	// Editor styles
	add_editor_style( 'admin.css' );

	// Navigation menus
	register_nav_menus( [
		'main_menu' => __( 'Main Menu', 'acklabs' ),
	] );
}
add_action( 'after_setup_theme', 'acklabs_setup' );


// ─────────────────────────────────────────────────────────────────────────────
// Document Title — "Site Name | Page Title" / "Site Name — Description"
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_document_title( $parts ) {
	$site_name = get_bloginfo( 'name' );
	$site_desc = get_bloginfo( 'description' );
	$page_title = isset( $parts['title'] ) ? $parts['title'] : '';

	if ( is_front_page() ) {
		return [ 'title' => $site_name . ( $site_desc ? ' — ' . $site_desc : '' ) ];
	}

	if ( $page_title ) {
		return [ 'title' => $site_name . ' | ' . $page_title ];
	}

	return [ 'title' => $site_name ];
}
add_filter( 'document_title_parts', 'acklabs_document_title' );


// ─────────────────────────────────────────────────────────────────────────────
// Enqueue Frontend Assets
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_enqueue_assets() {
	// Random hash on stylesheet to bypass caching during development
	$cache_buster = substr( md5( uniqid( (string) rand(), true ) ), 0, 8 );

	wp_enqueue_style(
		'acklabs-style',
		get_stylesheet_uri(),
		[],
		$cache_buster
	);

	wp_enqueue_script(
		'acklabs-navigation',
		get_template_directory_uri() . '/assets/scripts/navigation.js',
		[],
		'1.0.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'acklabs_enqueue_assets' );


// ─────────────────────────────────────────────────────────────────────────────
// Admin Stylesheet
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_enqueue_admin_styles() {
	wp_enqueue_style(
		'acklabs-admin',
		get_template_directory_uri() . '/admin.css',
		[],
		'1.0.0'
	);
}
add_action( 'admin_enqueue_scripts', 'acklabs_enqueue_admin_styles' );


// ─────────────────────────────────────────────────────────────────────────────
// Body Class — add 'asw' for style overrides
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_body_classes( $classes ) {
	$classes[] = 'asw';
	return $classes;
}
add_filter( 'body_class', 'acklabs_body_classes' );


// ─────────────────────────────────────────────────────────────────────────────
// Widgets / Sidebars
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_widgets_init() {
	register_sidebar( [
		'name'          => __( 'Footer Content', 'acklabs' ),
		'id'            => 'footer_content',
		'description'   => __( 'Widgets placed here replace the default footer content.', 'acklabs' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	] );
}
add_action( 'widgets_init', 'acklabs_widgets_init' );


// ─────────────────────────────────────────────────────────────────────────────
// Shortcode — [year]
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_year_shortcode() {
	return esc_html( gmdate( 'Y' ) );
}
add_shortcode( 'year', 'acklabs_year_shortcode' );


// ─────────────────────────────────────────────────────────────────────────────
// Disable All Comments
// ─────────────────────────────────────────────────────────────────────────────

// Remove comment support from all post types
function acklabs_disable_comments_support() {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}
add_action( 'admin_init', 'acklabs_disable_comments_support' );

// Return false for comments_open / pings_open
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open',    '__return_false', 20 );

// Hide existing comments
add_filter( 'comments_array', '__return_empty_array', 10 );

// Remove Comments from admin menu
function acklabs_disable_comments_admin_menu() {
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'acklabs_disable_comments_admin_menu' );

// Redirect direct access to edit-comments.php
function acklabs_disable_comments_redirect() {
	global $pagenow;
	if ( $pagenow === 'edit-comments.php' ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'acklabs_disable_comments_redirect' );

// Remove comments dashboard widget
function acklabs_disable_comments_dashboard() {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}
add_action( 'admin_init', 'acklabs_disable_comments_dashboard' );

// Remove comments from admin bar
function acklabs_disable_comments_admin_bar() {
	if ( is_admin_bar_showing() ) {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
	}
}
add_action( 'init', 'acklabs_disable_comments_admin_bar' );


// ─────────────────────────────────────────────────────────────────────────────
// Customizer
// ─────────────────────────────────────────────────────────────────────────────

require get_template_directory() . '/customizations/customizer.php';


// ─────────────────────────────────────────────────────────────────────────────
// Custom Blocks
// ─────────────────────────────────────────────────────────────────────────────

function acklabs_register_blocks() {
	$blocks = [ 'questions', 'offerings-grid', 'clients-carousel', 'stats-row' ];
	foreach ( $blocks as $block ) {
		$block_path = get_template_directory() . '/blocks/' . $block;
		if ( file_exists( $block_path . '/block.json' ) ) {
			register_block_type( $block_path );
		}
	}
}
add_action( 'init', 'acklabs_register_blocks' );
