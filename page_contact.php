<?php
/*
 * Template Name: Contact
 */
get_header(); ?>
<?php add_filter( 'gform_confirmation_anchor_3', '__return_false' ); ?>
<div class="container">
	<?php while( have_posts() ): the_post(); ?>
		<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
			<div class="left">
				<div class="form">
					<h1><?php the_title(); ?></h1>
					<?php 
					// ACF fields
					$text = get_field('text');
					$form_title = get_field('form_title');
					if( $text ) {
						echo $text;
					}
					if( $form_title ) {
						echo '<h2>' . $form_title . '</h2>';
					}

					// Quote stuff
					if( is_page( 'quote' ) ) :
						global $woocommerce;
						$cart = $woocommerce->cart;
						$items = $woocommerce->cart->get_cart();
						$cart_count = $woocommerce->cart->get_cart_contents_count();
						if( $cart_count == 0 ) : ?>
							<div class="empty">
								<p>You haven't selected any products yet.</p>
								<a class="button" href="<?php echo get_permalink( wc_get_page_id( 'shop' ) );  ?>">Browse our catalog</a>
							</div>
						<?php else : ?>
						<div class="quote">
							<ol class="products">
							<?php 
							foreach ($items as $item => $values) :
								$product = wc_get_product( $values['data']->get_id() );
								$cart_item_key = $values['key'];
								$custom_quantity = $values['_custom_product_quantity'];
								$quantity = $values['quantity'];
								$price = $product->get_price();
								$subtotal = floatval($quantity) * floatval($price);
								
									?>
									<li class="product" data-id="<?php echo $product->get_id(); ?>" data-key="<?php echo $cart_item_key; ?>">
										<a class="product-title" href="<?php echo $product->get_permalink(); ?>">
											<h5 class="name"><?php echo $product->get_name(); ?></h5>
											<?php if($price !== '0') : ?>
												<?php if (abs($subtotal - round($subtotal)) < 0.0001) : ?>
													<h5 class="price-container">
														<span class="symbol">$</span>
														<span class="price" data-price="<?php echo $price; ?>"><?php echo $subtotal; ?></span>
														<span class="decimals">.00</span>
													</h5>
												<?php else : ?>
													<h5 class="price-container">
														<span class="symbol">$</span>
														<span class="price" data-price="<?php echo $price; ?>"><?php echo $subtotal; ?></span>
													</h5>
												<?php endif; ?>
											<?php endif; ?>
										</a>
										<?php if( $custom_quantity ) : ?>
											<h5 class="qty" data-customqty="<?php echo $custom_quantity; ?>"><?php echo $custom_quantity; ?></h5>
											<div class="update">
												<i class="fa fa-refresh update-cart" data-key="<?php echo $item; ?>" data-qty="<?php echo $custom_quantity; ?>"></i>
												<input class="custom-quantity-updater" min="0" type="text" value="<?php echo $custom_quantity; ?>"/>
											</div>
										<?php else : ?>
											<h5 class="qty" data-qty="<?php echo $quantity; ?>""><?php echo $quantity; ?></h5>
											<div class="update">
												<i class="fa fa-refresh update-cart" data-key="<?php echo $item; ?>" data-qty="<?php echo $quantity; ?>"></i>
												<input class="quantity-updater" min="0" type="number" value="<?php echo $quantity; ?>"/>
											</div>
										<?php endif; ?>
										<i class="fa fa-edit"></i>
									</li>
								<?php
							endforeach;
							?>
							</ol>
						</div>
						<?php
						endif;
					endif;

					// For gravity form
					the_content();
					
					?>
				</div>
			</div>
			
			<div class="details">
				<?php
				if( have_rows('contact_information', 'option' ) ) :
					?>
					<ul>
						<li>
							<h2>Contact Info</h2>
						</li>
						<?php
						the_row();  
						$phone_number .= get_sub_field('phone_number');
						$fax_number = get_sub_field('fax_number');
						$phone_clean .= preg_replace('/\D+/', '', $phone_number);
						$fax_clean = preg_replace('/\D+/', '', $fax_number);
						$email_address = get_sub_field('email_address');
						$address = get_sub_field('address');
						?>
						<li>
							<i class="fa fa-phone fa-2x"></i>
							<a href="tel:<?php echo $phone_clean; ?>">
								<h3><?php echo $phone_number; ?></h3>
							</a>
						</li>
						<li>
							<i class="fa fa-fax fa-2x"></i>
							<a href="tel:<?php echo $fax_clean ?>">
								<h3><?php echo $fax_number; ?></h3>
							</a>
							
						</li>
						<li>
							<i class="fa fa-envelope fa-2x"></i>
							<a href="mailto:<?php echo $email_address ?>">
								<h3 class="email"><?php echo $email_address; ?></h3>
							</a>
						</li>
						<li>
							<i class="fa fa-location-arrow fa-2x"></i>
							<h3><?php echo $address; ?></h3>
						</li>
						<?php 

						?>
					</ul>
					<?php
				endif; 
				?>
			</div>
		</article>
	<?php endwhile; ?>
</div>
<div class="map-container">
	<div class="get-directions"></div>
	<div class="map"></div>
</div>

<?php get_footer();