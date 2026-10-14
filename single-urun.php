<?php get_header(); ?>
<main id="primary" class="site-main" role="main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<h1><?php the_title(); ?></h1>
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'large', array( 'class' => 'product-image' ) );
			}
			?>
			<div><?php the_content(); ?></div>
			<p><?php esc_html_e( 'Fiyat:', 'superpro' ); ?> <?php echo esc_html( get_post_meta( get_the_ID(), '_urun_fiyat', true ) ); ?></p>
			<p><?php esc_html_e( 'Stok Kodu:', 'superpro' ); ?> <?php echo esc_html( get_post_meta( get_the_ID(), '_urun_kod', true ) ); ?></p>
			<form method="post" action="<?php echo esc_url( get_permalink() ); ?>">
				<?php wp_nonce_field( 'superpro_cart_action', 'superpro_nonce' ); ?>
				<input type="hidden" name="superpro_action" value="sepete_ekle">
				<input type="hidden" name="product_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
				<label for="product-quantity"><?php esc_html_e( 'Adet', 'superpro' ); ?></label>
				<input id="product-quantity" name="quantity" type="number" min="1" max="99" value="1">
				<button type="submit"><?php esc_html_e( 'Sepete Ekle', 'superpro' ); ?></button>
			</form>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
