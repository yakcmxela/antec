(function($){

	var docReady = function(){

		// General
			var pageLocationTop;
			var pageLocationBottom;
			var windowHeight;
			var windowWidth;

			function defineViewport() {
				windowHeight = window.innerHeight;
				windowWidth = window.innerWidth;
				pageLocationTop = $(window).scrollTop();
				pageLocationBottom = pageLocationTop + windowHeight;
			}

			function debounce(func, wait, immediate) {
				var timeout;
				return function() {
					var context = this, args = arguments;
					var later = function() {
						timeout = null;
						if (!immediate) func.apply(context, args);
					};
					var callNow = immediate && !timeout;
					clearTimeout(timeout);
					timeout = setTimeout(later, wait);
					if (callNow) func.apply(context, args);
				};
			}

			function hoverElement(el) {
				if(el.length) {
					el.on('mouseenter', function(e) {
						el.addClass('overlay');
						$(e.currentTarget).removeClass('overlay');
					});
					el.on('mouseleave', function() {
						el.removeClass('overlay');
					});
				}
			}

			function animateElement(el, reset, trigger) {
				// el = element to add animated class
				// reset = reset when scrolled out of view (bool)
				// trigger = when to add class. percentage of element height (int) 
				//           for example: 50 would be 50% of the elements height.
				if(el.length) {
					defineViewport();
					var elTop = el.offset().top;
					var elHeight = el.innerHeight();
					var elBottom = elTop + elHeight;
					var elAnimated = (elHeight * (trigger / 100)) + elTop;
					var elReset = reset;

					if( pageLocationBottom >= elAnimated ) {
						el.addClass('animated');
					} else if( pageLocationBottom < elAnimated && elReset == true) {
						el.removeClass('animated');
					}
				}
			}

			// Elements with hover effect
			var homepageCategoriesOverlay = $('.categories ul li a');

			// Elements to animate
			var homepageCategories = $('.categories ul li');
			var woocommerceImageContainer = $('.woocommerce-product-gallery');
			var footerCallout = $('.callout');

			// Doc ready events
			hoverElement(homepageCategoriesOverlay);
			animateElement(homepageCategories, true, 50);
			animateElement(footerCallout, true, 15);
			
			// On load
			$(window).on('load', function() {
				setInitialQty();
				stickyProduct();
			});

			// Scroll events
			$(window).on('scroll', function() {
				$.each(homepageCategories, function() {
					animateElement($(this), true, 50);
				});
				animateElement(footerCallout, true, 5);
				stickyProduct();
			});

		// Nav
			// Dropdown
			// Add overflow scroll after animation and set max height
			var dropdownNav = $('#dropdown-nav-primary');
			var ocnNav = $('#ocn');
			var parentMenuItem = $('#dropdown-nav-primary .menu-item-has-children');
			var dropdownTransitionTime;
			if(dropdownNav.length) {
				dropdownTransitionTime = dropdownNav.css('transition-duration');
				dropdownTransitionTime = dropdownTransitionTime.slice(0, -1);
				dropdownTransitionTime = parseFloat(dropdownTransitionTime) * 1000;
			} 
			var isNavOpen = false;
			var mainMaxHeight;

			$.each(parentMenuItem, function() {
				var next = document.createElement('div');
				next.setAttribute('class', 'next-level-button');
				$(this).append(next);
			});

			$.each($('.next-level-button'), function() {
				var arrow = document.createElement('i');
				arrow.setAttribute('class', 'fa fa-angle-double-down');
				$(this).append(arrow);
			});

			$('.next-level-button').on('click', function(e) {
				var el = $(e.currentTarget).parent();
				$(this).toggleClass('rotate');
				el.toggleClass('menu-item-open');
			});

			// Search toggle
			$('.search-trigger').on('click', function() {
				$('.search-container').toggleClass('active');
			});
			
		// Pages
			// Product archive page
			var filter = {
				container: $('.product-filter'),
				heading: $('.product-filter .heading'),
				category: $('.product-categories .toggleable'),
				button: $('.product-filter .button'),
				clear: $('.clear-results'),
				orderby: $('.orderby select'),
				selected: $('.product-categories .selected'),
			};
			var loader = $('.loader');
			var products = $('.products');
			var singleProduct = products.find('.product');
			var selectedCategories = [];
			var sort = filter.orderby.val();
			var currentPage;
			var postsPerPage;
			var pageSelected;
			var offset;

				$.each(filter.selected, function() {
					selectedCategories.push($(this).data('slug'));
				});

				// Open/Close menu
				filter.heading.on('click', function() {
					filter.container.toggleClass('open');
				});

				// Create array of selected categories and add selected state
				filter.category.on('click', function(e) {
					// Add checkmark
					$(e.currentTarget).toggleClass('selected');
					// Add or remove query parameters
					var selected = $(this).data('slug');
					if( selectedCategories.indexOf(selected) > -1 ) {
						selectedCategories = selectedCategories.filter(function(item) { 
							return item !== selected;
						});
					} else {
						selectedCategories.push(selected);
					}
				});

		// AJAX filter
			function filterProducts(sort, params, offset, pageSelected) {
				var results = '';
				// Add transitional class
				products.addClass('loading');
				$('.ajax-pagination').css('opacity', '0');
				loader.addClass('animated');
				var scrollTo = ($('.product-filter').offset().top - 24) - $('.site-header').innerHeight();

				var filterProducts = $.ajax({
					url: MSWObject.ajax_url,
					method: 'POST',
					data: {
						action: 'antec_filter_products',
						params: params,
						sort: sort,
						offset: offset,
						pageSelected: pageSelected,
					}
				});

				filterProducts.fail(function(textStatus) {
					console.log(textStatus);
				});
				
				filterProducts.success(function(data) {
					$('.ajax-pagination').remove();
					// // Set initial box sizing
					// var currentProductsHeight = products.innerHeight();
					// products.css('max-height', currentProductsHeight);
					// products.css('min-height', currentProductsHeight);
					// Create container
					var productsDummy = document.createElement('ul');
					productsDummy.setAttribute('id', 'dummy');
					productsDummy.setAttribute('class', 'products');
					$('.archive .container').append(productsDummy);
				});

				filterProducts.done(function(data) {
					// Hide loader, close menu
					loader.removeClass('animated');
					filter.container.removeClass('open');
					// Append data
					var dummy = $('#dummy');
					dummy.html(data);
					var dummyHeight = dummy.innerHeight();
					dummy.remove();
					products.css('max-height', dummyHeight);
					products.css('min-height', dummyHeight);
					products.html(data);
					products.addClass('loading');
					setTimeout(function() {
						products.removeClass('loading');
					}, 300);
					$('html, body').animate({scrollTop: scrollTo}, 500, 'linear');
				});
			}

			var productContainerResize = debounce(function() {
				var productsDummy = document.createElement('ul');
				productsDummy.setAttribute('id', 'dummy');
				productsDummy.setAttribute('class', 'products');
				$('.archive .container').append(productsDummy);

				var data = products.html();
				var dummy = $('#dummy');
				dummy.html(data);
				var dummyHeight = dummy.innerHeight();
				dummy.remove();

				products.css('max-height', dummyHeight);
				products.css('min-height', dummyHeight);
			}, 300);

			window.addEventListener('resize', productContainerResize);

			filter.orderby.on('change', function() {
				sort = $(this).val();
			});

			// Filter products
			filter.button.on('click', function() {
				// Adds clear results option after menu is closed
				setTimeout(function() {
					filter.clear.css('display', 'block');
				}, 1000);

				filterProducts(sort, selectedCategories, 0, 1);
			});

			// Clear filter
			filter.clear.on('click', function() {
				setTimeout(function() {
					filter.clear.css('display', 'none');
				}, 1000);

				// Reset selections and empty array
				filter.category.removeClass('selected');
				selectedCategories = [];

				filterProducts(sort, selectedCategories, 0, 1);
			});

			$('.container').on('click', '.ajax-pagination .page', function(e) {
				postsPerPage = parseInt(filter.container.data('ppp'));
				pageSelected = parseInt($(e.currentTarget).attr('data-page'));
				offset = (pageSelected - 1) * postsPerPage;

				filterProducts(sort, selectedCategories, offset, pageSelected);
			});

			$('.container').on('click', '.ajax-pagination .page-nav', function(e) {
				postsPerPage = parseInt(filter.container.data('ppp'));
				pageSelected = parseInt($(e.currentTarget).attr('data-page'));
				offset = (pageSelected - 1) * postsPerPage;

				filterProducts(sort, selectedCategories, offset, pageSelected);
			});

		// Single product
			$('.qty').on('keyup change', function() {
				setInitialQty();
			});

			function setInitialQty() {
				var qty = $('.qty').val();
				$('.flex-control-thumbs li').attr('data-qty', qty);
				$('.woocommerce-product-gallery__wrapper').attr('data-qty', qty);
			}

			function stickyProduct() {
				defineViewport();
				var imageTop = pageLocationTop + 1;
					// Max set @ cart plus padding
					var elHeight = $('.woocommerce-product-gallery__wrapper').innerHeight();
					var containerHeight = $('.entry-summary').innerHeight();
					var cartHeight = $('.cart').innerHeight();

					var max = containerHeight - cartHeight - elHeight - 64; // 64 padding
					if(pageLocationTop <= max) {
						$('.woocommerce-product-gallery').css('top', imageTop + 'px');
					}
				}

		// AJAX Add to cart 
			function addToQuote(product, quantity, customQuantity, variation, grouped) {
				var addToQuote = $.ajax({
					url: MSWObject.ajax_url,
					method: 'POST',
					data: {
						action: 'antec_add_to_quote',
						product: product,
						quantity: quantity,
						custom_quantity: customQuantity,
						variation: variation,
						grouped: grouped,
					}
				});

				addToQuote.fail(function(textStatus) {
					console.log(textStatus);
				});

				addToQuote.success(function(data) {
					$('.site-header').addClass('quote');
					$('.single_add_to_cart_button').addClass('added');
					$('.quote-count').attr('data-qty', data);
				});
			}

			var addToCart = $('.single_add_to_cart_button');
			addToCart.on('click', function(e) {
				e.preventDefault();

				var quantity;
				var customQuantity;
				var product;
				var variation;
				var variationSelected = $('.variations_form .variations .value select');
				var groupedProducts;
				var groupProduct = $('.woocommerce-grouped-product-list-item');
				
				if(variationSelected.length) {
					// Variable products
					variation = [];
					$.each(variationSelected, function() {
						var variation_obj = {
							'variation': $(this).attr('data-attribute_name'),
							'value': $(this).val(),
						};
						variation.push(variation_obj);
					});
					quantity = $('.qty').val();
					customQuantity = $('.custom-product-quantity').val();
					product = $('.variations_form').attr('data-product_id');
				} else if(groupProduct.length) {
					// Grouped products
					groupedProducts = [];
					$.each(groupProduct, function() {
						quantity = $(this).find('.qty').val();
						customQuantity = $('.custom-product-quantity').val();
						id = $(this).attr('id').replace('product-', '');

						groupedProducts.push({
							id: id,
							quantity: quantity
						});
					});

				} else {
					// Simple products
					product = addToCart.val();
					quantity = $('.qty').val();
					customQuantity = $('.custom-product-quantity').val();
				}

				addToQuote(product, quantity, customQuantity, variation, groupedProducts);
			});

		// Contact & Quote page
			// Set min container height for when the form is submitted
			var contactContainerHeight = debounce(function() {
				defineViewport();
				if(windowWidth >= 890) {
					var detailsHeight = $('.page-template-page_contact .details').innerHeight();
					$('.page-template-page_contact .container article').css('min-height', detailsHeight);
				}
			}, 250);
			window.addEventListener('resize', contactContainerHeight);
			window.addEventListener('load', contactContainerHeight);
			// Google Map
			function antec_map( $el ) {
				var maps_obj = JSON.parse(MSWObject.google_maps);
				var lat = parseFloat(maps_obj[0].lat);
				var lng = parseFloat(maps_obj[0].lng);
				
				var center = {lat: lat, lng: lng};
				var opts = {
					zoom: 14,
					center: center,
				};

				var map = new google.maps.Map( $el[0], opts );
				var marker = new google.maps.Marker({
					position: center,
					map: map
				});

				$('.get-directions').html(maps_obj[0].address);
				return map;
			}

			var map;
			$('.map').each(function() {
				map = antec_map( $(this) );
			});

			// Edit product quantities 
			$('.fa-edit').on('click', function(e) {
				$(e.currentTarget).parent().addClass('edit');
			});

		// Update the quote (cart)
			function updateQuote(product, itemKey, quantity, customQuantity, currentTarget) {
				var updateQuote = $.ajax({
					url: MSWObject.ajax_url,
					method: 'POST',
					data: {
						action: 'antec_update_quote',
						product: product,
						item_key: itemKey,
						quantity: quantity,
						custom_quantity: customQuantity,
					}
				});

				updateQuote.fail(function(textStatus) {
					console.log(textStatus);
				});

				updateQuote.done(function(data) {
					currentTarget.removeClass('animated');
					currentTarget.parent().parent().removeClass('edit');
					if(data.custom_quantity !== null) {
						currentTarget.parent().siblings('.qty').html(data.custom_quantity);
						currentTarget.parent().siblings('.qty').attr('data-customqty', data.custom_quantity);
					}
					currentTarget.parent().siblings('.qty').html(quantity);
					currentTarget.parent().siblings('.qty').attr('data-qty', quantity);
					$('.quote-count').attr('data-qty', data.cart_count);

					var price = currentTarget.parent().siblings('.product-title').find('.price').attr('data-price');
					var newPrice = parseFloat(price) * parseInt(quantity);
					currentTarget.parent().siblings('.product-title').find('.price').html(newPrice);

					if(quantity == 0 || customQuantity == '0') {
						setTimeout(function() {
							currentTarget.parent().parent().fadeOut();
						}, 300);
					}
				});
			}

			$('.update-cart').on('click', function(e) {
				var lineItem = $(e.currentTarget);
				var itemKey = lineItem.data('key');
				var quantity = lineItem.data('qty');
				var newQuantity = lineItem.siblings('.quantity-updater').val();
				var customQuantity = lineItem.siblings('.custom-quantity-updater').val();
				var product = lineItem.data('key');
				$(this).addClass('animated');
				updateQuote(product, itemKey, newQuantity, customQuantity, lineItem);
			});

		// Request quote
			function requestQuote(customer, cart) {
				console.log(customer);
				var requestQuote = $.ajax({
					url: MSWObject.ajax_url,
					method: 'POST',
					data: {
						action: 'antec_request_quote',
						cart: cart,
						customer: customer,
					}
				});

				requestQuote.fail(function(textStatus) {
					console.log(textStatus);
				});

				requestQuote.done(function(data) {
					// Nothing
				});
			}

			// Form vars
			var quoteForm = {
				form: $('.quote-form'),
				id: $('.quote-form').attr('id'),
				submit: $('.quote-form .gform_footer .gform_button'),
			};

			// Fire ajax when quote form is successful
			$(document).bind('gform_confirmation_loaded', function(e, formId) {
				if(quoteForm.form.length) {
					// Only fire if form is quote form
					quoteFormId = quoteForm.id.slice(-1);
					// Fire ajax
					if(formId == quoteFormId) {
						requestQuote(customerDetails, customerCart);
					}
					
				}
				var scrollTo = $('article').offset().top;
				$('html, body').animate({scrollTop: '0px'}, 500, 'linear');
				
			});

		// Gather product information and form data. Actual ajax is fired above
			var customerDetails = [];
			var customerCart = [];

			quoteForm.submit.on('click', function() {

				// Fade out and stop clicks to prevent multiple entries
				quoteForm.submit.css('opacity', '.25');
				quoteForm.submit.css('pointer-events', 'none');

				var firstName = $('.quote-first-name .ginput_container input').val();
				var lastName = $('.quote-last-name .ginput_container input').val();
				var email = $('.quote-email .ginput_container input').val();
				var phone = $('.quote-phone .ginput_container input').val();
				var streetAddress1 = $('.quote-address .address_line_1 input').val();
				var streetAddress2 = $('.quote-address .address_line_2 input').val();
				var city = $('.quote-address .address_city input').val();
				var state = $('.quote-address .address_state input').val();
				var zip = $('.quote-address .address_zip input').val();
				var country = $('.quote-address .address_country select').val();
				var message = $('.quote-message .ginput_container textarea').val();
				
				customerDetails = {
					first_name: firstName,
					last_name: lastName,
					email: email,
					phone: phone,
					address_1: streetAddress1,
					address_2: streetAddress2,
					city: city,
					state: state,
					postcode: zip,
					country: country,
					message: message,
				};

				$.each(singleProduct, function() {
					var id = $(this).data('id');
					var quantity = $(this).find('.qty').attr('data-qty');
					var customQuantity = $(this).find('.qty').attr('data-customqty');
					quantity = parseInt(quantity);

					customerCart.push({
						id: id,
						quantity: quantity,
						custom_quantity: customQuantity,
					});

				});
				
			});
	};

	$(document).ready(function(){
		docReady();
	});

})(jQuery);