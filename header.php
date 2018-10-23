<!DOCTYPE html>
<html lang="en-US" class="no-js">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
		<!--[if lt IE 9]>
			<script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
			<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body <?php body_class(); ?>>
		<?php 
			$phone_number;
			if( have_rows('contact_information', 'option') ) {
				the_row();
				$phone_number = get_sub_field('phone_number', 'option');
				$phone_clean = preg_replace('/\D+/', '', $phone_number);
				reset_rows();
			}				
			
			// Add phone to nav menu for scrolled state
			function nav_wrap($phone, $phone_clean, $location) {
				// Get cart url (quote page)
				global $woocommerce;
				$cart_url = $woocommerce->cart->get_cart_url();
				$cart_page_id = get_option( 'woocommerce_cart_page_id' );
				$cart_page_name = get_the_title($cart_page_id);

				$wrap = '<ul class="menu">';
				if( $location == 'after' ) {
					$wrap .= '%3$s';
				}
				$wrap .= '<li class="menu-item search search-trigger">';
				$wrap .= '<i class="fa fa-search"></i>';
				$wrap .= '</li>';
				$wrap .= '<li class="menu-item tel">';
				$wrap .= '<a href="tel:' . $phone_clean . '">';
				$wrap .= '<i class="fa fa-phone"></i>';
				$wrap .= '<span>' . $phone . '</span>';
				$wrap .= '</a>';
				$wrap .= '</li>';
				$wrap .= '<li class="menu-item quote">';
				$wrap .= '<a href="' . $cart_url . '" data-qty="' . $woocommerce->cart->get_cart_contents_count() . '" class="quote-count">';
				$wrap .= '<i class="fa fa-list-ul"></i>';
				$wrap .= '<span>' . $cart_page_name . '</span>';
				$wrap .= '</a>';
				$wrap .= '</li>';
				if ( $location == 'before' ) {
					$wrap .= '%3$s';
				}
				$wrap .= '</ul>';
				return $wrap;
			}

		?>
		<?php if( 'ocn' == get_field( 'menu_type', 'option' ) ): ?>
			<div id="ocn">
				<div id="ocn-inner">
					<div id="ocn-top">
						<a href="<?php echo home_url(); ?>" title="<?php bloginfo( 'name' ); ?>" id="ocn-brand">
							<?php if( the_field( 'site_logo', 'option' ) ) : ?>
								<img src="<?php the_field( 'site_logo', 'option' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
							<?php else : ?>
								<img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/assets/logo.svg" alt="<?php bloginfo( 'name' ); ?>">
							<?php endif; ?>
						</a>
						<button class="nav-toggle" type="button" id="ocn-close">
							<span></span>
						</button>
					</div>
					<?php wp_nav_menu( array(
						'container' => 'nav',
						'container_id' => 'ocn-nav-primary',
						'theme_location' => 'primary',
						'items_wrap' => nav_wrap($phone_number, $phone_clean, 'after'),
						'before' => '<span class="ocn-link-wrap">',
						'after' => '<span class="ocn-sub-menu-button"></span></span>'
					) ); ?>
					<div class="ocn-search">
						<?php get_search_form(); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php  
			global $woocommerce;
			$count = $woocommerce->cart->get_cart_contents_count();
			$class = '';
			if( is_front_page() ) {
				$class = 'home ';
			};
			if( $count > 0 ) {
				$class .= ' quote';
			};
		?>
		<header class="site-header <?php echo $class; ?>">
			<div class="inner">
				<div class="left">
					<a href="<?php echo home_url(); ?>" title="<?php bloginfo( 'name' ); ?>" class="brand scrolled">
						<?php if( the_field( 'site_logo', 'option' ) ) : // If custom logo exists ?>
							<img src="<?php the_field( 'site_logo', 'option' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
						<?php else : // Otherwise use SVG files in /assets directory ?>
							<img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/assets/logo-text.svg" alt="<?php bloginfo( 'name' ); ?>">
						<?php endif; ?>
					</a>
					<a href="<?php echo home_url(); ?>" title="<?php bloginfo( 'name' ); ?>" class="brand top">
						<?php if( the_field( 'site_logo', 'option' ) ) : // If custom logo exists ?>
							<img src="<?php the_field( 'site_logo', 'option' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
						<?php else : // Otherwise use SVG files in /assets directory ?>
							<img src="<?php echo get_bloginfo('stylesheet_directory'); ?>/assets/logo.svg" alt="<?php bloginfo( 'name' ); ?>">
						<?php endif; ?>
					</a>
				</div>
				<div class="right">
					<div class="upper">
						<a href="#" class="search search-trigger">
							<i class="fa fa-search"></i>
							Search
						</a>
						<a class="tel full" href="tel:<?php echo $phone_clean; ?>">
							<i class="fa fa-phone"></i>
							<?php echo $phone_number; ?>
						</a>
						<?php 
						// Get cart url (quote page)
						global $woocommerce;
						$cart_url = $woocommerce->cart->get_cart_url();
						?>
							<a href="<?php echo $cart_url; ?>" class="quote quote-count" data-qty="<?php echo $woocommerce->cart->get_cart_contents_count()  ?>">
								<i class="fa fa-list-ul"></i>
							</a>
					</div>
					<?php
					// Nav menu with added wrap
					wp_nav_menu( array(
						'menu' => 'Homepage',
						'container' => 'nav',
						'container_id' => 'large-nav-primary',
						'items_wrap' => nav_wrap($phone_number, $phone_clean, 'after'),
						'theme_location' => 'primary',
					) );
					?>
					<button class="nav-toggle" type="button" id="nav-toggle">
						Menu
					</button>
				</div>
			</div>
			<div class="search-container">
				<?php get_search_form(); ?>
			</div>
			<?php
				if( 'dropdown' == get_field( 'menu_type', 'option' ) ){
					wp_nav_menu( array(
						'menu' => 'Homepage',
						'container' => 'nav',
						'container_id' => 'dropdown-nav-primary',
						'items_wrap' => nav_wrap($phone_number, $phone_clean, 'after'),
						'theme_location' => 'primary'
					) );
				}
			?>
		</header>
		<?php
			if( function_exists( 'yoast_breadcrumb' ) && !is_front_page() ){
				yoast_breadcrumb( '<div id="breadcrumbs">', '</div>' );
			}
		?>