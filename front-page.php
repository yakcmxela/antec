<?php 
/*
 * Template Name: Front-page
 */
get_header(); 
?>
	
<div class="home">

	<!-- HERO -->
		<?php 
			// Fields
			$hero_image = get_field('hero_image');
			$hero_text = get_field('hero_text');
			$hero_button_link = get_field_object('hero_button_link');
			$hero_url = $hero_button_link['value'];
			$hero_button_text = get_field('hero_button_text');
			$overlay_color_1 = get_field('overlay_color_1');
			$overlay_color_2 = get_field('overlay_color_2');
			$overlay_angle = get_field('overlay_angle');
			$overlay;
			if( $overlay_color_1 && $overlay_color_2 && $overlay_angle ) {
				$overlay = 'background: linear-gradient(' . $overlay_angle . 'deg, ' . $overlay_color_1 . ', ' . $overlay_color_2 . ');';
			}
		?>
		
		<section class="hero" style="background-image: url(<?php echo $hero_image['url']; ?>)">
			<div class="overlay" style="<?php echo $overlay; ?>"></div>
			<div class="inner">
				<h1><?php echo $hero_text; ?></h1>
				<a href="<?php echo $hero_url; ?>">
					<p><?php echo $hero_button_text; ?></p>
				</a>
			</div>
		</section>

	<!-- PRODUCT CATEGORIES -->
		<?php 
			// Fields
			$categories_text = get_field('categories_text');
		?>
		<?php if( have_rows('categories') ) : ?>
			<div class="categories">
				<p class="headline"><?php echo $categories_text; ?></p>
				<ul>
				<?php while( have_rows('categories') ) : the_row(); ?>
					<?php 
						// Subfields
						$category = get_sub_field('category');
						$short_sentence = get_sub_field('short_sentence');

						$queried_object = get_queried_object();
						$taxonomy = get_sub_field('taxonomy', $queried_object);
						$url = get_term_link($taxonomy);

						$name;
						if( $category !== '' ) {
							$name = $category;
						} else {
							$name = $taxonomy->name;
						}
						
					?>
					<li>
						<a href="<?php echo $url; ?>">
							<div class="category">
								<h3><?php echo $name; ?></h3>
								<h4><?php echo $short_sentence; ?></h4>
								<div class="plus">
									<div class="line"></div>
									<div class="line"></div>
								</div>
							</div>
						</a>
					</li>
				<?php endwhile; ?>
				</ul>
			</div>
		<?php endif; ?>
	
	<!-- QUANTI-PAK -->
		<?php 
			// Fields
			$qp_heading = get_field('qp_heading');
			$qp_image = get_field('qp_image');
			if( $qp_image ) {
				$image = $qp_image['url'];
				$alt = $qp_image['alt'];
			} else { 
				$image = get_bloginfo('stylesheet_directory') . '/assets/qp-lp.svg';
				$alt = 'QUANTI-PAK chemical cupplies';
			}
			$qp_text = get_field('qp_text');
			$qp_button_text = get_field('qp_button_text');
			$qp_button_url = get_field('qp_button_link');
			$qp_link_text = get_field('qp_link_text');
			$qp_link_url = get_field('qp_link_url');

		?>
		<section class="qp">
			<div class="container">
				<div class="inner">
					<div class="left">
						<h2><?php echo $qp_heading; ?></h2>
						<?php echo $qp_text; ?>
						<div>
							<a class="button" href="<?php echo $qp_button_url; ?>">
								<?php echo $qp_button_text; ?>
							</a>	
						</div>
						<span class="call">
							<a href="<?php echo $qp_link_url; ?>">
								<?php echo $qp_link_text; ?>
							</a>
						</span>
					</div>
					<div class="right">
						<img src="<?php echo $image; ?>" alt="<?php echo $alt; ?>">
					</div>
				</div>
				
			</div>
		</section>
	
	<!-- RESOURCES -->
		<?php 
			// Fields
			$res_heading = get_field('res_heading');
			$res_text = get_field('res_text');
			$res_button_text = get_field('res_button_text');
			$res_button_url = get_field('res_button_link');
			$res_embellishment = get_field('res_embellishment');
			$res_chart_title = get_field('res_chart_title');
			$res_page_link = get_field('res_page_link');
		?>
		<section class="resources">
			<div class="container">
				<div class="inner">
					<div class="left">
						<h2><?php echo $res_heading; ?></h2>
						<?php echo $res_text; ?>
						<div>
							<a class="button" href="<?php echo $res_button_url; ?>">
								<?php echo $res_button_text; ?>
							</a>	
						</div>
						<div class="embellishment">
							<h3><?php echo $res_embellishment; ?></h3>
							<img src="<?php echo get_bloginfo('stylesheet_directory') ?>/assets/arrow.svg">
						</div>
					</div>
					<div class="right">
						<?php if( have_rows('res_chart') ) : ?>
							<h2><?php echo $res_chart_title; ?></h2>
							<table>
							<?php while( have_rows('res_chart') ) : the_row(); ?>
								<tr>
									<th><?php echo get_sub_field('column_one'); ?></th>
									<th><?php echo get_sub_field('column_two'); ?></th>
								</tr>
							<?php endwhile; ?>
							</table>
							<div class="page-link">
								<a href="<?php echo $res_page_link['url']; ?>" title="<?php echo $res_page_link['title']; ?>">
									<?php echo $res_page_link['title']; ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

	<!-- ABOUT -->
		<?php 
			// Fields
			$logo;
			if( the_field( 'site_logo', 'option' ) ) {
				$logo = the_field( 'site_logo', 'option' );
			} else {
				$logo = get_bloginfo('stylesheet_directory') . '/assets/logo-dark.svg';
			}
			$about_image = get_field('about_image');
			$about_heading = get_field('about_heading');
			$about_text = get_field('about_text');
			$about_button_text = get_field('about_button_text');
			$about_button_url = get_field('about_button_link');
		?>
		<section class="about" style="background-image: url(<?php echo $about_image['url']; ?>);" alt="<?php echo $about_image['alt']; ?>">
			<div class="container">
				<div class="inner">
					<div class="left">
						<img class="logo" src="<?php echo $logo ?>" alt="<?php bloginfo('name'); ?>">	
					</div>
					<div class="right">
						<h2><?php echo $about_heading; ?></h2>
						<?php echo $about_text; ?>
						<div>
							<a class="button" href="<?php echo $about_button_url; ?>">
								<?php echo $about_button_text; ?>
							</a>	
						</div>
					</div>
				</div>
			</div>
		</section>

</div>

<?php get_footer();