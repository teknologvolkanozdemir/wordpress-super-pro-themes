<?php get_header(); ?>
<main id="primary" class="site-main" role="main">
	<h1><?php esc_html_e( 'Ürünler', 'superpro' ); ?></h1>
	<?php
	$products = new WP_Query(
		array(
			'post_type'      => 'urun',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
		)
	);
	if ( $products->have_posts() ) :
		?>
		<ul class="product-list">
			<?php
			while ( $products->have_posts() ) :
				$products->the_post();
				?>
				<li>
					<a href="<?php the_permalink(); ?>">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'medium' );
						}
						?>
						<h2><?php the_title(); ?></h2>
					</a>
					<p><?php echo esc_html( get_post_meta( get_the_ID(), '_urun_fiyat', true ) ); ?></p>
				</li>
				<?php
			endwhile;
			?>
		</ul>
		<?php
		wp_reset_postdata();
	else :
		?>
		<p><?php esc_html_e( 'Henüz ürün eklenmedi.', 'superpro' ); ?></p>
	<?php endif; ?>
</main>
<?php if ( is_active_sidebar( 'shop-sidebar' ) ) : ?>
	<aside class="shop-sidebar" aria-label="<?php esc_attr_e( 'Mağaza Kenar Çubuğu', 'superpro' ); ?>">
		<?php dynamic_sidebar( 'shop-sidebar' ); ?>
	</aside>
<?php endif; ?>
<?php get_footer(); ?>
