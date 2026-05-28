<?php
/**
 * acklabs/offerings-grid — PHP render callback
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML.
 */

$items = isset( $attributes['items'] ) ? (array) $attributes['items'] : [];
if ( empty( $items ) ) return '';

ob_start();
?>
<div class="offerings-section">
	<div class="offerings-grid">
		<?php foreach ( $items as $item ) :
			$step  = isset( $item['stepLabel'] )   ? sanitize_text_field( $item['stepLabel'] )   : '';
			$title = isset( $item['title'] )        ? sanitize_text_field( $item['title'] )        : '';
			$desc  = isset( $item['description'] )  ? sanitize_text_field( $item['description'] )  : '';
			$url   = isset( $item['url'] )          ? esc_url( $item['url'] )                     : '';
			$tag   = $url ? 'a' : 'div';
			$extra = $url ? ' href="' . $url . '"' : '';
		?>
		<<?php echo esc_attr( $tag ) . $extra; ?> class="offering-card">
			<?php if ( $step ) : ?>
				<div class="offering-step"><?php echo esc_html( $step ); ?></div>
			<?php endif; ?>
			<div class="offering-title"><?php echo esc_html( $title ); ?></div>
			<?php if ( $desc ) : ?>
				<div class="offering-desc"><?php echo esc_html( $desc ); ?></div>
			<?php endif; ?>
		</<?php echo esc_attr( $tag ); ?>>
		<?php endforeach; ?>
	</div>
</div>
<?php
echo ob_get_clean();
