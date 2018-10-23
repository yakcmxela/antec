<?php  
	// The template for the footer callout
	// Fields
	$heading_option = get_field('callout_heading', 'option');
	$subheading_option = get_field('callout_subheading', 'option');
	$button_text_option = get_field('callout_button_text', 'option');
	$link_or_phone_option = get_field('link_or_phone_number', 'option');
	$button_link_option = get_field('callout_button_link', 'option');
	$button_phone_option = get_field('callout_phone_number', 'option');
	$callout_image_option = get_field('callout_image', 'option');

	$heading_page = get_field('custom_heading', $post_id);
	$subheading_page = get_field('custom_subheading', $post_id);
	$link_or_phone_page = get_field('link_or_phone_number', $post_id);
	$button_text_page = get_field('custom_link_text', $post_id);
	$button_phone_page = get_field('custom_phone', $post_id);
	$button_link_page = get_field('custom_link', $post_id);
	$callout_image_page = get_field('custom_image', $post_id);

	if( $callout_image_page ) {
		$img = $callout_image_page['url'];
		$alt = $callout_image_page['alt'];
		$image = '<img src="' . $img . '" alt="' . $alt . '">';
	}
	if( !$callout_image_page && $callout_image_option ) {
		$img = $callout_image_option['url'];
		$alt = $callout_image_option['alt'];
		$image = '<img src="' . $img . '" alt="' . $alt . '">';
	} elseif( !$callout_image_page && !$callout_image_option ) {
		$alt = get_bloginfo('title');
		$directory = get_bloginfo('stylesheet_directory');
		$meter = file_get_contents($directory . '/assets/1-meter-a.svg');
		$burner = file_get_contents($directory . '/assets/2-burner-a.svg');
		$glassA = file_get_contents($directory . '/assets/3-glass-a.svg');
		$glassB = file_get_contents($directory . '/assets/4-glass-a.svg');
		$bottleA = file_get_contents($directory . '/assets/5-bottle-a.svg');
		$bottleB = file_get_contents($directory . '/assets/6-glasses-books-a.svg');
		$books = file_get_contents($directory . '/assets/7-beaker-a.svg');
		$beaker = file_get_contents($directory . '/assets/8-microscope-a.svg');
		$microscope = file_get_contents($directory . '/assets/9-bottle-a.svg');
		
		$image = $meter . $burner . $glassA . $glassB . $bottleA . $bottleB . $books . $beaker . $microscope;
	}

	if( $heading_page ) {
		$heading = $heading_page;
	} else {
		$heading = $heading_option;
	}

	if( $subheading_page ) {
		$subheading = $subheading_page;
	} else {
		$subheading = $subheading_option;
	}

	if( $button_text_page ) {
		$button_text = $button_text_page;
	} else {
		$button_text = $button_text_option;
	}

	if( $button_phone_page || $button_link_page ) {

		if( $link_or_phone_page == 'phone' ) {
			$button_link = 'tel:' . preg_replace('/\D+/', '', $button_phone_page);
		} elseif ($link_or_phone_page == 'link' ) {
			$button_link = $button_link_page['url'];
		}

	} else {

		if( $link_or_phone_option == 'phone' ) {
			$button_link = 'tel:' . preg_replace('/\D+/', '', $button_phone_option);
		} elseif ($link_or_phone_option == 'link' ) {
			$button_link = $button_link_option['url'];
		}

	}


?>
<section class="callout">
	<div class="text">
		<h1><?php echo $heading; ?></h1>
		<p><?php echo $subheading; ?></p>
		<a class="button-ghost" href="<?php echo $button_link; ?>" alt="<?php echo $alt; ?>"><?php echo $button_text; ?></a>
	</div>
	<div class="image">
		<?php echo $image; ?>
	</div>
</section>