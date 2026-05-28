<?php get_header(); ?>

<?php if ( is_404() ) : ?>

	<div class="not-found-page">
		<div class="not-found-code">404</div>
		<h1><?php esc_html_e( 'Page not found', 'acklabs' ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for doesn\'t exist or has been moved.', 'acklabs' ); ?></p>
		<br>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn"><?php esc_html_e( 'Back to Home', 'acklabs' ); ?></a>
	</div>

<?php elseif ( is_singular() ) : ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<div class="page">
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endwhile; ?>

<?php elseif ( is_home() || is_archive() || is_search() ) : ?>

	<div class="page">
		<div class="page-hero animate-1">
			<?php if ( is_home() ) : ?>
				<p class="page-eyebrow"><?php esc_html_e( 'Latest', 'acklabs' ); ?></p>
				<h1 class="page-title"><?php esc_html_e( 'Insights &amp; Updates', 'acklabs' ); ?></h1>
			<?php elseif ( is_archive() ) : ?>
				<p class="page-eyebrow"><?php esc_html_e( 'Archive', 'acklabs' ); ?></p>
				<h1 class="page-title"><?php the_archive_title(); ?></h1>
				<?php the_archive_description( '<p class="page-intro">', '</p>' ); ?>
			<?php elseif ( is_search() ) : ?>
				<p class="page-eyebrow"><?php esc_html_e( 'Search', 'acklabs' ); ?></p>
				<h1 class="page-title">
					<?php
					printf(
						/* translators: %s: search query */
						esc_html__( 'Results for: %s', 'acklabs' ),
						'<em>' . esc_html( get_search_query() ) . '</em>'
					);
					?>
				</h1>
			<?php endif; ?>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid animate-2">
				<?php while ( have_posts() ) : the_post(); ?>
					<a class="post-card" href="<?php the_permalink(); ?>">
						<div class="post-meta">
							<?php
							$categories = get_the_category();
							if ( $categories ) :
							?>
								<span class="post-cat"><?php echo esc_html( $categories[0]->name ); ?></span>
							<?php endif; ?>
							<span class="post-date"><?php echo esc_html( get_the_date() ); ?></span>
						</div>
						<h2 class="post-title"><?php the_title(); ?></h2>
						<?php if ( has_excerpt() ) : ?>
							<p class="post-excerpt"><?php the_excerpt(); ?></p>
						<?php endif; ?>
					</a>
				<?php endwhile; ?>
			</div>

			<?php the_posts_navigation(); ?>

		<?php else : ?>
			<p><?php esc_html_e( 'No posts found.', 'acklabs' ); ?></p>
		<?php endif; ?>
	</div>

<?php else : ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<div class="page">
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endwhile; ?>

<?php endif; ?>

<?php get_footer(); ?>
