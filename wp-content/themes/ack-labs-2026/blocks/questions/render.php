<?php
/**
 * acklabs/questions — PHP render callback
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML.
 */

$items = isset( $attributes['items'] ) ? (array) $attributes['items'] : [];
if ( empty( $items ) ) return '';

$chevron = '<svg class="question-chevron" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M5 8l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';

ob_start();
?>
<div class="questions">
	<?php foreach ( $items as $item ) :
		$dim_label    = isset( $item['dimLabel'] )    ? sanitize_text_field( $item['dimLabel'] )    : '';
		$question     = isset( $item['questionText'] ) ? sanitize_text_field( $item['questionText'] ) : '';
		$answer       = isset( $item['answer'] )      ? wp_kses_post( $item['answer'] )             : '';
	?>
	<div class="question">
		<div class="question-trigger">
			<div class="question-left">
				<?php if ( $dim_label ) : ?>
					<span class="dim-label"><?php echo esc_html( $dim_label ); ?></span>
				<?php endif; ?>
				<div class="question-text"><?php echo esc_html( $question ); ?></div>
			</div>
			<?php echo $chevron; ?>
		</div>
		<div class="question-body">
			<div class="question-expand"><?php echo $answer; ?></div>
		</div>
	</div>
	<?php endforeach; ?>
</div>
<?php
return ob_get_clean();
