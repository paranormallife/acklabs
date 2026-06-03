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

		<!-- Page Hero -->
		<div class="page-hero animate-1">
			<?php if ( is_home() ) : ?>
				<?php
				$posts_page_id = (int) get_option( 'page_for_posts' );
				$page_title    = $posts_page_id ? get_the_title( $posts_page_id ) : __( 'Blog', 'acklabs' );
				$page_intro    = '';
				if ( $posts_page_id ) {
					$posts_page = get_post( $posts_page_id );
					$title	  = get_the_title( $posts_page_id );
					$content  = apply_filters( 'the_content', $posts_page->post_content );
					$page_intro = '<h1>' . wp_kses( $title, [ 'em' => [], 'strong' => [], 'br' => [] ] ) . '</h1>' . $content;
				}
				?>
				<p class="page-eyebrow"><?php esc_html_e( 'The Ack Labs Blog', 'acklabs' ); ?></p>
				<h1 class="page-title"><?php echo wp_kses( $page_title, [ 'em' => [], 'strong' => [], 'br' => [] ] ); ?></h1>
				<?php if ( $page_intro ) : ?>
					<div class="page-intro"><?php echo wp_kses( $page_intro, [ 'p' => [], 'em' => [], 'strong' => [], 'br' => [] ] ); ?></div>
				<?php endif; ?>
			<?php elseif ( is_category() ) : ?>
				<p class="page-eyebrow"><?php esc_html_e( 'The Ack Labs Blog', 'acklabs' ); ?></p>
				<h1 class="page-title"><?php single_cat_title(); ?></h1>
				<?php the_archive_description( '<p class="page-intro">', '</p>' ); ?>
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

		<!-- Category Filter Bar (blog index + category archives only) -->
		<?php if ( is_home() || is_category() ) : ?>
			<div class="category-bar animate-2">
				<?php
				$blog_url = get_permalink( get_option( 'page_for_posts' ) );
				$all_active = is_home() && ! is_category();
				?>
				<a href="<?php echo esc_url( $blog_url ); ?>"
				   class="cat-btn<?php echo $all_active ? ' active' : ''; ?>">
					<?php esc_html_e( 'All', 'acklabs' ); ?>
				</a>
				<?php
				$cats = get_categories( [
					'orderby'    => 'name',
					'order'      => 'ASC',
					'hide_empty' => true,
				] );
				foreach ( $cats as $cat ) :
					$cat_active = is_category( $cat->term_id );
				?>
					<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
					   class="cat-btn<?php echo $cat_active ? ' active' : ''; ?>">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Posts Loop -->
		<?php if ( have_posts() ) : ?>
			<div class="post-grid animate-3">
				<?php
				$post_index = 0;
				while ( have_posts() ) : the_post();
					$is_featured = ( $post_index === 0 && is_home() && ! is_paged() );
					$categories  = get_the_category();
					$card_class  = 'post-card' . ( $is_featured ? ' featured' : '' );
					$post_index++;
				?>
					<a class="<?php echo esc_attr( $card_class ); ?>" href="<?php the_permalink(); ?>"
					   style="animation-delay:<?php echo esc_attr( 0.1 + $post_index * 0.05 ); ?>s">
						<div>
							<?php if ( $is_featured ) : ?>
								<span class="featured-label"><?php esc_html_e( 'Featured Post', 'acklabs' ); ?></span>
							<?php endif; ?>
							<div class="post-meta">
								<?php if ( $categories ) : ?>
									<span class="post-cat"><?php echo esc_html( $categories[0]->name ); ?></span>
								<?php endif; ?>
								<span class="post-date"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></span>
							</div>
							<h2 class="post-title"><?php the_title(); ?></h2>
							<?php if ( has_excerpt() ) : ?>
								<p class="post-excerpt"><?php the_excerpt(); ?></p>
							<?php endif; ?>
							<span class="post-read">Read &rarr;</span>
						</div>
						<?php if ( $is_featured ) : ?>
							<div class="featured-image-placeholder" aria-hidden="true">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large' ); ?>
								<?php else : ?>
									<span>Aa</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</a>
				<?php endwhile; ?>
			</div>

			<!-- Numbered Pagination -->
			<?php
			global $wp_query;
			$pagination = paginate_links( [
				'base'      => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
				'format'    => '?paged=%#%',
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'total'     => $wp_query->max_num_pages,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'type'      => 'array',
			] );
			if ( $pagination ) :
			?>
				<div class="pagination animate-4">
					<?php foreach ( $pagination as $page_link ) : ?>
						<?php echo $page_link; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<p class="no-posts"><?php esc_html_e( 'No posts found.', 'acklabs' ); ?></p>
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
