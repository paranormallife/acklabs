<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php
// ─── Build OG / Meta values ───────────────────────────────────────────────

$site_name = get_bloginfo( 'name' );
$site_desc = get_bloginfo( 'description' );

// Description
if ( is_singular() ) {
	if ( has_excerpt() ) {
		$meta_desc = get_the_excerpt();
	} else {
		$meta_desc = wp_trim_words( wp_strip_all_tags( get_the_content() ), 30, '…' );
	}
} elseif ( is_tax() || is_category() || is_tag() ) {
	$meta_desc = wp_strip_all_tags( term_description() ) ?: $site_desc;
} else {
	$meta_desc = $site_desc;
}
$meta_desc = esc_attr( wp_strip_all_tags( $meta_desc ) );

// OG Title
if ( is_front_page() ) {
	$og_title = $site_name . ( $site_desc ? ' — ' . $site_desc : '' );
} elseif ( is_singular() ) {
	$og_title = $site_name . ' | ' . get_the_title();
} elseif ( is_archive() ) {
	$og_title = $site_name . ' | ' . get_the_archive_title();
} else {
	$og_title = $site_name;
}

// OG URL
if ( is_singular() ) {
	$og_url = get_permalink();
} elseif ( is_home() ) {
	$og_url = get_post_type_archive_link( 'post' ) ?: home_url( '/' );
} elseif ( is_front_page() ) {
	$og_url = home_url( '/' );
} elseif ( is_tax() || is_category() || is_tag() ) {
	$term_link = get_term_link( get_queried_object() );
	$og_url    = is_wp_error( $term_link ) ? home_url( '/' ) : $term_link;
} else {
	$og_url = home_url( '/' );
}

// OG Image — featured image → customizer default → site icon
$og_image = '';
if ( is_singular() && has_post_thumbnail() ) {
	$og_image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
}
if ( ! $og_image ) {
	$default_img_id = get_theme_mod( 'default_seo_image' );
	if ( $default_img_id ) {
		$og_image = wp_get_attachment_image_url( (int) $default_img_id, 'large' );
	}
}
if ( ! $og_image ) {
	$icon_id = get_option( 'site_icon' );
	if ( $icon_id ) {
		$og_image = wp_get_attachment_image_url( (int) $icon_id, 'full' );
	}
}
?>

<?php if ( $meta_desc ) : ?>
<meta name="description" content="<?php echo $meta_desc; ?>">
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:site_name"   content="<?php echo esc_attr( $site_name ); ?>">
<meta property="og:title"       content="<?php echo esc_attr( $og_title ); ?>">
<?php if ( $meta_desc ) : ?>
<meta property="og:description" content="<?php echo $meta_desc; ?>">
<?php endif; ?>
<meta property="og:url"         content="<?php echo esc_url( $og_url ); ?>">
<meta property="og:type"        content="<?php echo is_singular( 'post' ) ? 'article' : 'website'; ?>">
<?php if ( $og_image ) : ?>
<meta property="og:image"       content="<?php echo esc_url( $og_image ); ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo esc_attr( $og_title ); ?>">
<?php if ( $meta_desc ) : ?>
<meta name="twitter:description" content="<?php echo $meta_desc; ?>">
<?php endif; ?>
<?php if ( $og_image ) : ?>
<meta name="twitter:image"       content="<?php echo esc_url( $og_image ); ?>">
<?php endif; ?>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav id="site-nav" class="site-nav" role="navigation" aria-label="<?php esc_attr_e( 'Main navigation', 'acklabs' ); ?>">

	<a class="nav-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img
			src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/Ack_Labs_Beaker_Transparent.png' ); ?>"
			alt="<?php echo esc_attr( $site_name ); ?>"
			width="26"
			height="26"
		>
		<span class="nav-logo-text"><?php bloginfo( 'name' ); ?></span>
	</a>

	<button
		class="nav-toggle"
		aria-controls="main-menu"
		aria-expanded="false"
		aria-label="<?php esc_attr_e( 'Toggle navigation', 'acklabs' ); ?>"
	>
		<span class="hamburger-bar"></span>
		<span class="hamburger-bar"></span>
		<span class="hamburger-bar"></span>
	</button>

	<?php
	wp_nav_menu( [
		'theme_location' => 'main_menu',
		'menu_id'        => 'main-menu',
		'container'      => false,
		'menu_class'     => 'nav-links',
		'fallback_cb'    => false,
		'depth'          => 2,
	] );
	?>

</nav>
