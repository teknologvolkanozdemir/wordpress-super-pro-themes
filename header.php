<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'İçeriğe Atla', 'superpro' ); ?></a>
<header class="site-header" role="banner">
	<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a></p>
	<nav class="primary-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Ana Menü', 'superpro' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
			)
		);
		?>
		<a href="<?php echo esc_url( superpro_cart_url() ); ?>"><?php esc_html_e( 'Sepet', 'superpro' ); ?></a>
	</nav>
	<?php superpro_cart_notice(); ?>
</header>
