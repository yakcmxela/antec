<?php get_header(); ?>

	<div class="container">
		<div class="filter-container">
			<div class="filter-label">Filter By...</div>
			<div class="filter-dropdown">
				<div class="filter-display">
					<?php
						if( single_term_title( '', false ) ){
							single_term_title();
						} else {
							echo 'Filter By...';
						}
					?>
				</div>
				<ul>
					<li><a href="<?php echo get_permalink( get_option('page_for_posts' ) ); ?>">All</a></li>
					<?php
						$categories = get_categories( array(
							'orderby' => 'name',
							'order'   => 'ASC'
						) );

						foreach( $categories as $category ) {
							$caturl = get_category_link( $category->term_id );
							$catname = $category->name;

							echo '<li><a href="' . $caturl .'">' . $catname. '</a></li>';
						}

						$args = array(
					        'taxonomy' => 'product_cat',
					        'orderby' => 'name',
					        'order' => 'ASC',
					        'hide_empty' => false
					  	);
					  	$categories = get_categories( $args );
					  	foreach( $categories as $category ) {
							$caturl = get_category_link( $category->term_id );
							$catname = $category->name;
							if(strtolower($catname) !== 'uncategorized' && strtolower($catname) !== 'chemical type') {
								echo '<li><a href="' . $caturl .'?s=' . get_search_query() . '">' . $catname. '</a></li>';
							}
						}
					?>
				</ul>
			</div>
		</div>
		<?php if($post == null) : ?>
			<h2>No results found.</h2>
		<?php else : ?>
			<?php while( have_posts() ): the_post(); ?>
				<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
					<a href="<?php the_permalink(); ?>">
						<h2 class="result-title"><?php the_title(); ?></h2>
						<div class="details" href="<?php the_permalink(); ?>">
							<?php the_excerpt(); ?>
							<div class="button-ghost"><i class="fa fa-search-plus"></i>Learn More</div>
						</div>
					</a>
				</article>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php
			echo paginate_links( array(
				'prev_text' => '<i class="fa fa-angle-left"></i>',
				'next_text' => '<i class="fa fa-angle-right"></i>',
				'type' => 'list'
			) );
		?>
	</div>

<?php get_footer();