<?php
/**
 * acklabs/stats-row — PHP render callback
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML.
 */

$items = isset( $attributes['items'] ) ? (array) $attributes['items'] : [];
if ( empty( $items ) ) return '';

ob_start();
?>
<div class="stats-row">
	<?php foreach ( $items as $item ) :
		$number = isset( $item['statNumber'] ) ? sanitize_text_field( $item['statNumber'] ) : '';
		$label  = isset( $item['statLabel'] )  ? sanitize_text_field( $item['statLabel'] )  : '';
	?>
	<div class="stat">
		<div class="stat-number"><?php echo esc_html( $number ); ?></div>
		<div class="stat-label"><?php echo esc_html( $label ); ?></div>
	</div>
	<?php endforeach; ?>
</div>
<?php
echo ob_get_clean();
