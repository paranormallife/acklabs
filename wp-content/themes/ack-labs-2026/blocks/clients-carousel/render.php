<?php
/**
 * acklabs/clients-carousel — PHP render callback
 * Duplicates items in the DOM to create a seamless infinite-scroll animation.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML.
 */

$items = isset( $attributes['items'] ) ? (array) $attributes['items'] : [];
$label = isset( $attributes['label'] ) ? sanitize_text_field( $attributes['label'] ) : '';

if ( empty( $items ) ) return '';

// Duplicate items for seamless loop (CSS animation runs -50% translateX)
$loop_items = array_merge( $items, $items );

ob_start();
?>
<div class="clients-section">

	<?php if ( $label ) : ?>
		<p class="clients-label"><?php echo esc_html( $label ); ?></p>
	<?php endif; ?>

	<div class="carousel-track-wrap">
		<div class="carousel-track">
			<?php foreach ( $loop_items as $item ) :
				$img_url = isset( $item['imageUrl'] ) ? esc_url( $item['imageUrl'] ) : '';
				$alt     = isset( $item['altText'] )  ? sanitize_text_field( $item['altText'] ) : '';
				$link    = isset( $item['linkUrl'] )  ? esc_url( $item['linkUrl'] )  : '';

				if ( ! $img_url ) continue;
			?>
				<?php if ( $link ) : ?>
					<a href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer">
						<img class="carousel-logo" src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $alt ); ?>">
					</a>
				<?php else : ?>
					<img class="carousel-logo" src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $alt ); ?>">
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>

</div>
<?php
echo ob_get_clean();
