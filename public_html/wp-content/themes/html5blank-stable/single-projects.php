<?php get_header(); ?>

<!-- Single.php -->
	<main role="main">
	<!-- section -->
	<section>

    <div class="page-content-archive">


	<?php if (have_posts()): while (have_posts()) : the_post(); ?>

    <div class="single-post-container">
  		<!-- article -->
  		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <!-- post title -->
  			<h1>
  				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
  			</h1>
  			<!-- /post title -->

  			<!-- post thumbnail -->
  			<?php if ( has_post_thumbnail()) : // Check if Thumbnail exists ?>

  					<img class="size-medium wp-image-51 aligncenter" src="<?php the_post_thumbnail_url(); // Fullsize image for the single post ?>" alt="">

  			<?php endif; ?>
  			<!-- /post thumbnail -->

        <?php the_content(); // Dynamic Content ?>

  		</article>
  		<!-- /article -->
    </div>

	<?php endwhile; ?>

	<?php else: ?>

		<!-- article -->
		<article>

			<h1><?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?></h1>

		</article>
		<!-- /article -->

	<?php endif; ?>

	</section>
	<!-- /section -->
	</main>

<?php get_footer(); ?>
