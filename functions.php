<?php

function superpro_theme_setup() {
	load_theme_textdomain( 'superpro', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array( 'primary' => __( 'Primary Menu', 'superpro' ) ) );
}
add_action( 'after_setup_theme', 'superpro_theme_setup' );

function superpro_register_product_type() {
	register_post_type(
		'urun',
		array(
			'labels'       => array(
				'name'          => __( 'Ürünler', 'superpro' ),
				'singular_name' => __( 'Ürün', 'superpro' ),
				'add_new_item'  => __( 'Yeni ürün ekle', 'superpro' ),
				'edit_item'     => __( 'Ürünü düzenle', 'superpro' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'urun' ),
			'menu_icon'    => 'dashicons-cart',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => false,
		)
	);
}
add_action( 'init', 'superpro_register_product_type' );

function superpro_register_shop_sidebar() {
	register_sidebar(
		array(
			'name'          => __( 'Mağaza Kenar Çubuğu', 'superpro' ),
			'id'            => 'shop-sidebar',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'superpro_register_shop_sidebar' );

function superpro_add_product_meta_box() {
	add_meta_box( 'superpro-product-details', __( 'Ürün Bilgileri', 'superpro' ), 'superpro_product_meta_box', 'urun', 'normal', 'default' );
}
add_action( 'add_meta_boxes_urun', 'superpro_add_product_meta_box' );

function superpro_product_meta_box( $post ) {
	wp_nonce_field( 'superpro_save_product', 'superpro_product_nonce' );
	$price = get_post_meta( $post->ID, '_urun_fiyat', true );
	$code  = get_post_meta( $post->ID, '_urun_kod', true );
	?>
	<p>
		<label for="urun-fiyat"><?php esc_html_e( 'Fiyat', 'superpro' ); ?></label>
		<input id="urun-fiyat" name="_urun_fiyat" type="number" min="0" step="0.01" value="<?php echo esc_attr( $price ); ?>">
	</p>
	<p>
		<label for="urun-kod"><?php esc_html_e( 'Stok Kodu', 'superpro' ); ?></label>
		<input id="urun-kod" name="_urun_kod" type="text" value="<?php echo esc_attr( $code ); ?>">
	</p>
	<?php
}

function superpro_save_product_meta( $post_id ) {
	if ( ! isset( $_POST['superpro_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['superpro_product_nonce'] ) ), 'superpro_save_product' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['_urun_fiyat'] ) ) {
		$price = trim( sanitize_text_field( wp_unslash( $_POST['_urun_fiyat'] ) ) );
		if ( preg_match( '/^\d+(?:[.,]\d{1,2})?$/', $price ) ) {
			update_post_meta( $post_id, '_urun_fiyat', str_replace( ',', '.', $price ) );
		} else {
			delete_post_meta( $post_id, '_urun_fiyat' );
		}
	}
	if ( isset( $_POST['_urun_kod'] ) ) {
		update_post_meta( $post_id, '_urun_kod', sanitize_text_field( wp_unslash( $_POST['_urun_kod'] ) ) );
	}
}
add_action( 'save_post_urun', 'superpro_save_product_meta' );

function superpro_start_session() {
	if ( ! is_admin() && session_status() === PHP_SESSION_NONE && ! headers_sent() ) {
		ini_set( 'session.use_strict_mode', '1' );
		ini_set( 'session.cookie_httponly', '1' );
		ini_set( 'session.cookie_secure', is_ssl() ? '1' : '0' );
		session_start();
	}
}
add_action( 'init', 'superpro_start_session', 1 );

function superpro_cart() {
	if ( ! isset( $_SESSION['superpro_cart'] ) || ! is_array( $_SESSION['superpro_cart'] ) ) {
		$_SESSION['superpro_cart'] = array();
	}
	return $_SESSION['superpro_cart'];
}

function superpro_cart_url() {
	$page = get_page_by_path( 'sepet' );
	return $page ? get_permalink( $page ) : home_url( '/sepet/' );
}

function superpro_redirect_with_status( $status, $destination = '' ) {
	$referer = $destination ? $destination : wp_get_referer();
	$referer = wp_validate_redirect( $referer, superpro_cart_url() );
	wp_safe_redirect( add_query_arg( 'cart_status', sanitize_key( $status ), remove_query_arg( 'cart_status', $referer ) ) );
	exit;
}

function superpro_process_cart_request() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['superpro_action'] ) ) {
		return;
	}
	if ( ! isset( $_POST['superpro_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['superpro_nonce'] ) ), 'superpro_cart_action' ) ) {
		superpro_redirect_with_status( 'invalid' );
	}

	$action = sanitize_key( wp_unslash( $_POST['superpro_action'] ) );
	$cart   = superpro_cart();
	if ( 'sepete_ekle' === $action ) {
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
		if ( 'urun' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) || $quantity < 1 || $quantity > 99 ) {
			superpro_redirect_with_status( 'invalid' );
		}
		$cart[ $product_id ] = min( 99, ( $cart[ $product_id ] ?? 0 ) + $quantity );
		$_SESSION['superpro_cart'] = $cart;
		superpro_redirect_with_status( 'added' );
	}
	if ( 'sepetten_cikar' === $action ) {
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		unset( $cart[ $product_id ] );
		$_SESSION['superpro_cart'] = $cart;
		superpro_redirect_with_status( 'removed' );
	}
	if ( 'sepeti_bosalt' === $action ) {
		$_SESSION['superpro_cart'] = array();
		superpro_redirect_with_status( 'cleared' );
	}
	if ( 'siparis_gonder' === $action ) {
		$name    = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
		$phone   = isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '';
		$address = isset( $_POST['customer_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_address'] ) ) : '';
		if ( ! $cart || '' === $name || '' === $phone || '' === $address ) {
			superpro_redirect_with_status( 'invalid', superpro_cart_url() );
		}

		$lines = array(
			'Ad Soyad: ' . $name,
			'Telefon: ' . $phone,
			'Adres: ' . $address,
			'',
			'Ürünler:',
		);
		foreach ( $cart as $product_id => $quantity ) {
			$product = get_post( absint( $product_id ) );
			if ( ! $product || 'urun' !== $product->post_type ) {
				continue;
			}
			$price   = get_post_meta( $product->ID, '_urun_fiyat', true );
			$lines[] = get_the_title( $product ) . ' × ' . absint( $quantity ) . ' — ' . $price;
		}
		$sent = wp_mail( get_option( 'admin_email' ), __( 'Yeni mağaza siparişi', 'superpro' ), implode( "\n", $lines ) );
		if ( $sent ) {
			$_SESSION['superpro_cart'] = array();
			superpro_redirect_with_status( 'order_sent', superpro_cart_url() );
		}
		superpro_redirect_with_status( 'order_failed', superpro_cart_url() );
	}
}
add_action( 'init', 'superpro_process_cart_request', 2 );

function superpro_cart_notice() {
	$messages = array(
		'added'       => __( 'Ürün sepete eklendi.', 'superpro' ),
		'removed'     => __( 'Ürün sepetten çıkarıldı.', 'superpro' ),
		'cleared'     => __( 'Sepet boşaltıldı.', 'superpro' ),
		'order_sent'  => __( 'Siparişiniz iletildi.', 'superpro' ),
		'order_failed'=> __( 'Sipariş e-postası gönderilemedi. Lütfen tekrar deneyin.', 'superpro' ),
		'invalid'     => __( 'İşlem tamamlanamadı. Lütfen bilgilerinizi kontrol edin ve tekrar deneyin.', 'superpro' ),
	);
	$status = isset( $_GET['cart_status'] ) ? sanitize_key( wp_unslash( $_GET['cart_status'] ) ) : '';
	if ( isset( $messages[ $status ] ) ) {
		printf( '<p class="cart-notice" role="status" aria-live="polite">%s</p>', esc_html( $messages[ $status ] ) );
	}
}
