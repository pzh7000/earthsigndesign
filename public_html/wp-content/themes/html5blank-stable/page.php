<?php get_header(); ?>
<!-- Page.php -->
	<main role="main">
		<!-- section -->
		<section>

			<!-- <div class="homepage__hero" style="background-image: url(<?php the_field('heading_image'); ?>); background-position: <?php the_field('background_position');?> center;">
				<div class="homepage__title_container">
					<h1><?php the_field('title'); ?></h1>
				</div>
			</div> -->

			<!-- <img class="heading-image" src="<?php the_field('heading_image'); ?>"> -->

			<div class="page-content">
				<h1><?php the_field('header'); ?></h1>

				<?php if (have_posts()): while (have_posts()) : the_post(); ?>

					<!-- article -->
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<?php the_content(); ?>

						<?php comments_template( '', true ); // Remove if you don't want comments ?>

						<br class="clear">

						<?php edit_post_link(); ?>

					</article>
					<!-- /article -->

					<?php endwhile; ?>

					<?php else: ?>

						<!-- article -->
						<article>

						<h2><?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?></h2>

					</article>
					<!-- /article -->

				<?php endif; ?>
			</div>
		</section>
		<!-- /section -->
	</main>

<?php get_footer(); ?>
