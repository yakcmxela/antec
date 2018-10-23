<?php

class MakespaceChild {

	function __construct(){
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		$this->antec_short_description_catalog();
		$this->antec_woocommerce_setup();
		$this->antec_filter_products();
		$this->antec_add_to_quote();
		$this->antec_update_quote();
		$this->antec_request_quote();
	}

	function wp_enqueue_scripts(){
		$msw_object = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'home_url' => home_url(),
			'show_dashboard_link' => current_user_can( 'manage_options' ) ? 1 : 0,
			'site_url' => site_url(),
			'stylesheet_directory' => get_stylesheet_directory_uri(),
			'google_maps' => get_google_map_data(),
		);
		$google_api_key = 'https://maps.googleapis.com/maps/api/js?key=' . get_field( 'msw_google_map_api_key', 'option' );
		wp_enqueue_script('google-maps', $google_api_key, true);

		wp_enqueue_script( 'theme', get_stylesheet_directory_uri() . '/scripts.min.js', array( 'jquery' ), filemtime( get_stylesheet_directory() . '/scripts.min.js' ) );
		wp_localize_script( 'theme', 'MSWObject', $msw_object );
		
		wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css?family=Kalam:100,400|Pragati+Narrow:400,700|Noto+Sans:400,700,700i' );
		wp_enqueue_style( 'theme', get_stylesheet_uri(), array(), filemtime( get_stylesheet_directory() . '/style.css' ) );
	}
	
	function antec_woocommerce_setup(){
		// Build custom catalog filter
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		add_action( 'woocommerce_before_shop_loop', 'antec_custom_filter', 30 );
		function antec_custom_filter() {
			$queried_object = get_queried_object();
			// Get all product categories
			$taxonomy		= 'product_cat';
			$show_count 	= 0;
			$pad_counts 	= 0;
			$hierarchical 	= 1;
			$title			= '';
			$empty			= 0;

			$category_args = array(
				'taxonomy' 		=> $taxonomy,
				'show_count' 	=> $show_count,
				'pad_counts' 	=> $pad_counts,
				'hierarchical' 	=> $hierarchical,
				'title_li' 		=> $title,
				'hide_empty' 	=> $empty,
			);
			$all_categories = get_categories( $category_args );

			// Parent categories
			// For custom ordered parent objects
			$parents = array(); 
			// Iterate over all available categories
			foreach ( $all_categories as $category ) {
				// Get parent categories excluding 'uncategorized'
				if( $category->parent === 0 && $category->slug !== 'uncategorized' ) {
					// Get custom ordering set as Taxonomy ACF term
					$order = intval(get_field('custom_order', $category));
					// Create parents array
					$parents[$order] = $category;
				}
			}

			// Sort parents by custom ordering
			ksort($parents);

			// For ajax and pagination
			$catalog_rows = intval(get_option( 'woocommerce_catalog_rows' ));
			$catalog_columns = intval(get_option( 'woocommerce_catalog_columns' ));
			$posts_per_page = $catalog_columns * $catalog_rows;

			// Create custom filters menu
			$filter_menu;
			$filter_menu .= '<h2>Product Catalog</h2>';
			$filter_menu .= '<div class="product-filter" data-ppp="' . $posts_per_page . '">';
			$filter_menu .= '<div class="heading">';
			$filter_menu .= '<h4>Filter By...</h4>';
			$filter_menu .= '<i class="fa fa-angle-down"></i>';
			$filter_menu .= '</div>';
			$filter_menu .= '<div class="catalog">';
			// Product categories
			$filter_menu .= '<div class="product-categories">';
			// Sort select button
			$filter_menu .= '<div class="orderby">';
			$filter_menu .= '<select>';
			$filter_menu .= '<option value="asc">A-Z</option>';
			$filter_menu .= '<option value="desc">Z-A</option>';
			$filter_menu .= '</select>';
			$filter_menu .= '<i class="fa fa-angle-down"></i>';
			$filter_menu .= '</div>';
			foreach ( $parents as $order => $category ) {
		 		// Start category list
				$filter_menu .= '<ul>';
				// Display category parent name
				$filter_menu .= '<li>' . $category->name . '</li>'; 
				// Search for child category
				$child_category = array();
				$sub_category_args = array(
					'taxonomy' => $taxonomy,
					'parent' => $category->cat_ID,
					'hide_empty' => 0
				);
				$sub_categories = get_categories( $sub_category_args );
				foreach ($sub_categories as $sub_category) {
					$external_link = get_field('external_link', $sub_category);
					if($external_link) {
						$filter_menu .= '<li data-slug="' . $sub_category->slug . '">';
						$filter_menu .= '<a href="' . $external_link['url'] . '" target="_blank">';
						$filter_menu .= $sub_category->name;
						$filter_menu .= '</a>';
					} else {
						if($sub_category->slug == $queried_object->slug) {
							$classes = 'toggleable selected';
						} else {
							$classes = 'toggleable';
						}
						$filter_menu .= '<li class="' . $classes . '" data-slug="' . $sub_category->slug . '">';
						$filter_menu .= $sub_category->name;
					}
					$filter_menu .= '</li>';
				}
				// Close list
				$filter_menu .= '</ul>';
			}

			// Closing categories
			$filter_menu .= '</div>';
			// Search button
			$filter_menu .= '<div class="button-container">';
			$filter_menu .= '<div class="loader">';
			$filter_menu .= '<span class="hex">&#x2B22;</span>';
			$filter_menu .= '<span class="hex">&#x2B22;</span>';
			$filter_menu .= '<span class="hex">&#x2B22;</span>';
			$filter_menu .= '<span class="hex">&#x2B22;</span>';
			$filter_menu .= '<span class="hex">&#x2B22;</span>';
			$filter_menu .= '<span class="hex">&#x2B22;</span>';
			$filter_menu .= '<span class="hex">&#x2B22;</span>';
			$filter_menu .= '</div>';
			$filter_menu .= '<a class="button">Get Results</a>';
			$filter_menu .= '<p class="clear-results">Clear results</p>';
			$filter_menu .= '</div>';
			$filter_menu .= '</div>';
			// Close filter menu
			$filter_menu .= '</div>';		
			echo $filter_menu;
		}

		// Add intro text content to catalog
		add_action( 'woocommerce_archive_description', 'antec_catalog_description', 10);
		function antec_catalog_description() {
			if( is_shop() ) {
				$post_id = get_option( 'woocommerce_shop_page_id' );

				if( the_field( 'image', $post_id ) ) {
					$custom_image = get_field( 'image', $post_id );
					$image = $custom_image['url'];
					$alt = $image['alt'];
				} else {
					$image = get_bloginfo('stylesheet_directory') . '/assets/chems-supplies.svg';
					$alt = 'Antec product catalog.';
				}
				
				$text = get_field('text', $post_id);

				$catalog_description = '<div class="intro">';
				$catalog_description .= '<div class="text">';
				$catalog_description .= $text;
				$catalog_description .= '</div>';
				$catalog_description .= '<img src="' . $image . '" alt="' . $alt . '">';
				$catalog_description .= '</div>';

				echo $catalog_description;
			}
		}

		// Remove product photos from catalog
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

		// Remove result counter from catalog
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20);

		// Remove add to cart button on catalog page, replace with 'Learn More'
		add_action( 'woocommerce_after_shop_loop_item', 'antec_replace_add_to_cart_buttons', 1 );
		function antec_replace_add_to_cart_buttons() {
		      	// Remove add to cart
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
		}

	    // Remove prices
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

		// Footer for woo pages
		add_action( 'woocommerce_after_main_content', 'antec_footer', 100 );

		function antec_footer() {
			$queried_object = get_queried_object();
			if( is_shop() ) {
				$post_id = get_option( 'woocommerce_shop_page_id' );
			} elseif( is_product() ) {
				$post_id = $queried_object->ID;
			} elseif( is_product_category() ){
				$post_id = $queried_object;
			} elseif( is_cart() ) {
				$post_id = get_option( 'woocommerce_cart_page_id' ); 
			}

			$request_callout = get_field('add_footer_callout', $post_id);
			if( $request_callout == 'yes' ) {
				include('footer-callout.php');
			}
		}

		add_action( 'woocommerce_after_shop_loop', 'antec_custom_pagination', 9);
		function antec_custom_pagination() {
			global $woocommerce;
			$queried_object = get_queried_object();
			if($queried_object !== null) {
				$args = array( 
					'post_type' => 'product',
					'product_cat' => $queried_object->slug,
					'posts_per_page' => -1 
				);
			} else {
				$args = array( 
					'post_type' => 'product',
					'posts_per_page' => -1 
				);
			}
			
			$products = new WP_Query( $args );
			$total_products = $products->found_posts;
			$catalog_rows = intval(get_option( 'woocommerce_catalog_rows' ));
			$catalog_columns = intval(get_option( 'woocommerce_catalog_columns' ));
			$posts_per_page = $catalog_columns * $catalog_rows;
			$total_pages = ceil($total_products / $posts_per_page);
			$current_page = 1;

			if( $total_pages > 1 ) {
				$pagination .= '<ul class="ajax-pagination" data-ppp="' . $posts_per_page . '">';

				$start_page = ( $current_page < 5 ) ? 1 : $current_page - 4;
				$end_page = 5 + $start_page;
				$end_page = ( $total_pages < $end_page ) ? $total_pages : $end_page;
				$delta = $start_page - $end_page + 8;
				$start_page -= ( $start_page - $delta > 0 ) ? $delta : 0;

				if( $start_page <= 0 ) {
					$end_page -= ($start_page - 1);
					$start_page = 1;
				}

				if( $end_page > $total_pages ) {
					$end_page = $total_pages;
				}

				if( $start_page > 1 ) {
					$pagination .= '<li class="page-nav prev" data-page="' . ($current_page - 1) . '"><</li>';
					if($current_page == 1 ) {
						$pagination .= '<li class="page current" data-page="1">1</li>';
					} else {
						$pagination .= '<li class="page" data-page="1">1</li>';
					}
					
					$pagination .= '<li class="trail">...</li>';
				}

				if( $current_page == null ) {
					$current_page = 1;
				}

				for( $i = $start_page; $i <= $end_page; $i++ ) {
					if($current_page == $i) {
						$pagination .= '<li class="page current" data-page="' . $i . '">' . $i . '</li>';
					} else {
						$pagination .= '<li class="page" data-page="' . $i . '">' . $i . '</li>';
					}
				}

				if( $end_page < $total_pages ) {
					$pagination .= '<li class="trail">...</li>';
					$pagination .= '<li class="page" data-page="' . $total_pages . '">' . $total_pages . '</li>';
					$pagination .= '<li class="page-nav next" data-page="' . ($current_page + 1) . '">></li>';
				}

				$pagination .= '</ul>';

				echo $pagination;
			}
			wp_reset_postdata();
		}
		
		// Single product layout
		// Add catalog name to page
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		add_action( 'woocommerce_before_single_product_summary', 'antec_catalog_name', 1 );
		function antec_catalog_name() {
			global $woocommerce;
			global $product;
			$catalog = get_the_title(woocommerce_get_page_id( 'shop' ));
			$item = $product->get_name();
			$headline = '<div class="headline">';
			$headline .= '<h1>' . $catalog . '</h1>';
			$headline .= '<h2>' . $item . '</h2>';
			// div closes after image thumbnail for mobile flexbox
			echo $headline;
		}
		add_action( 'after_setup_theme', 'antec_woo_supports' );

		function antec_woo_supports() {
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		add_action( 'woocommerce_single_product_summary', 'antec_product_content', 20 );
		function antec_product_content() {
			global $product;
			$content = $product->get_description();
			echo $content;
		}			

		// Remove 'you have successfully added' message on add to quote
		add_filter( 'wc_add_to_cart_message_html', '__return_null' );

		// Remove price
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			
		// Add custom product variations
		add_action( 'woocommerce_single_product_summary', 'antec_product_variations', 25 );
		function antec_product_variations() {
			global $product;
			if( $product->is_type('simple') ) { 
				add_action( 'woocommerce_before_add_to_cart_button', 'display_price' );
				function display_price() {
					global $product;
					if( $product->get_price() !== '0' ) {
						echo $product->get_price_html( true );
					}
				}
			}
		}

			// Remove upsells / related
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

			// Remove single product meta
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		
			// Remove data tabs
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 1);
		
			// Change cart text to add to quote
		remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
		add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

		add_filter( 'woocommerce_product_single_add_to_cart_text', 'antec_custom_cart_button' );
		
		function antec_custom_cart_button() {
			return __( 'Add to Quote', 'woocommerce' );
		}

			// Custom input at add to cart
		function antec_custom_cart() {
			global $product;
			$product_terms = get_the_terms( $product->ID, 'product_cat' );
			$has_custom_product_quantity_input = false;
			foreach ($product_terms as $term) {
				$parent_term = get_term( $term->parent);
				$has_custom_product_quantity_input = get_field('custom_product_quantity_input', $term);
				if($has_custom_product_quantity_input == false) {
					$has_custom_product_quantity_input = get_field('custom_product_quantity_input', $parent_term);
				}
			}
			if($has_custom_product_quantity_input ==  true) {
				$value = isset( $_POST['_custom_product_quantity'] ) ? sanitize_text_field( $_POST['_custom_product_quantity'] ) :
				printf( '<div class="quantity measured"><label>%s</label><input class="custom-product-quantity" name="_custom_product_quantity" value="%s" placeholder="e.g. 100 grams"/></div>', __('Measured Quantity', 'antec_custom_cart_quantity' ), esc_attr( $value ) );
			}
		}
		add_action( 'woocommerce_before_add_to_cart_button', 'antec_custom_cart', 9);
	}

	function antec_filter_products() {
		add_action( 'wp_ajax_antec_filter_products', 'filter_products' );
		add_action( 'wp_ajax_nopriv_antec_filter_products', 'filter_products' );
		function filter_products() {
			$params = $_POST['params'];
			$sort = $_POST['sort'];
			$offset = $_POST['offset'];
			$current_page = $_POST['pageSelected'];

			$product_cats = implode(',', $params);
			$catalog_rows = intval(get_option( 'woocommerce_catalog_rows' ));
			$catalog_columns = intval(get_option( 'woocommerce_catalog_columns' ));
			$posts_per_page = $catalog_columns * $catalog_rows;

			if( $offset == 'NaN' ) {
				$offset = 0;
			}

			$args = array(
				'post_type' => 'product',
				'posts_per_page' => $posts_per_page,
				'product_cat' => $product_cats,
				'tax_query' => array(
					'taxonomy' => 'product_cat',
					'terms' => $value,
					'operator' => 'AND',
				),
				'orderby' => array('menu_order' => $sort, 'title' => $sort),
				'offset' => $offset,
				'post_status' => 'publish',
			);

			// The query
			$loop = new WP_Query( $args );
			// Number of posts for pagination
			$total_products = $loop->found_posts;

			if( $loop->have_posts() ) {
				while( $loop->have_posts() ) : $loop->the_post();

					// Loop through products
					global $post;
					$id = $post->ID;
					$permalink = get_the_permalink($post);
					$title = get_the_title($post);
					$short_description =  wp_trim_words($post->post_content);
					$product = '<li class="product filtered" data-id="' . $id . '">';
					$product .= '<a href="' . $permalink. '">';
					$product .= '<h2>' . $title . '</h2>';
					$product .= '</a>';
					$product .= '<div class="details">';
					$product .= '<a href="' . $permalink. '">';
					$product .= '<div class="short-description">' . $short_description . '</div>';
					$product .= '<div class="button-ghost"><i class="fa fa-search-plus"></i>Learn More</div>';
					$product .= '</div>';
					$product .= '</a>';
					$product .= '</li>';

					echo $product;

				endwhile;

				// Create pagination
				$total_pages = ceil($total_products / $posts_per_page);

				if( $total_pages > 1 ) {
					$pagination .= '<ul class="ajax-pagination" data-ppp="' . $posts_per_page . '">';

					$start_page = ( $current_page < 5 ) ? 1 : $current_page - 2;
					$end_page = 4 + $start_page;
					$end_page = ( $total_pages < $end_page ) ? $total_pages : $end_page;
					$delta = $start_page - $end_page + 4;
					$start_page -= ( $start_page - $delta > 0 ) ? $delta : 0;

					if( $start_page <= 0 ) {
						$end_page -= ($start_page - 1);
						$start_page = 1;
					}

					if( $end_page > $total_pages ) {
						$end_page = $total_pages;
					}

					if( $start_page > 1 ) {
						$pagination .= '<li class="page-nav prev" data-page="' . ($current_page - 1) . '"><</li>';
						if($current_page == 1 ) {
							$pagination .= '<li class="page current" data-page="1">1</li>';
						} else {
							$pagination .= '<li class="page" data-page="1">1</li>';
						}

						$pagination .= '<li class="trail">...</li>';
					}

					if( $current_page == null ) {
						$current_page = 1;
					}

					for( $i = $start_page; $i <= $end_page; $i++ ) {
						if($current_page == $i) {
							$pagination .= '<li class="page current" data-page="' . $i . '">' . $i . '</li>';
						} else {
							$pagination .= '<li class="page" data-page="' . $i . '">' . $i . '</li>';
						}
					}

					if( $end_page < $total_pages ) {
						$pagination .= '<li class="trail">...</li>';
						$pagination .= '<li class="page" data-page="' . $total_pages . '">' . $total_pages . '</li>';
						$pagination .= '<li class="page-nav next" data-page="' . ($current_page + 1) . '">></li>';
					}

					$pagination .= '</ul>';

					echo $pagination;
				}
				
			} else {
				// Fallback if no products found
				$no_products .= '<div class="no-results">';
				$no_products .= '<p>No products found.</p>';
				$no_products .= '</div>';

				echo $no_products;
			}

			wp_reset_postdata();
			wp_die();
		}
	}

	function antec_add_to_quote() {
		// Update cart on quote page
		add_action( 'wp_ajax_antec_add_to_quote', 'add_to_quote' );
		add_action( 'wp_ajax_nopriv_antec_add_to_quote', 'add_to_quote' );
		function add_to_quote() {
			global $woocommerce;

			$product_id;
			$quantity;
			$custom_quantity;
			$product_variations;
			$grouped_products;

			if( isset($_POST['product']) ) {
				$product_id = $_POST['product'];
			} else {
				$product_id = null;
			}
			if( isset($_POST['quantity']) ) {
				$quantity = $_POST['quantity'];
			} else {
				$quantity = null;
			}
			if( isset($_POST['custom_quantity']) ) {
				$custom_quantity = sanitize_text_field($_POST['custom_quantity']);
			} else {
				$custom_quantity = null;
			}
			if( isset($_POST['variation']) ) {
				$product_variations = $_POST['variation'];
			} else {
				$product_variations = null;
			}
			if( isset($_POST['grouped']) ) {
				$grouped_products = $_POST['grouped'];
			} else {
				$grouped_products = null;
			}

			if( $grouped_products == null ) {
				$product = wc_get_product($product_id);
				if( $product->is_type( 'variable' ) ) {
					$variations = [];
					$attributes = [];
					$available_variations = $product->get_available_variations();
					foreach ($product_variations as $variation) {
						$values = array_values($variation);
						$variations[$values[0]] = $values[1];
					}
					foreach ( $available_variations as $key => $val ) {
						$attributes[$val['variation_id']] = $val['attributes'];
					}
					foreach ($attributes as $variation_id => $attribute) {
						if( $attribute === $variations ) {
							$woocommerce->cart->add_to_cart($product_id, $quantity, $variation_id, $attribute);
						}
					}
				} else {
					// Submit simple non-variable product
					$woocommerce->cart->add_to_cart($product_id, $quantity, null, null, array( '_custom_product_quantity' => $custom_quantity ) );
				}
			} else {
				foreach ($grouped_products as $grouped_product) {
					$product_id = $grouped_product['id'];
					$product_quantity = $grouped_product['quantity'];
					$woocommerce->cart->add_to_cart($product_id, $product_quantity);
				}
			}
			$count = $woocommerce->cart->get_cart_contents_count();
			wp_send_json($count);
		}
	}

	function antec_update_quote(){
		// Update cart on quote page
		add_action( 'wp_ajax_antec_update_quote', 'update_quote' );
		add_action( 'wp_ajax_nopriv_antec_update_quote', 'update_quote' );
		function update_quote() {
			global $woocommerce;

			$product = $_POST['product'];
			$quantity = $_POST['quantity'];
			$item_key = $_POST['item_key'];
			$custom_quantity = sanitize_text_field($_POST['custom_quantity']);

			if($custom_quantity == null) {
				$woocommerce->cart->set_quantity($product, $quantity);
				$response = array(
					'cart_count' => $woocommerce->cart->get_cart_contents_count()
				);
			} else {
				$response = array(
					'custom_quantity' => $custom_quantity
				);
				$cart_items = $woocommerce->cart->cart_contents;
				if($custom_quantity == 0) {
					$woocommerce->cart->remove_cart_item($item_key);
				} else {
					foreach ($cart_items as $cart_item) {
						if($cart_item['key'] == $item_key) {
							$cart_item['_custom_product_quantity'] = $custom_quantity;
							$woocommerce->cart->cart_contents[$cart_item['key']]['_custom_product_quantity'] = $custom_quantity;
							$woocommerce->cart->set_session();
						}

					}
				}
				
			}
			wp_send_json($response);		
		}
	}

	function antec_short_description_catalog() {
		// Short description and link to product (catalog)
		add_action( 'woocommerce_shop_loop_item_title', 'antec_short_description_start', 11);
		add_action( 'woocommerce_after_shop_loop_item', 'antec_short_description_end', 100);
		function antec_short_description_start() {
			if( is_product_category() || is_shop()) { 
				global $product;
				$description = $product->get_description();
				$details;
				$details .= '<div class="details">';
				$details .= '<div class="short-description">' . wp_trim_words( $description, 25 ) . '</div>';
				// The learn more buttom
				$details .= '<div class="button-ghost"><i class="fa fa-search-plus"></i>Learn More</div>';

				echo $details;
			}
		}	
		function antec_short_description_end() {
			$end = '</div>'; // Details
			$end .= '</div>'; // Description

			echo $end;
		}
	}

	function antec_request_quote() {
		add_action( 'wp_ajax_antec_request_quote', 'request_quote' );
		add_action( 'wp_ajax_nopriv_antec_request_quote', 'request_quote' );
		function request_quote() {
			$cart = $_POST['cart'];
			$customer = $_POST['customer'];

			global $woocommerce;

			$quote = wc_create_order();
			$order_id = $quote->get_ID();

			foreach( $cart as $item ) {
				$product_id = $item['id'];
				$product = get_product($product_id);
				$quantity = $item['quantity'];
				$args = array(
					'_custom_product_quantity' => $item['custom_quantity'],
				);
				$item_id = $quote->add_product( $product, $quantity, $args );
				if($item['custom_quantity'] !== null) {
					wc_update_order_item_meta( $item_id, 'Measured Qty', $item['custom_quantity'], true );
				}
			}
			$quote->set_address( $customer, 'billing' );
			$quote->set_address( $customer, 'shipping' );
			$quote->add_order_note( $customer['message'] );
			$quote->update_status( 'Completed', 'Imported order', true );
			$wc_emails = new WC_Emails();
			$emails = $wc_emails->get_emails();
			$new_email = $emails['WC_Email_New_Order'];
			$new_email->trigger($order_id);
			$woocommerce->cart->empty_cart();
		}
	}

}

$MakespaceChild = new MakespaceChild();
