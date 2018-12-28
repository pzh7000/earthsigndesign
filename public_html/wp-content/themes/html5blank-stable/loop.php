

<?php if (have_posts()): while (have_posts()) : the_post(); ?>
<div class="flex-4-columns_responsive">
	<!-- article -->
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<!-- post title -->
		<h2>
			<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
		</h2>
		<!-- /post title -->

		<!-- post thumbnail -->
		<?php if ( has_post_thumbnail()) : // Check if thumbnail exists ?>
			<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
				<img class="post-image aligncenter" src="<?php the_post_thumbnail_url(); // Fullsize image for the single post ?>" alt="">
			</a>
		<?php endif; ?>
		<!-- /post thumbnail -->

		<?php html5wp_excerpt('html5wp_index'); // Build your custom callback length in functions.php ?>

	</article>
	<!-- /article -->
</div>
<?php endwhile; ?>

<?php else: ?>

	<!-- article -->
	<article>
		<h2><?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?></h2>
	</article>
	<!-- /article -->


<?php endif; ?>
