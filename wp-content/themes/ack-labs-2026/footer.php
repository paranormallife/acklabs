<footer class="site-footer">
	<?php if ( is_active_sidebar( 'footer_content' ) ) : ?>

		<?php dynamic_sidebar( 'footer_content' ); ?>

	<?php else : ?>

		<div class="footer-logo">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/Ack_Labs_Beaker_Transparent.png' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				width="20"
				height="20"
			>
			<span class="footer-text"><?php bloginfo( 'name' ); ?></span>
		</div>

		<div class="footer-text">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.
		</div>

		<div class="footer-tagline">Organizational clarity starts here.</div>

	<?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>
</html>
