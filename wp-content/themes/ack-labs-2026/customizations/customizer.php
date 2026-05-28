<?php
/**
 * Ack Labs 2026 — WP Customizer Settings
 *
 * @package acklabs
 */

function acklabs_customizer_register( WP_Customize_Manager $wp_customize ) {

	// ─── Ack Labs Panel ───────────────────────────────────────────────────
	$wp_customize->add_panel( 'acklabs_panel', [
		'title'    => __( 'Ack Labs', 'acklabs' ),
		'priority' => 30,
	] );

	// ─── SEO & Social Section ─────────────────────────────────────────────
	$wp_customize->add_section( 'acklabs_seo', [
		'title'    => __( 'SEO & Social', 'acklabs' ),
		'panel'    => 'acklabs_panel',
		'priority' => 10,
	] );

	// Default SEO / OG Image
	$wp_customize->add_setting( 'default_seo_image', [
		'default'           => '',
		'sanitize_callback' => 'absint',
	] );

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'default_seo_image',
			[
				'label'       => __( 'Default Open Graph Image', 'acklabs' ),
				'description' => __( 'Used as the social sharing image when a post or page has no featured image set. Recommended: 1200 × 630 px.', 'acklabs' ),
				'section'     => 'acklabs_seo',
				'mime_type'   => 'image',
			]
		)
	);

	// ─── Hero Section ─────────────────────────────────────────────────────
	$wp_customize->add_section( 'acklabs_hero', [
		'title'    => __( 'Homepage Hero', 'acklabs' ),
		'panel'    => 'acklabs_panel',
		'priority' => 20,
	] );

	// Hero eyebrow
	$wp_customize->add_setting( 'hero_eyebrow', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'hero_eyebrow', [
		'label'   => __( 'Eyebrow Label', 'acklabs' ),
		'section' => 'acklabs_hero',
		'type'    => 'text',
	] );

	// Hero headline (allows basic HTML like <em>)
	$wp_customize->add_setting( 'hero_headline', [
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'hero_headline', [
		'label'       => __( 'Headline', 'acklabs' ),
		'description' => __( 'Wrap italic text in &lt;em&gt; tags for amber styling.', 'acklabs' ),
		'section'     => 'acklabs_hero',
		'type'        => 'textarea',
	] );

	// Hero copy
	$wp_customize->add_setting( 'hero_copy', [
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'hero_copy', [
		'label'   => __( 'Body Copy', 'acklabs' ),
		'section' => 'acklabs_hero',
		'type'    => 'textarea',
	] );

	// Hero CTA text
	$wp_customize->add_setting( 'hero_cta_text', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'hero_cta_text', [
		'label'   => __( 'CTA Button Label', 'acklabs' ),
		'section' => 'acklabs_hero',
		'type'    => 'text',
	] );

	// Hero CTA URL
	$wp_customize->add_setting( 'hero_cta_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'hero_cta_url', [
		'label'   => __( 'CTA Button URL', 'acklabs' ),
		'section' => 'acklabs_hero',
		'type'    => 'url',
	] );
}
add_action( 'customize_register', 'acklabs_customizer_register' );
