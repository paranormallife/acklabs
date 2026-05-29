<?php
/**
 * acklabs/eyebrow — PHP render callback
 *
 * @var array $attributes Block attributes.
 */

$text = isset( $attributes['text'] ) ? sanitize_text_field( $attributes['text'] ) : '';

if ( $text ) {
	echo '<div class="eyebrow">' . esc_html( $text ) . '</div>';
}
