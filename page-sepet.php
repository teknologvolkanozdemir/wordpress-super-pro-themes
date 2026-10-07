<?php get_header(); ?>
<main id="primary" class="site-main" role="main">
	<h1><?php esc_html_e( 'Sepet ve Sipariş', 'superpro' ); ?></h1>
	<?php
	$cart = superpro_cart();
	if ( $cart ) :
		?>
		<table>
			<caption><?php esc_html_e( 'Sepetinizdeki ürünler', 'superpro' ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Ürün', 'superpro' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Fiyat', 'superpro' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Adet', 'superpro' ); ?></th>
					<th scope="col"><?php esc_html_e( 'İşlem', 'superpro' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $cart as $product_id => $quantity ) : ?>
					<?php if ( 'urun' !== get_post_type( $product_id ) ) { continue; } ?>
					<tr>
						<td><a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( get_the_title( $product_id ) ); ?></a></td>
						<td><?php echo esc_html( get_post_meta( $product_id, '_urun_fiyat', true ) ); ?></td>
						<td><?php echo esc_html( absint( $quantity ) ); ?></td>
						<td>
							<form method="post" action="<?php echo esc_url( superpro_cart_url() ); ?>">
								<?php wp_nonce_field( 'superpro_cart_action', 'superpro_nonce' ); ?>
								<input type="hidden" name="superpro_action" value="sepetten_cikar">
								<input type="hidden" name="product_id" value="<?php echo esc_attr( absint( $product_id ) ); ?>">
								<button type="submit"><?php esc_html_e( 'Ürünü Çıkar', 'superpro' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<form method="post" action="<?php echo esc_url( superpro_cart_url() ); ?>">
			<?php wp_nonce_field( 'superpro_cart_action', 'superpro_nonce' ); ?>
			<input type="hidden" name="superpro_action" value="sepeti_bosalt">
			<button type="submit"><?php esc_html_e( 'Sepeti Boşalt', 'superpro' ); ?></button>
		</form>

		<h2><?php esc_html_e( 'Sipariş Bilgileri', 'superpro' ); ?></h2>
		<form method="post" action="<?php echo esc_url( superpro_cart_url() ); ?>">
			<?php wp_nonce_field( 'superpro_cart_action', 'superpro_nonce' ); ?>
			<input type="hidden" name="superpro_action" value="siparis_gonder">
			<label for="customer-name"><?php esc_html_e( 'Ad Soyad', 'superpro' ); ?></label>
			<input id="customer-name" name="customer_name" type="text" autocomplete="name" required>
			<label for="customer-phone"><?php esc_html_e( 'Telefon', 'superpro' ); ?></label>
			<input id="customer-phone" name="customer_phone" type="tel" autocomplete="tel" required>
			<label for="customer-address"><?php esc_html_e( 'Adres', 'superpro' ); ?></label>
			<textarea id="customer-address" name="customer_address" autocomplete="street-address" required></textarea>
			<button type="submit"><?php esc_html_e( 'Siparişi Gönder', 'superpro' ); ?></button>
		</form>
	<?php else : ?>
		<p><?php esc_html_e( 'Sepetiniz boş.', 'superpro' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
